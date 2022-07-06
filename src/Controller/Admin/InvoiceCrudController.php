<?php

namespace App\Controller\Admin;

use App\Entity\Invoice;
use App\Enum\InvoicePaymentTypeEnum;
use App\Enum\InvoiceStatusEnum;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\EnumType;

class InvoiceCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Invoice::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Invoice')
            ->setEntityLabelInPlural('Invoices')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setSearchFields(['company.name', 'period.year'])
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        $validateAction = Action::new('validate', 'Validate', 'fa fa-check')
            ->linkToCrudAction('validate')
            ->displayIf(fn (Invoice $invoice) => $invoice->getStatus() === InvoiceStatusEnum::Waiting)
            ->addCssClass('btn-sm btn-success');

        $actions
            ->add(Crud::PAGE_INDEX, $validateAction);

        return $actions;
    }

    public function configureFields(string $pageName): iterable
    {

        if (Crud::PAGE_INDEX === $pageName) {
            yield TextField::new('number');
            yield AssociationField::new('company');
            yield TextField::new('experienceCompany', 'Client');
            yield MoneyField::new('totalHt')->setCurrency('EUR')->setStoredAsCents(false)->setNumDecimals(0);
            yield MoneyField::new('totalNet')->setCurrency('EUR')->setStoredAsCents(false);
            yield MoneyField::new('totalHt')->setCurrency('EUR')->setStoredAsCents(false)->setNumDecimals(0);
            yield MoneyField::new('totalTax')->setCurrency('EUR')->setStoredAsCents(false)->setNumDecimals(0);
            yield NumberField::new('daysCount')->setNumDecimals(1);
            yield DateField::new('createdAt');
            yield DateField::new('payedAt');
            yield TextField::new('periodName', 'Period');
            yield TextField::new('socialDeclaration', 'Declaration');

        } else if (Crud::PAGE_EDIT === $pageName || Crud::PAGE_NEW === $pageName) {
            yield FormField::addPanel('Informations');
            yield TextField::new('number')->setColumns(2);
            yield TextField::new('reference')->setColumns(2);
            yield TextField::new('object')->setColumns(4);
            yield AssociationField::new('company')->setColumns(2)->autocomplete();
            yield AssociationField::new('experience')->setColumns(2)->autocomplete();

            yield FormField::addPanel('Invoicing');
            yield NumberField::new('daysCount')->setNumDecimals(1)->setColumns(1);
            yield MoneyField::new('tjm')->setColumns(1)->setCurrency('EUR')->setStoredAsCents(false)->setNumDecimals(0);
            yield TextField::new('extraLibelle')->setColumns(2);
            yield MoneyField::new('extraHt')->setColumns(2)->setCurrency('EUR')->setStoredAsCents(false)->setNumDecimals(0);
            yield MoneyField::new('totalHt')->setColumns(2)->setCurrency('EUR')->setStoredAsCents(false)->setNumDecimals(0);
            yield MoneyField::new('totalTax')->setColumns(2)->setCurrency('EUR')->setStoredAsCents(false)->setNumDecimals(0);

            yield FormField::addPanel('Status');
            yield DateField::new('createdAt')->setColumns(2);
            yield ChoiceField::new('payedBy')->setColumns(2)
                ->setChoices([...InvoicePaymentTypeEnum::cases()])
                ->setRequired(false)
                ->setFormType(EnumType::class)
                ->setFormTypeOptions(['class' => InvoicePaymentTypeEnum::class]);
            yield DateField::new('payedAt')->setColumns(2);
            yield ChoiceField::new('status')->setColumns(2)
                ->setChoices([...InvoiceStatusEnum::cases()])
                ->setFormType(EnumType::class)
                ->setFormTypeOptions(['class' => InvoiceStatusEnum::class]);
            yield AssociationField::new('period')->setColumns(2)->autocomplete();

            yield FormField::addPanel('Days');
            yield ArrayField::new('activities')
                ->setDisabled()
                ->setLabel('')
                ->setFormTypeOption('allow_add', false)
                ->setFormTypeOption('allow_delete', false)
                ->setColumns(6)
            ;
        }
    }
}
