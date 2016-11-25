<?php

namespace FL\QBJSParser\Parser\Doctrine;

use FL\QBJSParser\Exception\Parser\Doctrine\DuplicatePrefixException;
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
    private $fieldsToProperties;

    /**
     * @var array
     */
    private $fieldPrefixesToClasses;

    /**
     * @var array
     */
    private $embeddableFieldsToProperties;

    /**
     * @var array
     */
    private $embeddableFieldPrefixesToClasses;

    /**
     * @var array
     */
    private $embeddableFieldPrefixesToEmbeddableClasses;

    /**
     * @param string $className                                  E.g. Product::class
     * @param array  $fieldsToProperties                         E.g. [
     *                                                           'id' => 'id',
     *                                                           'labels.id' => 'labels.id',
     *                                                           'labels.name' => 'labels.name',
     *                                                           'labels.authors.id'=> 'labels.authors.id',
     *                                                           'labels.authors.address.city'=> 'labels.authors.address.city',
     *                                                           'labels.authors.nextHoliday.approved' => 'labels.authors.nextHoliday.approved'
     *                                                           'author.id' => 'author.id',
     *                                                           ]
     * @param array  $fieldPrefixesToClasses                     E.g. [
     *                                                           'labels' => Label::class,
     *                                                           'labels.authors' => Author::class,
     *                                                           'labels.authors.address' => Address::class,
     *                                                           'labels.authors.nextHoliday' => NextHoliday::class,
     *                                                           'author' => Author::class,
     *                                                           ]
     * @param array  $embeddableFieldsToProperties               E.g. [
     *                                                           'period.startDate' => 'period.endDate',
     *                                                           'period.endDate' => 'period.startDate',
     *                                                           'labels.period.startDate' => 'labels.period.startDate',
     *                                                           'labels.period.endDate'=> 'labels.period.endDate',
     *                                                           'labels.authors.nextHoliday.period.startDate' => 'labels.authors.nextHoliday.period.startDate',
     *                                                           'labels.authors.nextHoliday.period.endDate' => 'labels.authors.nextHoliday.period.endDate',
     *                                                           ]
     * @param array  $embeddableFieldPrefixesToClasses           E.g. [
     *                                                           'labels' => NextHoliday::class,
     *                                                           'labels.authors' => NextHoliday::class,
     *                                                           'labels.authors.nextHoliday' => NextHoliday::class,
     *                                                           ]
     * @param array  $embeddableFieldPrefixesToEmbeddableClasses E.g. [
     *                                                           'period' => \League\Period\Period::class,
     *                                                           'labels.period' => \League\Period\Period::class,
     *                                                           'labels.authors.nextHoliday.period' => \League\Period\Period::class,
     *                                                           ]
     */
    public function __construct(
        string $className,
        array $fieldsToProperties,
        array $fieldPrefixesToClasses = [],
        array $embeddableFieldsToProperties = [],
        array $embeddableFieldPrefixesToClasses = [],
        array $embeddableFieldPrefixesToEmbeddableClasses = []
    ) {
        $this->className = $className;
        $this->fieldsToProperties = $fieldsToProperties;
        $this->fieldPrefixesToClasses = $fieldPrefixesToClasses;
        $this->embeddableFieldsToProperties = $embeddableFieldsToProperties;
        $this->embeddableFieldPrefixesToClasses = $embeddableFieldPrefixesToClasses;
        $this->embeddableFieldPrefixesToEmbeddableClasses = $embeddableFieldPrefixesToEmbeddableClasses;
        $this->validate();
    }

    /**
     * {@inheritdoc}
     *
     * @return ParsedRuleGroup
     */
    final public function parse(RuleGroupInterface $ruleGroup, array $sortColumns = null) : ParsedRuleGroup
    {
        $selectString = SelectPartialParser::parse($this->fieldPrefixesToClasses);
        $fromString = FromPartialParser::parse($this->className);
        $joinString = JoinPartialParser::parse($this->fieldPrefixesToClasses);

        $whereParsedRuleGroup = WherePartialParser::parse(
            $this->fieldsToProperties,
            $ruleGroup,
            $this->embeddableFieldsToProperties,
            $this->embeddableFieldPrefixesToClasses,
            $this->embeddableFieldPrefixesToEmbeddableClasses
        );
        $whereString = $whereParsedRuleGroup->getDqlString();
        $parameters = $whereParsedRuleGroup->getParameters();

        $orderString = OrderPartialParser::parse($this->fieldsToProperties, $sortColumns);

        $dqlString = preg_replace('/\s+/', ' ', $selectString.$fromString.$joinString.$whereString.$orderString);

        return new ParsedRuleGroup($dqlString, $parameters); // preg_replace -> no more than one space
    }

    /**
     * @throws InvalidClassNameException|FieldMappingException|MissingAssociationClassException
     */
    final private function validate()
    {
        $this->validateClass($this->className);
        $this->validateFieldsToProperties($this->fieldsToProperties, $this->fieldPrefixesToClasses);
        $this->validateFieldPrefixesToClasses($this->fieldPrefixesToClasses);
        $allEmbeddablePrefixesToClasses = array_merge($this->embeddableFieldPrefixesToClasses, $this->embeddableFieldPrefixesToEmbeddableClasses);
        $this->validateFieldsToProperties($this->embeddableFieldsToProperties, $allEmbeddablePrefixesToClasses);
        $this->validateFieldPrefixesToClasses($allEmbeddablePrefixesToClasses);
        $this->validateEmbeddableFieldPrefixes($this->embeddableFieldPrefixesToClasses, $this->embeddableFieldPrefixesToEmbeddableClasses);
    }

    /**
     * @param array $fieldsToProperties
     * @param array $fieldPrefixesToClasses
     *
     * @throws InvalidClassNameException|FieldMappingException|MissingAssociationClassException
     */
    final private function validateFieldsToProperties(array $fieldsToProperties, array $fieldPrefixesToClasses)
    {
        // check $fieldsToProperties
        foreach ($fieldsToProperties as $queryBuilderField => $property) {
            $suffixPattern = '/\.((?!\.).)+$/';
            $suffixMatches = [];
            $doesFieldHaveSuffix = preg_match($suffixPattern, $queryBuilderField, $suffixMatches);
            if ($doesFieldHaveSuffix) { // $queryBuilderField is for an Association
                $fieldPrefix = preg_replace($suffixPattern, '', $queryBuilderField);
                $this->validateFieldPrefixIsInAssociations($fieldPrefix, $fieldPrefixesToClasses);
                $fieldSuffix = str_replace('.', '', $suffixMatches[0]); // remove preceding dot
                $classForThisPrefix = $fieldPrefixesToClasses[$fieldPrefix];
                $this->validateClass($classForThisPrefix);
                $this->validateClassHasProperty($classForThisPrefix, $fieldSuffix);
            } else { // $queryBuilderField is for $this->className
                $this->validateClassHasProperty($this->className, $property);
            }
        }
    }

    /**
     * @param array $fieldPrefixesToClasses
     *
     * @throws InvalidClassNameException|FieldMappingException|MissingAssociationClassException
     */
    final private function validateFieldPrefixesToClasses(array $fieldPrefixesToClasses)
    {
        // validate fieldPrefixesToClasses
        foreach ($fieldPrefixesToClasses as $fieldPrefix => $associationClass) {
            $suffixPattern = '/\.((?!\.).)+$/';
            $suffixMatches = [];
            $doesFieldPrefixHaveSuffix = preg_match($suffixPattern, $fieldPrefix, $suffixMatches);
            if ($doesFieldPrefixHaveSuffix) { // $fieldPrefix is for an Association in an Association
                $fieldPrefixPrefix = preg_replace($suffixPattern, '', $fieldPrefix);
                $fieldSuffix = str_replace('.', '', $suffixMatches[0]); // remove preceding dot
                if (!array_key_exists($fieldPrefixPrefix, $fieldPrefixesToClasses)) {
                    throw new MissingAssociationClassException(sprintf(
                        'Missing association class for queryBuilderFieldPrefix %s, at class %s, for parser %s',
                        $fieldPrefixPrefix,
                        $this->className,
                        static::class
                    ));
                }
                $classForThisPrefix = $fieldPrefixesToClasses[$fieldPrefixPrefix];
                $this->validateClassHasProperty($classForThisPrefix, $fieldSuffix);
            } else { // $fieldPrefix an association for $this->className
                $this->validateClassHasProperty($this->className, $fieldPrefix);
            }
        }
    }

    /**
     * There should be no repeated prefixes (keys) between the two arrays.
     *
     * @param array $embeddableFieldPrefixesToClasses
     * @param array $embeddableFieldPrefixesToEmbeddableClasses
     */
    final private function validateEmbeddableFieldPrefixes(array $embeddableFieldPrefixesToClasses, array $embeddableFieldPrefixesToEmbeddableClasses)
    {
        $prefixes = array_keys($embeddableFieldPrefixesToClasses);

        foreach ($prefixes as $prefix) {
            if (array_key_exists($prefix, $embeddableFieldPrefixesToEmbeddableClasses)) {
                throw new DuplicatePrefixException(sprintf(
                    'Duplicate embeddable field prefix %s, at class %s, for parser %s',
                    $prefix,
                    $this->className,
                    static::class
                ));
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
     * @param array  $fieldPrefixesToClasses
     *
     * @throws MissingAssociationClassException
     */
    final private function validateFieldPrefixIsInAssociations(string $fieldPrefix, array $fieldPrefixesToClasses)
    {
        if (!array_key_exists($fieldPrefix, $fieldPrefixesToClasses)) {
            throw new MissingAssociationClassException(sprintf(
                'Missing class for fieldPrefix %s, at class %s, for parser %s',
                $fieldPrefix,
                $this->className,
                static::class
            ));
        }
    }
}
