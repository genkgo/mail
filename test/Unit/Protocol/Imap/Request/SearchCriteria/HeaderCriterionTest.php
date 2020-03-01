<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Imap\Request\SearchCriteria;

use Genkgo\Mail\Header\HeaderName;
use Genkgo\Mail\Protocol\Imap\Request\SearchCriteria\HeaderCriterion;
use Genkgo\TestMail\AbstractTestCase;

final class HeaderCriterionTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_casts_to_string(): void
    {
        $this->assertSame(
            'HEADER X-Priority "1"',
            (string)new HeaderCriterion(new HeaderName('X-Priority'), '1')
        );
    }

    /**
     * @test
     */
    public function it_adds_slashes(): void
    {
        $this->assertSame(
            'HEADER X-Custom-Header "\"Test\""',
            (string)new HeaderCriterion(new HeaderName('X-Custom-Header'), '"Test"')
        );
    }

    /**
     * @test
     */
    public function it_throws_when_empty_query(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new HeaderCriterion(new HeaderName('X-Custom-Header'), '');
    }

    /**
     * @test
     */
    public function it_throws_when_query_contains_cr(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new HeaderCriterion(new HeaderName('X-Custom-Header'), "\r");
    }

    /**
     * @test
     */
    public function it_throws_when_query_contains_lf(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new HeaderCriterion(new HeaderName('X-Custom-Header'), "\n");
    }
}
