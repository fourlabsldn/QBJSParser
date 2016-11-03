<?php

namespace FL\QBJSParser\Model;

use FL\QBJSParser\Exception\Model\RuleConstructionException;

class Rule implements RuleInterface
{
    /**
     * Valid combinations of a Type and an Operator
     * @see Rule::$type
     * @see Rule::$operator
     *
     * Validation for this is at:
     * @see Rule::validate_Type_Operator()
     *
     * @var array
     */
    const TYPES_OPERATORS = [
        'string' => [
            'equal', 'not_equal', 'in', 'not_in', 'is_null', 'is_not_null',
            'begins_with', 'not_begins_with', 'contains', 'not_contains', 'ends_with', 'not_ends_with',
            'is_empty', 'is_not_empty',
        ],
        'integer' => [
            'equal', 'not_equal', 'in', 'not_in', 'is_null', 'is_not_null',
            'less', 'less_or_equal', 'greater', 'greater_or_equal', 'between',
        ],
        'double' => [
            'equal', 'not_equal', 'in', 'not_in', 'is_null', 'is_not_null',
            'less', 'less_or_equal', 'greater', 'greater_or_equal', 'between',
        ],
        'boolean' => [
            'equal', 'not_equal', 'is_null', 'is_not_null'
        ],
        'datetime' => [
            'equal', 'not_equal', 'in', 'not_in', 'is_null', 'is_not_null',
            'less', 'less_or_equal', 'greater', 'greater_or_equal', 'between',
        ],
        'date' => [
            'equal', 'not_equal', 'in', 'not_in', 'is_null', 'is_not_null',
            'less', 'less_or_equal', 'greater', 'greater_or_equal', 'between',
        ],
        'time' => [
            'equal', 'not_equal', 'in', 'not_in', 'is_null', 'is_not_null',
            'less', 'less_or_equal', 'greater', 'greater_or_equal', 'between',
        ],
    ];

    /**
     * Valid combinations of a Type and a Value's Type
     * @see Rule::$type
     * @see Rule::$value
     *
     * Validation for this is at:
     * @see Rule::validate_Type_ValueType()
     *
     * Special case when the valuetype is 'array':
     * @see Rule::validate_ValueIsArray()
     *
     * @var array
     */
    const TYPES_VALUETYPES = [
        'string' => ['string', 'array', 'NULL'],
        'integer' => ['integer', 'array', 'NULL'],
        'double' => ['double', 'array', 'NULL'],
        'boolean' => ['boolean', 'NULL'],
        'datetime' => [\DateTimeImmutable::class, 'array', 'NULL'],
        'date' => [\DateTimeImmutable::class, 'array', 'NULL'],
        'time' => [\DateTimeImmutable::class, 'array', 'NULL'],
    ];

    /**
     * Valid combinations of an Operator and a Value's Type
     * @see Rule::$operator
     * @see Rule::$value
     *
     * Validation for this is at:
     * @see Rule::validate_Operator_ValueType()
     *
     * Special case when the operator is 'between':
     * @see Rule::validate_OperatorIsBetween()
     *
     * @var array
     */
    const OPERATORS_VALUETYPES = [
        'equal' => ['string', 'integer', 'double', 'boolean', \DateTimeImmutable::class],
        'not_equal' => ['string', 'integer', 'double', 'boolean', \DateTimeImmutable::class],
        'in' => ['array'],
        'not_in' => ['array'],
        'between' => ['array'],
        'less' => ['integer', 'double', \DateTimeImmutable::class],
        'less_or_equal' => ['integer', 'double', \DateTimeImmutable::class],
        'greater' => ['integer', 'double', \DateTimeImmutable::class],
        'greater_or_equal' => ['integer', 'double', \DateTimeImmutable::class],
        'begins_with' => ['string'],
        'not_begins_with' => ['string'],
        'contains' => ['string'],
        'not_contains' => ['string'],
        'ends_with' => ['string'],
        'not_ends_with' => ['string'],
        'is_empty' => ['NULL'],
        'is_not_empty' => ['NULL'],
        'is_null' => ['NULL'],
        'is_not_null'=> ['NULL'],
    ];

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $field;

    /**
     * Possibilities for $type come from JSQueryBuilder types:
     * [
     *  'string', 'integer', 'double', 'date', 'time', 'datetime', 'boolean'
     * ]
     *
     * @var string
     */
    private $type;

    /**
     * Possibilities for $operator come from JSQueryBuilder operators:
     * [
     *  'equal', 'not_equal', 'in', 'not_in', 'less', 'less_or_equal', 'greater', 'greater_or_equal',
     *  'between', 'not_between', 'begins_with', 'not_begins_with', 'contains', 'not_contains',
     *  'ends_width', 'not_ends_with', 'is_empty', 'is_not_empty', 'is_null', 'is_not_null'
     * ]
     *
     * @var string
     */
    private $operator;

    /**
     * Possibilities for the type of value should come from @see Rule::valueType()
     * 
     * @var mixed
     */
    private $value;

    /**
     * @param string $id
     * @param string $field
     * @param string $type
     * @param string $operator
     * @param mixed $value
     */
    public function __construct(string $id, string $field, string $type, string $operator, $value)
    {
        $this->id = $id;
        $this->field = $field;
        $this->type = $type;
        $this->operator = $operator;
        $this->value = $value;

        $this->validate_Type_Operator();
        $this->validate_Type_ValueType();
        $this->validate_Operator_ValueType();
        // special cases
        $this->validate_ValueIsArray();
        $this->validate_OperatorIsBetween();
    }

    /**
     * @throws RuleConstructionException
     */
    private function validate_Type_Operator()
    {
        if (! in_array($this->operator, self::TYPES_OPERATORS[$this->type])) {
            throw new RuleConstructionException(sprintf(
                "Invalid Type/Operator Combination\nType: %s\nOperator: %s",
                $this->type,
                $this->operator
            ));
        }
    }

    /**
     * @throws RuleConstructionException
     */
    private function validate_Type_ValueType()
    {
        $valueType = $this->valueType($this->value);
        if (!in_array($valueType, self::TYPES_VALUETYPES[$this->type])) {
            throw new RuleConstructionException(sprintf(
                "Invalid Type/ValueType Combination\nType: %s\nValue: %s",
                $this->type,
                $valueType
            ));
        }
    }

    /**
     * @throws RuleConstructionException
     */
    private function validate_Operator_ValueType()
    {
        $valueType = $this->valueType($this->value);
        if (!in_array($valueType, self::OPERATORS_VALUETYPES[$this->operator])) {
            throw new RuleConstructionException(sprintf(
                "Invalid Operator/ValueType Combination\nOperator: %s\nValue: %s",
                $this->operator,
                $valueType
            ));
        }
    }

    /**
     * When @see Rule::$value is an array,
     * each ELEMENT must be of an allowed type,
     * according to @see Rule::TYPES_VALUETYPES
     * @throws RuleConstructionException
     */
    private function validate_ValueIsArray()
    {
        if ($this->valueType($this->value) !== 'array') {
            return;
        }

        foreach ($this->value as $element) {
            $elementValueType = $this->valueType($element);
            $allowedValueTypes = array_filter(self::TYPES_VALUETYPES[$this->type], function ($allowedValueType) {
                return $allowedValueType !== 'array';
            }, ARRAY_FILTER_USE_BOTH);

            if (!in_array($elementValueType, $allowedValueTypes)) {
                throw new RuleConstructionException(sprintf(
                    "Invalid Operator/ValueElementsType\nOperator: %s\nElementValueType: %s",
                    $this->operator,
                    $elementValueType
                ));
            }
        }
    }

    /**
     * When @see Rule::$operator is 'between', the @see Rule::$value must be an array of 2 elements
     * @throws RuleConstructionException
     */
    private function validate_OperatorIsBetween()
    {
        /**
         * Don't throw an exception if the valueType is not an array,
         * @see Rule::validate_Operator_ValueType() is in charge of that
         */
        if ($this->operator !== 'between' || $this->valueType($this->value) !== 'array') {
            return;
        }

        $valueCount = count($this->value);
        if ($valueCount === 2) {
            return;
        }

        throw new RuleConstructionException(sprintf(
            "Invalid Operator/ValueCount Combination \nOperator: %s\nValueCount: %u",
            $this->operator,
            $valueCount
        ));
    }

    /**
     * @inheritdoc
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * @inheritdoc
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @inheritdoc
     */
    public function getOperator(): string
    {
        return $this->operator;
    }

    /**
     * @inheritdoc
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @inheritdoc
     * Useful to convert a value (such as @see Rule::$value
     * into PHP variable types and class names
     * @link http://php.net/manual/en/function.gettype.php
     * @link http://php.net/manual/en/function.get-class.php
     * [
     *  'boolean', 'integer', 'double', 'string', 'array', 'NULL', ClassName::class
     * ]
     */
    public function valueType($value) : string
    {
        $valueType = gettype($value);
        if (gettype($value) === 'object') {
            return get_class($value);
        }

        return $valueType;
    }
}
