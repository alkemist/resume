<?php

namespace App\Entity;

use App\Enum\CompanyTypeEnum;
use App\Helper\StringHelper;
use App\Repository\CompanyRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;


#[ORM\Entity(repositoryClass: CompanyRepository::class)]
#[UniqueEntity('slug')]
class Company implements Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private int $id;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $displayName = null;

    #[ORM\Column(type: Types::STRING, length: 255, unique: true)]
    private ?string $slug = null;

    #[ORM\OneToMany(mappedBy: 'company', targetEntity: Experience::class, cascade: ['persist'])]
    private Collection $experiences;

    #[ORM\OneToMany(mappedBy: 'company', targetEntity: Invoice::class, cascade: ['persist'])]
    private Collection $invoices;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $street = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $postalCode = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $city = null;

    #[ORM\Column(type: Types::STRING, nullable: true, enumType: CompanyTypeEnum::class)]
    private ?CompanyTypeEnum $type = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $reference = null;

    /**
     * @var Collection<Person>
     */
    #[ORM\OneToMany(mappedBy: 'company', targetEntity: Person::class, cascade: ['persist'])]
    private Collection $persons;

    /**
     * @var Collection<Activity>
     */
    #[ORM\OneToMany(mappedBy: 'company', targetEntity: Activity::class)]
    private Collection $activities;

    #[ORM\ManyToOne(targetEntity: Company::class, inversedBy: 'clients')]
    private ?self $contractor = null;

    /**
     * @var Collection<self>
     */
    #[ORM\OneToMany(mappedBy: 'contractor', targetEntity: Company::class)]
    private Collection $clients;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $tjm = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $service = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $filename = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?DateTime $updatedAt = null;

    #[Vich\UploadableField(mapping: 'statements', fileNameProperty: 'filename')]
    private ?File $file = null;

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

    public function getDisplayName(): ?string
    {
        return $this->displayName ?: $this->getName();
    }

    public function setDisplayName(?string $displayName): self
    {
        $this->displayName = $displayName;

        return $this;
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

    public function getId(): int
    {
        return $this->id;
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

    public function setType(CompanyTypeEnum $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getTypeName(): ?string
    {
        return $this->type->toString();
    }

    public function getLastExperience(): ?Experience
    {
        $count = count($this->getExperiences());
        return $count > 1 ? $this->experiences[$count - 1] : null;
    }

    /**
     * @return Collection<Experience>
     */
    public function getExperiences(): Collection
    {
        return $this->experiences;
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

    public function getContractor(): ?Company
    {
        return $this->contractor;
    }

    public function setContractor(?self $contractor): self
    {
        $this->contractor = $contractor;

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

    public function getEmails(): array
    {
        foreach ($this->getPersons() as $person) {
            if ($person->getIsInvoicingDefault()) {
                return $person->getEmails();
            }
        }
        return [];
    }

    /**
     * @return Collection<Person>
     */
    public function getPersons(): Collection
    {
        return $this->persons;
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

    public function getContractorName(): string
    {
        return $this->contractor ? (string)$this->contractor : '';
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

    public function getFile(): File
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
}
