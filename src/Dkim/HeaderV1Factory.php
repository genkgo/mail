<?php
declare(strict_types=1);

namespace Genkgo\Mail\Dkim;

use Genkgo\Mail\Header\GenericHeader;
use Genkgo\Mail\Header\HeaderName;
use Genkgo\Mail\Header\HeaderValue;
use Genkgo\Mail\Header\HeaderValueParameter;
use Genkgo\Mail\HeaderInterface;
use Genkgo\Mail\MessageInterface;

final class HeaderV1Factory
{
    /**
     *
     */
    public CONST HEADER_NAME = 'DKIM-Signature';
    /**
     * @var array
     */
    private CONST HEADERS_IGNORED = [
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
     * SignedTransport constructor.
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
     * @param string $domain
     * @param string $selector
     * @param array $parameters
     * @return HeaderInterface
     */
    public function factory(
        MessageInterface $message,
        string $domain,
        string $selector,
        array $parameters = []
    ): HeaderInterface
    {
        $bodyHash = $this->sign->hashBody(
            $this->canonicalizeBody->canonicalize($message->getBody())
        );

        $headerCanonicalized = [];
        $headerNames = [];
        foreach ($message->getHeaders() as $headers) {
            /** @var HeaderInterface $header */
            foreach ($headers as $header) {
                $headerName = strtolower((string)$header->getName());
                if (isset(self::HEADERS_IGNORED[$headerName])) {
                    break;
                }

                // make sure we sign only the last header in the list
                $headerCanonicalized[$headerName] = $this->canonicalizeHeader->canonicalize($header);
                $headerNames[$headerName] = (string)$header->getName();
            }
        }

        $canonicalization = [$this->canonicalizeHeader->name(), $this->canonicalizeBody->name()];

        $headerValue = (new HeaderValue('v=1'))
            ->withParameter(new HeaderValueParameter('a', $this->sign->name()))
            ->withParameter(new HeaderValueParameter('b', '', true))
            ->withParameter(new HeaderValueParameter('bh', base64_encode($bodyHash), true))
            ->withParameter(new HeaderValueParameter('c', implode('/', $canonicalization)))
            ->withParameter(new HeaderValueParameter('d', $domain, true))
            ->withParameter(new HeaderValueParameter('h', implode(' : ', $headerNames), true))
            ->withParameter(new HeaderValueParameter('q', 'dns/txt'))
            ->withParameter(new HeaderValueParameter('s', $selector));

        foreach ($parameters as $key => $value) {
            $headerValue = $headerValue->withParameter(new HeaderValueParameter($key, $value));
        }

        $headerCanonicalized[strtolower(self::HEADER_NAME)] = $this->canonicalizeHeader->canonicalize(
            new GenericHeader(self::HEADER_NAME, (string) $headerValue)
        );

        $signature = base64_encode($this->sign->signHeaders(implode('', $headerCanonicalized)));
        $headerValue = $headerValue->withParameter(new HeaderValueParameter('b', trim($signature), true));

        return new class ($headerValue) implements HeaderInterface
        {
            /**
             * @var HeaderValue
             */
            private $headerValue;

            /**
             * DkimSignature constructor.
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