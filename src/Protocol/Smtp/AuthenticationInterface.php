<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Smtp;

interface AuthenticationInterface
{
    /**
     * @param string $username
     * @param string $password
     * @return bool
     */
    public function authenticate(string $username, string $password): bool;
}
