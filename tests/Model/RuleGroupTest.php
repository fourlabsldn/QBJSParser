<?php

namespace FL\QBJSParser\Tests\Model;

use FL\QBJSParser\Exception\Model\RuleGroupConstructionException;
use FL\QBJSParser\Model\RuleGroup;
use FL\QBJSParser\Model\RuleGroupInterface;

class RuleGroupTest extends \PHPUnit_Framework_TestCase
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
        $this->assertInstanceOf(RuleGroupInterface::class, $this->ruleGroup);
    }

    /**
     * @test
     */
    public function testConstructionException()
    {
        $this->assertRuleGroupConstructionException(function () {
            $this->ruleGroup = new RuleGroup(1000);
        });
    }


    /**
     * @param \Closure $function
     */
    private function assertRuleGroupConstructionException(\Closure $function)
    {
        try {
            $function();
        } catch (RuleGroupConstructionException $e) {
            return;
        }
        $this->fail('Expected ' . RuleGroupConstructionException::class);
    }
}
