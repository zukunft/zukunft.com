<?php

/*

    word_dsp.php - the extension of the word API objects to create word base html code
    ------------

    This file is part of the frontend of zukunft.com - calc with words

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

namespace html;

use api\word_api;
use api\phrase_api;

class word_dsp extends word_api
{

    // default view settings
    const TIME_MIN_COLS = 3; // minimum number of same time type word to display in a table e.g. if at least 3 years exist use a table to display
    const TIME_MAX_COLS = 10; // maximum number of same time type word to display in a table e.g. if more the 10 years exist, by default show only the lst 10 years

    /**
     * display a word as the view header
     * @param phrase_api|null $is_part_of the word group as a hint to the user
     *        e.g. City Zurich because in many cases if just the word Zurich is given the assumption is,
     *             that the Zurich (City) is the phrase to select
     * @returns string the HTML code to display a word
     */
    function dsp_header(?phrase_api $is_part_of = null): string
    {
        $result = '';

        if ($this->id <= 0) {
            $result .= 'no word selected';
        } else {
            // load the word parameters if not yet done
            if ($this->name == "") {
                log_err('Name for word with id ' . $this->id . ' is empty', 'word_dsp->dsp_header');
            }

            //$default_view_id = cl(DBL_VIEW_WORD);
            $title = '';
            //$title .= '<a href="/http/view.php?words='.$this->id.'&view='.$default_view_id.'" title="'.$this->description.'">'.$this->name.'</a>';
            if ($is_part_of != null) {
                if ($is_part_of->name <> '' and $is_part_of->name <> 'not set') {
                    $title .= ' (<a href="/http/view.php?words=' . $is_part_of->id . '">' . $is_part_of->name . '</a>)';
                }
            }
            /*      $title .= '  '.'<a href="/http/word_edit.php?id='.$this->id.'&back='.$this->id.'" title="Rename word"><img src="'.ZUH_IMG_EDIT.'" alt="Rename word" style="height: 0.65em;"></a>'; */
            $title .= ' <a href="/http/word_edit.php?id=' . $this->id . '&back=' . $this->id . '" title="Rename word"><span class="glyphicon glyphicon-pencil">';
            $title .= $this->name;
            $title .= '</span></a>';
            $result .= dsp_text_h1($title);
        }

        return $result;
    }


    /**
     * display a word with a link to the main page for the word
     */
    function dsp_link(): string
    {
        return '<a href="/http/view.php?words=' . $this->id . '" title="' . $this->description . '">' . $this->name . '</a>';
    }

    /**
     * similar to dsp_link, but using s CSS style; used by ??? to ???
     */
    function dsp_link_style($style): string
    {
        return '<a href="/http/view.php?words=' . $this->id . '" title="' . $this->description . '" class="' . $style . '">' . $this->name . '</a>';
    }

    /**
     * simply to display a single word in a table as a header
     */
    function dsp_tbl_head_right(): string
    {
        log_debug('word_dsp->dsp_tbl_head_right');
        $result = '    <th class="right_ref">' . "\n";
        $result .= '      ' . $this->dsp_link() . "\n";
        $result .= '    </th>' . "\n";
        return $result;
    }

    /**
     * simply to display a single word in a table cell
     */
    function dsp_tbl_cell(int $intent): string
    {
        log_debug('word_dsp->dsp_tbl_cell');
        $result = '    <td>' . "\n";
        while ($intent > 0) {
            $result .= '&nbsp;';
            $intent = $intent - 1;
        }
        $result .= '      ' . $this->dsp_link() . "\n";
        $result .= '    </td>' . "\n";
        return $result;
    }

    /**
     * simply to display a single word in a table
     * rename and join to dsp_tbl_cell to have a more specific name
     */
    function dsp_tbl(int $intent): string
    {
        log_debug('word_dsp->dsp_tbl');
        $result = '    <td>' . "\n";
        while ($intent > 0) {
            $result .= '&nbsp;';
            $intent = $intent - 1;
        }
        $result .= '      ' . $this->dsp_link() . "\n";
        $result .= '    </td>' . "\n";
        return $result;
    }

    function dsp_tbl_row(): string
    {
        $result = '  <tr>' . "\n";
        $result .= $this->dsp_tbl(0);
        $result .= '  </tr>' . "\n";
        return $result;
    }

    /**
     * simply to display a single word and allow to delete it
     * used by value->dsp_edit
     */
    function dsp_name_del($del_call): string
    {
        log_debug('word_dsp->dsp_name_del');
        $result = '  <tr>' . "\n";
        $result .= $this->dsp_tbl_cell(0);
        $result .= '    <td>' . "\n";
        $result .= '      ' . btn_del("delete", $del_call) . '<br> ';
        $result .= '    </td>' . "\n";
        $result .= '  </tr>' . "\n";
        return $result;
    }

    // allow the user to unlink a word
    function dsp_unlink($link_id): string
    {
        log_debug('word_dsp->dsp_unlink(' . $link_id . ')');
        $result = '    <td>' . "\n";
        $result .= btn_del("unlink word", "/http/link_del.php?id=" . $link_id . "&back=" . $this->id);
        $result .= '    </td>' . "\n";

        return $result;
    }

    // returns the html code to select a word link type
    // database link must be open
    private function selector_type($id, $form): string
    {
        log_debug('word_dsp->selector_type ... word id ' . $id);
        $result = '';

        if ($id <= 0) {
            $id = DEFAULT_WORD_TYPE_ID;
        }

        $sel = new html_selector;
        $sel->form = $form;
        $sel->name = 'type';
        $sel->sql = sql_lst("word_type");
        $sel->selected = $id;
        $sel->dummy_text = '';
        $result .= $sel->display();

        return $result;
    }

    /**
     * HTML code to edit all word fields
     * @param string $dsp_graph the html code of the related phrases
     * @param string $dsp_log the html code of the change log
     * @param string $dsp_frm the html code of the linked formulas
     * @param string $dsp_type the html code of the type selector formulas
     * @param string $back the html code to be opened in case of a back action
     * @return string the html code to display the edit page
     */
    function dsp_edit(string $dsp_graph, string $dsp_log, string $dsp_frm, string $dsp_type, string $back = ''): string
    {
        log_debug('word_dsp->dsp_edit ' . $this->dsp_id());
        $result = '';

        if ($this->id > 0) {
            $form = "word_edit";
            $result .= dsp_text_h2('Change "' . $this->name . '"');
            $result .= dsp_form_start($form);
            $result .= dsp_form_hidden("id", $this->id);
            $result .= dsp_form_hidden("back", $back);
            $result .= dsp_form_hidden("confirm", '1');
            $result .= '<div class="form-row">';
            $result .= $dsp_frm;
            $result .= dsp_form_text("plural", $this->plural, "Plural:", "col-sm-4");
            $result .= $dsp_type;
            $result .= '</div>';
            $result .= '<br>';
            $result .= dsp_form_text("description", $this->description, "Description:");
            $result .= dsp_form_end('', $back);
            $result .= '<br>';
            $result .= $dsp_graph;
        }

        $result .= $dsp_log;

        log_debug('word_dsp->dsp_edit -> done');
        return $result;
    }

    /**
     * return best possible identification for this object mainly used for debugging
     */
    function dsp_id(): string
    {
        $result = '';
        if ($this->name <> '') {
            $result .= '"' . $this->name . '"';
            if ($this->id > 0) {
                $result .= ' (' . $this->id . ')';
            }
        } else {
            $result .= $this->id;
        }
        return $result;
    }

    function name(): string
    {
        return $this->name;
    }

}
