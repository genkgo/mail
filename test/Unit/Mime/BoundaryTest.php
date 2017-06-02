<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Mime;

use Genkgo\TestMail\AbstractTestCase;;
use Genkgo\Mail\Mime\Boundary;

final class BoundaryTest extends AbstractTestCase
{

    /**
     * @test
     * @dataProvider provideValues
     */
    public function it_validates_correct_boundary_values($boundaryString, $constructed)
    {
        if ($constructed) {
             $boundary = new Boundary($boundaryString);
             $this->assertEquals($boundaryString, (string) $boundary);
        } else {
            $this->expectException(\InvalidArgumentException::class);

            new Boundary($boundaryString);
        }
    }

    /**
     * @return array
     */
    public function provideValues()
    {
        return [
            ['correct', true],
            ["incorrect \r\n", false],
            ["incorrect \r", false],
            ["incorrect \n", false],
            ["Aa'()+_,-./:=?", true],
            ["{}", false],
            ["test test", true],
            ["test ", false],
            [str_repeat("a", 70), true],
            [str_repeat("a", 71), false],
        ];
    }

}