<?php

namespace FL\QBJSParser\Model;

interface RuleInterface
{
    /**
     * @return string
     */
    public function getId(): string;

    /**
     * @return string
     */
    public function getField(): string;

    /**
     * @return string
     */
    public function getType(): string;

    /**
     * @return string
     */
    public function getOperator(): string;

    /**
     * @return mixed
     */
    public function getValue();

    /**
     * @param $value
     * @return string
     */
    public function valueType($value): string;
}
