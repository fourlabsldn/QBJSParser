<?php

namespace FL\QBJSParser\Tests\Serializer;

use FL\QBJSParser\Exception\Serializer\JsonDeserializerConditionException;
use FL\QBJSParser\Exception\Serializer\JsonDeserializerInvalidJsonException;
use FL\QBJSParser\Exception\Serializer\JsonDeserializerRuleKeyException;
use FL\QBJSParser\Exception\Serializer\JsonDeserializerUnexpectedTypeException;
use FL\QBJSParser\Model\Rule;
use FL\QBJSParser\Model\RuleGroup;
use FL\QBJSParser\Model\RuleGroupInterface;
use FL\QBJSParser\Serializer\JsonDeserializer;
use PHPUnit\Framework\TestCase;

class JsonDeserializerTest extends TestCase
{
    public function testDeserializationSuccessful()
    {
        $inputJsonString = '{
            "condition":"AND",
            "rules":[
                {"id":"price","field":"price","type":"double","input":"text","operator":"less","value":"10.25"},
                {"id":"price","field":"price","type":"double","input":"text","operator":"in","value":["10.25", "3.23", "5.22"]},
                {"id":"price","field":"price","type":"double","input":"text","operator":"between","value":["0.2", "100.3"]},
                {"id":"price","field":"price","type":"double","input":"text","operator":"not_between","value":["2.2", "12.3"]},
                {"id":"name","field":"name","type":"string","input":"text","operator":"in","value":["some_name", "another_name", null]},
                {"id":"name","field":"name","type":"string","input":"text","operator":"not_in","value":["some_name", "another_name", null]},
                {"id":"date","field":"date","type":"datetime","input":"text","operator":"in","value":["2017-08-03 14:12:12"]},
                {"id":"date","field":"date","type":"datetime","input":"text","operator":"not_in","value":["2017-08-03 14:12:12"]},
                {"id":"flag","field":"flag","type":"boolean","input":"checkbox","operator":"equal","value":true},
                {
                    "condition":"OR",
                    "not": false,
                    "rules":[
                        {"id":"category","field":"category","type":"integer","input":"select","operator":"equal","value":"2"},
                        {"id":"category","field":"category","type":"integer","input":"select","operator":"equal","value":"1"},
                        {"id":"category","field":"category","type":"integer","input":"select","operator":"is_not_null","value":null}
                    ]
                }
            ]
        }';

        $expectedOutputRuleGroup = (new RuleGroup(RuleGroupInterface::MODE_AND))
            ->addRule(new Rule('price', 'price', 'double', 'less', 10.25))
            ->addRule(new Rule('price', 'price', 'double', 'in', [10.25, 3.23, 5.22]))
            ->addRule(new Rule('price', 'price', 'double', 'between', [0.2, 100.3]))
            ->addRule(new Rule('price', 'price', 'double', 'not_between', [2.2, 12.3]))
            ->addRule(new Rule('name', 'name', 'string', 'in', ['some_name', 'another_name', null]))
            ->addRule(new Rule('name', 'name', 'string', 'not_in', ['some_name', 'another_name', null]))
            ->addRule(new Rule('date', 'date', 'datetime', 'in', [new \DateTimeImmutable('2017-08-03 14:12:12')]))
            ->addRule(new Rule('date', 'date', 'datetime', 'not_in', [new \DateTimeImmutable('2017-08-03 14:12:12')]))
            ->addRule(new Rule('flag', 'flag', 'boolean', 'equal', true))
            ->addRuleGroup(
                (new RuleGroup(RuleGroupInterface::MODE_OR, false))
                    ->addRule(new Rule('category', 'category', 'integer', 'equal', 2))
                    ->addRule(new Rule('category', 'category', 'integer', 'equal', 1))
                    ->addRule(new Rule('category', 'category', 'integer', 'is_not_null', null))
            )
        ;

        $jsonDeserializer = new JsonDeserializer();
        $deserializedRuleGroup = $jsonDeserializer->deserialize($inputJsonString);

        self::assertRuleGroupsAreEqual($deserializedRuleGroup, $expectedOutputRuleGroup);
    }

    public function testArrayConvertedToSingleValue()
    {
        $inputJsonString = '{
            "condition":"AND",
            "not": true,
            "rules":[
                {"id":"price","field":"price","type":"double","input":"text","operator":"less","value":["10.25"]}
            ]
        }';

        $expectedOutputRuleGroup = (new RuleGroup(RuleGroupInterface::MODE_AND, true))
            ->addRule(new Rule('price', 'price', 'double', 'less', 10.25))
        ;

        $jsonDeserializer = new JsonDeserializer();
        $deserializedRuleGroup = $jsonDeserializer->deserialize($inputJsonString);

        self::assertRuleGroupsAreEqual($deserializedRuleGroup, $expectedOutputRuleGroup);
    }

    public function testSingleValueConvertedToArray()
    {
        $inputJsonString = '{
            "condition":"AND",
            "not": false,
            "rules":[
                {"id":"price","field":"price","type":"double","input":"text","operator":"in","value":"10.25"}
            ]
        }';

        $expectedOutputRuleGroup = (new RuleGroup(RuleGroupInterface::MODE_AND))
            ->addRule(new Rule('price', 'price', 'double', 'in', [10.25]))
        ;

        $jsonDeserializer = new JsonDeserializer();
        $deserializedRuleGroup = $jsonDeserializer->deserialize($inputJsonString);

        self::assertRuleGroupsAreEqual($deserializedRuleGroup, $expectedOutputRuleGroup);
    }

    public function testInvalidJsonThrowsException()
    {
        $inputJsonString = '{
            "condition":"AND",
            "rules":[
                {"id":"price","field":"price","type":"float","input":"text","operator":"less","value":"10.25"},
            ]
        }';

        self::expectException(JsonDeserializerInvalidJsonException::class);

        $jsonDeserializer = new JsonDeserializer();
        $jsonDeserializer->deserialize($inputJsonString);
    }

    public function testUnexpectedTypeThrowsException()
    {
        $inputJsonString = '{
            "condition":"AND",
            "rules":[
                {"id":"price","field":"price","type":"float","input":"text","operator":"less","value":"10.25"}
            ]
        }';

        self::expectException(JsonDeserializerUnexpectedTypeException::class);

        $jsonDeserializer = new JsonDeserializer();
        $jsonDeserializer->deserialize($inputJsonString);
    }

    public function testMissingRuleArrayKeyThrowsException()
    {
        $inputJsonString = '{
            "condition":"AND",
            "rules":[
                {"id":"price","field":"price","type":"float","input":"text","operator":"less"}
            ]
        }';

        self::expectException(JsonDeserializerRuleKeyException::class);

        $jsonDeserializer = new JsonDeserializer();
        $jsonDeserializer->deserialize($inputJsonString);
    }

    public function testMissingRuleGroupArrayKeyThrowsException()
    {
        $inputJsonString = '{
            "rules":[
                {"id":"price","field":"price","type":"float","input":"text","operator":"less","value":"10.25"}
            ]
        }';

        self::expectException(JsonDeserializerConditionException::class);

        $jsonDeserializer = new JsonDeserializer();
        $jsonDeserializer->deserialize($inputJsonString);
    }

    /**
     * @param RuleGroupInterface $ruleGroupA
     * @param RuleGroupInterface $ruleGroupB
     */
    public static function assertRuleGroupsAreEqual(RuleGroupInterface $ruleGroupA, RuleGroupInterface $ruleGroupB)
    {
        self::assertEquals($ruleGroupA->getMode(), $ruleGroupB->getMode(), 'Failed asserting rule group modes match');
        self::assertEquals($ruleGroupA->isNot(), $ruleGroupB->isNot(), 'Failed asserting rule group not match');

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
            self::assertRuleGroupsAreEqual($ruleGroup, $ruleGroups_inRuleGroupB[$key]);
        }
        // do both, in case $ruleGroups_inRuleGroupB has more ruleGroups than $ruleGroups_inRuleGroupA
        foreach ($ruleGroups_inRuleGroupB as $key => $ruleGroup) {
            if (!isset($ruleGroups_inRuleGroupA[$key])) {
                self::fail('Number of RuleGroups not matching');
            }
            self::assertRuleGroupsAreEqual($ruleGroup, $ruleGroups_inRuleGroupA[$key]);
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
        // do both, in case $rules_inRuleGroupB has more rules than $rules_inRuleGroupA
        foreach ($rules_inRuleGroupB as $key => $rule) {
            if (!isset($rules_inRuleGroupA[$key])) {
                self::fail('Number of Rules not matching');
            }
            self::assertEquals($rule, $rules_inRuleGroupA[$key]);
        }
    }
}
