<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\MessageData;

final class PartialItem implements ItemInterface
{

    /**
     * @var ItemInterface
     */
    private $item;
    /**
     * @var int
     */
    private $firstByte;
    /**
     * @var int
     */
    private $lastByte;

    /**
     * PartialItem constructor.
     * @param ItemInterface $item
     * @param int $firstByte
     * @param int $lastByte
     */
    public function __construct(ItemInterface $item, int $firstByte, int $lastByte)
    {
        $this->item = $item;
        $this->firstByte = $firstByte;
        $this->lastByte = $lastByte;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->item->getName();
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return sprintf(
            '%s<%s>',
            (string)$this->item,
            $this->firstByte === $this->lastByte ? $this->firstByte : $this->firstByte . '.' . $this->lastByte
        );
    }

    /**
     * @param ItemInterface $item
     * @param string $partial
     * @return PartialItem
     */
    public static function fromString(ItemInterface $item, string $partial): self
    {
        $result = preg_match('/^<([0-9]+)\.([0-9]+)$/', $partial, $matches);

        if ($result !== 1) {
            throw new \InvalidArgumentException('String is not a partial');
        }

        return new self($item, (int)$matches[1], (int)$matches[2]);
    }
}