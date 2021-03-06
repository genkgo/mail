<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\Response;

use Genkgo\Mail\Exception\AssertionFailedException;
use Genkgo\Mail\Protocol\Imap\ResponseInterface;
use Genkgo\Mail\Protocol\Imap\Tag;

final class TaggedResponse implements ResponseInterface
{
    /**
     * @var Tag
     */
    private $tag;

    /**
     * @var string
     */
    private $line;

    /**
     * @param Tag $tag
     * @param string $line
     */
    public function __construct(Tag $tag, string $line)
    {
        $this->tag = $tag;
        $this->line = $line;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return \sprintf('%s %s', (string)$this->tag, $this->line);
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
            throw new AssertionFailedException(
                \sprintf(
                    'Response %s does not include a completion result',
                    $this->line
                )
            );
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
     */
    public function assertTagged(): ResponseInterface
    {
        return $this;
    }

    /**
     * @param string $className
     * @return ResponseInterface
     * @throws AssertionFailedException
     */
    public function assertParsed(string $className): ResponseInterface
    {
        throw new AssertionFailedException('A tagged response is never parsed');
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        return $this->line;
    }
}
