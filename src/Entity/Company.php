<?php

namespace App\Entity;

use App\Enum\CompanyTypeEnum;
use App\Helper\StringHelper;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;


/**
 * @ORM\Entity(repositoryClass="App\Repository\CompanyRepository")
 * @UniqueEntity("slug")
 */
class Company
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
    private ?string $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $displayName;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private ?string $slug;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Experience", mappedBy="company", cascade={"persist"})
     */
    private ArrayCollection $experiences;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Invoice", mappedBy="company", cascade={"persist"})
     */
    private ArrayCollection $invoices;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $street;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $postalCode;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $city;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private CompanyTypeEnum $type;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $reference;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Person", mappedBy="company", cascade={"persist"})
     * @var ArrayCollection<Person>
     */
    private ArrayCollection $persons;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Activity", mappedBy="company")
     * @var ArrayCollection<Activity>
     */
    private ArrayCollection $activities;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Company", inversedBy="clients")
     */
    private ?self $contractor;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Company", mappedBy="contractor")
     * @var ArrayCollection<self>
     */
    private ArrayCollection $clients;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $notes;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $tjm;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $service;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $filename;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private DateTime $updatedAt;

    /**
     * @Vich\UploadableField(mapping="statements", fileNameProperty="filename")
     */
    private File $file;

    public function __construct()
    {
        $this->experiences = new ArrayCollection();
        $this->invoices = new ArrayCollection();
        $this->persons = new ArrayCollection();
        $this->activities = new ArrayCollection();
        $this->clients = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getDisplayName();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        $this->setSlug(StringHelper::slugify($name));

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getType(): ?CompanyTypeEnum
    {
        return $this->type;
    }

    public function getTypeName(): ?string
    {
        return $this->type->toString();
    }

    public function setType(CompanyTypeEnum $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return Collection<Experience>
     */
    public function getExperiences(): Collection
    {
        return $this->experiences;
    }

    /**
     * @return Experience|null
     */
    public function getLastExperience(): ?Experience
    {
        $count = count($this->getExperiences());
        return $count > 1 ? $this->experiences[$count - 1] : null;
    }

    public function addExperience(Experience $experience): self
    {
        if (!$this->experiences->contains($experience)) {
            $this->experiences[] = $experience;
            $experience->setCompany($this);
        }

        return $this;
    }

    public function removeExperience(Experience $experience): self
    {
        if ($this->experiences->contains($experience)) {
            $this->experiences->removeElement($experience);
            // set the owning side to null (unless already changed)
            if ($experience->getCompany() === $this) {
                $experience->setCompany(null);
            }
        }

        return $this;
    }

    /**
     * @param self[] $contractors
     * @return self[]
     */
    public function getAllContractors(array $contractors = []): array
    {
        if ($this->getContractor()) {
            $contractor = $this->getContractor();

            if (!in_array($contractor, $contractors)) {
                $contractors[] = $contractor;

                return $contractor->getAllContractors($contractors);
            }
        }

        return $contractors;
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
            $invoice->setCompany($this);
        }

        return $this;
    }

    public function removeInvoice(Invoice $invoice): self
    {
        if ($this->invoices->contains($invoice)) {
            $this->invoices->removeElement($invoice);
            // set the owning side to null (unless already changed)
            if ($invoice->getCompany() === $this) {
                $invoice->setCompany(null);
            }
        }

        return $this;
    }

    public function getDisplayName(): ?string
    {
        return $this->displayName ?: $this->getName();
    }

    public function setDisplayName(?string $displayName): self
    {
        $this->displayName = $displayName;

        return $this;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street): self
    {
        $this->street = $street;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): self
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return Collection<Person>
     */
    public function getPersons(): Collection
    {
        return $this->persons;
    }

    public function getEmails(): array
    {
        foreach ($this->getPersons() as $person) {
            if ($person->getIsInvoicingDefault()) {
                return $person->getEmails();
            }
        }
        return [];
    }

    public function addPerson(Person $person): self
    {
        if (!$this->persons->contains($person)) {
            $this->persons[] = $person;
            $person->setCompany($this);
        }

        return $this;
    }

    public function removePerson(Person $person): self
    {
        if ($this->persons->contains($person)) {
            $this->persons->removeElement($person);
            // set the owning side to null (unless already changed)
            if ($person->getCompany() === $this) {
                $person->setCompany(null);
            }
        }

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
            $activity->setCompany($this);
        }

        return $this;
    }

    public function removeActivity(Activity $activity): self
    {
        if ($this->activities->contains($activity)) {
            $this->activities->removeElement($activity);
            // set the owning side to null (unless already changed)
            if ($activity->getCompany() === $this) {
                $activity->setCompany(null);
            }
        }

        return $this;
    }

    /**
     * @return self|null
     */
    public function getContractor(): ?Company
    {
        return $this->contractor;
    }

    public function getContractorName(): string
    {
        return $this->contractor ? (string) $this->contractor : '';
    }

    public function setContractor(?self $contractor): self
    {
        $this->contractor = $contractor;

        return $this;
    }

    /**
     * @return Collection<self>
     */
    public function getClients(): Collection
    {
        return $this->clients;
    }

    /**
     * @return self|null
     */
    public function getClient(): self|null
    {
        return count($this->clients) == 1 ? $this->clients[0] : null;
    }

    public function addClient(self $client): self
    {
        if (!$this->clients->contains($client)) {
            $this->clients[] = $client;
            $client->setContractor($this);
        }

        return $this;
    }

    public function removeClient(self $client): self
    {
        if ($this->clients->contains($client)) {
            $this->clients->removeElement($client);
            // set the owning side to null (unless already changed)
            if (!$client->getContractor()) {
                $client->setContractor(null);
            }
        }

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;

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

    public function getService(): ?string
    {
        return $this->service;
    }

    public function setService(?string $service): self
    {
        $this->service = $service;

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

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(?string $filename): self
    {
        $this->filename = $filename;

        return $this;
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

    public function getFile(): File
    {
        return $this->file;
    }
}
