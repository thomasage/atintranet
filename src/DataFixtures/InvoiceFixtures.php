<?php
declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Address;
use App\Entity\Client;
use App\Entity\Invoice;
use App\Entity\InvoiceDetail;
use App\Repository\ClientRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

/**
 * Class InvoiceFixtures
 * @package App\DataFixtures
 */
class InvoiceFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @var ClientRepository
     */
    private $clientRepository;

    /**
     * InvoiceFixtures constructor.
     * @param ClientRepository $clientRepository
     */
    public function __construct(ClientRepository $clientRepository)
    {
        $this->clientRepository = $clientRepository;
    }

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        for ($i = 0; $i < 10; $i++) {

            $address = new Address();
            $address
                ->setAddress($faker->streetAddress)
                ->setCity($faker->city)
                ->setCountry($faker->countryCode)
                ->setName($faker->company)
                ->setPostcode(preg_replace('[^0-9]', '', $faker->postcode));
            $manager->persist($address);

            $issue = $faker->dateTimeBetween('-1 year');

            $invoice = new Invoice();
            $invoice
                ->setAddress($address)
                ->setClient($this->getRandomClient())
                ->setIssueDate($issue)
                ->setNumber(
                    sprintf(
                        '%s%s%s',
                        $issue->format('y'),
                        $issue->format('m'),
                        str_pad((string)$faker->numberBetween(1, 999), 3, '0', STR_PAD_LEFT)
                    )
                );
            $manager->persist($invoice);

            $detail = new InvoiceDetail();
            $detail
                ->setAmountUnit((string)$faker->numberBetween(50, 100))
                ->setDesignation(ucfirst($faker->words(3, true)))
                ->setQuantity($faker->numberBetween(1, 10));
            $manager->persist($detail);

            $invoice->addDetail($detail);

            $detail = new InvoiceDetail();
            $detail
                ->setAmountUnit((string)$faker->numberBetween(50, 100))
                ->setDesignation(ucfirst($faker->words(3, true)))
                ->setQuantity($faker->numberBetween(1, 10));
            $manager->persist($detail);

            $invoice->addDetail($detail);

            $detail = new InvoiceDetail();
            $detail
                ->setAmountUnit((string)$faker->numberBetween(50, 100))
                ->setDesignation(ucfirst($faker->words(3, true)))
                ->setQuantity($faker->numberBetween(1, 10));
            $manager->persist($detail);

            $invoice->addDetail($detail);

        }

        $manager->flush();
    }

    /**
     * @return Client
     */
    private function getRandomClient(): Client
    {
        $clients = $this->clientRepository->findAll();
        shuffle($clients);

        return $clients[0];
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
