<?php

namespace App\Controller\Admin;

use App\Entity\Statement;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use Vich\UploaderBundle\Form\Type\VichImageType;

class StatementCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Statement::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Statement')
            ->setEntityLabelInPlural('Statements')
            ->setDefaultSort(['date' => 'DESC'])
            ->setSearchFields(['date'])
            ;
    }

    public function configureActions(Actions $actions): Actions
    {
        $ocrAction = Action::new('ocr', 'Ocr', 'fa fa-eye')
            ->linkToCrudAction('ocr')
            ->addCssClass('btn-sm btn-success');

        $actions
            ->add(Crud::PAGE_INDEX, $ocrAction)
        ;

        return $actions;
    }

    public function configureFields(string $pageName): iterable
    {
        if (Crud::PAGE_INDEX === $pageName || Crud::PAGE_EDIT === $pageName) {
            yield DateField::new('date');
        }
        if (Crud::PAGE_EDIT === $pageName || Crud::PAGE_NEW === $pageName) {
            yield TextareaField::new('file')->setFormType(VichImageType::class);
        }
        if (Crud::PAGE_INDEX === $pageName) {
            yield NumberField::new('operationsCount', 'Operations');
        }
    }
}
