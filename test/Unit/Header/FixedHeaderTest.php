<?php 
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Header;

use Genkgo\Mail\Header\FixedHeader;
use Genkgo\Mail\Header\HeaderName;
use Genkgo\Mail\Header\HeaderValue;
use Genkgo\TestMail\AbstractTestCase;

final class FixedHeaderTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_produces_correct_values()
    {
        $header = new FixedHeader(new HeaderName('Name'), new HeaderValue('Value'));

        $this->assertEquals('Name', (string)$header->getName());
        $this->assertEquals('Value', (string)$header->getValue());
    }
}
