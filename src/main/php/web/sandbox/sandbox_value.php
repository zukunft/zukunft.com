<?php

/*

    web/user/sandbox_value.php - the superclass for the html frontend of value sandbox objects
    ---------------------------

    This superclass should be used by the classes value_dsp, result_dsp, ...


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

namespace html\sandbox;

include_once WEB_SANDBOX_PATH . 'sandbox.php';
include_once WEB_SANDBOX_PATH . 'db_object.php';
include_once WEB_GROUP_PATH . 'group.php';
include_once WEB_PHRASE_PATH . 'phrase_list.php';
include_once WEB_USER_PATH . 'user_message.php';
include_once SHARED_PATH . 'json_fields.php';

use html\group\group;
use html\phrase\phrase_list;
use html\user\user_message;
use shared\json_fields;

class sandbox_value extends sandbox
{

    private group $grp; // the phrase group with the list of words and triples (not the source words and triples)
    private ?float $number; // the number calculated by the system

    // true if the user has done no personal overwrites which is the default case
    public bool $is_std;


    /*
     * construct and map
     */

    /**
     * the html display object are always filled base on the api message
     * @param string|null $api_json the api message to set all object vars
     */
    function __construct(?string $api_json = null)
    {
        $this->set_grp(new group());
        parent::__construct($api_json);
    }


    /*
     * set and get
     */

    function set_grp(group $grp): void
    {
        $this->grp = $grp;
    }

    function set_number(?float $number): void
    {
        $this->number = $number;
    }

    function set_is_std(bool $is_std = true): void
    {
        $this->is_std = $is_std;
    }

    function grp(): group
    {
        return $this->grp;
    }

    function number(): ?float
    {
        return $this->number;
    }

    /**
     * @return bool false if the loaded value is user specific
     */
    function is_std(): bool
    {
        return $this->is_std;
    }

    /**
     * @returns phrase_list the list of phrases as an object
     */
    function phr_lst(): phrase_list
    {
        return $this->grp()->phr_lst();
    }


    /**
     * set the vars of this object bases on the api json array
     * @param array $json_array an api json message
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function set_from_json_array(array $json_array): user_message
    {
        $usr_msg = new user_message();
        if (array_key_exists(json_fields::ID, $json_array)) {
            $this->set_id($json_array[json_fields::ID]);
        } else {
            $this->set_id(0);
            $usr_msg->add_err('Mandatory field id missing in API JSON ' . json_encode($json_array));
        }
        if (array_key_exists(json_fields::NUMBER, $json_array)) {
            $this->set_number($json_array[json_fields::NUMBER]);
        } else {
            $this->set_number(null);
        }
        if (array_key_exists(json_fields::IS_STD, $json_array)) {
            $this->set_is_std($json_array[json_fields::IS_STD]);
        } else {
            $this->set_is_std();
        }
        $this->set_grp(new group());
        if (array_key_exists(json_fields::PHRASES, $json_array)) {
            $this->grp()->set_from_json_array($json_array[json_fields::PHRASES]);
        } else {
            $usr_msg->add_err('Mandatory field phrase group missing in API JSON ' . json_encode($json_array));
        }
        return $usr_msg;
    }


    /*
     * display
     */

    /**
     * @returns string the html code to display the value with reference links
     * TODO create a popup with the details e.g. the values of other users
     */
    function value_linked(): string
    {
        return $this->number;
    }

    /**
     * depending on the word list format the numeric value
     * format the value for on screen display
     * similar to the corresponding function in the "result" class
     * @returns string the html text with the formatted value
     */
    function val_formatted(): string
    {
        global $usr;
        $result = '';

        // TODO check that the phrases are set

        if (!$this->is_null()) {
            if ($this->is_percent()) {
                $result = round($this->number() * 100, $usr->percent_decimals) . "%";
            } else {
                if ($this->number() >= 1000 or $this->number() <= -1000) {
                    $result .= number_format($this->number(), 0, $usr->dec_point, $usr->thousand_sep);
                } else {
                    $result = round($this->number(), 2);
                }
            }
        }
        return $result;
    }


    /*
     * info
     */

    /**
     * @return bool true if one of the phrases that classify this value is of type percent
     */
    function is_percent(): bool
    {
        if ($this->grp()->has_percent()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return bool true if the value is not available
     */
    function is_null(): bool
    {
        if ($this->number() == null) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return bool if the id of the group is valid
     */
    function is_id_set(): bool
    {
        if ($this->id() != 0) {
            return true;
        } else {
            return false;
        }
    }

}


