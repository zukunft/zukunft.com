<?php

/*

    web/component/execute/ui_link.php - html user interface components to link and unlink objects
    ---------------------------------


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

namespace Zukunft\ZukunftCom\main\php\web\component\execute;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::PHRASE . 'phrase_list.php';
include_once html_paths::SANDBOX . 'db_object.php';
include_once paths::SHARED . 'url_var.php';

use Zukunft\ZukunftCom\main\php\web\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\web\sandbox\db_object;
use Zukunft\ZukunftCom\main\php\shared\url_var;

class ui_link
{

    /**
     * @return string show a list of phrases with a suggested link type that might be linked to the object
     */
    function phrase_link(
        db_object   $phr,
        string      $form_name,
        phrase_list $phr_lst
    ): string
    {
        return $phr->phrase_selector($phr_lst, url_var::PHRASE, $form_name, $phr->id());
    }

}
