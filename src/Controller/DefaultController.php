<?php
declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DefaultController
 * @package App\Controller
 */
class DefaultController extends AbstractController
{
    /**
     * @return Response
     *
     * @Route("/",
     *     name="app_homepage",
     *     methods={"GET"})
     */
    public function index(): Response
    {
        return $this->render('default/index.html.twig');
    }
}
