<?php

namespace App\Controller\Admin;

use App\BaseClass\BaseAdminCrudController;
use App\Entity\WebAuthnKey;
use App\Entity\WebAuthnUser;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Bundle\SecurityBundle\Security;

class WebAuthnKeyCrudController extends BaseAdminCrudController
{
    public function __construct(
        private readonly Security $security,
    )
    {

    }

    public static function getEntityFqcn(): string
    {
        return WebAuthnKey::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Key')
            ->setEntityLabelInPlural('Keys')
            ->setDefaultSort([])
            ->setSearchFields(['id'])
            ->showEntityActionsInlined(false);
    }

    public function configureActions(Actions $actions): Actions
    {
        /** @var WebAuthnUser $currentUser */
        $currentUser = $this->security->getUser();

        $actions->disable(Action::NEW)
            ->disable(Action::BATCH_DELETE);


        return $actions;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters;
    }

    public function configureFields(string $pageName): iterable
    {
        if (Crud::PAGE_INDEX === $pageName) {
            yield AssociationField::new('user');
            yield TextField::new('name');
            yield TextField::new('id');
        } elseif (Crud::PAGE_EDIT === $pageName) {
            yield TextField::new('name');
        }
    }

}
