<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Imap\Request\SearchCriteria;

use Genkgo\Mail\Protocol\Imap\Request\SearchCriteria\MatchContentCriterion;
use Genkgo\Mail\Protocol\Imap\Request\SearchCriteria\OrCriterion;
use Genkgo\Mail\Protocol\Imap\Request\SearchCriteria\Query;
use Genkgo\TestMail\AbstractTestCase;

final class OrCriterionTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_casts_to_string()
    {
        $this->assertSame(
            '(OR BODY "Hello World" BODY "Hello Planet")',
            (string)new OrCriterion(
                new Query(
                    [
                        MatchContentCriterion::body('Hello World'),
                        MatchContentCriterion::body('Hello Planet'),
                    ]
                )
            )
        );
    }

    /**
     * @test
     */
    public function it_throws_when_subquery_is_empty()
    {
        $this->expectException(\InvalidArgumentException::class);
        new OrCriterion(new Query());
    }

    /**
     * @test
     */
    public function it_throws_when_subquery_contains_one_criterion()
    {
        $this->expectException(\InvalidArgumentException::class);
        new OrCriterion(new Query([MatchContentCriterion::body('Hello World'),]));
    }

    /**
     * @test
     */
    public function it_throws_when_subquery_contains_more_than_two_criteria()
    {
        $this->expectException(\InvalidArgumentException::class);
        new OrCriterion(
            new Query([
                MatchContentCriterion::body('Hello World'),
                MatchContentCriterion::body('Hello Planet'),
                MatchContentCriterion::body('Hello Universe'),
            ])
        );
    }
}
