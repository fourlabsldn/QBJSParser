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
    public function addRule(RuleInterface $rule): self;

    /**
     * @param RuleInterface $rule
     *
     * @return RuleGroupInterface
     */
    public function removeRule(RuleInterface $rule): self;

    /**
     * @return \SplObjectStorage|RuleGroupInterface[]
     */
    public function getRuleGroups();

    /**
     * @param RuleGroupInterface $ruleGroup
     *
     * @return RuleGroupInterface
     */
    public function addRuleGroup(self $ruleGroup): self;

    /**
     * @param RuleGroupInterface $ruleGroup
     *
     * @return RuleGroupInterface
     */
    public function removeRuleGroup(self $ruleGroup): self;

    /**
     * @return string
     */
    public function getMode(): string;

    /**
     * @return bool
     */
    public function isNot(): bool;
}
