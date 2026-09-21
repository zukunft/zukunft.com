<?php

/*

    test/create/test_results.php - create the test result objects
    ----------------------------


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

namespace Zukunft\ZukunftCom\test\php\create;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\cfg\result\result;
use Zukunft\ZukunftCom\main\php\cfg\result\result_list;
use Zukunft\ZukunftCom\main\php\shared\const\results;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\main\php\shared\types\protection_types;
use Zukunft\ZukunftCom\main\php\shared\types\share_types;
use Zukunft\ZukunftCom\main\php\web\result\result as result_ui;
use Zukunft\ZukunftCom\main\php\web\result\result_list as result_list_ui;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;
use Zukunft\ZukunftCom\test\php\utils\test_lib;
use DateTime;

include_once paths::MODEL_PHRASE . 'phrase_list.php';
include_once paths::MODEL_RESULT . 'result.php';
include_once paths::MODEL_RESULT . 'result_list.php';
include_once paths::SHARED_CONST . 'results.php';
include_once paths::SHARED_CONST . 'words.php';
include_once paths::SHARED_TYPES . 'api_types.php';
include_once paths::SHARED_TYPES . 'phrase_types.php';
include_once paths::SHARED_TYPES . 'protection_types.php';
include_once paths::SHARED_TYPES . 'share_types.php';
include_once html_paths::RESULT . 'result.php';
include_once html_paths::RESULT . 'result_list.php';
include_once test_paths::UTILS . 'test_cleanup.php';
include_once test_paths::UTILS . 'test_lib.php';

class test_results
{

    /*
     * init
     */

    // use the global test environment
    private test_cleanup $env;

    function __construct(test_cleanup $env) {
        $this->env = $env;
    }


    /*
     * unit
     */

    function result_simple_1(): result
    {
        $t_wrd = new test_words($this->env);
        $res = new result($this->env->usr1);
        $wrd = $t_wrd->word();
        $phr_lst = new phrase_list($this->env->usr1);
        $phr_lst->add($wrd->phrase());
        $res->set_id(1);
        $res->grp()->set_phrase_list($phr_lst);
        $res->set_number(results::TV_INT);
        return $res;
    }

    function result_simple(): result
    {
        $t_wrd = new test_words($this->env);
        $res = new result($this->env->usr1);
        $wrd = $t_wrd->word();
        $phr_lst = new phrase_list($this->env->usr1);
        $phr_lst->add($wrd->phrase());
        $res->grp()->set_phrase_list($phr_lst);
        $res->set_number(results::TV_INT);
        return $res;
    }

    function result_prime(): result
    {
        $t_grp = new test_groups($this->env);
        $res = new result($this->env->usr1);
        $res->set_grp($t_grp->group());
        $res->set_src_grp($t_grp->group_const());
        $res->set_number(results::TV_INT);
        return $res;
    }

    /**
     * @return result with a source group of 16 phrases, which result::save cannot write
     *                as the bigint source_group_id of results_prime and results_main
     */
    function result_src_grp_big(): result
    {
        $t_grp = new test_groups($this->env);
        $res = $this->result_prime();
        $res->set_src_grp($t_grp->group_16());
        return $res;
    }

    function result_prime_max(): result
    {
        $t_grp = new test_groups($this->env);
        $res = new result($this->env->usr1);
        $res->set_grp($t_grp->group_prime_3());
        $res->set_src_grp($t_grp->group_const());
        $res->set_number(results::TV_INT);
        return $res;
    }

    function result_main(): result
    {
        $t_grp = new test_groups($this->env);
        $res = new result($this->env->usr1);
        $res->set_grp($t_grp->group_prime_max());
        $res->set_src_grp($t_grp->group_const());
        $res->set_number(results::TV_INT);
        return $res;
    }

    function result_main_max(): result
    {
        $t_frm = new test_formulas($this->env);
        $t_grp = new test_groups($this->env);
        $res = new result($this->env->usr1);
        $res->set_formula($t_frm->formula());
        $res->set_grp($t_grp->group_main_max());
        $res->set_src_grp($t_grp->group_const());
        $res->set_number(results::TV_INT);
        return $res;
    }

    /**
     * @return result_ui the filled result with the time of the last calculation as the api
     *                   sends it for a page request incl. the names of the result phrases and
     *                   of the creating formula, so that the result default page can be tested
     */
    function result_page_ui(): result_ui
    {
        $res = $this->result_main_max();
        $res->set_last_update(new DateTime(test_const::DUMMY_DATETIME));
        return new result_ui($res->api_json([api_types::INCL_RELATED, api_types::TEST_MODE]));
    }

    /**
     * @return result with all fields set to none standard to test if all fields are updated
     */
    function result_main_filled(): result
    {
        global $sys;
        $res = $this->result_main_max();
        $res->exclude();
        $res->set_share_id($sys->typ_lst->shr_typ->id(share_types::GROUP));
        $res->set_protection_id($sys->typ_lst->ptc_typ->id(protection_types::USER));
        return $res;
    }

    function result(): result
    {
        $t_grp = new test_groups($this->env);
        $res = new result($this->env->usr1);
        $res->set_grp($t_grp->group_16());
        $res->set_number(results::TV_INT);
        return $res;
    }

    function result_incomplete(): result
    {
        $t_grp = new test_groups($this->env);
        $res = $this->result();
        $res->set_grp($t_grp->group_incomplete());
        return $res;
    }

    function result_big(): result
    {
        $t_grp = new test_groups($this->env);
        $res = new result($this->env->usr1);
        $res->set_grp($t_grp->group_17_plus());
        $res->set_number(results::TV_INT);
        return $res;
    }


    function result_pct(): result
    {
        $t_wrd = new test_words($this->env);
        $res = new result($this->env->usr1);
        $wrd_pct = $t_wrd->word_percent();
        $phr_lst = new phrase_list($this->env->usr1);
        $phr_lst->add($wrd_pct->phrase());
        $res->grp()->set_phrase_list($phr_lst);
        $res->set_number(results::TV_PCT);
        return $res;
    }

    function result_add(): result
    {
        $t_grp = new test_groups($this->env);
        $res = new result($this->env->usr1);
        $res->set_grp($t_grp->group_add());
        $res->set_number(results::TV_FLOAT);
        return $res;
    }


    function result_list(): result_list
    {
        $lst = new result_list($this->env->usr1);
        $lst->add($this->result_simple_1());
        $lst->add($this->result_pct());
        return $lst;
    }

    /**
     * a result of the given phrases, mirroring test_values::value_for_phrases, so that a
     * calculated number can be used like a measured one
     *
     * @param array $phrases the phrases that name the result
     * @param float $number the calculated number
     * @return result the result of the given phrases
     */
    function result_for_phrases(array $phrases, float $number): result
    {
        $lst = new phrase_list($this->env->usr1);
        foreach ($phrases as $phr) {
            $lst->add($phr);
        }
        $res = new result($this->env->usr1);
        $res->set_grp($lst->get_grp_id(false));
        $res->set_number($number);
        return $res;
    }

    /**
     * the calculated numbers of the start page ranking, e.g. the happy time points that the
     * potential loss of a problem costs, which the table shows next to the measured numbers
     *
     * @return result_list the results of the start page ranking
     */
    function result_list_solution_prio(): result_list
    {
        $t_wrd = new test_words($this->env);
        $t_trp = new test_triples($this->env);
        $lst = new result_list($this->env->usr1);
        $lst->add($this->result_for_phrases([
            $t_trp->global_warming()->phrase(),
            $t_wrd->word_potential()->phrase(),
            $t_wrd->word_loss()->phrase(),
            $t_wrd->word_htp()->phrase()
        ], results::TV_PRIO_LOSS_HTP));
        return $lst;
    }

    /**
     * @return result_list_ui the frontend ranking results for unit testing
     */
    function result_list_solution_prio_ui(): result_list_ui
    {
        $tl = new test_lib();
        return $tl->list_to_ui($this->result_list_solution_prio(), [api_types::INCL_PHRASES]);
    }

}