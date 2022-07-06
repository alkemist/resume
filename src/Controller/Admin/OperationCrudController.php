<?php

namespace App\Controller\Admin;

use App\Entity\Operation;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class OperationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Operation::class;
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
