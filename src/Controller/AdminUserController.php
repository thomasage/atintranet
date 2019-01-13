<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/user")
 * @IsGranted("ROLE_ADMIN")
 */
class AdminUserController extends AbstractController
{
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
     * @param User $user
     * @return Response
     *
     * @Route("/{user}",
     *     name="app_admin_user_show",
     *     methods={"GET"},
     *     requirements={"user"})
     *
     * @ParamConverter("user")
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
