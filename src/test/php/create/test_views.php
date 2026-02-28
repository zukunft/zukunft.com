<?php

/*

    test/create/test_views.php - create the test view objects
    --------------------------


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
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::MODEL_VIEW . 'view.php';
include_once paths::MODEL_VIEW . 'view_list.php';
include_once paths::MODEL_VIEW . 'view_relation.php';
include_once paths::MODEL_VIEW . 'term_view.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED_TYPES . 'protection_types.php';
include_once paths::SHARED_TYPES . 'share_types.php';
include_once paths::SHARED_TYPES . 'view_styles.php';
include_once paths::SHARED_TYPES . 'view_types.php';
include_once paths::SHARED_TYPES . 'view_link_types.php';
include_once paths::SHARED_TYPES . 'view_relation_types.php';
include_once html_paths::VIEW . 'view_list.php';
include_once test_paths::CREATE . 'test_const.php';
include_once test_paths::UTILS . 'test_cleanup.php';

use Zukunft\ZukunftCom\main\php\cfg\view\view;
use Zukunft\ZukunftCom\main\php\cfg\view\view_list;
use Zukunft\ZukunftCom\main\php\cfg\view\view_relation;
use Zukunft\ZukunftCom\main\php\cfg\view\term_view;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\types\protection_types;
use Zukunft\ZukunftCom\main\php\shared\types\share_types;
use Zukunft\ZukunftCom\main\php\shared\types\view_styles;
use Zukunft\ZukunftCom\main\php\shared\types\view_types;
use Zukunft\ZukunftCom\main\php\shared\types\view_link_types;
use Zukunft\ZukunftCom\main\php\shared\types\view_relation_types;
use Zukunft\ZukunftCom\main\php\web\view\view_list as view_list_ui;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class test_views
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

    function view(): view
    {
        $msk = new view($this->env->usr1);
        $msk->set(views::START_ID, views::START_NAME);
        $msk->description = views::START_COM;
        return $msk;
    }

    function view_code_id(): view
    {
        $msk = new view($this->env->usr1);
        $msk->set(views::START_ID, views::START_NAME);
        $msk->description = views::START_COM;
        $msk->set_code_id_db(views::START_CODE);
        return $msk;
    }

    function view_protected(): view
    {
        global $sys;
        $msk = new view($this->env->usr1);
        $msk->set(views::START_ID, views::START_NAME);
        $msk->description = views::START_COM;
        $msk->set_code_id_db(views::START_CODE);
        $msk->set_type(view_types::ENTRY, $this->env->usr1);
        $msk->set_protection_id($sys->typ_lst->ptc_typ->id(protection_types::ADMIN));
        return $msk;
    }

    function view_add(): view
    {
        $msk = new view($this->env->usr1);
        $msk->set_name(views::TEST_ADD);
        return $msk;
    }

    /**
     * used to test the view relation where the log components are added to the parent view
     * @return view with the parent view to change a word
     */
    function view_word_edit(): view
    {
        $msk = new view($this->env->usr1);
        $msk->set(views::WORD_EDIT_ID, views::WORD_EDIT);
        return $msk;
    }

    /**
     * used to test the view relation where the log components are added to the parent view
     * @return view with the parent view to change a word
     */
    function view_word_log(): view
    {
        $msk = new view($this->env->usr1);
        $msk->set(views::WORD_LOG_ID, views::WORD_LOG);
        return $msk;
    }

    /**
     * @return view with sample data to view a phrase from the science point of view
     */
    function view_science(): view
    {
        $msk = new view($this->env->usr1);
        $msk->set(views::SCIENCE_ID, views::SCIENCE);
        $msk->description = views::SCIENCE_NAME;
        return $msk;
    }

    /**
     * @return view with sample data to show mainly related words that are relevant in sciences
     */
    function view_historic(): view
    {
        $msk = new view($this->env->usr1);
        $msk->set(views::HISTORIC_ID, views::HISTORIC_NAME);
        $msk->description = views::HISTORIC_COM;
        return $msk;
    }

    /**
     * @return view a view from the biological point of view e.g. with the
     */
    function view_biological(): view
    {
        $msk = new view($this->env->usr1);
        $msk->set(views::BIOLOGICAL_ID, views::BIOLOGICAL_NAME);
        $msk->description = views::BIOLOGICAL_COM;
        return $msk;
    }

    /**
     * @return view with sample data to show mainly related words that are relevant in sciences
     */
    function view_education(): view
    {
        $msk = new view($this->env->usr1);
        $msk->set(views::EDUCATION_ID, views::EDUCATION_NAME);
        $msk->description = views::EDUCATION_COM;
        return $msk;
    }

    /**
     * @return view with sample data to show mainly related words that are relevant in sciences
     */
    function view_touristic(): view
    {
        $msk = new view($this->env->usr1);
        $msk->set(views::TOURISTIC_ID, views::TOURISTIC_NAME);
        $msk->description = views::TOURISTIC_COM;
        return $msk;
    }

    /**
     * @return view with sample data to show mainly related words that are relevant in sciences
     */
    function view_graph(): view
    {
        $msk = new view($this->env->usr1);
        $msk->set(views::GRAPH_ID, views::GRAPH_NAME);
        $msk->description = views::GRAPH_COM;
        return $msk;
    }

    /**
     * @return view with sample data to show mainly related words that are relevant in sciences
     */
    function view_simple(): view
    {
        $msk = new view($this->env->usr1);
        $msk->set(views::SIMPLE_ID, views::SIMPLE_NAME);
        $msk->description = views::SIMPLE_COM;
        return $msk;
    }

    /**
     * @return view created by a user, so without a code_id
     */
    function view_added(): view
    {
        $msk = new view($this->env->usr1);
        $msk->set(views::START_ID, views::START_NAME);
        $msk->description = views::START_COM;
        return $msk;
    }

    /**
     * @return view where a mandatory var is missing which is in this case the name and the id
     */
    function view_incomplete(): view
    {
        $msk = new view($this->env->usr1);
        $msk->description = views::START_COM;
        return $msk;
    }

    /**
     * @return view with all fields e.g. to check if all fields are covered by the sql insert statement creation
     */
    function view_filled(): view
    {
        global $sys;
        $msk = new view($this->env->usr1);
        $msk->set(views::START_ID, views::START_NAME);
        $msk->description = views::START_COM;
        $msk->set_code_id_db(views::START_CODE);
        $msk->set_type(view_types::ENTRY, $this->env->usr1);
        $msk->set_style(view_styles::COL_SM_4);
        $msk->set_usage(test_const::DUMMY_USAGE_VIEW);
        $msk->exclude();
        $msk->set_share_id($sys->typ_lst->shr_typ->id(share_types::GROUP));
        $msk->set_protection_id($sys->typ_lst->ptc_typ->id(protection_types::USER));
        return $msk;
    }

    /**
     * @return view with all fields set and a reserved test name for testing the db write function
     */
    function view_filled_add(): view
    {
        $msk = $this->view_filled();
        $msk->include();
        $msk->id = 0;
        $msk->set_code_id_db(views::TEST_ADD);
        $msk->set_name(views::TEST_ADD_NAME);
        return $msk;
    }

    /**
     * @return view to test the sql insert via function
     */
    function view_add_by_func(): view
    {
        $msk = new view($this->env->usr1);
        $msk->set_name(views::TEST_ADD_VIA_FUNC_NAME);
        return $msk;
    }

    function view_with_components(): view
    {
        $t_cmp = new test_components($this->env);
        $msk = $this->view_protected();
        $msk->cmp_lnk_lst = $t_cmp->component_link_list();
        return $msk;
    }

    function view_word_add(): view
    {
        $t_cmp = new test_components($this->env);
        $msk = new view($this->env->usr1);
        $msk->set(views::TEST_FORM_ID, views::TEST_FORM_NAME);
        $msk->description = views::TEST_FORM_COM;
        $msk->set_code_id_db(views::TEST_FORM);
        $msk->cmp_lnk_lst = $t_cmp->components_word_add($msk);
        return $msk;
    }

    function view_list(): view_list
    {
        $lst = new view_list($this->env->usr1);
        $lst->add($this->view_with_components());
        $lst->add($this->view_word_add());
        return $lst;
    }

    /**
     * @return view_list with a list of suggested views for a word
     */
    function view_list_word(): view_list
    {
        $lst = new view_list($this->env->usr1);
        $lst->add($this->view_science());
        $lst->add($this->view_historic());
        $lst->add($this->view_education());
        $lst->add($this->view_touristic());
        return $lst;
    }

    /**
     * TODO add the relevance to test the sorting
     * @return view_list with a longer list of suggested views for a word
     */
    function view_list_word_long(): view_list
    {
        $lst = $this->view_list_word();
        $lst->add($this->view_biological());
        $lst->add($this->view_graph());
        $lst->add($this->view_simple());
        return $lst;
    }

    /**
     * @return view_list_ui a sample frontend view list with more than 5 entries
     */
    function view_list_long_dsp(): view_list_ui
    {
        return new view_list_ui($this->view_list_word_long()->api_json());
    }

    /**
     * @return view_list_ui a sample frontend view list
     */
    function view_list_ui(): view_list_ui
    {
        $t_msk = new test_views($this->env);
        return new view_list_ui($t_msk->view_list_word()->api_json());
    }

    function view_relation(): view_relation
    {
        $mrl = new view_relation($this->env->usr1);
        $mrl->id = 1;
        $mrl->set_parent($this->view_word_edit());
        $mrl->set_relation_type(view_relation_types::ADD);
        $mrl->set_child($this->view_word_log());
        $mrl->start_pos = 15;
        return $mrl;
    }

    function view_relation_filled(): view_relation
    {
        global $sys;
        // TODO Prio 0 maybe use msk_rel instead of mrl
        $mrl = $this->view_relation();
        $mrl->description = 'add usage and log of a word';
        $mrl->exclude();
        $mrl->set_share_id($sys->typ_lst->shr_typ->id(share_types::GROUP));
        $mrl->set_protection_id($sys->typ_lst->ptc_typ->id(protection_types::USER));
        return $mrl;
    }

    /**
     * @return view_relation with all fields set and a reserved test name for testing the db write function
     */
    function view_relation_filled_add(): view_relation
    {
        $msk_rel = $this->view_relation_filled();
        $msk_rel->include();
        $msk_rel->id = 0;
        // TODO Prio 3 maybe used the added test view and component
        //$msk_rel->set_parent($this->view_filled_add());
        //$msk_rel->set_child($this->view_part_filled_add());
        return $msk_rel;
    }

    function term_view(): term_view
    {
        $trm_msk = new term_view($this->env->usr1);
        $t_wrd = new test_words($this->env);
        $trm_msk->id = 1;
        $trm_msk->set_term($t_wrd->word()->term());
        $trm_msk->set_predicate(view_link_types::DEFAULT);
        $trm_msk->set_view($this->view());
        return $trm_msk;
    }

    function term_view_filled(): term_view
    {
        global $sys;
        $trm_msk = $this->term_view();
        $trm_msk->description = 'add usage and log of a word';
        $trm_msk->exclude();
        $trm_msk->set_share_id($sys->typ_lst->shr_typ->id(share_types::GROUP));
        $trm_msk->set_protection_id($sys->typ_lst->ptc_typ->id(protection_types::USER));
        return $trm_msk;
    }

    /**
     * @return term_view with all fields set and a reserved test name for testing the db write function
     */
    function term_view_filled_add(): term_view
    {
        $msk_lnk = $this->term_view_filled();
        $msk_lnk->include();
        $msk_lnk->id = 0;
        // TODO Prio 3 maybe used the added test view and component
        //$msk_lnk->set_parent($this->view_filled_add());
        //$msk_lnk->set_child($this->view_part_filled_add());
        return $msk_lnk;
    }

}