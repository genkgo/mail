<?php
declare(strict_types=1);

namespace Genkgo\Mail\Dkim;

use Genkgo\Mail\Header\HeaderName;
use Genkgo\Mail\Header\HeaderValue;
use Genkgo\Mail\Header\HeaderValueParameter;
use Genkgo\Mail\HeaderInterface;
use Genkgo\Mail\MessageInterface;

final class HeaderV1Factory
{
    public const HEADER_NAME = 'DKIM-Signature';

    /**
     * @var array
     */
    private const HEADERS_IGNORED = [
        'return-path' => true,
        'received' => true,
        'comments' => true,
        'keywords' => true,
        'bcc' => true,
        'resent-bcc' => true,
    ];

    /**
     * @var SignInterface
     */
    private $sign;

    /**
     * @var CanonicalizeHeaderInterface
     */
    private $canonicalizeHeader;

    /**
     * @var CanonicalizeBodyInterface
     */
    private $canonicalizeBody;

    /**
     * @param SignInterface $sign
     * @param CanonicalizeHeaderInterface $canonicalizeHeader
     * @param CanonicalizeBodyInterface $canonicalizeBody
     */
    public function __construct(
        SignInterface $sign,
        CanonicalizeHeaderInterface $canonicalizeHeader,
        CanonicalizeBodyInterface $canonicalizeBody
    ) {
        $this->sign = $sign;
        $this->canonicalizeHeader = $canonicalizeHeader;
        $this->canonicalizeBody = $canonicalizeBody;
    }

    /**
     * @param MessageInterface $message
     * @param Parameters $parameters
     * @return HeaderInterface
     */
    public function factory(
        MessageInterface $message,
        Parameters $parameters
    ): HeaderInterface {
        $headerCanonicalized = [];
        $headerNames = [];
        foreach ($message->getHeaders() as $headers) {
            /** @var HeaderInterface $header */
            foreach ($headers as $header) {
                $headerName = \strtolower((string)$header->getName());
                if (isset(self::HEADERS_IGNORED[$headerName])) {
                    break;
                }

                // make sure we sign only the last header in the list
                $headerCanonicalized[$headerName] = $this->canonicalizeHeader->canonicalize($header);
                $headerNames[$headerName] = (string)$header->getName();
            }
        }

        $bodyHash = $this->sign->hashBody(
            $this->canonicalizeBody->canonicalize($message->getBody())
        );

        $canonicalization = [$this->canonicalizeHeader->name(), $this->canonicalizeBody->name()];

        $headerValue = $parameters->newHeaderValue()
            ->withParameter($this->newUnquotedParameter('a', $this->sign->name()))
            ->withParameter($this->newUnquotedParameter('c', \implode('/', $canonicalization)))
            ->withParameter($this->newUnquotedParameter('h', \implode(':', $headerNames)), true)
            ->withParameter($this->newUnquotedParameter('bh', \base64_encode($bodyHash)), true)
            ->withParameter($this->newUnquotedParameter('b', ''), true);

        $headerCanonicalized[\strtolower(self::HEADER_NAME)] = $this->canonicalizeHeader->canonicalize(
            $this->newHeader($headerValue)
        );

        $headers = \implode("\r\n", $headerCanonicalized);
        $signature = \base64_encode($this->sign->signHeaders($headers));
        $headerValue = $headerValue->withParameter(new HeaderValueParameter('b', $signature), true);

        return $this->newHeader($headerValue);
    }

    /**
     * @param string $name
     * @param string $value
     * @return HeaderValueParameter
     */
    private function newUnquotedParameter(string $name, string $value): HeaderValueParameter
    {
        return (new HeaderValueParameter($name, $value))->withSpecials('');
    }

    /**
     * @param HeaderValue $headerValue
     * @return HeaderInterface
     */
    private function newHeader(HeaderValue $headerValue): HeaderInterface
    {
        return new class($headerValue) implements HeaderInterface {
            /**
             * @var HeaderValue
             */
            private $headerValue;

            /**
             * @param HeaderValue $headerValue
             */
            public function __construct(HeaderValue $headerValue)
            {
                $this->headerValue = $headerValue;
            }

            /**
             * @return HeaderName
             */
            public function getName(): HeaderName
            {
                return new HeaderName(HeaderV1Factory::HEADER_NAME);
            }

            /**
             * @return HeaderValue
             */
            public function getValue(): HeaderValue
            {
                return $this->headerValue;
            }
        };
    }
}
