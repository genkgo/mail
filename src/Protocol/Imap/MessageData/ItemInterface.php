<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\MessageData;

interface ItemInterface
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return string
     */
    public function __toString(): string;
}
