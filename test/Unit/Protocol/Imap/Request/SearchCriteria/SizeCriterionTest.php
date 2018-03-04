<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Imap\Request\SearchCriteria;

use Genkgo\Mail\Protocol\Imap\Request\SearchCriteria\SizeCriterion;
use Genkgo\TestMail\AbstractTestCase;

final class SizeCriterionTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_casts_to_string()
    {
        $this->assertSame('LARGER 5', (string)SizeCriterion::larger(5));
    }

    /**
     * @test
     */
    public function it_queries_messages_larger_than()
    {
        $this->assertSame('LARGER 10', (string)SizeCriterion::larger(10));
    }

    /**
     * @test
     */
    public function it_queries_messages_smaller_than()
    {
        $this->assertSame('SMALLER 10', (string)SizeCriterion::smaller(10));
    }
}
