<?php

namespace FL\QBJSParser\Tests\Util\Doctrine\Mock\DoctrineParser;

use FL\QBJSParser\Parser\Doctrine\DoctrineParser;
use FL\QBJSParser\Tests\Util\Doctrine\Mock\Entity\MockBadEntity2;

class MockBadEntity2DoctrineParser extends DoctrineParser
{
    public function __construct()
    {
        parent::__construct(MockBadEntity2::class, [
            'id' => 'id',
            'price' => 'price',
            'name' => 'name',
            'date' => 'date',
        ], []);
    }
}
