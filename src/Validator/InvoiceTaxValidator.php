<?php

namespace App\Validator;

use App\Repository\InvoiceRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class InvoiceTaxValidator extends ConstraintValidator
{
    public function __construct(private readonly InvoiceRepository $invoiceRepository)
    {
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function validate($value, Constraint $constraint)
    {
        /* @var $constraint  InvoiceTax */

        $isOutOfTaxLimt = $this->invoiceRepository->isOutOfTaxLimit();

        if ($isOutOfTaxLimt && $value || !$isOutOfTaxLimt && !$value) {
            return;
        }

        $this->context->buildViolation($isOutOfTaxLimt ? $constraint->message_fill : $constraint->message_empty)
            ->setParameter('{{ value }}', $value)
            ->addViolation();
    }
}
