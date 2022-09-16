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
    public function it_advertises(): void
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
    public function it_will_accept_messages(): void
    {
        $connection = $this->createMock(ConnectionInterface::class);

        $connection
            ->expects($this->exactly(2))
            ->method('send')
            ->withConsecutive(
                [],
                ['250 Message received, queue for delivering']
            );

        $connection
            ->expects($this->exactly(3))
            ->method('receive')
            ->willReturnOnConsecutiveCalls(
                "Subject: test\r\n",
                "\r\n",
                "."
            );

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
    public function it_will_not_accepted_malformed_messages(): void
    {
        $connection = $this->createMock(ConnectionInterface::class);

        $connection
            ->expects($this->exactly(2))
            ->method('send')
            ->withConsecutive(
                [],
                ['500 Malformed message']
            );

        $connection
            ->expects($this->exactly(3))
            ->method('receive')
            ->willReturnOnConsecutiveCalls(
                " Subject: test\r\n",
                "\r\n",
                "."
            );

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
    public function it_will_greylist(): void
    {
        $connection = $this->createMock(ConnectionInterface::class);

        $connection
            ->expects($this->exactly(2))
            ->method('send')
            ->withConsecutive(
                [],
                ['421 Please try again later']
            );

        $connection
            ->expects($this->exactly(4))
            ->method('receive')
            ->willReturnOnConsecutiveCalls(
                "Subject: test\r\n",
                "\r\n",
                "word word word",
                "."
            );

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
    public function it_will_bounce_spam(): void
    {
        $connection = $this->createMock(ConnectionInterface::class);

        $connection
            ->expects($this->exactly(2))
            ->method('send')
            ->withConsecutive(
                [],
                ['550 Message discarded as high-probability spam']
            );

        $connection
            ->expects($this->exactly(4))
            ->method('receive')
            ->willReturnOnConsecutiveCalls(
                "Subject: test\r\n",
                "\r\n",
                "word word word word word word",
                "."
            );

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
    public function it_will_deliver_to_junk_after_greylist(): void
    {
        $greylist = new ArrayGreyList();
        $messages = new \ArrayObject();
        $backend = new ArrayBackend(['test@genkgo.nl'], $messages);

        for ($i = 0; $i < 2; $i++) {
            $connection = $this->createMock(ConnectionInterface::class);
            $session = (new Session())->withRecipient(new EmailAddress('test@genkgo.nl'));

            $connection
                ->expects($this->exactly(2))
                ->method('send')
                ->withConsecutive(
                    ['354 Enter message, ending with "." on a line by itself'],
                    [$i === 0 ? '421 Please try again later' : '250 Message received, queue for delivering'],
                );

            $connection
                ->expects($this->exactly(4))
                ->method('receive')
                ->willReturnOnConsecutiveCalls(
                    "Subject: test\r\n",
                    "\r\n",
                    "word word word",
                    "."
                );

            $capability = new DataCapability(
                $backend,
                new ForbiddenWordSpamScore(['word'], 3),
                $greylist,
                new SpamDecideScore(4, 15)
            );

            switch ($i) {
                case 0:
                    $session = $capability->manifest($connection, $session);

                    $this->assertSame(Session::STATE_MESSAGE, $session->getState());
                    $this->assertCount(0, $messages);
                    break;

                case 1:
                    $session = $capability->manifest($connection, $session);

                    $this->assertSame(Session::STATE_MESSAGE_RECEIVED, $session->getState());
                    $this->assertCount(1, $messages);
                    break;
            }
        }
    }
}
