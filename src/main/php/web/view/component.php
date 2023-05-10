<?php

/*

    /web/view/component.php - the display extension of the api component object
    -----------------------

    to creat the HTML code to display a component


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

namespace html\view;

include_once WEB_SANDBOX_PATH . 'sandbox_typed.php';

use controller\controller;
use html\api;
use html\html_base;
use html\phrase\phrase as phrase_dsp;
use html\phrase\term as term_dsp;
use html\sandbox_typed_dsp;
use model\view_cmp_type;

class component extends sandbox_typed_dsp
{

    /*
     * set and get
     */

    /**
     * repeat here the sandbox object function to force to include all component object fields
     * @param array $json_array an api single object json message
     * @return void
     */
    function set_obj_from_json_array(array $json_array): void
    {
        $wrd = new component();
        $wrd->set_from_json_array($json_array);
    }

    /**
     * set the vars this component bases on the api json array
     * public because it is reused e.g. by the phrase group display object
     * @param array $json_array an api json message
     * @return void
     */
    function set_from_json_array(array $json_array): void
    {
        parent::set_from_json_array($json_array);
        if (array_key_exists(controller::API_FLD_CODE_ID, $json_array)) {
            $this->code_id = $json_array[controller::API_FLD_CODE_ID];
        } else {
            $this->code_id = null;
        }
    }


    /*
     * display
     */

    /**
     * TODO review these simplified function
     * @return string
     */
    function display(): string
    {
        return $this->name();
    }

    /**
     * TODO review these simplified function
     * @return string
     */
    function display_linked(): string
    {
        return $this->name();
    }

    /**
     * @return string a fixed text
     */
    function text(): string
    {
        $f = $this->type_code_id();
        if ($this->type_code_id() == view_cmp_type::TEXT) {
            return $this->name();
        } else {
            return '';
        }
    }

    /**
     * @return string the name of a phrase and give the user the possibility to change the phrase name
     */
    function word_name(phrase_dsp $phr): string
    {
        if ($this->type_code_id() == view_cmp_type::PHRASE_NAME) {
            return $phr->name();
        } else {
            return '';
        }
    }

    /**
     * TODO move code from view_cmp_dsp_old
     * @return string a dummy text
     */
    function table(): string
    {
        if ($this->type_code_id() == view_cmp_type::VALUES_RELATED) {
            return $this->name();
        } else {
            return '';
        }
    }

    /**
     * TODO move code from view_cmp_dsp_old
     * @return string a dummy text
     */
    function num_list(): string
    {
        if ($this->type_code_id() == view_cmp_type::WORD_VALUE) {
            return $this->name();
        } else {
            return '';
        }
    }

    /**
     * TODO move code from view_cmp_dsp_old
     * @return string a dummy text
     */
    function formulas(): string
    {
        if ($this->type_code_id() == view_cmp_type::FORMULAS) {
            return $this->name();
        } else {
            return '';
        }
    }

    /**
     * TODO move code from view_cmp_dsp_old
     * @return string a dummy text
     */
    function results(): string
    {
        if ($this->type_code_id() == view_cmp_type::FORMULA_RESULTS) {
            return $this->name();
        } else {
            return '';
        }
    }

    /**
     * TODO move code from view_cmp_dsp_old
     * @return string a dummy text
     */
    function word_children(): string
    {
        if ($this->type_code_id() == view_cmp_type::WORDS_DOWN) {
            return $this->name();
        } else {
            return '';
        }
    }

    /**
     * TODO move code from view_cmp_dsp_old
     * @return string a dummy text
     */
    function word_parents(): string
    {
        if ($this->type_code_id() == view_cmp_type::WORDS_UP) {
            return $this->name();
        } else {
            return '';
        }
    }

    /**
     * TODO move code from view_cmp_dsp_old
     * @return string a dummy text
     */
    function json_export(): string
    {
        global $view_component_types;
        if ($view_component_types->code_id($this->type_id()) == view_cmp_type::JSON_EXPORT) {
            return $this->name();
        } else {
            return '';
        }
    }

    /**
     * TODO move code from view_cmp_dsp_old
     * @return string a dummy text
     */
    function xml_export(): string
    {
        global $view_component_types;
        if ($view_component_types->code_id($this->type_id()) == view_cmp_type::XML_EXPORT) {
            return $this->name();
        } else {
            return '';
        }
    }

    /**
     * TODO move code from view_cmp_dsp_old
     * @return string a dummy text
     */
    function csv_export(): string
    {
        global $view_component_types;
        if ($view_component_types->code_id($this->type_id()) == view_cmp_type::CSV_EXPORT) {
            return $this->name();
        } else {
            return '';
        }
    }

    /**
     * TODO move code from view_cmp_dsp_old
     * @return string a dummy text
     */
    function all(): string
    {
        global $view_component_types;
        if ($view_component_types->code_id($this->type_id()) == view_cmp_type::VALUES_ALL) {
            return $this->name();
        } else {
            return '';
        }
    }


    function type_code_id(): string
    {
        global $view_component_types;
        return $view_component_types->code_id($this->type_id());
    }


    /*
      * interface
      */

    /**
     * @return array the json message array to send the updated data to the backend
     * an array is used (instead of a string) to enable combinations of api_array() calls
     */
    function api_array(): array
    {
        $vars = parent::api_array();
        return array_filter($vars, fn($value) => !is_null($value));
    }

}
