<?php

/*

    web/component/execute/ui_list.php - create the html for listed related to an object
    ---------------------------------

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
include_once html_paths::FORMULA . 'formula_link_list.php';
include_once html_paths::HELPER . 'config.php';
include_once html_paths::HELPER . 'data_object.php';
include_once html_paths::HTML . 'list_sort.php';
include_once html_paths::PHRASE . 'phrase.php';
include_once html_paths::PHRASE . 'phrase_list.php';
include_once html_paths::REF . 'source.php';
//include_once html_paths::RESULT . 'result_list.php';
//include_once html_paths::VALUE . 'value_list.php';
include_once html_paths::VERB . 'verb.php';
include_once html_paths::WORD . 'triple.php';
include_once html_paths::WORD . 'word.php';
include_once html_paths::SANDBOX . 'db_object.php';
include_once paths::SHARED_CONST . 'triples.php';
include_once paths::SHARED_TYPES . 'verbs.php';
include_once paths::SHARED_CONST . 'words.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_ENUM . 'foaf_direction.php';

use Zukunft\ZukunftCom\main\php\web\formula\formula;
use Zukunft\ZukunftCom\main\php\web\formula\formula_link_list;
use Zukunft\ZukunftCom\main\php\web\helper\config;
use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\html\list_sort;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\web\ref\source;
use Zukunft\ZukunftCom\main\php\web\result\result_list;
use Zukunft\ZukunftCom\main\php\web\value\value_list;
use Zukunft\ZukunftCom\main\php\web\verb\verb;
use Zukunft\ZukunftCom\main\php\web\word\triple;
use Zukunft\ZukunftCom\main\php\web\word\word;
use Zukunft\ZukunftCom\main\php\web\sandbox\db_object;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\enum\foaf_direction;

class ui_list extends ui_base
{

    /**
     * HTML for a list of words or triples
     * @param word|db_object $wrd the object that should be used to select the related objects e.g. the triple "Canton of Zurich"
     * @param phrase_list|null $phr_lst the cached list of phrases for initial display without backend call
     * @return string the html code to start a new form and display the tile
     */
    function parents_of_word(word|db_object $wrd, ?phrase_list $phr_lst = null): string
    {
        return $this->phrases($wrd->phrase(), foaf_direction::UP, $phr_lst);
    }

    /**
     * HTML for a list of words or triples
     * @param word|db_object $wrd the object that should be used to select the related objects e.g. the triple "Canton of Zurich"
     * @param phrase_list|null $phr_lst the cached list of phrases for initial display without backend call
     * @return string the html code to start a new form and display the tile
     */
    function children_of_word(word|db_object $wrd, ?phrase_list $phr_lst = null): string
    {
        return $this->phrases($wrd->phrase(), foaf_direction::DOWN, $phr_lst);
    }

    /**
     * HTML for a list of words or triples linked to the given formula in order of impact
     * @param formula|db_object $frm the object that should be used to select the related objects e.g. the triple "Canton of Zurich"
     * @param data_object|null $cac the cached list of phrases for initial display without backend call
     * @return string the html code to start a new form and display the tile
     */
    function phrases_of_formula(formula|db_object $frm, ?data_object $cac = null): string
    {
        global $cfg;

        $page = new system_page();

        $result = $page->system_sub_tile(msg_id::FORM_SUB_TITLE_ASSIGNED_PHRASES);
        $lnk_lst = $cac?->frm_lnk_lst;
        // TODO Prio 2 decide if and when a reloading via api is done
        if ($lnk_lst == null) {
            $lnk_lst = new formula_link_list();
            $lnk_lst->load_by_formula_id($frm->id());
        }
        $phr_lst = $lnk_lst->get_phrase_list($cac->phr_lst);
        if ($phr_lst->is_empty()) {
            $phr_lst = new phrase_list();
            $phr_lst->load_by_formula($frm);
        }
        $row_limit = $cfg->get_by([triples::LINK_LIST, words::LIMIT, words::LISTS, words::FRONTEND, words::USER], config::LIMIT_NAME_LIST);
        $result .= $phr_lst->name_link('', $row_limit);
        return $result;
    }

    /**
     * TODO move to a component exe part class
     * @return string a dummy text
     */
    function triple_list(?db_object $dbo = null, ?data_object $cfg = null): string
    {
        global $mtr;

        $result = '';
        $trp_lst = clone $cfg->trp_lst;
        if ($dbo::class == verb::class) {
            $trp_lst = $trp_lst->get_by_verb($dbo);
            $result = $trp_lst->display();
        } else {
            log_err($dbo::class . '  is not expected to be a selection for triples');
        }
        if ($result == '') {
            $result = $mtr->txt(msg_id::NOT_USED_FOR_TRIPLES);
        }
        return $result;
    }

    /**
     * get a list of formulas related to e.g. a verb
     * @param db_object $dbo e.g. a verb to select only the formulas where the object is used
     * @param data_object|null $cfg the cache values used for a backend independent preselection of the formulas
     * @return string the most relevant formulas related to e.g. a verb
     */
    function formula_list(db_object $dbo, ?data_object $cfg = null): string
    {
        global $mtr;

        $result = '';
        $frm_lst = clone $cfg->frm_lst;
        if ($dbo::class == verb::class) {
            $frm_lst = $frm_lst->get_by_verb($dbo);
            $result = $frm_lst->name_link();
        } else {
            log_err($dbo::class . '  is not expected to be a selection for formulas');
        }
        if ($result == '') {
            $result = $mtr->txt(msg_id::NOT_USED_FOR_VERB);
        }
        return $result;
    }

    /**
     * TODO Prio 1 review at least the verb part
     * @param phrase $phr
     * @param foaf_direction $dir
     * @param phrase_list|null $phr_lst
     * @return string
     */
    private function phrases(
        phrase         $phr,
        foaf_direction $dir,
        ?phrase_list   $phr_cac = null
    ): string
    {
        if ($phr_cac == null) {
            $phr_lst = new phrase_list();
            $phr_lst->load_related($phr, $dir);
        } else {
            //$vrb = new verb();
            //$vrb->id = verbs::IS_ID;
            if ($dir == foaf_direction::UP) {
                $phr_lst = $phr_cac->parent_triples($phr);
            } elseif ($dir == foaf_direction::DOWN) {
                $phr_lst = $phr_cac->children($phr);
            } else {
                $phr_lst = $phr_cac;
            }
        }
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
        if ($dbo::class == word::class) {
            $phr = $dbo->phrase();
        }
        if ($dbo::class == triple::class) {
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
    function values_by_word(
        word|db_object|null $dbo,
        ?data_object        $dto = null,
        ?int                $style_id = null
    ): string
    {
        $val_lst = $dto->val_lst?->filter($dbo);
        $phr_lst = new phrase_list();
        $phr_lst->add_phrase($dbo->phrase());
        return $this->value_list($val_lst, $phr_lst, $style_id);
    }

    /**
     * show a list of values related to the given triple
     *
     * @param triple|db_object|null $dbo the selection object for the value list e.g. if mathematics the most often use math const are shown
     * @param data_object|null $dto the data cache used to fill the value list until the backend has returned the updated list
     * @return string the html code to show the list of values
     */
    function values_by_triple(
        triple|db_object|null $dbo,
        ?data_object          $dto = null,
        ?int                  $style_id = null
    ): string
    {
        $val_lst = $dto->val_lst?->filter($dbo);
        $phr_lst = new phrase_list();
        $phr_lst->add_phrase($dbo->phrase());
        return $this->value_list($val_lst, $phr_lst, $style_id);
    }

    /**
     * show a list of values related to the given triple
     *
     * @param source|db_object|null $dbo the selection object for the value list e.g. if mathematics the most often use math const are shown
     * @param data_object|null $dto the data cache used to fill the value list until the backend has returned the updated list
     * @return string the html code to show the list of values
     */
    function values_by_source(
        source|db_object|null $dbo,
        ?data_object          $dto = null,
        ?int                  $style_id = null
    ): string
    {
        $val_lst = $dto->val_lst?->filter($dbo);
        return $this->value_list_unit($val_lst, $style_id);
    }

    /**
     * show a list of values related to the given object
     * the list is first created based on the given data object
     * but additional an update of the list is request via api
     * if the updated list is returned from the backend the list is updated
     *
     * @param formula|db_object|null $dbo the selection object for the value list e.g. if mathematics the most often use math const are shown
     * @param data_object|null $dto the data cache used to fill the value list until the backend has returned the updated list
     * @return string the html code to show the list of values
     */
    function results_by_word(
        formula|db_object|null $dbo,
        ?data_object        $dto = null,
        ?int                $style_id = null
    ): string
    {
        $res_lst = $dto->res_lst?->filter($dbo);
        $phr_lst = new phrase_list();
        $phr_lst->add_phrase($dbo->phrase());
        return $this->result_list_by($res_lst, $phr_lst, $style_id);
    }

    /**
     * show a list of values related to the given object
     * the list is first created based on the given data object
     * but additional an update of the list is request via api
     * if the updated list is returned from the backend the list is updated
     *
     * @param value_list $val_lst
     * @param phrase_list $phr_lst
     * @param int|null $style_id id
     * @return string the html code to show the list of values
     */
    private function value_list(
        value_list  $val_lst,
        phrase_list $phr_lst,
        ?int        $style_id = null
    ): string
    {
        global $sys;
        $style_txt = '';
        if ($style_id != null) {
            $style = $sys->typ_lst->msk_sty->get($style_id);
            $style_txt = $style->get_code_id();
        }
        return $val_lst->list($phr_lst, '', $style_txt);
    }

    /**
     * show a list of values related to the given object
     * the list is first created based on the given data object
     * but additional an update of the list is request via api
     * if the updated list is returned from the backend the list is updated
     *
     * @param value_list $val_lst
     * @param int|null $style_id id
     * @return string the html code to show the list of values
     */
    private function value_list_unit(
        value_list  $val_lst,
        ?int        $style_id = null
    ): string
    {
        global $sys;
        $style_txt = '';
        if ($style_id != null) {
            $style = $sys->typ_lst->msk_sty->get($style_id);
            $style_txt = $style->get_code_id();
        }
        return $val_lst->list_unit();
    }

    /**
     * show a list of values related to the given object
     * the list is first created based on the given data object
     * but additional an update of the list is request via api
     * if the updated list is returned from the backend the list is updated
     *
     * @param result_list $res_lst
     * @param phrase_list $phr_lst
     * @param int|null $style_id id
     * @return string the html code to show the list of values
     */
    private function result_list_by(
        result_list $res_lst,
        phrase_list $phr_lst,
        ?int        $style_id = null
    ): string
    {
        global $sys;
        $style_txt = '';
        if ($style_id != null) {
            $style = $sys->typ_lst->msk_sty->get($style_id);
            $style_txt = $style->get_code_id();
        }
        return $res_lst->list($phr_lst, '', $style_txt);
    }

    /**
     * @return string a dummy text
     */
    function result_list(?db_object $dbo = null, ?data_object $cfg = null): string
    {
        global $mtr;

        $result = '';
        $res_lst = clone $cfg->res_lst;
        if ($dbo::class == formula::class) {
            $res_lst = $res_lst->get_by_formula($dbo);
            $result = $res_lst->name_link();
        } else {
            log_err($dbo::class . '  is not expected to be a selection for results');
        }
        if ($result == '') {
            $result = $mtr->txt(msg_id::INFO_NOT_USED_FOR_FORMULAS);
        }
        return $result;
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
     * @return string the html code of a sortable list
     */
    function list_sort(
        phrase      $phr,
        data_object $dto
    ): string
    {
        $lst = new list_sort();
        return $lst->list_sort($phr, $dto);
    }

    /**
     * @return string the html code for the start view as a sortable list
     */
    function start_list(
        data_object $dto
    ): string
    {
        $phr = new phrase();
        $phr->load_by_name(triples::GLOBAL_PROBLEM);
        return $this->list_sort($phr, $dto);
    }

    /**
     * @return string the html code of the results that changes if the pending user change is confirmed
     */
    function result_changes(
        result_list|db_object $dbo
    ): string
    {
        return $dbo->display();
    }

}
