<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\MessageData;

final class ItemList
{
    /**
     *
     */
    private CONST STATE_NONE = 0;
    /**
     *
     */
    private CONST STATE_NAME = 1;
    /**
     *
     */
    private CONST STATE_SECTION = 2;
    /**
     *
     */
    private CONST STATE_PARTIAL = 3;
    /**
     *
     */
    private CONST STATE_OCTET = 4;
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
     * ItemList constructor.
     * @param array $list
     */
    public function __construct(array $list = [])
    {
        $this->list = $list;
    }

    /**
     * @param Item $item
     * @return ItemList
     */
    public function withItem(Item $item): self
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
     * @param $name
     * @return Item
     */
    public function getName($name): Item
    {
        if (!isset($this->list[$name])) {
            throw new \UnexpectedValueException(
                sprintf('Unknown name %s', $name)
            );
        }

        return $this->list[$name];
    }

    /**
     * @return Item
     */
    public function last(): Item
    {
        if (empty($this->list)) {
            throw new \OutOfBoundsException('Cannot return last item from empty list');
        }

        return end($this->list);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $string = implode(
            ' ',
            array_map(
                function (Item $item) {
                    return (string)$item;
                },
                $this->list
            )
        );

        if (count($this->list) > 1) {
            return '(' . $string . ')';
        }

        return $string;
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

        while (isset($serializedList[$index])) {
            $char = $serializedList[$index];
            $sequence .= $char;

            switch ($char) {
                case '[':
                    if ($state !== self::STATE_NAME) {
                        throw new \InvalidArgumentException('Invalid character [ found');
                    }

                    $list = $list->withItem(new Item(substr($sequence, 0, -1)));
                    $sequence = '[';
                    $state = self::STATE_SECTION;
                    break;
                case ']':
                    if ($state !== self::STATE_SECTION) {
                        throw new \InvalidArgumentException('Invalid character ] found');
                    }

                    $list = $list->withItem(
                        $list
                            ->last()
                            ->withSections(SectionList::fromString($sequence))
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
                        $list
                            ->last()
                            ->withPartial(Partial::fromString($sequence))
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

                    $list = $list->withOctet((int)substr($sequence, 1, -1));
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
                        $list = $list->withItem(new Item(substr($sequence, 0, -1)));
                        $sequence = '';
                        $state = self::STATE_NONE;
                    }

                    break;
                case "\n":
                    $list = $list->withBody(substr($serializedList, $index + 1));
                    $sequence = '';
                    break 2;
            }

            $index++;
        }

        if ($sequence) {
            $list = $list->withItem(new Item($sequence));
        }

        return $list;
    }
}