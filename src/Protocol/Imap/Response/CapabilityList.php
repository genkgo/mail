<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\Response;

use Genkgo\Mail\Protocol\Imap\ResponseInterface;

final class CapabilityList
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
     * @return CapabilityList
     */
    public static function fromResponse(ResponseInterface $response): self
    {
        $advertisements = preg_split('/[\s]+/', $response->getBody());

        return new self(
            array_combine(
                $advertisements,
                array_fill(0, count($advertisements), true)
            )
        );
    }

}