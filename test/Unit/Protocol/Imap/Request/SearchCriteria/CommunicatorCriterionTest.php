<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Imap\Request\SearchCriteria;

use Genkgo\Mail\Protocol\Imap\Request\SearchCriteria\CommunicatorCriterion;
use Genkgo\TestMail\AbstractTestCase;

final class CommunicatorCriterionTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_casts_to_string(): void
    {
        $this->assertSame('TO test', (string)CommunicatorCriterion::to('test'));
    }

    /**
     * @test
     */
    public function it_uses_to(): void
    {
        $this->assertSame('TO query', (string)CommunicatorCriterion::to('query'));
    }

    /**
     * @test
     */
    public function it_uses_cc(): void
    {
        $this->assertSame('CC query', (string)CommunicatorCriterion::cc('query'));
    }

    /**
     * @test
     */
    public function it_uses_bcc(): void
    {
        $this->assertSame('BCC query', (string)CommunicatorCriterion::bcc('query'));
    }

    /**
     * @test
     */
    public function it_uses_from(): void
    {
        $this->assertSame('FROM query', (string)CommunicatorCriterion::from('query'));
    }
}
