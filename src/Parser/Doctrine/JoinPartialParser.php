<?php

namespace FL\QBJSParser\Parser\Doctrine;

abstract class JoinPartialParser
{
    final private function __construct()
    {
    }

    /**
     * @param array $queryBuilderFieldPrefixesToAssociationClasses
     * @return string
     */
    final public static function parse(array $queryBuilderFieldPrefixesToAssociationClasses) : string
    {
        $joinString = '';
        foreach ($queryBuilderFieldPrefixesToAssociationClasses as $queryBuilderPrefix => $associationClass) {
            $joinPart = ' '. SelectPartialParser::OBJECT_WORD . '.' .$queryBuilderPrefix .' ';
            $joinString .= ' LEFT JOIN '  . static::replaceAllDotsExceptLast($joinPart) . ' ' . static::replaceAllDots($joinPart) . ' ';
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
        if ($countDots >= 2) {
            $stringArray  = explode('.', $string);
            $string = '';
            for ($i = 0; $i < $countDots - 1; $i++) {
                $string .= $stringArray[$i] . '_';
            }
            $string .= $stringArray[$countDots - 1] . '.' . $stringArray[$countDots];
        }

        return $string;
    }

    /**
     * @param string $string
     * @return string
     */
    final private static function replaceAllDots(string $string) : string
    {
        return str_replace(".", "_", $string);
    }
}
