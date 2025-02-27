<?php

/*

    web/word/triple.php - create the HTML code to display a triple (two linked words or triples)
    -------------------

    The main sections of this object are
    - object vars:       the variables of this triple object
    - set and get:       to capsule the vars from unexpected changes
    - api:               set the object vars based on the api json message and create a json for the backend
    - base:              html code for the single object vars
    - buttons:           html code for the buttons e.g. to add, edit, del, link or unlink
    - select:            html code to select parameter like the type
    - type:              link to code actions
    - views:             system view to add, change or delete the word (to be deprecated)


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
include_once WEB_HTML_PATH . 'button.php';
include_once WEB_HTML_PATH . 'html_base.php';
include_once WEB_HTML_PATH . 'html_names.php';
include_once WEB_HTML_PATH . 'html_selector.php';
include_once WEB_HTML_PATH . 'rest_ctrl.php';
include_once WEB_PHRASE_PATH . 'phrase.php';
include_once WEB_PHRASE_PATH . 'phrase_list.php';
//include_once WEB_PHRASE_PATH . 'term.php';
include_once WEB_SANDBOX_PATH . 'sandbox_typed.php';
include_once WEB_SYSTEM_PATH . 'messages.php';
include_once WEB_USER_PATH . 'user_message.php';
//include_once WEB_VERB_PATH . 'verb.php';
include_once WEB_WORD_PATH . 'triple.php';
include_once WEB_WORD_PATH . 'word.php';
include_once SHARED_CONST_PATH . 'views.php';
include_once SHARED_TYPES_PATH . 'phrase_type.php';
include_once SHARED_TYPES_PATH . 'view_styles.php';
include_once SHARED_PATH . 'api.php';
include_once SHARED_PATH . 'json_fields.php';

use html\html_names;
use html\phrase\phrase_list;
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
use shared\const\views;
use shared\json_fields;
use shared\types\phrase_type;
use shared\types\phrase_type as phrase_type_shared;
use shared\types\view_styles;

class triple extends sandbox_typed
{

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
        global $phr_typ_cac;
        if ($code_id == null) {
            $this->set_type_id();
        } else {
            $this->set_type_id($phr_typ_cac->id($code_id));
        }
    }

    /**
     * TODO use ENUM instead of string in php version 8.1
     * @return phrase_type|null the phrase type of this word
     */
    function type(): ?object
    {
        global $phr_typ_cac;
        if ($this->type_id() == null) {
            return null;
        } else {
            return $phr_typ_cac->get($this->type_id());
        }
    }


    /*
     * api
     */

    /**
     * set the vars of this object bases on the api json array
     * @param array $json_array an api json message
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function api_mapper(array $json_array): user_message
    {
        $usr_msg = parent::api_mapper($json_array);
        if (array_key_exists(json_fields::FROM, $json_array)) {
            $value = $json_array[json_fields::FROM];
            if (is_array($value)) {
                $phr = new phrase_dsp();
                $phr->api_mapper($value);
                $this->set_from($phr);
            } else {
                $this->set_from_by_id($value);
            }
        } else {
            $this->set_from(new phrase_dsp());
        }
        if (array_key_exists(json_fields::VERB, $json_array)) {
            $value = $json_array[json_fields::VERB];
            if (is_array($value)) {
                $vrb = new verb_dsp();
                $vrb->api_mapper($value);
                $this->set_verb($vrb);
            } else {
                $this->set_verb_by_id($value);
            }
        } else {
            $this->set_verb(new verb_dsp());
        }
        if (array_key_exists(json_fields::TO, $json_array)) {
            $value = $json_array[json_fields::TO];
            if (is_array($value)) {
                $phr = new phrase_dsp();
                $phr->api_mapper($value);
                $this->set_to($phr);
            } else {
                $this->set_to_by_id($value);
            }
        } else {
            $this->set_to(new phrase_dsp());
        }
        return $usr_msg;
    }

    /**
     * @return array the json message array to send the updated data to the backend
     * an array is used (instead of a string) to enable combinations of api_array() calls
     */
    function api_array(): array
    {
        $vars = parent::api_array();
        $vars[json_fields::FROM] = $this->from()->id();
        $vars[json_fields::VERB] = $this->verb()->id();
        $vars[json_fields::TO] = $this->to()->id();
        return $vars;
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

    /**
     * recursive function to include the foaf words for this triple
     */
    function wrd_lst(): word_list
    {
        log_debug('triple->wrd_lst ' . $this->dsp_id());
        $wrd_lst = new word_list();

        // add the "from" side
        if ($this->from() != null) {
            if ($this->from()->id() > 0) {
                $wrd_lst->add($this->from()->obj()->word());
            } elseif ($this->from->id() < 0) {
                $sub_wrd_lst = $this->from()->wrd_lst();
                foreach ($sub_wrd_lst->lst() as $wrd) {
                    $wrd_lst->add($wrd);
                }
            } else {
                log_err('The from phrase ' . $this->from()->dsp_id() . ' should not have the id 0', 'triple->wrd_lst');
            }
        }

        // add the "to" side
        if ($this->to() != null) {
            if ($this->to->id() > 0) {
                $wrd_lst->add($this->to()->obj()->word());
            } elseif ($this->to->id() < 0) {
                $sub_wrd_lst = $this->to()->wrd_lst();
                foreach ($sub_wrd_lst->lst() as $wrd) {
                    $wrd_lst->add($wrd);
                }
            } else {
                log_err('The to phrase ' . $this->to()->dsp_id() . ' should not have the id 0', 'triple->wrd_lst');
            }
        }

        log_debug($wrd_lst->name_tip());
        return $wrd_lst;
    }


    /*
     * base
     */

    /**
     * display a triple with a link to the main page for the triple
     * @param string|null $back the back trace url for the undo functionality
     * @param string $style the CSS style that should be used
     * @returns string the html code
     */
    function name_link(?string $back = '', string $style = '', int $msk_id = views::TRIPLE_ID): string
    {
        return parent::name_link($back, $style, $msk_id);
    }


    /*
     * buttons
     */

    /**
     * @return string the html code for a bottom
     * to create a new word for the current user
     */
    function btn_add(string $back = ''): string
    {
        return parent::btn_add_sbx(
            views::TRIPLE_ADD,
            messages::TRIPLE_ADD,
            $back);
    }

    /**
     * @return string the html code for a bottom
     * to change a triple e.g. the name or the type
     */
    function btn_edit(string $back = ''): string
    {
        return parent::btn_edit_sbx(
            views::TRIPLE_EDIT,
            messages::TRIPLE_EDIT,
            $back);
    }

    /**
     * @return string the html code for a bottom
     * to exclude the triple for the current user
     * or if no one uses the word delete the complete word
     */
    function btn_del(string $back = ''): string
    {
        return parent::btn_del_sbx(
            views::TRIPLE_DEL,
            messages::TRIPLE_DEL,
            $back);
    }


    /*
     * select
     */

    /**
     * create the HTML code to select a phrase type
     * and select the phrase type of this word
     * @param string $form the name of the html form
     * @return string the html code to select the phrase type
     */
    public function phrase_type_selector(string $form): string
    {
        global $html_phrase_types;
        $used_phrase_id = $this->type_id();
        if ($used_phrase_id == null) {
            $used_phrase_id = $html_phrase_types->default_id();
        }
        return $html_phrase_types->selector($form, $used_phrase_id);
    }

    /**
     * to select the from phrase
     * @param string $form the name of the html form
     * @param phrase_list_dsp|null $phr_lst a preloaded phrase list for the selection
     * @return string the html code to select the phrase
     */
    function phrase_selector_from(
        string $form,
        ?phrase_list $phr_lst = null,
        string $name = ''
    ): string
    {
        $name = html_names::PHRASE . html_names::SEP . html_names::FROM;
        return $this->phrase_selector(
            $form, $this->from()->id(), $phr_lst, $name);
    }

    /**
     * to select the to phrase
     * @param string $form the name of the html form
     * @param phrase_list_dsp|null $phr_lst a preloaded phrase list for the selection
     * @return string the html code to select the phrase
     */
    function phrase_selector_to(
        string $form,
        ?phrase_list $phr_lst = null
    ): string
    {
        $name = html_names::PHRASE . html_names::SEP . html_names::TO;
        return $this->phrase_selector(
            $form, $this->to()->id(), $phr_lst, $name);
    }

    /**
     * to select the from phrase
     * @param string $form the name of the html form
     * @param phrase_list_dsp|null $phr_lst a preloaded phrase list for the selection
     * @param string $name the unique name within the html form for this selector
     * @return string the html code to select the phrase
     */
    private function phrase_selector(
        string $form,
        int $id,
        ?phrase_list $phr_lst = null,
        string $name = ''
    ): string
    {
        if ($phr_lst == null) {
            $phr_lst = new phrase_list();
        }
        return $phr_lst->selector(
            $form, $id, $name,
            '', view_styles::COL_SM_4,
            html_selector::TYPE_DATALIST);
    }

    /**
     * TODO review
     *
     * select a phrase based on a given context
     *
     * @param string $name the unique name inside the form for this selector
     * @param string $form the name of the html form
     * @param string $label the text show to the user
     * @param string $col_class the formatting code to adjust the formatting
     * @param int $selected the id of the preselected phrase
     * @param string $pattern the pattern to filter the phrases
     * @param phrase_dsp|null $phr the context to select the phrases, which is until now just the phrase
     * @return string the html code to select a phrase
     */
    public function phrase_selector_old(
        string      $name,
        string      $form,
        string      $label = '',
        string      $col_class = '',
        int         $selected = 0,
        string      $pattern = '',
        ?phrase_dsp $phr = null
    ): string
    {
        $phr_lst = new phrase_list_dsp();
        $phr_lst->load_like($pattern);
        return $phr_lst->selector($form, $selected, $name, $label, view_styles::COL_SM_4, html_selector::TYPE_DATALIST);
    }

    /**
     * @param string $form the name of the html form
     * @return string the html code to select a phrase
     */
    public function verb_selector(string $form): string
    {
        global $html_verbs;
        if ($this->verb != null) {
            $id = $this->verb()->id();
        } else {
            $id = 0;
        }
        return $html_verbs->selector($form, $id, 'verb', view_styles::COL_SM_4, 'verb:');
    }


    /*
     * type
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
        return $this->is_type(phrase_type_shared::PERCENT);
    }


    /*
     * table
     */

    /**
     * @return string the html code for a table row with the word
     */
    function tr(): string
    {
        return (new html_base())->tr($this->td());
    }

    /**
     * @param string $back the back trace url for the undo functionality
     * @param string $style the CSS style that should be used
     * @returns string the word as a table cell
     */
    function td(string $back = '', string $style = '', int $intent = 0): string
    {
        $cell_text = $this->name_link($back, $style);
        return (new html_base)->td($cell_text, $intent);
    }

    /**
     * simply to display a single triple in a table
     */
    function dsp_tbl($intent): string
    {
        log_debug('triple->dsp_tbl');
        $result = '    <td>' . "\n";
        while ($intent > 0) {
            $result .= '&nbsp;';
            $intent = $intent - 1;
        }
        $result .= '      ' . $this->name_link() . "\n";
        $result .= '    </td>' . "\n";
        return $result;
    }


    /*
     * views
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
            $detail_fields .= 'from: ' . $this->phrase_selector_old(
                    'from', views::TRIPLE_EDIT, 'from:', '', $this->from()->id(), '', $this->from());
            /* TODO
            if (isset($this->verb)) {
                $result .= $this->verb->dsp_selector('forward', $form_name, view_styles::COL_SM_4, $back);
            }
            */
            $detail_fields .= 'to: ' . $this->phrase_selector_old(
                    'to', views::TRIPLE_EDIT, 'to:', '', $this->to()->id(), '', $this->to());
            $detail_row = $html->fr($detail_fields) . '<br>';
            $result = $header . $html->form(views::TRIPLE_EDIT, $hidden_fields . $detail_row);
        }

        return $result;
    }


}
