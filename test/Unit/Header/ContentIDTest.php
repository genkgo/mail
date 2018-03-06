<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Header;

use Genkgo\Mail\Header\ContentID;
use Genkgo\TestMail\AbstractTestCase;

final class ContentIDTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_can_be_constructed_with_any_string()
    {
        $header = new ContentID('abc123');
        $this->assertSame('abc123', (string)$header->getValue());
        $this->assertSame('Content-ID', (string)$header->getName());
    }

    /**
     * @test
     */
    public function it_can_be_created_from_an_url_address()
    {
        $header = ContentID::fromUrlAddress('local', 'domain.com');
        $this->assertSame('<local@domain.com>', (string)$header->getValue());
        $this->assertSame('Content-ID', (string)$header->getName());
    }

    /**
     * @test
     */
    public function it_uses_puny_code_for_domains()
    {
        $header = ContentID::fromUrlAddress('local', 'münchen.com');
        $this->assertSame('<local@xn--mnchen-3ya.com>', (string)$header->getValue());
        $this->assertSame('Content-ID', (string)$header->getName());
    }
}
