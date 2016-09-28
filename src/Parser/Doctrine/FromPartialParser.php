<?php

namespace FL\QBJSParser\Parser\Doctrine;

abstract class FromPartialParser
{
    final private function __construct()
    {
    }

    /**
     * @param string $className
     * @return string
     */
    final public static function parse(string $className) : string
    {
        $fromString = ' FROM ' . $className . ' ' . SelectPartialParser::OBJECT_WORD . ' ' ;

        return $fromString;
    }
}
