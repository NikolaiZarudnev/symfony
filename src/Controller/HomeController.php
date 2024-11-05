<?php

namespace App\Controller;

use App\Datatable\Type\AccountTableType;
use App\Entity\User;
use App\Repository\AccountRepository;
use App\Repository\UserRepository;
use App\Service\AccountCacher;
use Omines\DataTablesBundle\DataTableFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;


class HomeController extends AbstractController
{
    public function __construct(
        private readonly DataTableFactory $dataTableFactory,
        private readonly AccountRepository $accountRepository,
        private readonly AccountCacher $accountCacher,
        private readonly UserRepository $userRepository,
        private readonly RequestStack $requestStack,
    ) {}

    public function index(Request $request): Response
    {

        $table = $this->dataTableFactory->createFromType(AccountTableType::class);
        $table->handleRequest($request);
        if ($table->isCallback()) {
            return $table->getResponse();
        }

        $projectRoot = $this->getParameter('kernel.project_dir');
        return $this->render('home/index.html.twig', [
            'projectRoot' => $projectRoot,
            'datatable' => $table,
            'count' => rand(1, 50),
        ]);
    }

    public function header(Request $request): Response
    {
        $countAccounts = $this->accountRepository->getCountAccounts();

        $account = $this->accountCacher->getAccount();

        if ($this->isGranted(User::ROLE_ADMIN)) {
            $countUsers = $this->userRepository->getCountUsers();
        } else {
            $countUsers = null;
        }

        if ($this->requestStack->getParentRequest()->attributes->get('_route')) {
            $headerParentRequest = $this->requestStack->getParentRequest();
        } else {
            $headerParentRequest = $this->requestStack->getMainRequest();
        }
        return $this->render('/header.html.twig', [
            'count_accounts' => $countAccounts,
            'count_users' => $countUsers,
            'account' => $account,
            'parent_request' => $headerParentRequest,
        ]);
    }
}
