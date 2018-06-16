<?php

namespace FL\QBJSParser\Parsed;

abstract class AbstractParsedRuleGroup
{
    /**
     * @return string
     */
    abstract public function getQueryString(): string;

    /**
     * @return array
     */
    abstract public function getParameters(): array;

    /**
     * The class name of the objects this ParsedRuleGroup is querying.
     *
     * @return string
     */
    abstract public function getClassName(): string;

    /**
     * Manipulate the query string with search and replace.
     *
     * @param string $search
     * @param string $replace
     * @param string $appendToEndIfNotFound
     *
     * @return AbstractParsedRuleGroup
     */
    abstract public function copyWithReplacedString(string $search, string $replace, string $appendToEndIfNotFound): self;

    /**
     * Manipulate the query string with regex search and replace.
     *
     * @param string $pattern
     * @param string $replace
     * @param string $appendToEndIfNotFound
     *
     * @return AbstractParsedRuleGroup
     */
    abstract public function copyWithReplacedStringRegex(string $pattern, string $replace, string $appendToEndIfNotFound): self;
}
