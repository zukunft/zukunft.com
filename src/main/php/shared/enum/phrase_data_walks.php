<?php

/*

    shared/enum/phrase_data_walks.php - how the phrase data api collects the data of a phrase
    ---------------------------------

    asked for by the url of the api (url_var::PHRASE_DATA_WALK) e.g. /api/phraseData/?id=-2754&pw=pwf;
    the value walk is the default, because it returns only data that names the phrase


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

    Copyright (c) 1995-2026 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\main\php\shared\enum;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::SHARED . 'url_var.php';

use Zukunft\ZukunftCom\main\php\shared\url_var;

enum phrase_data_walks: string
{
    // the values that have the phrase with all their phrases e.g. the inputs of a calculator page
    case VALUE = url_var::PHRASE_DATA_WALK_VALUE;
    // the triples on the from side of the phrase and their values e.g. the whole calculator tree
    case FROM = url_var::PHRASE_DATA_WALK_FROM;

    // the walk if the url names none
    const phrase_data_walks DEFAULT = self::VALUE;

}
