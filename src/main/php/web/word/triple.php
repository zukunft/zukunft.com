<?php

/*

    web/word/triple.php - a list function to create the HTML code to display a triple (two linked words or triples)
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

namespace html\word;

include_once WEB_SANDBOX_PATH . 'sandbox_typed.php';

use cfg\phrase_type;
use html\api;
use html\button;
use html\html_base;
use html\html_selector;
use html\msg;
use html\phrase\phrase_list as phrase_list_dsp;
use html\word\word as word_dsp;
use html\phrase\phrase as phrase_dsp;
use html\sandbox\sandbox_typed;
use html\phrase\term as term_dsp;
use html\verb\verb as verb_dsp;

class triple extends sandbox_typed
{

    // the form names to change the word
    const FORM_ADD = 'triple_add';
    const FORM_EDIT = 'triple_edit';
    const FORM_DEL = 'triple_del';

    // the json field names in the api json message which is supposed to be the same as the var $id
    const FLD_FROM = 'from';
    const FLD_VERB = 'parent';
    const FLD_TO = 'to';


    /*
     * object vars
     */

    // the triple components
    private phrase_dsp $from;
    private ?verb_dsp $verb = null;
    private phrase_dsp $to;


    /*
     * set and get
     */

    /**
     * set the vars of this object bases on the api json array
     * @param array $json_array an api json message
     * @return void
     */
    function set_from_json_array(array $json_array): void
    {
        parent::set_from_json_array($json_array);
        if (array_key_exists(self::FLD_FROM, $json_array)) {
            $this->set_from($json_array[self::FLD_FROM]);
        } else {
            $this->set_from(new phrase_dsp());
        }
        if (array_key_exists(self::FLD_VERB, $json_array)) {
            $this->set_verb($json_array[self::FLD_VERB]);
        } else {
            $this->set_verb(new verb_dsp());
        }
        if (array_key_exists(self::FLD_TO, $json_array)) {
            $this->set_to($json_array[self::FLD_TO]);
        } else {
            $this->set_to(new phrase_dsp());
        }
    }

    function set(string $from, string $verb, string $to): void
    {
        $this->set_from((new word_dsp(0, $from))->phrase());
        $this->set_verb(new verb_dsp(0, $verb));
        $this->set_to((new word_dsp(0, $to))->phrase());
    }

    function set_from(phrase_dsp $from): void
    {
        $this->from = $from;
    }

    function set_verb(verb_dsp $vrb): void
    {
        $this->verb = $vrb;
    }

    function set_to(phrase_dsp $to): void
    {
        $this->to = $to;
    }

    function from(): phrase_dsp
    {
        return $this->from;
    }

    function verb(): verb_dsp
    {
        return $this->verb;
    }

    function to(): phrase_dsp
    {
        return $this->to;
    }

    /**
     * @param string|null $code_id the code id of the phrase type
     */
    function set_type(?string $code_id): void
    {
        global $phrase_types;
        if ($code_id == null) {
            $this->set_type_id();
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
        if ($this->type_id() == null) {
            return null;
        } else {
            return $phrase_types->get($this->type_id());
        }
    }


    /*
     * base elements
     */

    /**
     * @returns string simply the word name, but later with mouse over that shows the description
     */
    function display(): string
    {
        return $this->name();
    }

    /**
     * display a triple with a link to the main page for the triple
     * @param string|null $back the back trace url for the undo functionality
     * @param string $style the CSS style that should be used
     * @returns string the html code
     */
    function display_linked(?string $back = '', string $style = ''): string
    {
        $html = new html_base();
        $url = $html->url(api::TRIPLE, $this->id, $back, api::PAR_VIEW_TRIPLES);
        return $html->ref($url, $this->name(), $this->name(), $style);
    }


    /*
     * select
     */

    /**
     * TODO review
     *
     * select a phrase based on a given context
     *
     * @param string $name the unique name inside the form for this selector
     * @param string $form_name the name of the html form
     * @param string $label the text show to the user
     * @param string $col_class the formatting code to adjust the formatting
     * @param int $selected the id of the preselected phrase
     * @param string $pattern the pattern to filter the phrases
     * @param phrase_dsp|null $phr the context to select the phrases, which is until now just the phrase
     * @return string the html code to select a phrase
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
        return $phr_lst->selector($name, $form_name, $label, html_base::COL_SM_4, $selected, html_selector::TYPE_DATALIST);
    }

    /**
     * @param string $form_name the name of the html form
     * @return string the html code to select a phrase
     */
    protected function verb_selector(string $name, string $form_name): string
    {
        global $html_verbs;
        if ($this->verb != null) {
            $id = $this->verb()->id();
        } else {
            $id = 0;
        }
        return $html_verbs->selector($form_name, $id, 'verb', html_base::COL_SM_4, 'verb:');
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
            $detail_fields .= 'from: ' . $this->phrase_selector(
                'from', self::FORM_EDIT, 'from:', '', $this->from()->id(), '', $this->from());
            /* TODO
            if (isset($this->verb)) {
                $result .= $this->verb->dsp_selector('forward', $form_name, html_base::COL_SM_4, $back);
            }
            */
            $detail_fields .= 'to: ' . $this->phrase_selector(
                'to', self::FORM_EDIT, 'to:', '', $this->to()->id(), '', $this->to());
            $detail_row = $html->fr($detail_fields) . '<br>';
            $result = $header . $html->form(self::FORM_EDIT, $hidden_fields . $detail_row);
        }

        return $result;
    }


    /*
     * buttons
     */

    /**
     * @returns string the html code to display a bottom to edit this triple in a table cell
     */
    function btn_add(string $back = ''): string
    {

        $html = new html_base();
        $url = $html->url(api::PATH_FIXED . 'link' . api::CREATE . api::EXT, $this->id, $this->id);
        $btn = (new button($url. $back))->edit(msg::TRIPLE_ADD);

        return $html->td($btn);
    }

    /**
     * @returns string the html code to display a bottom to edit this triple in a table cell
     */
    function btn_edit(phrase_dsp $trp, string $back = ''): string
    {

        $html = new html_base();
        $url = $html->url(api::PATH_FIXED . 'link' . api::UPDATE . api::EXT, $this->id, $trp->id());
        $btn = (new button($url. $back))->edit(msg::TRIPLE_EDIT);

        return $html->td($btn);
    }


    /*
     * cast
     */

    /**
     * @returns phrase_dsp the phrase display object base on this triple object
     */
    function phrase(): phrase_dsp
    {
        $phr = new phrase_dsp();
        $phr->set_obj($this);
        return $phr;
    }

    function term(): term_dsp
    {
        $trm = new term_dsp();
        $trm->set_obj($this);
        return $trm;
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


    /*
     * info
     */

    /**
     * @return bool true if the word has the type "scaling_percent" (e.g. "percent")
     */
    function is_percent(): bool
    {
        return $this->is_type(phrase_type::PERCENT);
    }

}
