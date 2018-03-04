<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Imap\Request\SearchCriteria;

use Genkgo\Mail\Protocol\Imap\Request\SearchCriteria\AllCriterion;
use Genkgo\TestMail\AbstractTestCase;

final class AllCriterionTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_casts_to_string()
    {
        $this->assertSame('ALL', (string)new AllCriterion());
    }
}
