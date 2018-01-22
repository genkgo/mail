<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\Response;

use Genkgo\Mail\Exception\AssertionFailedException;
use Genkgo\Mail\Protocol\Imap\CompletionResult;
use Genkgo\Mail\Protocol\Imap\ResponseInterface;

/**
 * Class UntaggedStatusResponse
 * @package Genkgo\Mail\Protocol\Imap\Response
 */
final class UntaggedResponse implements ResponseInterface
{
    /**
     * @var string
     */
    private $line;
    /**
     * @var string
     */
    private $command;

    /**
     * UntaggedResponse constructor.
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
        return '* ' . trim(implode(' ', [$this->command, $this->line]));
    }

    /**
     * @param string $data
     * @return ResponseInterface
     */
    public function withBody(string $data): ResponseInterface
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
        try {
            $completionResult = CompletionResult::fromLine($this->line);
            if (!$completionResult->equals($expectedResult)) {
                throw new AssertionFailedException(
                    sprintf(
                        'Returned completion, but %s is not equals to expected %s',
                        (string)$completionResult,
                        (string)$expectedResult
                    )
                );
            }
        } catch (\InvalidArgumentException $e) {
            throw new AssertionFailedException();
        }

        return $this;
    }

    /**
     * @param string $expectedCommand
     * @return ResponseInterface
     * @throws AssertionFailedException
     */
    public function assertCommand(string $expectedCommand): ResponseInterface
    {
        $commandLength = strlen($expectedCommand . ' ');
        $commandActual = substr($this->line, 0, $commandLength);

        if (strcasecmp($commandActual, $expectedCommand . ' ')) {
            throw new AssertionFailedException(
                sprintf(
                    'Command %s is not equal to %s',
                    $commandActual,
                    $expectedCommand
                )
            );
        }

        $clone = clone $this;
        $clone->command = strtoupper($expectedCommand);
        $clone->line = substr($this->line, $commandLength);
        return $clone;
    }

    /**
     * @return ResponseInterface
     * @throws AssertionFailedException
     */
    public function assertContinuation(): ResponseInterface
    {
        throw new AssertionFailedException();
    }

    /**
     * @return ResponseInterface
     * @throws AssertionFailedException
     */
    public function assertTagged(): ResponseInterface
    {
        throw new AssertionFailedException();
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        return $this->line;
    }
}