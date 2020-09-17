<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\MessageData\Item;

use Genkgo\Mail\Protocol\Imap\Flag;
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
        return $this->operator . ($this->silent ? 'FLAGS.SILENT' : 'FLAGS');
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

    /**
     * @param string $list
     * @return FlagsItem
     */
    public static function fromString(string $list): self
    {
        if ($list === '') {
            throw new \InvalidArgumentException('Cannot parse empty string');
        }

        $silent = false;
        $operator = self::OPERATOR_REPLACE;
        $flagList = [];

        if (isset(self::OPERATORS[$list[0]])) {
            $operator = $list[0];
            $list = \substr($list, 1);
        }

        $index = 0;
        $sequence = '';

        while (isset($list[$index])) {
            $char = $list[$index];
            $sequence .= $char;

            switch ($char) {
                case ' ':
                    $sequence = \trim($sequence);

                    if ($sequence === 'FLAGS.SILENT') {
                        $silent = true;
                    } elseif ($sequence !== 'FLAGS') {
                        throw new \UnexpectedValueException('Only expecting FLAGS or FLAGS.SILENT');
                    }

                    $sequence = '';
                    break;
                case '(':
                    $flagList = \array_map(
                        function (string $flagString) {
                            return new Flag($flagString);
                        },
                        \explode(' ', \substr($list, $index + 1, -1))
                    );
                    break 2;
            }

            $index++;
        }

        if ($silent) {
            return self::silent(new FlagParenthesizedList($flagList), $operator);
        }

        return new self(new FlagParenthesizedList($flagList), $operator);
    }
}
