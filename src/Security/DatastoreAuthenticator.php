<?php
// src/Security/ApiKeyAuthenticator.php
namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class DatastoreAuthenticator extends AbstractAuthenticator
{
    const SESSION_AUTH_KEY = 'token';

    public function __construct(
        private readonly HttpClientInterface       $client,
        private readonly UrlGeneratorInterface     $router,
        private readonly UserRepository            $userRepository,
        private readonly CsrfTokenManagerInterface $csrfTokenManager,
        private readonly TokenGeneratorInterface   $tokenGenerator,
        readonly string                            $storeBaseUrl
    ) {

    }

    /**
     * Called on every request to decide if this authenticator should be
     * used for the request. Returning `false` will cause this authenticator
     * to be skipped.
     */
    public function supports(Request $request): ?bool
    {
        return str_starts_with($request->attributes->get('_route'), 'admin');
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function authenticate(Request $request): Passport
    {
        $token = $request->getSession()->get(self::SESSION_AUTH_KEY);

        $response = $this->client->withOptions(
            [
                'base_uri' => $this->storeBaseUrl,
                'headers'  => [
                    'X-AUTH-TOKEN'   => $token,
                    'X-AUTH-PROJECT' => 'resume'
                ]
            ]
        )->request(
            'GET',
            "/api/profile/resume"
        );

        $statusCode = $response->getStatusCode();

        if ($statusCode === 200) {

            /** @var {string response, string item} $content */
            $content = $response->toArray();

            if ($content['item']) {
                /*$user = $this->userRepository->findOneBy(
                    ['email' => $content['item']['email']]
                );*/

                $user = new User(1);
                $user->setEmail($content['item']['email']);
                $user->setUsername($content['item']['username']);
                $user->setRoles(['ROLE_ADMIN']);

                return new SelfValidatingPassport(
                    new UserBadge(
                        $user->getEmail(),
                        function () use ($user) {
                            return $user;
                        }
                    ),
                    [
                        new CsrfTokenBadge(
                            'authenticate',
                            $this->csrfTokenManager->refreshToken('authenticate')
                        )
                    ]
                );
            }
        }

        throw new CustomUserMessageAuthenticationException(
            'No API token provided',
            [],
            $statusCode
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // on success, let the request continue
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        if ($exception->getCode() === 200) {
            $request->getSession()->set(self::SESSION_AUTH_KEY, '');
            $callback = $this->router->generate(
                'logged',
                [], UrlGeneratorInterface::ABSOLUTE_URL
            );

            return new RedirectResponse(
                "{$this->storeBaseUrl}/login?callback={$callback}"
            );
        }

        dump($exception);
        die;

        return new RedirectResponse(
            $this->router->generate(
                'app_index',
                [
                    'error' => "Unauthorized project"
                ], UrlGeneratorInterface::ABSOLUTE_URL
            ),
        );
    }
}