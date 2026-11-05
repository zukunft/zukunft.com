<?php

/*

    web/sandbox/ListBase.php - the superclass for html list objects
    ------------------------

    e.g. used to display phrase, term and figure lists

    The main sections of this object are
    - construct and map: including the mapping of the api message to this list object
    - set and get:       for fast detection of pending backend updates
    - api:               create an api array for the backend to update the database
    - load:              update the list using the backend api
    - modify:            update the list based on the function parameters
    - info:              just the make the code easier to read
    - html:              create the html code to show this list to the user in different forms
    - select:            create the html code to select on of the elements from the list


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

namespace Zukunft\ZukunftCom\main\php\web\sandbox;

use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::API_OBJECT . 'api_message.php';
include_once html_paths::HTML . 'html_base.php';
include_once html_paths::HTML . 'html_selector.php';
include_once html_paths::HTML . 'rest_call.php';
include_once html_paths::PHRASE . 'phrase.php';
include_once html_paths::PHRASE . 'phrase_list.php';
//include_once html_paths::USER . 'user.php';
include_once html_paths::USER . 'user_message.php';
//include_once html_paths::VIEW . 'view.php';
//include_once html_paths::VIEW . 'view_list.php';
//include_once html_paths::SHARED_CONST . 'rest_ctrl.php';
//include_once html_paths::SHARED_CONST . 'views.php';
include_once html_paths::SHARED_ENUM . 'messages.php';
include_once html_paths::SHARED_HELPER . 'CombineObject.php';
include_once html_paths::SHARED_HELPER . 'IdObject.php';
include_once html_paths::SHARED_HELPER . 'TextIdObject.php';
include_once html_paths::SHARED_HELPER . 'ListOfIdObjects.php';
include_once html_paths::SHARED_TYPES . 'api_type_list.php';
include_once html_paths::SHARED_TYPES . 'view_styles.php';
include_once html_paths::SHARED_TYPES . 'view_types.php';
include_once html_paths::SHARED . 'api.php';
include_once html_paths::SHARED . 'json_fields.php';
include_once html_paths::SHARED . 'url_var.php';
include_once html_paths::SHARED . 'library.php';

use Zukunft\ZukunftCom\main\php\api\api_message;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\html\html_selector;
use Zukunft\ZukunftCom\main\php\web\html\rest_call;
use Zukunft\ZukunftCom\main\php\web\html\rest_call as api_ui;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\web\user\user;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\view\view;
use Zukunft\ZukunftCom\main\php\shared\api;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\helper\CombineObject;
use Zukunft\ZukunftCom\main\php\shared\helper\IdObject;
use Zukunft\ZukunftCom\main\php\shared\helper\ListOfIdObjects;
use Zukunft\ZukunftCom\main\php\shared\helper\TextIdObject;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
use Zukunft\ZukunftCom\main\php\shared\types\view_styles;
use Zukunft\ZukunftCom\main\php\shared\types\view_types;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\web\view\view_list;

class ListBase extends ListOfIdObjects
{

    // error return codes
    const int CODE_ID_NOT_FOUND = -1;
    // extra entry used in a selection to separate the highlighted entries from the sorted entries
    const string SELECT_SEPARATOR = ' --- ';
    // the sql like wildcard as a load_like pattern, which the backend completes to select all objects
    const string PATTERN_ALL = '%';

    private array $hash = []; // hash list with the code id for fast selection


    /*
     * construct and map
     */

    function __construct(?string $api_json = null)
    {
        parent::__construct();
        if ($api_json != null) {
            $this->set_from_json($api_json);
        }
    }

    /**
     * TODO Prio 0 check that all missing overwrites are creating an error log message
     * set the vars of this figure list based on the given json
     * @param array $json_array an api single object json message
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function api_mapper(array $json_array): user_message
    {
        $msg = 'set_from_json_array not overwritten by child object ' . $this::class;
        log_err($msg);
        return new user_message(new user(), $msg); // the message IS the return value of this stub
    }

    /**
     * set the vars of these list display objects bases on the api json array
     * TODO Prio 1 add user_message parameter (to all function that return a user_message)
     * @param array $json_array an api list json message
     * @param db_object|IdObject|TextIdObject|CombineObject $dbo an object with a unique database id that should be added to the list
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function api_mapper_list(array $json_array, db_object|IdObject|TextIdObject|CombineObject $dbo): user_message
    {
        $msg = new user_message(); // the message IS the return value, so the caller merges it
        foreach ($json_array as $value) {
            if (is_array($value)) {
                $new = clone $dbo;
                $new->api_mapper($value, $msg);
                $this->add_obj($new, true);
            } else {
                // e.g. an api error message should not stop the rendering of the other components
                log_warning('unexpected api message part "' . $value . '" for a ' . $dbo::class . ' list');
            }
        }
        return $msg;
    }

    /**
     * map a value or result list answer, also if the answer names each phrase only once: then it is
     * an object with the phrases once and the items with only the phrase ids, and a triple in it with
     * only the ids of its from and to phrases; each of these phrases is mapped to one object that
     * every item of the answer uses, because a copy per item would not fit in the memory of the pod
     * for an answer with many items, e.g. the results of the initial effort
     *
     * @param array $json_array the json array of the answer, the list of items or the object with each phrase once
     * @param string $items_key the field of the items in the object e.g. json_fields::RESULTS
     * @param db_object|IdObject|TextIdObject|CombineObject $dbo the empty item that is cloned for each item e.g. a result
     * @param user_message $msg to report a phrase id that the phrase list of the answer does not contain
     * @return void
     */
    protected function api_mapper_with_phrases(
        array                                         $json_array,
        string                                        $items_key,
        db_object|IdObject|TextIdObject|CombineObject $dbo,
        user_message                                  $msg
    ): void
    {
        if (array_key_exists(json_fields::PHRASES, $json_array) and array_key_exists($items_key, $json_array)) {
            $phr_by_id = $this->phrases_by_id($json_array[json_fields::PHRASES], $msg);
            foreach ($json_array[$items_key] as $item) {
                $this->add_item_with_phrases($item, $phr_by_id, $dbo, $msg);
            }
        } else {
            $msg->merge($this->api_mapper_list($json_array, $dbo));
        }
    }

    /**
     * load a value or result list answer page by page while the backend names a next page, because a
     * page holds at most the row limit of the backend, so that the list of many phrases is complete;
     * the backend counts a page before it filters its rows, so a short page can still have a next one
     *
     * @param string $class the frontend list class of the api e.g. value_list::class
     * @param array $data the url vars of the api call without the page e.g. the phrase ids
     * @param user_message $msg with the requesting user and to report a problem of the api message
     * @param string $step_name the names of the phrases of the load shown below the loading text of a slow page
     * @return void
     */
    protected function load_pages(string $class, array $data, user_message $msg, string $step_name = ''): void
    {
        $rest = new rest_call();
        $page = 0;
        $has_next = true;
        while ($has_next) {
            $data[api::JSON_LIST_PAGE] = $page;
            $json_body = $rest->api_get($class, $data, $msg, $step_name);
            $msg->merge($this->api_mapper($json_body));
            $has_next = $json_body[json_fields::NEXT_PAGE] ?? false;
            $page++;
        }
    }

    /**
     * @param array $phr_json_lst the phrases of the answer, each once
     * @param user_message $msg to report a from or to phrase id that the answer does not contain
     * @return array the phrase object by phrase id with the from and to phrase of a triple
     */
    private function phrases_by_id(array $phr_json_lst, user_message $msg): array
    {
        $json_by_id = [];
        foreach ($phr_json_lst as $phr_json) {
            $json_by_id[library::phrase_id_of_json($phr_json)] = $phr_json;
        }
        $result = [];
        foreach (array_keys($json_by_id) as $phr_id) {
            $phr = new phrase();
            $phr->api_mapper($this->phrase_json_by_id($phr_id, $json_by_id, [], $msg) ?? [], $msg);
            $result[$phr_id] = $phr;
        }
        return $result;
    }

    /**
     * add one item of an answer with each phrase once and give it the shared phrase objects
     *
     * @param mixed $item the json array of the item with the phrase ids e.g. of a result
     * @param array $phr_by_id the phrase object by phrase id of the answer
     * @param db_object|IdObject|TextIdObject|CombineObject $dbo the empty item that is cloned
     * @param user_message $msg to report a phrase id that the answer does not contain
     * @return void
     */
    private function add_item_with_phrases(
        mixed                                         $item,
        array                                         $phr_by_id,
        db_object|IdObject|TextIdObject|CombineObject $dbo,
        user_message                                  $msg
    ): void
    {
        if (is_array($item)) {
            // the phrases of a value or result and the phrases a result is calculated from
            $phr_of_fld = [];
            foreach ([json_fields::PHRASES, json_fields::CONTEXT] as $fld) {
                if (array_key_exists($fld, $item)) {
                    $phr_of_fld[$fld] = $this->phrases_of_ids($item[$fld], $phr_by_id, $msg);
                    $item[$fld] = [];
                }
            }
            $new = clone $dbo;
            $new->api_mapper($item, $msg);
            $new->set_phrases_of_fields($phr_of_fld);
            $this->add_obj($new, true);
        } else {
            // e.g. an api error message should not stop the rendering of the other components
            log_warning('unexpected api message part "' . $item . '" for a ' . $dbo::class . ' list');
        }
    }

    /**
     * @param array $ids the phrase ids of an item
     * @param array $phr_by_id the phrase object by phrase id of the answer
     * @param user_message $msg to report a phrase id that the answer does not contain
     * @return array the phrase objects of the ids in the order of the ids
     */
    private function phrases_of_ids(array $ids, array $phr_by_id, user_message $msg): array
    {
        $result = [];
        foreach ($ids as $phr_id) {
            if (array_key_exists((int)$phr_id, $phr_by_id)) {
                $result[] = $phr_by_id[(int)$phr_id];
            } else {
                $msg->add(msg_id::API_PHRASE_ID_MISSING, [msg_id::VAR_ID => $phr_id]);
            }
        }
        return $result;
    }

    /**
     * @param int $phr_id the phrase id of an item or of the from or to of a triple
     * @param array $phr_by_id the phrase json of the answer by phrase id
     * @param array $path the phrase ids of the triples above, so that a triple linked to itself does not loop
     * @param user_message $msg to report a phrase id that the answer does not contain
     * @return array|null the phrase json with the from and to phrase of a triple or null if the id is unknown
     */
    private function phrase_json_by_id(int $phr_id, array $phr_by_id, array $path, user_message $msg): ?array
    {
        $result = null;
        if (!array_key_exists($phr_id, $phr_by_id)) {
            $msg->add(msg_id::API_PHRASE_ID_MISSING, [msg_id::VAR_ID => $phr_id]);
        } elseif (!in_array($phr_id, $path)) {
            $result = $phr_by_id[$phr_id];
            $sides = [json_fields::FROM => json_fields::FROM_PHRASE, json_fields::TO => json_fields::TO_PHRASE];
            foreach ($sides as $id_fld => $phr_fld) {
                $side_id = $result[$id_fld] ?? 0;
                if (!array_key_exists($phr_fld, $result) and is_numeric($side_id) and $side_id != 0) {
                    $side_json = $this->phrase_json_by_id((int)$side_id, $phr_by_id, [...$path, $phr_id], $msg);
                    if ($side_json != null) {
                        $result[$phr_fld] = $side_json;
                    }
                }
            }
        }
        return $result;
    }


    /*
     * set and get
     */

    /**
     * set the vars of these list display objects bases on the api message
     * @param string $json_api_msg an api json message as a string
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function set_from_json(string $json_api_msg): user_message
    {
        return $this->api_mapper(json_decode($json_api_msg, true));
    }

    /**
     * @returns array with the names on the db keys
     */
    function lst_key(): array
    {
        $result = array();
        foreach ($this->lst() as $sbx) {
            $result[$sbx->id()] = $sbx->name();
        }
        return $result;
    }


    /*
     * load
     */

    /**
     * TODO Prio 1 add the backend loading for all main objects and create a api and horizontal tests
     * get the list of objects that are related to the given object from the backend via api
     * e.g. to get the phrases assigned to a formula get the formula links of a formula selected by the id
     *
     * @param string $class the list class name that should be loaded e.g. formula_link_list
     * @param string $url_var the url var of the object to filter the list e.g. formula
     * @param int $id of the object to filter the list
     * @return bool true if the load has been successful
     */
    function load_by_id(string $class, string $url_var, int $id, user_message $msg): bool
    {
        $result = false;

        $data = array($url_var => $id);
        $rest = new rest_call();
        $json_body = $rest->api_get($class, $data, $msg);
        $this->api_mapper($json_body);
        if (!$this->is_empty()) {
            $result = true;
        }
        return $result;
    }

    /**
     * refresh the list by id from the backend via api
     *
     * @param user_message $msg the list class name that should be loaded e.g. formula_link_list
     * @return bool true if the reload has been successful
     */
    function reload(user_message $msg): bool
    {
        $id_lst = $this->id_lst();
        /*
        $data = array($url_var => $id);
        $rest = new rest_call();
        $json_body = $rest->api_get($class, $data);
        $this->api_mapper($json_body);
        if (!$this->is_empty()) {
            $result = true;
        }
        */
        return $msg->is_ok();
    }


    /*
     * api
     */

    /**
     * create the api json message string of this list that can be sent to the backend
     *
     * @param api_type_list|array $typ_lst configuration for the api message e.g. if phrases should be included
     * @param user|null $usr the user for whom the api message should be created which can differ from the session user
     * @return string with the api json string that should be sent to the backend
     */
    function api_json(api_type_list|array $typ_lst = [], user_message $msg = new user_message(), user|null $usr = null): string
    {
        $api_msg = new api_message();
        $pod_name = $api_msg->api_site_name();
        if (is_array($typ_lst)) {
            $typ_lst = new api_type_list($typ_lst);
        }
        $vars = $this->api_array($typ_lst, $msg);
        return $api_msg->api_json($pod_name, $this::class, $vars, $typ_lst, $usr);
    }

    /**
     * create the json array for updating the database via backend
     *
     * @return array the json message array to send the updated data to the backend
     * an array is used (instead of a string) to enable combinations of api_array($msg) calls
     */
    function api_array(api_type_list|array $typ_lst, user_message $msg): array
    {
        $result = array();
        foreach ($this->lst() as $obj) {
            if ($obj != null) {
                $result[] = $obj->api_array($typ_lst, $msg);
            }
        }
        return $result;
    }


    /*
     * load
     */

    /**
     * add the objects from the backend
     * @param string $pattern part of the name that should be used to select the objects
     * @param user_message $msg with the requesting user for whom the objects are selected
     * @return bool true if at least one object has been found
     */
    function load_like(string $pattern, user_message $msg): bool
    {
        $result = false;

        $api = new api_ui();
        $data = array();
        $data[url_var::PATTERN] = $pattern;
        $json_body = $api->api_get($this::class, $data, $msg);
        $this->api_mapper($json_body);
        if (!$this->is_empty()) {
            $result = true;
        }
        return $result;
    }


    /*
     * modify
     */

    function merge(ListBase $lst, user_message $msg): void
    {
        foreach ($lst->lst() as $phr) {
            $this->add($phr, $msg);
        }
    }

    /**
     * add one named object e.g. a word to the list, but only if it is not yet part of the list
     * @param IdObject|TextIdObject|CombineObject|null $to_add the named object e.g. a word object that should be added
     * @param user_message $msg to report which entry is double
     * @returns bool true the object has been added
     */
    function add(IdObject|TextIdObject|CombineObject|null $to_add, user_message $msg): bool
    {
        $result = false;
        if ($to_add != null) {
            $add_msg = new user_message(); // add_obj returns is_ok(), so the double check must judge only this add
            $this->add_obj($to_add, false, $add_msg);
            $msg->merge($add_msg);
            $result = true;
        }
        return $result;
    }


    /*
     * info
     */

    /**
     * @returns array with all unique ids of this list
     */
    function id_lst(): array
    {
        return parent::ids();
    }


    /*
     * html
     */

    /**
     * create the html code to show the entries below each other in a vertical list
     *
     * @param phrase_list $context_phr_lst list of phrases that should be excluded from the value name because humans would assume these phrases
     * @param array $url_arr the url vars of the calling page for the back link
     * @param string $style to define e.g. the width of the list
     * @param int|null $limit the max number of entries to show
     * @param int|null $page the offset if there are more entries that could be shown at once
     * @return string the html code to show a useful numbers of list objects
     */
    function list(
        user_message $msg,
        phrase_list  $context_phr_lst = new phrase_list(),
        array        $url_arr = [],
        string       $style = '',
        ?int         $limit = null,
        ?int         $page = null
    ): string
    {
        $result = '';

        $html = new html_base();

        foreach ($this->lst() as $obj) {
            $result .= $obj->name_link($context_phr_lst);
            $result .= $html->lf();
        }
        return $result;
    }

    /**
     * TODO Prio 1 easy move to a library function
     * @returns array with the names on the db keys
     */
    function lst_key_sort_by_name(array $highlighted = []): array
    {
        $result = $this->lst_key();
        natsort($result);

        if (!empty($highlighted)) {
            $highlightSet = array_flip($highlighted);
            $final = [];
            $remaining = [];
            $separator = [];
            $separator[0] = self::SELECT_SEPARATOR;

            foreach ($result as $key => $val) {
                if (isset($highlightSet[$val])) {
                    $final[$key] = $val;
                } else {
                    $remaining[$key] = $val;
                }
            }

            // Combine, keeping original keys
            return $final + $separator + $remaining;
        }

        return $result;
    }

    /**
     * create the html code to show the entries below each other in a vertical list
     *
     * @return string the html code to show a useful numbers of list objects
     */
    function name_text(): string
    {
        $names = [];
        foreach ($this->lst() as $obj) {
            $names[] = $obj->name();
        }
        return implode(', ', $names);
    }

    /**
     * return the database row id based on the code_id
     *
     * @param string $code_id
     * @return int the database id for the given code_id
     */
    function id(string $code_id = ''): int
    {
        $lib = new library();
        $result = 0;
        if ($code_id != '' and $code_id != null) {
            if (array_key_exists($code_id, $this->hash)) {
                $result = $this->hash[$code_id];
            } else {
                $result = self::CODE_ID_NOT_FOUND;
                log_debug('Type id not found for "' . $code_id . '" in ' . $lib->dsp_array_keys($this->hash));
            }
        } else {
            log_debug('Type code id not not set');
        }
        return $result;
    }

    /**
     * get the type object by code id (just to shorten the code)
     * @param string $code_id
     * @return view|sandbox|IdObject|TextIdObject|CombineObject|null
     */
    function get_by_code_id(string $code_id): view|sandbox|IdObject|TextIdObject|CombineObject|null
    {
        return $this->get($this->id($code_id));
    }


    /*
     * select
     */

    /**
     * create a selector for this list
     * used for words, triples, phrases, formulas, terms, views and components
     *
     * the calling function hierarchy is
     * 1. msk_lst->selector: adding the default parameters to select a view
     * 2. sbx->view_selector: adding the sandbox related parameters e.g. the default view of the object
     * 3. cmp->view_selector: adding the component specific parameters e.g. the phrase context to sort the views
     * 4. cmp->view_select: add the component and view parameters e.g. the form name and the unique name within the form
     *
     * @param string $form the html form name which must be unique within the html page
     * @param int|string|null $selected the unique database id of the object that has been selected
     * @param string $name the name of this selector which must be unique within the form
     * @param msg_id $label_id the text show to the user
     * @param string $style the formatting code to adjust the formatting
     * @returns string the html code to select a word from this list
     */
    function selector(
        string          $form = '',
        int|string|null $selected = null,
        string          $name = '',
        msg_id          $label_id = msg_id::FORM_SELECT,
        string          $style = view_styles::COL_SM_4,
        string          $type = html_selector::TYPE_SELECT
    ): string
    {
        return $this->selector_ui($form, $selected, $name, $label_id, $style, $type)->display();
    }

    /**
     * the same as selector, but as the frontend object, so that a caller can adjust what the
     * selector shows before it is rendered, e.g. the more entry of a selector that offers only
     * one selection list (see ui_list::formula_link_form)
     *
     * @param string $form the html form name which must be unique within the html page
     * @param int|string|null $selected the unique database id of the object that has been selected
     * @param string $name the name of this selector which must be unique within the form
     * @param msg_id $label_id the text show to the user
     * @param string $style the formatting code to adjust the formatting
     * @returns html_selector the selector of this list filled with the given parameters
     */
    function selector_ui(
        string          $form = '',
        int|string|null $selected = null,
        string          $name = '',
        msg_id          $label_id = msg_id::FORM_SELECT,
        string          $style = view_styles::COL_SM_4,
        string          $type = html_selector::TYPE_SELECT
    ): html_selector
    {
        $sel = new html_selector();
        if (in_array($label_id, msg_id::FORM_TYPE_SELECTOR_LABELS_SORT_BY_ALPHA_WITH_DEFAULT)) {
            // get the default selection entry
            // TODO Prio 2 move $default to a function var a
            //      this default value is only valid to select the view
            //      but it does not work for e.g. the formula selector
            if ($this::class == view_list::class) {
                if ($form == views::WORD_ADD_DETAIL or $form == views::WORD_EDIT) {
                    $default = views::WORD;
                } elseif ($form == views::VERB_ADD or $form == views::VERB_EDIT) {
                    $default = views::VERB;
                } elseif ($form == views::TRIPLE_ADD_DETAIL or $form == views::TRIPLE_EDIT) {
                    $default = views::TRIPLE;
                } elseif ($form == views::SOURCE_ADD or $form == views::SOURCE_EDIT) {
                    $default = views::SOURCE;
                } elseif ($form == views::REF_ADD or $form == views::REF_EDIT) {
                    $default = views::REF;
                } elseif ($form == views::LANGUAGE_ADD or $form == views::LANGUAGE_EDIT) {
                    $default = views::LANGUAGE;
                } elseif ($form == views::VALUE_ADD_DETAIL or $form == views::VALUE_EDIT) {
                    $default = views::VALUE;
                } elseif ($form == views::FORMULA_ADD or $form == views::FORMULA_EDIT) {
                    $default = views::FORMULA;
                } elseif ($form == views::RESULT_ADD or $form == views::RESULT_EDIT) {
                    $default = views::RESULT;
                } else {
                    $default = views::COMPLETE;
                }
            } else {
                $default = null;
            }
            if ($default != null) {
                $std = $this->get_by_code_id($default);
            } else {
                $std = null;
            }
            if ($std != null) {
                $sel->lst = $this->lst_key_sort_by_name([$std->name()]);
            } else {
                $sel->lst = $this->lst_key_sort_by_name();
            }
        } elseif (in_array($label_id, msg_id::FORM_TYPE_SELECTOR_LABELS_SORT_BY_ALPHA)) {
            $sel->lst = $this->lst_key_sort_by_name();
        } else {
            $sel->lst = $this->lst_key();
        }
        $sel->name = $name;
        $sel->form = $form;
        $sel->selected = $selected;
        $sel->label_id = $label_id;
        $sel->style = $style;
        $sel->type = $type;
        return $sel;
    }

    /**
     * create the HTML code to select a file for im- or export
     * @param string $form the name of the html form
     * @param string|null $name the suggested name of the file
     * @param array|null $lst with the suggested file names
     * @param msg_id $msg_id the label shown to the user
     * @return string the html code to select a file
     */
    public function file_selector(
        string  $form,
        ?string $name = '',
        ?array  $lst = [],
        msg_id  $msg_id = msg_id::FORM_SELECT_FILE
    ): string
    {
        global $mtr;
        $html = new html_base();
        $action = api::MAIN_SCRIPT . url_var::PAR . url_var::MASK . url_var::EQ . $form;
        $frm_str = $html->form_field('fileToUpload', $msg_id, $name, html_base::INPUT_FILE);
        $frm_str .= $html->form_submit($mtr->txt(msg_id::SYSTEM_BUTTON_IMPORT));
        $result = '<' . html_base::FORM
            . ' ' . html_base::ACTION . '="' . $action . '"'
            . ' ' . html_base::METHOD . '="' . html_base::METHOD_POST . '"'
            . ' ' . html_base::ENCTYPE . '="multipart/form-data">'
            . $html->form_user()
            . $frm_str
            . '</' . html_base::FORM . '>';
        return $result;
    }

}
