<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Dkim;

use Genkgo\Mail\Dkim\CanonicalizeHeaderSimple;
use Genkgo\Mail\Header\GenericHeader;
use Genkgo\TestMail\AbstractTestCase;

final class CanonicalizeHeaderSimpleTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_canonicalizes_example_from_rfc()
    {
        $canonicalization = new CanonicalizeHeaderSimple();

        $this->assertEquals(
            'A: X ',
            $canonicalization->canonicalize(new GenericHeader('A', 'X '))
        );
    }

    /**
     * @test
     */
    public function it_is_called_simple()
    {
        $canonicalization = new CanonicalizeHeaderSimple();

        $this->assertEquals('simple', $canonicalization->name());
    }
}
