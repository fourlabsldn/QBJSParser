<?php

namespace FL\QBJSParser\Parsed\Doctrine;

use FL\QBJSParser\Exception\Parsed\Doctrine\ParsedRuleGroupConstructionException;
use FL\QBJSParser\Parsed\AbstractParsedRuleGroup;

class ParsedRuleGroup extends AbstractParsedRuleGroup
{
    /**
     * @var string
     */
    private $dqlString;

    /**
     * @var array
     */
    private $parameters;

    /**
     * @var string
     */
    private $className;

    /**
     * @param string $dqlString
     * @param array  $parameters
     * @param string $className
     */
    public function __construct(
        string $dqlString,
        array $parameters,
        string $className
    ) {
        $this->dqlString = $dqlString;
        $this->parameters = $parameters;
        $this->className = $className;
        $this->validateParametersCountInDql();
        $this->validateClassName();
    }

    /**
     * @throws ParsedRuleGroupConstructionException
     */
    private function validateParametersCountInDql()
    {
        $dqlString = $this->dqlString;

        $dqlParametersCount = preg_match_all('/\?\d+/', $dqlString);
        $boundParametersCount = count($this->parameters);

        if ($dqlParametersCount !== $boundParametersCount) {
            throw new ParsedRuleGroupConstructionException(sprintf(
                '%s has %u parameters. Expected %u.',
                $dqlString,
                $boundParametersCount,
                $dqlParametersCount
            ));
        }
    }

    /**
     * @throws ParsedRuleGroupConstructionException
     */
    private function validateClassName()
    {
        if (!class_exists($this->className)) {
            throw new ParsedRuleGroupConstructionException(sprintf(
                'The class %s does not exist',
                $this->className
            ));
        }
    }

    /**
     * @return string (this is a dql string)
     */
    public function getQueryString(): string
    {
        return $this->dqlString;
    }

    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }
}
