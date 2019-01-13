<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Client;
use App\Form\Type\ClientType;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/client")
 * @IsGranted("ROLE_ADMIN")
 */
class ClientController extends AbstractController
{
    /**
     * @param Request $request
     * @param Client $client
     * @return Response
     *
     * @Route("/{uuid}",
     *     name="app_client_delete",
     *     methods={"DELETE"},
     *     requirements={"uuid"})
     */
    public function delete(Request $request, Client $client): Response
    {
        if ($this->isCsrfTokenValid('delete'.$client->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($client);
            $em->flush();
        }

        return $this->redirectToRoute('app_client_index');
    }

    /**
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param TranslatorInterface $translator
     * @param Client $client
     * @return Response
     *
     * @Route("/{uuid}/edit",
     *     name="app_client_edit",
     *     methods={"GET", "POST"},
     *     requirements={"uuid"})
     */
    public function edit(
        Request $request,
        EntityManagerInterface $em,
        TranslatorInterface $translator,
        Client $client
    ): Response {

        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->flush();

            $this->addFlash('success', $translator->trans('notification.client_updated'));

            return $this->redirectToRoute('app_client_show', ['client' => $client->getUuid()]);

        }

        return $this->render(
            'client/edit.html.twig',
            [
                'client' => $client,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @param ClientRepository $clientRepository
     * @return Response
     *
     * @Route("/",
     *     name="app_client_index",
     *     methods={"GET"})
     */
    public function index(ClientRepository $clientRepository): Response
    {
        $clients = $clientRepository->findBy([], ['name' => 'ASC']);

        return $this->render(
            'client/index.html.twig',
            [
                'clients' => $clients,
            ]
        );
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @Route("/new",
     *     name="app_client_new", methods={"GET", "POST"})
     */
    public function new(Request $request): Response
    {
        $client = new Client();
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($client);
            $em->flush();

            return $this->redirectToRoute('app_client_index');
        }

        return $this->render(
            'client/new.html.twig',
            [
                'client' => $client,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @param Client $client
     * @return Response
     *
     * @Route("/{uuid}",
     *     name="app_client_show",
     *     methods={"GET"},
     *     requirements={"uuid"})
     */
    public function show(Client $client): Response
    {
        return $this->render(
            'client/show.html.twig',
            [
                'client' => $client,
            ]
        );
    }
}
