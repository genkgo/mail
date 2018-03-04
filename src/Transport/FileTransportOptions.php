<?php
declare(strict_types=1);

namespace Genkgo\Mail\Transport;

final class FileTransportOptions
{
    /**
     * @var string
     */
    private $directory;

    /**
     * @var \Closure
     */
    private $fileNameGenerator;

    /**
     * @param string $directory
     * @param \Closure $fileNameGenerator
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
     * @return \Closure
     */
    public function getFileNameGenerator(): \Closure
    {
        return $this->fileNameGenerator;
    }
}
