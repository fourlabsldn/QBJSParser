<?php

namespace FL\QBJSParser\Parser\Doctrine;

use FL\QBJSParser\Model\RuleGroupInterface;
use FL\QBJSParser\Parsed\Doctrine\ParsedRuleGroup;
use FL\QBJSParser\Parser\ParserInterface;

interface DoctrineParserInterface extends ParserInterface
{
    /**
     * {@inheritdoc}
     */
    public function parse(RuleGroupInterface $ruleGroup, array $sortColumns = null): ParsedRuleGroup;
}
