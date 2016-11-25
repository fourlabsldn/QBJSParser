<?php

namespace FL\QBJSParser\Tests\Util\Doctrine\Test;

use FL\QBJSParser\Model\RuleGroup;

class DoctrineParserTestCase
{
    /**
     * @var RuleGroup
     */
    private $ruleGroup;

    /**
     * @var string
     */
    private $expectedDqlString;

    /**
     * @var array
     */
    private $expectedParameters;

    /**
     * @param RuleGroup $ruleGroup
     * @param string $expectedDqlString
     * @param array $expectedParameters
     */
    public function __construct(RuleGroup $ruleGroup, string $expectedDqlString, array $expectedParameters)
    {
        $this->ruleGroup = $ruleGroup;
        $this->expectedDqlString = $expectedDqlString;
        $this->expectedParameters = $expectedParameters;
    }

    /**
     * @return RuleGroup
     */
    public function getRuleGroup(): RuleGroup
    {
        return $this->ruleGroup;
    }

    /**
     * @return string
     */
    public function getExpectedDqlString(): string
    {
        return $this->expectedDqlString;
    }

    /**
     * @return array
     */
    public function getExpectedParameters(): array
    {
        return $this->expectedParameters;
    }
}