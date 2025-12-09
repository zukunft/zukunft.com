<?php

/*

    web/helper/data_object.php - frontend cache object
    --------------------------

    header object for all frontend data objects e.g. phrase_list, values, formulas


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

namespace Zukunft\ZukunftCom\main\php\web\helper;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

// including off child objects deactivated to avoid loops in including
//include_once html_paths::COMPONENT . 'component_list.php';
//include_once html_paths::FORMULA . 'formula_list.php';
//include_once html_paths::FORMULA . 'formula_link_list.php';
//include_once html_paths::LOG . 'change_log_list.php';
//include_once html_paths::PHRASE . 'phrase_list.php';
//include_once html_paths::REF . 'source_list.php';
//include_once html_paths::REF . 'ref_list.php';
//include_once html_paths::RESULT . 'result_list.php';
//include_once html_paths::TYPES . 'type_lists.php';
//include_once html_paths::USER . 'user_message.php';
//include_once html_paths::VALUE . 'value_list.php';
//include_once html_paths::VIEW . 'view_list.php';
//include_once html_paths::USER . 'user.php';
//include_once html_paths::WORD . 'word_list.php';
//include_once html_paths::WORD . 'triple_list.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED . 'json_fields.php';

use Zukunft\ZukunftCom\main\php\web\component\component_list;
use Zukunft\ZukunftCom\main\php\web\formula\formula_link_list;
use Zukunft\ZukunftCom\main\php\web\formula\formula_list;
use Zukunft\ZukunftCom\main\php\web\log\change_log_list;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\web\ref\ref_list;
use Zukunft\ZukunftCom\main\php\web\ref\source_list;
use Zukunft\ZukunftCom\main\php\web\result\result_list;
use Zukunft\ZukunftCom\main\php\web\types\type_lists;
use Zukunft\ZukunftCom\main\php\web\user\user;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\value\value_list;
use Zukunft\ZukunftCom\main\php\web\view\view_list;
use Zukunft\ZukunftCom\main\php\web\word\triple_list;
use Zukunft\ZukunftCom\main\php\web\word\word_list;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\json_fields;

class data_object
{

    /*
     *  object vars
     */

    // TODO measure and optimize memory versus speed
    //      by keeping either separate lists
    //      or generate always the word or triple list from the phrase list
    //      or generate always the phrase list from the word or triple list

    // the list of cached words
    // using more memory instead of recreating the list every time
    private bool $wrd_lst_dirty = false;
    public word_list $wrd_lst {
        get {
            if ($this->wrd_lst_dirty) {
                if (!$this->phr_lst_dirty) {
                    $this->wrd_lst->merge($this->phr_lst->word_list());
                }
                $this->wrd_lst_dirty = false;
            }
            return $this->wrd_lst;
        }
        set(word_list $value) {
            $this->wrd_lst = $value;
            $this->wrd_lst_dirty = false;
            $this->phr_lst_dirty = true;
        }
    }

    // the list of cached triples
    // using more memory instead of recreating the list every time
    private bool $trp_lst_dirty = false;
    public triple_list $trp_lst {
        get {
            if ($this->trp_lst_dirty) {
                if (!$this->phr_lst_dirty) {
                    $this->trp_lst->merge($this->phr_lst->triple_list());
                }
                $this->trp_lst_dirty = false;
            }
            return $this->trp_lst;
        }
        set(triple_list $value) {
            $this->trp_lst = $value;
            $this->trp_lst_dirty = false;
            $this->phr_lst_dirty = true;
        }
    }

    // the list of cached phrases
    // using more memory instead of recreating the list every time
    // true if the phrase list is not inline with the word and triple list
    private bool $phr_lst_dirty = false;
    public phrase_list $phr_lst {
        get {
            if ($this->phr_lst_dirty) {
                if (!$this->wrd_lst_dirty) {
                    $this->phr_lst->merge($this->wrd_lst->phrase_list());
                }
                if (!$this->trp_lst_dirty) {
                    $this->phr_lst->merge($this->trp_lst->phrase_list());
                }
                $this->phr_lst_dirty = false;
            }
            return $this->phr_lst;
        }
        set(phrase_list $value) {
            $this->phr_lst = $value;
            $this->wrd_lst_dirty = true;
            $this->trp_lst_dirty = true;
            $this->phr_lst_dirty = false;
        }
    }

    public source_list $src_lst {
        set(source_list $value) {
            $this->src_lst = $value;
        }
    }
    public ref_list $ref_lst {
        set(ref_list $value) {
            $this->ref_lst = $value;
        }
    }
    public value_list $val_lst {
        set(value_list $value) {
            $this->val_lst = $value;
        }
    }
    public formula_list $frm_lst;
    public formula_link_list $frm_lnk_lst;
    public result_list $res_lst {
        set(result_list $value) {
            $this->res_lst = $value;
        }
    }
    public view_list $msk_lst;
    private component_list $cmp_lst;
    public ?type_lists $typ_lst_cache = null;

    // the session user
    public user $usr;

    public change_log_list $chg_log;

    // for warning and errors while filling the data_object
    private user_message $usr_msg;
    // set to false if the api should not be used to reload missing data e.g. for unit tests
    public bool $online;


    /*
     * construct and map
     */

    /**
     * init the data object vars and set the lists based on the given api json
     * @param string|null $api_json string with the api json message to fill the list
     */
    function __construct(?string $api_json = null)
    {
        $this->usr_msg = new user_message();
        if ($api_json != null) {
            $this->val_lst = new value_list();
            $this->res_lst = new result_list();
            $this->src_lst = new source_list();
            $this->ref_lst = new ref_list();
            $this->set_from_json($api_json, $this->usr_msg);
            $this->usr = new user();
        } else {
            $this->reset();
        }
    }

    // TODO Prio 0 add missing objects like view_link_list
    function reset(): void
    {
        $this->usr = new user();
        $this->wrd_lst_dirty = true;
        $this->wrd_lst = new word_list();
        $this->trp_lst_dirty = true;
        $this->trp_lst = new triple_list();
        $this->phr_lst_dirty = true;
        $this->phr_lst = new phrase_list();
        $this->src_lst = new source_list();
        $this->ref_lst = new ref_list();
        $this->val_lst = new value_list();
        $this->frm_lst = new formula_list();
        $this->frm_lnk_lst = new formula_link_list();
        $this->res_lst = new result_list();
        $this->msk_lst = new view_list();
        $this->cmp_lst = new component_list();
        $this->chg_log = new change_log_list();
        $this->online = true;
    }


    /*
     * set and get
     */

    /**
     * set the vars of these list display objects bases on the api message
     * @param string $json_api_msg an api json message as a string
     * @param user_message $usr_msg ok or a warning e.g. if the server version does not match
     * @return bool true if the object is filled
     */
    function set_from_json(string $json_api_msg, user_message $usr_msg): bool
    {
        $this->reset();
        $json_array = json_decode($json_api_msg, true);
        if (array_key_exists(json_fields::WORDS, $json_array)) {
            $msg = $this->wrd_lst->api_mapper($json_array[json_fields::WORDS]);
            $usr_msg->add($msg);
        }
        return $usr_msg->is_ok();
    }

    function refresh_words_via_api(user_message $usr_msg): bool
    {
        if ($this->online) {
            $this->wrd_lst->reload($usr_msg);
        }
        return $usr_msg->is_ok();
    }


    /**
     * set the formula_list of this data object
     * @param formula_list $frm_lst
     */
    function set_formula_list(formula_list $frm_lst): void
    {
        $this->frm_lst = $frm_lst;
    }

    /**
     * @return formula_list with the formulas of this data object
     */
    function formula_list(): formula_list
    {
        return $this->frm_lst;
    }

    /**
     * set the view_list of this data object
     * @param view_list $msk_lst
     */
    function set_view_list(view_list $msk_lst): void
    {
        $this->msk_lst = $msk_lst;
    }

    /**
     * set the view_list of this data object
     * @param view_list $msk_lst
     */
    function merge_view_list(view_list $msk_lst): void
    {
        $this->msk_lst->merge($msk_lst);
    }

    /**
     * @return view_list with the views of this data object
     */
    function view_list(): view_list
    {
        return $this->msk_lst;
    }

    /**
     * @return bool true if this context object contains a view list
     */
    function has_view_list(): bool
    {
        if ($this->msk_lst->count() > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * set the component_list of this data object
     * @param component_list $cmp_lst
     */
    function set_component_list(component_list $cmp_lst): void
    {
        $this->cmp_lst = $cmp_lst;
    }

    /**
     * @return component_list with the components of this data object
     */
    function component_list(): component_list
    {
        return $this->cmp_lst;
    }

    /**
     * @return bool true if this context object contains at least some phrases
     */
    function has_phrases(): bool
    {
        if ($this->phr_lst->count() > 0) {
            return true;
        } else {
            return false;
        }
    }

    function add_phrases(phrase_list $phr_lst): void
    {
        foreach ($phr_lst->lst() as $phr) {
            $this->phr_lst->add($phr);
        }
        $this->wrd_lst_dirty = true;
        $this->trp_lst_dirty = true;
    }

    function ref_list_cloned(): ref_list
    {
        return clone $this->ref_lst;
    }

    function value_list_cloned(): value_list
    {
        return clone $this->val_lst;
    }

    /**
     * @return bool true if this context object contains at least some phrases
     */
    function has_changes(): bool
    {
        return !$this->chg_log->is_empty();
    }

    function add_changes(change_log_list $chg_log): void
    {
        foreach ($chg_log->lst() as $log) {
            $this->chg_log->add($log);
        }
    }

    /**
     * @return change_log_list the cache of the relevant change log entries
     */
    function change_log(): change_log_list
    {
        return $this->chg_log;
    }


    /*
     * fill
     */

    /**
     * add the database id of the known test views to view list
     * @return void
     */
    function add_id_to_views(): void
    {
        $views = new views();
        $msk_lst = $this->msk_lst;
        foreach ($msk_lst->lst() as $msk) {
            if ($msk->id == 0) {
                $code_id = $msk->code_id;
                if ($code_id != null) {
                    $msk->id = $views->code_id_to_id($code_id);
                }
            }
        }
    }

    /**
     * add the database id of the known test views to view list
     * @return void
     */
    function add_components_to_views(): void
    {
        $msk_lst = $this->msk_lst;
        foreach ($msk_lst->lst() as $msk) {
            if ($msk->id > 0) {
                foreach ($msk->component_list()->lst() as $cmp) {
                    if ($cmp->id == 0) {
                        $filled = $this->component_list()->get_by_id($cmp->id);
                        if ($filled != null) {
                            $cmp->fill($filled);
                        }
                    }
                }
            }
        }
    }
}
