<?php

namespace FL\QBJSParser\Serializer;

use FL\QBJSParser\Model\RuleGroupInterface;

interface DeserializerInterface
{
    /**
     * @param string $string
     *
     * @return RuleGroupInterface
     */
    public function deserialize(string $string) : RuleGroupInterface;
}
