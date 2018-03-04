<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Dkim;

use Genkgo\Mail\Dkim\CanonicalizeBodySimple;
use Genkgo\Mail\Stream\StringStream;
use Genkgo\TestMail\AbstractTestCase;

final class CanonicalizeBodySimpleTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_canonicalizes_example_from_rfc()
    {
        $canonicalization = new CanonicalizeBodySimple();

        $this->assertEquals(
            " C \r\nD \t E\r\n",
            $canonicalization->canonicalize(new StringStream(" C \r\nD \t E\r\n\r\n\r\n"))
        );
    }

    /**
     * @test
     */
    public function it_is_called_simple()
    {
        $canonicalization = new CanonicalizeBodySimple();

        $this->assertEquals('simple', $canonicalization->name());
    }
}
