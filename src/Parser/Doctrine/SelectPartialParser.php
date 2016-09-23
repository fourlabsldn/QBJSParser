<?php

namespace FL\QBJSParser\Parser\Doctrine;

abstract class SelectPartialParser
{
    const OBJECT_WORD = 'object';

    final private function __construct(){}

    /**
     * @param array $queryBuilderPrefixesToAssociationClasses
     * @return string
     */
    final public static function parse(array $queryBuilderPrefixesToAssociationClasses = []): string
    {
        $selectString = 'SELECT ' . static::OBJECT_WORD;

        foreach($queryBuilderPrefixesToAssociationClasses as $queryBuilderPrefix => $associationClass){
            $selectString .= ', ' . SelectPartialParser::OBJECT_WORD .'_' . str_replace('.', '_', $queryBuilderPrefix);
        }

        return $selectString;
    }
}
