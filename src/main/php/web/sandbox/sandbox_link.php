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

use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::COMPONENT . 'component.php';
include_once html_paths::FORMULA . 'formula.php';
include_once html_paths::HELPER . 'data_object.php';
include_once html_paths::PHRASE . 'phrase.php';
include_once html_paths::PHRASE . 'term.php';
include_once html_paths::USER . 'user_message.php';
include_once html_paths::SANDBOX . 'sandbox.php';
include_once html_paths::TYPES . 'type_object.php';
//include_once html_paths::VIEW . 'view.php';
include_once html_paths::SHARED_TYPES . 'api_type_list.php';
include_once html_paths::SHARED_ENUM . 'messages.php';
include_once html_paths::SHARED_HELPER . 'ListOfIdObjects.php';
include_once html_paths::SHARED . 'json_fields.php';
include_once html_paths::SHARED . 'url_var.php';

use Zukunft\ZukunftCom\main\php\web\component\component;
use Zukunft\ZukunftCom\main\php\web\formula\formula;
use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase;
use Zukunft\ZukunftCom\main\php\web\phrase\term;
use Zukunft\ZukunftCom\main\php\web\types\type_object;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\view\view;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\helper\ListOfIdObjects;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
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
     * @param user_message $msg to enrich with warnings, problems and solutions
     * @param data_object|null $dto the cache as a parameter to be able to simulate test conditions
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function url_mapper(array $url_array, user_message $msg, data_object|null $dto = null): user_message
    {
        parent::url_mapper($url_array, $msg, $dto);
        if ($msg->is_ok()) {
            // the linked objects are set in the child object
            // e.g. the view is set in the view_relation class
            if (array_key_exists(url_var::TYPE, $url_array)) {
                $this->predicate_id = $url_array[url_var::TYPE];
            }
        }
        return $msg;
    }

    /**
     * set the vars this sandbox link bases on the api json array
     * @param array $json_array an api json message
     * @param user_message $msg ok or a warning e.g. if the server version does not match
     * @return bool true if the mapping has been completed successfully
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
     * an array is used (instead of a string) to enable combinations of api_array($msg) calls
     */
    function api_array(api_type_list|array $typ_lst, user_message $msg): array
    {
        $vars = parent::api_array($typ_lst, $msg);
        $vars[json_fields::PREDICATE_ID] = $this->predicate_id;
        return $vars;
    }


    /*
     * display
     */

    /**
     * @return string that best describes this object
     */
    function display(): string
    {
        global $mtr;
        $from_name = $this->fob?->name() ?? '';
        $to_name = is_string($this->tob) ? $this->tob : ($this->tob?->name() ?? '');
        $result = $to_name . ' ' . $mtr->txt(msg_id::LINK_EXTENDS) . ' ' . $from_name;
        return $result;
    }

    /**
     * load the link incl. its two linked objects by adding the ?incl_related=1 url flag, so that
     * the api handler sets api_types::INCL_RELATED and the backend adds the linked objects with
     * their names (see the cfg sandbox_link::api_json_array_linked); the link default page needs
     * the names for the links of the link title subtitle
     *
     * @param int|string $id the database id of the link to load
     * @param user_message $msg to collect the load problems for the requesting user
     * @param int $usr_id the id of the session user to load the link for, 0 for the default
     * @return bool true on a successful load (mirrors load_by_id)
     */
    function load_by_id_with_related(int|string $id, user_message $msg, int $usr_id = 0): bool
    {
        return $this->load_by_id($id, $msg, [url_var::INCL_RELATED => '1'], $usr_id);
    }

    /**
     * the linked object with its name taken from the request cache; a page url carries only the
     * id of the linked objects, so without the cache the link title would show a link without a
     * text (see the "Link title" component of base_views.json)
     *
     * @param object|null $dbo the linked object with only the id set as created from the page url
     * @param ListOfIdObjects|null $lst the cached list of the class of the linked object
     * @return object|null the cached object with its name or the given id only object
     */
    protected function named_from_cache(?object $dbo, ?ListOfIdObjects $lst): ?object
    {
        $result = $dbo;
        if ($dbo != null and $lst != null and $dbo->id() != 0) {
            $cached = $lst->get($dbo->id());
            if ($cached != null) {
                $result = $cached;
            }
        }
        return $result;
    }

    /**
     * get the view with its name from the request cache based on the id of the given view;
     * the system views are in the type list cache that is filled for every page rendering
     * and the user views are in the view list of the request cache, so check both
     * @param object|null $msk the id only view created from the url or the api message
     * @param data_object|null $dto the request cache
     * @return object|null the cached view with its name or the given id only view
     */
    protected function view_named_from_cache(?object $msk, ?data_object $dto): ?object
    {
        $result = $this->named_from_cache($msk, $dto?->typ_lst_cache?->msk_sys);
        if ($result === $msk) {
            $result = $this->named_from_cache($msk, $dto?->view_list());
        }
        return $result;
    }

    /**
     * the children overwrite this with the type list of their own link type
     * @return type_object|null the link type or null if this link has no or no known type
     */
    function link_type(): ?type_object
    {
        return null;
    }

    /**
     * the two linked objects as links, e.g. for the subtitle of the link default page;
     * the children overwrite this with the wording specific to their link
     * @param string $back the back trace url for the undo functionality
     * @return string the html code with a link to each of the two linked objects
     */
    function name_linked(string $back = ''): string
    {
        global $mtr;
        $result = '';
        // an external key (a string to object) cannot be linked
        if ($this->fob != null and $this->tob != null and !is_string($this->tob)) {
            $result = $this->tob->name_link() . ' '
                . $mtr->txt(msg_id::LINK_EXTENDS) . ' '
                . $this->fob->name_link();
        }
        return $result;
    }

}


