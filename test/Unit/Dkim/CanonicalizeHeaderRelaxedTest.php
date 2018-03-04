<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Dkim;

use Genkgo\Mail\Dkim\CanonicalizeBodyRelaxed;
use Genkgo\Mail\Dkim\CanonicalizeHeaderRelaxed;
use Genkgo\Mail\Header\HeaderLine;
use Genkgo\TestMail\AbstractTestCase;

final class CanonicalizeHeaderRelaxedTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_canonicalizes_example_from_rfc()
    {
        $canonicalization = new CanonicalizeHeaderRelaxed();

        $this->assertEquals(
            'a:X',
            $canonicalization->canonicalize(HeaderLine::fromString('A: X ')->getHeader())
        );
    }

    /**
     * @test
     */
    public function it_is_called_simple()
    {
        $canonicalization = new CanonicalizeBodyRelaxed();

        $this->assertEquals('relaxed', $canonicalization->name());
    }
}
