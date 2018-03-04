<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Smtp\Capability;

use Genkgo\Mail\GenericMessage;
use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Protocol\Smtp\BackendInterface;
use Genkgo\Mail\Protocol\Smtp\CapabilityInterface;
use Genkgo\Mail\Protocol\Smtp\GreyListInterface;
use Genkgo\Mail\Protocol\Smtp\Session;
use Genkgo\Mail\Protocol\Smtp\SpamDecideScore;
use Genkgo\Mail\Protocol\Smtp\SpamScoreInterface;

final class DataCapability implements CapabilityInterface
{
    /**
     * @var BackendInterface
     */
    private $backend;

    /**
     * @var SpamScoreInterface
     */
    private $spamScore;

    /**
     * @var SpamDecideScore
     */
    private $spamDecideScore;

    /**
     * @var GreyListInterface
     */
    private $greyListing;

    /**
     * @param BackendInterface $backend
     * @param SpamScoreInterface $spamScore
     * @param GreyListInterface $greyListing
     * @param SpamDecideScore $spamDecideScore
     */
    public function __construct(
        BackendInterface $backend,
        SpamScoreInterface $spamScore,
        GreyListInterface $greyListing,
        SpamDecideScore $spamDecideScore
    ) {
        $this->backend = $backend;
        $this->spamScore = $spamScore;
        $this->spamDecideScore = $spamDecideScore;
        $this->greyListing = $greyListing;
    }

    /**
     * @param ConnectionInterface $connection
     * @param Session $session
     * @return Session
     */
    public function manifest(ConnectionInterface $connection, Session $session): Session
    {
        $connection->send('354 Enter message, ending with "." on a line by itself');
        $data = [];

        while (true) {
            $line = $connection->receive();

            if ($line === '.') {
                break;
            }

            $data[] = $line;
        }

        try {
            $message = GenericMessage::fromString(\implode("\r\n", $data));
        } catch (\InvalidArgumentException $e) {
            $connection->send('500 Malformed message');
            return $session;
        }

        $spamScore = $this->spamScore->calculate($message);

        if ($this->spamDecideScore->isSpam($spamScore)) {
            $connection->send('550 Message discarded as high-probability spam');
            return $session;
        }

        if ($this->spamDecideScore->isLikelySpam($spamScore) && !$this->greyListing->contains($message)) {
            $this->greyListing->attach($message);
            $connection->send('421 Please try again later');
            return $session;
        }

        $this->greyListing->detach($message);

        $folder = 'INBOX';

        if ($this->spamDecideScore->isLikelySpam($spamScore)) {
            $folder = 'JUNK';
        }

        foreach ($session->getRecipients() as $recipient) {
            $this->backend->store($recipient, $message, $folder);
        }

        $connection->send('250 Message received, queue for delivering');
        return $session->withMessage($message);
    }

    /**
     * @return string
     */
    public function advertise(): string
    {
        return 'DATA';
    }
}
