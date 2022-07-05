<?php

namespace App\Entity;

use App\Repository\StatementRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @UniqueEntity(fields={"filename"}, message="Operation already exists")
 * @ORM\Entity(repositoryClass=StatementRepository::class)
 * @Vich\Uploadable
 */
class Statement
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private ?DateTimeInterface $date;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private ?string $filename;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?DateTimeInterface $updatedAt;

    /**
     * @Vich\UploadableField(mapping="statements", fileNameProperty="filename")
     */
    private File $file;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $operationsCount;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?DateTimeInterface $date): self
    {
        $this->date = $date;

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

    public function getOperationsCount(): ?int
    {
        return $this->operationsCount;
    }

    public function setOperationsCount(?int $operationsCount): self
    {
        $this->operationsCount = $operationsCount;

        return $this;
    }
}
