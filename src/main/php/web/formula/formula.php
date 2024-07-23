<?php

/*

    /web/formula/formula.php - the display extension of the api formula object
    -----------------------

    to creat the HTML code to display a formula


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

namespace html\formula;

include_once WEB_SANDBOX_PATH . 'sandbox_typed.php';

use api\api;
use cfg\db\sql_db;
use cfg\expression;
use cfg\formula_link_list;
use cfg\phrase;
use cfg\phrase_list;
use cfg\result\result;
use cfg\result\result_list;
use cfg\term_list;
use controller\controller;
use html\rest_ctrl as api_dsp;
use html\button;
use html\html_base;
use html\html_selector;
use html\log\user_log_display;
use html\message;
use html\phrase\phrase as phrase_dsp;
use html\phrase\phrase_list as phrase_list_dsp;
use html\phrase\term as term_dsp;
use html\result\result as result_dsp;
use html\sandbox\sandbox_typed;
use html\system\messages;
use html\word\word as word_dsp;
use shared\library;

class formula extends sandbox_typed
{

    /*
     * object vars
     */

    // the formula expression as shown to the user
    private string $usr_text = '';
    private string $ref_text = '';
    public ?bool $need_all_val = false;    // calculate and save the result only if all used values are not null
    public ?phrase_dsp $name_wrd = null;         // the triple object for the formula name:


    /*
     * set and get
     */

    /**
     * repeat here the sandbox object function to force to include all formula object fields
     * @param array $json_array an api single object json message
     * @return void
     */
    function set_obj_from_json_array(array $json_array): void
    {
        $wrd = new formula();
        $wrd->set_from_json_array($json_array);
    }

    /**
     * set the vars this formula bases on the api json array
     * public because it is reused e.g. by the phrase group display object
     * @param array $json_array an api json message
     * @return void
     */
    function set_from_json_array(array $json_array): void
    {
        parent::set_from_json_array($json_array);
        if (array_key_exists(api::FLD_USER_TEXT, $json_array)) {
            $this->set_usr_text($json_array[api::FLD_USER_TEXT]);
        } else {
            $this->set_usr_text(null);
        }
        if (array_key_exists(api::FLD_REF_TEXT, $json_array)) {
            $this->set_ref_text($json_array[api::FLD_REF_TEXT]);
        } else {
            $this->set_ref_text(null);
        }
        if (array_key_exists(api::FLD_NEED_ALL_VAL, $json_array)) {
            $this->need_all_val = $json_array[api::FLD_NEED_ALL_VAL];
        } else {
            $this->need_all_val = false;
        }
        if (array_key_exists(api::FLD_FORMULA_NAME_PHRASE, $json_array)) {
            $this->name_wrd = new phrase_dsp($json_array[api::FLD_FORMULA_NAME_PHRASE]);
        } else {
            $this->name_wrd = null;
        }
    }

    function set_usr_text(?string $usr_text): void
    {
        if ($usr_text != null) {
            $this->usr_text = $usr_text;
        }
    }

    function usr_text(): string
    {
        return $this->usr_text;
    }

    function set_ref_text(?string $ref_text): void
    {
        if ($ref_text != null) {
            $this->ref_text = $ref_text;
        }
    }

    function ref_text(): string
    {
        return $this->ref_text;
    }


    /*
     * interface
     */

    /**
     * @return array the json message array to send the updated data to the backend
     * an array is used (instead of a string) to enable combinations of api_array() calls
     */
    function api_array(): array
    {
        $vars = parent::api_array();

        $vars[api::FLD_USER_TEXT] = $this->usr_text();
        return array_filter($vars, fn($value) => !is_null($value) && $value !== '');
    }


    /*
     * cast
     */

    function term(): term_dsp
    {
        $trm = new term_dsp();
        $trm->set_obj($this);
        return $trm;
    }


    /*
     * display
     */

    /**
     * display the formula name with the tooltip
     * @returns string the html code
     */
    function display(): string
    {
        return $this->name();
    }

    /**
     * display the formula name with a link to the main page for the formula
     * @param string|null $back the back trace url for the undo functionality
     * @param string $style the CSS style that should be used
     * @returns string the html code
     */
    function display_linked(?string $back = '', string $style = ''): string
    {
        $html = new html_base();
        $url = $html->url(api_dsp::FORMULA, $this->id, $back, api_dsp::PAR_VIEW_FORMULAS);
        return $html->ref($url, $this->name(), $this->name(), $style);
    }

    /**
     * display the formula name with a link to change the formula
     * @param string|null $back the back trace url for the undo functionality
     * @returns string the html code
     */
    function edit_link(?string $back = ''): string
    {
        $url = $this->obj_url(controller::MC_FORMULA_EDIT, $back);
        return (new html_base())->ref($url, $this->name(), $this->name());
    }

    /**
     * create the HTML code for a button to create a new formula
     * @param string $back the stack trace for the undo functionality
     * @return string html code to change to formula
     */
    function btn_add(string $back = ''): string
    {
        $url = $this->obj_url(controller::MC_FORMULA_ADD);
        return (new button($url, $back))->add(messages::FORMULA_ADD, $this->name);
    }

    /**
     * create the HTML code for a button to change the formula
     * @param string $back the stack trace for the undo functionality
     * @return string html code to change to formula
     */
    function btn_edit(string $back = ''): string
    {
        $url = $this->obj_url(controller::MC_FORMULA_EDIT);
        return (new button($url, $back))->edit(messages::FORMULA_EDIT, messages::FOR . $this->name);
    }

    /**
     * create the HTML code for a button to delete or exclude this formula
     * @param string $back the stack trace for the undo functionality
     * @return string html code to change to formula
     */
    function btn_del(string $back = ''): string
    {
        $url = $this->obj_url(controller::MC_FORMULA_DEL);
        return (new button($url, $back))->del(messages::FORMULA_DEL, messages::OF . $this->name);
    }


    /*
     * internal
     */

    /**
     * @param string $form_name the name of the html form
     * @return string the html code to select the formula type
     */
    function dsp_type_selector(string $form_name): string
    {
        global $html_formula_types;
        return $html_formula_types->selector($form_name);
    }


    /*
     * to review
     */

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

    // display the history of a formula
    private function dsp_hist_log($page, $size, $call, $back): user_log_display
    {
        global $usr;
        $log_dsp = new user_log_display($usr);
        $log_dsp->id = $this->id;
        $log_dsp->type = formula::class;
        $log_dsp->page = $page;
        $log_dsp->size = $size;
        $log_dsp->call = $call;
        $log_dsp->back = $back;
        return $log_dsp;
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

    // create the HTML code for the form to adjust a formula
    // $add is the number of new words to be linked
    // $wrd is the word that should be linked (used for a new formula)
    function dsp_edit($add, $wrd, $back): string
    {
        global $usr;

        log_debug(" for " . $wrd->name() . ", back:" . $back);
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
            $result .= '<div class="' . html_base::COL_SM_7 . '">';
        }

        // formula fields
        $result .= $html->dsp_form_start($script);
        $result .= $html->dsp_form_hidden("id", $this->id);
        $result .= $html->dsp_form_hidden("word", $wrd->id());
        $result .= $html->dsp_form_hidden("confirm", 1);
        if (trim($back) <> '') {
            $result .= $html->dsp_form_hidden("back", $back);
        }
        $result .= '<div class="form-row">';
        $result .= $html->dsp_form_fld("formula_name", $this->name, "Formula name:", html_base::COL_SM_8);
        $result .= $this->dsp_type_selector($script);
        $result .= '</div>';
        $result .= $html->dsp_form_fld("description", $this->description, "Description:", html_base::COL_SM_8);
        // predefined formulas like "this" or "next" should only be changed by an admin
        // TODO check if formula user or login user should be used
        if (!$this->is_special() or $usr->is_admin()) {
            $result .= $html->dsp_form_fld("formula_text", $resolved_text, "Expression:", html_base::COL_SM_12);
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
            $changes = $this->dsp_hist(0, sql_db::ROW_LIMIT, '', $back);
            if (trim($changes) <> "") {
                $hist_html = $changes;
            } else {
                $hist_html = 'Nothing changed yet.';
            }
            $changes = $this->dsp_hist_links(0, sql_db::ROW_LIMIT, '', $back);
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

    /**
     * return the true if the formula has a special type and the result is a kind of hardcoded
     * e.g. "this" or "next" where the value of this or the following time word is returned
     */
    function is_special(): bool
    {
        $result = false;
        if ($this->type_id() != null) {
            $result = true;
            log_debug($this->dsp_id());
        }
        return $result;
    }

    // list all words linked to the formula and allow to unlink or add new words
    function dsp_used4words($add, $wrd, $back): string
    {
        global $usr;
        log_debug($this->ref_text . " for " . $wrd->name() . ",back:" . $back);
        $result = '';

        $html = new html_base();

        $phr_lst = $this->assign_phr_ulst_direct();
        log_debug("words linked loaded");

        // list all linked words
        $result .= $html->dsp_tbl_start_half();
        if ($phr_lst != null) {
            foreach ($phr_lst->lst() as $phr_linked) {
                $result .= '  <tr>' . "\n";
                $result .= $phr_linked->dsp_tbl(0);
                $result .= $this->dsp_unlink_phr($phr_linked->id(), $back);
                $result .= '  </tr>' . "\n";
            }
        }

        // give the user the possibility to add a similar word
        log_debug("user");
        $result .= '  <tr>';
        $result .= '    <td>';
        if ($add == 1 or $wrd->id() > 0) {
            //$sel->dummy_text = 'select a word where the formula should also be used';
            if ($wrd->id() > 0) {
                $selected = $wrd->id();
            } else {
                $selected = 0;
            }
            $result .= $this->phrase_selector('link_phrase', "formula_edit",
                    '', $selected) . ' ';
        } else {
            if ($this->id > 0) {
                $url = $this->obj_url(controller::MC_FORMULA_ADD);
                // TODO check if 'add_link=1' is needed
                $result .= (new button($url, $back))->add(messages::FORMULA_ADD);
            }
        }
        $result .= '    </td>';
        $result .= '  </tr>';

        $result .= $html->dsp_tbl_end();

        log_debug("done");
        return $result;
    }

    /**
     * HTML code of a phrase selector
     * @param string $name the unique name inside the form for this selector
     * @param string $form_name the name of the html form
     * @param string $label the text show to the user
     * @param string $col_class the formatting code to adjust the formatting
     * @param int $selected the id of the preselected phrase
     * @param string $pattern the pattern to filter the phrases
     * @param phrase_dsp|null $phr phrase to preselect the phrases e.g. use Country to narrow the selection
     * @return string with the HTML code to show the phrase selector
     */
    protected function phrase_selector(
        string $name,
        string $form_name,
        string $label = '',
        string $col_class = '',
        int $selected = 0,
        string $pattern = '',
        ?phrase_dsp $phr = null
    ): string
    {
        $phr_lst = new phrase_list_dsp();
        $phr_lst->load_like($pattern);
        return $phr_lst->selector($name, $form_name, $label, '', $selected, html_selector::TYPE_DATALIST);
    }

    // test and refresh the formula and show some sample values by returning the HTML code

    function dsp_test_and_samples(string $back = ''): string
    {
        global $usr;
        log_debug($this->ref_text);
        $result = '<br>';
        $html = new html_base();

        $result .= $html->dsp_btn_text("Test", '/http/formula_test.php?id=' . $this->id . '&user=' . $usr->id() . '&back=' . $back);
        $result .= $html->dsp_btn_text("Refresh results", '/http/formula_test.php?id=' . $this->id . '&user=' . $usr->id() . '&back=' . $back . '&refresh=1');

        $result .= '<br><br>';

        // display some sample values
        log_debug("value list");
        $res_lst = new result_list($usr);
        $res_lst->load_by_obj($this);
        $sample_val = $res_lst->display($back);
        if (trim($sample_val) <> "") {
            if ($this->name_wrd != null) {
                $name_wrd_dsp = new word_dsp($this->name_wrd->api_json());
                $result .= $html->dsp_text_h3("Results for " . $name_wrd_dsp->display_linked($back), "change_hist");
            }
            $result .= $sample_val;
        }

        log_debug("done");
        return $result;
    }


    /**
     * @param term_list|null $trm_lst a list of preloaded terms that should be used for the transformation
     * @return expression the formula expression as an expression element
     */
    function expression(?term_list $trm_lst = null): expression
    {
        global $usr;
        $exp = new expression($usr);
        $exp->set_ref_text($this->ref_text(), $trm_lst);
        $exp->set_user_text($this->usr_text(), $trm_lst);
        log_debug('->expression ' . $exp->ref_text() . ' for user ' . $exp->usr->name);
        return $exp;
    }

    /**
     * the user specific list of a phrases assigned to a formula
     */
    function assign_phr_ulst_direct(): ?phrase_list
    {
        return $this->assign_phr_glst_direct(true);
    }

    /**
     * lists of all words directly assigned to a formula and where the formula should be used
     */
    function assign_phr_glst_direct($sbx): ?phrase_list
    {
        global $usr;
        $phr_lst = null;
        $lib = new library();

        if ($this->id() > 0 and $usr != null) {
            log_debug('for formula ' . $this->dsp_id() . ' and user "' . $usr->name . '"');
            $frm_lnk_lst = new formula_link_list($usr);
            $frm_lnk_lst->load_by_frm_id($this->id);
            $phr_ids = $frm_lnk_lst->phrase_ids($sbx);

            if (count($phr_ids->lst) > 0) {
                $phr_lst = new phrase_list($usr);
                $phr_lst->load_names_by_ids($phr_ids);
                log_debug("number of words " . $lib->dsp_count($phr_lst->lst()));
            }
        } else {
            log_err("The user id must be set to list the formula links.", "formula->assign_phr_glst_direct");
        }

        return $phr_lst;
    }

    // allow the user to unlink a word
    function dsp_unlink_phr($phr_id, $back): string
    {
        log_debug($phr_id);
        $result = '    <td>' . "\n";
        $url = \html\rest_ctrl::PATH_FIXED . self::class . api_dsp::UPDATE . api_dsp::EXT . '?id=' . $this->id . '&unlink_phrase=' . $phr_id . '&back=' . $back;
        $result .= (new button($url, $back))->del(messages::FORMULA_UNLINK);
        $result .= '    </td>' . "\n";
        return $result;
    }

    /*
     * to review
     */

    /**
     * display the most interesting formula result for one word
     * TODO define the criteria and review the result loading
     */
    function dsp_result(phrase $phr, $back): string
    {
        log_debug('for "' . $phr->name() . '" and formula ' . $this->dsp_id());
        $res = new result($this->user());
        $res->load_by_formula_and_group_list($this, $phr->groups());
        $dsp_res = new result_dsp($res->api_json());
        return $dsp_res->display($back);
    }


}
