<?php

/*

    model/system/base_list.php - the minimal list object used for the list used in the model
    --------------------------

    e.g. used for the ip range list object

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

    Copyright (c) 1995-2023 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace cfg\system;

include_once API_OBJECT_PATH . 'api_message.php';
include_once DB_PATH . 'sql_db.php';
include_once MODEL_HELPER_PATH . 'combine_object.php';
include_once MODEL_HELPER_PATH . 'db_object_seq_id.php';
//include_once MODEL_SANDBOX_PATH . 'sandbox.php';
include_once MODEL_USER_PATH . 'user.php';
include_once MODEL_USER_PATH . 'user_message.php';
include_once SHARED_ENUM_PATH . 'messages.php';
include_once SHARED_HELPER_PATH . 'CombineObject.php';
include_once SHARED_HELPER_PATH . 'IdObject.php';
include_once SHARED_HELPER_PATH . 'TextIdObject.php';
include_once SHARED_HELPER_PATH . 'ListOfIdObjects.php';
include_once SHARED_TYPES_PATH . 'api_type_list.php';
include_once SHARED_PATH . 'library.php';

use cfg\db\sql_db;
use cfg\helper\combine_object;
use cfg\helper\db_object_seq_id;
use cfg\user\user;
use cfg\user\user_message;
use controller\api_message;
use shared\enum\messages as msg_id;
use shared\helper\CombineObject;
use shared\helper\IdObject;
use shared\helper\ListOfIdObjects;
use shared\helper\TextIdObject;
use shared\types\api_type_list;

class base_list extends ListOfIdObjects
{

    /*
     *  object vars
     */

    // paging vars
    // display and select fields to increase the response time
    private int $offset; // start to display with this id
    public int $limit;   // if not defined, use the default page size


    /*
     * construct and map
     */

    function __construct(array $lst = array())
    {
        parent::__construct($lst);

        $this->offset = 0;
        $this->limit = sql_db::ROW_LIMIT;
    }


    /*
     * set and get
     */


    /**
     * @return array with the API object of the values
     */
    function api_lst(bool $do_save = true): array
    {
        $api_lst = array();
        foreach ($this->lst() as $val) {
            $api_lst[] = $val->api_obj($do_save);
        }

        return $api_lst;
    }

    function set_offset(int $offset): void
    {
        $this->offset = $offset;
    }

    function offset(): int
    {
        return $this->offset;
    }

    /**
     * @returns array with the names on the db keys
     */
    function lst_key(): array
    {
        $result = array();
        foreach ($this->lst() as $val) {
            $result[$val->id()] = $val->name();
        }
        return $result;
    }


    /*
     * api
     */

    /**
     * create the api json message string for this list
     * @param api_type_list|array $typ_lst configuration for the api message e.g. if phrases should be included
     * @param user|null $usr the user for whom the api message should be created which can differ from the session user
     * @returns string the api json message for the object as a string
     */
    function api_json(api_type_list|array $typ_lst = [], user|null $usr = null): string
    {
        if (is_array($typ_lst)) {
            $typ_lst = new api_type_list($typ_lst);
        }

        // null values are not needed in the api message to the frontend
        // but in the api message to the backend null values are relevant
        // e.g. to remove empty string overwrites
        $vars = $this->api_json_array($typ_lst, $usr);
        $vars = array_filter($vars, fn($value) => !is_null($value) && $value !== '');

        // add header if requested
        if ($typ_lst->use_header()) {
            global $db_con;
            $api_msg = new api_message();
            $msg = $api_msg->api_header_array($db_con,  $this::class, $usr, $vars);
        } else {
            $msg = $vars;
        }

        return json_encode($msg);
    }

    /**
     * create an array for the json api message
     *
     * @param api_type_list $typ_lst configuration for the api message e.g. if phrases should be included
     * @param user|null $usr the user for whom the api message should be created which can differ from the session user
     * @returns array with the json fields to create an api message
     */
    function api_json_array(api_type_list $typ_lst, user|null $usr = null): array
    {
        $lst = [];
        foreach ($this->lst() as $sbx) {
            $vars = $sbx->api_json_array($typ_lst, $usr);
            $lst[] = array_filter($vars, fn($value) => !is_null($value) && $value !== '');
        }
        return $lst;
    }

}
