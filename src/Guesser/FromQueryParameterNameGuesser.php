<?php

declare(strict_types=1);

namespace App\Guesser;

use App\Repository\WebAuthnUserRepository;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpFoundation\Request;
use Webauthn\Bundle\Security\Guesser\UserEntityGuesser;
use Webauthn\Exception\InvalidDataException;
use Webauthn\PublicKeyCredentialUserEntity;

final class FromQueryParameterNameGuesser implements UserEntityGuesser
{
    public function __construct(
        private readonly WebAuthnUserRepository $userEntityRepository
    )
    {
    }

    /**
     * @throws InvalidDataException
     * @throws NonUniqueResultException
     */
    public function findUserEntity(Request $request): PublicKeyCredentialUserEntity
    {
        $name = $request->attributes->get('user_name');
        return $this->userEntityRepository->findOneByUsername($name);
    }
}