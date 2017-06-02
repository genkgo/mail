<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Smtp\Request;

use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Protocol\Smtp\RequestInterface;

final class AuthLoginUsernameRequest implements RequestInterface
{

    /**
     * @var string
     */
    private $username;

    /**
     * AuthPlainUsernameRequest constructor.
     * @param string $username
     */
    public function __construct(string $username)
    {
        $this->username = $username;
    }

    /**
     * @param ConnectionInterface $connection
     * @return void
     */
    public function execute(ConnectionInterface $connection)
    {
        $connection->send(base64_encode($this->username));
    }
}