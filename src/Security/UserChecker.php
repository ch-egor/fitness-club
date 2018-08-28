<?php

namespace App\Security;

use App\Entity\Client;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user)
    {
        if (!$user instanceof Client) {
            return;
        }

        if (!empty($user->getEmailConfirmationCode())) {
            throw new DisabledException('The user has not confirmed the e-mail address.');
        }
        if (!$user->getIsActive()) {
            throw new DisabledException('The user is inactive.');
        }
    }

    public function checkPostAuth(UserInterface $user)
    {
        if (!$user instanceof Client) {
            return;
        }
    }
}
