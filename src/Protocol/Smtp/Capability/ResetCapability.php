<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Smtp\Capability;

use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Protocol\Smtp\CapabilityInterface;
use Genkgo\Mail\Protocol\Smtp\Session;

final class ResetCapability implements CapabilityInterface
{
    /**
     * @param ConnectionInterface $connection
     * @param Session $session
     * @return Session
     */
    public function manifest(ConnectionInterface $connection, Session $session): Session
    {
        $connection->send('250 Reset OK');
        $connection->disconnect();

        return $session->withState(Session::STATE_CONNECTED);
    }

    /**
     * @return string
     */
    public function advertise(): string
    {
        return 'RSET';
    }
}
