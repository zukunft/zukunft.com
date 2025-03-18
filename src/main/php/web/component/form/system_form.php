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

include_once WEB_COMPONENT_PATH . 'component.php';
include_once DB_PATH . 'sql_db.php';
include_once WEB_HTML_PATH . 'html_base.php';
include_once WEB_SANDBOX_PATH . 'db_object.php';
include_once WEB_TYPES_PATH . 'view_style_list.php';
include_once SHARED_CONST_PATH . 'views.php';
include_once SHARED_CONST_PATH . 'words.php';
include_once SHARED_ENUM_PATH . 'messages.php';
include_once SHARED_TYPES_PATH . 'view_styles.php';
include_once SHARED_PATH . 'api.php';
include_once SHARED_PATH . 'library.php';

use html\component\component;
use html\html_base;
use html\sandbox\db_object as db_object_dsp;
use shared\api;
use shared\library;
use shared\const\views;
use shared\const\words;
use shared\enum\messages as msg_id;
use shared\types\view_styles;

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
        $result .= $html->input(api::URL_VAR_MASK, $msk_id, html_base::INPUT_HIDDEN);
        $result .= $html->input(api::URL_VAR_ID, $id, html_base::INPUT_HIDDEN);
        $result .= $html->input(api::URL_VAR_BACK, $back, html_base::INPUT_HIDDEN);
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
            api::URL_VAR_NAME,
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
            api::URL_VAR_DESCRIPTION,
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
            api::URL_VAR_PLURAL,
            $plural,
            html_base::INPUT_TEXT,
            '', $style_text
        );
    }

    /**
     * TODO replace _add with a parameter value
     * TODO move form_field_triple_phrase_to to a const
     * TODO remove fixed pattern
     * @return string the html code to request the description from the user
     */
    function form_phrase(db_object_dsp $dbo, bool $test_mode = false, string $name = ''): string
    {
        $lib = new library();
        $form_name = $lib->class_to_name($dbo::class) . '_add';
        // TODO use a pattern base on user entry
        $pattern = '';
        if ($test_mode) {
            $pattern = words::MATH;
        }
        // TODO activate Prio 3
        //if ($this->code_id == 'form_field_triple_phrase_from') {
        if ($name == 'system form triple phrase from') {
            return $dbo->phrase_selector_old('from', $form_name, 'from:', '', $dbo->id(), $pattern);
        } else {
            return $dbo->phrase_selector_old('to', $form_name, 'to:', '', $dbo->id(), $pattern);
        }
    }

    /**
     * create the html code for the form element to select the phrase type
     * @param db_object_dsp $dbo the frontend phrase object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @return string the html code to select the verb
     */
    function form_verb(db_object_dsp $dbo, string $form_name): string
    {
        return $dbo->verb_selector($form_name);
    }

    /**
     * create the html code for the form element to select the phrase type
     * @param db_object_dsp $dbo the frontend phrase object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @return string the html code to select the phrase type
     */
    function form_phrase_type(db_object_dsp $dbo, string $form_name): string
    {
        return $dbo->phrase_type_selector($form_name);
    }

    /**
     * create the html code for the form element to select the source type
     * @param db_object_dsp $dbo the frontend source object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @return string the html code to select the source type
     */
    function form_source_type(db_object_dsp $dbo, string $form_name): string
    {
        return $dbo->source_type_selector($form_name);
    }

    /**
     * create the html code for the form element to select the reference type
     * @param db_object_dsp $dbo the frontend reference object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @return string the html code to select the reference type
     */
    function form_ref_type(db_object_dsp $dbo, string $form_name): string
    {
        return $dbo->ref_type_selector($form_name);
    }

    /**
     * create the html code for the form element to select the formula type
     * @param db_object_dsp $dbo the frontend formula object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @return string the html code to select the formula type
     */
    function form_formula_type(db_object_dsp $dbo, string $form_name): string
    {
        return $dbo->formula_type_selector($form_name);
    }

    /**
     * create the html code for the form element to select the view type
     * @param db_object_dsp $dbo the frontend view object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @return string the html code to select the view type
     */
    function form_view_type(db_object_dsp $dbo, string $form_name): string
    {
        return $dbo->view_type_selector($form_name);
    }

    /**
     * create the html code for the form element to select the component type
     * @param db_object_dsp $dbo the frontend component object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @return string the html code to select the component type
     */
    function form_component_type(db_object_dsp $dbo, string $form_name): string
    {
        return $dbo->component_type_selector($form_name);
    }

    /**
     * create the html code for the form element to select the share type
     * @param db_object_dsp $dbo the frontend object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @return string the html code to select the share type
     */
    function form_share_type(db_object_dsp $dbo, string $form_name): string
    {
        return $dbo->share_type_selector($form_name);
    }

    /**
     * create the html code for the form element to select the protection type
     * @param db_object_dsp $dbo the frontend object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @return string the html code to select the protection type
     */
    function form_protection_type(db_object_dsp $dbo, string $form_name): string
    {
        return $dbo->protection_type_selector($form_name);
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
            api::URL_VAR_NEED_ALL,
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
            api::URL_VAR_NEED_ALL,
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
            . api::URL_PAR . api::URL_VAR_MASK . api::URL_EQ . $base_id;
        if ($id != 0) {
            $url .= api::URL_ADD . api::URL_VAR_ID . api::URL_EQ . $id;
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
