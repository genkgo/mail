<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Smtp\SpamScore;

use Genkgo\Mail\MessageInterface;
use Genkgo\Mail\Protocol\Smtp\SpamScoreInterface;

final class AggregateSpamScore implements SpamScoreInterface
{
    /**
     * @var SpamScoreInterface[]
     */
    private $checkers;

    /**
     * @param array $checkers
     */
    public function __construct(array $checkers)
    {
        $this->checkers = $checkers;
    }

    /**
     * @param MessageInterface $message
     * @return int
     */
    public function calculate(MessageInterface $message): int
    {
        $score = 0;

        foreach ($this->checkers as $check) {
            $score += $check->calculate($message);
        }

        return $score;
    }
}
