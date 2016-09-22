<?php

namespace FL\QBJSParser\Parser\Doctrine;

use FL\QBJSParser\Exception\Parser\Doctrine\InvalidFieldException;
use FL\QBJSParser\Exception\Parser\Doctrine\InvalidOperatorException;
use FL\QBJSParser\Model\RuleGroupInterface;
use FL\QBJSParser\Model\RuleInterface;
use FL\QBJSParser\Parsed\Doctrine\ParsedRuleGroup;

class WhereParser
{
    /**
     * @var string
     */
    private $dqlPartialWhereString = '';

    /**
     * @var array
     */
    private $parameters = [];

    /**
     * @var array
     */
    private $queryBuilderFieldsToEntityProperties = ['id'=>'id'];

    /**
     * @param array $queryBuilderFieldsToEntityProperties
     */
    public function __construct(array $queryBuilderFieldsToEntityProperties = ['id'=>'id'])
    {
        $this->queryBuilderFieldsToEntityProperties = $queryBuilderFieldsToEntityProperties;
    }

    /**
     * @inheritdoc
     * @return RuleGroupInterface
     */
    final public function parse(RuleGroupInterface $ruleGroup) : ParsedRuleGroup
    {
        $this->parameters = [];

        // populate $this->dqlPartialWhereString and $this->parameters
        $this->parseRuleGroup($ruleGroup, ' WHERE ( ', ' ) ');

        // remove double whitespaces from $this->dqlPartialWhereString
        $dqlString = preg_replace('/\s+/', ' ', $this->dqlPartialWhereString);

        return new ParsedRuleGroup($dqlString, $this->parameters);
    }

    /**
     * @param RuleGroupInterface $ruleGroup
     * @param string|null $prepend
     * @param string|null $append
     * @return void
     */
    final private function parseRuleGroup(RuleGroupInterface $ruleGroup, string $prepend = null, string $append = null)
    {
        $this->dqlPartialWhereString .= $prepend ?? '';
        $iteration = 0;

        if ($ruleGroup->getMode() === RuleGroupInterface::MODE_AND) {
            $andOr = ' AND ';
        } else {
            $andOr = ' OR ';
        }

        foreach ($ruleGroup->getRules() as $rule) {
            if ($iteration === 0) {
                $this->parseRule($rule, ' ', ' ');
            } else {
                $this->parseRule($rule, ' ' . $andOr .' ', ' ');
            }
            $iteration ++;
        }

        foreach ($ruleGroup->getRuleGroups() as $ruleGroup) {
            if ($iteration === 0) {
                $this->parseRuleGroup($ruleGroup, ' ( ', ' ) ');
            } else {
                $this->parseRuleGroup($ruleGroup, ' ' . $andOr . ' ( ', ' ) ');
            }
            $iteration ++;
        }

        $this->dqlPartialWhereString .= $append ?? '';

        return;
    }

    /**
     * @param RuleInterface $rule
     * @param string|null $prepend
     * @param string|null $append
     * @return void
     */
    final private function parseRule(RuleInterface $rule, string $prepend = null, string $append = null)
    {
        $this->dqlPartialWhereString .= $prepend ?? '';

        $queryBuilderField = $rule->getField();
        $safeField = $this->queryBuilderFieldToEntityProperty($queryBuilderField);
        $queryBuilderOperator = $rule->getOperator();
        $doctrineOperator = $this->queryBuilderOperatorToDoctrineOperator($queryBuilderOperator);
        $value = $rule->getValue();

        $parameterCount = count($this->parameters);

        if ($this->queryBuilderOperator_UsesValue($queryBuilderOperator)) {
            $this->dqlPartialWhereString .= ' object.' . $safeField . ' ' . $doctrineOperator . ' ?' . $parameterCount . ' ';
            $this->parameters[$parameterCount] = $value;
        } elseif ($this->queryBuilderOperator_UsesArray($queryBuilderOperator)) {
            $this->dqlPartialWhereString .= ' object.' . $safeField . ' ' . $doctrineOperator . ' (?'. $parameterCount . ') ';
            $this->parameters[$parameterCount] = $value;
        } elseif ($this->queryBuilderOperator_UsesArrayOfTwo($queryBuilderOperator)) {
            $this->dqlPartialWhereString .= ' object.' . $safeField . ' ' . $doctrineOperator . ' ?'. $parameterCount . ' AND ?'. ($parameterCount + 1) . ' ';
            $this->parameters[$parameterCount] = $value[0];
            $this->parameters[$parameterCount+1] = $value[1];
        } elseif ($this->queryBuilderOperator_UsesNull($queryBuilderOperator)) {
            $this->dqlPartialWhereString .= ' object.' . $safeField . ' ' . $doctrineOperator . ' ';
        }

        $this->dqlPartialWhereString .= $append ?? '';
        return;
    }

    /**
     * @param string $operator
     * @return bool
     */
    final private function queryBuilderOperator_UsesValue(string $operator) : bool
    {
        return in_array($operator, [
            'equal', 'not_equal', 'less', 'less_or_equal', 'greater', 'greater_or_equal',
            'begins_with', 'not_begins_with', 'contains', 'not_contains', 'ends_with', 'not_ends_with',
        ]);
    }

    /**
     * @param string $operator
     * @return bool
     */
    final private function queryBuilderOperator_UsesArray(string $operator) : bool
    {
        return in_array($operator, ['in', 'not_in']);
    }

    /**
     * @param string $operator
     * @return bool
     */
    final private function queryBuilderOperator_UsesArrayOfTwo(string $operator) : bool
    {
        return in_array($operator, ['between']);
    }

    /**
     * @param string $operator
     * @return bool
     */
    final private function queryBuilderOperator_UsesNull(string $operator) : bool
    {
        return in_array($operator, ['is_empty', 'is_not_empty', 'is_null', 'is_not_null']);
    }

    /**
     * @param string $queryBuilderOperator
     * @return string
     */
    final private function queryBuilderOperatorToDoctrineOperator(string $queryBuilderOperator) : string
    {
        $dictionary = [
            'equal' => '=',
            'not_equal' => '!=',
            'in' => 'IN',
            'not_in' => 'NOT IN',
            'between' => 'BETWEEN',
            'less' => '<',
            'less_or_equal' => '<=',
            'greater' => '>',
            'greater_or_equal' => '>=',
            'begins_with' => '%LIKE',
            'not_begins_with' => '%NOT_LIKE',
            'contains' => '%LIKE%',
            'not_contains' => '%NOT LIKE%',
            'ends_with' => 'LIKE%',
            'not_ends_with' => 'NOT LIKE%',
            'is_empty' => 'IS EMPTY',
            'is_not_empty' => 'IS NOT EMPTY',
            'is_null' => 'IS NULL',
            'is_not_null'=> 'IS NOT NULL',
        ];

        if (!isset($dictionary[$queryBuilderOperator])) {
            throw new InvalidOperatorException();
        }

        return $dictionary[$queryBuilderOperator];
    }

    /**
     * @param string $queryBuilderField
     * @return string
     */
    final private function queryBuilderFieldToEntityProperty(string $queryBuilderField) : string
    {
        $dictionary = $this->queryBuilderFieldsToEntityProperties;

        if (!array_key_exists($queryBuilderField, $dictionary)) {
            throw new InvalidFieldException($queryBuilderField);
        }

        return $dictionary[$queryBuilderField];
    }
}
