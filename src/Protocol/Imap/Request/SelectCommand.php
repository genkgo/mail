<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\Request;

use Genkgo\Mail\Protocol\Imap\RequestInterface;
use Genkgo\Mail\Stream\StringStream;
use Genkgo\Mail\StreamInterface;

final class SelectCommand implements RequestInterface
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
    public function toStream(): StreamInterface
    {
        return new StringStream(
            sprintf(
                'SELECT %s',
                $this->name
            )
        );
    }
}