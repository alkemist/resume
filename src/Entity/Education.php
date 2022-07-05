<?php

namespace App\Entity;

use App\Repository\EducationRepository;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Stringable;

#[ORM\Entity(repositoryClass: EducationRepository::class)]
class Education implements Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $school = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $details = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $location = null;

    #[ORM\Column(type: 'smallint')]
    private ?int $level = null;

    #[ORM\Column(type: 'date')]
    private ?DateTimeInterface $dateBegin = null;

    #[ORM\Column(type: 'date')]
    private ?DateTimeInterface $dateEnd = null;

    public function __toString(): string
    {
        return $this->getName();
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

    public function getId(): int
    {
        return $this->id;
    }

    public function getSchool(): ?string
    {
        return $this->school;
    }

    public function setSchool(string $school): self
    {
        $this->school = $school;

        return $this;
    }

    public function getDetails(): ?string
    {
        return $this->details;
    }

    public function setDetails(?string $details): self
    {
        $this->details = $details;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(int $level): self
    {
        $this->level = $level;

        return $this;
    }

    public function getDateBegin(): ?DateTimeInterface
    {
        return $this->dateBegin;
    }

    public function setDateBegin(DateTimeInterface $dateBegin): self
    {
        $this->dateBegin = $dateBegin;

        return $this;
    }

    public function getDateEnd(): ?DateTimeInterface
    {
        return $this->dateEnd;
    }

    public function setDateEnd(DateTimeInterface $dateEnd): self
    {
        $this->dateEnd = $dateEnd;

        return $this;
    }
}
