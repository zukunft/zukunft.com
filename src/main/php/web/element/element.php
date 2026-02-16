<?php

/*

    web/element/element.php - either a word, triple, verb or formula with a link to a formula
    -----------------------

    formula elements are terms or expression operators such as add or brackets
    the element is not a simple combine object because it also includes the link to the formula


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

    Copyright (c) 1995-2024 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\main\php\web\element;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::SANDBOX . 'db_object.php';
include_once html_paths::FORMULA . 'formula.php';
include_once html_paths::WORD . 'triple.php';
include_once html_paths::VERB . 'verb.php';
include_once html_paths::WORD . 'word.php';
include_once html_paths::USER . 'user_message.php';
include_once paths::SHARED . 'json_fields.php';

use Zukunft\ZukunftCom\main\php\web\sandbox\db_object;
use Zukunft\ZukunftCom\main\php\web\formula\formula;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\verb\verb;
use Zukunft\ZukunftCom\main\php\web\word\triple;
use Zukunft\ZukunftCom\main\php\web\word\word;
use Zukunft\ZukunftCom\main\php\shared\json_fields;

class element extends db_object
{

    /*
     * object vars
     */

    // the repeated formula object for direct access
    public formula $frm;
    // the word, verb or formula class name to direct the links
    // TODO Prio 2 deprecate and use $typ_id instead
    public string $type = '';
    // the element type which is the term type plus the result phrases plus special formula selections
    public int $typ_id = 0;
    // the word, verb or formula object
    public word|triple|verb|formula|null $obj = null;
    // the database reference symbol for formula expressions
    public ?string $symbol = null;


    /*
     * api
     */

    /**
     * set the vars of this element object based on the api json array
     * @param array $json_array an api json message
     * @param user_message $msg OK or a warning e.g. if the server version does not match
     * @return bool true if the mapping has been completed successfully
     */
    function api_mapper(array $json_array, user_message $msg): bool
    {
        parent::api_mapper($json_array, $msg);
        if (array_key_exists(json_fields::ID, $json_array)) {
            $this->id = $json_array[json_fields::ID];
        }
        if (array_key_exists(json_fields::FORMULA, $json_array)) {
            $frm = new formula();
            $frm->api_mapper($json_array[json_fields::FORMULA], $msg);
            $this->frm = $frm;
        }
        if (array_key_exists(json_fields::TERM, $json_array)) {
            if (array_key_exists(json_fields::OBJECT_CLASS, $json_array)) {
                if ($json_array[json_fields::OBJECT_CLASS] == json_fields::CLASS_WORD) {
                    $wrd = new word();
                    $wrd->api_mapper($json_array[json_fields::TERM], $msg);
                    $this->obj = $wrd;
                } elseif ($json_array[json_fields::OBJECT_CLASS] == json_fields::CLASS_TRIPLE) {
                    $trp = new triple();
                    $trp->api_mapper($json_array[json_fields::TERM], $msg);
                    $this->obj = $trp;
                } elseif ($json_array[json_fields::OBJECT_CLASS] == json_fields::CLASS_VERB) {
                    $vrb = new verb();
                    $vrb->api_mapper($json_array[json_fields::TERM], $msg);
                    $this->obj = $vrb;
                } elseif ($json_array[json_fields::OBJECT_CLASS] == json_fields::CLASS_FORMULA) {
                    $frm = new formula();
                    $frm->api_mapper($json_array[json_fields::TERM], $msg);
                    $this->obj = $frm;
                }
            }
        }
        if (array_key_exists(json_fields::TYPE, $json_array)) {
            $this->typ_id = $json_array[json_fields::TYPE];
        }
        return $msg->is_ok();
    }


    /*
     * api
     */

    /**
     * create an api json array for the backend based on this frontend object
     * @return array the json message array to send the updated data to the backend
     * an array is used (instead of a string) to enable combinations of api_array() calls
     */
    function api_array(): array
    {
        $vars = parent::api_array();

        $vars[json_fields::ID] = $this->id();
        $vars[json_fields::FORMULA] = $this->frm->api_array();
        if ($this->obj != null) {
            $vars[json_fields::TERM] = $this->obj->api_array();
            if ($this->obj->term()->is_word()) {
                $vars[json_fields::OBJECT_CLASS] = json_fields::CLASS_WORD;
            } elseif ($this->obj->term()->is_verb()) {
                $vars[json_fields::OBJECT_CLASS] = json_fields::CLASS_VERB;
            } elseif ($this->obj->term()->is_triple()) {
                $vars[json_fields::OBJECT_CLASS] = json_fields::CLASS_TRIPLE;
            } elseif ($this->obj->term()->is_formula()) {
                $vars[json_fields::OBJECT_CLASS] = json_fields::CLASS_FORMULA;
            }
            $vars[json_fields::TYPE] = $this->typ_id;
        }
        return array_filter($vars, fn($value) => !is_null($value) && $value !== '');
    }


    /*
     * html
     */

    function name(): string
    {
        return $this->obj->name;
    }

    /**
     * return the HTML code for the element name including a link to inspect the element
     *
     * @param string $back
     * @return string
     */
    function link(string $back = ''): string
    {
        $result = '';

        if ($this->obj != null) {
            if ($this->obj->id() <> 0) {
                // TODO replace with phrase
                if ($this->obj::class == word::class
                    or $this->obj::class == triple::class) {
                    $result = $this->obj->name_link($back);
                }
                if ($this->obj::class == verb::class) {
                    $result = $this->obj->name();
                }
                if ($this->obj::class == formula::class) {
                    $result = $this->obj->edit_link($back);
                }
            }
        }

        return $result;
    }

}