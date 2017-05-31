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
     * @param int $code
     * @param string $message
     * @return Reply
     */
    public function withLine(int $code, string $message): Reply
    {
        $clone = clone $this;
        $clone->lines[] = [$code, $message];
        $clone->codes[$code] = true;
        return $clone;
    }

    /**
     * @param int $code
     * @return Client
     */
    public function assert(int $code): Client {
        return $this->assertBetween($code, $code);
    }

    /**
     * @return Client
     */
    public function assertCompleted(): Client {
        return $this->assertBetween(200, 299);
    }

    /**
     * @param RequestInterface $command
     * @return Reply
     */
    public function assertIntermediate(RequestInterface $command): Reply {
        return $this->assertBetween(300, 399)->request($command);
    }

    /**
     * @param int $min
     * @param int $max
     * @return Client
     */
    private function assertBetween(int $min, int $max)
    {
        foreach ($this->codes as $code) {
            if ($code >= $min && $code <= $max) {
                return $this->client;
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