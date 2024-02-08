<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use App\Entity\WebAuthnUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Ulid;
use Webauthn\Bundle\Repository\CanRegisterUserEntity;
use Webauthn\Bundle\Repository\PublicKeyCredentialUserEntityRepositoryInterface;
use Webauthn\Exception\InvalidDataException;
use Webauthn\PublicKeyCredentialUserEntity;

/**
 * @method WebAuthnUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method WebAuthnUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method WebAuthnUser[]    findAll()
 * @method WebAuthnUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class WebAuthnUserRepository extends ServiceEntityRepository implements CanRegisterUserEntity, PublicKeyCredentialUserEntityRepositoryInterface
{
    /**
     * The UserRepository $userRepository is the repository
     * that already exists in the application
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WebAuthnUser::class);
    }

    /**
     * This method creates the next Webauthn User Entity ID
     * In this example, we use Ulid
     */
    public function generateNextUserEntityId(): string
    {
        return Ulid::generate();
    }

    /**
     * This method saves the user or does nothing if the user already exists.
     * It may throw an exception. Just adapt it on your needs
     * @throws InvalidDataException
     */
    public function saveUserEntity(PublicKeyCredentialUserEntity $userEntity): void
    {
        /** @var User|null $user */
        $user = $this->findOneBy([
            'id' => $userEntity->getId(),
        ]);
        if ($user) {
            return;
        }
        $user = new WebAuthnUser(
            $userEntity->getId(),
            $userEntity->getName(),
            $userEntity->getDisplayName(),
            $userEntity->getIcon(),
        );
        $this->save($user); // Custom method to be added in your repository*/
    }

    private function save(WebAuthnUser $publicKeyCredentialUser): void
    {
        $this->getEntityManager()->persist($publicKeyCredentialUser);
        $this->getEntityManager()->flush();
    }

    /**
     * @throws InvalidDataException
     * @throws NonUniqueResultException
     */
    public function findOneByUsername(string $username): ?PublicKeyCredentialUserEntity
    {
        //return null;
        $user = $this->findOneBy([
            'name' => $username,
        ]);

        return $this->getUserEntity($user);
    }

    /**
     * Converts a Symfony User (if any) into a Webauthn User Entity
     * @throws InvalidDataException
     */
    private function getUserEntity(null|WebAuthnUser $user): ?PublicKeyCredentialUserEntity
    {
        if ($user === null) {
            return null;
        }

        return new PublicKeyCredentialUserEntity(
            $user->getName(),
            $user->getId(),
            $user->getDisplayName(),
            $user->getIcon()
        );
    }

    /**
     * @throws InvalidDataException
     */
    public function findOneByUserHandle(string $userHandle): ?PublicKeyCredentialUserEntity
    {
        $user = $this->findOneBy([
            'id' => $userHandle,
        ]);

        return $this->getUserEntity($user);
    }
}
