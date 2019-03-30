<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Project;
use App\Entity\ProjectRate;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

/**
 * Class ProjectRateFixtures.
 */
class ProjectRateFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        for ($p = 0; $p < 10; ++$p) {
            /** @var Project $project */
            $project = $this->getReference('project'.$p);

            $rates = $faker->randomDigit;

            for ($r = 0; $r < $rates; ++$r) {
                $rate = new ProjectRate();
                $rate
                    ->setHourlyRateOffSite($faker->boolean ? $faker->randomFloat(2, 50, 200) : null)
                    ->setHourlyRateOnSite($faker->boolean ? $faker->randomFloat(2, 50, 200) : null)
                    ->setProject($project)
                    ->setStartedAt($faker->dateTime());
                $manager->persist($rate);
            }
        }

        $manager->flush();
    }

    /**
     * @return array
     */
    public function getDependencies(): array
    {
        return [
            ProjectFixtures::class,
        ];
    }
}
