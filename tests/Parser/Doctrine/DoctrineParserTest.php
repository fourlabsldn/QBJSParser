<?php

namespace FL\QBJSParser\Tests\Parser\Doctrine;

use FL\QBJSParser\Exception\Parser\Doctrine\FieldMappingException;
use FL\QBJSParser\Exception\Parser\Doctrine\InvalidClassNameException;
use FL\QBJSParser\Exception\Parser\Doctrine\InvalidFieldException;
use FL\QBJSParser\Model\Rule;
use FL\QBJSParser\Model\RuleGroup;
use FL\QBJSParser\Model\RuleGroupInterface;
use FL\QBJSParser\Parser\Doctrine\DoctrineParser;
use FL\QBJSParser\Tests\Util\Doctrine\Mock\DoctrineParser\MockBadEntity2DoctrineParser;
use FL\QBJSParser\Tests\Util\Doctrine\Mock\DoctrineParser\MockBadEntityDoctrineParser;
use FL\QBJSParser\Tests\Util\Doctrine\Mock\DoctrineParser\MockEntityDoctrineParser;
use FL\QBJSParser\Tests\Util\Doctrine\Mock\DoctrineParser\MockEntityWithAssociationDoctrineParser;
use FL\QBJSParser\Tests\Util\Doctrine\Mock\DoctrineParser\MockEntityWithEmbeddableDoctrineParser;
use FL\QBJSParser\Tests\Util\Doctrine\Mock\Entity\MockEntity;
use PHPUnit\Framework\TestCase;

class DoctrineParserTest extends TestCase
{
    public function testMockBadEntity()
    {
        self::expectException(FieldMappingException::class);

        new MockBadEntityDoctrineParser();
    }

    public function testMockBadEntity2()
    {
        self::expectException(FieldMappingException::class);

        new MockBadEntity2DoctrineParser();
    }

    public function testNonExistentClass()
    {
        self::expectException(InvalidClassNameException::class);

        new DoctrineParser('This_Really_Long_Class_Name_With_Invalid_Characters_@#_IS_NOT_A_CLASS', [], []);
    }

    public function testInvalidFieldExceptionThrown()
    {
        $ruleGroup = (new RuleGroup(RuleGroupInterface::MODE_AND))
            ->addRule(new Rule('rule_id', 'invalid_field', 'double', 'is_not_null', null))
        ;

        self::expectException(InvalidFieldException::class);

        $parser = new MockEntityDoctrineParser();
        $parser->parse($ruleGroup);
    }

    public function testRuleAndOrderBy()
    {
        $ruleGroup = (new RuleGroup(RuleGroupInterface::MODE_AND))
            ->addRule(new Rule('rule_id', 'price', 'double', 'is_not_null', null))
        ;
        $expectedDql = 'SELECT object FROM '.MockEntity::class.' object'
            .' WHERE ( object.price IS NOT NULL )'
            .' ORDER BY object.price ASC'
        ;

        $parser = new MockEntityDoctrineParser();
        $parsed = $parser->parse($ruleGroup, ['price' => 'ASC']);

        self::assertEquals($expectedDql, $parsed->getQueryString());
        self::assertEquals([], $parsed->getParameters());
    }

    public function testNoRuleAndOrderBy()
    {
        $ruleGroup = new RuleGroup(RuleGroupInterface::MODE_AND);
        $expectedDql = 'SELECT object FROM '.MockEntity::class.' object '
            .'ORDER BY object.date ASC';

        $parser = new MockEntityDoctrineParser();
        $parsed = $parser->parse($ruleGroup, ['date' => 'ASC']);

        self::assertEquals($expectedDql, $parsed->getQueryString());
        self::assertEquals([], $parsed->getParameters());
    }

    public function testArrayValues()
    {
        $ruleGroup = (new RuleGroup(RuleGroupInterface::MODE_AND))
            ->addRule(new Rule('rule_id', 'date', 'date', 'in', [new \DateTimeImmutable('2017-09-18')]))
            ->addRule(new Rule('rule_id', 'date', 'date', 'between', [new \DateTimeImmutable('2017-09-17'), new \DateTimeImmutable('2017-09-19')]))
        ;
        $expectedDql = 'SELECT object FROM '.MockEntity::class.' object'
            .' WHERE ( object.date IN (?0) AND object.date BETWEEN ?1 AND ?2 )'
        ;

        $parser = new MockEntityDoctrineParser();
        $parsed = $parser->parse($ruleGroup);

        self::assertEquals($expectedDql, $parsed->getQueryString());
    }

    public function testRuleGroupAndMultipleSortColumns()
    {
        $ruleGroup = (new RuleGroup(RuleGroupInterface::MODE_OR))
            ->addRule(new Rule('rule_id', 'price', 'double', 'is_not_null', null))
            ->addRule(new Rule('rule_id', 'name', 'string', 'equal', 'hello'))
            ->addRule(new Rule('rule_id', 'name', 'string', 'contains', 'world'))
            ->addRule(new Rule('rule_id', 'name', 'string', 'not_contains', 'world'))
            ->addRule(new Rule('rule_id', 'name', 'string', 'begins_with', 'world'))
            ->addRule(new Rule('rule_id', 'name', 'string', 'ends_with', 'world'))
            ->addRule(new Rule('rule_id', 'name', 'string', 'not_begins_with', 'world'))
            ->addRule(new Rule('rule_id', 'name', 'string', 'not_ends_with', 'world'))
            ->addRule(new Rule('rule_id', 'name', 'string', 'is_empty', null))
            ->addRule(new Rule('rule_id', 'name', 'string', 'is_not_empty', null))
        ;
        $sortColumns = ['price' => 'ASC', 'name' => 'DESC'];
        $expectedDql = 'SELECT object FROM '.MockEntity::class.' object'
            .' WHERE ( object.price IS NOT NULL OR object.name = ?0'
            .' OR object.name LIKE ?1 OR object.name NOT LIKE ?2'
            .' OR object.name LIKE ?3 OR object.name LIKE ?4'
            .' OR object.name NOT LIKE ?5 OR object.name NOT LIKE ?6'
            .' OR object.name = \'\' OR object.name != \'\' )'
            .' ORDER BY object.price ASC, object.name DESC'
        ;
        $expectedParameters = [
            'hello',
            '%world%',
            '%world%',
            'world%',
            '%world',
            'world%',
            '%world',
        ];

        $parser = new MockEntityDoctrineParser();

        $parsed = $parser->parse($ruleGroup, $sortColumns);

        self::assertEquals($expectedDql, $parsed->getQueryString());
        self::assertEquals($expectedParameters, $parsed->getParameters());
    }

    public function testNestedRuleGroups()
    {
        $ruleGroup = (new RuleGroup(RuleGroupInterface::MODE_AND))
            ->addRule(new Rule('rule_id', 'price', 'double', 'is_not_null', null))
            ->addRule(new Rule('rule_id', 'name', 'string', 'equal', 'hello'))
            ->addRuleGroup(
                (new RuleGroup(RuleGroupInterface::MODE_OR))
                    ->addRule(new Rule('rule_id', 'price', 'double', 'greater', 0.3))
                    ->addRule(new Rule('rule_id', 'price', 'double', 'less_or_equal', 22.0))
            )
        ;
        $sortColumns = [
            'name' => 'DESC',
            'price' => 'ASC',
        ];
        $expectedDql = 'SELECT object FROM '.MockEntity::class.' object'
            .' WHERE ( object.price IS NOT NULL AND object.name = ?0'
            .' AND ( object.price > ?1 OR object.price <= ?2 ) )'
            .' ORDER BY object.name DESC, object.price ASC'
        ;
        $expectedParameters = ['hello', 0.3, 22.0];

        $parser = new MockEntityDoctrineParser();

        $parsed = $parser->parse($ruleGroup, $sortColumns);

        self::assertEquals($expectedDql, $parsed->getQueryString());
        self::assertEquals($expectedParameters, $parsed->getParameters());
    }

    public function testNestNotRuleGroups()
    {
        $ruleGroup = (new RuleGroup(RuleGroupInterface::MODE_AND, true))
            ->addRule(new Rule('rule_id', 'price', 'double', 'is_not_null', null))
            ->addRule(new Rule('rule_id', 'name', 'string', 'equal', 'hello'))
            ->addRuleGroup(
                (new RuleGroup(RuleGroupInterface::MODE_OR, true))
                    ->addRule(new Rule('rule_id', 'price', 'double', 'greater', 0.3))
                    ->addRule((new Rule('rule_id', 'price', 'double', 'less_or_equal', 22.0)))
            )
        ;

        $sortColumns = [
            'name' => 'DESC',
            'price' => 'ASC',
        ];
        $expectedDql = 'SELECT object FROM '.MockEntity::class.' object'
            .' WHERE ( NOT ( object.price IS NOT NULL AND object.name = ?0'
            .' AND ( NOT ( object.price > ?1 OR object.price <= ?2 ) ) ) )'
            .' ORDER BY object.name DESC, object.price ASC'
        ;
        $expectedParameters = ['hello', 0.3, 22.0];

        $parser = new MockEntityDoctrineParser();

        $parsed = $parser->parse($ruleGroup, $sortColumns);

        self::assertEquals($expectedDql, $parsed->getQueryString());
        self::assertEquals($expectedParameters, $parsed->getParameters());
    }

    public function testAssociationClassParsedToJoin()
    {
        $ruleGroup = (new RuleGroup(RuleGroupInterface::MODE_AND))
            ->addRule(new Rule('rule_id', 'price', 'double', 'is_not_null', null))
            ->addRule(new Rule('rule_id', 'associationEntity.id', 'string', 'equal', 'hello'))
        ;
        $sortColumns = [
            'name' => 'DESC',
            'associationEntity.id' => 'ASC',
        ];
        $expectedDql = 'SELECT object, object_associationEntity FROM '.MockEntity::class.' object'
            .' LEFT JOIN object.associationEntity object_associationEntity'
            .' WHERE ( object.price IS NOT NULL AND object_associationEntity.id = ?0 )'
            .' ORDER BY object.name DESC, object_associationEntity.id ASC'
        ;
        $expectedParameters = ['hello'];

        $parser = new MockEntityWithAssociationDoctrineParser();

        $parsed = $parser->parse($ruleGroup, $sortColumns);

        self::assertEquals($expectedDql, $parsed->getQueryString());
        self::assertEquals($expectedParameters, $parsed->getParameters());
    }

    public function testEmbeddablesNotParsedToJoin()
    {
        $dateA = new \DateTimeImmutable('now - 2 days');
        $dateB = new \DateTimeImmutable('now + 2 days');
        $ruleGroup = (new RuleGroup(RuleGroupInterface::MODE_AND))
            ->addRule(new Rule('rule_id', 'price', 'double', 'is_not_null', null))
            ->addRule(new Rule('rule_id', 'associationEntity.id', 'string', 'equal', 'hello'))
            ->addRule(new Rule('rule_id', 'embeddable.startDate', 'date', 'equal', $dateA))
            ->addRule(new Rule('rule_id', 'associationEntity.embeddable.startDate', 'date', 'equal', $dateA))
            ->addRule(new Rule('rule_id', 'associationEntity.embeddable.endDate', 'date', 'equal', $dateB))
            ->addRule(new Rule('rule_id', 'associationEntity.associationEntity.embeddable.startDate', 'date', 'equal', $dateA))
            ->addRule(new Rule('rule_id', 'embeddable.embeddableInsideEmbeddable.code', 'string', 'equal', 'goodbye'))
            ->addRule(new Rule('rule_id', 'associationEntity.embeddable.embeddableInsideEmbeddable.code', 'string', 'equal', 'cool'))
        ;
        $sortColumns = [
            'name' => 'DESC',
            'associationEntity.id' => 'ASC',
            'associationEntity.embeddable.startDate' => 'ASC',
            'associationEntity.associationEntity.embeddable.startDate' => 'DESC',
            'embeddable.embeddableInsideEmbeddable.code' => 'ASC',
            'associationEntity.embeddable.embeddableInsideEmbeddable.code' => 'DESC',
        ];

        $expectedDql = 'SELECT object, object_associationEntity FROM '.MockEntity::class.' object'
            .' LEFT JOIN object.associationEntity object_associationEntity'
            .' WHERE ( object.price IS NOT NULL AND object_associationEntity.id = ?0'
            .' AND object.embeddable.startDate = ?1 AND object_associationEntity.embeddable.startDate = ?2'
            .' AND object_associationEntity.embeddable.endDate = ?3'
            .' AND object_associationEntity_associationEntity.embeddable.startDate = ?4'
            .' AND object.embeddable.embeddableInsideEmbeddable.code = ?5'
            .' AND object_associationEntity.embeddable.embeddableInsideEmbeddable.code = ?6 )'
            .' ORDER BY object.name DESC, object_associationEntity.id ASC,'
            .' object_associationEntity.embeddable.startDate ASC,'
            .' object_associationEntity_associationEntity.embeddable.startDate DESC,'
            .' object.embeddable.embeddableInsideEmbeddable.code ASC,'
            .' object_associationEntity.embeddable.embeddableInsideEmbeddable.code DESC'
        ;
        $expectedParameters = ['hello', $dateA, $dateA, $dateB, $dateA, 'goodbye', 'cool'];

        $parser = new MockEntityWithEmbeddableDoctrineParser();

        $parsed = $parser->parse($ruleGroup, $sortColumns);

        self::assertEquals($expectedDql, $parsed->getQueryString());
        self::assertEquals($expectedParameters, $parsed->getParameters());
    }
}
