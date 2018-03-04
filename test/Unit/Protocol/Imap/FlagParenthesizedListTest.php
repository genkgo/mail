<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Imap;

use Genkgo\Mail\Protocol\Imap\Flag;
use Genkgo\Mail\Protocol\Imap\FlagParenthesizedList;
use Genkgo\TestMail\AbstractTestCase;

final class FlagParenthesizedListTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_returns_an_empty_string_when_list_is_empty()
    {
        $this->assertSame('', (string)new FlagParenthesizedList());
    }

    /**
     * @test
     */
    public function it_uses_parenthesizes_when_list_is_not_empty()
    {
        $list = (new FlagParenthesizedList())->with(new Flag('\\Answered'));
        $this->assertSame('(', ((string)$list)[0]);
        $this->assertSame(')', ((string)$list)[-1]);
    }

    /**
     * @test
     */
    public function it_uses_spaces_as_separator()
    {
        $list = (new FlagParenthesizedList())->with(new Flag('\\Answered'))->with(new Flag('\\Seen'));
        $this->assertSame('(\\Answered \\Seen)', (string)$list);
    }

    /**
     * @test
     */
    public function it_is_immutable()
    {
        $list = new FlagParenthesizedList();
        $this->assertNotSame($list, $list->with(new Flag('\\Answered')));
        $this->assertNotSame($list, $list->without(new Flag('\\Answered')));
    }

    /**
     * @test
     */
    public function it_adds_to_and_removes_from_the_list()
    {
        $list = (new FlagParenthesizedList())->with(new Flag('\\Answered'));
        $this->assertSame('(\\Answered)', (string)$list);
        $this->assertSame('', (string)$list->without(new Flag('\\Answered')));
    }
}
