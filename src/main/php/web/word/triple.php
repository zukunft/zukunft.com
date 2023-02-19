<?php

/*

    triple_dsp.php - a list function to create the HTML code to display a triple (two linked words or triples)
    --------------

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

namespace html;

use api\phrase_api;
use api\triple_api;
use cfg\phrase_type;
use phrase;
use phrase_list;
use triple;

class triple_dsp extends triple_api
{

    // the form names to change the word
    const FORM_ADD = 'triple_add';
    const FORM_EDIT = 'triple_edit';
    const FORM_DEL = 'triple_del';


    /*
     * base elements
     */

    /**
     * @returns string simply the word name, but later with mouse over that shows the description
     */
    function dsp(): string
    {
        return $this->name();
    }

    /**
     * display a triple with a link to the main page for the triple
     * @param string|null $back the back trace url for the undo functionality
     * @param string $style the CSS style that should be used
     * @returns string the html code
     */
    function dsp_link(?string $back = '', string $style = ''): string
    {
        $html = new html_base();
        $url = $html->url(api::TRIPLE, $this->id, $back, api::PAR_VIEW_TRIPLES);
        return $html->ref($url, $this->name(), $this->name(), $style);
    }


    /*
     * get and set
     */

    /**
     * @param string|null $code_id the code id of the phrase type
     */
    function set_type(?string $code_id): void
    {
        global $phrase_types;
        if ($code_id == null) {
            $this->set_type_id(null);
        } else {
            $this->set_type_id($phrase_types->id($code_id));
        }
    }

    /**
     * TODO use ENUM instead of string in php version 8.1
     * @return phrase_type|null the phrase type of this word
     */
    function type(): ?object
    {
        global $phrase_types;
        if ($this->type_id == null) {
            return null;
        } else {
            return $phrase_types->get_by_id($this->type_id);
        }
    }


    /*
     * select
     */

    /**
     * TODO review
     *
     * select a phrase based on a given context
     *
     * @param string $form_name
     * @param phrase $phr the context to select the phrases, which is until now just the phrase
     * @return string the html code to select a phrase
     */
    private function phrase_selector(string $form_name, string $label, phrase_api $phr): string
    {
        global $usr;
        $phr_lst = new phrase_list($usr);
        $phr_lst->load_by_phr($phr);
        return $phr_lst->dsp_obj()->selector($label, $form_name, $label);
    }


    /*
     * change forms
     */

    /**
     * display a form to adjust the link between too words or triples
     */
    function form_edit(string $back = ''): string
    {
        $html = new html_base();
        $result = ''; // reset the html code var

        // prepare to show the word link
        if ($this->id > 0) {
            $header = $html->text_h2('Change "' . $this->from()->name() . ' ' . $this->verb()->name() . ' ' . $this->to()->name() . '"');
            $hidden_fields = $html->form_hidden("id", $this->id);
            $hidden_fields .= $html->form_hidden("back", $back);
            $hidden_fields .= $html->form_hidden("confirm", '1');
            $detail_fields = $html->form_text("name", $this->name());
            $detail_fields .= $html->form_text("description", $this->description);
            $detail_fields .= $this->phrase_selector(self::FORM_EDIT, 'from', $this->from());
            /* TODO
            if (isset($this->verb)) {
                $result .= $this->verb->dsp_selector('forward', $form_name, "col-sm-4", $back);
            }
            */
            $detail_fields .= $this->phrase_selector(self::FORM_EDIT, 'to', $this->to());
            $detail_row = $html->fr($detail_fields) . '<br>';
            $result = $header . $html->form(self::FORM_EDIT, $hidden_fields . $detail_row);
        }

        return $result;
    }


    /*
     * buttons
     */

    /**
     * @returns string the html code to display a bottom to edit the word link in a table cell
     */
    function btn_edit(phrase_api $trp): string
    {

        $html = new html_base();
        $url = $html->url(api::PATH_FIXED . 'link' . api::UPDATE . api::EXT, $this->id, $trp->id);
        $btn = (new button("edit word link", $url))->edit();

        return $html->td($btn);
    }


    /*
     * casting
     */

    /**
     * @returns phrase_dsp the phrase display object base on this triple object
     */
    function phrase_dsp(): phrase_dsp
    {
        return new phrase_dsp($this->id(), $this->name());
    }

    function term(): term_dsp
    {
        return new term_dsp($this->id, $this->name, triple::class);
    }


    /*
     * type functions
     */

    /**
     * repeating of the backend functions in the frontend to enable filtering in the frontend and reduce the traffic
     * repeated in triple, because a triple can have it's own type
     * kind of repeated in phrase to use hierarchies
     *
     * @param string $type the ENUM string of the fixed type
     * @returns bool true if the word has the given type
     * TODO Switch to php 8.1 and real ENUM
     */
    function is_type(string $type): bool
    {
        $result = false;
        if ($this->type() != Null) {
            if ($this->type()->code_id == $type) {
                $result = true;
            }
        }
        return $result;
    }

    /**
     * @return bool true if the word has the type "scaling_percent" (e.g. "percent")
     */
    function is_percent(): bool
    {
        return $this->is_type(phrase_type::PERCENT);
    }

}
