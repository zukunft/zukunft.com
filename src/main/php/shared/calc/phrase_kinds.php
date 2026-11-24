<?php

/*

    shared/calc/phrase_kinds.php - the kinds of the phrases of a number for a list function
    ----------------------------

    a list function takes a number for a phrase of its operand or of its context also if the
    number has a phrase that is a kind of it, over several levels of "is a", e.g. the walk row of
    LISP for the stocks, because LISP is a stock; the backend (formula::list_numbers), the page
    (formula_calc::list_values, calculator_json.js listEntries) and the import
    (data_object::list_numbers) select the numbers of a list by this rule

    The main sections of this object are
    - construct and map: the "is a" links of the data
    - select:            whether a number has the wanted phrases


    zukunft.com - calc with words

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
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2026 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\main\php\shared\calc;

/**
 * suggested var name: $knd
 */
class phrase_kinds
{

    /*
     * construct and map
     */

    // per phrase name the names of the phrases that it is a kind of e.g. stock for LISP
    private array $parents;

    /**
     * @param array $parents per phrase name the names of the phrases that it is a kind of (see with_link)
     */
    function __construct(array $parents = [])
    {
        $this->parents = $parents;
    }

    /**
     * @param array $parents per phrase name the names of the phrases that it is a kind of
     * @param string $from the name of the phrase that is a kind e.g. LISP
     * @param string $to the name of its kind e.g. stock
     * @return array the kinds with the link, a second same link only once
     */
    static function with_link(array $parents, string $from, string $to): array
    {
        $kinds = $parents[$from] ?? [];
        if (!in_array($to, $kinds, true)) {
            $parents[$from] = [...$kinds, $to];
        }
        return $parents;
    }


    /*
     * select
     */

    /**
     * @param array $names the phrase names of a number e.g. LISP, received value and first child portfolio
     * @param array $wanted the phrase names that the number must have e.g. stock and first child portfolio
     * @return bool true if the number has each wanted phrase or a phrase that is a kind of it
     */
    function has_all(array $names, array $wanted): bool
    {
        return array_diff($wanted, $this->with_kinds($names)) == [];
    }

    /**
     * @param array $names the phrase names of a number
     * @return array the names and the names of every phrase that they are a kind of, over all levels,
     *               each once, so that a loop of links in the data ends
     */
    private function with_kinds(array $names): array
    {
        $result = $names;
        $open = $names;
        while ($open != []) {
            $name = array_shift($open);
            foreach ($this->parents[$name] ?? [] as $kind) {
                if (!in_array($kind, $result, true)) {
                    $result[] = $kind;
                    $open[] = $kind;
                }
            }
        }
        return $result;
    }

}
