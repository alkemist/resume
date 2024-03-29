<?php

namespace App\Service;

use App\Entity\Period;
use App\Repository\PeriodRepository;
use DateInterval;
use DateTime;
use DateTimeInterface;
use Exception;

class PeriodService
{
    public function __construct(private readonly PeriodRepository $periodRepository)
    {
    }

    public function getAnnualyByYear(int $year): ?Period
    {
        return $this->periodRepository->findOneBy([
            'year' => $year
        ]);
    }

    /**
     * @return Period[]
     */
    public function getQuarterlyByYear(int $year): array
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

    /**
     * Renvoi la période courante
     * @param DateTimeInterface|DateTime $date
     * @return Period[]
     * @throws Exception
     */
    public function getCurrentPeriod(DateTimeInterface|DateTime $date): array
    {
        $year = intval($date->format('Y'));

        // @TODO J'ai supprimé le "$year - 1" parce qu'il crée un bug (aout 2022 => 2021 T3)
        $annualyPeriod = $this->periodRepository->findOneBy([
            'year' => $year
        ]);
        $quarterlyPeriod = $this->periodRepository->findOneBy([
            'year' => $year,
            'quarter' => ceil(intval($date->format('n')) / 3)
        ]);

        return [$annualyPeriod, $quarterlyPeriod];
    }
}
