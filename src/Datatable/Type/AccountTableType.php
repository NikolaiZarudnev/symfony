<?php

namespace App\Datatable\Type;


use App\Entity\Account;
use App\Form\DataTransformer\StreetTransformer;
use App\Repository\AccountRepository;
use App\Service\LocaleService;
use Doctrine\ORM\QueryBuilder;
use Omines\DataTablesBundle\Adapter\Doctrine\ORM\SearchCriteriaProvider;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Omines\DataTablesBundle\Column\DateTimeColumn;
use Omines\DataTablesBundle\Column\MapColumn;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\Column\TwigColumn;
use Omines\DataTablesBundle\DataTable;
use Omines\DataTablesBundle\DataTableState;
use Omines\DataTablesBundle\DataTableTypeInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatableMessage;

class AccountTableType implements DataTableTypeInterface
{
    public function __construct(
        private readonly StreetTransformer     $transformer,
        private readonly AccountRepository     $accountRepository,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly LocaleService         $localeService,
    )
    {
    }

    public function configure(DataTable $dataTable, array $options): void
    {
        $dataTable
            ->add('fullName', TextColumn::class, [
                'label' => 'full name',
                'data' => function (Account $account) {
                    return $account->getFirstName() . ' ' . $account->getLastName();
                }
            ])
            ->add('firstName', TextColumn::class, [
                'visible' => false
            ])
            ->add('lastName', TextColumn::class, [
                'visible' => false
            ])
            ->add('email', TextColumn::class, [
                'label' => 'email',
                'render' => function (string $value, Account $account) {
                    return sprintf(
                        '<a href="'
                        . $this->urlGenerator->generate('app_account_show', [
                            '_locale' => $this->localeService->getLocale(),
                            'id' => $account->getId(),
                        ])
                        . '">%s</a>', $value);
                }])
            ->add('companyName', TextColumn::class, [
                'label' => 'company',
            ])
            ->add('position', TextColumn::class, [
                'label' => 'position',
            ])
            ->add('sex', TwigColumn::class, [
                'label' => 'sex',

                'template' => 'tables/sex.html.twig',

                'globalSearchable' => false,
                'searchable' => true
            ])
            ->add('country', TextColumn::class, [
                'label' => 'country',
                'field' => 'country.name',
                'data' => function ($account) {
                    return $account->getAddress()->getCountry() ? $account->getAddress()->getCountry()->getName() : null;
                },
            ])
            ->add('city', TextColumn::class, [
                'label' => 'city',
                'field' => 'city.name',
                'data' => function ($account) {
                    return $account->getAddress()->getCity() ? $account->getAddress()->getCity()->getName() : null;
                },
            ])
            ->add('address', TextColumn::class, [
                'label' => 'address',
                'data' => function ($account) {
                    return $account->getAddress()->getStreet1() ? $this->transformer->transform($account->getAddress()) : null;
                },
            ])
            ->add('createdAt', TwigColumn::class, [
                'label' => 'createdAt',
                'data' => function (Account $account) {
                    return $account->getCreatedAt();
                },
                'template' => 'tables/date.html.twig',
                'globalSearchable' => false,
                'searchable' => false,
                'orderable' => true
            ])
            ->add('actions', TwigColumn::class, [
                'label' => 'actions',
                'template' => 'tables/actions.html.twig'
            ])
            ->createAdapter(ORMAdapter::class, [
                'entity' => Account::class,
                'query' => [
                    function (QueryBuilder $builder) {
                        $builder = $this->accountRepository->getBuilder($builder);
                    }
                ],
                'criteria' => [
                    function (QueryBuilder $builder, DataTableState $state) {

                        if (empty($state->getGlobalSearch())) {
                            return;
                        }
                        $searchData = json_decode($state->getGlobalSearch(), true);

                        if ($searchData['createdAt']) {
                            $dates = $searchData['createdAt'];

                            if ($dates['startDate']) {
                                $startDate = new \DateTime($dates['startDate']);
                            } else {
                                $startDate = new \DateTime('today');
                            }
                            if ($dates['endDate']) {
                                $endDate = new \DateTime($dates['endDate']);
                            } else {
                                $endDate = new \DateTime('today');
                            }

                            $builder
                                ->andWhere('account.createdAt BETWEEN :startDate AND :endDate')
                                ->setParameter('startDate', $startDate->format('Y-m-d H:i:s'))
                                ->setParameter('endDate', $endDate->format('Y-m-d H:i:s'));

                        } else {
                            dump('Cant decode: '.$state->getGlobalSearch());
                        }
                        $state->setGlobalSearch($searchData['search']);
                    },
                    new SearchCriteriaProvider()
                ],
            ]);
    }
}