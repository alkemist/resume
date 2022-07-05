<?php

namespace App\Entity;

use App\Enum\DeclarationStatusEnum;
use App\Enum\DeclarationTypeEnum;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DeclarationRepository")
 */
class Declaration
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private ?string $revenue;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private ?string $tax;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private ?DeclarationTypeEnum $type;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Period", inversedBy="declarations")
     */
    private ?Period $period;

    const SOCIAL_NON_COMMERCIALE = 0.22;
    const SOCIAL_CFP = 0.002;
    const IMPOT_ABATTEMENT = 0.34;

    /**
     * @ORM\Column(type="string", length=255, nullable=true))
     */
    private DeclarationStatusEnum $status;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private ?DateTimeInterface $payedAt;

    public function __construct()
    {
        $this->setStatus(DeclarationStatusEnum::Waiting);
    }

    public function __toString()
    {
        $str = ucfirst($this->getType()->toString()) . ' ';
        $period = $this->getPeriod();

        if ($period->getYear()) {
            $str .= $period->getYear();
        }
        if ($period->getQuarter()) {
            $str .= ' T' . $period->getQuarter();
        }

        return $str;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRevenue(): ?string
    {
        return $this->revenue;
    }

    public function setRevenue(string $revenue): self
    {
        $this->revenue = $revenue;

        return $this;
    }

    public function getTax(): ?string
    {
        return $this->tax;
    }

    public function setTax(?string $tax): self
    {
        $this->tax = $tax;

        return $this;
    }

    public function getRate(): float
    {
        return $this->getRevenue() > 0
            ? round($this->getTax() * 100 / $this->getRevenue(), 2)
            : 0;
    }

    public function getType(): ?DeclarationTypeEnum
    {
        return $this->type;
    }

    public function getTypeName(): ?string
    {
        return $this->type->toString();
    }

    public function setType(DeclarationTypeEnum $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getPeriod(): ?Period
    {
        return $this->period;
    }

    public function setPeriod(?Period $period): self
    {
        $this->period = $period;

        return $this;
    }

    public function getStatus(): ?DeclarationStatusEnum
    {
        return $this->status;
    }

    public function getStatusName(): ?string
    {
        return $this->status->toString();
    }

    public function setStatus(DeclarationStatusEnum $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getPayedAt(): ?DateTimeInterface
    {
        return $this->payedAt;
    }

    public function setPayedAt(?DateTimeInterface $payedAt): self
    {
        $this->payedAt = $payedAt;

        return $this;
    }

    /**
     * @return Invoice[]
     */
    public function getInvoices(): array
    {
        $period = $this->getPeriod();
        if (!$period) {
            return [];
        }

        $periodsQuarter = $period->getPeriodsQuarter();

        if (count($periodsQuarter)) {
            $invoices = [];
            foreach ($periodsQuarter as $period) {
                $invoices = array_merge($invoices, $period->getPayedInvoices());
            }
            return $invoices;
        }

        return $period->getPayedInvoices();
    }

    /**
    public function getPurchases(): array
    {
        if ($this->getType() !== DeclarationTypeEnum::TVA) {
            return [];
        }

        $period = $this->getPeriod();
        if (!$period) {
            return [];
        }

        return $period->getPurchases()->toArray();
    }

    public function setInvoices(?array $invoices): array
    {
        return $this;
    }*/
}
