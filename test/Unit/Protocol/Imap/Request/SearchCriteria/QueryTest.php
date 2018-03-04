<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Imap\Request\SearchCriteria;

use Genkgo\Mail\Protocol\Imap\Request\SearchCriteria\DateCriterion;
use Genkgo\Mail\Protocol\Imap\Request\SearchCriteria\MatchContentCriterion;
use Genkgo\Mail\Protocol\Imap\Request\SearchCriteria\Query;
use Genkgo\TestMail\AbstractTestCase;

final class QueryTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_can_be_constructed_with_criteria()
    {
        $query = new Query([MatchContentCriterion::text('Hello World')]);
        $this->assertSame('TEXT "Hello World"', (string)$query);
    }

    /**
     * @test
     */
    public function it_can_be_extended_with_more_criteria()
    {
        $query = (new Query())
            ->with(DateCriterion::after(new \DateTimeImmutable('2015-01-01')))
            ->with(MatchContentCriterion::text('Hello World'));

        $this->assertSame('AFTER 1-Thu-2015 TEXT "Hello World"', (string)$query);
    }

    /**
     * @test
     */
    public function it_is_immutable()
    {
        $query = new Query();
        $this->assertNotSame($query, $query->with(DateCriterion::after(new \DateTimeImmutable('2015-01-01'))));
    }

    /**
     * @test
     */
    public function it_can_be_counted()
    {
        $query = new Query();
        $this->assertCount(0, $query);

        $query = new Query([MatchContentCriterion::text('Hello World')]);
        $this->assertCount(1, $query);

        $query = (new Query())
            ->with(DateCriterion::after(new \DateTimeImmutable('2015-01-01')))
            ->with(MatchContentCriterion::text('Hello World'));

        $this->assertCount(2, $query);
    }
}
