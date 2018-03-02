<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Imap\Request\SearchCriteria;

use Genkgo\Mail\Protocol\Imap\Request\SearchCriteria\SequenceSetCriterion;
use Genkgo\Mail\Protocol\Imap\Request\SequenceSet;
use Genkgo\TestMail\AbstractTestCase;

final class SequenceSetCriterionTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_casts_to_string()
    {
        $this->assertSame('1:5', (string)new SequenceSetCriterion(SequenceSet::sequence(1, 5)));
    }
}