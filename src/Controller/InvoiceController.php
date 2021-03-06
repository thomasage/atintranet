<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Address;
use App\Entity\Client;
use App\Entity\Invoice;
use App\Entity\InvoiceDetail;
use App\Form\Type\InvoiceDeleteType;
use App\Form\Type\InvoiceSearchType;
use App\Form\Type\InvoiceType;
use App\Model\InvoicePDF;
use App\Repository\InvoiceRepository;
use App\Service\SearchManager;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
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
final class InvoiceController extends AbstractController
{
    /**
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
     * @Route("/new",
     *     name="app_invoice_new",
     *     methods={"GET", "POST"})
     */
    public function new(Request $request, EntityManagerInterface $em, TranslatorInterface $translator): Response
    {
        try {
            $invoice = new Invoice();
        } catch (Exception $e) {
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
                strtoupper(iconv('UTF-8', 'ASCII//TRANSLIT', (string) $client->getCode()))
            )
        );

        return $response;
    }

    /**
     * @Route("/{uuid}", name="app_invoice_show", methods={"GET"})
     */
    public function show(
        Request $request,
        EntityManagerInterface $em,
        TranslatorInterface $translator,
        Invoice $invoice
    ): Response {
        if ($request->query->has('copy')) {
            try {
                /** @var Address $address */
                $address = $invoice->getAddress();

                $copyAddress = new Address();
                $copyAddress
                    ->setAddress($address->getAddress())
                    ->setCity($address->getCity())
                    ->setCountry($address->getCountry())
                    ->setName($address->getName())
                    ->setPostcode($address->getPostcode());
                $em->persist($copyAddress);

                $copyInvoice = new Invoice();
                $copyInvoice
                    ->setAddress($copyAddress)
                    ->setAmountExcludingTax($invoice->getAmountExcludingTax())
                    ->setAmountIncludingTax($invoice->getAmountIncludingTax())
                    ->setAmountPaid($invoice->getAmountPaid())
                    ->setClient($invoice->getClient())
                    ->setCredit($invoice->getCredit())
                    ->setComment($invoice->getComment())
                    ->setCommentInternal($invoice->getCommentInternal())
                    ->setDueDate(new DateTime('+30 days'))
                    ->setIssueDate(new DateTime())
                    ->setOrderNumber($invoice->getOrderNumber())
                    ->setTaxRate($invoice->getTaxRate())
                    ->setType($invoice->getType())
                    ->updateAmounts();
                $em->persist($copyInvoice);

                foreach ($invoice->getDetails() as $detail) {
                    $copyDetail = new InvoiceDetail();
                    $copyDetail
                        ->setAmountTotal($detail->getAmountTotal())
                        ->setAmountUnit($detail->getAmountUnit())
                        ->setDesignation($detail->getDesignation())
                        ->setInvoice($copyInvoice)
                        ->setQuantity($detail->getQuantity());
                    $em->persist($copyDetail);
                }

                $em->flush();

                $this->addFlash('success', $translator->trans('notification.invoice_copied'));

                return $this->redirectToRoute('app_invoice_edit', ['uuid' => $copyInvoice->getUuid()]);
            } catch (Exception $e) {
                return $this->redirectToRoute('app_invoice_show', ['uuid' => $invoice->getUuid()]);
            }
        }

        return $this->render(
            'invoice/show.html.twig',
            [
                'invoice' => $invoice,
            ]
        );
    }
}
