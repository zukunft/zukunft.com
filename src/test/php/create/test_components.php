<?php

/*

    test/create/test_components.php - create the test component objects
    -------------------------------


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

include_once paths::MODEL_COMPONENT . 'component.php';
include_once paths::MODEL_COMPONENT . 'component_link.php';
include_once paths::MODEL_COMPONENT . 'component_link_list.php';
include_once paths::MODEL_COMPONENT . 'component_link_type.php';
include_once paths::MODEL_COMPONENT . 'component_list.php';
include_once paths::MODEL_VIEW . 'term_view.php';
include_once paths::MODEL_VIEW . 'view.php';
include_once paths::MODEL_VIEW . 'view_link_type.php';
include_once paths::SHARED_CONST . 'components.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_TYPES . 'component_type.php';
include_once paths::SHARED_TYPES . 'position_types.php';
include_once paths::SHARED_TYPES . 'protection_type.php';
include_once paths::SHARED_TYPES . 'share_type.php';
include_once paths::SHARED_TYPES . 'view_styles.php';
include_once html_paths::COMPONENT . 'component_list.php';
include_once html_paths::FORMULA . 'formula_list.php';
include_once html_paths::VIEW . 'view_list.php';
include_once test_paths::CREATE . 'test_const.php';
include_once test_paths::UTILS . 'test_cleanup.php';

use Zukunft\ZukunftCom\main\php\cfg\component\component;
use Zukunft\ZukunftCom\main\php\cfg\component\component_link;
use Zukunft\ZukunftCom\main\php\cfg\component\component_link_list;
use Zukunft\ZukunftCom\main\php\cfg\component\component_link_type;
use Zukunft\ZukunftCom\main\php\cfg\component\component_list;
use Zukunft\ZukunftCom\main\php\cfg\view\term_view;
use Zukunft\ZukunftCom\main\php\cfg\view\view;
use Zukunft\ZukunftCom\main\php\cfg\view\view_link_type;
use Zukunft\ZukunftCom\main\php\shared\const\components;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\types\component_type;
use Zukunft\ZukunftCom\main\php\shared\types\position_types;
use Zukunft\ZukunftCom\main\php\shared\types\protection_type;
use Zukunft\ZukunftCom\main\php\shared\types\share_type;
use Zukunft\ZukunftCom\main\php\shared\types\view_styles;
use Zukunft\ZukunftCom\main\php\web\component\component_list as component_list_ui;
use Zukunft\ZukunftCom\main\php\web\formula\formula_list as formula_list_ui;
use Zukunft\ZukunftCom\main\php\web\view\view_list as view_list_ui;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class test_components
{

    /*
     * init
     */

    // use the global test environment
    private test_cleanup $env;

    function __construct(test_cleanup $env) {
        $this->env = $env;
    }


    /**
     * @return component_list with a list of suggested component for a word
     */
    function component_list_word(): component_list
    {
        $lst = new component_list($this->env->usr1);
        $lst->add($this->component_filled());
        return $lst;
    }

    /**
     * @return formula_list_ui a sample frontend formula list
     */
    function formula_list_dsp(): formula_list_ui
    {
        $t_msk = new test_views($this->env);
        return new formula_list_ui($t_msk->view_list_word()->api_json());
    }

    /**
     * @return component_list_ui a sample frontend component list
     */
    function component_list_ui(): component_list_ui
    {
        return new component_list_ui($this->component_list_word()->api_json());
    }

    function view_link(): term_view
    {
        global $sys;
        $t_wrd = new test_words($this->env);
        $t_msk = new test_views($this->env);
        $lnk = new term_view($this->env->usr1);
        $lnk->set(1, $t_msk->view(), $t_wrd->word()->term());
        $lnk->set_predicate_id($sys->typ_lst->msk_lnk_typ->id(view_link_type::DEFAULT));
        $lnk->description = 2;
        return $lnk;
    }

    function view_link_filled(): term_view
    {
        global $sys;
        $lnk = $this->view_link();
        $lnk->exclude();
        $lnk->set_share_id($sys->typ_lst->shr_typ->id(share_type::GROUP));
        $lnk->set_protection_id($sys->typ_lst->ptc_typ->id(protection_type::USER));
        return $lnk;
    }

    function view_link_filled_add(): term_view
    {
        $t_wrd = new test_words($this->env);
        $t_msk = new test_views($this->env);
        $lnk = $this->view_link_filled();
        $lnk->include();
        $lnk->id = 0;
        $lnk->set_view($t_msk->view_filled_add());
        $lnk->set_term($t_wrd->word_filled_add()->term());
        return $lnk;
    }

    function component(): component
    {
        $cmp = new component($this->env->usr1);
        $cmp->set(components::WORD_ID, components::WORD_NAME);
        $cmp->set_type(component_type::PHRASE_NAME, $this->env->usr1);
        $cmp->description = components::WORD_COM;
        return $cmp;
    }

    function component_matrix(): component
    {
        $cmp = new component($this->env->usr1);
        $cmp->set(components::MATRIX_ID, components::MATRIX_NAME);
        $cmp->set_type(component_type::CALC_SHEET, $this->env->usr1);
        $cmp->description = components::MATRIX_COM;
        return $cmp;
    }

    /**
     * @return component with all fields set to check if the save and load process is complete
     */
    function component_filled(): component
    {
        global $sys;
        $t_phr = new test_phrases($this->env);
        $cmp = new component($this->env->usr1);
        $cmp->set(components::WORD_ID, components::WORD_NAME);
        $cmp->description = components::WORD_COM;
        $cmp->set_type(component_type::TEXT, $this->env->usr1);
        $cmp->set_style(view_styles::COL_SM_4);
        $cmp->set_code_id(components::FORM_TITLE, $this->env->usr_system);
        $cmp->set_usage(test_const::DUMMY_USAGE_COMPONENT);
        $cmp->ui_msg_code_id = msg_id::PLEASE_SELECT;
        $cmp->ui_msg_code_id_vars = msg_id::DONE;
        $cmp->ui_msg_code_id_exception = msg_id::ERROR_TEXT;
        $cmp->ui_msg_value_exception = 0;
        $cmp->set_row_phrase($t_phr->year());
        $cmp->set_col_phrase($t_phr->canton());
        $cmp->set_col_sub_phrase($t_phr->city());
        // TODO Prio 2 activate
        //$cmp->set_formula($this->formula());
        $cmp->set_link_type(component_link_type::EXPRESSION);
        $cmp->exclude();
        $cmp->set_share_id($sys->typ_lst->shr_typ->id(share_type::GROUP));
        $cmp->set_protection_id($sys->typ_lst->ptc_typ->id(protection_type::USER));
        return $cmp;
    }

    /**
     * @return component with all fields set to check if the save and load process is complete
     */
    function component_filled_all(): component
    {
        $t_frm = new test_formulas($this->env);
        $cmp = $this->component_filled();
        $cmp->set_formula($t_frm->formula());
        return $cmp;
    }

    /**
     * @return component with all fields set and a reserved test name for testing the db write function
     */
    function component_filled_add(): component
    {
        $cmp = $this->component_filled();
        $cmp->include();
        $cmp->id = 0;
        $cmp->set_name(components::TEST_ADD_NAME);
        return $cmp;
    }

    /**
     * @return component to test the sql insert via function
     */
    function component_add_by_func(): component
    {
        $cmp = new component($this->env->usr1);
        $cmp->set_name(components::TEST_ADD_VIA_FUNC_NAME);
        $cmp->set_type(component_type::TEXT, $this->env->usr1);
        return $cmp;
    }

    /**
     * @return component to test the sql insert without use of function
     */
    function component_add_by_sql(): component
    {
        $cmp = new component($this->env->usr1);
        $cmp->set_name(components::TEST_ADD_VIA_SQL_NAME);
        $cmp->set_type(component_type::TEXT, $this->env->usr1);
        return $cmp;
    }

    function component_word_add_title(): component
    {
        $cmp = new component($this->env->usr1);
        $cmp->set(components::WORD_ID, components::FORM_TITLE_NAME);
        $cmp->set_type(component_type::FORM_TITLE, $this->env->usr1);
        $cmp->description = components::FORM_TITLE_COM;
        $cmp->set_code_id(components::FORM_TITLE, $this->env->usr_system);
        return $cmp;
    }

    function component_word_add_back_stack(): component
    {
        $cmp = new component($this->env->usr1);
        $cmp->set(components::MATRIX_ID, components::FORM_BACK_NAME);
        $cmp->set_type(component_type::FORM_HIDDEN_BACK, $this->env->usr1);
        $cmp->description = components::FORM_BACK_COM;
        $cmp->set_code_id(components::FORM_BACK, $this->env->usr_system);
        return $cmp;
    }

    function component_word_add_button_confirm(): component
    {
        $cmp = new component($this->env->usr1);
        $cmp->set(3, components::FORM_CONFIRM_NAME);
        $cmp->set_type(component_type::FORM_HIDDEN_STEP, $this->env->usr1);
        $cmp->description = components::FORM_CONFIRM_COM;
        $cmp->set_code_id(components::FORM_CONFIRM, $this->env->usr_system);
        return $cmp;
    }

    function component_word_add_name(): component
    {
        $cmp = new component($this->env->usr1);
        $cmp->set(components::FORM_NAME_ID, components::FORM_NAME_NAME);
        $cmp->set_type(component_type::FORM_FIELD_NAME, $this->env->usr1);
        $cmp->description = components::FORM_NAME_COM;
        $cmp->set_code_id(components::FORM_NAME, $this->env->usr_system);
        return $cmp;
    }

    function component_word_add_description(): component
    {
        $cmp = new component($this->env->usr1);
        $cmp->set(5, components::FORM_DESCRIPTION_NAME);
        $cmp->set_type(component_type::FORM_FIELD_DESCRIPTION, $this->env->usr1);
        $cmp->description = components::FORM_DESCRIPTION_COM;
        $cmp->set_code_id(components::FORM_DESCRIPTION, $this->env->usr_system);
        return $cmp;
    }

    function component_word_add_plural(): component
    {
        $cmp = new component($this->env->usr1);
        $cmp->set(components::FORM_PLURAL_ID, components::FORM_PLURAL_NAME);
        $cmp->set_type(component_type::FORM_FIELD_PLURAL, $this->env->usr1);
        $cmp->description = components::FORM_PLURAL_COM;
        $cmp->set_code_id(components::FORM_PLURAL, $this->env->usr_system);
        return $cmp;
    }

    function component_word_add_phrase_type(): component
    {
        $cmp = new component($this->env->usr1);
        $cmp->set(6, components::FORM_PHRASE_TYPE_NAME);
        $cmp->set_type(component_type::FORM_SELECT_PHRASE_TYPE, $this->env->usr1);
        $cmp->description = components::FORM_PHRASE_TYPE_COM;
        $cmp->set_code_id(components::FORM_PHRASE_TYPE, $this->env->usr_system);
        return $cmp;
    }

    function component_word_add_share_type(): component
    {
        $cmp = new component($this->env->usr1);
        $cmp->set(7, components::FORM_SHARE_TYPE_NAME);
        $cmp->set_type(component_type::FORM_SHARE_TYPE, $this->env->usr1);
        $cmp->description = components::FORM_SHARE_TYPE_COM;
        $cmp->set_code_id(components::FORM_SHARE_TYPE, $this->env->usr_system);
        return $cmp;
    }

    function component_word_add_protection_type(): component
    {
        $cmp = new component($this->env->usr1);
        $cmp->set(8, components::FORM_PROTECTION_TYPE_NAME);
        $cmp->set_type(component_type::FORM_PROTECTION_TYPE, $this->env->usr1);
        $cmp->description = components::FORM_PROTECTION_TYPE_COM;
        $cmp->set_code_id(components::FORM_PROTECTION_TYPE, $this->env->usr_system);
        return $cmp;
    }

    function component_word_add_cancel(): component
    {
        $cmp = new component($this->env->usr1);
        $cmp->set(9, components::FORM_CANCEL_NAME);
        $cmp->set_type(component_type::FORM_BUTTON_CANCEL, $this->env->usr1);
        $cmp->description = components::FORM_CANCEL_COM;
        $cmp->set_code_id(components::FORM_CANCEL, $this->env->usr_system);
        return $cmp;
    }

    function component_word_add_save(): component
    {
        $cmp = new component($this->env->usr1);
        $cmp->set(10, components::FORM_SAVE_NAME);
        $cmp->set_type(component_type::FORM_BUTTON_SAVE, $this->env->usr1);
        $cmp->description = components::FORM_SAVE_COM;
        $cmp->set_code_id(components::FORM_SAVE, $this->env->usr_system);
        return $cmp;
    }

    function component_word_add_form_end(): component
    {
        $cmp = new component($this->env->usr1);
        $cmp->set(11, components::FORM_END_NAME);
        $cmp->set_type(component_type::FORM_END, $this->env->usr1);
        $cmp->description = components::FORM_END_COM;
        $cmp->set_code_id(components::FORM_END, $this->env->usr_system);
        return $cmp;
    }

    function component_list(): component_list
    {
        $lst = new component_list($this->env->usr1);
        $lst->add($this->component());
        $lst->add($this->component_word_add_share_type());
        return $lst;
    }

    function component_link(): component_link
    {
        $t_msk = new test_views($this->env);
        $lnk = new component_link($this->env->usr1);
        $lnk->set(1, $t_msk->view(), $this->component(), 1);
        return $lnk;
    }

    function component_matrix_link(): component_link
    {
        $t_msk = new test_views($this->env);
        $lnk = new component_link($this->env->usr1);
        $lnk->set(2, $t_msk->view(), $this->component_matrix(), 2);
        return $lnk;
    }


    function component_link_filled(): component_link
    {
        global $sys;
        $t_msk = new test_views($this->env);
        $lnk = new component_link($this->env->usr1);
        $lnk->set(1, $t_msk->view(), $this->component(), 1);
        $lnk->set_predicate(component_link_type::EXPRESSION);
        $lnk->set_pos_type(position_types::SIDE);
        $lnk->set_style(view_styles::COL_SM_4);
        $lnk->exclude();
        $lnk->set_share_id($sys->typ_lst->shr_typ->id(share_type::GROUP));
        $lnk->set_protection_id($sys->typ_lst->ptc_typ->id(protection_type::USER));
        return $lnk;
    }

    function component_link_filled_add(): component_link
    {
        $t_msk = new test_views($this->env);
        $lnk = $this->component_link_filled();
        $lnk->include();
        $lnk->id = 0;
        $lnk->set_view($t_msk->view_filled_add());
        $lnk->set_component($this->component_filled_add());
        return $lnk;
    }

    function component_link_list(): component_link_list
    {
        $lst = new component_link_list($this->env->usr1);
        $lst->add_link($this->component_link());
        $lst->add_link($this->component_matrix_link());
        return $lst;
    }

    function components_word_add(view $msk): component_link_list
    {
        $pos = 1;
        $lst = new component_link_list($this->env->usr1);
        $lst->add($pos, $msk, $this->component_word_add_title(), $pos);
        $pos++;
        $lst->add($pos, $msk, $this->component_word_add_back_stack(), $pos);
        $pos++;
        $lst->add($pos, $msk, $this->component_word_add_button_confirm(), $pos);
        $pos++;
        $lst->add($pos, $msk, $this->component_word_add_name(), $pos);
        $pos++;
        $lst->add($pos, $msk, $this->component_word_add_description(), $pos);
        $pos++;
        $lst->add($pos, $msk, $this->component_word_add_plural(), $pos);
        $pos++;
        $lst->add($pos, $msk, $this->component_word_add_phrase_type(), $pos);
        $pos++;
        $lst->add($pos, $msk, $this->component_word_add_share_type(), $pos);
        $pos++;
        $lst->add($pos, $msk, $this->component_word_add_protection_type(), $pos);
        $pos++;
        $lst->add($pos, $msk, $this->component_word_add_cancel(), $pos);
        $pos++;
        $lst->add($pos, $msk, $this->component_word_add_save(), $pos);
        $pos++;
        $lst->add($pos, $msk, $this->component_word_add_form_end(), $pos);
        return $lst;
    }

}