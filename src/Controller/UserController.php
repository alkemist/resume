<?php

namespace App\Controller;

use App\Controller\Admin\DashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class UserController extends AbstractController
{
    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly Security          $security,
    )
    {
    }

    #[Route(path: '/login', name: 'login')]
    public function login(Request $request, AuthenticationUtils $authenticationUtils, FormFactoryInterface $formFactory): Response
    {
        $enableWebAuthn = $this->security->getFirewallConfig($request)->getProvider()
            === "security.user.provider.concrete.webauthn"
            || $request->query->get('test');;

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();
        $formFactory = $formFactory
            ->createNamedBuilder('login')
            ->setAction('/login')
            ->add('_username', null, ['label' => 'Username', 'attr' => ['autocomplete' => "webauthn username"]]);

        if (!$enableWebAuthn) {
            $formFactory
                ->add('_password', PasswordType::class, ['label' => 'Password']);
        }

        $formFactory->add('submit', SubmitType::class, [
            'label' => 'Connexion', 'attr' => ['class' => 'btn-primary btn-block',
                'click' => ''
            ]
        ]);

        $form = $formFactory
            ->getForm();

        return $this->render('security/login.html.twig', [
            'mainNavLogin' => true, 'title' => 'Connexion',
            'form' => $form->createView(),
            'last_username' => $lastUsername,
            'error' => $error,
            'emailField' => "login[_username]",
            'callbackUrl' => $this->adminUrlGenerator
                ->setController(DashboardController::class)
                ->generateUrl(),
            'enableWebAuthn' => $enableWebAuthn,
        ]);
    }

    #[Route(path: '/logout', name: 'logout')]
    public function logout()
    {

    }
}
