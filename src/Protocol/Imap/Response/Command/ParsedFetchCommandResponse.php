<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\Response\Command;

use Genkgo\Mail\Exception\AssertionFailedException;
use Genkgo\Mail\Protocol\Imap\MessageData\ItemList;
use Genkgo\Mail\Protocol\Imap\Response\CompletionResult;
use Genkgo\Mail\Protocol\Imap\ResponseInterface;

final class ParsedFetchCommandResponse implements ResponseInterface
{
    /**
     * @var int
     */
    private $number;

    /**
     * @var ItemList
     */
    private $itemList;

    /**
     * @param \Iterator<int, string> $lineIterator
     */
    public function __construct(\Iterator $lineIterator)
    {
        $line = $lineIterator->current();
        if (\substr($line, 0, 2) !== '* ') {
            throw new \InvalidArgumentException('Expecting an untagged response');
        }

        $lineContent = \substr($line, 2);

        $fetchStart = \preg_match('/^([0-9]+) FETCH (.*)$/s', $lineContent, $matches);
        if ($fetchStart !== 1) {
            throw new \InvalidArgumentException('Expecting a fetch command response');
        }

        $this->number = (int)$matches[1];
        $this->itemList = ItemList::fromBytes(
            (function () use ($matches, $lineIterator) {
                foreach (\str_split($matches[2]) as $char) {
                    yield $char;
                }

                while ($lineIterator->valid()) {
                    $lineIterator->next();

                    foreach (\str_split($lineIterator->current()) as $char) {
                        yield $char;
                    }
                }
            })()
        );
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return \sprintf("* %s\r\n", $this->getBody());
    }

    /**
     * @param CompletionResult $expectedResult
     * @return ResponseInterface
     * @throws AssertionFailedException
     */
    public function assertCompletion(CompletionResult $expectedResult): ResponseInterface
    {
        throw new AssertionFailedException('A parsed fetch command response is a completion result');
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
        if ($className !== self::class) {
            throw new AssertionFailedException(self::class . ' is not parsed as a ' . $className);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        return \sprintf(
            '%s FETCH %s',
            $this->number,
            (string)$this->itemList
        );
    }

    /**
     * @return int
     */
    public function getNumber(): int
    {
        return $this->number;
    }

    /**
     * @return ItemList
     */
    public function getItemList(): ItemList
    {
        return $this->itemList;
    }
}
