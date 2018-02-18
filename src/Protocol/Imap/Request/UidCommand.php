<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\Request;

use Genkgo\Mail\Protocol\Imap\RequestInterface;
use Genkgo\Mail\Protocol\Imap\Tag;
use Genkgo\Mail\Stream\ConcatenatedStream;
use Genkgo\Mail\Stream\StringStream;
use Genkgo\Mail\StreamInterface;

final class UidCommand implements RequestInterface
{

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * UidCommand constructor.
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
        return new ConcatenatedStream([
            new StringStream('UID '),
            $this->request->toStream()
        ]);
    }

    /**
     * @return Tag
     */
    public function getTag(): Tag
    {
        return $this->request->getTag();
    }
}