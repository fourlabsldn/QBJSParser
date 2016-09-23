<?php

namespace FL\QBJSParser\Parser\Doctrine;

abstract class JoinPartialParser
{
    final private function __construct(){}

    /**
     * @param array $queryBuilderPrefixesToAssociationClasses
     * @return string
     */
    final public static function parse(array $queryBuilderPrefixesToAssociationClasses) : string
    {
        $joinString = '';
        foreach($queryBuilderPrefixesToAssociationClasses as $queryBuilderPrefix => $associationClass){
            $joinPart = ' '. SelectPartialParser::OBJECT_WORD . '_' .$queryBuilderPrefix .' ';
            $joinString .= ' JOIN '  . static::replaceAllDotsExceptLast($joinPart) . ' ' . static::replaceAllDots($joinPart) . ' ';
        }

        return $joinString;
    }

    /**
     * @param string $string
     * @return string
     */
    final private static function replaceAllDotsExceptLast(string $string) : string
    {
        $countDots = substr_count($string, '.');
        $dotsMinusOne = $countDots - 1;
        if($countDots >= 2){
            $string =  str_replace(".","_",$string, $dotsMinusOne);
        }
        return $string;
    }

    /**
     * @param string $string
     * @return string
     */
    final private static function replaceAllDots(string $string) : string
    {
        return str_replace(".","_",$string);
    }
}