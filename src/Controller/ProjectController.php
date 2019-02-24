<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Project;
use App\Form\Type\ProjectType;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/project")
 * @IsGranted("ROLE_ADMIN")
 */
class ProjectController extends AbstractController
{
    /**
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param TranslatorInterface $translator
     * @param Project $project
     * @return Response
     *
     * @Route("/{uuid}/edit",
     *     name="app_project_edit",
     *     methods={"GET", "POST"})
     */
    public function edit(
        Request $request,
        EntityManagerInterface $em,
        TranslatorInterface $translator,
        Project $project
    ): Response {

        $formEdit = $this->createForm(ProjectType::class, $project);
        $formEdit->handleRequest($request);

        if ($formEdit->isSubmitted() && $formEdit->isValid()) {

            $em->flush();

            $this->addFlash('success', $translator->trans('notification.project_updated'));

            return $this->redirectToRoute('app_project_show', ['uuid' => $project->getUuid()]);

        }

        return $this->render(
            'project/edit.html.twig',
            [
                'formEdit' => $formEdit->createView(),
                'project' => $project,
            ]
        );
    }

    /**
     * @param ProjectRepository $repository
     * @return Response
     *
     * @Route("/",
     *     name="app_project_index",
     *     methods={"GET"})
     */
    public function index(ProjectRepository $repository): Response
    {
        $projects = $repository->findBy([], ['name' => 'ASC']);

        return $this->render(
            'project/index.html.twig',
            [
                'projects' => $projects,
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
     *     name="app_project_new",
     *     methods={"GET", "POST"})
     */
    public function new(Request $request, EntityManagerInterface $em, TranslatorInterface $translator): Response
    {
        $project = new Project();

        $formEdit = $this->createForm(ProjectType::class, $project);
        $formEdit->handleRequest($request);

        if ($formEdit->isSubmitted() && $formEdit->isValid()) {

            $em->persist($project);
            $em->flush();

            $this->addFlash('success', $translator->trans('notification.project_added'));

            return $this->redirectToRoute('app_project_show', ['uuid' => $project->getUuid()]);

        }

        return $this->render(
            'project/new.html.twig',
            [
                'formEdit' => $formEdit->createView(),
            ]
        );
    }

    /**
     * @param Project $project
     * @return Response
     *
     * @Route("/{uuid}",
     *     name="app_project_show",
     *     methods={"GET"})
     */
    public function show(Project $project): Response
    {
        return $this->render(
            'project/show.html.twig',
            [
                'project' => $project,
            ]
        );
    }
}
