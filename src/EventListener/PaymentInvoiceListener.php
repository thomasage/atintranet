<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Invoice;
use App\Entity\PaymentInvoice;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

/**
 * Class PaymentInvoiceListener.
 */
class PaymentInvoiceListener
{
    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args): void
    {
        $this->updateInvoiceAmountPaid($args);
    }

    /**
     * @param LifecycleEventArgs $args
     */
    private function updateInvoiceAmountPaid(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof PaymentInvoice) {
            return;
        }

        /** @var Invoice $invoice */
        $invoice = $entity->getInvoice();

        $amount = '0.0';
        foreach ($invoice->getPaymentInvoices() as $paymentInvoice) {
            $amount = bcadd($amount, $paymentInvoice->getAmount());
        }

        $invoice->setAmountPaid($amount);

        $em = $args->getObjectManager();
        $em->flush();
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args): void
    {
        $this->updateInvoiceAmountPaid($args);
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postRemove(LifecycleEventArgs $args): void
    {
        $this->updateInvoiceAmountPaid($args);
    }
}
