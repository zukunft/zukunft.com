<?php

/*

    web/formula/formula_calc.php - repeat the calculation of a result with the values of a page
    ----------------------------

    the frontend calculates a result from the values and results it has loaded, so that a stale
    stored result is never shown silently (docs/llm/frontend.md "The frontend calculates, a save
    job verifies"); the phrase names of the formula are replaced by the numbers of the page the
    same way as the import validation does (data_object::expression_with_values)


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

namespace Zukunft\ZukunftCom\main\php\web\formula;

use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::FORMULA . 'formula_list.php';
include_once html_paths::RESULT . 'result.php';
include_once html_paths::VALUE . 'value.php';
include_once html_paths::VALUE . 'value_list.php';
include_once html_paths::SHARED_CALC . 'arithmetic.php';
include_once html_paths::SHARED_CONST . 'chars.php';
include_once html_paths::SHARED_CONST . 'words.php';
include_once html_paths::SHARED . 'library.php';

use Zukunft\ZukunftCom\main\php\web\result\result;
use Zukunft\ZukunftCom\main\php\web\value\value;
use Zukunft\ZukunftCom\main\php\web\value\value_list;
use Zukunft\ZukunftCom\main\php\shared\calc\arithmetic;
use Zukunft\ZukunftCom\main\php\shared\const\chars;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\library;

class formula_calc
{

    // the decimals compared, the same as the import validation (data_object::CALC_VALIDATION_DECIMALS)
    const int DECIMALS = 2;

    // the kinds of the tokens of a calculation that the browser repeats (see tokens and calculator.js)
    const string TOKEN_NUM = 'n';
    const string TOKEN_OP = 'o';
    const string TOKEN_REF = 'r';
    // one number, one char of the arithmetic or of a condition or a math function between the operands
    const string TOKEN_PATTERN = '/\G\s*(\d+(?:\.\d+)?|[-+*\/()^=&|' . chars::FUNC_ARG_SEPARATOR . ']|'
        . chars::FUNC_MIN . '|' . chars::FUNC_MAX . '|' . chars::FUNC_IF . '|' . chars::FUNC_ABS . '|' . chars::FUNC_COS . '|'
        . chars::FUNC_EXP . '|' . chars::FUNC_LN . '|' . chars::FUNC_LOG . '|' . chars::FUNC_ROUND . '|' . chars::FUNC_SEC . '|'
        . chars::FUNC_SIN . '|' . chars::FUNC_SQRT . '|' . chars::FUNC_SUM . '|' . chars::FUNC_COUNT . '|' . chars::FUNC_MEAN . ')/';

    // per value object the value itself and its phrase names, because each operand of each
    // result is searched in all values of the page, but the phrases of a value do not change;
    // the value is kept with its names, so a new object with a reused object id is not mixed up
    private array $names_by_val = [];

    /**
     * repeat the calculation of a result with the values and results of the page, e.g. the
     * lifetime production from the annual production and the lifetime of the installation
     *
     * @param result $res the stored result with its formula and the phrases of the values used
     * @param value_list $val_lst the values and results of the page, the values first
     * @param formula_list $frm_lst the formulas of the page with their user text
     * @return float|null the number calculated with the values of the page or null if the formula or a value is missing
     */
    function result(result $res, value_list $val_lst, formula_list $frm_lst): ?float
    {
        $result = null;
        $frm_id = $res->frm?->id() ?? 0;
        if ($res->src_grp != null and $frm_id != 0 and $frm_lst->has_id($frm_id)) {
            $frm = $frm_lst->get($frm_id);
            $exp = new library()->str_right_of($frm->get_usr_text(), chars::CHAR_CALC);
            $txt = $this->with_numbers($exp, $res, $val_lst);
            if ($txt !== null) {
                $result = new arithmetic()->calc($txt);
            }
        }
        return $result;
    }

    /**
     * @param result $res a result of the page
     * @param string $phr_name the name of a phrase e.g. PV-weighted consumption mix
     * @param formula_list $frm_lst the formulas of the page with their user text
     * @return bool true if the phrase alone is left of the = of the formula, so the result is the number of the phrase
     *              and not e.g. the emission payback, which only names the phrase as its context
     */
    function calculates_phrase(result $res, string $phr_name, formula_list $frm_lst): bool
    {
        $result = false;
        $frm_id = $res->frm?->id() ?? 0;
        if ($frm_id != 0 and $frm_lst->has_id($frm_id)) {
            $left = new library()->str_left_of($frm_lst->get($frm_id)->get_usr_text(), chars::CHAR_CALC);
            $result = (trim(str_replace(chars::TERM_DELIMITER, '', $left)) == $phr_name);
        }
        return $result;
    }

    /**
     * the calculation of a result as tokens for the browser, which repeats it when an input of a
     * calculator page changes: each operand is the html id of the value or result that the
     * calculation of this page uses for it, so the browser needs no phrase names and never runs
     * a formula text as code
     *
     * @param result $res the stored result with its formula and the phrases of the values used
     * @param value_list $val_lst the values and results of the page, the values first
     * @param formula_list $frm_lst the formulas of the page with their user text
     * @return array|null per token its kind (TOKEN_NUM, TOKEN_OP or TOKEN_REF) and its number, char
     *                    or html id; null if the formula, an operand or the arithmetic is missing
     */
    function tokens(result $res, value_list $val_lst, formula_list $frm_lst): ?array
    {
        $result = null;
        $frm_id = $res->frm?->id() ?? 0;
        if ($res->src_grp != null and $frm_id != 0 and $frm_lst->has_id($frm_id)) {
            $exp = new library()->str_right_of($frm_lst->get($frm_id)->get_usr_text(), chars::CHAR_CALC);
            $result = $this->operand_tokens($exp, $res, $val_lst);
        }
        return $result;
    }

    /**
     * @param string $exp the right part of the formula expression with the phrase names
     * @param result $res the result with the context phrases of the values used
     * @param value_list $val_lst the values and results of the page
     * @return array|null the tokens of the expression or null if an operand or the arithmetic is missing
     */
    private function operand_tokens(string $exp, result $res, value_list $val_lst): ?array
    {
        $ctx = $res->src_grp->phr_lst()->names();
        preg_match_all($this->operand_pattern(), $exp, $found, PREG_OFFSET_CAPTURE);
        $result = $this->lists_are_plain($exp) ? [] : null;
        $pos = 0;
        foreach ($found[0] as [$operand, $start]) {
            $names = array_map('trim', explode(chars::SEPARATOR, str_replace(chars::TERM_DELIMITER, '', $operand)));
            $vals = $this->values_of($names, $ctx, $res, $val_lst, $this->is_list_argument($exp, $start));
            $between = $this->arithmetic_tokens(substr($exp, $pos, $start - $pos));
            if ($result !== null and $vals != [] and $between !== null) {
                $result = [...$result, ...$between];
                foreach ($vals as $key => $val) {
                    if ($key > 0) {
                        $result[] = [self::TOKEN_OP => chars::FUNC_ARG_SEPARATOR];
                    }
                    $result[] = [self::TOKEN_REF => $val->html_id()];
                }
            } else {
                $result = null;
            }
            $pos = $start + strlen($operand);
        }
        $rest = $this->arithmetic_tokens(substr($exp, $pos));
        if ($result !== null and $rest !== null) {
            $result = [...$result, ...$rest];
        } else {
            $result = null;
        }
        return $result;
    }

    /**
     * @param string $txt a part of an expression between two operands e.g. " * 1000 / "
     * @return array|null the numbers and the chars of the arithmetic as tokens or null if the text has another char
     */
    function arithmetic_tokens(string $txt): ?array
    {
        $result = [];
        $pos = 0;
        while (preg_match(self::TOKEN_PATTERN, $txt, $found, 0, $pos)) {
            $pos += strlen($found[0]);
            if (is_numeric($found[1])) {
                $result[] = [self::TOKEN_NUM => (float)$found[1]];
            } else {
                $result[] = [self::TOKEN_OP => $found[1]];
            }
        }
        if (trim(substr($txt, $pos)) != '') {
            $result = null;
        }
        return $result;
    }

    /**
     * replace each phrase name of the expression by the number of the value or result that names
     * the phrases and belongs to the context of the result
     *
     * @param string $exp the right part of the formula expression with the phrase names
     * @param result $res the result with the context phrases of the values used
     * @param value_list $val_lst the values and results of the page
     * @return string|null the expression with numbers or null if a value is missing
     */
    private function with_numbers(string $exp, result $res, value_list $val_lst): ?string
    {
        $ctx = $res->src_grp->phr_lst()->names();
        $missing = !$this->lists_are_plain($exp);
        $txt = preg_replace_callback($this->operand_pattern(),
            function (array $found) use ($exp, $res, $val_lst, $ctx, &$missing): string {
                $names = explode(chars::SEPARATOR, str_replace(chars::TERM_DELIMITER, '', $found[0][0]));
                $is_list = $this->is_list_argument($exp, $found[0][1]);
                $vals = $this->values_of(array_map('trim', $names), $ctx, $res, $val_lst, $is_list);
                if ($vals == []) {
                    $missing = true;
                    return '0';
                }
                $nums = array_map(fn($val) => sprintf('%.12F', $val->number()), $vals);
                return implode(chars::FUNC_ARG_SEPARATOR, $nums);
            }, $exp, -1, $count, PREG_OFFSET_CAPTURE);
        return $missing ? null : $txt;
    }

    /**
     * @param string $exp the right part of the formula expression with the phrase names
     * @return bool true if each list function has only plain operands or numbers as arguments, the same rule as the backend
     */
    private function lists_are_plain(string $exp): bool
    {
        return arithmetic::list_args_are_plain(preg_replace($this->operand_pattern(), 'x', $exp));
    }

    /**
     * @param string $exp the right part of the formula expression with the phrase names
     * @param int $start the position of the operand in the expression
     * @return bool true if the operand is an argument of a list function and stands for all its values
     */
    private function is_list_argument(string $exp, int $start): bool
    {
        $names = implode('|', chars::FUNC_LIST);
        return preg_match('/\b(?:' . $names . ')\s*\([^()]*$/', substr($exp, 0, $start)) === 1;
    }

    /**
     * @param array $names the phrase names of one operand
     * @param array $ctx the phrase names of the context of the result
     * @param result $res the result that is calculated
     * @param value_list $val_lst the values and results of the page
     * @param bool $is_list true for an argument of a list function
     * @return array the one value or result of the operand or, in a list function, all values of it
     */
    private function values_of(array $names, array $ctx, result $res, value_list $val_lst, bool $is_list): array
    {
        if ($is_list) {
            $result = $this->list_values($names, $ctx, $val_lst);
        } else {
            $val = $this->value_of($names, $ctx, $res, $val_lst);
            $result = $val == null ? [] : [$val];
        }
        return $result;
    }

    /**
     * the same selection as the backend (formula::fill_list_element_group): all stored values that
     * name the operand and the context, without a second number of the same fact
     *
     * @param array $names the phrase names of one operand
     * @param array $ctx the phrase names of the context of the result
     * @param value_list $val_lst the values and results of the page
     * @return array the values of the list
     */
    private function list_values(array $names, array $ctx, value_list $val_lst): array
    {
        $result = [];
        foreach ($val_lst->lst() as $val) {
            $phr_names = $this->phrase_names($val);
            $has_all = array_diff([...$names, ...$ctx], $phr_names) == [];
            $is_excluded = array_intersect($phr_names, words::LIST_FUNCTION_EXCLUDED) != [];
            if ($val instanceof value and $has_all and !$is_excluded) {
                $result[] = $val;
            }
        }
        return $result;
    }

    /**
     * @return string the pattern of one operand: a phrase name between the term delimiters,
     *                joined to the next name by the separator if one value needs several phrases
     */
    private function operand_pattern(): string
    {
        $name = preg_quote(chars::TERM_DELIMITER, '/') . '[^' . preg_quote(chars::TERM_DELIMITER, '/') . ']+'
            . preg_quote(chars::TERM_DELIMITER, '/');
        return '/' . $name . '(?:\s*' . preg_quote(chars::SEPARATOR, '/') . '\s*' . $name . ')*/';
    }

    /**
     * the values and results that the calculation of a result uses, found the same way as the
     * calculation finds them, e.g. for the assumptions of a page
     *
     * @param result $res the stored result with its formula and the phrases of the values used
     * @param value_list $val_lst the values and results of the page, the values first
     * @param formula_list $frm_lst the formulas of the page with their user text
     * @return array the values and results used, each once; empty if the formula or the context is missing
     */
    function operands(result $res, value_list $val_lst, formula_list $frm_lst): array
    {
        $result = [];
        $frm_id = $res->frm?->id() ?? 0;
        if ($res->src_grp != null and $frm_id != 0 and $frm_lst->has_id($frm_id)) {
            $exp = new library()->str_right_of($frm_lst->get($frm_id)->get_usr_text(), chars::CHAR_CALC);
            $ctx = $res->src_grp->phr_lst()->names();
            preg_match_all($this->operand_pattern(), $exp, $found, PREG_OFFSET_CAPTURE);
            foreach ($found[0] as [$operand, $start]) {
                $names = array_map('trim', explode(chars::SEPARATOR, str_replace(chars::TERM_DELIMITER, '', $operand)));
                foreach ($this->values_of($names, $ctx, $res, $val_lst, $this->is_list_argument($exp, $start)) as $val) {
                    $result[$val->id()] = $val;
                }
            }
        }
        return $result;
    }

    /**
     * the first value or result of the page that names all given phrases and only phrases of
     * the context; the result that is calculated is never its own operand
     *
     * @param array $names the phrase names of one operand
     * @param array $ctx the phrase names of the context of the result
     * @param result $res the result that is calculated
     * @param value_list $val_lst the values and results of the page
     * @return value|result|null the matching value or result, the values before the results like in the import validation
     */
    private function value_of(array $names, array $ctx, result $res, value_list $val_lst): value|result|null
    {
        foreach ($val_lst->lst() as $val) {
            $phr_names = $this->phrase_names($val);
            $matches = (array_diff($names, $phr_names) == [] and array_diff($phr_names, $ctx) == []);
            if ($val->id() != $res->id() and $matches) {
                return $val;
            }
        }
        return null;
    }

    /**
     * @param value|result $val the value or result of the page
     * @return array the names of the phrases of the value, created once per value object
     */
    private function phrase_names(value|result $val): array
    {
        $key = spl_object_id($val);
        if (!$this->has_names_of($val)) {
            $this->names_by_val[$key] = [$val, $val->phr_lst()->names()];
        }
        return $this->names_by_val[$key][1];
    }

    /**
     * @param value|result $val the value or result of the page
     * @return bool true if the phrase names of this value object are already created
     */
    private function has_names_of(value|result $val): bool
    {
        $result = false;
        $key = spl_object_id($val);
        if (array_key_exists($key, $this->names_by_val)) {
            // the same object and not a new one that got the id of a freed object
            if ($this->names_by_val[$key][0] === $val) {
                $result = true;
            }
        }
        return $result;
    }

}
