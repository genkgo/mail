<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Imap\Request\SearchCriteria;

use Genkgo\Mail\Protocol\Imap\Flag;
use Genkgo\Mail\Protocol\Imap\Request\SearchCriteria\FlagCriterion;
use Genkgo\TestMail\AbstractTestCase;

final class FlagCriterionTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_casts_to_string(): void
    {
        $this->assertSame('ANSWERED', (string)new FlagCriterion(new Flag('\\Answered')));
    }

    /**
     * @test
     */
    public function it_can_negate(): void
    {
        $this->assertSame('UNANSWERED', (string)FlagCriterion::negate(new Flag('\\Answered')));
    }

    /**
     * @test
     */
    public function it_can_search_for_keyword(): void
    {
        $this->assertSame('KEYWORD test', (string)new FlagCriterion(new Flag('test')));
    }

    /**
     * @test
     */
    public function it_can_negate_keyword(): void
    {
        $this->assertSame('UNKEYWORD test', (string)FlagCriterion::negate(new Flag('test')));
    }
}
