<?php

namespace FL\QBJSParser\Model;

interface RuleGroupInterface
{
    /**
     * @see http://stackoverflow.com/questions/5350672/pros-and-cons-of-interface-constants#5354044
     */
    const MODE_AND = 'AND';

    const MODE_OR = 'OR';

    const DEFINED_MODES = [
        self::MODE_AND,
        self::MODE_OR,
    ];

    /**
     * @return \SplObjectStorage|RuleInterface[]
     */
    public function getRules();

    /**
     * @param RuleInterface $rule
     *
     * @return RuleGroupInterface
     */
    public function addRule(RuleInterface $rule): RuleGroupInterface;

    /**
     * @param RuleInterface $rule
     *
     * @return RuleGroupInterface
     */
    public function removeRule(RuleInterface $rule): RuleGroupInterface;

    /**
     * @return \SplObjectStorage|RuleGroupInterface[]
     */
    public function getRuleGroups();

    /**
     * @param RuleGroupInterface $ruleGroup
     *
     * @return RuleGroupInterface
     */
    public function addRuleGroup(RuleGroupInterface $ruleGroup): RuleGroupInterface;

    /**
     * @param RuleGroupInterface $ruleGroup
     *
     * @return RuleGroupInterface
     */
    public function removeRuleGroup(RuleGroupInterface $ruleGroup): RuleGroupInterface;

    /**
     * @return string
     */
    public function getMode(): string;

    /**
     * @return bool
     */
    public function isNot(): bool;
}
