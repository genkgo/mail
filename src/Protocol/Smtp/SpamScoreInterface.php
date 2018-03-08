<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Smtp;

use Genkgo\Mail\MessageInterface;

interface SpamScoreInterface
{
    /**
     * @param MessageInterface $message
     * @return int
     */
    public function calculate(MessageInterface $message): int;
}
