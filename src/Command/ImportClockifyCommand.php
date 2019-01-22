<?php
declare(strict_types=1);

namespace App\Command;

use App\Entity\Address;
use App\Entity\Client;
use App\Entity\Project;
use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client as GuzzleClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class ImportClockifyCommand
 * @package App\Command
 */
class ImportClockifyCommand extends Command
{
    /**
     * @var string
     */
    protected static $clockifyApiUrl = 'https://api.clockify.me/api';

    /**
     * @var string
     */
    protected static $defaultName = 'app:import-clockify';

    /**
     * @var string
     */
    private $clockifyApiKey;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * ImportClockifyCommand constructor.
     * @param EntityManagerInterface $em
     * @param string $clockifyApiKey
     */
    public function __construct(EntityManagerInterface $em, string $clockifyApiKey)
    {
        if ('' === $clockifyApiKey || 'null' === $clockifyApiKey) {
            throw new \RuntimeException('CLOCKIFY_API_KEY is not set.');
        }

        parent::__construct();
        $this->em = $em;
        $this->clockifyApiKey = $clockifyApiKey;
    }

    protected function configure(): void
    {
        $this->setDescription('Import CSV file from Clockify');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $repoClient = $this->em->getRepository(Client::class);
        $repoProject = $this->em->getRepository(Project::class);
        $repoTask = $this->em->getRepository(Task::class);

        $guzzleClient = new GuzzleClient(
            [
                'base_uri' => self::$clockifyApiUrl.'/',
                'headers' => ['X-Api-Key' => $this->clockifyApiKey],
                'http_errors' => false,
                'timeout' => 10.0,
            ]
        );

        $response = $guzzleClient->get('workspaces/');
        if (200 !== $response->getStatusCode()) {
            $io->error(sprintf('Unable to fetch workspaces (%d)', $response->getStatusCode()));

            return 1;
        }

        $workspaces = json_decode($response->getBody()->getContents());

        foreach ($workspaces as $workspace) {

            $io->section(sprintf('Workspace %s', $workspace->id));

            $io->note('Fetch projects');

            $response = $guzzleClient->get(sprintf('workspaces/%s/projects/', $workspace->id));
            if (200 !== $response->getStatusCode()) {
                $io->error(sprintf('Unable to fetch projects (%d)', $response->getStatusCode()));

                return 1;
            }

            $projects = json_decode($response->getBody()->getContents());

            foreach ($projects as $p) {

                $io->writeln(sprintf('Project "%s"', $p->name));

                $client = $repoClient->findOneBy(['externalReference' => $p->client->id]);
                if (!$client instanceof Client) {

                    $address = new Address();
                    $address
                        ->setCity('-')
                        ->setName($p->client->name)
                        ->setPostcode('-');
                    $this->em->persist($address);

                    $client = new Client();
                    $client
                        ->setAddressPrimary($address)
                        ->setExternalReference($p->client->id)
                        ->setName($p->client->name);
                    $this->em->persist($client);

                }

                $project = $repoProject->findOneBy(['client' => $client, 'externalReference' => $p->id]);
                if (!$project instanceof Project) {
                    $project = new Project();
                    $project
                        ->setClient($client)
                        ->setExternalReference($p->id)
                        ->setName($p->name);
                    $this->em->persist($project);
                }

                $response = $guzzleClient->get(sprintf('workspaces/%s/timeEntries/project/%s', $workspace->id, $p->id));
                if (200 !== $response->getStatusCode()) {
                    $io->error(sprintf('Unable to fetch time entries (%d)', $response->getStatusCode()));
                    $this->em->flush();

                    return 1;
                }

                $timeEntries = json_decode($response->getBody()->getContents());

                foreach ($timeEntries as $timeEntry) {

                    $task = $repoTask->findOneBy(['externalReference' => $timeEntry->id]);
                    if ($task instanceof Task) {
                        continue;
                    }

                    try {

                        $task = new Task();
                        $task
                            ->setExternalReference($timeEntry->id)
                            ->setName($timeEntry->description)
                            ->setProject($project)
                            ->setStart(new \DateTime($timeEntry->timeInterval->start))
                            ->setStop(new \DateTime($timeEntry->timeInterval->end));

                        if (is_array($timeEntry->tags)) {
                            foreach ($timeEntry->tags as $tag) {
                                if ('On site' === $tag->name) {
                                    $task->setOnSite(true);
                                } elseif ('Unexpected' === $tag->name) {
                                    $task->setExpected(false);
                                }
                            }
                        }

                        $this->em->persist($task);

                    } catch (\Exception $e) {

                        $io->error(sprintf('Unable to create task: %s', $e));

                        $this->em->flush();

                        return 1;

                    }

                }

            }

        }

        $this->em->flush();

        $io->success('DONE');

        return 0;
    }
}
