<?php

namespace FL\QBJSParser\Parser\Doctrine;

abstract class JoinPartialParser
{
    final private function __construct()
    {
    }

    /**
     * @param array $queryBuilderFieldPrefixesToAssociationClasses
     *
     * @return string
     */
    final public static function parse(array $queryBuilderFieldPrefixesToAssociationClasses, array $prefixJoinType):
    string {
        $joinString = '';
        foreach ($queryBuilderFieldPrefixesToAssociationClasses as $queryBuilderPrefix => $associationClass) {
            $joinPart = sprintf(
                ' %s.%s ',
                SelectPartialParser::OBJECT_WORD,
                $queryBuilderPrefix
            );
            $joinString .= sprintf(
                ' %s JOIN %s %s ',
                strtoupper($prefixJoinType[$queryBuilderPrefix]),
                StringManipulator::replaceAllDotsExceptLast($joinPart),
                StringManipulator::replaceAllDots($joinPart)
            );
        }

        return $joinString;
    }
}
