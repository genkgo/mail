<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\Request\SearchCriteria;

final class CommunicatorCriterion implements CriterionInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $query;

    /**
     * @param string $name
     * @param string $query
     */
    private function __construct(string $name, string $query)
    {
        $this->query = $query;
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->name . ' ' . $this->query;
    }

    /**
     * @param string $query
     * @return CommunicatorCriterion
     */
    public static function to(string $query): self
    {
        return new self('TO', $query);
    }

    /**
     * @param string $query
     * @return CommunicatorCriterion
     */
    public static function cc(string $query): self
    {
        return new self('CC', $query);
    }

    /**
     * @param string $query
     * @return CommunicatorCriterion
     */
    public static function bcc(string $query): self
    {
        return new self('BCC', $query);
    }

    /**
     * @param string $query
     * @return CommunicatorCriterion
     */
    public static function from(string $query): self
    {
        return new self('FROM', $query);
    }
}
