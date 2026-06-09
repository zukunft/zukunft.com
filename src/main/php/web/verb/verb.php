<?php

/*

    web/verb/verb.php - the display extension of the api verb object
    -----------------

    $vrb is the suggested var name

    The main sections of this object are
    - object vars:       the variables of this word object
    - set and get:       to capsule the vars from unexpected changes
    - api:               set the object vars based on the api json message and create a json for the backend
    - cast:              create related frontend objects e.g. the phrase of a triple
    - base:              html code for the single object vars
    - buttons:           html code for the buttons e.g. to add, edit, del, link or unlink


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

namespace Zukunft\ZukunftCom\main\php\web\verb;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::HELPER . 'data_object.php';
include_once html_paths::HTML . 'html_base.php';
include_once paths::SHARED_CONST . 'rest_ctrl.php';
include_once html_paths::PHRASE . 'term.php';
include_once html_paths::SANDBOX . 'sandbox_named.php';
include_once html_paths::SANDBOX . 'sandbox_named.php';
include_once html_paths::TYPES . 'type_lists.php';
include_once html_paths::VIEW . 'view_list.php';
include_once html_paths::USER . 'user_message.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_TYPES . 'view_types.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED . 'url_var.php';

use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\phrase\term;
use Zukunft\ZukunftCom\main\php\web\sandbox\sandbox_named;
use Zukunft\ZukunftCom\main\php\web\types\type_lists;
use Zukunft\ZukunftCom\main\php\web\view\view_list;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\types\view_types;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\url_var;

class verb extends sandbox_named
{

    /*
     * const
     */

    // curl views
    const string VIEW_ADD = views::VERB_ADD;
    const string VIEW_EDIT = views::VERB_EDIT;
    const string VIEW_DEL = views::VERB_DEL;
    const int VIEW_EDIT_ID = views::VERB_EDIT_ID;

    // curl message id
    const msg_id MSG_ADD = msg_id::VERB_ADD;
    const msg_id MSG_EDIT = msg_id::VERB_EDIT;
    const msg_id MSG_DEL = msg_id::VERB_DEL;


    /*
     * object vars
     */

    // this id text is unique for all code links and is used for system im- and export
    public ?string $code_id = null;
    public ?string $plural = null;
    public ?string $reverse = null;
    public ?string $rev_plural = null;
    // short name of the verb for the use in formulas
    // because there both sides are combined
    public ?string $frm_name = null;
    public float $impact = 0.0;


    /*
     * construct and map
     */

    /**
     * set the vars of this verb frontend object bases on the url array
     * @param array $url_array an array based on $_GET from a form submit
     * @param user_message $usr_msg to enrich with warnings, problems and solutions
     * @param data_object|null $dto the cache as a parameter to be able to simulate test conditions
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function url_mapper(array $url_array, user_message $usr_msg, data_object|null $dto = null): user_message
    {
        parent::url_mapper($url_array, $usr_msg, $dto);
        if ($usr_msg->is_ok()) {
            // the code id is not set by the url and cannot be changed by the frontend
            if (array_key_exists(url_var::PLURAL, $url_array)) {
                $this->plural = $url_array[url_var::PLURAL];
            } else {
                $this->plural = null;
            }
            if (array_key_exists(url_var::REVERSE, $url_array)) {
                $this->reverse = $url_array[url_var::REVERSE];
            } else {
                $this->reverse = null;
            }
            if (array_key_exists(url_var::REVERSE_PLURAL, $url_array)) {
                $this->rev_plural = $url_array[url_var::REVERSE_PLURAL];
            } else {
                $this->rev_plural = null;
            }
            if (array_key_exists(url_var::NAME_IN_FORMULA, $url_array)) {
                if ($url_array[url_var::NAME_IN_FORMULA] != null) {
                    $this->frm_name = $url_array[url_var::IMPACT];
                }
            }
            if (array_key_exists(url_var::IMPACT, $url_array)) {
                if ($url_array[url_var::IMPACT] != null) {
                    $this->impact = $url_array[url_var::IMPACT];
                }
            }
        }
        return $usr_msg;
    }


    /*
     * set and get
     */

    function set_code_id(string $code_id): void
    {
        $this->code_id = $code_id;
    }

    function get_code_id(): ?string
    {
        return $this->code_id;
    }

    /**
     * the verb itself is a type
     * this function is only used as an interface mapping for the term
     * @return int|null
     */
    function type_id(): ?int
    {
        return $this->id;
    }

    function get_plural(): ?string
    {
        return $this->plural;
    }

    function reverse(): ?string
    {
        return $this->reverse;
    }

    function plural_reverse(): ?string
    {
        return $this->rev_plural;
    }

    function formula_name(): ?string
    {
        return $this->frm_name;
    }

    function impact(): int
    {
        return $this->impact;
    }


    /*
     * api
     */

    /**
     * set the vars of this object bases on the api json array
     * public because it is reused e.g. by the phrase group display object
     * @param array $json_array an api json message
     * @param user_message $msg ok or a warning e.g. if the server version does not match
     * @return bool true if the mapping has been completed successfully
     */
    function api_mapper(array $json_array, user_message $msg): bool
    {
        parent::api_mapper($json_array, $msg);
        if (array_key_exists(json_fields::CODE_ID, $json_array)) {
            $this->set_code_id($json_array[json_fields::CODE_ID]);
        } else {
            $this->set_code_id('');
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
        if (array_key_exists(json_fields::PLURAL, $json_array)) {
            $this->plural = $json_array[json_fields::PLURAL];
        } else {
            $this->plural = '';
        }
        if (array_key_exists(json_fields::REVERSE, $json_array)) {
            $this->reverse = $json_array[json_fields::REVERSE];
        } else {
            $this->reverse = '';
        }
        if (array_key_exists(json_fields::REV_PLURAL, $json_array)) {
            $this->rev_plural = $json_array[json_fields::REV_PLURAL];
        } else {
            $this->rev_plural = '';
        }
        if (array_key_exists(json_fields::NAME_IN_FORMULA, $json_array)) {
            $this->frm_name = $json_array[json_fields::NAME_IN_FORMULA];
        } else {
            $this->frm_name = '';
        }
        return $msg->is_ok();
    }

    /**
     * @return array the json message array to send the updated data to the backend
     * an array is used (instead of a string) to enable combinations of api_array() calls
     */
    function api_array(): array
    {
        $vars = parent::api_array();
        $vars[json_fields::CODE_ID] = $this->get_code_id();
        $vars[json_fields::PLURAL] = $this->plural;
        $vars[json_fields::REVERSE] = $this->reverse;
        $vars[json_fields::REV_PLURAL] = $this->rev_plural;
        $vars[json_fields::NAME_IN_FORMULA] = $this->frm_name;
        return $vars;
    }


    /*
     * cast
     */

    function term(): term
    {
        $trm = new term();
        $trm->set_obj($this);
        return $trm;
    }


    /*
     * base
     */

    /**
     * display the verb with a link to the main page for the verb
     * @param string|null $back the back trace url for the undo functionality
     * @param string $style the CSS style that should be used
     * @param int $msk_id database id of the view that should be shown
     * @returns string the html code
     */
    function name_link(?string $back = '', string $style = '', int $msk_id = views::VERB_ID): string
    {
        return parent::name_link($back, $style, $msk_id);
    }


    /*
     * select
     */

    /**
     * create the HTML code to select a view usable for a source
     * @param string $form the name of the html form
     * @param view_list $msk_lst with all suggested views
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
        $msk_lst = $msk_lst->only_type(view_types::VERB);
        return $msk_lst->selector($form, $view_id, $name, $msg_id);
    }


    /*
     * deprecate
     */

    // show the html form to add or edit a new verb
    function dsp_edit(string $back = ''): string
    {
        $html = new html_base();
        log_debug('verb->dsp_edit ' . $this->dsp_id());
        $result = '';

        if ($this->id() <= 0) {
            $script = "verb_add";
            $result .= $html->dsp_text_h2('Add verb (word link type)');
        } else {
            $script = "verb_edit";
            $result .= $html->dsp_text_h2('Change verb (word link type)');
        }
        $result .= $html->dsp_form_start($script);
        $result .= $html->dsp_tbl_start_half();
        $result .= '  <tr>';
        $result .= '    <td>';
        $result .= '      verb name:';
        $result .= '    </td>';
        $result .= '    <td>';
        $result .= '      <input type="' . html_base::INPUT_TEXT . '" name="name" value="' . $this->name . '">';
        $result .= '    </td>';
        $result .= '  </tr>';
        $result .= '  <tr>';
        $result .= '    <td>';
        $result .= '      verb plural:';
        $result .= '    </td>';
        $result .= '    <td>';
        $result .= '      <input type="' . html_base::INPUT_TEXT . '" name="plural" value="' . $this->get_plural() . '">';
        $result .= '    </td>';
        $result .= '  </tr>';
        $result .= '  <tr>';
        $result .= '    <td>';
        $result .= '      reverse:';
        $result .= '    </td>';
        $result .= '    <td>';
        $result .= '      <input type="' . html_base::INPUT_TEXT . '" name="reverse" value="' . $this->reverse . '">';
        $result .= '    </td>';
        $result .= '  </tr>';
        $result .= '  <tr>';
        $result .= '    <td>';
        $result .= '      plural_reverse:';
        $result .= '    </td>';
        $result .= '    <td>';
        $result .= '      <input type="' . html_base::INPUT_TEXT . '" name="plural_reverse" value="' . $this->rev_plural . '">';
        $result .= '    </td>';
        $result .= '  </tr>';
        $result .= '  <input type="' . html_base::INPUT_HIDDEN . '" name="back" value="' . $back . '">';
        $result .= '  <input type="' . html_base::INPUT_HIDDEN . '" name="confirm" value="1">';
        $result .= $html->dsp_tbl_end();
        $result .= $html->dsp_form_end('', $back);

        log_debug('verb->dsp_edit ... done');
        return $result;
    }

}
