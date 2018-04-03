<?php
declare(strict_types=1);

namespace Genkgo\Mail;

use Genkgo\Mail\Header\Cc;
use Genkgo\Mail\Header\ContentType;
use Genkgo\Mail\Header\GenericHeader;
use Genkgo\Mail\Header\HeaderName;
use Genkgo\Mail\Header\ParsedHeader;
use Genkgo\Mail\Header\Subject;
use Genkgo\Mail\Header\To;
use Genkgo\Mail\Mime\Boundary;
use Genkgo\Mail\Mime\EmbeddedImage;
use Genkgo\Mail\Mime\HtmlPart;
use Genkgo\Mail\Mime\MultiPart;
use Genkgo\Mail\Mime\MultiPartInterface;
use Genkgo\Mail\Mime\PartInterface;
use Genkgo\Mail\Mime\PlainTextPart;
use Genkgo\Mail\Mime\ResourceAttachment;

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
     * @param MessageInterface $message
     * @return MessageBodyCollection
     */
    public function withAttachedMessage(MessageInterface $message): self
    {
        return $this
            ->withAttachment(
                ResourceAttachment::fromString(
                    (string)$message,
                    \transliterator_transliterate(
                        'Any-Latin; NFD; [:Nonspacing Mark:] Remove; NFC; [:Punctuation:] Remove;',
                        $this->extractSubject($message)
                    ) . '.eml',
                    new ContentType('message/rfc822')
                )
            );
    }

    /**
     * @param MessageInterface $message
     * @param QuotationInterface $quotation
     * @return MessageBodyCollection
     */
    public function withQuotedMessage(MessageInterface $message, QuotationInterface $quotation): self
    {
        return $quotation->quote($this, $message);
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
     * @param MessageInterface $originalMessage
     * @return MessageInterface
     */
    public function inReplyTo(MessageInterface $originalMessage): MessageInterface
    {
        return $this->newReply(
            $originalMessage,
            $originalMessage->hasHeader('Reply-To') ? ['Reply-To'] : ['From']
        );
    }

    /**
     * @param MessageInterface $originalMessage
     * @return MessageInterface
     */
    public function inReplyToAll(MessageInterface $originalMessage): MessageInterface
    {
        return $this->newReply(
            $originalMessage,
            $originalMessage->hasHeader('Reply-To') ? ['Reply-To'] : ['From', 'Cc']
        );
    }

    /**
     * @param MessageInterface $originalMessage
     * @param array $replyRecipientHeaderNames
     * @return MessageInterface
     */
    private function newReply(MessageInterface $originalMessage, array $replyRecipientHeaderNames): MessageInterface
    {
        $reply = $this
            ->createReferencedMessage($originalMessage)
            ->withHeader(new Subject('Re: ' . $this->extractSubject($originalMessage)));

        foreach ($replyRecipientHeaderNames as $replyRecipientHeaderName) {
            foreach ($originalMessage->getHeader($replyRecipientHeaderName) as $recipientHeader) {
                $reply = $reply->withHeader($this->determineReplyHeader($recipientHeader));
            }
        }

        return $reply;
    }

    /**
     * @param MessageInterface $originalMessage
     * @return MessageInterface
     */
    public function asForwardTo(MessageInterface $originalMessage): MessageInterface
    {
        return $this
            ->createReferencedMessage($originalMessage)
            ->withHeader(new Subject('Fwd: ' . $this->extractSubject($originalMessage)));
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

    /**
     * @param MessageInterface $message
     * @return string
     */
    private function extractSubject(MessageInterface $message): string
    {
        foreach ($message->getHeader('Subject') as $header) {
            return $header->getValue()->getRaw();
        }

        return '';
    }

    /**
     * @param HeaderInterface $header
     * @return HeaderInterface
     */
    private function determineReplyHeader(HeaderInterface $header): HeaderInterface
    {
        $headerName = $header->getName();
        if ($headerName->equals(new HeaderName('Reply-To')) || $headerName->equals(new HeaderName('From'))) {
            return new To(AddressList::fromString((string)$header->getValue()));
        }

        return new Cc(AddressList::fromString((string)$header->getValue()));
    }

    /**
     * @param MessageInterface $originalMessage
     * @return MessageInterface
     */
    private function createReferencedMessage(MessageInterface $originalMessage): MessageInterface
    {
        $reply = $this->createMessage();

        foreach ($originalMessage->getHeader('Message-ID') as $messageIdHeader) {
            $references = $messageIdHeader->getValue()->getRaw();
            foreach ($originalMessage->getHeader('References') as $referenceHeader) {
                $references = $referenceHeader->getValue()->getRaw() . ', ' . $references;
            }

            return $reply
                ->withHeader(
                    new ParsedHeader(
                        new HeaderName('In-Reply-To'),
                        $messageIdHeader->getValue()
                    )
                )
                ->withHeader(new GenericHeader('References', $references));
        }

        return $reply;
    }
}
