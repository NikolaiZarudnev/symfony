<?php

namespace App\Command;

use App\DTO\UserDTO;
use App\Entity\User;
use App\Model\UserModel;
use App\Repository\UserRepository;
use App\Service\GuzzleService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:create-user', description: 'Creates a new user.')]
class CreateUserCommand extends Command
{
    public function __construct(
        private readonly UserModel      $userModel,
        private readonly GuzzleService  $guzzleService,
        private readonly UserRepository $userRepository,
        bool                            $requirePassword = false,
        bool                            $requireActive = false,
        bool                            $requireRole = false,
    )
    {
        $this->requirePassword = $requirePassword;
        $this->requireActive = $requireActive;
        $this->requireRole = $requireRole;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln([
            'User Creator',
            '============',
            '',
        ]);

        $userDTO = new UserDTO();
        $id = $input->getOption('id');
        if ($id) {
            $userDTO->setId($id);
        }
        $userDTO->setEmail($input->getArgument('email'));
        $userDTO->setPassword($input->getArgument('password'));
        $userDTO->setRoles([$input->getArgument('role')]);
        $userDTO->setIsActive($input->getArgument('active'));

        $guzzle = $input->getOption('api');
        if ($guzzle) {
            try {
                $responseData = $this->guzzleService->create($userDTO);
            } catch (\Exception $e) {
                $output->writeln('Error: ' . $e->getMessage());

                return Command::FAILURE;
            }
            $userDTO->setId($responseData['id']);

            $output->writeln($responseData['message']);
        } else {
            if ($userDTO->getId()) {
                $user = $this->userRepository->find($userDTO->getId());

                $message = 'User updated';
            } else {
                $user = new User();

                $message = 'User created';
            }

            try {
                $this->userModel->createByDTO($user, $userDTO);
            } catch (\Exception $e) {
                $output->writeln('Error: ' . $e->getFile() . ' ' . $e->getLine() . ': ' . $e->getMessage());

                return Command::FAILURE;
            }
            $userDTO->setId($user->getId());
            $output->writeln($message);
        }

        $output->writeln('Id: ' . $userDTO->getId());
        $output->writeln('Email: ' . $userDTO->getEmail());
        $output->writeln('Password: ' . $userDTO->getPassword());
        $output->writeln('Roles: ' . implode(', ', $userDTO->getRoles()));
        $output->writeln('Active: ' . $userDTO->getIsActive() . "\n");

        return Command::SUCCESS;
    }

    protected function configure(): void
    {
        $rand = rand(1000, 9999);
        $this
            ->setHelp('This command allows you to create a user.')
            ->addOption('api', null, InputOption::VALUE_NONE, 'Create user using api')
            ->addOption('id', null, InputOption::VALUE_REQUIRED, 'Update user with id instead of create.')
            ->addArgument('email', $this->requirePassword ? InputArgument::REQUIRED : InputArgument::OPTIONAL, 'The email of the user.', 'command-' . $rand . '@test.test')
            ->addArgument('password', $this->requirePassword ? InputArgument::REQUIRED : InputArgument::OPTIONAL, 'User password', 'command-' . $rand . '@test.test')
            ->addArgument('role', $this->requireRole ? InputArgument::REQUIRED : InputArgument::OPTIONAL, 'The role of the user.', User::ROLE_USER)
            ->addArgument('active', $this->requireActive ? InputArgument::REQUIRED : InputArgument::OPTIONAL, 'User active (1 - true, 0 - false)', 1);
    }
}