<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Form\Type\UserAddType;
use App\Form\Type\UserDeleteType;
use App\Form\Type\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/admin/user")
 * @IsGranted("ROLE_ADMIN")
 */
class AdminUserController extends AbstractController
{
    /**
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param TranslatorInterface $translator
     * @param User $user
     * @return Response
     *
     * @Route("/{uuid}/delete",
     *     name="app_admin_user_delete",
     *     methods={"GET", "POST"},
     *     requirements={"uuid"})
     */
    public function delete(
        Request $request,
        EntityManagerInterface $em,
        TranslatorInterface $translator,
        User $user
    ): Response {

        // Can't delete current user
        if ($this->getUser()->getId() === $user->getId()) {
            return $this->redirectToRoute('app_admin_user_show', ['uuid' => $user->getUuid()]);
        }

        $form = $this->createForm(UserDeleteType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->remove($user);
            $em->flush();

            $this->addFlash('success', $translator->trans('notification.user_removed'));

            return $this->redirectToRoute('app_admin_user_index');

        }

        return $this->render(
            'admin/user/delete.html.twig',
            [
                'form' => $form->createView(),
                'user' => $user,
            ]
        );
    }

    /**
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param TranslatorInterface $translator
     * @param User $user
     * @return Response
     *
     * @Route("/{uuid}/edit",
     *     name="app_admin_user_edit",
     *     methods={"GET", "POST"},
     *     requirements={"uuid"})
     */
    public function edit(
        Request $request,
        EntityManagerInterface $em,
        TranslatorInterface $translator,
        User $user
    ): Response {

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->flush();

            $this->addFlash('success', $translator->trans('notification.user_updated'));

            return $this->redirectToRoute('app_admin_user_show', ['uuid' => $user->getUuid()]);

        }

        return $this->render(
            'admin/user/edit.html.twig',
            [
                'form' => $form->createView(),
                'user' => $user,
            ]
        );
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @Route("/client-select",
     *     name="app_admin_user_client_select",
     *     methods={"GET"})
     */
    public function getClientSelect(Request $request): Response
    {
        $user = new User();
        $user->setRoles($request->query->get('roles') ?? []);
        $form = $this->createForm(UserType::class, $user);
        if (!$form->has('client')) {
            return new Response(null, Response::HTTP_NO_CONTENT);
        }

        return $this->render(
            'admin/user/_client_select.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @param UserRepository $repository
     * @return Response
     *
     * @Route("/",
     *     name="app_admin_user_index",
     *     methods={"GET"})
     */
    public function index(UserRepository $repository): Response
    {
        $users = $repository->findBySearch();

        return $this->render(
            'admin/user/index.html.twig',
            [
                'users' => $users,
            ]
        );
    }

    /**
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param TranslatorInterface $translator
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return Response
     *
     * @Route("/new",
     *     name="app_admin_user_new",
     *     methods={"GET", "POST"})
     */
    public function new(
        Request $request,
        EntityManagerInterface $em,
        TranslatorInterface $translator,
        UserPasswordEncoderInterface $passwordEncoder
    ): Response {

        $user = new User();
        $form = $this->createForm(UserAddType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user->setPassword($passwordEncoder->encodePassword($user, $form->get('password')->getData()));
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', $translator->trans('notification.user_added'));

            return $this->redirectToRoute('app_admin_user_show', ['uuid' => $user->getUuid()]);

        }

        return $this->render(
            'admin/user/new.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @param User $user
     * @return Response
     *
     * @Route("/{uuid}",
     *     name="app_admin_user_show",
     *     methods={"GET"},
     *     requirements={"uuid"})
     */
    public function show(User $user): Response
    {
        return $this->render(
            'admin/user/show.html.twig',
            [
                'user' => $user,
            ]
        );
    }
}
