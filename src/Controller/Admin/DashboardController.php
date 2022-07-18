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
use App\Repository\ExperienceRepository;
use App\Repository\InvoiceRepository;
use App\Service\DeclarationService;
use DateTime;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Ds\Map;
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
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;
use function Symfony\Component\Translation\t;

class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private ChartBuilderInterface $chartBuilder,
        private InvoiceRepository     $invoiceRepository,
        private ExperienceRepository  $experienceRepository,
        private DeclarationService    $declarationService,
    ) {
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    #[Route('/admin/{year<\d+>?0}/{quarter<\d+>?0}', name: 'dashboard')]
    public function index(int $year = 0, int $quarter = 0): Response
    {
        $viewData = [];

        $viewData['currentYear'] = intval((new DateTime())->format('Y'));
        $viewData['currentQuarter'] = ceil((new DateTime())->format('n') / 3);
        $viewData['activeYear'] = $year ? $year : $viewData['currentYear'];
        $viewData['activeQuarter'] = $quarter ? $quarter : $viewData['currentQuarter'];
        $viewData['years'] = array_filter($this->invoiceRepository->findYears());

        if (!in_array($viewData['currentYear'], $viewData['years'])) {
            $viewData['years'][] = $viewData['currentYear'];
        }

        $viewData['activeRevenuesOnYear'] = $this->invoiceRepository->getSalesRevenuesBy($viewData['activeYear']);
        $viewData['activeRevenuesOnQuarter'] = $this->invoiceRepository->getSalesRevenuesBy(
            $viewData['activeYear'], $viewData['activeQuarter']
        );
        $viewData['currentRevenuesOnYear'] = $this->invoiceRepository->getSalesRevenuesBy($viewData['currentYear']);
        $viewData['currentRevenuesOnQuarter'] = $this->invoiceRepository->getSalesRevenuesBy(
            $viewData['currentYear'], $viewData['currentQuarter']
        );
        $viewData['dayCount'] = $this->invoiceRepository->getDaysCountByYear($viewData['activeYear']);

        $viewData['remainingDaysBeforeTaxLimit'] = $this->invoiceRepository->remainingDaysBeforeTaxLimit();
        $viewData['remainingDaysBeforeLimit'] = $this->invoiceRepository->remainingDaysBeforeLimit();
        $viewData['currentTaxesOnQuarter'] = $this->invoiceRepository->getSalesTaxesBy(
            $viewData['activeYear'], $viewData['activeQuarter']
        );

        $revenuesByYears = $this->invoiceRepository->getSalesRevenuesGroupBy('year');
        $viewData['revenuesByYears'] = array_combine(
            array_map(function ($item) {
                return $item['year'];
            }, $revenuesByYears),
            array_map(function ($item) {
                return intval($item['total']);
            }, $revenuesByYears)
        );

        $revenuesByQuarters = $this->invoiceRepository->getSalesRevenuesGroupBy(
            'quarter', $viewData['activeYear'], null, true
        );
        $viewData['revenuesByQuarters'] = array_combine(
            array_map(function ($item) {
                return 'T' . $item['quarter'];
            }, $revenuesByQuarters),
            array_map(function ($item) {
                return intval($item['total']);
            }, $revenuesByQuarters)
        );

        $daysByMonthMap = new Map();
        $daysByMonth = $this->invoiceRepository->getDaysCountByMonth($viewData['activeYear']);
        $daysByMonthAssociative = [];
        foreach ($daysByMonth as $item) {
            $daysByMonthAssociative[intval($item['month'])] = $item['total'];
        }
        for ($i = 1; $i <= 12; $i++) {
            $monthName = t(date('F', mktime(0, 0, 0, $i, 10)));
            $daysByMonthMap->put($monthName, isset($daysByMonthAssociative[$i]) ?? 0);
        }
        $viewData['daysByMonth'] = $daysByMonthMap;

        $viewData['colorsByYears'] = [];
        foreach ($viewData['years'] as $year) {
            $viewData['colorsByYears'][] = $year == $viewData['activeYear'] ? 'rgba(56, 142, 60, 0.6)' : 'rgba(56, 142, 60, 0.3)';
        }

        $viewData['colorsByQuarters'] = [];
        foreach ($viewData['revenuesByQuarters'] as $quarter => $item) {
            if (isset($quarter[1])) {
                $viewData['colorsByQuarters'][] = $quarter[1] == $viewData['activeQuarter'] ? 'rgba(56, 142, 60, 0.6)' : 'rgba(0, 0, 0, 0.1)';
            }
        }

        $viewData['unpayedInvoices'] = $this->invoiceRepository->findInvoicesBy(null, null, false);
        $viewData['currentExperiences'] = $this->experienceRepository->getCurrents();
        $viewData['nextDueDate'] = $this->declarationService->getNextDueDate();

        $viewData['globalByYears'] = [];
        foreach ($viewData['years'] as $year) {
            $totalSocial = $this->declarationService->declarationTypeSocial->getTotalByYear($year);
            $totalCfe = $this->declarationService->declarationTypeCfe->getTotalByYear($year);
            $totalTva = $this->declarationService->declarationTypeTva->getTotalByYear($year);
            $totalImpot = $this->declarationService->declarationTypeImpot->getTotalByYear($year);
            $totalSales = $this->invoiceRepository->getSalesRevenuesBy($year);
            $daysByMonth = $this->invoiceRepository->getDaysCountByYear($year);
            $net = $totalSales - $totalSocial - $totalImpot - $totalCfe;

            $viewData['globalByYears'][] = [
                'year'       => $year,
                'social'     => round($totalSocial),
                'cfe'        => round($totalCfe),
                'tva'        => round($totalTva),
                'impot'      => round($totalImpot),
                'ht'         => round($totalSales),
                'net'        => round($net),
                'days'       => $daysByMonth,
                'percent'    => round($daysByMonth * 100 / (20 * 12)),
                'netByMonth' => round($net / 12),
            ];
        }

        $chartRevenuesByYears = $this->chartBuilder->createChart(Chart::TYPE_BAR);
        $chartRevenuesByYears->setData([
                                           'labels'   => array_keys($viewData['revenuesByYears']),
                                           'datasets' => [
                                               [
                                                   'label'           => 'CA (€)',
                                                   'backgroundColor' => array_values($viewData['colorsByYears']),
                                                   'data'            => array_values($viewData['revenuesByYears']),
                                               ],
                                           ],
                                       ]);
        $viewData['chartRevenuesByYears'] = $chartRevenuesByYears;

        $chartRevenuesByQuarters = $this->chartBuilder->createChart(Chart::TYPE_BAR);
        $chartRevenuesByQuarters->setData([
                                              'labels'   => array_keys($viewData['revenuesByQuarters']),
                                              'datasets' => [
                                                  [
                                                      'label'           => 'CA (€)',
                                                      'backgroundColor' => array_values($viewData['colorsByQuarters']),
                                                      'data'            => array_values(
                                                          $viewData['revenuesByQuarters']
                                                      ),
                                                  ],
                                              ],
                                          ]);
        $viewData['chartRevenuesByQuarters'] = $chartRevenuesByQuarters;

        $chartDaysByMonth = $this->chartBuilder->createChart(Chart::TYPE_BAR);
        $chartDaysByMonth->setData([
                                       'labels'   => $viewData['daysByMonth']->keys(),
                                       'datasets' => [
                                           [
                                               'label' => 'CA (€)',
                                               'data'  => $viewData['daysByMonth']->values(),
                                           ],
                                       ],
                                   ]);
        $viewData['chartDaysByMonth'] = $chartDaysByMonth;

        return $this->render('admin/dashboard.html.twig', $viewData);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Resume')
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

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToUrl('Return to website', 'fa fa-arrow-left', '/');
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-chart-bar');

        yield MenuItem::section('Invoicing');
        yield MenuItem::linkToCrud('Invoices', 'fa fa-coins', Invoice::class);//->setBadge('test badge');
        yield MenuItem::linkToRoute('Invoices book', 'fa fa-coins', 'invoices_csv');
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
            ->add(Crud::PAGE_INDEX, $actionDelete)
            ->disable(Action::BATCH_DELETE);
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
            ->setGravatarEmail($user->getEmail());
    }
}
