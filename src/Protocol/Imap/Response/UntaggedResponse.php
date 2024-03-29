<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\Response;

use Genkgo\Mail\Exception\AssertionFailedException;
use Genkgo\Mail\Protocol\Imap\ResponseInterface;

final class UntaggedResponse implements ResponseInterface
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
        return '* ' . \trim($this->line);
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
                    \sprintf(
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
        throw new AssertionFailedException('An untagged response is never tagged');
    }

    /**
     * @param string $className
     * @return ResponseInterface
     * @throws AssertionFailedException
     */
    public function assertParsed(string $className): ResponseInterface
    {
        throw new AssertionFailedException('An untagged response is never parsed');
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        return $this->line;
    }
}
