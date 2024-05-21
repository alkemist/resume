<?php

namespace App\Helper;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

trait EasyAdminTools
{
    public function getReferer(AdminUrlGenerator $adminUrlGenerator, AdminContext $context): string
    {
        $listUrl = $adminUrlGenerator
            ->setController(self::class)
            ->setAction(Crud::PAGE_INDEX)
            ->generateUrl();

        return $context->getReferrer()
            ? $context->getReferrer()
            : $listUrl;
    }
}