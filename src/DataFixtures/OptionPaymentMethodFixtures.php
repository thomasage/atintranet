<?php
declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\OptionPaymentMethod;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class OptionPaymentMethodFixtures
 * @package App\DataFixtures
 */
class OptionPaymentMethodFixtures extends Fixture
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $option = new OptionPaymentMethod();
        $option->setName('Virement');
        $manager->persist($option);
        $this->setReference('payment_method_transfer', $option);

        $option = new OptionPaymentMethod();
        $option->setName('Chèque');
        $manager->persist($option);
        $this->setReference('payment_method_check', $option);

        $option = new OptionPaymentMethod();
        $option->setName('Espèces');
        $manager->persist($option);
        $this->setReference('payment_method_cash', $option);

        $option = new OptionPaymentMethod();
        $option->setName('Carte bancaire');
        $manager->persist($option);
        $this->setReference('payment_method_card', $option);

        $manager->flush();
    }
}
