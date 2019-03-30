<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class UserFixtures.
 */
class UserFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    /**
     * UserFixtures constructor.
     *
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $user = new User();
        $user
            ->setPassword($this->encoder->encodePassword($user, 'admin'))
            ->setRole('ROLE_ADMIN')
            ->setUsername('admin');
        $manager->persist($user);

        for ($i = 0; $i < 3; ++$i) {

            /** @var Client $client */
            $client = $this->getReference('client'.$i);

            $user = new User();
            $user
                ->setClient($client)
                ->setPassword($this->encoder->encodePassword($user, $faker->password))
                ->setRole('ROLE_CLIENT')
                ->setUsername($faker->userName);
            $manager->persist($user);

        }

        $manager->flush();
    }

    /**
     * @return array
     */
    public function getDependencies(): array
    {
        return [
            ClientFixtures::class,
        ];
    }
}
