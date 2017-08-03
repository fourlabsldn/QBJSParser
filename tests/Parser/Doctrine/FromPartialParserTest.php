<?php

namespace FL\QBJSParser\Tests\Parser\Doctrine;

use FL\QBJSParser\Parser\Doctrine\FromPartialParser;
use PHPUnit\Framework\TestCase;

class FromPartialParserTest extends TestCase
{
    /**
     * @test
     */
    public function parseTest()
    {
        $className = 'SomeNamespace\SomeClass';
        $parsed = FromPartialParser::parse($className);
        $expected = ' FROM '.$className.' object ';

        $this->assertEquals($expected, $parsed);
    }
}
