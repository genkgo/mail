<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Smtp;

use Genkgo\Mail\Protocol\ConnectionInterface;

/**
 * Class Command
 * @package Genkgo\Mail\Protocol\Smtp
 */
final class Command
{

    /**
     * @var ConnectionInterface
     */
    private $connection;
    /**
     * @var string
     */
    private $command;
    /**
     * @var array
     */
    private $expectedCodes = [];
    /**
     * @var \Closure[]
     */
    private $callbacks;

    /**
     * Command constructor.
     * @param ConnectionInterface $connection
     * @param string $command
     */
    public function __construct(
        ConnectionInterface $connection,
        string $command
    ) {
        $this->connection = $connection;
        $this->command = $command;
    }

    public function withExpectCode(int $code)
    {
        $clone = clone $this;
        $clone->expectedCodes[] = (string) $code;
        return $clone;
    }

    public function withExpectCodes(array $codes)
    {
        $clone = clone $this;
        $clone->expectedCodes = array_merge(
            $this->expectedCodes,
            array_map('strval', $codes)
        );
        return $clone;
    }

    public function withCallback(\Closure $callback)
    {
        $clone = clone $this;
        $clone->callbacks[] = $callback;
        return $clone;
    }

    public function execute()
    {
        $this->connection->send($this->command . "\r\n");
        $this->assert();
    }

    /**
     *
     */
    private function assert(): void
    {
        $error = '';

        do {
            $result = $this->connection->receive();
            list($cmd, $more, $message) = preg_split(
                '/([\s-]+)/',
                $result,
                2,
                PREG_SPLIT_DELIM_CAPTURE
            );

            if ($error !== '') {
                $error .= ' ' . $message;
            } elseif ($cmd === null || !in_array($cmd, $this->expectedCodes)) {
                $error = $message;
            }
        } while (strpos($more, '-') === 0);

        if ($error !== '') {
            throw new \RuntimeException($error);
        }
    }
}