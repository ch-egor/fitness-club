<?php

namespace App\Service;

use App\Entity\Client;
use App\Entity\GroupSession;
use App\Entity\Subscription;

class Notifications
{
    public function __construct()
    {
        
    }

    public function sendGroupSessionEmailNotification(GroupSession $groupSession, ?string $messageTemplate): void
    {
        if (empty($messageTemplate)) {
            return;
        }

        foreach ($groupSession->getSubscriptions() as $subscription) {
            if ($subscription->getNotificationType() !== Subscription::NOTIFICATION_TYPE_EMAIL) {
                continue;
            }
            $client = $subscription->getClient();

            $message = $this->renderMessageTemplate($messageTemplate, $client);
            $email = $client->getEmail();

            $this->enqueueEmailMessage($email, $message);
        }
    }

    public function sendGroupSessionSmsNotification(GroupSession $groupSession, ?string $messageTemplate): void
    {
        if (empty($messageTemplate)) {
            return;
        }

        foreach ($groupSession->getSubscriptions() as $subscription) {
            if ($subscription->getNotificationType() !== Subscription::NOTIFICATION_TYPE_SMS) {
                continue;
            }
            $client = $subscription->getClient();

            $message = $this->renderMessageTemplate($messageTemplate, $client);
            $phone = $client->getPhone();

            $this->enqueueSmsMessage($phone, $message);
        }
    }

    private function renderMessageTemplate(string $messageTemplate, Client $client): string
    {
        $templateStrings = ['%name%', '%dob%', '%email%', '%phone'];
        $replacements = [$client->getName(), $client->getDateOfBirth(), $client->getEmail(), $client->getPhone()];

        return str_replace($templateStrings, $replacements, $messageTemplate);
    }

    private function enqueueEmailMessage(string $email, string $message): void
    {
        // TODO: implement
    }

    private function enqueueSmsMessage(string $phone, string $message): void
    {
        // TODO: implement
    }
}
