<?php

/*

    cfg/rdf/qlever.php - import of qlever nt and tsv files
    ------------------


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

namespace Zukunft\ZukunftCom\main\php\cfg\rdf;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_USER . 'user_message.php';

use Zukunft\ZukunftCom\main\php\cfg\user\user_message;

class qlever
{

    /*
     * predicates
     */

    // predicates that are converted to a phrase
    const string COUNTRY_OF_NATIONALITY = 'Country_of_nationality';
    const string GENDER = 'Gender';
    const string PROFESSION = 'Profession';

    // predicates that are converted to a verb
    const string VIEW = 'is-a';

    function import(): user_message
    {
        return new user_message();
    }

}
