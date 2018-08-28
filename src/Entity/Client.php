<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ClientRepository")
 */
class Client implements UserInterface, \Serializable
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=128)
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @ORM\Column(type="date")
     * @Assert\Date()
     */
    private $dateOfBirth;

    /**
     * @ORM\Column(type="string", length=1)
     * @Assert\Choice(choices={"M", "F"}, message="Choose a valid sex.")
     */
    private $sex;

    /**
     * @ORM\Column(type="string", length=128, unique=true)
     * @Assert\NotBlank()
     * @Assert\Email(
     *     message = "The email '{{ value }}' is not a valid email.",
     *     checkMX = false
     * )
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=24)
     * @Assert\NotBlank()
     */
    private $phone;

    /**
     * @ORM\Column(type="string", length=128, nullable=true)
     * @Assert\Image(
     *     minWidth = 200,
     *     maxWidth = 400,
     *     minHeight = 200,
     *     maxHeight = 400
     * )
     */
    private $photo;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $emailConfirmationCode;

    /**
     * @ORM\Column(type="boolean")
     * @Assert\Type("bool")
     */
    private $isActive;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $password;
    
    public function __construct()
    {
        $this->setPassword('');
        $this->setIsActive(0);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDateOfBirth(): ?\DateTimeInterface
    {
        return $this->dateOfBirth;
    }

    public function setDateOfBirth(\DateTimeInterface $dateOfBirth): self
    {
        $this->dateOfBirth = $dateOfBirth;

        return $this;
    }

    public function getSex(): ?string
    {
        return $this->sex;
    }

    public function setSex(?string $sex): self
    {
        $this->sex = $sex;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getPhoto()
    {
        return $this->photo;
    }

    public function setPhoto($photo): self
    {
        $this->photo = $photo;

        return $this;
    }

    public function getEmailConfirmationCode(): ?string
    {
        return $this->emailConfirmationCode;
    }

    public function setEmailConfirmationCode(?string $emailConfirmationCode): self
    {
        $this->emailConfirmationCode = $emailConfirmationCode;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(?bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getUsername()
    {
        return $this->email;
    }

    public function getSalt()
    {
        // you *may* need a real salt depending on your encoder
        // see section on salt below
        return null;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getRoles()
    {
        if ($this->email === 'admin@example.com') {
            return ['ROLE_ADMIN'];
        }
        return ['ROLE_USER'];
    }

    public function eraseCredentials()
    {
    }

    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize([
            $this->id,
            $this->email,
            $this->password,
            // see section on salt below
            // $this->salt,
        ]);
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        [
            $this->id,
            $this->email,
            $this->password,
            // see section on salt below
            // $this->salt
        ] = unserialize($serialized, ['allowed_classes' => false]);
    }
}
