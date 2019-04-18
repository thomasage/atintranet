<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Invoice;
use App\Form\Type\InvoiceDeleteType;
use App\Form\Type\InvoiceSearchType;
use App\Form\Type\InvoiceType;
use App\Model\InvoicePDF;
use App\Repository\InvoiceRepository;
use App\Service\SearchManager;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/invoice")
 * @IsGranted("ROLE_ADMIN")
 */
class InvoiceController extends AbstractController
{
    /**
     * @param Request                $request
     * @param EntityManagerInterface $em
     * @param TranslatorInterface    $translator
     * @param Invoice                $invoice
     *
     * @return Response
     *
     * @Route("/{uuid}/delete",
     *     name="app_invoice_delete",
     *     methods={"GET", "POST"})
     */
    public function delete(
        Request $request,
        EntityManagerInterface $em,
        TranslatorInterface $translator,
        Invoice $invoice
    ): Response {
        if ($invoice->getLocked()) {
            return $this->redirectToRoute('app_invoice_show', ['uuid' => $invoice->getUuid()]);
        }

        $formDelete = $this->createForm(InvoiceDeleteType::class, $invoice);
        $formDelete->handleRequest($request);

        if ($formDelete->isSubmitted() && $formDelete->isValid()) {
            $em->remove($invoice);
            $em->flush();

            $this->addFlash('success', $translator->trans('notification.invoice_removed'));

            return $this->redirectToRoute('app_invoice_index');
        }

        return $this->render(
            'invoice/delete.html.twig',
            [
                'formDelete' => $formDelete->createView(),
                'invoice' => $invoice,
            ]
        );
    }

    /**
     * @param Request                $request
     * @param TranslatorInterface    $translator
     * @param EntityManagerInterface $em
     * @param Invoice                $invoice
     *
     * @return Response
     *
     * @Route("/{uuid}/edit",
     *     name="app_invoice_edit",
     *     methods={"GET", "POST"})
     */
    public function edit(
        Request $request,
        TranslatorInterface $translator,
        EntityManagerInterface $em,
        Invoice $invoice
    ): Response {
        if ($invoice->getLocked()) {
            return $this->redirectToRoute('app_invoice_show', ['uuid' => $invoice->getUuid()]);
        }

        $formEdit = $this->createForm(InvoiceType::class, $invoice);
        $formEdit->handleRequest($request);

        if ($formEdit->isSubmitted() && $formEdit->isValid()) {
            $em->flush();

            $this->addFlash('success', $translator->trans('notification.invoice_updated'));

            return $this->redirectToRoute('app_invoice_show', ['uuid' => $invoice->getUuid()]);
        }

        return $this->render(
            'invoice/edit.html.twig',
            [
                'formEdit' => $formEdit->createView(),
                'invoice' => $invoice,
            ]
        );
    }

    /**
     * @param Request           $request
     * @param SearchManager     $sm
     * @param InvoiceRepository $invoiceRepository
     *
     * @return Response
     *
     * @Route("/",
     *     name="app_invoice_index",
     *     methods={"GET", "POST"})
     */
    public function index(Request $request, SearchManager $sm, InvoiceRepository $invoiceRepository): Response
    {
        $search = $sm->find($this->getUser(), 'app_invoice_index');

        $formSearch = $this->createForm(InvoiceSearchType::class);

        if ($sm->handleRequest($search, $request, $formSearch)) {
            return $this->redirectToRoute($search->getRoute());
        }

        $invoices = $invoiceRepository->findBySearch($search);

        return $this->render(
            'invoice/index.html.twig',
            [
                'formSearch' => $formSearch->createView(),
                'invoices' => $invoices,
                'search' => $search,
            ]
        );
    }

    /**
     * @param EntityManagerInterface $em
     * @param TranslatorInterface    $translator
     * @param Invoice                $invoice
     * @param string                 $lock
     *
     * @return Response
     *
     * @Route("/{uuid}/lock/{lock}",
     *     name="app_invoice_lock",
     *     methods={"GET"},
     *     requirements={"lock"="0|1"})
     */
    public function lock(
        EntityManagerInterface $em,
        TranslatorInterface $translator,
        Invoice $invoice,
        string $lock
    ): Response {
        $invoice->setLocked((bool) $lock);

        $em->flush();

        if ($invoice->getLocked()) {
            $type = 'success';
            $message = 'notification.invoice_locked';
        } else {
            $type = 'danger';
            $message = 'notification.invoice_unlocked';
        }

        $this->addFlash($type, $translator->trans($message, ['%number%' => $invoice->getNumber()]));

        return $this->redirectToRoute(
            'app_invoice_show',
            [
                'uuid' => $invoice->getUuid(),
            ]
        );
    }

    /**
     * @param Request                $request
     * @param EntityManagerInterface $em
     * @param TranslatorInterface    $translator
     *
     * @return Response
     *
     * @Route("/new",
     *     name="app_invoice_new",
     *     methods={"GET", "POST"})
     */
    public function new(Request $request, EntityManagerInterface $em, TranslatorInterface $translator): Response
    {
        try {
            $invoice = new Invoice();
        } catch (\Exception $e) {
            $this->addFlash('danger', $translator->trans('notification.unable_to_create_invoice'));

            return $this->redirectToRoute('app_invoice_index');
        }
        $formEdit = $this->createForm(InvoiceType::class, $invoice);
        $formEdit->handleRequest($request);

        if ($formEdit->isSubmitted() && $formEdit->isValid()) {
            $em->persist($invoice);
            $em->flush();

            $this->addFlash('success', $translator->trans('notification.invoice_added'));

            return $this->redirectToRoute('app_invoice_show', ['uuid' => $invoice->getUuid()]);
        }

        return $this->render(
            'invoice/new.html.twig',
            [
                'formEdit' => $formEdit->createView(),
            ]
        );
    }

    /**
     * @param InvoicePDF          $generator
     * @param TranslatorInterface $translator
     * @param Invoice             $invoice
     *
     * @return BinaryFileResponse
     *
     * @Route("/{uuid}/print",
     *     name="app_invoice_print",
     *     methods={"GET"})
     */
    public function print(InvoicePDF $generator, TranslatorInterface $translator, Invoice $invoice): BinaryFileResponse
    {
        $filename = tempnam(sys_get_temp_dir(), 'invoice-');

        $generator->build($invoice);
        $generator->Output($filename, 'F');

        /** @var Client $client */
        $client = $invoice->getClient();

        $response = new BinaryFileResponse($filename);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            sprintf(
                '%s - %s %s - %s.pdf',
                $invoice->getIssueDate()->format('Y-m-d'),
                $translator->trans($invoice->getType()),
                $invoice->getNumberComplete(),
                strtoupper(iconv('UTF-8', 'ASCII//TRANSLIT', $client->getCode()))
            )
        );

        return $response;
    }

    /**
     * @param Invoice $invoice
     *
     * @return Response
     *
     * @Route("/{uuid}",
     *     name="app_invoice_show",
     *     methods={"GET"})
     */
    public function show(Invoice $invoice): Response
    {
        return $this->render(
            'invoice/show.html.twig',
            [
                'invoice' => $invoice,
            ]
        );
    }
}
