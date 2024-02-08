<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\WebAuthnKeyRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Symfony\Component\Uid\AbstractUid;
use Symfony\Component\Uid\Ulid;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\TrustPath\TrustPath;

#[Table()]
#[ORM\Entity(repositoryClass: WebAuthnKeyRepository::class)]
class WebAuthnKey extends PublicKeyCredentialSource
{
    #[Id]
    #[ORM\Column(type: "ulid", unique: true)]
    #[GeneratedValue(strategy: "NONE")]
    protected string $id;

    #[ORM\ManyToOne(inversedBy: 'keys')]
    #[ORM\JoinColumn(nullable: false)]
    private ?WebAuthnUser $user = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    public function __construct(
        string      $publicKeyCredentialId,
        string      $type,
        array       $transports,
        string      $attestationType,
        TrustPath   $trustPath,
        AbstractUid $aaguid,
        string      $credentialPublicKey,
        string      $userHandle,
        int         $counter
    )
    {
        $this->id = Ulid::generate();
        parent::__construct($publicKeyCredentialId, $type, $transports, $attestationType, $trustPath, $aaguid, $credentialPublicKey, $userHandle, $counter);
    }

    public function __toString(): string
    {
        return $this->getId();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getuser(): ?WebAuthnUser
    {
        return $this->user;
    }

    public function setuser(?WebAuthnUser $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }
}