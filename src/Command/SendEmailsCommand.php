<?php

namespace App\Command;

use App\Entity\User\UserHandler;
use App\Entity\Email\EmailHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SendEmailsCommand extends Command
{
    protected static $defaultName = 'app:reminder:send';

    public function __construct(EmailHandler $emailHandler, UserHandler $userHandler)
    {
        $this->emailHandler = $emailHandler;
        $this->userHandler = $userHandler;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Send dues reminder email to members.');
        $this->setHelp('This command sends out emails to members whose membership payments are due at the end of the current month. It should be run on the 1st of each month using a cron job.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $users = $this->userHandler->getMembersExpiringThisMonth();
        $count = 0;
        foreach ($users as $user) {
            $this->emailHandler->sendDuesReminderEmail($user);
            $count += 1;
        }
        $output->writeln(sizeof($users).' member(s) will lapse at the end of this month.');
        $output->writeln($count.' dues reminder emails have been spooled.');
    }
}
