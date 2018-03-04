<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Smtp\Capability;

use Genkgo\Mail\EmailAddress;
use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Protocol\Smtp\CapabilityInterface;
use Genkgo\Mail\Protocol\Smtp\Session;

final class MailFromCapability implements CapabilityInterface
{
    /**
     * @param ConnectionInterface $connection
     * @param Session $session
     * @return Session
     */
    public function manifest(ConnectionInterface $connection, Session $session): Session
    {
        try {
            $address = new EmailAddress(\substr($session->getCommand(), 11, -1));
            $connection->send('250 OK');
            return $session->withEnvelope($address);
        } catch (\InvalidArgumentException $e) {
            $connection->send('501 Invalid envelope');
            return $session;
        }
    }

    /**
     * @return string
     */
    public function advertise(): string
    {
        return 'MAIL FROM';
    }
}
