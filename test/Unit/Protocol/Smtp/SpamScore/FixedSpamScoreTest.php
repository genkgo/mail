<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Smtp\SpamScore;

use Genkgo\Mail\PlainTextMessage;
use Genkgo\Mail\Protocol\Smtp\SpamScore\FixedSpamScore;
use Genkgo\TestMail\AbstractTestCase;

final class FixedSpamScoreTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_calculates_spam_score()
    {
        $checker = new FixedSpamScore(1);

        $this->assertEquals(1, $checker->calculate(new PlainTextMessage('test')));
    }
}
