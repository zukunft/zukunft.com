<?php

/*

    model/formula/figure.php - combine object for value and result
    ------------------------

    either a value of a formula result object or a value if a user has overwritten a formula result


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

namespace cfg;

include_once MODEL_HELPER_PATH . 'combine_object.php';
include_once API_FORMULA_PATH . 'figure.php';
include_once MODEL_VALUE_PATH . 'value.php';
include_once MODEL_RESULT_PATH . 'result.php';
include_once MODEL_FORMULA_PATH . 'formula.php';
include_once MODEL_USER_PATH . 'user.php';
include_once SHARED_PATH . 'json_fields.php';

use api\formula\figure as figure_api;
use cfg\group\group;
use cfg\result\result;
use cfg\value\value;
use DateTime;
use shared\json_fields;

class figure extends combine_object
{

    /*
     * database link
     */

    // the database and JSON object duplicate field names for combined value and result mainly to link figures
    const FLD_ID = 'figure_id';

    // the common figure database field names excluding the id and excluding the user specific fields
    const FLD_NAMES = array(
        group::FLD_ID
    );


    /*
     * construct and map
     */

    /**
     * a figure is either created based on a user value or formula result
     * @param user|value|result|null $obj
     */
    function __construct(user|value|result|null $obj)
    {
        if ($obj::class == user::class) {
            // create a dummy value object to remember the user
            parent::__construct(new value($obj));
        } else {
            parent::__construct($obj);
        }
    }

    /**
     * map the common value and result database fields to the figure fields
     *
     * @param array|null $db_row with the data directly from the database
     * @param string $id_fld the name of the id field as defined in this child and given to the parent
     * @return bool true if the triple is loaded and valid
     */
    function row_mapper(?array $db_row, string $ext, string $id_fld = self::FLD_ID): bool
    {
        $result = false;
        $this->set_id(0);
        if ($db_row != null) {
            if ($db_row[$id_fld] > 0) {
                $this->set_obj_id($db_row[$id_fld]);
                // map a user value
                $val = new value($this->user());
                $val->row_mapper_sandbox_multi($db_row, $ext);
                $this->set_obj($val);
                $result = true;
            } elseif ($db_row[$id_fld] < 0) {
                $this->set_obj_id($db_row[$id_fld]);
                // map a formula result
                $res = new result($this->user());
                $res->row_mapper($db_row);
                $this->set_obj($res);
                $result = true;
            } else {
                log_warning('figure with id 0 is not expected');
            }
        }
        return $result;
    }


    /*
     * set and get
     */

    /**
     * set the object id based on the given term id
     * must have the same logic as the api and the frontend
     *
     * @param int $id the term (not the object!) id
     * @return void
     */
    function set_id(int $id): void
    {
        // TODO check if not set_id should be used
        $this->set_obj_id(abs($id));
    }

    /**
     * @param int $id the id of the object
     * the id of the value or result is
     * created dynamically by the child class
     */
    function set_obj_id(int $id): void
    {
        $this->obj()?->set_id($id);
    }

    /**
     * @return int the figure id based on the value or result id
     * must have the same logic as the database view and the frontend
     */
    function id(): int
    {
        if ($this->is_result()) {
            return $this->obj_id() * -1;
        } else {
            return $this->obj_id();
        }
    }

    /**
     * @return int the id of the value or result id (not unique!)
     * must have the same logic as the database view and the frontend
     */
    function obj_id(): int
    {
        return $this->obj()->id();
    }

    /**
     * @return user the person who wants to see a word, verb, triple, formula or view
     */
    function user(): user
    {
        return $this->obj()->user();
    }

    /**
     * @return float|null with the value either from the formula result or the db value from a user or source
     */
    function number(): ?float
    {
        return $this->obj()->number();
    }

    /**
     * set by the formula element that has be used to get this figure
     * @param string $symbol the reference text either from the formula result or the db value from a user or source
     */
    function set_symbol(string $symbol): void
    {
        $this->obj()->set_symbol($symbol);
    }

    /**
     * @return string the reference text either from the formula result or the db value from a user or source
     */
    function symbol(): string
    {
        return $this->obj()->symbol();
    }

    /**
     * @return DateTime|null the timestamp of the last update either from the formula result or the db value from a user or source
     */
    function last_update(): ?DateTime
    {
        return $this->obj()->last_update();
    }

    /**
     * @return bool true if the user has done no overwrites either of the value direct
     * or the formula or the formula assignment
     */
    function is_std(): bool
    {
        if ($this->is_result()) {
            if ($this->obj == null) {
                return false;
            } else {
                if (get_class($this->obj) == formula::class) {
                    return $this->obj->is_std();
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }


    /*
     * cast
     */

    /**
     * @returns figure_api the cast object for the api
     */
    function api_obj(bool $do_save = true): figure_api
    {
        return new figure_api($this->obj->api_obj($do_save));
    }

    /**
     * @returns string the api json message for the object as a string
     */
    function api_json(): string
    {
        return $this->api_obj()->get_json();
    }

    /**
     * map a figure api json to this model figure object
     * @param array $api_json the api array with the figure values that should be mapped
     */
    function set_by_api_json(array $api_json): user_message
    {
        $usr_msg = new user_message();

        if ($api_json[json_fields::ID] > 0) {
            $val = new value($this->user());
            $usr_msg->add($val->set_by_api_json($api_json));
            if ($usr_msg->is_ok()) {
                $this->obj = $val;
            }
        } else {
            $res = new result($this->user());
            $api_json[json_fields::ID] = $api_json[json_fields::ID] * -1;
            $usr_msg->add($res->set_by_api_json($api_json));
            if ($usr_msg->is_ok()) {
                $this->obj = $res;
            }
        }
        return $usr_msg;
    }



    /*
     * classification
     */

    /**
     * @return bool true if the value has been calculated and not set by a user
     */
    function is_result(): bool
    {
        if ($this->obj()::class == result::class) {
            return true;
        } else {
            return false;
        }
    }


    /*
     * debug
     */

    /**
     * @return string the unique id fields of a figure mainly for debugging
     */
    function dsp_id(): string
    {

        $result = '';
        if ($this->is_result()) {
            $result .= 'result figure ';
        } else {
            $result .= 'value figure ';
        }
        if (isset($this->obj)) {
            $result .= $this->obj->dsp_id();
        }
        if ($this->last_update() != null) {
            $result .= ' ' . $this->last_update()->format('Y-m-d H:i:s');
        }

        return $result;
    }

    /**
     * @return string the created name of a figure
     */
    function name(): string
    {

        $result = ' ' . $this->number();
        $result .= ' ' . $this->symbol();
        if (isset($this->obj)) {
            $result .= $this->obj->name();
        }

        return $result;
    }

}