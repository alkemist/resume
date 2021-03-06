<?php

namespace App\Entity;

use App\Enum\InvoiceStatusEnum;
use App\Repository\PeriodRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Stringable;

#[ORM\Entity(repositoryClass: PeriodRepository::class)]
class Period implements Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private int $id;

    /**
     * @var Collection<Invoice>
     */
    #[ORM\OneToMany(mappedBy: 'period', targetEntity: Invoice::class)]
    private Collection $invoices;

    #[ORM\OneToMany(mappedBy: 'period', targetEntity: Declaration::class)]
    private Collection $declarations;

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $year = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $quarter = null;

    #[ORM\ManyToOne(targetEntity: Period::class, inversedBy: 'periodsQuarter')]
    private ?Period $periodYear = null;

    /**
     * @var Collection<Period>
     */
    #[ORM\OneToMany(mappedBy: 'periodYear', targetEntity: Period::class)]
    private Collection $periodsQuarter;

    public function __construct()
    {
        $this->invoices = new ArrayCollection();
        $this->declarations = new ArrayCollection();
        $this->periodsQuarter = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function __toString(): string
    {
        $array = [
            $this->getYear()
        ];
        if ($this->getQuarter()) {
            $array[] = 'T' . $this->getQuarter();
        }

        return implode(' - ', $array);
    }

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(int $year): self
    {
        $this->year = $year;

        return $this;
    }

    public function getQuarter(): ?int
    {
        return $this->quarter;
    }

    public function setQuarter(int $quarter): self
    {
        $this->quarter = $quarter;

        return $this;
    }

    /**
     * @return Collection<Invoice>
     */
    public function getInvoices(): Collection
    {
        return $this->invoices;
    }

    /**
     * @return Invoice[]
     */
    public function getPayedInvoices(): array
    {
        $invoices = [];
        /** @var Invoice $invoice */
        foreach ($this->invoices as $invoice) {
            if ($invoice->getStatus() === InvoiceStatusEnum::Payed) {
                $invoices[] = $invoice;
            }
        }
        return $invoices;
    }

    public function addInvoice(Invoice $invoice): self
    {
        if (!$this->invoices->contains($invoice)) {
            $this->invoices[] = $invoice;
            $invoice->setPeriod($this);
        }

        return $this;
    }

    public function removeInvoice(Invoice $invoice): self
    {
        if ($this->invoices->contains($invoice)) {
            $this->invoices->removeElement($invoice);
            // set the owning side to null (unless already changed)
            if ($invoice->getPeriod() === $this) {
                $invoice->setPeriod(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<Declaration>
     */
    public function getDeclarations(): Collection
    {
        return $this->declarations;
    }

    public function addDeclaration(Declaration $declaration): self
    {
        if (!$this->declarations->contains($declaration)) {
            $this->declarations[] = $declaration;
            $declaration->setPeriod($this);
        }

        return $this;
    }

    public function removeDeclaration(Declaration $declaration): self
    {
        if ($this->declarations->contains($declaration)) {
            $this->declarations->removeElement($declaration);
            // set the owning side to null (unless already changed)
            if ($declaration->getPeriod() === $this) {
                $declaration->setPeriod(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<self>
     */
    public function getPeriodsQuarter(): Collection
    {
        return $this->periodsQuarter;
    }

    public function addPeriodQuarter(self $quarter): self
    {
        if (!$this->periodsQuarter->contains($quarter)) {
            $this->periodsQuarter[] = $quarter;
            $quarter->setPeriodYear($this);
        }

        return $this;
    }

    public function removePeriodQuarter(self $quarter): self
    {
        if ($this->periodsQuarter->contains($quarter)) {
            $this->periodsQuarter->removeElement($quarter);
            // set the owning side to null (unless already changed)
            if ($quarter->getPeriodYear() === $this) {
                $quarter->setPeriodYear(null);
            }
        }

        return $this;
    }

    public function getPeriodYear(): ?self
    {
        return $this->periodYear;
    }

    public function setPeriodYear(?self $periodYear): self
    {
        $this->periodYear = $periodYear;

        return $this;
    }
}
