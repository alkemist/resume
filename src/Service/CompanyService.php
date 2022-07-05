<?php

namespace App\Service;

use App\Entity\Declaration;
use App\Entity\Invoice;
use App\Repository\CompanyRepository;
use App\Repository\DeclarationRepository;
use App\Repository\InvoiceRepository;
use App\Repository\PeriodRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class CompanyService
{
    public function __construct(
    ) {
    }

    /**
     * @return array
     */
    public function getNotifications(): array
    {
        return [];
    }
}