<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\MessageData;

use Genkgo\Mail\Protocol\Imap\MessageData\Item\FlagsItem;
use Genkgo\Mail\Protocol\Imap\MessageData\Item\NameItem;
use Genkgo\Mail\Protocol\Imap\MessageData\Item\PartialItem;
use Genkgo\Mail\Protocol\Imap\MessageData\Item\SectionItem;

final class ItemList
{
    private const STATE_START = 0;

    private const STATE_NONE = 1;

    private const STATE_NAME = 2;

    private const STATE_SECTION = 3;

    private const STATE_PARTIAL = 4;

    private const STATE_OCTET = 5;

    private const STATE_FLAGS = 6;

    private const STATE_BODY = 7;

    /**
     * @var array<string, ItemInterface>
     */
    private $list = [];

    /**
     * @var int
     */
    private $size;

    /**
     * @var string
     */
    private $body;

    /**
     * @param array|ItemInterface[] $list
     */
    public function __construct(array $list = [])
    {
        foreach ($list as $item) {
            $this->list[$item->getName()] = $item;
        }
    }

    /**
     * @param ItemInterface $item
     * @return ItemList
     */
    public function withItem(ItemInterface $item): self
    {
        $clone = clone $this;
        $clone->list[$item->getName()] = $item;
        return $clone;
    }

    /**
     * @param int $size
     * @return ItemList
     */
    public function withOctet(int $size): self
    {
        $clone = clone $this;
        $clone->size = $size;
        return $clone;
    }

    /**
     * @param string $body
     * @return ItemList
     */
    public function withBody(string $body): self
    {
        $clone = clone $this;
        $clone->body = $body;
        return $clone;
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * @param string $name
     * @return ItemInterface
     */
    public function getItem(string $name): ItemInterface
    {
        if (!isset($this->list[$name])) {
            throw new \UnexpectedValueException(
                \sprintf('Unknown name %s', $name)
            );
        }

        return $this->list[$name];
    }

    /**
     * @return ItemInterface
     */
    public function last(): ItemInterface
    {
        if (empty($this->list)) {
            throw new \OutOfBoundsException('Cannot return last item from empty list');
        }

        return \end($this->list);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $items = \implode(
            ' ',
            \array_map(
                function (ItemInterface $item) {
                    return (string)$item;
                },
                $this->list
            )
        );

        if ($this->size) {
            $items .= ' {' . $this->size . '}';
        }

        if ($this->body) {
            $items .= "\r\n" . $this->body;
        }

        if (\count($this->list) > 1 || $this->body) {
            return '(' . $items . ')';
        }

        return '(' . $items . ')';
    }

    /**
     * @param string $serializedList
     * @return ItemList
     */
    public static function fromString(string $serializedList): self
    {
        if ($serializedList === '') {
            throw new \InvalidArgumentException('Cannot create list from empty string');
        }

        return self::fromBytes(new \ArrayIterator(\str_split($serializedList)));
    }

    /**
     * @param \Iterator<int, string> $bytes
     * @return ItemList
     */
    public static function fromBytes(\Iterator $bytes): self
    {
        $list = new self();

        $state = self::STATE_START;
        $sequence = '';
        $remainingBodyBytes = 0;

        foreach ($bytes as $key => $char) {
            $sequence .= $char;

            if ($state === self::STATE_START) {
                if ($sequence !== '(') {
                    throw new \UnexpectedValueException('Expecting ( as start of item list');
                }

                $state = self::STATE_NAME;
                $sequence = '';
                continue;
            }

            if ($state === self::STATE_BODY) {
                $remainingBodyBytes--;
                if ($remainingBodyBytes === 0) {
                    $list->body = $sequence;
                    $bytes->next();
                    if ($bytes->current() !== ')') {
                        throw new \UnexpectedValueException('List contains extra data after body, expecting )');
                    }

                    return $list;
                }

                continue;
            }

            if ($state !== self::STATE_BODY) {
                switch ($char) {
                    case '(':
                        $sequence = '';
                        if ($state === self::STATE_NONE) {
                            $lastKey = \array_key_last($list->list);
                            $sequence .= $list->list[$lastKey]->getName() . ' ';
                            unset($list->list[$lastKey]);
                        }

                        $sequence .= '(';
                        $state = self::STATE_FLAGS;
                        break;
                    case ')':
                        if ($state === self::STATE_FLAGS) {
                            $flagsItem = FlagsItem::fromString($sequence);
                            $list->list[$flagsItem->getName()] = $flagsItem;
                            $sequence = '';
                            $state = self::STATE_NAME;
                            break;
                        }

                        if ($sequence) {
                            $nameItem = new NameItem(\substr($sequence, 0, -1));
                            $list->list[$nameItem->getName()] = $nameItem;
                        }

                        return $list;
                    case '[':
                        if ($state !== self::STATE_NAME && $state !== self::STATE_NONE) {
                            throw new \InvalidArgumentException('Invalid character [ found');
                        }

                        $nameItem = new NameItem(\substr($sequence, 0, -1));
                        $list->list[$nameItem->getName()] = $nameItem;

                        $sequence = '[';
                        $state = self::STATE_SECTION;
                        break;
                    case ']':
                        if ($state !== self::STATE_SECTION) {
                            throw new \InvalidArgumentException('Invalid character ] found');
                        }

                        $sectionItem = new SectionItem($list->last(), SectionList::fromString($sequence));
                        $list->list[$sectionItem->getName()] = $sectionItem;

                        $sequence = '';
                        $state = self::STATE_NAME;
                        break;
                    case '<':
                        if ($state !== self::STATE_NAME) {
                            throw new \InvalidArgumentException('Invalid character < found');
                        }

                        $state = self::STATE_PARTIAL;
                        break;
                    case '>':
                        if ($state !== self::STATE_PARTIAL) {
                            throw new \InvalidArgumentException('Invalid character > found');
                        }

                        $partialItem = new PartialItem($list->last(), Partial::fromString($sequence));
                        $list->list[$partialItem->getName()] = $partialItem;

                        $sequence = '';
                        $state = self::STATE_NAME;
                        break;
                    case '{':
                        if ($state !== self::STATE_NONE) {
                            throw new \InvalidArgumentException('Invalid character { found');
                        }

                        $state = self::STATE_OCTET;
                        break;
                    case '}':
                        if ($state !== self::STATE_OCTET) {
                            throw new \InvalidArgumentException('Invalid characters } found');
                        }

                        $crlf = '';
                        $bytes->next();
                        $crlf .= $bytes->current();
                        $bytes->next();
                        $crlf .= $bytes->current();

                        if ($crlf !== "\r\n") {
                            throw new \UnexpectedValueException('Octet is expected to be followed by a CRLF');
                        }

                        $list->size = (int)\substr($sequence, 1, -1);
                        $sequence = '';

                        $remainingBodyBytes = $list->size;
                        $state = self::STATE_BODY;
                        break;
                    case ' ':
                        if ($sequence === ' ') {
                            $state = self::STATE_NONE;
                        }

                        if ($state === self::STATE_NONE) {
                            $sequence = '';
                        }

                        if ($state === self::STATE_NAME) {
                            $nameItem = new NameItem(\substr($sequence, 0, -1));
                            $list->list[$nameItem->getName()] = $nameItem;

                            $sequence = '';
                            $state = self::STATE_NONE;
                        }

                        break;
                }
            }
        }

        if ($remainingBodyBytes > 0) {
            throw new \UnexpectedValueException('Unexpected end of item list, expecting more bytes');
        }

        throw new \UnexpectedValueException('Unexpected end of item list, expecting ) to finish the list');
    }
}
