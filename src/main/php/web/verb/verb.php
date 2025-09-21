<?php

/*

    web/verb/verb.php - the display extension of the api verb object
    -----------------

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
include_once html_paths::SANDBOX . 'sandbox_named.php';
include_once html_paths::TYPES . 'type_lists.php';
include_once html_paths::HTML . 'html_base.php';
include_once paths::SHARED_CONST . 'rest_ctrl.php';
include_once html_paths::PHRASE . 'term.php';
include_once html_paths::SANDBOX . 'sandbox_named.php';
include_once html_paths::USER . 'user_message.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED . 'json_fields.php';

use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\phrase\term;
use Zukunft\ZukunftCom\main\php\web\sandbox\sandbox_named;
use Zukunft\ZukunftCom\main\php\web\types\type_lists;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\json_fields;

class verb extends sandbox_named
{

    /*
     * const
     */

    // curl views
    const string VIEW_ADD = views::VERB_ADD;
    const string VIEW_EDIT = views::VERB_EDIT;
    const string VIEW_DEL = views::VERB_DEL;

    // curl message id
    const msg_id MSG_ADD = msg_id::VERB_ADD;
    const msg_id MSG_EDIT = msg_id::VERB_EDIT;
    const msg_id MSG_DEL = msg_id::VERB_DEL;


    /*
     * object vars
     */

    // this id text is unique for all code links and is used for system im- and export
    public ?string $code_id = null;
    public int $usage = 0;
    public ?string $reverse = null;
    public ?string $rev_plural = null;


    /*
     * set and get
     */

    function set_code_id(string $code_id): void
    {
        $this->code_id = $code_id;
    }

    function code_id(): ?string
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


    /*
     * api
     */

    /**
     * set the vars of this object bases on the api json array
     * public because it is reused e.g. by the phrase group display object
     * @param array $json_array an api json message
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function api_mapper(array $json_array): user_message
    {
        $usr_msg = parent::api_mapper($json_array);
        if (array_key_exists(json_fields::CODE_ID, $json_array)) {
            $this->set_code_id($json_array[json_fields::CODE_ID]);
        } else {
            $this->set_code_id('');
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
        $vars[json_fields::CODE_ID] = $this->code_id();
        $vars[json_fields::USAGE] = $this->usage;
        //$lib = new library();
        //$class = $lib->class_to_name($this::class);
        //$vars[json_fields::OBJECT_CLASS] = $class;
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

    /**
     * create the html code to select the verb type
     * @param string $form the name of the html form
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the verb type
     */
    public function verb_type_selector(string $form, ?type_lists $typ_lst): string
    {
        $used_verb_type_id = $this->type_id();
        if ($used_verb_type_id == null) {
            //$used_verb_type_id = $typ_lst->html_verb_types->default_id();
        }
        //return $typ_lst->html_verb_types->selector($form, $used_verb_type_id);
        return '';
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
