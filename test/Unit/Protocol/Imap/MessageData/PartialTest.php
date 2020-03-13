<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Imap\MessageData;

use Genkgo\Mail\Protocol\Imap\MessageData\Partial;
use Genkgo\TestMail\AbstractTestCase;

final class PartialTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_casts_to_string(): void
    {
        $partial = new Partial(1, 5);
        $this->assertSame('<1.5>', (string)$partial);
    }

    /**
     * @test
     */
    public function it_throws_when_unsigned(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Partial(-1, 5);
    }

    /**
     * @test
     */
    public function it_throws_when_first_greater_than_last(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Partial(5, -1);
    }

    /**
     * @test
     */
    public function it_displays_single_digit_when_first_equals_last(): void
    {
        $partial = new Partial(1, 1);
        $this->assertSame('<1>', (string)$partial);
    }

    /**
     * @test
     */
    public function it_parses_string(): void
    {
        $partial = Partial::fromString('<1.5>');
        $this->assertSame('<1.5>', (string)$partial);

        $partial = Partial::fromString('<5>');
        $this->assertSame('<5>', (string)$partial);
    }

    /**
     * @test
     */
    public function it_throws_when_invalid_string(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Partial::fromString('<1,5>');
    }
}
