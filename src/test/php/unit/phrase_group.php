<?php

/*

  test/php/unit/phrase_group.php - unit tests related to a phrase group
  ------------------------------


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

namespace test;

include_once API_PHRASE_PATH . 'group.php';
include_once MODEL_GROUP_PATH . 'group_id.php';
include_once MODEL_GROUP_PATH . 'group_link.php';
include_once MODEL_GROUP_PATH . 'group_list.php';

use api\phrase\group as group_api;
use cfg\group\group_id;
use cfg\group\group;
use cfg\group\group_link;
use cfg\group\group_list;
use cfg\library;
use cfg\phrase_list;
use cfg\sql_db;
use cfg\word;
use cfg\word_list;

class group_unit_tests
{
    function run(test_cleanup $t): void
    {

        global $usr;

        // init
        $lib = new library();
        $db_con = new sql_db();
        $t->name = 'group->';
        $t->resource_path = 'db/group/';
        $usr->set_id(1);

        $t->header('Unit tests of the phrase group class (src/main/php/model/group/group.php)');

        $t->subheader('Group id tests');
        $grp_id = new group_id();
        $t->assert('64 bit group_id short word list', $grp_id->get_id($t->dummy_word_list_short()->phrase_lst()),
            131078);
        $t->assert('phrase ids of 64 bit group_id short', $grp_id->get_array(131078),
            $t->dummy_word_list_short()->phrase_lst()->ids());
        $t->assert('64 bit group_id word list', $grp_id->get_id($t->dummy_word_list()->phrase_lst()),
            562967133683720);
        $t->assert('phrase ids of 64 bit group_id', $grp_id->get_array(562967133683720),
            $t->dummy_word_list()->phrase_lst()->ids());

        //$this->check_64_bit_key($t, [0,0,0,0], 0);
        $this->check_64_bit_key($t, [1], 2);
        $this->check_64_bit_key($t, [-1], 3);
        $this->check_64_bit_key($t, [2], 4);
        $this->check_64_bit_key($t, [-2], 5);
        $this->check_64_bit_key($t, [32767], 65534);
        $this->check_64_bit_key($t, [-32767], 65535);
        $this->check_64_bit_key($t, [1,32767], 196606);
        $this->check_64_bit_key($t, [-1,-32767], 262143);
        $this->check_64_bit_key($t, [2,32767], 327678);
        $this->check_64_bit_key($t, [-2,32767], 393214);
        $this->check_64_bit_key($t, [32767,-32767], 4294901759);
        $this->check_64_bit_key($t, [1,-32767,32767], 12884901886);
        $this->check_64_bit_key($t, [-1,32767,-32767], 17179803647);
        $this->check_64_bit_key($t, [-1,-32767,32767], 17179869182);
        $this->check_64_bit_key($t, [32767,32766,32765], 281470681546746);
        $this->check_64_bit_key($t, [1,32767,32766,32765], 844420634968058);
        $this->check_64_bit_key($t, [1234,32767,32766,32765], 694961713203445754);
        $this->check_64_bit_key($t, [15678,32767,32766,32765], 8826210840420876282);
        $this->check_64_bit_key($t, [-15678,-32767,32767,-32766], 8826492319692685309);
        // TODO fix ist
        //$this->check_64_bit_key($t, [32767,32766,32765,32764], -281487861940224);
        //$this->check_64_bit_key($t, [-32767,32767,-32766,32766], 9223231297218904063);

        $t->assert('group_id triple list', $grp_id->get_id($t->dummy_triple_list()->phrase_lst()),5);
        $t->assert('triple ids 64 bit group_id ', $grp_id->get_array(5), $t->dummy_triple_list()->phrase_lst()->ids());
        $phr_lst = new phrase_list($usr);
        $phr_lst->merge($t->dummy_word_list()->phrase_lst());
        $phr_lst->merge($t->dummy_triple_list()->phrase_lst());
        $t->assert('group_id combine phrase list', $grp_id->get_id($phr_lst),
            '...../+.....0+.....1+.....2+.....0-......+......+......+......+......+......+......+......+......+......+......+');
        $t->assert('group_id phrase list', $grp_id->get_id($t->dummy_phrase_list()),
            '...../+.....0+.....1+...../-.....0-......+......+......+......+......+......+......+......+......+......+......+');
        $t->assert('group_id phrase list 16', $grp_id->get_id($t->dummy_phrase_list_16()),
            '...../+.....9-.....A+.....Z-.....a+..../.-....3s+....Yz-...1Ao+...I1A-../vLC+..8jId-.//ZSB+.4LYK3-.ZSahL+1FajJ2-');
        $t->assert('group_id phrase list 16', $grp_id->get_id($t->dummy_phrase_list_17_plus()),
            '...../+.....9-.....A+.....Z-.....a+..../.-....3s+....Yz-...1Ao+...I1A-../vLC+..8jId-.//ZSB+.4LYK3-.ZSahL+1FajJ2-.uraWl+');
        $t->assert('group_id revers phrase list 16',
            implode(',', $grp_id->get_array('...../+.....9-.....A+.....Z-.....a+..../.-....3s+....Yz-...1Ao+...I1A-../vLC+..8jId-.//ZSB+.4LYK3-.ZSahL+1FajJ2-')),
            '1,-11,12,-37,38,-64,376,-2367,13108,-82124,505294,-2815273,17192845,-106841477,628779863,-3516593476');
        $grp_id = 0;

        $t->subheader('SQL statements - setup');
        $grp = new group($usr);
        $t->assert_sql_truncate($db_con, $grp);
        $t->assert_sql_table_create($db_con, $grp);
        $t->assert_sql_index_create($db_con, $grp);
        $t->assert_sql_foreign_key_create($db_con, $grp);

        $t->subheader('SQL statements - read');
        $this->assert_sql_by_phrase_list($t, $db_con);
        $t->assert_sql_by_name($db_con, $grp);


        $t->header('Unit tests of the phrase group link class (src/main/php/model/group/group_link.php)');

        $t->subheader('SQL statement tests');

        // sql to load the phrase links related to a group
        $grp_lnk = new group_link();
        // TODO activate or use group id
        //$t->assert_sql_by_id($db_con, $grp_lnk);

        $grp->set_id(14);
        // TODO activate or use group id
        //$this->assert_sql_load_by_group_id($t, $db_con, $grp_lnk, $grp);

    }

    /**
     * similar to $t->assert_sql_all but calling load_by_group_id_sql instead of load_sql
     *
     * @param test_cleanup $t the forwarded testing object
     * @param sql_db $db_con does not need to be connected to a real database
     * @param group_link $phr_grp_lnk the phrase group triple or word link object used for testing
     * @param group $grp the phrase group object to select the links
     */
    private function assert_sql_load_by_group_id(
        test_cleanup $t,
        sql_db       $db_con,
        group_link   $phr_grp_lnk,
        group        $grp): void
    {
        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $phr_grp_lnk->load_by_group_id_sql($db_con, $grp);
        $result = $t->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $phr_grp_lnk->load_by_group_id_sql($db_con, $grp);
            $t->assert_qp($qp, $db_con->db_type);
        }
    }

    /**
     * test the sql statement creation to load the group base on a phrase list
     * depending on the size of the phrase list one of three group types are used
     * 1. up to four phrases with an id within +/- 32k the table with a 64-bit key is used (named prime)
     * 2. up to 16 phrase a table with a 512-bit key is used
     * 3. for more than 16 phrase a table with a text key is used (named big)
     *
     * @param test_cleanup $t the forwarded testing object
     * @param sql_db $db_con does not need to be connected to a real database
     */
    private function assert_sql_by_phrase_list(
        test_cleanup $t,
        sql_db       $db_con): void
    {
        global $usr;

        $grp = new group($usr);

        // check the Postgres query syntax for a list of up to four prime phrases
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $grp->load_sql_by_phrase_list($db_con->sql_creator(), $t->dummy_phrase_list_prime());
        $result = $t->assert_qp($qp, $db_con->db_type);

        // ... and for 16 phrase
        if ($result) {
            $qp = $grp->load_sql_by_phrase_list($db_con->sql_creator(), $t->dummy_phrase_list_16());
            $t->assert_qp($qp, $db_con->db_type);
        }

        // ... and for more than 16 phrase
        if ($result) {
            $qp = $grp->load_sql_by_phrase_list($db_con->sql_creator(), $t->dummy_phrase_list_17_plus());
            $t->assert_qp($qp, $db_con->db_type);
        }

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $grp->load_sql_by_phrase_list($db_con->sql_creator(), $t->dummy_phrase_list_prime());
            $t->assert_qp($qp, $db_con->db_type);
        }

        // ... and for 16 phrase
        if ($result) {
            $qp = $grp->load_sql_by_phrase_list($db_con->sql_creator(), $t->dummy_phrase_list_16());
            $t->assert_qp($qp, $db_con->db_type);
        }

        // ... and for more than 16 phrase
        if ($result) {
            $qp = $grp->load_sql_by_phrase_list($db_con->sql_creator(), $t->dummy_phrase_list_17_plus());
            $t->assert_qp($qp, $db_con->db_type);
        }
    }

    private function check_64_bit_key(test_cleanup $t, array $ids, int $id): void
    {
        $grp_id = new group_id();
        $phr_lst = new phrase_list($t->usr1);
        foreach ($ids as $phr_id) {
            if ($phr_id < 0) {
                $trp_phr = $t->dummy_triple()->phrase();
                $trp_phr->set_id($phr_id);
                $phr_lst->add($trp_phr);
            } else {
                $wrd_phr = $t->dummy_word()->phrase();
                $wrd_phr->set_id($phr_id);
                $phr_lst->add($wrd_phr);
            }
        }
        $t->assert('64 bit group_id ' . $id, $grp_id->get_id($phr_lst), $id);
        $a = $grp_id->get_array($id);
        $t->assert('phrase ids 64 bit group_id ' . $id, $grp_id->get_array($id), $ids);
    }
}