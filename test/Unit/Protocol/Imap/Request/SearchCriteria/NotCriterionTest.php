<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Imap\Request\SearchCriteria;

use Genkgo\Mail\Protocol\Imap\Request\SearchCriteria\MatchContentCriterion;
use Genkgo\Mail\Protocol\Imap\Request\SearchCriteria\NotCriterion;
use Genkgo\Mail\Protocol\Imap\Request\SearchCriteria\Query;
use Genkgo\TestMail\AbstractTestCase;

final class NotCriterionTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_casts_to_string()
    {
        $this->assertSame(
            '(NOT BODY "Hello World")',
            (string)new NotCriterion(new Query([MatchContentCriterion::body('Hello World')]))
        );
    }

    /**
     * @test
     */
    public function it_throws_when_subquery_is_empty()
    {
        $this->expectException(\InvalidArgumentException::class);
        new NotCriterion(new Query());
    }
}
