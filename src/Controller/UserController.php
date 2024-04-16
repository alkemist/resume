<?php

namespace App\Controller;

use App\Security\StoreAuthenticator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    public function __construct()
    {
    }

//    #[Route(path: '/login', name: 'login')]
//    public function login(Request $request, AuthenticationUtils $authenticationUtils, FormFactoryInterface $formFactory
//    ): Response {
//
//        $error = $authenticationUtils->getLastAuthenticationError();
//        $lastUsername = $authenticationUtils->getLastUsername();
//        $formFactory = $formFactory
//            ->createNamedBuilder('login')
//            ->setAction('/login')
//            ->add('_username', null, ['label' => 'Username']);
//
//        $formFactory
//            ->add('_password', PasswordType::class, ['label' => 'Password']);
//
//        $formFactory->add('submit', SubmitType::class, [
//            'label' => 'Connexion', 'attr' => ['class' => 'btn-primary btn-block',
//                                               'click' => ''
//            ]
//        ]);
//
//        $form = $formFactory
//            ->getForm();
//
//        return $this->render('security/login.html.twig', [
//            'mainNavLogin'  => true, 'title' => 'Connexion',
//            'form'          => $form->createView(),
//            'last_username' => $lastUsername,
//            'error'         => $error,
//            'emailField'    => "login[_username]",
//        ]);
//    }

    #[Route(path: '/logout', name: 'logout')]
    public function logout()
    {

    }

    #[Route(path: '/logged', name: 'logged')]
    public function logged(Request $request)
    {
        $token = $request->query->get('code');
        $request->getSession()->set(StoreAuthenticator::SESSION_AUTH_KEY, $token);
        return $this->redirectToRoute('admin_dashboard');
    }
}
