<?php

namespace App\BaseClass;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class BaseAdminCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return '';
    }
}
