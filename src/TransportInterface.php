<?php
declare(strict_types=1);

namespace Genkgo\Mail;

interface TransportInterface
{
    /**
     * @param MessageInterface $message
     * @return void
     */
    public function send(MessageInterface $message): void;
}
