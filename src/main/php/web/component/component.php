<?php

/*

    web/view/component.php - the display extension of the api component object
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

include_once WEB_SANDBOX_PATH . 'sandbox_typed.php';
include_once DB_PATH . 'sql_db.php';
include_once WEB_HTML_PATH . 'html_base.php';
include_once WEB_HTML_PATH . 'html_selector.php';
include_once WEB_HTML_PATH . 'sheet.php';
include_once WEB_LOG_PATH . 'user_log_display.php';
include_once WEB_PHRASE_PATH . 'phrase.php';
include_once WEB_PHRASE_PATH . 'phrase_list.php';
include_once WEB_USER_PATH . 'user_message.php';
include_once WEB_HELPER_PATH . 'data_object.php';
include_once WEB_SANDBOX_PATH . 'db_object.php';
include_once WEB_SANDBOX_PATH . 'sandbox_typed.php';
include_once WEB_SYSTEM_PATH . 'back_trace.php';
include_once WEB_SYSTEM_PATH . 'messages.php';
include_once WEB_VIEW_PATH . 'view_list.php';
include_once WEB_TYPES_PATH . 'view_style_list.php';
include_once WEB_WORD_PATH . 'word.php';
include_once SHARED_CONST_PATH . 'views.php';
include_once SHARED_CONST_PATH . 'words.php';
include_once SHARED_TYPES_PATH . 'component_type.php';
include_once SHARED_TYPES_PATH . 'position_types.php';
include_once SHARED_TYPES_PATH . 'view_styles.php';
include_once SHARED_PATH . 'api.php';
include_once SHARED_PATH . 'json_fields.php';
include_once SHARED_PATH . 'library.php';
include_once SHARED_PATH . 'library.php';

use html\helper\data_object as data_object_dsp;
use html\html_base;
use html\html_selector;
use html\log\user_log_display;
use html\phrase\phrase as phrase_dsp;
use html\phrase\phrase_list;
use html\sandbox\db_object as db_object_dsp;
use html\sandbox\sandbox_typed;
use html\sheet;
use html\system\back_trace;
use html\system\messages;
use html\user\user_message;
use html\view\view_list;
use html\word\word;
use shared\api;
use shared\json_fields;
use shared\library;
use shared\const\views;
use shared\const\words;
use shared\types\component_type;
use shared\types\position_types;
use shared\types\view_styles;

class component extends sandbox_typed
{

    /*
     * object vars
     */

    public ?string $code_id = null;         // the entry type code id
    public ?int $position = 0;              // for the frontend the position of the link is included in the component object
    public ?int $link_id = 0;               // ??

    // the code_id for the message that should be shown to the user and that should be translated to the frontend language
    public ?string $ui_msg_code_id = null;

    // mainly for table components
    public ?phrase_dsp $phr_row = null;     // the main phrase to select the table rows
    public ?phrase_dsp $phr_col = null;     // the phrase to select the main table columns
    public ?phrase_dsp $wrd_col2 = null;    // the phrase to select the sub table columns

    // vars from the link
    // TODO move these vars to the frontend component link object
    public int $pos_type_id = position_types::DEFAULT_ID;
    public ?int $style_id = null;


    /*
     * base
     */

    /**
     * create the html code to show the component name with the link to change the component parameters
     * @param string|null $back the back trace url for the undo functionality
     * @param string $style the CSS style that should be used
     * @param int $msk_id database id of the view that should be shown
     * @returns string the html code
     */
    function name_link(?string $back = '', string $style = '', int $msk_id = views::COMPONENT_EDIT_ID): string
    {
        return parent::name_link($back, $style, $msk_id);
    }


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


    /*
     * info
     */

    private function type_code_id(): string
    {
        global $html_component_types;
        $type_code_id = '';
        $err_msg = 'Component type code id for ' . $this->dsp_id()
            . ' and type id ' . $this->type_id() . ' missing';
        if ($this->type_id() == null) {
            $this->log_err($err_msg);
        } else {
            $type_code_id = $html_component_types->code_id($this->type_id());
            if ($type_code_id == '') {
                $this->log_err($err_msg);
            }
        }
        return $type_code_id;
    }

    function pos_type_code_id(): string
    {
        global $html_position_types;
        $pos_type_code_id = '';
        $err_msg = 'Position type code id for ' . $this->dsp_id() . ' missing';
        if ($this->pos_type_id == null) {
            $this->log_err($err_msg);
        } else {
            $pos_type_code_id = $html_position_types->code_id($this->pos_type_id);
            if ($pos_type_code_id == '') {
                $this->log_err($err_msg);
            }
        }
        return $pos_type_code_id;
    }

    function style_text(): string
    {
        global $html_view_styles;
        if ($this->style_id != null) {
            return $html_view_styles->name($this->style_id);
        } else {
            return '';
        }
    }



    /*
     * set and get
     */

    /**
     * TODO all set_from_json_array functions should only use json_fields not api::FLD
     * set the vars this component bases on the api json array
     * public because it is reused e.g. by the phrase group display object
     * @param array $json_array an api json message
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function set_from_json_array(array $json_array): user_message
    {
        $usr_msg = parent::set_from_json_array($json_array);
        if (array_key_exists(json_fields::CODE_ID, $json_array)) {
            $this->code_id = $json_array[json_fields::CODE_ID];
        } else {
            $this->code_id = null;
        }
        if (array_key_exists(json_fields::UI_MSG_CODE_ID, $json_array)) {
            $this->ui_msg_code_id = $json_array[json_fields::UI_MSG_CODE_ID];
        } else {
            $this->ui_msg_code_id = null;
        }
        if (array_key_exists(json_fields::POSITION, $json_array)) {
            $this->position = $json_array[json_fields::POSITION];
        } else {
            $this->position = 0;
        }
        if (array_key_exists(json_fields::LINK_ID, $json_array)) {
            $this->link_id = $json_array[json_fields::LINK_ID];
        } else {
            $this->link_id = 0;
        }
        if (array_key_exists(json_fields::POS_TYPE, $json_array)) {
            $this->pos_type_id = $json_array[json_fields::POS_TYPE];
        } else {
            $this->pos_type_id = position_types::DEFAULT_ID;
        }
        if (array_key_exists(json_fields::STYLE, $json_array)) {
            $this->style_id = $json_array[json_fields::STYLE];
        } else {
            $this->style_id = null;
        }
        return $usr_msg;
    }


    /*
     * interface
     */

    /**
     * TODO all set_from_json_array functions should only use json_fields not api::FLD
     * create an array for the json api message
     * an array is used (instead of a string) to enable combinations of api_array() calls
     * @return array the json message array to send the updated data to the backend
     * an array is used (instead of a string) to enable combinations of api_array() calls
     */
    function api_array(): array
    {
        $vars = parent::api_array();
        $vars[json_fields::CODE_ID] = $this->code_id;
        $vars[json_fields::UI_MSG_CODE_ID] = $this->ui_msg_code_id;
        if ($this->position != 0 or $this->link_id != 0) {
            $vars[json_fields::POSITION] = $this->position;
        }
        if ($this->link_id != 0) {
            $vars[json_fields::LINK_ID] = $this->link_id;
        }
        if ($this->pos_type_id != position_types::DEFAULT_ID or $this->link_id != 0) {
            $vars[json_fields::POS_TYPE] = $this->pos_type_id;
        }
        if ($this->style_id != 0) {
            $vars[json_fields::STYLE] = $this->style_id;
        }
        return array_filter($vars, fn($value) => !is_null($value) && $value !== '');
    }


    /*
     * info
     */

    /**
     * @return bool true if the component is a system form button
     */
    function is_button(): bool
    {
        if (in_array($this->type_code_id(), component_type::BUTTON_TYPES)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return bool true if the component is a hidden system form element
     */
    function is_hidden(): bool
    {
        if (in_array($this->type_code_id(), component_type::HIDDEN_TYPES)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return bool true if the component is a system form button or a hidden form element
     */
    function is_button_or_hidden(): bool
    {
        if ($this->is_button() or $this->is_hidden()) {
            return true;
        } else {
            return false;
        }
    }


    /*
     * internal
     */

    /**
     * @param string $form_name the name of the html form
     * @return string the html code to select the component type
     */
    private function dsp_type_selector(string $form_name): string
    {
        global $html_component_types;
        return $html_component_types->selector($form_name);
    }


    /*
     * to be replaced
     */

    /**
     * HTML code to edit all component fields
     * @param string $dsp_type the html code to display the type selector
     * @param string $phr_row the html code to select the phrase for the row
     * @param string $phr_col the html code to select the phrase for the column
     * @param string $phr_cols the html code to select the phrase for the second column
     * @param string $dsp_log the html code of the change log
     * @param string $back the html code to be opened in case of a back action
     * @return string the html code to display the edit page
     */
    function form_edit(
        string $dsp_type,
        string $phr_row,
        string $phr_col,
        string $phr_cols,
        string $dsp_log,
        string $back = ''): string
    {
        $html = new html_base();
        $result = '';

        $hidden_fields = '';
        if ($this->id() <= 0) {
            $script = views::COMPONENT_ADD;
            $fld_ext = '_add';
            $header = $html->text_h2('Create a view element');
        } else {
            $script = views::COMPONENT_EDIT;
            $fld_ext = '';
            $header = $html->text_h2('Change "' . $this->name . '"');
            $hidden_fields .= $html->form_hidden("id", $this->id());
        }
        $hidden_fields .= $html->form_hidden("back", $back);
        $hidden_fields .= $html->form_hidden("confirm", '1');
        $detail_fields = $html->form_text("name" . $fld_ext, $this->name(), "Name");
        $detail_fields .= $html->form_text("description" . $fld_ext, $this->description, "Description");
        $detail_fields .= $dsp_type;
        $detail_row = $html->fr($detail_fields) . '<br>';
        $result = $header
            . $html->form($script, $hidden_fields . $detail_row)
            . '<br>';

        $result .= $dsp_log;

        return $result;
    }

    /*
     * to review
     */


    // TODO HTML code to add a view component
    function dsp_add($add_link, $wrd, $back): string
    {
        return $this->dsp_edit($add_link, $wrd, $back);
    }

    /**
     * HTML code to edit all word fields
     * @param int $add_link the id of the view that should be linked to the word
     * @param back_trace|null $back
     * @param word $wrd
     */
    function dsp_edit(int $add_link, word $wrd, back_trace $back = null): string
    {
        $this->log_debug($this->dsp_id() . ' (called from ' . $back->url_encode() . ')');
        $result = '';
        $html = new html_base();

        // show the view component name
        if ($this->id() <= 0) {
            $script = "component_add";
            $result .= $html->dsp_text_h2('Create a view element for <a href="/http/view.php?words=' . $wrd->id() . '">' . $wrd->name() . '</a>');
        } else {
            $script = "component_edit";
            $result .= $html->dsp_text_h2('Edit the view element "' . $this->name . '" (used for <a href="/http/view.php?words=' . $wrd->id() . '">' . $wrd->name() . '</a>) ');
        }
        $result .= '<div class="row">';

        // when changing a view component show the fields only on the left side
        if ($this->id() > 0) {
            $result .= '<div class="' . view_styles::COL_SM_7 . '">';
        }

        $result .= $html->dsp_form_start($script);
        if ($this->id() > 0) {
            $result .= $html->dsp_form_id($this->id());
        }
        $result .= $html->dsp_form_hidden("word", $wrd->id());
        $result .= $html->dsp_form_hidden("back", $wrd->id());
        $result .= $html->dsp_form_hidden("confirm", 1);
        $result .= '<div class="form-row">';
        $result .= $html->dsp_form_fld("name", $this->name, "Component name:", view_styles::COL_SM_8);
        $result .= $this->dsp_type_selector($script); // allow to change the type
        $result .= '</div>';
        $result .= '<div class="form-row">';
        $result .= $this->dsp_word_row_selector($script, view_styles::COL_SM_6); // allow to change the word_row word
        $result .= $this->dsp_word_col_selector($script, view_styles::COL_SM_6); // allow to change the word col word
        $result .= '</div>';
        $result .= $html->dsp_form_fld("comment", $this->description, "Comment:");
        if ($add_link <= 0) {
            if ($this->id() > 0) {
                $result .= $html->dsp_form_end('', $back, "/http/component_del.php?id=" . $this->id() . "&back=" . $back->url_encode());
            } else {
                $result .= $html->dsp_form_end('', $back, '');
            }
        }

        if ($this->id() > 0) {
            $result .= '</div>';

            $view_html = $this->linked_views($add_link, $wrd, $back);
            $changes = $this->dsp_hist(0, 0, '', $back);
            if (trim($changes) <> "") {
                $hist_html = $changes;
            } else {
                $hist_html = 'Nothing changed yet.';
            }
            $changes = $this->dsp_hist_links(0, 0, '', $back);
            if (trim($changes) <> "") {
                $link_html = $changes;
            } else {
                $link_html = 'No component have been added or removed yet.';
            }
            $result .= $html->dsp_link_hist_box('Views', $view_html,
                '', '',
                'Changes', $hist_html,
                'Link changes', $link_html);
        }

        $result .= '</div>';   // of row
        $result .= '<br><br>'; // this a usually a small for, so the footer can be moved away

        return $result;
    }

    /**
     * HTML code to edit all component fields
     * @param string $dsp_type the html code to display the type selector
     * @param string $phr_row the html code to select the phrase for the row
     * @param string $phr_col the html code to select the phrase for the column
     * @param string $phr_cols the html code to select the phrase for the second column
     * @param string $dsp_log the html code of the change log
     * @param string $back the html code to be opened in case of a back action
     * @return string the html code to display the edit page
     */
    function form_edit_new(
        string $dsp_type,
        string $phr_row,
        string $phr_col,
        string $phr_cols,
        string $dsp_log,
        string $back = ''): string
    {
        $html = new html_base();
        $result = '';

        $hidden_fields = '';
        if ($this->id() <= 0) {
            $script = views::COMPONENT_ADD;
            $fld_ext = '_add';
            $header = $html->text_h2('Create a view element');
        } else {
            $script = views::COMPONENT_EDIT;
            $fld_ext = '';
            $header = $html->text_h2('Change "' . $this->name . '"');
            $hidden_fields .= $html->form_hidden("id", $this->id());
        }
        $hidden_fields .= $html->form_hidden("back", $back);
        $hidden_fields .= $html->form_hidden("confirm", '1');
        $detail_fields = $html->form_text("name" . $fld_ext, $this->name(), "Name");
        $detail_fields .= $html->form_text("description" . $fld_ext, $this->description, "Description");
        $detail_fields .= $dsp_type;
        $detail_row = $html->fr($detail_fields) . '<br>';
        $result = $header
            . $html->form($script, $hidden_fields . $detail_row)
            . '<br>';

        $result .= $dsp_log;

        return $result;
    }


    /**
     * @returns string the html code to display this view component
     */
    function html(?phrase_dsp $phr = null, ?db_object_dsp $dbo = null, ?data_object_dsp $cfg = null): string
    {
        global $cmp_typ_cac;
        return match ($cmp_typ_cac->code_id($this->type_id())) {
            component_type::TEXT => $this->text(),
            component_type::PHRASE_NAME => $this->word_name($phr),
            component_type::VALUES_RELATED => $this->table($dbo, $cfg),
            default => 'ERROR: unknown type ',
        };
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

    /**
     * @param string $script the name of the html form
     * @param string $col_class the formatting code to adjust the formatting
     * @return string with the HTML code to show the component word_row selector
     */
    private function dsp_word_row_selector(string $script, string $col_class): string
    {
        $label = "Take rows from:";
        if ($this->phr_row != null) {
            //$phr_dsp = new word_dsp($this->phr_row->api_json());
            $phr_dsp = $this->phr_row;
            $label = "Rows taken from " . $phr_dsp->name_link() . ":";
        }
        return $this->phrase_selector_old('word_row', $script, $label, $col_class, $this->phr_row->id()) . ' ';
    }

    /**
     * @param string $script the name of the html form
     * @param string $col_class the formatting code to adjust the formatting
     * @return string with the HTML code to show the component word_col selector
     */
    private function dsp_word_col_selector(string $script, string $col_class): string
    {
        global $usr;
        $label = "Take columns from:";
        if (isset($this->phr_col)) {
            //$phr_dsp = new word_dsp($this->phr_col->api_json());
            $phr_dsp = $this->phr_col;
            $label = "Columns taken from " . $phr_dsp->name_link() . ":";
        }
        return $this->phrase_selector_old('word_col', $script, $label, $col_class, $this->phr_row->id()) . ' ';
    }

    /**
     * HTML code of a phrase selector
     * @param string $name the unique name inside the form for this selector
     * @param string $form the name of the html form
     * @param string $label the text show to the user
     * @param string $col_class the formatting code to adjust the formatting
     * @param int $selected the id of the preselected phrase
     * @param string $pattern the pattern to filter the phrases
     * @param phrase_dsp|null $phr phrase to preselect the phrases e.g. use Country to narrow the selection
     * @return string with the HTML code to show the phrase selector
     */
    protected function phrase_selector_old(
        string      $name,
        string      $form,
        string      $label = '',
        string      $col_class = '',
        int         $selected = 0,
        string      $pattern = '',
        ?phrase_dsp $phr = null): string
    {
        $phr_lst = new phrase_list();
        $phr_lst->load_like($pattern);
        return $phr_lst->selector($form, $selected, $name, $label, view_styles::COL_SM_4, html_selector::TYPE_DATALIST);
    }

    /**
     * lists of all views where this component is used
     */
    private function linked_views($add_link, $wrd, $back): string
    {
        $this->log_debug("id " . $this->id() . " (word " . $wrd->id() . ", add " . $add_link . ").");

        global $usr;
        global $db_con;
        $html = new html_base();
        $result = '';

        if (html_base::UI_USE_BOOTSTRAP) {
            $result .= $html->dsp_tbl_start_hist();
        } else {
            $result .= $html->dsp_tbl_start_half();
        }

        $msk_lst = new view_list();
        $msk_lst->load_by_component_id($this->id());

        foreach ($msk_lst as $msk) {
            $result .= '  <tr>' . "\n";
            $result .= '    <td>' . "\n";
            $result .= '      ' . $msk->name_linked($wrd, $back) . "\n";
            $result .= '    </td>' . "\n";
            $result .= $this->btn_unlink($msk->id(), $wrd, $back);
            $result .= '  </tr>' . "\n";
        }

        // give the user the possibility to add a view
        $result .= '  <tr>';
        $result .= '    <td>';
        if ($add_link == 1) {
            // $sel->dummy_text = 'select a view where the view component should also be used';
            $msk_lst = new view_list();
            $result .= $msk_lst->selector('component_edit', 0, 'link_view');

            $result .= $html->dsp_form_end('', $back);
        } else {
            $result .= '      ' . \html\btn_add('add new', '/http/component_edit.php?id=' . $this->id() . '&add_link=1&word=' . $wrd->id . '&back=' . $back);
        }
        $result .= '    </td>';
        $result .= '  </tr>';

        $result .= $html->dsp_tbl_end();
        $result .= '  <br>';

        return $result;
    }

    function btn_unlink(): string
    {
        return '';
    }

    /**
     * display the history of a view component
     */
    function dsp_hist(
        int        $page,
        int        $size,
        string     $call,
        back_trace $back = null
    ): string
    {
        $this->log_debug("for id " . $this->id() . " page " . $size . ", size " . $size . ", call " . $call . ", back " . $back->url_encode() . ".");
        $result = ''; // reset the html code var

        $log_dsp = new user_log_display();
        $result .= $log_dsp->dsp_hist(component::class, $this->id(), $size, $page);

        $this->log_debug("done");
        return $result;
    }

    // display the link history of a view component
    function dsp_hist_links($page, $size, $call, $back): string
    {
        $this->log_debug("for id " . $this->id() . " page " . $size . ", size " . $size . ", call " . $call . ", back " . $back . ".");
        $result = ''; // reset the html code var

        $log_dsp = new user_log_display();
        $log_dsp->id = $this->id();
        $log_dsp->type = component::class;
        $log_dsp->page = $page;
        $log_dsp->size = $size;
        $log_dsp->call = $call;
        $log_dsp->back = $back;
        $result .= $log_dsp->dsp_hist_links();

        $this->log_debug("done");
        return $result;
    }

    function log_err(string $msg): void
    {
        echo $msg;
    }

    function log_debug(string $msg): void
    {
        echo '';
    }


}
