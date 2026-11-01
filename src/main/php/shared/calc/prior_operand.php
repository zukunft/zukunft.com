<?php

/*

    shared/calc/prior_operand.php - the operand of a formula that reads the number of the previous row
    -----------------------------

    an operand with the name prior next to another phrase e.g. "cumulative deviation","prior" reads
    the number of the phrase before the phrase of the context in its "is follower of" chain, so that
    a walk carries a number from one row into the next e.g. from LISP to LISN; the first phrase of a
    chain has no previous row and the number 0; a plain "prior" is the time jump of the backend

    The main sections of this object are
    - construct and map: the previous phrases of the data
    - select:            the names and the context that select the number of the operand


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

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::SHARED_TYPES . 'phrase_types.php';

use Zukunft\ZukunftCom\main\php\shared\types\phrase_types;

/**
 * suggested var name: $pri
 */
class prior_operand
{

    // the previous phrase of a phrase with two different previous phrases, because then the previous row is not clear
    const string UNCLEAR = '';

    /*
     *  construct and map
     */

    /**
     * @param array $prior per phrase name the name of the phrase before it in its "is follower of" chain
     */
    function __construct(private readonly array $prior = [])
    {
    }

    /**
     * add one "is follower of" link independent of the order of the links
     *
     * @param array $prior per phrase name the name of the phrase before it
     * @param string $from the name of the phrase that follows
     * @param string $to the name of the phrase before it
     * @return array the previous phrases with the link, self::UNCLEAR for a phrase with two different previous phrases
     */
    static function with_link(array $prior, string $from, string $to): array
    {
        $is_other = (array_key_exists($from, $prior) and $prior[$from] != $to);
        $prior[$from] = $is_other ? self::UNCLEAR : $to;
        return $prior;
    }


    /*
     * select
     */

    /**
     * @param array $names the phrase names of one operand
     * @param array $ctx the phrase names of the context of the result
     * @return array the names and the context that select the number of the operand: the name prior
     *               replaced by the previous phrase, else as given, which then selects no number
     */
    function select(array $names, array $ctx): array
    {
        $phr = $this->chain_phrase($names, $ctx);
        $result = [$names, $ctx];
        if ($phr != '' and array_key_exists($phr, $this->prior)) {
            $before = $this->prior[$phr];
            $result = [
                [...array_diff($names, [phrase_types::PRIOR_NAME]), $before],
                [...array_diff($ctx, [$phr]), $before]
            ];
        }
        return $result;
    }

    /**
     * @param array $names the phrase names of one operand
     * @param array $ctx the phrase names of the context of the result
     * @return bool true if the operand reads the previous row of the first row, which is the number 0
     */
    function is_first(array $names, array $ctx): bool
    {
        $phr = $this->chain_phrase($names, $ctx);
        return ($phr != '' and !array_key_exists($phr, $this->prior));
    }

    /**
     * @param array $names the phrase names of one operand
     * @param array $ctx the phrase names of the context of the result
     * @return string the one phrase of the context that is part of a chain, '' for another operand
     *                or if several phrases are or the phrase has several previous phrases,
     *                because then the previous row is not clear
     */
    private function chain_phrase(array $names, array $ctx): string
    {
        $result = '';
        if (count($names) > 1 and in_array(phrase_types::PRIOR_NAME, $names)) {
            $members = [...array_keys($this->prior), ...array_values($this->prior)];
            $found = array_values(array_intersect($ctx, $members));
            if (count($found) == 1 and ($this->prior[$found[0]] ?? null) !== self::UNCLEAR) {
                $result = $found[0];
            }
        }
        return $result;
    }

}
