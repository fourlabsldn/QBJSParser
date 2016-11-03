<?php

namespace FL\QBJSParser\Tests\Util;

use FL\QBJSParser\Parser\Doctrine\DoctrineParser;

class MockEntityWithAssociationDoctrineParser extends DoctrineParser
{
    public function __construct()
    {
        parent::__construct(MockEntity::class,
            [
            'id' => 'id',
            'price' => 'price',
            'name' => 'name',
            'date' => 'date',
            'associationEntities.id' => 'associationEntities.id',
            ],
            [
                'associationEntities' => MockEntityAssociation::class,
            ]
        );
    }
}
