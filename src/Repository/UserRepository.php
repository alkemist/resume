<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Ulid;
use Webauthn\Exception\InvalidDataException;
use Webauthn\PublicKeyCredentialUserEntity;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements UserLoaderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
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
     */
    public function saveUserEntity(PublicKeyCredentialUserEntity $userEntity): void
    {
        dump($userEntity);
        /** @var User|null $user */
        $user = $this->findOneBy([
            'username' => $userEntity->getName(),
        ]);
        if ($user instanceof UserInterface) {
            return;
        }
        return;
        /*$user = new User(
            $userEntity->getId(),
            $userEntity->getUserIdentifier(),
            $userEntity->getDisplayName() // Or any other similar method your entity may have
        );
        $this->userRepository->save($user); // Custom method to be added in your repository*/
    }

    /**
     * @throws InvalidDataException
     * @throws NonUniqueResultException
     */
    public function findOneByUsername(string $identifier): ?PublicKeyCredentialUserEntity
    {
        return null;
        /** @var User|null $user */
        /*$user = $this->loadUserByIdentifier($identifier);

        return $this->getUserEntity($user);*/
    }

    /**
     * @throws NonUniqueResultException
     */
    public function loadUserByIdentifier(string $identifier): ?User
    {
        $entityManager = $this->getEntityManager();

        return $entityManager->createQuery(
            'SELECT u
                FROM App\Entity\User u
                WHERE u.username = :query
                OR u.email = :query'
        )
            ->setParameter('query', $identifier)
            ->getOneOrNullResult();
    }

    /**
     * @throws InvalidDataException
     */
    public function findOneByUserHandle(string $userHandle): ?PublicKeyCredentialUserEntity
    {
        /** @var User|null $user */
        $user = $this->findOneBy([
            'id' => $userHandle,
        ]);

        return $this->getUserEntity($user);
    }

    /**
     * Converts a Symfony User (if any) into a Webauthn User Entity
     * @throws InvalidDataException
     */
    private function getUserEntity(null|User $user): ?PublicKeyCredentialUserEntity
    {
        if ($user === null) {
            return null;
        }

        return new PublicKeyCredentialUserEntity(
            $user->getUsername(),
            $user->getUserIdentifier(),
            $user->__toString(),
            null
        );
    }
}
