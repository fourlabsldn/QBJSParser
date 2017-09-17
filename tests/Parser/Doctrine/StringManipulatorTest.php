<?php

namespace FL\QBJSParser\Tests\Parser\Doctrine;

use FL\QBJSParser\Parser\Doctrine\StringManipulator;
use PHPUnit\Framework\TestCase;

class StringManipulatorTest extends TestCase
{
    public function testClassCantBeInstantiated()
    {
        self::expectException(\Error::class);

        new class extends StringManipulator {};
    }

    public function testReplaceAllDotsExceptLastThree()
    {
        self::assertEquals(
            'object_something.cool.today.noon',
            StringManipulator::replaceAllDotsExceptLastThree('object.something.cool.today.noon')
        );
        self::assertEquals(
            'object_something.cool.today.',
            StringManipulator::replaceAllDotsExceptLastThree('object.something.cool.today.')
        );
        self::assertEquals(
            'object.something.cool.today',
            StringManipulator::replaceAllDotsExceptLastThree('object.something.cool.today')
        );
        self::assertEquals(
            'object.something.cool.',
            StringManipulator::replaceAllDotsExceptLastThree('object.something.cool.')
        );
        self::assertEquals(
            'object.something.cool',
            StringManipulator::replaceAllDotsExceptLastThree('object.something.cool')
        );
        self::assertEquals(
            'object.something.',
            StringManipulator::replaceAllDotsExceptLastThree('object.something.')
        );
        self::assertEquals(
            'object.something',
            StringManipulator::replaceAllDotsExceptLastThree('object.something')
        );
        self::assertEquals(
            'object.',
            StringManipulator::replaceAllDotsExceptLastThree('object.')
        );
        self::assertEquals(
            'object',
            StringManipulator::replaceAllDotsExceptLastThree('object')
        );
        self::assertEquals(
            '_object_something.cool.today.noon',
            StringManipulator::replaceAllDotsExceptLastThree('.object.something.cool.today.noon')
        );
        self::assertEquals(
            '_object_something.cool.today.',
            StringManipulator::replaceAllDotsExceptLastThree('.object.something.cool.today.')
        );
        self::assertEquals(
            '_object.something.cool.today',
            StringManipulator::replaceAllDotsExceptLastThree('.object.something.cool.today')
        );
        self::assertEquals(
            '_object.something.cool.',
            StringManipulator::replaceAllDotsExceptLastThree('.object.something.cool.')
        );
        self::assertEquals(
            '.object.something.cool',
            StringManipulator::replaceAllDotsExceptLastThree('.object.something.cool')
        );
        self::assertEquals(
            '.object.something.',
            StringManipulator::replaceAllDotsExceptLastThree('.object.something.')
        );
        self::assertEquals(
            '.object.something',
            StringManipulator::replaceAllDotsExceptLastThree('.object.something')
        );
        self::assertEquals(
            '.object.',
            StringManipulator::replaceAllDotsExceptLastThree('.object.')
        );
        self::assertEquals(
            '.object',
            StringManipulator::replaceAllDotsExceptLastThree('.object')
        );
        self::assertEquals(
            '.',
            StringManipulator::replaceAllDotsExceptLastThree('.')
        );
    }

    public function testReplaceAllDotsExceptLastTwo()
    {
        self::assertEquals(
            'object_something.cool.today',
            StringManipulator::replaceAllDotsExceptLastTwo('object.something.cool.today')
        );
        self::assertEquals(
            'object_something.cool.',
            StringManipulator::replaceAllDotsExceptLastTwo('object.something.cool.')
        );
        self::assertEquals(
            'object.something.cool',
            StringManipulator::replaceAllDotsExceptLastTwo('object.something.cool')
        );
        self::assertEquals(
            'object.something.',
            StringManipulator::replaceAllDotsExceptLastTwo('object.something.')
        );
        self::assertEquals(
            'object.something',
            StringManipulator::replaceAllDotsExceptLastTwo('object.something')
        );
        self::assertEquals(
            'object.',
            StringManipulator::replaceAllDotsExceptLastTwo('object.')
        );
        self::assertEquals(
            'object',
            StringManipulator::replaceAllDotsExceptLastTwo('object')
        );
        self::assertEquals(
            '_object_something.cool.today',
            StringManipulator::replaceAllDotsExceptLastTwo('.object.something.cool.today')
        );
        self::assertEquals(
            '_object_something.cool.',
            StringManipulator::replaceAllDotsExceptLastTwo('.object.something.cool.')
        );
        self::assertEquals(
            '_object.something.cool',
            StringManipulator::replaceAllDotsExceptLastTwo('.object.something.cool')
        );
        self::assertEquals(
            '_object.something.',
            StringManipulator::replaceAllDotsExceptLastTwo('.object.something.')
        );
        self::assertEquals(
            '.object.something',
            StringManipulator::replaceAllDotsExceptLastTwo('.object.something')
        );
        self::assertEquals(
            '.object.',
            StringManipulator::replaceAllDotsExceptLastTwo('.object.')
        );
        self::assertEquals(
            '.object',
            StringManipulator::replaceAllDotsExceptLastTwo('.object')
        );
        self::assertEquals(
            '.',
            StringManipulator::replaceAllDotsExceptLastTwo('.')
        );
    }

    /**
     * @test
     */
    public function testReplaceAllDotsExceptLast()
    {
        self::assertEquals(
            'object_something_cool.today',
            StringManipulator::replaceAllDotsExceptLast('object.something.cool.today')
        );
        self::assertEquals(
            'object_something_cool.',
            StringManipulator::replaceAllDotsExceptLast('object.something.cool.')
        );
        self::assertEquals(
            'object_something.cool',
            StringManipulator::replaceAllDotsExceptLast('object.something.cool')
        );
        self::assertEquals(
            'object_something.',
            StringManipulator::replaceAllDotsExceptLast('object.something.')
        );
        self::assertEquals(
            'object.something',
            StringManipulator::replaceAllDotsExceptLast('object.something')
        );
        self::assertEquals(
            'object.',
            StringManipulator::replaceAllDotsExceptLast('object.')
        );
        self::assertEquals(
            'object',
            StringManipulator::replaceAllDotsExceptLast('object')
        );
        self::assertEquals(
            '_object_something_cool.today',
            StringManipulator::replaceAllDotsExceptLast('.object.something.cool.today')
        );
        self::assertEquals(
            '_object_something_cool.',
            StringManipulator::replaceAllDotsExceptLast('.object.something.cool.')
        );
        self::assertEquals(
            '_object_something.cool',
            StringManipulator::replaceAllDotsExceptLast('.object.something.cool')
        );
        self::assertEquals(
            '_object_something.',
            StringManipulator::replaceAllDotsExceptLast('.object.something.')
        );
        self::assertEquals(
            '_object.something',
            StringManipulator::replaceAllDotsExceptLast('.object.something')
        );
        self::assertEquals(
            '_object.',
            StringManipulator::replaceAllDotsExceptLast('.object.')
        );
        self::assertEquals(
            '.object',
            StringManipulator::replaceAllDotsExceptLast('.object')
        );
        self::assertEquals(
            '.',
            StringManipulator::replaceAllDotsExceptLast('.')
        );
    }

    /**
     * @test
     */
    public function testReplaceAllDots()
    {
        self::assertEquals(
            'object_something_cool_today',
            StringManipulator::replaceAllDots('object.something.cool.today')
        );
        self::assertEquals(
            'object_something_cool_',
            StringManipulator::replaceAllDots('object.something.cool.')
        );
        self::assertEquals(
            'object_something_cool',
            StringManipulator::replaceAllDots('object.something.cool')
        );
        self::assertEquals(
            'object_something_',
            StringManipulator::replaceAllDots('object.something.')
        );
        self::assertEquals(
            'object_something',
            StringManipulator::replaceAllDots('object.something')
        );
        self::assertEquals(
            'object_',
            StringManipulator::replaceAllDots('object.')
        );
        self::assertEquals(
            'object',
            StringManipulator::replaceAllDots('object')
        );
        self::assertEquals(
            '_object_something_cool_today',
            StringManipulator::replaceAllDots('.object.something.cool.today')
        );
        self::assertEquals(
            '_object_something_cool_',
            StringManipulator::replaceAllDots('.object.something.cool.')
        );
        self::assertEquals(
            '_object_something_cool',
            StringManipulator::replaceAllDots('.object.something.cool')
        );
        self::assertEquals(
            '_object_something_',
            StringManipulator::replaceAllDots('.object.something.')
        );
        self::assertEquals(
            '_object_something',
            StringManipulator::replaceAllDots('.object.something')
        );
        self::assertEquals(
            '_object_',
            StringManipulator::replaceAllDots('.object.')
        );
        self::assertEquals(
            '_object',
            StringManipulator::replaceAllDots('.object')
        );
        self::assertEquals(
            '_',
            StringManipulator::replaceAllDots('.')
        );
    }
}
