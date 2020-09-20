<?php

declare(strict_types=1);

namespace App\Form\Type\ChoiceType;

use App\Entity\Client;
use App\Repository\ClientRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ClientChoiceType.
 */
class ClientChoiceType extends AbstractType
{
    /**
     * @var array
     */
    private $choices;

    /**
     * ClientChoiceType constructor.
     */
    public function __construct(ClientRepository $repo)
    {
        /** @var Client[] $clients */
        $clients = $repo->findBy([], ['name' => 'ASC', 'id' => 'ASC']);

        $this->choices = [];
        foreach ($clients as $c) {
            $this->choices[(string) $c->getName()] = $c->getId();
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['choices' => $this->choices]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
