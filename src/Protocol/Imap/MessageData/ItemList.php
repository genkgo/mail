<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\MessageData;

use Genkgo\Mail\Protocol\Imap\MessageData\Item\NameItem;
use Genkgo\Mail\Protocol\Imap\MessageData\Item\PartialItem;
use Genkgo\Mail\Protocol\Imap\MessageData\Item\SectionItem;

final class ItemList
{
    private const STATE_NONE = 0;
    
    private const STATE_NAME = 1;
    
    private const STATE_SECTION = 2;
    
    private const STATE_PARTIAL = 3;
    
    private const STATE_OCTET = 4;

    /**
     * @var array
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
     * @param array $list
     */
    public function __construct(array $list = [])
    {
        $this->list = $list;
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
            $items .= "\n" . $this->body;
        }

        if (\count($this->list) > 1 || $this->body) {
            return '(' . $items . ')';
        }

        return $items;
    }

    /**
     * @param string $serializedList
     * @return ItemList
     */
    public static function fromString(string $serializedList): self
    {
        $list = new self();

        $index = 0;
        $state = self::STATE_NAME;
        $sequence = '';

        if ($serializedList === '') {
            throw new \InvalidArgumentException('Cannot create list from empty string');
        }

        if ($serializedList[0] === '(' && $serializedList[-1] === ')') {
            $serializedList = \substr($serializedList, 1, -1);
        }

        while (isset($serializedList[$index])) {
            $char = $serializedList[$index];
            $sequence .= $char;

            switch ($char) {
                case '[':
                    if ($state !== self::STATE_NAME) {
                        throw new \InvalidArgumentException('Invalid character [ found');
                    }

                    $list = $list->withItem(new NameItem(\substr($sequence, 0, -1)));
                    $sequence = '[';
                    $state = self::STATE_SECTION;
                    break;
                case ']':
                    if ($state !== self::STATE_SECTION) {
                        throw new \InvalidArgumentException('Invalid character ] found');
                    }

                    $list = $list->withItem(
                        new SectionItem($list->last(), SectionList::fromString($sequence))
                    );

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

                    $list = $list->withItem(
                        new PartialItem($list->last(), Partial::fromString($sequence))
                    );

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

                    $list = $list->withOctet((int)\substr($sequence, 1, -1));
                    $sequence = '';

                    $state = self::STATE_NAME;
                    break;
                case ' ':
                    if ($sequence === ' ') {
                        $state = self::STATE_NONE;
                    }

                    if ($state === self::STATE_NONE) {
                        $sequence = '';
                    }

                    if ($state === self::STATE_NAME) {
                        $list = $list->withItem(new NameItem(\substr($sequence, 0, -1)));
                        $sequence = '';
                        $state = self::STATE_NONE;
                    }

                    break;
                case "\n":
                    $list = $list->withBody(\substr($serializedList, $index + 1));
                    $sequence = '';
                    break 2;
            }

            $index++;
        }

        if ($sequence) {
            $list = $list->withItem(new NameItem($sequence));
        }

        return $list;
    }
}
