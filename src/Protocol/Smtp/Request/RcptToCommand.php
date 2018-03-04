<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Smtp\Request;

use Genkgo\Mail\EmailAddress;
use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Protocol\Smtp\RequestInterface;

final class RcptToCommand implements RequestInterface
{
    /**
     * @var EmailAddress
     */
    private $recipient;

    /**
     * @param EmailAddress $recipient
     */
    public function __construct(EmailAddress $recipient)
    {
        $this->recipient = $recipient;
    }

    /**
     * @param ConnectionInterface $connection
     * @return void
     */
    public function execute(ConnectionInterface $connection): void
    {
        $connection->send(\sprintf("RCPT TO:<%s>", (string)$this->recipient));
    }
}
