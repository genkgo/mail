<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\Request;

use Genkgo\Mail\Protocol\Imap\RequestInterface;
use Genkgo\Mail\Protocol\Imap\Tag;
use Genkgo\Mail\Stream\StringStream;
use Genkgo\Mail\StreamInterface;

final class UidCommand implements RequestInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param RequestInterface $request
     */
    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * @return StreamInterface
     */
    public function toStream(): StreamInterface
    {
        return new StringStream(
            \sprintf(
                '%s UID %s',
                (string)$this->getTag(),
                $this->getTag()->extractBodyFromLine((string) $this->request->toStream())
            )
        );
    }

    /**
     * @return Tag
     */
    public function getTag(): Tag
    {
        return $this->request->getTag();
    }
}
