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
            $selectString .= sprintf(
                ', %s_%s',
                self::OBJECT_WORD,
                StringManipulator::replaceAllDots($fieldPrefix)
            );
        }

        return $selectString.' ';
    }
}
