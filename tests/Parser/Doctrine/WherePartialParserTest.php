<?php

namespace FL\QBJSParser\Tests\Parser\Doctrine;

use FL\QBJSParser\Parser\Doctrine\WherePartialParser;
use PHPUnit\Framework\TestCase;

class WherePartialParserTest extends TestCase
{
    public function testClassCantBeInstantiated()
    {
        self::expectException(\Error::class);

        new class extends WherePartialParser {};
    }
}
