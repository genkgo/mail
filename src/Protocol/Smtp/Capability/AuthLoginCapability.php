<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Smtp\Capability;

use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Protocol\Smtp\AuthenticationInterface;
use Genkgo\Mail\Protocol\Smtp\CapabilityInterface;
use Genkgo\Mail\Protocol\Smtp\Session;

final class AuthLoginCapability implements CapabilityInterface
{
    /**
     * @var AuthenticationInterface
     */
    private $authentication;

    /**
     * @param AuthenticationInterface $authentication
     */
    public function __construct(AuthenticationInterface $authentication)
    {
        $this->authentication = $authentication;
    }

    /**
     * @param ConnectionInterface $connection
     * @param Session $session
     * @return Session
     */
    public function manifest(ConnectionInterface $connection, Session $session): Session
    {
        $connection->send('330 Please send me your username');
        $username = \base64_decode($connection->receive());
        $connection->send('330 Please send me your password');
        $password = \base64_decode($connection->receive());

        if ($this->authentication->authenticate($username, $password) === false) {
            $connection->send('535 Authentication failed');
            return $session;
        }

        $connection->send('235 Authentication succeeded');
        return $session->withState(Session::STATE_AUTHENTICATED);
    }

    /**
     * @return string
     */
    public function advertise(): string
    {
        return 'AUTH LOGIN';
    }
}
