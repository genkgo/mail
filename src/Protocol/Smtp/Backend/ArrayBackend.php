<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Smtp\Backend;

use Genkgo\Mail\EmailAddress;
use Genkgo\Mail\MessageInterface;
use Genkgo\Mail\Protocol\Smtp\BackendInterface;

final class ArrayBackend implements BackendInterface
{
    /**
     * @var array<string, bool>
     */
    private $addresses;

    /**
     * @var \ArrayAccess<string, mixed>
     */
    private $backend;

    /**
     * @param array<int, string> $addresses
     * @param \ArrayAccess<string, mixed> $backend
     */
    public function __construct(array $addresses, \ArrayAccess $backend)
    {
        $addresses = \array_combine(
            $addresses,
            \array_fill(0, \count($addresses), true)
        );

        /** @var array<string, bool>|false $addresses */
        if ($addresses === false) {
            throw new \UnexpectedValueException('Cannot combine arrays');
        }

        $this->addresses = $addresses;
        $this->backend = $backend;
    }

    /**
     * @param EmailAddress $mailbox
     * @return bool
     */
    public function contains(EmailAddress $mailbox): bool
    {
        return isset($this->addresses[(string)$mailbox]);
    }

    /**
     * @param EmailAddress $mailbox
     * @param MessageInterface $message
     * @param string $folder
     */
    public function store(EmailAddress $mailbox, MessageInterface $message, string $folder): void
    {
        if (!isset($this->addresses[(string)$mailbox])) {
            throw new \UnexpectedValueException('Unknown mailbox');
        }

        if (!isset($this->backend[(string)$mailbox])) {
            $this->backend[(string)$mailbox] = [];
        }

        if (!isset($this->backend[(string)$mailbox][$folder])) {
            $this->backend[(string)$mailbox][$folder] = [];
        }

        $this->backend[(string)$mailbox][$folder][] = $message;
    }
}
