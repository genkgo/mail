<?php
declare(strict_types=1);

namespace Genkgo\Mail;

use Genkgo\Mail\Mime\AlternativePart;
use Genkgo\Mail\Mime\Boundary;
use Genkgo\Mail\Mime\HtmlPart;
use Genkgo\Mail\Mime\Message;
use Genkgo\Mail\Mime\MixedPart;
use Genkgo\Mail\Mime\PartInterface;
use Genkgo\Mail\Mime\PlainTextPart;
use Genkgo\Mail\Mime\RelatedPart;
use Traversable;

/**
 * Class FormattedMessageFactory
 * @package Genkgo\Email
 */
final class FormattedMessageFactory
{
    /**
     * @var array
     */
    private $attachments = [];
    /**
     * @var array
     */
    private $embedImages = [];
    /**
     * @var string
     */
    private $html;
    /**
     * @var string
     */
    private $text;

    /**
     * @param string $html
     * @return FormattedMessageFactory
     */
    public function withHtml(string $html): FormattedMessageFactory
    {
        $clone = clone $this;
        $clone->html = $html;
        $clone->text = $this->generateTextFromHtml($html);
        return $clone;
    }

    /**
     * @param string $html
     * @return FormattedMessageFactory
     */
    public function withHtmlAndNoGeneratedAlternativeText(string $html): FormattedMessageFactory
    {
        $clone = clone $this;
        $clone->html = $html;
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
     * @param string $text
     * @return FormattedMessageFactory
     */
    public function withAlternativeText(string $text): FormattedMessageFactory
    {
        $clone = clone $this;
        $clone->text = $text;
        return $clone;
    }

    /**
     * @return string
     */
    public function getAlternativeText(): string
    {
        return $this->text;
    }

    /**
     * @param PartInterface $part
     * @return FormattedMessageFactory
     */
    public function withAttachment(PartInterface $part): FormattedMessageFactory
    {
        try {
            $disposition = (string) $part->getHeader('Content-Disposition')->getValue();
            if (substr($disposition, 0, strpos($disposition, ';')) !== 'attachment') {
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
     * @param PartInterface $part
     * @return FormattedMessageFactory
     */
    public function withoutAttachment (PartInterface $part): FormattedMessageFactory
    {
        $clone = clone $this;

        $key = array_search($part, $this->attachments, true);
        if ($key !== false) {
            unset($clone->attachments[$key]);
        }

        return $clone;
    }

    /**
     * @return Traversable|PartInterface[]
     */
    public function getAttachments(): \Traversable
    {
        return new \ArrayIterator($this->attachments);
    }

    /**
     * @param PartInterface $part
     * @return FormattedMessageFactory
     */
    public function withEmbeddedImage(PartInterface $part): FormattedMessageFactory
    {
        try {
            $disposition = (string) $part->getHeader('Content-Disposition')->getValue();
            if (substr($disposition, 0, strpos($disposition, ';')) !== 'inline') {
                throw new \InvalidArgumentException(
                    'An embedded image must have Content-Disposition header with value `inline`'
                );
            }
        } catch (\UnexpectedValueException $e) {
            throw new \InvalidArgumentException(
                'An embedded image must have an Content-Disposition header'
            );
        }

        $clone = clone $this;
        $clone->embedImages[] = $part;
        return $clone;
    }

    /**
     * @param PartInterface $part
     * @return FormattedMessageFactory
     */
    public function withoutEmbeddedImage (PartInterface $part): FormattedMessageFactory
    {
        $clone = clone $this;

        $key = array_search($part, $this->embedImages, true);
        if ($key !== false) {
            unset($clone->embedImages[$key]);
        }

        return $clone;
    }

    /**
     * @return Traversable|PartInterface[]
     */
    public function getEmbeddedImages(): \Traversable
    {
        return new \ArrayIterator($this->embedImages);
    }

    /**
     * @param string $html
     * @return string
     */
    private function generateTextFromHtml(string $html)
    {
        return strip_tags($html);
    }

    /**
     * @return MessageInterface
     */
    public function createMessage(): MessageInterface
    {
        return new MimeMessage(
            (new Message())->withPart($this->createMessageRoot())
        );
    }

    /**
     * @return PartInterface
     */
    private function createMessageRoot(): PartInterface
    {
        if ($this->attachments) {
            return (new MixedPart(Boundary::newRandomBoundary()))
                ->withPart($this->createMessageHumanReadable())
                ->withParts($this->attachments)
            ;
        }

        return $this->createMessageHumanReadable();
    }

    /**
     * @return PartInterface
     */
    private function createMessageHumanReadable(): PartInterface
    {
        if ($this->embedImages) {
            return (new RelatedPart(Boundary::newRandomBoundary()))
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
        if ($this->text === null && $this->html === null) {
            return new PlainTextPart('');
        }

        if ($this->text === null) {
            return new HtmlPart($this->html);
        }

        return (new AlternativePart(Boundary::newRandomBoundary()))
            ->withPart(new PlainTextPart($this->text))
            ->withPart(new HtmlPart($this->html));
    }
}