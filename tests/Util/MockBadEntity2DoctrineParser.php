<?php

namespace FL\QBJSParser\Tests\Util;

use FL\QBJSParser\Parser\Doctrine\DoctrineParser;

class MockBadEntity2DoctrineParser extends DoctrineParser
{
    public function __construct()
    {
        parent::__construct(MockBadEntity2::class,[
            'id' => 'id',
            'price' => 'price',
            'name' => 'name',
            'date' => 'date',
        ]);
    }
}
