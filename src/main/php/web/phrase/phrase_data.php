<?php

/*

    web/phrase/phrase_data.php - the data of one phrase as the phraseData api sends it
    --------------------------

    e.g. the calculator tree of a phrase with its values and results for the calculator editor

    $phd is the suggested var name

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

namespace Zukunft\ZukunftCom\main\php\web\phrase;

use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::FORMULA . 'formula_list.php';
include_once html_paths::HELPER . 'data_object.php';
include_once html_paths::HTML . 'html_base.php';
include_once html_paths::HTML . 'rest_call.php';
include_once html_paths::PHRASE . 'phrase.php';
include_once html_paths::PHRASE . 'phrase_list.php';
include_once html_paths::REF . 'source_list.php';
include_once html_paths::RESULT . 'result_list.php';
include_once html_paths::TYPES . 'type_list.php';
include_once html_paths::USER . 'user_message.php';
include_once html_paths::VALUE . 'value_list.php';
include_once html_paths::SHARED_CONST . 'rest_ctrl.php';
include_once html_paths::SHARED_ENUM . 'messages.php';
include_once html_paths::SHARED_ENUM . 'phrase_data_walks.php';
include_once html_paths::SHARED_TYPES . 'verbs.php';
include_once html_paths::SHARED . 'json_fields.php';
include_once html_paths::SHARED . 'library.php';
include_once html_paths::SHARED . 'url_var.php';

use Zukunft\ZukunftCom\main\php\shared\const\rest_ctrl;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\enum\phrase_data_walks;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\web\formula\formula_list;
use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\html\rest_call;
use Zukunft\ZukunftCom\main\php\web\ref\source_list;
use Zukunft\ZukunftCom\main\php\web\result\result_list;
use Zukunft\ZukunftCom\main\php\web\types\type_list;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\value\value_list;

class phrase_data
{

    // the words and triples of the answer by their name, because the objects name their phrases by name
    private array $wrd_by_name = [];
    private array $trp_by_name = [];
    // the api json of each phrase by its name, created once also if many objects name the phrase
    private array $phr_json = [];

    /**
     * the answer of the phraseData api is the export json without the api message header, so it
     * is read by the plain api call and not by rest_call::api_get, which expects the header
     *
     * @param phrase $phr the phrase of the page e.g. example PV installation
     * @param user_message $msg with the requesting user and to report an answer without the phrase
     * @return array the words, triples, formulas, sources, values and results on the from side of
     *               the phrase with their database ids, or an empty array if the api has not sent them
     */
    function load_from_walk(phrase $phr, user_message $msg): array
    {
        $html = new html_base();
        $lib = new library();
        $api = new rest_call();
        $url = $html->url_api($lib->class_to_api_route(self::class));
        $data = [
            url_var::ID => $phr->id(),
            url_var::PHRASE_DATA_WALK => phrase_data_walks::FROM->value,
            url_var::INCL_RESULTS => url_var::TRUE
        ];
        $json = json_decode($api->api_call(rest_ctrl::GET, $url, $api->data_with_user($data, $msg)), true) ?? [];
        $result = [];
        if (array_key_exists(json_fields::SELECTION, $json)) {
            $result = $json;
        } elseif (array_key_exists(json_fields::MSG, $json)) {
            // the reason of the api e.g. an unknown phrase
            $msg->add(msg_id::API_MESSAGE, [msg_id::VAR_JSON_TEXT => $json[json_fields::MSG]]);
        } else {
            $msg->add(msg_id::API_MESSAGE_EMPTY, [msg_id::VAR_REQUEST => $url]);
        }
        return $result;
    }

    /**
     * fill the request cache of a page from one answer of the phraseData api, so that the page
     * needs no other api call for its phrases, values, results, formulas and sources; each object
     * of the answer is converted to the api json that the frontend object maps, with the phrases
     * of a triple, value or result found by their name within the answer
     *
     * @param array $json the answer of the phraseData api e.g. of load_from_walk
     * @param data_object $dto the request cache to fill
     * @param user_message $msg to report a name that the answer does not contain, which is never loaded by another api call
     * @return bool true if every name of the answer has been found
     */
    function fill(array $json, data_object $dto, user_message $msg): bool
    {
        $fill_msg = new user_message($msg->usr); // merged into $msg at the end, so that only a problem of this answer leaves the cache incomplete
        $this->wrd_by_name = $this->by_name($json[json_fields::WORDS] ?? []);
        $this->trp_by_name = $this->triples_by_name($json[json_fields::TRIPLES] ?? []);
        $this->phr_json = [];
        $frm_ids = $this->ids_by_name($json[json_fields::FORMULAS] ?? []);
        $src_ids = $this->ids_by_name($json[json_fields::SOURCES] ?? []);

        $phr_lst = new phrase_list();
        $names = array_merge(array_keys($this->wrd_by_name), array_keys($this->trp_by_name));
        $fill_msg->merge($phr_lst->api_mapper($this->phrases_json($names, $fill_msg)));
        $dto->add_phrases($phr_lst, $fill_msg);

        $frm_objs = $json[json_fields::FORMULAS] ?? [];
        $frm_json = array_map(fn($frm) => $this->formula_json($frm, $fill_msg), $frm_objs);
        $frm_lst = new formula_list();
        $fill_msg->merge($frm_lst->api_mapper($frm_json));
        $dto->frm_lst->merge($frm_lst, $fill_msg);

        // a source of the answer has the fields of the api json
        $src_lst = new source_list();
        $fill_msg->merge($src_lst->api_mapper($json[json_fields::SOURCES] ?? []));
        $dto->src_lst->merge($src_lst, $fill_msg);

        $val_objs = $json[json_fields::VALUES] ?? [];
        $val_json = array_map(fn($val) => $this->value_json($val, $src_ids, $fill_msg), $val_objs);
        $val_lst = new value_list();
        $fill_msg->merge($val_lst->api_mapper($val_json));
        $dto->val_lst->merge($val_lst, $fill_msg);

        $res_objs = $json[json_fields::RESULTS] ?? [];
        $res_json = array_map(fn($res) => $this->result_json($res, $frm_ids, $fill_msg), $res_objs);
        $res_lst = new result_list();
        $fill_msg->merge($res_lst->api_mapper($res_json));
        foreach ($res_lst->lst() as $res) {
            $dto->res_lst->add_result($res);
        }
        // the results of the selected phrase are complete, so that the page does not load them again
        foreach ($json[json_fields::SELECTION] ?? [] as $sel) {
            $sel_json = $this->phrase_json($sel[json_fields::NAME] ?? '', $fill_msg);
            if ($sel_json != null) {
                $dto->add_results_of($this->phrase_id($sel_json), new result_list());
            }
        }
        $result = $fill_msg->is_ok();
        $msg->merge($fill_msg);
        return $result;
    }

    /**
     * @param array $objects the json of the words, triples, formulas or sources of the answer
     * @return array the json of each object by its name
     */
    private function by_name(array $objects): array
    {
        $result = [];
        foreach ($objects as $obj) {
            $result[$obj[json_fields::NAME] ?? ''] = $obj;
        }
        return $result;
    }

    /**
     * @param array $objects the json of the formulas or sources of the answer
     * @return array the id of each object by its name
     */
    private function ids_by_name(array $objects): array
    {
        $result = [];
        foreach ($this->by_name($objects) as $name => $obj) {
            $result[$name] = $obj[json_fields::ID];
        }
        return $result;
    }

    /**
     * @param array $triples the json of the triples of the answer
     * @return array the json of each triple by its name, which is the generated name for a triple
     *               without its own name, because the other objects name it so e.g. gram per kWh
     */
    private function triples_by_name(array $triples): array
    {
        $is_name = $this->is_verb_name();
        $result = [];
        foreach ($triples as $trp) {
            $name = $trp[json_fields::NAME] ?? '';
            if ($name == '') {
                $name = $this->generated_name($trp, $is_name);
                $trp[json_fields::NAME] = $name;
            }
            $result[$name] = $trp;
        }
        return $result;
    }

    /**
     * @return string the name of the verb "is" of the type cache or an empty string if the cache is missing
     */
    private function is_verb_name(): string
    {
        global $ui_sys;
        $result = '';
        $vrb_lst = $ui_sys?->typ_lst_cache?->vrb;
        if ($vrb_lst != null) {
            $is_vrb = $vrb_lst->get_by_code_id(verbs::IS);
            $result = $is_vrb?->name() ?? '';
        }
        return $result;
    }

    /**
     * like the backend triple::generate_name, because the other objects name a triple without its own name so
     *
     * @param array $trp the json of a triple of the answer without its own name
     * @param string $is_name the name of the verb "is", which is shown as brackets
     * @return string e.g. "Pi (math)" for the verb "is" or "gram per kWh" for every other verb
     */
    private function generated_name(array $trp, string $is_name): string
    {
        $from = $trp[json_fields::EX_FROM] ?? '';
        $vrb = $trp[json_fields::EX_VERB] ?? '';
        $to = $trp[json_fields::EX_TO] ?? '';
        if ($vrb == $is_name) {
            $result = $from . ' (' . $to . ')';
        } else {
            $result = $from . ' ' . $vrb . ' ' . $to;
        }
        return $result;
    }

    /**
     * @param array $names the names of words or triples of the answer e.g. the phrases of a value
     * @param user_message $msg to report a name that the answer does not contain
     * @return array the api json of the phrases that the answer contains
     */
    private function phrases_json(array $names, user_message $msg): array
    {
        $result = [];
        foreach ($names as $name) {
            $phr = $this->phrase_json($name, $msg);
            if ($phr != null) {
                $result[] = $phr;
            }
        }
        return $result;
    }

    /**
     * the api json of a word or of a triple with its from and to phrase, as the phraseList api sends it
     *
     * @param string $name the name of a word or triple of the answer
     * @param user_message $msg to report a name, a verb or a type that the frontend does not know
     * @return array|null the api json of the phrase or null if the answer does not contain the name
     */
    private function phrase_json(string $name, user_message $msg): ?array
    {
        global $ui_sys;
        $typ_lst = $ui_sys?->typ_lst_cache;
        if (!array_key_exists($name, $this->phr_json)) {
            // set before the sides are converted, so that a triple that is built from itself ends
            $this->phr_json[$name] = null;
            $result = null;
            if (array_key_exists($name, $this->wrd_by_name)) {
                $wrd = $this->wrd_by_name[$name];
                $result = [
                    json_fields::ID => $wrd[json_fields::ID],
                    json_fields::NAME => $name,
                    json_fields::DESCRIPTION => $wrd[json_fields::DESCRIPTION] ?? null,
                    json_fields::TYPE => $this->type_id($typ_lst?->phr_typ, $this->type_keys($wrd),
                        msg_id::PHRASE_TYPE_NOT_FOUND, $msg),
                    json_fields::OBJECT_CLASS => json_fields::CLASS_WORD
                ];
            } elseif (array_key_exists($name, $this->trp_by_name)) {
                $trp = $this->trp_by_name[$name];
                $from = $this->phrase_json($trp[json_fields::EX_FROM] ?? '', $msg);
                $to = $this->phrase_json($trp[json_fields::EX_TO] ?? '', $msg);
                $result = [
                    json_fields::ID => $trp[json_fields::ID],
                    json_fields::NAME => $name,
                    json_fields::DESCRIPTION => $trp[json_fields::DESCRIPTION] ?? null,
                    json_fields::TYPE => $this->type_id($typ_lst?->phr_typ, $this->type_keys($trp),
                        msg_id::PHRASE_TYPE_NOT_FOUND, $msg),
                    json_fields::FROM => $from == null ? null : $this->phrase_id($from),
                    json_fields::FROM_PHRASE => $from,
                    json_fields::VERB => $this->type_id($typ_lst?->vrb, [$trp[json_fields::EX_VERB] ?? ''],
                        msg_id::TRIPLE_VERB_NOT_FOUND, $msg),
                    json_fields::TO => $to == null ? null : $this->phrase_id($to),
                    json_fields::TO_PHRASE => $to,
                    json_fields::OBJECT_CLASS => json_fields::CLASS_TRIPLE
                ];
            } else {
                $msg->add(msg_id::PHRASE_NAME_NOT_FOUND, [msg_id::VAR_NAME => $name]);
            }
            $this->phr_json[$name] = $this->without_null($result);
        }
        return $this->phr_json[$name];
    }

    /**
     * @param array $phr the api json of a word or triple
     * @return int the phrase id, which is negative for a triple
     */
    private function phrase_id(array $phr): int
    {
        $id = (int)$phr[json_fields::ID];
        return $phr[json_fields::OBJECT_CLASS] == json_fields::CLASS_TRIPLE ? -$id : $id;
    }

    /**
     * the id of a type or verb that the answer names by its code id or by its name, because e.g.
     * the export names the type of a word by the code id and the type of a triple by the name
     *
     * @param type_list|null $lst the type list of the frontend cache e.g. the phrase types
     * @param array $keys the code id and the name of the type, of which an empty one is not set
     * @param msg_id $msg_id the message if the frontend does not know the type e.g. PHRASE_TYPE_NOT_FOUND
     * @param user_message $msg to report a type that the frontend does not know
     * @return int|null the id of the type or null if no type is set or found
     */
    private function type_id(?type_list $lst, array $keys, msg_id $msg_id, user_message $msg): ?int
    {
        $result = null;
        $keys = array_values(array_filter($keys, fn($key) => $key != ''));
        foreach ($keys as $key) {
            if ($result === null and $lst != null) {
                $id = $lst->id($key);
                if ($id <= 0) {
                    $id = $lst->id_by_name($key);
                }
                $result = $id > 0 ? $id : null;
            }
        }
        if ($result === null and $keys != []) {
            $msg->add($msg_id, [msg_id::VAR_NAME => $keys[0]]);
        }
        return $result;
    }

    /**
     * @param array $obj the json of a word, triple or formula of the answer
     * @return array the code id and the name of its type, of which an empty one is not set
     */
    private function type_keys(array $obj): array
    {
        return [$obj[json_fields::TYPE_CODE_ID] ?? '', $obj[json_fields::TYPE_NAME] ?? ''];
    }

    /**
     * @param array $frm the json of a formula of the answer
     * @param user_message $msg to report a type that the frontend does not know
     * @return array the api json of the formula with its expression as the user text
     */
    private function formula_json(array $frm, user_message $msg): array
    {
        global $ui_sys;
        return $this->without_null([
            json_fields::ID => $frm[json_fields::ID],
            json_fields::NAME => $frm[json_fields::NAME] ?? null,
            json_fields::DESCRIPTION => $frm[json_fields::DESCRIPTION] ?? null,
            json_fields::TYPE => $this->type_id($ui_sys?->typ_lst_cache?->frm_typ, $this->type_keys($frm),
                msg_id::FORMULA_TYPE_NOT_FOUND, $msg),
            json_fields::USER_TEXT => $frm[json_fields::EXPRESSION] ?? null,
            json_fields::LATEX => $frm[json_fields::LATEX] ?? null
        ]);
    }

    /**
     * @param array $val the json of a value of the answer
     * @param array $src_ids the source ids of the answer by the source name
     * @param user_message $msg to report a phrase or source name that the answer does not contain
     * @return array the api json of the value with its phrases
     */
    private function value_json(array $val, array $src_ids, user_message $msg): array
    {
        $src_name = $val[json_fields::SOURCE] ?? '';
        if ($src_name != '' and !array_key_exists($src_name, $src_ids)) {
            $msg->add(msg_id::IMPORT_SOURCE_NOT_FOUND, [
                msg_id::VAR_NAME => $src_name,
                msg_id::VAR_ID => (string)($val[json_fields::ID] ?? '')
            ]);
        }
        return $this->without_null([
            json_fields::ID => $val[json_fields::ID] ?? null,
            json_fields::NUMBER => $val[json_fields::NUMBER] ?? null,
            json_fields::DESCRIPTION => $val[json_fields::DESCRIPTION] ?? null,
            json_fields::SOURCE_ID => $src_ids[$src_name] ?? null,
            json_fields::PHRASES => $this->phrases_json($val[json_fields::WORDS] ?? [], $msg)
        ]);
    }

    /**
     * @param array $res the json of a result of the answer
     * @param array $frm_ids the formula ids of the answer by the formula name
     * @param user_message $msg to report a phrase or formula name that the answer does not contain
     * @return array the api json of the result with its phrases, its context and the id of its formula
     */
    private function result_json(array $res, array $frm_ids, user_message $msg): array
    {
        $frm_name = $res[json_fields::FORMULA_NAME] ?? '';
        if ($frm_name != '' and !array_key_exists($frm_name, $frm_ids)) {
            $msg->add(msg_id::IMPORT_TERM_NOT_FOUND, [
                msg_id::VAR_NAME => $frm_name,
                msg_id::VAR_ID => (string)($res[json_fields::ID] ?? '')
            ]);
        }
        $ctx = $res[json_fields::CONTEXT] ?? null;
        return $this->without_null([
            json_fields::ID => $res[json_fields::ID] ?? null,
            json_fields::NUMBER => $res[json_fields::NUMBER] ?? null,
            json_fields::FORMULA_ID => $frm_ids[$frm_name] ?? null,
            json_fields::PHRASES => $this->phrases_json($res[json_fields::WORDS] ?? [], $msg),
            json_fields::CONTEXT => $ctx === null ? null : $this->phrases_json($ctx, $msg)
        ]);
    }

    /**
     * @param array|null $json an api json
     * @return array|null the json without the fields that are not set, because the mappers of
     *                    the frontend objects take a field that exists as given
     */
    private function without_null(?array $json): ?array
    {
        return $json === null ? null : array_filter($json, fn($fld) => $fld !== null);
    }

}
