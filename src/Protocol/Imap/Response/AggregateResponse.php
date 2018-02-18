<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\Response;

use Genkgo\Mail\Exception\AssertionFailedException;
use Genkgo\Mail\Protocol\Imap\RequestInterface;
use Genkgo\Mail\Protocol\Imap\ResponseInterface;

/**
 * Class Response
 * @package Genkgo\Mail\Protocol\Smtp
 */
final class AggregateResponse implements \IteratorAggregate
{
    /**
     * @var array|ResponseInterface[]
     */
    private $lines = [];

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * Reply constructor.
     * @param RequestInterface $request
     */
    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
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
            throw new \UnexpectedValueException('Cannot return item of empty response');
        }

        return reset($this->lines);
    }

    /**
     * @return ResponseInterface
     */
    public function last(): ResponseInterface
    {
        if (empty($this->lines)) {
            throw new \UnexpectedValueException('Cannot return item of empty response');
        }

        return end($this->lines);
    }

    /**
     * @param int $index
     * @return ResponseInterface
     */
    public function at(int $index): ResponseInterface
    {
        if (!isset($this->lines[$index])) {
            throw new \UnexpectedValueException('GenericItem not in response');
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

        $lastCommand = end($this->lines);
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
        $clone = clone $this;

        if (!isset($line[0])) {
            throw new \InvalidArgumentException('Empty line');
        }

        switch (substr($line, 0, 2)) {
            case '+ ':
                $clone->lines[] = new CommandContinuationRequestResponse(
                    substr($line, 2)
                );
                break;
            case '* ':
                $clone->lines[] = new UntaggedResponse(
                    substr($line, 2)
                );
                break;
            default:
                try {
                    $tag = $this->request->getTag();
                    $clone->lines[] = new TaggedResponse($tag, $tag->extractBodyFromLine($line));
                } catch (\InvalidArgumentException | \BadMethodCallException $e) {
                    $keys = array_keys($clone->lines);
                    $lastKey = end($keys);
                    $clone->lines[$lastKey] = $clone->lines[$lastKey]->withBody($line);
                }
                break;

        }

        return $clone;
    }
}
