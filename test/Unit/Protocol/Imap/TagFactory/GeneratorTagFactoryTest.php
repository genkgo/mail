<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Imap\TagFactory;

use Genkgo\Mail\Protocol\Imap\TagFactory\GeneratorTagFactory;
use Genkgo\TestMail\AbstractTestCase;

final class GeneratorTagFactoryTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_generates_a_new_tag_using_a_nonce()
    {
        $factory = new GeneratorTagFactory();
        $this->assertSame('TAG1', (string)$factory->newTag());
        $this->assertSame('TAG2', (string)$factory->newTag());
        $this->assertSame('TAG3', (string)$factory->newTag());

        $factory = new GeneratorTagFactory();
        $this->assertSame('TAG1', (string)$factory->newTag());
        $this->assertSame('TAG2', (string)$factory->newTag());
        $this->assertSame('TAG3', (string)$factory->newTag());
    }
}
