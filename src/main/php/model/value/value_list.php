<?php

/*

    model/value/value_list.php - to show or modify a list of values
    --------------------------

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

namespace model;

include_once SERVICE_EXPORT_PATH . 'value_list_exp.php';

use api\value_list_api;
use cfg\export\exp_obj;
use cfg\export\source_exp;
use cfg\export\value_list_exp;
use cfg\protection_type;
use cfg\share_type;
use html\button;
use html\html_base;

class value_list extends sandbox_list
{

    // to deprecate
    // fields to select the values
    public ?phrase $phr = null;              // show the values related to this phrase
    public ?phrase_list $phr_lst = null;     // show the values related to these phrases

    /*
     * im- and export link
     */

    // the field names used for the im- and export in the json or yaml format
    const FLD_EX_CONTEXT = 'context';
    const FLD_EX_VALUES = 'values';

    /*
     * construct and map
     */

    /**
     * always set the user because a value list is always user specific
     * @param user $usr the user who requested to see this value list
     */
    function __construct(user $usr)
    {
        parent::__construct($usr);
        $this->lst = array();
        $this->set_user($usr);
    }


    /*
     * cast
     */

    /**
     * @return value_list_api frontend API object filled with the relevant data of this object
     */
    function api_obj(): value_list_api
    {
        $api_obj = new value_list_api();
        $api_obj->set_lst($this->api_lst());
        return $api_obj;
    }

    /**
     * @return array with the API object of the values
     */
    function api_lst(): array
    {
        $api_lst = array();
        foreach ($this->lst as $val) {
            $api_lst[] = $val->api_obj();
        }

        return $api_lst;
    }


    /*
     * db loading
     */

    // TODO review the VAR and LIMIT definitions
    function load_sql(sql_db $db_con): sql_par
    {
        $lib = new library();
        $class = $lib->str_right_of_or_all(self::class, '\\');
        $db_con->set_type(sql_db::TBL_VALUE);
        $qp = new sql_par($class);
        $sql_name = $class . '_by_';
        $sql_name_ext = '';
        $sql_where = '';


        if ($this->phr != null) {
            if ($this->phr->id() <> 0) {
                if ($this->phr->is_word()) {
                    $sql_name_ext .= word::FLD_ID;
                } else {
                    $sql_name_ext .= triple::FLD_ID;
                }
            }
        } elseif ($this->phr_lst != '') {
            $sql_name_ext .= 'phrase_list';
        }
        if ($sql_name_ext == '') {
            log_err("Either a phrase or the phrase list and the user must be set to load a value list.", self::class . '->load_sql');
        } else {
            $sql_name .= $sql_name_ext;
            $db_con->set_name($sql_name);
            $db_con->set_usr($this->user()->id());
            $db_con->set_fields(value::FLD_NAMES);
            $db_con->set_usr_num_fields(value::FLD_NAMES_NUM_USR);
            $db_con->set_usr_only_fields(value::FLD_NAMES_USR_ONLY);
            $db_con->set_join_fields(array(phrase_group::FLD_ID), sql_db::TBL_PHRASE_GROUP);
            if ($this->phr->is_word()) {
                $db_con->set_join_fields(array(word::FLD_ID), sql_db::TBL_PHRASE_GROUP_WORD_LINK, phrase_group::FLD_ID, phrase_group::FLD_ID);
            } else {
                $db_con->set_join_fields(array(triple::FLD_ID), sql_db::TBL_PHRASE_GROUP_TRIPLE_LINK, phrase_group::FLD_ID, phrase_group::FLD_ID);
            }
            if ($this->phr != null) {
                if ($this->phr->id() <> 0) {
                    if ($this->phr->is_word()) {
                        $db_con->add_par(sql_db::PAR_INT, $this->phr->id());
                        $sql_where = 'l2.' . word::FLD_ID . ' = ' . $db_con->par_name();
                    } else {
                        $db_con->add_par(sql_db::PAR_INT, $this->phr->id() * -1);
                        $sql_where = 'l2.' . triple::FLD_ID . ' = ' . $db_con->par_name();
                    }
                }
            }
            $db_con->set_where_text($sql_where);
            //$db_con->set_page_par();
            $qp->name = $sql_name;
            $qp->sql = $db_con->select_by_set_id();
            $qp->par = $db_con->get_par();

        }

        return $qp;
    }

    /**
     * the general load function (either by word, triple or phrase list)
     *
     * @param int $page
     * @param int $size
     * @return bool
     */
    function load(int $page = 1, int $size = SQL_ROW_LIMIT): bool
    {

        global $db_con;
        $result = false;
        $lib = new library();

        // check the all minimal input parameters
        if (!$this->user()->is_set()) {
            log_err('The user must be set to load ' . self::class, self::class . '->load');
        } else {
            $qp = $this->load_sql($db_con);

            if ($db_con->get_where() == '') {
                log_err('The phrase must be set to load ' . self::class, self::class . '->load');
            } else {
                $db_con->usr_id = $this->user()->id();
                $db_val_lst = $db_con->get($qp);
                foreach ($db_val_lst as $db_val) {
                    if (is_null($db_val[sandbox::FLD_EXCLUDED]) or $db_val[sandbox::FLD_EXCLUDED] == 0) {
                        $val = new value($this->user());
                        $val->row_mapper($db_val);
                        $this->lst[] = $val;
                        $result = true;
                    }
                }
                log_debug($lib->dsp_count($this->lst));
            }
        }

        return $result;
    }

    /**
     * create an SQL statement to retrieve a list of values linked to a phrase from the database
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param phrase $phr if set to get all values for this phrase
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_by_phr_sql(sql_db $db_con, phrase $phr): sql_par
    {
        $db_con->set_type(sql_db::TBL_VALUE);
        $qp = new sql_par(self::class);
        $qp->name .= 'phrase_id';

        $db_con->set_name($qp->name);
        $db_con->set_usr($this->user()->id());
        $db_con->set_fields(value::FLD_NAMES);
        $db_con->set_usr_num_fields(value::FLD_NAMES_NUM_USR);
        $db_con->set_usr_only_fields(value::FLD_NAMES_USR_ONLY);
        $db_con->set_join_fields(
            array(value::FLD_ID), sql_db::TBL_VALUE_PHRASE_LINK,
            value::FLD_ID, value::FLD_ID,
            phrase::FLD_ID, $phr->id());
        $qp->sql = $db_con->select_by_set_id();
        $qp->par = $db_con->get_par();

        return $qp;
    }

    /**
     * load a list of values that are related to a phrase or a list of phrases
     *
     * @param phrase $phr if set to get all values for this phrase
     * @return bool true if at least one value has been loaded
     */
    function load_by_phr(phrase $phr, int $limit = 0): bool
    {
        global $db_con;
        $result = false;
        $lib = new library();

        if ($limit <= 0) {
            $limit = SQL_ROW_LIMIT;
        }

        $qp = $this->load_by_phr_sql($db_con, $phr);

        $db_con->usr_id = $this->user()->id();
        $db_val_lst = $db_con->get($qp);
        foreach ($db_val_lst as $db_val) {
            if (is_null($db_val[sandbox::FLD_EXCLUDED]) or $db_val[sandbox::FLD_EXCLUDED] == 0) {
                $val = new value($this->user());
                $val->row_mapper($db_val);
                $this->lst[] = $val;
                log_debug($lib->dsp_count($this->lst));
                $result = true;
            }
        }
        return $result;
    }

    function load_all_sql(): string
    {
        global $db_con;
        $sql = "SELECT v.value_id,
                      u.value_id AS user_value_id,
                      v.user_id,
                    " . $db_con->get_usr_field(value::FLD_VALUE, 'v', 'u', sql_db::FLD_FORMAT_VAL) . ",
                    " . $db_con->get_usr_field(sandbox::FLD_EXCLUDED, 'v', 'u', sql_db::FLD_FORMAT_VAL) . ",
                    " . $db_con->get_usr_field(value::FLD_LAST_UPDATE, 'v', 'u', sql_db::FLD_FORMAT_VAL) . ",
                    " . $db_con->get_usr_field(source::FLD_ID, 'v', 'u', sql_db::FLD_FORMAT_VAL) . ",
                      v.phrase_group_id
                  FROM " . $db_con->get_table_name_esc(sql_db::TBL_VALUE) . " v 
            LEFT JOIN user_values u ON u.value_id = v.value_id 
                                    AND u.user_id = " . $this->user()->id() . " 
                WHERE v.value_id IN ( SELECT value_id 
                                        FROM value_phrase_links 
                                        WHERE phrase_id IN (" . implode(",", $this->phr_lst->id_lst()) . ")
                                    GROUP BY value_id )
              ORDER BY v.phrase_group_id;";
        return $sql;
    }

    /**
     * load a list of values that are related to one
     */
    function load_all(): void
    {

        global $db_con;
        $lib = new library();

        // the id and the user must be set
        if (isset($this->phr_lst)) {
            if (count($this->phr_lst->id_lst()) > 0 and !is_null($this->user()->id())) {
                log_debug('for ' . $this->phr_lst->dsp_id());
                $sql = $this->load_all_sql();
                $db_con->usr_id = $this->user()->id();
                $db_val_lst = $db_con->get_old($sql);
                if ($db_val_lst != false) {
                    foreach ($db_val_lst as $db_val) {
                        if (is_null($db_val[sandbox::FLD_EXCLUDED]) or $db_val[sandbox::FLD_EXCLUDED] == 0) {
                            $val = new value($this->user());
                            $val->row_mapper($db_val);
                            $this->lst[] = $val;
                        }
                    }
                }
                log_debug($lib->dsp_count($this->lst));
            }
        }
        log_debug('done');
    }

    /**
     * build the sql statement based in the number of words
     * create an SQL statement to retrieve the parameters of a list of phrases from the database
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param bool $get_name to create the SQL statement name for the predefined SQL within the same function to avoid duplicating if in case of more than on where type
     * @return string the SQL statement base on the parameters set in $this
     */
    function load_by_phr_lst_sql(sql_db $db_con, bool $get_name = false): string
    {

        $sql_name = 'phr_lst_by_';
        $phr_ids = $this->phr_lst->id_lst();
        if (count($phr_ids) > 0) {
            $sql_name .= count($phr_ids) . 'ids';
        } else {
            log_err("At lease on phrase ID must be set to load a value list.", "value_list->load_by_phr_lst_sql");
        }

        $sql = '';
        $sql_where = '';
        $sql_from = '';
        $sql_pos = 0;
        foreach ($phr_ids as $phr_id) {
            if ($phr_id > 0) {
                $sql_pos = $sql_pos + 1;
                $sql_from = $sql_from . " value_phrase_links l" . $sql_pos . ", ";
                if ($sql_pos == 1) {
                    $sql_where = $sql_where . " WHERE l" . $sql_pos . ".phrase_id = " . $phr_id . " AND l" . $sql_pos . ".value_id = v.value_id ";
                } else {
                    $sql_where = $sql_where . "   AND l" . $sql_pos . ".phrase_id = " . $phr_id . " AND l" . $sql_pos . ".value_id = v.value_id ";
                }
            }
        }

        if ($sql_where <> '') {
            $sql = "SELECT DISTINCT v.value_id,
                    " . $db_con->get_usr_field(value::FLD_VALUE, 'v', 'u', sql_db::FLD_FORMAT_VAL) . ",
                    " . $db_con->get_usr_field(sandbox::FLD_EXCLUDED, 'v', 'u', sql_db::FLD_FORMAT_VAL) . ",
                    " . $db_con->get_usr_field(value::FLD_LAST_UPDATE, 'v', 'u', sql_db::FLD_FORMAT_VAL) . ",
                    " . $db_con->get_usr_field(source::FLD_ID, 'v', 'u', sql_db::FLD_FORMAT_VAL) . ",
                       v.user_id,
                       v.phrase_group_id
                  FROM " . $db_con->get_table_name_esc(sql_db::TBL_VALUE) . " v 
             LEFT JOIN user_values u ON u.value_id = v.value_id 
                                    AND u.user_id = " . $this->user()->id() . " 
                 WHERE v.value_id IN ( SELECT DISTINCT v.value_id 
                                         FROM " . $sql_from . "
                                              " . $db_con->get_table_name_esc(sql_db::TBL_VALUE) . " v
                                              " . $sql_where . " )
              ORDER BY v.phrase_group_id;";
        }

        if ($get_name) {
            $result = $sql_name;
        } else {
            $result = $sql;
        }
        return $result;
    }

    /**
     * load a list of values that are related to all words of the list
     */
    function load_by_phr_lst_old(): void
    {

        global $db_con;
        $lib = new library();

        // the word list and the user must be set
        if (count($this->phr_lst->id_lst()) > 0 and !is_null($this->user()->id())) {
            $sql = $this->load_by_phr_lst_sql($db_con);

            if ($sql <> '') {
                $db_con->usr_id = $this->user()->id();
                $db_val_lst = $db_con->get_old($sql);
                if ($db_val_lst != false) {
                    foreach ($db_val_lst as $db_val) {
                        if (is_null($db_val[sandbox::FLD_EXCLUDED]) or $db_val[sandbox::FLD_EXCLUDED] == 0) {
                            $val = new value($this->user());
                            //$val->row_mapper($db_val);
                            $val->set_id($db_val[value::FLD_ID]);
                            $val->owner_id = $db_val[sandbox::FLD_USER];
                            $val->set_number($db_val[value::FLD_VALUE]);
                            $val->set_source_id($db_val[source::FLD_ID]);
                            $val->last_update = $lib->get_datetime($db_val[value::FLD_LAST_UPDATE]);
                            $val->grp->set_id($db_val[phrase_group::FLD_ID]);
                            $this->lst[] = $val;
                        }
                    }
                }
            }
            log_debug($lib->dsp_count($this->lst));
        }
    }

    /**
     * set the word objects for all value in the list if needed
     * not included in load, because sometimes loading of the word objects is not needed
     */
    function load_phrases(): void
    {
        // loading via word group is the most used case, because to save database space and reading time the value is saved with the word group id
        foreach ($this->lst as $val) {
            $val->load_phrases();
        }
    }


    /*
     * im- and export
     */

    /**
     * import a value from an external object
     *
     * @param array $json_obj an array with the data of the json object
     * @param bool $do_save can be set to false for unit testing
     * @return user_message the status of the import and if needed the error messages that should be shown to the user
     */
    function import_obj(array $json_obj, bool $do_save = true): user_message
    {
        global $share_types;
        global $protection_types;

        log_debug();
        $result = new user_message();
        $lib = new library();

        $val = new value($this->user());
        $phr_lst = new phrase_list($this->user());

        foreach ($json_obj as $key => $value) {

            if ($key == self::FLD_EX_CONTEXT) {
                $phr_lst = new phrase_list($this->user());
                $result->add($phr_lst->import_lst($value, $do_save));
                $val->grp = $phr_lst->get_grp($do_save);
            }

            if ($key == exp_obj::FLD_TIMESTAMP) {
                if (strtotime($value)) {
                    $val->time_stamp = $lib->get_datetime($value, $val->dsp_id(), 'JSON import');
                } else {
                    $result->add_message('Cannot add timestamp "' . $value . '" when importing ' . $val->dsp_id());
                }
            }

            if ($key == share_type::JSON_FLD) {
                $val->share_id = $share_types->id($value);
            }

            if ($key == protection_type::JSON_FLD) {
                $val->protection_id = $protection_types->id($value);
            }

            if ($key == source_exp::FLD_REF) {
                $src = new source($this->user());
                $src->set_name($value);
                if ($result->is_ok() and $do_save) {
                    $src->load_by_name($value);
                    if ($src->id() == 0) {
                        $result->add_message($src->save());
                    }
                }
                $val->source = $src;
            }

            if ($key == self::FLD_EX_VALUES) {
                foreach ($value as $val_entry) {
                    foreach ($val_entry as $val_key => $val_number) {
                        $val_to_add = clone $val;
                        $phr_lst_to_add = clone $phr_lst;
                        $val_phr = new phrase($this->user());
                        if ($do_save) {
                            $val_phr->load_by_name($val_key);
                        } else {
                            $val_phr->set_name($val_key, word::class);
                        }
                        $phr_lst_to_add->add($val_phr);
                        $val_to_add->set_number($val_number);
                        $val_to_add->grp = $phr_lst_to_add->get_grp($do_save);
                        if ($do_save) {
                            $result->add_message($val_to_add->save());
                        }
                        $this->lst[] = $val_to_add;
                    }
                }
            }

        }

        return $result;
    }

    /**
     * create a value list object for the JSON export
     */
    function export_obj(bool $do_load = true): exp_obj
    {
        log_debug();
        $result = new value_list_exp();
        global $share_types;
        global $protection_types;

        // reload the value parameters
        if ($do_load) {
            log_debug();
            $this->load();
        }

        if (count($this->lst) > 1) {

            // use the first value to get the context parameter
            $val0 = $this->lst[0];
            // use the second value to detect the context phrases
            $val1 = $this->lst[1];

            // get phrase names of the first value
            $phr_lst1 = $val0->phr_names();
            // get phrase names of the second value
            $phr_lst2 = $val1->phr_names();
            // add common phrase of the first and second value
            $phr_lst = array();
            if (count($phr_lst1) > 0 and count($phr_lst2) > 0) {
                $phr_lst = array_intersect($phr_lst1, $phr_lst2);
                $result->context = $phr_lst;
            }

            // order the context to make the string result reproducible
            ksort($result->context);


            // add the share type
            log_debug('get share');
            if ($val0->share_id > 0 and $val0->share_id <> $share_types->id(share_type::PUBLIC)) {
                $result->share = $val0->share_type_code_id();
            }

            // add the protection type
            log_debug('get protection');
            if ($val0->protection_id > 0 and $val0->protection_id <> $protection_types->id(protection_type::NO_PROTECT)) {
                $result->protection = $val0->protection_type_code_id();
            }

            // add the source
            if ($val0->source != null) {
                $result->source = $val0->source->name();
            }

            foreach ($this->lst as $val) {
                $phr_name = array_diff($val->phr_names(), $phr_lst);
                if (count($phr_name) > 0) {
                    $val_entry = array();
                    $key_name = array_values($phr_name)[0];
                    $val_entry[$key_name] = $val->number();
                    $result->values[] = $val_entry;
                }
            }
        }

        log_debug(json_encode($result));
        return $result;
    }


    /*
     * data retrieval functions
     */

    /**
     * get a list with all time phrase used in the complete value list
     */
    function time_lst(): phrase_list
    {
        $lib = new library();
        $all_ids = array();
        foreach ($this->lst as $val) {
            $all_ids = array_unique(array_merge($all_ids, array($val->time_id)));
        }
        $phr_lst = new phrase_list($this->user());
        if (count($all_ids) > 0) {
            $phr_lst->load_names_by_ids(new phr_ids($all_ids));
        }
        log_debug($lib->dsp_count($phr_lst->lst));
        return $phr_lst;
    }

    /**
     * @return phrase_list list with all unique phrase used in the complete value list
     */
    function phr_lst(): phrase_list
    {
        log_debug('by ids (needs review)');
        $phr_lst = new phrase_list($this->user());
        $lib = new library();

        foreach ($this->lst as $val) {
            if (!isset($val->phr_lst)) {
                $val->load();
                $val->load_phrases();
            }
            $phr_lst->merge($val->phr_lst);
        }

        log_debug($lib->dsp_count($phr_lst->lst));
        return $phr_lst;
    }

    /**
     * @return phrase_list  list with all unique phrase including the time phrase
     */
    function phr_lst_all(): phrase_list
    {
        log_debug();

        $phr_lst = $this->phr_lst();
        $phr_lst->merge($this->time_lst());

        log_debug('done');
        return $phr_lst;
    }

    /**
     * @return word_list list of all words used for the value list
     */
    function wrd_lst(): word_list
    {
        log_debug();

        $phr_lst = $this->phr_lst_all();
        $wrd_lst = $phr_lst->wrd_lst_all();

        log_debug('done');
        return $wrd_lst;
    }

    /**
     * get a list of all words used for the value list
     */
    function source_lst(): array
    {
        log_debug();
        $result = array();
        $src_ids = array();

        foreach ($this->lst as $val) {
            if ($val->source_id > 0) {
                log_debug('test id ' . $val->source_id);
                if (!in_array($val->source_id, $src_ids)) {
                    log_debug('add id ' . $val->source_id);
                    if (!isset($val->source)) {
                        log_debug('load id ' . $val->source_id);
                        $val->load_source();
                        log_debug('loaded ' . $val->source->name);
                    } else {
                        if ($val->source_id <> $val->source->id) {
                            log_debug('load id ' . $val->source_id);
                            $val->load_source();
                            log_debug('loaded ' . $val->source->name);
                        }
                    }
                    $result[] = $val->source;
                    $src_ids[] = $val->source_id;
                    log_debug('added ' . $val->source->name);
                }
            }
        }

        log_debug('done');
        return $result;
    }

    /*
    filter and select functions
    */

    /**
     * @returns value_list that contains only values that match the time word list
     */
    function filter_by_time($time_lst): value_list
    {
        log_debug();
        $lib = new library();
        $val_lst = array();
        foreach ($this->lst as $val) {
            // only include time specific value
            if ($val->time_id > 0) {
                // only include values within the specific time periods
                if (in_array($val->time_id, $time_lst->ids)) {
                    $val_lst[] = $val;
                    log_debug('include ' . $val->name());
                } else {
                    log_debug('excluded ' . $val->name() . ' because outside the specified time periods');
                }
            } else {
                log_debug('excluded ' . $val->name() . ' because this is not time specific');
            }
        }
        $result = clone $this;
        $result->lst = $val_lst;

        log_debug($lib->dsp_count($result->lst));
        return $result;
    }

    /**
     * return a value list object that contains only values that match at least one phrase from the phrase list
     */
    function filter_by_phrase_lst($phr_lst): value_list
    {
        $lib = new library();
        log_debug($lib->dsp_count($this->lst) . ' values by ' . $phr_lst->name());
        $result = array();
        foreach ($this->lst as $val) {
            //$val->load_phrases();
            $val_phr_lst = $val->phr_lst;
            if (isset($val_phr_lst)) {
                log_debug('val phrase list ' . $val_phr_lst->name());
            } else {
                log_debug('val no value phrase list');
            }
            $found = false;
            foreach ($val_phr_lst->lst as $phr) {
                //zu_debug('value_list->filter_by_phrase_lst val is '.$phr->name().' in '.$phr_lst->name());
                if (in_array($phr->name(), $phr_lst->names())) {
                    if (isset($val_phr_lst)) {
                        log_debug('val phrase list ' . $val_phr_lst->name() . ' is found in ' . $phr_lst->name());
                    } else {
                        log_debug('val found, but no value phrase list');
                    }
                    $found = true; // to make sure that each value is only added once; an improvement could be to stop searching after a phrase is found
                }
            }
            if ($found) {
                $result[] = $val;
            }
        }
        $this->lst = $result;

        log_debug($lib->dsp_count($this->lst));
        return $this;
    }

    /**
     * selects from a val_lst_phr the best matching value
     * best matching means that all words from word_ids must be matching and the least additional words, because this would be a more specific value
     * used by value_list_dsp->dsp_table
     */
    function get_from_lst($word_ids)
    {
        asort($word_ids);
        log_debug("ids " . implode(",", $word_ids) . ".");
        $lib = new library();

        $found = false;
        $result = null;
        foreach ($this->lst as $val) {
            if (!$found) {
                log_debug("check " . implode(",", $word_ids) . " with (" . implode(",", $val->ids) . ")");
                $wrd_missing = $lib->lst_not_in($word_ids, $val->ids);
                if (empty($wrd_missing)) {
                    // potential result candidate, because the value has all needed words
                    log_debug("can (" . $val->number() . ")");
                    $wrd_extra = $lib->lst_not_in($val->ids, $word_ids);
                    if (empty($wrd_extra)) {
                        // if there is no extra word, it is the correct value
                        log_debug("is (" . $val->number() . ")");
                        $found = true;
                        $result = $val;
                    } else {
                        log_debug("is not, because (" . implode(",", $wrd_extra) . ")");
                    }
                }
            }
        }

        log_debug("done " . $result->number);
        return $result;
    }

    /**
     * selects from a val_lst_wrd the best matching value
     * best matching means that all words from word_ids must be matching and the least additional words, because this would be a more specific value
     * used by value_list_dsp->dsp_table
     */
    function get_by_grp($grp, $time)
    {
        log_debug("value_list->get_by_grp " . $grp->auto_name . ".");

        $found = false;
        $result = null;
        $row = 0;
        foreach ($this->lst as $val) {
            if (!$found) {
                // show only a few debug messages for a useful result
                if ($row < 6) {
                    log_debug("value_list->get_by_grp check if " . $val->grp_id . " = " . $grp->id . " and " . $val->time_id . " = " . $time->id . ".");
                }
                if ($val->grp_id == $grp->id
                    and $val->time_id == $time->id) {
                    $found = true;
                    $result = $val;
                } else {
                    if (!isset($val->grp)) {
                        log_debug("load group");
                        $val->load_phrases();
                    }
                    if (isset($val->grp)) {
                        if ($row < 6) {
                            log_debug('check if all of ' . $grp->name() . ' are in ' . $val->grp->name() . ' and value should be used');
                        }
                        if ($val->grp->has_all_phrases_of($grp)
                            and $val->time_id == $time->id) {
                            log_debug('all of ' . $grp->name() . ' are in ' . $val->grp->name() . ' so value is used');
                            $found = true;
                            $result = $val;
                        }
                    }
                }
            }
            $row++;
        }

        log_debug("done " . $result->number);
        return $result;
    }

    /**
     * @return bool true if the list contains at least one value
     */
    function has_values(): bool
    {
        $result = false;
        if (count($this->lst) > 0) {
            $result = true;
        }
        return $result;
    }


    /*
     * convert functions
     */

    /**
     * return a list of phrase groups for all values of this list
     */
    function phrase_groups(): phrase_group_list
    {
        log_debug();
        $lib = new library();
        $grp_lst = new phrase_group_list($this->user());
        foreach ($this->lst as $val) {
            if (!isset($val->grp)) {
                $val->load_grp_by_id();
            }
            if (isset($val->grp)) {
                $grp_lst->lst[] = $val->grp;
            } else {
                log_err("The phrase group for value " . $val->id . " cannot be loaded.", "value_list->phrase_groups");
            }
        }

        log_debug($lib->dsp_count($grp_lst->lst));
        return $grp_lst;
    }


    /**
     * return a list of phrases used for each value
     */
    function common_phrases(): phrase_list
    {
        $lib = new library();
        $grp_lst = $this->phrase_groups();
        $phr_lst = $grp_lst->common_phrases();
        log_debug($lib->dsp_count($phr_lst->lst));
        return $phr_lst;
    }

    /*
     * check / database consistency functions
     */

    /**
     * check the consistency for all values
     * so get the words and triples linked from the word group
     *    and update the slave table value_phrase_links (which should be renamed to value_phrase_links)
     * TODO split into smaller sections by adding LIMIT to the query and start a loop
     */
    function check_all(): bool
    {

        global $db_con;
        $lib = new library();
        $result = true;

        // the id and the user must be set
        $db_con->set_type(sql_db::TBL_VALUE);
        $db_con->set_usr($this->user()->id());
        $sql = $db_con->select_by_set_id();
        $db_val_lst = $db_con->get_old($sql);
        foreach ($db_val_lst as $db_val) {
            $val = new value($this->user());
            $val->load_by_id($db_val[value::FLD_ID], value::class);
            if (!$val->check()) {
                $result = false;
            }
            log_debug($lib->dsp_count($this->lst));
        }
        log_debug($lib->dsp_count($this->lst));
        return $result;
    }

    /**
     * to be integrated into load
     * list of values related to a formula
     * described by the word to which the formula is assigned
     * and the words used in the formula
     */
    function load_frm_related($phr_id, $phr_ids, $user_id)
    {
        log_debug("value_list->load_frm_related (" . $phr_id . ",ft" . implode(",", $phr_ids) . ",u" . $user_id . ")");

        global $db_con;
        $result = array();

        if ($phr_id > 0 and !empty($phr_ids)) {
            $sql = "SELECT l1.value_id
                FROM value_phrase_links l1,
                    value_phrase_links l2
              WHERE l1.value_id = l2.value_id
                AND l1.phrase_id = " . $phr_id . "
                AND l2.phrase_id IN (" . implode(",", $phr_ids) . ");";
            //$db_con = New mysql;
            $db_con->usr_id = $this->user()->id();
            $db_lst = $db_con->get_old($sql);
            foreach ($db_lst as $db_val) {
                $result = $db_val[value::FLD_ID];
            }
        }

        log_debug(implode(",", $result));
        return $result;
    }

    /*
     * group words
     * kind of similar to zu_sql_val_lst_wrd
    function load_frm_related_grp_phrs_part($val_ids, $phr_id, $phr_ids, $user_id): array
    {
        log_debug("(v" . implode(",", $val_ids) . ",t" . $phr_id . ",ft" . implode(",", $phr_ids) . ",u" . $user_id . ")");

        global $db_con;
        $result = array();

        if ($phr_id > 0 and !empty($phr_ids) and !empty($val_ids)) {
            $phr_ids[] = $phr_id; // add the main word to the exclude words
            $sql = "SELECT l.value_id,
                    " . $db_con->get_usr_field('word_value', 'v', 'u', sql_db::FLD_FORMAT_VAL) . ",
                    l.phrase_id, 
                    v.excluded, 
                    u.excluded AS user_excluded 
                FROM value_phrase_links l,
                    " . $db_con->get_table_name_esc(sql_db::TBL_VALUE) . " v 
          LEFT JOIN user_values u ON v.value_id = u.value_id AND u.user_id = " . $user_id . " 
              WHERE l.value_id = v.value_id
                AND l.phrase_id NOT IN (" . implode(",", $phr_ids) . ")
                AND l.value_id IN (" . implode(",", $val_ids) . ")
                AND (u.excluded IS NULL OR u.excluded = 0) 
            GROUP BY l.value_id, l.phrase_id;";
            //$db_con = New mysql;
            $db_con->usr_id = $this->user()->id();
            $db_lst = $db_con->get_old($sql);
            $value_id = -1; // set to an id that is never used to force the creation of a new entry at start
            foreach ($db_lst as $db_val) {
                if ($value_id == $db_val[value::FLD_ID]) {
                    $phr_result[] = $db_val[phrase::FLD_ID];
                } else {
                    if ($value_id >= 0) {
                        // remember the previous values
                        $row_result[] = $phr_result;
                        $result[$value_id] = $row_result;
                    }
                    // remember the values for a new result row
                    $value_id = $db_val[value::FLD_ID];
                    $val_num = $db_val['word_value'];
                    $row_result = array();
                    $row_result[] = $val_num;
                    $phr_result = array();
                    $phr_result[] = $db_val[phrase::FLD_ID];
                }
            }
            if ($value_id >= 0) {
                // remember the last values
                $row_result[] = $phr_result;
                $result[$value_id] = $row_result;
            }
        }

        log_debug(zu_lst_dsp($result));
        return $result;
    }
     */


    /*
    private function common_phrases(): phrase_list
    {

    }
    */

    /**
     * return the html code to display all values related to a given word
     * $phr->id is the related word that should not be included in the display
     * $this->user()->id() is a parameter, because the viewer must not be the owner of the value
     * TODO add back
     */
    function html($back): string
    {
        $lib = new library();
        $html = new html_base();
        log_debug($lib->dsp_count($this->lst));
        $result = '';

        $html = new html_base();

        // get common words
        $common_phr_ids = array();
        foreach ($this->lst as $val) {
            if ($val->check() > 0) {
                log_warning('The group id for value ' . $val->id . ' has not been updated, but should now be correct.', "value_list->html");
            }
            $val->load_phrases();
            log_debug('value_list->html loaded');
            $val_phr_lst = $val->phr_lst;
            if ($val_phr_lst->lst != null) {
                if (count($val_phr_lst->lst) > 0) {
                    log_debug('get words ' . $val->phr_lst->dsp_id() . ' for "' . $val->number() . '" (' . $val->id . ')');
                    if (empty($common_phr_ids)) {
                        $common_phr_ids = $val_phr_lst->id_lst();
                    } else {
                        $common_phr_ids = array_intersect($common_phr_ids, $val_phr_lst->id_lst());
                    }
                }
            }
        }

        log_debug('common ');
        $common_phr_ids = array_diff($common_phr_ids, array($this->phr->id()));  // exclude the list word
        $common_phr_ids = array_values($common_phr_ids);            // cleanup the array

        // display the common words
        log_debug('common dsp');
        if (!empty($common_phr_ids)) {
            $common_phr_lst = new word_list($this->user());
            $common_phr_lst->load_by_ids($common_phr_ids);
            $common_phr_lst_dsp = $common_phr_lst->dsp_obj();
            $result .= ' in (' . implode(",", $common_phr_lst_dsp->names_linked()) . ')<br>';
        }

        // instead of the saved result maybe display the calculated result based on formulas that matches the word pattern
        log_debug('tbl_start');
        $result .= $html->dsp_tbl_start();

        // the reused button object
        $btn = new button;

        // to avoid repeating the same words in each line and to offer a useful "add new value"
        $last_phr_lst = array();

        log_debug('add new button');
        foreach ($this->lst as $val) {
            //$this->user()->id()  = $val->user()->id();

            // get the words
            $val->load_phrases();
            if (isset($val->phr_lst)) {
                $val_phr_lst = $val->phr_lst;

                // remove the main word from the list, because it should not be shown on each line
                log_debug('remove main ' . $val->id);
                $dsp_phr_lst = $val_phr_lst->dsp_obj();
                log_debug('cloned ' . $val->id);
                if (isset($this->phr)) {
                    if ($this->phr->id() != null) {
                        $dsp_phr_lst->diff_by_ids(array($this->phr->id()));
                    }
                }
                log_debug('removed ' . $this->phr->id());
                $dsp_phr_lst->diff_by_ids($common_phr_ids);
                // remove the words of the previous row, because it should not be shown on each line
                if (isset($last_phr_lst->ids)) {
                    $dsp_phr_lst->diff_by_ids($last_phr_lst->ids);
                }

                //if (isset($val->time_phr)) {
                log_debug('add time ' . $val->id);
                if ($val->time_phr != null) {
                    if ($val->time_phr->id > 0) {
                        $time_phr = new phrase($val->user());
                        $time_phr->set_id($val->time_phr->id);
                        $time_phr->load_by_obj_par();
                        $val->time_phr = $time_phr;
                        $dsp_phr_lst->add($time_phr);
                        log_debug('add time word ' . $val->time_phr->name());
                    }
                }

                $result .= '  <tr>';
                $result .= '    <td>';
                log_debug('linked words ' . $val->id);
                $ref_edit = $val->dsp_obj()->ref_edit();
                $result .= '      ' . $dsp_phr_lst->name_linked() . $ref_edit;
                log_debug('linked words ' . $val->id . ' done');
                // to review
                // list the related formula values
                $fv_lst = new formula_value_list($this->user());
                $fv_lst->load_by_val($val);
                $result .= $fv_lst->frm_links_html();
                $result .= '    </td>';
                log_debug('formula results ' . $val->id . ' loaded');

                if ($last_phr_lst != $val_phr_lst) {
                    $last_phr_lst = $val_phr_lst;
                    $result .= '    <td>';
                    $result .= \html\btn_add_value($val_phr_lst, Null, $this->phr->id());

                    $result .= '    </td>';
                }
                $result .= '    <td>';
                $result .= '      ' . $btn->edit_value($val_phr_lst, $val->id, $this->phr->id());
                $result .= '    </td>';
                $result .= '    <td>';
                $result .= '      ' . $btn->del_value($val_phr_lst, $val->id, $this->phr->id());
                $result .= '    </td>';
                $result .= '  </tr>';
            }
        }
        log_debug('add new button done');

        $result .= $html->dsp_tbl_end();

        // allow the user to add a completely new value
        log_debug('new');
        if (empty($common_phr_ids)) {
            $common_phr_lst_new = new word_list($this->user());
            $common_phr_ids[] = $this->phr->id();
            $common_phr_lst_new->load_by_ids($common_phr_ids);
        }

        $common_phr_lst = $common_phr_lst->phrase_lst();

        // TODO review probably wrong call from /var/www/default/src/main/php/model/view/view.php(267): view_component_dsp->all(Object(word_dsp), 291, 17
        /*
        if (get_class($this->phr) == word::class or get_class($this->phr) == word_dsp::class) {
            $this->phr = $this->phr->phrase();
        }
        */
        if ($common_phr_lst->is_valid()) {
            if (!empty($common_phr_lst->lst)) {
                $common_phr_lst->add($this->phr);
                $phr_lst_dsp = $common_phr_lst->dsp_obj();
                $result .= $phr_lst_dsp->btn_add_value($back);
            }
        }

        log_debug("value_list->html ... done");

        return $result;
    }

    /**
     * delete all loaded values e.g. to delete all the values linked to a phrase
     * @return user_message
     */
    function del(): user_message
    {
        $result = new user_message();

        foreach ($this->lst as $val) {
            $result->add($val->del());
        }
        return new user_message();
    }

}
