<?php

namespace FL\QBJSParser\Tests\Util\Doctrine\Mock\DoctrineParser;

use FL\QBJSParser\Parser\Doctrine\DoctrineParser;
use FL\QBJSParser\Tests\Util\Doctrine\Mock\Entity\MockEntity;

class MockEntityDoctrineParser extends DoctrineParser
{
    public function __construct()
    {
        parent::__construct(MockEntity::class, [
            'id' => 'id',
            'price' => 'price',
            'name' => 'name',
            'date' => 'date',
        ], []);
    }
}
