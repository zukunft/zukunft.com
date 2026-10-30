<?php

/*

    shared/calc/arithmetic.php - calculate a text with numbers, the four basic operations, the power, brackets and the math functions
    --------------------------

    used by the frontend to repeat the calculation of a result with the values of a page
    (docs/llm/frontend.md "The frontend calculates, a save job verifies"); the text is the
    right part of a formula expression with the phrase names replaced by numbers


    This file is part of zukunft.com - calc with words

    zukunft.com is free software: you can redistribute it and/or modify it
    under the terms of the GNU General Public License as
    published by the Free Software Foundation, either version 3 of
    the License, or (at your option) any later version.
    zukunft.com is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/agpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2026 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\main\php\shared\calc;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::SHARED_CONST . 'chars.php';

use Zukunft\ZukunftCom\main\php\shared\const\chars;

class arithmetic
{

    // the text to calculate and the position of the next char to read
    private string $txt = '';
    private int $pos = 0;
    // false as soon as a char does not fit the grammar
    private bool $valid = true;
    // true if a part has no number e.g. a division by zero, which an if function may leave out
    private bool $no_number = false;

    /**
     * the number of a function of one number, also used by the backend calculation (service/math/calc_internal.php)
     *
     * @param string $name the name of the function, one of chars::FUNC_ONE_NUMBER
     * @param float $num the number of its argument
     * @return float the number, which is not finite if the function has no number for the argument e.g. the logarithm of zero
     */
    static function one_number(string $name, float $num): float
    {
        return match ($name) {
            chars::FUNC_ABS => abs($num),
            chars::FUNC_COS => cos($num),
            chars::FUNC_EXP => exp($num),
            chars::FUNC_LN => log($num),
            chars::FUNC_LOG => log10($num),
            chars::FUNC_ROUND => round($num),
            chars::FUNC_SEC => 1 / cos($num),
            chars::FUNC_SIN => sin($num),
            chars::FUNC_SQRT => sqrt($num),
            default => NAN,
        };
    }

    /**
     * the logarithm, also used by the backend calculation (service/math/calc_internal.php)
     *
     * @param array $nums the number and optional the base, which is 10 if it is not given
     * @return float the logarithm, which is not finite for a base without a logarithm or a wrong number of arguments
     */
    static function log_number(array $nums): float
    {
        $result = NAN;
        if (count($nums) == 1) {
            $result = log10($nums[0]);
        } elseif (count($nums) == 2 and $nums[1] > 0 and $nums[1] != 1) {
            $result = log($nums[0]) / log($nums[1]);
        }
        return $result;
    }

    /**
     * the number of a list function, also used by the backend calculation (service/math/calc_internal.php)
     *
     * @param string $name the name of the function, one of chars::FUNC_LIST
     * @param array $nums the numbers of the list
     * @return float the number, which is not finite for an unknown function
     */
    static function list_number(string $name, array $nums): float
    {
        return match ($name) {
            chars::FUNC_SUM => array_sum($nums),
            chars::FUNC_COUNT => count($nums),
            chars::FUNC_MEAN => array_sum($nums) / count($nums),
            default => NAN,
        };
    }

    /**
     * a list function takes all values of a plain operand, which cannot be combined with another number:
     * sum( "a" * "b" ) would need pairs of values and is refused instead of giving a wrong number
     *
     * @param string $txt the expression with each operand replaced by x
     * @return bool true if each list function has only operands or numbers as its arguments
     */
    static function list_args_are_plain(string $txt): bool
    {
        $names = implode('|', chars::FUNC_LIST);
        $arg = '\s*(?:x|-?\d+(?:\.\d+)?)\s*';
        $sep = chars::FUNC_ARG_SEPARATOR;
        $rest = preg_replace('/\b(?:' . $names . ')\s*\(' . $arg . '(?:' . $sep . $arg . ')*\)/', '', $txt);
        return preg_match('/\b(?:' . $names . ')\s*\(/', $rest) !== 1;
    }

    /**
     * the power, also used by the backend calculation (service/math/calc_internal.php)
     *
     * @param float $base the number that is raised
     * @param float $exponent the power
     * @return float the number, which is not finite if there is none e.g. zero to a negative power
     */
    static function power_of(float $base, float $exponent): float
    {
        $is_pole = ($base == 0 and $exponent < 0);
        return $is_pole ? NAN : $base ** $exponent;
    }

    /**
     * calculate a text with numbers, + - * / ^, brackets and the math functions e.g. "( 1575 + 2223.77 ) * 30" or "min( 207.4 ; 157.5 )"
     *
     * @param string $txt the text to calculate
     * @return float|null the number or null if the text is not a valid calculation or divides by zero
     */
    function calc(string $txt): ?float
    {
        $this->txt = $txt;
        $this->pos = 0;
        $this->valid = true;
        $this->no_number = false;
        $result = $this->sum();
        $this->skip_blanks();
        if (!$this->valid or $this->no_number or $this->pos < strlen($this->txt)) {
            $result = null;
        }
        return $result;
    }

    /**
     * @return float the products added or subtracted from left to right
     */
    private function sum(): float
    {
        $result = $this->product();
        while ($this->valid and $this->next_is(['+', '-'])) {
            $op = $this->take();
            $right = $this->product();
            $result = $op == '+' ? $result + $right : $result - $right;
        }
        return $result;
    }

    /**
     * @return float the powers multiplied or divided from left to right
     */
    private function product(): float
    {
        $result = $this->power();
        while ($this->valid and $this->next_is(['*', '/'])) {
            $op = $this->take();
            $right = $this->power();
            if ($op == '*') {
                $result = $result * $right;
            } elseif ($right == 0.0) {
                $this->no_number = true;
            } else {
                $result = $result / $right;
            }
        }
        return $result;
    }

    /**
     * @return float a signed number or its power from right to left; a number keeps its sign, the same as calc_internal
     */
    private function power(): float
    {
        $result = $this->signed();
        if ($this->valid and $this->next_is([chars::POW])) {
            $this->take();
            $result = $this->finite(self::power_of($result, $this->power()));
        }
        return $result;
    }

    /**
     * @param float $num a calculated number
     * @return float the number, which is marked as no number if it is not finite
     */
    private function finite(float $num): float
    {
        if (!is_finite($num)) {
            $this->no_number = true;
        }
        return $num;
    }

    /**
     * @return float a number or a bracket with an optional sign in front
     */
    private function signed(): float
    {
        if ($this->next_is(['-'])) {
            $this->take();
            return -$this->signed();
        }
        if ($this->next_is(['+'])) {
            $this->take();
        }
        return $this->atom();
    }

    /**
     * @return float a bracket with a sum inside, a math function or a number, 0 and invalid for anything else
     */
    private function atom(): float
    {
        $result = 0.0;
        if ($this->next_is(['('])) {
            $this->take();
            $result = $this->sum();
            $this->take_bracket_close();
        } elseif ($this->take_function(chars::FUNC_MIN)) {
            $result = $this->pick_argument(min(...));
        } elseif ($this->take_function(chars::FUNC_MAX)) {
            $result = $this->pick_argument(max(...));
        } elseif ($this->take_function(chars::FUNC_IF)) {
            $result = $this->if_function();
        } elseif ($this->next_function([chars::FUNC_LOG]) != '') {
            $this->take_function(chars::FUNC_LOG);
            $result = $this->finite(self::log_number($this->list_arguments()));
        } elseif ($this->next_function(chars::FUNC_ONE_NUMBER) != '') {
            $name = $this->next_function(chars::FUNC_ONE_NUMBER);
            $this->take_function($name);
            $arg = $this->sum();
            $this->take_bracket_close();
            $result = $this->finite(self::one_number($name, $arg));
        } elseif ($this->next_function(chars::FUNC_LIST) != '') {
            $name = $this->next_function(chars::FUNC_LIST);
            $this->take_function($name);
            $result = $this->finite(self::list_number($name, $this->list_arguments()));
        } elseif (preg_match('/\G\s*(\d+(\.\d+)?)/', $this->txt, $found, 0, $this->pos)) {
            $this->pos += strlen($found[0]);
            $result = (float)$found[1];
        } else {
            $this->valid = false;
        }
        return $result;
    }

    /**
     * @param callable $pick returns the one of two numbers that the function keeps e.g. the smaller for min
     * @return float the number kept of the sums separated by the argument separator up to the closing bracket
     */
    private function pick_argument(callable $pick): float
    {
        $result = $this->sum();
        while ($this->valid and $this->next_is([chars::FUNC_ARG_SEPARATOR])) {
            $this->take();
            $result = $pick($result, $this->sum());
        }
        $this->take_bracket_close();
        return $result;
    }

    /**
     * @return array the sums separated by the argument separator up to the closing bracket
     */
    private function list_arguments(): array
    {
        $result = [$this->sum()];
        while ($this->valid and $this->next_is([chars::FUNC_ARG_SEPARATOR])) {
            $this->take();
            $result[] = $this->sum();
        }
        $this->take_bracket_close();
        return $result;
    }

    /**
     * @param array $names the names of the functions to look for
     * @return string the name of the function that follows or an empty text
     */
    private function next_function(array $names): string
    {
        $result = '';
        foreach ($names as $name) {
            $pattern = '/\G\s*' . preg_quote($name, '/') . '\s*\(/';
            if (preg_match($pattern, $this->txt, $found, 0, $this->pos) === 1) {
                $result = $name;
            }
        }
        return $result;
    }

    /**
     * @return float the number of the then or the else part up to the closing bracket, of which only the chosen part
     *               counts, because the condition often guards the other part e.g. against a division by zero
     */
    private function if_function(): float
    {
        $is_true = $this->or_condition();
        $this->take_separator();
        $no_number_before = $this->no_number;
        $then_num = $this->sum();
        $no_number_then = $this->no_number;
        $this->no_number = $no_number_before;
        $this->take_separator();
        $else_num = $this->sum();
        $this->take_bracket_close();
        $result = $else_num;
        if ($is_true) {
            $this->no_number = $no_number_then;
            $result = $then_num;
        }
        return $result;
    }

    /**
     * read the separator of the arguments or set the calculation invalid if it is missing
     */
    private function take_separator(): void
    {
        if ($this->next_is([chars::FUNC_ARG_SEPARATOR])) {
            $this->take();
        } else {
            $this->valid = false;
        }
    }

    /**
     * @return bool the conditions joined by chars::OR
     */
    private function or_condition(): bool
    {
        $result = $this->and_condition();
        while ($this->valid and $this->next_is([chars::OR])) {
            $this->take();
            $right = $this->and_condition();
            $result = ($result or $right);
        }
        return $result;
    }

    /**
     * @return bool the conditions joined by chars::AND
     */
    private function and_condition(): bool
    {
        $result = $this->compare();
        while ($this->valid and $this->next_is([chars::AND])) {
            $this->take();
            $right = $this->compare();
            $result = ($result and $right);
        }
        return $result;
    }

    /**
     * @return bool two sums compared by "=" or a sum that is true if it is not zero
     */
    private function compare(): bool
    {
        $left = $this->sum();
        $result = ($left != 0.0);
        if ($this->valid and $this->next_is([chars::CHAR_CALC])) {
            $this->take();
            $result = ($left == $this->sum());
        }
        return $result;
    }

    /**
     * @param string $name the name of the function e.g. "min"
     * @return bool true if the function name and its open bracket follow, which are then read
     */
    private function take_function(string $name): bool
    {
        $pattern = '/\G\s*' . preg_quote($name, '/') . '\s*\(/';
        $result = (preg_match($pattern, $this->txt, $found, 0, $this->pos) === 1);
        if ($result) {
            $this->pos += strlen($found[0]);
        }
        return $result;
    }

    /**
     * read the closing bracket or set the calculation invalid if it is missing
     */
    private function take_bracket_close(): void
    {
        if ($this->next_is([')'])) {
            $this->take();
        } else {
            $this->valid = false;
        }
    }

    /**
     * @param array $chars the chars that may follow
     * @return bool true if the next char that is no blank is one of the given chars
     */
    private function next_is(array $chars): bool
    {
        $this->skip_blanks();
        return in_array(substr($this->txt, $this->pos, 1), $chars, true);
    }

    /**
     * @return string the next char, which the caller has checked with next_is
     */
    private function take(): string
    {
        $char = substr($this->txt, $this->pos, 1);
        $this->pos++;
        return $char;
    }

    private function skip_blanks(): void
    {
        while (substr($this->txt, $this->pos, 1) == ' ') {
            $this->pos++;
        }
    }

}
