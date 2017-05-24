<?php
declare(strict_types=1);

namespace Genkgo\Mail;

/**
 * Interface TransportInterface
 * @package Genkgo\Email
 */
interface TransportInterface
{
    /**
     * @param MessageInterface $message
     * @return void
     */
    public function send(MessageInterface $message): void;
}
