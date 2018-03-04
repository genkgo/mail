<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\MessageData\Item;

use Genkgo\Mail\Protocol\Imap\FlagParenthesizedList;
use Genkgo\Mail\Protocol\Imap\MessageData\ItemInterface;

final class FlagsItem implements ItemInterface
{
    public const OPERATOR_REPLACE = '';
    
    public const OPERATOR_ADD = '+';
    
    public const OPERATOR_REMOVE = '-';
    
    private const OPERATORS = [
        self::OPERATOR_REPLACE => true,
        self::OPERATOR_ADD => true,
        self::OPERATOR_REMOVE => true,
    ];

    /**
     * @var FlagParenthesizedList
     */
    private $flagList;

    /**
     * @var bool
     */
    private $silent = false;

    /**
     * @var string
     */
    private $operator = '';

    /**
     * @param FlagParenthesizedList $flagList
     * @param string $operator
     */
    public function __construct(FlagParenthesizedList $flagList, string $operator = '')
    {
        if (!isset(self::OPERATORS[$operator])) {
            throw new \InvalidArgumentException('Invalid operator');
        }

        $this->flagList = $flagList;
        $this->operator = $operator;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->operator . $this->silent ? 'FLAGS.SILENT' : 'FLAGS';
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return \sprintf(
            '%s %s',
            $this->getName(),
            (string)$this->flagList
        );
    }

    /**
     * @param FlagParenthesizedList $flagParenthesizedList
     * @param string $operator
     * @return FlagsItem
     */
    public static function silent(FlagParenthesizedList $flagParenthesizedList, string $operator = ''): self
    {
        $bodySection = new self($flagParenthesizedList, $operator);
        $bodySection->silent = true;
        return $bodySection;
    }
}
