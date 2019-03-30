<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Project;
use App\Entity\Task;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

/**
 * Class TaskFixtures.
 */
class TaskFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        for ($i = 0; $i < 10; ++$i) {
            /**
             * @var Project
             */
            $project = $this->getReference('project'.$i);

            for ($j = 0; $j < $faker->numberBetween(50, 100); ++$j) {
                $start = $faker->dateTimeBetween('-2 years');
                $stop = clone $start;
                $stop->modify(sprintf('+%d minutes', $faker->numberBetween(5, 60 * 2)));

                $task = new Task();
                $task
                    ->setExpected($faker->boolean)
                    ->setName($faker->randomElement(['Task A', 'Task B']))
                    ->setOnSite($faker->boolean)
                    ->setProject($project)
                    ->setStart($start)
                    ->setStop($stop);
                $manager->persist($task);
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
