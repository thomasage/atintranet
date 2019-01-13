<?php
declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Class SecurityController
 * @package App\Controller
 */
class SecurityController extends AbstractController
{
    /**
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     *
     * @Route("/login",
     *     name="app_security_login",
     *     methods={"GET", "POST"})
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        return $this->render(
            'security/login.html.twig',
            [
                'error' => $authenticationUtils->getLastAuthenticationError(),
                'last_username' => $authenticationUtils->getLastUsername(),
            ]
        );
    }
}
