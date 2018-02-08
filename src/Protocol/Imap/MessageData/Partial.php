<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\MessageData;

final class Partial
{
    /**
     * @var int
     */
    private $firstByte;
    /**
     * @var int
     */
    private $lastByte;

    /**
     * @param int $firstByte
     * @param int $lastByte
     */
    public function __construct(int $firstByte, int $lastByte)
    {
        $this->firstByte = $firstByte;
        $this->lastByte = $lastByte;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return sprintf(
            '<%s>',
            $this->firstByte === $this->lastByte ? $this->firstByte : $this->firstByte . '.' . $this->lastByte
        );
    }

    /**
     * @param string $partial
     * @return Partial
     */
    public static function fromString(string $partial): self
    {
        $result = preg_match('/^<([0-9]+)\.([0-9]+)$/', $partial, $matches);

        if ($result !== 1) {
            throw new \InvalidArgumentException('String is not a partial');
        }

        return new self((int)$matches[1], (int)$matches[2]);
    }
}