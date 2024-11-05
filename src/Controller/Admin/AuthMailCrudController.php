<?php

namespace App\Controller\Admin;

use App\Entity\AuthMail;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class AuthMailCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return AuthMail::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('link'),
            TextField::new('code'),
            AssociationField::new('user')
                ->hideOnForm(),
            DateTimeField::new('expirationDate')
                ->setFormat('d-m-Y hh:mm:ss'),
        ];
    }

}
