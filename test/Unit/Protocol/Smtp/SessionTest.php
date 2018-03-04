<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Smtp;

use Genkgo\Mail\EmailAddress;
use Genkgo\Mail\PlainTextMessage;
use Genkgo\Mail\Protocol\Smtp\Session;
use Genkgo\TestMail\AbstractTestCase;

final class SessionTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_is_immutable()
    {
        $session = new Session();
        $this->assertNotSame($session, $session->withCommand('x'));
        $this->assertNotSame($session, $session->withEnvelope(new EmailAddress('test@genkgo.nl')));
        $this->assertNotSame($session, $session->withMessage(new PlainTextMessage('test')));
        $this->assertNotSame($session, $session->withRecipient(new EmailAddress('test@genkgo.nl')));
        $this->assertNotSame($session, $session->withState(Session::STATE_CONNECTED));
    }

    /**
     * @test
     */
    public function it_carries_command()
    {
        $session = new Session();
        $this->assertSame('x', $session->withCommand('x')->getCommand());
    }

    /**
     * @test
     */
    public function it_carries_envelope()
    {
        $session = new Session();

        $envelope = $session
            ->withEnvelope(new EmailAddress('test@genkgo.nl'))
            ->getEnvelope();

        $this->assertTrue($envelope->equals(new EmailAddress('test@genkgo.nl')));
    }

    /**
     * @test
     */
    public function it_carries_message()
    {
        $session = new Session();

        $message = $session
            ->withMessage(new PlainTextMessage('test'))
            ->getMessage();

        $this->assertSame((string)new PlainTextMessage('test'), (string)$message);
    }

    /**
     * @test
     */
    public function it_carries_recipient()
    {
        $session = new Session();

        $recipients = $session
            ->withRecipient(new EmailAddress('test@genkgo.nl'))
            ->getRecipients();

        $this->assertCount(1, $recipients);
        $this->assertTrue(\reset($recipients)->equals(new EmailAddress('test@genkgo.nl')));

        $recipients = $session
            ->withRecipient(new EmailAddress('test@genkgo.nl'))
            ->withRecipient(new EmailAddress('xxx@genkgo.nl'))
            ->getRecipients();

        $this->assertCount(2, $recipients);
        $this->assertTrue($recipients[0]->equals(new EmailAddress('test@genkgo.nl')));
        $this->assertTrue($recipients[1]->equals(new EmailAddress('xxx@genkgo.nl')));
    }

    /**
     * @test
     */
    public function it_carries_state()
    {
        $session = new Session();

        $state = $session
            ->withState(Session::STATE_EHLO)
            ->getState();

        $this->assertSame(Session::STATE_EHLO, $state);
    }

    /**
     * @test
     */
    public function it_throws_without_command()
    {
        $this->expectException(\UnexpectedValueException::class);

        $session = new Session();

        $session->getCommand();
    }

    /**
     * @test
     */
    public function it_throws_without_envelope()
    {
        $this->expectException(\UnexpectedValueException::class);

        $session = new Session();

        $session->getEnvelope();
    }

    /**
     * @test
     */
    public function it_throws_without_message()
    {
        $this->expectException(\UnexpectedValueException::class);

        $session = new Session();

        $session->getMessage();
    }

    /**
     * @test
     */
    public function it_has_default_state()
    {
        $session = new Session();

        $this->assertSame(Session::STATE_CONNECTED, $session->getState());
    }
}
