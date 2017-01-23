<?php

namespace FL\QBJSParser\Parsed;

abstract class AbstractParsedRuleGroup
{
    /**
     * @return string (this is a dql string)
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
}
