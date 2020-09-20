<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Client;
use App\Form\Type\StatTurnoverSearchType;
use App\Repository\ClientRepository;
use App\Repository\InvoiceRepository;
use App\Repository\TaskRepository;
use App\Service\TrackerManager;
use DateTime;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/stat")
 */
class StatController extends AbstractController
{
    /**
     * @Route("/report/time",
     *     name="app_stat_report_time",
     *     methods={"GET"})
     */
    public function reportTime(
        Request $request,
        TaskRepository $taskRepository,
        ClientRepository $clientRepository,
        TrackerManager $tm,
        TranslatorInterface $translator
    ): Response {
        if ($request->query->has('client') && $request->query->has('month')) {
            $client = $clientRepository->findOneBy(['uuid' => $request->query->get('client')]);
            if (!$client instanceof Client) {
                return $this->redirectToRoute('app_stat_report_time');
            }

            $month = DateTime::createFromFormat('Y-m-d H:i:s', $request->query->get('month').'-01 00:00:00');

            $response = $tm->export($month, $client);

            if (null === $response) {
                $this->addFlash('danger', $translator->trans('notification.export_failed'));

                return $this->redirectToRoute('app_stat_report_time');
            }

            return $response;
        }

        $reports = $taskRepository->findReports();

        return $this->render(
            'stat/report_time.html.twig',
            [
                'reports' => $reports,
            ]
        );
    }

    /**
     * @Route("/time/chart/{period}",
     *     name="app_stat_time_chart",
     *     methods={"GET"},
     *     requirements={"period"="d|w|m|y"},
     *     defaults={"period"="d"})
     */
    public function timeChart(TaskRepository $repository, string $period): Response
    {
        $data = $repository->findStatByPeriodGroupByProject($this->getUser(), $period);

        return $this->render(
            'stat/time_chart.html.twig',
            [
                'data' => $data,
                'period' => $period,
            ]
        );
    }

    /**
     * @Route("/turnover/client",
     *     name="app_stat_turnover_client",
     *     methods={"GET"})
     *
     * @IsGranted("ROLE_ADMIN")
     */
    public function turnoverClient(Request $request, InvoiceRepository $repository): Response
    {
        $formSearch = $this->createForm(StatTurnoverSearchType::class, null, ['method' => 'GET']);
        $formSearch->handleRequest($request);

        $data = $repository->findStatByClient(
            $formSearch->get('start')->getData(),
            $formSearch->get('stop')->getData()
        );

        return $this->render(
            'stat/turnover_client.html.twig',
            [
                'data' => $data,
                'formSearch' => $formSearch->createView(),
            ]
        );
    }

    /**
     * @Route("/turnover/period/{period}",
     *     name="app_stat_turnover_period",
     *     methods={"GET"},
     *     requirements={"period"="m|y"},
     *     defaults={"period"="m"})
     *
     * @IsGranted("ROLE_ADMIN")
     */
    public function turnoverPeriod(InvoiceRepository $repository, string $period): Response
    {
        $data = $repository->findStatByPeriod($period);

        return $this->render(
            'stat/turnover_period.html.twig',
            [
                'data' => $data,
                'period' => $period,
            ]
        );
    }
}
