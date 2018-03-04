<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Imap\Request\SearchCriteria;

use Genkgo\Mail\Protocol\Imap\Request\SearchCriteria\MatchContentCriterion;
use Genkgo\TestMail\AbstractTestCase;

final class MatchContentCriterionTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_casts_to_string()
    {
        $this->assertSame('BODY "Hello World"', (string)MatchContentCriterion::body('Hello World'));
    }

    /**
     * @test
     */
    public function it_can_search_in_all_text()
    {
        $this->assertSame('TEXT "Hello World"', (string)MatchContentCriterion::text('Hello World'));
    }

    /**
     * @test
     */
    public function it_can_search_in_body()
    {
        $this->assertSame('BODY "Hello World!"', (string)MatchContentCriterion::body('Hello World!'));
    }

    /**
     * @test
     */
    public function it_can_search_in_subject()
    {
        $this->assertSame('SUBJECT "Hello World!"', (string)MatchContentCriterion::subject('Hello World!'));
    }

    /**
     * @test
     */
    public function it_throws_when_empty_query()
    {
        $this->expectException(\InvalidArgumentException::class);
        MatchContentCriterion::text('');
    }

    /**
     * @test
     */
    public function it_throws_when_empty_using_lf()
    {
        $this->expectException(\InvalidArgumentException::class);
        MatchContentCriterion::text("\n");
    }

    /**
     * @test
     */
    public function it_throws_when_empty_using_cr()
    {
        $this->expectException(\InvalidArgumentException::class);
        MatchContentCriterion::text("\r");
    }
}
