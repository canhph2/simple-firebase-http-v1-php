<?php

namespace CongnqNexlesoft\SimpleFirebaseHttpV1\Helpers;

/**
 * (Last updated on July 4, 2042)
 * ### A String Helper
 * - This is a simple string helper for PHP < 8.1
 */
class StrHelper
{
    /**
     * @param string $toCheck
     * @param string $search
     * @return bool
     */
    public static function contains(string $toCheck, string $search): bool
    {
        return strpos($toCheck, $search) !== false;
    }

    /**
     * @param string $toCheck
     * @param string $search
     * @return bool
     */
    public static function startWith(string $toCheck, string $search): bool
    {
        return strpos($toCheck, $search) === 0;
    }

    /**
     * @param string $toCheck
     * @param string $search
     * @return bool
     */
    public static function endWith(string $toCheck, string $search): bool
    {
        $length = strlen($search);
        if ($length === 0) {
            return false; // Empty needle always matches
        }
        return substr($toCheck, -$length) === $search;
    }

    /**
     * If there's no $separator, return the entire string
     *
     * @param string $strToCheck
     * @param string $separator
     * @return string
     */
    public static function lastPart(string $strToCheck, string $separator): string
    {
        return strrchr($strToCheck, $separator) === false ? $strToCheck
            : ltrim(strrchr($strToCheck, $separator), $separator);
    }

    /**
     * If there's no $separator, return the entire string
     *
     * @param string $strToCheck
     * @param string $separator
     * @return string
     */
    public static function removeLastPart(string $strToCheck, string $separator): string
    {
        return strrchr($strToCheck, $separator) === false ? $strToCheck
            : substr($strToCheck, 0, strrpos($strToCheck, $separator));
    }

    /**
     * - replace all special characters and spaces
     * @param string $str
     * @param string $replacementChar
     * @return string
     */
    public static function sanitize(string $str, string $replacementChar = ''): string
    {
        return preg_replace('/[^a-zA-Z0-9]/', $replacementChar, $str);
    }

    /**
     * @param string $string
     * @param bool $capitalizeFirstCharacter
     * @return string
     */
    public static function dashesToCamelCase(string $string, bool $capitalizeFirstCharacter = false): string
    {
        $str = str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
        if (!$capitalizeFirstCharacter) {
            $str[0] = strtolower($str[0]);
        }
        return $str;
    }
}
