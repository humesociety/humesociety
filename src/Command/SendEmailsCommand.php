<?php

namespace App\Command;

use App\Entity\User\UserHandler;
use App\Entity\Email\EmailHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for sending automatic reminder emails.
 */
class SendEmailsCommand extends Command
{
    /**
     * The name of the command (used to call it from the command line).
     *
     * @var string
     */
    protected static $defaultName = 'app:reminder:send';

    /**
     * Constructor function.
     *
     * @param EmailHandler The email handler.
     * @param UserHandler The user handler.
     * @return void
     */
    public function __construct(EmailHandler $emails, UserHandler $users)
    {
        $this->emails = $emails;
        $this->users = $users;
        parent::__construct();
    }

    /**
     * Configure the command (set its description and help text).
     *
     * @return void
     */
    protected function configure()
    {
        $description = 'Send dues reminder email to members.';
        $help = 'This command sends out emails to members whose membership payments are due at the '
              . 'end of the current month. It should be run on the 1st of each month using a cron job.';
        $this->setDescription($description);
        $this->setHelp($help);
    }

    /**
     * The code to run when the command is called.
     *
     * @param InputInterface Symfony's input interface.
     * @param OutputInterface Symfony's output interface.
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $users = $this->users->getMembersExpiringThisMonth();
        $count = 0;
        foreach ($users as $user) {
            $this->emails->sendSocietyEmail($user, 'reminder');
            $count += 1;
        }
        $output->writeln(sizeof($users).' member(s) will lapse at the end of this month.');
        $output->writeln($count.' dues reminder emails have been spooled.');
    }
}
