<?php

/*

    web/component/execute/system_form.php - function to execute a system form component
    -------------------------------------

    to create the HTML code to display a system form component

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

namespace Zukunft\ZukunftCom\main\php\web\component\execute;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\shared\enum\messages;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once paths::DB . 'sql_db.php';
include_once html_paths::COMPONENT . 'component.php';
include_once html_paths::COMPONENT . 'component_list.php';
include_once html_paths::FORMULA . 'formula_list.php';
include_once html_paths::HTML . 'html_names.php';
include_once html_paths::HTML . 'html_base.php';
include_once html_paths::REF . 'ref.php';
include_once html_paths::REF . 'source_list.php';
include_once html_paths::SANDBOX . 'db_object.php';
include_once html_paths::SYSTEM . 'language.php';
include_once html_paths::PHRASE . 'phrase_list.php';
include_once html_paths::TYPES . 'type_lists.php';
include_once html_paths::TYPES . 'view_style_list.php';
include_once html_paths::USER . 'user.php';
include_once html_paths::VIEW . 'view_list.php';
include_once html_paths::VIEW . 'view_relation.php';
include_once html_paths::WORD . 'triple.php';
include_once paths::SHARED_CONST . 'components.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED_CONST . 'words.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_TYPES . 'view_styles.php';
include_once paths::SHARED . 'api.php';
include_once paths::SHARED . 'url_var.php';
include_once paths::SHARED . 'library.php';

use Zukunft\ZukunftCom\main\php\web\component\component;
use Zukunft\ZukunftCom\main\php\web\component\component_list;
use Zukunft\ZukunftCom\main\php\web\formula\formula_list;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\web\ref\ref;
use Zukunft\ZukunftCom\main\php\web\ref\source_list;
use Zukunft\ZukunftCom\main\php\web\sandbox\db_object as db_object_dsp;
use Zukunft\ZukunftCom\main\php\web\system\language;
use Zukunft\ZukunftCom\main\php\web\types\type_lists;
use Zukunft\ZukunftCom\main\php\web\user\user;
use Zukunft\ZukunftCom\main\php\web\view\view_list;
use Zukunft\ZukunftCom\main\php\web\view\view_relation;
use Zukunft\ZukunftCom\main\php\web\word\triple;
use Zukunft\ZukunftCom\main\php\shared\api;
use Zukunft\ZukunftCom\main\php\shared\const\components;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\types\view_styles;
use Zukunft\ZukunftCom\main\php\shared\url_var;

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
     * @param int|string|null $id the database id of the object that should be shown in the view (string is used for the phrase list of values)
     * @param string $back the history of the views and actions for the back und undo function
     * @return string the html code to include the back trace into the form result
     */
    function form_back(int $msk_id, int|string|null $id, string $back): string
    {
        $result = '';
        $html = new html_base();
        $result .= $html->input(url_var::MASK, msg_id::FORM_FIELD_MASK, $msk_id, html_base::INPUT_HIDDEN);
        $result .= $html->input(url_var::ID, msg_id::FORM_FIELD_ID, $id, html_base::INPUT_HIDDEN);
        $result .= $html->input(url_var::BACK, msg_id::FORM_FIELD_BACK, $back, html_base::INPUT_HIDDEN);
        return $result;
    }

    /**
     * @return string the html code to check if the form changes has already confirmed by the user
     */
    function form_confirm(): string
    {
        $html = new html_base();
        return $html->input(url_var::STEP, msg_id::FORM_FIELD_CONFIRM, url_var::STEP_CONFIRM, html_base::INPUT_HIDDEN);
    }

    /**
     * @return string the html code so that an admin user can overwrite the username
     */
    function admin_form_username(user|db_object_dsp $dbo): string
    {
        $html = new html_base();
        return $html->input(
            url_var::USERNAME,
            msg_id::FORM_FIELD_USERNAME,
            $dbo->name(),
            html_base::INPUT_TEXT);
    }

    /**
     * @return string the html code so that an admin user can overwrite the user email
     */
    function admin_form_user_email(user|db_object_dsp $dbo): string
    {
        $html = new html_base();
        return $html->input(
            url_var::EMAIL,
            msg_id::FORM_FIELD_USER_EMAIL,
            $dbo->email,
            html_base::INPUT_EMAIL);
    }

    /**
     * @return string the html code so that an admin user can overwrite the user password
     */
    function admin_form_user_password(user|db_object_dsp $dbo): string
    {
        $html = new html_base();
        return $html->input(
            url_var::USER_PASSWORD,
            msg_id::FORM_FIELD_USER_PASSWORD,
            $dbo->password(),
            html_base::INPUT_PASSWORD);
    }

    /**
     * @return string the html code so that an admin can overwrite the language symbol
     */
    function admin_form_language_symbol(language|db_object_dsp $dbo): string
    {
        $html = new html_base();
        return $html->input(
            url_var::LANGUAGE_SYMBOL,
            msg_id::FORM_FIELD_LANGUAGE_SYMBOL,
            $dbo->symbol,
            html_base::INPUT_TEXT);
    }

    /**
     * @return string the html code to show the language symbol
     */
    function show_language_symbol(language|db_object_dsp $dbo): string
    {
        return $dbo->symbol;
    }


    /**
     * show the name of an object to the user
     * @param db_object_dsp $dbo the object
     * @param string $code_id e.g. to select the name in case of a link object
     * @return string the html code to show the object name to the user
     */
    function show_name(db_object_dsp $dbo, string $code_id = ''): string
    {
        if ($code_id == '') {
            return $dbo->name();
        } elseif ($code_id == 'show_field_formula_name') {
            return $dbo->formula_name();
        } elseif ($code_id == 'show_field_phrase_name') {
            return $dbo->phrase_name();
        } else {
            log_warning('code id ' . $code_id . ' not yet defined in show_name');
            return $dbo->name();
        }
    }

    /**
     * @param db_object_dsp $dbo the object
     * @return string the html code to show the object description to the user
     */
    function show_description(db_object_dsp $dbo): string
    {
        return $dbo->description();
    }

    /**
     * @param ref|db_object_dsp $dbo the object
     * @return string the html code to show the object reference type to the user
     */
    function show_ref_type(ref|db_object_dsp $dbo): string
    {
        return $dbo->type_name();
    }

    /**
     * @param ref|db_object_dsp $dbo the object
     * @return string the html code to show the object reference type to the user
     */
    function show_ref_key(ref|db_object_dsp $dbo): string
    {
        return $dbo->external_key();
    }

    /**
     * @param ref|db_object_dsp $dbo the object
     * @return string the html code to show the object reference type to the user
     */
    function show_ref_source(ref|db_object_dsp $dbo): string
    {
        $src_txt = $dbo->source_name();
        if ($src_txt == null) {
            $src_txt = '';
        }
        return $src_txt;
    }

    /**
     * @param ref|db_object_dsp $dbo the object
     * @return string the html code to show the object reference type to the user
     */
    function show_ref_url(ref|db_object_dsp $dbo): string
    {
        return $dbo->url();
    }

    /**
     * TODO Prio 1 fill with the correct field
     * @param db_object_dsp $dbo the object
     * @return string the html code to show the object name to the user
     */
    function show_usage(db_object_dsp $dbo): string
    {
        return $dbo->name();
    }

    /**
     * @param view_relation|db_object_dsp $dbo the object
     * @return string the html code to show the object name to the user
     */
    function show_parent_view(view_relation|db_object_dsp $dbo): string
    {
        return $dbo->parent()?->name();
    }

    /**
     * @param view_relation|db_object_dsp $dbo the object
     * @return string the html code to show the object name to the user
     */
    function show_child_view(view_relation|db_object_dsp $dbo): string
    {
        return $dbo->child()?->name();
    }

    /**
     * @param view_relation|db_object_dsp $dbo the object
     * @return string the html code to show the object name to the user
     */
    function show_relation_type(view_relation|db_object_dsp $dbo): string
    {
        return $dbo->relation_type()?->name();
    }

    /**
     * @param view_relation|db_object_dsp $dbo the object
     * @return string the html code to show the object name to the user
     */
    function show_start_pos(view_relation|db_object_dsp $dbo): string
    {
        return $dbo->start_pos;
    }

    /**
     * TODO Prio 1 fill with the correct field
     * @param db_object_dsp $dbo the object
     * @return string the html code to show the object name to the user
     */
    function result(db_object_dsp $dbo): string
    {
        return $dbo->name();
    }

    /**
     * TODO Prio 1 fill with the correct field
     * @param db_object_dsp $dbo the object
     * @return string the html code to show the object name to the user
     */
    function used_as_text(db_object_dsp $dbo): string
    {
        return $dbo->name();
    }

    /**
     * TODO Prio 1 fill with the correct field
     * @param db_object_dsp $dbo the object
     * @return string the html code to show the object name to the user
     */
    function used_as_text_link(db_object_dsp $dbo): string
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
            msg_id::FORM_FIELD_NAME,
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
            msg_id::FORM_FIELD_DESCRIPTION,
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
    function form_field_plural(db_object_dsp $dbo, string $style_text): string
    {
        $html = new html_base();
        $plural = $dbo->get_plural();
        if ($plural == null) {
            $plural = '';
        }
        return $html->form_field(
            url_var::PLURAL,
            msg_id::FORM_FIELD_PLURAL,
            $plural,
            html_base::INPUT_TEXT,
            '', $style_text
        );
    }

    /**
     * request the verb name if used the other way round
     * e.g. if Zurich is part of Switzerland, Switzerland contains Zurich and "contains" is the reverse name for "ia part of"
     * @param db_object_dsp $dbo the object
     * @return string the html code to request the verb name used if the triple is used the other way round
     */
    function form_field_reverse(db_object_dsp $dbo, string $style_text): string
    {
        $html = new html_base();
        $reverse = $dbo->reverse();
        if ($reverse == null) {
            $reverse = '';
        }
        return $html->form_field(
            url_var::REVERSE,
            msg_id::FORM_FIELD_REVERSE,
            $reverse,
            html_base::INPUT_TEXT,
            '', $style_text
        );
    }

    /**
     * request the verb name if used the other way round
     * e.g. if Zurich is part of Switzerland, Switzerland contains Zurich and "contains" is the reverse name for "ia part of"
     * @param db_object_dsp $dbo the object
     * @return string the html code to request the verb name used if the triple is used the other way round
     */
    function form_field_plural_reverse(db_object_dsp $dbo, string $style_text): string
    {
        $html = new html_base();
        $reverse = $dbo->plural_reverse();
        if ($reverse == null) {
            $reverse = '';
        }
        return $html->form_field(
            url_var::REVERSE_PLURAL,
            msg_id::FORM_FIELD_PLURAL_REVERSE,
            $reverse,
            html_base::INPUT_TEXT,
            '', $style_text
        );
    }

    /**
     * request the verb name if used in a formula
     * @param db_object_dsp $dbo the object
     * @return string the html code to request the verb name used in a formula
     */
    function form_field_name_in_formulas(db_object_dsp $dbo, string $style_text): string
    {
        $html = new html_base();
        $frm_name = $dbo->formula_name();
        if ($frm_name == null) {
            $frm_name = '';
        }
        return $html->form_field(
            url_var::NAME_IN_FORMULA,
            msg_id::FORM_FIELD_PLURAL_REVERSE,
            $frm_name,
            html_base::INPUT_TEXT,
            '', $style_text
        );
    }

    /**
     * request the external kay of a reference
     * @param ref|db_object_dsp $dbo the reference object
     * @return string the html code to request the verb name used in a formula
     */
    function form_field_ref_key(ref|db_object_dsp $dbo, string $style_text): string
    {
        $html = new html_base();
        $ref_key = $dbo->external_key();
        if ($ref_key == null) {
            $ref_key = '';
        }
        return $html->form_field(
            url_var::EXTERNAL_KEY,
            msg_id::FORM_FIELD_EXTERNAL_KEY,
            $ref_key,
            html_base::INPUT_TEXT,
            '', $style_text
        );
    }

    /**
     * edit field for the triple weight
     * @param triple|db_object_dsp $trp the triple object
     * @return string the html code to request the triple weight from the user
     */
    function form_field_weight(triple|db_object_dsp $trp): string
    {
        $html = new html_base();
        $weight = $trp->weight;
        if ($weight == null) {
            $weight = '';
        }
        return $html->form_field(
            url_var::WEIGHT,
            msg_id::FORM_FIELD_WEIGHT,
            $weight,
            html_base::INPUT_PERCENT,
            '',view_styles::COL_SM_1
        );
    }

    /**
     * @param db_object_dsp $dbo the object
     * @return string the html code to request a numeric value from the user
     */
    function form_num_value(db_object_dsp $dbo, string $style_text): string
    {
        $html = new html_base();
        $val_txt = $dbo->value();
        if ($val_txt == null) {
            $val_txt = '';
        }
        return $html->form_field(
            url_var::VALUE,
            msg_id::FORM_FIELD_VALUE,
            $val_txt,
            html_base::INPUT_NUMBER,
            '', $style_text
        );
    }

    /**
     * @return string the html code to request a url from the user
     */
    function form_field_url(db_object_dsp $dbo, string $style_text = ''): string
    {
        $html = new html_base();
        $url = $dbo->url();
        if ($url == null) {
            $url = '';
        }
        if ($style_text == '') {
            $style_text = view_styles::COL_SM_12;
        }
        return $html->form_field(
            url_var::URL,
            msg_id::FORM_FIELD_URL,
            $url,
            html_base::INPUT_TEXT,
            '',
            $style_text
        );
    }

    /**
     * @return string the html code to request the group name
     */
    function form_field_group_name(db_object_dsp $dbo): string
    {
        $html = new html_base();
        return $html->form_field(
            url_var::GROUP_NAME,
            msg_id::FORM_FIELD_GROUP,
            $dbo->name(),
            html_base::INPUT_TEXT,
            '',
            view_styles::COL_SM_8
        );
    }

    /**
     * @return string the html code to request the source group name
     */
    function form_field_source_group_name(db_object_dsp $dbo): string
    {
        $html = new html_base();
        return $html->form_field(
            url_var::GROUP_NAME,
            msg_id::FORM_FIELD_GROUP,
            $dbo->name(),
            html_base::INPUT_TEXT,
            '',
            view_styles::COL_SM_8
        );
    }

    /**
     * @return string the html code to request the group name or a list of phrases
     */
    function form_field_group_or_phrases(db_object_dsp $dbo): string
    {
        $html = new html_base();
        return $html->form_field(
            url_var::GROUP_NAME,
            msg_id::FORM_FIELD_GROUP,
            $dbo->name(),
            html_base::INPUT_TEXT,
            '',
            view_styles::COL_SM_8
        );
    }

    /**
     * @return string the html code to request the group name or a list of phrases
     */
    function form_field_source_group_or_phrases(db_object_dsp $dbo): string
    {
        $html = new html_base();
        return $html->form_field(
            url_var::GROUP_NAME,
            msg_id::FORM_FIELD_GROUP,
            $dbo->name(),
            html_base::INPUT_TEXT,
            '',
            view_styles::COL_SM_8
        );
    }

    /**
     * @return string the html code to request the formula link priority
     */
    function form_field_formula_link_priority(db_object_dsp $dbo): string
    {
        $html = new html_base();
        return $html->form_field(
            url_var::FORMULA_LINK_PRIO,
            msg_id::FORM_FIELD_GROUP,
            $dbo->url()
        );
    }

    /**
     * @return string the html code to request the view link priority
     */
    function form_field_view_link_priority(db_object_dsp $dbo): string
    {
        $html = new html_base();
        return $html->form_field(
            url_var::VIEW_TERM_LINK_PRIO,
            msg_id::FORM_FIELD_VIEW_TERM_LINK_PRIO,
            $dbo->url(),
            html_base::INPUT_TEXT,
            '',
            view_styles::COL_SM_12
        );
    }

    /**
     * @return string the html code to request the component position
     */
    function form_field_component_link_order_number(db_object_dsp $dbo): string
    {
        $html = new html_base();
        return $html->form_field(
            url_var::COMPONENT_LINK,
            msg_id::FORM_FIELD_COMPONENT_LINK,
            $dbo->url(),
            html_base::INPUT_TEXT,
            '',
            view_styles::COL_SM_12
        );
    }

    /**
     * @return string the html code to request the view modification start position
     */
    function form_view_relation_pos(db_object_dsp $dbo): string
    {
        $html = new html_base();
        return $html->form_field(
            url_var::POSITION,
            msg_id::FORM_FIELD_COMPONENT_LINK,
            $dbo->url(),
            html_base::INPUT_INT,
            '',
            view_styles::COL_SM_1
        );
    }

    /**
     * @return string the html code to request the selection name from the user
     */
    function form_field_selection_name(db_object_dsp $dbo): string
    {
        $html = new html_base();
        return $html->form_field(
            url_var::GROUP_NAME,
            msg_id::FORM_FIELD_NAME,
            $dbo->name(),
            html_base::INPUT_TEXT,
            '',
            view_styles::COL_SM_8
        );
    }

    /**
     * @return string the html code to request the selection description from the user
     */
    function form_field_selection_description(db_object_dsp $dbo): string
    {
        $html = new html_base();
        return $html->form_field(
            url_var::GROUP_NAME,
            msg_id::FORM_FIELD_GROUP,
            $dbo->name(),
            html_base::INPUT_TEXT,
            '',
            view_styles::COL_SM_8
        );
    }

    /**
     * @return string the html code to request the selection text from the user
     */
    function form_field_selection_text(db_object_dsp $dbo): string
    {
        $html = new html_base();
        return $html->form_field(
            url_var::GROUP_NAME,
            msg_id::FORM_FIELD_GROUP,
            $dbo->name(),
            html_base::INPUT_TEXT,
            '',
            view_styles::COL_SM_8
        );
    }

    /**
     * create the HTML code to select a word or triple
     * selected by the component type form_select_phrase
     * in this case there can be more than only component with the type form_select_phrase
     * all are used to select a phrase
     * but depending on the code_id different url fields and labels are used
     *
     * TODO move form_select_phrase_to to a const
     * TODO remove fixed pattern
     *
     * @param db_object_dsp|triple $dbo the frontend phrase object with the id used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @return string the html code to request the description from the user
     */
    function form_phrase(
        db_object_dsp|triple $dbo,
        string               $form_name,
        string               $code_id = '',
        ?phrase_list         $phr_lst = null,
        bool                 $test_mode = false
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
        $name = url_var::PHRASE;
        $label_id = msg_id::FORM_SELECT_PHRASE;
        if ($code_id == components::FORM_PHRASE_FROM_CODE_ID) {
            $id = $dbo->from()?->id();
            $name = url_var::PHRASE_FROM;
            $label_id = msg_id::FORM_SELECT_PHRASE_FROM;
        } elseif ($code_id == components::FORM_PHRASE_TO_CODE_ID) {
            $id = $dbo->to()?->id();
            $name = url_var::PHRASE_TO;
            $label_id = msg_id::FORM_SELECT_PHRASE_TO;
        } elseif ($code_id == components::FORM_PHRASE_REF_CODE_ID) {
            $id = $dbo->from()?->id();
        } elseif ($code_id == components::FORM_PHRASE_ROW) {
            // TODO Prio 1 activate
            // $id = $dbo->phr_row?->id();
            $id = 1;
            $name = url_var::PHRASE_ROW;
            $label_id = msg_id::FORM_SELECT_PHRASE_ROW;
        } elseif ($code_id == components::FORM_PHRASE_COL) {
            // TODO Prio 1 activate
            //$id = $dbo->phr_col?->id();
            $id = 1;
            $name = url_var::PHRASE_COL;
            $label_id = msg_id::FORM_SELECT_PHRASE_COL;
        } elseif ($code_id == components::FORM_PHRASE_COL_SUB) {
            // TODO Prio 1 activate
            //$id = $dbo->phr_col2?->id();
            $id = 1;
            $name = url_var::PHRASE_COL_SUB;
            $label_id = msg_id::FORM_SELECT_PHRASE_COL_SUB;
        }
        if ($id == null) {
            $id = 0;
            log_warning('id missing in ' . $dbo->dsp_id());
        }

        return $dbo->phrase_selector($phr_lst, $name, $form_name, $id, $pattern, $label_id);
    }

    /**
     * create the HTML code to select one or more words or triples
     * TODO review
     *
     * @param db_object_dsp|triple $dbo the frontend phrase object with the id used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @return string the html code to request the description from the user
     */
    function form_phrases(
        db_object_dsp|triple $dbo,
        string               $form_name,
        string               $code_id = '',
        ?phrase_list         $phr_lst = null,
        bool                 $test_mode = false
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
        $name = url_var::PHRASE;
        $label_id = msg_id::FORM_SELECT_PHRASE;
        if ($code_id == components::FORM_PHRASE_FROM_CODE_ID) {
            $id = $dbo->from()?->id();
            $name = url_var::PHRASE_FROM;
            $label_id = msg_id::FORM_SELECT_PHRASE_FROM;
        } elseif ($code_id == components::FORM_PHRASE_TO_CODE_ID) {
            $id = $dbo->to()?->id();
            $name = url_var::PHRASE_TO;
            $label_id = msg_id::FORM_SELECT_PHRASE_TO;
        } elseif ($code_id == components::FORM_PHRASE_REF_CODE_ID) {
            $id = $dbo->from()?->id();
        } elseif ($code_id == components::FORM_PHRASE_ROW) {
            // TODO Prio 1 activate
            // $id = $dbo->phr_row?->id();
            $id = 1;
            $name = url_var::PHRASE_ROW;
            $label_id = msg_id::FORM_SELECT_PHRASE_ROW;
        } elseif ($code_id == components::FORM_PHRASE_COL) {
            // TODO Prio 1 activate
            //$id = $dbo->phr_col?->id();
            $id = 1;
            $name = url_var::PHRASE_COL;
            $label_id = msg_id::FORM_SELECT_PHRASE_COL;
        } elseif ($code_id == components::FORM_PHRASE_COL_SUB) {
            // TODO Prio 1 activate
            //$id = $dbo->phr_col2?->id();
            $id = 1;
            $name = url_var::PHRASE_COL_SUB;
            $label_id = msg_id::FORM_SELECT_PHRASE_COL_SUB;
        }
        if ($id == null) {
            $id = 0;
            log_warning('id missing in ' . $dbo->dsp_id());
        }

        return $dbo->phrase_selector($phr_lst, $name, $form_name, $id, $pattern, $label_id);
    }

    /**
     * create the HTML code to select a word, verb, triple or formula
     * TODO Prio 1 review
     *
     * @param db_object_dsp|triple $dbo the frontend phrase object with the id used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @return string the html code to request the description from the user
     */
    function form_term(
        db_object_dsp|triple $dbo,
        string               $form_name,
        string               $code_id = '',
        ?phrase_list         $phr_lst = null,
        bool                 $test_mode = false
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
        $name = url_var::PHRASE;
        $label_id = msg_id::FORM_SELECT_PHRASE;
        if ($code_id == components::FORM_PHRASE_FROM_CODE_ID) {
            $id = $dbo->from()?->id();
            $name = url_var::PHRASE_FROM;
            $label_id = msg_id::FORM_SELECT_PHRASE_FROM;
        } else {
            // TODO Prio 1 activate
            //$id = $dbo->phr_col2?->id();
            $id = 1;
            $name = url_var::PHRASE_COL_SUB;
            $label_id = msg_id::FORM_SELECT_PHRASE_COL_SUB;
        }
        if ($id == null) {
            $id = 0;
            log_warning('id missing in ' . $dbo->dsp_id());
        }

        return $dbo->phrase_selector($phr_lst, $name, $form_name, $id, $pattern, $label_id);
    }

    /**
     * create the HTML code to select one or mane words, verbs, triples or formulas
     * TODO Prio 1 review
     *
     * @param db_object_dsp|triple $dbo the frontend phrase object with the id used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @return string the html code to request the description from the user
     */
    function form_terms(
        db_object_dsp|triple $dbo,
        string               $form_name,
        string               $code_id = '',
        ?phrase_list         $phr_lst = null,
        bool                 $test_mode = false
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
        $name = url_var::PHRASE;
        $label_id = msg_id::FORM_SELECT_PHRASE;
        if ($code_id == components::FORM_PHRASE_FROM_CODE_ID) {
            $id = $dbo->from()?->id();
            $name = url_var::PHRASE_FROM;
            $label_id = msg_id::FORM_SELECT_PHRASE_FROM;
        } else {
            // TODO Prio 1 activate
            //$id = $dbo->phr_col2?->id();
            $id = 1;
            $name = url_var::PHRASE_COL_SUB;
            $label_id = msg_id::FORM_SELECT_PHRASE_COL_SUB;
        }
        if ($id == null) {
            $id = 0;
            log_warning('id missing in ' . $dbo->dsp_id());
        }

        return $dbo->phrase_selector($phr_lst, $name, $form_name, $id, $pattern, $label_id);
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
     * create the html code for the form element to select one or more verbs
     * @param db_object_dsp $dbo the frontend phrase object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the verb
     */
    function form_verbs(db_object_dsp $dbo, string $form_name, ?type_lists $typ_lst): string
    {
        return $dbo->verb_selector($form_name, $typ_lst);
    }

    /**
     * create the html code for the form element to select the source
     * @param db_object_dsp $dbo the frontend phrase object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param source_list|null $src_lst the frontend cache with the configuration, the preloaded source and the cached objects
     * @param string $pattern the selection pattern to filter a selection
     * @return string the html code to select the source
     */
    function form_source(db_object_dsp $dbo, string $form_name, ?source_list $src_lst, string $pattern = ''): string
    {
        return $dbo->source_selector($form_name, $pattern, $src_lst);
    }

    /**
     * create the html code for the form element to select one or many sources
     * @param db_object_dsp $dbo the frontend phrase object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param source_list|null $src_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the source
     */
    function form_sources(db_object_dsp $dbo, string $form_name, ?source_list $src_lst): string
    {
        return $dbo->source_selector($form_name, '', $src_lst);
    }

    /**
     * create the html code for the form element to select the reference
     * @param db_object_dsp $dbo the frontend phrase object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @param string $pattern the selection pattern to filter a selection
     * @return string the html code to select the reference
     */
    function form_ref(db_object_dsp $dbo, string $form_name, ?type_lists $typ_lst, string $pattern = ''): string
    {
        return $dbo->ref_selector($form_name, $pattern);
    }

    /**
     * create the html code for the form element to select one or many references
     * TODO Prio 1 review
     * @param db_object_dsp $dbo the frontend phrase object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the reference
     */
    function form_refs(db_object_dsp $dbo, string $form_name, ?type_lists $typ_lst): string
    {
        return $dbo->ref_selector($form_name, '');
    }

    /**
     * create the html code for the form element to select a value
     * @param db_object_dsp $dbo the frontend phrase object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the view
     */
    function form_value(db_object_dsp $dbo, string $form_name, ?type_lists $typ_lst): string
    {
        return $dbo->value_selector($form_name, $typ_lst);
    }

    /**
     * create the html code for the form element to select a value
     * @param db_object_dsp $dbo the frontend phrase object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the view
     */
    function form_values(db_object_dsp $dbo, string $form_name, ?type_lists $typ_lst): string
    {
        return $dbo->value_selector($form_name, $typ_lst);
    }

    /**
     * create the html code for the form element to select a result
     * @param db_object_dsp $dbo the frontend phrase object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the view
     */
    function form_result(db_object_dsp $dbo, string $form_name, ?type_lists $typ_lst): string
    {
        return $dbo->result_selector($form_name, $typ_lst);
    }

    /**
     * create the html code for the form element to select a result
     * @param db_object_dsp $dbo the frontend phrase object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the view
     */
    function form_results(db_object_dsp $dbo, string $form_name, ?type_lists $typ_lst): string
    {
        return $dbo->result_selector($form_name, $typ_lst);
    }

    /**
     * create the html code for the form element to select one formula
     * @param db_object_dsp $dbo the frontend object with the view used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param formula_list|null $frm_lst cached list of views for fast selection
     * @return string the html code to select the view
     */
    function form_formula(db_object_dsp $dbo, string $form_name, ?formula_list $frm_lst): string
    {
        return $dbo->formula_selector($form_name, $frm_lst);
    }

    /**
     * create the html code for the form element to select one formula
     * @param db_object_dsp $dbo the frontend object with the view used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param formula_list|null $frm_lst cached list of views for fast selection
     * @return string the html code to select the view
     */
    function form_formulas(db_object_dsp $dbo, string $form_name, ?formula_list $frm_lst): string
    {
        return $dbo->formula_selector($form_name, $frm_lst);
    }

    /**
     * create the html code for the form element to select the view
     * @param db_object_dsp $dbo the frontend object with the view used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param view_list|null $msk_lst cached list of views for fast selection
     * @return string the html code to select the view
     */
    function form_view(db_object_dsp $dbo, string $form_name, ?view_list $msk_lst): string
    {
        return $dbo->view_selector($form_name, $msk_lst);
    }

    /**
     * create the html code for the form element to select the parent view
     * @param db_object_dsp $dbo the frontend object with the view used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param view_list|null $msk_lst cached list of views for fast selection
     * @return string the html code to select the view
     */
    function form_parent_view(db_object_dsp $dbo, string $form_name, ?view_list $msk_lst): string
    {
        return $dbo->view_selector($form_name, $msk_lst,
            url_var::VIEW_PARENT,msg_id::FORM_SELECT_PARENT_VIEW);
    }

    /**
     * create the html code for the form element to select the child view
     * @param db_object_dsp $dbo the frontend object with the view used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param view_list|null $msk_lst cached list of views for fast selection
     * @return string the html code to select the view
     */
    function form_child_view(db_object_dsp $dbo, string $form_name, ?view_list $msk_lst): string
    {
        return $dbo->view_selector($form_name, $msk_lst,
            url_var::VIEW_CHILD,msg_id::FORM_SELECT_CHILD_VIEW);
    }

    /**
     * create the html code for the form element to select the view
     * there are three fields / functions to select a view:
     *   form_view_default - this select default to set the default view of a sandbox object within a system form
     *   form_view         - the select view as a form field e.g. to select a view for the export
     *   select_view       - the select view as a direct save to change the view of a sandbox object without changing other fields
     *
     * @param db_object_dsp $dbo the frontend object with the view used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param view_list|null $msk_lst cached list of views for fast selection
     * @return string the html code to select the view
     */
    function form_view_default(db_object_dsp $dbo, string $form_name, ?view_list $msk_lst): string
    {
        return $dbo->view_selector($form_name, $msk_lst);
    }

    /**
     * create the html code for the form element to select one or many views
     * @param db_object_dsp $dbo the frontend object with the view used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param view_list|null $msk_lst cached list of views for fast selection
     * @return string the html code to select the view
     */
    function form_views(db_object_dsp $dbo, string $form_name, ?view_list $msk_lst): string
    {
        return $dbo->view_selector($form_name, $msk_lst);
    }

    /**
     * create the html code for the form element to select the component
     * @param db_object_dsp $dbo the frontend object with the component used until now
     * @param string $form_name the name of the component which is also used for the html form name
     * @param string $pattern the pattern used to filter the components by the name
     * @param int $id the id of the component selected until now
     * @param component_list|null $cmp_lst cached list of components for fast selection
     * @return string the html code to select the component
     */
    function form_component(
        db_object_dsp   $dbo,
        string          $form_name,
        string          $pattern,
        int             $id,
        ?component_list $cmp_lst
    ): string
    {
        return $dbo->component_selector($form_name, $pattern, $id, $cmp_lst);
    }

    /**
     * create the html code for the form element to select one or many components
     * @param db_object_dsp $dbo the frontend object with the component used until now
     * @param string $form_name the name of the component which is also used for the html form name
     * @param string $pattern the pattern used to filter the components by the name
     * @param int $id the id of the component selected until now
     * @param component_list|null $msk_lst cached list of components for fast selection
     * @return string the html code to select the component
     */
    function form_components(
        db_object_dsp   $dbo,
        string          $form_name,
        string          $pattern,
        int             $id,
        ?component_list $msk_lst
    ): string
    {
        return $dbo->component_selector($form_name, $pattern, $id, $msk_lst);
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
     * create the html code for the form element to select the value type
     * @param db_object_dsp $dbo the frontend value object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the value type
     */
    function form_value_type(db_object_dsp $dbo, string $form_name, ?type_lists $typ_lst): string
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
     * create the html code for the form element to select the view style
     * used by the view and the component
     *
     * @param db_object_dsp $dbo the frontend view object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the view type
     */
    function form_view_style(db_object_dsp $dbo, string $form_name, ?type_lists $typ_lst): string
    {
        return $dbo->style_selector($form_name, $typ_lst);
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
     * create the html code for the form element to select the component style
     * @param db_object_dsp $dbo the frontend component object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the component style
     */
    function form_component_style(db_object_dsp $dbo, string $form_name, ?type_lists $typ_lst): string
    {
        return $dbo->component_style_selector($form_name, $typ_lst);
    }

    /**
     * create the html code for the form element to select the view relation type
     * @param db_object_dsp $dbo the frontend component object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the view relation type
     */
    function form_view_relation_type(db_object_dsp $dbo, string $form_name, ?type_lists $typ_lst): string
    {
        return $dbo->view_relation_type_selector($form_name, $typ_lst);
    }

    /**
     * create the html code for the form element to select the formula link type
     * @param db_object_dsp $dbo the frontend formula object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the formula link type
     */
    function form_formula_link_type(db_object_dsp $dbo, string $form_name, ?type_lists $typ_lst): string
    {
        return $dbo->formula_link_type_selector($form_name, $typ_lst);
    }

    /**
     * create the html code for the form element to select the view link type
     * @param db_object_dsp $dbo the frontend view object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the view link type
     */
    function form_view_link_type(db_object_dsp $dbo, string $form_name, ?type_lists $typ_lst): string
    {
        return $dbo->view_link_type_selector($form_name, $typ_lst);
    }

    /**
     * create the html code for the form element to select the component link type
     * @param db_object_dsp $dbo the frontend component object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the component link type
     */
    function form_component_link_type(db_object_dsp $dbo, string $form_name, ?type_lists $typ_lst): string
    {
        return $dbo->component_link_type_selector($form_name, $typ_lst);
    }

    /**
     * create the html code for the form element to select the component position type
     * @param db_object_dsp $dbo the frontend component object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the component link type
     */
    function form_component_pos_type(db_object_dsp $dbo, string $form_name, ?type_lists $typ_lst): string
    {
        return $dbo->component_link_type_selector($form_name, $typ_lst);
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
     * TODO Prio 0 review
     * create the html code for the form element to select the protection type
     * @param db_object_dsp $dbo the frontend object with the type used until now
     * @param string $form_name the name of the view which is also used for the html form name
     * @param view_list|null $msk_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the protection type
     */
    function form_table_linked_view(db_object_dsp $dbo, string $form_name, ?view_list $msk_lst): string
    {
        return $dbo->view_selector($form_name, $msk_lst);
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
        return $html->form_field(
            url_var::NEED_ALL,
            msg_id::FORM_FIELD_FORMULA_EXPRESSION,
            $dbo->user_expression(),
            html_base::INPUT_TEXT,
            '',
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
    function button_cancel(int $msk_id, int|string|null $id): string
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
    function button_save(): string
    {
        $html = new html_base();
        return $html->button('Save');
    }

    /**
     * @return string the html code for a form save button
     */
    function button_del(): string
    {
        $html = new html_base();
        return $html->button('Delete', html_base::BS_BTN_DEL);
    }

    /**
     * @return string the html code for a form save button
     */
    function button_import(): string
    {
        $html = new html_base();
        return $html->button('Import', html_base::BS_BTN_IMPORT);
    }

    /**
     * @return string the html code for a form save button
     */
    function button_export(): string
    {
        $html = new html_base();
        return $html->button('Export', html_base::BS_BTN_EXPORT);
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
