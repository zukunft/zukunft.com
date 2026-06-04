<?php

/*

    web/ref/ref.php - the extension of the reference API objects to create ref base html code
    ---------------

    extends db_object because this is the only display object that does not have an explicit name but has a type


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
include_once html_paths::HTML . 'html_selector.php';
include_once html_paths::PHRASE . 'phrase.php';
include_once html_paths::PHRASE . 'phrase_list.php';
include_once html_paths::TYPES . 'type_lists.php';
include_once html_paths::TYPES . 'ref_type.php';
include_once html_paths::SANDBOX . 'sandbox.php';
include_once html_paths::USER . 'user_message.php';
include_once html_paths::WORD . 'triple.php';
include_once html_paths::WORD . 'word.php';
include_once html_paths::VIEW . 'view_list.php';
include_once html_paths::REF . 'source.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_TYPES . 'view_styles.php';
include_once paths::SHARED_TYPES . 'view_types.php';
include_once paths::SHARED . 'url_var.php';
include_once paths::SHARED . 'json_fields.php';

use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\html\html_selector;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\web\sandbox\sandbox;
use Zukunft\ZukunftCom\main\php\web\types\type_lists;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\view\view_list;
use Zukunft\ZukunftCom\main\php\web\word\triple;
use Zukunft\ZukunftCom\main\php\web\word\word;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\types\view_styles;
use Zukunft\ZukunftCom\main\php\shared\types\view_types;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\shared\json_fields;

class ref extends sandbox
{

    /*
     * const
     */

    // crud views
    const string VIEW_ADD = views::REF_ADD;
    const string VIEW_EDIT = views::REF_EDIT;
    const string VIEW_DEL = views::REF_DEL;
    const int VIEW_EDIT_ID = views::REF_EDIT_ID;

    // curl message id
    const msg_id MSG_ADD = msg_id::REF_ADD;
    const msg_id MSG_EDIT = msg_id::REF_EDIT;
    const msg_id MSG_DEL = msg_id::REF_DEL;


    /*
     * object vars
     */

    private ?phrase $phr;
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

    /**
     * set the vars of this reference frontend object bases on the url array
     * @param array $url_array an array based on $_GET from a form submit
     * @param user_message $usr_msg to enrich with warnings, problems and solutions
     * @param data_object|null $dto the cache as a parameter to be able to simulate test conditions
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function url_mapper(array $url_array, user_message $usr_msg, data_object|null $dto = null): user_message
    {
        parent::url_mapper($url_array, $usr_msg, $dto);
        if ($usr_msg->is_ok()) {
            if (array_key_exists(url_var::PHRASE, $url_array)) {
                $this->set_phrase_by_id($url_array[url_var::PHRASE]);
            } else {
                $this->phr = null;
            }
            if (array_key_exists(url_var::SOURCE, $url_array)) {
                $this->set_source_by_id($url_array[url_var::PHRASE]);
            } else {
                $this->source = null;
            }
            if (array_key_exists(url_var::EXTERNAL_KEY, $url_array)) {
                $this->external_key = $url_array[url_var::EXTERNAL_KEY];
            } else {
                $this->external_key = '';
            }
            if (array_key_exists(url_var::URL, $url_array)) {
                $this->url = $url_array[url_var::URL];
            } else {
                $this->url = null;
            }
            if (array_key_exists(url_var::TYPE, $url_array)) {
                $this->set_predicate_id($url_array[url_var::TYPE]);
            } else {
                $this->set_predicate_id();
            }
            if (array_key_exists(url_var::DESCRIPTION, $url_array)) {
                $this->description = $url_array[url_var::DESCRIPTION];
            } else {
                $this->description = null;
            }
        }
        return $usr_msg;
    }


    /*
     * set and get
     */

    /**
     * set the vars of this source frontend object bases on the api json array
     * because called from the constructor the null value must be set if the parameter is missing
     * @param array $json_array an api json message
     * @param user_message $msg ok or a warning e.g. if the server version does not match
     * @return bool true if the mapping has been completed successfully
     */
    function api_mapper(array $json_array, user_message $msg): bool
    {
        parent::api_mapper($json_array, $msg);
        if (array_key_exists(json_fields::PHRASE_ID, $json_array)) {
            $phr = new phrase();
            $wrd = new word();
            $phr->set_obj($wrd);
            $phr->set_id($json_array[json_fields::PHRASE_ID]);
            $this->phr = $phr;
        } elseif (array_key_exists(json_fields::PHRASES, $json_array)) {
            $phr = new phrase();
            $phr_lst_json = $json_array[json_fields::PHRASES];
            if (count($phr_lst_json) > 1) {
                log_warning('reference ' . json_encode($json_array) . 'is not expected to have more than on phrase');
            } elseif (count($phr_lst_json) < 1) {
                log_warning('reference ' . json_encode($json_array)  . 'is not expected to have no phrase');
            } else {
                $phr = new phrase();
                $phr_json = $phr_lst_json[0];
                $phr->api_mapper($phr_json, $msg);
            }
            $this->phr = $phr;
        } else {
            $this->phr = null;
        }
        if (array_key_exists(json_fields::SOURCE_ID, $json_array)) {
            $this->set_source_by_id($json_array[json_fields::SOURCE_ID]);
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
        if (array_key_exists(json_fields::PREDICATE_ID, $json_array)) {
            $this->set_predicate_id($json_array[json_fields::PREDICATE_ID]);
        } else {
            $this->set_predicate_id();
        }
        if (array_key_exists(json_fields::DESCRIPTION, $json_array)) {
            $this->description = $json_array[json_fields::DESCRIPTION];
        } else {
            $this->description = null;
        }
        return $msg->is_ok();
    }

    function name(): string|null
    {
        return $this->source_name() . ' ' . $this->external_key();
    }

    function set_phrase(?phrase $phr = null): void
    {
        $this->phr = $phr;
    }

    function phrase(): phrase
    {
        return $this->phr;
    }

    private function set_phrase_by_id(int $id): void
    {
        if ($id > 0) {
            $wrd = new word();
            $wrd->set_id($id);
            $phr = $wrd->phrase();
        } elseif ($id < 0) {
            $trp = new triple();
            $trp->set_id($id * -1);
            $phr = $trp->phrase();
        } else {
            $wrd = new word();
            $wrd->set_id(0);
            $phr = $wrd->phrase();
        }
        $this->phr = $phr;
    }

    function source(): ?source
    {
        return $this->source;
    }

    function source_name(): ?string
    {
        return $this->source?->name();
    }

    private function set_source_by_id(int $id): void
    {
        $src = new source();
        $src->set_id($id);
        $this->source = $src;
    }

    /**
     * @return string the name of the reference type e.g. wikidata
     */
    function type_name(): string
    {
        global $ui_sys;
        return $ui_sys->typ_lst_cache->html_ref_types->name($this->predicate_id());
    }

    /**
     * @return string the url of the reference type e.g. https://www.wikidata.org/wiki/
     */
    function type_url(): string
    {
        global $ui_sys;
        $ref_typ_lst = $ui_sys->typ_lst_cache->html_ref_types;
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
    function get_description(): string
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
        $vars[json_fields::PHRASE_ID] = $this->phr?->id();
        $vars[json_fields::SOURCE_ID] = $this->source?->id();
        $vars[json_fields::URL] = $this->url();
        $vars[json_fields::EXTERNAL_KEY] = $this->external_key();
        $vars[json_fields::PREDICATE_ID] = $this->predicate_id();
        $vars[json_fields::DESCRIPTION] = $this->get_description();
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
                $this->get_description()
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
     * to select the word or triple
     * @param phrase_list $phr_lst a preloaded list of suggested phrases for the selection if no additional input is given from the user
     * @param string $name the unique name within the html form for this selector
     * @param string $form the name of the html form
     * @param int|null $selected the row id of the suggested phrase or the already selected phrase
     * @param string $pattern the pattern to filter the phrases
     * @param msg_id $label_id the translation id for the text show to the user
     * @param string $style the style code e.g. to define the target width
     * @return string the html code to select the phrase
     */
    function phrase_selector(
        phrase_list $phr_lst,
        string      $name,
        string      $form,
        ?int        $selected = null,
        string      $pattern = '',
        msg_id      $label_id = msg_id::FORM_SELECT_PHRASE,
        string      $style = view_styles::COL_SM_4
    ): string
    {
        if ($phr_lst == null) {
            $phr_lst = new phrase_list();
        }
        return $phr_lst->selector($form, $selected, $name, $label_id, $style, html_selector::TYPE_DATALIST);
    }

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

    /**
     * @param string $form
     * @param string $pattern
     * @param source_list|null $src_lst the frontend cache with the configuration, the preloaded source and the cached objects
     * @return string
     */
    function source_selector(string $form, string $pattern, ?source_list $src_lst): string
    {
        // TODO review and maybe use test_mode parameter
        if ($pattern != '') {
            $src_lst->load_like($pattern);
        }
        return $src_lst->selector($form, $this->id(), url_var::SOURCE,  msg_id::FORM_SELECT_SOURCE);
    }


    /*
     * select
     */

    /**
     * create the HTML code to select a view usable for a source
     * @param string $form the name of the html form
     * @param view_list $msk_lst with all suggested views
     * @param string $name the unique html field name for the selection of the view
     * @return string the html code to select a view
     */
    public function view_selector(
        string    $form,
        view_list $msk_lst,
        string    $name = url_var::VIEW,
        msg_id    $msg_id = msg_id::FORM_SELECT_VIEW
    ): string
    {
        $view_id = $this->view_id();
        if ($view_id == null) {
            $view_id = $msk_lst->default_id($this);
        }
        $msk_lst = $msk_lst->only_type(view_types::REF);
        return $msk_lst->selector($form, $view_id, $name, $msg_id);
    }

    /**
     * @return string that best describes this object
     */
    function display(): string
    {
        return $this->name();
    }

}
