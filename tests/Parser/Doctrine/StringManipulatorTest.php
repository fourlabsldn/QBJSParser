<?php

namespace FL\QBJSParser\Tests\Parser\Doctrine;

use FL\QBJSParser\Parser\Doctrine\StringManipulator;
use PHPUnit\Framework\TestCase;

class StringManipulatorTest extends TestCase
{
    public function testClassCantBeInstantiated()
    {
        self::expectException(\Error::class);

        new class() extends StringManipulator {
        };
    }

    /**
     * @dataProvider replaceAllDotsExceptLastThreeStringProvider
     *
     * @param string $input
     * @param string $output
     */
    public function testReplaceAllDotsExceptLastThree(string $input, string $output)
    {
        self::assertEquals($output, StringManipulator::replaceAllDotsExceptLastThree($input));
    }

    public function replaceAllDotsExceptLastThreeStringProvider(): array
    {
        return [
            ['object.something.cool.today.', 'object_something.cool.today.'],
            ['object.something.cool.today.', 'object_something.cool.today.'],
            ['object.something.cool.today', 'object.something.cool.today'],
            ['object.something.cool.', 'object.something.cool.'],
            ['object.something.cool', 'object.something.cool'],
            ['object.something.', 'object.something.'],
            ['object.something', 'object.something'],
            ['object.', 'object.'],
            ['object', 'object'],
            ['.object.something.cool.today.', '_object_something.cool.today.'],
            ['.object.something.cool.today', '_object.something.cool.today'],
            ['.object.something.cool.', '_object.something.cool.'],
            ['.object.something.cool', '.object.something.cool'],
            ['.object.something.', '.object.something.'],
            ['.object.something', '.object.something'],
            ['.object.', '.object.'],
            ['.object', '.object'],
            ['.', '.'],
        ];
    }

    /**
     * @dataProvider replaceAllDotsExceptLastTwoStringProvider
     *
     * @param string $input
     * @param string $output
     */
    public function testReplaceAllDotsExceptLastTwo(string $input, string $output)
    {
        self::assertEquals($output, StringManipulator::replaceAllDotsExceptLastTwo($input));
    }

    public function replaceAllDotsExceptLastTwoStringProvider(): array
    {
        return [
            ['object.something.cool.today', 'object_something.cool.today'],
            ['object.something.cool.', 'object_something.cool.'],
            ['object.something.cool', 'object.something.cool'],
            ['object.something.', 'object.something.'],
            ['object.something', 'object.something'],
            ['object.', 'object.'],
            ['object', 'object'],
            ['.object.something.cool.today', '_object_something.cool.today'],
            ['.object.something.cool.', '_object_something.cool.'],
            ['.object.something.cool', '_object.something.cool'],
            ['.object.something.', '_object.something.'],
            ['.object.something', '.object.something'],
            ['.object.', '.object.'],
            ['.object', '.object'],
            ['.', '.'],
        ];
    }

    /**
     * @dataProvider replaceAllDotsExceptLastStringProvider
     *
     * @param string $input
     * @param string $output
     */
    public function testReplaceAllDotsExceptLast(string $input, string $output)
    {
        self::assertEquals($output, StringManipulator::replaceAllDotsExceptLast($input));
    }

    public function replaceAllDotsExceptLastStringProvider(): array
    {
        return [
            ['object.something.cool.today', 'object_something_cool.today'],
            ['object.something.cool.', 'object_something_cool.'],
            ['object.something.cool', 'object_something.cool'],
            ['object.something.', 'object_something.'],
            ['object.something', 'object.something'],
            ['object.', 'object.'],
            ['object', 'object'],
            ['_object_something_cool.today', '_object_something_cool.today'],
            ['.object.something.cool', '_object_something.cool'],
            ['.object.something.', '_object_something.'],
            ['.object.something', '_object.something'],
            ['.object.', '_object.'],
            ['.object', '.object'],
            ['.', '.'],
        ];
    }

    /**
     * @dataProvider replaceAllDotsStringProvider
     *
     * @param string $input
     * @param string $output
     */
    public function testReplaceAllDots(string $input, string $output)
    {
        self::assertEquals($output, StringManipulator::replaceAllDots($input));
    }

    public function replaceAllDotsStringProvider(): array
    {
        return [
            ['object.something.cool.today', 'object_something_cool_today'],
            ['object.something.cool.', 'object_something_cool_'],
            ['object.something.cool', 'object_something_cool'],
            ['object.something.', 'object_something_'],
            ['object.something', 'object_something'],
            ['object.', 'object_'],
            ['object', 'object'],
            ['.object.something.cool.today', '_object_something_cool_today'],
            ['.object.something.cool.', '_object_something_cool_'],
            ['.object.something.cool', '_object_something_cool'],
            ['.object.something.', '_object_something_'],
            ['.object.something', '_object_something'],
            ['.object.', '_object_'],
            ['.object', '_object'],
            ['.', '_'],
        ];
    }
}
