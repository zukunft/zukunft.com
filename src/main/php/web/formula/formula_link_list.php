<?php

/*

    web/formula/formula_link_list.php - create the html code for a list of formula links
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

namespace Zukunft\ZukunftCom\main\php\web\formula;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::FORMULA . 'formula_link.php';
include_once html_paths::PHRASE . 'phrase.php';
include_once html_paths::PHRASE . 'phrase_list.php';
include_once html_paths::SANDBOX . 'list_dsp.php';
include_once html_paths::USER . 'user_message.php';
include_once paths::SHARED . 'url_var.php';

use Zukunft\ZukunftCom\main\php\web\phrase\phrase;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\web\sandbox\list_dsp;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\url_var;

class formula_link_list extends list_dsp
{


    /*
     * load
     */

    /**
     * get the formula link that use this formula from the backend via api
     *
     * @param int $id of the component
     * @return bool true if the load has been successful
     */
    function load_by_formula_id(int $id): bool
    {
        return parent::load_by_id(formula_link_list::class, url_var::FORMULA, $id);
    }


    /*
     * set and get
     */

    /**
     * set the vars of a formula object based on the given json
     * @param array $json_array an api single object json message
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function api_mapper(array $json_array): user_message
    {
        return parent::api_mapper_list($json_array, new formula_link());
    }


    /*
     * related
     */

    /**
     * get the phrases from this list
     * @param phrase_list $cac_lst cache of phrases used to reduce the backend calls
     * @return phrase_list with the phrases of this list
     */
    function get_phrase_list(phrase_list $cac_lst): phrase_list
    {
        $phr_lst = new phrase_list();
        foreach ($this->lst() as $lnk) {
            $phr_id = $lnk->phrase()->id();
            $to_add = $cac_lst->get_by_id($phr_id);
            if ($to_add == null) {
                if ($phr_id != 0) {
                    // TODO Prio 2 speed up by loading all phrase at once
                    $to_add = new phrase();
                    $to_add->load_by_id($phr_id);
                }
            }
            if ($to_add != null) {
                $phr_lst->add_phrase($to_add);
            }
        }
        return $phr_lst;
    }


    /*
     * display
     */

    /**
     * @return string with a list of the formula names with html links
     * ex. names_linked
     */
    function name_tip(): string
    {
        $names = array();
        foreach ($this->lst() as $lnk) {
            $names[] = $lnk->name_tip();
        }
        return implode(', ', $names);
    }

}
