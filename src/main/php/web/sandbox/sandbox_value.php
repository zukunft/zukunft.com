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

use cfg\const\paths;
use html\const\paths as html_paths;
include_once html_paths::SANDBOX . 'sandbox.php';
include_once html_paths::SANDBOX . 'db_object.php';
include_once html_paths::GROUP . 'group.php';
include_once html_paths::PHRASE . 'phrase.php';
include_once html_paths::PHRASE . 'phrase_list.php';
include_once html_paths::USER . 'user_message.php';
include_once paths::SHARED . 'api.php';
include_once paths::SHARED . 'json_fields.php';

use DateTime;
use html\group\group;
use html\phrase\phrase;
use html\phrase\phrase_list;
use html\user\user_message;
use shared\api;
use shared\json_fields;

class sandbox_value extends sandbox
{

    /*
     * object vars
     */

    private group $grp; // the phrase group with the list of words and triples (not the source words and triples)
    private ?float $number = null; // the number calculated by the system
    private ?string $text_value = null; // a text value that is not expected to be included in selections
    private ?DateTime $time_value = null; // a time value
    // TODO add geo points

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

    /**
     * set the vars of this value frontend object bases on the url array
     * @param array $url_array an array based on $_GET from a form submit
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function url_mapper(array $url_array): user_message
    {
        $usr_msg = parent::url_mapper($url_array);
        if ($usr_msg->is_ok()) {
            if (array_key_exists(api::URL_VAR_PHRASE_LIST_LONG, $url_array)) {
                $id_lst = explode(',', $url_array[api::URL_VAR_PHRASE_LIST_LONG]);
                if (count($id_lst) > 0) {
                    $this->set_phrases_by_is_list($id_lst);
                }
            }
            if (array_key_exists(api::URL_VAR_NUMERIC_VALUE_LONG, $url_array)) {
                if ($url_array[api::URL_VAR_NUMERIC_VALUE_LONG] != null) {
                    $this->set_number($url_array[api::URL_VAR_NUMERIC_VALUE_LONG]);
                }
            }
        }
        return $usr_msg;
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

    function set_text_value(?string $text_value): void
    {
        $this->text_value = $text_value;
    }

    function set_time_value(?DateTime $time_value): void
    {
        $this->time_value = $time_value;
    }

    function set_is_std(bool $is_std = true): void
    {
        $this->is_std = $is_std;
    }

    function grp(): group
    {
        return $this->grp;
    }

    // TODO review (split value objects?)
    function value(): float|string|DateTime|null
    {
        if ($this->number() != null) {
            return $this->number();
        } elseif ($this->text_value() != null) {
            return $this->text_value();
        } elseif ($this->time_value() != null) {
            return $this->time_value();
        } else {
            return null;
        }
    }

    function number(): ?float
    {
        return $this->number;
    }

    function text_value(): ?string
    {
        return $this->text_value;
    }

    function time_value(): ?DateTime
    {
        return $this->time_value;
    }

    /**
     * @return bool false if the loaded value is user specific
     */
    function is_std(): bool
    {
        return $this->is_std;
    }

    /**
     * set the phrase list based on the given id list
     * @param array $id_lst with the all phrase ids for the unique identification of this value
     * @return void
     */
    function set_phrases_by_is_list(array $id_lst): void
    {
        $phr_lst = new phrase_list();
        foreach ($id_lst as $id) {
            $phr = new phrase();
            $phr->set_id($id);
            $this->grp()->add($phr);
        }
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
    function api_mapper(array $json_array): user_message
    {
        $usr_msg = parent::api_mapper($json_array);

        if (array_key_exists(json_fields::ID, $json_array)) {
            $this->set_id($json_array[json_fields::ID]);
        } else {
            $this->set_id(0);
            $usr_msg->add_err('Mandatory field id missing in API JSON ' . json_encode($json_array));
        }
        if (array_key_exists(json_fields::NUMBER, $json_array)) {
            $this->set_number($json_array[json_fields::NUMBER]);
        } elseif (array_key_exists(json_fields::TEXT_VALUE, $json_array)) {
            $this->set_text_value($json_array[json_fields::TEXT_VALUE]);
        } elseif (array_key_exists(json_fields::TIME_VALUE, $json_array)) {
            $this->set_time_value($json_array[json_fields::TIME_VALUE]);
            // TODO add geo point
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
            $this->grp()->api_mapper($json_array[json_fields::PHRASES]);
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


