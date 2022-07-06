<?php

namespace App\Controller\Admin;

use App\Entity\Invoice;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        $data = [];
        return $this->render('admin/dashboard.html.twig', $data);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Resume')
            ->setTranslationDomain('messages')
            ->renderSidebarMinimized()
            ->renderContentMaximized()
        ;
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToUrl('Return to website', 'fa fa-arrow-left', '/');
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-chart-bar');

        yield MenuItem::section('Invoicing');
        yield MenuItem::linkToCrud('Invoices', 'fa fa-coins', Invoice::class);

        /*yield MenuItem::subMenu('Blog', 'fa fa-article')->setSubItems([
            MenuItem::linkToCrud('Categories', 'fa fa-tags', Category::class),
            MenuItem::linkToCrud('Posts', 'fa fa-file-text', BlogPost::class),
            MenuItem::linkToCrud('Comments', 'fa fa-comment', Comment::class),
        ]);
        yield MenuItem::linkToCrud('Add Category', 'fa fa-tags', Category::class)
            ->setAction('new');*/
            //;
    }

    /**
     * @param User $user
     * @return UserMenu
     */
    public function configureUserMenu(UserInterface $user): UserMenu
    {
        // Usually it's better to call the parent method because that gives you a
        // user menu with some menu items already created ("sign out", "exit impersonation", etc.)
        // if you prefer to create the user menu from scratch, use: return UserMenu::new()->...
        return parent::configureUserMenu($user)
            // use the given $user object to get the user name
            ->setName($user->getUserIdentifier())
            // use this method if you don't want to display the name of the user
            ->displayUserName(true)
            // use this method if you don't want to display the user image
            ->displayUserAvatar(true)
            // you can also pass an email address to use gravatar's service
            ->setGravatarEmail($user->getEmail())
        ;
    }
}
