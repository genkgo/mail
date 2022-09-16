<?php
declare(strict_types=1);

namespace Genkgo\Mail\Transport;

use Genkgo\Mail\MessageInterface;

final class FileTransportOptions
{
    /**
     * @var string
     */
    private $directory;

    /**
     * @var \Closure(MessageInterface): string
     */
    private $fileNameGenerator;

    /**
     * @param string $directory
     * @param \Closure(MessageInterface): string $fileNameGenerator
     */
    public function __construct(string $directory, \Closure $fileNameGenerator)
    {
        $this->directory = $directory;
        $this->fileNameGenerator = $fileNameGenerator;
    }

    /**
     * @return string
     */
    public function getDirectory(): string
    {
        return $this->directory;
    }

    /**
     * @return \Closure(MessageInterface): string
     */
    public function getFileNameGenerator(): \Closure
    {
        return $this->fileNameGenerator;
    }
}
