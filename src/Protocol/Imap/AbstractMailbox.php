<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap;

abstract class AbstractMailbox
{
    /**
     * @var string
     */
    private $name;

    /**
     *
     */
    protected const RFC_3501_FIXED = [
        "INBOX" => true,
    ];

    /**
     * Mailbox constructor.
     * @param string $name
     */
    final public function __construct(string $name)
    {
        $this->validate($name);
        $this->name = $name;
    }

    /**
     * @param string $name
     */
    abstract protected function validate(string $name): void;

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->name;
    }

}