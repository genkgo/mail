<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Header;

use Genkgo\Mail\Header\HeaderValueParameter;
use Genkgo\TestMail\AbstractTestCase;

final class HeaderValueParameterTest extends AbstractTestCase
{

    /**
     * @test
     */
    public function it_can_parse_a_string()
    {
        $parameter = HeaderValueParameter::fromString('charset="utf-8"');

        $this->assertEquals((string)$parameter, 'charset="utf-8"');
        $this->assertEquals($parameter->getName(), 'charset');
        $this->assertEquals($parameter->getValue(), 'utf-8');
    }

    /**
     * @test
     */
    public function it_can_parse_an_unquoted_string()
    {
        $parameter = HeaderValueParameter::fromString('charset=utf-8');

        $this->assertEquals((string)$parameter, 'charset="utf-8"');
        $this->assertEquals($parameter->getName(), 'charset');
        $this->assertEquals($parameter->getValue(), 'utf-8');
    }

    /**
     * @test
     */
    public function it_does_not_parse_invalid_values()
    {
        $this->expectException(\InvalidArgumentException::class);
        HeaderValueParameter::fromString('charset,utf-8');
    }

}