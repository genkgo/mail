<?php

use Genkgo\Mail\Protocol\PlainTcpConnectionListener;
use Genkgo\Mail\Protocol\Smtp\Authentication\ArrayAuthentication;
use Genkgo\Mail\Protocol\Smtp\Backend\ConsoleBackend;
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

$config = (require_once "config.php")['server']['smtp'];

$backend = new ConsoleBackend();

$server = new Server(
    new PlainTcpConnectionListener($config['address'], $config['port']),
    [
        new AuthLoginCapability(
            new ArrayAuthentication(
                $config['users']
            )
        ),
        new MailFromCapability(),
        new RcptToCapability($backend),
        new DataCapability(
            $backend,
            new AggregateSpamScore([
                new ForbiddenWordSpamScore(
                    $config['spam']['forbidden']['words'],
                    $config['spam']['forbidden']['points']
                )
            ]),
            new ArrayGreyList(),
            new SpamDecideScore(
                $config['spam']['ham'],
                $config['spam']['spam']
            )
        )
    ],
    $config['name']
);

$server->start();