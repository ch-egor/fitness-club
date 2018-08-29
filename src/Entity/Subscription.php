<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SubscriptionRepository")
 */
class Subscription
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Client", inversedBy="subscriptions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $client;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\GroupSession", inversedBy="subscriptions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $groupSession;

    /**
     * @ORM\Column(type="integer")
     */
    private $notificationType;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getGroupSession(): ?GroupSession
    {
        return $this->groupSession;
    }

    public function setGroupSession(?GroupSession $groupSession): self
    {
        $this->groupSession = $groupSession;

        return $this;
    }

    public function getNotificationType(): ?int
    {
        return $this->notificationType;
    }

    public function setNotificationType(int $notificationType): self
    {
        $this->notificationType = $notificationType;

        return $this;
    }
}
