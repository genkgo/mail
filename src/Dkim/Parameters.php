<?php
declare(strict_types=1);

namespace Genkgo\Mail\Dkim;

use Genkgo\Mail\Header\HeaderValue;
use Genkgo\Mail\Header\HeaderValueParameter;

final class Parameters
{
    /**
     * @var string
     */
    private $selector;

    /**
     * @var string
     */
    private $domain;

    /**
     * @var \DateTimeImmutable
     */
    private $signatureTimestamp;

    /**
     * @var \DateTimeImmutable
     */
    private $signatureExpiration;

    /**
     * @param string $selector
     * @param string $domain
     */
    public function __construct(string $selector, string $domain)
    {
        $this->selector = $selector;
        $this->domain = $domain;
    }

    /**
     * @param \DateTimeImmutable $moment
     * @return Parameters
     */
    public function withSignatureTimestamp(\DateTimeImmutable $moment): self
    {
        $clone = clone $this;
        $clone->signatureTimestamp = $moment;
        return $clone;
    }

    /**
     * @param \DateTimeImmutable $moment
     * @return Parameters
     */
    public function withSignatureExpiration(\DateTimeImmutable $moment): self
    {
        $clone = clone $this;
        $clone->signatureExpiration = $moment;
        return $clone;
    }

    /**
     * @return HeaderValue
     */
    public function newHeaderValue(): HeaderValue
    {
        $selector = \idn_to_ascii($this->selector, 0, INTL_IDNA_VARIANT_UTS46);

        // @codeCoverageIgnoreStart
        if ($selector === false) {
            throw new \UnexpectedValueException('Cannot encode selector as A-label');
        }

        $domain = \idn_to_ascii($this->domain, 0, INTL_IDNA_VARIANT_UTS46);
        if ($domain === false) {
            throw new \UnexpectedValueException('Cannot encode domain as A-label');
        }
        // @codeCoverageIgnoreEnd

        $headerValue = (new HeaderValue('v=1'))
            ->withParameter((new HeaderValueParameter('q', 'dns/txt'))->withSpecials(''))
            ->withParameter((new HeaderValueParameter('d', $domain))->withSpecials(''), true)
            ->withParameter((new HeaderValueParameter('s', $selector))->withSpecials(''));

        if ($this->signatureTimestamp !== null) {
            $signatureTimestamp = $this
                ->signatureTimestamp
                ->setTimezone(new \DateTimeZone('UTC'))
                ->format('U');

            $headerValue = $headerValue->withParameter((new HeaderValueParameter('t', $signatureTimestamp)));
        }

        if ($this->signatureExpiration !== null) {
            $signatureExpiration = $this
                ->signatureExpiration
                ->setTimezone(new \DateTimeZone('UTC'))
                ->format('U');

            $headerValue = $headerValue->withParameter((new HeaderValueParameter('x', $signatureExpiration)));
        }

        return $headerValue;
    }
}
