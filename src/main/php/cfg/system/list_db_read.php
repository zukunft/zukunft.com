<?php

/*

    model/system/list_db_read.php - add function for paged database reading to the minimal list object
    ------------------------------

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

namespace Zukunft\ZukunftCom\main\php\cfg\system;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::API_OBJECT . 'api_message.php';
//include_once paths::DB . 'sql_db.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::SHARED_HELPER . 'ListOfIdObjects.php';
include_once paths::SHARED_TYPES . 'api_type_list.php';

use Zukunft\ZukunftCom\main\php\api\api_message;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\shared\helper\ListOfIdObjects;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;

class list_db_read extends ListOfIdObjects
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
        global $db_con;
        $api_msg = new api_message();
        $pod_name = $api_msg->api_site_name($db_con);
        if (is_array($typ_lst)) {
            $typ_lst = new api_type_list($typ_lst);
        }
        $vars = $this->api_json_array($typ_lst, $usr);
        return $api_msg->api_json($pod_name, $this::class, $vars, $typ_lst, $usr);
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
