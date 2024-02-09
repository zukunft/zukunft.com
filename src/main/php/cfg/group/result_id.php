<?php

/*

    model/group/result_id.php - e.g. to create a id based on a mix of source, result or both phrases and the formula id
    -------------------------

    there are four result id formats for speed- and space-saving:

    1. for up to two prime phrases, one result phrase and a formula id with a 16 bit integer id
       a 64 bit bigint key is used for fast and efficient saving for some results
       TODO if the adds a phrase to the result use the formula to get this phrase
            e.g. if the increase formula is assigned to inhabitants
                 the results have the phrases city, inhabitants and 2020
                 for the source and the result
                 the words percent and increase should be taken from the increase formula
                 because the formulas can be cached in the frontend
                 so the frontend can and the phrases without interaction to the backend
       TODO reduce the length of the formula id e.g. to 10 bit
            and use 10 bit for the result phrase
            and 12, 16 and 16 bit for the source and result phrases
            to use a 64 bit key as much as possible
            and add one more phrase in the selection
    2. for up to five prime phrases, one result and one source only phrase
       and a formula id with a 16 bit integer id
       a 128 bit bigint key is used for fast and efficient saving for most results
    3. for up the 15 phrases (source, result or both) with a 32 bit integer id and a 32 bit formula integer id
       a 512 bit db key is used, which is shown using the chars . for 0, / for 1 and 0 to 9, A to Z and a to z
    4. if the phrase list contains an id with 64 bit or more than 16 phrases are used
       a alpha_num text is used for the db key

    base on the four db key types four result tables are used:
    1. results_prime with the 64 bit bigint key
    1. results_main with the 128 bit bigint key
    2. results with the 512 bit db key
    1. results_big with the text key for many phrases

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

use cfg\db\sql_table_type;
use cfg\formula;
use cfg\phrase_list;

class result_id
{

    /*
     * database link
     */

    // the database table name extensions
    const TBL_EXT_PRIME = '_prime'; // the table name extension for up to four prime phrase ids
    const TBL_EXT_MAIN = '_main'; // the table name extension for up to four prime phrase ids
    const TBL_EXT_BIG = '_big'; // the table name extension for more than 16 phrase ids
    const TBL_EXT_PHRASE_ID = '_p'; // the table name extension with the number of phrases for up to four prime phrase ids
    const PRIME_PHRASES = 3;
    const PRIME_SOURCE_PHRASES = 0;
    const PRIME_RESULT_PHRASES = 0;
    const MAIN_PHRASES = 5;
    const MAIN_SOURCE_PHRASES = 1;
    const MAIN_RESULT_PHRASES = 1;
    const STANDARD_PHRASES = 15;

    /**
     * @param phrase_list $phr_lst the list of phrases that define the result
     * @param phrase_list $src_phr_lst the list of phrases that selects tha values for the formula
     * @param formula $frm the formula used to calculate the result
     * @return int|string the group id based on the given phrase list
     *                    as 64-bit integer, 512-bit key as 112 chars or list of more than 16 keys with 6 chars
     */
    function get_id(phrase_list $phr_lst, phrase_list $src_phr_lst, formula $frm): int|string
    {
        // get the phrases used only for the result
        $res_only = $phr_lst->get_diff($src_phr_lst);
        $res_only = $res_only->sort_by_id();
        // get the phrases used only for the source
        $src_only = $src_phr_lst->get_diff($phr_lst);
        $src_only = $src_only->sort_by_id();
        // get the phrases used for source and result
        $all_lst = $phr_lst->merge($src_phr_lst);
        $only_lst = $res_only->merge($src_only);
        $both_lst = $all_lst->get_diff($only_lst);
        $both_lst = $both_lst->sort_by_id();

        if ($both_lst->count() <= self::PRIME_PHRASES
            and $src_only->count() <= self::PRIME_SOURCE_PHRASES
            and $res_only->count() <= self::PRIME_RESULT_PHRASES
            and $all_lst->prime_only()) {
            $db_key = $this->int_group_id($both_lst, $res_only, $frm);
        } elseif ($both_lst->count() <= self::MAIN_PHRASES
            and $src_only->count() <= self::MAIN_SOURCE_PHRASES
            and $res_only->count() <= self::MAIN_RESULT_PHRASES
            and $all_lst->prime_only()) {
            $db_key = $this->num_text_group_id($both_lst, $res_only, $src_only, $frm);
        } elseif ($phr_lst->count() <= self::STANDARD_PHRASES) {
            $db_key = $this->alpha_num($phr_lst);
        } else {
            $db_key = $this->alpha_num_big($phr_lst);
        }
        return $db_key;
    }

    /**
     * TODO check that system is running on 64 bit hardware
     * @param phrase_list $both_lst list of words or triples that are used to select the source and result
     * @param phrase_list $res_lst list of words or triples that are specific for the result
     * @return int the group id based on the given phrase list as 64-bit integer
     */
    private function int_group_id(phrase_list $both_lst, phrase_list $res_lst, formula $frm): int
    {
        $keys = [];
        $id_lst = [];
        $id_lst[] = $frm->id();
        foreach ($both_lst->lst() as $phr) {
            $id_lst[] = $phr->id();
        }
        // fill the missing phrase id with zero to have the result phrases always staring at the same place
        // plus one for the formula phrase
        while (count($id_lst) < self::PRIME_PHRASES + 1) {
            $id_lst[] = 0;
        }
        foreach ($res_lst->lst() as $phr) {
            $id_lst[] = $phr->id();
        }
        foreach ($id_lst as $id) {
            $key = str_pad(decbin(abs($id)), 15, '0', STR_PAD_LEFT);
            if ($id < 0) {
                $key = $key . '1';
            } else {
                $key = $key . '0';
            }
            $keys[] = $key;
        }
        while (count($keys) < self::PRIME_PHRASES) {
            array_unshift($keys, str_repeat('0', 16));
        }
        $bin_key = implode('', $keys);
        $bin_key = str_pad($bin_key, 64, '0', STR_PAD_LEFT);
        $result = (int)bindec($bin_key);
        if ($result > PHP_INT_MAX or $result < PHP_INT_MIN) {
            log_err('Integer size on this system is not the expected 64 bit');
        }
        return $result;
    }

    /**
     * TODO check that system is running on 64 bit hardware
     * @param phrase_list $both_lst list of words or triples that are used to select the source and result
     * @param phrase_list $res_lst list of words or triples that are specific for the result
     * @param phrase_list $src_lst list of words or triples that are specific for the source value
     * @return string the group id based on the given phrase list as 128-bit integer
     */
    private function num_text_group_id(phrase_list $both_lst, phrase_list $res_lst, phrase_list $src_lst, formula $frm): string
    {
        $keys = [];
        $id_lst = [];
        $id_lst[] = $frm->id();
        foreach ($both_lst->lst() as $phr) {
            $id_lst[] = $phr->id();
        }
        while (count($id_lst) < 3) {
            $id_lst[] = 0;
        }
        foreach ($res_lst->lst() as $phr) {
            $id_lst[] = $phr->id();
        }
        foreach ($id_lst as $id) {
            $key = str_pad(decbin(abs($id)), 15, '0', STR_PAD_LEFT);
            if ($id < 0) {
                $key = $key . '1';
            } else {
                $key = $key . '0';
            }
            $keys[] = $key;
        }
        while (count($keys) < self::PRIME_PHRASES) {
            array_unshift($keys, str_repeat('0', 16));
        }
        $bin_key = implode('', $keys);
        $bin_key = str_pad($bin_key, 64, '0', STR_PAD_LEFT);
        $result = (int)bindec($bin_key);
        if ($result > PHP_INT_MAX or $result < PHP_INT_MIN) {
            log_err('Integer size on this system is not the expected 64 bit');
        }
        return $result;
    }

    /**
     * create the database key for a phrase group
     * @param phrase_list $phr_lst list of words or triples
     * @return string the 512 bit db key of up to 16 32 bit phrase ids in alpha_num format
     */
    private function alpha_num(phrase_list $phr_lst): string
    {
        $db_key = '';
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
        return $db_key;
    }

    /**
     * create the database key for a phrase group
     * @param phrase_list $phr_lst list of words or triples
     * @return string the group id based on the given phrase list of more than 16 keys with 6 chars
     */
    private function alpha_num_big(phrase_list $phr_lst): string
    {
        $db_key = '';
        foreach ($phr_lst->lst() as $phr) {
            $db_key .= $this->int2alpha_num($phr->id());
        }
        return $db_key;
    }

    /**
     * @param int $id a phrase id
     * @return string a 6 char db key of a 32 bit phrase id in alpha_num format e.g. "3'082'113" ist "..AkS/"
     */
    function int2alpha_num(int $id): string
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

}