<?php
declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Address;
use App\Entity\Client;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

/**
 * Class ClientFixtures
 * @package App\DataFixtures
 */
class ClientFixtures extends Fixture
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        for ($i = 0; $i < 20; $i++) {

            $address = new Address();
            $address
                ->setAddress($faker->streetAddress)
                ->setCity($faker->city)
                ->setCountry($faker->countryCode)
                ->setName($faker->company)
                ->setPostcode(preg_replace('/[\D]/', '', $faker->postcode));
            $manager->persist($address);

            $client = new Client();
            $client
                ->setActive($faker->boolean)
                ->setAddressPrimary($address)
                ->setCode(substr(strtoupper($faker->unique()->word), 0, 10))
                ->setName($faker->company)
                ->setVatNumber(preg_replace('/[^0-9A-Z]/', '', $faker->vat));
            $manager->persist($client);
            $this->setReference('client'.$i, $client);

        }

        $manager->flush();
    }
}
