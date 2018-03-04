<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Smtp\SpamScore;

use Genkgo\Mail\MessageInterface;
use Genkgo\Mail\Protocol\Smtp\SpamScoreInterface;

final class FixedSpamScore implements SpamScoreInterface
{
    /**
     * @var int
     */
    private $score;

    /**
     * @param int $score
     */
    public function __construct(int $score)
    {
        $this->score = $score;
    }

    /**
     * @param MessageInterface $message
     * @return int
     */
    public function calculate(MessageInterface $message): int
    {
        return $this->score;
    }
}
