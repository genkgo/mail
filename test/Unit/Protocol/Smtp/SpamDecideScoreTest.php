<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Smtp;

use Genkgo\Mail\Protocol\Smtp\SpamDecideScore;
use Genkgo\TestMail\AbstractTestCase;

final class SpamDecideScoreTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_uses_correct_classification()
    {
        $decide = new SpamDecideScore(4, 15);
        $this->assertTrue($decide->isHam(3));
        $this->assertTrue($decide->isHam(4));
        $this->assertFalse($decide->isHam(5));
        $this->assertTrue($decide->isLikelySpam(5));
        $this->assertTrue($decide->isLikelySpam(14));
        $this->assertFalse($decide->isLikelySpam(15));
        $this->assertTrue($decide->isSpam(15));
        $this->assertFalse($decide->isSpam(14));
    }
}
