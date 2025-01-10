<?php

/*

    /web/ref/ref.php - the extension of the reference API objects to create ref base html code
    ----------------

    extends db_object_dsp because this is the only display object that does not have an explicit name but has a type


    This file is part of the frontend of zukunft.com - calc with words

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

include_once SHARED_PATH . 'json_fields.php';

include_once SHARED_PATH . 'json_fields.php';
include_once WEB_PHRASE_PATH . 'phrase.php';
include_once WEB_SANDBOX_PATH . 'db_object.php';
include_once WEB_USER_PATH . 'user_message.php';
include_once WEB_WORD_PATH . 'word.php';
include_once WEB_REF_PATH . 'source.php';

use html\sandbox\db_object as db_object_dsp;
use html\phrase\phrase as phrase_dsp;
use html\user\user_message;
use html\word\word as word_dsp;
use html\ref\source as source_dsp;
use shared\json_fields;

class ref extends db_object_dsp
{

    /*
     * object vars
     */

    private ?phrase_dsp $phr;
    private ?source $source;
    private ?string $external_key; // maybe use field name instead
    private ?string $url;
    private ?int $predicate_id;
    // the mouse over tooltip for the named object e.g. word, triple, formula, verb, view or component
    public ?string $description = null;


    /*
     * construct and map
     */

    /**
     * the html display object are always filled base on the api message
     * @param string|null $api_json the api message to set all object vars
     */
    function __construct(?string $api_json = null)
    {
        $this->set_phrase();
        $this->set_predicate_id();
        parent::__construct($api_json);
    }


    /*
     * set and get
     */

    /**
     * set the vars of this source frontend object bases on the api json array
     * because called from the constructor the null value must be set if the parameter is missing
     * @param array $json_array an api json message
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function set_from_json_array(array $json_array): user_message
    {
        $usr_msg = parent::set_from_json_array($json_array);
        if (array_key_exists(json_fields::PHRASE, $json_array)) {
            $phr = new phrase_dsp();
            $wrd = new word_dsp();
            $phr->set_obj($wrd);
            $phr->set_id($json_array[json_fields::PHRASE]);
            $this->phr = $phr;
        } else {
            $this->phr = null;
        }
        if (array_key_exists(json_fields::SOURCE, $json_array)) {
            $src = new source_dsp();
            $src->set_id($json_array[json_fields::SOURCE]);
            $this->source = $src;
        } else {
            $this->source = null;
        }
        if (array_key_exists(json_fields::EXTERNAL_KEY, $json_array)) {
            $this->set_external_key($json_array[json_fields::EXTERNAL_KEY]);
        } else {
            $this->set_external_key(null);
        }
        if (array_key_exists(json_fields::URL, $json_array)) {
            $this->set_url($json_array[json_fields::URL]);
        } else {
            $this->set_url(null);
        }
        if (array_key_exists(json_fields::PREDICATE, $json_array)) {
            $this->set_predicate_id($json_array[json_fields::PREDICATE]);
        } else {
            $this->set_predicate_id();
        }
        if (array_key_exists(json_fields::DESCRIPTION, $json_array)) {
            $this->set_description($json_array[json_fields::DESCRIPTION]);
        } else {
            $this->set_description(null);
        }
        return $usr_msg;
    }

    function set_phrase(phrase_dsp $phr = null): void
    {
        $this->phr = $phr;
    }

    function phrase(): phrase_dsp
    {
        return $this->phr;
    }

    function set_sourced(?source $src = null): void
    {
        $this->source = $src;
    }

    function source(): ?source
    {
        return $this->source;
    }

    /**
     * @return string the name of the reference type e.g. wikidata
     */
    function type_name(): string
    {
        global $ref_typ_cac;
        return $ref_typ_cac->name($this->predicate_id());
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

    function set_predicate_id(?int $type_id = null): void
    {
        $this->predicate_id = $type_id;
    }

    function predicate_id(): ?int
    {
        return $this->predicate_id;
    }

    function set_description(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return string the display value of the tooltip where null is an empty string
     */
    function description(): string
    {
        if ($this->description == null) {
            return '';
        } else {
            return $this->description;
        }
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

    /**
     * @returns string simply the ref name, but later with mouse over that shows the description
     */
    function display_linked(): string
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
        $vars[json_fields::URL] = $this->url();
        $vars[json_fields::EXTERNAL_KEY] = $this->external_key();
        $vars[json_fields::PHRASE] = $this->phr->id();
        $vars[json_fields::SOURCE] = $this->source?->id();
        $vars[json_fields::PREDICATE] = $this->predicate_id();
        $vars[json_fields::DESCRIPTION] = $this->description();
        return array_filter($vars, fn($value) => !is_null($value) && $value !== '');
    }

}
