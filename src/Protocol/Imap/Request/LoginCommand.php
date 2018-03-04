<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\Request;

use Genkgo\Mail\Protocol\Imap\Tag;
use Genkgo\Mail\Stream\StringStream;
use Genkgo\Mail\StreamInterface;

final class LoginCommand extends AbstractCommand
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
        if (\preg_match('/\s/', $username . $password) === 1) {
            throw new \InvalidArgumentException('Username and/or password may not contain any whitespace');
        }

        $this->username = $username;
        $this->password = $password;
        $this->tag = $tag;
    }

    /**
     * @return StreamInterface
     */
    protected function createStream(): StreamInterface
    {
        return new StringStream(
            \sprintf(
                'LOGIN %s %s',
                $this->username,
                $this->password
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
