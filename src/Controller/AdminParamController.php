<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Param;
use App\Form\Type\ParamCollectionType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/admin/param")
 * @IsGranted("ROLE_ADMIN")
 */
class AdminParamController extends AbstractController
{
    /**
     * @Route("/",
     *     name="app_admin_param_index",
     *     methods={"GET", "POST"})
     */
    public function index(Request $request, EntityManagerInterface $em, TranslatorInterface $translator): Response
    {
        $formEdit = $this->createForm(ParamCollectionType::class);
        $formEdit->handleRequest($request);

        if ($formEdit->isSubmitted() && $formEdit->isValid()) {
            $repository = $em->getRepository(Param::class);
            foreach ($formEdit->getData() as $key => $value) {
                $param = $repository->findOneBy(['code' => $key]);
                if (!$param instanceof Param) {
                    continue;
                }
                $param->setValue($value);
            }

            $em->flush();

            $this->addFlash('success', $translator->trans('notification.param_updated'));

            return $this->redirectToRoute('app_admin_param_index');
        }

        return $this->render(
            'admin/param/index.html.twig',
            [
                'formEdit' => $formEdit->createView(),
            ]
        );
    }
}
