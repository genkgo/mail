<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\Response;

use Genkgo\Mail\Exception\AssertionFailedException;
use Genkgo\Mail\Protocol\Imap\ResponseInterface;
use Genkgo\Mail\Protocol\Imap\Tag;

/**
 * @implements \IteratorAggregate<int, ResponseInterface[]>
 */
final class AggregateResponse implements \IteratorAggregate
{
    /**
     * @var array|ResponseInterface[]
     */
    private $lines = [];

    /**
     * @var Tag
     */
    private $tag;

    /**
     * @param Tag $tag
     */
    public function __construct(Tag $tag)
    {
        $this->tag = $tag;
    }

    /**
     * @return \Iterator|ResponseInterface[]
     */
    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->lines);
    }

    /**
     * @return ResponseInterface
     */
    public function first(): ResponseInterface
    {
        if (empty($this->lines)) {
            throw new \OutOfBoundsException('Cannot return item of empty response');
        }

        return \reset($this->lines);
    }

    /**
     * @return ResponseInterface
     */
    public function last(): ResponseInterface
    {
        if (empty($this->lines)) {
            throw new \OutOfBoundsException('Cannot return item of empty response');
        }

        return \end($this->lines);
    }

    /**
     * @param int $index
     * @return ResponseInterface
     */
    public function at(int $index): ResponseInterface
    {
        if (!isset($this->lines[$index])) {
            throw new \OutOfBoundsException('GenericItem not in response');
        }

        return $this->lines[$index];
    }

    /**
     * @return bool
     */
    public function hasCompleted(): bool
    {
        if (empty($this->lines)) {
            return false;
        }

        $lastCommand = \end($this->lines);
        try {
            $lastCommand->assertTagged();
            return true;
        } catch (AssertionFailedException $e) {
        }

        try {
            $lastCommand->assertContinuation();
            return true;
        } catch (AssertionFailedException $e) {
        }

        return false;
    }

    /**
     * @param string $line
     * @return AggregateResponse
     */
    public function withLine(string $line): AggregateResponse
    {
        switch (\substr($line, 0, 2)) {
            case '+ ':
                $this->lines[] = new CommandContinuationRequestResponse(
                    \substr($line, 2)
                );
                break;
            case '* ':
                $this->lines[] = new UntaggedResponse(
                    \substr($line, 2)
                );
                break;
            default:
                try {
                    $this->lines[] = new TaggedResponse(
                        $this->tag,
                        $this->tag->extractBodyFromLine($line)
                    );
                } catch (\InvalidArgumentException $e) {
                    if (empty($this->lines)) {
                        throw new \UnexpectedValueException(
                            'Expected line to begin with +, * or tag. Got: ' . $line
                        );
                    }

                    $keys = \array_keys($this->lines);
                    $lastKey = \end($keys);
                    $this->lines[$lastKey]->withAddedBody($line);
                }
                break;

        }

        return $this;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return \implode(
            "\r\n",
            \array_map(
                function (ResponseInterface $response) {
                    return (string)$response;
                },
                $this->lines
            )
        );
    }
}
