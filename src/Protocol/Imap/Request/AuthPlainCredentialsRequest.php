<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\Request;

use Genkgo\Mail\Stream\StringStream;
use Genkgo\Mail\StreamInterface;

final class AuthPlainCredentialsRequest extends AbstractContinuationRequest
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
     * AuthPlainCredentialsRequest constructor.
     * @param string $username
     * @param string $password
     */
    public function __construct(string $username, string $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * @return StreamInterface
     */
    protected function createStream(): StreamInterface
    {
        return new StringStream(
            base64_encode(
                sprintf("\0%s\0%s", $this->username, $this->password)
            )
        );
    }
}