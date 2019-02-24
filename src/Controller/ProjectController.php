<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Project;
use App\Entity\ProjectRate;
use App\Form\Type\ProjectRateDeleteType;
use App\Form\Type\ProjectRateType;
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
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param TranslatorInterface $translator
     * @param ProjectRate $rate
     * @return Response
     *
     * @Route("/rate/{uuid}/delete",
     *     name="app_project_rate_delete",
     *     methods={"GET", "POST"})
     */
    public function rateDelete(
        Request $request,
        EntityManagerInterface $em,
        TranslatorInterface $translator,
        ProjectRate $rate
    ): Response {

        /** @var Project $project */
        $project = $rate->getProject();

        $formDelete = $this->createForm(ProjectRateDeleteType::class, $rate);
        $formDelete->handleRequest($request);

        if ($formDelete->isSubmitted() && $formDelete->isValid()) {

            $em->remove($rate);
            $em->flush();

            $this->addFlash('success', $translator->trans('notification.project_rate_removed'));

            return $this->redirectToRoute('app_project_show', ['uuid' => $project->getUuid()]);

        }

        return $this->render(
            'project/rate_delete.html.twig',
            [
                'formDelete' => $formDelete->createView(),
                'project' => $project,
            ]
        );
    }

    /**
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param TranslatorInterface $translator
     * @param ProjectRate $rate
     * @return Response
     *
     * @Route("/rate/{uuid}/edit",
     *     name="app_project_rate_edit",
     *     methods={"GET", "POST"})
     */
    public function rateEdit(
        Request $request,
        EntityManagerInterface $em,
        TranslatorInterface $translator,
        ProjectRate $rate
    ): Response {

        /** @var Project $project */
        $project = $rate->getProject();

        $formEdit = $this->createForm(ProjectRateType::class, $rate);
        $formEdit->handleRequest($request);

        if ($formEdit->isSubmitted() && $formEdit->isValid()) {

            $em->flush();

            $this->addFlash('success', $translator->trans('notification.project_rate_updated'));

            return $this->redirectToRoute('app_project_show', ['uuid' => $project->getUuid()]);

        }

        return $this->render(
            'project/rate_edit.html.twig',
            [
                'formEdit' => $formEdit->createView(),
                'project' => $project,
            ]
        );
    }

    /**
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param TranslatorInterface $translator
     * @param Project $project
     * @return Response
     *
     * @Route("/rate/new/{uuid}",
     *     name="app_project_rate_new",
     *     methods={"GET", "POST"})
     */
    public function rateNew(
        Request $request,
        EntityManagerInterface $em,
        TranslatorInterface $translator,
        Project $project
    ): Response {

        $rate = new ProjectRate();
        $rate->setProject($project);

        $formEdit = $this->createForm(ProjectRateType::class, $rate);
        $formEdit->handleRequest($request);

        if ($formEdit->isSubmitted() && $formEdit->isValid()) {

            $em->persist($rate);
            $em->flush();

            $this->addFlash('success', $translator->trans('notification.project_rate_added'));

            return $this->redirectToRoute('app_project_show', ['uuid' => $project->getUuid()]);

        }

        return $this->render(
            'project/rate_new.html.twig',
            [
                'formEdit' => $formEdit->createView(),
                'project' => $project,
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
