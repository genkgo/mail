<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Smtp;

/**
 * Class Reply
 * @package Genkgo\Mail\Protocol\Smtp
 */
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
     * Reply constructor.
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
        } catch (\RuntimeException $e) {
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
     * @return Reply
     */
    public function assert(int $code): Reply {
        return $this->assertBetween($code, $code);
    }

    /**
     * @return Reply
     */
    public function assertCompleted(): Reply {
        return $this->assertBetween(200, 299);
    }

    /**
     * @param RequestInterface $command
     * @return Reply
     */
    public function assertIntermediate(RequestInterface $command): Reply {
        return $this->assertBetween(300, 399)
            ->client
            ->request($command);
    }

    /**
     * @param int $min
     * @param int $max
     * @return Reply
     */
    private function assertBetween(int $min, int $max): Reply
    {
        foreach ($this->codes as $code) {
            if ($code >= $min && $code <= $max) {
                return $this;
            }
        }

        throw new \RuntimeException(
            sprintf(
                'Cannot assert OK reply code. Server replied %s',
                $this->createErrorMessage()
            )
        );
    }

    /**
     * @return string
     */
    private function createErrorMessage(): string
    {
        return implode(
            "\r\n",
            array_map(
                function ($values) {
                    return implode(' ', $values);
                },
                $this->lines
            )
        );
    }
}