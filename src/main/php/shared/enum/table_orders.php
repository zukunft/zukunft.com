<?php

/*

    shared/enum/table_orders.php - the conditions by which the rows of a value table are sorted
    ----------------------------

    asked for by the url of the page (url_var::DISPLAY_LIST_ORDER and the two sub orders): each
    url value names the phrase id of a column of the table and the condition, e.g. "123.numeric_desc"
    for the biggest number of the column of the phrase 123 first; a row without a key in that
    column is behind the rows with a key for both directions and the rows that every order of
    the url leaves equal keep the impact order


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

namespace Zukunft\ZukunftCom\main\php\shared\enum;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::SHARED . 'url_var.php';

use Zukunft\ZukunftCom\main\php\shared\url_var;

enum table_orders: string
{
    // by the number of the cell
    case NUMERIC_ASC = 'numeric_asc';
    case NUMERIC_DESC = 'numeric_desc';
    // by the text of the cell: the name of the phrase or the text of a text value
    case ALPHA_ASC = 'alpha_asc';
    case ALPHA_DESC = 'alpha_desc';
    // by the names of the parent phrases followed by the name of the phrase, so that the rows
    // are grouped by their parent e.g. the solutions by their problem
    case ALPHA_PARENT_ASC = 'alpha_parent_asc';
    case ALPHA_PARENT_DESC = 'alpha_parent_desc';

    // the char between the phrase id of the column and the condition in the url value
    const string ID_SEP = '.';

    // the url vars of the orders, the prime order first
    const array URL_VARS = [
        url_var::DISPLAY_LIST_ORDER,
        url_var::DISPLAY_LIST_ORDER_SUB,
        url_var::DISPLAY_LIST_ORDER_SUB_SUB,
    ];

    /**
     * @param string $url_value the url value of one order e.g. "123.numeric_desc"
     * @return array the phrase id of the column and the condition, 0 and null if the value names no order
     */
    static function parse(string $url_value): array
    {
        [$id, $cond] = array_pad(explode(self::ID_SEP, $url_value, 2), 2, '');
        $phr_id = filter_var($id, FILTER_VALIDATE_INT);
        return [$phr_id === false ? 0 : $phr_id, self::tryFrom($cond)];
    }

    /**
     * @param int $phr_id the phrase id of the column
     * @return string the url value that asks for this order of the column e.g. "123.numeric_desc"
     */
    function url_value(int $phr_id): string
    {
        return $phr_id . self::ID_SEP . $this->value;
    }

    /**
     * @return bool true if the rows are sorted by the number of the cell and not by its text
     */
    function is_numeric(): bool
    {
        return in_array($this, [self::NUMERIC_ASC, self::NUMERIC_DESC]);
    }

    /**
     * @return bool true if the names of the parent phrases are part of the text of the cell
     */
    function with_parent(): bool
    {
        return in_array($this, [self::ALPHA_PARENT_ASC, self::ALPHA_PARENT_DESC]);
    }

    /**
     * @return bool true if the biggest number or the last name comes first
     */
    function is_desc(): bool
    {
        return in_array($this, [self::NUMERIC_DESC, self::ALPHA_DESC, self::ALPHA_PARENT_DESC]);
    }

    /**
     * compare the sort keys of two rows like usort expects it
     *
     * @param float|string|null $a the key of the first row, null if its cell has none
     * @param float|string|null $b the key of the second row, null if its cell has none
     * @return int negative if the first row comes first, positive if the second, 0 if the order leaves them equal
     */
    function compare(float|string|null $a, float|string|null $b): int
    {
        if ($a === null or $b === null) {
            // a row without a key is behind the rows with a key for both directions
            $result = ($a === null) <=> ($b === null);
        } else {
            $result = is_string($a) ? strcasecmp($a, $b) : $a <=> $b;
            if ($this->is_desc()) {
                $result = -$result;
            }
        }
        return $result;
    }

}
