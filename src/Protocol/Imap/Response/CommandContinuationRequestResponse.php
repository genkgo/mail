<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\Response;

use Genkgo\Mail\Exception\AssertionFailedException;
use Genkgo\Mail\Protocol\Imap\ResponseInterface;

final class CommandContinuationRequestResponse implements ResponseInterface
{
    /**
     * @var string
     */
    private $line;

    /**
     * @param string $line
     */
    public function __construct(string $line)
    {
        $this->line = $line;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return \trim('+ ' . $this->line);
    }

    /**
     * @param string $data
     * @return ResponseInterface
     */
    public function withAddedBody(string $data): ResponseInterface
    {
        $clone = clone $this;
        $clone->line .= $data;
        return $clone;
    }

    /**
     * @param CompletionResult $expectedResult
     * @return ResponseInterface
     * @throws AssertionFailedException
     */
    public function assertCompletion(CompletionResult $expectedResult): ResponseInterface
    {
        throw new AssertionFailedException();
    }

    /**
     * @return ResponseInterface
     */
    public function assertContinuation(): ResponseInterface
    {
        return $this;
    }

    /**
     * @return ResponseInterface
     * @throws AssertionFailedException
     */
    public function assertTagged(): ResponseInterface
    {
        throw new AssertionFailedException('A command continuous request is never tagged');
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        return $this->line;
    }
}
