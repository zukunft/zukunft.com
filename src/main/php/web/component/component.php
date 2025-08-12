<?php

/*

    web/view/component.php - function to add, change or delete a view component
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

use cfg\const\paths;
use html\const\paths as html_paths;

include_once html_paths::SANDBOX . 'sandbox_typed.php';
include_once paths::DB . 'sql_db.php';
include_once html_paths::HELPER . 'data_object.php';
include_once html_paths::HTML . 'html_base.php';
include_once html_paths::HTML . 'html_selector.php';
include_once html_paths::LOG . 'user_log_display.php';
include_once html_paths::PHRASE . 'phrase.php';
include_once html_paths::PHRASE . 'phrase_list.php';
include_once html_paths::TYPES . 'type_lists.php';
include_once html_paths::TYPES . 'view_style_list.php';
include_once html_paths::SANDBOX . 'db_object.php';
include_once html_paths::SANDBOX . 'sandbox_code_id.php';
include_once html_paths::SYSTEM . 'back_trace.php';
include_once html_paths::VIEW . 'view_list.php';
include_once html_paths::USER . 'user_message.php';
include_once html_paths::WORD . 'word.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_TYPES . 'component_type.php';
include_once paths::SHARED_TYPES . 'position_types.php';
include_once paths::SHARED_TYPES . 'view_styles.php';
include_once paths::SHARED . 'json_fields.php';

use html\helper\data_object;
use html\html_base;
use html\html_selector;
use html\log\user_log_display;
use html\phrase\phrase;
use html\phrase\phrase_list;
use html\phrase\phrase_list as phrase_list_dsp;
use html\sandbox\db_object;
use html\sandbox\sandbox_code_id;
use html\system\back_trace;
use html\types\type_lists;
use html\user\user_message;
use html\view\view_list;
use html\word\word;
use shared\json_fields;
use shared\const\views;
use shared\enum\messages as msg_id;
use shared\types\component_type;
use shared\types\position_types;
use shared\types\view_styles;

class component extends sandbox_code_id
{

    /*
     * const
     */

    // curl views
    const VIEW_ADD = views::COMPONENT_ADD;
    const VIEW_EDIT = views::COMPONENT_EDIT;
    const VIEW_DEL = views::COMPONENT_DEL;

    // curl message id
    const MSG_ADD = msg_id::COMPONENT_ADD;
    const MSG_EDIT = msg_id::COMPONENT_EDIT;
    const MSG_DEL = msg_id::COMPONENT_DEL;


    /*
     * object vars
     */

    public ?int $position = 0;              // for the frontend the position of the link is included in the component object
    public ?int $link_id = 0;               // ??

    // the code_id for the message that should be shown to the user and that should be translated to the frontend language
    public ?msg_id $ui_msg_code_id = null;

    // mainly for table components
    public ?phrase $phr_row = null;     // the main phrase to select the table rows
    public ?phrase $phr_col = null;     // the phrase to select the main table columns
    public ?phrase $wrd_col2 = null;    // the phrase to select the sub table columns

    // vars from the link
    // TODO move these vars to the frontend component link object
    public int $pos_type_id = position_types::DEFAULT_ID;
    public ?int $style_id = null;


    /*
     * api
     */

    /**
     * TODO all set_from_json_array functions should only use json_fields not api::FLD
     * set the vars this component bases on the api json array
     * public because it is reused e.g. by the phrase group display object
     * @param array $json_array an api json message
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function api_mapper(array $json_array): user_message
    {
        $usr_msg = parent::api_mapper($json_array);
        if (array_key_exists(json_fields::UI_MSG_CODE_ID, $json_array)) {
            global $mtr;
            $this->ui_msg_code_id = $mtr->get($json_array[json_fields::UI_MSG_CODE_ID]);
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
        $vars[json_fields::UI_MSG_CODE_ID] = $this->ui_msg_code_id?->value;
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
     * select
     */

    /**
     * create the HTML code to select a component type
     * @param string $form the name of the html form
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the phrase type
     */
    function component_type_selector(string $form, ?type_lists $typ_lst): string
    {
        $used_type_id = $this->type_id();
        if ($used_type_id == null) {
            $used_type_id = $typ_lst->html_component_types->default_id();
        }
        return $typ_lst->html_component_types->selector($form, $used_type_id);
    }


    /*
     * info
     */

    protected function type_code_id(?type_lists $typ_lst): string
    {

        $type_code_id = '';
        if ($typ_lst->html_component_types == null) {
            $this->log_err('html_component_types are empty');
        } else {
            $err_msg = 'Component type code id for ' . $this->dsp_id()
                . ' and type id ' . $this->type_id() . ' missing';
            if ($this->type_id() == null) {
                $this->log_err($err_msg);
            } else {
                $type_code_id = $typ_lst->html_component_types->code_id($this->type_id());
                if ($type_code_id == '') {
                    $this->log_err($err_msg);
                }
            }
        }

        return $type_code_id;
    }

    function pos_type_code_id(?type_lists $typ_lst): string
    {
        $pos_type_code_id = '';
        if ($typ_lst->html_position_types == null) {
            $this->log_err('html_position_types are empty');
        } else {
            $err_msg = 'Position type code id for ' . $this->dsp_id() . ' missing';
            if ($this->pos_type_id == null) {
                $this->log_err($err_msg);
            } else {
                $pos_type_code_id = $typ_lst->html_position_types->code_id($this->pos_type_id);
                if ($pos_type_code_id == '') {
                    $this->log_err($err_msg);
                }
            }
        }

        return $pos_type_code_id;
    }

    /**
     * get the name of the style that is used for the user to select the style
     * @param type_lists|null $typ_lst
     * @return string
     */
    function style_text(?type_lists $typ_lst): string
    {
        $style_name = '';
        if ($typ_lst->html_view_styles == null) {
            $this->log_err('html_view_styles are empty');
        } else {
            if ($this->style_id != null) {
                $style_name = $typ_lst->html_view_styles->name($this->style_id);
            }
        }
        return $style_name;
    }

    /**
     * get the code id that is used for the unique selection of the style and is also the actual html code to use
     * @param type_lists|null $typ_lst
     * @return string
     */
    function style_code_id(?type_lists $typ_lst): string
    {
        $style_name = '';
        if ($typ_lst->html_view_styles == null) {
            $this->log_err('html_view_styles are empty');
        } else {
            if ($this->style_id != null) {
                $style_name = $typ_lst->html_view_styles->code_id($this->style_id);
            }
        }
        return $style_name;
    }


    /*
     * info
     */

    /**
     * @return bool true if the component is a system form button
     */
    function is_button(?type_lists $typ_lst): bool
    {
        if (in_array($this->type_code_id($typ_lst), component_type::BUTTON_TYPES)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return bool true if the component is a hidden system form element
     */
    function is_hidden(?type_lists $typ_lst): bool
    {
        if (in_array($this->type_code_id($typ_lst), component_type::HIDDEN_TYPES)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return bool true if the component is a system form button or a hidden form element
     */
    function is_button_or_hidden(?type_lists $typ_lst): bool
    {
        if ($this->is_button($typ_lst) or $this->is_hidden($typ_lst)) {
            return true;
        } else {
            return false;
        }
    }


    /*
     * select
     */

    /**
     * create the HTML code to select a view type
     * @param string $form the name of the html form
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the phrase type
     */
    function type_selector(string $form, ?type_lists $typ_lst): string
    {
        $used_type_id = $this->type_id();
        if ($used_type_id == null) {
            $used_type_id = $typ_lst->html_component_types->default_id();
        }
        return $typ_lst->html_component_types->selector($form, $used_type_id);
    }

    /**
     * create the HTML code to select a view style
     * @param string $form the name of the html form
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the phrase type
     */
    function style_selector(string $form, ?type_lists $typ_lst): string
    {
        $used_type_id = $this->type_id();
        if ($used_type_id == null) {
            $used_type_id = $typ_lst->html_view_styles->default_id();
        }
        return $typ_lst->html_view_styles->selector($form, $used_type_id);
    }



    /*
     * internal
     */

    /**
     * @param string $form the name of the html form
     * @return string the html code to select the component type
     */
    private function dsp_type_selector(string $form, ?type_lists $typ_lst): string
    {
        return $typ_lst->html_component_types->selector($form);
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
     * @param word $wrd
     * @param back_trace|null $back
     * @return string
     */
    function dsp_edit(int $add_link, word $wrd, back_trace $back = null): string
    {
        $this->log_debug($this->dsp_id() . ' (called from ' . $back->url_encode() . ')');
        $result = '';
        $html = new html_base();

        // show the view component name
        if ($this->id() <= 0) {
            $form_name= views::COMPONENT_ADD;
            $result .= $html->dsp_text_h2('Create a view element for <a href="/http/view.php?words=' . $wrd->id() . '">' . $wrd->name() . '</a>');
        } else {
            $form_name = views::COMPONENT_EDIT;
            $result .= $html->dsp_text_h2('Edit the view element "' . $this->name . '" (used for <a href="/http/view.php?words=' . $wrd->id() . '">' . $wrd->name() . '</a>) ');
        }
        $result .= '<div class="row">';

        // when changing a view component show the fields only on the left side
        if ($this->id() > 0) {
            $result .= '<div class="' . view_styles::COL_SM_7 . '">';
        }

        $result .= $html->dsp_form_start($form_name);
        if ($this->id() > 0) {
            $result .= $html->dsp_form_id($this->id());
        }
        $result .= $html->dsp_form_hidden("word", $wrd->id());
        $result .= $html->dsp_form_hidden("back", $wrd->id());
        $result .= $html->dsp_form_hidden("confirm", 1);
        $result .= '<div class="form-row">';
        $result .= $html->dsp_form_fld("name", $this->name, "Component name:", view_styles::COL_SM_8);
        // TODO Prio 0 check if the generated component edit mask has the type and all other elements used here and remove this function
        //$result .= $this->dsp_type_selector($form_name); // allow to change the type
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
    function html(?phrase $phr = null, ?db_object $dbo = null, ?data_object $cfg = null): string
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
     * @return string a fixed text
     */
    function text(): string
    {
        return $this->name();
    }

    /**
     * TODO move code from component_dsp_old
     * @return string the html code to show a list of values
     */
    function table(?db_object $dbo = null, ?data_object $cfg = null): string
    {
        return 'values related to ' . $this->name() . ' ';
    }

    /**
     * @return string the name of a phrase and give the user the possibility to change the phrase name
     */
    function word_name(phrase $phr): string
    {
        global $cmp_typ_cac;
        if ($cmp_typ_cac->code_id($this->type_id()) == component_type::PHRASE_NAME) {
            return $phr->name();
        } else {
            return '';
        }
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

        $form = 'component_view_links';

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
            // TODO Prio 0 activate
            //$result .= $msk_lst->selector($form, 0, url_var::COMPONENT_LINK_LONG);

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

    /**
     * to select the word or triple
     * @param phrase_list_dsp $phr_lst a preloaded list of suggested phrases for the selection if no additional input is given from the user
     * @param string $name the unique name within the html form for this selector
     * @param string $form the name of the html form
     * @param int|null $selected the row id of the suggested phrase or the already selected phrase
     * @param string $pattern the pattern to filter the phrases
     * @param msg_id $label_id the translation id for the text show to the user
     * @param string $style the style code e.g. to define the target width
     * @return string the html code to select the phrase
     */
    function phrase_selector(
        phrase_list $phr_lst,
        string      $name,
        string      $form,
        ?int        $selected = null,
        string      $pattern = '',
        msg_id      $label_id = msg_id::LABEL_PHRASE,
        string      $style = view_styles::COL_SM_4
    ): string
    {
        if ($phr_lst == null) {
            $phr_lst = new phrase_list();
        }
        return $phr_lst->selector($form, $selected, $name, $label_id, $style, html_selector::TYPE_DATALIST);
    }


}
