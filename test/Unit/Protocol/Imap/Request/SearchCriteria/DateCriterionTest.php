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
    public function it_casts_to_string()
    {
        $this->assertSame('BEFORE 1-Thu-2015', (string)DateCriterion::before(new \DateTimeImmutable('2015-01-01 00:00:00')));
    }

    /**
     * @test
     */
    public function it_uses_before()
    {
        $this->assertSame('BEFORE 1-Thu-2015', (string)DateCriterion::before(new \DateTimeImmutable('2015-01-01 00:00:00')));
    }

    /**
     * @test
     */
    public function it_uses_after()
    {
        $this->assertSame('AFTER 1-Thu-2015', (string)DateCriterion::after(new \DateTimeImmutable('2015-01-01 00:00:00')));
    }

    /**
     * @test
     */
    public function it_uses_sent_after()
    {
        $this->assertSame('SENTAFTER 1-Thu-2015', (string)DateCriterion::sentAfter(new \DateTimeImmutable('2015-01-01 00:00:00')));
    }

    /**
     * @test
     */
    public function it_uses_sent_before()
    {
        $this->assertSame('SENTBEFORE 1-Thu-2015', (string)DateCriterion::sentBefore(new \DateTimeImmutable('2015-01-01 00:00:00')));
    }

    /**
     * @test
     */
    public function it_uses_since()
    {
        $this->assertSame('SINCE 1-Thu-2015', (string)DateCriterion::since(new \DateTimeImmutable('2015-01-01 00:00:00')));
    }

    /**
     * @test
     */
    public function it_uses_on()
    {
        $this->assertSame('ON 1-Thu-2015', (string)DateCriterion::on(new \DateTimeImmutable('2015-01-01 00:00:00')));
    }

    /**
     * @test
     */
    public function it_uses_sent_since()
    {
        $this->assertSame('SENTSINCE 1-Thu-2015', (string)DateCriterion::sentSince(new \DateTimeImmutable('2015-01-01 00:00:00')));
    }

    /**
     * @test
     */
    public function it_uses_sent_on()
    {
        $this->assertSame('SENTON 1-Thu-2015', (string)DateCriterion::sentOn(new \DateTimeImmutable('2015-01-01 00:00:00')));
    }
}
