<?php

namespace App\Controller\Admin;

use App\Entity\Phone;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;

class PhoneCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Phone::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setFormOptions([
                'validation_groups' => ['Default', 'phone']
            ]);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->remove(Crud::PAGE_INDEX, Action::NEW);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TelephoneField::new('number')->formatValue(function ($number) {
                $phoneNumber = substr($number, -7);
                $phoneCodeOperator = substr($number, -10, 3);
                $phoneCodeCountry = '+7';//todo country codes

                $phoneCodeOperator = '(' . $phoneCodeOperator . ')';

                preg_match('/(\d{3})(\d{2})(\d{2})$/', $phoneNumber, $parts);
                $parts = array_slice($parts, 1);
                $phoneNumber = implode('-', $parts);

                return $phoneCodeCountry . ' ' . $phoneCodeOperator . ' ' . $phoneNumber;
            }),
            AssociationField::new('account')->hideOnForm(),
        ];
    }
}
