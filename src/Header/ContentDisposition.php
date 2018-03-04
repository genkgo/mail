<?php
declare(strict_types=1);

namespace Genkgo\Mail\Header;

use Genkgo\Mail\HeaderInterface;

final class ContentDisposition implements HeaderInterface
{
    /**
     * @var string
     */
    private $value;

    /**
     * @var string
     */
    private $filename;

    /**
     * @param string $value
     * @param string $filename
     */
    private function __construct(string $value, string $filename)
    {
        $this->value = $value;
        $this->filename = $filename;
    }

    /**
     * @return HeaderName
     */
    public function getName(): HeaderName
    {
        return new HeaderName('Content-Disposition');
    }

    /**
     * @return HeaderValue
     */
    public function getValue(): HeaderValue
    {
        return (new HeaderValue($this->value))
            ->withParameter(
                new HeaderValueParameter(
                    'filename',
                    $this->filename
                )
            );
    }

    /**
     * @param string $filename
     * @return ContentDisposition
     */
    public static function newInline(string $filename)
    {
        return new self('inline', $filename);
    }

    /**
     * @param string $filename
     * @return ContentDisposition
     */
    public static function newAttachment(string $filename)
    {
        return new self('attachment', $filename);
    }
}
