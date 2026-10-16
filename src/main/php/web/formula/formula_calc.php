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
include_once html_paths::SHARED . 'library.php';

use Zukunft\ZukunftCom\main\php\web\result\result;
use Zukunft\ZukunftCom\main\php\web\value\value;
use Zukunft\ZukunftCom\main\php\web\value\value_list;
use Zukunft\ZukunftCom\main\php\shared\calc\arithmetic;
use Zukunft\ZukunftCom\main\php\shared\const\chars;
use Zukunft\ZukunftCom\main\php\shared\library;

class formula_calc
{

    // the decimals compared, the same as the import validation (data_object::CALC_VALIDATION_DECIMALS)
    const int DECIMALS = 2;

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
        $missing = false;
        $txt = preg_replace_callback($this->operand_pattern(),
            function (array $found) use ($res, $val_lst, $ctx, &$missing): string {
                $names = explode(chars::SEPARATOR, str_replace(chars::TERM_DELIMITER, '', $found[0]));
                $num = $this->number_of(array_map('trim', $names), $ctx, $res, $val_lst);
                if ($num === null) {
                    $missing = true;
                    return '0';
                }
                return sprintf('%.12F', $num);
            }, $exp);
        return $missing ? null : $txt;
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
     * the first value or result of the page that names all given phrases and only phrases of
     * the context, the values before the results like in the import validation; the result that
     * is calculated is never its own operand
     *
     * @param array $names the phrase names of one operand
     * @param array $ctx the phrase names of the context of the result
     * @param result $res the result that is calculated
     * @param value_list $val_lst the values and results of the page
     * @return float|null the number of the matching value or null if none matches
     */
    private function number_of(array $names, array $ctx, result $res, value_list $val_lst): ?float
    {
        return $this->value_of($names, $ctx, $res, $val_lst)?->number();
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
            preg_match_all($this->operand_pattern(), $exp, $found);
            foreach ($found[0] as $operand) {
                $names = array_map('trim', explode(chars::SEPARATOR, str_replace(chars::TERM_DELIMITER, '', $operand)));
                $val = $this->value_of($names, $ctx, $res, $val_lst);
                if ($val != null) {
                    $result[$val->id()] = $val;
                }
            }
        }
        return $result;
    }

    /**
     * @param array $names the phrase names of one operand
     * @param array $ctx the phrase names of the context of the result
     * @param result $res the result that is calculated
     * @param value_list $val_lst the values and results of the page
     * @return value|result|null the matching value or result, the values before the results like in the import validation
     */
    private function value_of(array $names, array $ctx, result $res, value_list $val_lst): value|result|null
    {
        foreach ($val_lst->lst() as $val) {
            $phr_names = $val->phr_lst()->names();
            $matches = (array_diff($names, $phr_names) == [] and array_diff($phr_names, $ctx) == []);
            if ($val->id() != $res->id() and $matches) {
                return $val;
            }
        }
        return null;
    }

}
