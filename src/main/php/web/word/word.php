<?php

/*

    \web\word\word.php - the extension of the word API objects to create word base html code
    ------------------

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
     * @returns string simply the word name, but later with mouse over that shows the description
     */
    function dsp(): string
    {
        return $this->name;
    }

    /**
     * display a word with a link to the main page for the word
     * @param string $back the back trace url for the undo functionality
     * @param string $style the CSS style that should be used
     * @returns string the html code
     */
    function dsp_link(string $back = '', string $style = ''): string
    {
        $html = new html_base();
        $url = $html->url(api::VIEW, $this->id, $back, api::PAR_VIEW_WORDS);
        return $html->ref($url, $this->name(), $this->description, $style);
    }

    /**
     * @param string $back the back trace url for the undo functionality
     * @param string $style the CSS style that should be used
     * @returns string the word as a table cell
     */
    function td(string $back = '', string $style = '', int $intent = 0): string
    {
        $cell_text = '';
        while ($intent > 0) {
            $cell_text .= '&nbsp;';
            $intent = $intent - 1;
        }
        $cell_text .= $this->dsp_link($back, $style);
        return (new html_base)->td($cell_text);
    }

    /**
     * @param string $back the back trace url for the undo functionality
     * @param string $style the CSS style that should be used
     * @returns string the word as a table cell
     */
    function th(string $back = '', string $style = ''): string
    {
        return (new html_base)->th($this->dsp_link($back, $style));
    }

    /**
     * @return string the html code for a table row with the word
     */
    function tr(): string
    {
        return (new html_base())->tr($this->td());
    }

    /**
     * display a word as the view header
     * @param phrase_api|null $is_part_of the word group as a hint to the user
     *        e.g. City Zurich because in many cases if just the word Zurich is given the assumption is,
     *             that the Zurich (City) is the phrase to select
     * @returns string the HTML code to display a word
     */
    function header(?phrase_api $is_part_of = null): string
    {
        $html = new html_base();

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
            if ($is_part_of != null) {
                if ($is_part_of->name <> '' and $is_part_of->name <> 'not set') {
                    $url = $html->url(api::VIEW, $is_part_of->id, '', api::PAR_VIEW_WORDS);
                    $title .= ' (' . $html->ref($url, $is_part_of->name) . ')';
                }
            }
            $url = $html->url(api::WORD . api::UPDATE, $this->id, $this->id);
            $title .= $html->ref($url, $html->span($this->name(), api::STYLE_GLYPH), 'Rename word');
            $result .= dsp_text_h1($title);
        }

        return $result;
    }


    /**
     * simply to display a single word and allow to delete it
     * used by value->dsp_edit
     */
    function dsp_del(): string
    {
        $name = $this->td('', '', 0);
        $btn = $this->td($this->btn_del());
        return (new html_base())->tr($name . $btn);
    }

    // allow the user to unlink a word
    function dsp_unlink($link_id): string
    {
        $result = '    <td>' . "\n";
        $result .= \html\btn_del("unlink word", "/http/link_del.php?id=" . $link_id . "&back=" . $this->id);
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

    /*
     * buttons
     */

    /**
     * @returns string the html code to display a bottom to edit the word link in a table cell
     */
    function btn_del(): string
    {
        $url = (new html_base())->url(api::WORD . api::REMOVE, $this->id, $this->id);
        return (new button("Delete word", $url))->del();
    }

}
