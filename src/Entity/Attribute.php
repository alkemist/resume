<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AttributeRepository")
 * @UniqueEntity("slug")
 */
class Attribute
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private ?string $slug;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $icon;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $value;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private ?int $weight;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private bool $isListable;

    public function __construct()
    {
        $this->isListable = false;
    }

    public function __toString(): string
    {
        return $this->getSlug();
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

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getWeight(): ?int
    {
        return $this->weight;
    }

    public function setWeight(int $weight): self
    {
        $this->weight = $weight;

        return $this;
    }

    public function getIsListable(): ?bool
    {
        return $this->isListable;
    }

    public function setIsListable(bool $isListable): self
    {
        $this->isListable = $isListable;

        return $this;
    }
}
