<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Imap\MessageData\Item;

use Genkgo\Mail\Protocol\Imap\MessageData\Item\NameItem;
use Genkgo\Mail\Protocol\Imap\MessageData\Item\PartialItem;
use Genkgo\Mail\Protocol\Imap\MessageData\Partial;
use Genkgo\TestMail\AbstractTestCase;

final class PartialItemTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_has_the_name_of_decorated_item()
    {
        $item = new PartialItem(new NameItem('TEST'), new Partial(0, 15));
        $this->assertSame('TEST', $item->getName());
    }

    /**
     * @test
     */
    public function it_generates_a_section_list()
    {
        $item = new PartialItem(new NameItem('TEST'), new Partial(0, 15));
        $this->assertSame('TEST<0.15>', (string)$item);
    }
}
