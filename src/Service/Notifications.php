<?php

namespace App\Service;

use App\Entity\Client;
use App\Entity\GroupSession;
use App\Entity\Subscription;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Notifications
{
    private const MAIN_EXCHANGE_NAME = 'fc_main';
    private const DELAYED_EXCHANGE_NAME = 'fc_delayed';

    private $channel;
    private $mailer;
    private $router;

    public function __construct(\Swift_Mailer $mailer, UrlGeneratorInterface $router)
    {
        $this->mailer = $mailer;
        $this->router = $router;
    }
    
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

            $this->enqueueEmail($email, $subject, $content);
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

            $this->enqueueSms($phone, $content);
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
            
            $isDelivered = $this->dispatchMessage($message->body);
            $output->writeln($isDelivered ? 'Delivered' : 'Not delivered');
        };
        $channel->basic_consume($queueName, '', false, true, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }
        $this->closeChannel();
    }

    private function dispatchMessage(string $messageJson): bool
    {
        $messageData = json_decode($messageJson);
        
        switch ($messageData->type) {
            case 'email':
                return $this->dispatchEmail($messageData->email, $messageData->subject, $messageData->content);
            case 'sms':
                return $this->dispatchSms($messageData->phone, $messageData->content);
            default:
                return false;
        }
    }

    private function dispatchEmail(string $email, string $subject, string $content): bool
    {
        $message = (new \Swift_Message())
            ->setSubject($subject)
            ->setFrom('noreply@example.com')
            ->setTo($email)
            ->setBody($content, 'text/html');

        $recipientCount = $this->mailer->send($message);
        return $recipientCount > 0;
    }

    private function dispatchSms(string $phone, string $content): bool
    {
        $apiUrl = 'http://localhost/fitness-club/public/index.php/sms/send';

        $requestUrl = $apiUrl . '?phone=' . urlencode($phone) . '&message=' . urlencode($content);

        $client = new \GuzzleHttp\Client();
        try {
            $res = $client->request('GET', $requestUrl);
        } catch (\GuzzleHttp\Exception\ServerException $e) {
            return false;
        }
        return true;
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

    private function enqueueEmail(string $email, string $subject, string $content): void
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

    private function enqueueSms(string $phone, string $content): void
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
