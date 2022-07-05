<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="app_users")
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User implements UserInterface
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=25, unique=true)
     */
    private string $username;

    /**
     * @ORM\Column(type="string", length=254, unique=true)
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private string $email;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(max=250)
     */
    private string $plainPassword;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private string $password;

    /**
     * @ORM\Column(type="string", length=250)
     */
    private string $salt;

    /**
     * @ORM\Column(name="is_active", type="boolean")
     */
    private bool $isActive;

    /**
     * @ORM\Column(name="roles", type="array")
     * @var string[]
     */
    private array $roles = [];

    public function __construct()
    {
        $this->isActive = false;
        $this->salt = md5(uniqid('', true));
    }

    public function __toString()
    {
        return $this->getUsername();
    }

    function getId(): int
    {
        return $this->id;
    }

    function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }

    public function getSalt(): ?string
    {
        return $this->salt;
    }

    public function setSalt(string $salt): self
    {
        $this->salt = $salt;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getPlainPassword(): string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;
        return $this;
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

    function addRole(string $role)
    {
        $this->roles[] = $role;
    }

    function getIsActive(): bool
    {
        return $this->isActive;
    }

    function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }

    /**
     * @return string
     */
    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    public function isIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function eraseCredentials(): void
    {
    }

    /**
     * @return string[]
     */
    #[ArrayShape(['id' => "int", 'username' => "string"])]
    public function __serialize(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
        ];
    }

    public function __unserialize(array $data)
    {
        $this->id = $data['id'];
        $this->username = $data['usernames'];
    }
}