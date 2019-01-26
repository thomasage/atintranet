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
 * Class ImportTogglCommand
 * @package App\Command
 */
class ImportTogglCommand extends Command
{
    /**
     * @var string
     */
    protected static $togglApiUrl = 'https://www.toggl.com/api/v8';

    /**
     * @var string
     */
    protected static $defaultName = 'app:import-toggl';

    /**
     * @var string
     */
    private $togglApiKey;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * ImportTogglCommand constructor.
     * @param EntityManagerInterface $em
     * @param string $togglApiKey
     */
    public function __construct(EntityManagerInterface $em, string $togglApiKey)
    {
        if ('' === $togglApiKey || 'null' === $togglApiKey) {
            throw new \RuntimeException('TOGGL_API_KEY is not set.');
        }

        parent::__construct();
        $this->em = $em;
        $this->togglApiKey = $togglApiKey;
    }

    protected function configure(): void
    {
        $this->setDescription('Import CSV file from Toggl');
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
                'auth' => [$this->togglApiKey, 'api_token'],
                'base_uri' => self::$togglApiUrl.'/',
                'http_errors' => false,
                'timeout' => 10.0,
            ]
        );

        $response = $guzzleClient->get('workspaces');
        if (200 !== $response->getStatusCode()) {
            $io->error(sprintf('Unable to fetch workspace (%d)', $response->getStatusCode()));

            return 1;
        }

        $workspaces = json_decode($response->getBody()->getContents());

        foreach ($workspaces as $workspace) {

            $io->section(sprintf('Workspace #%d "%s"', $workspace->id, $workspace->name));

            $response = $guzzleClient->get(sprintf('workspaces/%d/clients', $workspace->id));
            if (200 !== $response->getStatusCode()) {
                $io->error(sprintf('Unable to fetch clients (%d)', $response->getStatusCode()));

                return 1;
            }

            $clients = json_decode($response->getBody()->getContents());

            foreach ($clients as $client) {

                $io->writeln(sprintf('Client #%d "%s"', $client->id, $client->name));

                $localClient = $repoClient->findOneBy(['externalReference' => $client->id]);
                if (!$localClient instanceof Client) {

                    $address = new Address();
                    $address
                        ->setCity('-')
                        ->setName($client->name)
                        ->setPostcode('-');
                    $this->em->persist($address);

                    $localClient = new Client();
                    $localClient
                        ->setAddressPrimary($address)
                        ->setExternalReference((string)$client->id)
                        ->setName($client->name);
                    $this->em->persist($localClient);

                }

            }

        }

        $this->em->flush();

        foreach ($workspaces as $workspace) {

            $io->section(sprintf('Workspace #%d "%s"', $workspace->id, $workspace->name));

            $response = $guzzleClient->get(sprintf('workspaces/%d/projects', $workspace->id));
            if (200 !== $response->getStatusCode()) {
                $io->error(sprintf('Unable to fetch projects (%d)', $response->getStatusCode()));

                return 1;
            }

            $projects = json_decode($response->getBody()->getContents());

            foreach ($projects as $project) {

                $io->writeln(sprintf('Project #%d "%s"', $project->id, $project->name));

                $localProject = $repoProject->findOneBy(['externalReference' => $project->id]);
                if (!$localProject instanceof Project) {

                    $localClient = $repoClient->findOneBy(['externalReference' => $project->cid]);
                    if (!$localClient instanceof Client) {
                        $io->error(sprintf('Unable to find client "%d"', $project->cid));

                        return 1;
                    }

                    $localProject = new Project();
                    $localProject
                        ->setClient($localClient)
                        ->setExternalReference((string)$project->id)
                        ->setName($project->name);
                    $this->em->persist($localProject);

                    $io->note('Project added');

                }

            }

        }

        $this->em->flush();

        $start = \DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s', mktime(0, 0, 0, (int)date('n'), 1)));

        $response = $guzzleClient->get(sprintf('time_entries?start_date=%s', urlencode($start->format('c'))));
        if (200 !== $response->getStatusCode()) {
            $io->error(sprintf('Unable to fetch time_entries (%d)', $response->getStatusCode()));

            return 1;
        }

        $timeEntries = json_decode($response->getBody()->getContents());

        foreach ($timeEntries as $timeEntry) {

            $io->writeln(sprintf('Task #%d "%s"', $timeEntry->id, $timeEntry->description));

            $task = $repoTask->findOneBy(['externalReference' => $timeEntry->id]);
            if (!$task instanceof Task) {

                $localProject = $repoProject->findOneBy(['externalReference' => $timeEntry->pid]);
                if (!$localProject instanceof Project) {
                    $io->error(sprintf('Unable to find project "%d"', $timeEntry->pid));

                    return 1;
                }

                try {

                    $task = new Task();
                    $task
                        ->setExternalReference((string)$timeEntry->id)
                        ->setName($timeEntry->description)
                        ->setProject($localProject)
                        ->setStart(new \DateTime($timeEntry->start))
                        ->setStop(new \DateTime($timeEntry->stop));

                    if (isset($timeEntries->tags)) {
                        foreach ($timeEntry->tags as $tag) {
                            if ('On site' === $tag) {
                                $task->setOnSite(true);
                            } elseif ('Unexpected' === $tag) {
                                $task->setExpected(false);
                            }
                        }
                    }

                    $this->em->persist($task);

                    $io->note('Task added');

                } catch (\Exception $e) {

                    $io->error(sprintf('Unable to create task: %s', $e));

                    return 1;

                }

            }

        }

        $this->em->flush();

        $io->success('DONE');

        return 0;
    }
}
