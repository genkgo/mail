<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap;

final class Tag
{
    /**
     * @var string
     */
    private $tag;

    /**
     * @var int
     */
    private $tagLength;

    /**
     * @param string $tag
     */
    public function __construct(string $tag)
    {
        $this->tag = $tag;
        $this->tagLength = \strlen($tag);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->tag;
    }

    /**
     * @param string $line
     * @return string
     */
    public function extractBodyFromLine(string $line): string
    {
        if (\substr($line, 0, $this->tagLength + 1) === $this->tag . ' ') {
            return \substr($line, $this->tagLength + 1);
        }

        throw new \InvalidArgumentException(
            \sprintf(
                'Line `%s` does not contain the tag %s',
                $line,
                $this->tag
            )
        );
    }

    /**
     * @param int $nonce
     * @return Tag
     */
    public static function fromNonce(int $nonce): self
    {
        return new self('TAG' . $nonce);
    }
}
