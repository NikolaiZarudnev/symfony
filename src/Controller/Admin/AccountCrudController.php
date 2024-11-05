<?php

namespace App\Controller\Admin;

use App\Entity\Account;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Contracts\Translation\TranslatorInterface;

class AccountCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly TranslatorInterface $translator,

    ){}

    protected function getRedirectResponseAfterSave(AdminContext $context, string $action): RedirectResponse
    {
        $this->addFlash('notice', $this->translator->trans('notify.account saved', domain: 'messages'));
        return parent::getRedirectResponseAfterSave($context, $action);
    }
    public static function getEntityFqcn(): string
    {
        return Account::class;
    }

    public function createEntity(string $entityFqcn)
    {
        $account = new Account();
        $account->setOwner($this->getUser());

        return $account;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setFormOptions([
                'validation_groups' => ['Default', 'account']
            ]);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->hideOnForm(),
            TextField::new('firstName'),
            TextField::new('lastName'),
            EmailField::new('email'),
            TextField::new('companyName'),
            TextField::new('position'),
            ChoiceField::new('sex')
                ->setChoices([
                    'male' => Account::ACCOUNT_SEX_MALE,
                    'female' => Account::ACCOUNT_SEX_FEMALE,
                    'unknown' => Account::ACCOUNT_SEX_NOT_FOUND,
                ]),
            DateTimeField::new('createdAt')
                ->onlyOnDetail()
                ->setFormat('d-m-Y hh:mm:ss'),

            DateTimeField::new('updatedAt')
                ->onlyOnDetail()
                ->setFormat('d-m-Y hh:mm:ss'),

            DateTimeField::new('deletedAt')
                ->onlyOnDetail()
                ->setFormat('d-m-Y hh:mm:ss'),

            ImageField::new('image')
                ->onlyOnDetail()
                ->setBasePath('public/uploads/account_image/'),

            TextField::new('slug')
                ->onlyOnDetail(),

            AssociationField::new('owner'),

            CollectionField::new('phones')
                ->onlyOnForms()
                ->useEntryCrudForm()
            ,
            CollectionField::new('phones')
                ->onlyOnDetail()
                ->formatValue(
                    function ($numbersStr, Account $account) {
                        $elements = [];

                        foreach ($account->getPhones() as $phone) {
                            $phoneNumber = substr($phone->getNumber(), -7);
                            $phoneCodeOperator = substr($phone->getNumber(), -10, 3);
                            $phoneCodeCountry = '+7';

                            $phoneCodeOperator = '(' . $phoneCodeOperator . ')';

                            preg_match('/(\d{3})(\d{2})(\d{2})$/', $phoneNumber, $parts);
                            $parts = array_slice($parts, 1);
                            $phoneNumber = implode('-', $parts);

                            $formattedNumber = $phoneCodeCountry . ' ' . $phoneCodeOperator . ' ' . $phoneNumber;

                            $url = $this->adminUrlGenerator
                                ->setController(PhoneCrudController::class)
                                ->setAction(Action::DETAIL)
                                ->setEntityId($phone->getId())
                                ->generateUrl();

                            $elements[] = "<a href=$url>$formattedNumber</a>";
                        }

                        return implode(', ', $elements);
                    }
                ),
            AssociationField::new('address')
                ->onlyOnDetail(),
            AssociationField::new('address')
                ->onlyOnForms()
                ->renderAsEmbeddedForm(AddressCrudController::class),
        ];

    }
}
