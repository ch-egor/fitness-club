<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GroupSessionRepository")
 */
class GroupSession
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=128)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=128)
     */
    private $coach;

    /**
     * @ORM\Column(type="string", length=1000)
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Subscription", mappedBy="groupSession", cascade={"persist"}, orphanRemoval=true)
     */
    private $subscriptions;

    public function __construct()
    {
        $this->subscriptions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCoach(): ?string
    {
        return $this->coach;
    }

    public function setCoach(string $coach): self
    {
        $this->coach = $coach;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getSubscribedClientCount(): int
    {
        return $this->subscriptions
            ->filter(function ($subscription) {
                return $subscription->getNotificationType() !== Subscription::NOTIFICATION_TYPE_NONE;
            })
            ->count()
        ;
    }

    public function generateClientSubscription(Client $client): Subscription
    {
        $subscription = $this->subscriptions
            ->filter(function ($subscription) use ($client) {
                return $subscription->getClient() === $client;
            })
            ->first()
        ;
        if (!$subscription) {
            $subscription = (new Subscription())
                ->setClient($client)
                ->setGroupSession($this)
            ;
            $this->subscriptions[] = $subscription;
        }
        return $subscription;
    }
}
