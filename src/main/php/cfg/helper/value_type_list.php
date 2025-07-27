<?php

/*

    model/helper/value_type_list.php - a list of value types e.g. to create the query extension
    --------------------------------


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

namespace cfg\helper;

use cfg\const\paths;

include_once paths::DB . 'sql.php';
include_once paths::SHARED_ENUM . 'value_types.php';
include_once paths::SHARED_HELPER . 'CombineObject.php';
include_once paths::SHARED_HELPER . 'IdObject.php';
include_once paths::SHARED_HELPER . 'ListOf.php';
include_once paths::SHARED_HELPER . 'TextIdObject.php';

use cfg\db\sql;
use shared\enum\value_types;
use shared\helper\CombineObject;
use shared\helper\IdObject;
use shared\helper\ListOf;
use shared\helper\TextIdObject;

class value_type_list extends ListOf
{

    /*
     * modify
     */

    protected function add_direct(IdObject|TextIdObject|CombineObject|value_types $obj_to_add): void
    {
        if ($obj_to_add::class == value_types::class) {
            parent::add_direct($obj_to_add);
        } else {
            log_err($obj_to_add::class . ' is expected to be ' . value_types::class);
        }
    }

    function query_extension(): string
    {
        $result = '';
        if ($this->lst() != [value_types::NUMBER]) {
            foreach ($this->lst() as $typ) {
                $result .= $typ->query_extension();
            }
        }
        if ($result != '') {
            $result = sql::NAME_SEP . $result;
        }
        return $result;
    }


}
