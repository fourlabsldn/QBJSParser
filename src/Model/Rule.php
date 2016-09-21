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
        'datetime' => [\DateTime::class, 'array', 'NULL'],
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
        'equal' => ['string', 'integer', 'double', 'boolean', \DateTime::class],
        'not_equal' => ['string', 'integer', 'double', 'boolean', \DateTime::class],
        'in' => ['array'],
        'not_in' => ['array'],
        'between' => ['array'],
        'less' => ['integer', 'double', \DateTime::class],
        'less_or_equal' => ['integer', 'double', \DateTime::class],
        'greater' => ['integer', 'double', \DateTime::class],
        'greater_or_equal' => ['integer', 'double', \DateTime::class],
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
     * @var string
     * Possibilities for $type come from JSQueryBuilder types:
     * [
     *  'string', 'integer', 'double', 'date', 'time', 'datetime', 'boolean'
     * ]
     */
    private $type;

    /**
     * @var string
     * Possibilities for $operator come from JSQueryBuilder operators:
     * [
     *  'equal', 'not_equal', 'in', 'not_in', 'less', 'less_or_equal', 'greater', 'greater_or_equal',
     *  'between', 'not_between', 'begins_with', 'not_begins_with', 'contains', 'not_contains',
     *  'ends_width', 'not_ends_with', 'is_empty', 'is_not_empty', 'is_null', 'is_not_null'
     * ]
     */
    private $operator;

    /**
     * @var mixed
     * Possibilities for the type of value should come from @see Rule::valueType()
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
        $this->validateConstruction();
    }

    /**
     * @throws RuleConstructionException
     */
    private function validateConstruction()
    {
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
            throw new RuleConstructionException("Invalid Type/Operator Combination \nType: {$this->type} \nOperator: {$this->operator}");
        }
    }

    /**
     * @throws RuleConstructionException
     */
    private function validate_Type_ValueType()
    {
        $valueType = $this->valueType($this->value);
        if (! in_array($valueType, self::TYPES_VALUETYPES[$this->type])) {
            throw new RuleConstructionException("Invalid Type/ValueType Combination \nType: {$this->type} \nValue: {$valueType}");
        }
    }

    /**
     * @throws RuleConstructionException
     */
    private function validate_Operator_ValueType()
    {
        $valueType = $this->valueType($this->value);

        if (! in_array($valueType, self::OPERATORS_VALUETYPES[$this->operator])) {
            throw new RuleConstructionException("Invalid Operator/ValueType Combination \nOperator: {$this->operator} \nValue: {$valueType}");
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
        if ($this->valueType($this->value) === 'array') {
            $elements = $this->value;

            foreach ($elements as $element) {
                $elementValueType = $this->valueType($element);
                $allowedValueTypes = array_filter(self::TYPES_VALUETYPES[$this->type], function ($allowedValueType, $key) {
                    return $allowedValueType !== 'array';
                }, ARRAY_FILTER_USE_BOTH);

                if (! in_array($elementValueType, $allowedValueTypes)) {
                    throw new RuleConstructionException("Invalid Operator/ValueElementsType \nOperator: {$this->operator} \nElementValueType: {$elementValueType}");
                }
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
        if ($this->operator === 'between' && $this->valueType($this->value) === 'array') {
            $valueCount = count($this->value);
            if ($valueCount !== 2) {
                throw new RuleConstructionException("Invalid Operator/ValueCount Combination \nOperator: {$this->operator} \nValueCount: {$valueCount}");
            }
        }
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
        } else {
            return $valueType;
        }
    }
}
