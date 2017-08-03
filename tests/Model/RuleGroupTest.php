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
        $this->ruleGroup = new RuleGroup(RuleGroup::MODE_OR);
        $this->ruleGroup = new RuleGroup(RuleGroup::MODE_AND);
    }

    /**
     * @test
     */
    public function testRuleGroupImplementsInterface()
    {
        self::assertInstanceOf(RuleGroupInterface::class, $this->ruleGroup);
    }

    /**
     * @test
     */
    public function testConstructionException()
    {
        $this->expectException(RuleGroupConstructionException::class);

        new RuleGroup(1000);
    }
}
