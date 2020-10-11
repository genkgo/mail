<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap;

interface CommandResponseCanBeParsedInterface
{
    /**
     * @param \Iterator $lineIterator
     * @return ResponseInterface
     */
    public function createParsedResponse(\Iterator $lineIterator): ResponseInterface;
}
