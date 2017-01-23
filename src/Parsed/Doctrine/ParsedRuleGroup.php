<?php

namespace FL\QBJSParser\Parsed\Doctrine;

use FL\QBJSParser\Exception\Parsed\Doctrine\ParsedRuleGroupConstructionException;
use FL\QBJSParser\Parsed\AbstractParsedRuleGroup;

class ParsedRuleGroup extends AbstractParsedRuleGroup
{
    /**
     * @var string
     */
    private $queryString;

    /**
     * @var array
     */
    private $parameters;

    /**
     * @var string
     */
    private $className;

    /**
     * @param string $queryString
     * @param array  $parameters
     * @param string $className
     */
    public function __construct(
        string $queryString,
        array $parameters,
        string $className
    ) {
        $this->queryString = $queryString;
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
        $queryString = $this->queryString;

        $dqlParametersCount = preg_match_all('/\?\d+/', $queryString);
        $boundParametersCount = count($this->parameters);

        if ($dqlParametersCount !== $boundParametersCount) {
            throw new ParsedRuleGroupConstructionException(sprintf(
                '%s has %u parameters. Expected %u.',
                $queryString,
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
     * This is a dql string.
     *
     * {@inheritdoc}
     */
    public function getQueryString(): string
    {
        return $this->queryString;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * E.g. $parsedRuleGroup->('ORDER BY', 'GROUP BY object.id ORDER BY', 'GROUP BY object.id')
     *
     *
     * {@inheritdoc}
     */
    public function copyWithReplacedString(
        string $search,
        string $replace,
        string $appendToEndIfNotFound
    ): AbstractParsedRuleGroup {
        $count = 0;
        $newDql = str_replace($search, $replace, $this->getQueryString(), $count);

        if ($count === 0) {
            $newDql = $this->getQueryString() . $appendToEndIfNotFound;
        }
        
        return new ParsedRuleGroup($newDql, $this->parameters, $this->className);
    }
}
