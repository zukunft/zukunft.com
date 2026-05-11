<?php

/*

    web/word/triple.php - create the HTML code to display a triple (two linked words or triples)
    -------------------

    The main sections of this object are
    - object vars:       the variables of this triple object
    - api:               set the object vars based on the api json message and create a json for the backend
    - set and get:       to capsule the vars from unexpected changes
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

namespace Zukunft\ZukunftCom\main\php\web\word;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\shared\api;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::HELPER . 'data_object.php';
include_once html_paths::HTML . 'button.php';
include_once html_paths::HTML . 'html_base.php';
include_once html_paths::HTML . 'html_names.php';
include_once html_paths::HTML . 'html_selector.php';
include_once html_paths::PHRASE . 'phrase.php';
include_once html_paths::PHRASE . 'phrase_list.php';
//include_once html_paths::PHRASE . 'term.php';
include_once html_paths::SANDBOX . 'sandbox_code_id.php';
include_once html_paths::TYPES . 'type_lists.php';
include_once html_paths::USER . 'user_message.php';
//include_once html_paths::VERB . 'verb.php';
//include_once html_paths::VIEW . 'view_list.php';
include_once html_paths::WORD . 'word.php';
include_once paths::SHARED_CONST . 'rest_ctrl.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_TYPES . 'phrase_types.php';
include_once paths::SHARED_TYPES . 'view_styles.php';
include_once paths::SHARED . 'api.php';
include_once paths::SHARED . 'url_var.php';
include_once paths::SHARED . 'json_fields.php';

use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\html\html_selector;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\web\phrase\term;
use Zukunft\ZukunftCom\main\php\web\sandbox\sandbox_code_id;
use Zukunft\ZukunftCom\main\php\web\types\type_lists;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\verb\verb;
use Zukunft\ZukunftCom\main\php\web\view\view_list;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\types\phrase_types;
use Zukunft\ZukunftCom\main\php\shared\types\view_styles;
use Zukunft\ZukunftCom\main\php\shared\url_var;

class triple extends sandbox_code_id
{

    /*
     * const
     */

    // crud views
    const string VIEW_ADD = views::TRIPLE_ADD;
    const string VIEW_EDIT = views::TRIPLE_EDIT;
    const string VIEW_DEL = views::TRIPLE_DEL;

    // crud message id
    const msg_id MSG_ADD = msg_id::TRIPLE_ADD;
    const msg_id MSG_EDIT = msg_id::TRIPLE_EDIT;
    const msg_id MSG_DEL = msg_id::TRIPLE_DEL;


    /*
     * object vars
     */

    // the triple components
    // they can be null to allow front end error messages to the user
    private ?phrase $from = null;
    private ?verb $verb = null;
    private ?phrase $to = null;
    public ?float $weight = null;
    public ?string $plural = null {
        get {
            return $this->plural;
        }
        set {
            $this->plural = $value;
        }
    }
    // the impact used to sort the triples
    private float $impact = 0.0;


    /*
     * construct and map
     */

    /**
     * TODO add the cache object and use it to get linked objects
     * set the vars of this word frontend object bases on the url array
     * public because it is reused e.g. by the phrase group display object
     *
     * @param array $url_array an array based on $_GET from a form submit
     * @param user_message $usr_msg to enrich with warnings, problems and solutions
     * @param data_object|null $dto the cache as a parameter to be able to simulate test conditions
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function url_mapper(array $url_array, user_message $usr_msg, data_object|null $dto = null): user_message
    {
        parent::url_mapper($url_array, $usr_msg, $dto);
        if ($usr_msg->is_ok()) {
            if (array_key_exists(url_var::PHRASE_FROM, $url_array)) {
                $this->set_from_by_id($url_array[url_var::PHRASE_FROM], $dto);
            }
            if (array_key_exists(url_var::VERB, $url_array)) {
                $this->set_verb_by_id($url_array[url_var::VERB]);
            }
            if (array_key_exists(url_var::PHRASE_TO, $url_array)) {
                $this->set_to_by_id($url_array[url_var::PHRASE_TO], $dto);
            }
            if (array_key_exists(url_var::WEIGHT, $url_array)) {
                $this->weight = $url_array[url_var::WEIGHT];
            }
            // TODO Prio 2 use the languages forms
            if (array_key_exists(url_var::PLURAL, $url_array)) {
                $this->plural = $url_array[url_var::PLURAL];
            } else {
                $this->plural = null;
            }
            if (array_key_exists(url_var::IMPACT, $url_array)) {
                if ($url_array[url_var::IMPACT] != null) {
                    $this->impact = $url_array[url_var::IMPACT];
                }
            }
        }
        return $usr_msg;
    }

    /**
     * set the vars of this object bases on the api json array
     * @param array $json_array an api json message
     * @param user_message $msg ok or a warning e.g. if the server version does not match
     * @return bool true if the mapping has been completed successfully
     */
    function api_mapper(array $json_array, user_message $msg): bool
    {
        parent::api_mapper($json_array, $msg);
        if (array_key_exists(json_fields::FROM_PHRASE, $json_array)) {
            $value = $json_array[json_fields::FROM_PHRASE];
            if (is_array($value)) {
                $phr = new phrase();
                $phr->api_mapper($value, $msg);
                $this->set_from($phr);
            } else {
                $this->set_from_by_id($value);
            }
        } elseif (array_key_exists(json_fields::FROM, $json_array)) {
            $value = $json_array[json_fields::FROM];
            if (is_array($value)) {
                $phr = new phrase();
                $phr->api_mapper($value, $msg);
                $this->set_from($phr);
            } else {
                $this->set_from_by_id($value);
            }
        } else {
            $this->set_from(new phrase());
        }
        if (array_key_exists(json_fields::VERB, $json_array)) {
            $value = $json_array[json_fields::VERB];
            if (is_array($value)) {
                $vrb = new verb();
                $vrb->api_mapper($value, $msg);
                $this->set_verb($vrb);
            } else {
                $this->set_verb_by_id($value);
            }
        } else {
            $this->set_verb(new verb());
        }
        if (array_key_exists(json_fields::TO_PHRASE, $json_array)) {
            $value = $json_array[json_fields::TO_PHRASE];
            if (is_array($value)) {
                $phr = new phrase();
                $phr->api_mapper($value, $msg);
                $this->set_to($phr);
            } else {
                $this->set_to_by_id($value);
            }
        } elseif (array_key_exists(json_fields::TO, $json_array)) {
            $value = $json_array[json_fields::TO];
            if (is_array($value)) {
                $phr = new phrase();
                $phr->api_mapper($value, $msg);
                $this->set_to($phr);
            } else {
                $this->set_to_by_id($value);
            }
        } else {
            $this->set_to(new phrase());
        }
        if (array_key_exists(json_fields::WEIGHT, $json_array)) {
            $this->weight = $json_array[json_fields::WEIGHT];
        }
        if (array_key_exists(json_fields::PLURAL, $json_array)) {
            $this->plural = $json_array[json_fields::PLURAL];
        }
        if (array_key_exists(json_fields::IMPACT, $json_array)) {
            if ($json_array[json_fields::IMPACT] != null) {
                $this->impact = $json_array[json_fields::IMPACT];
            } else {
                $this->impact = 0.0;
            }
        } else {
            $this->impact = 0.0;
        }
        return $msg->is_ok();
    }


    /*
     * api
     */

    /**
     * @return array the json message array to send the updated data to the backend
     * an array is used (instead of a string) to enable combinations of api_array() calls
     */
    function api_array(): array
    {
        $vars = parent::api_array();
        $vars[json_fields::FROM] = $this->get_from()->id();
        $vars[json_fields::VERB] = $this->get_verb()->id();
        $vars[json_fields::TO] = $this->get_to()->id();
        $vars[json_fields::WEIGHT] = $this->weight;
        $vars[json_fields::PLURAL] = $this->plural;
        // usage and impact are not included here because this system value is never updated by the frontend
        return $vars;
    }


    /*
     * set and get
     */

    function set(string $from, string $verb, string $to): void
    {
        $this->set_from(new word($from)->phrase());
        $this->set_verb(new verb($verb));
        $this->set_to(new word($to)->phrase());
    }

    function set_from(phrase $from): void
    {
        $this->from = $from;
    }

    function set_from_by_id(int $id, data_object|null $dto = null): void
    {
        $this->from = $this->set_phrase_by_id($id, $dto);
    }

    function set_verb(verb $vrb): void
    {
        $this->verb = $vrb;
    }

    function set_verb_by_id(int $id): void
    {
        $vrb = new verb();
        $vrb->set_id($id);
        $this->verb = $vrb;
    }

    function set_to(phrase $to): void
    {
        $this->to = $to;
    }

    function set_to_by_id(int $id, data_object|null $dto = null): void
    {
        $this->to = $this->set_phrase_by_id($id, $dto);
    }

    private function set_phrase_by_id(int $id, data_object|null $dto): phrase
    {
        $phr = null;
        if ($dto != null) {
            $phr_lst = $dto->phr_lst;
            $phr = $phr_lst->get($id);
        }
        if ($phr == null) {
            if ($id > 0) {
                $wrd = new word();
                $wrd->set_id($id);
                $phr = $wrd->phrase();
            } elseif ($id < 0) {
                $trp = new triple();
                $trp->set_id($id * -1);
                $phr = $trp->phrase();
            } else {
                $wrd = new word();
                $wrd->set_id(0);
                $phr = $wrd->phrase();
            }
        }
        return $phr;
    }

    function get_from(): ?phrase
    {
        return $this->from;
    }

    function get_verb(): verb
    {
        return $this->verb;
    }

    function get_to(): ?phrase
    {
        return $this->to;
    }

    /**
     * @param string|null $code_id the code id of the phrase type
     */
    function set_type(?string $code_id): void
    {
        global $sys;
        if ($code_id == null) {
            $this->set_type_id();
        } else {
            $this->set_type_id($sys->typ_lst->phr_typ->id($code_id));
        }
    }

    /**
     * TODO use ENUM instead of string in php version 8.1
     * @return phrase_types|null the phrase type of this word
     */
    function type(): ?object
    {
        global $sys;
        if ($this->type_id() == null) {
            return null;
        } else {
            return $sys->typ_lst->phr_typ->get($this->type_id());
        }
    }

    function get_plural(): ?string
    {
        return $this->plural;
    }

    function impact(): float
    {
        return $this->impact;
    }

    function has_verb(verb $vrb): bool
    {
        if ($this->verb->id() == $vrb->id()) {
            return true;
        } else {
            return false;
        }
    }

    /*
     * cast
     */

    /**
     * @returns phrase the phrase display object base on this triple object
     */
    function phrase(): phrase
    {
        $phr = new phrase();
        $phr->set_obj($this);
        return $phr;
    }

    function term(): term
    {
        $trm = new term();
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
        if ($this->get_from() != null) {
            if ($this->get_from()->id() > 0) {
                $wrd_lst->add($this->get_from()->obj()->word());
            } elseif ($this->get_from()->id() < 0) {
                $sub_wrd_lst = $this->get_from()->wrd_lst();
                foreach ($sub_wrd_lst->lst() as $wrd) {
                    $wrd_lst->add($wrd);
                }
            } else {
                log_err('The from phrase ' . $this->get_from()->dsp_id() . ' should not have the id 0', 'triple->wrd_lst');
            }
        }

        // add the "to" side
        if ($this->get_to() != null) {
            if ($this->get_to()->id() > 0) {
                $wrd_lst->add($this->get_to()->obj()->word());
            } elseif ($this->get_to()->id() < 0) {
                $sub_wrd_lst = $this->get_to()->wrd_lst();
                foreach ($sub_wrd_lst->lst() as $wrd) {
                    $wrd_lst->add($wrd);
                }
            } else {
                log_err('The to phrase ' . $this->get_to()->dsp_id() . ' should not have the id 0', 'triple->wrd_lst');
            }
        }

        log_debug($wrd_lst->name_tip());
        return $wrd_lst;
    }

    /**
     * @return bool true if the triple is normally not shown to the user e.g. scaling of one is assumed
     */
    function is_hidden(): bool
    {
        return $this->is_type(phrase_types::SCALING_HIDDEN);
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
     * select
     */

    /**
     * create the HTML code to select a phrase type
     * and select the phrase type of this word
     * @param string $form the name of the html form
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the phrase type
     */
    public function phrase_type_selector(string $form, ?type_lists $typ_lst): string
    {
        $used_phrase_id = $this->type_id();
        if ($used_phrase_id == null) {
            $used_phrase_id = $typ_lst->html_phrase_types->default_id();
        }
        return $typ_lst->html_phrase_types->selector($form, $used_phrase_id);
    }

    /**
     * to select the word or triple
     * @param phrase_list $phr_lst a preloaded list of suggested phrases for the selection if no additional input is given from the user
     * @param string $name the unique name within the html form for this selector
     * @param string $form the name of the html form
     * @param int|null $selected the row id of the suggested phrase or the already selected phrase
     * @param string $pattern the pattern to filter the phrases
     * @param msg_id $label_id the translation id for the text show to the user
     * @param string $style the style code e.g. to define the target width
     * @return string the html code to select the phrase
     */
    function phrase_selector(
        phrase_list $phr_lst,
        string      $name,
        string      $form,
        ?int        $selected = null,
        string      $pattern = '',
        msg_id      $label_id = msg_id::FORM_SELECT_PHRASE,
        string      $style = view_styles::COL_SM_4
    ): string
    {
        return $phr_lst->selector($form, $selected, $name, $label_id, $style, html_selector::TYPE_DATALIST);
    }

    /**
     * create the html code to select the verb
     * @param string $form the name of the html form
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @param string $style the formatting code e.g. to fix the width with the default value to leave space for the triple weight
     * @return string the html code to select a verb
     */
    public function verb_selector(
        string      $form,
        ?type_lists $typ_lst,
        string      $style = view_styles::COL_SM_3
    ): string
    {
        if ($this->verb != null) {
            $id = $this->get_verb()->id();
        } else {
            $id = 0;
        }
        return $typ_lst->html_verbs->selector($form, $id, url_var::VERB, $style);
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
        return $this->is_type(phrase_types::PERCENT);
    }

    function is_measure(): bool
    {
        return $this->is_type(phrase_types::MEASURE);
    }

    /**
     * @return bool true if the word has the type "information" (e.g. "1967 (year of definition)")
     * if used for a value these phrases are shown only as a tooltip
     */
    function is_info(): bool
    {
        return $this->is_type(phrase_types::INFO);
    }


    /*
     * table
     */

    /**
     * @return string the html code for a table row with the word
     */
    function tr(): string
    {
        return new html_base()->tr($this->td());
    }

    /**
     * @param string $back the back trace url for the undo functionality
     * @param string $style the CSS style that should be used
     * @returns string the word as a table cell
     */
    function td(string $back = '', string $style = '', int $intent = 0): string
    {
        $cell_text = $this->name_link($back, $style);
        return (new html_base)->td($cell_text, '', $intent);
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
     * select
     */

    /**
     * create the HTML code to select a view
     * @param string $form the name of the html form
     * @param view_list $msk_lst with the suggested views
     * @param string $name the unique html field name for the selection of the view
     * @return string the html code to select a view
     */
    public function view_selector(
        string    $form,
        view_list $msk_lst,
        string    $name = url_var::VIEW,
        msg_id    $msg_id = msg_id::FORM_SELECT_VIEW
    ): string
    {
        $view_id = $this->view_id();
        if ($view_id == null) {
            $view_id = $msk_lst->default_id($this);
        }
        $msk_lst = $msk_lst->ex_system();
        $msk_lst = $msk_lst->ex_non_phrase();
        return $msk_lst->selector($form, $view_id, $name, $msg_id);
    }

    /*
     * to review
     */

    /**
     * display one link to the user by returning the HTML code for the link to the calling function
     * TODO include the user sandbox in the selection
     */
    private
    function display(): string
    {
        log_debug("triple->dsp " . $this->id() . ".");

        $result = ''; // reset the html code var
        $msg = new user_message();

        // get the link from the database
        $this->reload_objects($msg);

        // prepare to show the triple
        $result .= $this->get_from()->name() . ' '; // e.g. Australia
        $result .= $this->get_verb_name() . ' '; // e.g. is a
        $result .= $this->get_to()->name();       // e.g. Country

        return $result;
    }

    /**
     * similar to dsp, but display the reverse expression
     */
    private
    function dsp_r(): string
    {
        log_debug("triple->dsp_r " . $this->id() . ".");

        $result = ''; // reset the html code var
        $msg = new user_message();

        // get the link from the database
        $this->reload_objects($msg);

        // prepare to show the triple
        $result .= $this->get_to()->name() . ' ';   // e.g. Countries
        $result .= $this->get_verb_name() . ' '; // e.g. are
        $result .= $this->get_from()->name();     // e.g. Australia (and others)

        return $result;
    }

    /**
     * display a form to adjust the link between too words or triples
     */
    function dsp_del(string $back = ''): string
    {
        log_debug("triple->dsp_del " . $this->id() . ".");
        $result = ''; // reset the html code var

        //$btn = new button();
        //$result .= $btn->yes_no('Is "' . $this->display() . '" wrong?', '/http/link_del.php?id=' . $this->id() . '&back=' . $back);
        $result .= '<br><br>... and "' . $this->dsp_r() . '" is also wrong.<br><br>If you press Yes, both rules will be removed.';

        return $result;
    }

    /**
     * simply to display a single triple in a table
     */
    function display_linked(): string
    {
        return (new html_base())->ref(api::MAIN_SCRIPT . '?link=' . $this->id(), $this->name());
    }



}
