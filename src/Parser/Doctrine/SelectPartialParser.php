<?php

namespace FL\QBJSParser\Parser\Doctrine;

abstract class SelectPartialParser
{
    const OBJECT_WORD = 'object';

    final private function __construct()
    {
    }

    /**
     * @param array $queryBuilderFieldPrefixesToAssociationClasses
     *
     * @return string
     */
    final public static function parse(array $queryBuilderFieldPrefixesToAssociationClasses = []): string
    {
        $selectString = 'SELECT '.static::OBJECT_WORD;

        foreach ($queryBuilderFieldPrefixesToAssociationClasses as $queryBuilderPrefix => $associationClass) {
            $selectString .= ', '.self::OBJECT_WORD.'_'.str_replace('.', '_', $queryBuilderPrefix);
        }

        return $selectString.' ';
    }
}
