<?php

/*

    model/group/group_id.php - e.g. to create a group_id based on a phrase list
    ------------------------

    there are three group id formats for speed- and space-saving:

    1. for up to four prime phrases with a 16 bit integer id a 64 bit bigint key is used
       this allows fast and efficient saving for many number
    2. for up the 16 phrases with a 32 bit integer id a 512 bit db key is used,
       which is shown using the chars . for 0, / for 1 and 0 to 9, A to Z and a to z
    3. if the phrase list contains an id with 64 bit or more than 16 phrases are used
       a alpha_num text is used for the db key

    base on the three db key types three value tables are used:
    1. values_prime with the 64 bit bigint key
    2. values with the 512 bit db key
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

namespace cfg\group;

include_once MODEL_GROUP_PATH . 'id.php';
include_once DB_PATH . 'sql_type.php';
include_once MODEL_PHRASE_PATH . 'phrase_list.php';

use cfg\db\sql_type;
use cfg\phrase\phrase_list;

class group_id extends id
{

    /*
     * database link
     */

    // the database table name extensions
    const TBL_EXT_PRIME = '_prime'; // the table name extension for up to four prime phrase ids
    const TBL_EXT_BIG = '_big'; // the table name extension for more than 16 phrase ids
    const TBL_EXT_PHRASE_ID = '_p'; // the table name extension with the number of phrases for up to four prime phrase ids
    const PRIME_PHRASES_STD = 4;
    const MAIN_PHRASES_STD = 7;
    const STANDARD_PHRASES = 16;

    /**
     * @param phrase_list $phr_lst the list of phrases that define the value
     * @return int|string the group id based on the given phrase list
     *                    as 64-bit integer, 512-bit key as 112 chars or list of more than 16 keys with 6 chars
     */
    function get_id(phrase_list $phr_lst): int|string
    {
        if ($phr_lst->count() <= self::PRIME_PHRASES_STD
            and $phr_lst->prime_only()
            and ($phr_lst->one_positiv() or $phr_lst->count() < self::PRIME_PHRASES_STD)
        ) {
            $phr_lst = $phr_lst->sort_rev_by_id();
            $db_key = $this->int_group_id($phr_lst);
        } elseif ($phr_lst->count() <= self::STANDARD_PHRASES) {
            $phr_lst = $phr_lst->sort_by_id();
            $db_key = $this->alpha_num($phr_lst);
        } else {
            $phr_lst = $phr_lst->sort_by_id();
            $db_key = $this->alpha_num_big($phr_lst);
        }
        return $db_key;
    }

    /**
     * get the max number if phrases for type of the given id
     * @param int|string $id either a 64-bit integer group id, a 512-bit alpha_num group id or a text of more than 16 +/- separated 6 alpha_num char phrase ids
     * @return int the
     */
    function max_number_of_phrase(int|string $id): int
    {
        $tbl_typ = $this->table_type($id);
        if ($tbl_typ == sql_type::PRIME) {
            return self::PRIME_PHRASES_STD;
        } elseif ($tbl_typ == sql_type::BIG) {
            $id_keys = preg_split("/[+-]/", $id);
            return count($id_keys);
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
     * @param int|string $grp_id either a 64-bit integer group id, a 512-bit alpha_num group id or a text of more than 16 +/- separated 6 alpha_num char phrase ids
     * @param bool $filled if true the missing ids are filled with a null value
     * @return array a sorted list of phrase ids
     */
    function get_array(int|string $grp_id, bool $filled = false): array
    {
        if ($this->is_prime($grp_id)) {
            $result = $this->int_array($grp_id);
        } else {
            $result = [];
            $signs = array_values(array_filter(str_split($grp_id), fn($value) => $value == '+' || $value == '-'));
            $id_keys = preg_split("/[+-]/", $grp_id);
            foreach ($id_keys as $key => $id_key) {
                $id = $this->alpha_num2int($id_key);
                if ($id != 0) {
                    if ($signs[$key] == '-') {
                        $result[] = $id * -1;
                    } else {
                        $result[] = $id;
                    }
                }
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
     * @return bool true if the $grp_id represents more then 16 phrase ids
     */
    function is_big(int|string $grp_id): bool
    {
        if (strlen($grp_id) > 112) {
            return true;
        } else {
            return false;
        }
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