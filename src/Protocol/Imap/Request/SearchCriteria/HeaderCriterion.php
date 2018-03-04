<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\Request\SearchCriteria;

use Genkgo\Mail\Header\HeaderName;

final class HeaderCriterion implements CriterionInterface
{
    /**
     * @var HeaderName
     */
    private $headerName;

    /**
     * @var string
     */
    private $query;

    /**
     * HeaderCriterium constructor.
     * @param HeaderName $headerName
     * @param string $query
     */
    public function __construct(HeaderName $headerName, string $query)
    {
        if ($query === '' || \strlen($query) !== \strcspn($query, "\r\n")) {
            throw new \InvalidArgumentException('CR and LF are not allowed in quoted strings');
        }

        $this->headerName = $headerName;
        $this->query = $query;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return \sprintf(
            'HEADER %s "%s"',
            (string)$this->headerName,
            \addslashes($this->query)
        );
    }
}
