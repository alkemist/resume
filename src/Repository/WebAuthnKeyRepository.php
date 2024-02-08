<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\WebAuthnKey;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Persisters\Entity\BasicEntityPersister;
use Doctrine\ORM\Persisters\Entity\EntityPersister;
use Doctrine\ORM\Persisters\Entity\JoinedSubclassPersister;
use Doctrine\ORM\Persisters\Entity\SingleTablePersister;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\Uid\Uuid;
use Webauthn\Bundle\Repository\DoctrineCredentialSourceRepository;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\PublicKeyCredentialUserEntity;

final class WebAuthnKeyRepository extends DoctrineCredentialSourceRepository implements ObjectRepository
{
    private WebAuthnUserRepository $userRepository;

    public function __construct(ManagerRegistry $registry, WebAuthnUserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
        parent::__construct($registry, WebAuthnKey::class);
    }

    /**
     * {@inheritdoc}
     */
    public function findAllForUserEntity(PublicKeyCredentialUserEntity $publicKeyCredentialUserEntity): array
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        return $qb->select('c')
            ->from($this->getClass(), 'c')
            ->where('c.userHandle = :userHandle')
            ->setParameter(':userHandle', $publicKeyCredentialUserEntity->getId())
            ->getQuery()
            ->execute();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findOneByCredentialId(string $publicKeyCredentialId): ?PublicKeyCredentialSource
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        return $qb->select('c')
            ->from($this->getClass(), 'c')
            ->where('c.publicKeyCredentialId = :publicKeyCredentialId')
            ->setParameter(':publicKeyCredentialId', base64_encode($publicKeyCredentialId))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function saveCredentialSource(
        PublicKeyCredentialSource $publicKeyCredentialSource, bool $flush = true
    ): void {
        $user = $this->userRepository->find($publicKeyCredentialSource->getUserHandle());

        if (!$publicKeyCredentialSource instanceof WebAuthnKey) {
            $publicKeyCredentialSource = new WebAuthnKey(
                $publicKeyCredentialSource->getPublicKeyCredentialId(),
                $publicKeyCredentialSource->getType(),
                $publicKeyCredentialSource->getTransports(),
                $publicKeyCredentialSource->getAttestationType(),
                $publicKeyCredentialSource->getTrustPath(),
                $publicKeyCredentialSource->getAaguid(),
                $publicKeyCredentialSource->getCredentialPublicKey(),
                $publicKeyCredentialSource->getUserHandle(),
                $publicKeyCredentialSource->getCounter()
            );
            $publicKeyCredentialSource->setUser($user);
        }
        parent::saveCredentialSource($publicKeyCredentialSource, $flush);
    }

    /**
     * @throws NonUniqueResultException
     */
    public function find($id, $lockMode = null, $lockVersion = null): null|object
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        return $qb->select('c')
            ->from($this->_entityName, 'c')
            ->where('c.id = :id')
            ->setParameter(':id', Uuid::fromBase32($id))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAll(): array
    {
        return $this->findBy([]);
    }

    public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null): array
    {
        return $this->getPersister()->loadAll($criteria, $orderBy, $limit, $offset);
    }

    private function getPersister(): JoinedSubclassPersister|SingleTablePersister|EntityPersister|BasicEntityPersister
    {
        return $this->getEntityManager()->getUnitOfWork()->getEntityPersister($this->getClassName());
    }

    public function getClassName(): string
    {
        return WebAuthnKey::class;
    }

    public function findOneBy(array $criteria, ?array $orderBy = null): null|object
    {
        return $this->getPersister()->load($criteria, null, null, [], null, 1, $orderBy);
    }
}