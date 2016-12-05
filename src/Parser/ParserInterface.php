<?php

namespace FL\QBJSParser\Parser;

use FL\QBJSParser\Model\RuleGroupInterface;
use FL\QBJSParser\Parsed\AbstractParsedRuleGroup;

/**
 * Implementations of Parser Interface, must convert a @see RuleGroupInterface
 * into an @see AbstractParsedRuleGroup an ORM/ODM or similar can use.
 * E.g. This AbstractParsedRuleGroup can contain proprietary query language string and parameters;
 * an sql string and parameters; or something else altogether.
 */
interface ParserInterface
{
    /**
     * @param RuleGroupInterface $ruleGroup
     * @param array              $sortColumns (E.g. ['id' => 'ASC']) Not part of QBJS Builder, hence it's a separate parameter
     *
     * @return AbstractParsedRuleGroup
     */
    public function parse(RuleGroupInterface $ruleGroup, array $sortColumns = null);
}
