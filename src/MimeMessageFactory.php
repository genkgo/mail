<?php
declare(strict_types=1);

namespace Genkgo\Mail;

use Genkgo\Mail\Header\GenericHeader;
use Genkgo\Mail\Header\HeaderLine;
use Genkgo\Mail\Header\HeaderValueParameter;
use Genkgo\Mail\Header\MimeVersion;
use Genkgo\Mail\Mime\MultiPartInterface;
use Genkgo\Mail\Mime\PartInterface;
use Genkgo\Mail\Stream\BitEncodedStream;
use Genkgo\Mail\Stream\ConcatenatedStream;

/**
 * Class MimeMessageFactory
 * @package Genkgo\Mail
 */
final class MimeMessageFactory
{
    /**
     * @var PartInterface
     */
    private $part;

    /**
     * Message constructor.
     * @param PartInterface $part
     */
    public function __construct(PartInterface $part)
    {
        $this->part = $part;
    }

    /**
     * @return MessageInterface
     */
    public function createMessage(): MessageInterface
    {
        $message = (new GenericMessage())
            ->withHeader(new MimeVersion());

        $part = $this->pickOptimalCharset($this->part);

        foreach ($part->getHeaders() as $header) {
            $message = $message->withHeader($header);
        }

        if ($part instanceof MultiPartInterface) {
            $message = $message->withBody(
                new ConcatenatedStream(
                    new \ArrayObject([
                        new BitEncodedStream("This is a multipart message in MIME format.\r\n"),
                        $this->createPartStream($part, false)
                    ])
                )
            );
        } else {
            $message = $message->withBody($this->createPartStream($part, false));
        }

        return $message;
    }

    /**
     * @param PartInterface $part
     * @param bool $headers
     * @return StreamInterface
     */
    private function createPartStream(PartInterface $part, bool $headers = true): StreamInterface
    {
        $part = $this->pickOptimalCharset($part);

        $partBody = $part->getBody();
        $streams = new \ArrayObject();
        if ($headers) {
            $streams->append(new BitEncodedStream($this->createHeaderLines($part)));
            $streams->append(new BitEncodedStream("\r\n\r\n"));
        }

        $streams->append($partBody);

        if ($part instanceof MultiPartInterface) {
            $childParts = $part->getParts();
            $boundary = $part->getBoundary();

            foreach ($childParts as $childPart) {
                $streams->append(new BitEncodedStream("\r\n"));
                $streams->append(new BitEncodedStream('--' . (string) $boundary));
                $streams->append(new BitEncodedStream("\r\n"));
                $streams->append($this->createPartStream($childPart));
            }

            if ($childParts) {
                $streams->append(new BitEncodedStream('--' . (string) $boundary . '--'));
                $streams->append(new BitEncodedStream("\r\n"));
            }
        }

        if ($partBody->getSize() !== 0) {
            $streams->append(new BitEncodedStream("\r\n"));
        }

        return new ConcatenatedStream($streams);
    }

    /**
     * @param PartInterface $part
     * @return string
     */
    private function createHeaderLines(PartInterface $part): string
    {
        return implode(
            "\r\n",
            array_values(
                array_map(
                    function (HeaderInterface $header) {
                        return (string) (new HeaderLine($header));
                    },
                    $part->getHeaders()
                )
            )
        );
    }

    /**
     * @param PartInterface $part
     * @return PartInterface
     */
    private function pickOptimalCharset(PartInterface $part): PartInterface
    {
        try {
            $transferEncoding = (string) $part->getHeader('Content-Transfer-Encoding')->getValue();
        } catch (\UnexpectedValueException $e) {
            return $part;
        }

        $contentTypeHeader = $part->getHeader('Content-Type')->getValue();
        if ($transferEncoding !== '7bit' || substr((string)$contentTypeHeader, 0, 5) !== 'text/') {
            return $part;
        }

        try {
            if ($contentTypeHeader->getParameter('charset')->getValue() === 'us-ascii') {
                return $part;
            }
        } catch (\UnexpectedValueException $e) {
        }

        return $part->withHeader(
            new GenericHeader(
                'Content-Type',
                (string) $contentTypeHeader->withParameter(
                    new HeaderValueParameter('charset', 'us-ascii')
                )
            )
        );
    }
}