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
     * @param Tag $tag
     * @return self
     */
    public function withTag(Tag $tag): RequestInterface;

    /**
     * @return Tag
     */
    public function getTag(): Tag;

    /**
     * @return StreamInterface
     */
    public function toStream(): StreamInterface;

}