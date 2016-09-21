<?php

namespace FL\QBJSParser\Tests\Util;

use FL\QBJSParser\Parser\Doctrine\AbstractDoctrineParser;

class MockDoctrineParser extends AbstractDoctrineParser
{
    /**
     * MockDoctrineParser constructor.
     * @param string $className
     */
    public function __construct(string $className)
    {
        parent::__construct($className);
    }

    /**
     * @return array
     */
    protected function map_QueryBuilderFields_ToEntityProperties() : array
    {
        return [
            'id' => 'id',
            'price' => 'price',
            'name' => 'name',
            'date' => 'date',
        ];
    }
}