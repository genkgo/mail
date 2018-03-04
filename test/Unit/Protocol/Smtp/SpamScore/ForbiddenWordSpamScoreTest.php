<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Smtp\SpamScore;

use Genkgo\Mail\PlainTextMessage;
use Genkgo\Mail\Protocol\Smtp\SpamScore\ForbiddenWordSpamScore;
use Genkgo\TestMail\AbstractTestCase;

final class ForbiddenWordSpamScoreTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_calculates_total_of_spam_checkers()
    {
        $checker = new ForbiddenWordSpamScore(['forbidden', 'word'], 3);

        $this->assertEquals(6, $checker->calculate(new PlainTextMessage('forbidden word')));
    }
}
