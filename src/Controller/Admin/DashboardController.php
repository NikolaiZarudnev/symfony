<?php

namespace App\Controller\Admin;

use App\Entity\Account;
use App\Entity\Address;
use App\Entity\AuthMail;
use App\Entity\City;
use App\Entity\Country;
use App\Entity\Order;
use App\Entity\Payment;
use App\Entity\Phone;
use App\Entity\Product;
use App\Entity\User;
use App\Repository\OrderRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;

class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
    ) {}


    public function index(): Response
    {
        return $this->render('admin/dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Ng Mysymfony');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::section('Accounts');
        yield MenuItem::linkToCrud('Account', 'fas fa-list', Account::class)
            ->setPermission(User::ROLE_MANAGER);
        yield MenuItem::linkToCrud('Phone', 'fas fa-list', Phone::class);
        yield MenuItem::linkToCrud('Address', 'fas fa-list', Address::class);
        yield MenuItem::linkToCrud('Country', 'fas fa-list', Country::class);
        yield MenuItem::linkToCrud('City', 'fas fa-list', City::class);
        yield MenuItem::section('Users');
        yield MenuItem::linkToCrud('User', 'fas fa-list', User::class);
        yield MenuItem::linkToCrud('AuthMail', 'fas fa-list', AuthMail::class);
        yield MenuItem::linkToCrud('Order', 'fas fa-list', Order::class);
        yield MenuItem::linkToCrud('Payment', 'fas fa-list', Payment::class);
        yield MenuItem::section('Products');
        yield MenuItem::linkToCrud('Products', 'fas fa-list', Product::class);
    }
}
