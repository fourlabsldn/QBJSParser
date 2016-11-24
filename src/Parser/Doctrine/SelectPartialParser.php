<?php

namespace FL\QBJSParser\Parser\Doctrine;

abstract class SelectPartialParser
{
    const OBJECT_WORD = 'object';

    final private function __construct()
    {
    }

    /**
     * @param array $fieldPrefixesToClasses
     *
     * @return string
     */
    final public static function parse(array $fieldPrefixesToClasses = []): string
    {
        $selectString = 'SELECT '.static::OBJECT_WORD;

        foreach ($fieldPrefixesToClasses as $fieldPrefix => $associationClass) {
            $selectString .= ', '.self::OBJECT_WORD.'_'.str_replace('.', '_', $fieldPrefix);
        }

        return $selectString.' ';
    }
}
