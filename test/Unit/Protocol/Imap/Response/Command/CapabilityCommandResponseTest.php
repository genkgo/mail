<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Imap\Response\Command;

use Genkgo\Mail\Protocol\Imap\Response\Command\CapabilityCommandResponse;
use Genkgo\TestMail\AbstractTestCase;

final class CapabilityCommandResponseTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_can_be_constructed_from_string()
    {
        $response = CapabilityCommandResponse::fromString('CAPABILITY IMAP4rev1 STARTTLS AUTH=GSSAPI');
        $this->assertSame('CAPABILITY IMAP4rev1 STARTTLS AUTH=GSSAPI', (string)$response);
    }

    /**
     * @test
     */
    public function it_can_be_checked_for_advertisements()
    {
        $response = CapabilityCommandResponse::fromString('CAPABILITY IMAP4rev1 STARTTLS AUTH=GSSAPI');
        $this->assertTrue($response->isAdvertising('IMAP4rev1'));
        $this->assertTrue($response->isAdvertising('STARTTLS'));
        $this->assertTrue($response->isAdvertising('AUTH=GSSAPI'));
        $this->assertFalse($response->isAdvertising(''));
    }

    /**
     * @test
     */
    public function it_throws_when_constructing_non_capability_command()
    {
        $this->expectException(\InvalidArgumentException::class);
        CapabilityCommandResponse::fromString('STORE COMMAND');
    }
}
