<?php

namespace FL\QBJSParser\Parser\Doctrine;

use FL\QBJSParser\Exception\Parser\Doctrine\InvalidClassNameException;
use FL\QBJSParser\Exception\Parser\Doctrine\FieldMappingException;
use FL\QBJSParser\Exception\Parser\Doctrine\MissingAssociationClassException;
use FL\QBJSParser\Model\RuleGroupInterface;
use FL\QBJSParser\Parsed\Doctrine\ParsedRuleGroup;
use FL\QBJSParser\Parser\ParserInterface;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;

class DoctrineParser implements ParserInterface
{
    /**
     * @var string
     */
    private $className;

    /**
     * @var array
     */
    private $queryBuilderFieldsToProperties;

    /**
     * @var array
     */
    private $queryBuilderFieldPrefixesToAssociationClasses;

    /**
     * @param string $className
     * @param array  $queryBuilderFieldsToProperties                E.g. [
     *                                                              'id' => 'id',
     *                                                              'labels.id' => 'labels.id',
     *                                                              'labels.name' => 'labels.name',
     *                                                              'labels.authors.id'=> 'labels.authors.id',
     *                                                              'labels.authors.address.city'=> 'labels.authors.address.city',
     *                                                              'authors.id' => 'authors.id',
     *                                                              ]
     * @param array  $queryBuilderFieldPrefixesToAssociationClasses E.g. [
     *                                                              'labels' => Label::class,
     *                                                              'labels.authors' => Author::class,
     *                                                              'labels.authors.address' => Address::class,
     *                                                              'author' => Address::class,
     *                                                              ]
     */
    public function __construct(string $className, array $queryBuilderFieldsToProperties, array $queryBuilderFieldPrefixesToAssociationClasses)
    {
        $this->className = $className;
        $this->queryBuilderFieldsToProperties = $queryBuilderFieldsToProperties;
        $this->queryBuilderFieldPrefixesToAssociationClasses = $queryBuilderFieldPrefixesToAssociationClasses;
        $this->validate();
    }

    /**
     * {@inheritdoc}
     *
     * @return ParsedRuleGroup
     */
    final public function parse(RuleGroupInterface $ruleGroup, array $sortColumns = null) : ParsedRuleGroup
    {
        $selectString = SelectPartialParser::parse($this->queryBuilderFieldPrefixesToAssociationClasses);
        $fromString = FromPartialParser::parse($this->className);
        $joinString = JoinPartialParser::parse($this->queryBuilderFieldPrefixesToAssociationClasses);

        $whereParsedRuleGroup = WherePartialParser::parse($this->queryBuilderFieldsToProperties, $ruleGroup);
        $whereString = $whereParsedRuleGroup->getDqlString();
        $parameters = $whereParsedRuleGroup->getParameters();

        $orderString = OrderPartialParser::parse($this->queryBuilderFieldsToProperties, $sortColumns);

        $dqlString = preg_replace('/\s+/', ' ',$selectString.$fromString.$joinString.$whereString.$orderString);

        return new ParsedRuleGroup($dqlString, $parameters); // preg_replace -> no more than one space
    }

    /**
     * @throws InvalidClassNameException|FieldMappingException|MissingAssociationClassException
     */
    final private function validate()
    {
        $this->validateClass($this->className);

        // check $queryBuilderFieldsToProperties
        foreach ($this->queryBuilderFieldsToProperties as $queryBuilderField => $property) {
            $suffixPattern = '/\.((?!\.).)+$/';
            $suffixMatches = [];
            $doesFieldHaveSuffix = preg_match($suffixPattern, $queryBuilderField, $suffixMatches);
            if ($doesFieldHaveSuffix) { // $queryBuilderField is for an Association
                $fieldPrefix = preg_replace($suffixPattern, '', $queryBuilderField);
                $this->validateFieldPrefixIsInAssociations($fieldPrefix);
                $fieldSuffix = str_replace('.', '', $suffixMatches[0]); // remove preceding dot
                $classForThisPrefix = $this->queryBuilderFieldPrefixesToAssociationClasses[$fieldPrefix];
                $this->validateClass($classForThisPrefix);
                $this->validateClassHasProperty($classForThisPrefix, $fieldSuffix);
            } else { // $queryBuilderField is for $this->className
                $this->validateClassHasProperty($this->className, $property);
            }
        }

        // validate queryBuilderFieldPrefixesToAssociationClasses
        foreach ($this->queryBuilderFieldPrefixesToAssociationClasses as $fieldPrefix => $associationClass) {
            $suffixPattern = '/\.((?!\.).)+$/';
            $suffixMatches = [];
            $doesFieldPrefixHaveSuffix = preg_match($suffixPattern, $fieldPrefix, $suffixMatches);
            if ($doesFieldPrefixHaveSuffix) { // $fieldPrefix is for an Association in an Association
                $fieldPrefixPrefix = preg_replace($suffixPattern, '', $fieldPrefix);
                $fieldSuffix = str_replace('.', '', $suffixMatches[0]); // remove preceding dot
                if (!array_key_exists($fieldPrefixPrefix, $this->queryBuilderFieldPrefixesToAssociationClasses)) {
                    throw new MissingAssociationClassException(sprintf(
                        'Missing association class for queryBuilderFieldPrefix %s, at class %s, for parser %s',
                        $fieldPrefixPrefix,
                        $this->className,
                        static::class
                    ));
                }
                $classForThisPrefix = $this->queryBuilderFieldPrefixesToAssociationClasses[$fieldPrefixPrefix];
                $this->validateClassHasProperty($classForThisPrefix, $fieldSuffix);
            } else { // $fieldPrefix an association for $this->className
                $this->validateClassHasProperty($this->className, $fieldPrefix);
            }
        }
    }

    /**
     * @param string $className
     *
     * @link http://symfony.com/doc/current/components/property_info.html#components-property-info-extractors
     *
     * @throws InvalidClassNameException
     */
    final private function validateClass(string $className)
    {
        if (!class_exists($className)) {
            throw new InvalidClassNameException(sprintf(
                'Expected valid class name in %s. %s was given, and it is not a valid class name.',
                static::class,
                $className
            ));
        }
    }

    /**
     * @param string $className
     * @param string $classProperty
     *
     * @link http://symfony.com/doc/current/components/property_info.html#components-property-info-extractors
     *
     * @throws FieldMappingException
     */
    final private function validateClassHasProperty(string $className, string $classProperty)
    {
        $propertyInfo = new PropertyInfoExtractor([new ReflectionExtractor()]);
        $properties = $propertyInfo->getProperties($className);

        if (!in_array($classProperty, $properties)) {
            throw new FieldMappingException(sprintf(
                'Property %s is not accessible in %s.',
                $classProperty,
                $className
            ));
        }
    }

    /**
     * @param string $fieldPrefix
     *
     * @throws MissingAssociationClassException
     */
    final private function validateFieldPrefixIsInAssociations(string $fieldPrefix)
    {
        if (!array_key_exists($fieldPrefix, $this->queryBuilderFieldPrefixesToAssociationClasses)) {
            throw new MissingAssociationClassException(sprintf(
                'Missing association class for queryBuilderFieldPrefix %s, at class %s, for parser %s',
                $fieldPrefix,
                $this->className,
                static::class
            ));
        }
    }
}
