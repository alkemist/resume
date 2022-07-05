<?php

namespace App\Entity;

use App\Enum\SkillTypeEnum;
use App\Helper\StringHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SkillRepository")
 * @UniqueEntity("slug")
 */
class Skill
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private int $id;

    /**
     * @ORM\Column(name="name", type="string", length=100, nullable=false)
     */
    private string $name;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private ?string $slug;

    /**
     * @ORM\Column(name="type", type="string")
     */
    private SkillTypeEnum $type;

    /**
     * @var int
     *
     * @ORM\Column(name="level", type="integer", nullable=true)
     */
    private int $level;

    /**
     * @var bool
     *
     * @ORM\Column(name="on_homepage", type="boolean", nullable=false)
     */
    private bool $onHomepage;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Experience", mappedBy="skills", cascade={"persist"})
     * @var ArrayCollection<Experience>
     */
    private ArrayCollection $experiences;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Skill", inversedBy="children")
     */
    private ?Skill $parent;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Skill", mappedBy="parent")
     */
    private ArrayCollection $children;

    public function __construct()
    {
        $this->experiences = new ArrayCollection();
        $this->children = new ArrayCollection();
        $this->level = 0;
    }

    public function __toString(): string
    {
        return $this->getName();
    }

    public function getId(): ?int
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

    public function getType(): ?SkillTypeEnum
    {
        return $this->type;
    }

    public function getTypeName(): string
    {
        return $this->type->toString();
    }

    public function setType(?SkillTypeEnum $type): self
    {
        $this->type = $type;

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

    public function getOnHomepage(): ?bool
    {
        return $this->onHomepage;
    }

    public function setOnHomepage(bool $onHomepage): self
    {
        $this->onHomepage = $onHomepage;

        return $this;
    }

    /**
     * @return ArrayCollection<Experience>
     */
    public function getExperiences(): ArrayCollection
    {
        return $this->experiences;
    }

    public function addExperience(Experience $experience): self
    {
        if (!$this->experiences->contains($experience)) {
            $this->experiences[] = $experience;
            $experience->addSkill($this);
        }

        return $this;
    }

    public function removeExperience(Experience $experience): self
    {
        if ($this->experiences->contains($experience)) {
            $this->experiences->removeElement($experience);
            $experience->removeSkill($this);
        }

        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function getParentName(): ?string
    {
        return $this->parent?->getName();
    }

    public function setParent(?self $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return ArrayCollection<Skill>
     */
    public function getChildren(): ArrayCollection
    {
        return $this->children;
    }

    public function addChild(Skill $child): self
    {
        if (!$this->children->contains($child)) {
            $this->children[] = $child;
            $child->setParent($this);
        }

        return $this;
    }

    public function removeChild(Skill $child): self
    {
        if ($this->children->contains($child)) {
            $this->children->removeElement($child);
            // set the owning side to null (unless already changed)
            if ($child->getParent() === $this) {
                $child->setParent(null);
            }
        }

        return $this;
    }

    /**
     * @param Skill[] $parents
     * @return Skill[]
     */
    public function getAllParents(array $parents = []): array
    {
        if ($this->getParent()) {
            $parent = $this->getParent();

            if (!in_array($parent, $parents)) {
                $parents[] = $parent;

                return $parent->getAllParents($parents);
            }
        }

        return $parents;
    }
}
