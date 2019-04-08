<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class UserAddCommand.
 */
class UserAddCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'app:user:add';

    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var UserRepository
     */
    private $repository;

    /**
     * UserAddCommand constructor.
     *
     * @param UserPasswordEncoderInterface $encoder
     * @param UserRepository               $repository
     * @param EntityManagerInterface       $em
     */
    public function __construct(
        UserPasswordEncoderInterface $encoder,
        UserRepository $repository,
        EntityManagerInterface $em
    ) {
        parent::__construct();
        $this->encoder = $encoder;
        $this->repository = $repository;
        $this->em = $em;
    }

    protected function configure(): void
    {
        $this->setDescription('Add an user');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $helper = $this->getHelper('question');

        $question = new Question('Please enter the username:');
        $username = $helper->ask($input, $output, $question);

        $user = $this->repository->findOneBy(['username' => $username]);
        if ($user instanceof User) {
            $io->warning('User already exists.');

            return 1;
        }

        $question = new Question('Please enter the password:');
        $question->setHidden(true);
        $question->setHiddenFallback(false);
        $password = $helper->ask($input, $output, $question);

        $question = new ChoiceQuestion(
            'Please enter the role:', ['ROLE_ADMIN', 'ROLE_CLIENT', 'ROLE_USER'], 'ROLE_USER'
        );
        $role = $helper->ask($input, $output, $question);

        try {
            $user = new User();
        } catch (\Exception $e) {
            $io->warning(sprintf('Unable to create user: %e', $e));

            return 1;
        }
        $user
            ->setPassword($this->encoder->encodePassword($user, $password))
            ->setRole($role)
            ->setUsername($username);
        $this->em->persist($user);
        $this->em->flush();

        $io->success('User added.');

        return 0;
    }
}
