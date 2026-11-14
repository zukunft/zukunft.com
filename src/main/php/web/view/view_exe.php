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

use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::COMPONENT . 'component.php';
include_once html_paths::EXECUTE . 'calculator.php';
include_once html_paths::EXECUTE . 'system_page.php';
include_once html_paths::HELPER . 'data_object.php';
include_once html_paths::HTML . 'button.php';
include_once html_paths::HTML . 'display_list.php';
include_once html_paths::HTML . 'html_base.php';
include_once html_paths::HTML . 'styles.php';
include_once html_paths::LOG . 'user_log_display.php';
include_once html_paths::PHRASE . 'phrase.php';
include_once html_paths::PHRASE . 'phrase_data.php';
include_once html_paths::PHRASE . 'phrase_list.php';
include_once html_paths::SANDBOX . 'combine_named.php';
include_once html_paths::SANDBOX . 'db_object.php';
include_once html_paths::SANDBOX . 'sandbox_list.php';
include_once html_paths::TYPES . 'type_object.php';
include_once html_paths::USER . 'user.php';
include_once html_paths::USER . 'user_message.php';
include_once html_paths::VIEW . 'view_base.php';
include_once html_paths::VIEW . 'view_list.php';
include_once html_paths::WORD . 'triple.php';
include_once html_paths::WORD . 'word.php';
include_once html_paths::SHARED_CONST . 'components.php';
include_once html_paths::SHARED_CONST . 'def.php';
include_once html_paths::SHARED_CONST . 'triples.php';
include_once html_paths::SHARED_CONST . 'rest_ctrl.php';
include_once html_paths::SHARED_CONST . 'views.php';
include_once html_paths::SHARED_CONST . 'words.php';
include_once html_paths::SHARED_ENUM . 'foaf_direction.php';
include_once html_paths::SHARED_ENUM . 'messages.php';
include_once html_paths::SHARED_TYPES . 'position_types.php';
include_once html_paths::SHARED_TYPES . 'view_styles.php';
include_once html_paths::SHARED_TYPES . 'view_types.php';
include_once html_paths::SHARED . 'api.php';
include_once html_paths::SHARED . 'url_var.php';
include_once html_paths::SHARED . 'library.php';

use Zukunft\ZukunftCom\main\php\web\component\execute\system_page;
use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\html\styles;
use Zukunft\ZukunftCom\main\php\web\log\user_log_display;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase_data;
use Zukunft\ZukunftCom\main\php\web\sandbox\combine_named;
use Zukunft\ZukunftCom\main\php\web\sandbox\db_object;
use Zukunft\ZukunftCom\main\php\web\sandbox\sandbox_list;
use Zukunft\ZukunftCom\main\php\web\types\type_object;
use Zukunft\ZukunftCom\main\php\web\user\user;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\word\triple;
use Zukunft\ZukunftCom\main\php\web\word\word;
use Zukunft\ZukunftCom\main\php\shared\api;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\types\position_types;
use Zukunft\ZukunftCom\main\php\shared\types\view_styles;
use Zukunft\ZukunftCom\main\php\shared\url_var;

class view_exe extends view_base
{


    /*
     * execute
     */

    /**
     * create the html code to view a sandbox object
     * @param db_object|type_object|combine_named|sandbox_list $dbo the word, triple or formula object that should be shown to the user
     * @param user_message $msg to collect the messages for the user
     * @param data_object|null $cfg the context used to create the view
     * @param string $pattern the selection pattern to filter a selection
     * @param bool $test_mode true to create a reproducible result e.g. by using just one phrase
     * @param array $url_array the url parameters of the page that shows the view, so that the
     *                         links of the components can return to the page and the
     *                         "... more" tail can call the same page with the next list size;
     *                         an empty array if the page is not known
     * @return string the html code for a view: this is the main function of this lib
     */
    function show(
        db_object|type_object|combine_named|sandbox_list $dbo,
        user_message                                     $msg,
        ?data_object                                     $cfg = null,
        string                                           $pattern = '',
        bool                                             $test_mode = false,
        array                                            $url_array = []
    ): string
    {
        $result = '';

        $this->log_debug($dbo->dsp_id() . ' with the view ' . $this->dsp_id());

        // check and correct the parameters
        if ($this->code_id != '') {
            $form_name = $this->code_id;
        } else {
            // a user view has no code id, so its name is the form name, without the chars that
            // an html id may not contain, because the inputs of the page refer to the form by its id
            $form_name = trim(preg_replace('/[^\p{L}\p{N}_-]+/u', '_', $this->name()), '_');
        }

        if ($this->id() <= 0) {
            $this->log_err("The view id must be loaded to display it.");
        } else {
            // the view of the page is in the request cache, so that a component can show its name
            // and description (see system_page::view_title)
            if ($cfg != null) {
                $cfg->msk = $this;
            }
            $this->add_page_cache($dbo, $msg, $cfg);
            // display always the view name in the top right corner and allow the user to edit the view
            $result .= $this->dsp_type_open($msg);
            $result .= new system_page()->calculator_result_width_style($dbo, $cfg);
            // the page that the browser builds from the json data replaces the components, which
            // creates nothing for a phrase without calculator triples or if the config allows no
            // javascript for the calculator, so that the page of the server is shown
            $js_page = '';
            if ($this->is_javascript_page($url_array)) {
                $js_page = $this->code_id == views::TABLE_EDITOR
                    ? new system_page()->table_editor($dbo, $msg, $cfg)
                    : new system_page()->calculator_json($dbo, $msg, $cfg, $form_name, $this->id());
            }
            if ($js_page != '') {
                $result .= $js_page;
            } else {
                // the style of the view is the page class e.g. use-case for the grid of a use case page
                $entries = $this->dsp_entries($dbo, $msg, $cfg, $form_name, $pattern, $test_mode, $url_array);
                $result .= new html_base()->add_style($entries, $this->get_style_id());
            }
            $result .= $this->dsp_type_close($msg);
        }

        return $result;
    }

    /**
     * @param array $url_array the url parameters of the page that shows the view
     * @return bool true if the page that the browser builds from its json data may be shown;
     *              sc=0 (url_var::JAVASCRIPT) asks for the page of the server
     */
    private function is_javascript_page(array $url_array): bool
    {
        $refused = ($url_array[url_var::JAVASCRIPT] ?? '') == url_var::FALSE;
        return !$refused and in_array($this->code_id, views::JAVASCRIPT_PAGES);
    }

    /**
     * create the html code for all components of this view
     *
     * @param db_object|type_object|combine_named|sandbox_list $dbo the word, triple or formula object that should be shown to the user
     * @param data_object|null $cfg the context used to create the view
     * @param string $form_name the name of the view which is also used for the html form name
     * @param string $pattern the selection pattern to filter a selection
     * @param bool $test_mode true to create a reproducible result e.g. by using just one phrase
     * @param array $url_array the url parameters of the page that shows the view (see show)
     * @return string the html code of all view components
     */
    private function dsp_entries(
        db_object|type_object|combine_named|sandbox_list $dbo,
        user_message                                     $msg,
        ?data_object                                     $cfg = null,
        string                                           $form_name = '',
        string                                           $pattern = '',
        bool                                             $test_mode = false,
        array                                            $url_array = []
    ): string
    {
        global $ui_sys;
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

            // if a row contains only standard form components
            // the row start and end can be set automatically
            // if a row contains buttons, hidden components, subheader or related tables
            // the row start and end should be defined by explicit components
            $auto_row = true;
            // the style for the column if used
            $style_id = null;
            // the columns of a side-or-below group that are stacked on small screens
            $col_lst = [];
            // a use case page e.g. the calculator shows its first row as the header and its
            // side-or-below group as panels in one grid instead of the bootstrap rows and columns
            $use_case = $html->get_style_code($this->get_style_id()) == view_styles::USE_CASE;
            foreach ($this->cmp_lst->lst() as $cmp) {
                $pos_type = $cmp->pos_type_code_id($cfg->typ_lst_cache);
                // add previous collected components to the final result
                if ($row != '' or $col_lst != []) {
                    // position the next component in a new row
                    if ($pos_type == position_types::BELOW) {
                        if ($col_lst != []) {
                            // close the side-or-below group with the last collected column
                            $col_lst[] = $row;
                            $result .= $this->dsp_side_or_below_row($col_lst, $msg, $use_case);
                            $col_lst = [];
                            $style_id = null;
                        } elseif ($use_case and $result == '') {
                            $result .= $html->block(html_base::HEADER, $row);
                            $style_id = null;
                        } elseif ($auto_row) {
                            // the row uses the style of the component e.g. col-md-4 for a short line
                            // or the full page width if no style is set
                            // TODO Prio 2 easy move code to HTML class
                            $row_style = $html->get_style_code($style_id);
                            if ($row_style == '') {
                                $row_style = view_styles::DEFAULT_ROW;
                            }
                            $result .= $html->div_row($row, $row_style);
                            $style_id = null;
                        } else {
                            // the component html code is added without adding a table row
                            $result .= $html->add_style($row, $style_id);
                            $style_id = null;
                        }
                        $row = '';
                        $auto_row = true;
                    }
                    if (in_array($pos_type, position_types::SIDE_OR_BELOW_GROUP)) {
                        // the collected components become a column of the side-or-below group;
                        // apply the column style (e.g. text-left set on the column's lead
                        // component) so a column can opt out of the inherited centre alignment
                        $col_lst[] = $html->add_style($row, $style_id);
                        $row = '';
                        $style_id = null;
                    }
                    if ($pos_type == position_types::COLUMN) {
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
                if ($cmp->needs_row_components($cfg->typ_lst_cache, $msg)) {
                    $auto_row = false;
                }
                $tc_id = $cmp->type_code_id($cfg->typ_lst_cache, $msg);
                // the render time per component type splits the url to html time in the report
                $ui_sys?->start_html_step(microtime(true));
                $cmp_html = $cmp->dsp_entries($dbo, $msg, $form_name, $this->id(), $cfg, $cmp->style_id, $pattern, $test_mode, $url_array);
                $ui_sys?->end_html_step($tc_id, microtime(true));
                // the time of this message is the creation of this component incl. its api calls, so a slow component can be found
                log_debug('component ' . $cmp->dsp_id() . ' rendered', url_var::DEBUG_LEVEL_MAIN_STEP);
                // a combined component shares the row with the previous one, but it is still an own
                // component, so it starts on an own line, e.g. the plural of a word below its
                // description instead of behind it; a component that creates no html adds no line
                if ($pos_type == position_types::COMBINE and $cmp_html != '') {
                    $cmp_html = $html->div($cmp_html);
                }
                // a same-line component continues the line of the previous one, so a middle dot
                // separates the two fields e.g. the last update and the source of a value; with
                // an empty row the previous field is missing and the dot would stand alone
                if ($pos_type == position_types::SAME_LINE and $cmp_html != '' and $row != '') {
                    $cmp_html = html_base::MIDDLE_DOT . $cmp_html;
                }
                $row .= $cmp_html;

                // remember the style to apply it to the complete row or column
                // TODO Prio 1 use a row / col explicit style parameter instead
                if ($cmp->style_id != null) {
                    $style_id = $cmp->style_id;
                }

                // Do not add the row or column style
                // if the style has been added by the component already
                // TODO Prio 1 find a more strait forward way to define it
                if ($cmp->no_row_style($tc_id)) {
                    $style_id = null;
                }

            }
            if ($col_lst != []) {
                // close a side-or-below group at the end of the view
                $col_lst[] = $html->add_style($row, $style_id);
                $result .= $this->dsp_side_or_below_row($col_lst, $msg, $use_case);
            } elseif ($row != '') {
                $result .= $row;
            }
        }

        return $result;
    }

    /**
     * combine the columns of a side-or-below group to one row
     * that shows the columns side by side on wide screens
     * and wraps them onto fewer rows (down to a single stacked column)
     * as the screen gets narrower than the configured side widths
     *
     * @param array $col_lst the html code of the columns
     * @param bool $use_case true for a use case page, whose first column is the input panel and
     *                       every following column a result panel in one grid
     * @return string the html code of the row with the wrapping columns
     */
    private function dsp_side_or_below_row(array $col_lst, user_message $msg, bool $use_case = false): string
    {
        $html = new html_base();
        if (!$use_case) {
            return $html->div_row_wrapping_cols($col_lst, $msg);
        }
        // empty columns are skipped like in a bootstrap row, so the first panel shown is the input panel
        $panels = '';
        foreach (array_values(array_filter($col_lst, fn($col) => $col != '')) as $pos => $col) {
            $tag = $pos == 0 ? html_base::ASIDE : html_base::SECTION;
            $panels .= $html->block($tag, $col, styles::PANEL);
        }
        return $html->div($panels, styles::GRID);
    }


    /**
     * fill the request cache with the data that the components of this view show, before the
     * title and the components are created, so that their html creation needs no backend call;
     * for the calculator these are the phrases, values and results of one phraseData answer
     * (docs/llm/new_view_sample_pv_balance.md), for the start page the data of its table
     *
     * @param db_object|type_object|combine_named|sandbox_list $dbo the object of the page
     * @param user_message $msg to report a problem of the api message to the user
     * @param data_object|null $dto the request cache to fill
     * @return void
     */
    function add_page_cache(
        db_object|type_object|combine_named|sandbox_list $dbo,
        user_message                                     $msg,
        ?data_object                                     $dto
    ): void
    {
        if ($this->code_id == views::CALCULATOR and $dto != null and $dto->online
            and ($dbo instanceof word or $dbo instanceof triple)) {
            $this->add_calculator_data($dbo->phrase(), $dto, $msg);
        }
        if ($dto != null) {
            foreach ($this->cmp_lst->lst() as $cmp) {
                $cmp->add_page_cache($dto, $msg);
            }
        }
    }


    /*
     * internal execute
     */

    /**
     * fill the request cache of a calculator page from one answer of the phraseData api, once per
     * page: the calculator tree of the page phrase with the labels, hints, flags and selector
     * options, the values, the results with their context, the formulas and the sources; only if
     * the answer names nothing that it does not contain, the cache is marked as complete, so that
     * else the components load the missing part
     *
     * @param phrase $phr the phrase of the page e.g. example PV installation
     * @param data_object $dto the request cache to fill
     * @param user_message $msg to report a problem of the api message to the user
     * @return void
     */
    private function add_calculator_data(phrase $phr, data_object $dto, user_message $msg): void
    {
        // the title step and the show step both call this, and a second fill would add every formula and source twice
        if ($dto->page_data == []) {
            $phd = new phrase_data();
            $json = $phd->load_from_walk($phr, $msg);
            $dto->page_data = $json;
            if ($json != [] and $phd->fill($json, $dto, $msg)) {
                $dto->page_data_complete = true;
            }
        }
    }

    /**
     * return the beginning html code for the view_type;
     * the view type defines something like the basic setup of a view
     * e.g. the catch view does not have the header, whereas all other views have
     */
    private function dsp_type_open(user_message $msg): string
    {
        $result = '';
        // move to database !!
        // but avoid security leaks
        // maybe use a view component for that
        if ($this->type_id($msg) == 1) {
            $result .= '<h1>';
        }
        return $result;
    }

    private function dsp_type_close(user_message $msg): string
    {
        $result = '';
        // move to a view component function
        // for the word array build an object
        if ($this->type_id($msg) == 1) {
            $result = $result . '<br><br>';
            //$result = $result . '<a href="' . api::MAIN_SCRIPT_REL . '?' . url_var::MASK . '=' . views::PHRASE . '&'
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
     * TODO Prio 2 fill
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
     * display the history of a view
     * @param array $url_arr the url vars of the calling page for the back link of the undo buttons
     */
    function dsp_hist(
        int          $page,
        int          $size,
        string       $call,
        user_message $msg,
        array        $url_arr = []
    ): string
    {
        $log_ui = new user_log_display();
        return $log_ui->dsp_hist(view_exe::class, $this->id(), $size, $page, $msg, '', $url_arr);
    }

    /**
     * display the link history of a view
     * @param array $url_arr the url vars of the calling page for the back link of the undo buttons
     */
    function dsp_hist_links($page, $size, $call, array $url_arr, user_message $msg): string
    {
        $this->log_debug("for id " . $this->id() . " page " . $size . ", size " . $size . ", call " . $call . ".");
        $result = ''; // reset the html code var

        $log_ui = new user_log_display();
        $log_ui->id = $this->id();
        $log_ui->type = view_exe::class;
        $log_ui->page = $page;
        $log_ui->size = $size;
        $log_ui->call = $call;
        $log_ui->url_arr = $url_arr;
        $result .= $log_ui->dsp_hist_links($msg);

        $this->log_debug("done");
        return $result;
    }

}
