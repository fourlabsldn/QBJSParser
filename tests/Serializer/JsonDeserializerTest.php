<?php

namespace FL\QBJSParser\Tests\Serializer;

use FL\QBJSParser\Model\Rule;
use FL\QBJSParser\Model\RuleGroup;
use FL\QBJSParser\Model\RuleGroupInterface;
use FL\QBJSParser\Serializer\JsonDeserializer;
use FL\QBJSParser\Tests\Util\Doctrine\Mock\DoctrineParser\MockEntityDoctrineParser;
use PHPUnit\Framework\TestCase;

class JsonDeserializerTest extends TestCase
{
    /**
     * @var string
     */
    private $inputJsonString;

    /**
     * @var RuleGroupInterface
     */
    private $expectedOutputRuleGroup;

    public function setUp()
    {
        $this->inputJsonString =
            '{'.
                '"condition":"AND",'.
                '"rules":['.
                    '{"id":"price","field":"price","type":"double","input":"text","operator":"less","value":"10.25"},'.
                    '{"id":"price","field":"price","type":"double","input":"text","operator":"in","value":["10.25", "3.23", "5.22"]},'.
                    '{"id":"price","field":"price","type":"double","input":"text","operator":"between","value":["0.2", "100.3"]},'.
                    '{"id":"price","field":"price","type":"double","input":"text","operator":"not_between","value":["2.2", "12.3"]},'.
                    '{"id":"name","field":"name","type":"string","input":"text","operator":"in","value":["some_name", "another_name", null]},'.
                    '{"id":"name","field":"name","type":"string","input":"text","operator":"not_in","value":["some_name", "another_name", null]},'.
                    '{'.
                        '"condition":"OR",'.
                        '"rules":['.
                            '{"id":"category","field":"category","type":"integer","input":"select","operator":"equal","value":"2"},'.
                            '{"id":"category","field":"category","type":"integer","input":"select","operator":"equal","value":"1"},'.
                            '{"id":"category","field":"category","type":"integer","input":"select","operator":"is_not_null","value":null}'.
                        ']'.
                    '}'.
                ']'.
            '}';

        $ruleGroupA = (new RuleGroup(RuleGroupInterface::MODE_AND))
            ->addRule(
                new Rule('price', 'price', 'double', 'less', 10.25)
            )
            ->addRule(
                new Rule('price', 'price', 'double', 'in', [10.25, 3.23, 5.22])
            )
            ->addRule(
                new Rule('price', 'price', 'double', 'between', [0.2, 100.3])
            )
            ->addRule(
                new Rule('price', 'price', 'double', 'not_between', [2.2, 12.3])
            )
            ->addRule(
                new Rule('name', 'name', 'string', 'in', ['some_name', 'another_name', null])
            )
            ->addRule(
                new Rule('name', 'name', 'string', 'not_in', ['some_name', 'another_name', null])
            )
        ;
        $ruleGroupA->addRuleGroup(
            (new RuleGroup(RuleGroupInterface::MODE_OR))
                ->addRule(
                    new Rule('category', 'category', 'integer', 'equal', 2)
                )
                ->addRule(
                    new Rule('category', 'category', 'integer', 'equal', 1)
                )
                ->addRule(
                    new Rule('category', 'category', 'integer', 'is_not_null', null)
                )
        );

        $this->expectedOutputRuleGroup = $ruleGroupA;
    }

    /**
     * @test
     */
    public function testDeserialization()
    {
        $jsonDeserializer = new JsonDeserializer();
        $deserializedRuleGroup = $jsonDeserializer->deserialize($this->inputJsonString);
        $this->assertRuleGroupsAreEqual($deserializedRuleGroup, $this->expectedOutputRuleGroup);
    }

    /**
     * @test
     */
    public function testParsing()
    {
        $ruleGroupA = new RuleGroup(RuleGroupInterface::MODE_AND);
        $ruleGroupA_RuleA = new Rule('price', 'price', 'double', 'less', 10.25);
        $ruleGroupA_RuleB = new Rule('date', 'date', 'datetime', 'in', [new \DateTimeImmutable('2017-08-03 14:12:12')]);
        $ruleGroupA_RuleC = new Rule('date', 'date', 'datetime', 'in', [new \DateTimeImmutable('2017-08-03 14:12:12')]);
        $ruleGroupA_RuleD = new Rule('date', 'date', 'datetime', 'not_in', [new \DateTimeImmutable('2017-08-03 14:12:12')]);
        $ruleGroupA->addRule($ruleGroupA_RuleA);
        $ruleGroupA->addRule($ruleGroupA_RuleB);
        $ruleGroupA->addRule($ruleGroupA_RuleC);
        $ruleGroupA->addRule($ruleGroupA_RuleD);

        $jsonString =
            '{'.
                '"condition":"AND",'.
                '"rules":['.
                    '{"id":"price","field":"price","type":"double","input":"text","operator":"less","value":"10.25"},'.
                    '{"id":"date","field":"date","type":"datetime","input":"text","operator":"in","value":["2017-08-03 14:12:12"]},'.
                    // operators in and not_in require an array, the next two lines test that single values are converted to an array
                    '{"id":"date","field":"date","type":"datetime","input":"text","operator":"in","value":"2017-08-03 14:12:12"},'.
                    '{"id":"date","field":"date","type":"datetime","input":"text","operator":"not_in","value":"2017-08-03 14:12:12"}'.
                ']'.
            '}';

        $jsonDeserializer = new JsonDeserializer();
        $deserializedRuleGroup = $jsonDeserializer->deserialize($jsonString);

        $mockDoctrineParser = new MockEntityDoctrineParser();
        $mockDoctrineParser->parse($deserializedRuleGroup);
        $this->assertRuleGroupsAreEqual($deserializedRuleGroup, $ruleGroupA);
    }

    /**
     * @param RuleGroupInterface $ruleGroupA
     * @param RuleGroupInterface $ruleGroupB
     */
    public function assertRuleGroupsAreEqual(RuleGroupInterface $ruleGroupA, RuleGroupInterface $ruleGroupB)
    {
        /*
         * Verify descendants are equal, recursively
         */
        $ruleGroups_inRuleGroupA = [];
        $ruleGroups_inRuleGroupB = [];
        foreach ($ruleGroupA->getRuleGroups() as $ruleGroup) {
            $ruleGroups_inRuleGroupA[] = $ruleGroup;
        }
        foreach ($ruleGroupB->getRuleGroups() as $ruleGroup) {
            $ruleGroups_inRuleGroupB[] = $ruleGroup;
        }

        foreach ($ruleGroups_inRuleGroupA as $key => $ruleGroup) {
            /* @var RuleGroupInterface $ruleGroup */
            if (!isset($ruleGroups_inRuleGroupB[$key])) {
                self::fail('Number of RuleGroups not matching');
            }
            $this->assertRuleGroupsAreEqual($ruleGroup, $ruleGroups_inRuleGroupB[$key]);
        }
        foreach ($ruleGroups_inRuleGroupB as $key => $ruleGroup) { // do both, in case $ruleGroups_inRuleGroupB has more ruleGroups than $ruleGroups_inRuleGroupA
            if (!isset($ruleGroups_inRuleGroupA[$key])) {
                self::fail('Number of RuleGroups not matching');
            }
            $this->assertRuleGroupsAreEqual($ruleGroup, $ruleGroups_inRuleGroupA[$key]);
        }

        /*
         * Verify rules are equal
         */
        $rules_inRuleGroupA = [];
        $rules_inRuleGroupB = [];
        foreach ($ruleGroupA->getRules() as $rule) {
            $rules_inRuleGroupA[] = $rule;
        }
        foreach ($ruleGroupB->getRules() as $rule) {
            $rules_inRuleGroupB[] = $rule;
        }

        foreach ($rules_inRuleGroupA as $key => $rule) {
            if (!isset($rules_inRuleGroupB[$key])) {
                self::fail('Number of Rules not matching');
            }
            self::assertEquals($rule, $rules_inRuleGroupB[$key]);
        }
        foreach ($rules_inRuleGroupB as $key => $rule) { // do both, in case $rules_inRuleGroupB has more rules than $rules_inRuleGroupA
            if (!isset($rules_inRuleGroupA[$key])) {
                self::fail('Number of Rules not matching');
            }
            self::assertEquals($rule, $rules_inRuleGroupA[$key]);
        }
    }
}
