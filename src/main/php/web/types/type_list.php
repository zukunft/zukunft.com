<?php

/*

    web/types/type_list.php - base object for preloaded types used in the html frontend
    -----------------------

    this base object is without set_from_json function,
    because the setting is done once for all type objects with the parent object

    TODO Prio 1 : check that all type list objetc have the same additional vars as the backend object and check the api fillings


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

    Copyright (c) 1995-2023 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com
  
*/

namespace Zukunft\ZukunftCom\main\php\web\types;

use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::SHARED . 'url_var.php';
include_once html_paths::SYSTEM . 'language.php';
include_once html_paths::TYPES . 'protection.php';
include_once html_paths::HTML . 'html_base.php';
include_once html_paths::HTML . 'html_selector.php';
include_once html_paths::HTML . 'styles.php';
include_once html_paths::TYPES . 'ref_type.php';
include_once html_paths::TYPES . 'type_object.php';
include_once html_paths::USER . 'user_message.php';
//include_once html_paths::VERB . 'verb.php';
include_once html_paths::SHARED_ENUM . 'messages.php';
include_once html_paths::SHARED_HELPER . 'Config.php';
include_once html_paths::SHARED_TYPES . 'phrase_types.php';
include_once html_paths::SHARED_TYPES . 'view_styles.php';
include_once html_paths::SHARED . 'json_fields.php';
include_once html_paths::SHARED . 'library.php';

use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\html\html_selector;
use Zukunft\ZukunftCom\main\php\web\html\styles;
use Zukunft\ZukunftCom\main\php\web\system\language;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\verb\verb;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\helper\Config as shared_config;
use Zukunft\ZukunftCom\main\php\shared\types\phrase_types;
use Zukunft\ZukunftCom\main\php\shared\types\view_styles;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\library;

class type_list
{

    // error return codes
    const int CODE_ID_NOT_FOUND = -1;
    // extra entry used in a selection to separate the highlighted entries from the sorted entries
    const string SELECT_SEPARATOR = ' --- ';
    // the limit of the all version of a list, which shows every entry
    const int LIMIT_ALL = 0;
    // the separator between two entries of a one line list
    // TODO Prio 1 read the separator from the config.yaml (user: frontend: lists: separator: entry)
    const string ENTRY_SEPARATOR = ', ';
    // the view that shows the complete list, used as the target of the "... and n more" tail;
    // overwritten by the child that has such a view, 0 keeps the tail an unlinked text
    const int VIEW_ALL_ID = 0;

    // the protected main var without id list because this is only loaded once
    protected array $lst = [];
    // the database id of each entry on its code id, so that id() can resolve a code id without
    // scanning the list; filled only by add_obj(), so there is one writer of this contract
    private array $hash = [];


    function reset(): void
    {
        $this->lst = [];
        $this->hash = [];
    }

    /**
     * set the vars of these list display objects bases on the api json array
     * @param array $json_array an api list json message
     * @param user_message $msg ok or a warning e.g. if the server version does not match
     * @param string $class to force the type child class e.g. verb, ref_type or language
     * @return bool true if there are no errors
     */
    function set_from_json_array(array $json_array, user_message $msg, string $class = ''): bool
    {
        foreach ($json_array as $value) {
            if ($class == verb::class) {
                $vrb = new verb();
                $vrb->api_mapper($value, $msg);
                $this->add_obj($vrb, $msg);
            } elseif ($class == ref_type::class) {
                $ref_typ = new ref_type(
                    $value[json_fields::ID],
                    $value[json_fields::CODE_ID],
                    $value[json_fields::NAME],
                    $value[json_fields::DESCRIPTION]
                );
                if (key_exists(json_fields::URL, $value)) {
                    $ref_typ->url = $value[json_fields::URL];
                }
                $this->add_obj($ref_typ, $msg);
            } elseif ($class == language::class) {
                $lan = new language(
                    $value[json_fields::ID],
                    $value[json_fields::CODE_ID],
                    $value[json_fields::NAME],
                    $value[json_fields::DESCRIPTION]
                );
                if (key_exists(json_fields::WIKI_CODE, $value)) {
                    $lan->wiki_code = $value[json_fields::WIKI_CODE];
                }
                if (key_exists(json_fields::LOCAL_NAME, $value)) {
                    $lan->local_name = $value[json_fields::LOCAL_NAME];
                }
                if (key_exists(json_fields::USAGE, $value)) {
                    $lan->usage = $value[json_fields::USAGE];
                }
                $this->add_obj($lan, $msg);
            } else {
                if (!array_key_exists(json_fields::CODE_ID, $value)) {
                    $msg->add_error_text('code id is missing for ' . implode(',', $value));
                }
                if (array_key_exists(json_fields::DESCRIPTION, $value)) {
                    $typ = new type_object(
                        $value[json_fields::ID],
                        $value[json_fields::CODE_ID],
                        $value[json_fields::NAME],
                        $value[json_fields::DESCRIPTION]
                    );
                } else {
                    $typ = new type_object(
                        $value[json_fields::ID],
                        $value[json_fields::CODE_ID],
                        $value[json_fields::NAME]
                    );
                }
                $this->add_obj($typ, $msg);
            }
        }
        return $msg->is_ok();
    }

    /**
     * @returns array with the names on the db keys
     */
    function lst_key(): array
    {
        $result = array();
        foreach ($this->lst as $typ) {
            $result[$typ->id()] = $typ->name();
        }
        return $result;
    }

    /**
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
     * @returns array the protected list of values or formula results
     */
    function lst(): array
    {
        return $this->lst;
    }

    /**
     * @returns array with the names on the db keys
     */
    function db_id_list(): array
    {
        $result = array();
        foreach ($this->lst as $obj) {
            $result[$obj->id()] = $obj->name();
        }
        return $result;
    }

    /**
     * return the database row id based on the code_id
     *
     * @param string $code_id
     * @return int the database id for the given code_id
     */
    function id(string $code_id): int
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

    function get_code_id(int $id): string
    {
        $result = '';
        $type = $this->get($id);
        if ($type != null) {
            $result = $type->code_id;
        } else {
            log_warning('Type code id not found for ' . $id . ' in ' . $this->dsp_id());
        }
        return $result;
    }

    function name(?int $id): string
    {
        $result = '';
        $type = $this->get($id);
        if ($type != null) {
            $result = $type->name;
        }
        return $result;
    }

    /**
     * pick a type from the preloaded object list;
     * a null or zero id means the type is simply not (yet) set, e.g. in an add form, which is a
     * normal case and therefore not reported as an error, whereas an unknown positive id points
     * to an inconsistency between the request and the preloaded type list and is logged below
     * @param int|null $id the database id of the expected type or null if the type is not set
     * @return verb|ref_type|type_object|null the type object or null if the type is not set
     */
    function get(?int $id): verb|ref_type|type_object|null
    {
        $result = null;
        if (count($this->hash) != count($this->lst)) {
            $dub_key = [];
            $all_key = [];
            foreach ($this->lst as $typ) {
                $key = $typ->get_code_id();
                if (!in_array($key, $all_key)) {
                    $all_key[] = $key;
                } else {
                    if ($key != '') {
                        $dub_key[] = $key;
                    }
                }
            }
            if (count($dub_key) > 0) {
                log_err('probably "' . implode(', ', $dub_key) . '" are duplicate code_id in ' . $this::class);
            }
            //log_warning('probably "' . implode(', ' ,$dub_key) . '" are duplicate code_id in ' . $this::class);
        }
        if ($id > 0) {
            // pick by id instead of mapping the position in the hash to the position in the list,
            // because the two are only parallel as long as no entry is added twice
            $found = array_filter($this->lst, fn(verb|ref_type|type_object $typ) => $typ->id() == $id);
            if ($found != []) {
                $result = reset($found);
            } else {
                log_warning('Type with is ' . $id . ' not found in ' . $this->dsp_id());
            }
        } else {
            log_debug('Type id not set');
        }
        return $result;
    }

    /**
     * get the type object by code id (just to shorten the code)
     * @param string $code_id
     * @return verb|type_object|null
     */
    function get_by_code_id(string $code_id): verb|type_object|null
    {
        return $this->get($this->id($code_id));
    }


    /*
     * modify functions
     */

    /**
     * add a phrase or ... to the list
     * @param object $obj the type object that should be added to this list
     * @param user_message $msg to report an entry that is already part of this list
     * @param bool $allow_duplicates true if the list can contain the same entry twice
     *                               e.g. a verb list that counts the verb usages of a triple list
     * @returns bool true if the object has been added
     */
    protected function add_obj(object $obj, user_message $msg, bool $allow_duplicates = false): bool
    {
        $added = false;
        if ($allow_duplicates or !in_array($obj->id(), $this->id_lst())) {
            $this->lst[] = $obj;
            $this->hash[$obj->code_id] = $obj->id();
            $added = true;
        } else {
            $msg->add(msg_id::LIST_DOUBLE_ENTRY, [
                msg_id::VAR_NAME => $obj->name(),
                msg_id::VAR_CLASS_NAME => library::class_to_name($obj::class)
            ]);
        }
        return $added;
    }

    /**
     * @returns array with all unique ids of this list
     */
    protected function id_lst(): array
    {
        $result = array();
        foreach ($this->lst as $val) {
            if (!in_array($val->id(), $result)) {
                $result[] = $val->id();
            }
        }
        return $result;
    }


    /*
     * display
     */

    /**
     * the names of this list in one line, comma separated, each with its description as tooltip
     * the short version of a list shows shared_config::LIMIT_SHORT_LIST entries, the more version
     * shared_config::LIMIT_MORE_LIST and the all version every entry (self::LIMIT_ALL)
     * @param int $limit the maximum number of entries to show, self::LIMIT_ALL for the complete list
     * @return string the html code of the names in one line
     */
    function name_tip(int $limit = shared_config::LIMIT_SHORT_LIST): string
    {
        return $this->names_one_line($limit, false);
    }

    /**
     * the names of this list in one line, comma separated, each linked to its own page
     * @param int $limit the maximum number of entries to show, self::LIMIT_ALL for the complete list
     * @param string $base_url to set an absolut html path for urls
     * @return string the html code of the linked names in one line
     */
    function name_link(int $limit = shared_config::LIMIT_SHORT_LIST, string $base_url = ''): string
    {
        return $this->names_one_line($limit, true, $base_url);
    }

    /**
     * the entries of this list ordered by name
     *
     * the sorted entries are returned instead of sorting the list in place, because get() maps the
     * position in the hash to the position in the list, so reordering $this->lst would break every
     * later lookup by id or code id of a preloaded type list
     *
     * @return array the list entries ordered by name
     */
    function sort_by_name(): array
    {
        $lst = $this->lst();
        usort($lst, fn(verb|type_object $one, verb|type_object $two) => strcmp($one->name(), $two->name()));
        return $lst;
    }

    /**
     * the entries of this list ordered by the system calculated relevance, highest first,
     * so that a shortened list shows the entries that matter most to the user
     *
     * entries with the same (or no) impact are ordered by name and finally by id, so that the order
     * is always deterministic and the html does not change between runs e.g. for the snapshot tests
     *
     * @return array the list entries ordered by impact, name and id
     */
    function sort_by_impact(): array
    {
        $lst = $this->lst();
        usort($lst, function (verb|type_object $one, verb|type_object $two) {
            return $two->impact() <=> $one->impact()
                ?: strcmp($one->name(), $two->name())
                    ?: $one->id() <=> $two->id();
        });
        return $lst;
    }

    /**
     * the shared body of name_tip and name_link
     * @param int $limit the maximum number of entries to show, self::LIMIT_ALL for the complete list
     * @param bool $with_link true to link each entry to its page, false to show only the tooltip
     * @param string $base_url to set an absolut html path for urls
     * @return string the html code of the names in one line
     */
    private function names_one_line(int $limit, bool $with_link, string $base_url = ''): string
    {
        // the short and the more version show only a part of the list, so the entries with the
        // highest impact are picked; the all version is sorted by name, which reads easier
        $lst = $limit == self::LIMIT_ALL ? $this->sort_by_name() : $this->sort_by_impact();
        $shown = count($lst);
        if ($limit != self::LIMIT_ALL and $limit < $shown) {
            $shown = $limit;
        }
        $names = [];
        foreach (array_slice($lst, 0, $shown) as $obj) {
            // named argument, so that each entry keeps the view id of its own class
            $names[] = $with_link ? $obj->name_link(base_url: $base_url) : $obj->name_tip();
        }
        $line = implode(self::ENTRY_SEPARATOR, $names);
        if ($shown < count($lst)) {
            $line .= self::ENTRY_SEPARATOR . $this->more_tail(count($lst) - $shown, $with_link, $base_url);
        }
        // text-nowrap keeps the entries on one line, so that neither an entry name of several words
        // nor the list itself is split across lines in the browser and in the html snapshot
        return new html_base()->span($line, styles::TEXT_NOWRAP);
    }

    /**
     * the "... and n more" tail of a truncated list
     * TODO Prio 1 link the tail to the next list version (short to more, more to all) once the
     *             url var for the list version exists; for now it points to the complete list
     * @param int $diff the number of entries that are not shown
     * @param bool $with_link true to link the tail to the view that shows the complete list
     * @param string $base_url to set an absolut html path for urls
     * @return string the html code of the tail
     */
    private function more_tail(int $diff, bool $with_link, string $base_url = ''): string
    {
        $result = msg_id::THREE_POINTS->text() . ' ' . msg_id::AND_MORE_BEFORE->text() . ' '
            . $diff . ' ' . msg_id::MORE->text();
        if ($with_link and $this::VIEW_ALL_ID != 0) {
            $html = new html_base();
            $result = $html->ref($html->url_back($this::VIEW_ALL_ID, base_url: $base_url), $result);
        }
        return $result;
    }

    /**
     * create the HTML code to select a type
     * TODO Prio 0 use this var order for all selectors
     * TODO use the label_id for all function calls
     * @param string $form the unique name of the html form
     * @param int|null $selected the id of the preselected phrase
     * @param string $name the unique name inside the form for this selector
     * @param string $style the formatting code to adjust the formatting
     * @param msg_id $label_id the text show to the user
     * @returns string the html code to select a type from this list
     */
    function type_selector(
        string $form,
        ?int   $selected = null,
        string $name = '',
        msg_id $label_id = msg_id::FORM_FIELD_TYPE,
        string $style = view_styles::COL_SM_4
    ): string
    {
        $sel = new html_selector();
        if (in_array($label_id, msg_id::FORM_TYPE_SELECTOR_LABELS_SORT_BY_ALPHA_WITH_DEFAULT)) {
            $std = $this->get_by_code_id(phrase_types::DEFAULT);
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
        $sel->type = html_selector::TYPE_SELECT;
        return $sel->display();
    }


    /*
     * debug
     */
    function dsp_id(): string
    {
        return '';
    }

}