<?php

namespace FL\QBJSParser\Parser\Doctrine;

abstract class StringManipulator
{
    private function __construct()
    {
    }

    /**
     * Will convert "object_something.cool.today.midday" TO "object_something.cool.today.midday".
     * Will convert "object.something.cool.today" TO "object.something.cool.today".
     * Will convert "object.something.cool" TO "object.something.cool".
     * Will convert "object.something" TO "object.something".
     * Will convert "object" TO "object".
     *
     * @param string $string
     *
     * @return string
     */
    final public static function replaceAllDotsExceptLastThree(string $string): string
    {
        $countDots = substr_count($string, '.');
        if ($countDots > 3) {
            $stringArray = explode('.', $string);
            $string = '';
            for ($i = 0; $i < $countDots - 3; ++$i) {
                $string .= $stringArray[$i].'_';
            }
            $string .= $stringArray[$countDots - 3].'.'.$stringArray[$countDots - 2].'.'.$stringArray[$countDots - 1].'.'.$stringArray[$countDots];
        }

        return $string;
    }

    /**
     * Will convert "object.something.cool.today" TO "object_something.cool.today".
     * Will convert "object.something.cool" TO "object.something.cool".
     * Will convert "object.something" TO "object.something".
     * Will convert "object" TO "object".
     *
     * @param string $string
     *
     * @return string
     */
    final public static function replaceAllDotsExceptLastTwo(string $string): string
    {
        $countDots = substr_count($string, '.');
        if ($countDots > 2) {
            $stringArray = explode('.', $string);
            $string = '';
            for ($i = 0; $i < $countDots - 2; ++$i) {
                $string .= $stringArray[$i].'_';
            }
            $string .= $stringArray[$countDots - 2].'.'.$stringArray[$countDots - 1].'.'.$stringArray[$countDots];
        }

        return $string;
    }

    /**
     * Will convert "object.something.cool.today" TO "object_something_cool.today".
     * Will convert "object.something.cool" TO "object_something.cool".
     * Will convert "object.something" TO "object.something".
     * Will convert "object" TO "object".
     *
     * @param string $string
     *
     * @return string
     */
    final public static function replaceAllDotsExceptLast(string $string): string
    {
        $countDots = substr_count($string, '.');
        if ($countDots > 1) {
            $stringArray = explode('.', $string);
            $string = '';
            for ($i = 0; $i < $countDots - 1; ++$i) {
                $string .= $stringArray[$i].'_';
            }
            $string .= $stringArray[$countDots - 1].'.'.$stringArray[$countDots];
        }

        return $string;
    }

    /**
     * Will convert "object.something.cool.today" TO "object_something_cool_today".
     * Will convert "object.something.cool" TO object_something_cool".
     * Will convert "object.something" "TO object_something".
     * Will convert "object" TO "object".
     *
     * @param string $string
     *
     * @return string
     */
    final public static function replaceAllDots(string $string): string
    {
        return str_replace('.', '_', $string);
    }
}
