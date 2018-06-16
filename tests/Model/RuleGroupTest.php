<?php

namespace FL\QBJSParser\Tests\Model;

use FL\QBJSParser\Exception\Model\RuleGroupConstructionException;
use FL\QBJSParser\Model\RuleGroup;
use FL\QBJSParser\Model\RuleGroupInterface;
use PHPUnit\Framework\TestCase;

class RuleGroupTest extends TestCase
{
    /**
     * @var RuleGroupInterface
     */
    private $ruleGroup;

    public function setup()
    {
        // do both! neither of these should render an exception

        $this->ruleGroup = new RuleGroup(RuleGroup::MODE_AND);
    }

    /**
     * @test
     */
    public function testRuleGroupImplementsInterface()
    {
        self::assertInstanceOf(RuleGroupInterface::class, new RuleGroup(RuleGroup::MODE_AND));
    }

    /**
     * @test
     */
    public function testConstructionException()
    {
        $this->expectException(RuleGroupConstructionException::class);

        new RuleGroup(1000);
    }

    /**
     * @test
     */
    public function testConstructionWithDifferentModes()
    {
        $ruleGroup = new RuleGroup(RuleGroup::MODE_OR);
        $this->assertEquals(RuleGroup::MODE_OR, $ruleGroup->getMode());
        $this->assertEquals(false, $ruleGroup->isNot());

        $ruleGroup = new RuleGroup(RuleGroup::MODE_AND);
        $this->assertEquals(RuleGroup::MODE_AND, $ruleGroup->getMode());
        $this->assertEquals(false, $ruleGroup->isNot());
    }

    /**
     * @test
     */
    public function testConstructionWithNot()
    {
        $ruleGroup = new RuleGroup(RuleGroup::MODE_OR, true);
        $this->assertEquals(RuleGroup::MODE_OR, $ruleGroup->getMode());
        $this->assertEquals(true, $ruleGroup->isNot());

        $ruleGroup = new RuleGroup(RuleGroup::MODE_AND, true);
        $this->assertEquals(RuleGroup::MODE_AND, $ruleGroup->getMode());
        $this->assertEquals(true, $ruleGroup->isNot());

        $ruleGroup = new RuleGroup(RuleGroup::MODE_OR, false);
        $this->assertEquals(RuleGroup::MODE_OR, $ruleGroup->getMode());
        $this->assertEquals(false, $ruleGroup->isNot());

        $ruleGroup = new RuleGroup(RuleGroup::MODE_AND, false);
        $this->assertEquals(RuleGroup::MODE_AND, $ruleGroup->getMode());
        $this->assertEquals(false, $ruleGroup->isNot());
    }
}
