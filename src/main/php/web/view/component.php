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
use html\sandbox\db_object as db_object_dsp;
use html\sandbox_typed_dsp;
use model\view_cmp_type;

class component extends sandbox_typed_dsp
{

    /*
     * object vars
     */

    public ?string $code_id = null;         // the entry type code id


    /*
     * display
     */

    /**
     * @param db_object_dsp $dbo the word, triple or formula object that should be shown to the user
     * @return string the html code of all view components
     */
    function dsp_entries(db_object_dsp $dbo, string $back): string
    {
        log_debug('"' . $dbo->dsp_id() . '" with the view ' . $this->dsp_id() . '"');

        $result = '';

        // list of all possible view components
        $result .= match ($this->type_code_id()) {
            view_cmp_type::TEXT => $this->text(),  // just to display a simple text
            view_cmp_type::PHRASE_NAME => $this->word_name($dbo->phrase()), // show the word name and give the user the possibility to change the word name
            view_cmp_type::VALUES_RELATED => $this->table($dbo), // display a table (e.g. ABB as first word, Cash Flow Statement as second word)
            view_cmp_type::WORD_VALUE => $this->num_list($dbo, $back), // a word list with some key numbers e.g. all companies with the PE ratio
            view_cmp_type::FORMULAS => $this->formulas($dbo), // display all formulas related to the given word
            view_cmp_type::FORMULA_RESULTS => $this->results($dbo), // show a list of formula results related to a word
            view_cmp_type::WORDS_DOWN => $this->word_children($dbo), // show all words that are based on the given start word
            view_cmp_type::WORDS_UP => $this->word_parents($dbo), // show all word that this words is based on
            view_cmp_type::JSON_EXPORT => $this->json_export($dbo, $back), // offer to configure and create an JSON file
            view_cmp_type::XML_EXPORT => $this->xml_export($dbo, $back), // offer to configure and create an XML file
            view_cmp_type::CSV_EXPORT => $this->csv_export($dbo, $back), // offer to configure and create an CSV file
            view_cmp_type::VALUES_ALL => $this->all($dbo, $back), // shows all: all words that link to the given word and all values related to the given word
            default => 'program code for component type ' . $this->type_code_id() . ' missing'
        };

        return $result;
    }

    /**
     * TODO review these simplified function
     * @return string
     */
    function display_name(): string
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
        return $this->name();
    }

    /**
     * @return string the name of a phrase and give the user the possibility to change the phrase name
     */
    function word_name(phrase_dsp $phr): string
    {
        return $phr->name();
    }

    /**
     * TODO move code from view_cmp_dsp_old
     * @return string a dummy text
     */
    function table(): string
    {
        return $this->name();
    }

    /**
     * TODO move code from view_cmp_dsp_old
     * @return string a dummy text
     */
    function num_list(): string
    {
        return $this->name();
    }

    /**
     * TODO move code from view_cmp_dsp_old
     * @return string a dummy text
     */
    function formulas(): string
    {
        return $this->name();
    }

    /**
     * TODO move code from view_cmp_dsp_old
     * @return string a dummy text
     */
    function results(): string
    {
        return $this->name();
    }

    /**
     * TODO move code from view_cmp_dsp_old
     * @return string a dummy text
     */
    function word_children(): string
    {
        return $this->name();
    }

    /**
     * TODO move code from view_cmp_dsp_old
     * @return string a dummy text
     */
    function word_parents(): string
    {
        return $this->name();
    }

    /**
     * TODO move code from view_cmp_dsp_old
     * @return string a dummy text
     */
    function json_export(): string
    {
        return $this->name();
    }

    /**
     * TODO move code from view_cmp_dsp_old
     * @return string a dummy text
     */
    function xml_export(): string
    {
        return $this->name();
    }

    /**
     * TODO move code from view_cmp_dsp_old
     * @return string a dummy text
     */
    function csv_export(): string
    {
        return $this->name();
    }

    /**
     * TODO move code from view_cmp_dsp_old
     * @return string a dummy text
     */
    function all(): string
    {
        return $this->name();
    }


    function type_code_id(): string
    {
        global $view_component_types;
        return $view_component_types->code_id($this->type_id());
    }


    /*
     * set and get
     */

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
