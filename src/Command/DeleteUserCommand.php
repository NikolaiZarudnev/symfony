<?php

namespace App\Command;

use App\Entity\User;
use App\Model\UserModel;
use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:delete-user', description: 'Deletes a user.')]
class DeleteUserCommand extends Command
{
    public function __construct(
        private readonly UserModel      $userModel,
        private readonly UserRepository $userRepository,
        bool                            $requireId = false,
        bool                            $requireEmail = false,
    )
    {
        $this->requireId = $requireId;
        $this->requireEmail = $requireEmail;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln([
            'User Remover',
            '============',
            '',
        ]);
        $count = $input->getOption('last_users');
        $email = $input->getOption('email');
        $id = $input->getOption('id');
        if ($count) {
            $users = $this->userRepository->findLastUsers($count);

            foreach ($users as $user) {
                $this->deleteUser($user, $output);
            }
        } else {
            $user = new User();

            if ($email) {
                $user = $this->userRepository->findOneBy(['email' => $email]);
            }
            if ($id) {
                $user = $this->userRepository->find($id);
            }

            $this->deleteUser($user, $output);
        }


        return Command::SUCCESS;
    }

    // ...
    protected function configure(): void
    {
        $this
            ->setHelp('This command allows you to delete a user by id or by email. Or option --last_users allow you to delete the last (value) users')
            ->addOption(
                'id',
                null,
                InputOption::VALUE_REQUIRED,
                'Delete the user by id',
            )
            ->addOption(
                'email',
                null,
                InputOption::VALUE_REQUIRED,
                'Delete the user by email',
            )
            ->addOption(
                'last_users',
                null,
                InputOption::VALUE_REQUIRED,
                'Delete the last (value) users',
            );
    }

    private function deleteUser(User $user, OutputInterface $output): void
    {
        $output->writeln('This user will be deleted now:');
        $output->write('Id: ' . $user->getId() . '; ');
        $output->write('Email: ' . $user->getEmail() . '; ');
        $output->write('Role: ' . implode(", ", $user->getRoles()) . '; ');
        $output->writeln('Active: ' . $user->getIsActive());

        $this->userModel->delete($user);

        $output->writeln('Successful');
    }
}