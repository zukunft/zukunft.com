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
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED_TYPES . 'protection_type.php';
include_once paths::SHARED_TYPES . 'share_type.php';
include_once paths::SHARED_TYPES . 'view_styles.php';
include_once paths::SHARED_TYPES . 'view_type.php';
include_once html_paths::VIEW . 'view_list.php';
include_once test_paths::CREATE . 'test_const.php';
include_once test_paths::UTILS . 'test_cleanup.php';

use Zukunft\ZukunftCom\main\php\cfg\view\view;
use Zukunft\ZukunftCom\main\php\cfg\view\view_list;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\types\protection_type;
use Zukunft\ZukunftCom\main\php\shared\types\share_type;
use Zukunft\ZukunftCom\main\php\shared\types\view_styles;
use Zukunft\ZukunftCom\main\php\shared\types\view_type;
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
        $msk->set_code_id_db(views::START_CODE);
        return $msk;
    }

    function view_protected(): view
    {
        global $ptc_typ_cac;
        $msk = new view($this->env->usr1);
        $msk->set(views::START_ID, views::START_NAME);
        $msk->description = views::START_COM;
        $msk->set_code_id_db(views::START_CODE);
        $msk->set_type(view_type::ENTRY, $this->env->usr1);
        $msk->set_protection_id($ptc_typ_cac->id(protection_type::ADMIN));
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
     * @return view with all fields e.g. to check if all fields are covered by the sql insert statement creation
     */
    function view_filled(): view
    {
        global $shr_typ_cac;
        global $ptc_typ_cac;
        $msk = new view($this->env->usr1);
        $msk->set(views::START_ID, views::START_NAME);
        $msk->description = views::START_COM;
        $msk->set_code_id_db(views::START_CODE);
        $msk->set_type(view_type::ENTRY, $this->env->usr1);
        $msk->set_style(view_styles::COL_SM_4);
        $msk->set_usage(test_const::DUMMY_USAGE);
        $msk->exclude();
        $msk->set_share_id($shr_typ_cac->id(share_type::GROUP));
        $msk->set_protection_id($ptc_typ_cac->id(protection_type::USER));
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

    /**
     * @return view to test the sql insert without use of function
     */
    function view_add_by_sql(): view
    {
        $msk = new view($this->env->usr1);
        $msk->set_name(views::TEST_ADD_VIA_SQL_NAME);
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
    function view_list_dsp(): view_list_ui
    {
        $t_msk = new test_views($this->env);
        return new view_list_ui($t_msk->view_list_word()->api_json());
    }

}