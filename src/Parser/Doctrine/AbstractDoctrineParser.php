<?php

namespace FL\QBJSParser\Parser\Doctrine;

use FL\QBJSParser\Exception\Parser\Doctrine\InvalidClassNameException;
use FL\QBJSParser\Exception\Parser\Doctrine\InvalidFieldException;
use FL\QBJSParser\Exception\Parser\Doctrine\InvalidOperatorException;
use FL\QBJSParser\Exception\Parser\Doctrine\MapFunctionException;
use FL\QBJSParser\Model\RuleGroupInterface;
use FL\QBJSParser\Model\RuleInterface;
use FL\QBJSParser\Parsed\Doctrine\ParsedRuleGroup;
use FL\QBJSParser\Parser\ParserInterface;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;

abstract class AbstractDoctrineParser implements ParserInterface
{
    /**
     * @var string
     */
    protected $className;

    /**
     * @var string
     */
    protected $dqlString = '';

    /**
     * @var array
     */
    protected $parameters = [];

    /**
     * @var array
     */
    protected $queryBuilderFieldsToEntityProperties = ['id'=>'id'];

    /**
     * @param string $className
     * @param array $queryBuilderFieldsToEntityProperties
     */
    public function __construct(string $className, array $queryBuilderFieldsToEntityProperties = ['id'=>'id'])
    {
        if (!class_exists($className)) {
            throw new InvalidClassNameException(sprintf(
                'Expected valid class name in %s. %s was given, and it is not a valid class name.',
                static::class,
                $className
            ));
        }
        $this->queryBuilderFieldsToEntityProperties = $queryBuilderFieldsToEntityProperties;
        $this->className = $className;
        $this->validateQueryBuilderFieldsToEntityProperties();
    }

    /**
     * @inheritdoc
     * @return array
     */
    final public function parse(RuleGroupInterface $ruleGroup) : ParsedRuleGroup
    {
        $this->dqlString = 'SELECT object FROM ' . $this->className . ' object ';
        $this->parameters = [];

        // populate $this->dqlString and $this->parameters
        $this->parseRuleGroup($ruleGroup, ' WHERE (', ') ');

        // remove double whitespaces from $this->dqlString
        $dqlString = preg_replace('/\s+/', ' ', $this->dqlString);

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
        $this->dqlString .= $prepend ?? '';
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

        $this->dqlString .= $append ?? '';

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
        $this->dqlString .= $prepend ?? '';

        $queryBuilderField = $rule->getField();
        $safeField = $this->queryBuilderFieldToEntityProperty($queryBuilderField);
        $queryBuilderOperator = $rule->getOperator();
        $doctrineOperator = $this->queryBuilderOperatorToDoctrineOperator($queryBuilderOperator);
        $value = $rule->getValue();

        $parameterCount = count($this->parameters);

        if ($this->queryBuilderOperator_UsesValue($queryBuilderOperator)) {
            $this->dqlString .= ' object.' . $safeField . ' ' . $doctrineOperator . ' ?' . $parameterCount . ' ';
            $this->parameters[$parameterCount] = $value;
        } elseif ($this->queryBuilderOperator_UsesArray($queryBuilderOperator)) {
            $this->dqlString .= ' object.' . $safeField . ' ' . $doctrineOperator . ' (?'. $parameterCount . ') ';
            $this->parameters[$parameterCount] = $value;
        } elseif ($this->queryBuilderOperator_UsesArrayOfTwo($queryBuilderOperator)) {
            $this->dqlString .= ' object.' . $safeField . ' ' . $doctrineOperator . ' ?'. $parameterCount . ' AND ?'. ($parameterCount + 1) . ' ';
            $this->parameters[$parameterCount] = $value[0];
            $this->parameters[$parameterCount+1] = $value[1];
        } elseif ($this->queryBuilderOperator_UsesNull($queryBuilderOperator)) {
            $this->dqlString .= ' object.' . $safeField . ' ' . $doctrineOperator . ' ';
        }

        $this->dqlString .= $append ?? '';
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

    /**
     * @link http://symfony.com/doc/current/components/property_info.html#components-property-info-extractors
     * @throws MapFunctionException
     */
    final private function validateQueryBuilderFieldsToEntityProperties()
    {
        $propertyInfo = new PropertyInfoExtractor([new ReflectionExtractor()]);
        $properties = $propertyInfo->getProperties($this->className);

        foreach ($this->queryBuilderFieldsToEntityProperties as $queryBuilderField => $entityProperty) {
            if (!in_array($entityProperty, $properties)) {
                throw new MapFunctionException(sprintf(
                    'Property %s is not accessible in %s.',
                    $entityProperty,
                    $this->className
                ));
            }
        }
    }
}
