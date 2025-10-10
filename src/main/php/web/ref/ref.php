<?php

/*

    web/ref/ref.php - the extension of the reference API objects to create ref base html code
    ---------------

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

namespace Zukunft\ZukunftCom\main\php\web\ref;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::HELPER . 'data_object.php';
include_once html_paths::HTML . 'html_base.php';
include_once html_paths::TYPES . 'type_lists.php';
include_once html_paths::PHRASE . 'phrase.php';
include_once html_paths::TYPES . 'ref_type.php';
include_once html_paths::SANDBOX . 'db_object.php';
include_once html_paths::USER . 'user_message.php';
include_once html_paths::WORD . 'word.php';
include_once html_paths::REF . 'source.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED . 'json_fields.php';

use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase as phrase_dsp;
use Zukunft\ZukunftCom\main\php\web\types\type_lists;
use Zukunft\ZukunftCom\main\php\web\sandbox\db_object as db_object_dsp;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\word\word as word_dsp;
use Zukunft\ZukunftCom\main\php\web\ref\source as source_dsp;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\json_fields;

class ref extends db_object_dsp
{

    /*
     * const
     */

    // crud views
    const string VIEW_ADD = views::REF_ADD;
    const string VIEW_EDIT = views::REF_EDIT;
    const string VIEW_DEL = views::REF_DEL;

    // curl message id
    const msg_id MSG_ADD = msg_id::REF_ADD;
    const msg_id MSG_EDIT = msg_id::REF_EDIT;
    const msg_id MSG_DEL = msg_id::REF_DEL;


    /*
     * object vars
     */

    private ?phrase_dsp $phr;
    private ?source $source = null;
    private ?string $url = null {
        set {
            $this->url = $value;
        }
    }
    private ?string $external_key = null {
        set {
            $this->external_key = $value;
        }
    } // maybe use field name instead
    private ?int $predicate_id;
    // the mouse over tooltip for the named object e.g. word, triple, formula, verb, view or component
    public ?string $description = null {
        set {
            $this->description = $value;
        }
    }


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
    function api_mapper(array $json_array): user_message
    {
        $usr_msg = parent::api_mapper($json_array);
        if (array_key_exists(json_fields::PHRASE, $json_array)) {
            $phr = new phrase_dsp();
            $wrd = new word_dsp();
            $phr->set_obj($wrd);
            $phr->set_id($json_array[json_fields::PHRASE]);
            $this->phr = $phr;
        } elseif (array_key_exists(json_fields::PHRASES, $json_array)) {
            $phr = new phrase_dsp();
            $phr_lst_json = $json_array[json_fields::PHRASES];
            if (count($phr_lst_json) > 1) {
                log_warning('reference ' . json_encode($json_array) . 'is not expected to have more than on phrase');
            } elseif (count($phr_lst_json) < 1) {
                log_warning('reference ' . json_encode($json_array)  . 'is not expected to have no phrase');
            } else {
                $phr = new phrase_dsp();
                $phr_json = $phr_lst_json[0];
                $phr->api_mapper($phr_json);
            }
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
            $this->external_key = $json_array[json_fields::EXTERNAL_KEY];
        } else {
            $this->external_key = null;
        }
        if (array_key_exists(json_fields::URL, $json_array)) {
            $this->url = $json_array[json_fields::URL];
        } else {
            $this->url = null;
        }
        if (array_key_exists(json_fields::PREDICATE, $json_array)) {
            $this->set_predicate_id($json_array[json_fields::PREDICATE]);
        } else {
            $this->set_predicate_id();
        }
        if (array_key_exists(json_fields::DESCRIPTION, $json_array)) {
            $this->description = $json_array[json_fields::DESCRIPTION];
        } else {
            $this->description = null;
        }
        return $usr_msg;
    }

    function name(): string
    {
        return $this->source_name() . ' ' . $this->external_key();
    }

    function set_phrase(?phrase_dsp $phr = null): void
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

    function source_name(): ?string
    {
        return $this->source?->name();
    }

    /**
     * @return string the name of the reference type e.g. wikidata
     */
    function type_name(): string
    {
        global $ref_typ_cac;
        return $ref_typ_cac->name($this->predicate_id());
    }

    /**
     * @return string the url of the reference type e.g. https://www.wikidata.org/wiki/
     */
    function type_url(): string
    {
        global $ui_cac;
        $ref_typ_lst = $ui_cac->typ_lst_cache->html_ref_types;
        return $ref_typ_lst->url($this->predicate_id());
    }

    function used_url(): string
    {
        if ($this->url() != null) {
            return $this->url();
        } else {
            return $this->type_url();
        }
    }

    function external_key(): ?string
    {
        return $this->external_key;
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
     * api
     */

    /**
     * @return array the json message array to send the updated data to the backend
     * an array is used (instead of a string) to enable combinations of api_array() calls
     */
    function api_array(): array
    {
        $vars = parent::api_array();
        $vars[json_fields::PHRASE] = $this->phr?->id();
        $vars[json_fields::SOURCE] = $this->source?->id();
        $vars[json_fields::URL] = $this->url();
        $vars[json_fields::EXTERNAL_KEY] = $this->external_key();
        $vars[json_fields::PREDICATE] = $this->predicate_id();
        $vars[json_fields::DESCRIPTION] = $this->description();
        return array_filter($vars, fn($value) => !is_null($value) && $value !== '');
    }


    /*
     * base
     */

    /**
     * @returns string simply the ref name, but later with mouse over that shows the description
     */
    function name_tip(): string
    {
        return $this->type_name() . ' ' . $this->external_key();
    }

    /**
     * @returns string simply the ref name, but later with mouse over that shows the description
     */
    function name_link(): string
    {
        $html = new html_base();
        $url = $this->used_url();
        if ($url != null) {
            return $html->url_ex(
                $url,
                $this->external_key(),
                $this->type_name(),
                $this->description()
            );
        } else {
            return 'ERROR: url is null';
        }
    }


    /*
     * info
     */

    function has_phrase(phrase $phr): bool
    {
        $result = false;
        if ($this->phr->id() == $phr->id()) {
            $result = true;
        }
        return $result;
    }


    /*
     * select
     */

    /**
     * @param string $form
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string
     */
    public function ref_type_selector(string $form, ?type_lists $typ_lst): string
    {
        $used_ref_type_id = $this->predicate_id();
        if ($used_ref_type_id == null) {
            $used_ref_type_id = $typ_lst->html_ref_types->default_id();
        }
        return $typ_lst->html_ref_types->selector($form, $used_ref_type_id);
    }

}
