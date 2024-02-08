<?php

namespace App\Service;

use App\Controller\Admin\WebAuthnKeyCrudController;
use App\Controller\Admin\WebAuthnUserCrudController;
use App\Entity\User;
use App\Entity\WebAuthnUser;
use App\Repository\WebAuthnUserRepository;
use Doctrine\ORM\NonUniqueResultException;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\SecurityBundle\Security;
use Webauthn\Exception\InvalidDataException;
use Webauthn\PublicKeyCredentialUserEntity;

class UserService
{
    public function __construct(
        private readonly Security               $security,
        private readonly AdminUrlGenerator      $adminUrlGenerator,
        private readonly WebAuthnUserRepository $userRepository
    )
    {

    }

    /**
     * @throws InvalidDataException
     * @throws NonUniqueResultException
     */
    public function getMetadata(): array
    {
        $user = $this->security->getUser();

        if ($user) {
            if ($user instanceof User) {
                $webAuthnUser = $this->userRepository->findOneByUsername($user->getEmail());

                if ($webAuthnUser) {
                    return $this->getWebauthnUserMetadata($webAuthnUser);
                } else {
                    return $this->getPasswordUserMetadata($user);
                }
            } elseif ($user instanceof WebAuthnUser) {
                return $this->getWebauthnUserMetadata($user);
            }
        }

        return [];
    }

    private function getWebauthnUserMetadata(WebAuthnUser|PublicKeyCredentialUserEntity $webAuthnUser): array
    {
        return [
            'user' => [
                'id' => $webAuthnUser->getId()
            ],
            'callbackUrl' => $this->adminUrlGenerator
                ->setController(WebAuthnKeyCrudController::class)
                ->generateUrl()
        ];
    }

    private function getPasswordUserMetadata(User $user): array
    {
        return [
            'user' => [
                'email' => $user->getEmail(),
                'username' => $user->getUsername(),
            ],
            'callbackUrl' => $this->adminUrlGenerator
                ->setController(WebAuthnUserCrudController::class)
                ->generateUrl()
        ];
    }
}