<?php

/*

    web/view/term_view.php - create HTML code to display a n:m link between a term and a view
    ----------------------


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

    Copyright (c) 1995-2025 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\main\php\web\view;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once paths::API_OBJECT . 'api_message.php';
include_once html_paths::HELPER . 'data_object.php';
include_once html_paths::PHRASE . 'term.php';
include_once html_paths::SANDBOX . 'sandbox_link.php';
include_once html_paths::USER . 'user_message.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED . 'url_var.php';

use Zukunft\ZukunftCom\main\php\api\api_message;
use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\phrase\term;
use Zukunft\ZukunftCom\main\php\web\sandbox\sandbox_link;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\url_var;

class term_view extends sandbox_link
{

    /*
     * const
     */

    // crud views
    const string VIEW_ADD = views::VIEW_LINK_ADD;
    const string VIEW_EDIT = views::VIEW_LINK_EDIT;
    const string VIEW_DEL = views::VIEW_LINK_DEL;

    // crud message id
    const msg_id MSG_ADD = msg_id::VIEW_LINK_ADD;
    const msg_id MSG_EDIT = msg_id::VIEW_LINK_EDIT;
    const msg_id MSG_DEL = msg_id::VIEW_LINK_DEL;


    /*
     * object vars
     */

    public ?string $description = null;


    /*
     * construct and map
     */

    /**
     * set the vars of this word frontend object bases on the url array
     * public because it is reused e.g. by the phrase group display object
     * @param array $url_array an array based on $_GET from a form submit
     * @param user_message $usr_msg to enrich with warnings, problems and solutions
     * @param data_object|null $dto the cache as a parameter to be able to simulate test conditions
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function url_mapper(array $url_array, user_message $usr_msg, data_object|null $dto = null): user_message
    {
        parent::url_mapper($url_array, $usr_msg, $dto);
        if ($usr_msg->is_ok()) {
            if (array_key_exists(url_var::VIEW, $url_array)) {
                $this->set_view_id($url_array[url_var::VIEW]);
            }
            if (array_key_exists(url_var::TERM, $url_array)) {
                $this->set_term_id($url_array[url_var::TERM]);
            }
            if (array_key_exists(url_var::DESCRIPTION, $url_array)) {
                $this->description = $url_array[url_var::DESCRIPTION];
            }
        }
        return $usr_msg;
    }

    /**
     * set the vars of this object bases on the api json array
     * public because it is reused e.g. by the phrase group display object
     * @param array $json_array an api json message
     * @param user_message $msg ok or a warning e.g. if the server version does not match
     * @return bool true if the mapping has been completed successfully
     */
    function api_mapper(array $json_array, user_message $msg): bool
    {
        // get body from message
        $api_msg = new api_message();
        $json_array = $api_msg->validate($json_array);

        parent::api_mapper($json_array, $msg);
        if (array_key_exists(json_fields::VIEW_ID, $json_array)) {
            $this->set_view_id($json_array[json_fields::VIEW_ID]);
        }
        if (array_key_exists(json_fields::TERM_ID, $json_array)) {
            $this->set_term_id($json_array[json_fields::TERM_ID]);
        }
        if (array_key_exists(json_fields::DESCRIPTION, $json_array)) {
            $this->description = $json_array[json_fields::DESCRIPTION];
        }
        return $msg->is_ok();
    }


    /*
     * api
     */

    /**
     * create an api json array for the backend based on this frontend object
     * @return array the json message array to send the updated data to the backend
     * an array is used (instead of a string) to enable combinations of api_array() calls
     */
    function api_array(): array
    {
        $vars = parent::api_array();

        $vars[json_fields::VIEW_ID] = $this->view()?->id();
        $vars[json_fields::TERM_ID] = $this->term_linked()?->id();
        $vars[json_fields::DESCRIPTION] = $this->description;
        return array_filter($vars, fn($value) => !is_null($value) && $value !== '');
    }


    /*
     * set and get
     */

    function set_view_id(?int $view_id): void
    {
        $msk = new view();
        $msk->set_id($view_id);
        $this->set_view($msk);
    }

    function set_view(?view $view): void
    {
        $this->fob = $view;
    }

    function set_term_id(?int $trm_id): void
    {
        $trm = new term();
        $trm->set_id($trm_id);
        $this->set_term($trm);
    }

    function set_term(?term $trm): void
    {
        $this->tob = $trm;
    }

    function set_type_id(?int $type_id = null): void
    {
        $this->predicate_id = $type_id;
    }


    /*
     * display
     */

    /**
     * return the html code to display the link name
     */
    function name(): string|null
    {
        $result = '';

        if ($this->view() != null and $this->term_linked() != null) {
            if ($this->view()->name() <> null and $this->term_linked()->name() <> null) {
                $result .= '"' . $this->term_linked()->name() . '" extends "'; // e.g. company details
                $result .= $this->view()->name() . '"';     // e.g. cash flow statement
            }
        } else {
            $result .= 'view link objects not set';
        }
        return $result;
    }

    /**
     * return the html code to display the link name with the hyperlink to the link
     */
    function name_linked(string $back = ''): string
    {
        $result = '';

        //$this->load_objects();
        if ($this->view() != null and $this->term_linked() != null) {
            $result = $this->view()->name_link(NULL, $back) . ' to ' . $this->term_linked()->name_link(NULL, $back);
        } else {
            $result .= log_err("The view name or the component name cannot be loaded.", "component_link->name");
        }

        return $result;
    }


    /*
     * interface
     */

    function view(): ?view
    {
        return $this->fob;
    }

    /**
     * get the term that is linked to the view (or the other way round)
     * the term function is used to cast a word, triple, verb or formula to a term
     * @return term|null
     */
    function term_linked(): ?term
    {
        return $this->tob;
    }

}
