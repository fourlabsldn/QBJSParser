<?php

namespace FL\QBJSParser\Tests\Util;

use FL\QBJSParser\Parser\Doctrine\DoctrineParser;

class MockBadEntityDoctrineParser extends DoctrineParser
{
    public function __construct()
    {
        parent::__construct(MockBadEntity::class,[
            'id' => 'id',
            'price' => 'price',
            'name' => 'name',
            'date' => 'date',
        ], []);
    }
}
