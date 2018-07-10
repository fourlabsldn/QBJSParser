<?php

namespace FL\QBJSParser\Tests\Parser\Doctrine;

use FL\QBJSParser\Parser\Doctrine\FromPartialParser;
use PHPUnit\Framework\TestCase;

class FromPartialParserTest extends TestCase
{
    public function testClassNameParsed()
    {
        $className = 'SomeNamespace\SomeClass';
        $parsed = FromPartialParser::parse($className);
        $expected = ' FROM '.$className.' object ';

        self::assertEquals($expected, $parsed);
    }

    public function testClassCantBeInstantiated()
    {
        self::expectException(\Error::class);

        new class() extends FromPartialParser {
        };
    }
}
