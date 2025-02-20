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

namespace html\component;

include_once WEB_COMPONENT_PATH . 'component.php';
include_once DB_PATH . 'sql_db.php';
include_once WEB_HTML_PATH . 'html_base.php';
include_once WEB_HTML_PATH . 'sheet.php';
include_once WEB_PHRASE_PATH . 'phrase.php';
include_once WEB_HELPER_PATH . 'data_object.php';
include_once WEB_SANDBOX_PATH . 'db_object.php';
include_once WEB_SYSTEM_PATH . 'messages.php';
include_once WEB_TYPES_PATH . 'view_style_list.php';
include_once SHARED_CONST_PATH . 'views.php';
include_once SHARED_CONST_PATH . 'words.php';
include_once SHARED_TYPES_PATH . 'component_type.php';
include_once SHARED_TYPES_PATH . 'view_styles.php';
include_once SHARED_PATH . 'api.php';
include_once SHARED_PATH . 'library.php';

use html\helper\data_object as data_object_dsp;
use html\html_base;
use html\phrase\phrase as phrase_dsp;
use html\sandbox\db_object as db_object_dsp;
use html\sheet;
use html\system\messages;
use shared\api;
use shared\library;
use shared\const\views;
use shared\const\words;
use shared\types\component_type;
use shared\types\view_styles;

class component_exe extends component
{

    /*
     * display
     */

    /**
     * @param db_object_dsp|null $dbo the word, triple or formula object that should be shown to the user
     * @param string $form_name the name of the view which is also used for the html form name
     * @param int $msk_id the database id of the calling view
     * @param data_object_dsp|null $cfg the context used to create the view
     * @param string $back the backtrace for undo actions
     * @param bool $test_mode true to create a reproducible result e.g. by using just one phrase
     * @return string the html code of all view components
     */
    function dsp_entries(
        ?db_object_dsp   $dbo,
        string           $form_name = '',
        int              $msk_id = 0,
        ?data_object_dsp $cfg = null,
        string           $back = '',
        bool             $test_mode = false
    ): string
    {
        if ($dbo == null) {
            // the $dbo check and the message creation has already been done in the view level
            $this->log_debug($this->dsp_id());
        } else {
            $this->log_debug($dbo->dsp_id() . ' with the view ' . $this->dsp_id());
        }

        $result = '';

        // list of all possible view components
        $result .= match ($this->type_code_id()) {
            // start page
            component_type::TEXT => $this->text(),
            component_type::CALC_SHEET => $this->calc_sheet(),

            // system form - usage only allowed for internal system forms
            component_type::FORM_TITLE => $this->form_tile($form_name),
            component_type::FORM_BACK => $this->form_back($msk_id, $dbo->id(), $back),
            component_type::FORM_CONFIRM => $this->form_confirm($dbo, $back),
            component_type::SHOW_NAME => $this->show_name($dbo, $back),
            component_type::FORM_NAME => $this->form_name($dbo, $back),
            component_type::FORM_DESCRIPTION => $this->form_description($dbo, $back),
            component_type::FORM_PHRASE => $this->form_phrase($dbo, $test_mode),
            component_type::FORM_VERB_SELECTOR => $this->form_verb($dbo, $form_name),
            component_type::FORM_PHRASE_TYPE => $this->form_phrase_type($dbo, $form_name),
            component_type::FORM_SOURCE_TYPE => $this->form_source_type($dbo, $form_name),
            component_type::FORM_SHARE_TYPE => $this->form_share_type($dbo, $form_name),
            component_type::FORM_PROTECTION_TYPE => $this->form_protection_type($dbo, $form_name),
            component_type::FORM_CANCEL => $this->form_cancel($msk_id, $dbo->id()),
            component_type::FORM_SAVE => $this->form_save($dbo, $back),
            component_type::FORM_DEL => $this->form_del($dbo, $back),
            component_type::FORM_END => $this->form_end(),

            // hidden - only used for formatting without functional behaviour
            component_type::ROW_START => $this->row_start(),
            component_type::ROW_RIGHT => $this->row_right(),
            component_type::ROW_END => $this->row_end(),

            // view only -
            component_type::USAGE_WORD => $this->usage_word($dbo, $form_name),
            component_type::SYSTEM_CHANGE_LOG => $this->system_change_log($dbo, $form_name),

            // verb only -
            component_type::VERB_NAME => $this->verb_name($dbo),
            component_type::TRIPLE_LIST => $this->triple_list($dbo),

            // base
            component_type::PHRASE => $this->name_tip(),
            component_type::PHRASE_NAME => $this->phrase_name($dbo),
            component_type::LINK => $this->phrase_link($dbo, $form_name),

            // select
            component_type::VIEW_SELECT => $this->view_select($dbo, $form_name, $cfg),
            component_type::PHRASE_SELECT => $this->phrase_select($dbo, $form_name),

            // table
            component_type::VALUES_ALL => $this->all($dbo, $back),
            component_type::VALUES_RELATED => $this->table($dbo, $cfg),
            component_type::NUMERIC_VALUE => $this->num_list($dbo, $back),

            // related
            component_type::REF_LIST_WORD => $this->ref_list_word($dbo, $cfg),
            component_type::LINK_LIST_WORD => $this->link_list_word($dbo, $cfg),
            component_type::FORMULAS => $this->formulas($dbo),
            component_type::FORMULA_RESULTS => $this->results($dbo),
            component_type::WORDS_DOWN => $this->word_children($dbo),
            component_type::WORDS_UP => $this->word_parents($dbo),

            // export
            component_type::JSON_EXPORT => $this->json_export($dbo, $back),
            component_type::XML_EXPORT => $this->xml_export($dbo, $back),
            component_type::CSV_EXPORT => $this->csv_export($dbo, $back),

            default => 'program code for component ' . $this->dsp_id() . ' missing<br>'
        };
        $this->log_debug($this->dsp_id() . ' created');

        return $result;
    }

    /**
     * @return string a fixed text
     */
    function text(): string
    {
        return $this->name();
    }

    /**
     * @return string the name of a phrase and give the user the possibility to change the phrase name
     */
    function phrase_name(db_object_dsp $phr): string
    {
        return $phr->name();
    }

    /**
     * @return string the name of a phrase and give the user the possibility to change the phrase name
     */
    function phrase_select(db_object_dsp $phr, string $form_name): string
    {
        return $phr->phrase_selector_old('phrase', $form_name, 'word:', '', $phr->id());
    }

    /**
     * @return string show a list of phrases with a suggested link type that might be linked to the object
     */
    function phrase_link(db_object_dsp $phr, string $form_name): string
    {
        return $phr->phrase_selector_old('phrase', $form_name, 'word:', '', $phr->id());
    }

    /**
     * the html code to select the view for the given object
     * which can also be the component itself
     * so view_select (for the $obj) can call view_selector of this class if $obj is of class component
     * @param db_object_dsp $dbo the word, triple or formula object that should be shown to the user
     * @param string $form the name of the view which is also used for the html form name
     * @param data_object_dsp|null $cfg the context used to create the view
     * @return string with the html code to select a view
     */
    function view_select(db_object_dsp $dbo, string $form, ?data_object_dsp $cfg = null): string
    {
        $msk_lst = null;
        // over
        if ($cfg != null) {
            if ($cfg->has_view_list()) {
                $msk_lst = $cfg->view_list();
            }
        }
        if ($msk_lst == null) {
            $msk_lst = $dbo->view_list();
        }
        return $dbo->view_selector($form, $msk_lst);
    }

    /**
     * @param db_object_dsp $dbo the word, triple or formula object that should be shown to the user
     * @param data_object_dsp|null $cfg the context used to create the view
     * @return string with the html code of the external references
     */
    function ref_list_word(db_object_dsp $dbo, ?data_object_dsp $cfg): string
    {
        // TODO review
        $result = 'list of references to ' . $dbo->name() . ' ';
        if ($cfg != null) {
            $result .= '';
        }
        return $result;
    }

    /**
     * @param db_object_dsp $dbo the word, triple or formula object that should be shown to the user
     * @param data_object_dsp|null $cfg the context used to create the view
     * @return string with the html code of links that can be changes
     */
    function link_list_word(db_object_dsp $dbo, ?data_object_dsp $cfg): string
    {
        // TODO review
        return 'list of phrases related to ' . $dbo->name() . ' ';
    }

    /**
     * @return string with the html code that shows the usage of this word
     */
    function usage_word(db_object_dsp $phr, string $form_name): string
    {
        // TODO review
        return 'usage of ' . $phr->name() . ' ';
    }

    /**
     * @return string with the html code that shows the recent changes of this object
     */
    function system_change_log(db_object_dsp $phr, string $form_name): string
    {
        // TODO review
        return 'change log for ' . $phr->name() . ' ';
    }

    /**
     * TODO move code from component_dsp_old
     * @return string the html code to show a list of values
     */
    function table(?db_object_dsp $dbo = null, ?data_object_dsp $cfg = null): string
    {
        return 'values related to ' . $this->name() . ' ';
    }

    /**
     * TODO move code from component_dsp_old
     * @return string a dummy text
     */
    function num_list(): string
    {
        return $this->name();
    }

    /**
     * TODO move to a component exe part class
     * @return string a dummy text
     */
    function verb_name(?db_object_dsp $dbo = null): string
    {
        return $dbo->name();
    }

    /**
     * TODO move to a component exe part class
     * @return string a dummy text
     */
    function triple_list(?db_object_dsp $dbo = null): string
    {
        return $dbo->name();
    }

    /**
     * TODO move code from component_dsp_old
     * @return string a dummy text
     */
    function formulas(): string
    {
        return $this->name();
    }

    /**
     * TODO move code from component_dsp_old
     * @return string a dummy text
     */
    function results(): string
    {
        return $this->name();
    }

    /**
     * TODO move code from component_dsp_old
     * @return string a dummy text
     */
    function word_children(): string
    {
        return $this->name();
    }

    /**
     * TODO move code from component_dsp_old
     * @return string a dummy text
     */
    function word_parents(): string
    {
        return $this->name();
    }

    /**
     * TODO move code from component_dsp_old
     * @return string a dummy text
     */
    function json_export(): string
    {
        return $this->name();
    }

    /**
     * TODO move code from component_dsp_old
     * @return string a dummy text
     */
    function xml_export(): string
    {
        return $this->name();
    }

    /**
     * TODO move code from component_dsp_old
     * @return string a dummy text
     */
    function csv_export(): string
    {
        return $this->name();
    }

    /**
     * TODO move code from component_dsp_old
     * @return string a dummy text
     */
    function all(): string
    {
        return $this->name();
    }

    /**
     * start an HTML form, show the title and set and set the unique form name
     * @param string $form_name the name of the view which is also used for the html form name
     * @return string the html code to start a new form and display the tile
     */
    function form_tile(string $form_name): string
    {
        $html = new html_base();
        $ui_msg = new messages();
        $result = '';
        if ($this->ui_msg_code_id != null) {
            $result .= $html->text_h2($ui_msg->txt($this->ui_msg_code_id));
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
    function form_name(db_object_dsp $dbo): string
    {
        $html = new html_base();
        return $html->form_field(
            api::URL_VAR_NAME,
            $dbo->name(),
            html_base::INPUT_TEXT,
            '', $this->style_text()
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
     * TODO replace _add with a parameter value
     * TODO move form_field_triple_phrase_to to a const
     * TODO remove fixed pattern
     * @return string the html code to request the description from the user
     */
    function form_phrase(db_object_dsp $dbo, bool $test_mode = false): string
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
        if ($this->name == 'system form triple phrase from') {
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

    /**
     * @return string just to indicate that a row ends
     */
    function calc_sheet(): string
    {
        $sheet = new sheet();
        return $sheet->calc_sheet();
    }

    /**
     * @return string the name of a phrase and give the user the possibility to change the phrase name
     */
    function word_name(phrase_dsp $phr): string
    {
        global $cmp_typ_cac;
        if ($cmp_typ_cac->code_id($this->type_id()) == component_type::PHRASE_NAME) {
            return $phr->name();
        } else {
            return '';
        }
    }

}
