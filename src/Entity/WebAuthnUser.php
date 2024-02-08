<?php

namespace App\Entity;

use App\Repository\WebAuthnUserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table()]
#[ORM\Entity(repositoryClass: WebAuthnUserRepository::class)]
class WebAuthnUser implements UserInterface
{
    #[ORM\Column(type: Types::STRING, unique: true)]
    #[ORM\Id]
    private string $id;

    #[ORM\Column(type: Types::STRING, length: 25)]
    #[Assert\NotBlank]
    #[Assert\Email]
    private ?string $name = null;

    #[ORM\Column(type: Types::STRING, length: 25)]
    private ?string $displayName = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $icon = null;

    /**
     * @var string[]
     */
    #[ORM\Column(name: 'roles', type: 'array')]
    private array $roles = [];

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: WebAuthnKey::class, orphanRemoval: true)]
    private Collection $keys;

    public function __construct($id, $name, $displayName, $icon)
    {
        $this->id = $id;
        $this->name = $name;
        $this->displayName = $displayName;
        $this->icon = $icon;
        $this->email = $this->name;
        $this->keys = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getName();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    public function setDisplayName(string $displayName): self
    {
        $this->displayName = $displayName;
        return $this;
    }

    public function getIcon(): string|null
    {
        return $this->icon;
    }

    public function setIcon(string $icon): self
    {
        $this->icon = $icon;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->name;
    }

    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        if (empty($this->roles)) {
            return ['ROLE_USER'];
        }
        return $this->roles;
    }

    /**
     * @param string[] $roles
     * @return $this
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    function addRole(string $role): void
    {
        $this->roles[] = $role;
    }

    public function eraseCredentials()
    {

    }

    public function getUserIdentifier(): string
    {
        return $this->getName();
    }

    public function getUsername(): string
    {
        return $this->getName();
    }

    /**
     * @return Collection<int, WebAuthnKey>
     */
    public function getKeys(): Collection
    {
        return $this->keys;
    }

    public function addKey(WebAuthnKey $key): static
    {
        if (!$this->keys->contains($key)) {
            $this->keys->add($key);
            $key->setuser($this);
        }

        return $this;
    }

    public function removeKey(WebAuthnKey $key): static
    {
        if ($this->keys->removeElement($key)) {
            // set the owning side to null (unless already changed)
            if ($key->getuser() === $this) {
                $key->setuser(null);
            }
        }

        return $this;
    }
}