<?php

/*

    shared/calc/arithmetic.php - calculate a text with numbers, the four basic operations and brackets
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

class arithmetic
{

    // the text to calculate and the position of the next char to read
    private string $txt = '';
    private int $pos = 0;
    // false as soon as a char does not fit the grammar or a division by zero is asked
    private bool $valid = true;

    /**
     * calculate a text with numbers, + - * / and brackets e.g. "( 1575 + 2223.77 ) * 30"
     *
     * @param string $txt the text to calculate
     * @return float|null the number or null if the text is not a valid calculation or divides by zero
     */
    function calc(string $txt): ?float
    {
        $this->txt = $txt;
        $this->pos = 0;
        $this->valid = true;
        $result = $this->sum();
        $this->skip_blanks();
        if (!$this->valid or $this->pos < strlen($this->txt)) {
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
     * @return float the signed numbers multiplied or divided from left to right
     */
    private function product(): float
    {
        $result = $this->signed();
        while ($this->valid and $this->next_is(['*', '/'])) {
            $op = $this->take();
            $right = $this->signed();
            if ($op == '*') {
                $result = $result * $right;
            } elseif ($right == 0.0) {
                $this->valid = false;
            } else {
                $result = $result / $right;
            }
        }
        return $result;
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
     * @return float a bracket with a sum inside or a number, 0 and invalid for anything else
     */
    private function atom(): float
    {
        $result = 0.0;
        if ($this->next_is(['('])) {
            $this->take();
            $result = $this->sum();
            if ($this->next_is([')'])) {
                $this->take();
            } else {
                $this->valid = false;
            }
        } elseif (preg_match('/\G\s*(\d+(\.\d+)?)/', $this->txt, $found, 0, $this->pos)) {
            $this->pos += strlen($found[0]);
            $result = (float)$found[1];
        } else {
            $this->valid = false;
        }
        return $result;
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
