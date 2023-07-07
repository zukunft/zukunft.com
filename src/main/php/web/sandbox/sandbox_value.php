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

namespace html;

include_once WEB_SANDBOX_PATH . 'db_object.php';

use api\api;
use api\sandbox_api;
use api\sandbox_value_api;
use controller\controller;
use html\phrase\phrase_list as phrase_list_dsp;
use html\phrase\phrase_group as phrase_group_dsp;
use html\sandbox\db_object as db_object_dsp;

class sandbox_value_dsp extends db_object_dsp
{

    private phrase_group_dsp $grp; // the phrase group with the list of words and triples (not the source words and triples)
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
        $this->set_grp(new phrase_group_dsp());
        parent::__construct($api_json);
    }


    /*
     * set and get
     */

    function set_grp(phrase_group_dsp $grp): void
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

    function grp(): phrase_group_dsp
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
     * @returns phrase_list_dsp the list of phrases as an object
     */
    function phr_lst(): phrase_list_dsp
    {
        return $this->grp()->phr_lst();
    }


    /**
     * set the vars of this object bases on the api json array
     * @param array $json_array an api json message
     * @return void
     */
    function set_from_json_array(array $json_array): void
    {
        if (array_key_exists(api::FLD_ID, $json_array)) {
            $this->set_id($json_array[api::FLD_ID]);
        } else {
            $this->set_id(0);
            log_err('Mandatory field id missing in API JSON ' . json_encode($json_array));
        }
        if (array_key_exists(sandbox_value_api::FLD_NUMBER, $json_array)) {
            $this->set_number($json_array[sandbox_value_api::FLD_NUMBER]);
        } else {
            $this->set_number(null);
        }
        if (array_key_exists(api::FLD_IS_STD, $json_array)) {
            $this->set_is_std($json_array[api::FLD_IS_STD]);
        } else {
            $this->set_is_std();
        }
        if (array_key_exists(api::FLD_PHRASES, $json_array)) {
            $this->grp()->set_from_json_array($json_array[api::FLD_PHRASES]);
        } else {
            $this->set_grp(new phrase_group_dsp());
            log_err('Mandatory field phrase group missing in API JSON ' . json_encode($json_array));
        }
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

}


