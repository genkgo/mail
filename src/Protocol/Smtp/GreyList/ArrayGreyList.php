<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Smtp\GreyList;

use Genkgo\Mail\MessageInterface;
use Genkgo\Mail\Protocol\Smtp\GreyListInterface;

final class ArrayGreyList implements GreyListInterface
{
    /**
     * @var array
     */
    private $list = [];

    /**
     * @param MessageInterface $message
     * @return bool
     */
    public function contains(MessageInterface $message): bool
    {
        $hash = \hash('sha256', (string)$message);
        return isset($this->list[$hash]);
    }

    /**
     * @param MessageInterface $message
     */
    public function attach(MessageInterface $message): void
    {
        $hash = \hash('sha256', (string)$message);
        $this->list[$hash] = true;
    }

    /**
     * @param MessageInterface $message
     */
    public function detach(MessageInterface $message): void
    {
        $hash = \hash('sha256', (string)$message);
        unset($this->list[$hash]);
    }
}
