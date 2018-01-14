<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap;

use Genkgo\Mail\Exception\AssertionFailedException;

/**
 * Class Response
 * @package Genkgo\Mail\Protocol\Smtp
 */
final class Response
{
    /**
     * @var array
     */
    private $lines = [];
    /**
     * @var EmitterInterface
     */
    private $client;
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * Reply constructor.
     * @param EmitterInterface $client
     * @param RequestInterface $request
     */
    public function __construct(EmitterInterface $client, RequestInterface $request)
    {
        $this->client = $client;
        $this->request = $request;
    }

    /**
     * @return bool
     */
    public function isError(): bool
    {
        try {
            $this->assertOk();
            return false;
        } catch (AssertionFailedException $e) {
            return true;
        }
    }

    /**
     * @return array
     */
    public function getLines(): array
    {
        return $this->lines;
    }

    /**
     * @param string $line
     * @return Response
     */
    public function withLine(string $line): Response
    {
        $clone = clone $this;
        $clone->lines[] = $line;
        return $clone;
    }

    /**
     * @return EmitterInterface
     */
    public function assertOk(): EmitterInterface
    {
        return $this->assertPrefixedWith('*');
    }

    /**
     * @return EmitterInterface
     */
    public function assertIntermediate(): EmitterInterface
    {
        return $this->assertPrefixedWith('+');
    }

    /**
     * @return EmitterInterface
     */
    public function assertTagged(): EmitterInterface
    {
        return $this->assertPrefixedWith('TAG');
    }

    /**
     * @param string $prefix
     * @return EmitterInterface
     * @throws AssertionFailedException
     */
    private function assertPrefixedWith(string $prefix): EmitterInterface
    {
        if (empty($this->lines)) {
            throw new AssertionFailedException('No reply received');
        }

        $firstLine = trim(reset($this->lines));
        if (substr($firstLine, 0, strlen($prefix)) === $prefix) {
            return $this->client;
        }

        throw new AssertionFailedException(
            sprintf(
                'Cannot assert reply is prefixed with %s. Server replied %s.',
                $prefix,
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
