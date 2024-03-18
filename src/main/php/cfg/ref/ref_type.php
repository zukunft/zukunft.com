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
  
  Copyright (c) 1995-2023 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
   
*/

namespace cfg;

use cfg\db\sql_field_default;
use cfg\db\sql_field_type;

include_once MODEL_HELPER_PATH . 'type_object.php';


class ref_type extends type_object
{
    // list of the ref types that have a coded functionality
    const WIKIDATA = "wikidata";
    const WIKIPEDIA = "wikipedia";

    // the url that can be used to receive data if the external key is added
    public ?string $url = null;

    /*
     * database link
     */

    // comments used for the database creation
    const TBL_COMMENT = 'to link code functionality to a list of references';
    const FLD_ID = 'ref_type_id'; // name of the id field as const for other const
    const FLD_URL_COM = 'the base url to create the urls for the assigned references';
    const FLD_URL = 'base_url';

    // list of fields that are additional to the standard type fields used for the reference type
    const FLD_LST_EXTRA = array(
        [self::FLD_URL, sql_field_type::TEXT, sql_field_default::NULL, '', '', self::FLD_URL_COM],
    );

}
