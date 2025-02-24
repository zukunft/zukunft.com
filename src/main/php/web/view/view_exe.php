<?php

/*

    web/view/view_exe.php - adding the view execution to the main view object
    ---------------------

    to create the HTML code to display a view

    The main sections of this object are
    - execute:           create the html code for an object view


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

namespace html\view;

include_once WEB_VIEW_PATH . 'view_base.php';
include_once WEB_HTML_PATH . 'button.php';
include_once WEB_HTML_PATH . 'display_list.php';
include_once WEB_HTML_PATH . 'html_base.php';
include_once WEB_HTML_PATH . 'rest_ctrl.php';
include_once WEB_COMPONENT_PATH . 'component.php';
include_once WEB_HELPER_PATH . 'config.php';
include_once WEB_HELPER_PATH . 'data_object.php';
include_once WEB_LOG_PATH . 'user_log_display.php';
include_once WEB_SANDBOX_PATH . 'db_object.php';
include_once WEB_SYSTEM_PATH . 'messages.php';
include_once WEB_SYSTEM_PATH . 'back_trace.php';
include_once WEB_VIEW_PATH . 'view_list.php';
include_once WEB_WORD_PATH . 'word.php';
include_once SHARED_CONST_PATH . 'components.php';
include_once SHARED_TYPES_PATH . 'position_types.php';
include_once SHARED_TYPES_PATH . 'view_styles.php';
include_once SHARED_TYPES_PATH . 'view_type.php';
include_once SHARED_PATH . 'api.php';
include_once SHARED_PATH . 'library.php';


use html\button;
use html\display_list;
use html\helper\config;
use html\helper\data_object;
use html\html_base;
use html\log\user_log_display;
use html\sandbox\db_object;
use html\system\back_trace;
use html\system\messages;
use html\word\word;
use shared\api;
use shared\types\position_types;
use shared\types\view_styles;
use shared\types\view_type;

class view_exe extends view_base
{

    /*
     * execute
     */

    /**
     * create the html code to view a sandbox object
     * @param db_object $dbo the word, triple or formula object that should be shown to the user
     * @param data_object|null $cfg the context used to create the view
     * @param string $back the history of the user actions to allow rollbacks
     * @param bool $test_mode true to create a reproducible result e.g. by using just one phrase
     * @return string the html code for a view: this is the main function of this lib
     * TODO use backtrace or use a global backtrace var
     */
    function show(
        db_object    $dbo,
        ?data_object $cfg = null,
        string       $back = '',
        bool         $test_mode = false
    ): string
    {
        $result = '';

        $this->log_debug($dbo->dsp_id() . ' with the view ' . $this->dsp_id());

        // check and correct the parameters
        if ($this->code_id() != '') {
            $form_name = $this->code_id();
        } else {
            $form_name = $this->name();
        }
        if ($back == '') {
            $back = $dbo->id();
        }

        if ($this->id() <= 0) {
            $this->log_err("The view id must be loaded to display it.");
        } else {
            // display always the view name in the top right corner and allow the user to edit the view
            $result .= $this->dsp_type_open();
            $result .= $this->dsp_navbar($back);
            $result .= $this->dsp_entries($dbo, $cfg, $form_name, $back, $test_mode);
            $result .= $this->dsp_type_close();
        }

        return $result;
    }

    /**
     * create the html code for all components of this view
     *
     * @param db_object $dbo the word, triple or formula object that should be shown to the user
     * @param data_object|null $cfg the context used to create the view
     * @param string $form_name the name of the view which is also used for the html form name
     * @param string $back the backtrace for undo actions
     * @param bool $test_mode true to create a reproducible result e.g. by using just one phrase
     * @return string the html code of all view components
     */
    private function dsp_entries(
        db_object    $dbo,
        ?data_object $cfg = null,
        string       $form_name = '',
        string       $back = '',
        bool         $test_mode = false
    ): string
    {
        $this->log_debug($this->dsp_id());
        $result = '';
        if ($this->cmp_lst->is_empty()) {
            $this->log_debug('no components for ' . $this->dsp_id());
        } else {
            $row = '';
            $button_only = true;
            foreach ($this->cmp_lst->lst() as $cmp) {
                // add previous collected components to the final result
                if ($row != '') {
                    if ($cmp->pos_type_code_id() == position_types::BELOW) {
                        if ($button_only) {
                            $result .= $row;
                        } else {
                            // TODO easy move code to HTML class
                            $result .= '<div class="row ';
                            $result .= view_styles::COL_SM_12;
                            $result .= '">' . $row . ' </div>';
                        }
                        $row = '';
                        $button_only = true;
                    }
                }
                if (!$cmp->is_button_or_hidden()) {
                    $button_only = false;
                }
                $row .= $cmp->dsp_entries($dbo, $form_name, $this->id(), $cfg, $back, $test_mode);
            }
            if ($row != '') {
                $result .= $row;
            }
        }

        return $result;
    }


    /*
     * internal execute
     */

    /**
     * return the beginning html code for the view_type;
     * the view type defines something like the basic setup of a view
     * e.g. the catch view does not have the header, whereas all other views have
     */
    private function dsp_type_open(): string
    {
        $result = '';
        // move to database !!
        // but avoid security leaks
        // maybe use a view component for that
        if ($this->type_id() == 1) {
            $result .= '<h1>';
        }
        return $result;
    }

    private function dsp_type_close(): string
    {
        $result = '';
        // move to a view component function
        // for the word array build an object
        if ($this->type_id() == 1) {
            $result = $result . '<br><br>';
            //$result = $result . '<a href="/http/view.php?words='.implode (",", $word_array).'&type=3">Really?</a>';
            $result = $result . '</h1>';
        }
        return $result;
    }

    protected function dsp_user(): string
    {
        return '';
    }

    /**
     * TODO fill
     * @return string
     */
    protected function user(): string
    {
        return '';
    }


    /*
     * to review / deprecate
     */


    /*
     * to review
     */

    /**
     * HTML code to edit all word fields
     */
    function dsp_edit($add_cmp, $wrd, $back): string
    {
        global $usr;
        global $msk_typ_cac;

        $result = '';
        $html = new html_base();

        // use the default settings if needed
        if ($this->type_id() <= 0) {
            $this->set_type_id($msk_typ_cac->id(view_type::DEFAULT));
        }

        // the header to add or change a view
        if ($this->id() <= 0) {
            $this->log_debug('create a view');
            $script = "view_add";
            $result .= $html->dsp_text_h2('Create a new view (for <a href="/http/view.php?words=' . $wrd->id() . '">' . $wrd->name() . '</a>)');
        } else {
            $this->log_debug($this->dsp_id() . ' for user ' . $usr->name() . ' (called from ' . $back . ')');
            $script = "view_edit";
            $result .= $html->dsp_text_h2('Edit view "' . $this->name . '" (used for <a href="/http/view.php?words=' . $wrd->id() . '">' . $wrd->name() . '</a>)');
        }
        $result .= '<div class="row">';

        // when changing a view show the fields only on the left side
        if ($this->id() > 0) {
            $result .= '<div class="' . view_styles::COL_SM_7 . '">';
        }

        // show the edit fields
        $result .= $html->dsp_form_start($script);
        $result .= $html->dsp_form_id($this->id());
        $result .= $html->dsp_form_hidden("word", $wrd->id);
        $result .= $html->dsp_form_hidden("back", $back);
        $result .= $html->dsp_form_hidden("confirm", '1');
        $result .= '<div class="form-row">';
        if ($add_cmp < 0 or $add_cmp > 0) {
            // show the fields inactive, because the assign fields are active
            $result .= $html->dsp_form_text("name", $this->name, "Name:", view_styles::COL_SM_8, "disabled");
            $result .= $this->dsp_type_selector($script, view_styles::COL_SM_4, "disabled");
            $result .= '</div>';
            $result .= $html->dsp_form_text_big("description", $this->description, "Comment:", "", "disabled");
        } else {
            // show the fields inactive, because the assign fields are active
            $result .= $html->dsp_form_text("name", $this->name, "Name:", view_styles::COL_SM_8);
            $result .= $this->dsp_type_selector($script, view_styles::COL_SM_4, "");
            $result .= '</div>';
            $result .= $html->dsp_form_text_big("description", $this->description, "Comment:");
            $result .= $html->dsp_form_end('', $back, "/http/view_del.php?id=" . $this->id() . "&back=" . $back);
        }

        // in edit mode show the assigned words and the hist on the right
        if ($this->id() > 0) {
            $result .= '</div>';

            $comp_html = $this->linked_components($add_cmp, $wrd, $script, $back);

            // collect the history
            $changes = $this->dsp_hist(0, shared_config::ROW_LIMIT, '', $back);
            if (trim($changes) <> "") {
                $hist_html = $changes;
            } else {
                $hist_html = 'Nothing changed yet.';
            }
            $changes = $this->dsp_hist_links(0, shared_config::ROW_LIMIT, '', $back);
            if (trim($changes) <> "") {
                $link_html = $changes;
            } else {
                $link_html = 'No component have been added or removed yet.';
            }

            // display the tab box with the links and changes
            $result .= $html->dsp_link_hist_box('Components', $comp_html,
                '', '',
                'Changes', $hist_html,
                'Component changes', $link_html);

            $this->log_debug('done');
        }

        $result .= '</div>';   // of row
        $result .= '<br><br>'; // this a usually a small for, so the footer can be moved away

        return $result;
    }

    /**
     * @param string $script the name of the html form
     * @return string the html code for the view type selector
     */
    private function dsp_type_selector(string $script, string $class, string $attribute): string
    {
        global $html_view_types;
        //$sel->bs_class = $class;
        //$sel->attribute = $attribute;
        return $html_view_types->selector($script);
    }

    /**
     * lists of all view components which are used by this view
     */
    private function linked_components($add_cmp, $wrd, string $script, $back): string
    {
        $html = new html_base();

        $result = '';

        if (html_base::UI_USE_BOOTSTRAP) {
            $result .= $html->dsp_tbl_start_hist();
        }

        // show the view elements and allow the user to change them
        $this->log_debug('load');
        if (!$this->load_components()) {
            $this->log_err('Loading of view components for ' . $this->dsp_id() . ' failed');
        } else {
            $this->log_debug('loaded');
            $dsp_list = new display_list;
            $dsp_list->lst = $this->cmp_lst->lst();
            $dsp_list->script_name = "view_edit.php";
            $dsp_list->class_edit = view_exe::class;
            $dsp_list->script_parameter = $this->id() . "&back=" . $back . "&word=" . $wrd->id();
            $result .= $dsp_list->display($back);
            $this->log_debug('displayed');
            if (html_base::UI_USE_BOOTSTRAP) {
                $result .= '<tr><td>';
            }

            // check if the add button has been pressed and ask the user what to add
            if ($add_cmp > 0) {
                $result .= 'View component to add: ';
                $url = $html->url(api::DSP_VIEW_ADD, $this->id(), $back, '', word::class . '=' . $wrd->id() . '&add_entry=-1&');
                $result .= (new button($url, $back))->add(messages::COMPONENT_ADD);
                $id_selected = 0; // no default view component to add defined yet, maybe use the last???
                $result .= $this->component_selector($script, '', $id_selected);

                $result .= $html->dsp_form_end('', "/http/view_edit.php?id=" . $this->id() . "&word=" . $wrd->id() . "&back=" . $back);
            } elseif ($add_cmp < 0) {
                $result .= 'Name of the new display element: ';
                $result .= $html->input('entry_name', '', html_base::INPUT_TEXT);
                // TODO ??? should this not be the default entry type
                $result .= $this->component_selector($script, '', $this->type_id());
                $result .= $html->dsp_form_end('', "/http/view_edit.php?id=" . $this->id() . "&word=" . $wrd->id() . "&back=" . $back);
            } else {
                $url = $html->url(api::DSP_COMPONENT_LINK, $this->id(), $back, '', word::class . '=' . $wrd->id() . '&add_entry=1');
                $result .= (new button($url, $back))->add(messages::COMPONENT_ADD);
            }
        }
        if (html_base::UI_USE_BOOTSTRAP) {
            $result .= '</td></tr>';
        }

        if (html_base::UI_USE_BOOTSTRAP) {
            $result .= $html->dsp_tbl_end();
        }

        return $result;
    }

    /**
     * display the history of a view
     */
    function dsp_hist(
        int        $page,
        int        $size,
        string     $call,
        back_trace $back = null
    ): string
    {
        $log_dsp = new user_log_display();
        return $log_dsp->dsp_hist(view_exe::class, $this->id(), $size, $page, '', $back);
    }

    /**
     * display the link history of a view
     */
    function dsp_hist_links($page, $size, $call, $back): string
    {
        $this->log_debug("for id " . $this->id() . " page " . $size . ", size " . $size . ", call " . $call . ", back " . $back . ".");
        $result = ''; // reset the html code var

        $log_dsp = new user_log_display();
        $log_dsp->id = $this->id();
        $log_dsp->type = view_exe::class;
        $log_dsp->page = $page;
        $log_dsp->size = $size;
        $log_dsp->call = $call;
        $log_dsp->back = $back;
        $result .= $log_dsp->dsp_hist_links();

        $this->log_debug("done");
        return $result;
    }

    private function load_components(): bool
    {
        return true;
    }

    /**
     * create a selection page where the user can select a view
     * that should be used for a term
     *
     */
    function selector_page($wrd_id, $back): string
    {
        $this->log_debug($this->id() . ',' . $wrd_id);

        $result = '';

        $dsp_lst = new view_list();

        $call = '/http/view.php?words=' . $wrd_id;
        $field = 'new_id';

        foreach ($dsp_lst as $dsp) {
            $view_id = $dsp->id();
            $view_name = $dsp->name();
            if ($view_id == $this->id()) {
                $result .= '<b><a href="' . $call . '&' . $field . '=' . $view_id . '">' . $view_name . '</a></b> ';
            } else {
                $result .= '<a href="' . $call . '&' . $field . '=' . $view_id . '">' . $view_name . '</a> ';
            }
            $call_edit = '/http/view_edit.php?id=' . $view_id . '&word=' . $wrd_id . '&back=' . $back;
            $result .= \html\btn_edit('design the view', $call_edit) . ' ';
            $call_del = '/http/view_del.php?id=' . $view_id . '&word=' . $wrd_id . '&back=' . $back;
            $result .= \html\btn_del('delete the view', $call_del) . ' ';
            $result .= '<br>';
        }

        $this->log_debug('done');
        return $result;
    }

}
