<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('email'),
            DateTimeField::new('loggedAt')
                ->setFormat('d-m-Y hh:mm:ss'),
            BooleanField::new('isActive'),
            TextField::new('googleId')
                ->hideOnIndex(),
            TextField::new('hostedDomain')
                ->hideOnIndex(),

            ArrayField::new('roles'),

            CollectionField::new('orders')
                ->hideOnIndex()
                ->useEntryCrudForm(),
            CollectionField::new('accounts')
                ->hideOnIndex()
                ->useEntryCrudForm(),
            CollectionField::new('authMails')
                ->hideOnIndex()
                ->useEntryCrudForm(),

        ];
    }
}
