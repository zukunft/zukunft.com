<?php

/*

  view_display.php - the extension of the view object to create html code
  ----------------
  
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

use cfg\view_type;
use controller\controller;
use dsp_list;
use html\api;
use html\button;
use html\html_base;
use html\html_selector;
use html\log\user_log_display;
use html\msg;
use model\view;
use model\word;

class view_dsp_old extends view
{

    /*
    object display functions
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
        if ($this->type_id == 1) {
            $result .= '<h1>';
        }
        return $result;
    }

    private function dsp_type_close(): string
    {
        $result = '';
        // move to a view component function
        // for the word array build an object
        if ($this->type_id == 1) {
            $result = $result . '<br><br>';
            //$result = $result . '<a href="/http/view.php?words='.implode (",", $word_array).'&type=3">Really?</a>';
            $result = $result . '</h1>';
        }
        return $result;
    }

    /**
     * return the html code of all view components
     */
    private function dsp_entries($wrd, $back): string
    {
        log_debug('"' . $wrd->name() . '" with the view ' . $this->dsp_id() . ' for user "' . $this->user()->name . '"');

        $result = '';
        $this->load_components();
        foreach ($this->cmp_lst->lst() as $cmp) {
            log_debug('"' . $cmp->name . '" type "' . $cmp->type_id . '"');

            // list of all possible view components
            $cmp_dsp = $cmp->dsp_obj();
            $result .= $cmp_dsp->text();        // just to display a simple text
            $result .= $cmp_dsp->word_name($wrd->phrase()->dsp_obj()); // show the word name and give the user the possibility to change the word name
            $result .= $cmp_dsp->table($wrd); // display a table (e.g. ABB as first word, Cash Flow Statement as second word)
            $result .= $cmp_dsp->num_list($wrd, $back); // a word list with some key numbers e.g. all companies with the PE ratio
            $result .= $cmp_dsp->formulas($wrd); // display all formulas related to the given word
            $result .= $cmp_dsp->results($wrd); // show a list of formula results related to a word
            $result .= $cmp_dsp->word_children($wrd); // show all words that are based on the given start word
            $result .= $cmp_dsp->word_parents($wrd); // show all word that this words is based on
            $result .= $cmp_dsp->json_export($wrd, $back); // offer to configure and create an JSON file
            $result .= $cmp_dsp->xml_export($wrd, $back); // offer to configure and create an XML file
            $result .= $cmp_dsp->csv_export($wrd, $back); // offer to configure and create an CSV file
            $result .= $cmp_dsp->all($wrd->phrase(), $back); // shows all: all words that link to the given word and all values related to the given word
        }

        log_debug('done');
        return $result;
    }

    /**
     * returns the html code for a view: this is the main function of this lib
     * view_id is used to force the display to a set form; e.g. display the sectors of a company instead of the balance sheet
     * view_type_id is used to .... remove???
     * word_id - id of the starting word to display; can be a single word, a comma separated list of word ids, a word group or a word triple
     */
    function display($wrd, $back): string
    {
        log_debug('"' . $wrd->name() . '" with the view ' . $this->dsp_id() . ' (type ' . $this->type_id . ')  for user "' . $this->user()->name . '"');
        $result = '';

        // check and correct the parameters
        if ($back == '') {
            $back = $wrd->id;
        }

        if ($this->id <= 0) {
            log_err("The view id must be loaded to display it.", "view->display");
        } else {
            // display always the view name in the top right corner and allow the user to edit the view
            $result .= $this->dsp_type_open();
            $result .= $this->dsp_navbar($back);
            $result .= $this->dsp_entries($wrd, $back);
            $result .= $this->dsp_type_close();
        }
        log_debug('done');

        return $result;
    }

    /*
    internal functions to display the navbar for the bootstrap and the pure HTML version
    */

    /**
     * true if the view/page is used by the system and should only be changed by an administrator
     */
    private function is_system(): bool
    {
        $result = false;
        if ($this->code_id <> "") {
            $result = true;
        }
        return $result;
    }

    /**
     * show the name of the used view and allow to change it
     */
    private function dsp_view_name(string $back = ''): string
    {
        return 'view <a href="/http/view_select.php?id=' . $this->id . '&word=' . $back . '&back=' . $back . '">' . $this->name . '</a> ';
    }

    /**
     * either the username or the link to create an account
     */
    private function dsp_user(string $back = ''): string
    {
        $result = '';
        if (isset($_SESSION)) {
            if (in_array('logged', $_SESSION)) {
                if ($_SESSION['logged']) {
                    log_debug('for user ' . $_SESSION['user_name']);
                    log_debug('for user ' . $_SESSION['usr_id']);
                    log_debug('for user ' . $back);
                    $result .= '<a href="/http/user.php?id=' . $_SESSION['usr_id'] . '&back=' . $back . '">' . $_SESSION['user_name'] . '</a>';
                    log_debug('user done');
                }
            }
        }
        if ($result == '') {
            if (in_array('HTTP_HOST', $_SERVER)) {
                $url = "//{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
                $back_path = parse_url($url, PHP_URL_PATH);
                $parsed = parse_url($url);
                $query = $parsed['query'];
                parse_str($query, $params);
                unset($params['back']);
                $back = $back_path . '?' . http_build_query($params);
                //$back = htmlspecialchars( $url, ENT_QUOTES, 'UTF-8' );
                $result .= '<a href="/http/login.php?back=' . $back . '">log in</a> or <a href="/http/signup.php">Create account</a>';
            } else {
                $result = 'local test user';
            }
        }

        log_debug('done');
        return $result;
    }

    private function dsp_logout(): string
    {
        $result = '';
        if (in_array('logged', $_SESSION)) {
            if ($_SESSION['logged']) {
                $result = ' <a href="/http/logout.php">log out</a>';
            }
        }
        return $result;
    }

    /*
    pure HTML functions that do not need JavaScript
    */

    private function html_navbar_start(): string
    {
        $html = new html_base();
        $result = $html->dsp_tbl_start();
        $result .= '<tr><td>';
        $result .= '  <tr>';
        $result .= '    <td>';
        $result .= $html->logo();
        $result .= '    </td>';
        return $result;
    }

    /**
     * the zukunft logo that should be show always
     */
    private function html_navbar_end(): string
    {
        $html = new html_base();
        $result = '  </tr>';
        $result .= $html->dsp_tbl_end();
        return $result;
    }

    /**
     * show the standard top right corner, where the user can log in or change the settings
     * @param string $back the id of the word from which the page has been called (TODO to be replace with the back trace object)
     * @returns string the HTML code to display the navigation bar on top of the page
     */
    private function dsp_navbar_html(string $back = ''): string
    {
        $html = new html_base();

        $result = $this->html_navbar_start();
        $result .= '<td class="right_ref">';
        if ($this->is_system() and !$this->user()->is_admin()) {
            $url = $html->url(api::SEARCH);
            $result .= (new button($url, $back))->find(msg::SEARCH_MAIN) . ' - ';
            $result .= $this->name . ' ';
        } else {
            $url = '/http/find.php?word=' . $back;
            $result .= (new button($url, $back))->find(msg::SEARCH_MAIN) . ' - ';
            $result .= $this->dsp_view_name($back);
            $url = $html->url(controller::DSP_VIEW_EDIT, $this->id());
            $result .= (new button($url, $back))->edit(msg::VIEW_EDIT, $this->name) . ' ';
            $url = $html->url(controller::DSP_VIEW_ADD);
            $result .= (new button($url, $back))->add(msg::VIEW_ADD);
        }
        $result .= ' - ';
        log_debug($this->dsp_id() . ' (' . $this->id . ')');
        $result .= $this->dsp_user($back);
        $result .= ' ';
        $result .= $this->dsp_logout();
        $result .= '</td>';
        $result .= $this->html_navbar_end();

        return $result;
    }

    /**
     * same as dsp_navbar, but without the view change used for the view editors
     */
    function dsp_navbar_html_no_view(string $back = ''): string
    {

        $result = $this->html_navbar_start();
        $result .= '<td class="right_ref">';
        $result .= $this->dsp_user($back);
        $result .= $this->dsp_logout();
        $result .= '</td>';
        $result .= $this->html_navbar_end();

        return $result;
    }

    /*
     * JavaScript functions using bootstrap
     */

    // same as dsp_navbar_html, but using bootstrap
    private function dsp_navbar_bs($show_view, $back): string
    {
        $html = new html_base();
        $result = '<nav class="navbar bg-light fixed-top">';
        $result .= $html->logo();
        $result .= '  <form action="/http/find.php" class="form-inline my-2 my-lg-0">';
        $result .= $this->input_search_pattern();
        $result .= '    <button class="btn btn-outline-primary my-2 my-sm-0" type="submit">Get numbers</button>';
        $result .= '  </form>';
        $result .= '  <div class="col-sm-2">';
        $result .= '    <ul class="nav navbar-nav">';
        $result .= '      <li class="active">';
        $result .= $this->dsp_user($back);
        $result .= '      </li>';
        $result .= '      <li class="active">';
        $result .= $this->dsp_logout();
        $result .= '      </li>';
        if ($show_view) {
            $result .= '      <li class="active">';
            $result .= $this->dsp_view_name($back);
            $url_edit = $html->url(view::class . api::UPDATE, $this->id, $back, '', word::class . '=' . $back);
            $result .= (new button($url_edit, $back))->edit(msg::VIEW_EDIT);
            $url_add = $html->url(view::class . api::CREATE, 0, $back, '', word::class . '=' . $back);
            $result .= (new button($url_add, $back))->add(msg::VIEW_ADD);
            $result .= '      </li>';
        }
        $result .= '    </ul>';
        $result .= '  </div>';
        /*
        $result .= '  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">';
        $result .= '    <span class="navbar-toggler-icon"></span>';
        $result .= '  </button>';
        $result .= '  <div class="collapse navbar-collapse" id="navbarSupportedContent">';
        $result .= '    <ul class="navbar-nav mr-auto">';
        // $result .= '      <li><a href="/http/find.php?word='.$back).'"><span class="glyphicon glyphicon-search"></span></a></li>';
        $result .= '      <li class="nav-item dropdown">';
        $result .= '        <a class="nav-link dropdown-toggle" ';
        $result .= '          href="/http/view_select.php?id='.$this->id.'&word='.$back.'&back='.$back.'"';
        $result .= '          id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
        $result .= '          '.$this->name.'';
        $result .= '        </a>';
        $result .= '        <div class="dropdown-menu" aria-labelledby="navbarDropdown">';
        $result .= '          <a class="dropdown-item" href="/http/view_edit.php?id='.$this->id.'&word='.$back.'&back='.$back.'">Edit</a>';
        $result .= '          <a class="dropdown-item" href="#">New</a>';
        $result .= '        </div>';
        $result .= '      </li>';
        $result .= '    </ul>';
        $result .= '  </div>';
        */
        $result .= '</nav>';
        // to avoid that the first data line is below the navbar
        $result .= '<br>';
        $result .= '<br>';
        $result .= '<br>';
        $result .= '<br>';
        $result .= '<br>';
        return $result;
    }

    private function input_search_pattern(): string
    {
        $html = new html_base();
        return $html->input(
            'pattern', '',
            html_base::INPUT_SEARCH,
            html_base::BS_SM_2,
            'word or formula');
    }

    /*
    public functions that switch between the bootstrap and the pure HTML version
    */

    /**
     * show the navigation bar, which allow the user to search, to login or change the settings
     * without javascript this is the top right corner
     * with    javascript this is a bar on the top
     */
    function dsp_navbar(string $back = ''): string
    {
        log_debug();
        $result = '';

        // check the all minimal input parameters are set
        if ($this->user() == null) {
            log_err("The user id must be set to display a view.", "view_dsp->dsp_navbar");
        } elseif ($this->id <= 0) {
            log_err("The display ID (" . $this->id . ") must be set to display a view.", "view_dsp->dsp_navbar");
        } else {
            if ($this->name == '') {
                $this->load_by_id($this->id());
            }
            if (UI_USE_BOOTSTRAP) {
                $result = $this->dsp_navbar_bs(TRUE, $back);
            } else {
                $result = $this->dsp_navbar_html($back);
            }
        }

        log_debug('done');
        return $result;
    }

    /**
     * same as dsp_navbar, but without the view change used for the view editors
     */
    function dsp_navbar_no_view(string $back = ''): string
    {
        $result = '';

        // check the all minimal input parameters are set
        if ($this->user() == null) {
            log_err("The user id must be set to display a view.", "view_dsp->dsp_navbar");
        } else {
            if (UI_USE_BOOTSTRAP) {
                $result .= $this->dsp_navbar_bs(FALSE, $back);
            } else {
                $result .= $this->dsp_navbar_html_no_view($back);
            }
        }
        return $result;
    }

    /**
     * the basic zukunft top elements that should be show always
     */
    function dsp_navbar_simple(): string
    {
        if (UI_USE_BOOTSTRAP) {
            $result = $this->dsp_navbar_bs(FALSE, 0);
        } else {
            $result = $this->html_navbar_start();
            $result .= $this->html_navbar_end();
        }
        return $result;
    }

    /*
    to display the view itself, so that the user can change it
    */

    /**
     * display the history of a view
     */
    function dsp_hist($page, $size, $call, $back): string
    {
        log_debug("for id " . $this->id . " page " . $size . ", size " . $size . ", call " . $call . ", back " . $back . ".");
        $result = ''; // reset the html code var

        $log_dsp = new user_log_display($this->user());
        $log_dsp->id = $this->id;
        $log_dsp->type = view::class;
        $log_dsp->page = $page;
        $log_dsp->size = $size;
        $log_dsp->call = $call;
        $log_dsp->back = $back;
        $result .= $log_dsp->dsp_hist();

        log_debug("done");
        return $result;
    }

    /**
     * display the link history of a view
     */
    function dsp_hist_links($page, $size, $call, $back): string
    {
        log_debug("for id " . $this->id . " page " . $size . ", size " . $size . ", call " . $call . ", back " . $back . ".");
        $result = ''; // reset the html code var

        $log_dsp = new user_log_display($this->user());
        $log_dsp->id = $this->id;
        $log_dsp->type = view::class;
        $log_dsp->page = $page;
        $log_dsp->size = $size;
        $log_dsp->call = $call;
        $log_dsp->back = $back;
        $result .= $log_dsp->dsp_hist_links();

        log_debug("done");
        return $result;
    }

    /*
    // create the HTML code to edit a view
    function edit($wrd) {
      $result = '';

      // check the all minimal input parameters are set
      if ($this->user() == null) {
        zu_err("The user id must be set to display a view.", "view_dsp->dsp_navbar");
      } else {
        $result  = $this->dsp_user($wrd);
        $result .= $this->dsp_logout();
        $result .= '</td>';
        $result .= $this->html_navbar_end();
      }
      return $result;
    }
  */
    /**
     * lists of all view components which are used by this view
     */
    private function linked_components($add_cmp, $wrd, $back): string
    {
        $html = new html_base();

        $result = '';

        if (UI_USE_BOOTSTRAP) {
            $result .= $html->dsp_tbl_start_hist();
        }

        // show the view elements and allow the user to change them
        log_debug('load');
        if (!$this->load_components()) {
            log_err('Loading of view components for ' . $this->dsp_id() . ' failed');
        } else {
            log_debug('loaded');
            $dsp_list = new dsp_list;
            $dsp_list->lst = $this->cmp_lst;
            $dsp_list->id_field = "component_id";
            $dsp_list->script_name = "view_edit.php";
            $dsp_list->class_edit = view::class;
            $dsp_list->script_parameter = $this->id . "&back=" . $back . "&word=" . $wrd->id;
            $result .= $dsp_list->display($back);
            log_debug('displayed');
            if (UI_USE_BOOTSTRAP) {
                $result .= '<tr><td>';
            }

            // check if the add button has been pressed and ask the user what to add
            if ($add_cmp > 0) {
                $result .= 'View component to add: ';
                $url = $html->url(controller::DSP_VIEW_ADD, $this->id, $back, '', word::class . '=' . $wrd->id() . '&add_entry=-1&');
                $result .= (new button($url, $back))->add(msg::COMPONENT_ADD);
                $sel = new html_selector;
                $sel->form = 'view_edit';
                $sel->dummy_text = 'Select a view component ...';
                $sel->name = 'add_component';
                $sel->sql = sql_lst_usr("component", $this->user());
                $sel->selected = 0; // no default view component to add defined yet, maybe use the last???
                $result .= $sel->display_old();

                $result .= $html->dsp_form_end('', "/http/view_edit.php?id=" . $this->id . "&word=" . $wrd->id() . "&back=" . $back);
            } elseif ($add_cmp < 0) {
                $result .= 'Name of the new display element: ';
                $result .= $html->input('entry_name', '', html_base::INPUT_TEXT);
                $sel = new html_selector;
                $sel->form = 'view_edit';
                $sel->dummy_text = 'Select a type ...';
                $sel->name = 'new_entry_type';
                $sel->sql = sql_lst("component_type");
                $sel->selected = $this->type_id;  // ??? should this not be the default entry type
                $result .= $sel->display_old();
                $result .= $html->dsp_form_end('', "/http/view_edit.php?id=" . $this->id . "&word=" . $wrd->id() . "&back=" . $back);
            } else {
                $url = $html->url(controller::DSP_COMPONENT_LINK, $this->id, $back, '', word::class . '=' . $wrd->id() . '&add_entry=1');
                $result .= (new button($url, $back))->add(msg::COMPONENT_ADD);
            }
        }
        if (UI_USE_BOOTSTRAP) {
            $result .= '</td></tr>';
        }

        if (UI_USE_BOOTSTRAP) {
            $result .= $html->dsp_tbl_end();
        }

        return $result;
    }

    /**
     * display the type selector
     */
    private function dsp_type_selector($script, $class, $attribute): string
    {
        $result = '';
        $sel = new html_selector;
        $sel->form = $script;
        $sel->name = 'type';
        $sel->label = "View type:";
        $sel->bs_class = $class;
        $sel->attribute = $attribute;
        $sel->sql = sql_lst("view_type");
        $sel->selected = $this->type_id;
        $sel->dummy_text = '';
        $result .= $sel->display_old();
        return $result;
    }

    /**
     * HTML code to edit all word fields
     */
    function dsp_edit($add_cmp, $wrd, $back): string
    {
        global $view_types;

        $result = '';
        $html = new html_base();

        // use the default settings if needed
        if ($this->type_id <= 0) {
            $this->type_id = $view_types->id(view_type::DEFAULT);
        }

        // the header to add or change a view
        if ($this->id <= 0) {
            log_debug('create a view');
            $script = "view_add";
            $result .= $html->dsp_text_h2('Create a new view (for <a href="/http/view.php?words=' . $wrd->id() . '">' . $wrd->name() . '</a>)');
        } else {
            log_debug($this->dsp_id() . ' for user ' . $this->user()->name . ' (called from ' . $back . ')');
            $script = "view_edit";
            $result .= $html->dsp_text_h2('Edit view "' . $this->name . '" (used for <a href="/http/view.php?words=' . $wrd->id() . '">' . $wrd->name() . '</a>)');
        }
        $result .= '<div class="row">';

        // when changing a view show the fields only on the left side
        if ($this->id > 0) {
            $result .= '<div class="col-sm-7">';
        }

        // show the edit fields
        $result .= $html->dsp_form_start($script);
        $result .= $html->dsp_form_id($this->id);
        $result .= $html->dsp_form_hidden("word", $wrd->id);
        $result .= $html->dsp_form_hidden("back", $back);
        $result .= $html->dsp_form_hidden("confirm", '1');
        $result .= '<div class="form-row">';
        if ($add_cmp < 0 or $add_cmp > 0) {
            // show the fields inactive, because the assign fields are active
            $result .= $html->dsp_form_text("name", $this->name, "Name:", "col-sm-8", "disabled");
            $result .= $this->dsp_type_selector($script, "col-sm-4", "disabled");
            $result .= '</div>';
            $result .= $html->dsp_form_text_big("description", $this->description, "Comment:", "", "disabled");
        } else {
            // show the fields inactive, because the assign fields are active
            $result .= $html->dsp_form_text("name", $this->name, "Name:", "col-sm-8");
            $result .= $this->dsp_type_selector($script, "col-sm-4", "");
            $result .= '</div>';
            $result .= $html->dsp_form_text_big("description", $this->description, "Comment:");
            $result .= $html->dsp_form_end('', $back, "/http/view_del.php?id=" . $this->id . "&back=" . $back);
        }

        // in edit mode show the assigned words and the hist on the right
        if ($this->id > 0) {
            $result .= '</div>';

            $comp_html = $this->linked_components($add_cmp, $wrd, $back);

            // collect the history
            $changes = $this->dsp_hist(0, SQL_ROW_LIMIT, '', $back);
            if (trim($changes) <> "") {
                $hist_html = $changes;
            } else {
                $hist_html = 'Nothing changed yet.';
            }
            $changes = $this->dsp_hist_links(0, SQL_ROW_LIMIT, '', $back);
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

            log_debug('done');
        }

        $result .= '</div>';   // of row
        $result .= '<br><br>'; // this a usually a small for, so the footer can be moved away

        return $result;
    }

}
