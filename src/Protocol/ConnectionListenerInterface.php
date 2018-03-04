<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol;

/**
 * Interface ConnectionListenerInterface
 * @package Genkgo\Mail\Protocol
 */
interface ConnectionListenerInterface
{
    /**
     * @return ConnectionInterface
     */
    public function listen(): ConnectionInterface;
}
