<?php

namespace App\Controller\Admin;

use App\Entity\Declaration;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class DeclarationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Declaration::class;
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
