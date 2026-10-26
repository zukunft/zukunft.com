<?php

/*

    web/helper/number_format.php - the unit and the decimals that a group of numbers is shown with
    ----------------------------

    The numbers of one group e.g. the kg results of one table column share the unit and the
    decimals, so that they can be compared at a glance: the largest number decides; if it has more
    integer digits than the target, it is converted to a larger unit while a preloaded formula
    converts the unit e.g. "kg" = "tonne" * 1000, and if it is below one to a smaller unit while
    the number keeps the target digits; the decimals are the target digits minus the integer
    digits of the largest number, e.g. with 4 digits 10'482 kg, 2'000 kg and 113'805 kg are
    shown as 10.5 t, 2.0 t and 113.8 t
    var name: $fmt


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

namespace Zukunft\ZukunftCom\main\php\web\helper;

use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::FORMULA . 'formula.php';
include_once html_paths::FORMULA . 'formula_list.php';
include_once html_paths::PHRASE . 'phrase.php';
include_once html_paths::PHRASE . 'phrase_list.php';
include_once html_paths::USER . 'user_message.php';
include_once html_paths::SHARED_CONST . 'chars.php';
include_once html_paths::SHARED_HELPER . 'Config.php';

use Zukunft\ZukunftCom\main\php\web\formula\formula;
use Zukunft\ZukunftCom\main\php\web\formula\formula_list;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\chars;
use Zukunft\ZukunftCom\main\php\shared\helper\Config as shared_config;

class number_format
{

    // a formula that converts a unit by a constant factor e.g. "kg" = "tonne" * 1000
    const string CONVERSION_PATTERN = '/^\s*"([^"]+)"\s*=\s*"([^"]+)"\s*([*\/])\s*([0-9]+(?:\.[0-9]+)?)\s*$/';

    // the unit that the numbers are shown in; null if the numbers are shown without converting the unit
    public ?phrase $unit = null;
    // the factor from the stored to the shown number e.g. 0.001 from kg to tonne
    public float $factor = 1.0;
    // the decimals of every number of the group e.g. 1 for 113.8 t
    public int $decimals = 0;

    // the formulas that convert the units and the phrases they name
    private ?formula_list $frm_lst;
    private ?phrase_list $phr_lst;

    /**
     * @param formula_list|null $frm_lst the formulas that convert the units; null for the preloaded ones
     * @param phrase_list|null $phr_lst the phrases that the formulas name e.g. "tonne"; null for the preloaded ones
     */
    function __construct(?formula_list $frm_lst = null, ?phrase_list $phr_lst = null)
    {
        global $ui_sys;
        $this->frm_lst = $frm_lst ?? $ui_sys?->typ_lst_cache?->frm_sys;
        $this->phr_lst = $phr_lst ?? $ui_sys?->typ_lst_cache?->phr_sys;
    }

    /**
     * decide the unit, the factor and the decimals of a group of numbers
     *
     * @param array $numbers the stored numbers of the group e.g. the kg results of a table column
     * @param phrase|null $unit the unit of the numbers; null keeps the unit e.g. if the table header names it
     * @param int $digits the target number of digits e.g. 4 (config.yaml number format > digits)
     * @param user_message $msg to report a problem of the phrase lookup
     * @return number_format this format to chain the call
     */
    function group(array $numbers, ?phrase $unit, int $digits, user_message $msg): number_format
    {
        $max = 0.0;
        foreach ($numbers as $num) {
            $max = max($max, abs((float)$num));
        }
        $this->unit = $unit;
        $this->factor = 1.0;
        if ($unit != null and $max > 0) {
            $cnv = $this->next_unit($max, $digits, $msg);
            while ($cnv != null) {
                $this->unit = $cnv[0];
                $this->factor = $this->factor * $cnv[1];
                $cnv = $this->next_unit($max, $digits, $msg);
            }
        }
        $this->decimals = 0;
        if ($max > 0) {
            $this->decimals = max(0, $digits - $this->magnitude($max * $this->factor));
        }
        return $this;
    }

    /**
     * @param float $num a stored number of the group
     * @param string $dec_point the decimal point of the user
     * @param string $thousand_sep the thousand separator of the user
     * @return string the number in the unit and with the decimals of the group e.g. "10.5" for 10'482 kg shown in tonne
     */
    function text(
        float  $num,
        string $dec_point = shared_config::DEFAULT_DEC_POINT,
        string $thousand_sep = shared_config::DEFAULT_THOUSAND_SEP
    ): string
    {
        return number_format($num * $this->factor, $this->decimals, $dec_point, $thousand_sep);
    }

    /**
     * @param user_message $msg to report a problem of the symbol lookup
     * @return string the unit shown behind the numbers e.g. "t" for tonne or an empty string if the group has no unit
     */
    function unit_text(user_message $msg): string
    {
        return $this->unit?->unit_text($msg) ?? '';
    }

    /**
     * the conversion that brings the largest number of the group closer to the target digits: to a
     * larger unit if the number has more integer digits than the target, to a smaller one if it is
     * below one and keeps the target digits; the nearest unit is taken, so that e.g. gram is
     * converted to kg before tonne
     *
     * @param float $max the largest stored number of the group
     * @param int $digits the target number of digits
     * @param user_message $msg to report a problem of the phrase lookup
     * @return array|null the unit phrase and the factor of the conversion or null if no conversion fits
     */
    private function next_unit(float $max, int $digits, user_message $msg): ?array
    {
        $num = $max * $this->factor;
        $too_big = $this->magnitude($num) > $digits;
        $too_small = $num < 1;
        $result = null;
        foreach ($this->conversions($msg) as $cnv) {
            $new = $num * $cnv[1];
            $to_larger = ($too_big and $cnv[1] < 1 and $new >= 1);
            $to_smaller = ($too_small and $cnv[1] > 1 and $this->magnitude($new) <= $digits);
            $fits = ($to_larger or $to_smaller);
            if ($fits and ($result == null or $this->is_nearer($cnv[1], $result[1]))) {
                $result = $cnv;
            }
        }
        return $result;
    }

    /**
     * @param user_message $msg to report a problem of the phrase lookup
     * @return array the units that the unit of the group can be converted to with the factor of each e.g. [[tonne, 0.001]]
     */
    private function conversions(user_message $msg): array
    {
        $result = [];
        foreach ($this->frm_lst?->lst() ?? [] as $frm) {
            $cnv = $this->conversion($frm, $msg);
            if ($cnv != null) {
                $result[] = $cnv;
            }
        }
        return $result;
    }

    /**
     * @param formula $frm a preloaded formula e.g. "kg" = "tonne" * 1000
     * @param user_message $msg to report a problem of the phrase lookup
     * @return array|null the other unit and the factor to it if the formula converts the unit of the group e.g. [tonne, 0.001] for kg
     */
    private function conversion(formula $frm, user_message $msg): ?array
    {
        $result = null;
        $parts = $this->conversion_parts($frm);
        if ($parts != null and $this->is_unit($parts[0])) {
            $result = $this->unit_factor($parts[1], 1 / $parts[2], $msg);
        } elseif ($parts != null and $this->is_unit($parts[1])) {
            $result = $this->unit_factor($parts[0], $parts[2], $msg);
        }
        return $result;
    }

    /**
     * @param formula $frm a preloaded formula e.g. "kg" = "tonne" * 1000
     * @return array|null the name of the result unit, of the source unit and the positive factor k
     *                    of "res" = "src" * k, which says that one src is k res, e.g. ["kg", "tonne", 1000]
     */
    private function conversion_parts(formula $frm): ?array
    {
        $result = null;
        if (preg_match(self::CONVERSION_PATTERN, $frm->get_usr_text() ?? '', $part) === 1) {
            $k = (float)$part[4];
            if ($part[3] == chars::DIV and $k != 0.0) {
                $k = 1 / $k;
            }
            if ($k > 0) {
                $result = [$part[1], $part[2], $k];
            }
        }
        return $result;
    }

    /**
     * every unit conversion of the preloaded formulas in both directions, so that a script can
     * convert a group of numbers like group() without the formulas
     *
     * @param user_message $msg to report a problem of the phrase lookup
     * @return array per conversion the name of the unit, the name and the shown text of the other
     *               unit and the factor to it e.g. [["kg", "tonne", "t", 0.001], ["tonne", "kg", "kg", 1000]]
     */
    function unit_conversions(user_message $msg): array
    {
        $result = [];
        foreach ($this->frm_lst?->lst() ?? [] as $frm) {
            $parts = $this->conversion_parts($frm);
            if ($parts != null) {
                $res = $this->phr_lst?->get_by_name($parts[0], $msg);
                $src = $this->phr_lst?->get_by_name($parts[1], $msg);
                if ($res instanceof phrase and $src instanceof phrase) {
                    $result[] = [$parts[0], $parts[1], $src->unit_text($msg), 1 / $parts[2]];
                    $result[] = [$parts[1], $parts[0], $res->unit_text($msg), $parts[2]];
                }
            }
        }
        return $result;
    }

    /**
     * @param string $name the name of the other unit in the formula e.g. "tonne"
     * @param float $factor the factor from the unit of the group to the other unit
     * @param user_message $msg to report a problem of the phrase lookup
     * @return array|null the other unit and the factor or null if the phrase of the name is not preloaded
     */
    private function unit_factor(string $name, float $factor, user_message $msg): ?array
    {
        $phr = $this->phr_lst?->get_by_name($name, $msg);
        $result = null;
        if ($phr instanceof phrase) {
            $result = [$phr, $factor];
        }
        return $result;
    }

    /**
     * @param string $name a unit name of a formula e.g. "kg"
     * @return bool true if the name is the current unit of the group or the phrase it is a symbol for e.g. "kg" for "kilogram"
     */
    private function is_unit(string $name): bool
    {
        return ($name == $this->unit?->name() or $name == $this->unit?->symbol_owner()->name());
    }

    /**
     * @param float $factor the factor of a conversion
     * @param float $other the factor of the conversion found until now
     * @return bool true if the conversion changes the number less, so that the unit is the nearer one
     */
    private function is_nearer(float $factor, float $other): bool
    {
        return abs(log($factor)) < abs(log($other));
    }

    /**
     * @param float $num a positive number
     * @return int the number of integer digits e.g. 3 for 113.8, and below one zero or minus the zeros behind the point e.g. -1 for 0.049
     */
    private function magnitude(float $num): int
    {
        return (int)floor(log10($num)) + 1;
    }

}
