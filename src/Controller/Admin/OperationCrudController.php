<?php

namespace App\Controller\Admin;

use App\Entity\Operation;
use App\Enum\OperationTypeEnum;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\EnumType;

class OperationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Operation::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Operation')
            ->setEntityLabelInPlural('Operations')
            ->setDefaultSort(['date' => 'DESC'])
            ->setSearchFields(['date', 'type', 'label', 'target', 'location', 'amount'])
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        if (Crud::PAGE_INDEX === $pageName) {
            yield DateField::new('date');
            yield ChoiceField::new('type')
                ->setTranslatableChoices([...OperationTypeEnum::choices()]);

            yield TextField::new('label');
            yield TextField::new('target');
            yield TextField::new('name');
            yield TextField::new('location');
            yield MoneyField::new('amount')->setCurrency('EUR')->setStoredAsCents(false)->setNumDecimals(2);

        } elseif (Crud::PAGE_EDIT === $pageName || Crud::PAGE_NEW === $pageName) {
            yield FormField::addPanel('Operation');
            yield DateField::new('date')->setColumns(2);
            yield TextField::new('name')->setColumns(8);
            yield MoneyField::new('amount')->setColumns(2)->setCurrency('EUR')->setStoredAsCents(false)->setNumDecimals(2);

            yield FormField::addPanel('Categories');
            yield ChoiceField::new('type')->setColumns(2)
                ->setChoices([...OperationTypeEnum::cases()])
                ->setFormType(EnumType::class)
                ->setFormTypeOptions(['class' => OperationTypeEnum::class]);
            yield TextField::new('label')->setColumns(2);

            yield FormField::addPanel('Informations');
            yield TextField::new('target')->setColumns(2);
            yield TextField::new('location')->setColumns(2);
        }
    }
}
