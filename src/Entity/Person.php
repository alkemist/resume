<?php

namespace App\Entity;

use App\Enum\PersonCivilityEnum;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PersonRepository")
 */
class Person
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?PersonCivilityEnum $civility;
    /**
     * @ORM\Column(type="string", length=255)
     */
    private ?string $firstname;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private ?string $lastname;

    /**
     * @ORM\Column(type="simple_array", nullable=true)
     * @var string[]
     */
    private array $phones = [];

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Company", inversedBy="persons", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private ?Company $company;

    /**
     * @ORM\Column(type="boolean")
     */
    private ?bool $isInvoicingDefault;

    /**
     * @ORM\Column(type="simple_array", nullable=true)
     * @var string[]
     */
    private array $emails = [];

    public function __toString(): string
    {
        return $this->getCivilityName() . ' ' . $this->getFirstname() . ' ' . $this->getLastname();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCivility(): ?PersonCivilityEnum
    {
        return $this->civility;
    }

    public function getCivilityName(): string
    {
        return $this->civility->toString();
    }

    public function setCivility(?PersonCivilityEnum $civility): self
    {
        $this->civility = $civility;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getPhones(): ?array
    {
        return $this->phones;
    }

    public function setPhones(?array $phones): self
    {
        $this->phones = $phones;

        return $this;
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

    public function getIsInvoicingDefault(): ?bool
    {
        return $this->isInvoicingDefault;
    }

    public function setIsInvoicingDefault(bool $isInvoicingDefault): self
    {
        $this->isInvoicingDefault = $isInvoicingDefault;

        return $this;
    }

    public function getEmails(): ?array
    {
        return $this->emails;
    }

    public function setEmails(?array $emails): self
    {
        $this->emails = $emails;

        return $this;
    }
}
