<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Invoice;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

/**
 * Class InvoiceListener.
 */
class InvoiceListener
{
    /**
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof Invoice) {
            return;
        }

        $em = $args->getObjectManager();
        $number = $em->getRepository(Invoice::class)->findNextNumber($entity);

        $entity->setNumber($number);
    }
}
