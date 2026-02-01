<?php

/*

    web/sandbox/sandbox_link.php - extends the frontend sandbox object for links
    ----------------------------

    $sbx_lnk is the suggested var name

    The main sections of this object are
    - object vars:       the variables of this sandbox object
    - construct and map: including the mapping of the db row to this sandbox object
    - api:               create an api array for the frontend and set the vars based on a frontend api message


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

namespace Zukunft\ZukunftCom\main\php\web\sandbox;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::COMPONENT . 'component.php';
include_once html_paths::FORMULA . 'formula.php';
include_once html_paths::HELPER . 'data_object.php';
include_once html_paths::PHRASE . 'phrase.php';
include_once html_paths::PHRASE . 'term.php';
include_once html_paths::USER . 'user_message.php';
include_once html_paths::SANDBOX . 'sandbox.php';
//include_once html_paths::VIEW . 'view.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED . 'url_var.php';

use Zukunft\ZukunftCom\main\php\web\component\component;
use Zukunft\ZukunftCom\main\php\web\formula\formula;
use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase;
use Zukunft\ZukunftCom\main\php\web\phrase\term;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\view\view;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\url_var;

class sandbox_link extends sandbox
{

    /*
     * object vars
     */

    protected formula|view|sandbox_named|combine_named|null $fob = null; // the (F)rom (OB)ject which this linked object is creating the connection
    protected phrase|term|view|component|sandbox_named|combine_named|string|null $tob = null; // the (T)o (OB)ject which this linked object is creating the connection (can be a string for external keys)
    protected int|null $predicate_id = null; // the link type


    /*
     * construct and map
     */

    /**
     * set the vars of this sandbox link object bases on the url array
     * @param array $url_array an array based on $_GET from a form submit
     * @param user_message $usr_msg to enrich with warnings, problems and solutions
     * @param data_object|null $dto the cache as a parameter to be able to simulate test conditions
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function url_mapper(array $url_array, user_message $usr_msg, data_object|null $dto = null): user_message
    {
        parent::url_mapper($url_array, $usr_msg, $dto);
        if ($usr_msg->is_ok()) {
            // the linked objects are set in the child object
            // e.g. the view is set in the view_relation class
            if (array_key_exists(url_var::TYPE, $url_array)) {
                $this->predicate_id = $url_array[url_var::TYPE];
            }
        }
        return $usr_msg;
    }

    /**
     * set the vars this sandbox link bases on the api json array
     * @param array $json_array an api json message
     * @param user_message $msg ok or a warning e.g. if the server version does not match
     * @return bool true if the mapping has been completed successful
     */
    function api_mapper(array $json_array, user_message $msg): bool
    {
        parent::api_mapper($json_array, $msg);
        if (array_key_exists(json_fields::PREDICATE_ID, $json_array)) {
            $this->predicate_id = $json_array[json_fields::PREDICATE_ID];
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
        $vars[json_fields::PREDICATE_ID] = $this->predicate_id;
        return $vars;
    }

}


