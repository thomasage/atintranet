<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Address;
use App\Entity\Client;
use App\Entity\Offer;
use App\Entity\OfferDetail;
use App\Form\Type\OfferDeleteType;
use App\Form\Type\OfferSearchType;
use App\Form\Type\OfferType;
use App\Model\OfferPDF;
use App\Repository\OfferRepository;
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
 * @Route("/offer")
 * @IsGranted("ROLE_ADMIN")
 */
class OfferController extends AbstractController
{
    /**
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param TranslatorInterface $translator
     * @param Offer $offer
     *
     * @return Response
     *
     * @Route("/{uuid}/delete",
     *     name="app_offer_delete",
     *     methods={"GET", "POST"})
     */
    public function delete(
        Request $request,
        EntityManagerInterface $em,
        TranslatorInterface $translator,
        Offer $offer
    ): Response {

        $formDelete = $this->createForm(OfferDeleteType::class, $offer);
        $formDelete->handleRequest($request);

        if ($formDelete->isSubmitted() && $formDelete->isValid()) {
            $em->remove($offer);
            $em->flush();

            $this->addFlash('success', $translator->trans('notification.offer_removed'));

            return $this->redirectToRoute('app_offer_index');
        }

        return $this->render(
            'offer/delete.html.twig',
            [
                'formDelete' => $formDelete->createView(),
                'offer' => $offer,
            ]
        );
    }

    /**
     * @param Request $request
     * @param TranslatorInterface $translator
     * @param EntityManagerInterface $em
     * @param Offer $offer
     *
     * @return Response
     *
     * @Route("/{uuid}/edit",
     *     name="app_offer_edit",
     *     methods={"GET", "POST"})
     */
    public function edit(
        Request $request,
        TranslatorInterface $translator,
        EntityManagerInterface $em,
        Offer $offer
    ): Response {

        $formEdit = $this->createForm(OfferType::class, $offer);
        $formEdit->handleRequest($request);

        if ($formEdit->isSubmitted() && $formEdit->isValid()) {
            $em->flush();

            $this->addFlash('success', $translator->trans('notification.offer_updated'));

            return $this->redirectToRoute('app_offer_show', ['uuid' => $offer->getUuid()]);
        }

        return $this->render(
            'offer/edit.html.twig',
            [
                'formEdit' => $formEdit->createView(),
                'offer' => $offer,
            ]
        );
    }

    /**
     * @param Request $request
     * @param SearchManager $sm
     * @param OfferRepository $offerRepository
     *
     * @return Response
     *
     * @Route("/",
     *     name="app_offer_index",
     *     methods={"GET", "POST"})
     */
    public function index(Request $request, SearchManager $sm, OfferRepository $offerRepository): Response
    {
        $search = $sm->find($this->getUser(), 'app_offer_index');

        $formSearch = $this->createForm(OfferSearchType::class);

        if ($sm->handleRequest($search, $request, $formSearch)) {
            return $this->redirectToRoute($search->getRoute());
        }

        $offers = $offerRepository->findBySearch($search);

        return $this->render(
            'offer/index.html.twig',
            [
                'formSearch' => $formSearch->createView(),
                'offers' => $offers,
                'search' => $search,
            ]
        );
    }

    /**
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param TranslatorInterface $translator
     *
     * @return Response
     *
     * @Route("/new",
     *     name="app_offer_new",
     *     methods={"GET", "POST"})
     */
    public function new(Request $request, EntityManagerInterface $em, TranslatorInterface $translator): Response
    {
        try {
            $offer = new Offer();
        } catch (Exception $e) {
            $this->addFlash('danger', $translator->trans('notification.unable_to_create_offer'));

            return $this->redirectToRoute('app_offer_index');
        }
        $formEdit = $this->createForm(OfferType::class, $offer);
        $formEdit->handleRequest($request);

        if ($formEdit->isSubmitted() && $formEdit->isValid()) {
            $em->persist($offer);
            $em->flush();

            $this->addFlash('success', $translator->trans('notification.offer_added'));

            return $this->redirectToRoute('app_offer_show', ['uuid' => $offer->getUuid()]);
        }

        return $this->render(
            'offer/new.html.twig',
            [
                'formEdit' => $formEdit->createView(),
            ]
        );
    }

    /**
     * @param OfferPDF $generator
     * @param TranslatorInterface $translator
     * @param Offer $offer
     *
     * @return BinaryFileResponse
     *
     * @Route("/{uuid}/print",
     *     name="app_offer_print",
     *     methods={"GET"})
     */
    public function print(OfferPDF $generator, TranslatorInterface $translator, Offer $offer): BinaryFileResponse
    {
        $filename = tempnam(sys_get_temp_dir(), 'offer-');

        $generator->build($offer);
        $generator->Output($filename, 'F');

        /** @var Client $client */
        $client = $offer->getClient();

        $response = new BinaryFileResponse($filename);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            sprintf(
                '%s - %s %s - %s.pdf',
                $offer->getIssueDate()->format('Y-m-d'),
                $translator->trans('offer'),
                $offer->getNumberComplete(),
                strtoupper(iconv('UTF-8', 'ASCII//TRANSLIT', $client->getCode()))
            )
        );

        return $response;
    }

    /**
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param TranslatorInterface $translator
     * @param Offer $offer
     *
     * @return Response
     *
     * @Route("/{uuid}", name="app_offer_show", methods={"GET"})
     */
    public function show(
        Request $request,
        EntityManagerInterface $em,
        TranslatorInterface $translator,
        Offer $offer
    ): Response {
        if ($request->query->has('copy')) {

            try {

                /** @var Address $address */
                $address = $offer->getAddress();

                $copyAddress = new Address();
                $copyAddress
                    ->setAddress($address->getAddress())
                    ->setCity($address->getCity())
                    ->setCountry($address->getCountry())
                    ->setName($address->getName())
                    ->setPostcode($address->getPostcode());
                $em->persist($copyAddress);

                $copyOffer = new Offer();
                $copyOffer
                    ->setAddress($copyAddress)
                    ->setAmountExcludingTax($offer->getAmountExcludingTax())
                    ->setAmountIncludingTax($offer->getAmountIncludingTax())
                    ->setAmountPaid($offer->getAmountPaid())
                    ->setClient($offer->getClient())
                    ->setCredit($offer->getCredit())
                    ->setComment($offer->getComment())
                    ->setCommentInternal($offer->getCommentInternal())
                    ->setDueDate(new DateTime('+30 days'))
                    ->setIssueDate(new DateTime())
                    ->setOrderNumber($offer->getOrderNumber())
                    ->setTaxRate($offer->getTaxRate())
                    ->setType($offer->getType())
                    ->updateAmounts();
                $em->persist($copyOffer);

                foreach ($offer->getDetails() as $detail) {

                    $copyDetail = new OfferDetail();
                    $copyDetail
                        ->setAmountTotal($detail->getAmountTotal())
                        ->setAmountUnit($detail->getAmountUnit())
                        ->setDesignation($detail->getDesignation())
                        ->setOffer($copyOffer)
                        ->setQuantity($detail->getQuantity());
                    $em->persist($copyDetail);

                }

                $em->flush();

                $this->addFlash('success', $translator->trans('notification.offer_copied'));

                return $this->redirectToRoute('app_offer_edit', ['uuid' => $copyOffer->getUuid()]);

            } catch (Exception $e) {

                return $this->redirectToRoute('app_offer_show', ['uuid' => $offer->getUuid()]);

            }

        }

        return $this->render(
            'offer/show.html.twig',
            [
                'offer' => $offer,
            ]
        );
    }
}
