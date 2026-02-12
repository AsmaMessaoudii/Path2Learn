<?php

namespace App\Security;

use App\Entity\User;
use App\Enum\UserStatus;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if ($user->getStatus() === UserStatus::DISABLE) {
            // This will prevent login and show this message
            throw new CustomUserMessageAccountStatusException(
                'This account has been blocked. Please contact the admin.'
            );
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        // optional: nothing here
    }
}
