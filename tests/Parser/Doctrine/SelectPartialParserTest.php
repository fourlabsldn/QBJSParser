<?php

namespace FL\QBJSParser\Tests\Parser\Doctrine;

use FL\QBJSParser\Parser\Doctrine\SelectPartialParser;
use PHPUnit\Framework\TestCase;

class SelectPartialParserTest extends TestCase
{
    /**
     * @test
     */
    public function parseTest()
    {
        $fieldPrefixesToClasses = [
            'labels' => 'Valid_Class_Is_Not_Checked',
            'specification' => 'Valid_Class_Is_Not_Checked',
            'labels.specification' => 'Valid_Class_Is_Not_Checked',
        ];
        $parsed = SelectPartialParser::parse($fieldPrefixesToClasses);
        $expected = 'SELECT object, object_labels, object_specification, object_labels_specification ';

        $this->assertEquals($expected, $parsed);
    }
}
