<?php 
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Header;

use Genkgo\Mail\Header\GenericHeader;
use Genkgo\TestMail\AbstractTestCase;

final class GenericHeaderTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_produces_correct_values()
    {
        $header = new GenericHeader('Name', 'Value');

        $this->assertEquals('Name', (string)$header->getName());
        $this->assertEquals('Value', (string)$header->getValue());
    }
}
