<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Imap\Request\SearchCriteria;

use Genkgo\Mail\Protocol\Imap\Request\SearchCriteria\DateCriterion;
use Genkgo\TestMail\AbstractTestCase;

final class DateCriterionTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_casts_to_string(): void
    {
        $this->assertSame('BEFORE 1-Thu-2015', (string)DateCriterion::before(new \DateTimeImmutable('2015-01-01 00:00:00')));
    }

    /**
     * @test
     */
    public function it_uses_before(): void
    {
        $this->assertSame('BEFORE 1-Thu-2015', (string)DateCriterion::before(new \DateTimeImmutable('2015-01-01 00:00:00')));
    }

    /**
     * @test
     */
    public function it_uses_after(): void
    {
        $this->assertSame('AFTER 1-Thu-2015', (string)DateCriterion::after(new \DateTimeImmutable('2015-01-01 00:00:00')));
    }

    /**
     * @test
     */
    public function it_uses_sent_after(): void
    {
        $this->assertSame('SENTAFTER 1-Thu-2015', (string)DateCriterion::sentAfter(new \DateTimeImmutable('2015-01-01 00:00:00')));
    }

    /**
     * @test
     */
    public function it_uses_sent_before(): void
    {
        $this->assertSame('SENTBEFORE 1-Thu-2015', (string)DateCriterion::sentBefore(new \DateTimeImmutable('2015-01-01 00:00:00')));
    }

    /**
     * @test
     */
    public function it_uses_since(): void
    {
        $this->assertSame('SINCE 1-Thu-2015', (string)DateCriterion::since(new \DateTimeImmutable('2015-01-01 00:00:00')));
    }

    /**
     * @test
     */
    public function it_uses_on(): void
    {
        $this->assertSame('ON 1-Thu-2015', (string)DateCriterion::on(new \DateTimeImmutable('2015-01-01 00:00:00')));
    }

    /**
     * @test
     */
    public function it_uses_sent_since(): void
    {
        $this->assertSame('SENTSINCE 1-Thu-2015', (string)DateCriterion::sentSince(new \DateTimeImmutable('2015-01-01 00:00:00')));
    }

    /**
     * @test
     */
    public function it_uses_sent_on(): void
    {
        $this->assertSame('SENTON 1-Thu-2015', (string)DateCriterion::sentOn(new \DateTimeImmutable('2015-01-01 00:00:00')));
    }
}
