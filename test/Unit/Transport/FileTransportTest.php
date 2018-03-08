<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Transport;

use Genkgo\TestMail\AbstractTestCase;
use Genkgo\Mail\GenericMessage;
use Genkgo\Mail\Header\Date;
use Genkgo\Mail\MessageInterface;
use Genkgo\Mail\Transport\FileTransport;
use Genkgo\Mail\Transport\FileTransportOptions;

final class FileTransportTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_saves_messages_with_correct_name()
    {
        $directory = \sys_get_temp_dir();

        $message = (new GenericMessage())
            ->withHeader(new Date(new \DateTimeImmutable('2017-01-01 18:15:00')));

        $transport = new FileTransport(
            new FileTransportOptions(
                $directory,
                function (MessageInterface $message) {
                    return \md5((string)$message);
                }
            )
        );

        $transport->send($message);

        $fileName = $directory.'/'.\md5((string)$message);
        $this->assertTrue(\file_exists($fileName));
        $this->assertEquals((string)$message, \file_get_contents($fileName));
    }
}
