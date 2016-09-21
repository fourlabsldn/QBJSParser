<?php

namespace FL\QBJSParser\Model;

use FL\QBJSParser\Exception\Model\RuleGroupConstructionException;

class RuleGroup implements RuleGroupInterface
{
    /**
     * @var \SplObjectStorage
     */
    private $rules;

    /**
     * @var \SplObjectStorage
     */
    private $ruleGroups;

    /**
     * @var int
     */
    private $mode;

    /**
     * @param string $mode
     */
    public function __construct(string $mode)
    {
        $this->rules = new \SplObjectStorage();
        $this->ruleGroups = new \SplObjectStorage();

        if (!in_array($mode, static::DEFINED_MODES)) {
            throw new RuleGroupConstructionException();
        }

        $this->mode = $mode;
    }

    /**
     * @inheritdoc
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * @inheritdoc
     */
    public function addRule(RuleInterface $rule): RuleGroupInterface
    {
        $this->rules->attach($rule);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeRule(RuleInterface $rule): RuleGroupInterface
    {
        $this->rules->detach($rule);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getRuleGroups()
    {
        return $this->ruleGroups;
    }

    /**
     * @inheritdoc
     */
    public function addRuleGroup(RuleGroupInterface $ruleGroup): RuleGroupInterface
    {
        $this->ruleGroups->attach($ruleGroup);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeRuleGroup(RuleGroupInterface $ruleGroup): RuleGroupInterface
    {
        $this->ruleGroups->detach($ruleGroup);

        return $this;
    }

    /**
     * @return int
     */
    public function getMode(): int
    {
        return $this->mode;
    }
}
