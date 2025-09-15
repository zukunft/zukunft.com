<?php

/*

    web/component/execute/list_related.php - create the html for listed related to an object
    --------------------------------------

    function to create the pure HTML frontend code to display lists of objects related to a given object

    The main sections of this object are
    - object vars:       the variables of this word object


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

include_once html_paths::COMPONENT . 'component.php';
include_once html_paths::FORMULA . 'formula.php';
include_once html_paths::HELPER . 'config.php';
include_once html_paths::PHRASE . 'phrase.php';
include_once html_paths::PHRASE . 'phrase_list.php';
include_once html_paths::WORD . 'word.php';
include_once html_paths::SANDBOX . 'db_object.php';
include_once paths::SHARED_CONST . 'triples.php';
include_once paths::SHARED_CONST . 'words.php';
include_once paths::SHARED_ENUM . 'foaf_direction.php';

use Zukunft\ZukunftCom\main\php\web\component\component;
use Zukunft\ZukunftCom\main\php\web\formula\formula;
use Zukunft\ZukunftCom\main\php\web\helper\config;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\web\word\word;
use Zukunft\ZukunftCom\main\php\web\sandbox\db_object;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\enum\foaf_direction;

class list_related extends component
{

    /**
     * HTML for a list of words or triples
     * @param word|db_object $wrd the object that should be used to select the related objects e.g. the triple "Canton of Zurich"
     * @return string the html code to start a new form and display the tile
     */
    function parents_of_word(word|db_object $wrd): string
    {
        return $this->phrases($wrd->phrase(), foaf_direction::UP);
    }

    /**
     * HTML for a list of words or triples
     * @param word|db_object $wrd the object that should be used to select the related objects e.g. the triple "Canton of Zurich"
     * @return string the html code to start a new form and display the tile
     */
    function children_of_word(word|db_object $wrd): string
    {
        return $this->phrases($wrd->phrase(), foaf_direction::DOWN);
    }

    /**
     * HTML for a list of words or triples
     * @param formula|db_object $frm the object that should be used to select the related objects e.g. the triple "Canton of Zurich"
     * @return string the html code to start a new form and display the tile
     */
    function phrases_of_formula(formula|db_object $frm): string
    {
        global $cfg;

        $phr_lst = new phrase_list();
        $phr_lst->load_by_formula($frm);
        $row_limit = $cfg->get_by([triples::LINK_LIST, words::LIMIT, words::LISTS, words::FRONTEND, words::USER], config::LIMIT_NAME_LIST);
        return $phr_lst->name_link('', $row_limit);
    }

    /**
     * TODO move to a component exe part class
     * @return string a dummy text
     */
    function triple_list(?db_object $dbo = null): string
    {
        return $dbo->name();
    }

    private function phrases(phrase $phr, foaf_direction $dir): string
    {
        $phr_lst = new phrase_list();
        $phr_lst->load_related($phr, $dir);
        return $phr_lst->name_link();
    }

}
