<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\Request;

use Genkgo\Mail\Protocol\Imap\RequestInterface;
use Genkgo\Mail\Protocol\Imap\Tag;
use Genkgo\Mail\Stream\StringStream;
use Genkgo\Mail\StreamInterface;

final class AuthPlainCredentialsRequest implements RequestInterface
{
    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * @var Tag
     */
    private $tag;

    /**
     * @param Tag $tag
     * @param string $username
     * @param string $password
     */
    public function __construct(Tag $tag, string $username, string $password)
    {
        $this->tag = $tag;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * @return StreamInterface
     */
    public function toStream(): StreamInterface
    {
        return new StringStream(
            \base64_encode(
                \sprintf("\0%s\0%s", $this->username, $this->password)
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
