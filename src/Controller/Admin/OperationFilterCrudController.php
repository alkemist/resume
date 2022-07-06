<?php

namespace App\Controller\Admin;

use App\Entity\OperationFilter;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class OperationFilterCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return OperationFilter::class;
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
