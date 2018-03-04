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
    public function it_throws_with_wrong_values()
    {
        $this->expectException(\InvalidArgumentException::class);
        new HeaderValueParameter('test', 'ë');
    }

    /**
     * @test
     */
    public function it_throws_with_new_line()
    {
        $this->expectException(\InvalidArgumentException::class);
        new HeaderValueParameter('test', "\n");
    }

    /**
     * @test
     */
    public function it_encodes_with_t_specials()
    {
        $parameter = new HeaderValueParameter('test', 'something(else)');
        $this->assertEquals('test="something(else)"', (string)$parameter);
    }

    /**
     * @test
     */
    public function it_can_parse_a_string()
    {
        $parameter = HeaderValueParameter::fromString('charset="utf-8"');

        $this->assertEquals((string)$parameter, 'charset=utf-8');
        $this->assertEquals('charset', $parameter->getName());
        $this->assertEquals('utf-8', $parameter->getValue());
    }

    /**
     * @test
     */
    public function it_can_parse_an_unquoted_string()
    {
        $parameter = HeaderValueParameter::fromString('charset=utf-8');

        $this->assertEquals((string)$parameter, 'charset=utf-8');
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
