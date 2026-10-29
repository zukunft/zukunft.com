<?php

/*

    model/import/update_put.php - save the changes of an update put message with the database ids
    ----------------------------

    $upd is the suggested var name

    the backend of api/updatePut e.g. for the save of the calculator editor; the message has the
    api header and in the body the format of an import file, but every object may carry its
    database id, which is used as the prime key if given
    (src/test/resources/api/calculator_editor/ has samples)

    the body of a message
    - words, triples and values: the objects that are added or changed, each with the fields that
      the client manages; an object, its phrases, its verb and its type are named by their id and
      by their name only if they have no id yet, e.g. a word that the same message adds
    - formulas: the formulas that are changed, each with its id and the expression in the user
      format e.g. the formula of a calculator row that the user has changed in the editor
    - delete: the class and id of the words, triples and values that the user removes; the del()
      of the object decides if the object is deleted or only excluded for the user
    a message whose header names another user than the requesting one is refused; the sections
    are saved in this order and the save stops at the first problem; then the cached
    pages and api answers are dropped, so that e.g. the editor reads the saved data

    The main sections of this object are
    - put:               save the changes of a message
    - internal:          the object save, the object delete, the cache drop and the id of a name


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

    Copyright (c) 1995-2026 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\main\php\cfg\import;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::API_OBJECT . 'api_message.php';
include_once paths::EXPORT . 'phrase_data.php';
include_once paths::MODEL_FORMULA . 'formula.php';
include_once paths::MODEL_HELPER . 'db_cache_page.php';
include_once paths::MODEL_PHRASE . 'phrase.php';
include_once paths::MODEL_PHRASE . 'phrase_list.php';
include_once paths::MODEL_RESULT . 'result_list.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::MODEL_VALUE . 'value.php';
include_once paths::MODEL_VALUE . 'value_list.php';
include_once paths::MODEL_WORD . 'triple.php';
include_once paths::MODEL_WORD . 'word.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_TYPES . 'api_types.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED . 'library.php';

use Zukunft\ZukunftCom\main\php\api\api_message;
use Zukunft\ZukunftCom\main\php\cfg\export\phrase_data;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula;
use Zukunft\ZukunftCom\main\php\cfg\helper\db_cache_page;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\cfg\result\result_list;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\value\value;
use Zukunft\ZukunftCom\main\php\cfg\value\value_list;
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;

class update_put
{

    // the class of the objects of each body section in the order of the save,
    // so that e.g. a triple can use a word that is added by the same message
    // and a formula a phrase that is added by the same message
    const array SECTION_CLASSES = [
        json_fields::WORDS => word::class,
        json_fields::TRIPLES => triple::class,
        json_fields::FORMULAS => formula::class,
        json_fields::VALUES => value::class,
    ];

    // the classes whose objects a message may delete; a formula is only changed, because the
    // editor that sends the message shows the formulas of a page but does not manage them
    const array DELETE_CLASSES = [
        word::class,
        triple::class,
        value::class,
    ];

    // the id field of a phrase of an object and the name field that is used if the id is missing
    const array PHRASE_NAME_FIELDS = [
        json_fields::FROM => json_fields::EX_FROM,
        json_fields::TO => json_fields::EX_TO,
    ];

    // the apis whose cached answers show the saved objects e.g. the phrase data that the editor reads after the save
    const array CACHED_APIS = [
        phrase_data::class,
        phrase_list::class,
        value_list::class,
        result_list::class,
    ];

    // what the save has done with an object of the message, confirmed by the reload of the saved row
    const string ADDED = 'added';
    const string CHANGED = 'changed';
    const string UNCHANGED = 'unchanged';
    const string REMOVED = 'removed';


    /*
     * put
     */

    /**
     * save the changes of an update put message and create the answer for the api
     *
     * @param array $json the update put message with the api header
     * @param user_message $msg with the requesting user, to report e.g. a phrase that does not exist
     * @return string the answer with the api header and the message body with the database id of each saved object
     */
    function api_json(array $json, user_message $msg): string
    {
        global $db_con;
        $lib = new library();
        $api_msg = new api_message();
        $body = $this->put($json, $msg);
        $vars = $api_msg->api_header_array(
            $api_msg->api_site_name($db_con),
            $lib->class_to_api_route(self::class),
            $msg->usr,
            $body
        );
        return json_encode($vars);
    }

    /**
     * save the changes of an update put message
     *
     * @param array $json the update put message with or without the api header
     * @param user_message $msg with the requesting user, to report e.g. a phrase that does not exist
     * @return array the body of the message with the database id of each saved object, what the save has done and the warnings of the put
     */
    function put(array $json, user_message $msg): array
    {
        $api_msg = new api_message();
        $body = $api_msg->validate($json);
        $put_msg = new user_message($msg->usr); // the problems of this put, merged into $msg below, so that only a problem of the put stops it
        $this->check_user($json, $put_msg);
        $skipped = 0;
        $result = [];
        foreach (self::SECTION_CLASSES as $section => $class) {
            $result[$section] = [];
            foreach ($body[$section] ?? [] as $obj_json) {
                if ($put_msg->is_ok()) {
                    $result[$section][] = $this->save_object($class, $obj_json, $put_msg);
                } else {
                    $skipped++;
                }
            }
        }
        $result[json_fields::DELETE] = [];
        foreach ($body[json_fields::DELETE] ?? [] as $del_json) {
            if ($put_msg->is_ok()) {
                $result[json_fields::DELETE][] = $this->delete_object($del_json, $put_msg);
            } else {
                $skipped++;
            }
        }
        if ($skipped > 0) {
            $put_msg->add(msg_id::UPDATE_PUT_SKIPPED, [msg_id::VAR_COUNTER => (string)$skipped]);
        }
        // also after a problem, because the objects before the problem are saved
        $this->drop_cached_answers();
        // the api sends the messages only for a refused put, so the warnings of a done put are part
        // of the body, and only the ones of this put, not the messages of the request before it
        $warnings = $put_msg->all_message_text();
        if ($put_msg->is_ok() and $warnings != '') {
            $result[json_fields::WARNINGS] = $warnings;
        }
        $msg->merge($put_msg);
        return $result;
    }


    /*
     * internal
     */

    /**
     * refuse a message that names another user than the requesting user in its header, e.g. the
     * edits of a page that has been loaded before a login as another user; the session decides
     * who saves, so a message without a user in the header is saved for the requesting user
     *
     * @param array $json the update put message with or without the api header
     * @param user_message $msg with the requesting user, to report the other user
     * @return void
     */
    private function check_user(array $json, user_message $msg): void
    {
        $msg_usr_id = (int)($json[json_fields::USER_ID] ?? 0);
        if ($msg_usr_id != 0 and $msg_usr_id != $msg->usr->id()) {
            $msg->add(msg_id::UPDATE_PUT_OTHER_USER, [
                msg_id::VAR_ID => (string)$msg_usr_id,
                msg_id::VAR_USER_NAME => $msg->usr->name()
            ]);
        }
    }

    /**
     * add or update one object of the message
     *
     * @param string $class the class of the object e.g. triple::class
     * @param array $obj_json the object of the message with the database ids and the names
     * @param user_message $msg with the requesting user
     * @return array the object of the message with the database id of the saved object and what the save has done
     */
    private function save_object(string $class, array $obj_json, user_message $msg): array
    {
        $dbo = new $class($msg->usr);
        $api_json = $this->with_ids($obj_json, $msg);
        if ($msg->is_ok()) {
            $dbo->api_mapper($api_json, $msg);
        }
        // only for an id of the message, because e.g. a new value has the id of its phrase group
        $db_obj = null;
        if ($msg->is_ok() and key_exists(json_fields::ID, $obj_json)) {
            $db_obj = $this->fill_from_db($dbo, $msg);
        } elseif ($msg->is_ok()) {
            // the row that the save changes instead of adding one, so that the answer never confirms it as added
            $db_obj = $this->existing($dbo, $msg);
        }
        if ($msg->is_ok()) {
            $dbo->save($msg);
        }
        $result = $obj_json;
        if ($msg->is_ok()) {
            $result = $this->confirmed($obj_json, $dbo, $db_obj, $msg);
        }
        $result[json_fields::ID] = $dbo->id();
        return $result;
    }

    /**
     * reload the saved row and compare it with the row before the save, so that the answer
     * confirms only what is in the database and a change that has not been written is reported
     *
     * @param array $obj_json the object of the message
     * @param word|triple|formula|value $dbo the saved object
     * @param word|triple|formula|value|null $db_obj the database row before the save or null for an added object
     * @param user_message $msg with the requesting user, to warn about an object that the save has not changed
     * @return array the object of the message with the action, the changed fields and if the user has an own version after the save
     */
    private function confirmed(
        array                  $obj_json,
        word|triple|formula|value      $dbo,
        word|triple|formula|value|null $db_obj,
        user_message           $msg
    ): array
    {
        $lib = new library();
        $class = $dbo::class;
        $saved = new $class($msg->usr);
        $saved->load_by_id($dbo->id(), $msg);
        // with the excluded objects, because the api json of an excluded object is empty
        $typ_lst = [api_types::WITH_EXCLUDED];
        $after = $saved->api_json_array($typ_lst, $msg);
        $before = $db_obj?->api_json_array($typ_lst, $msg) ?? [];
        $changes = [];
        foreach (array_keys($obj_json) as $fld) {
            $old = $before[$fld] ?? null;
            $new = $after[$fld] ?? null;
            if ($fld != json_fields::ID and $old !== $new) {
                $changes[] = [
                    json_fields::FIELD => $fld,
                    json_fields::OLD_VALUE => $old,
                    json_fields::NEW_VALUE => $new
                ];
            }
        }
        if ($db_obj === null) {
            $action = self::ADDED;
        } elseif ($changes == []) {
            $action = self::UNCHANGED;
            $msg->add_warning_with_vars(msg_id::UPDATE_PUT_UNCHANGED, [
                msg_id::VAR_CLASS_NAME => $lib->class_to_name($class),
                msg_id::VAR_ID => (string)$dbo->id()
            ]);
        } else {
            $action = self::CHANGED;
        }
        $result = $obj_json;
        $result[json_fields::ACTION] = $action;
        $result[json_fields::CHANGES] = $changes;
        $result[json_fields::USER_OVERWRITE] = $saved->has_usr_cfg();
        return $result;
    }

    /**
     * the row that the save of an object without id in the message changes, because the save finds
     * an existing row by the name or the linked phrases (get_similar) or a value by its phrase group
     *
     * @param word|triple|formula|value $dbo the object of the message without database id
     * @param user_message $msg with the requesting user, to report a problem of the database read
     * @return word|triple|formula|value|null the existing row or null if the save adds the object
     */
    private function existing(word|triple|formula|value $dbo, user_message $msg): word|triple|formula|value|null
    {
        $result = null;
        if ($dbo instanceof value) {
            $db_val = new value($msg->usr);
            // the id of a value is the id of its phrase group, which the load keeps also if no row is found
            if ($dbo->id() != 0 and $dbo->id() != '' and $db_val->load_by_id($dbo->id(), $msg) != 0) {
                $result = $db_val;
            }
        } else {
            $sim_msg = new user_message($msg->usr); // not reported, because a duplicate message only decides here if the row exists and the save reports it with its own check
            $sim = $dbo->get_similar($sim_msg);
            // e.g. a formula with the name of the word is not the row that the save of the word changes
            if ($sim instanceof $dbo) {
                $result = $sim;
            }
        }
        return $result;
    }

    /**
     * take over the fields that the message does not name from the database row of the object,
     * so that e.g. the type of a triple stays if the message changes only its description
     *
     * @param word|triple|formula|value $dbo the object of the message with its database id
     * @param user_message $msg with the requesting user, to report an id that does not exist
     * @return word|triple|formula|value|null the database row before the save or null if the id does not exist
     */
    private function fill_from_db(word|triple|formula|value $dbo, user_message $msg): word|triple|formula|value|null
    {
        $result = null;
        $lib = new library();
        $class = $dbo::class;
        $db_obj = new $class($msg->usr);
        // the returned id, because a value keeps its id, which is the id of its group, also if no row is found
        $found = $db_obj->load_by_id($dbo->id(), $msg) != 0;
        // only if the load has not reported another reason
        if (!$found and $msg->is_ok()) {
            $msg->add(msg_id::OBJECT_NOT_FOUND, [
                msg_id::VAR_CLASS_NAME => $lib->class_to_name($class),
                msg_id::VAR_ID => (string)$dbo->id()
            ]);
        } elseif ($found) {
            $msg->merge($dbo->fill($db_obj, $msg->usr));
            $result = $db_obj;
        }
        return $result;
    }

    /**
     * delete an object of the message or exclude it for the user, which the del() of the object decides
     *
     * @param array $del_json the class name and the database id e.g. "class": "triple", "id": 1
     * @param user_message $msg with the requesting user
     * @return array the delete entry of the message with the action, which the reload confirms
     */
    private function delete_object(array $del_json, user_message $msg): array
    {
        $result = $del_json;
        $lib = new library();
        $cls_name = $del_json[json_fields::OBJECT_CLASS] ?? '';
        $id = $this->db_id($del_json[json_fields::ID] ?? 0);
        $dbo = null;
        // the class is kept for the reload, because the object may stay null
        $dbo_class = '';
        foreach (self::DELETE_CLASSES as $class) {
            if ($lib->class_to_name($class) == $cls_name) {
                $dbo_class = $class;
                $dbo = new $class($msg->usr);
            }
        }
        $found = false;
        if ($dbo != null and $id != 0) {
            // the returned id, see fill_from_db
            $found = $dbo->load_by_id($id, $msg) != 0;
        }
        // only if the load has not reported another reason
        if (!$found and $msg->is_ok()) {
            $msg->add(msg_id::OBJECT_NOT_FOUND, [
                msg_id::VAR_CLASS_NAME => $cls_name,
                msg_id::VAR_ID => (string)$id
            ]);
        } elseif ($found) {
            $dbo->del($msg);
        }
        if ($found and $msg->is_ok()) {
            // an excluded row is removed for the user even if the load still finds it
            $chk = new $dbo_class($msg->usr);
            $removed = ($chk->load_by_id($id, $msg) == 0 or $chk->is_excluded() === true);
            $result[json_fields::ACTION] = $removed ? self::REMOVED : self::UNCHANGED;
            if (!$removed) {
                $msg->add_warning_with_vars(msg_id::UPDATE_PUT_UNCHANGED, [
                    msg_id::VAR_CLASS_NAME => $cls_name,
                    msg_id::VAR_ID => (string)$id
                ]);
            }
        }
        return $result;
    }

    /**
     * remove the cached html pages and api answers of all users, because a change of the standard
     * data is shown to every user; done as the system user and a failure is only logged, because a
     * page that is cleared too late is a stale page, not a lost change
     *
     * @return void
     */
    private function drop_cached_answers(): void
    {
        $cac_page = new db_cache_page();
        $del_msg = new user_message(user::system()); // not reported, see above
        $cac_page->del_html($del_msg);
        foreach (self::CACHED_APIS as $class) {
            $cac_page->del_api($class, $del_msg);
        }
        if (!$del_msg->is_ok()) {
            log_warning('removing the cached pages after an update put failed because ' . $del_msg->get_message());
        }
    }

    /**
     * the object of the message with the database id of each phrase, verb and type that is given by name only
     *
     * @param array $obj_json the object of the message
     * @param user_message $msg with the requesting user, to report a name that does not exist
     * @return array the object in the api format, where the phrases of a triple include their names
     */
    private function with_ids(array $obj_json, user_message $msg): array
    {
        global $sys;
        $result = $obj_json;
        if (key_exists(json_fields::ID, $obj_json)) {
            $result[json_fields::ID] = $this->db_id($obj_json[json_fields::ID]);
        }
        foreach (self::PHRASE_NAME_FIELDS as $id_fld => $name_fld) {
            if (key_exists($id_fld, $obj_json) or key_exists($name_fld, $obj_json)) {
                $phr = $this->phrase($obj_json[$id_fld] ?? 0, $obj_json[$name_fld] ?? '', $msg);
                // with the name, because a triple without a given name generates it from the phrase names
                $result[$id_fld] = [json_fields::ID => $phr->id(), json_fields::NAME => $phr->name()];
            }
        }
        if (!key_exists(json_fields::VERB, $obj_json) and key_exists(json_fields::EX_VERB, $obj_json)) {
            $vrb = $sys->typ_lst->vrb->get_by_name($obj_json[json_fields::EX_VERB]);
            if ($vrb == null) {
                $msg->add(msg_id::TRIPLE_VERB_NOT_FOUND, [msg_id::VAR_NAME => $obj_json[json_fields::EX_VERB]]);
            } else {
                $result[json_fields::VERB] = $vrb->id();
            }
        }
        if (!key_exists(json_fields::TYPE, $obj_json) and key_exists(json_fields::TYPE_NAME, $obj_json)) {
            $typ_id = $sys->typ_lst->phr_typ->id($obj_json[json_fields::TYPE_NAME], false);
            if ($typ_id <= 0) {
                $msg->add(msg_id::PHRASE_TYPE_NOT_FOUND, [msg_id::VAR_NAME => $obj_json[json_fields::TYPE_NAME]]);
            } else {
                $result[json_fields::TYPE] = $typ_id;
            }
        }
        // the phrases of a value by their id and the phrases without id yet by their name, only if the
        // value is not given by its id, which is the id of its phrase group
        if (!key_exists(json_fields::ID, $obj_json) and key_exists(json_fields::WORDS, $obj_json)) {
            $result[json_fields::PHRASES] = $obj_json[json_fields::PHRASES] ?? [];
            foreach ($obj_json[json_fields::WORDS] as $phr_name) {
                $phr = $this->phrase(0, $phr_name, $msg);
                $result[json_fields::PHRASES][] = [json_fields::ID => $phr->id()];
            }
        }
        return $result;
    }

    /**
     * @param int|string $id the database id of the message e.g. "81495585085949938" for the group id
     *                       of a value, which the editor sends as text, because it is beyond the safe
     *                       integer of javascript, or the text id of a value with more than four phrases
     * @return int|string the id as a number if it is a number, else the text id
     * public only so that the conversion can be checked by a unit test (see import_tests)
     */
    function db_id(int|string $id): int|string
    {
        $result = $id;
        if (is_string($id) and ctype_digit($id)) {
            $result = (int)$id;
        }
        return $result;
    }

    /**
     * @param int $id the phrase id, which is used if given e.g. -2754 for a triple
     * @param string $name the phrase name, which is used if the id is missing
     * @param user_message $msg with the requesting user, to report a phrase that does not exist
     * @return phrase the loaded phrase or an empty phrase if it does not exist
     */
    private function phrase(int $id, string $name, user_message $msg): phrase
    {
        $lib = new library();
        $phr = new phrase($msg->usr);
        if ($id != 0) {
            $phr->load_by_id($id, $msg);
            if ($phr->id() == 0) {
                $msg->add(msg_id::OBJECT_NOT_FOUND, [
                    msg_id::VAR_CLASS_NAME => $lib->class_to_name(phrase::class),
                    msg_id::VAR_ID => (string)$id
                ]);
            }
        } else {
            $phr->load_by_name($name, $msg);
            if ($phr->id() == 0) {
                $msg->add(msg_id::PHRASE_NAME_NOT_FOUND, [msg_id::VAR_NAME => $name]);
            }
        }
        return $phr;
    }

}
