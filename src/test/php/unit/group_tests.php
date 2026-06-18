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

namespace Zukunft\ZukunftCom\test\php\unit;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_GROUP . 'group_id.php';
include_once paths::MODEL_GROUP . 'group_link.php';
include_once paths::MODEL_GROUP . 'group_list.php';
include_once paths::MODEL_GROUP . 'result_id.php';
include_once paths::SHARED_CONST . 'groups.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type;
use Zukunft\ZukunftCom\main\php\cfg\group\group;
use Zukunft\ZukunftCom\main\php\cfg\group\group_id;
use Zukunft\ZukunftCom\main\php\cfg\group\group_link;
use Zukunft\ZukunftCom\main\php\cfg\group\result_id;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\shared\const\groups;
use Zukunft\ZukunftCom\main\php\shared\const\values;
use Zukunft\ZukunftCom\test\php\create\test_formulas;
use Zukunft\ZukunftCom\test\php\create\test_groups;
use Zukunft\ZukunftCom\test\php\create\test_phrases;
use Zukunft\ZukunftCom\test\php\create\test_triples;
use Zukunft\ZukunftCom\test\php\create\test_words;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class group_tests
{
    function run(test_cleanup $t): void
    {

        global $usr;

        // init
        $db_con = new sql_db();
        $sc = new sql_creator();
        $t_wrd = new test_words($t);
        $t_trp = new test_triples($t);
        $t_phr = new test_phrases($t);
        $t_grp = new test_groups($t);
        $t_frm = new test_formulas($t);
        $t->name = 'group->';
        $t->resource_path = 'db/group/';

        // start the test section (ts)
        $ts = 'unit group ';
        $t->header($ts);

        $t->subheader($ts . 'id');
        $grp_id = new group_id();
        $t->assert('64 bit group_id short word list', $grp_id->get_id($t_wrd->word_list_short()->phrase_list()),
            1114113);
        $t->assert('phrase ids of 64 bit group_id short', $grp_id->get_array(1114113),
            $t_wrd->word_list_short()->phrase_list()->ids());
        $t->assert('64 bit group_id word list', $grp_id->get_id($t_wrd->word_list()->phrase_list()),
            1688871335231489);
        $t->assert('phrase ids of 64 bit group_id', $grp_id->get_array(1688871335231489),
            $t_wrd->word_list()->phrase_list()->ids());

        //$this->check_64_bit_key($t, [0,0,0,0], 0);
        $this->check_64_bit_key($t, [1], 1);
        $this->check_64_bit_key($t, [-1], 32769);
        $this->check_64_bit_key($t, [2], 2);
        $this->check_64_bit_key($t, [4], 4);
        $this->check_64_bit_key($t, [7], 7);
        $this->check_64_bit_key($t, [-2], 32770);
        $this->check_64_bit_key($t, [-3], 32771);
        $this->check_64_bit_key($t, [-51], 32819);
        $this->check_64_bit_key($t, [32767], 32767);
        $this->check_64_bit_key($t, [-32767], 65535);
        $this->check_64_bit_key($t, [1,32767], 2147418113);
        $this->check_64_bit_key($t, [2,32767], 2147418114);
        $this->check_64_bit_key($t, [-2,32767], 2147450882);
        $this->check_64_bit_key($t, [-32767,-1], 2147614719);
        $this->check_64_bit_key($t, [-32767,32767], 2147483647);
        $this->check_64_bit_key($t, [-32767,1,32767], 140733193519103);
        $this->check_64_bit_key($t, [-32767,-1,32767], 140735341002751);
        $this->check_64_bit_key($t, [-1,-32767,32767], 140735341002751);
        $this->check_64_bit_key($t, [32765,32766,32767], 140735340773373);
        $this->check_64_bit_key($t, [1,32765,32766,32767], 9223231292923772929);
        $this->check_64_bit_key($t, [1234,32765,32766,32767], 9223231292923774162);
        $this->check_64_bit_key($t, [15678,32765,32766,32767], 9223231292923788606);
        $this->check_64_bit_key($t, [-15677,32767,-15676,32766], 9223231293951360317);
        $this->check_64_bit_key($t, [32767,32766,32765,32764], 9223231292923805692);
        $this->check_64_bit_key($t, [-15678,32767,-32766,32766], 9223231293951508478);
        $this->check_64_bit_key($t, [-32765,32767,-32766,32766], 9223231295071322110);
        $this->check_64_bit_key($t, [-32767,-32766,-32765,32767], 9223372028264775679);
        // these are not "prime" anymore because at least one id must be positiv to avoid exeeding PHP_INT_MAX
        $this->check_64_bit_key($t, [-1,-2,-3,-4], '.....2-.....1-.....0-...../-......+......+......+......+......+......+......+......+......+......+......+......+');
        $this->check_64_bit_key($t, [-32767,-32766,-32765,-1], '...5zz-...5zy-...5zx-...../-......+......+......+......+......+......+......+......+......+......+......+......+');

        $this->check_int2alpha($t, 0, '......+');
        $this->check_int2alpha($t, 1, '...../+');
        $this->check_int2alpha($t, 2, '.....0+');
        $this->check_int2alpha($t, 11, '.....9+');
        $this->check_int2alpha($t, 12, '.....A+');
        $this->check_int2alpha($t, 37, '.....Z+');
        $this->check_int2alpha($t, 38, '.....a+');
        $this->check_int2alpha($t, 63, '.....z+');
        $this->check_int2alpha($t, 64, '..../.+');
        $this->check_int2alpha($t, -1, '...../-');
        $this->check_int2alpha($t, -2, '.....0-');
        $this->check_int2alpha($t, -11, '.....9-');
        $this->check_int2alpha($t, -12, '.....A-');
        $this->check_int2alpha($t, -37, '.....Z-');
        $this->check_int2alpha($t, -38, '.....a-');
        $this->check_int2alpha($t, -63, '.....z-');
        $this->check_int2alpha($t, -64, '..../.-');
        $this->check_int2alpha($t, 12, '.....A<', true, );
        $this->check_int2alpha($t, 12, '.....A>', false, true);
        $this->check_int2alpha($t, 12, '.....A=', false, false, true);
        $this->check_int2alpha($t, -12, '.....A(', true, );
        $this->check_int2alpha($t, -12, '.....A)', false, true);

        $t->assert('group_id triple list', $grp_id->get_id($t_trp->triple_list_one()->phrase_list()),values::PI_SYMBOL_ID);
        $t->assert('triple ids 64 bit group_id ', $grp_id->get_array(values::PI_SYMBOL_ID), $t_trp->triple_list_one()->phrase_list()->ids());
        $phr_lst = new phrase_list($usr);
        $phr_lst->merge($t_wrd->word_list()->phrase_list());
        $phr_lst->merge($t_trp->triple_list_short()->phrase_list());
        $t->assert('group_id combine phrase list', $grp_id->get_id($phr_lst),
            '..../d-.....0-...../-...../+.....0+.....3+.....4+......+......+......+......+......+......+......+......+......+');
        $t->assert('group_id phrase list', $grp_id->get_id($t_phr->phrase_list()),
            '.....0-...../-...../+.....0+.....F+......+......+......+......+......+......+......+......+......+......+......+');
        $t->assert('group_id phrase list 16', $grp_id->get_id($t_phr->phrase_list_16()),
            '1FajJ2-.4LYK3-..8jId-...I1A-....Yz-..../.-.....Z-.....9-...../+.....A+.....a+....3s+...1Ao+../vLC+.//ZSB+.ZSahL+');
        $t->assert('group_id phrase list 16', $grp_id->get_id($t_phr->phrase_list_17_plus()),
            '1FajJ2-.4LYK3-..8jId-...I1A-....Yz-..../.-.....Z-.....9-...../+.....A+.....a+....3s+...1Ao+../vLC+.//ZSB+.ZSahL+.uraWl+');
        $t->assert('group_id revers phrase list 16',
            implode(',', $grp_id->get_array('...../+.....9-.....A+.....Z-.....a+..../.-....3s+....Yz-...1Ao+...I1A-../vLC+..8jId-.//ZSB+.4LYK3-.ZSahL+1FajJ2-')),
            '1,-11,12,-37,38,-64,376,-2367,13108,-82124,505294,-2815273,17192845,-106841477,628779863,-3516593476');
        $grp_id = 0;

        $t->subheader($ts . 'result id');
        // TODO assign the formula "increase" to the word inhabitants
        // TODO based on the formula the name of the formula and the phrases on the left side
        //      are always added to the result, so they do not need to be included in the phrase lists
        // TODO add test to show that the result is always based on a concrete value
        //      and not on the value selection phrase list for the formula
        $res_id = new result_id();
        $t->assert('64 bit result_id for the formula increase, '
            . 'the phrases Zurich (city) and inhabitants and the result only phrase 2023 (year)',
            $res_id->get_id($t_phr->zh_inhabitants_2020(), $t_phr->zh_inhabitants_2020(), $t_frm->formula_increase()),
            6052132915249350);
        $t->assert('128 bit result_id for the formula increase, '
            . 'the phrases Zurich (city), Geneva (city) and inhabitants and the result only phrase 2023 (year)',
            $res_id->get_id($t_phr->zh_ge_inhabitants_2020(), $t_phr->zh_ge_inhabitants_2020(), $t_frm->formula_increase()),
            '9234844886612976128');
        $t->assert('512 bit result_id ',
            $res_id->get_id($t_phr->phrase_list_14(), $t_phr->phrase_list_14b(), $t_frm->formula_increase()),
            '.....J=..8jId-...I1A-....Yz-..../.-.....Z-.....9-...../+.....A+.....a+....3s+...1Ao+../vLC+.//ZSB+1FajJ2(.4LYK3)1FajJ2)');
        $t->assert('512 bit result_id ',
            $res_id->get_id($t_phr->phrase_list_17_plus(), $t_phr->phrase_list_17_plus(), $t_frm->formula_increase()),
            '...../+.....9-.....A+.....Z-.....a+..../.-....3s+....Yz-...1Ao+...I1A-../vLC+..8jId-.//ZSB+.4LYK3-.ZSahL+1FajJ2-.uraWl+');

        $t->subheader($ts . 'sql statements - setup');
        $grp = new group($usr);
        $t->assert_sql_table_create($grp);
        $t->assert_sql_index_create($grp);
        $t->assert_sql_foreign_key_create($grp);
        $t->assert_sql_truncate($sc, $grp);

        $t->subheader($ts . 'sql statements - read');
        $grp = $t_grp->group();
        $t->assert_sql_by_name($sc, $grp); // by name is always for all tables: prime, most and big
        $t->assert_sql_standard($sc, $grp);
        $t->assert_sql_standard_by_name($sc, $grp);
        $this->assert_sql_by_phrase_list($t, $db_con);

        // TODO Prio 0 activate db write
        $t->subheader($ts . 'sql statements - write');
        $grp = new group($usr);
        $grp->set_phrase_list($t_phr->phrase_list_prime());
        $t->assert_sql_insert($sc, $grp);
        $t->assert_sql_insert($sc, $grp, [sql_type::USER]);
        $db_grp = $t_grp->group();
        $grp = $grp->renamed(groups::TN_RENAMED);
        $t->assert_sql_update($sc, $grp, $db_grp);
        $t->assert_sql_update($sc, $grp, $db_grp, [sql_type::USER]);
        $grp->set_phrase_list($t_phr->phrase_list_16());
        $t->assert_sql_insert($sc, $grp);
        $t->assert_sql_insert($sc, $grp, [sql_type::LOG]);
        $grp->set_phrase_list($t_phr->phrase_list_17_plus());
        $grp->description = groups::TN_READ_COM;
        $t->assert_sql_insert($sc, $grp, [sql_type::USER]);
        // test already done by the horizontal tests
        //$grp->set_phrase_list($t_phr->phrase_list_prime());
        //$t->assert_sql_delete($sc, $grp, [sql_type::LOG]);
        $grp->set_phrase_list($t_phr->phrase_list_16());
        $t->assert_sql_delete($sc, $grp, [sql_type::LOG, sql_type::USER]);
        $grp->set_phrase_list($t_phr->phrase_list_17_plus());
        $t->assert_sql_delete($sc, $grp);
        $t->assert_sql_delete($sc, $grp, [sql_type::USER]);
        $t->assert_sql_delete($sc, $grp, [sql_type::LOG]);
        $t->assert_sql_delete($sc, $grp, [sql_type::LOG, sql_type::USER]);


        // start the test section (ts)
        $ts = 'unit phrase group list ';
        $t->header($ts);

        $t->subheader($ts . 'sql statement');

        // load the group by the phrase ids

        // sql to load the phrase links related to a group
        $grp_lnk = new group_link();
        // TODO Prio 2 activate or use group id
        //$t->assert_sql_by_id($sc, $grp_lnk);

        $grp->set_id(14);
        // TODO Prio 2 activate or use group id
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
        $t_phr = new test_phrases($t);

        // check the Postgres query syntax for a list of up to four prime phrases
        $db_con->db_type = sql_db::POSTGRES;
        $sc = $db_con->sql_creator();
        $qp = $grp->load_sql_by_phrase_list($sc, $t_phr->phrase_list_prime());
        $result = $t->assert_qp($qp, $sc->db_type);

        // ... and for 16 phrase
        if ($result) {
            $qp = $grp->load_sql_by_phrase_list($sc, $t_phr->phrase_list_16());
            $t->assert_qp($qp, $sc->db_type);
        }

        // ... and for more than 16 phrase
        if ($result) {
            $qp = $grp->load_sql_by_phrase_list($sc, $t_phr->phrase_list_17_plus());
            $t->assert_qp($qp, $sc->db_type);
        }

        // ... and check the MySQL query syntax
        if ($result) {
            $sc->db_type = sql_db::MYSQL;
            $qp = $grp->load_sql_by_phrase_list($sc, $t_phr->phrase_list_prime());
            $t->assert_qp($qp, $sc->db_type);
        }

        // ... and for 16 phrase
        if ($result) {
            $qp = $grp->load_sql_by_phrase_list($sc, $t_phr->phrase_list_16());
            $t->assert_qp($qp, $sc->db_type);
        }

        // ... and for more than 16 phrase
        if ($result) {
            $qp = $grp->load_sql_by_phrase_list($sc, $t_phr->phrase_list_17_plus());
            $t->assert_qp($qp, $sc->db_type);
        }
    }

    private function check_64_bit_key(test_cleanup $t, array $ids, int|string $id): void
    {
        $t_wrd = new test_words($t);
        $t_trp = new test_triples($t);
        $grp_id = new group_id();
        $phr_lst = new phrase_list($t->usr1);
        foreach ($ids as $phr_id) {
            if ($phr_id < 0) {
                $trp_phr = $t_trp->triple()->phrase();
                $trp_phr->set_id($phr_id);
                $phr_lst->add($trp_phr);
            } else {
                $wrd_phr = $t_wrd->word()->phrase();
                $wrd_phr->set_id($phr_id);
                $phr_lst->add($wrd_phr);
            }
        }
        $t->assert('64 bit group_id ' . $id, $grp_id->get_id($phr_lst), $id);
        $a = $grp_id->get_array($id);
        $t->assert('phrase ids 64 bit group_id ' . $id, $grp_id->get_array($id), $ids);
    }

    private function check_int2alpha(
        test_cleanup $t,
        int $id,
        string $alpha,
        bool $is_src = false,
        bool $is_res = false,
        bool $is_frm = false
    ): void
    {
        $grp_id = new group_id();
        $result = $grp_id->int2alpha_num($id, $is_src, $is_res, $is_frm);
        $t->assert('int to alpha of ' . $id, $result, $alpha);
    }

}