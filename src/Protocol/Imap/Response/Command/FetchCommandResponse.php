<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\Response\Command;

use Genkgo\Mail\Protocol\Imap\MessageData\ItemList;

final class FetchCommandResponse
{
    /**
     * @var int
     */
    private $number;

    /**
     * @var ItemList
     */
    private $dataItemList;

    /**
     * @param int $number
     * @param ItemList $dataItemList
     */
    public function __construct(int $number, ItemList $dataItemList)
    {
        $this->number = $number;
        $this->dataItemList = $dataItemList;
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
    public function getDataItemList(): ItemList
    {
        return $this->dataItemList;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return \sprintf(
            "%s FETCH %s",
            $this->number,
            (string)$this->dataItemList
        );
    }

    /**
     * @param string$response
     * @return FetchCommandResponse
     */
    public static function fromString(string $response): self
    {
        $matches = [];
        $result = \preg_match('/^([0-9]+) FETCH ((\()?.*?(\))?)\s*$/s', $response, $matches);
        if ($result !== 1) {
            throw new \InvalidArgumentException('Not a fetch command');
        }

        return new self((int)$matches[1], ItemList::fromString($matches[2]));
    }
}
