<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit;

use Genkgo\Mail\GenericMessage;
use Genkgo\Mail\Post;
use Genkgo\TestMail\AbstractTestCase;

final class PostTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_parses_messages()
    {
        $message = GenericMessage::fromString(
            \file_get_contents(__DIR__. '/../Stub/FormattedMessageFactoryTest/full-formatted-message.eml')
        );

        $post = Post::fromMessage($message);
        $this->assertSame('<html><body><p>Hello World</p></body></html>', $post->getHtml());
        $this->assertSame('Hello World', (string)$post->getText());
        $this->assertCount(1, $post->getEmbeddedImages());
        $this->assertCount(1, $post->getAttachments());
    }
}
