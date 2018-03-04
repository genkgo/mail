<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Smtp\Capability;

use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Protocol\Smtp\CapabilityInterface;
use Genkgo\Mail\Protocol\Smtp\Session;

final class QuitCapability implements CapabilityInterface
{
    /**
     * @param ConnectionInterface $connection
     * @param Session $session
     * @return Session
     */
    public function manifest(ConnectionInterface $connection, Session $session): Session
    {
        $connection->send('221 Thank you for listening');
        $connection->disconnect();

        return $session->withState(Session::STATE_DISCONNECT);
    }

    /**
     * @return string
     */
    public function advertise(): string
    {
        return 'QUIT';
    }
}
