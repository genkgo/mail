<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Smtp\Capability;

use Genkgo\Mail\EmailAddress;
use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Protocol\Smtp\Backend\ArrayBackend;
use Genkgo\Mail\Protocol\Smtp\Capability\DataCapability;
use Genkgo\Mail\Protocol\Smtp\GreyList\ArrayGreyList;
use Genkgo\Mail\Protocol\Smtp\Session;
use Genkgo\Mail\Protocol\Smtp\SpamDecideScore;
use Genkgo\Mail\Protocol\Smtp\SpamScore\ForbiddenWordSpamScore;
use Genkgo\TestMail\AbstractTestCase;

final class DataTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_advertises()
    {
        $capability = new DataCapability(
            new ArrayBackend(['test@genkgo.nl'], new \ArrayObject()),
            new ForbiddenWordSpamScore(['word'], 1),
            new ArrayGreyList(),
            new SpamDecideScore(4, 15)
        );

        $this->assertSame('DATA', $capability->advertise());
    }

    /**
     * @test
     */
    public function it_will_accept_messages()
    {
        $connection = $this->createMock(ConnectionInterface::class);

        $connection
            ->expects($this->at(0))
            ->method('send');

        $connection
            ->expects($this->at(1))
            ->method('receive')
            ->willReturn("Subject: test\r\n");

        $connection
            ->expects($this->at(2))
            ->method('receive')
            ->willReturn("\r\n");

        $connection
            ->expects($this->at(3))
            ->method('receive')
            ->willReturn(".");

        $connection
            ->expects($this->at(4))
            ->method('send')
            ->with('250 Message received, queue for delivering');

        $capability = new DataCapability(
            new ArrayBackend(['test@genkgo.nl'], new \ArrayObject()),
            new ForbiddenWordSpamScore(['word'], 1),
            new ArrayGreyList(),
            new SpamDecideScore(4, 15)
        );

        $session = $capability->manifest($connection, new Session());
        $this->assertSame(Session::STATE_MESSAGE_RECEIVED, $session->getState());
    }

    /**
     * @test
     */
    public function it_will_not_accepted_malformed_messages()
    {
        $connection = $this->createMock(ConnectionInterface::class);

        $connection
            ->expects($this->at(0))
            ->method('send');

        $connection
            ->expects($this->at(1))
            ->method('receive')
            ->willReturn(" Subject: test\r\n");

        $connection
            ->expects($this->at(2))
            ->method('receive')
            ->willReturn("\r\n");

        $connection
            ->expects($this->at(3))
            ->method('receive')
            ->willReturn(".");

        $connection
            ->expects($this->at(4))
            ->method('send')
            ->with('500 Malformed message');

        $capability = new DataCapability(
            new ArrayBackend(['test@genkgo.nl'], new \ArrayObject()),
            new ForbiddenWordSpamScore(['word'], 1),
            new ArrayGreyList(),
            new SpamDecideScore(4, 15)
        );

        $session = $capability->manifest($connection, new Session());
        $this->assertSame(Session::STATE_CONNECTED, $session->getState());
    }

    /**
     * @test
     */
    public function it_will_greylist()
    {
        $connection = $this->createMock(ConnectionInterface::class);

        $connection
            ->expects($this->at(0))
            ->method('send');

        $connection
            ->expects($this->at(1))
            ->method('receive')
            ->willReturn("Subject: test\r\n");

        $connection
            ->expects($this->at(2))
            ->method('receive')
            ->willReturn("\r\n");

        $connection
            ->expects($this->at(3))
            ->method('receive')
            ->willReturn("word word word");

        $connection
            ->expects($this->at(4))
            ->method('receive')
            ->willReturn(".");

        $connection
            ->expects($this->at(5))
            ->method('send')
            ->with('421 Please try again later');

        $capability = new DataCapability(
            new ArrayBackend(['test@genkgo.nl'], new \ArrayObject()),
            new ForbiddenWordSpamScore(['word'], 3),
            new ArrayGreyList(),
            new SpamDecideScore(4, 15)
        );

        $session = $capability->manifest($connection, new Session());
        $this->assertSame(Session::STATE_CONNECTED, $session->getState());
    }

    /**
     * @test
     */
    public function it_will_bounce_spam()
    {
        $connection = $this->createMock(ConnectionInterface::class);

        $connection
            ->expects($this->at(0))
            ->method('send');

        $connection
            ->expects($this->at(1))
            ->method('receive')
            ->willReturn("Subject: test\r\n");

        $connection
            ->expects($this->at(2))
            ->method('receive')
            ->willReturn("\r\n");

        $connection
            ->expects($this->at(3))
            ->method('receive')
            ->willReturn("word word word word word word");

        $connection
            ->expects($this->at(4))
            ->method('receive')
            ->willReturn(".");

        $connection
            ->expects($this->at(5))
            ->method('send')
            ->with('550 Message discarded as high-probability spam');

        $capability = new DataCapability(
            new ArrayBackend(['test@genkgo.nl'], new \ArrayObject()),
            new ForbiddenWordSpamScore(['word'], 3),
            new ArrayGreyList(),
            new SpamDecideScore(4, 15)
        );

        $session = $capability->manifest($connection, new Session());
        $this->assertSame(Session::STATE_CONNECTED, $session->getState());
    }

    /**
     * @test
     */
    public function it_will_deliver_to_junk_after_greylist()
    {
        $greylist = new ArrayGreyList();
        $messages = new \ArrayObject();
        $backend = new ArrayBackend(['test@genkgo.nl'], $messages);

        for ($i = 0; $i < 2; $i++) {
            $connection = $this->createMock(ConnectionInterface::class);
            $session = (new Session())->withRecipient(new EmailAddress('test@genkgo.nl'));

            $connection
                ->expects($this->at(0))
                ->method('send');

            $connection
                ->expects($this->at(1))
                ->method('receive')
                ->willReturn("Subject: test\r\n");

            $connection
                ->expects($this->at(2))
                ->method('receive')
                ->willReturn("\r\n");

            $connection
                ->expects($this->at(3))
                ->method('receive')
                ->willReturn("word word word");

            $connection
                ->expects($this->at(4))
                ->method('receive')
                ->willReturn(".");

            $capability = new DataCapability(
                $backend,
                new ForbiddenWordSpamScore(['word'], 3),
                $greylist,
                new SpamDecideScore(4, 15)
            );

            switch ($i) {
                case 0:
                    $connection
                        ->expects($this->at(5))
                        ->method('send')
                        ->with('421 Please try again later');

                    $session = $capability->manifest($connection, $session);

                    $this->assertSame(Session::STATE_MESSAGE, $session->getState());
                    $this->assertCount(0, $messages);
                    break;

                case 1:
                    $connection
                        ->expects($this->at(5))
                        ->method('send')
                        ->with('250 Message received, queue for delivering');

                    $session = $capability->manifest($connection, $session);

                    $this->assertSame(Session::STATE_MESSAGE_RECEIVED, $session->getState());
                    $this->assertCount(1, $messages);
                    break;

            }
        }
    }
}
