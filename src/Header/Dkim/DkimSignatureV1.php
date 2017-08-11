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
        return new HeaderName('DKIM-Signature');
    }

    /**
     * @return HeaderValue
     */
    public function getValue(): HeaderValue
    {
        $bodyHash = $this->signing->hashBody(
            $this->canonicalizeBody->canonicalize((string) $this->message->getBody())
        );

        $headerCanonicalized = '';
        $headerNames = [];
        foreach ($this->message->getHeaders() as $headers) {
            /** @var HeaderInterface $header */
            foreach ($headers as $header) {
                $headerCanonicalized .= $this->canonicalizeHeader->canonicalize($header);
                $headerNames[(string)$header->getName()] = true;
            }
        }

        $headerValue = (new HeaderValue('v=1'))
            ->withParameter(new HeaderValueParameter('a', 'rsa-sha256'))
            ->withParameter(new HeaderValueParameter('c', 'relaxed/simple'))
            ->withParameter(new HeaderValueParameter('q', 'dns/txt'))
            ->withParameter(new HeaderValueParameter('s', $this->selector))
            ->withParameter(new HeaderValueParameter('d', $this->domain, true))
            ->withParameter(new HeaderValueParameter('h', implode(': ', array_keys($headerNames)), true))
            ->withParameter(new HeaderValueParameter('bh', base64_encode($bodyHash), true))
            ->withParameter(new HeaderValueParameter('b', '', true));

        $headerCanonicalized .= $this->canonicalizeHeader->canonicalize(
            new GenericHeader('DKIM-Signature', (string) $headerValue)
        );

        $signature = trim(chunk_split(base64_encode($this->signing->signHeaders($headerCanonicalized)), 73));
        $headerValue = $headerValue->withParameter(new HeaderValueParameter('b', $signature));
        return $headerValue;
    }
}