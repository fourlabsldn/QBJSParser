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
     * @var string
     */
    private $mode;

    /**
     * @var string
     */
    private $not;

    /**
     * @param string $mode
     * @param bool $not
     */
    public function __construct(string $mode, bool $not = false)
    {
        $this->rules = new \SplObjectStorage();
        $this->ruleGroups = new \SplObjectStorage();

        if (!in_array($mode, static::DEFINED_MODES)) {
            throw new RuleGroupConstructionException();
        }

        $this->mode = $mode;
        $this->not = $not;
    }

    /**
     * {@inheritdoc}
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * {@inheritdoc}
     */
    public function addRule(RuleInterface $rule): RuleGroupInterface
    {
        $this->rules->attach($rule);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeRule(RuleInterface $rule): RuleGroupInterface
    {
        $this->rules->detach($rule);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRuleGroups()
    {
        return $this->ruleGroups;
    }

    /**
     * {@inheritdoc}
     */
    public function addRuleGroup(RuleGroupInterface $ruleGroup): RuleGroupInterface
    {
        $this->ruleGroups->attach($ruleGroup);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeRuleGroup(RuleGroupInterface $ruleGroup): RuleGroupInterface
    {
        $this->ruleGroups->detach($ruleGroup);

        return $this;
    }

    /**
     * @return string
     */
    public function getMode(): string
    {
        return $this->mode;
    }

    /**
     * @return bool
     */
    public function isNot(): bool
    {
        return $this->not;
    }
}
