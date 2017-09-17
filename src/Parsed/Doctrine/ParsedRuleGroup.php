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
    public function __construct(string $queryString, array $parameters, string $className)
    {
        $this->queryString = trim($queryString);
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
     * For example, $parsedRuleGroup->copyWithReplacedString('ORDER BY', 'GROUP BY object.id ORDER BY', 'GROUP BY object.id').
     *
     * "SELECT object FROM App\Entity\Class object ORDER BY object.id" would change to
     * "SELECT object FROM App\Entity\Class object GROUP BY object.id ORDER BY object.id".
     *
     * "SELECT object FROM App\Entity\Class object" would change to
     * "SELECT object FROM App\Entity\Class object GROUP BY object.id"
     *
     * {@inheritdoc}
     */
    public function copyWithReplacedString(string $search, string $replace, string $appendToEndIfNotFound): AbstractParsedRuleGroup
    {
        $count = 0;
        $newDql = str_replace($search, $replace, $this->getQueryString(), $count);

        if (0 === $count) {
            $newDql = $this->getQueryString().$appendToEndIfNotFound;
        }

        return new self($newDql, $this->parameters, $this->className);
    }

    /**
     * For example $parsedRuleGroup->copyWithReplacedString('/SELECT.+object.+FROM/', 'SELECT object FROM', '').
     *
     * "SELECT object, association FROM App\Entity\Class object LEFT JOIN object.association association ORDER BY object.id" would change to
     * "SELECT object FROM App\Entity\Class object LEFT JOIN object.association association ORDER BY object.id".
     *
     * {@inheritdoc}
     */
    public function copyWithReplacedStringRegex(
        string $pattern,
        string $replace,
        string $appendToEndIfNotFound
    ): AbstractParsedRuleGroup {
        $count = 0;
        $newDql = preg_replace($pattern, $replace, $this->getQueryString(), -1, $count);

        if (0 === $count) {
            $newDql = $this->getQueryString().$appendToEndIfNotFound;
        }

        return new self($newDql, $this->parameters, $this->className);
    }
}
