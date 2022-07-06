<?php

namespace App\Controller\Admin;

use App\Entity\Education;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class EducationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Education::class;
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
