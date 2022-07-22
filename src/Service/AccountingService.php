<?php

namespace App\Service;


use App\Repository\OperationRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

class AccountingService
{
    public function __construct(private OperationRepository $operationRepository)
    {

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