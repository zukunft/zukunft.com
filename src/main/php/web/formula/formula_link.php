<?php

/*

    web/formula/formula_link.php - create HTML code to display a formula link
    ----------------------------

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

include_once html_paths::PHRASE . 'phrase.php';
include_once html_paths::SANDBOX . 'sandbox_link.php';
include_once html_paths::USER . 'user_message.php';
include_once paths::SHARED . 'json_fields.php';

use Zukunft\ZukunftCom\main\php\web\phrase\phrase;
use Zukunft\ZukunftCom\main\php\web\sandbox\sandbox_link;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\json_fields;

class formula_link extends sandbox_link
{

    /*
     * object vars
     */

    // database fields additional to the user sandbox_link fields
    public ?int $order_nbr = null;    // to set the priority of the formula links


    /**
     * return the html code to display the link name
     */
    function name(): string
    {
        $result = '';

        if ($this->formula() != null and $this->phrase() != null) {
            if ($this->formula()->name() <> '' and $this->phrase()->name() <> '') {
                $result .= '"' . $this->phrase()->name() . '" in "'; // e.g. company details
                $result .= $this->formula()->name() . '"';     // e.g. cash flow statement
            }
        } else {
            $result .= 'formula link objects not set';
        }
        return $result;
    }

    /**
     * return the html code to display the link name with the hyperlink to the link
     */
    function name_linked(string $back = ''): string
    {
        $result = '';

        if ($this->formula() != null and $this->phrase() != null) {
            $result = $this->formula()->name_link(NULL, $back) . ' to ' . $this->phrase()->name_link(NULL, $back);
        } else {
            $result .= log_err("The formula name or the phrase name cannot be loaded.", "component_link->name");
        }

        return $result;
    }


    /**
     * set the vars this formula bases on the api json array
     * public because it is reused e.g. by the phrase group display object
     * @param array $json_array an api json message
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function api_mapper(array $json_array): user_message
    {
        $usr_msg = parent::api_mapper($json_array);
        if (array_key_exists(json_fields::FORMULA, $json_array)) {
            $frm = new formula();
            $frm->api_mapper($json_array[json_fields::FORMULA]);
            $this->set_formula($frm);
        } elseif (array_key_exists(json_fields::FORMULA_ID, $json_array)) {
            $frm = new formula();
            $frm->set_id($json_array[json_fields::FORMULA_ID]);
            // TODO Prio 2 get from cache (or api)
            $this->set_formula($frm);
        }
        if (array_key_exists(json_fields::PHRASE, $json_array)) {
            $phr = new phrase();
            $phr->api_mapper($json_array[json_fields::PHRASE]);
            $this->set_phrase($phr);
        } elseif (array_key_exists(json_fields::PHRASE_ID, $json_array)) {
            $phr = new phrase();
            $phr->set_id($json_array[json_fields::PHRASE_ID]);
            // TODO Prio 2 get from cache (or api)
            $this->set_phrase($phr);
        }
        // TODO Prio 1 activate
        /*
        if (array_key_exists(json_fields::N, $json_array)) {
            $this->order_nbr = $json_array[json_fields::REF_TEXT];
        } else {
            $this->order_nbr = null;
        }
        */
        return $usr_msg;
    }


    /*
     * interface
     */

    function set_formula(formula $frm): void
    {
        $this->fob = $frm;
    }

    function formula(): formula
    {
        return $this->fob;
    }

    function set_phrase(phrase $phr): void
    {
        $this->tob = $phr;
    }

    function phrase(): phrase
    {
        return $this->tob;
    }

}
