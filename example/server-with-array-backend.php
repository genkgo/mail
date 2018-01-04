<?php

use Genkgo\Mail\Protocol\PlainTcpConnectionListener;
use Genkgo\Mail\Protocol\Smtp\Authentication\ArrayAuthentication;
use Genkgo\Mail\Protocol\Smtp\Backend\ArrayBackend;
use Genkgo\Mail\Protocol\Smtp\Capability\AuthLoginCapability;
use Genkgo\Mail\Protocol\Smtp\Capability\DataCapability;
use Genkgo\Mail\Protocol\Smtp\Capability\MailFromCapability;
use Genkgo\Mail\Protocol\Smtp\Capability\RcptToCapability;
use Genkgo\Mail\Protocol\Smtp\GreyList\ArrayGreyList;
use Genkgo\Mail\Protocol\Smtp\Server;
use Genkgo\Mail\Protocol\Smtp\SpamDecideScore;
use Genkgo\Mail\Protocol\Smtp\SpamScore\AggregateSpamScore;
use Genkgo\Mail\Protocol\Smtp\SpamScore\ForbiddenWordSpamScore;

require_once __DIR__ . '/../vendor/autoload.php';

$backend = new ArrayBackend(['mailbox@domain.com'], new \ArrayObject());

$server = new Server(
    new PlainTcpConnectionListener('0.0.0.0', 8025),
    [
        new AuthLoginCapability(
            new ArrayAuthentication(
                ['username' => 'password']
            )
        ),
        new MailFromCapability(),
        new RcptToCapability($backend),
        new DataCapability(
            $backend,
            new AggregateSpamScore([
                new ForbiddenWordSpamScore(['viagra'], 5)
            ]),
            new ArrayGreyList(),
            new SpamDecideScore(5, 15)
        )
    ],
    'mail.local'
);

$server->start();