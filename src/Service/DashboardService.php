<?php

namespace App\Service;

use App\Repository\ExperienceRepository;
use App\Repository\InvoiceRepository;
use DateTime;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Ds\Map;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class DashboardService
{
    public function __construct(
        private readonly ChartBuilderInterface $chartBuilder,
        private readonly InvoiceRepository     $invoiceRepository,
        private readonly ExperienceRepository  $experienceRepository,
        private readonly DeclarationService    $declarationService,
        private readonly TranslatorInterface   $translator
    ) {
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function getDashboard(int $year): array
    {
        $viewData = [];

        $viewData['currentYear'] = intval((new DateTime())->format('Y'));
        $viewData['currentQuarter'] = ceil((new DateTime())->format('n') / 3);
        $viewData['currentMonth'] = intval((new DateTime())->format('m'));
        $viewData['activeYear'] = $year ?: $viewData['currentYear'];
        $viewData['years'] = array_filter($this->invoiceRepository->findYears());

        if (!in_array($viewData['currentYear'], $viewData['years'])) {
            $viewData['years'][] = $viewData['currentYear'];
        }

        $viewData['dayCount'] = $this->invoiceRepository->getDaysCountByYear($viewData['activeYear']);

        $viewData['remainingDaysBeforeLimit'] = $this->invoiceRepository->remainingDaysBeforeLimit();

        $revenuesByYears = $this->invoiceRepository->getSalesRevenuesGroupBy('year');
        $viewData['revenuesByYears'] = array_combine(
            array_map(fn($item) => $item['year'], $revenuesByYears),
            array_map(fn($item) => intval($item['total']), $revenuesByYears)
        );

        $revenuesByQuarters = $this->invoiceRepository->getSalesRevenuesGroupBy(
            'quarter', $viewData['activeYear'], null, true
        );
        $viewData['revenuesByQuarters'] = array_combine(
            array_map(fn($item) => 'T' . $item['quarter'], $revenuesByQuarters),
            array_map(fn($item) => intval($item['total']), $revenuesByQuarters)
        );

        $daysByMonthMap = new Map();
        $daysByMonth = $this->invoiceRepository->getDaysCountByMonth($viewData['activeYear']);
        $daysByMonthAssociative = [];
        $viewData['colorsByMonths'] = [];
        foreach ($daysByMonth as $item) {
            $daysByMonthAssociative[intval($item['month'])] = $item['total'];
        }
        for ($month = 1; $month <= 12; $month++) {
            $monthName = $this->translator->trans(date('F', mktime(0, 0, 0, $month, 10)));
            $daysByMonthMap->put($monthName, isset($daysByMonthAssociative[$month]) ?? 0);
            $viewData['colorsByMonths'][] = $month === $viewData['currentMonth'] ? 'rgba(96, 165, 250, 0.6)' : 'rgba(96, 165, 250, 0.2)';
        }
        $viewData['daysByMonth'] = $daysByMonthMap;

        $viewData['colorsByYears'] = [];
        foreach ($viewData['years'] as $year) {
            $viewData['colorsByYears'][] = $year == $viewData['activeYear'] ? 'rgba(96, 165, 250, 0.6)' : 'rgba(96, 165, 250, 0.2)';
        }

        $viewData['colorsByQuarters'] = [];
        foreach ($viewData['revenuesByQuarters'] as $quarter => $item) {
            if (isset($quarter[1])) {
                $viewData['colorsByQuarters'][] = $quarter[1] == $viewData['currentQuarter'] ? 'rgba(96, 165, 250, 0.6)' : 'rgba(96, 165, 250, 0.2)';
            }
        }

        $viewData['nextDueDate'] = $this->declarationService->getNextDueDate();

        $viewData['globalByYears'] = [];
        foreach ($viewData['years'] as $year) {
            $totalSocial = $this->declarationService->declarationTypeSocial->getTotalByYear($year);
            $totalCfe = $this->declarationService->declarationTypeCfe->getTotalByYear($year);
            $totalTva = $this->declarationService->declarationTypeTva->getTotalByYear($year);
            $totalImpot = $this->declarationService->declarationTypeImpot->getTotalByYear($year);
            $totalSales = $this->invoiceRepository->getSalesRevenuesBy($year);
            $daysByMonth = $this->invoiceRepository->getDaysCountByYear($year);
            $net = $totalSales - $totalSocial - $totalImpot - $totalCfe;

            $viewData['globalByYears'][] = [
                'year'       => $year,
                'social'     => round($totalSocial),
                'cfe'        => round($totalCfe),
                'tva'        => round($totalTva),
                'impot'      => round($totalImpot),
                'ht'         => round($totalSales),
                'net'        => round($net),
                'days'       => $daysByMonth,
                'percent'    => round($daysByMonth * 100 / (20 * 12)),
                'netByMonth' => round($net / 12),
            ];
        }

        $chartRevenuesByYears = $this->chartBuilder->createChart(Chart::TYPE_BAR);
        $chartRevenuesByYears->setData([
                                           'labels'   => array_keys($viewData['revenuesByYears']),
                                           'datasets' => [
                                               [
                                                   'label'           => 'CA (€)',
                                                   'backgroundColor' => array_values($viewData['colorsByYears']),
                                                   'data'            => array_values($viewData['revenuesByYears']),
                                               ],
                                           ],
                                       ]);
        $viewData['chartRevenuesByYears'] = $chartRevenuesByYears;

        $chartRevenuesByQuarters = $this->chartBuilder->createChart(Chart::TYPE_BAR);
        $chartRevenuesByQuarters->setData([
                                              'labels'   => array_keys($viewData['revenuesByQuarters']),
                                              'datasets' => [
                                                  [
                                                      'label'           => 'CA (€)',
                                                      'backgroundColor' => array_values($viewData['colorsByQuarters']),
                                                      'data'            => array_values(
                                                          $viewData['revenuesByQuarters']
                                                      ),
                                                  ],
                                              ],
                                          ]);
        $viewData['chartRevenuesByQuarters'] = $chartRevenuesByQuarters;

        $chartDaysByMonth = $this->chartBuilder->createChart(Chart::TYPE_BAR);
        $chartDaysByMonth->setData([
                                       'labels'   => $viewData['daysByMonth']->keys(),
                                       'datasets' => [
                                           [
                                               'label'           => 'jours (€)',
                                               'backgroundColor' => array_values($viewData['colorsByMonths']),
                                               'data'            => $viewData['daysByMonth']->values(),
                                           ],
                                       ],
                                   ]);
        $viewData['chartDaysByMonth'] = $chartDaysByMonth;

        return $viewData;
    }
}