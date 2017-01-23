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
     * Manipulate the string.
     *
     * @param string $search
     * @param string $replace
     * @param string $appendToEndIfNotFound
     *
     * @return AbstractParsedRuleGroup
     */
    abstract public function copyWithReplacedString(string $search, string $replace, string $appendToEndIfNotFound): AbstractParsedRuleGroup;
}
