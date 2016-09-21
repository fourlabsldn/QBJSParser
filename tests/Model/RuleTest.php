<?php

namespace FL\QBJSParser\Tests\Model;

use FL\QBJSParser\Exception\Model\RuleConstructionException;
use FL\QBJSParser\Model\Rule;
use FL\QBJSParser\Model\RuleInterface;

class RuleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    private $samplesValidCombinations;

    /**
     * @var array
     */
    private $samplesInvalidCombinations;

    public function setup()
    {
        $this->samplesValidCombinations = [
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
            ['type' => 'integer', 'operator' => 'in', 'value' =>  [1,2,3]],
            ['type' => 'integer', 'operator' => 'not_in', 'value' =>  [1,2,3]],
            ['type' => 'integer', 'operator' => 'between', 'value' =>  [1,3]],

            ['type' => 'double', 'operator' => 'equal', 'value' => M_PI],
            ['type' => 'double', 'operator' => 'greater', 'value' => 3.1416],
            ['type' => 'double', 'operator' => 'greater_or_equal', 'value' => M_E],
            ['type' => 'double', 'operator' => 'less', 'value' => 2.71828],
            ['type' => 'double', 'operator' => 'less_or_equal', 'value' => 7.0],
            ['type' => 'double', 'operator' => 'in', 'value' =>  [1.3,0.0,3.1]],
            ['type' => 'double', 'operator' => 'between', 'value' =>  [1.0,10.3]],


            ['type' => 'boolean', 'operator' => 'equal', 'value' => true],
            ['type' => 'boolean', 'operator' => 'is_null', 'value' => null],
            ['type' => 'boolean', 'operator' => 'is_not_null', 'value' => null],

            ['type' => 'datetime', 'operator' => 'equal', 'value' => new \DateTime()],
            ['type' => 'datetime', 'operator' => 'not_equal', 'value' => new \DateTime()],
            ['type' => 'datetime', 'operator' => 'greater', 'value' => new \DateTime()],
            ['type' => 'datetime', 'operator' => 'in', 'value' => [new \DateTime(), new \DateTime("+2 hours")]],
        ];
        $this->samplesInvalidCombinations = [
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
            ['type' => 'integer', 'operator' => 'between', 'value' =>  [1]], // OPERATOR 'between' must have a VALUE array with two elements
            ['type' => 'integer', 'operator' => 'between', 'value' =>  [1, 3, 5]], // OPERATOR 'between' must have a VALUE array with two elements

            ['type' => 'double', 'operator' => 'equal', 'value' => '3'], // TYPE 'double' cannot have VALUETYPE 'string'
            ['type' => 'double', 'operator' => 'equal', 'value' => 4], // TYPE 'double' cannot have VALUETYPE 'integer'
            ['type' => 'double', 'operator' => 'greater', 'value' => 5], // TYPE 'double' cannot have VALUETYPE 'integer'
            ['type' => 'double', 'operator' => 'greater_or_equal', 'value' => 0], // TYPE 'double' cannot have VALUETYPE 'integer'
            ['type' => 'double', 'operator' => 'between', 'value' =>  [1.3]], // OPERATOR 'between' must have a VALUE array with two elements
            ['type' => 'double', 'operator' => 'between', 'value' =>  [1.3,3.4, 1.5]], // OPERATOR 'between' must have a VALUE array with two elements

            ['type' => 'boolean', 'operator' => 'equal', 'value' => []], // OPERATOR 'equal' cannot have VALUETYPE 'array'
            ['type' => 'boolean', 'operator' => 'equal', 'value' => '3'], // TYPE 'boolean' cannot have VALUETYPE 'string'

            ['type' => 'datetime', 'operator' => 'not_begins_with', 'value' => new \DateTime()], // TYPE 'datetime' cannot correspond to OPERATOR 'string'
            ['type' => 'datetime', 'operator' => 'greater', 'value' => 3], // TYPE 'datetime' cannot correspond to VALUETYPE 'integer'
            ['type' => 'datetime', 'operator' => 'less_or_equal', 'value' => '123312313'], // TYPE 'datetime' cannot correspond to VALUETYPE 'string'
            ['type' => 'datetime', 'operator' => 'is_null', 'value' =>  new \DateTime()], // OPERATOR 'is_null' must correspond to VALUETYPE 'NULL'
            ['type' => 'datetime', 'operator' => 'in', 'value' =>  new \DateTime()], // OPERATOR 'in' must have VALUETYPE 'array'
            ['type' => 'datetime', 'operator' => 'in', 'value' =>  [1,2,3]], // OPERATOR 'in' must have VALUETYPE 'array' AND all values of the 'array' must be '\Datetime::class'
            ['type' => 'datetime', 'operator' => 'in', 'value' =>  [new \DateTime(),2,3]], // OPERATOR 'in' must have VALUETYPE 'array' AND all values of the 'array' must be '\Datetime::class'
        ];
    }

    /**
     * @test
     */
    public function testRuleImplementsInterface()
    {
        $this->assertInstanceOf(RuleInterface::class, new Rule("id", "field", "string", "equal", "value"));
    }

    /**
     * @test
     */
    public function testSampleValidCombinations()
    {
        $combinations = $this->samplesValidCombinations;
        foreach ($combinations as $combination) {
            new Rule("id", "field", $combination["type"], $combination["operator"], $combination["value"]);
        }
    }

    /**
     * @test
     */
    public function testSampleInvalidCombinations()
    {
        $combinations = $this->samplesInvalidCombinations;
        foreach ($combinations as $combination) {
            $this->assertRuleConstructionException(function () use ($combination) {
                new Rule("id", "field", $combination["type"], $combination["operator"], $combination["value"]);
            }, $combination);
        }
    }

    /**
     * @param \Closure $function
     * @param array $combination
     */
    private function assertRuleConstructionException(\Closure $function, array $combination)
    {
        try {
            $function();
        } catch (RuleConstructionException $e) {
            return;
        }

        $appendToErrorSummary = '';
        $errorValue = $combination['value'];
        if (
            (!is_array($errorValue)) &&
            ((!is_object($errorValue) && settype($errorValue, 'string') !== false) ||
                (is_object($errorValue) && method_exists($errorValue, '__toString')))
        ) {
            $appendToErrorSummary = ' AND value: ' . strval($errorValue);
        }

        $errorSummary = 'Rule with type: ' . strval($combination["type"]) . ' AND operator: ' . strval($combination["operator"]) . $appendToErrorSummary;

        $this->fail('Expected ' . RuleConstructionException::class . '. ' . $errorSummary);
    }
}
