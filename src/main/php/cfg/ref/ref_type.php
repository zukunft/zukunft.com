<?php

/*

    model/ref/ref_type.php - the base object for links between a phrase and another system such as wikidata
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

namespace Zukunft\ZukunftCom\main\php\cfg\ref;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::DB . 'sql_field_default.php';
include_once paths::DB . 'sql_field_type.php';
include_once paths::MODEL_HELPER . 'type_object.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::SHARED_TYPES . 'api_type_list.php';
include_once paths::SHARED . 'json_fields.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_default;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_type;
use Zukunft\ZukunftCom\main\php\cfg\helper\type_object;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;

class ref_type extends type_object
{
    // list of the ref types that have a coded functionality
    const string WIKIDATA = "wikidata";
    const string WIKIPEDIA = "wikipedia";

    // the url that can be used to receive data if the external key is added
    public ?string $url = null;

    /*
     * database link
     */

    // comments used for the database creation
    const string TBL_COMMENT = 'to link code functionality to a list of references';
    const string FLD_ID = 'ref_type_id'; // name of the id field as const for other const
    const string FLD_URL_COM = 'the base url to create the urls for the assigned references';
    const string FLD_URL = 'base_url';

    // list of fields that are additional to the standard type fields used for the reference type
    const array FLD_LST_EXTRA = array(
        [self::FLD_URL, sql_field_type::TEXT, sql_field_default::NULL, '', '', self::FLD_URL_COM],
    );

    /*
     * api
     */

    /**
     * TODO use parent function for setting the name, ...
     * create an array for the api json creation
     * differs from the export array by using the internal id instead of the names
     * @param api_type_list|array $typ_lst configuration for the api message e.g. if phrases should be included
     * @param user|null $usr the user for whom the api message should be created which can differ from the session user
     * @return array the filled array used to create the api json message to the frontend
     */
    function api_json_array(api_type_list|array $typ_lst = [], user|null $usr = null): array
    {
        $vars = [];
        $vars[json_fields::NAME] = $this->name();
        $vars[json_fields::CODE_ID] = $this->get_code_id();
        $vars[json_fields::DESCRIPTION] = $this->get_description();
        $vars[json_fields::URL] = $this->url;
        $vars[json_fields::ID] = $this->id();
        return $vars;
    }


}
