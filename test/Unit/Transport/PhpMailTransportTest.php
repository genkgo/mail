<?php

namespace Genkgo\Mail\Unit\Transport;

use Genkgo\Mail\AbstractTestCase;
use Genkgo\Mail\Address;
use Genkgo\Mail\AddressList;
use Genkgo\Mail\EmailAddress;
use Genkgo\Mail\GenericMessage;
use Genkgo\Mail\Header\From;
use Genkgo\Mail\Header\Subject;
use Genkgo\Mail\Header\To;
use Genkgo\Mail\Stream\BitEncodedStream;
use Genkgo\Mail\Transport\EnvelopeFactory;
use Genkgo\Mail\Transport\PhpMailTransport;
use Symfony\Component\Finder\SplFileInfo;

final class PhpMailTransportTest extends AbstractTestCase
{

    /**
     * @test
     */
    public function it_sends_messages()
    {
        $directory = sys_get_temp_dir();

        $transport = new PhpMailTransport(
            EnvelopeFactory::useExtractedFromHeader(),
            ['-ODeliveryMode=d', '-OQueueDirectory='.$directory]
        );

        $message = (new GenericMessage())
            ->withHeader(new From(new Address(new EmailAddress('from@localhost'), 'name')))
            ->withHeader(new To(new AddressList([new Address(new EmailAddress('to@localhost'), 'name')])))
            ->withHeader(new Subject('subject'))
            ->withBody(new BitEncodedStream('test'));

        $transport->send($message);

        $this->assertCount(
            1,
            iterator_to_array(new \GlobIterator($directory .'/qfv*'))
        );

        $contentFile = iterator_to_array(new \GlobIterator($directory .'/dfv*'));
        $this->assertCount(1, $contentFile);

        $this->assertEquals(
            'test',
            trim(file_get_contents(reset($contentFile)->getPathname()))
        );
    }

    public function setUp()
    {
        $this->emptyQueue();
    }

    public function tearDown()
    {
        $this->emptyQueue();
    }

    private function emptyQueue()
    {
        $directory = sys_get_temp_dir();

        /** @var SplFileInfo $queueFile */
        foreach (new \GlobIterator($directory .'/qfv*') as $queueFile) {
            unlink($queueFile->getPathname());
        }

        /** @var SplFileInfo $bodyFile */
        foreach (new \GlobIterator($directory .'/dfv*') as $bodyFile) {
            unlink($bodyFile->getPathname());
        }
    }
}