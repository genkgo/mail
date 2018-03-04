<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap;

interface TagFactoryInterface
{
    /**
     * @return Tag
     */
    public function newTag(): Tag;
}
