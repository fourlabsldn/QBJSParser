<?php

namespace FL\QBJSParser\Tests\Util;

use FL\QBJSParser\Parser\Doctrine\DoctrineParser;

class MockEntityDoctrineParser extends DoctrineParser
{
    public function __construct()
    {
        parent::__construct(MockEntity::class,[
            'id' => 'id',
            'price' => 'price',
            'name' => 'name',
            'date' => 'date',
        ]);
    }
}
