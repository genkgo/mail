<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Imap\Request;

use Genkgo\Mail\Header\Date;
use Genkgo\Mail\Header\Subject;
use Genkgo\Mail\PlainTextMessage;
use Genkgo\Mail\Protocol\Imap\Request\AppendDataRequest;
use Genkgo\Mail\Protocol\Imap\Tag;
use Genkgo\TestMail\AbstractTestCase;

final class AppendDataRequestTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_creates_a_stream()
    {
        $message = (new PlainTextMessage('Hello World'))
            ->withHeader(new Subject('Test'))
            ->withHeader(new Date(new \DateTimeImmutable('2015-01-01 00:00:00')));

        $command = new AppendDataRequest(
            Tag::fromNonce(1),
            $message
        );

        $this->assertSame((string)$message, (string)$command->toStream());
        $this->assertSame('TAG1', (string)$command->getTag());
    }
}
