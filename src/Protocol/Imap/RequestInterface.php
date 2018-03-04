<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap;

use Genkgo\Mail\StreamInterface;

/**
 * Interface RequestInterface
 * @package Genkgo\Mail\Protocol\Imap
 */
interface RequestInterface
{

    /**
     * @return StreamInterface
     */
    public function toStream(): StreamInterface;

    /**
     * @return Tag
     */
    public function getTag(): Tag;
}
