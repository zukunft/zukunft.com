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

include_once API_PHRASE_PATH . 'phrase_group.php';
include_once MODEL_GROUP_PATH . 'group_id.php';
include_once MODEL_PHRASE_PATH . 'phrase_group_word_link.php';
include_once MODEL_PHRASE_PATH . 'phrase_group_triple_link.php';
include_once MODEL_PHRASE_PATH . 'phrase_group_list.php';

use api\phrase_group_api;
use api\word_api;
use cfg\group\group_id;
use cfg\library;
use cfg\phrase_group;
use cfg\phrase_group_link;
use cfg\phrase_group_list;
use cfg\phrase_group_triple_link;
use cfg\phrase_group_word_link;
use cfg\phrase_list;
use cfg\sql_db;
use cfg\triple;
use cfg\verb;
use cfg\word;
use cfg\word_list;

class phrase_group_unit_tests
{
    function run(test_cleanup $t): void
    {

        global $usr;

        // init
        $lib = new library();
        $db_con = new sql_db();
        $t->name = 'phrase_group->';
        $t->resource_path = 'db/phrase/';
        $usr->set_id(1);

        $t->header('Unit tests of the phrase group class (src/main/php/model/phrase/phrase_group.php)');

        $t->subheader('Group id tests');
        $grp_id = new group_id();
        $t->assert('64 bit group_id short word list', $grp_id->get_id($t->dummy_word_list_short()->phrase_lst()),
            65539);
        $t->assert('phrase ids of 64 bit group_id short', $grp_id->get_array(65539),
            $t->dummy_word_list_short()->phrase_lst()->ids());
        $t->assert('64 bit group_id word list', $grp_id->get_id($t->dummy_word_list()->phrase_lst()),
            281483566841860);
        $t->assert('phrase ids of 64 bit group_id', $grp_id->get_array(281483566841860),
            $t->dummy_word_list()->phrase_lst()->ids());

        //$this->check_64_bit_key($t, [0,0,0,0], 0);
        $this->check_64_bit_key($t, [1], 1);
        $this->check_64_bit_key($t, [2], 2);
        $this->check_64_bit_key($t, [32767], 32767);
        $this->check_64_bit_key($t, [1,32767], 98303);
        $this->check_64_bit_key($t, [2,32767], 163839);
        $this->check_64_bit_key($t, [32767,32767], 2147450879);
        $this->check_64_bit_key($t, [1,32767,32767], 6442418175);
        $this->check_64_bit_key($t, [32767,32767,32767], 140735340838911);
        $this->check_64_bit_key($t, [1,32767,32767,32767], 422210317549567);
        $this->check_64_bit_key($t, [32767,32767,32767,32767], 9223231297218904063);

        $t->assert('group_id triple list', $grp_id->get_id($t->dummy_triple_list()->phrase_lst()),32770);
        $t->assert('triple ids 64 bit group_id ', $grp_id->get_array(32770), $t->dummy_triple_list()->phrase_lst()->ids());
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

        $t->subheader('SQL statement tests');
        $phr_grp = new phrase_group($usr);
        $t->assert_sql_by_id($db_con, $phr_grp);

        // sql to load the phrase group by word ids
        $phr_grp = new phrase_group($usr);
        $phr_lst = new phrase_list($usr);
        $phr_lst->merge($t->dummy_word_list()->phrase_lst());
        $phr_grp->phr_lst = $phr_lst;
        $t->assert_load_sql_obj_vars($db_con, $phr_grp);

        // sql to load the phrase group by triple ids
        $phr_grp = new phrase_group($usr);
        $phr_lst = new phrase_list($usr);
        $phr_lst->merge($t->dummy_triple_list()->phrase_lst());
        $phr_grp->phr_lst = $phr_lst;
        $t->assert_load_sql_obj_vars($db_con, $phr_grp);

        // sql to load the phrase group by word and triple ids
        $phr_grp = new phrase_group($usr);
        $phr_lst = new phrase_list($usr);
        $phr_lst->merge($t->dummy_word_list()->phrase_lst());
        $phr_lst->merge($t->dummy_triple_list()->phrase_lst());
        $phr_grp->phr_lst = $phr_lst;
        $t->assert_load_sql_obj_vars($db_con, $phr_grp);

        // sql to load the phrase group by name
        $phr_grp = new phrase_group($usr);
        $phr_grp->name = phrase_group_api::TN_READ;
        $t->assert_load_sql_obj_vars($db_con, $phr_grp);

        // sql to load the word list ids
        $wrd_lst = new word_list($usr);
        $wrd1 = new word($usr);
        $wrd1->set_id(1);
        $wrd_lst->add($wrd1);
        $wrd2 = new word($usr);
        $wrd2->set_id(2);
        $wrd_lst->add($wrd2);
        $wrd3 = new word($usr);
        $wrd3->set_id(3);
        $wrd_lst->add($wrd3);
        $phr_grp = new phrase_group($usr);
        $phr_grp->set_id(0);
        $phr_grp->phr_lst = $wrd_lst->phrase_lst();
        $db_con->db_type = sql_db::POSTGRES;
        $created_sql = $phr_grp->get_by_wrd_lst_sql();
        $expected_sql = $t->file('db/phrase/phrase_group_by_id_list.sql');
        $t->assert('phrase_group->get_by_wrd_lst_sql by word list ids', $lib->trim($created_sql), $lib->trim($expected_sql));

        // ... and check if the prepared sql name is unique
        $t->assert_sql_name_unique($phr_grp->get_by_wrd_lst_sql(true));


        $t->header('Unit tests of the phrase group word link class (src/main/php/model/phrase/phrase_group_triple.php)');

        $t->subheader('SQL statement tests');

        // sql to load the phrase group word links related to a group
        $grp_wrd_lnk = new phrase_group_word_link();
        $t->assert_sql_by_id($db_con, $grp_wrd_lnk);

        $phr_grp = new phrase_group($usr);
        $phr_grp->set_id(13);
        $this->assert_sql_load_by_group_id($t, $db_con, $grp_wrd_lnk, $phr_grp);

        // sql to load the phrase group triple links related to a group
        $grp_trp_lnk = new phrase_group_triple_link();
        $t->assert_sql_by_id($db_con, $grp_trp_lnk);

        $phr_grp->set_id(14);
        $this->assert_sql_load_by_group_id($t, $db_con, $grp_trp_lnk, $phr_grp);

    }

    /**
     * similar to $t->assert_sql_all but calling load_by_group_id_sql instead of load_sql
     *
     * @param test_cleanup $t the forwarded testing object
     * @param sql_db $db_con does not need to be connected to a real database
     * @param phrase_group_link $phr_grp_lnk the phrase group triple or word link object used for testing
     * @param phrase_group $grp the phrase group object to select the links
     */
    private function assert_sql_load_by_group_id(
        test_cleanup $t,
        sql_db $db_con,
        phrase_group_link $phr_grp_lnk,
        phrase_group $grp): void
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

    private function check_64_bit_key(test_cleanup $t, array $ids, int $id): void
    {
        $grp_id = new group_id();
        $phr_lst = $t->dummy_word_list()->phrase_lst();
        $wrd_ids = $ids;
        while (count($wrd_ids) < 4) {
            array_unshift($wrd_ids , 0);
        }
        $phr_lst->set_ids($wrd_ids);
        $t->assert('64 bit group_id ' . $id, $grp_id->get_id($phr_lst), $id);
        $a = $grp_id->get_array($id);
        $t->assert('phrase ids 64 bit group_id ' . $id, $grp_id->get_array($id), $ids);
    }
}