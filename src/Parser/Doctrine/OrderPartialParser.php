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
     * @param array      $embeddableFieldPrefixesToClasses
     * @param array      $embeddableFieldPrefixesToEmbeddableClasses
     *
     * @return string
     */
    final public static function parse(
        array $queryBuilderFieldsToProperties,
        array $sortColumns = null,
        array $embeddableFieldsToProperties,
        array $embeddableFieldPrefixesToClasses,
        array $embeddableFieldPrefixesToEmbeddableClasses
    ): string {
        foreach ($queryBuilderFieldsToProperties as $queryBuilderField => $property) {
            static::$queryBuilderFieldsToOrderAlias[$queryBuilderField] = StringManipulator::replaceAllDotsExceptLast(SelectPartialParser::OBJECT_WORD.'.'.$property);
        }
        foreach ($embeddableFieldsToProperties as $queryBuilderField => $property) {
            $suffixPattern = '/\.((?!\.).)+$/';
            $fieldPrefix = preg_replace($suffixPattern, '', $queryBuilderField);

            if (in_array($fieldPrefix, array_keys($embeddableFieldPrefixesToClasses))) {
                static::$queryBuilderFieldsToOrderAlias[$queryBuilderField] = SelectPartialParser::OBJECT_WORD.StringManipulator::replaceAllDotsExceptLast('.'.$property);
            } elseif (in_array($fieldPrefix, array_keys($embeddableFieldPrefixesToEmbeddableClasses))) {
                static::$queryBuilderFieldsToOrderAlias[$queryBuilderField] = SelectPartialParser::OBJECT_WORD.StringManipulator::replaceAllDotsExceptLastTwo('.'.$property);
            }
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
