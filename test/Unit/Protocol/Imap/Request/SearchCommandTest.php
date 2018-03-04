<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Imap\Request;

use Genkgo\Mail\Protocol\Imap\Request\SearchCommand;
use Genkgo\Mail\Protocol\Imap\Request\SearchCriteria\MatchContentCriterion;
use Genkgo\Mail\Protocol\Imap\Request\SearchCriteria\Query;
use Genkgo\Mail\Protocol\Imap\Tag;
use Genkgo\TestMail\AbstractTestCase;

final class SearchCommandTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_creates_a_stream()
    {
        $command = new SearchCommand(
            Tag::fromNonce(1),
            new Query([MatchContentCriterion::text('Hello World')])
        );

        $this->assertSame('TAG1 SEARCH TEXT "Hello World"', (string)$command->toStream());
        $this->assertSame('TAG1', (string)$command->getTag());
    }

    /**
     * @test
     */
    public function it_creates_a_stream_with_a_charset()
    {
        $command = new SearchCommand(
            Tag::fromNonce(1),
            new Query([MatchContentCriterion::text('Hello World')]),
            'UTF-8'
        );

        $this->assertSame('TAG1 SEARCH CHARSET UTF-8 TEXT "Hello World"', (string)$command->toStream());
        $this->assertSame('TAG1', (string)$command->getTag());
    }

    /**
     * @test
     */
    public function it_throws_with_an_invalid_charset()
    {
        $this->expectException(\InvalidArgumentException::class);

        new SearchCommand(
            Tag::fromNonce(1),
            new Query([MatchContentCriterion::text('Hello World')]),
            'UTF*8'
        );
    }
}
