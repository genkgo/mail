<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\Response;

use Genkgo\Mail\Exception\AssertionFailedException;
use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Protocol\Imap\CommandResponseCanBeParsedInterface;
use Genkgo\Mail\Protocol\Imap\RequestInterface;
use Genkgo\Mail\Protocol\Imap\ResponseInterface;

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
     * @return string
     */
    public function __toString(): string
    {
        return \implode(
            '',
            \array_map(
                function (ResponseInterface $response) {
                    return (string)$response;
                },
                $this->lines
            )
        );
    }

    /**
     * @param RequestInterface $request
     * @param ConnectionInterface $connection
     * @return AggregateResponse
     */
    public static function fromServerResponse(ConnectionInterface $connection, RequestInterface $request): self
    {
        return self::fromResponseLines(
            (function () use ($connection) {
                while ($line = $connection->receive()) {
                    yield $line;
                }
            })(),
            $request
        );
    }

    /**
     * @param \Iterator $lines
     * @param RequestInterface $request
     * @return static
     */
    public static function fromResponseLines(\Iterator $lines, RequestInterface $request): self
    {
        $response = new self();
        $currentLine = [];

        foreach ($lines as $line) {
            switch (\substr($line, 0, 2)) {
                case '+ ':
                    if ($currentLine !== []) {
                        $response->lines[] = new UntaggedResponse(\implode('', $currentLine));
                        $currentLine = [];
                    }

                    $response->lines[] = new CommandContinuationRequestResponse(
                        \substr($line, 2)
                    );
                    break;
                case '* ':
                    if ($currentLine !== []) {
                        $response->lines[] = new UntaggedResponse(\implode('', $currentLine));
                        $currentLine = [];
                    }

                    if ($request instanceof CommandResponseCanBeParsedInterface) {
                        $response->lines[] = $request->createParsedResponse(
                            (function () use ($line, $lines) {
                                yield $line;

                                while ($lines->valid()) {
                                    $lines->next();
                                    yield $lines->current();
                                }
                            })()
                        );
                    } else {
                        $currentLine = [\substr($line, 2)];
                    }
                    break;
                default:
                    try {
                        $taggedResponse = new TaggedResponse(
                            $request->getTag(),
                            $request->getTag()->extractBodyFromLine($line)
                        );

                        if ($currentLine !== []) {
                            $response->lines[] = new UntaggedResponse(\implode('', $currentLine));
                            $currentLine = [];
                        }

                        $response->lines[] = $taggedResponse;
                    } catch (\InvalidArgumentException $e) {
                        if ($response->lines === []) {
                            throw new \UnexpectedValueException(
                                'Expected line to begin with +, * or tag. Got: ' . $line
                            );
                        }

                        $currentLine[] = $line;
                    }
                    break;
            }
        }

        if ($currentLine) {
            $response->lines[] = new UntaggedResponse(\implode('', $currentLine));
        }

        return $response;
    }
}
