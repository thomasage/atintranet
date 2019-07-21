<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\TaskRepository;
use App\Service\TrackerManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/tracker")
 */
class TrackerController extends AbstractController
{
    /**
     * @param Request $request
     * @param TaskRepository $repository
     *
     * @param TrackerManager $trackerManager
     * @return Response
     *
     * @Route("/",
     *     name="app_tracker_index",
     *     methods={"GET"})
     */
    public function index(Request $request, TaskRepository $repository, TrackerManager $trackerManager): Response
    {
        if ($request->query->has('refresh')) {

            $trackerManager->importFromToggl();

            return $this->redirectToRoute('app_tracker_index');

        }

        $tasks = $repository->findBySearch();

        return $this->render(
            'tracker/index.html.twig',
            [
                'tasks' => $tasks,
            ]
        );
    }
}
