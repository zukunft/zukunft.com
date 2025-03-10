<?php

/*

    model/helper/data_object.php - a header object for all data objects e.g. phrase_list, values, formulas
    --------------------------

    the views are only added here for selection boxes


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

namespace cfg\helper;

// more specific includes are switched off to avoid circular includes
//include_once MODEL_FORMULA_PATH . 'formula.php';
//include_once MODEL_FORMULA_PATH . 'formula_list.php';
include_once MODEL_USER_PATH . 'user.php';
include_once MODEL_USER_PATH . 'user_message.php';
//include_once MODEL_REF_PATH . 'source.php';
//include_once MODEL_REF_PATH . 'source_list.php';
//include_once MODEL_PHRASE_PATH . 'phrase.php';
//include_once MODEL_PHRASE_PATH . 'phrase_list.php';
//include_once MODEL_VALUE_PATH . 'value.php';
//include_once MODEL_VALUE_PATH . 'value_base.php';
//include_once MODEL_VALUE_PATH . 'value_list.php';
//include_once MODEL_VIEW_PATH . 'view_list.php';
//include_once MODEL_WORD_PATH . 'word.php';
//include_once MODEL_WORD_PATH . 'word_list.php';
//include_once MODEL_WORD_PATH . 'triple.php';
//include_once MODEL_WORD_PATH . 'triple_list.php';
include_once API_OBJECT_PATH . 'api_message.php';
include_once SHARED_TYPES_PATH . 'api_type_list.php';
include_once SHARED_PATH . 'json_fields.php';

use cfg\formula\formula;
use cfg\formula\formula_list;
use cfg\phrase\phrase;
use cfg\phrase\phrase_list;
use cfg\ref\source;
use cfg\ref\source_list;
use cfg\user\user;
use cfg\user\user_message;
use cfg\value\value;
use cfg\value\value_list;
use cfg\view\view_list;
use cfg\word\word;
use cfg\word\word_list;
use cfg\word\triple;
use cfg\word\triple_list;
use controller\api_message;
use shared\json_fields;
use shared\types\api_type_list;

class data_object
{

    /*
     *  object vars
     */

    private user $usr; // the person for whom the list has been created

    private word_list $wrd_lst;
    private triple_list $trp_lst;
    private source_list $src_lst;
    private value_list $val_lst;
    private formula_list $frm_lst;
    private view_list $msk_lst;
    // for warning and errors while filling the data_object
    private user_message $usr_msg;


    /*
     * construct and map
     */

    /**
     * always set the user because always someone must have requested to create the list
     * e.g. an admin can have requested to import words for another user
     *
     * @param user $usr the user who requested the action
     */
    function __construct(user $usr)
    {
        $this->set_user($usr);
        $this->wrd_lst = new word_list($usr);
        $this->trp_lst = new triple_list($usr);
        $this->src_lst = new source_list($usr);
        $this->val_lst = new value_list($usr);
        $this->frm_lst = new formula_list($usr);
        $this->msk_lst = new view_list($usr);
        $this->usr_msg = new user_message();
    }


    /*
     * api
     */

    /**
     * create the api json message string of this data object that can be sent to the frontend
     * @param api_type_list|array $typ_lst configuration for the api message e.g. if phrases should be included
     * @param user|null $usr the user for whom the api message should be created which can differ from the session user
     * @return string with the api json string that should be sent to the backend
     */
    function api_json(api_type_list|array $typ_lst = [], user|null $usr = null): string
    {
        if (is_array($typ_lst)) {
            $typ_lst = new api_type_list($typ_lst);
        }

        $vars = $this->api_array($typ_lst);

        // add header if requested
        if ($typ_lst->use_header()) {
            global $db_con;
            $api_msg = new api_message();
            $msg = $api_msg->api_header_array($db_con, $this::class, $usr, $vars);
        } else {
            $msg = $vars;
        }

        return json_encode($msg);
    }

    /**
     * create an api json array for the backend based on this frontend object
     * @param api_type_list|array $typ_lst configuration for the api message e.g. if phrases should be included
     * @return array the json message array to send the updated data to the backend
     * an array is used (instead of a string) to enable combinations of api_array() calls
     */
    function api_array(api_type_list|array $typ_lst = []): array
    {
        $vars = [];
        $vars[json_fields::WORDS] = $this->wrd_lst->api_json_array($typ_lst);
        $vars[json_fields::TRIPLES] = $this->trp_lst->api_json_array($typ_lst);
        $vars[json_fields::SOURCES] = $this->src_lst->api_json_array($typ_lst);
        $vars[json_fields::VALUES] = $this->val_lst->api_json_array($typ_lst);
        $vars[json_fields::FORMULAS] = $this->frm_lst->api_json_array($typ_lst);
        $vars[json_fields::VIEWS] = $this->msk_lst->api_json_array($typ_lst);
        return array_filter($vars, fn($value) => !is_null($value) && $value !== '');
    }


    /*
     * set and get
     */

    /**
     * set the user of the phrase list
     *
     * @param user $usr the person who wants to access the phrases
     * @return void
     */
    function set_user(user $usr): void
    {
        $this->usr = $usr;
    }

    /**
     * @return user the person who wants to see the phrases
     */
    function user(): user
    {
        return $this->usr;
    }

    /**
     * @return word_list with the words of this data object
     */
    function word_list(): word_list
    {
        return $this->wrd_lst;
    }

    /**
     * @return triple_list with the triples of this data object
     */
    function triple_list(): triple_list
    {
        return $this->trp_lst;
    }

    /**
     * @return phrase_list with the words and triples of this data object
     */
    function phrase_list(): phrase_list
    {
        $phr_lst = $this->word_list()->phrase_lst();
        $phr_lst->merge($this->triple_list()->phrase_lst());
        return $phr_lst;
    }

    /**
     * @return source_list with the sources of this data object
     */
    function source_list(): source_list
    {
        return $this->src_lst;
    }

    /**
     * @return value_list with the values of this data object
     */
    function value_list(): value_list
    {
        return $this->val_lst;
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
     * @return view_list with the view of this data object
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
     * add a name word without db id to the list
     * @param word $wrd with the name set
     * @return void
     */
    function add_word(word $wrd): void
    {
        $this->wrd_lst->add_by_name($wrd);
    }

    /**
     * add a triple with the names of the linked phrase names but without db id to the list
     * @param triple $trp with the name and word names set
     * @return void
     */
    function add_triple(triple $trp): void
    {
        $this->trp_lst->add_by_name($trp);
    }

    /**
     * add a source with the names but without db id to the list
     * @param source $src with the name and word names set
     * @return void
     */
    function add_source(source $src): void
    {
        $this->src_lst->add_by_name($src);
    }

    /**
     * add a formula with word and triple names but without db id to the list
     * @param formula $frm with the name and word names set
     * @return void
     */
    function add_formula(formula $frm): void
    {
        $this->frm_lst->add_by_name($frm);
    }

    /**
     * add a value to the list
     * @param value $val a value that might not yet have a group id
     * @return void
     */
    function add_value(value $val): void
    {
        $this->val_lst->add($val, true);
    }

    function add_message(string $msg): void
    {
        $this->usr_msg->add_message($msg);
    }

    /**
     * get a word or triple by the name from this cache object
     * @param string $name the name of the word or triple
     * @return phrase|null
     */
    function get_phrase_by_name(string $name): ?phrase
    {
        $wrd = $this->word_list()->get_by_name($name);
        $phr = $wrd?->phrase();
        if ($phr == null) {
            $trp = $this->triple_list()->get_by_name($name);
            $phr = $trp?->phrase();
        }
        return $phr;
    }

    /**
     * add all words, triples and values to the database
     * or update the database
     * @return user_message ok or the error message for the user with the suggested solution
     */
    function save(): user_message
    {
        $usr_msg = new user_message();
        // save the data lists in order of the dependencies
        $usr_msg->add($this->word_list()->save());
        $usr_msg->add($this->triple_list()->save($this->word_list()->phrase_lst()));
        $usr_msg->add($this->value_list()->save());
        return $usr_msg;
    }

}
