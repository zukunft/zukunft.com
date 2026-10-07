<?php

/*

    model/group/group_id.php - e.g. to create a group_id based on a phrase list
    ------------------------

    there are three group id formats for speed- and space-saving:

    1. for up to four prime phrases with a 16 bit integer id a 64 bit bigint key is used
       this allows fast and efficient saving for many number
    2. for up the 16 phrases a compact alpha_num text key of up to 112 chars is used,
       e.g. "0U+0X+3-" (see docs/llm/group_id.md)
    3. for more than 16 phrases or a longer key the same compact text key is used without limit

    base on the three db key types three value tables are used:
    1. values_prime with the 64 bit bigint key
    2. values with the text key of up to 112 chars
    1. values_big with the text key for many phrases

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

namespace Zukunft\ZukunftCom\main\php\cfg\group;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_GROUP . 'id.php';
include_once paths::DB . 'sql_type.php';
include_once paths::MODEL_PHRASE . 'phrase_list.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED . 'group_id_url.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql_type;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\group_id_url;

class group_id extends id
{

    /*
     * database link
     */

    // the database table name extensions
    const string TBL_EXT_PRIME = '_prime'; // the table name extension for up to four prime phrase ids
    const string TBL_EXT_BIG = '_big'; // the table name extension for more than 16 phrase ids
    const string TBL_EXT_PHRASE_ID = '_p'; // the table name extension with the number of phrases for up to four prime phrase ids
    const int PRIME_PHRASES_STD = 4;
    const int MAIN_PHRASES_STD = 7;
    const int STANDARD_PHRASES = 16;
    // the max length of the text key in the standard table: 16 phrase ids of 6 chars and a sign char
    const int STANDARD_KEY_CHARS = 112;

    /**
     * @param phrase_list $phr_lst the list of phrases that define the value
     * @return int|string the group id based on the given phrase list as
     *                    64-bit integer or the compact text key e.g. "0U+0X+3-"
     */
    function get_id(phrase_list $phr_lst): int|string
    {
        // a local message, because a group id is computed from positions this list owns:
        // a missing key while sorting is an internal inconsistency and not a user decision,
        // so it is not propagated to the callers (is_prime, is_big, get_grp_id, ...)
        $sort_msg = new user_message(); // not reported, see above
        if ($phr_lst->count() <= self::PRIME_PHRASES_STD
            and $phr_lst->prime_only()
            and ($phr_lst->one_positiv() or $phr_lst->count() < self::PRIME_PHRASES_STD)
        ) {
            $phr_lst = $phr_lst->sort_rev_by_id($sort_msg);
            $db_key = $this->int_group_id($phr_lst);
        } else {
            // the standard and the big table use the same key, which is_big() selects by its size
            $phr_lst = $phr_lst->sort_by_id($sort_msg);
            $db_key = $this->alpha_num($phr_lst);
        }
        return $db_key;
    }

    /**
     * get the max number if phrases for type of the given id
     * @param int|string $id either a 64-bit integer group id or the compact text key e.g. "0U+0X+3-"
     * @return int the
     */
    function max_number_of_phrase(int|string $id): int
    {
        $tbl_typ = $this->table_type($id);
        if ($tbl_typ == sql_type::PRIME) {
            return self::PRIME_PHRASES_STD;
        } elseif ($tbl_typ == sql_type::BIG) {
            return group_id_url::phrase_count($id);
        } elseif ($tbl_typ == sql_type::MOST) {
            return self::STANDARD_PHRASES;
        } else {
            log_err('Unexpected table type ' . $tbl_typ->value);
            return self::STANDARD_PHRASES;
        }
    }

    /**
     * get the sorted array of phrase ids from the given group id
     *
     * @param int|string $grp_id either a 64-bit integer group id or the compact text key e.g. "0U+0X+3-"
     * @param bool $filled if true the missing ids are filled with a null value
     * @return array a sorted list of phrase ids
     */
    function get_array(int|string $grp_id, bool $filled = false): array
    {
        if ($this->is_prime($grp_id)) {
            $result = $this->int_array($grp_id);
        } else {
            $result = [];
            // each phrase id is ended by its sign char e.g. "-" for a triple and "+" or "_" for a word
            $slot = '~(' . group_id_url::ID_CHAR_PATTERN . '+)(' . group_id_url::SIGN_PATTERN . ')~';
            preg_match_all($slot, $grp_id, $parts, PREG_SET_ORDER);
            foreach ($parts as [, $id_key, $sign]) {
                $id = $this->alpha_num2int($id_key);
                $is_triple = in_array($sign, [self::CHAR_TRIPLE, self::CHAR_SOURCE_TRIPLE, self::CHAR_RESULT_TRIPLE]);
                $result[] = $is_triple ? $id * -1 : $id;
            }
        }
        $is = count($result);
        $max = $this->max_number_of_phrase($grp_id);
        if ($filled and $is < $this->max_number_of_phrase($grp_id)) {
            for ($i = $is; $i < $max; $i++) {
                $result[] = null;
            }
        }
        return $result;
    }

    /**
     * TODO use directly the phrase list without converting to a group id and back
     * @return int the number of phrases of this group id
     */
    function count(int|string $grp_id): int
    {
        return count($this->get_array($grp_id));
    }

    /**
     * test if the group id can be used to save a value or result in the database
     * @param int|string $grp_id the group id that should be tested
     * @return bool true if the group id can be used to save a value or result in the database
     */
    function is_valid(int|string $grp_id): bool
    {
        if ($this->count($grp_id) > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * get the table name extension for value, result and group tables
     * depending on the number of phrases a different table for value and results is used
     * for faster searching
     *
     * @param int|string $grp_id
     * @param bool $with_phrase_count false if the number of phrases are not relevant e.g. even for prime tables
     * @return string the extension for the table name based on the id
     */
    function table_extension(int|string $grp_id, bool $with_phrase_count = true): string
    {
        $tbl_typ = $this->table_type($grp_id);
        $ext = '';
        // only for prime value and result tables the number of ids is relevant
        if ($tbl_typ == sql_type::PRIME) {
            if ($with_phrase_count) {
                $ext .= self::TBL_EXT_PHRASE_ID . $this->count($grp_id);
            }
        }
        return $ext;
    }

    /**
     * get the table name extension for value, result and group tables
     * depending on the number of phrases a different table for value and results is used
     * for faster searching
     *
     * @param int|string $grp_id
     * @return sql_type the extension for the table name based on the id
     */
    function table_type(int|string $grp_id): sql_type
    {
        $ext = '';
        if ($this->is_prime($grp_id)) {
            $ext = sql_type::PRIME;
        } elseif ($this->is_big($grp_id)) {
            $ext = sql_type::BIG;
        } else {
            $ext = sql_type::MOST;
        }
        return $ext;
    }

    /**
     * @return array with the possible table extension
     */
    function table_extension_list(): array
    {
        $tbl_ext_lst = array();
        $tbl_ext_lst[] = self::TBL_EXT_PRIME;
        $tbl_ext_lst[] = '';
        $tbl_ext_lst[] = self::TBL_EXT_BIG;
        return $tbl_ext_lst;
    }

    /**
     * @param int|string $grp_id
     * @return bool true if the $grp_id represents up to four prime phrase ids
     */
    function is_prime(int|string $grp_id): bool
    {
        // TODO check why is_int is not working
        // if (is_int($grp_id)) {
        if (is_numeric($grp_id) and $grp_id < PHP_INT_MAX and $grp_id > PHP_INT_MIN) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param int|string $grp_id
     * @return bool true if the $grp_id represents more then 16 phrase ids or does not fit the standard key column
     */
    function is_big(int|string $grp_id): bool
    {
        // the compact key has no fixed length, so the phrases are counted
        $key = (string)$grp_id;
        return strlen($key) > self::STANDARD_KEY_CHARS
            or group_id_url::phrase_count($key) > self::STANDARD_PHRASES;
    }

    function int_array(int $grp_id): array
    {
        $result = [];
        $bin_key = decbin($grp_id);
        $bin_key = str_pad($bin_key, 64, "0", STR_PAD_LEFT);
        while ($bin_key != '') {
            $sign = substr($bin_key, 0, 1);
            $id = bindec(substr($bin_key, 1, 15));
            if ($id != 0) {
                if ($sign == 1) {
                    $result[] = $id * -1;
                } else {
                    $result[] = $id;
                }
            }
            $bin_key = substr($bin_key, 16);
        }

        return $result;
    }

    /**
     * the 16 slot text key of a prime group, e.g. for a result that is saved in the standard result
     * table, because its source group does not fit the bigint column of the prime table
     *
     * @param int $grp_id the 64-bit integer id of a prime group
     * @return string the text key with the phrase ids sorted like get_id() sorts a non-prime group
     */
    function int2key(int $grp_id): string
    {
        $id_lst = $this->int_array($grp_id);
        sort($id_lst);
        return $this->ids_to_alpha_num($id_lst);
    }

    /**
     * create a 64-bit integer id based on four prime phrase ids
     *
     * @param phrase_list $phr_lst list of words or triples that are used to select the value
     * @return int the group id based on the given phrase list as 64-bit integer
     */
    private function int_group_id(phrase_list $phr_lst): int
    {
        $id_lst = [];
        foreach ($phr_lst->lst() as $phr) {
            $id_lst[] = $phr->id();
        }
        return $this->id_lst_to_int($id_lst);
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
            $key = substr($key, 1);
        }
        return $result;
    }

}