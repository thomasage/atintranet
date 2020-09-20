<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Param;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

class ParamFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $param = new Param();
        $param
            ->setCode('company_name')
            ->setDescription('Name of your company')
            ->setValue($faker->company);
        $manager->persist($param);

        $param = new Param();
        $param
            ->setCode('company_address')
            ->setDescription('Address of your company')
            ->setValue(
                sprintf(
                    "%s\n%s %s\n%s",
                    $faker->streetAddress,
                    preg_replace('/[^0-9]/', '', $faker->postcode),
                    $faker->city,
                    $faker->country
                )
            );
        $manager->persist($param);

        $param = new Param();
        $param
            ->setCode('invoice_footer')
            ->setDescription('Footer of invoice')
            ->setValue(
                sprintf(
                    "En application de la loi n° 91-1442 : règlement anticipé 0%% d'escompte ; pénalités de retard : une fois et demie le taux d'intérêt légal ; indemnité forfaitaire pour frais de recouvrement pour non paiement dans les délais : 40 €
SAS au capital de %d € - RCS %s %s - SIRET %s - NAF %d%d%d%d%s - N° TVA %s %s",
                    $faker->randomNumber(0),
                    $faker->city,
                    $faker->siren,
                    $faker->siret,
                    $faker->randomDigit,
                    $faker->randomDigit,
                    $faker->randomDigit,
                    $faker->randomDigit,
                    strtoupper($faker->randomLetter),
                    $faker->countryCode,
                    $faker->siren
                )
            );
        $manager->persist($param);

        $param = new Param();
        $param
            ->setCode('invoice_bank_account')
            ->setDescription('Account of your bank (for invoice)')
            ->setValue(
                sprintf(
                    'Banque : %s %s
IBAN : %sXX XXXX XXXX XXXX XXXX XXXX XXX - BIC/SWIFT : XXXXXXXXXX
Code banque : %d - Code guichet : %d - Compte : %s - Clé : %d',
                    $faker->company,
                    $faker->city,
                    $faker->countryCode,
                    $faker->randomNumber(5),
                    $faker->randomNumber(5),
                    $faker->bankAccountNumber,
                    $faker->randomNumber(2)
                )
            );
        $manager->persist($param);

        $manager->flush();
    }
}
