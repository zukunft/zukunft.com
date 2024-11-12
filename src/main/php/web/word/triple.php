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

include_once SANDBOX_PATH . 'sandbox_typed.php';
include_once SHARED_TYPES_PATH . 'phrase_type.php';

use shared\api;
use cfg\phrase_type;
use html\rest_ctrl;
use html\button;
use html\html_base;
use html\html_selector;
use html\phrase\phrase_list as phrase_list_dsp;
use html\system\messages;
use html\user\user_message;
use html\word\word as word_dsp;
use html\word\triple as triple_dsp;
use html\phrase\phrase as phrase_dsp;
use html\sandbox\sandbox_typed;
use html\phrase\term as term_dsp;
use html\verb\verb as verb_dsp;
use shared\api AS api_shared;
use shared\types\phrase_type AS phrase_type_shared;

class triple extends sandbox_typed
{

    // the form names to change the word
    const FORM_ADD = 'triple_add';
    const FORM_EDIT = 'triple_edit';
    const FORM_DEL = 'triple_del';


    /*
     * object vars
     */

    // the triple components
    // they can be null to allow front end error messages to the user
    private ?phrase_dsp $from = null;
    private ?verb_dsp $verb = null;
    private ?phrase_dsp $to = null;
    private ?string $plural = null;


    /*
     * set and get
     */

    /**
     * set the vars of this object bases on the api json array
     * @param array $json_array an api json message
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function set_from_json_array(array $json_array): user_message
    {
        $usr_msg = parent::set_from_json_array($json_array);
        if (array_key_exists(api::FLD_FROM, $json_array)) {
            $this->set_from_by_id($json_array[api::FLD_FROM]);
        } else {
            $this->set_from(new phrase_dsp());
        }
        if (array_key_exists(api::FLD_VERB, $json_array)) {
            $this->set_verb_by_id($json_array[api::FLD_VERB]);
        } else {
            $this->set_verb(new verb_dsp());
        }
        if (array_key_exists(api::FLD_TO, $json_array)) {
            $this->set_to_by_id($json_array[api::FLD_TO]);
        } else {
            $this->set_to(new phrase_dsp());
        }
        return $usr_msg;
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

    function set_from_by_id(int $id): void
    {
        $this->from = $this->set_phrase_by_id($id);
    }

    function set_verb(verb_dsp $vrb): void
    {
        $this->verb = $vrb;
    }

    function set_verb_by_id(int $id): void
    {
        $vrb = new verb_dsp();
        $vrb->set_id($id);
        $this->verb = $vrb;
    }

    function set_to(phrase_dsp $to): void
    {
        $this->to = $to;
    }

    function set_to_by_id(int $id): void
    {
        $this->to = $this->set_phrase_by_id($id);
    }

    private function set_phrase_by_id(int $id): phrase_dsp
    {
        if ($id > 0) {
            $wrd = new word_dsp();
            $wrd->set_id($id);
            $phr = $wrd->phrase();
        } elseif ($id < 0) {
            $trp = new triple_dsp();
            $trp->set_id($id * -1);
            $phr = $trp->phrase();
        } else {
            $wrd = new word_dsp();
            $wrd->set_id(0);
            $phr = $wrd->phrase();
        }
        return $phr;
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

    function set_plural(string $plural): void
    {
        $this->plural = $plural;
    }

    function plural(): ?string
    {
        return $this->plural;
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
        $url = $html->url(rest_ctrl::TRIPLE, $this->id(), $back, api_shared::URL_VAR_TRIPLES);
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
    protected function verb_selector(string $form_name): string
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
        if ($this->id() > 0) {
            $header = $html->text_h2('Change "' . $this->from()->name() . ' ' . $this->verb()->name() . ' ' . $this->to()->name() . '"');
            $hidden_fields = $html->form_hidden("id", $this->id());
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
        $url = $html->url(rest_ctrl::PATH_FIXED . 'link' . rest_ctrl::CREATE . rest_ctrl::EXT, $this->id(), $this->id());
        $btn = (new button($url. $back))->edit(messages::TRIPLE_ADD);

        return $html->td($btn);
    }

    /**
     * @returns string the html code to display a bottom to edit this triple in a table cell
     */
    function btn_edit(phrase_dsp $trp, string $back = ''): string
    {

        $html = new html_base();
        $url = $html->url(rest_ctrl::PATH_FIXED . 'link' . rest_ctrl::UPDATE . rest_ctrl::EXT, $this->id(), $trp->id());
        $btn = (new button($url. $back))->edit(messages::TRIPLE_EDIT);

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
        return $this->is_type(phrase_type_shared::PERCENT);
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
        $vars[api::FLD_FROM] = $this->from()->id();
        $vars[api::FLD_VERB] = $this->verb()->id();
        $vars[api::FLD_TO] = $this->to()->id();
        return $vars;
    }

}
