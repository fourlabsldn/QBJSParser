<?php

namespace FL\QBJSParser\Tests\Util\Doctrine\Test;

use FL\QBJSParser\Model\RuleGroup;
use FL\QBJSParser\Parser\Doctrine\DoctrineParser;

class DoctrineParserTestCase
{
    /**
     * @var DoctrineParser
     */
    private $doctrineParser;

    /**
     * @var RuleGroup
     */
    private $ruleGroup;

    /**
     * @var array
     */
    private $sortColumns;

    /**
     * @var string
     */
    private $expectedDqlString;

    /**
     * @var array
     */
    private $expectedParameters;

    /**
     * @param DoctrineParser $doctrineParser
     * @param RuleGroup $ruleGroup
     * @param array $sortColumns
     * @param string $expectedDqlString
     * @param array $expectedParameters
     */
    public function __construct(
        DoctrineParser $doctrineParser,
        RuleGroup $ruleGroup,
        array $sortColumns,
        string $expectedDqlString,
        array $expectedParameters
    ) {
        $this->doctrineParser = $doctrineParser;
        $this->ruleGroup = $ruleGroup;
        $this->sortColumns = $sortColumns;
        $this->expectedDqlString = $expectedDqlString;
        $this->expectedParameters = $expectedParameters;
    }

    /**
     * @return DoctrineParser
     */
    public function getDoctrineParser(): DoctrineParser
    {
        return $this->doctrineParser;
    }

    /**
     * @return RuleGroup
     */
    public function getRuleGroup(): RuleGroup
    {
        return $this->ruleGroup;
    }

    /**
     * @return array
     */
    public function getSortColumns(): array
    {
        return $this->sortColumns;
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