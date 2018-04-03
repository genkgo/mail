<?php
declare(strict_types=1);

namespace Genkgo\Mail;

final class AlternativeText
{
    /**
     * @var string
     */
    private $text;

    /**
     * @param string $text
     */
    public function __construct(string $text)
    {
        $this->text = $text;
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->text === '';
    }

    /**
     * @return string
     */
    public function getRaw(): string
    {
        return $this->text;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->normalizeSpace($this->text);
    }

    /**
     * @param string $string
     * @return string
     */
    private function normalizeSpace(string $string): string
    {
        return $this->wrap(
            \str_replace(
                ["  ", "\n ", " \n", " \r\n", "\t"],
                [" ", "\n", "\n", "\r\n", "    "],
                \trim($string)
            )
        );
    }

    /**
     * @param string $html AlternativeText
     * @return AlternativeText
     */
    public static function fromHtml(string $html): AlternativeText
    {
        if ($html === '') {
            return new self($html);
        }

        $html = \preg_replace('/\h\h+/', ' ', $html);
        $html = \preg_replace('/\v/', '', $html);
        $text = new self($html);

        try {
            $document = new \DOMDocument();
            $result = @$document->loadHTML($text->text);
            if ($result === false) {
                throw new \DOMException('Incorrect HTML');
            }

            $text->wrapSymbols($document);
            $text->updateHorizontalRule($document);
            $text->updateLists($document);
            $text->updateImages($document);
            $text->updateLinks($document);
            $text->removeHead($document);
            $text->updateParagraphsAndBreaksToNewLine($document);

            $text->text = $document->textContent;
        } catch (\DOMException $e) {
            $text->text = \strip_tags($text->text);
        }

        return $text;
    }

    /**
     * @param \DOMDocument $document
     */
    private function updateParagraphsAndBreaksToNewLine(\DOMDocument $document): void
    {
        $xpath = new \DOMXPath($document);
        $break = [
            'br' => "\r\n",
            'ul' => "\r\n",
            'ol' => "\r\n",
            'dl' => "\r\n",
        ];

        /** @var \DOMElement $element */
        foreach ($xpath->query('//p|//br|//h1|//h2|//h3|//h4|//h5|//h6|//ul|//ol|//dl|//hr') as $element) {
            if (isset($break[$element->nodeName])) {
                $textNode = $document->createTextNode($break[$element->nodeName]);
            } else {
                $textNode = $document->createTextNode("\r\n\r\n");
            }

            $element->appendChild($textNode);
        }
    }

    /**
     * @param \DOMDocument $document
     */
    private function wrapSymbols(\DOMDocument $document): void
    {
        $xpath = new \DOMXPath($document);
        $wrap = [
            'h1' => "*",
            'h2' => "**",
            'h3' => "***",
            'h4' => "****",
            'h5' => "*****",
            'h6' => "******",
            'strong' => "*",
            'b' => "*",
            'em' => "*",
            'i' => "*",
        ];

        /** @var \DOMElement $element */
        foreach ($xpath->query('//h1|//h2|//h3|//h4|//h5|//h6|//strong|//b|//em|//i') as $element) {
            $element->appendChild(
                $document->createTextNode($wrap[$element->nodeName])
            );

            if ($element->firstChild !== null) {
                $element->insertBefore(
                    $document->createTextNode($wrap[$element->nodeName]),
                    $element->firstChild
                );
            }
        }
    }

    /**
     * @param \DOMDocument $document
     */
    private function updateLists(\DOMDocument $document): void
    {
        $xpath = new \DOMXPath($document);

        /** @var \DOMElement $element */
        foreach ($xpath->query('//ul/li') as $element) {
            if ($element->firstChild !== null) {
                $element->insertBefore(
                    $document->createTextNode("\t- "),
                    $element->firstChild
                );
            } else {
                $element->appendChild(
                    $document->createTextNode("\t- ")
                );
            }

            $element->appendChild(
                $document->createTextNode("\r\n")
            );
        }

        /** @var \DOMElement $element */
        foreach ($xpath->query('//ol/li') as $element) {
            $itemPath = new \DOMXPath($document);
            $itemNumber = (int)$itemPath->evaluate('string(count(preceding-sibling::li))', $element) + 1;
            $text = \sprintf("\t%d. ", $itemNumber);

            if ($element->firstChild !== null) {
                $element->insertBefore(
                    $document->createTextNode($text),
                    $element->firstChild
                );
            } else {
                $element->appendChild(
                    $document->createTextNode($text)
                );
            }

            $element->appendChild(
                $document->createTextNode("\r\n")
            );
        }

        /** @var \DOMElement $element */
        foreach ($xpath->query('//dl/dt') as $element) {
            $element->appendChild(
                $document->createTextNode(': ')
            );
        }

        /** @var \DOMElement $element */
        foreach ($xpath->query('//dl/dd') as $element) {
            $element->appendChild(
                $document->createTextNode("\r\n")
            );
        }
    }

    /**
     * @param \DOMDocument $document
     */
    private function updateImages(\DOMDocument $document): void
    {
        $xpath = new \DOMXPath($document);

        /** @var \DOMElement $element */
        foreach ($xpath->query('//img[@src and @alt]') as $element) {
            $link = $document->createElement('a');
            $link->setAttribute('href', $element->getAttribute('src'));
            $link->textContent = $element->getAttribute('alt');
            $element->parentNode->replaceChild($link, $element);
        }
    }

    /**
     * @param \DOMDocument $document
     */
    private function updateHorizontalRule(\DOMDocument $document): void
    {
        $xpath = new \DOMXPath($document);

        /** @var \DOMElement $element */
        foreach ($xpath->query('//hr') as $element) {
            $element->textContent = \str_repeat('=', 78);
        }
    }

    /**
     * @param \DOMDocument $document
     */
    private function updateLinks(\DOMDocument $document): void
    {
        $xpath = new \DOMXPath($document);

        /** @var \DOMElement $element */
        foreach ($xpath->query('//a[@href and @href != .]') as $element) {
            $element->insertBefore(
                $document->createTextNode('>> '),
                $element->firstChild
            );

            $element->appendChild(
                $document->createTextNode(
                    \sprintf(
                        " <%s>",
                        $element->getAttribute('href')
                    )
                )
            );
        }
    }

    /**
     * @param \DOMDocument $document
     */
    private function removeHead(\DOMDocument $document): void
    {
        $heads = $document->getElementsByTagName('head');
        while ($heads->length > 0) {
            $head = $heads->item(0);
            $head->parentNode->removeChild($head);
        }
    }

    /**
     * @param string $unwrappedText
     * @param int $width
     * @return string
     */
    private function wrap(string $unwrappedText, int $width = 75): string
    {
        $result = [];
        $carriageReturn = false;
        $lineChars = -1;
        $quote = false;
        $quoteLength = 0;

        $iterator = \IntlBreakIterator::createCharacterInstance(\Locale::getDefault());
        $iterator->setText($unwrappedText);
        foreach ($iterator->getPartsIterator() as $char) {
            if ($char === "\r\n") {
                $lineChars = -1;
                $quoteLength = 0;
                $quote = false;
            } elseif ($char === "\r") {
                $carriageReturn = true;
            } elseif ($char === "\n") {
                if (!$carriageReturn) {
                    $char = "\r\n";
                }

                $lineChars = -1;
                $quoteLength = 0;
                $quote = false;
            }

            if ($char !== "\r") {
                $carriageReturn = false;
            }

            if ($lineChars >= $width && \IntlChar::isWhitespace($char)) {
                $char = "\r\n" . \str_pad('', $quoteLength, '>');
                $lineChars = -1;
                $quoteLength = 0;
                $quote = false;
            }

            $result[] = $char;
            $lineChars++;

            if ($lineChars === 1 && $char === ">") {
                $quote = true;
                $quoteLength = 1;
            } elseif ($quote && $char === ">") {
                $quoteLength++;
            } else {
                $quote = false;
            }
        }

        return \implode('', $result);
    }
}
