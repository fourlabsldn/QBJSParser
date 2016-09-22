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

class DoctrineParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    private $mockEntity_ParseCases = [];

    public function setUp()
    {

        /**
         * First $this->mockEntity_ParseCases Case
         */
        $ruleGroupA = new RuleGroup(RuleGroupInterface::MODE_AND);
        $ruleGroupA_RuleA = new Rule('rule_id', 'price', 'double', 'is_not_null', null);
        $ruleGroupA->addRule($ruleGroupA_RuleA);

        $this->mockEntity_ParseCases[] = [
                'rulegroup' => $ruleGroupA,
                'expectedDqlString'=>'SELECT object FROM ' . MockEntity::class . ' object WHERE ( object.price IS NOT NULL ) ',
                'expectedParameters' => [],
        ];

        /**
         * Second $this->mockEntity_ParseCases Case
         */
        $ruleGroupA = new RuleGroup(RuleGroupInterface::MODE_OR);
        $ruleGroupA_RuleA = new Rule('rule_id', 'price', 'double', 'is_not_null', null);
        $ruleGroupA_RuleB = new Rule('rule_id', 'name', 'string', 'equal', 'hello');
        $ruleGroupA->addRule($ruleGroupA_RuleA);
        $ruleGroupA->addRule($ruleGroupA_RuleB);

        $this->mockEntity_ParseCases[] = [
            'rulegroup' => $ruleGroupA,
            'expectedDqlString'=>'SELECT object FROM ' . MockEntity::class . ' object WHERE ( object.price IS NOT NULL OR object.name = ?0 ) ',
            'expectedParameters' => ['hello'],
        ];

        /**
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
            'expectedDqlString'=>'SELECT object FROM ' . MockEntity::class . ' object WHERE ( object.price IS NOT NULL AND object.name = ?0 AND ( object.price > ?1 OR object.price <= ?2 ) ) ',
            'expectedParameters' => ['hello', 0.3, 22.0],
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

            $this->assertEquals($dqlString, $case['expectedDqlString']);
            $this->assertEquals($parameters, $case['expectedParameters']);
        }
    }

    /**
     * @test
     */
    public function testMockBadEntity()
    {
        $this->assertMapFunctionException(function () {
            new MockBadEntityDoctrineParser();
        });
    }

    /**
     * @test
     */
    public function testMockBadEntity2()
    {
        $this->assertMapFunctionException(function () {
            new MockBadEntity2DoctrineParser();
        });
    }

    /**
     * @test
     */
    public function testNonExistentClass()
    {
        $this->assertInvalidClassNameException(function () {
            new DoctrineParser('This_Really_Long_Class_Name_With_Invalid_Characters_@#_IS_NOT_A_CLASS');
        });
    }

    /**
     * @param \Closure $function
     */
    private function assertMapFunctionException(\Closure $function)
    {
        try {
            $function();
        } catch (FieldMappingException $e) {
            return;
        }
        $this->fail('Expected ' . FieldMappingException::class);
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
        $this->fail('Expected ' . InvalidClassNameException::class);
    }
}
