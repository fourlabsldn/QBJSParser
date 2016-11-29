<?php

namespace FL\QBJSParser\Parser\Doctrine;

use FL\QBJSParser\Exception\Parser\Doctrine\InvalidFieldException;

abstract class OrderPartialParser
{
    /**
     * @var array
     */
    private static $queryBuilderFieldsToOrderAlias;

    final private function __construct()
    {
    }

    /**
     * @param array      $queryBuilderFieldsToProperties
     * @param array|null $sortColumns
     * @param array      $embeddableFieldsToProperties
     * @param array      $embeddableInsideEmbeddableFieldsToProperties
     *
     * @return string
     */
    final public static function parse(
        array $queryBuilderFieldsToProperties,
        array $sortColumns = null,
        array $embeddableFieldsToProperties,
        array $embeddableInsideEmbeddableFieldsToProperties
    ): string {
        foreach ($queryBuilderFieldsToProperties as $field => $property) {
            static::$queryBuilderFieldsToOrderAlias[$field] = StringManipulator::replaceAllDotsExceptLast(SelectPartialParser::OBJECT_WORD.'.'.$property);
        }
        foreach ($embeddableFieldsToProperties as $field => $property) {
            static::$queryBuilderFieldsToOrderAlias[$field] = SelectPartialParser::OBJECT_WORD.StringManipulator::replaceAllDotsExceptLastTwo('.'.$property);
        }
        foreach ($embeddableInsideEmbeddableFieldsToProperties as $field => $property) {
            static::$queryBuilderFieldsToOrderAlias[$field] = SelectPartialParser::OBJECT_WORD.StringManipulator::replaceAllDotsExceptLastThree('.'.$property);
        }

        if ($sortColumns === null || count($sortColumns) === 0) {
            return '';
        }

        $orderString = ' ORDER BY ';
        foreach ($sortColumns as $field => $order) {
            $orderString .= sprintf(' %s %s, ',
                static::queryBuilderFieldToOrderAlias($field),
                static::queryBuilderOrderDirectionToSafeValue($order)
            );
        }

        return rtrim($orderString, ', ').' ';
    }

    /**
     * @param string $orderDirection
     *
     * @return string
     */
    final private static function queryBuilderOrderDirectionToSafeValue(string $orderDirection) : string
    {
        $dictionary = [
            'ASC' => 'ASC',
            'DESC' => 'DESC',
        ];

        if (!array_key_exists($orderDirection, $dictionary)) {
            throw new InvalidFieldException($orderDirection);
        }

        return $dictionary[$orderDirection];
    }

    /**
     * @param string $queryBuilderField
     *
     * @return string
     */
    final private static function queryBuilderFieldToOrderAlias(string $queryBuilderField) : string
    {
        $dictionary = static::$queryBuilderFieldsToOrderAlias;

        if (!array_key_exists($queryBuilderField, $dictionary)) {
            throw new InvalidFieldException($queryBuilderField);
        }

        return $dictionary[$queryBuilderField];
    }
}
