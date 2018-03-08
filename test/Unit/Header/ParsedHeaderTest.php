<?php 
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Header;

use Genkgo\Mail\Header\ParsedHeader;
use Genkgo\Mail\Header\HeaderName;
use Genkgo\Mail\Header\HeaderValue;
use Genkgo\TestMail\AbstractTestCase;

final class ParsedHeaderTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_produces_correct_values()
    {
        $header = new ParsedHeader(new HeaderName('Name'), new HeaderValue('Value'));

        $this->assertEquals('Name', (string)$header->getName());
        $this->assertEquals('Value', (string)$header->getValue());
    }
}
