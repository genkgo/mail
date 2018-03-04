<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Transport;

use Genkgo\Mail\Exception\EnvelopeException;
use Genkgo\TestMail\AbstractTestCase;
use Genkgo\Mail\Address;
use Genkgo\Mail\EmailAddress;
use Genkgo\Mail\GenericMessage;
use Genkgo\Mail\Header\From;
use Genkgo\Mail\Header\GenericHeader;
use Genkgo\Mail\Header\Sender;
use Genkgo\Mail\Transport\EnvelopeFactory;

final class EnvelopeFactoryTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_extracts_from_sender_header()
    {
        $factory = EnvelopeFactory::useExtractedHeader();

        $message = (new GenericMessage())
            ->withHeader(new Sender(new Address(new EmailAddress('a@b.com'))));

        $envelope = $factory->make($message);

        $this->assertEquals('a@b.com', $envelope->getAddress());
    }

    /**
     * @test
     */
    public function it_extracts_from_from_header()
    {
        $factory = EnvelopeFactory::useExtractedHeader();

        $message = (new GenericMessage())
            ->withHeader(new From(new Address(new EmailAddress('a@b.com'))));

        $envelope = $factory->make($message);

        $this->assertEquals('a@b.com', $envelope->getAddress());
    }

    /**
     * @test
     */
    public function it_extracts_raw_header_value()
    {
        $factory = EnvelopeFactory::useExtractedHeader();

        $message = (new GenericMessage())
            ->withHeader(new From(new Address(new EmailAddress('"this-is-a-very-long email-address-as-such-requiring-folding"@more-more-more-more.com'))));

        $envelope = $factory->make($message);

        $this->assertEquals('"this-is-a-very-long email-address-as-such-requiring-folding"@more-more-more-more.com', $envelope->getAddress());
    }

    /**
     * @test
     */
    public function it_extracts_from_from_header_when_sender_is_empty()
    {
        $factory = EnvelopeFactory::useExtractedHeader();

        $message = (new GenericMessage())
            ->withHeader(new GenericHeader('Sender', ''))
            ->withHeader(new From(new Address(new EmailAddress('a@b.com'))));

        $envelope = $factory->make($message);

        $this->assertEquals('a@b.com', $envelope->getAddress());
    }

    /**
     * @test
     */
    public function it_throws_when_cannot_extract()
    {
        $this->expectException(EnvelopeException::class);

        $factory = EnvelopeFactory::useExtractedHeader();

        $message = (new GenericMessage());

        $factory->make($message);
    }

    /**
     * @test
     */
    public function it_falls_back_when_specified()
    {
        $factory = EnvelopeFactory::useExtractedHeader()
            ->withFallback(new EmailAddress('fallback@example.com'));

        $message = (new GenericMessage());

        $envelope = $factory->make($message);

        $this->assertEquals('fallback@example.com', $envelope->getAddress());
    }

    /**
     * @test
     */
    public function it_uses_a_fixed_address()
    {
        $factory = EnvelopeFactory::useFixed(new EmailAddress('fixed@example.com'));

        $message = (new GenericMessage());

        $envelope = $factory->make($message);

        $this->assertEquals('fixed@example.com', $envelope->getAddress());
    }

    /**
     * @test
     */
    public function it_uses_a_callback()
    {
        $factory = EnvelopeFactory::useCallback(
            function () {
                return new EmailAddress('callback@example.com');
            }
        );

        $message = (new GenericMessage());

        $envelope = $factory->make($message);

        $this->assertEquals('callback@example.com', $envelope->getAddress());
    }

    /**
     * @test
     */
    public function it_uses_a_fallback_when_runtime_exception()
    {
        $factory = EnvelopeFactory::useCallback(
            function () {
                throw new EnvelopeException();
            }
        )->withFallback(new EmailAddress('fallback@example.com'));

        $message = (new GenericMessage());

        $envelope = $factory->make($message);

        $this->assertEquals('fallback@example.com', $envelope->getAddress());
    }

    /**
     * @test
     */
    public function it_does_not_use_the_fallback_when_other_than_runtime_exception()
    {
        $this->expectException(\InvalidArgumentException::class);

        $factory = EnvelopeFactory::useCallback(
            function () {
                throw new \InvalidArgumentException();
            }
        )->withFallback(new EmailAddress('fallback@example.com'));

        $message = (new GenericMessage());

        $factory->make($message);
    }
}
