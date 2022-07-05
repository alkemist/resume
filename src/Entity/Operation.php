<?php

namespace App\Entity;

use App\Enum\OperationTypeEnum;
use App\Repository\OperationRepository;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[UniqueEntity(fields: ['date', 'name', 'amount'], message: 'Operation already exists')]
#[ORM\Entity(repositoryClass: OperationRepository::class)]
class Operation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'date')]
    private ?DateTimeInterface $date = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private ?string $amount = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?OperationTypeEnum $type = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $target = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $label = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $location = null;

    public function __construct()
    {

    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getType(): ?OperationTypeEnum
    {
        return $this->type;
    }

    public function setType(?OperationTypeEnum $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getTypeName(): ?string
    {
        return $this->type->toString();
    }

    public function getTarget(): ?string
    {
        return $this->target;
    }

    public function setTarget(?string $target): self
    {
        $this->target = $target;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label ?: $this->name;
    }

    public function setLabel(?string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): self
    {
        $this->location = $location;

        return $this;
    }
}
