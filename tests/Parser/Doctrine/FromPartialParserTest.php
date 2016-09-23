<?php

namespace FL\QBJSParser\Tests\Parser\Doctrine;

use FL\QBJSParser\Parser\Doctrine\FromPartialParser;

class FromPartialParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function parseTest()
    {
        $className = 'SomeNamespace\SomeClass';
        $parsed = FromPartialParser::parse($className);
        $expected = ' FROM ' . $className . ' object ';

        $this->assertEquals($expected, $parsed);
    }
}
