<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\Project;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

/**
 * Class ProjectFixtures.
 */
class ProjectFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        for ($i = 0; $i < 10; ++$i) {
            /** @var Client $client */
            $client = $this->getReference('client'.$i);

            $project = new Project();
            $project
                ->setClient($client)
                ->setName(ucfirst($faker->words(3, true)));
            $manager->persist($project);
            $this->setReference('project'.$i, $project);
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
