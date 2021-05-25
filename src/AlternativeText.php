<?php
declare(strict_types=1);

namespace Genkgo\Mail;

final class AlternativeText
{
    private const DEFAULT_CHARSET = '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>';

    /**
     * @var string
     */
    private $text;

    /**
     * @var bool
     */
    private $shouldNormalizeSpace = true;

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
        if ($this->shouldNormalizeSpace) {
            return $this->normalizeSpace($this->text);
        }

        return $this->text;
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
     * @param string $text
     * @param string $charset
     * @return AlternativeText
     */
    public static function fromEncodedText(string $text, string $charset): AlternativeText
    {
        if ($charset === '') {
            return new self($text);
        }

        $charset = \strtoupper($charset);
        if ($charset === 'UTF-8' || $charset === 'UTF8') {
            return new self($text);
        }

        $converted = \iconv($charset, 'UTF-8', $text);
        if ($converted === false) {
            throw new \InvalidArgumentException(
                'The encoded text cannot be converted to UTF-8. Is the charset ' . $charset . ' correct?'
            );
        }

        return new self($converted);
    }

    /**
     * @param string $text
     * @return AlternativeText
     */
    public static function fromRawString(string $text): AlternativeText
    {
        $self = new self($text);
        $self->shouldNormalizeSpace = false;
        return $self;
    }

    /**
     * @param string $html
     * @return AlternativeText
     */
    public static function fromHtml(string $html): AlternativeText
    {
        if ($html === '') {
            return new self($html);
        }

        $html = self::ensureHtmlCharset($html);
        $html = \preg_replace('/\h\h+/', ' ', (string)$html);
        $html = \preg_replace('/\v/', '', (string)$html);
        $text = new self((string)$html);

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

        $query = $xpath->query('//p|//br|//h1|//h2|//h3|//h4|//h5|//h6|//ul|//ol|//dl|//hr');
        if ($query) {
            /** @var \DOMElement $element */
            foreach ($query as $element) {
                if (isset($break[$element->nodeName])) {
                    $textNode = $document->createTextNode($break[$element->nodeName]);
                } else {
                    $textNode = $document->createTextNode("\r\n\r\n");
                }

                $element->appendChild($textNode);
            }
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

        $query = $xpath->query('//h1|//h2|//h3|//h4|//h5|//h6|//strong|//b|//em|//i');
        if ($query) {
            /** @var \DOMElement $element */
            foreach ($query as $element) {
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
    }

    /**
     * @param \DOMDocument $document
     */
    private function updateLists(\DOMDocument $document): void
    {
        $xpath = new \DOMXPath($document);

        $query = $xpath->query('//ul/li');
        if ($query) {
            /** @var \DOMElement $element */
            foreach ($query as $element) {
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
        }

        $query = $xpath->query('//ol/li');
        if ($query) {
            /** @var \DOMElement $element */
            foreach ($query as $element) {
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
        }

        $query = $xpath->query('//dl/dt');
        if ($query) {
            /** @var \DOMElement $element */
            foreach ($query as $element) {
                $element->appendChild(
                    $document->createTextNode(': ')
                );
            }
        }

        $query = $xpath->query('//dl/dd');
        if ($query) {
            /** @var \DOMElement $element */
            foreach ($query as $element) {
                $element->appendChild(
                    $document->createTextNode("\r\n")
                );
            }
        }
    }

    /**
     * @param \DOMDocument $document
     */
    private function updateImages(\DOMDocument $document): void
    {
        $xpath = new \DOMXPath($document);
        $query = $xpath->query('//img[@src and @alt]');

        if ($query) {
            /** @var \DOMElement $element */
            foreach ($query as $element) {
                $link = $document->createElement('a');
                $link->setAttribute('href', $element->getAttribute('src'));
                $link->textContent = $element->getAttribute('alt');
                $parent = $element->parentNode;
                if ($parent) {
                    $parent->replaceChild($link, $element);
                }
            }
        }
    }

    /**
     * @param \DOMDocument $document
     */
    private function updateHorizontalRule(\DOMDocument $document): void
    {
        $xpath = new \DOMXPath($document);
        $query = $xpath->query('//hr');

        if ($query) {
            /** @var \DOMElement $element */
            foreach ($query as $element) {
                $element->textContent = \str_repeat('=', 78);
            }
        }
    }

    /**
     * @param \DOMDocument $document
     */
    private function updateLinks(\DOMDocument $document): void
    {
        $xpath = new \DOMXPath($document);
        $query = $xpath->query('//a[@href and @href != .]');

        if ($query) {
            /** @var \DOMElement $element */
            foreach ($query as $element) {
                if ($element->firstChild) {
                    $element->insertBefore(
                        $document->createTextNode('>> '),
                        $element->firstChild
                    );
                }

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
    }

    /**
     * @param \DOMDocument $document
     */
    private function removeHead(\DOMDocument $document): void
    {
        $heads = $document->getElementsByTagName('head');
        while ($heads->length > 0) {
            /** @var \DOMElement $head */
            $head = $heads->item(0);
            /** @var \DOMElement $parent */
            $parent = $head->parentNode;
            $parent->removeChild($head);
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

    /**
     * @param string $html
     * @return string
     */
    private static function ensureHtmlCharset(string $html): string
    {
        if ($html === '') {
            return '';
        }

        if (\strpos($html, 'content="text/html') !== false || \strpos($html, 'charset="') !== false) {
            return $html;
        }

        $headCloseStart = \strpos($html, '</head>');
        if ($headCloseStart !== false) {
            return \substr_replace($html, self::DEFAULT_CHARSET, $headCloseStart, 0);
        }

        $bodyOpenStart = \strpos($html, '<body');
        if ($bodyOpenStart !== false) {
            return \substr_replace($html, '<head>' . self::DEFAULT_CHARSET . '</head>', $bodyOpenStart, 0);
        }

        return '<html><head>' . self::DEFAULT_CHARSET . '</head><body>' . $html . '</body></html>';
    }
}
