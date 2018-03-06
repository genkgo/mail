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

final class Post
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
     * @return Post
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
     * @return Post
     */
    public function withHtmlAndNoGeneratedAlternativeText(string $html): self
    {
        $clone = clone $this;
        $clone->html = $html;
        return $clone;
    }

    /**
     * @param AlternativeText $text
     * @return Post
     */
    public function withText(AlternativeText $text): self
    {
        $clone = clone $this;
        $clone->text = $text;
        return $clone;
    }

    /**
     * @param PartInterface $part
     * @return Post
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
     * @return Post
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
        if ($this->text === null && $this->html === '') {
            return new PlainTextPart('');
        }

        if ($this->text === null) {
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
     * @return Post
     */
    public static function fromMessage(MessageInterface $message): Post
    {
        return (new self())->extract($message);
    }

    /**
     * @param MessageInterface $message
     * @return Post
     */
    private function extract(MessageInterface $message): self
    {
        try {
            $this->extractFromMimePart(MultiPart::fromMessage($message));
        } catch (\InvalidArgumentException $e) {
            foreach ($message->getHeader('Content-Type') as $header) {
                $contentType = $header->getValue()->getRaw();
                if ($contentType === 'text/html') {
                    $this->html = (string)$message->getBody();
                }

                if ($contentType === 'text/plain') {
                    $this->text = new AlternativeText((string)$message->getBody());
                }
            }
        }

        return $this;
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
