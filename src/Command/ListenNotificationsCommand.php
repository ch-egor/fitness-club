<?php

namespace App\Command;

use App\Service\Notifications;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListenNotificationsCommand extends Command
{
    private $notifications;

    public function __construct(Notifications $notifications)
    {
        $this->notifications = $notifications;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('app:listen')

            // the short description shown while running "php bin/console list"
            ->setDescription('Listens for notification messages and sends them.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to listen for notification messages and send them.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Waiting for messages. To exit press CTRL+C');

        $this->notifications->listen($output);
    }
}
