<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Invoice;
use App\Form\Type\InvoiceType;
use App\Model\InvoicePDF;
use App\Repository\InvoiceRepository;
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
     * @param Request $request
     * @param TranslatorInterface $translator
     * @param EntityManagerInterface $em
     * @param Invoice $invoice
     * @return Response
     *
     * @Route("/{uuid}/edit",
     *     name="app_invoice_edit",
     *     methods={"GET", "POST"},
     *     requirements={"uuid"})
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

            return $this->redirectToRoute('app_invoice_edit', ['uuid' => $invoice->getUuid()]);

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
     * @param InvoiceRepository $invoiceRepository
     * @return Response
     *
     * @Route("/",
     *     name="app_invoice_index",
     *     methods={"GET"})
     */
    public function index(InvoiceRepository $invoiceRepository): Response
    {
        $invoices = $invoiceRepository->findBySearch();

        return $this->render(
            'invoice/index.html.twig',
            [
                'invoices' => $invoices,
            ]
        );
    }

    /**
     * @param EntityManagerInterface $em
     * @param TranslatorInterface $translator
     * @param Invoice $invoice
     * @return Response
     *
     * @Route("/{uuid}/lock/{lock}",
     *     name="app_invoice_lock",
     *     methods={"GET"},
     *     requirements={"uuid", "lock"="0|1"})
     */
    public function lock(
        EntityManagerInterface $em,
        TranslatorInterface $translator,
        Invoice $invoice,
        string $lock
    ): Response {

        $invoice->setLocked((bool)$lock);

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
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return Response
     *
     * @Route("/new",
     *     name="app_invoice_new",
     *     methods={"GET", "POST"})
     */
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $formEdit = $this->createForm(InvoiceType::class);
        $formEdit->handleRequest($request);

        if ($formEdit->isSubmitted() && $formEdit->isValid()) {

            dd($formEdit->getData());

        }

        return $this->render(
            'invoice/new.html.twig',
            [
                'formEdit' => $formEdit->createView(),
            ]
        );
    }

    /**
     * @param InvoicePDF $generator
     * @param TranslatorInterface $translator
     * @param Invoice $invoice
     * @return BinaryFileResponse
     *
     * @Route("/{uuid}/print",
     *     name="app_invoice_print",
     *     methods={"GET"},
     *     requirements={"uuid"})
     */
    public function print(InvoicePDF $generator, TranslatorInterface $translator, Invoice $invoice): BinaryFileResponse
    {
        $filename = tempnam(sys_get_temp_dir(), 'invoice-');

        $generator->build($invoice);
        $generator->Output($filename, 'F');

        $response = new BinaryFileResponse($filename);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            sprintf(
                '%s - %s %s - %s.pdf',
                $invoice->getIssueDate()->format('Y-m-d'),
                $translator->trans($invoice->getType()),
                $invoice->getNumber(),
                strtoupper(iconv('UTF-8', 'ASCII//TRANSLIT', $invoice->getClient()->getName()))
            )
        );

        return $response;
    }

    /**
     * @param Invoice $invoice
     * @return Response
     *
     * @Route("/{uuid}",
     *     name="app_invoice_show",
     *     methods={"GET"},
     *     requirements={"uuid"})
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
