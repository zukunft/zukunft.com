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

//include_once html_paths::COMPONENT . 'component_list.php';
include_once html_paths::FORMULA . 'formula_list.php';
include_once html_paths::PHRASE . 'phrase_list.php';
include_once html_paths::REF . 'ref_list.php';
include_once html_paths::TYPES . 'type_lists.php';
include_once html_paths::USER . 'user_message.php';
include_once html_paths::VALUE . 'value_list.php';
include_once html_paths::VIEW . 'view_list.php';
include_once html_paths::USER . 'user.php';
include_once html_paths::WORD . 'word_list.php';
include_once paths::SHARED . 'json_fields.php';

use Zukunft\ZukunftCom\main\php\web\component\component_list;
use Zukunft\ZukunftCom\main\php\web\formula\formula_list;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\web\ref\ref_list;
use Zukunft\ZukunftCom\main\php\web\types\type_lists;
use Zukunft\ZukunftCom\main\php\web\user\user;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\value\value_list;
use Zukunft\ZukunftCom\main\php\web\view\view_list;
use Zukunft\ZukunftCom\main\php\web\word\word_list;
use Zukunft\ZukunftCom\main\php\shared\json_fields;

class data_object
{

    /*
     *  object vars
     */

    private word_list $wrd_lst;
    private phrase_list $phr_lst;
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
    private formula_list $frm_lst;
    private view_list $msk_lst;
    private component_list $cmp_lst;
    public ?type_lists $typ_lst_cache = null;

    // the session user
    public user $usr;

    // for warning and errors while filling the data_object
    private user_message $usr_msg;
    // set to false if the api should not be used to reload missing data e.g. for unit tests
    private bool $online;


    /*
     * construct and map
     */

    /**
     * init the data object vars and set the lists based on the given api json
     * @param string|null $api_json string with the api json message to fill the list
     */
    function __construct(?string $api_json = null)
    {
        if ($api_json != null) {
            $this->val_lst = new value_list();
            $this->ref_lst = new ref_list();
            $this->set_from_json($api_json);
            $this->usr = new user();
        } else {
            $this->reset();
        }
    }

    function reset(): void
    {
        $this->usr = new user();
        $this->wrd_lst = new word_list();
        $this->phr_lst = new phrase_list();
        $this->val_lst = new value_list();
        $this->ref_lst = new ref_list();
        $this->frm_lst = new formula_list();
        $this->msk_lst = new view_list();
        $this->cmp_lst = new component_list();
        $this->usr_msg = new user_message();
        $this->online = true;
    }


    /*
     * set and get
     */

    /**
     * set the vars of these list display objects bases on the api message
     * @param string $json_api_msg an api json message as a string
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function set_from_json(string $json_api_msg): user_message
    {
        $usr_msg = new user_message();
        $this->reset();
        $json_array = json_decode($json_api_msg, true);
        if (array_key_exists(json_fields::WORDS, $json_array)) {
            $msg = $this->wrd_lst->api_mapper($json_array[json_fields::WORDS]);
            $usr_msg->add($msg);
        }
        return $usr_msg;
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
    }

    function phrase_list(): phrase_list
    {
        return $this->phr_lst;
    }

    function ref_list_cloned(): ref_list
    {
        return clone $this->ref_lst;
    }

    function value_list_cloned(): value_list
    {
        return clone $this->val_lst;
    }

    function set_online(): void
    {
        $this->online = true;
    }

    function set_offline(): void
    {
        $this->online = false;
    }

}
