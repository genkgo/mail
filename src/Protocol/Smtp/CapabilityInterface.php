<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Smtp;

use Genkgo\Mail\Protocol\ConnectionInterface;

/**
 * Interface CapabilityInterface
 * @package Genkgo\Mail\Protocol\Smtp
 */
interface CapabilityInterface
{
    /**
     * @param ConnectionInterface $connection
     * @param Session $session
     * @return Session
     */
    public function manifest(ConnectionInterface $connection, Session $session): Session;

    /**
     * @return string
     */
    public function advertise(): string;

}