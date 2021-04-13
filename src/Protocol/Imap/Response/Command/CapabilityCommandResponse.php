<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\Response\Command;

final class CapabilityCommandResponse
{
    /**
     * @var array<string, bool>
     */
    private $advertisements = [];

    /**
     * @param array<string, bool> $list
     */
    public function __construct(array $list)
    {
        $this->advertisements = $list;
    }

    /**
     * @param string $command
     * @return bool
     */
    public function isAdvertising(string $command): bool
    {
        if ($command === '') {
            return false;
        }

        return isset($this->advertisements[$command]);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return \sprintf(
            'CAPABILITY %s',
            \implode(' ', \array_keys($this->advertisements))
        );
    }

    /**
     * @param string $response
     * @return CapabilityCommandResponse
     */
    public static function fromString(string $response): self
    {
        $command = 'CAPABILITY ';
        $commandLength = \strlen($command);

        if (\substr($response, 0, $commandLength) !== $command) {
            throw new \InvalidArgumentException(
                \sprintf('Expected CAPABILITY command, got %s', $response)
            );
        }

        $advertisements = \preg_split('/[\s]+/', \substr($response, $commandLength));
        if ($advertisements === false) {
            $advertisements = [];
        }

        $list = \array_combine(
            $advertisements,
            \array_fill(0, \count($advertisements), true)
        );

        /** @var array<string, bool>|false $list */
        if ($list === false) {
            throw new \UnexpectedValueException('Cannot create capability list');
        }

        return new self($list);
    }
}
