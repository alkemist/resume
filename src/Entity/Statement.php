<?php

namespace App\Entity;

use App\Repository\StatementRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Contracts\Translation\TranslatorInterface;
use Vich\UploaderBundle\Mapping\Annotation as Vich;


#[UniqueEntity(fields: ['filename'], message: 'Operation already exists')]
#[ORM\Entity(repositoryClass: StatementRepository::class)]
#[Vich\Uploadable]
class Statement implements \Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private int $id;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?DateTimeInterface $date = null;

    #[ORM\Column(type: Types::STRING, length: 255, unique: true)]
    private ?string $filename = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $updatedAt = null;

    #[Vich\UploadableField(mapping: 'statements', fileNameProperty: 'filename')]
    private ?File $file = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $operationsCount = null;

    private ?TranslatorInterface $translator = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $startAmount = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $endAmount = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $gapAmount = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $savingAmount = null;

    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }

    public function __toString(): string
    {
        $date = $this->getDate();
        if (!$date) {
            return '###';
        }

        if ($this->translator) {
            return $this->translator->trans($date->format('F')) . " " . $date->format('Y');
        } else {
            return $date->format('m') . " " . $date->format('Y');
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(?string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(File $file = null): void
    {
        $this->file = $file;

        // VERY IMPORTANT:
        // It is required that at least one field changes if you are using Doctrine,
        // otherwise the event listeners won't be called and the file is lost
        if ($file) {
            // if 'updatedAt' is not defined in your entity, use another property
            $this->updatedAt = new DateTime('now');
        }
    }

    public function getOperationsCount(): int
    {
        return $this->operationsCount ?? 0;
    }

    public function setOperationsCount(?int $operationsCount): self
    {
        $this->operationsCount = $operationsCount;

        return $this;
    }

    public function getStartAmount(): ?string
    {
        return $this->startAmount;
    }

    public function setStartAmount(?string $startAmount): self
    {
        $this->startAmount = $startAmount;

        return $this;
    }

    public function getEndAmount(): ?string
    {
        return $this->endAmount;
    }

    public function setEndAmount(?string $endAmount): self
    {
        $this->endAmount = $endAmount;

        return $this;
    }

    public function getGapAmount(): ?string
    {
        return $this->gapAmount;
    }

    public function setGapAmount(?string $gapAmount): self
    {
        $this->gapAmount = $gapAmount;

        return $this;
    }

    public function getSavingAmount(): ?string
    {
        return $this->savingAmount;
    }

    public function setSavingAmount(?string $savingAmount): self
    {
        $this->savingAmount = $savingAmount;

        return $this;
    }
}
