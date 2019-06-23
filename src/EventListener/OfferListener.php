<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Offer;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

class OfferListener
{
    public function prePersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof Offer) {
            return;
        }

        $em = $args->getObjectManager();
        $number = $em->getRepository(Offer::class)->findNextNumber($entity);

        $entity->setNumber($number);
    }
}
