<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap;

/**
 * Class CompletionResult
 */
/**
 * Class CompletionResult
 * @package Genkgo\Mail\Protocol\Imap
 */
final class CompletionResult
{
    /**
     *
     */
    private CONST BAD = 'BAD';
    /**
     *
     */
    private CONST NO = 'NO';
    /**
     *
     */
    private CONST OK = 'OK';
    /**
     *
     */
    private CONST ENUM = [
        self::BAD => true,
        self::NO => true,
        self::OK => true,
    ];
    /**
     * @var string
     */
    private $result;

    /**
     * CompletionResult constructor.
     * @param string $value
     */
    private function __construct(string $value)
    {
        if (!isset(self::ENUM[$value])) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Invalid completion result %s',
                    $value
                )
            );
        }

        $this->result = $value;
    }

    /**
     * @param CompletionResult $completionResult
     * @return bool
     */
    public function equals(CompletionResult $completionResult): bool
    {
        return $this->result === $completionResult->result;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->result;
    }

    /**
     * @param string $result
     * @return CompletionResult
     */
    public static function fromString(string $result): self
    {
        return new self($result);
    }

    /**
     * @param string $line
     * @return CompletionResult
     */
    public static function fromLine(string $line): self
    {
        $space = strpos($line, ' ');

        if ($space === false) {
            return new self($line);
        }

        return new self(substr($line, 0, $space));
    }

    /**
     * @return CompletionResult
     */
    public static function ok(): self
    {
        return new self(self::OK);
    }
}