<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\MessageData\Item;

use Genkgo\Mail\Protocol\Imap\MessageData\ItemInterface;

final class NameItem implements ItemInterface
{
    /**
     * @var string
     */
    private $name;
    
    private const VALID_CHAR = "ABCDEFGHIJKLMNOPQRSTUVWXYZ.+-";

    /**
     * @param string $name
     */
    public function __construct(string $name)
    {
        if (\strlen($name) !== \strspn($name, self::VALID_CHAR)) {
            throw new \InvalidArgumentException('name can only contain uppercase A-Z chars');
        }

        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->name;
    }
}
