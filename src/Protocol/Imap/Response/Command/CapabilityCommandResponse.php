<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\Response\Command;

use Genkgo\Mail\Protocol\Imap\ResponseInterface;

final class CapabilityCommandResponse
{
    /**
     * @var array
     */
    private $advertisements = [];

    /**
     * CapabilityList constructor.
     * @param array $list
     */
    public function __construct(array $list)
    {
        $this->advertisements = $list;
    }

    /**
     * @param string $command
     * @return bool
     */
    public function isAdvertising(string $command)
    {
        if ($command === '') {
            return false;
        }

        return isset($this->advertisements[$command]);
    }

    /**
     * @param ResponseInterface $response
     * @return CapabilityCommandResponse
     */
    public static function fromResponse(ResponseInterface $response): self
    {
        $command = 'CAPABILITY ';
        $commandLength = strlen($command);

        $body = $response->getBody();
        if (substr($body,0, $commandLength) !== $command) {
            throw new \InvalidArgumentException(
                sprintf('Expected CAPABILITY command, got %s', $body)
            );
        }

        $advertisements = preg_split('/[\s]+/', substr($body, $commandLength));

        return new self(
            array_combine(
                $advertisements,
                array_fill(0, count($advertisements), true)
            )
        );
    }

}