<?php

namespace FL\QBJSParser\Parser\Doctrine;

use FL\QBJSParser\Exception\Parser\Doctrine\InvalidClassNameException;
use FL\QBJSParser\Exception\Parser\Doctrine\FieldMappingException;
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
    private $queryBuilderPrefixesToAssociationClasses;

    /**
     * @param string $className
     * @param array $queryBuilderFieldsToProperties
     * E.g. [
     *      'id' => 'id',
     *      'labels.id' => 'labels.id',
     *      'labels.name' => 'labels.name',
     *      'labels.authors.id'=> 'labels.authors.id',
     *      'authors.id' => 'authors.id',
     * ]
     * @param array $queryBuilderPrefixesToAssociationClasses [
     *      'labels' => Label::class,
     *      'labels.authors' => Label::class,
     * ]
     */
    public function __construct(
        string $className,
        array $queryBuilderFieldsToProperties,
        array $queryBuilderPrefixesToAssociationClasses
    ) {
        $this->className = $className;
        $this->queryBuilderFieldsToProperties = $queryBuilderFieldsToProperties;
        $this->queryBuilderPrefixesToAssociationClasses = $queryBuilderPrefixesToAssociationClasses;
        $this->validateBaseProperties();
        $this->validateAssociationClasses();
    }

    /**
     * @inheritdoc
     * @return RuleGroupInterface
     */
    final public function parse(RuleGroupInterface $ruleGroup) : ParsedRuleGroup
    {
        $selectString = SelectPartialParser::parse($this->queryBuilderPrefixesToAssociationClasses);
        $fromString = FromPartialParser::parse($this->className);
        $joinString = JoinPartialParser::parse($this->queryBuilderPrefixesToAssociationClasses);

        $whereParser = new WherePartialParser($this->queryBuilderFieldsToProperties);
        $whereParsedRuleGroup = $whereParser->parse($ruleGroup);
        $whereString = $whereParsedRuleGroup->getDqlString();
        $parameters = $whereParsedRuleGroup->getParameters();

        $dqlString = $selectString . $fromString . $joinString . $whereString;
        return new ParsedRuleGroup(preg_replace('/\s+/', ' ', $dqlString), $parameters); // preg_replace -> no more than one space
    }

    /**
     * @param string $className
     * @link http://symfony.com/doc/current/components/property_info.html#components-property-info-extractors
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
     * @param string[] $classProperties
     * @link http://symfony.com/doc/current/components/property_info.html#components-property-info-extractors
     * @throws FieldMappingException
     */
    final private function validateClassHasProperties(string $className, array $classProperties)
    {
        $propertyInfo = new PropertyInfoExtractor([new ReflectionExtractor()]);
        $properties = $propertyInfo->getProperties($className);

        foreach ($classProperties as $classProperty) {
            if (!in_array($classProperty, $properties)) {
                throw new FieldMappingException(sprintf(
                    'Property %s is not accessible in %s.',
                    $classProperty,
                    $this->className
                ));
            }
        }
    }

    final private function validateBaseProperties()
    {

    }

    final private function validateAssociationClasses()
    {
    }


}
