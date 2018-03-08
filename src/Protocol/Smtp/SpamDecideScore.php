<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Smtp;

final class SpamDecideScore
{
    /**
     * @var int
     */
    private $ham;

    /**
     * @var int
     */
    private $spam;

    /**
     * @param int $ham
     * @param int $spam
     */
    public function __construct(int $ham, int $spam)
    {
        $this->ham = $ham;
        $this->spam = $spam;
    }

    /**
     * @param int $score
     * @return bool
     */
    public function isHam(int $score): bool
    {
        return $this->ham >= $score;
    }

    /**
     * @param int $score
     * @return bool
     */
    public function isSpam(int $score): bool
    {
        return $this->spam <= $score;
    }

    /**
     * @param int $score
     * @return bool
     */
    public function isLikelySpam(int $score): bool
    {
        return $this->ham < $score && $this->spam > $score;
    }
}
