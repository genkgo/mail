<?php
declare(strict_types=1);

namespace Genkgo\Mail;

use Genkgo\Mail\Header\ContentType;
use Genkgo\Mail\Mime\Boundary;
use Genkgo\Mail\Mime\EmbeddedImage;
use Genkgo\Mail\Mime\HtmlPart;
use Genkgo\Mail\Mime\MultiPart;
use Genkgo\Mail\Mime\MultiPartInterface;
use Genkgo\Mail\Mime\PartInterface;
use Genkgo\Mail\Mime\PlainTextPart;

final class MessageBodyCollection
{
    /**
     * @var string
     */
    private $html = '';

    /**
     * @var AlternativeText
     */
    private $text;

    /**
     * @var array|PartInterface[]
     */
    private $attachments = [];

    /**
     * @var array|PartInterface[]
     */
    private $embedImages = [];

    /**
     * @param string $html
     */
    public function __construct(string $html = '')
    {
        $this->html = $html;
        $this->text = AlternativeText::fromHtml($html);
    }

    /**
     * @param string $html
     * @return MessageBodyCollection
     */
    public function withHtml(string $html): self
    {
        $clone = clone $this;
        $clone->html = $html;
        $clone->text = AlternativeText::fromHtml($html);
        return $clone;
    }

    /**
     * @param string $html
     * @return MessageBodyCollection
     */
    public function withHtmlAndNoGeneratedAlternativeText(string $html): self
    {
        $clone = clone $this;
        $clone->html = $html;
        return $clone;
    }

    /**
     * @param AlternativeText $text
     * @return MessageBodyCollection
     */
    public function withAlternativeText(AlternativeText $text): self
    {
        $clone = clone $this;
        $clone->text = $text;
        return $clone;
    }

    /**
     * @param PartInterface $part
     * @return MessageBodyCollection
     */
    public function withAttachment(PartInterface $part): self
    {
        try {
            $disposition = $part->getHeader('Content-Disposition')->getValue()->getRaw();
            if ($disposition !== 'attachment') {
                throw new \InvalidArgumentException(
                    'An attachment must have Content-Disposition header with value `attachment`'
                );
            }
        } catch (\UnexpectedValueException $e) {
            throw new \InvalidArgumentException(
                'An attachment must have an Content-Disposition header'
            );
        }

        $clone = clone $this;
        $clone->attachments[] = $part;
        return $clone;
    }

    /**
     * @param EmbeddedImage $embeddedImage
     * @return MessageBodyCollection
     */
    public function withEmbeddedImage(EmbeddedImage $embeddedImage): self
    {
        $clone = clone $this;
        $clone->embedImages[] = $embeddedImage;
        return $clone;
    }

    /**
     * @return string
     */
    public function getHtml(): string
    {
        return $this->html;
    }

    /**
     * @return AlternativeText
     */
    public function getText(): AlternativeText
    {
        return $this->text;
    }

    /**
     * @return array|PartInterface[]
     */
    public function getAttachments(): iterable
    {
        return $this->attachments;
    }

    /**
     * @return array|PartInterface[]
     */
    public function getEmbeddedImages(): iterable
    {
        return $this->embedImages;
    }

    /**
     * @return MessageInterface
     */
    public function createMessage(): MessageInterface
    {
        return (new MimeMessageFactory())->createMessage($this->createMessageRoot());
    }

    /**
     * @param MessageInterface $message
     * @return MessageInterface
     */
    public function attachToMessage(MessageInterface $message): MessageInterface
    {
        $newMessage = $this->createMessage();

        /** @var HeaderInterface[] $headers */
        foreach ($newMessage->getHeaders() as $headers) {
            foreach ($headers as $header) {
                $message = $message->withHeader($header);
            }
        }

        return $message->withBody($newMessage->getBody());
    }

    /**
     * @return PartInterface
     */
    private function createMessageRoot(): PartInterface
    {
        if (!empty($this->attachments)) {
            return (new MultiPart(
                Boundary::newRandom(),
                new ContentType('multipart/mixed')
            ))
                ->withPart($this->createMessageHumanReadable())
                ->withParts($this->attachments);
        }

        return $this->createMessageHumanReadable();
    }

    /**
     * @return PartInterface
     */
    private function createMessageHumanReadable(): PartInterface
    {
        if (!empty($this->embedImages)) {
            return (new MultiPart(
                Boundary::newRandom(),
                new ContentType('multipart/related')
            ))
                ->withPart($this->createMessageText())
                ->withParts($this->embedImages);
        }

        return $this->createMessageText();
    }

    /**
     * @return PartInterface
     */
    private function createMessageText(): PartInterface
    {
        if ($this->text->isEmpty() && $this->html === '') {
            return new PlainTextPart('');
        }

        if ($this->text->isEmpty()) {
            return new HtmlPart($this->html);
        }

        if ($this->html === '') {
            return new PlainTextPart((string)$this->text);
        }

        return (new MultiPart(
            Boundary::newRandom(),
            new ContentType('multipart/alternative')
        ))
            ->withPart(new PlainTextPart((string)$this->text))
            ->withPart(new HtmlPart($this->html));
    }

    /**
     * @param MessageInterface $message
     * @return MessageBodyCollection
     */
    public static function extract(MessageInterface $message): MessageBodyCollection
    {
        $collection = new self();

        try {
            $collection->extractFromMimePart(MultiPart::fromMessage($message));
        } catch (\InvalidArgumentException $e) {
            foreach ($message->getHeader('Content-Type') as $header) {
                $contentType = $header->getValue()->getRaw();
                if ($contentType === 'text/html') {
                    $collection->html = \rtrim((string)$message->getBody());
                }

                if ($contentType === 'text/plain') {
                    $collection->text = new AlternativeText(\rtrim((string)$message->getBody()));
                }
            }
        }

        return $collection;
    }

    /**
     * @param MultiPartInterface $parts
     */
    private function extractFromMimePart(MultiPartInterface $parts): void
    {
        foreach ($parts->getParts() as $part) {
            $contentType = $part->getHeader('Content-Type')->getValue()->getRaw();
            $hasDisposition = $part->hasHeader('Content-Disposition');

            if (!$hasDisposition && $contentType === 'text/html') {
                $this->html = (string)$part->getBody();
                continue;
            }

            if (!$hasDisposition && $contentType === 'text/plain') {
                $this->text = new AlternativeText((string)$part->getBody());
                continue;
            }

            if ($hasDisposition) {
                $disposition = $part->getHeader('Content-Disposition')->getValue()->getRaw();

                if ($disposition === 'attachment') {
                    $this->attachments[] = $part;
                    continue;
                }

                if ($disposition === 'inline' && \substr($contentType, 0, 6) === 'image/' && $part->hasHeader('Content-ID')) {
                    $this->embedImages[] = $part;
                    continue;
                }
            }

            if ($part instanceof MultiPartInterface) {
                $this->extractFromMimePart($part);
            }
        }
    }
}
