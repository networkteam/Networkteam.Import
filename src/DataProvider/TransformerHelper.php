<?php
namespace Networkteam\Import\DataProvider;

/**
 * Helper functions for expressions in TransformingProviderDecorator
 *
 * Borrowed from typo3/eel StringHelper.
 */
class TransformerHelper
{

    /**
     * Return the characters in a string from start up to the given length
     *
     * This implementation follows the JavaScript specification for "substr".
     *
     * Examples:
     *
     *   String.substr('Hello, World!', 7, 5) === 'World'
     *
     *   String.substr('Hello, World!', 7) === 'World!'
     *
     *   String.substr('Hello, World!', -6) === 'World!'
     *
     * @param string $string A string
     * @param int $start Start offset
     * @param int $length Maximum length of the substring that is returned
     * @return string The substring
     */
    public function substr(string $string, int $start, int $length = null): string
    {
        if ($length === null) {
            $length = mb_strlen($string, 'UTF-8');
        }
        $length = max(0, $length);
        return mb_substr($string, $start, $length, 'UTF-8');
    }

    /**
     * Return the characters in a string from a start index to an end index
     *
     * This implementation follows the JavaScript specification for "substring".
     *
     * Examples:
     *
     *   String.substring('Hello, World!', 7, 12) === 'World'
     *
     *   String.substring('Hello, World!', 7) === 'World!'
     *
     * @param string $string
     * @param int $start Start index
     * @param int $end End index
     * @return string The substring
     */
    public function substring(string $string, int $start, int $end = null): string
    {
        if ($end === null) {
            $end = mb_strlen($string, 'UTF-8');
        }
        $start = max(0, $start);
        $end = max(0, $end);
        if ($start > $end) {
            $temp = $start;
            $start = $end;
            $end = $temp;
        }
        $length = $end - $start;
        return mb_substr($string, $start, $length, 'UTF-8');
    }

    /**
     * @param string $string
     * @param int $index
     * @return string The character at the given index
     */
    public function charAt(string $string, int $index): string
    {
        if ($index < 0) {
            return '';
        }
        return mb_substr($string, $index, 1, 'UTF-8');
    }

    /**
     * Test if a string ends with the given search string
     *
     * Examples:
     *
     *   String.endsWith('Hello, World!', 'World!') === true
     *
     * @param string $string The string
     * @param string $search A string to search
     * @param int $position Optional position for limiting the string
     * @return bool TRUE if the string ends with the given search
     */
    public function endsWith(string $string, string $search, int $position = null): bool
    {
        $position = $position !== null ? $position : mb_strlen($string, 'UTF-8');
        $position = $position - mb_strlen($search, 'UTF-8');
        return mb_strrpos($string, $search, null, 'UTF-8') === $position;
    }

    /**
     * @param string $string
     * @param string $search
     * @param int $fromIndex
     * @return int
     */
    public function indexOf(string $string, string $search, int $fromIndex = null): int
    {
        $fromIndex = max(0, $fromIndex);
        if ($search === '') {
            return min(mb_strlen($string, 'UTF-8'), $fromIndex);
        }
        $index = mb_strpos($string, $search, $fromIndex, 'UTF-8');
        if ($index === false) {
            return -1;
        }
        return (int)$index;
    }

    /**
     * @param string $string
     * @param string $search
     * @param int $toIndex
     * @return int
     */
    public function lastIndexOf(string $string, string $search, int $toIndex = null): int
    {
        $length = mb_strlen($string, 'UTF-8');
        if ($toIndex === null) {
            $toIndex = $length;
        }
        $toIndex = max(0, $toIndex);
        if ($search === '') {
            return min($length, $toIndex);
        }
        $string = mb_substr($string, 0, $toIndex, 'UTF-8');
        $index = mb_strrpos($string, $search, 0, 'UTF-8');
        if ($index === false) {
            return -1;
        }
        return (int)$index;
    }

    /**
     * Match a string with a regular expression (PREG style)
     *
     * @param string $string
     * @param string $pattern
     * @return array The matches as array or NULL if not matched
     * @throws \Networkteam\Import\Exception
     */
    public function pregMatch(string $string, string $pattern): ?array
    {
        $number = preg_match($pattern, $string, $matches);
        if ($number === false) {
            throw new \Networkteam\Import\Exception('Error evaluating regular expression ' . $pattern . ': ' . preg_last_error(),
                1372793595);
        }
        if ($number === 0) {
            return null;
        }
        return $matches;
    }

    /**
     * Replace occurrences of a search string inside the string using regular expression matching (PREG style)
     *
     * @param string $string
     * @param string $pattern
     * @param string $replace
     * @return string The string with all occurrences replaced
     */
    public function pregReplace(string $string, string $pattern, string $replace): string
    {
        return preg_replace($pattern, $replace, $string);
    }

    /**
     * Replace occurrences of a search string inside the string
     *
     * Note: this method does not perform regular expression matching, @param string $string
     *
     * @param string $search
     * @param string $replace
     * @return string The string with all occurrences replaced
     * @see pregReplace().
     *
     */
    public function replace(string $string, string $search, string $replace): string
    {
        return str_replace($search, $replace, $string);
    }

    /**
     * Split a string by a separator
     *
     * Node: This implementation follows JavaScript semantics without support of regular expressions.
     *
     * @param string $string
     * @param string $separator
     * @param int $limit
     * @return array
     */
    public function split(string $string, string $separator = null, int $limit = null): array
    {
        if ($separator === null) {
            return [$string];
        }
        if ($separator === '') {
            $result = str_split($string);
            if ($limit !== null) {
                $result = array_slice($result, 0, $limit);
            }
            return $result;
        }
        if ($limit === null) {
            $result = explode($separator, $string);
        } else {
            $result = explode($separator, $string, $limit);
        }
        return $result;
    }

    /**
     * Test if a string starts with the given search string
     *
     * @param string $string
     * @param string $search
     * @param int $position
     * @return bool
     */
    public function startsWith(string $string, string $search, int $position = null): bool
    {
        $position = $position !== null ? $position : 0;
        return mb_strrpos($string, $search, null, 'UTF-8') === $position;
    }

    /**
     * @param string $string
     * @return string
     */
    public function toLowerCase(string $string): string
    {
        return mb_strtolower($string, 'UTF-8');
    }

    /**
     * @param string $string
     * @return string
     */
    public function toUpperCase(string $string): string
    {
        return mb_strtoupper($string, 'UTF-8');
    }

    /**
     * Strip all tags from the given string
     *
     * This is a wrapper for the strip_tags() PHP function.
     *
     * @param string $string
     * @return string
     */
    public function stripTags(string $string): string
    {
        return strip_tags($string);
    }

    /**
     * Test if the given string is blank (empty or consists of whitespace only)
     *
     * @param string $string
     * @return bool TRUE if the given string is blank
     */
    public function isBlank(string $string): bool
    {
        return trim((string)$string) === '';
    }

    /**
     * Trim whitespace at the beginning and end of a string
     *
     *
     *
     * @param string $string
     * @param string $charlist Optional list of characters that should be trimmed, defaults to whitespace
     * @return string
     */
    public function trim(string $string, string $charlist = null): string
    {
        if ($charlist === null) {
            return trim($string);
        } else {
            return trim($string, $charlist);
        }
    }

    /**
     * Convert the given value to a string
     *
     * @param mixed $value
     * @return string
     */
    public function toString($value): string
    {
        return (string)$value;
    }

    /**
     * Convert a string to integer
     *
     * @param string $string
     * @return int
     */
    public function toInteger(string $string): int
    {
        return (int)$string;
    }

    /**
     * Convert a string to float
     *
     * @param string $string
     * @return float
     */
    public function toFloat(string $string): float
    {
        return (float)$string;
    }

    /**
     * Convert a string to boolean
     *
     * A value is TRUE, if it is either the string "true" (case insensitive) or the number "1".
     *
     * @param string $string
     * @return bool
     */
    public function toBoolean(string $string): bool
    {
        return strtolower($string) === 'true' || (int)$string === 1;
    }

    /**
     * Encode the string for URLs according to RFC 3986
     *
     * @param string $string
     * @return string
     */
    public function rawUrlEncode(string $string): string
    {
        return rawurlencode($string);
    }

    /**
     * Decode the string from URLs according to RFC 3986
     *
     * @param string $string
     * @return string
     */
    public function rawUrlDecode(string $string): string
    {
        return rawurldecode($string);
    }

    /**
     * @param string $string
     * @param bool $preserveEntities TRUE if entities should not be double encoded
     * @return string
     */
    public function htmlSpecialChars(string $string, bool $preserveEntities = false): string
    {
        return htmlspecialchars($string, null, null, !$preserveEntities);
    }

    /**
     * Calculates the MD5 checksum of the given string
     *
     * @param string $string
     * @return string
     */
    public function md5(string $string): string
    {
        return md5($string);
    }

}