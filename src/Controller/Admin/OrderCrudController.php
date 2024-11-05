<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;

class OrderCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Order::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Order');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->remove(Crud::PAGE_INDEX, Action::NEW);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->hideOnForm(),
            DateTimeField::new('createdAt')
                ->setFormat('d-m-Y hh:mm:ss')
                ->hideOnForm(),

            ChoiceField::new('status')
                ->setLabel('order.status')
                ->setChoices([
                    'paid' => Order::PAID,
                    'cancelled' => Order::CANCELLED,
                    'processing' => Order::PROCESSING,
                ])
                ->renderAsBadges([
                    Order::PAID => 'success',
                    Order::CANCELLED => 'danger',
                    Order::PROCESSING => 'warning',
                ]),

            IntegerField::new('totalCost')
                ->hideOnForm(),

            AssociationField::new('user')
                ->hideOnForm(),

            AssociationField::new('payment')
                ->onlyOnDetail(),
            AssociationField::new('payment')
                ->onlyOnForms()
                ->renderAsEmbeddedForm(PaymentCrudController::class),

            CollectionField::new('products')
                ->onlyOnDetail(),
        ];
    }
}
