<?php

/*

    web/ref/ref_list.php - create the HTML code to display a reference list
    --------------------

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

namespace Zukunft\ZukunftCom\main\php\web\ref;

use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;
use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once html_paths::HTML . 'html_base.php';
include_once html_paths::PHRASE . 'phrase.php';
include_once html_paths::PHRASE . 'phrase_list.php';
include_once html_paths::SANDBOX . 'list_dsp.php';
include_once html_paths::USER . 'user_message.php';
include_once paths::SHARED . 'json_fields.php';

use Zukunft\ZukunftCom\main\php\web\phrase\phrase;
use Zukunft\ZukunftCom\main\php\web\sandbox\list_dsp;
use Zukunft\ZukunftCom\main\php\web\user\user_message;

class ref_list extends list_dsp
{

    /*
     * set and get
     */

    /**
     * set the vars of a source object based on the given json
     * @param array $json_array an api single object json message
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function api_mapper(array $json_array): user_message
    {
        return parent::api_mapper_list($json_array, new ref());
    }

    /**
     * select the references that are linked to the given phrase
     * @param phrase|null $phr
     * @return ref_list
     */
    function get_by_phrase(phrase|null $phr): ref_list
    {
        $ref_lst = new ref_list();
        if ($phr != null) {
            foreach ($this->lst() as $ref) {
                if ($ref->has_phrase($phr)) {
                    $ref_lst->add($ref);
                }
            }
        }
        return $ref_lst;
    }

}
