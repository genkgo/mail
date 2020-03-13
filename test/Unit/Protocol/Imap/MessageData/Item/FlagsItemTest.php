<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Imap\MessageData\Item;

use Genkgo\Mail\Protocol\Imap\Flag;
use Genkgo\Mail\Protocol\Imap\FlagParenthesizedList;
use Genkgo\Mail\Protocol\Imap\MessageData\Item\FlagsItem;
use Genkgo\TestMail\AbstractTestCase;

final class FlagsItemTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_has_a_name(): void
    {
        $item = new FlagsItem(new FlagParenthesizedList([new Flag('\\Seen')]));
        $this->assertSame('FLAGS', $item->getName());
    }

    /**
     * @test
     */
    public function it_can_be_casted_as_string(): void
    {
        $item = new FlagsItem(new FlagParenthesizedList([new Flag('\\Seen')]));
        $this->assertSame('FLAGS (\\Seen)', (string)$item);
    }

    /**
     * @test
     */
    public function it_throws_when_using_invalid_operator(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new FlagsItem(new FlagParenthesizedList([new Flag('\\Seen')]), '*');
    }

    /**
     * @test
     */
    public function it_can_be_constructed_as_silent(): void
    {
        $this->assertSame(
            'FLAGS.SILENT (\Seen)',
            (string)FlagsItem::silent(new FlagParenthesizedList([new Flag('\\Seen')]))
        );
    }
}
