<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Smtp\Request;

use Genkgo\Mail\EmailAddress;
use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Protocol\Smtp\RequestInterface;

final class MailFromCommand implements RequestInterface
{
    /**
     * @var EmailAddress
     */
    private $envelope;

    /**
     * @param EmailAddress $envelope
     */
    public function __construct(EmailAddress $envelope)
    {
        $this->envelope = $envelope;
    }

    /**
     * @param ConnectionInterface $connection
     * @return void
     */
    public function execute(ConnectionInterface $connection): void
    {
        $connection->send(\sprintf("MAIL FROM:<%s>", (string)$this->envelope));
    }
}
