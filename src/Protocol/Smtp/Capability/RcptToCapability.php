<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Smtp\Capability;

use Genkgo\Mail\EmailAddress;
use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Protocol\Smtp\BackendInterface;
use Genkgo\Mail\Protocol\Smtp\CapabilityInterface;
use Genkgo\Mail\Protocol\Smtp\Session;

final class RcptToCapability implements CapabilityInterface
{
    /**
     * @var BackendInterface
     */
    private $backend;

    /**
     * @param BackendInterface $backend
     */
    public function __construct(BackendInterface $backend)
    {
        $this->backend = $backend;
    }

    /**
     * @param ConnectionInterface $connection
     * @param Session $session
     * @return Session
     */
    public function manifest(ConnectionInterface $connection, Session $session): Session
    {
        try {
            $address = new EmailAddress(\substr($session->getCommand(), 9, -1));

            if ($this->backend->contains($address) === false) {
                $connection->send('550 Unknown mailbox');
                return $session;
            }

            $connection->send('250 OK');
            return $session->withRecipient($address);
        } catch (\InvalidArgumentException $e) {
            $connection->send('501 Invalid recipient');
            return $session;
        }
    }

    /**
     * @return string
     */
    public function advertise(): string
    {
        return 'RCPT TO';
    }
}
