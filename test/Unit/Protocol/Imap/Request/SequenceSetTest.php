<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Imap\Request;

use Genkgo\Mail\Protocol\Imap\Request\SequenceSet;
use Genkgo\TestMail\AbstractTestCase;

final class SequenceSetTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_can_be_casted_to_string()
    {
        $this->assertSame('1', (string)SequenceSet::single(1));
    }

    /**
     * @test
     */
    public function it_can_be_single()
    {
        $this->assertSame('1', (string)SequenceSet::single(1));
    }

    /**
     * @test
     */
    public function it_can_be_range()
    {
        $this->assertSame('1:5', (string)SequenceSet::range(1, 5));
    }

    /**
     * @test
     */
    public function it_can_be_infinite_range()
    {
        $this->assertSame('1:*', (string)SequenceSet::infiniteRange(1));
    }

    /**
     * @test
     */
    public function it_can_be_all()
    {
        $this->assertSame('*', (string)SequenceSet::all());
    }

    /**
     * @test
     */
    public function it_can_be_extended_with_single()
    {
        $this->assertSame('1,5', (string)SequenceSet::single(1)->withSingle(5));
    }

    /**
     * @test
     */
    public function it_can_be_extended_with_range()
    {
        $this->assertSame('1,3:5', (string)SequenceSet::single(1)->withRange(3, 5));
    }

    /**
     * @test
     */
    public function it_can_be_extended_with_infinite_range()
    {
        $this->assertSame('1,3:*', (string)SequenceSet::single(1)->withInfiniteRange(3));
    }
}
