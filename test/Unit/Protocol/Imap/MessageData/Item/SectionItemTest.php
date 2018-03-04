<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Imap\MessageData\Item;

use Genkgo\Mail\Protocol\Imap\MessageData\Item\NameItem;
use Genkgo\Mail\Protocol\Imap\MessageData\Item\SectionItem;
use Genkgo\Mail\Protocol\Imap\MessageData\SectionList;
use Genkgo\TestMail\AbstractTestCase;

final class SectionItemTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_has_the_name_of_decorated_item()
    {
        $item = new SectionItem(new NameItem('TEST'), new SectionList());
        $this->assertSame('TEST', $item->getName());
    }

    /**
     * @test
     */
    public function it_generates_a_section_list()
    {
        $item = new SectionItem(new NameItem('TEST'), new SectionList());
        $this->assertSame('TEST[]', (string)$item);
    }
}
