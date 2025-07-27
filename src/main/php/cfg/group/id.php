<?php

/*

    model/group/id.php - parent class for the group and result id
    ------------------

    contains the common functions used for creating a group_id or result_id


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

//include_once paths::MODEL_PHRASE . 'phrase_list.php';

use cfg\phrase\phrase_list;

class id
{

    // the max number of int
    const PRIME_PHRASES_STD = 4;

    const CHAR_FORMULA = '=';
    const CHAR_TRIPLE = '-';
    const CHAR_SOURCE_TRIPLE = '(';
    const CHAR_RESULT_TRIPLE = ')';
    const CHAR_WORD = '+';
    const CHAR_SOURCE_WORD = '<';
    const CHAR_RESULT_WORD = '>';

    /**
     * create a 64-bit integer id based on four 16-bit integer ids
     * TODO check that system is running on 64 bit hardware
     *
     * @param array $id_lst list of prime phrase ids or formula id (16 bit) that are used to select a value or result
     * @return int the group or result id based on the given id list as 64-bit integer
     */
    protected function id_lst_to_int(array $id_lst): int
    {
        $bin_key = $this->id_lst_to_bin($id_lst);
        $bin_key = str_pad($bin_key, 64, '0', STR_PAD_LEFT);
        if (substr($bin_key, 0, 1) == 1) {
            log_err('Integer size on this system is not the expected 64 bit');
        }
        return (int)bindec($bin_key);
    }

    /**
     * create a binary string based on an int list
     *
     * @param array $id_lst list of prime phrase ids or formula id (8 bit) that are used to select a value or result
     * @return string the group or result id based on the given id list as 64-bit integer
     */
    private function id_lst_to_bin(array $id_lst): string
    {
        $keys = [];
        foreach ($id_lst as $id) {
            $key = str_pad(decbin(abs($id)), 15, '0', STR_PAD_LEFT);
            if ($id < 0) {
                $key = '1' . $key;
            } else {
                $key = '0' . $key;
            }
            $keys[] = $key;
        }
        while (count($keys) < self::PRIME_PHRASES_STD) {
            array_unshift($keys, str_repeat('0', 16));
        }
        return implode('', $keys);
    }

    /**
     * create the database key for a phrase group
     * @param phrase_list $phr_lst list of words or triples
     * @param bool $fill true if a 512-bit key should be created
     * @return string the 512-bit db key of up to 16 32 bit phrase ids in alpha_num format
     */
    protected function alpha_num(phrase_list $phr_lst, bool $fill = true): string
    {
        $db_key = '';
        $i = 16;
        foreach ($phr_lst->lst() as $phr) {
            $db_key .= $this->int2alpha_num($phr->id());
            $i--;
        }
        // fill the remaining key entries with zero keys to always have the same key size
        if ($fill) {
            while ($i > 0) {
                $db_key .= $this->int2alpha_num(0);
                $i--;
            }
        }
        return $db_key;
    }

    /**
     * @param int $id a phrase id
     * @return string a 6 char db key of a 32 bit phrase id in alpha_num format e.g. "3'082'113" ist "...AkS/"
     */
    function int2alpha_num(int $id, bool $is_src = false, bool $is_res = false, bool $is_frm = false): string
    {
        $i = 6;
        $chars = [];
        if ($is_frm) {
            $chars[] = self::CHAR_FORMULA;
        } else {
            if ($id < 0) {
                if ($is_src) {
                    $chars[] = self::CHAR_SOURCE_TRIPLE;
                } else {
                    if ($is_res) {
                        $chars[] = self::CHAR_RESULT_TRIPLE;
                    } else {
                        $chars[] = self::CHAR_TRIPLE;
                    }
                }
                $id = abs($id);
            } else {
                if ($is_src) {
                    $chars[] = self::CHAR_SOURCE_WORD;
                } else {
                    if ($is_res) {
                        $chars[] = self::CHAR_RESULT_WORD;
                    } else {
                        $chars[] = self::CHAR_WORD;
                    }
                }
            }
        }
        while ($i > 0 and $id > 0) {
            if ($id < 64) {
                $chars[] = $this->int2char($id);
                $id = 0;
            } else {
                $chars[] = $this->int2char((int)($id % 64));
                $id = (int)($id / 64);
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
     * using
     *
     * @param int $id the integer value to convert
     * @return string the alpha_num change e.g. "." for 0, "A" for 12, "a" for 38 and "z" for 63
     */
    private function int2char(int $id): string
    {
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

}