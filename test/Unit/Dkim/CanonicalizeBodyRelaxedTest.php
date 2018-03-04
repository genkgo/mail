<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Dkim;

use Genkgo\Mail\Dkim\CanonicalizeBodyRelaxed;
use Genkgo\Mail\Stream\StringStream;
use Genkgo\TestMail\AbstractTestCase;

final class CanonicalizeBodyRelaxedTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_canonicalizes_example_from_rfc()
    {
        $canonicalization = new CanonicalizeBodyRelaxed();

        $this->assertEquals(
            " C\r\nD E\r\n",
            $canonicalization->canonicalize(new StringStream(" C \r\nD \t E\r\n\r\n\r\n"))
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
