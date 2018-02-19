<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Imap\MessageData\Item;

use Genkgo\Mail\Protocol\Imap\FlagParenthesizedList;
use Genkgo\Mail\Protocol\Imap\MessageData\Item\FlagsItem;
use Genkgo\TestMail\AbstractTestCase;

final class FlagsItemTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_has_a_name()
    {
        $item = new FlagsItem(new FlagParenthesizedList(['\\Seen']));
        $this->assertSame('FLAGS', $item->getName());
    }

    /**
     * @test
     */
    public function it_can_be_casted_as_string()
    {
        $item = new FlagsItem(new FlagParenthesizedList(['\\Seen']));
        $this->assertSame('FLAGS (\\Seen)', (string)$item);
    }

}