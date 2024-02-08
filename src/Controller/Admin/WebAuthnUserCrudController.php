<?php

namespace App\Controller\Admin;

use App\BaseClass\BaseAdminCrudController;
use App\Entity\Invoice;
use App\Entity\WebAuthnUser;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Exception;
use Symfony\Bundle\SecurityBundle\Security;

class WebAuthnUserCrudController extends BaseAdminCrudController
{
    public function __construct(
        private readonly Security $security,
    )
    {

    }

    public static function getEntityFqcn(): string
    {
        return WebAuthnUser::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('User')
            ->setEntityLabelInPlural('Users')
            ->setDefaultSort([])
            ->setSearchFields(['name', 'displayName'])
            ->showEntityActionsInlined(false);
    }

    public function configureActions(Actions $actions): Actions
    {
        /** @var WebAuthnUser $currentUser */
        $currentUser = $this->security->getUser();

        $actionEdit = Action::new(Action::EDIT, 'Edit', 'fa fa-pencil')
            ->displayIf(fn(WebAuthnUser $user) => $user->getId() !== $currentUser->getId())
            ->linkToCrudAction(Action::EDIT);

        $actionDelete = Action::new(Action::DELETE, 'Delete', 'fa fa-trash-can')
            ->displayIf(fn(WebAuthnUser $user) => $user->getId() !== $currentUser->getId())
            ->linkToCrudAction(Action::DELETE);

        $actions->disable(Action::NEW)
            ->disable(Action::BATCH_DELETE)
            ->remove(Crud::PAGE_EDIT, Action::DELETE)
            ->add(Crud::PAGE_EDIT, $actionDelete)
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->add(Crud::PAGE_INDEX, $actionDelete)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->add(Crud::PAGE_INDEX, $actionEdit);


        return $actions;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters;
    }

    public function configureFields(string $pageName): iterable
    {
        if (Crud::PAGE_INDEX === $pageName) {
            yield TextField::new('name', 'Email');
            yield TextField::new('displayName', 'Username');
            yield ArrayField::new('roles');
            yield CollectionField::new('keys');

        } elseif (Crud::PAGE_EDIT === $pageName) {
            // Le name n'est pas modifiable parce que lié au certificat/hashage de la clé
            yield TextField::new('name', 'Email')
                ->setDisabled(true)
                ->setColumns(2);
            yield TextField::new('displayName', 'Username')
                ->setDisabled(true)
                ->setColumns(2);
            yield ArrayField::new('roles')
                ->setColumns(2);
        }
    }

    /**
     * @param Invoice $entityInstance
     * @throws Exception
     */
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        parent::persistEntity($entityManager, $entityInstance);
    }

    /**
     * @param Invoice $entityInstance
     * @throws Exception
     */
    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        parent::updateEntity($entityManager, $entityInstance);
    }
}
