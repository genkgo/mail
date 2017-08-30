<?php

namespace Genkgo\Mail\Protocol\Smtp;

use Genkgo\Mail\MessageInterface;

/**
 * Interface SpamScoreInterface
 * @package Genkgo\Mail\Protocol\Smtp
 */
interface SpamScoreInterface
{

    /**
     * @param MessageInterface $message
     * @return int
     */
    public function calculate(MessageInterface $message): int;

}