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
 * Class ProjectRateFixtures
 * @package App\DataFixtures
 */
class ProjectRateFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        for ($i = 0; $i < 10; $i++) {

            /**
             * @var Project $project
             */
            $project = $this->getReference('project'.$i);

            $rate = new ProjectRate();
            $rate
                ->setHourlyRateOffSite($faker->boolean ? $faker->randomFloat(2, 50, 200) : null)
                ->setHourlyRateOnSite($faker->boolean ? $faker->randomFloat(2, 50, 200) : null)
                ->setProject($project)
                ->setStartedAt($faker->dateTime());
            $manager->persist($rate);
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
            ProjectFixtures::class,
        ];
    }
}
