<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\ClientRate;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

class ClientRateFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        for ($c = 0; $c < 20; ++$c) {
            /** @var Client $client */
            $client = $this->getReference('client'.$c);

            $rates = $faker->randomDigit;

            for ($r = 0; $r < $rates; ++$r) {
                $rate = new ClientRate();
                $rate
                    ->setHourlyRateOffSite($faker->boolean ? $faker->randomFloat(2, 50, 200) : null)
                    ->setHourlyRateOnSite($faker->boolean ? $faker->randomFloat(2, 50, 200) : null)
                    ->setClient($client)
                    ->setStartedAt($faker->dateTime());
                $manager->persist($rate);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ClientFixtures::class,
        ];
    }
}
