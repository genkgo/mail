<?php
declare(strict_types=1);

namespace Genkgo\Mail\Header;

use Genkgo\Mail\Header\Dkim\CanonicalizeBodyInterface;
use Genkgo\Mail\Header\Dkim\CanonicalizeHeaderInterface;
use Genkgo\Mail\Header\Dkim\SignatureInterface;
use Genkgo\Mail\HeaderInterface;
use Genkgo\Mail\MessageInterface;

final class DkimSignatureV1 implements HeaderInterface
{
    /**
     *
     */
    private CONST HEADER_NAME = 'DKIM-Signature';
    /**
     * @var MessageInterface
     */
    private $message;
    /**
     * @var CanonicalizeBodyInterface
     */
    private $canonicalizeBody;
    /**
     * @var CanonicalizeHeaderInterface
     */
    private $canonicalizeHeader;
    /**
     * @var SignatureInterface
     */
    private $signing;
    /**
     * @var string
     */
    private $domain;
    /**
     * @var string
     */
    private $selector;
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
     * @param MessageInterface $message
     * @param CanonicalizeBodyInterface $canonicalizeBody
     * @param CanonicalizeHeaderInterface $canonicalizeHeader
     * @param SignatureInterface $signing
     * @param string $domain
     * @param string $selector
     */
    public function __construct(
        MessageInterface $message,
        CanonicalizeBodyInterface $canonicalizeBody,
        CanonicalizeHeaderInterface $canonicalizeHeader,
        SignatureInterface $signing,
        string $domain,
        string $selector
    ) {
        if (!$message->hasHeader('from')) {
            throw new \InvalidArgumentException(
                'In order add a DKIM signature the message MUST include a From header'
            );
        }

        $this->message = $message;
        $this->canonicalizeBody = $canonicalizeBody;
        $this->canonicalizeHeader = $canonicalizeHeader;
        $this->signing = $signing;
        $this->domain = $domain;
        $this->selector = $selector;
    }

    /**
     * @return HeaderName
     */
    public function getName(): HeaderName
    {
        return new HeaderName(self::HEADER_NAME);
    }

    /**
     * @return HeaderValue
     */
    public function getValue(): HeaderValue
    {
        $bodyHash = $this->signing->hashBody(
            $this->canonicalizeBody->canonicalize((string) $this->message->getBody())
        );

        $headerCanonicalized = [];
        $headerNames = [];
        foreach ($this->message->getHeaders() as $headers) {
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

        $canonicalizedParameter = [$this->canonicalizeHeader::getName(), $this->canonicalizeBody::getName()];

        $headerValue = (new HeaderValue('v=1'))
            ->withParameter($this->signing->createAlgorithmParameters())
            ->withParameter(new HeaderValueParameter('c', implode('/', $canonicalizedParameter)))
            ->withParameter(new HeaderValueParameter('q', 'dns/txt'))
            ->withParameter(new HeaderValueParameter('s', $this->selector))
            ->withParameter(new HeaderValueParameter('d', $this->domain, true))
            ->withParameter(new HeaderValueParameter('h', implode(' : ', $headerNames), true))
            ->withParameter(new HeaderValueParameter('bh', base64_encode($bodyHash), true))
            ->withParameter(new HeaderValueParameter('b', '', true));

        $headerCanonicalized[strtolower(self::HEADER_NAME)] = $this->canonicalizeHeader->canonicalize(
            new GenericHeader(self::HEADER_NAME, (string) $headerValue)
        );

        $signature = base64_encode($this->signing->signHeaders(implode('', $headerCanonicalized)));
        return $headerValue->withParameter(new HeaderValueParameter('b', trim($signature), true));
    }
}