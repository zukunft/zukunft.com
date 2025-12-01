<?php

/*

    web/view/view_relation.php - create HTML code to display a n:m link between two views
    --------------------------


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
include_once html_paths::SANDBOX . 'sandbox_link.php';
include_once html_paths::USER . 'user_message.php';
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

class view_relation extends sandbox_link
{

    /*
     * const
     */

    // crud views
    const string VIEW_ADD = views::VIEW_RELATION_ADD;
    const string VIEW_EDIT = views::VIEW_RELATION_EDIT;
    const string VIEW_DEL = views::VIEW_RELATION_DEL;

    // crud message id
    const msg_id MSG_ADD = msg_id::VIEW_RELATION_ADD;
    const msg_id MSG_EDIT = msg_id::VIEW_RELATION_EDIT;
    const msg_id MSG_DEL = msg_id::VIEW_RELATION_DEL;


    /*
     * object vars
     */

    // the start position in the parent component chain of the modifications defined by the child view
    // e.g. if the parent view contains 3 components, the child view contains 2, the start pos is 2 and the type is to insert (add)
    // the result component list is: 1 (1 from parent), 2 (1 from child), 3 (2 from child), 4 (2 from parent), 5 (3 from parent)
    public ?int $start_pos = null;
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
            if (array_key_exists(url_var::VIEW_PARENT, $url_array)) {
                $this->set_parent_view_id($url_array[url_var::VIEW_PARENT]);
            }
            if (array_key_exists(url_var::VIEW_CHILD, $url_array)) {
                $this->set_child_view_id($url_array[url_var::VIEW_CHILD]);
            }
            if (array_key_exists(url_var::TYPE, $url_array)) {
                $this->set_type_id($url_array[url_var::TYPE]);
            }
            if (array_key_exists(url_var::POSITION, $url_array)) {
                $this->start_pos = $url_array[url_var::POSITION];
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
     * @param user_message $usr_msg ok or a warning e.g. if the server version does not match
     * @return bool true if the mapping has been completed successful
     */
    function api_mapper(array $json_array, user_message $usr_msg): bool
    {
        // get body from message
        $api_msg = new api_message();
        $json_array = $api_msg->validate($json_array);

        parent::api_mapper($json_array, $usr_msg);
        if (array_key_exists(json_fields::PARENT_ID, $json_array)) {
            $this->set_parent_view_id($json_array[json_fields::PARENT_ID]);
        }
        if (array_key_exists(json_fields::CHILD_ID, $json_array)) {
            $this->set_child_view_id($json_array[json_fields::CHILD_ID]);
        }
        if (array_key_exists(json_fields::RELATION_TYPE, $json_array)) {
            $this->set_type_id($json_array[json_fields::RELATION_TYPE]);
        }
        if (array_key_exists(json_fields::POSITION, $json_array)) {
            $this->start_pos = $json_array[json_fields::POSITION];
        }
        if (array_key_exists(json_fields::DESCRIPTION, $json_array)) {
            $this->description = $json_array[json_fields::DESCRIPTION];
        }
        return $usr_msg->is_ok();
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

        $vars[json_fields::PARENT_ID] = $this->parent()?->id();
        $vars[json_fields::CHILD_ID] = $this->child()?->id();
        $vars[json_fields::RELATION_TYPE] = $this->type_id;
        $vars[json_fields::POSITION] = $this->start_pos;
        $vars[json_fields::DESCRIPTION] = $this->description;
        return array_filter($vars, fn($value) => !is_null($value) && $value !== '');
    }


    /*
     * set and get
     */

    function set_parent_view_id(?int $view_id): void
    {
        $msk = new view();
        $msk->set_id($view_id);
        $this->set_parent_view($msk);
    }

    function set_parent_view(?view $view): void
    {
        $this->fob = $view;
    }

    function set_child_view_id(?int $view_id): void
    {
        $msk = new view();
        $msk->set_id($view_id);
        $this->set_child_view($msk);
    }

    function set_child_view(?view $view): void
    {
        $this->tob = $view;
    }

    function set_type_id(?int $type_id = null): void
    {
        $this->type_id = $type_id;
    }


    /*
     * display
     */

    /**
     * return the html code to display the link name
     */
    function name(): string
    {
        $result = '';

        if ($this->parent() != null and $this->child() != null) {
            if ($this->parent()->name() <> '' and $this->child()->name() <> '') {
                $result .= '"' . $this->child()->name() . '" extends "'; // e.g. company details
                $result .= $this->parent()->name() . '"';     // e.g. cash flow statement
            }
        } else {
            $result .= 'view relation objects not set';
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
        if ($this->parent() != null and $this->child() != null) {
            $result = $this->parent()->name_link(NULL, $back) . ' to ' . $this->child()->name_link(NULL, $back);
        } else {
            $result .= log_err("The view name or the component name cannot be loaded.", "component_link->name");
        }

        return $result;
    }


    /*
     * interface
     */

    function parent(): ?view
    {
        return $this->fob;
    }

    function child(): ?view
    {
        return $this->tob;
    }

    /**
     * TODO Prio 0 add cache to be able to return the type name
     * @return string|null
     */
    function relation_type(): ?string
    {
        return $this->type_id;
    }

}
