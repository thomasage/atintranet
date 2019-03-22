<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Payment;
use App\Form\Type\PaymentDeleteType;
use App\Form\Type\PaymentSearchType;
use App\Form\Type\PaymentType;
use App\Repository\PaymentRepository;
use App\Service\PaymentManager;
use App\Service\SearchManager;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
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
     * @param Payment $payment
     * @return Response
     *
     * @Route("/{uuid}/delete",
     *     name="app_payment_delete",
     *     methods={"GET", "POST"})
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
     * @param PaymentManager $pm
     * @return Response
     *
     * @Route("/download",
     *     name="app_payment_download",
     *     methods={"GET"})
     */
    public function download(PaymentManager $pm): Response
    {
        $response = $pm->download();

        if (!$response instanceof StreamedResponse) {
            return $this->redirectToRoute('app_payment_index');
        }

        return $response;
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
     *     methods={"GET", "POST"})
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
     * @param EntityManagerInterface $em
     * @param TranslatorInterface $translator
     * @param Payment $payment
     * @param bool $lock
     * @return Response
     *
     * @Route("/{uuid}/lock/{lock}",
     *     name="app_payment_lock",
     *     methods={"GET"},
     *     requirements={"lock", "uuid"})
     */
    public function lock(
        EntityManagerInterface $em,
        TranslatorInterface $translator,
        Payment $payment,
        bool $lock
    ): Response {

        $payment->setLocked($lock);

        $em->flush();

        if ($payment->getLocked()) {
            $message = 'notification.payment_locked';
        } else {
            $message = 'notification.payment_unlocked';
        }

        $this->addFlash('success', $translator->trans($message));

        return $this->redirectToRoute('app_payment_show', ['uuid' => $payment->getUuid()]);
    }

    /**
     * @param Request $request
     * @param SearchManager $sm
     * @param PaymentRepository $repository
     * @return Response
     *
     * @Route("/",
     *     name="app_payment_index",
     *     methods={"GET", "POST"})
     */
    public function index(Request $request, SearchManager $sm, PaymentRepository $repository): Response
    {
        $search = $sm->find($this->getUser(), 'app_payment_index');

        $formSearch = $this->createForm(PaymentSearchType::class);

        if ($sm->handleRequest($search, $request, $formSearch)) {
            return $this->redirectToRoute($search->getRoute());
        }

        $payments = $repository->findBySearch($search);

        return $this->render(
            'payment/index.html.twig',
            [
                'formSearch' => $formSearch->createView(),
                'payments' => $payments,
                'search' => $search,
            ]
        );
    }

    /**
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param TranslatorInterface $translator
     * @return Response
     *
     * @Route("/new",
     *     name="app_payment_new",
     *     methods={"GET", "POST"})
     */
    public function new(Request $request, EntityManagerInterface $em, TranslatorInterface $translator): Response
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
            'payment/new.html.twig',
            [
                'formEdit' => $formEdit->createView(),
            ]
        );
    }

    /**
     * @param Payment $payment
     * @return Response
     *
     * @Route("/{uuid}",
     *     name="app_payment_show",
     *     methods={"GET"})
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
