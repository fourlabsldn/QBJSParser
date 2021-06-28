<?php

namespace FL\QBJSParser\Parser\Doctrine;

use FL\QBJSParser\Exception\Parser\Doctrine\DuplicatePrefixException;
use FL\QBJSParser\Exception\Parser\Doctrine\InvalidClassNameException;
use FL\QBJSParser\Exception\Parser\Doctrine\FieldMappingException;
use FL\QBJSParser\Exception\Parser\Doctrine\MissingAssociationClassException;
use FL\QBJSParser\Model\RuleGroupInterface;
use FL\QBJSParser\Parsed\Doctrine\ParsedRuleGroup;
use FL\QBJSParser\Tests\Util\Doctrine\Mock\DoctrineParser\MockEntityWithEmbeddableDoctrineParser;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;

class DoctrineParser implements DoctrineParserInterface
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
    private $embeddableInsideEmbeddableFieldsToProperties;

    /**
     * @var array
     */
    private $embeddableFieldPrefixesToClasses;

    /**
     * @var array
     */
    private $embeddableFieldPrefixesToEmbeddableClasses;

    /**
     * @var array
     */
    private $fieldPrefixesJoinType;


    /**
     * @param string $className
     * @param array $fieldsToProperties
     * @param array $fieldPrefixesToClasses
     * @param array $embeddableFieldsToProperties
     * @param array $embeddableInsideEmbeddableFieldsToProperties
     * @param array $embeddableFieldPrefixesToClasses
     * @param array $embeddableFieldPrefixesToEmbeddableClasses
     * @param array $fieldPrefixesJoinType
     *
     * @see MockEntityWithEmbeddableDoctrineParser for full example
     */
    public function __construct(
        string $className,
        array $fieldsToProperties,
        array $fieldPrefixesToClasses = [],
        array $fieldPrefixesJoinType = [],
        array $embeddableFieldsToProperties = [],
        array $embeddableInsideEmbeddableFieldsToProperties = [],
        array $embeddableFieldPrefixesToClasses = [],
        array $embeddableFieldPrefixesToEmbeddableClasses = []

    ) {
        $this->className = $className;
        $this->fieldsToProperties = $fieldsToProperties;
        $this->fieldPrefixesToClasses = $fieldPrefixesToClasses;
        $this->fieldPrefixesJoinType = $fieldPrefixesJoinType;
        $this->embeddableFieldsToProperties = $embeddableFieldsToProperties;
        $this->embeddableInsideEmbeddableFieldsToProperties = $embeddableInsideEmbeddableFieldsToProperties;
        $this->embeddableFieldPrefixesToClasses = $embeddableFieldPrefixesToClasses;
        $this->embeddableFieldPrefixesToEmbeddableClasses = $embeddableFieldPrefixesToEmbeddableClasses;
        $this->validate();
    }

    /**
     * {@inheritdoc}
     *
     * @return ParsedRuleGroup
     */
    final public function parse(RuleGroupInterface $ruleGroup, array $sortColumns = null): ParsedRuleGroup
    {
        $selectString = SelectPartialParser::parse($this->fieldPrefixesToClasses);
        $fromString = FromPartialParser::parse($this->className);
        $joinString = JoinPartialParser::parse($this->fieldPrefixesToClasses, $this->fieldPrefixesJoinType);

        $whereParsedRuleGroup = WherePartialParser::parse(
            $this->fieldsToProperties,
            $ruleGroup,
            $this->embeddableFieldsToProperties,
            $this->embeddableInsideEmbeddableFieldsToProperties,
            $this->className
        );
        $whereString = $whereParsedRuleGroup->getQueryString();
        $parameters = $whereParsedRuleGroup->getParameters();

        $orderString = OrderPartialParser::parse(
            $this->fieldsToProperties,
            $sortColumns,
            $this->embeddableFieldsToProperties,
            $this->embeddableInsideEmbeddableFieldsToProperties
        );

        $dqlString = preg_replace(
            '/\s+/',
            ' ',
            $selectString . $fromString . $joinString . $whereString . $orderString
        );

        return new ParsedRuleGroup($dqlString, $parameters, $this->className); // preg_replace -> no more than one space
    }

    /**
     * @throws InvalidClassNameException|FieldMappingException|MissingAssociationClassException
     */
    final private function validate()
    {
        $this->validateClass($this->className);
        $this->validateFieldsToProperties($this->fieldsToProperties, $this->fieldPrefixesToClasses);
        $this->validateFieldPrefixesToClasses($this->fieldPrefixesToClasses);
        $allEmbeddableFields = array_merge(
            $this->embeddableFieldsToProperties,
            $this->embeddableInsideEmbeddableFieldsToProperties
        );
        $allEmbeddablePrefixesToClasses = array_merge(
            $this->embeddableFieldPrefixesToClasses,
            $this->embeddableFieldPrefixesToEmbeddableClasses
        );
        $this->validateFieldsToProperties($allEmbeddableFields, $allEmbeddablePrefixesToClasses);
        $this->validateFieldPrefixesToClasses($allEmbeddablePrefixesToClasses);
        $this->validateEmbeddableFieldPrefixes(
            $this->embeddableFieldPrefixesToClasses,
            $this->embeddableFieldPrefixesToEmbeddableClasses
        );
        $this->validateJoinTypes($this->fieldPrefixesToClasses, $this->fieldPrefixesJoinType);
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
                    throw new MissingAssociationClassException(
                        sprintf(
                            'Missing association class for queryBuilderFieldPrefix %s, at class %s, for parser %s',
                            $fieldPrefixPrefix,
                            $this->className,
                            static::class
                        )
                    );
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
    final private function validateEmbeddableFieldPrefixes(
        array $embeddableFieldPrefixesToClasses,
        array $embeddableFieldPrefixesToEmbeddableClasses
    ) {
        $prefixes = array_keys($embeddableFieldPrefixesToClasses);

        foreach ($prefixes as $prefix) {
            if (array_key_exists($prefix, $embeddableFieldPrefixesToEmbeddableClasses)) {
                throw new DuplicatePrefixException(
                    sprintf(
                        'Duplicate embeddable field prefix %s, at class %s, for parser %s',
                        $prefix,
                        $this->className,
                        static::class
                    )
                );
            }
        }
    }

    /**
     * @param string $className
     *
     * @throws InvalidClassNameException
     * @see http://symfony.com/doc/current/components/property_info.html#components-property-info-extractors
     *
     */
    final private function validateClass(string $className)
    {
        if (!class_exists($className)) {
            throw new InvalidClassNameException(
                sprintf(
                    'Expected valid class name in %s. %s was given, and it is not a valid class name.',
                    static::class,
                    $className
                )
            );
        }
    }

    /**
     * @param string $className
     * @param string $classProperty
     *
     * @throws FieldMappingException
     * @see http://symfony.com/doc/current/components/property_info.html#components-property-info-extractors
     *
     */
    final private function validateClassHasProperty(string $className, string $classProperty)
    {
        $propertyInfo = new PropertyInfoExtractor([new ReflectionExtractor()]);
        $properties = $propertyInfo->getProperties($className);

        if (!in_array($classProperty, $properties)) {
            throw new FieldMappingException(
                sprintf(
                    'Property %s is not accessible in %s.',
                    $classProperty,
                    $className
                )
            );
        }
    }

    /**
     * @param string $fieldPrefix
     * @param array $fieldPrefixesToClasses
     *
     * @throws MissingAssociationClassException
     */
    final private function validateFieldPrefixIsInAssociations(string $fieldPrefix, array $fieldPrefixesToClasses)
    {
        if (!array_key_exists($fieldPrefix, $fieldPrefixesToClasses)) {
            throw new MissingAssociationClassException(
                sprintf(
                    'Missing class for fieldPrefix %s, at class %s, for parser %s',
                    $fieldPrefix,
                    $this->className,
                    static::class
                )
            );
        }
    }

    final private function validateJoinTypes(array $fieldPrefixesToClasses, array $fieldPrefixesJoinType)
    {
        foreach ($fieldPrefixesToClasses as $prefix => $class) {
            if (!array_key_exists($prefix, $fieldPrefixesJoinType)) {
                throw new MissingAssociationClassException(
                    sprintf(
                        'Missing Join Type for fieldPrefix %s, at class %s, for parser %s',
                        $prefix,
                        $this->className,
                        static::class
                    )
                );
            }
        }
    }
}
