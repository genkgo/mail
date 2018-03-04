<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Smtp;

use Genkgo\Mail\Exception\AssertionFailedException;

final class Reply
{
    /**
     * @var array
     */
    private $lines = [];

    /**
     * @var array
     */
    private $codes = [];

    /**
     * @var array
     */
    private $messages = [];

    /**
     * @var Client
     */
    private $client;

    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @return bool
     */
    public function isError(): bool
    {
        try {
            $this->assertBetween(400, 599);
            return true;
        } catch (AssertionFailedException $e) {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function isCommandNotImplemented(): bool
    {
        try {
            $this->assert(502);
            return true;
        } catch (AssertionFailedException $e) {
            return false;
        }
    }

    /**
     * @return array
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * @param int $code
     * @param string $message
     * @return Reply
     */
    public function withLine(int $code, string $message): Reply
    {
        $clone = clone $this;
        $clone->lines[] = [$code, $message];
        $clone->codes[$code] = true;
        $clone->messages[] = $message;
        return $clone;
    }

    /**
     * @param int $code
     * @return Client
     */
    public function assert(int $code): Client
    {
        return $this->assertBetween($code, $code);
    }

    /**
     * @return Client
     */
    public function assertCompleted(): Client
    {
        return $this->assertBetween(200, 299);
    }

    /**
     * @return Client
     */
    public function assertIntermediate(): Client
    {
        return $this->assertBetween(300, 399);
    }

    /**
     * @param int $min
     * @param int $max
     * @return Client
     * @throws AssertionFailedException
     */
    private function assertBetween(int $min, int $max): Client
    {
        foreach ($this->codes as $code => $activated) {
            if ($code >= $min && $code <= $max) {
                return $this->client;
            }
        }

        throw new AssertionFailedException(
            \sprintf(
                'Cannot assert reply code between %s and %s. Server replied %s.',
                $min,
                $max,
                $this->createErrorMessage()
            )
        );
    }

    /**
     * @return string
     */
    private function createErrorMessage(): string
    {
        return \implode(
            "\r\n",
            \array_map(
                function ($values) {
                    return \implode(' ', $values);
                },
                $this->lines
            )
        );
    }
}
