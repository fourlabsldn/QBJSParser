<?php

namespace FL\QBJSParser\Parser;

use FL\QBJSParser\Model\RuleGroupInterface;

/**
 * Implementations of Parser Interface, must convert a @see RuleGroupInterface
 * into something an ORM/ODM or similar can use.
 * E.g. This something could be a proprietary query language string and parameters;
 * an sql string and parameters; or something else altogether. Ideally, you will return
 * an instance of a class in the FL\QBJSParser\Parsed namespace.
 */
interface ParserInterface
{
    /**
     * @param RuleGroupInterface $ruleGroup
     * @param array $sortColumns (E.g. ['id' => 'ASC']) Not part of QBJS Builder, hence it's an o
     *
     * @return mixed
     */
    public function parse(RuleGroupInterface $ruleGroup, array $sortColumns = null);
}
