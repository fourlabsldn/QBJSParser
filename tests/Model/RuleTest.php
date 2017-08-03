<?php

namespace FL\QBJSParser\Tests\Model;

use FL\QBJSParser\Exception\Model\RuleConstructionException;
use FL\QBJSParser\Model\Rule;
use FL\QBJSParser\Model\RuleInterface;
use PHPUnit\Framework\TestCase;

class RuleTest extends TestCase
{
    /**
     * @test
     */
    public function testRuleImplementsInterface()
    {
        self::assertInstanceOf(RuleInterface::class, new Rule('id', 'field', 'string', 'equal', 'value'));
    }

    /**
     * @dataProvider validRulesProvider
     *
     * @param string $type
     * @param string $operator
     * @param mixed  $value
     */
    public function testSampleValidCombinations(string $type, string $operator, $value)
    {
        self::assertInstanceOf(RuleInterface::class, new Rule('id', 'field', $type, $operator, $value));
    }

    public function validRulesProvider()
    {
        return [
            ['type' => 'string', 'operator' => 'equal', 'value' => 'string is stringy'],
            ['type' => 'string', 'operator' => 'not_equal', 'value' => 'string is stringy'],
            ['type' => 'string', 'operator' => 'equal', 'value' => '3'],
            ['type' => 'string', 'operator' => 'not_begins_with', 'value' => 'test'],
            ['type' => 'string', 'operator' => 'in', 'value' => ['foo', 'bar']],

            ['type' => 'integer', 'operator' => 'equal', 'value' => 7],
            ['type' => 'integer', 'operator' => 'greater', 'value' => 7],
            ['type' => 'integer', 'operator' => 'greater_or_equal', 'value' => 7],
            ['type' => 'integer', 'operator' => 'less', 'value' => 7],
            ['type' => 'integer', 'operator' => 'less_or_equal', 'value' => 7],
            ['type' => 'integer', 'operator' => 'in', 'value' => [1, 2, 3]],
            ['type' => 'integer', 'operator' => 'not_in', 'value' => [1, 2, 3]],
            ['type' => 'integer', 'operator' => 'between', 'value' => [1, 3]],

            ['type' => 'double', 'operator' => 'equal', 'value' => M_PI],
            ['type' => 'double', 'operator' => 'greater', 'value' => 3.1416],
            ['type' => 'double', 'operator' => 'greater_or_equal', 'value' => M_E],
            ['type' => 'double', 'operator' => 'less', 'value' => 2.71828],
            ['type' => 'double', 'operator' => 'less_or_equal', 'value' => 7.0],
            ['type' => 'double', 'operator' => 'in', 'value' => [1.3, 0.0, 3.1]],
            ['type' => 'double', 'operator' => 'between', 'value' => [1.0, 10.3]],

            ['type' => 'boolean', 'operator' => 'equal', 'value' => true],
            ['type' => 'boolean', 'operator' => 'is_null', 'value' => null],
            ['type' => 'boolean', 'operator' => 'is_not_null', 'value' => null],

            ['type' => 'datetime', 'operator' => 'equal', 'value' => new \DateTimeImmutable()],
            ['type' => 'datetime', 'operator' => 'not_equal', 'value' => new \DateTimeImmutable()],
            ['type' => 'datetime', 'operator' => 'greater', 'value' => new \DateTimeImmutable()],
            ['type' => 'datetime', 'operator' => 'in', 'value' => [new \DateTimeImmutable(), new \DateTimeImmutable('+2 hours')]],
        ];
    }

    /**
     * @dataProvider invalidRulesProvider
     *
     * @param string $type
     * @param string $operator
     * @param mixed  $value
     */
    public function testSampleInvalidCombinations(string $type, string $operator, $value)
    {
        $this->expectException(RuleConstructionException::class);

        new Rule('id', 'field', $type, $operator, $value);
    }

    public function invalidRulesProvider()
    {
        return [
            ['type' => 'string', 'operator' => 'equals', 'value' => 'string is stringy'], // OPERATOR cannot be 'equals' (must be 'equal')
            ['type' => 'string', 'operator' => 'is_null', 'value' => 'string is stringy'], // OPERATOR 'is_null' must correspond to VALUETYPE 'NULL'
            ['type' => 'string', 'operator' => 'is_not_null', 'value' => 'string is stringy'], // OPERATOR 'is_not_null' must correspond to VALUETYPE 'NULL'
            ['type' => 'string', 'operator' => 'in', 'value' => 'test'], // OPERATOR 'in' must have VALUETYPE 'array'
            ['type' => 'string', 'operator' => 'between', 'value' => ['test', 'hello']], // TYPE 'string' cannot correspond to OPERATOR 'between'

            ['type' => 'integer', 'operator' => 'not_begins_with', 'value' => 'test'], // TYPE 'integer' cannot correspond to OPERATOR 'not_begins_with'
            ['type' => 'integer', 'operator' => 'equal', 'value' => '3'], // TYPE 'integer' cannot have VALUETYPE 'string'
            ['type' => 'integer', 'operator' => 'equal', 'value' => 3.1416], // TYPE 'integer' cannot have VALUETYPE 'double'
            ['type' => 'integer', 'operator' => 'greater', 'value' => 7.0], // TYPE 'integer' cannot have VALUETYPE 'double'
            ['type' => 'integer', 'operator' => 'greater_or_equal', 'value' => 0.0], // TYPE 'integer' cannot have VALUETYPE 'double'
            ['type' => 'integer', 'operator' => 'between', 'value' => [1]], // OPERATOR 'between' must have a VALUE array with two elements
            ['type' => 'integer', 'operator' => 'between', 'value' => [1, 3, 5]], // OPERATOR 'between' must have a VALUE array with two elements

            ['type' => 'double', 'operator' => 'equal', 'value' => '3'], // TYPE 'double' cannot have VALUETYPE 'string'
            ['type' => 'double', 'operator' => 'equal', 'value' => 4], // TYPE 'double' cannot have VALUETYPE 'integer'
            ['type' => 'double', 'operator' => 'greater', 'value' => 5], // TYPE 'double' cannot have VALUETYPE 'integer'
            ['type' => 'double', 'operator' => 'greater_or_equal', 'value' => 0], // TYPE 'double' cannot have VALUETYPE 'integer'
            ['type' => 'double', 'operator' => 'between', 'value' => [1.3]], // OPERATOR 'between' must have a VALUE array with two elements
            ['type' => 'double', 'operator' => 'between', 'value' => [1.3, 3.4, 1.5]], // OPERATOR 'between' must have a VALUE array with two elements

            ['type' => 'boolean', 'operator' => 'equal', 'value' => []], // OPERATOR 'equal' cannot have VALUETYPE 'array'
            ['type' => 'boolean', 'operator' => 'equal', 'value' => '3'], // TYPE 'boolean' cannot have VALUETYPE 'string'

            ['type' => 'datetime', 'operator' => 'not_begins_with', 'value' => new \DateTimeImmutable()], // TYPE 'datetime' cannot correspond to OPERATOR 'string'
            ['type' => 'datetime', 'operator' => 'greater', 'value' => 3], // TYPE 'datetime' cannot correspond to VALUETYPE 'integer'
            ['type' => 'datetime', 'operator' => 'less_or_equal', 'value' => '123312313'], // TYPE 'datetime' cannot correspond to VALUETYPE 'string'
            ['type' => 'datetime', 'operator' => 'is_null', 'value' => new \DateTimeImmutable()], // OPERATOR 'is_null' must correspond to VALUETYPE 'NULL'
            ['type' => 'datetime', 'operator' => 'in', 'value' => new \DateTimeImmutable()], // OPERATOR 'in' must have VALUETYPE 'array'
            ['type' => 'datetime', 'operator' => 'in', 'value' => [1, 2, 3]], // OPERATOR 'in' must have VALUETYPE 'array' AND all values of the 'array' must be '\Datetime::class'
            ['type' => 'datetime', 'operator' => 'in', 'value' => [new \DateTimeImmutable(), 2, 3]], // OPERATOR 'in' must have VALUETYPE 'array' AND all values of the 'array' must be '\Datetime::class'
        ];
    }
}
