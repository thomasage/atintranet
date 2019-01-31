<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Payment;
use App\Form\Type\PaymentDeleteType;
use App\Form\Type\PaymentType;
use App\Repository\PaymentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class PaymentController
 * @package App\Controller
 * @Route("/payment")
 * @IsGranted("ROLE_ADMIN")
 */
class PaymentController extends AbstractController
{
    /**
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param TranslatorInterface $translator
     * @return Response
     *
     * @Route("/add",
     *     name="app_payment_add",
     *     methods={"GET", "POST"})
     */
    public function add(Request $request, EntityManagerInterface $em, TranslatorInterface $translator): Response
    {
        $payment = new Payment();
        $formEdit = $this->createForm(PaymentType::class, $payment);
        $formEdit->handleRequest($request);

        if ($formEdit->isSubmitted() && $formEdit->isValid()) {

            $em->persist($payment);
            $em->flush();

            $this->addFlash('success', $translator->trans('notification.payment_added'));

            return $this->redirectToRoute('app_payment_show', ['uuid' => $payment->getUuid()]);

        }

        return $this->render(
            'payment/add.html.twig',
            [
                'formEdit' => $formEdit->createView(),
            ]
        );
    }

    /**
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param TranslatorInterface $translator
     * @param Payment $payment
     * @return Response
     *
     * @Route("/{uuid}/delete",
     *     name="app_payment_delete",
     *     methods={"GET", "POST"},
     *     requirements={"uuid"})
     */
    public function delete(
        Request $request,
        EntityManagerInterface $em,
        TranslatorInterface $translator,
        Payment $payment
    ): Response {

        $formDelete = $this->createForm(PaymentDeleteType::class, $payment);
        $formDelete->handleRequest($request);

        if ($formDelete->isSubmitted() && $formDelete->isValid()) {

            $em->remove($payment);
            $em->flush();

            $this->addFlash('success', $translator->trans('notification.payment_removed'));

            return $this->redirectToRoute('app_payment_index');

        }

        return $this->render(
            'payment/delete.html.twig',
            [
                'formDelete' => $formDelete->createView(),
                'payment' => $payment,
            ]
        );
    }

    /**
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param TranslatorInterface $translator
     * @param Payment $payment
     * @return Response
     *
     * @Route("/{uuid}/edit",
     *     name="app_payment_edit",
     *     methods={"GET", "POST"},
     *     requirements={"uuid"})
     */
    public function edit(
        Request $request,
        EntityManagerInterface $em,
        TranslatorInterface $translator,
        Payment $payment
    ): Response {

        $formEdit = $this->createForm(PaymentType::class, $payment);
        $formEdit->handleRequest($request);

        if ($formEdit->isSubmitted() && $formEdit->isValid()) {

            $em->flush();

            $this->addFlash('success', $translator->trans('notification.payment_updated'));

            return $this->redirectToRoute('app_payment_show', ['uuid' => $payment->getUuid()]);

        }

        return $this->render(
            'payment/edit.html.twig',
            [
                'formEdit' => $formEdit->createView(),
                'payment' => $payment,
            ]
        );
    }

    /**
     * @param PaymentRepository $repository
     * @return Response
     *
     * @Route("/",
     *     name="app_payment_index",
     *     methods={"GET"})
     */
    public function index(PaymentRepository $repository): Response
    {
        $payments = $repository->findBy([], ['operationDate' => 'DESC', 'valueDate' => 'DESC']);

        return $this->render(
            'payment/index.html.twig',
            [
                'payments' => $payments,
            ]
        );
    }

    /**
     * @param Payment $payment
     * @return Response
     *
     * @Route("/{uuid}",
     *     name="app_payment_show",
     *     methods={"GET"},
     *     requirements={"uuid"})
     */
    public function show(Payment $payment): Response
    {
        return $this->render(
            'payment/show.html.twig',
            [
                'payment' => $payment,
            ]
        );
    }
}