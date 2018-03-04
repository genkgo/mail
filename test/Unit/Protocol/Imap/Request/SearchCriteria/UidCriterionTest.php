<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Imap\Request\SearchCriteria;

use Genkgo\Mail\Protocol\Imap\Request\SearchCriteria\UidCriterion;
use Genkgo\Mail\Protocol\Imap\Request\SequenceSet;
use Genkgo\TestMail\AbstractTestCase;

final class UidCriterionTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_casts_to_string()
    {
        $this->assertSame('UID 1453543:5435342543', (string)new UidCriterion(SequenceSet::range(1453543, 5435342543)));
    }
}
