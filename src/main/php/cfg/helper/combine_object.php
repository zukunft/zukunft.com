<?php

/*

    model/helper/combine_object.php - parent object to combine two or four sandbox objects
    -------------------------------

    e.g. to combine value and result to figure
    or word and triple to phrase
    or word, triple, verb and formula to term

    TODO use it for figure
    TODO use it for phrase
    TODO use it for term


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

namespace cfg\helper;

use cfg\const\paths;

include_once paths::API_OBJECT . 'api_message.php';
include_once paths::DB . 'sql_db.php';
//include_once paths::MODEL_FORMULA . 'formula.php';
//include_once paths::MODEL_RESULT . 'result.php';
//include_once paths::MODEL_SANDBOX . 'sandbox_named.php';
//include_once paths::MODEL_VALUE . 'value_base.php';
//include_once paths::MODEL_USER . 'user.php';
//include_once paths::MODEL_USER . 'user_message.php';
//include_once paths::MODEL_VERB . 'verb.php';
//include_once paths::MODEL_WORD . 'word.php';
//include_once paths::MODEL_WORD . 'triple.php';
include_once paths::SHARED_TYPES . 'api_type_list.php';
include_once paths::SHARED_HELPER . 'CombineObject.php';
include_once paths::SHARED_HELPER . 'IdObject.php';
include_once paths::SHARED_HELPER . 'TextIdObject.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED . 'library.php';

use cfg\db\sql_db;
use cfg\formula\formula;
use cfg\result\result;
use cfg\sandbox\sandbox_named;
use cfg\user\user;
use cfg\user\user_message;
use cfg\value\value_base;
use cfg\verb\verb;
use cfg\word\triple;
use cfg\word\word;
use controller\api_message;
use shared\helper\CombineObject;
use shared\helper\IdObject;
use shared\helper\TextIdObject;
use shared\json_fields;
use shared\library;
use shared\types\api_type_list;

class combine_object extends CombineObject
{

    /*
     * construct and map
     */

    /**
     * a combine object always covers an existing object
     * e.g. used to combine word and triple to a phrase
     * @param IdObject|TextIdObject|word|triple|verb|formula|value_base|result|sandbox_named|null $obj the object that should be covered by a common interface
     */
    function __construct(IdObject|TextIdObject|word|triple|verb|formula|value_base|result|sandbox_named|null $obj)
    {
        parent::__construct($obj);
    }


    /*
     * set and get
     */

    function set_obj(IdObject|TextIdObject|word|triple|verb|formula|value_base|result|sandbox_named|null $obj): void
    {
        $this->obj = $obj;
    }

    function obj(): IdObject|TextIdObject|word|triple|null
    {
        return $this->obj;
    }

    function isset(): bool
    {
        return $this->obj()->isset();
    }


    /*
     * api
     */

    /**
     * create the api json message string of this combine object for the frontend
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
        $lib = new library();
        $vars = $this->obj()->api_json_array($typ_lst, $usr);
        if ($this->obj()->id() != 0) {
            $class = $lib->class_to_name($this->obj()::class);
            $vars[json_fields::OBJECT_CLASS] = $class;
        }
        return $vars;
    }


    /*
     * info
     */

    /**
     * @return string the field name of the unique id of the combine database view
     */
    function id_field(): string
    {
        $lib = new library();
        return $lib->class_to_name($this::class) . sql_db::FLD_EXT_ID;
    }

    /**
     * @return user_message empty if all vars of the underlying object are set and the phrase can be stored in the database
     */
    function db_ready(): user_message
    {
        return $this->obj()->db_ready();
    }

}
