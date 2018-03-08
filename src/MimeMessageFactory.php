<?php
declare(strict_types=1);

namespace Genkgo\Mail;

use Genkgo\Mail\Header\ParsedHeader;
use Genkgo\Mail\Header\HeaderLine;
use Genkgo\Mail\Header\HeaderName;
use Genkgo\Mail\Header\HeaderValueParameter;
use Genkgo\Mail\Mime\MultiPartInterface;
use Genkgo\Mail\Mime\PartInterface;
use Genkgo\Mail\Stream\ConcatenatedStream;
use Genkgo\Mail\Stream\StringStream;

final class MimeMessageFactory
{
    /**
     * @param PartInterface $part
     * @return MessageInterface
     */
    public function createMessage(PartInterface $part): MessageInterface
    {
        $message = new GenericMessage();

        $part = $this->pickOptimalCharset($part);

        foreach ($part->getHeaders() as $header) {
            $message = $message->withHeader($header);
        }

        if ($part instanceof MultiPartInterface) {
            $message = $message->withBody(
                new ConcatenatedStream(
                    new \ArrayObject([
                        new StringStream("This is a multipart message in MIME format.\r\n"),
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
            $streams->append(new StringStream($this->createHeaderLines($part)));
            $streams->append(new StringStream("\r\n\r\n"));
        }

        $streams->append($partBody);

        if ($part instanceof MultiPartInterface) {
            $childParts = $part->getParts();
            $boundary = $part->getBoundary();

            foreach ($childParts as $childPart) {
                $streams->append(new StringStream("\r\n"));
                $streams->append(new StringStream('--' . (string) $boundary));
                $streams->append(new StringStream("\r\n"));
                $streams->append($this->createPartStream($childPart));
            }

            if ($childParts) {
                $streams->append(new StringStream('--' . (string) $boundary . '--'));
                $streams->append(new StringStream("\r\n"));
            }
        }

        if ($partBody->getSize() !== 0) {
            $streams->append(new StringStream("\r\n"));
        }

        return new ConcatenatedStream($streams);
    }

    /**
     * @param PartInterface $part
     * @return string
     */
    private function createHeaderLines(PartInterface $part): string
    {
        return \implode(
            "\r\n",
            \array_values(
                \array_map(
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
        if ($transferEncoding !== '7bit' || \substr((string)$contentTypeHeader, 0, 5) !== 'text/') {
            return $part;
        }

        try {
            if ($contentTypeHeader->getParameter('charset')->getValue() === 'us-ascii') {
                return $part;
            }
        } catch (\UnexpectedValueException $e) {
        }

        return $part->withHeader(
            new ParsedHeader(
                new HeaderName('Content-Type'),
                $contentTypeHeader->withParameter(
                    new HeaderValueParameter('charset', 'us-ascii')
                )
            )
        );
    }
}
