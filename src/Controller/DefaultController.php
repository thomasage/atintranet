<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\TaskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DefaultController.
 */
class DefaultController extends AbstractController
{
    /**
     * @param TaskRepository $taskRepository
     *
     * @return Response
     *
     * @Route("/",
     *     name="app_homepage",
     *     methods={"GET"})
     */
    public function index(TaskRepository $taskRepository): Response
    {
        $timesThisMonth = $taskRepository->findTimesThisMonth($this->getUser());
        $timesThisWeek = $taskRepository->findTimesThisWeek($this->getUser());

        return $this->render(
            'default/index.html.twig',
            [
                'timesThisMonth' => $timesThisMonth,
                'timesThisWeek' => $timesThisWeek,
            ]
        );
    }
}
