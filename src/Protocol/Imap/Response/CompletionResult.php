<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\Response;

final class CompletionResult
{
    private const BAD = 'BAD';
    
    private const NO = 'NO';
    
    private const OK = 'OK';
    
    private const ENUM = [
        self::BAD => true,
        self::NO => true,
        self::OK => true,
    ];

    /**
     * @var string
     */
    private $result;

    /**
     * @param string $value
     */
    private function __construct(string $value)
    {
        if (!isset(self::ENUM[$value])) {
            throw new \InvalidArgumentException(
                \sprintf(
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
        $found = \preg_match('/^([A-Z]{0,3})\s/', $line, $matches);

        if ($found === 0) {
            return new self($line);
        }

        return new self($matches[1]);
    }

    /**
     * @return CompletionResult
     */
    public static function ok(): self
    {
        return new self(self::OK);
    }
}
