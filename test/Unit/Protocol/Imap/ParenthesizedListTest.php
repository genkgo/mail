<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Imap;

use Genkgo\Mail\Protocol\Imap\ParenthesizedList;
use Genkgo\TestMail\AbstractTestCase;

final class ParenthesizedListTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_returns_an_empty_string_when_list_is_empty()
    {
        $this->assertSame('', (string)new ParenthesizedList());
    }

    /**
     * @test
     */
    public function it_uses_parenthesizes_when_list_is_not_empty()
    {
        $list = (new ParenthesizedList())->with('BODY');
        $this->assertSame('(', ((string)$list)[0]);
        $this->assertSame(')', ((string)$list)[-1]);
    }

    /**
     * @test
     */
    public function it_uses_spaces_as_separator()
    {
        $list = (new ParenthesizedList())->with('BODY')->with('HEADER');
        $this->assertSame('(BODY HEADER)', (string)$list);
    }

    /**
     * @test
     */
    public function it_is_immutable()
    {
        $list = new ParenthesizedList();
        $this->assertNotSame($list, $list->with('BODY'));
        $this->assertNotSame($list, $list->without('BODY'));
    }

    /**
     * @test
     */
    public function it_adds_to_and_removes_from_the_list()
    {
        $list = (new ParenthesizedList())->with('BODY');
        $this->assertSame('(BODY)', (string)$list);
        $this->assertSame('', (string)$list->without('BODY'));
    }
}
