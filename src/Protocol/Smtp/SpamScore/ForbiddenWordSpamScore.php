<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Smtp\SpamScore;

use Genkgo\Mail\MessageInterface;
use Genkgo\Mail\Protocol\Smtp\SpamScoreInterface;

final class ForbiddenWordSpamScore implements SpamScoreInterface
{
    /**
     * @var array
     */
    private $words;

    /**
     * @var int
     */
    private $pointsPerMatchedWord;

    /**
     * @param array $words
     * @param int $pointsPerMatchedWord
     */
    public function __construct(array $words, int $pointsPerMatchedWord)
    {
        $this->words = $words;
        $this->pointsPerMatchedWord = $pointsPerMatchedWord;
    }

    /**
     * @param MessageInterface $message
     * @return int
     */
    public function calculate(MessageInterface $message): int
    {
        $messageBody = \strtolower((string)$message);

        $score = 0;

        foreach ($this->words as $word) {
            $score += $this->pointsPerMatchedWord * \substr_count($messageBody, \strtolower($word));
        }

        return $score;
    }
}
