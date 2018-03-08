<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Smtp;

use Genkgo\Mail\MessageInterface;

interface GreyListInterface
{
    /**
     * @param MessageInterface $message
     * @return bool
     */
    public function contains(MessageInterface $message): bool;

    /**
     * @param MessageInterface $message
     */
    public function attach(MessageInterface $message): void;

    /**
     * @param MessageInterface $message
     */
    public function detach(MessageInterface $message): void;
}
