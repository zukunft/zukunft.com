<?php

/*

    model/export.php - create an object to export data - the object can be converted to a json, yaml or XML message
    ----------

    offer the user the long or the short version
    the short version is using one time id for words, triples and groups

    add the instance id, user id and time stamp to the export file

    TODO Prio 2
    - offer to export the change log
    - export only the user view
    - ... or include the standard value
    - ... or include all user values in the export object


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

namespace Zukunft\ZukunftCom\main\php\cfg\export;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_CONST . 'def.php';
include_once paths::MODEL_FORMULA . 'formula.php';
include_once paths::MODEL_FORMULA . 'formula_list.php';
include_once paths::MODEL_PHRASE . 'phr_ids.php';
include_once paths::MODEL_PHRASE . 'phrase_list.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::MODEL_WORD . 'triple_list.php';
include_once paths::EXPORT . 'xml.php';
include_once paths::SERVICE . 'config.php';
include_once paths::SERVICE_EXPORT . 'json_io.php';
include_once paths::SHARED . 'library.php';
include_once paths::SHARED . 'url_var.php';
include_once paths::SHARED_CONST . 'rest_ctrl.php';
include_once paths::SHARED_ENUM . 'foaf_direction.php';
include_once paths::SHARED_ENUM . 'messages.php';

use Zukunft\ZukunftCom\main\php\cfg\const\def;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_list;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phr_ids;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\word\triple_list;
use Zukunft\ZukunftCom\main\php\service\config;
use Zukunft\ZukunftCom\main\php\service\export\json_io;
use Zukunft\ZukunftCom\main\php\shared\const\rest_ctrl;
use Zukunft\ZukunftCom\main\php\shared\enum\foaf_direction;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\url_var;

class export
{

    /*
     * set and get
     */

    /**
     * export zukunft.com data as object for creating e.g. a json message
     * @param user_message $msg to collect issue in export
     * @param user|null $usr the user who wants to im- or export
     * @param phrase_list|null $phr_lst to export all values related to this phrase
     * @return object
     */
    function get(user_message $msg, ?user $usr = null, ?phrase_list $phr_lst = null): object
    {

        global $db_con;

        $lib = new library();
        $cfg_sys = new config();

        log_debug();
        $export_obj = (object)[];

        if ($phr_lst != null) {
            if ($phr_lst->is_empty()) {
                log_warning("No words to filter the export are defined.", "export->get");
            } else {

                // 1. create the header
                $export_obj->version = def::PRG_VERSION;
                $export_obj->pod = $cfg_sys->get_db(config::SITE_NAME, $db_con, $msg, 'get pod name');
                $export_obj->time = date("Y-m-d H:i:s");
                $export_obj->user = $usr->name;
                $export_obj->selection = $phr_lst->names(); // must be set by before the call TODO Prio 2 not nice better use the $phr_lst->object_exp_lst()

                // 1.5. expand the selection to all triples (and their phrases) connected to the
                //      selected phrases, e.g. from "nuclear" and "power" to the triple "nuclear power"
                //      and on to "nuclear power plant", so that the related values, their sources and
                //      the related formulas are exported too, not just the directly selected words
                $phr_lst = clone $phr_lst; // keep the caller's selection list unchanged
                $added_lst = clone $phr_lst;
                $loops = 0;
                do {
                    $rel_trp_lst = new triple_list($usr);
                    $rel_trp_lst->load_by_phr_lst($added_lst, $msg, null, foaf_direction::BOTH);
                    $rel_phr_lst = $rel_trp_lst->phrase_list();
                    $added_lst = clone $rel_phr_lst;
                    $added_lst->remove($phr_lst);
                    $phr_lst->merge($rel_phr_lst);
                    $loops++;
                } while (!$added_lst->is_empty() and $loops < def::MAX_LOOP);

                // reload the expanded list fully by id so every word and triple has its type,
                // verb and from/to set; the graph load above sets the link ids only, which would
                // otherwise make the type and from/to access fail while exporting the triples
                if (!$phr_lst->is_empty()) {
                    $full_lst = new phrase_list($usr);
                    $full_lst->load_by_ids($phr_lst->phrase_ids(), $msg);
                    if (!$full_lst->is_empty()) {
                        $phr_lst = $full_lst;
                    }
                }
                // the expansion reads the triples of every user, so a non-public phrase of another user goes
                $phr_lst->filter_readable_by($usr);

                // 1.1. collect all personal values - value that cannot be seen by other user

                // 2. collect values linked to the user selected words
                //    e.g. if carrots are selected get the climate gas emissions per weight percent
                log_debug('export->get values');
                $val_lst = $phr_lst->val_lst($msg);
                $val_lst->filter_readable_by($usr);

                // 3. get all words and triples needed for the values that should be exported
                //    e.g. carrots, climate gas emission (CO2, methane), weight, percent
                log_debug('export->get words and triples');
                $phr_lst->merge($val_lst->phr_lst_all($msg));
                $phr_lst->filter_readable_by($usr);
                $wrd_lst = $phr_lst->wrd_lst_all($msg);

                // 4. export all words that have a special type or any other non default setting (standard words are created automatically on import with just the name)
                log_debug('export->get typed words');
                if ($wrd_lst != null) {
                    $exp_words = $wrd_lst->export_json($msg, []);
                    if (count($exp_words) > 0) {
                        $export_obj->words = $exp_words;
                    }
                }

                // 5. export all word relations
                log_debug('export->get triples');
                $lnk_lst = $phr_lst->triples();
                $exp_triples = array();
                foreach ($lnk_lst->lst() as $lnk) {
                    $exp_lnk = $lnk->export_json($msg, []);
                    if (isset($exp_lnk)) {
                        $exp_triples[] = $exp_lnk;
                    }
                }
                if (count($exp_triples) > 0) {
                    $export_obj->triples = $exp_triples;
                }

                // 6. add all sources to the export object
                log_debug('export->get sources');
                $source_lst = $val_lst->source_lst($msg);
                log_debug('export->got ' . $lib->dsp_count($source_lst) . ' sources');
                $exp_sources = array();
                if ($source_lst != null) {
                    foreach ($source_lst as $src) {
                        if (isset($src) and $src->is_readable_by($usr)) {
                            $exp_src = $src->export_json($msg, []);
                            if (isset($exp_src)) {
                                $exp_sources[] = $exp_src;
                            }
                        }
                    }
                }
                if (count($exp_sources) > 0) {
                    $export_obj->sources = $exp_sources;
                }

                // 7. add all values to the export object
                log_debug('export->get values');
                $exp_values = array();
                foreach ($val_lst->lst() as $val) {
                    if (isset($val)) {
                        $exp_val = $val->export_json($msg, []);
                        if (isset($exp_val)) {
                            $exp_values[] = $exp_val;
                        }
                    }
                }
                $export_obj->values = $exp_values;

                // 8. export all used formula relations to reproduce the results
                //    after the values so that the export matches the import file order
                log_debug('export->get formulas');
                $frm_lst = $phr_lst->frm_lst($msg);
                $frm_lst->filter_readable_by($usr);
                $exp_formulas = array();
                if (!$frm_lst->is_empty()) {
                    foreach ($frm_lst->lst() as $frm) {
                        $exp_frm = $frm->export_json($msg, []);
                        if (isset($exp_frm)) {
                            $exp_formulas[] = $exp_frm;
                        }
                    }
                }
                $export_obj->formulas = $exp_formulas;

                // 9. add all views and view components to the export object
                // TODO Prio 2 create an array add function that does not add duplicates
                log_debug('export->get views');
                //$wrd_lst = $phr_lst_used->wrd_lst_all();
                $view_lst = $wrd_lst->view_lst($msg);
                $exp_view_lst = array();
                foreach ($view_lst as $view) {
                    if ($view->is_readable_by($usr)) {
                        $exp_view_lst[] = $view->export_json($msg, []);
                    }
                }
                $export_obj->views = $exp_view_lst;

                // 10. just for validating the import: add all formula results to the export
                log_debug('export->get formula results');
                $exp_results = array();
                if (!$frm_lst->is_empty()) {
                    foreach ($frm_lst->lst() as $frm) {
                        $res_lst = $frm->get_res_lst($msg);
                        $res_lst->filter_readable_by($usr);
                        if ($res_lst->lst() != null) {
                            foreach ($res_lst->lst() as $res) {
                                $exp_res = $res->export_json($msg, []);
                                if (isset($exp_res)) {
                                    $exp_results[] = $exp_res;
                                }
                            }
                        }
                    }
                }
                $export_obj->results = $exp_results;

                // 11. just for validating the import: add "screenshots" of the views to the export
                log_debug('export->get screenshots');
            }
        }

        log_debug('export->get ... done');
        return $export_obj;
    }


    /*
     * api
     */

    /**
     * the export of the phrases of an api request in the requested format e.g. for the download of an export view
     *
     * @param array $phr_names the names of the selected phrases (url_var::WORDS); for the csv the names of the formulas
     * @param array $phr_ids the ids of the selected phrases (url_var::PHRASE_LIST), a triple with its negative id
     * @param string $format one of the url_var::EXPORT_FORMAT_* values
     * @param user_message $msg with the requesting user, whose readable data is exported, and to report why nothing is exported
     * @return string the json, the xml or the csv; empty if nothing has been exported, which $msg explains
     */
    function file_text(array $phr_names, array $phr_ids, string $format, user_message $msg): string
    {
        $result = '';
        if (!array_key_exists($format, rest_ctrl::EXPORT_MIME_TYPES)) {
            $msg->add(msg_id::EXPORT_FORMAT_UNKNOWN, [msg_id::VAR_VALUE => $format]);
        } elseif ($format == url_var::EXPORT_FORMAT_CSV) {
            $result = $this->formula_result_csv($phr_names, $msg);
        } else {
            $phr_lst = $this->phrase_list_by_request($phr_names, $phr_ids, $msg);
            if ($phr_lst->is_empty()) {
                $msg->add(msg_id::EXPORT_PHRASE_MISSING, []);
            } elseif ($format == url_var::EXPORT_FORMAT_XML) {
                $result = new xml($msg->usr)->export_by_phrase_list($phr_lst, $msg);
            } else {
                $result = new json_io($msg->usr, $phr_lst)->export($msg);
            }
        }
        return $result;
    }

    /**
     * the csv of the results of the formulas given by name: per formula a block with the formula name as the head of
     * the number column and one row per result named by its phrases, the blocks separated by an empty line like the
     * csv of the value tables of a page
     *
     * @param array $frm_names the names of the formulas whose results are exported
     * @param user_message $msg with the requesting user, whose readable results are exported, and to report a name that is no formula
     * @return string the csv text; empty if a name is no formula, which $msg explains
     */
    function formula_result_csv(array $frm_names, user_message $msg): string
    {
        $lib = new library();
        $frm_lst = new formula_list($msg->usr);
        if ($frm_names == []) {
            $msg->add(msg_id::EXPORT_FORMULA_NAME_MISSING, []);
        } else {
            $frm_lst->load_by_names($frm_names, $msg);
            $frm_lst->filter_readable_by($msg->usr);
        }
        $blocks = [];
        foreach ($frm_names as $frm_name) {
            $frm = $frm_lst->get_by_name($frm_name, $msg);
            if ($frm == null) {
                $msg->add(msg_id::EXPORT_FORMULA_MISSING, [msg_id::VAR_FORMULA_NAME => $frm_name]);
            } else {
                $blocks[] = $lib->csv_text($this->formula_result_rows($frm, $msg));
            }
        }
        $result = '';
        if ($msg->is_ok()) {
            $result = implode("\n", $blocks);
        }
        return $result;
    }

    /**
     * @param array $phr_names the names of the selected phrases
     * @param array $phr_ids the ids of the selected phrases, a triple with its negative id
     * @param user_message $msg with the requesting user for whom the phrases are loaded
     * @return phrase_list the selected phrases with all related phrases (phrase_list::are) that the requesting user may read
     */
    private function phrase_list_by_request(array $phr_names, array $phr_ids, user_message $msg): phrase_list
    {
        $phr_lst = new phrase_list($msg->usr);
        if ($phr_names != []) {
            $phr_lst->load_by_names($phr_names, $msg);
        }
        if ($phr_ids != []) {
            $id_lst = new phrase_list($msg->usr);
            $id_lst->load_by_ids(new phr_ids($phr_ids), $msg);
            $phr_lst->merge($id_lst);
        }
        if (!$phr_lst->is_empty()) {
            $phr_lst = $phr_lst->are($msg);
        }
        // the related phrases are read for every user, so the readable phrases are selected after the expansion
        $phr_lst->filter_readable_by($msg->usr);
        return $phr_lst;
    }

    /**
     * @param formula $frm the formula whose results are exported
     * @param user_message $msg with the requesting user, whose readable results are exported
     * @return array the csv rows: the head with an empty corner and the formula name, then per result its phrase names and its number, sorted by the phrase names
     */
    private function formula_result_rows(formula $frm, user_message $msg): array
    {
        $res_lst = $frm->get_res_lst($msg);
        $res_lst->filter_readable_by($msg->usr);
        $rows = [];
        foreach ($res_lst->lst() as $res) {
            $rows[] = [$res->name(), (string)$res->number()];
        }
        sort($rows);
        return array_merge([['', $frm->name()]], $rows);
    }


}
