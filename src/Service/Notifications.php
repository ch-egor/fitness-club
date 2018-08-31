<?php

namespace App\Service;

use App\Entity\Client;
use App\Entity\GroupSession;
use App\Entity\Subscription;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\Console\Output\OutputInterface;

class Notifications
{
    private const MAIN_EXCHANGE_NAME = 'fc_main';
    private const DELAYED_EXCHANGE_NAME = 'fc_delayed';

    private $channel;
    
    public function __destruct()
    {
        $this->closeChannel();
    }

    public function sendGroupSessionEmailNotification(GroupSession $groupSession, ?string $template): void
    {
        if (empty($template)) {
            return;
        }

        foreach ($groupSession->getSubscriptions() as $subscription) {
            if ($subscription->getNotificationType() !== Subscription::NOTIFICATION_TYPE_EMAIL) {
                continue;
            }
            $client = $subscription->getClient();
            if (!$client->getIsActive()) {
                continue;
            }

            $email = $client->getEmail();
            $subject = $groupSession->getName() . ' Notification';
            $content = $this->renderMessageContentTemplate($template, $client);

            $this->enqueueEmailMessage($email, $subject, $content);
        }
    }

    public function sendGroupSessionSmsNotification(GroupSession $groupSession, ?string $template): void
    {
        if (empty($template)) {
            return;
        }

        foreach ($groupSession->getSubscriptions() as $subscription) {
            if ($subscription->getNotificationType() !== Subscription::NOTIFICATION_TYPE_SMS) {
                continue;
            }
            $client = $subscription->getClient();
            if (!$client->getIsActive()) {
                continue;
            }

            $phone = $client->getPhone();
            $content = $this->renderMessageContentTemplate($template, $client);

            $this->enqueueSmsMessage($phone, $content);
        }
    }

    public function listen(OutputInterface $output): void
    {
        $channel = $this->getChannel();

        [$queueName, ,] = $channel->queue_declare("", false, false, true, false);
        $channel->queue_bind($queueName, self::MAIN_EXCHANGE_NAME);

        $callback = function ($message) use ($output) {
            $timestamp = date("Y-m-d H:i:s");
            $output->writeln(" [{$timestamp}] {$message->body}");
        };
        $channel->basic_consume($queueName, '', false, true, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }
        $this->closeChannel();
    }

    private function renderMessageContentTemplate(string $template, Client $client): string
    {
        $templateStrings = [
            '%name%',
            '%dob%',
            '%email%',
            '%phone',
        ];
        $replacements = [
            $client->getName(),
            $client->getDateOfBirth()->format('Y-m-d'),
            $client->getEmail(),
            $client->getPhone()
        ];

        return str_replace($templateStrings, $replacements, $template);
    }

    private function enqueueEmailMessage(string $email, string $subject, string $content): void
    {
        $data = json_encode([
            'type' => 'email',
            'email' => $email,
            'subject' => $subject,
            'content' => $content,
        ]);
        $message = new AMQPMessage($data);

        $channel = $this->getChannel();
        $channel->basic_publish($message, self::MAIN_EXCHANGE_NAME);
        $this->closeChannel();
    }

    private function enqueueSmsMessage(string $phone, string $content): void
    {
        $data = json_encode([
            'type' => 'sms',
            'phone' => $phone,
            'content' => $content,
        ]);
        $message = new AMQPMessage($data);

        $channel = $this->getChannel();
        $channel->basic_publish($message, self::MAIN_EXCHANGE_NAME);
        $this->closeChannel();
    }

    private function getChannel(): AMQPChannel
    {
        if (!$this->channel) {
            $this->openChannel();
        }
        return $this->channel;
    }

    private function openChannel(): void
    {
        $host = 'localhost';
        $post = 5672;
        $user = 'guest';
        $password = 'guest';

        $connection = new AMQPStreamConnection($host, $post, $user, $password);
        $channel = $connection->channel();

        $channel->exchange_declare(self::MAIN_EXCHANGE_NAME, 'fanout', false, false, false);

        $this->channel = $channel;
    }

    private function closeChannel(): void
    {
        if (!$this->channel) {
            return;
        }
        $channel = $this->channel;
        $connection = $channel->getConnection();

        $this->channel = null;

        $channel->close();
        $connection->close();
    }
}
