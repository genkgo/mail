<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Smtp\Authentication;

use Genkgo\Mail\Protocol\Smtp\AuthenticationInterface;

final class ArrayAuthentication implements AuthenticationInterface
{
    /**
     * @var array
     */
    private $users;

    /**
     * @param array $users
     */
    public function __construct(array $users)
    {
        $this->users = $users;
    }

    /**
     * @param string $username
     * @param string $password
     * @return bool
     */
    public function authenticate(string $username, string $password): bool
    {
        return isset($this->users[$username]) && $this->users[$username] === $password;
    }
}
