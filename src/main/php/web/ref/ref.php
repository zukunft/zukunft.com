<?php

/*

    /web/ref/ref.php - the extension of the reference API objects to create ref base html code
    ----------------

    This file is part of the frontend of zukunft.com - calc with refs

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

namespace html\ref;

use controller\controller;
use html\db_object_dsp;
use html\phrase\phrase as phrase_dsp;
use html\word\word as word_dsp;
use html\ref\source as source_dsp;
use html\sandbox_typed_dsp;
use html\word\word;

class ref extends sandbox_typed_dsp
{

    /*
     * object vars
     */

    public ?phrase_dsp $phr;
    private ?source $source;
    private ?string $external_key; // maybe use field name instead
    public ?int $type_id;
    public ?string $url;


    /*
     * set and get
     */

    /**
     * set the vars of this source frontend object bases on the api json array
     * because called from the constructor the null value must be set if the parameter is missing
     * @param array $json_array an api json message
     * @return void
     */
    function set_from_json_array(array $json_array): void
    {
        parent::set_from_json_array($json_array);
        if (array_key_exists(controller::API_FLD_PHRASE, $json_array)) {
            $phr = new phrase_dsp();
            $wrd = new word_dsp();
            $phr->set_obj($wrd);
            $phr->set_id($json_array[controller::API_FLD_PHRASE]);
            $this->phr = $phr;
        } else {
            $this->phr = null;
        }
        if (array_key_exists(controller::API_FLD_SOURCE, $json_array)) {
            $src = new source_dsp();
            $src->set_id($json_array[controller::API_FLD_SOURCE]);
            $this->source = $src;
        } else {
            $this->source = null;
        }
        if (array_key_exists(controller::API_FLD_EXTERNAL_KEY, $json_array)) {
            $this->set_external_key($json_array[controller::API_FLD_EXTERNAL_KEY]);
        } else {
            $this->set_external_key(null);
        }
        if (array_key_exists(controller::API_FLD_URL, $json_array)) {
            $this->set_url($json_array[controller::API_FLD_URL]);
        } else {
            $this->set_url(null);
        }
    }

    /**
     * @return string the name of the reference type e.g. wikidata
     */
    function type_name(): string
    {
        global $ref_types;
        return $ref_types->name($this->type_id);
    }

    function set_external_key(?string $external_key): void
    {
        $this->external_key = $external_key;
    }

    function external_key(): ?string
    {
        return $this->external_key;
    }

    function set_url(?string $url): void
    {
        $this->url = $url;
    }

    function url(): ?string
    {
        return $this->url;
    }


    /*
     * display
     */

    /**
     * @returns string simply the ref name, but later with mouse over that shows the description
     */
    function display(): string
    {
        return $this->type_name() . ' ' . $this->external_key;
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
        $vars[controller::API_FLD_URL] = $this->url();
        $vars[controller::API_FLD_EXTERNAL_KEY] = $this->external_key();
        $vars[controller::API_FLD_PHRASE] = $this->phr->id();
        $vars[controller::API_FLD_SOURCE] = $this->source->id();
        return $vars;
    }

}
