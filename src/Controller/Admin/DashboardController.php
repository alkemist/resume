<?php

namespace App\Controller\Admin;

use App\Entity\Attribute;
use App\Entity\Company;
use App\Entity\Consumption;
use App\Entity\ConsumptionMonth;
use App\Entity\ConsumptionStatement;
use App\Entity\Declaration;
use App\Entity\Education;
use App\Entity\Experience;
use App\Entity\Hobby;
use App\Entity\Invoice;
use App\Entity\Link;
use App\Entity\Operation;
use App\Entity\OperationFilter;
use App\Entity\Person;
use App\Entity\Project;
use App\Entity\Skill;
use App\Entity\Statement;
use App\Entity\User;
use App\Form\Type\MonthActivitiesType;
use App\Service\AccountingService;
use App\Service\ConsumptionService;
use App\Service\DashboardService;
use App\Service\InvoiceService;
use App\Service\ReportService;
use App\Service\StatementService;
use DateTime;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private readonly DashboardService   $dashboardService,
        private readonly ReportService      $reportService,
        private readonly AccountingService  $accountingService,
        private readonly StatementService   $statementService,
        private readonly InvoiceService     $invoiceService,
        private readonly ConsumptionService $consumptionService,
    ) {
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    #[Route('/admin/{year<\d+>?0}', name: 'admin_dashboard')]
    public function dashboard(int $year = 0): Response
    {
        $viewData = $this->dashboardService->getDashboard($year);

        return $this->render('admin/dashboard.html.twig', $viewData);
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     * @throws Exception
     */
    #[Route('/admin/report/{year<\d+>?0}/{month<\d+>?0}/{slug?}', name: 'admin_report')]
    public function report(Request $request, int $year = 0, int $month = 0, Company $company = null): Response
    {
        $viewData = [];
        $viewData['currentYear'] = (new DateTime())->format('Y');
        $viewData['activeYear'] = intval($year ?: $viewData['currentYear']);
        $viewData['activeMonth'] = intval($month ?: (new DateTime())->format('m'));

        $currentDate = new DateTime(
            $viewData['activeYear'] . ($viewData['activeMonth'] < 10 ? '0' : '') . $viewData['activeMonth'] . '01'
        );
        $viewData = $this->reportService->getDashboard($viewData, clone $currentDate, $year, $month, $company);

        $form = $this->createForm(MonthActivitiesType::class, null, [
            'activities'  => $viewData['companyActivities'],
            'currentDate' => clone $currentDate,
            'company'     => $viewData['activeCompany']
        ]);
        $form->handleRequest($request);
        $viewData['reportForm'] = $form->createView();

        if ($form->isSubmitted() && $form->isValid()) {
            $this->reportService->sendActivities($form->getData(), $currentDate);
            return $this->redirectToRoute(
                'admin_report',
                [
                    'year'  => $viewData['activeYear'],
                    'month' => $viewData['activeMonth'],
                    'slug'  => $viewData['activeCompany'] ? $viewData['activeCompany']->getSlug() : ''
                ]
            );
        }

        return $this->render('admin/report.html.twig', $viewData);
    }

    /**
     * @throws Exception
     */
    #[Route('/admin/accounting/{year<\d+>?0}/{month<\d+>?0}/{type<\w+>?}', name: 'admin_accounting')]
    public function accouting(int $year = 0, int $month = 0, $type = ''): Response
    {
        $viewData = $this->accountingService->getDashboard($year, $month, $type);
        return $this->render('admin/accounting.html.twig', $viewData);
    }

    /**
     * @throws Exception
     */
    #[Route('/admin/consumption/{year<\d+>?0}/{month<\d+>?0}/{type<\w+>?}', name: 'admin_consumption')]
    public function consumption(int $year = 0, int $month = 0, $type = ''): Response
    {
        $viewData = $this->consumptionService->getDashboard($year, $month, $type);
        return $this->render('admin/consumption.html.twig', $viewData);
    }

    #[Route('/admin/saving/{year<\d+>?0}', name: 'admin_saving')]
    public function saving(int $year = 0): Response
    {
        $viewData = $this->statementService->getDashboard($year);
        return $this->render('admin/saving.html.twig', $viewData);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Admin')
            ->setTranslationDomain('messages')
            ->renderSidebarMinimized()
            ->renderContentMaximized();
    }

    public function configureCrud(): Crud
    {
        return parent::configureCrud()
            ->setFormThemes(['fields/form.html.twig', '@EasyAdmin/crud/form_theme.html.twig'])
            ->showEntityActionsInlined();
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function configureMenuItems(): iterable
    {
        $countWaitingInvoices = $this->invoiceService->countWaitingInvoices();
        $countNullTypes = $this->accountingService->getNullTypesCount();
        $countNoOcr = $this->statementService->getNoOcrCount();

        yield MenuItem::linkToUrl('Return to website', 'fa fa-arrow-left', '/');

        yield MenuItem::section('Invoicing');
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-chart-bar');
        yield MenuItem::linkToRoute('Report', 'fa fa-calendar-alt', 'admin_report');
        yield MenuItem::linkToCrud('Invoices', 'fa fa-coins', Invoice::class)
            ->setBadge($countWaitingInvoices > 0 ? $countWaitingInvoices : '');
        yield MenuItem::linkToCrud('Declarations', 'fa fa-landmark', Declaration::class);
        yield MenuItem::linkToCrud('Companies', 'fa fa-building', Company::class);
        yield MenuItem::linkToCrud('Persons', 'fa fa-users', Person::class);

        yield MenuItem::section('Resume');
        yield MenuItem::linkToCrud('Experiences', 'fa fa-map-marker-alt', Experience::class);
        yield MenuItem::linkToCrud('Skills', 'fa fa-fill-drip', Skill::class);
        yield MenuItem::linkToCrud('Attributes', 'fa fa-address-card', Attribute::class);
        yield MenuItem::linkToCrud('Projects', 'fa fa-screwdriver-wrench', Project::class);
        yield MenuItem::linkToCrud('Hobbies', 'fa fa-chess', Hobby::class);
        yield MenuItem::linkToCrud('Educations', 'fa fa-graduation-cap', Education::class);
        yield MenuItem::linkToCrud('Links', 'fa fa-link', Link::class);

        yield MenuItem::section('Accounting');

        yield MenuItem::linkToRoute('Dashboard', 'fa fa-chart-pie', 'admin_accounting');
        yield MenuItem::linkToRoute('Saving', 'fa fa-vault', 'admin_saving');
        yield MenuItem::linkToCrud('Statements', 'fa fa-file-alt', Statement::class)
            ->setBadge($countNoOcr > 0 ? $countNoOcr : '');
        yield MenuItem::linkToCrud('Operations', 'fa fa-columns', Operation::class)
            ->setBadge($countNullTypes > 0 ? $countNullTypes : '');
        yield MenuItem::linkToCrud('Filters', 'fa fa-filter', OperationFilter::class);

        yield MenuItem::section('Consumption');

        yield MenuItem::linkToRoute('Dashboard', 'fa fa-chart-pie', 'admin_consumption');
        yield MenuItem::linkToCrud('ConsumptionStatements', 'fa fa-file', ConsumptionStatement::class);
        yield MenuItem::linkToCrud('ConsumptionMonths', 'fa fa-calendar-days', ConsumptionMonth::class);
        yield MenuItem::linkToCrud('Consumptions', 'fa fa-bolt', Consumption::class);

        yield MenuItem::section('Authentication');
        yield MenuItem::linkToCrud('Users', 'fa fa-user', User::class);
    }

    public function configureActions(): Actions
    {
        $editAction = Action::new(Action::EDIT, 'Edit', 'fa fa-pencil')
            ->linkToCrudAction(Action::EDIT);

        $actionDelete = Action::new(Action::DELETE, 'Delete', 'fa fa-trash-can')
            ->linkToCrudAction(Action::DELETE);

        return parent::configureActions()
            ->add(Crud::PAGE_NEW, Action::INDEX)
            ->add(Crud::PAGE_EDIT, Action::INDEX)
            ->add(Crud::PAGE_EDIT, Action::DELETE)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->add(Crud::PAGE_INDEX, $editAction)
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->add(Crud::PAGE_INDEX, $actionDelete);
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
            ->setName($user->getUsername())
            // use this method if you don't want to display the name of the user
            ->displayUserName()
            // use this method if you don't want to display the user image
            ->displayUserAvatar()
            // you can also pass an email address to use gravatar's service
            ->setGravatarEmail($user->getEmail());
    }

    public function configureAssets(): Assets
    {
        return Assets::new()
            ->addCssFile('build/css/admin.css');
    }
}
