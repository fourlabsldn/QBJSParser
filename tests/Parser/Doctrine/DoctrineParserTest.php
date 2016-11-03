<?php

namespace FL\QBJSParser\Tests\Parser\Doctrine;

use FL\QBJSParser\Exception\Parser\Doctrine\InvalidClassNameException;
use FL\QBJSParser\Exception\Parser\Doctrine\FieldMappingException;
use FL\QBJSParser\Model\Rule;
use FL\QBJSParser\Model\RuleGroup;
use FL\QBJSParser\Model\RuleGroupInterface;
use FL\QBJSParser\Parser\Doctrine\DoctrineParser;
use FL\QBJSParser\Tests\Util\MockBadEntity2DoctrineParser;
use FL\QBJSParser\Tests\Util\MockBadEntityDoctrineParser;
use FL\QBJSParser\Tests\Util\MockEntity;
use FL\QBJSParser\Tests\Util\MockEntityDoctrineParser;
use FL\QBJSParser\Tests\Util\MockEntityWithAssociationDoctrineParser;

class DoctrineParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    private $mockEntity_ParseCases = [];

    /**
     * @var array
     */
    private $mockEntity_WithAssociation_ParseCases = [];

    public function setUp()
    {

        /*
         * First $this->mockEntity_ParseCases Case
         */
        $ruleGroupA = new RuleGroup(RuleGroupInterface::MODE_AND);
        $ruleGroupA_RuleA = new Rule('rule_id', 'price', 'double', 'is_not_null', null);
        $ruleGroupA->addRule($ruleGroupA_RuleA);

        $this->mockEntity_ParseCases[] = [
                'rulegroup' => $ruleGroupA,
                'expectedDqlString' => 'SELECT object FROM '.MockEntity::class.' object WHERE ( object.price IS NOT NULL ) ',
                'expectedParameters' => [],
        ];

        /*
         * Second $this->mockEntity_ParseCases Case
         */
        $ruleGroupA = new RuleGroup(RuleGroupInterface::MODE_OR);
        $ruleGroupA_RuleA = new Rule('rule_id', 'price', 'double', 'is_not_null', null);
        $ruleGroupA_RuleB = new Rule('rule_id', 'name', 'string', 'equal', 'hello');
        $ruleGroupA_RuleC = new Rule('rule_id', 'name', 'string', 'contains', 'world');
        $ruleGroupA_RuleD = new Rule('rule_id', 'name', 'string', 'not_contains', 'world');
        $ruleGroupA_RuleE = new Rule('rule_id', 'name', 'string', 'begins_with', 'world');
        $ruleGroupA_RuleF = new Rule('rule_id', 'name', 'string', 'ends_with', 'world');
        $ruleGroupA_RuleG = new Rule('rule_id', 'name', 'string', 'not_begins_with', 'world');
        $ruleGroupA_RuleH = new Rule('rule_id', 'name', 'string', 'not_ends_with', 'world');
        $ruleGroupA_RuleI = new Rule('rule_id', 'name', 'string', 'is_empty', null);
        $ruleGroupA_RuleJ = new Rule('rule_id', 'name', 'string', 'is_not_empty', null);
        $ruleGroupA->addRule($ruleGroupA_RuleA);
        $ruleGroupA->addRule($ruleGroupA_RuleB);
        $ruleGroupA->addRule($ruleGroupA_RuleC);
        $ruleGroupA->addRule($ruleGroupA_RuleD);
        $ruleGroupA->addRule($ruleGroupA_RuleE);
        $ruleGroupA->addRule($ruleGroupA_RuleF);
        $ruleGroupA->addRule($ruleGroupA_RuleG);
        $ruleGroupA->addRule($ruleGroupA_RuleH);
        $ruleGroupA->addRule($ruleGroupA_RuleI);
        $ruleGroupA->addRule($ruleGroupA_RuleJ);

        $this->mockEntity_ParseCases[] = [
            'rulegroup' => $ruleGroupA,
            'expectedDqlString' => 'SELECT object FROM '.MockEntity::class.' object WHERE ( object.price IS NOT NULL OR object.name = ?0 OR object.name LIKE ?1 OR object.name NOT LIKE ?2 OR object.name LIKE ?3 OR object.name LIKE ?4 OR object.name NOT LIKE ?5 OR object.name NOT LIKE ?6 OR object.name = \'\' OR object.name != \'\' ) ',
            'expectedParameters' => [
                'hello',
                '%world%',
                '%world%',
                'world%',
                '%world',
                'world%',
                '%world',
            ],
        ];

        /*
         * Third $this->mockEntity_ParseCases Case
         */
        $ruleGroupA = new RuleGroup(RuleGroupInterface::MODE_AND);
        $ruleGroupA_RuleA = new Rule('rule_id', 'price', 'double', 'is_not_null', null);
        $ruleGroupA_RuleB = new Rule('rule_id', 'name', 'string', 'equal', 'hello');
        $ruleGroupA
            ->addRule($ruleGroupA_RuleA)
            ->addRule($ruleGroupA_RuleB)
        ;

        $ruleGroupA_RuleGroup1 = new RuleGroup(RuleGroupInterface::MODE_OR);
        $ruleGroupA_RuleGroup1_RuleA = new Rule('rule_id', 'price', 'double', 'greater', 0.3);
        $ruleGroupA_RuleGroup1_RuleB = new Rule('rule_id', 'price', 'double', 'less_or_equal', 22.0);
        $ruleGroupA->addRuleGroup($ruleGroupA_RuleGroup1);
        $ruleGroupA_RuleGroup1
            ->addRule($ruleGroupA_RuleGroup1_RuleA)
            ->addRule($ruleGroupA_RuleGroup1_RuleB)
        ;

        $this->mockEntity_ParseCases[] = [
            'rulegroup' => $ruleGroupA,
            'expectedDqlString' => 'SELECT object FROM '.MockEntity::class.' object WHERE ( object.price IS NOT NULL AND object.name = ?0 AND ( object.price > ?1 OR object.price <= ?2 ) ) ',
            'expectedParameters' => ['hello', 0.3, 22.0],
        ];

        $ruleGroupA = new RuleGroup(RuleGroupInterface::MODE_AND);
        $ruleGroupA_RuleA = new Rule('rule_id', 'price', 'double', 'is_not_null', null);
        $ruleGroupA_RuleB = new Rule('rule_id', 'associationEntities.id', 'string', 'equal', 'hello');
        $ruleGroupA
            ->addRule($ruleGroupA_RuleA)
            ->addRule($ruleGroupA_RuleB)
        ;

        $this->mockEntity_WithAssociation_ParseCases[] = [
            'rulegroup' => $ruleGroupA,
            'expectedDqlString' => 'SELECT object, object_associationEntities FROM '.MockEntity::class.' object LEFT JOIN object.associationEntities object_associationEntities WHERE ( object.price IS NOT NULL AND object_associationEntities.id = ?0 ) ',
            'expectedParameters' => ['hello'],
        ];
    }

    /**
     * @test
     */
    public function testMockEntity_ParseCases()
    {
        $parser = new MockEntityDoctrineParser();

        foreach ($this->mockEntity_ParseCases as $case) {
            $parsed = $parser->parse($case['rulegroup']);

            $dqlString = $parsed->getDqlString();
            $parameters = $parsed->getParameters();

            $this->assertEquals($case['expectedDqlString'], $dqlString);
            $this->assertEquals($case['expectedParameters'], $parameters);
        }
    }

    /**
     * @test
     */
    public function testMockEntity_WithAssociation_ParseCases()
    {
        $parser = new MockEntityWithAssociationDoctrineParser();

        foreach ($this->mockEntity_WithAssociation_ParseCases as $case) {
            $parsed = $parser->parse($case['rulegroup']);

            $dqlString = $parsed->getDqlString();
            $parameters = $parsed->getParameters();

            $this->assertEquals($case['expectedDqlString'], $dqlString);
            $this->assertEquals($case['expectedParameters'], $parameters);
        }
    }

    /**
     * @test
     */
    public function testMockBadEntity()
    {
        $this->assertFieldMappingException(function () {
            new MockBadEntityDoctrineParser();
        });
    }

    /**
     * @test
     */
    public function testMockBadEntity2()
    {
        $this->assertFieldMappingException(function () {
            new MockBadEntity2DoctrineParser();
        });
    }

    /**
     * @test
     */
    public function testNonExistentClass()
    {
        $this->assertInvalidClassNameException(function () {
            new DoctrineParser('This_Really_Long_Class_Name_With_Invalid_Characters_@#_IS_NOT_A_CLASS', [], []);
        });
    }

    /**
     * @param \Closure $function
     */
    private function assertFieldMappingException(\Closure $function)
    {
        try {
            $function();
        } catch (FieldMappingException $e) {
            return;
        }
        $this->fail('Expected '.FieldMappingException::class);
    }

    /**
     * @param \Closure $function
     */
    private function assertInvalidClassNameException(\Closure $function)
    {
        try {
            $function();
        } catch (InvalidClassNameException $e) {
            return;
        }
        $this->fail('Expected '.InvalidClassNameException::class);
    }
}
