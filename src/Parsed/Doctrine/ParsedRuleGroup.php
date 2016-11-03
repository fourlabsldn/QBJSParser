<?php

namespace FL\QBJSParser\Parsed\Doctrine;

use FL\QBJSParser\Exception\Parsed\Doctrine\ParsedRuleGroupConstructionException;

class ParsedRuleGroup
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
     * @param string $dqlString
     * @param array  $parameters
     */
    public function __construct(string $dqlString, array $parameters)
    {
        $this->dqlString = $dqlString;
        $this->parameters = $parameters;
        $this->validateParametersCountInDql();
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
     * @return string
     */
    public function getDqlString(): string
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
}
