<?php

/*

    web/view/component.php - function to execute a view component
    ----------------------

    to creat the HTML code to display a component

    The main sections of this object are
    - object vars:       the variables of this word object


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

namespace html\component\form;

use cfg\const\paths;
use html\component\component;
use html\const\paths as html_paths;
use html\html_base;
use html\html_names;
use html\phrase\phrase_list;
use html\sandbox\db_object as db_object_dsp;
use html\types\type_lists;
use html\word\triple;
use shared\api;
use shared\const\components;
use shared\const\views;
use shared\const\words;
use shared\enum\messages as msg_id;
use shared\library;
use shared\types\view_styles;
use shared\url_var;

include_once paths::DB . 'sql_db.php';
include_once html_paths::COMPONENT . 'component.php';
include_once html_paths::TYPES . 'type_lists.php';
include_once html_paths::HTML . 'html_names.php';
include_once html_paths::HTML . 'html_base.php';
include_once html_paths::SANDBOX . 'db_object.php';
include_once html_paths::PHRASE . 'phrase_list.php';
include_once html_paths::TYPES . 'view_style_list.php';
include_once html_paths::WORD . 'triple.php';
include_once paths::SHARED_CONST . 'components.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED_CONST . 'words.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_TYPES . 'view_styles.php';
include_once paths::SHARED . 'api.php';
include_once paths::SHARED . 'url_var.php';
include_once paths::SHARED . 'library.php';

class system_form extends component
{

    /**
     * start an HTML form, show the title and set and set the unique form name
     * @param string $form_name the name of the view which is also used for the html form name
     * @param msg_id|null $ui_msg_code_id the message id of the text that should be shown to the user in the user specific frontend language
     * @return string the html code to start a new form and display the tile
     */
    function form_tile(string $form_name, ?msg_id $ui_msg_code_id = null): string
    {
        global $mtr;

        $html = new html_base();
        $result = '';
        if ($ui_msg_code_id != null) {
            $result .= $html->text_h2($mtr->txt($ui_msg_code_id));
        }
        $result .= $html->form_start($form_name);
        return $result;
    }

    /**
     * create the HTML code to select this and the previous views
     *
     * @param int $msk_id the database id of the view that should be shown
     * @param int|null $id the database id of the object that should be shown in the view
     * @param string $back the history of the views and actions for the back und undo function
     * @return string the html code to include the back trace into the form result
     */
    function form_back(int $msk_id, ?int $id, string $back): string
    {
        $result = '';
        $html = new html_base();
        $result .= $html->input(url_var::MASK, $msk_id, html_base::INPUT_HIDDEN);
        $result .= $html->input(url_var::ID, $id, html_base::INPUT_HIDDEN);
        $result .= $html->input(url_var::BACK, $back, html_base::INPUT_HIDDEN);
        return $result;
    }

    /**
     * @return string the html code to check if the form changes has already confirmed by the user
     */
    function form_confirm(): string
    {
        $html = new html_base();
        return $html->input('confirm', '1', html_base::INPUT_HIDDEN);
    }

    /**
     * @param db_object_dsp $dbo the object
     * @return string the html code to show the object name to the user
     */
    function show_name(db_object_dsp $dbo): string
    {
        return $dbo->name();
    }

    /**
     * @param db_object_dsp $dbo the object
     * @return string the html code to request the object name from the user
     */
    function form_name(db_object_dsp $dbo, string $style_text): string
    {
        $html = new html_base();
        return $html->form_field(
            url_var::NAME,
            $dbo->name(),
            html_base::INPUT_TEXT,
            '', $style_text
        );
    }

    /**
     * @return string the html code to request the description from the user
     */
    function form_description(db_object_dsp $dbo): string
    {
        $html = new html_base();
        return $html->form_field(
            url_var::DESCRIPTION,
            $dbo->description(),
            html_base::INPUT_TEXT,
            '',
            view_styles::COL_SM_12
        );
    }

    /**
     * @param db_object_dsp $dbo the object
     * @return string the html code to request the object plural from the user
     */
    function form_plural(db_object_dsp $dbo, string $style_text): string
    {
        $html = new html_base();
        $plural = $dbo->plural();
        if ($plural == null) {
            $plural = '';
        }
        return $html->form_field(
            url_var::PLURAL,
            $plural,
            html_base::INPUT_TEXT,
            '', $style_text
        );
    }

    /**
     * TODO move form_field_triple_phrase_to to a const
     * TODO remove fixed pattern
     * @param db_object_dsp|triple $dbo the frontend phrase object with the id used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @return string the html code to request the description from the user
     */
    function form_phrase(
        db_object_dsp|triple $dbo,
        string $form_name,
        string $code_id = '',
        phrase_list $phr_lst = null,
        bool $test_mode = false
    ): string
    {
        $lib = new library();
        // TODO use a pattern base on user entry
        $pattern = '';
        if ($test_mode) {
            $pattern = words::MATH;
        }

        // get the selected phrase id
        $id = $dbo->id();
        $name = html_names::PHRASE;
        // TODO Prio 2 use a frontend language specific const for the label
        $label = 'word / triple';
        if ($code_id == components::FORM_PHRASE_FROM_CODE_ID) {
            $id = $dbo->from()?->id();
            $name .= html_names::SEP . html_names::FROM;
            $label = 'from word / triple';
        } elseif ($code_id == components::FORM_PHRASE_TO_CODE_ID) {
            $id = $dbo->to()?->id();
            $name .= html_names::SEP . html_names::TO;
            $label = 'to word / triple';
        }
        if ($id == null) {
            $id = 0;
            log_warning('id missing in ' . $dbo->dsp_id());
        }

        return $dbo->phrase_selector($form_name, $id, $phr_lst, $name, $label);
    }

    /**
     * create the html code for the form element to select the phrase type
     * @param db_object_dsp $dbo the frontend phrase object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the verb
     */
    function form_verb(db_object_dsp $dbo, string $form_name, ?type_lists $typ_lst): string
    {
        return $dbo->verb_selector($form_name, $typ_lst);
    }

    /**
     * create the html code for the form element to select the phrase type
     * @param db_object_dsp $dbo the frontend phrase object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the phrase type
     */
    function form_phrase_type(db_object_dsp $dbo, string $form_name, ?type_lists $typ_lst): string
    {
        return $dbo->phrase_type_selector($form_name, $typ_lst);
    }

    /**
     * create the html code for the form element to select the source type
     * @param db_object_dsp $dbo the frontend source object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the source type
     */
    function form_source_type(db_object_dsp $dbo, string $form_name, ?type_lists $typ_lst): string
    {
        return $dbo->source_type_selector($form_name, $typ_lst);
    }

    /**
     * create the html code for the form element to select the reference type
     * @param db_object_dsp $dbo the frontend reference object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the reference type
     */
    function form_ref_type(db_object_dsp $dbo, string $form_name, ?type_lists $typ_lst): string
    {
        return $dbo->ref_type_selector($form_name, $typ_lst);
    }

    /**
     * create the html code for the form element to select the formula type
     * @param db_object_dsp $dbo the frontend formula object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @return string the html code to select the formula type
     */
    function form_formula_type(db_object_dsp $dbo, string $form_name, ?type_lists $typ_lst): string
    {
        return $dbo->formula_type_selector($form_name, $typ_lst);
    }

    /**
     * create the html code for the form element to select the view type
     * @param db_object_dsp $dbo the frontend view object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the view type
     */
    function form_view_type(db_object_dsp $dbo, string $form_name, ?type_lists $typ_lst): string
    {
        return $dbo->view_type_selector($form_name, $typ_lst);
    }

    /**
     * create the html code for the form element to select the component type
     * @param db_object_dsp $dbo the frontend component object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the component type
     */
    function form_component_type(db_object_dsp $dbo, string $form_name, ?type_lists $typ_lst): string
    {
        return $dbo->component_type_selector($form_name, $typ_lst);
    }

    /**
     * create the html code for the form element to select the share type
     * @param db_object_dsp $dbo the frontend object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the share type
     */
    function form_share_type(db_object_dsp $dbo, string $form_name, ?type_lists $typ_lst): string
    {
        return $dbo->share_type_selector($form_name, $typ_lst);
    }

    /**
     * create the html code for the form element to select the protection type
     * @param db_object_dsp $dbo the frontend object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the protection type
     */
    function form_protection_type(db_object_dsp $dbo, string $form_name, ?type_lists $typ_lst): string
    {
        return $dbo->protection_type_selector($form_name, $typ_lst);
    }

    /**
     * create the html code for the form element to enter the formula expression
     * @param db_object_dsp $dbo the frontend formula object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @return string the html code to select the formula type
     */
    function form_formula_expression(db_object_dsp $dbo, string $form_name): string
    {
        $html = new html_base();
        return $html->dsp_form_fld(
            url_var::NEED_ALL,
            $dbo->user_expression(),
            "Expression:",
            view_styles::COL_SM_12);
    }

    /**
     * create the html code for the form flag to set that the formula needs all fields to be set
     * @param db_object_dsp $dbo the frontend formula object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @return string the html code to select the formula type
     */
    function form_formula_all_fields(db_object_dsp $dbo, string $form_name): string
    {
        $html = new html_base();
        return $html->dsp_form_fld_checkbox(
            url_var::NEED_ALL,
            $dbo->need_all(),
            "calculate only if all values used in the formula exist");
    }

    /**
     * @return string the html code for a form cancel button
     */
    function form_cancel(int $msk_id, ?int $id): string
    {
        $html = new html_base();
        $views = new views();
        $msk_ci = $views->id_to_code_id($msk_id);
        $base_ci = $views->system_to_base($msk_ci);
        $base_id = $views->code_id_to_id($base_ci);
        $result = '';
        $url = api::HOST_SAME . api::MAIN_SCRIPT
            . url_var::PAR . url_var::MASK . url_var::EQ . $base_id;
        if ($id != 0) {
            $url .= url_var::ADD . url_var::ID . url_var::EQ . $id;
        }
        $result .= $html->ref($url, 'Cancel', '', html_base::BS_BTN . ' ' . html_base::BS_BTN_CANCEL);
        return $result;
    }

    /**
     * @return string the html code for a form save button
     */
    function form_save(): string
    {
        $html = new html_base();
        return $html->button('Save');
    }

    /**
     * @return string the html code for a form save button
     */
    function form_del(): string
    {
        $html = new html_base();
        return $html->button('Delete', html_base::BS_BTN_DEL);
    }

    /**
     * @return string that simply closes the form
     */
    function form_end(): string
    {
        $html = new html_base();
        return $html->form_end();
    }

    /**
     * @return string combine the next elements to one row
     */
    function row_start(): string
    {
        $html = new html_base();
        return $html->row_start();
    }

    /**
     * @return string combine the next elements to one row and align to the right
     */
    function row_right(): string
    {
        $html = new html_base();
        return $html->row_right();
    }

    /**
     * @return string just to indicate that a row ends
     */
    function row_end(): string
    {
        $html = new html_base();
        return $html->row_end();
    }

}
