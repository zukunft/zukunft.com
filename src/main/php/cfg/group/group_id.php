<?php

/*

    model/group/group_id.php - e.g. to create a group_id based on a phrase list
    ------------------------

    there are there group id formats for speed- and space-saving:

    1. for up to two phrases a 64 bit bigint key is used
       this allows fast and efficient saving for many number
    2. for up the 16 phrases a 512 bit db key is used,
       which is shown using the chars . for 0, / for 1 and 0 to 9, A to Z and a to z
    3. if the phrase id is 64 bit or more than 16 phrases are used a alpha_num text is used for the db key

    base on the three db key types three value tables are used:
    1. value_quick with the 64 bit bigint key
    2. value with the 512 bit db key
    1. value_slow with the text key for many phrases

    the group id can include the order of the phrases
    and an alpha_num db key can be converted into a sorted array of phrase ids
    this has the advantage that no separate table for the group is needed,
    unless a user changed the name of the group or added a description

    TODO move the 32k most often used phrases to a phrase_most view
    TODO use a 8 byte key for up to 4 most often used phrase group


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

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com
  
*/

namespace cfg\group;

use cfg\phrase_list;

class group_id
{

    /**
     * create the database key for a phrase group
     * TODO if the phrase list contains only 1 or 2 phrase use a 64 bit bigint number as key
     * @param phrase_list $phr_lst list of words or triples
     * @return string the 512 bit db key of up to 16 32 bit phrase ids in alpha_num format
     */
    function alpha_num(phrase_list $phr_lst): string
    {
        $db_key = '';
        if ($phr_lst->count() <= 1) {
            foreach ($phr_lst->lst() as $phr) {
                $db_key .= $phr->id();
            }
        } elseif ($phr_lst->count() <= 16) {
            $i = 16;
            foreach ($phr_lst->lst() as $phr) {
                $db_key .= $this->int2alpha_num($phr->id());
                $i--;
            }
            // fill the remaining key entries with zero keys to always have the same key size
            while ($i > 0) {
                $db_key .= $this->int2alpha_num(0);
                $i--;
            }
        } else {
            foreach ($phr_lst->lst() as $phr) {
                $db_key .= $this->int2alpha_num($phr->id());
            }
        }
        return $db_key;
    }

    /**
     * @param string $grp_id
     * @return array
     */
    function int_array(string $grp_id): array
    {
        $result = [];
        $signs = array_values(array_filter(str_split($grp_id), fn($value) => $value == '+' || $value == '-'));
        $id_keys = preg_split("/[+-]/", $grp_id);
        foreach ($id_keys as $key => $id_key) {
            $id = $this->alpha_num2int($id_key);
            if ($id != 0) {
                if ($signs[$key] == '-') {
                    $result[] .= $id * -1;
                } else {
                    $result[] .= $id;
                }
            }
        }
        return $result;
    }

    /**
     * @param int $id a phrase id
     * @return string a 6 char db key of a 32 bit phrase id in alpha_num format e.g. "3'082'113" ist "..AkS/"
     */
    private function int2alpha_num(int $id): string
    {
        $i = 6;
        $chars = [];
        if ($id < 0) {
            $chars[] = '-';
            $id = abs($id);
        } else {
            $chars[] = '+';
        }
        while ($i > 0 and $id > 0) {
            if ($id < 64) {
                $chars[] = $this->int2char($id);
                $id = 0;
            } else {
                $chars[] = $this->int2char($id % 64);
                $id = $id / 64;
            }
            $i--;
        }
        // fill the remaining key chars with zero keys to always have the same key size
        while ($i > 0) {
            $chars[] = $this->int2char(0);
            $i--;
        }
        return implode('', array_reverse($chars));
    }

    /**
     * converts an integer to an alpha_num char
     * @param int $id the integer value to convert
     * @return string the alpha_num change e.g. "." for 0, "A" for 12, "a" for 38 and "z" for 63
     */
    private function int2char(int $id): string
    {
        $char = '';
        if ($id < 12) {
            $char = chr($id + 46);
        } else {
            if ($id < 38) {
                $char = chr($id + 53);
            } else {
                $char = chr($id + 59);
            }
        }
        return $char;
    }

    private function alpha_num2int(string $key): int
    {
        $result = 0;
        while ($key != '') {
            $result = $result * 64;
            $digit = ord($key[0]);
            if ($digit < 46 + 12) {
                $digit = $digit - 46;
            } elseif ($digit < 53 + 38) {
                $digit = $digit - 53;
            } else {
                $digit = $digit - 59;
            }
            $result = $result + $digit;
            $key =  substr($key, 1);
        }
        return $result;
    }

}