<?php
declare(strict_types=1);

namespace Genkgo\Mail\Quotation;

use Genkgo\Mail\Address;
use Genkgo\Mail\AlternativeText;
use Genkgo\Mail\MessageBodyCollection;
use Genkgo\Mail\MessageInterface;
use Genkgo\Mail\QuotationInterface;

final class FixedQuotation implements QuotationInterface
{
    /**
     * @var string
     */
    private $headerText;

    /**
     * @param string $headerText
     */
    public function __construct(string $headerText = '%s (%s):')
    {
        $this->headerText = $headerText;
    }

    /**
     * @param MessageBodyCollection $body
     * @param MessageInterface $originalMessage
     * @return MessageBodyCollection
     * @throws \DOMException
     */
    public function quote(MessageBodyCollection $body, MessageInterface $originalMessage): MessageBodyCollection
    {
        $originalBody = MessageBodyCollection::extract($originalMessage);
        $dateString = 'unknown';
        foreach ($originalMessage->getHeader('Date') as $header) {
            try {
                $date = new \DateTimeImmutable($header->getValue()->getRaw());
                $dateString = \IntlDateFormatter::create(
                    \Locale::getDefault(),
                    \IntlDateFormatter::MEDIUM,
                    \IntlDateFormatter::MEDIUM
                )->format($date);
            } catch (\Exception $e) {
            }
        }

        $fromString = '';
        foreach ($originalMessage->getHeader('From') as $header) {
            $from = Address::fromString($header->getValue()->getRaw());
            $fromString = $from->getName() ? $from->getName() : (string)$from->getAddress();
        }

        $headerText = \sprintf($this->headerText, $fromString, $dateString);

        return $body
            ->withHtmlAndNoGeneratedAlternativeText(
                $this->quoteHtml(
                    $body->getHtml(),
                    $originalBody->getHtml(),
                    $headerText
                )
            )
            ->withAlternativeText(
                $this->quoteText(
                    $body->getText(),
                    $originalBody->getText(),
                    $headerText
                )
            );
    }

    /**
     * @param string $newHtml
     * @param string $originalHtml
     * @param string $headerText
     * @return string
     * @throws \DOMException
     */
    private function quoteHtml(string $newHtml, string $originalHtml, string $headerText): string
    {
        $originalHtml = \trim($originalHtml);
        if ($originalHtml === '') {
            return '';
        }

        $document = new \DOMDocument();
        $document->substituteEntities = false;
        $document->resolveExternals = false;

        $result = @$document->loadHTML($originalHtml);
        if ($result === false) {
            throw new \DOMException('Incorrect HTML');
        }

        $query = new \DOMXPath($document);
        $removeItems = $query->query('//head|//script|//body/@style|//html/@style', $document->documentElement);
        /** @var \DOMElement $removeItem */
        foreach ($removeItems as $removeItem) {
            $parent = $removeItem->parentNode;
            $parent->removeChild($removeItem);
        }

        $body = $document->getElementsByTagName('body');
        $quote = $document->createElement('blockquote');
        $quote->setAttribute('type', 'cite');

        if ($body->length === 0) {
            $quote->appendChild($document->removeChild($document->documentElement));
        } else {
            $root = $body->item(0);
            while ($root->childNodes->length !== 0) {
                $quote->appendChild($root->childNodes->item(0));
            }
        }

        $newDocument = new \DOMDocument();
        $newDocument->substituteEntities = false;
        $newDocument->resolveExternals = false;
        $result = @$newDocument->loadHTML($newHtml, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        if ($result === false) {
            throw new \DOMException('Incorrect HTML');
        }

        $quotedNode = $newDocument->importNode($quote, true);
        $newBody = $this->prepareBody($newDocument);
        $newBody->appendChild($quotedNode);

        $header = $newDocument->createElement('p');
        $header->textContent = $headerText;

        $quotedNode->parentNode->insertBefore($header, $quotedNode);
        return \trim($newDocument->saveHTML());
    }

    /**
     * @param \DOMDocument $document
     * @return \DOMElement
     */
    private function prepareBody(\DOMDocument $document): \DOMElement
    {
        $bodyList = $document->getElementsByTagName('body');
        if ($bodyList->length === 0) {
            $html = $document->createElement('html');
            $body = $document->createElement('body');
            $html->appendChild($body);
            $body->appendChild($document->documentElement);
            $document->removeChild($document->documentElement);
            $document->appendChild($html);
            return $body;
        }

        $body = $bodyList->item(0);

        $queryHtml = new \DOMXPath($document);
        $htmlTags = $queryHtml->query('//html');
        if ($htmlTags->length > 0) {
            $html = $htmlTags->item(0);
            $html->appendChild($body);
            $document->removeChild($document->documentElement);
            $document->appendChild($html);
            return $body;
        }

        $html = $document->createElement('html');
        $html->appendChild($body);
        $document->removeChild($document->documentElement);
        $document->appendChild($html);
        return $body;
    }

    /**
     * @param AlternativeText $newText
     * @param AlternativeText $originalText
     * @param string $headerText
     * @return AlternativeText
     */
    private function quoteText(
        AlternativeText $newText,
        AlternativeText $originalText,
        string $headerText
    ): AlternativeText {
        return new AlternativeText(
            \sprintf(
                "%s\n\n%s\n>%s",
                (string)$newText,
                $headerText,
                \str_replace("\n", "\n>", $originalText->getRaw())
            )
        );
    }
}
