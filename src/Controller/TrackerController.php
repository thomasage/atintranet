<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\TaskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class TrackerController.
 *
 * @Route("/tracker")
 */
class TrackerController extends AbstractController
{
    /**
     * @param TaskRepository $repository
     *
     * @return Response
     *
     * @Route("/",
     *     name="app_tracker_index",
     *     methods={"GET"})
     */
    public function index(TaskRepository $repository): Response
    {
        $tasks = $repository->findBy([], ['start' => 'DESC']);

        return $this->render(
            'tracker/index.html.twig',
            [
                'tasks' => $tasks,
            ]
        );
    }
}
