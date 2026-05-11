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

namespace Zukunft\ZukunftCom\main\php\web\view;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::COMPONENT . 'component.php';
include_once html_paths::HELPER . 'data_object.php';
include_once html_paths::HTML . 'button.php';
include_once html_paths::HTML . 'display_list.php';
include_once html_paths::HTML . 'html_base.php';
include_once html_paths::LOG . 'user_log_display.php';
include_once html_paths::SANDBOX . 'db_object.php';
include_once html_paths::SYSTEM . 'back_trace.php';
include_once html_paths::USER . 'user.php';
include_once html_paths::VIEW . 'view_base.php';
include_once html_paths::VIEW . 'view_list.php';
include_once html_paths::WORD . 'word.php';
include_once paths::SHARED_CONST . 'components.php';
include_once paths::SHARED_CONST . 'def.php';
include_once paths::SHARED_CONST . 'triples.php';
include_once paths::SHARED_CONST . 'rest_ctrl.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED_CONST . 'words.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_TYPES . 'position_types.php';
include_once paths::SHARED_TYPES . 'view_styles.php';
include_once paths::SHARED_TYPES . 'view_types.php';
include_once paths::SHARED . 'api.php';
include_once paths::SHARED . 'url_var.php';
include_once paths::SHARED . 'library.php';

use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\html\button;
use Zukunft\ZukunftCom\main\php\web\html\display_list;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\log\user_log_display;
use Zukunft\ZukunftCom\main\php\web\sandbox\db_object;
use Zukunft\ZukunftCom\main\php\web\system\back_trace;
use Zukunft\ZukunftCom\main\php\web\user\user;
use Zukunft\ZukunftCom\main\php\web\word\word;
use Zukunft\ZukunftCom\main\php\shared\api;
use Zukunft\ZukunftCom\main\php\shared\const\def;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\types\position_types;
use Zukunft\ZukunftCom\main\php\shared\types\view_styles;
use Zukunft\ZukunftCom\main\php\shared\types\view_types;
use Zukunft\ZukunftCom\main\php\shared\url_var;

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
     * @param string $pattern the selection pattern to filter a selection
     * @param bool $test_mode true to create a reproducible result e.g. by using just one phrase
     * @return string the html code for a view: this is the main function of this lib
     * TODO use backtrace or use a global backtrace var
     */
    function show(
        db_object    $dbo,
        ?data_object $cfg = null,
        string       $back = '',
        string       $pattern = '',
        bool         $test_mode = false
    ): string
    {
        $result = '';

        $this->log_debug($dbo->dsp_id() . ' with the view ' . $this->dsp_id());

        // check and correct the parameters
        if ($this->code_id != '') {
            $form_name = $this->code_id;
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
            //$result .= $this->dsp_navbar($cfg, $back);
            $result .= $this->dsp_entries($dbo, $cfg, $form_name, $back, $pattern, $test_mode);
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
     * @param string $pattern the selection pattern to filter a selection
     * @param bool $test_mode true to create a reproducible result e.g. by using just one phrase
     * @return string the html code of all view components
     */
    private function dsp_entries(
        db_object    $dbo,
        ?data_object $cfg = null,
        string       $form_name = '',
        string       $back = '',
        string       $pattern = '',
        bool         $test_mode = false
    ): string
    {
        $html = new html_base();

        $this->log_debug($this->dsp_id());
        $result = '';
        if ($this->cmp_lst->is_empty()) {
            $this->log_debug('no components for ' . $this->dsp_id());
        } else {
            $row = '';
            // the standard is that each component has its own row
            // and the default style of the component is used
            // the style of the component can be overwritten for each view link
            // if the position type is side the component in the same row as the previous component
            // if the position type is combine the component below the previous component but within an explicitly defined row

            // if a row contains only standard for elements
            // the row start and end can be set automatically
            // if a row contains buttons, hidden components, subheader or related tables
            // the row start and end should be defined by explicit components
            $auto_row = true;
            // the style for the column if used
            $style_id = null;
            foreach ($this->cmp_lst->lst() as $cmp) {
                // add previous collected components to the final result
                if ($row != '') {
                    // position the next component in a new row
                    if ($cmp->pos_type_code_id($cfg->typ_lst_cache) == position_types::BELOW) {
                        if ($auto_row) {
                            // the full page width row if a row contains only standard form elements
                            // TODO easy move code to HTML class
                            $result .= $html->div_row($row, view_styles::DEFAULT_ROW);
                        } else {
                            // the component html code is added without adding a table row
                            $result .= $html->add_style($row, $style_id);
                            $style_id = null;
                        }
                        $row = '';
                        $auto_row = true;
                    }
                    if ($cmp->pos_type_code_id($cfg->typ_lst_cache) == position_types::COLUMN) {
                        // the component html code is added without adding a table row using the same style
                        $result .= $html->add_style($row, $style_id);
                        $row = '';
                    }
                }
                if ($cfg == null) {
                    $this->log_err('frontend data object is missing');
                }
                // if a row contains something else than standard form components
                // it needs to be grouped into a row by explicit components
                // so buttons, hidden components, subheader and lists of related objects
                // must be grouped by explicit start and end row components
                if ($cmp->needs_row_components($cfg->typ_lst_cache)) {
                    $auto_row = false;
                }
                $row .= $cmp->dsp_entries($dbo, $form_name, $this->id(), $cfg, $cmp->style_id, $back, $pattern, $test_mode);

                // remember the style to apply it to the complete row or column
                // TODO Prio 1 use a row / col explicit style parameter instead
                if ($cmp->style_id != null) {
                    $style_id = $cmp->style_id;
                }

                // Do not add the row or column style
                // if the style has been added by the component already
                // TODO Prio 1 find a more strait forward way to define it
                $tc_id = $cmp->type_code_id($cfg->typ_lst_cache);
                if ($cmp->no_row_style($tc_id)) {
                    $style_id = null;
                }

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
            //$result = $result . '<a href="' . api::MAIN_SCRIPT_REL . '?' . url_var::VIEW . '=' . views::PHRASE . '&'
            // . url_var::ID . '='.implode (",", $word_array).'&type=3">Really?</a>';
            $result = $result . '</h1>';
        }
        return $result;
    }

    protected function dsp_user(user $usr): string
    {
        return $usr->name_link();
    }

    /**
     * TODO fill
     * @return string
     */
    protected function user(): string
    {
        return 'Missing user';
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
        global $sys;
        global $cfg;

        $result = '';
        $html = new html_base();

        // use the default settings if needed
        if ($this->type_id() <= 0) {
            $this->set_type_id($sys->typ_lst->msk_typ->id(view_types::DEFAULT));
        }

        // the header to add or change a view
        if ($this->id() <= 0) {
            $this->log_debug('create a view');
            $script = "view_add";
            $result .= $html->dsp_text_h2('Create a new view (for '
                . $html->ref_view(views::PHRASE, $wrd->id(), $wrd->name()) . ')');
        } else {
            $this->log_debug($this->dsp_id() . ' for user ' . $usr->name() . ' (called from ' . $back . ')');
            $script = "view_edit";
            $result .= $html->dsp_text_h2('Edit view "' . $this->name . '" (used for '
                . $html->ref_view(views::PHRASE, $wrd->id(), $wrd->name()) . ')');
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
            $result .= $html->dsp_form_text("name", $this->name, msg_id::FORM_FIELD_NAME, view_styles::COL_SM_8, "disabled");
            //$result .= $this->dsp_type_selector($script, view_styles::COL_SM_4, "disabled");
            $result .= '</div>';
            $result .= $html->dsp_form_text_big("description", $this->description, msg_id::FORM_FIELD_DESCRIPTION, "", "disabled");
        } else {
            // show the fields inactive, because the assign fields are active
            $result .= $html->dsp_form_text("name", $this->name, msg_id::FORM_FIELD_NAME, view_styles::COL_SM_8);
            $result .= $this->dsp_type_selector($script);
            $result .= '</div>';
            $result .= $html->dsp_form_text_big("description", $this->description, msg_id::FORM_FIELD_DESCRIPTION);
            $result .= $html->dsp_form_end('', $back, "/http/view_del.php?id=" . $this->id() . "&back=" . $back);
        }

        // in edit mode show the assigned words and the hist on the right
        if ($this->id() > 0) {
            $result .= '</div>';

            $comp_html = $this->linked_components($add_cmp, $wrd, $script, $back);
            $row_limit = $cfg->get_by([triples::ROW_LIMIT, words::DATABASE], def::FALLBACK_DB_PAGE_ROWS);

            // collect the history
            $changes = $this->dsp_hist(0, $row_limit, '', $back);
            if (trim($changes) <> "") {
                $hist_html = $changes;
            } else {
                $hist_html = 'Nothing changed yet.';
            }
            $changes = $this->dsp_hist_links(0, $row_limit, '', $back);
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
     * @param string $form the name of the html form
     * @return string the html code for the view type selector
     */
    private function dsp_type_selector(string $form): string
    {//$sel->bs_class = $class;
        //$sel->attribute = $attribute;
        return null->html_view_types->selector($form);
    }

    /**
     * lists of all view components which are used by this view
     */
    private function linked_components($add_cmp, $wrd, string $script, $back): string
    {
        $html = new html_base();
        global $ui_cfg;

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
            $dsp_list->script_parameter = $this->id() . "&back=" . $back . "&word=" . $wrd->id();
            $result .= $dsp_list->display(view_exe::class, $this->id(), $back);
            $this->log_debug('displayed');
            if (html_base::UI_USE_BOOTSTRAP) {
                $result .= '<tr><td>';
            }

            // check if the add button has been pressed and ask the user what to add
            if ($add_cmp > 0) {
                $result .= 'View component to add: ';
                $url = $html->url(api::DSP_VIEW_ADD, $this->id(), $back, '', word::class . '=' . $wrd->id() . '&add_entry=-1&');
                $result .= new button($url, $back)->add(msg_id::COMPONENT_ADD);
                $id_selected = 0; // no default view component to add defined yet, maybe use the last???
                $result .= $this->component_selector($script, '', $id_selected, $ui_cfg->component_list());

                $result .= $html->dsp_form_end('', "/http/view_edit.php?id=" . $this->id() . "&word=" . $wrd->id() . "&back=" . $back);
            } elseif ($add_cmp < 0) {
                $result .= 'Name of the new display element: ';
                $result .= $html->input(url_var::NAME, msg_id::FORM_FIELD_NAME, '', html_base::INPUT_TEXT);
                // TODO ??? should this not be the default entry type
                $result .= $this->component_selector($script, '', $this->type_id(), $ui_cfg->component_list());
                $result .= $html->dsp_form_end('', "/http/view_edit.php?id=" . $this->id() . "&word=" . $wrd->id() . "&back=" . $back);
            } else {
                $url = $html->url(api::DSP_COMPONENT_LINK, $this->id(), $back, '', word::class . '=' . $wrd->id() . '&add_entry=1');
                $result .= new button($url, $back)->add(msg_id::COMPONENT_ADD);
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
        int         $page,
        int         $size,
        string      $call,
        ?back_trace $back = null
    ): string
    {
        $log_ui = new user_log_display();
        return $log_ui->dsp_hist(view_exe::class, $this->id(), $size, $page, '', $back);
    }

    /**
     * display the link history of a view
     */
    function dsp_hist_links($page, $size, $call, $back): string
    {
        $this->log_debug("for id " . $this->id() . " page " . $size . ", size " . $size . ", call " . $call . ", back " . $back . ".");
        $result = ''; // reset the html code var

        $log_ui = new user_log_display();
        $log_ui->id = $this->id();
        $log_ui->type = view_exe::class;
        $log_ui->page = $page;
        $log_ui->size = $size;
        $log_ui->call = $call;
        $log_ui->back = $back;
        $result .= $log_ui->dsp_hist_links();

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
        $html = new html_base();

        $dsp_lst = new view_list();

        $call = api::MAIN_SCRIPT . '?' . url_var::VIEW . '=' . views::PHRASE . '&' .url_var::ID . '=' . $wrd_id;
        $field = 'new_id';

        foreach ($dsp_lst as $dsp) {
            $view_id = $dsp->id();
            $view_name = $dsp->name();
            if ($view_id == $this->id()) {
                $result .= '<b>' . $html->ref($call . '&' . $field . '=' . $view_id, $view_name) . '</b> ';
            } else {
                $result .= $html->ref($call . '&' . $field . '=' . $view_id, $view_name) . ' ';
            }
            $call_edit = '/http/view_edit.php?id=' . $view_id . '&word=' . $wrd_id . '&back=' . $back;
            $result .= btn_edit('design the view', $call_edit) . ' ';
            $call_del = '/http/view_del.php?id=' . $view_id . '&word=' . $wrd_id . '&back=' . $back;
            $result .= \Zukunft\ZukunftCom\main\php\web\btn_del('delete the view', $call_del) . ' ';
            $result .= '<br>';
        }

        $this->log_debug('done');
        return $result;
    }

}
