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

include_once html_paths::FORMULA . 'formula.php';
include_once html_paths::HELPER . 'config.php';
include_once html_paths::HELPER . 'data_object.php';
include_once html_paths::HTML . 'list_sort.php';
include_once html_paths::PHRASE . 'phrase.php';
include_once html_paths::PHRASE . 'phrase_list.php';
include_once html_paths::WORD . 'triple.php';
include_once html_paths::WORD . 'word.php';
include_once html_paths::SANDBOX . 'db_object.php';
include_once paths::SHARED_CONST . 'triples.php';
include_once paths::SHARED_CONST . 'words.php';
include_once paths::SHARED_ENUM . 'foaf_direction.php';

use Zukunft\ZukunftCom\main\php\web\formula\formula;
use Zukunft\ZukunftCom\main\php\web\helper\config;
use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\html\list_sort;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\web\word\triple;
use Zukunft\ZukunftCom\main\php\web\word\word;
use Zukunft\ZukunftCom\main\php\web\sandbox\db_object;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\enum\foaf_direction;

class ui_list extends ui_base
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

    /**
     * TODO move to a component exe part class
     * @return string a dummy text
     */
    function formula_list(?db_object $dbo = null): string
    {
        return $dbo->name();
    }

    private function phrases(phrase $phr, foaf_direction $dir): string
    {
        $phr_lst = new phrase_list();
        $phr_lst->load_related($phr, $dir);
        return $phr_lst->name_link();
    }

    /**
     * show a list of references related to the given object
     * the list is first created based on the given data object
     * but additional an update of the list is request via api
     * if the updated list is returned from the backend the list is updated
     *
     * @param db_object $dbo the word or triple shown to the user and used to select the related references
     * @param data_object|null $dto the context used to create the view
     * @return string with the html code of the external references
     */
    function ref_list_word(db_object $dbo, ?data_object $dto): string
    {
        $result = '';
        $phr = null;
        if ($dbo:: class == word::class) {
            $phr = $dbo->phrase();
        }
        if ($dbo:: class == triple::class) {
            $phr = $dbo->phrase();
        }
        $ref_lst = $dto->ref_list_cloned();
        if ($phr != null) {
            $ref_lst = $ref_lst->get_by_phrase($phr);
            $phr_lst = new phrase_list();
            $phr_lst->add_phrase($dbo->phrase());
            $result = $ref_lst->list($phr_lst);
        }
        return $result;
    }

    /**
     * @param db_object $dbo the word, triple or formula object that should be shown to the user
     * @param data_object|null $cfg the context used to create the view
     * @return string with the html code of links that can be changes
     */
    function link_list_word(db_object $dbo, ?data_object $cfg): string
    {
        // TODO review
        return 'list of phrases related to ' . $dbo->name() . ' ';
    }

    /**
     * TODO move code from component_dsp_old
     * @return string a dummy text
     */
    function num_list(): string
    {
        return 'num_list component';
    }

    /**
     * TODO move code from component_dsp_old
     * @return string a dummy text
     */
    function formulas(): string
    {
        return 'formulas component';
    }

    /**
     * show a list of values related to the given object
     * the list is first created based on the given data object
     * but additional an update of the list is request via api
     * if the updated list is returned from the backend the list is updated
     *
     * @param word|db_object|null $dbo the selection object for the value list e.g. if mathematics the most often use math const are shown
     * @param data_object|null $dto the data cache used to fill the value list until the backend has returned the updated list
     * @return string the html code to show the list of values
     */
    function value_list(
        word|db_object|null $dbo,
        ?data_object        $dto = null,
        ?int                $style_id = null
    ): string
    {
        global $msk_sty_cac;
        $style_txt = '';
        if ($style_id != null) {
            $style = $msk_sty_cac->get($style_id);
            $style_txt = $style->code_id();
        }
        $val_lst = $dto->value_list_cloned();
        $val_lst->filter($dbo);
        $phr_lst = new phrase_list();
        $phr_lst->add_phrase($dbo->phrase());
        return $val_lst->list($phr_lst, '', $style_txt);
    }

    /**
     * TODO move code from component_dsp_old
     * @return string a dummy text
     */
    function results(): string
    {
        return 'results component';
    }

    /**
     * TODO move code from component_dsp_old
     * @return string a dummy text
     */
    function result_list(): string
    {
        return 'result_list component';
    }

    /**
     * @return string the html code of a sortable list
     */
    function list_sort(
        phrase      $phr,
        data_object $dbo
    ): string
    {
        $lst = new list_sort();
        return $lst->list_sort($phr, $dbo);
    }

    /**
     * @return string the html code for the start view as a sortable list
     */
    function start_list(
        data_object $dbo
    ): string
    {
        $phr = new phrase();
        $phr->load_by_name(triples::GLOBAL_PROBLEM);
        return $this->list_sort($phr, $dbo);
    }

}
