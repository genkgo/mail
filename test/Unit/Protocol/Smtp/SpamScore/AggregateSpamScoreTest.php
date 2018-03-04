<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Smtp\SpamScore;

use Genkgo\Mail\PlainTextMessage;
use Genkgo\Mail\Protocol\Smtp\SpamScore\AggregateSpamScore;
use Genkgo\Mail\Protocol\Smtp\SpamScore\FixedSpamScore;
use Genkgo\TestMail\AbstractTestCase;

final class AggregateSpamScoreTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_calculates_spam_score()
    {
        $checker = new AggregateSpamScore([
            new FixedSpamScore(1),
            new FixedSpamScore(2),
        ]);

        $this->assertEquals(3, $checker->calculate(new PlainTextMessage('test')));
    }
}
