<?php

/*

  formula_display.php - the extension of the formula object to create UI JSON messages or direct html code
  -------------------
  
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

namespace formula;

use html\api;
use html\button;
use html\html_base;
use html\html_selector;
use html\log\user_log_display;
use model\formula;
use model\phrase;
use model\result;
use model\result_list;

class formula_dsp_old extends formula
{

    // create the HTML code to display the formula name with the HTML link
    function name_linked(?string $back = ''): string
    {
        if ($back) {
            return '<a href="/http/formula_edit.php?id=' . $this->id . '">' . $this->name . '</a>';
        } else {
            return '<a href="/http/formula_edit.php?id=' . $this->id . '&back=' . $back . '">' . $this->name . '</a>';
        }
    }

    // create the HTML code to display the formula text in the human-readable format including links to the formula elements
    function dsp_text(string $back = ''): string
    {
        log_debug();
        $result = $this->usr_text;

        $exp = $this->expression();
        $elm_lst = $exp->element_list();
        foreach ($elm_lst->lst() as $elm) {
            log_debug("replace " . $elm->name() . " with " . $elm->name_linked($back) . ".");
            $result = str_replace('"' . $elm->name() . '"', $elm->name_linked($back), $result);
        }

        log_debug($result);
        return $result;
    }

    /**
     * display the most interesting formula result for one word
     * TODO define the criteria and review the result loading
     */
    function dsp_result(phrase $phr, $back): string
    {
        log_debug('for "' . $phr->name() . '" and formula ' . $this->dsp_id());
        $res = new result($this->user());
        $res->frm = $this;
        $res->phr = $phr;
        log_debug('load result');
        $res->load_obj_vars();
        log_debug('display');
        return $res->display($back);
    }

    /**
     * create the HTML code for a button to change the formula
     * @param string $back the stack trace for the undo functionality
     * @return string html code to change to formula
     */
    function btn_edit(string $back = ''): string
    {
        $url = (new html_base())->url(self::class . api::UPDATE, $this->id, $back);
        return (new button('Change ' . self::class . $this->name, $url))->edit();
    }

    /**
     * create the HTML code for a button to delete or exclude the formula
     * @param string $back the stack trace for the undo functionality
     * @return string html code to delete or exclude to formula
     */
    function btn_del(string $back = ''): string
    {
        $url = (new html_base())->url(self::class . api::REMOVE, $this->id, $back);
        return (new button('Delete ' . self::class . $this->name, $url))->del();
    }

    // allow the user to unlink a word
    function dsp_unlink_phr($phr_id, $back): string
    {
        log_debug($phr_id);
        $result = '    <td>' . "\n";
        $url = api::PATH_FIXED . self::class . api::UPDATE . api::EXT .'?id=' . $this->id . '&unlink_phrase=' . $phr_id . '&back=' . $back;
        $result .=  (new button("unlink word", $url))->del();
        $result .= '    </td>' . "\n";
        return $result;
    }

    // display the formula type selector
    function dsp_type_selector($script, $class): string
    {
        $result = '';
        $sel = new html_selector;
        $sel->form = $script;
        $sel->name = "type";
        $sel->label = "Formula type:";
        $sel->bs_class = $class;
        $sel->sql = sql_lst("formula_type");
        $sel->selected = $this->type_id;
        $sel->dummy_text = 'select a predefined type if needed';
        $result .= $sel->display() . ' ';
        return $result;
    }

    // display the history of a formula
    private function dsp_hist_log($page, $size, $call, $back): user_log_display
    {
        $log_dsp = new user_log_display($this->user());
        $log_dsp->id = $this->id;
        $log_dsp->usr = $this->user();
        $log_dsp->type = formula::class;
        $log_dsp->page = $page;
        $log_dsp->size = $size;
        $log_dsp->call = $call;
        $log_dsp->back = $back;
        return $log_dsp;
    }

    // display the history of a formula
    function dsp_hist($page, $size, $call, $back): string
    {
        log_debug("for id " . $this->id . " page " . $size . ", size " . $size . ", call " . $call . ", back " . $back . ".");
        $result = ''; // reset the html code var

        $log_dsp = $this->dsp_hist_log($page, $size, $call, $back);
        $result .= $log_dsp->dsp_hist();

        log_debug("done");
        return $result;
    }

    // display the link history of a formula
    function dsp_hist_links($page, $size, $call, $back): string
    {
        log_debug("for id " . $this->id . " page " . $size . ", size " . $size . ", call " . $call . ", back " . $back . ".");
        $result = ''; // reset the html code var

        $log_dsp = $this->dsp_hist_log($page, $size, $call, $back);
        $result .= $log_dsp->dsp_hist_links();

        log_debug("done");
        return $result;
    }

    // list all words linked to the formula and allow to unlink or add new words
    function dsp_used4words($add, $wrd, $back): string
    {
        log_debug($this->ref_text . " for " . $wrd->name() . ",back:" . $back . " and user " . $this->user()->name . ".");
        $result = '';

        $html = new html_base();

        $phr_lst = $this->assign_phr_ulst_direct();
        log_debug("words linked loaded");

        // list all linked words
        $result .= $html->dsp_tbl_start_half();
        foreach ($phr_lst->lst() as $phr_linked) {
            $result .= '  <tr>' . "\n";
            $result .= $phr_linked->dsp_tbl(0);
            $result .= $this->dsp_unlink_phr($phr_linked->id(), $back);
            $result .= '  </tr>' . "\n";
        }

        // give the user the possibility to add a similar word
        log_debug("user");
        $result .= '  <tr>';
        $result .= '    <td>';
        if ($add == 1 or $wrd->id > 0) {
            $sel = new html_selector;
            $sel->form = "formula_edit"; // ??? to review
            $sel->name = 'link_phrase';
            $sel->dummy_text = 'select a word where the formula should also be used';
            $sel->sql = sql_lst_usr("word", $this->user());
            if ($wrd->id > 0) {
                $sel->selected = $wrd->id;
            } else {
                $sel->selected = 0;
            }
            $result .= $sel->display();
        } else {
            if ($this->id > 0) {
                $url = $html->url(formula::class . api::UPDATE, $this->id, $back, '', 'add_link=1');
                $result .= '      ' . (new button('add new', $url))->add();
            }
        }
        $result .= '    </td>';
        $result .= '  </tr>';

        $result .= $html->dsp_tbl_end();

        log_debug("done");
        return $result;
    }

    // test and refresh the formula and show some sample values by returning the HTML code

    function dsp_test_and_samples(string $back = ''): string
    {
        log_debug($this->ref_text);
        $result = '<br>';
        $html = new html_base();

        $result .= $html->dsp_btn_text("Test", '/http/formula_test.php?id=' . $this->id . '&user=' . $this->user()->id() . '&back=' . $back);
        $result .= $html->dsp_btn_text("Refresh results", '/http/formula_test.php?id=' . $this->id . '&user=' . $this->user()->id() . '&back=' . $back . '&refresh=1');

        $result .= '<br><br>';

        // display some sample values
        log_debug("value list");
        $res_lst = new result_list($this->user());
        $res_lst->load($this);
        $sample_val = $res_lst->display($back);
        if (trim($sample_val) <> "") {
            if ($this->name_wrd != null) {
                $result .= $html->dsp_text_h3("Results for " . $this->name_wrd->dsp_obj()->dsp_link($back), "change_hist");
            }
            $result .= $sample_val;
        }

        log_debug("done");
        return $result;
    }

    // create the HTML code for the form to adjust a formula
    // $add is the number of new words to be linked
    // $wrd is the word that should be linked (used for a new formula)
    function dsp_edit($add, $wrd, $back): string
    {
        log_debug("" . $this->ref_text . " for " . $wrd->name() . ", back:" . $back . " and user " . $this->user()->name . ".");
        $result = '';
        $html = new html_base();

        $resolved_text = str_replace('"', '&quot;', $this->usr_text);

        // add new or change an existing formula
        if ($this->id <= 0) {
            $script = "formula_add";
            $result .= $html->dsp_text_h2('Add new formula for ' . $wrd->dsp_tbl_row() . ' ');
        } else {
            $script = "formula_edit";
            $result .= $html->dsp_text_h2('Formula "' . $this->name . '"');
        }
        $result .= '<div class="row">';

        // when changing a view show the fields only on the left side
        if ($this->id > 0) {
            $result .= '<div class="col-sm-7">';
        }

        // formula fields
        $result .= $html->dsp_form_start($script);
        $result .= $html->dsp_form_hidden("id", $this->id);
        $result .= $html->dsp_form_hidden("word", $wrd->id);
        $result .= $html->dsp_form_hidden("confirm", 1);
        if (trim($back) <> '') {
            $result .= $html->dsp_form_hidden("back", $back);
        }
        $result .= '<div class="form-row">';
        $result .= $html->dsp_form_fld("formula_name", $this->name, "Formula name:", "col-sm-8");
        $result .= $this->dsp_type_selector($script, "col-sm-4");
        $result .= '</div>';
        $result .= $html->dsp_form_fld("description", $this->description, "Description:", "col-sm-9");
        // predefined formulas like "this" or "next" should only be changed by an admin
        // TODO check if formula user or login user should be used
        if (!$this->is_special() or $this->user()->is_admin()) {
            $result .= $html->dsp_form_fld("formula_text", $resolved_text, "Expression:", "col-sm-10");
        }
        $result .= $html->dsp_form_fld_checkbox("need_all_val", $this->need_all_val, "calculate only if all values used in the formula exist");
        $result .= '<br><br>';
        $result .= $html->dsp_form_end('', $back);

        // list the assigned words
        if ($this->id > 0) {
            $result .= '</div>';

            // list all words linked to the formula and allow to unlink or add new words
            $comp_html = $this->dsp_used4words($add, $wrd, $back);
            // allow to test and refresh the formula and show some sample values
            $numbers_html = $this->dsp_test_and_samples($back);
            // display the user changes
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
                $link_html = 'No word have been added or removed yet.';
            }
            $result .= $html->dsp_link_hist_box('Usage', $comp_html,
                'Test', $numbers_html,
                'Changes', $hist_html,
                'Link changes', $link_html);
        }

        $result .= '</div>';   // of row
        $result .= '<br><br>'; // this a usually a small for, so the footer can be moved away

        log_debug("done");
        return $result;
    }

}
