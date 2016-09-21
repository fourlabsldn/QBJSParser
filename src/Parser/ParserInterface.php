<?php

namespace FL\QBJSParser\Parser;

use FL\QBJSParser\Model\RuleGroupInterface;

interface ParserInterface
{
    /**
     * @param RuleGroupInterface $ruleGroup
     * @return mixed
     */
    public function parse(RuleGroupInterface $ruleGroup);
}
