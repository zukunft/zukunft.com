<?php

/*

    web/component/component_link.php - create HTML code to display a n:m link between a view and a component
    --------------------------------

    The main sections of this object are
    - interface:   rename vars for better readability e.g. "view" instead of the "fob" from the parent object


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

namespace Zukunft\ZukunftCom\main\php\web\component;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once paths::API_OBJECT . 'api_message.php';
include_once html_paths::HELPER . 'data_object.php';
include_once html_paths::PHRASE . 'term.php';
include_once html_paths::SANDBOX . 'sandbox_link.php';
include_once html_paths::USER . 'user_message.php';
include_once html_paths::VIEW . 'view.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED . 'url_var.php';

use Zukunft\ZukunftCom\main\php\api\api_message;
use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\sandbox\sandbox_link;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\web\view\view;

class component_link extends sandbox_link
{

    /*
     * const
     */

    // crud views
    const string VIEW_ADD = views::COMPONENT_LINK_ADD;
    const string VIEW_EDIT = views::COMPONENT_LINK_EDIT;
    const string VIEW_DEL = views::COMPONENT_LINK_DEL;

    // crud message id
    const msg_id MSG_ADD = msg_id::COMPONENT_LINK_ADD;
    const msg_id MSG_EDIT = msg_id::COMPONENT_LINK_EDIT;
    const msg_id MSG_DEL = msg_id::COMPONENT_LINK_DEL;


    /*
     * object vars
     */

    public ?int $order_nbr = null;
    public ?int $pos_type_id = null;
    private ?int $style_id = null;


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
            if (array_key_exists(url_var::COMPONENT, $url_array)) {
                $this->set_component_id($url_array[url_var::COMPONENT]);
            }
            if (array_key_exists(url_var::POSITION, $url_array)) {
                $this->order_nbr = $url_array[url_var::POSITION];
            }
            if (array_key_exists(url_var::POSITION_TYPE, $url_array)) {
                $this->pos_type_id = $url_array[url_var::POSITION_TYPE];
            }
            if (array_key_exists(url_var::STYLE, $url_array)) {
                $this->style_id = $url_array[url_var::STYLE];
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

        // to order the component is only defined on the component link itself
        if (array_key_exists(json_fields::POSITION, $json_array)) {
            $this->order_nbr = $json_array[json_fields::POSITION];
        }
        // the position type is mainly defined on the component link but there is a default setting
        if (array_key_exists(json_fields::POS_TYPE, $json_array)) {
            $this->pos_type_id = $json_array[json_fields::POS_TYPE];
        }
        // the style of the component can be overwritten by the link
        if (array_key_exists(json_fields::STYLE, $json_array)) {
            $this->style_id = $json_array[json_fields::STYLE];
        }

        if (array_key_exists(json_fields::LINK_ID, $json_array)) {
            // the single layer json array version
            $cmp_json = $json_array;
            unset($cmp_json[json_fields::POS_TYPE]);
            unset($cmp_json[json_fields::POSITION]);
            unset($cmp_json[json_fields::STYLE]);
            $this->set_id($cmp_json[json_fields::LINK_ID]);
            unset($cmp_json[json_fields::LINK_ID]);
            if (array_key_exists(json_fields::ID, $json_array)) {
                $this->set_component_id($json_array[json_fields::ID]);
                $this->get_component()->api_mapper($cmp_json, $msg);
            }
        } else {
            // the full object detail version
            if (array_key_exists(json_fields::VIEW, $json_array)) {
                $msk = new view();
                $msk->api_mapper($json_array[json_fields::VIEW], $msg);
                $this->set_view($msk);
            }
            if (array_key_exists(json_fields::VIEW_ID, $json_array)) {
                $this->set_view_id($json_array[json_fields::VIEW_ID]);
            }
            if (array_key_exists(json_fields::COMPONENT, $json_array)) {
                $cmp = new component();
                $cmp->api_mapper($json_array[json_fields::COMPONENT], $msg);
                $this->set_component($cmp);
            }
            if (array_key_exists(json_fields::COMPONENT_ID, $json_array)) {
                $this->set_component_id($json_array[json_fields::COMPONENT_ID]);
            }
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

        $vars[json_fields::VIEW_ID] = $this->get_view()?->id();
        $vars[json_fields::COMPONENT_ID] = $this->get_component()?->id();
        $vars[json_fields::POSITION] = $this->order_nbr;
        $vars[json_fields::POS_TYPE] = $this->pos_type_id;
        $vars[json_fields::STYLE] = $this->style_id;
        return array_filter($vars, fn($value) => !is_null($value) && $value !== '');
    }


    /*
     * interface
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

    function set_component_id(?int $cmp_id): void
    {
        $cmp = new component();
        $cmp->set_id($cmp_id);
        $this->set_component($cmp);
    }

    function set_component(?component $cmp): void
    {
        $this->tob = $cmp;
    }

    function set_predicate_id(?int $predicate_id = null): void
    {
        $this->predicate_id = $predicate_id;
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

        if ($this->get_view() != null and $this->get_component() != null) {
            if ($this->get_view()->name() <> null and $this->get_component()->name() <> null) {
                $result .= '"' . $this->get_component()->name() . '" extends "'; // e.g. company details
                $result .= $this->get_view()->name() . '"';     // e.g. cash flow statement
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
        if ($this->get_view() != null and $this->get_component() != null) {
            $result = $this->get_view()->name_link(NULL, $back) . ' to ' . $this->get_component()->name_link(NULL, $back);
        } else {
            $result .= log_err("The view name or the component name cannot be loaded.", "component_link->name");
        }

        return $result;
    }


    /*
     * interface
     */

    function get_view(): ?view
    {
        return $this->fob;
    }

    /**
     * get the term that is linked to the view (or the other way round)
     * the term function is used to cast a word, triple, verb or formula to a term
     * @return component|null
     */
    function get_component(): ?component
    {
        return $this->tob;
    }

}
