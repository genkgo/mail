<?php
declare(strict_types=1);

namespace Genkgo\Mail;

use Genkgo\Mail\Mime\EmbeddedImage;
use Genkgo\Mail\Mime\PartInterface;

/**
 * This class is superseded by MessageBodyCollection. Since there is no need to remove or deprecate this
 * class, it will remain part of the package.
 */
final class FormattedMessageFactory
{
    /**
     * @var MessageBodyCollection
     */
    private $messageBody;

    public function __construct()
    {
        $this->messageBody = new MessageBodyCollection();
    }

    /**
     * @param string $html
     * @return FormattedMessageFactory
     */
    public function withHtml(string $html): FormattedMessageFactory
    {
        $clone = clone $this;
        $clone->messageBody = $clone->messageBody
            ->withHtml($html)
            ->withAlternativeText(AlternativeText::fromHtml($html));
        return $clone;
    }

    /**
     * @param string $html
     * @return FormattedMessageFactory
     */
    public function withHtmlAndNoGeneratedAlternativeText(string $html): FormattedMessageFactory
    {
        $clone = clone $this;
        $clone->messageBody = $clone->messageBody->withHtml($html);
        return $clone;
    }

    /**
     * @param AlternativeText $text
     * @return FormattedMessageFactory
     */
    public function withAlternativeText(AlternativeText $text): FormattedMessageFactory
    {
        $clone = clone $this;
        $clone->messageBody = $clone->messageBody->withAlternativeText($text);
        return $clone;
    }

    /**
     * @param PartInterface $part
     * @return FormattedMessageFactory
     */
    public function withAttachment(PartInterface $part): FormattedMessageFactory
    {
        $clone = clone $this;
        $clone->messageBody = $clone->messageBody->withAttachment($part);
        return $clone;
    }

    /**
     * @param EmbeddedImage $embeddedImage
     * @return FormattedMessageFactory
     */
    public function withEmbeddedImage(EmbeddedImage $embeddedImage): FormattedMessageFactory
    {
        $clone = clone $this;
        $clone->messageBody = $clone->messageBody->withEmbeddedImage($embeddedImage);
        return $clone;
    }

    /**
     * @return MessageInterface
     */
    public function createMessage(): MessageInterface
    {
        return $this->messageBody->createMessage();
    }
}
