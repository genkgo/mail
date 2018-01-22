<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\Request;

use Genkgo\Mail\Stream\StringStream;
use Genkgo\Mail\StreamInterface;

/**
 * Class SelectCommand
 * @package Genkgo\Mail\Protocol\Imap\Request
 */
final class SelectCommand extends AbstractCommand
{
    /**
     * @var string
     */
    private $name;

    /**
     * SelectCommand constructor.
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return StreamInterface
     */
    public function createStream(): StreamInterface
    {
        return new StringStream(
            sprintf(
                'SELECT %s',
                $this->name
            )
        );
    }
}