<?php

/*

    /web/ref/source.php - the extension of the source API objects to create source base html code
    -------------------

    This file is part of the frontend of zukunft.com - calc with sources

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

use api\source_api;
use api\phrase_api;
use cfg\phrase_type;
use source;

class source_dsp extends source_api
{

    /**
     * @returns string simply the source name, but later with mouse over that shows the description
     */
    function dsp(): string
    {
        return $this->name;
    }

    // return the html code to display a source name with the link
    function name_linked($wrd, $back): string
    {
        return '<a href="/http/source_edit.php?id=' . $this->id . '&word=' . $wrd->id . '&back=' . $back . '">' . $this->name . '</a>';
    }

    /*
     * TODO check if this is still needed (at least use the idea)
     *
    // returns the html code for a source: this is the main function of this lib
    // source_id is used to force the display to a set form; e.g. display the sectors of a company instead of the balance sheet
    // source_type_id is used to .... remove???
    // word_id - id of the starting word to display; can be a single word, a comma separated list of word ids, a word group or a word triple
    function display($wrd): string
    {
        log_debug('source->display "' . $wrd->name() . '" with the view ' . $this->dsp_id() . ' (type ' . $this->type_id . ')  for user "' . $this->user()->name . '"');
        $result = '';

        if ($this->id <= 0) {
            log_err("The source id must be loaded to display it.", "source->display");
        } else {
            // display always the source name in the top right corner and allow the user to edit the source
            $result .= $this->dsp_type_open();
            $result .= $this->dsp_navbar($wrd->id);
            $result .= $this->dsp_entries($wrd);
            $result .= $this->dsp_type_close();
        }
        log_debug('source->display ... done');

        return $result;
    }
    */

    // display a selector for the value source
    function dsp_select($form_name, $back): string
    {
        log_debug($this->dsp_id());
        $result = ''; // reset the html code var

        // for new values assume the last source used, but not for existing values to enable only changing the value, but not setting the source
        if ($this->id <= 0 and $form_name == "value_add") {
            $this->id = $this->user()->source_id;
        }

        log_debug("source id used (" . $this->id . ")");
        $sel = new html_selector;
        $sel->form = $form_name;
        $sel->name = "source";
        $sel->sql = sql_lst_usr("source", $this->user());
        $sel->selected = $this->id;
        $sel->dummy_text = 'please define the source';
        $result .= '      taken from ' . $sel->display() . ' ';
        $result .= '    <td>' . \html\btn_edit("Rename " . $this->name, '/http/source_edit.php?id=' . $this->id . '&back=' . $back) . '</td>';
        $result .= '    <td>' . \html\btn_add("Add new source", '/http/source_add.php?back=' . $back) . '</td>';
        return $result;
    }

    // display a selector for the source type
    private function dsp_select_type($form_name, $back): string
    {
        log_debug("source->dsp_select_type (" . $this->id . "," . $form_name . ",b" . $back . " and user " . $this->user()->name . ")");

        $result = ''; // reset the html code var

        $sel = new html_selector;
        $sel->form = $form_name;
        $sel->name = "source_type";
        $sel->sql = sql_lst("source_type");
        $sel->selected = $this->type_id;
        $sel->dummy_text = 'please select the source type';
        $result .= $sel->display();
        return $result;
    }

    // display a html view to change the source name and url
    function dsp_edit(string $back = ''): string
    {
        log_debug('source->dsp_edit ' . $this->dsp_id() . ' by user ' . $this->user()->name);
        $result = '';

        if ($this->id <= 0) {
            $script = "source_add";
            $result .= dsp_text_h2("Add source");
        } else {
            $script = "source_edit";
            $result .= dsp_text_h2('Edit source "' . $this->name . '"');
        }
        $result .= dsp_form_start($script);
        //$result .= dsp_tbl_start();
        $result .= dsp_form_hidden("id", $this->id);
        $result .= dsp_form_hidden("back", $back);
        $result .= dsp_form_hidden("confirm", 1);
        $result .= dsp_form_fld("name", $this->name, "Source name:");
        $result .= '<tr><td>type   </td><td>' . $this->dsp_select_type($script, $back) . '</td></tr>';
        $result .= dsp_form_fld("url", $this->url, "URL:");
        $result .= dsp_form_fld("comment", $this->description, "Comment:");
        //$result .= dsp_tbl_end ();
        $result .= dsp_form_end('', $back);

        log_debug('done');
        return $result;
    }

}
