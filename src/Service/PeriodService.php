<?php

namespace App\Service;

use App\Entity\Period;
use App\Repository\PeriodRepository;
use DateInterval;
use DateTime;
use Exception;

class PeriodService
{
    private PeriodRepository $periodRepository;

    public function __construct(
        PeriodRepository $periodRepository
    )
    {
        $this->periodRepository = $periodRepository;
    }

    /**
     * Renvoi la période courante
     * @param DateTime|null $date
     * @return Period[]
     * @throws Exception
     */
    public function getCurrentPeriod(DateTime $date = null): array
    {
        if (!$date) {
            $date = new DateTime();
        }

        $year = intval($date->format('Y'));

        $annualyPeriod = $this->periodRepository->findOneBy([
            'year' => $year - 1
        ]);
        $quarterlyPeriod = $this->periodRepository->findOneBy([
            'year' => $year - 1,
            'quarter' => ceil(intval($date->format('n')) / 3)
        ]);

        return [$annualyPeriod, $quarterlyPeriod];
    }

    /**
     * @param $year
     * @return Period|null
     */
    public function getAnnualyByYear($year): ?Period
    {
        return $this->periodRepository->findOneBy([
            'year' => $year
        ]);
    }

    /**
     * @param $year
     * @return Period[]
     */
    public function getQuarterlyByYear($year): array
    {
        $quarterly = [];
        for ($quarter = 1; $quarter <= 4; $quarter++) {
            $quarterly[] = $this->periodRepository->findOneBy([
                'year' => $year,
                'quarter' => $quarter
            ]);
        }
        return $quarterly;
    }


    /**
     * Renvoi la précédente période
     * @return Period[]
     * @throws Exception
     */
    public function getPreviousPeriod(): array
    {
        $date = (new DateTime())->sub(new DateInterval('P1M'));
        return $this->getCurrentPeriod($date);
    }
}
