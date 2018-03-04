<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\Request;

use Genkgo\Mail\Protocol\Imap\Request\SearchCriteria\Query;
use Genkgo\Mail\Protocol\Imap\Tag;
use Genkgo\Mail\Stream\StringStream;
use Genkgo\Mail\StreamInterface;

final class SearchCommand extends AbstractCommand
{
    private const CHARSET_VALID = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-+.:_";

    /**
     * @var Tag
     */
    private $tag;

    /**
     * @var Query
     */
    private $query;

    /**
     * @var string
     */
    private $charset;

    /**
     * @param Tag $tag
     * @param Query $query
     * @param string $charset
     */
    public function __construct(Tag $tag, Query $query, string $charset = '')
    {
        if (\strlen($charset) !== \strspn($charset, self::CHARSET_VALID)) {
            throw new \InvalidArgumentException("Invalid charset");
        }

        $this->tag = $tag;
        $this->query = $query;
        $this->charset = $charset;
    }

    /**
     * @return StreamInterface
     */
    protected function createStream(): StreamInterface
    {
        return new StringStream(
            \sprintf(
                'SEARCH %s%s',
                $this->charset ? 'CHARSET ' . $this->charset . ' ' : '',
                (string)$this->query
            )
        );
    }

    /**
     * @return Tag
     */
    public function getTag(): Tag
    {
        return $this->tag;
    }
}
