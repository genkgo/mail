<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Smtp\Capability;

use Genkgo\Mail\GenericMessage;
use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Protocol\Smtp\BackendInterface;
use Genkgo\Mail\Protocol\Smtp\CapabilityInterface;
use Genkgo\Mail\Protocol\Smtp\Session;

final class DataCapability implements CapabilityInterface
{
    /**
     * @var BackendInterface
     */
    private $backend;

    /**
     * DataCapability constructor.
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
        $connection->send('354 Enter message, ending with "." on a line by itself');
        $data = [];

        while (true) {
            $line = $connection->receive();

            if ($line === '.') {
                break;
            }

            $data[] = $line;
        }

        try {
            $message = GenericMessage::fromString(implode("\r\n", $data));
        } catch (\InvalidArgumentException $e) {
            $connection->send('500 Malformed message');
            return $session;
        }

        foreach ($session->getRecipients() as $recipient) {
            $this->backend->store($recipient, $message);
        }

        $connection->send('250 Message received, queue for delivering');
        return (clone $session)->withMessage($message);
    }

    /**
     * @return string
     */
    public function advertise(): string
    {
        return 'DATA';
    }
}