<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Imap\MessageData\Item;

use Genkgo\Mail\Protocol\Imap\MessageData\Item\NameItem;
use Genkgo\TestMail\AbstractTestCase;

final class NameItemTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_has_a_name()
    {
        $item = new NameItem('TEST');
        $this->assertSame('TEST', $item->getName());
    }

    /**
     * @test
     */
    public function it_can_be_casted_as_string()
    {
        $item = new NameItem('TEST');
        $this->assertSame('TEST', (string)$item);
    }

    /**
     * @test
     */
    public function it_throws_when_using_invalid_name()
    {
        $this->expectException(\InvalidArgumentException::class);
        new NameItem('TEST' . "\u{1000}");
    }
}
