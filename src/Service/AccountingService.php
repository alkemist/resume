<?php

namespace App\Service;


use App\Entity\Operation;
use App\Enum\InvoiceStatusEnum;
use App\Enum\OperationTypeEnum;
use App\Repository\OperationRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class AccountingService
{
    public function __construct(
        private readonly OperationRepository $operationRepository,
        private readonly ChartBuilderInterface $chartBuilder,
        private readonly TranslatorInterface $translator
    )
    {

    }

    #[ArrayShape([
        'years' => "array",
        'types' => "array",
        'activeType' => "string",
        'activeYear' => "int",
        'chartTotalsByMonthAndLabel' => "\Symfony\UX\Chartjs\Model\Chart",
        'chartTotalsByMonthAndType' => "\Symfony\UX\Chartjs\Model\Chart"
    ])]
    public function getDashboard(?int $year, ?string $type): array
    {
        $colors = [
            "#25CCF7","#FD7272","#54a0ff","#00d2d3",
            "#1abc9c","#2ecc71","#3498db","#9b59b6","#34495e",
            "#16a085","#27ae60","#2980b9","#8e44ad","#2c3e50",
            "#f1c40f","#e67e22","#e74c3c","#ecf0f1","#95a5a6",
            "#f39c12","#d35400","#c0392b","#bdc3c7","#7f8c8d",
            "#55efc4","#81ecec","#74b9ff","#a29bfe","#dfe6e9",
            "#00b894","#00cec9","#0984e3","#6c5ce7","#ffeaa7",
            "#fab1a0","#ff7675","#fd79a8","#fdcb6e","#e17055",
            "#d63031","#feca57","#5f27cd","#54a0ff","#01a3a4"
        ];
        $years = array_column($this->operationRepository->listYears(), 'date');
        sort($years);


        $types = iterator_to_array(OperationTypeEnum::choices(), true);

        unset($types[OperationTypeEnum::Hidden->value]);
        unset($types[OperationTypeEnum::Income->value]);
        unset($types[OperationTypeEnum::Refund->value]);

        $viewData = [
            'years' => $years,
            'types' => $types,
            'activeType' => $type,
            'activeYear' => $year
        ];

        $totalsByMonthAndType = $this->operationRepository->getTotalsByMonthAndType($year, $type);
        $totalsByMonthAndTypeStats = [];
        $emptyValuesByTypes = array_fill(0, count($types), 0);

        foreach ($totalsByMonthAndType as $row) {
            if (!isset($totalsByMonthAndTypeStats[$row['date']])) {
                $totalsByMonthAndTypeStats[$row['date']] = array_combine(array_keys($types), $emptyValuesByTypes);
            }

            $totalsByMonthAndTypeStats[$row['date']][$row['type']->value] = -floatval($row['total']);
        }

        $totalsByMonthAndTypeLabels = array_keys($totalsByMonthAndTypeStats);
        $totalsByMonthAndTypeStats = array_values($totalsByMonthAndTypeStats);

        $chartTotalsByMonthAndType = $this->chartBuilder->createChart(Chart::TYPE_BAR);
        $chartTotalsByMonthAndType->setData([
                                                'labels' => $totalsByMonthAndTypeLabels,
                                                'datasets' => [
                                                    [
                                                        'label' => $this->translator->trans(OperationTypeEnum::Charge->toString()),
                                                        'backgroundColor' => '#18227c',
                                                        'data' => array_map(function($item) {return $item[OperationTypeEnum::Charge->value];}, $totalsByMonthAndTypeStats),
                                                    ],
                                                    [
                                                        'label' => $this->translator->trans(OperationTypeEnum::Food->toString()),
                                                        'backgroundColor' => '#33691e',
                                                        'data' => array_map(function($item) {return $item[OperationTypeEnum::Food->value];}, $totalsByMonthAndTypeStats),
                                                    ],
                                                    [
                                                        'label' => $this->translator->trans(OperationTypeEnum::Supply->toString()),
                                                        'backgroundColor' => '#ff6f00',
                                                        'data' => array_map(function($item) {return $item[OperationTypeEnum::Supply->value];}, $totalsByMonthAndTypeStats),
                                                    ],
                                                    [
                                                        'label' => $this->translator->trans(OperationTypeEnum::Hobby->toString()),
                                                        'backgroundColor' => '#fdd835',
                                                        'data' => array_map(function($item) {return $item[OperationTypeEnum::Hobby->value];}, $totalsByMonthAndTypeStats),
                                                    ],
                                                    [
                                                        'label' => $this->translator->trans(OperationTypeEnum::Subscription->toString()),
                                                        'backgroundColor' => '#ad1457',
                                                        'data' => array_map(function($item) {return $item[OperationTypeEnum::Subscription->value];}, $totalsByMonthAndTypeStats),
                                                    ],
                                                    [
                                                        'label' => $this->translator->trans(OperationTypeEnum::Other->toString()),
                                                        'backgroundColor' => '#616161',
                                                        'data' => array_map(function($item) {return $item[OperationTypeEnum::Other->value];}, $totalsByMonthAndTypeStats),
                                                    ],
                                                ],
                                            ]);

        $chartTotalsByMonthAndType->setOptions([
             'scales' => [
                 'x' => [
                     'stacked' => true,
                 ],
                 'y' => [
                     'stacked' => true,
                     'ticks' => [
                         'stepSize' => $type ? null : 200,
                         'lineHeight' => 1
                     ],
                     'gridLines' => [
                         'lineWidth' => 1
                     ]
                 ]
             ]
         ]);
        $viewData['chartTotalsByMonthAndType'] = $chartTotalsByMonthAndType;

        if ($type) {
            $totalsByMonthAndLabel = $this->operationRepository->getTotalsByMonthAndLabel($year, $type);
            $totalsByMonthAndLabelStats = $labels = $totalsByMonthAndLabelDatasets = [];

            foreach ($totalsByMonthAndLabel as $row) {
                $totalsByMonthAndLabelStats[$row['date']][$row['label']] = -floatval($row['total']);

                if (!in_array($row['label'], $labels)) {
                    $labels[] = $row['label'];
                }
            }

            $totalsByMonthAndLabelLabels = array_keys($totalsByMonthAndLabelStats);
            $totalsByMonthAndLabelStats = array_values($totalsByMonthAndLabelStats);

            foreach ($labels as $index => $label) {
                $totalsByMonthAndLabelDatasets[] = [
                    'label' => $label,
                    'backgroundColor' => $colors[$index],
                    'data' => array_fill(0, count($totalsByMonthAndLabelLabels), 0)
                ];
            }
            foreach ($totalsByMonthAndLabelStats as $monthIndex => $month) {
                foreach ($month as $label => $total) {
                    $labelIndex = array_search($label, $labels);
                    $totalsByMonthAndLabelDatasets[$labelIndex]['data'][$monthIndex] = $total;
                }
            }
            $chartTotalsByMonthAndLabel = $this->chartBuilder->createChart(Chart::TYPE_BAR);
            $chartTotalsByMonthAndLabel->setData([
                                                     'labels' => $totalsByMonthAndLabelLabels,
                                                     'datasets' => $totalsByMonthAndLabelDatasets
                                                 ]);
            $viewData['chartTotalsByMonthAndLabel'] = $chartTotalsByMonthAndLabel;
        }

        return $viewData;
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function getNullTypesCount(): int
    {
        return $this->operationRepository->countNullTypes();
    }
}