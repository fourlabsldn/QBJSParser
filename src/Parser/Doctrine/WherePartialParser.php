<?php

namespace FL\QBJSParser\Parser\Doctrine;

use FL\QBJSParser\Exception\Parser\Doctrine\InvalidFieldException;
use FL\QBJSParser\Exception\Parser\Doctrine\InvalidOperatorException;
use FL\QBJSParser\Model\RuleGroupInterface;
use FL\QBJSParser\Model\RuleInterface;
use FL\QBJSParser\Parsed\Doctrine\ParsedRuleGroup;

abstract class WherePartialParser
{
    /**
     * @var string
     */
    private static $dqlPartialWhereString;

    /**
     * @var array
     */
    private static $parameters;

    /**
     * @var array
     */
    private static $queryBuilderFieldsToWhereAlias;

    final private function __construct()
    {
    }

    /**
     * @param array              $queryBuilderFieldsToProperties
     * @param RuleGroupInterface $ruleGroup
     * @param array              $embeddableFieldsToProperties
     * @param array              $embeddableInsideEmbeddableFieldsToProperties
     * @param string             $className
     *
     * @return ParsedRuleGroup
     */
    final public static function parse(
        array $queryBuilderFieldsToProperties,
        RuleGroupInterface $ruleGroup,
        array $embeddableFieldsToProperties,
        array $embeddableInsideEmbeddableFieldsToProperties,
        string $className
    ): ParsedRuleGroup {
        foreach ($queryBuilderFieldsToProperties as $field => $property) {
            static::$queryBuilderFieldsToWhereAlias[$field] = StringManipulator::replaceAllDotsExceptLast(SelectPartialParser::OBJECT_WORD.'.'.$property);
        }
        foreach ($embeddableFieldsToProperties as $field => $property) {
            static::$queryBuilderFieldsToWhereAlias[$field] = SelectPartialParser::OBJECT_WORD.StringManipulator::replaceAllDotsExceptLastTwo('.'.$property);
        }
        foreach ($embeddableInsideEmbeddableFieldsToProperties as $field => $property) {
            static::$queryBuilderFieldsToWhereAlias[$field] = SelectPartialParser::OBJECT_WORD.StringManipulator::replaceAllDotsExceptLastThree('.'.$property);
        }

        static::$parameters = [];
        static::$dqlPartialWhereString = '';

        // populate static::$dqlPartialWhereString and static::$parameters
        if (
            count($ruleGroup->getRuleGroups()) !== 0 ||
            count($ruleGroup->getRules()) !== 0
        ) {
            static::parseRuleGroup($ruleGroup, ' WHERE ( ', ' ) ');
        }

        return new ParsedRuleGroup(static::$dqlPartialWhereString, static::$parameters, $className);
    }

    /**
     * @param RuleGroupInterface $ruleGroup
     * @param string|null        $prepend
     * @param string|null        $append
     */
    final private static function parseRuleGroup(RuleGroupInterface $ruleGroup, string $prepend = null, string $append = null)
    {
        static::$dqlPartialWhereString .= $prepend ?? '';
        $iteration = 0;

        if ($ruleGroup->getMode() === RuleGroupInterface::MODE_AND) {
            $andOr = ' AND ';
        } else {
            $andOr = ' OR ';
        }

        foreach ($ruleGroup->getRules() as $rule) {
            if ($iteration === 0) {
                static::parseRule($rule, ' ', ' ');
            } else {
                static::parseRule($rule, ' '.$andOr.' ', ' ');
            }
            ++$iteration;
        }

        foreach ($ruleGroup->getRuleGroups() as $ruleGroup) {
            if ($iteration === 0) {
                static::parseRuleGroup($ruleGroup, ' ( ', ' ) ');
            } else {
                static::parseRuleGroup($ruleGroup, ' '.$andOr.' ( ', ' ) ');
            }
            ++$iteration;
        }

        static::$dqlPartialWhereString .= $append ?? '';

        return;
    }

    /**
     * @param RuleInterface $rule
     * @param string|null   $prepend
     * @param string|null   $append
     */
    final private static function parseRule(RuleInterface $rule, string $prepend = null, string $append = null)
    {
        static::$dqlPartialWhereString .= $prepend ?? '';

        $queryBuilderField = $rule->getField();
        $safeField = static::queryBuilderFieldToWhereAlias($queryBuilderField);
        $queryBuilderOperator = $rule->getOperator();
        $doctrineOperator = static::queryBuilderOperatorToDoctrineOperator($queryBuilderOperator);
        $value = static::transformValueAccordingToQueryBuilderOperator($queryBuilderOperator, $rule->getValue());

        $parameterCount = count(static::$parameters);

        if (static::queryBuilderOperator_UsesValue($queryBuilderOperator)) {
            static::$dqlPartialWhereString .= $safeField.' '.$doctrineOperator.' ?'.$parameterCount.' ';
            static::$parameters[$parameterCount] = $value;
        } elseif (static::queryBuilderOperator_UsesArray($queryBuilderOperator)) {
            static::$dqlPartialWhereString .= $safeField.' '.$doctrineOperator.' (?'.$parameterCount.') ';
            static::$parameters[$parameterCount] = $value;
        } elseif (static::queryBuilderOperator_UsesArrayOfTwo($queryBuilderOperator)) {
            static::$dqlPartialWhereString .= $safeField.' '.$doctrineOperator.' ?'.$parameterCount.' AND ?'.($parameterCount + 1).' ';
            static::$parameters[$parameterCount] = $value[0];
            static::$parameters[$parameterCount + 1] = $value[1];
        } elseif (static::queryBuilderOperator_UsesNull($queryBuilderOperator)) {
            static::$dqlPartialWhereString .= $safeField.' '.$doctrineOperator.' ';
        }

        static::$dqlPartialWhereString .= $append ?? '';

        return;
    }

    /**
     * @param string $operator
     *
     * @return bool
     */
    final private static function queryBuilderOperator_UsesValue(string $operator): bool
    {
        return in_array($operator, [
            'equal', 'not_equal', 'less', 'less_or_equal', 'greater', 'greater_or_equal',
            'begins_with', 'not_begins_with', 'contains', 'not_contains', 'ends_with', 'not_ends_with',
        ]);
    }

    /**
     * @param string $operator
     *
     * @return bool
     */
    final private static function queryBuilderOperator_UsesArray(string $operator): bool
    {
        return in_array($operator, ['in', 'not_in']);
    }

    /**
     * @param string $operator
     *
     * @return bool
     */
    final private static function queryBuilderOperator_UsesArrayOfTwo(string $operator): bool
    {
        return in_array($operator, ['between', 'not_between']);
    }

    /**
     * @param string $operator
     *
     * @return bool
     */
    final private static function queryBuilderOperator_UsesNull(string $operator): bool
    {
        return in_array($operator, ['is_empty', 'is_not_empty', 'is_null', 'is_not_null']);
    }

    /**
     * @param string $queryBuilderOperator
     *
     * @return string
     */
    final private static function queryBuilderOperatorToDoctrineOperator(string $queryBuilderOperator): string
    {
        $dictionary = [
            'equal' => '=',
            'not_equal' => '!=',
            'in' => 'IN',
            'not_in' => 'NOT IN',
            'between' => 'BETWEEN',
            'not_between' => 'NOT BETWEEN',
            'less' => '<',
            'less_or_equal' => '<=',
            'greater' => '>',
            'greater_or_equal' => '>=',
            'begins_with' => 'LIKE',
            'not_begins_with' => 'NOT LIKE',
            'contains' => 'LIKE',
            'not_contains' => 'NOT LIKE',
            'ends_with' => 'LIKE',
            'not_ends_with' => 'NOT LIKE',
            // doctrine's 'IS EMPTY' and 'IS NOT EMPTY' is for collections, not strings
            'is_empty' => '= \'\'',
            'is_not_empty' => '!= \'\'', 'is_null' => 'IS NULL',
            'is_not_null' => 'IS NOT NULL',
        ];

        if (!isset($dictionary[$queryBuilderOperator])) {
            throw new InvalidOperatorException();
        }

        return $dictionary[$queryBuilderOperator];
    }

    /**
     * @param string $queryBuilderOperator
     * @param mixed  $value
     *
     * @return string
     *
     * @see http://doctrine.readthedocs.io/en/latest/en/manual/dql-doctrine-query-language.html#like-expressions
     */
    final private static function transformValueAccordingToQueryBuilderOperator(string $queryBuilderOperator, $value)
    {
        if (is_string($value)) {
            switch ($queryBuilderOperator) {
                case 'begins_with':
                case 'not_begins_with':
                    return $value.'%';
                case 'contains':
                case 'not_contains':
                    return '%'.$value.'%';
                case 'ends_with':
                case 'not_ends_with':
                    return '%'.$value;
            }
        }

        return $value;
    }

    /**
     * @param string $queryBuilderField
     *
     * @return string
     */
    final private static function queryBuilderFieldToWhereAlias(string $queryBuilderField): string
    {
        $dictionary = static::$queryBuilderFieldsToWhereAlias;

        if (!array_key_exists($queryBuilderField, $dictionary)) {
            throw new InvalidFieldException($queryBuilderField);
        }

        return $dictionary[$queryBuilderField];
    }
}
