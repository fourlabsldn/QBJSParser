<?php

namespace FL\QBJSParser\Tests\Util\Doctrine\Mock\DoctrineParser;

use FL\QBJSParser\Parser\Doctrine\DoctrineParser;
use FL\QBJSParser\Tests\Util\Doctrine\Mock\Embeddable\MockEmbeddableInsideEmbeddable;
use FL\QBJSParser\Tests\Util\Doctrine\Mock\Entity\MockEntity;
use FL\QBJSParser\Tests\Util\Doctrine\Mock\Entity\MockEntityAssociation;
use FL\QBJSParser\Tests\Util\Doctrine\Mock\Embeddable\MockEmbeddable;

class MockEntityWithEmbeddableDoctrineParser extends DoctrineParser
{
    public function __construct()
    {
        parent::__construct(
            MockEntity::class,
            [
                'id' => 'id',
                'price' => 'price',
                'name' => 'name',
                'date' => 'date',
                'associationEntity.id' => 'associationEntity.id',
            ],
            [
                'associationEntity' => MockEntityAssociation::class,
            ],
            [
                'embeddable.startDate' => 'embeddable.startDate',
                'embeddable.endDate' => 'embeddable.endDate',
                'associationEntity.embeddable.startDate' => 'associationEntity.embeddable.startDate',
                'associationEntity.embeddable.endDate' => 'associationEntity.embeddable.endDate',
                'associationEntity.associationEntity.embeddable.startDate' => 'associationEntity.associationEntity.embeddable.startDate',
            ],
            [
                'embeddable.embeddableInsideEmbeddable.code' => 'embeddable.embeddableInsideEmbeddable.code',
                'associationEntity.embeddable.embeddableInsideEmbeddable.code' => 'associationEntity.embeddable.embeddableInsideEmbeddable.code',
            ],
            [
                'associationEntity' => MockEntityAssociation::class,
                'associationEntity.associationEntity' => MockEntityAssociation::class,
            ],
            [
                'embeddable' => MockEmbeddable::class,
                'associationEntity.embeddable' => MockEmbeddable::class,
                'associationEntity.associationEntity.embeddable' => MockEmbeddable::class,
                'embeddable.embeddableInsideEmbeddable' => MockEmbeddableInsideEmbeddable::class,
                'associationEntity.embeddable.embeddableInsideEmbeddable' => MockEmbeddableInsideEmbeddable::class,
            ]
        );
    }
}
