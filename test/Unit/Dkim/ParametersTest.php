<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Dkim;

use Genkgo\Mail\Dkim\Parameters;
use Genkgo\TestMail\AbstractTestCase;

final class ParametersTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_creates_a_header_value()
    {
        $parameters = new Parameters('x', 'example.com');
        $headerValue = $parameters->newHeaderValue();

        $this->assertEquals("v=1; q=dns/txt;\r\n d=example.com; s=x", (string)$headerValue);
    }

    /**
     * @test
     */
    public function it_internationalizes_selector_and_domain()
    {
        $parameters = new Parameters('ë', 'täst.de');
        $headerValue = $parameters->newHeaderValue();

        $this->assertEquals("v=1; q=dns/txt;\r\n d=xn--tst-qla.de; s=xn--cda", (string)$headerValue);
    }

    /**
     * @test
     */
    public function it_adds_signature_timestamp_in_correct_timezone()
    {
        $parameters = (new Parameters('x', 'example.com'))
            ->withSignatureTimestamp(
                new \DateTimeImmutable('1/1/2017 12:00:00', new \DateTimeZone('Europe/Amsterdam'))
            );

        $headerValue = $parameters->newHeaderValue();

        $this->assertEquals("v=1; q=dns/txt;\r\n d=example.com; s=x; t=1483268400", (string)$headerValue);
    }

    /**
     * @test
     */
    public function it_adds_signature_expiration_in_correct_timezone()
    {
        $parameters = (new Parameters('x', 'example.com'))
            ->withSignatureExpiration(
                new \DateTimeImmutable('1/1/2017 12:00:00', new \DateTimeZone('Europe/Amsterdam'))
            );

        $headerValue = $parameters->newHeaderValue();

        $this->assertEquals("v=1; q=dns/txt;\r\n d=example.com; s=x; x=1483268400", (string)$headerValue);
    }
}
