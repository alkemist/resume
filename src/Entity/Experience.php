<?php

namespace App\Entity;

use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ExperienceRepository")
 */
class Experience
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $title;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $search;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $description;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $isFreelance;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $onSite;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $onHomepage;

    /**
     * @ORM\Column(type="date")
     */
    private DateTimeInterface $dateBegin;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private ?DateTimeInterface $dateEnd;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Skill", inversedBy="experiences", cascade={"persist"})
     * @var ArrayCollection<Skill>
     */
    private ArrayCollection $skills;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Company", inversedBy="experiences", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private ?Company $company;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Invoice", mappedBy="experience", cascade={"persist"})
     * @var ArrayCollection<Invoice>
     */
    private ArrayCollection $invoices;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Company")
     * @ORM\JoinColumn(nullable=true)
     */
    private ?Company $client;

    public function __toString(): string
    {
        $str = $this->getCompany() . ' ' .
            $this->getDateBegin()->format('m/Y');

        if ($this->getDateEnd()) {
            $str .= ' ' . $this->getDateEnd()->format('m/Y');
        }

        return $str;
    }

    public function __construct()
    {
        $this->skills = new ArrayCollection();
        $this->isFreelance = true;
        $this->onSite = true;
        $this->onHomepage = true;
        $this->dateBegin = new DateTime();
        $this->title = "Développeur Web";
        $this->invoices = new ArrayCollection();
    }

    public function getMainSkills(): string
    {
        /** @var Skill[] $skillCollection */
        $skillCollection = $this->getSkills()->filter(
            function (Skill $var) {
                if (str_starts_with($var->getName(), "Symfony") || str_starts_with($var->getName(), "Angular")) {
                    return true;
                }
                return false;
            }
        );

        $skillNames = [];
        foreach ($skillCollection as $skill) {
            $skillNames[] = $skill->getName();
        }


        return implode(', ', $skillNames);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getSearch(): ?string
    {
        return $this->search;
    }

    public function setSearch(string $search): self
    {
        $this->search = $search;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getOnSite(): ?bool
    {
        return $this->onSite;
    }

    public function setOnSite(bool $onSite): self
    {
        $this->onSite = $onSite;

        return $this;
    }

    public function getOnHomepage(): ?bool
    {
        return $this->onHomepage;
    }

    public function setOnHomepage(bool $onHomepage): self
    {
        $this->onHomepage = $onHomepage;

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

    public function setDateEnd(?DateTimeInterface $dateEnd): self
    {
        $this->dateEnd = $dateEnd;

        return $this;
    }

    /**
     * @return Collection<Skill>
     */
    public function getSkills(): Collection
    {
        return $this->skills;
    }

    public function addSkill(Skill $skill): self
    {
        if (!$this->skills->contains($skill)) {
            $this->skills[] = $skill;
        }

        return $this;
    }

    public function removeSkill(Skill $skill): self
    {
        if ($this->skills->contains($skill)) {
            $this->skills->removeElement($skill);
        }

        return $this;
    }

    public function getIsFreelance(): ?bool
    {
        return $this->isFreelance;
    }

    public function setIsFreelance(bool $isFreelance): self
    {
        $this->isFreelance = $isFreelance;

        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): self
    {
        $this->company = $company;
        $this->client = $company;

        return $this;
    }

    /**
     * @return Collection<Invoice>
     */
    public function getInvoices(): Collection
    {
        return $this->invoices;
    }

    public function addInvoice(Invoice $invoice): self
    {
        if (!$this->invoices->contains($invoice)) {
            $this->invoices[] = $invoice;
            $invoice->setExperience($this);
        }

        return $this;
    }

    public function removeInvoice(Invoice $invoice): self
    {
        if ($this->invoices->contains($invoice)) {
            $this->invoices->removeElement($invoice);
            // set the owning side to null (unless already changed)
            if ($invoice->getExperience() === $this) {
                $invoice->setExperience(null);
            }
        }

        return $this;
    }

    public function getClient(): ?Company
    {
        return $this->client;
    }


    public function getClientName(): string
    {
        return $this->client ? $this->client->getName() : '';
    }

    public function setClient(?Company $client): self
    {
        $this->client = $client;

        return $this;
    }
}
