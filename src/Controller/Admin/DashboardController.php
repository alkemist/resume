<?php

namespace App\Controller\Admin;

use App\Entity\Attribute;
use App\Entity\Company;
use App\Entity\Declaration;
use App\Entity\Education;
use App\Entity\Experience;
use App\Entity\Hobby;
use App\Entity\Invoice;
use App\Entity\Link;
use App\Entity\Operation;
use App\Entity\OperationFilter;
use App\Entity\Person;
use App\Entity\Skill;
use App\Entity\Statement;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
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

    public function configureCrud(): Crud
    {
        return parent::configureCrud()
            ->setFormThemes(['fields/form.html.twig', '@EasyAdmin/crud/form_theme.html.twig'])
        ;
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToUrl('Return to website', 'fa fa-arrow-left', '/');
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-chart-bar');

        yield MenuItem::section('Invoicing');
        yield MenuItem::linkToCrud('Invoices', 'fa fa-coins', Invoice::class);
        yield MenuItem::linkToCrud('Declarations', 'fa fa-landmark', Declaration::class);
        yield MenuItem::linkToCrud('Companies', 'fa fa-building', Company::class);
        yield MenuItem::linkToCrud('Persons', 'fa fa-users', Person::class);

        yield MenuItem::section('Resume');
        yield MenuItem::linkToCrud('Experiences', 'fa fa-map-marker-alt', Experience::class);
        yield MenuItem::linkToCrud('Skills', 'fa fa-fill-drip', Skill::class);
        yield MenuItem::linkToCrud('Attributes', 'fa fa-address-card', Attribute::class);
        yield MenuItem::linkToCrud('Hobbies', 'fa fa-chess', Hobby::class);
        yield MenuItem::linkToCrud('Educations', 'fa fa-graduation-cap', Education::class);
        yield MenuItem::linkToCrud('Links', 'fa fa-link', Link::class);

        yield MenuItem::section('Other');

        yield MenuItem::section('Accounting');

        yield MenuItem::linkToCrud('Statements', 'fa fa-file-alt', Statement::class);
        yield MenuItem::linkToCrud('Operations', 'fa fa-columns', Operation::class);
        yield MenuItem::linkToCrud('Filters', 'fa fa-filter', OperationFilter::class);
    }

    public function configureActions(): Actions
    {
        return parent::configureActions()
            ->add(Crud::PAGE_NEW, Action::INDEX)
            ->add(Crud::PAGE_EDIT, Action::INDEX)
            ->add(Crud::PAGE_EDIT, Action::DELETE)
            ->disable(Action::BATCH_DELETE)
        ;
    }

    /**
     * @param User $user
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
            ->displayUserName()
            // use this method if you don't want to display the user image
            ->displayUserAvatar()
            // you can also pass an email address to use gravatar's service
            ->setGravatarEmail($user->getEmail())
        ;
    }
}
