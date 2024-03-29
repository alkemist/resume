<?php

namespace App\Entity;

use App\BaseClass\BaseEntity;
use App\Enum\DeclarationTypeEnum;
use App\Enum\InvoicePaymentTypeEnum;
use App\Enum\InvoiceStatusEnum;
use App\Repository\InvoiceRepository;
use DateInterval;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: InvoiceRepository::class)]
#[UniqueEntity('number')]
class Invoice extends BaseEntity
{
    final const NUMBER_DATE_FORMAT = 'Ym-';
    final const TAX_MULTIPLIER = 0.2;
    final const DUE_INTERVAL_1M = 'P1M';
    /** @var array user friendly named type */
    final const DUE_INTERVALES = [
        '30 days end of month' => self::DUE_INTERVAL_1M,
    ];
    final const TJM_DEFAULT = 400;
    final const LIMIT_AE_TVA = 33200;
    final const LIMIT_AE = 70000;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    protected int $id;
    #[ORM\Column(type: Types::STRING, length: 255, unique: true)]
    protected ?string $number = null;
    #[ORM\ManyToOne(targetEntity: Company::class, cascade: ['persist'], inversedBy: 'invoices')]
    #[ORM\JoinColumn(nullable: false)]
    protected ?Company $company = null;
    #[ORM\ManyToOne(targetEntity: Experience::class, cascade: ['persist'], inversedBy: 'invoices')]
    #[ORM\JoinColumn(nullable: true)]
    protected ?Experience $experience = null;
    #[ORM\Column(type: Types::DATE_MUTABLE)]
    protected ?DateTimeInterface $createdAt = null;
    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    protected ?DateTimeInterface $payedAt = null;
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    protected ?string$totalTax = null;
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    protected ?string $object = null;
    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    protected ?int $tjm = null;
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    protected ?string $totalHt = null;
    #[ORM\Column(type: Types::DECIMAL, precision: 3, scale: 1, nullable: true)]
    protected ?string $daysCount = null;
    #[ORM\Column(type: Types::STRING, nullable: true, enumType: InvoicePaymentTypeEnum::class)]
    protected ?InvoicePaymentTypeEnum $payedBy = null;
    #[ORM\Column(type: Types::STRING, nullable: true, enumType: InvoiceStatusEnum::class)]
    protected ?InvoiceStatusEnum $status = null;
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    protected ?string $dueInterval = null;
    /**
     * @var Collection<Activity>
     */
    #[ORM\OneToMany(mappedBy: 'invoice', targetEntity: Activity::class, cascade: ['persist', 'remove'])]
    protected Collection $activities;
    #[ORM\ManyToOne(targetEntity: Period::class, inversedBy: 'invoices')]
    protected ?Period $period = null;
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    protected ?string $extraLibelle = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    protected ?string $extraHt = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    protected ?string $reference = null;

    public function __construct(DateTime $currentDate = new DateTime())
    {
        $this->daysCount = 0;
        $this->tjm = self::TJM_DEFAULT;
        $this->createdAt = $currentDate;
        $this->object = "Prestation de développement web - " . $currentDate->format('Y-m');
        $this->number = $currentDate->format(Invoice::NUMBER_DATE_FORMAT) . '1';
        $this->payedBy = InvoicePaymentTypeEnum::Transfert;
        $this->status = InvoiceStatusEnum::Draft;
        $this->dueInterval = 'P1M';
        $this->activities = new ArrayCollection();
    }

    /**
     * @throws Exception
     */
    public function getDateMonth(): DateTime
    {
        [$invoiceYear, $invoiceMonth] = $this->getYearMonth();
        return (new DateTime($invoiceYear . $invoiceMonth . '01'));
    }

    /**
     * @return int[]
     */
    public function getYearMonth(): array
    {
        if (strlen($this->getNumber()) === 8) {
            return [intval(substr($this->getNumber(), 0, 4)),
                intval(substr($this->getNumber(), 4, 2))
            ];
        } else {
            $date = $this->getCreatedAt();
            return [
                intval($date->format('Y')),
                intval($date->format('m'))
            ];
        }
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(string $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function updateHt(): void
    {
        $this->totalHt = ($this->tjm * $this->daysCount) + $this->extraHt;
    }

    public function isCredit(): bool
    {
        return $this->getTotalHt() < 0;
    }

    public function getTotalHt(): ?string
    {
        return $this->totalHt;
    }

    public function setTotalHt(?string $totalHt): self
    {
        $this->totalHt = $totalHt;
        return $this;
    }

    public function getDaysCount(): ?float
    {
        return $this->daysCount;
    }

    public function setDaysCount(?float $daysCount): self
    {
        $this->daysCount = $daysCount;
        return $this;
    }

    public function getTjm(): ?int
    {
        return $this->tjm;
    }

    public function setTjm(?int $tjm): self
    {
        $this->tjm = $tjm;

        return $this;
    }

    public function getStatus(): ?InvoiceStatusEnum
    {
        return $this->status;
    }

    public function setStatus(?InvoiceStatusEnum $status): self
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

    public function getStatusOrder(): int
    {
        return match ($this) {
            InvoiceStatusEnum::Draft => 0,
            InvoiceStatusEnum::Waiting => 1,
            InvoiceStatusEnum::Payed => 2,
        };
    }

    public function getTotalNet(): float
    {
        return floatval($this->totalHt) * (1 - Declaration::SOCIAL_NON_COMMERCIALE);
    }

    /**
     * @param Activity[] $activities
     */
    public function importActivities(array $activities): void
    {
        $dayCount = 0;

        foreach ($activities as $activity) {
            $activity->setInvoice($this);
            $dayCount += $activity->getValue();
        }

        $this->setDaysCount($dayCount);
    }

    public function getFilename(): string
    {
        return $this->getNumber() . '.pdf';
    }


    public function getExperienceCompany(): string
    {
        return $this->experience ? $this->getExperience()->getCompany()->getDisplayName() : '';
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): self
    {
        $this->company = $company;

        return $this;
    }

    public function getExperience(): ?Experience
    {
        return $this->experience;
    }

    public function setExperience(?Experience $experience): self
    {
        $this->experience = $experience;

        return $this;
    }

    public function getExperienceName(): string
    {
        return $this->experience ? $this->getExperience()->__toString() : '';
    }

    public function __toString(): string
    {
        return $this->getNumber();
    }

    public function getCreatedAtYear(): int
    {
        return $this->createdAt->format('Y');
    }

    public function getPayedAtYear(): int
    {
        return $this->payedAt->format('Y');
    }

    public function getCreatedAtQuarter(): int
    {
        return ceil($this->createdAt->format('n') / 3);
    }

    public function getPayedAtQuarter(): int
    {
        return ceil($this->payedAt->format('n') / 3);
    }

    public function getTotalTtc(): ?string
    {
        return $this->getTotalHt() + $this->getTotalTax();
    }

    public function getTotalTax(): ?string
    {
        return $this->totalTax;
    }

    public function setTotalTax(?string $totalTax): self
    {
        $this->totalTax = $totalTax;

        return $this;
    }

    public function getObject(): ?string
    {
        return $this->object;
    }

    public function setObject(?string $object): self
    {
        $this->object = $object;

        return $this;
    }

    public function getPayedBy(): ?InvoicePaymentTypeEnum
    {
        return $this->payedBy;
    }

    public function setPayedBy(InvoicePaymentTypeEnum $payedBy): self
    {
        $this->payedBy = $payedBy;

        return $this;
    }

    public function getPayedByName(): ?string
    {
        return $this->payedBy->toString();
    }

    public function getStatusName(): string
    {
        return $this->status->toString();
    }

    /**
     * @throws Exception
     */
    public function getDueAt(): ?DateTime
    {
        if (!$this->getCreatedAt() || !$this->getDueInterval()) return null;

        $createdAt = $this->getCreatedAt();
        $lastDayOfMonth = new DateTime($createdAt->format('Y-m-t'));
        $firstDayOfNextMonth = (clone $lastDayOfMonth)->add(new DateInterval('P1D'));
        return new DateTime($firstDayOfNextMonth->format('Y-m-t'));
    }

    public function getDueInterval(): ?string
    {
        return $this->dueInterval;
    }

    public function setDueInterval(?string $dueInterval): self
    {
        $this->dueInterval = $dueInterval;

        return $this;
    }

    /**
     * @return Collection<Activity>
     */
    public function getActivities(): Collection
    {
        return $this->activities;
    }

    public function addActivity(Activity $activity): self
    {
        if (!$this->activities->contains($activity)) {
            $this->activities[] = $activity;
            $activity->setInvoice($this);
        }

        return $this;
    }

    public function removeActivity(Activity $activity): self
    {
        if ($this->activities->contains($activity)) {
            $this->activities->removeElement($activity);
            // set the owning side to null (unless already changed)
            if ($activity->getInvoice() === $this) {
                $activity->setInvoice(null);
            }
        }

        return $this;
    }

    public function getPeriodName(): string
    {
        return $this->period ? $this->period->__toString() : '';
    }

    public function getSocialDeclaration(): ?Declaration
    {
        if ($this->getPeriod()) {
            $declarations = $this->getPeriod()->getDeclarations();
            foreach ($declarations as $declaration) {
                if ($declaration->getType() === DeclarationTypeEnum::Social) {
                    return $declaration;
                }
            }
        }

        return null;
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

    public function getExtraLibelle(): ?string
    {
        return $this->extraLibelle;
    }

    public function setExtraLibelle(?string $extraLibelle): self
    {
        $this->extraLibelle = $extraLibelle;

        return $this;
    }

    public function getExtraHt(): ?string
    {
        return $this->extraHt;
    }

    public function setExtraHt(?string $extraHt): self
    {
        $this->extraHt = $extraHt;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }
}
