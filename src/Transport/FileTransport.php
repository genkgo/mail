<?php
declare(strict_types=1);

namespace Genkgo\Mail\Transport;

use Genkgo\Mail\MessageInterface;
use Genkgo\Mail\TransportInterface;

class FileTransport implements TransportInterface
{
    /**
     * @var FileTransportOptions
     */
    private $fileTransportOptions;

    /**
     * @param FileTransportOptions $fileTransportOptions
     */
    public function __construct(FileTransportOptions $fileTransportOptions)
    {
        $this->fileTransportOptions = $fileTransportOptions;
    }

    /**
     * @param MessageInterface $message
     */
    public function send(MessageInterface $message): void
    {
        $fileName = \sprintf(
            '%s/%s',
            $this->fileTransportOptions->getDirectory(),
            $this->fileTransportOptions->getFileNameGenerator()->call($this, $message)
        );

        \file_put_contents($fileName, (string)$message);
    }
}
