<?php

/*

    triple.php - the object that links two words (an RDF triple)
    -------------

    A link can also be used in replacement for a word
    e.g. "Zurich (Company)" where the link "Zurich is a company" is used

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

global $phrase_types;

use api\triple_api;
use cfg\share_type;
use cfg\protection_type;
use export\exp_obj;
use export\triple_exp;
use html\api;
use html\button;
use html\triple_dsp;

class triple extends user_sandbox_link_description
{

    /*
     * database link
     */

    // object specific database and JSON object field names
    const FLD_ID = 'triple_id';
    const FLD_FROM = 'from_phrase_id';
    const FLD_TO = 'to_phrase_id';
    const FLD_TYPE = 'word_type_id';
    const FLD_NAME = 'triple_name';  // the name used which must be unique within the terms of the user
    const FLD_NAME_GIVEN = 'name_given'; // the name set by the user, which can be null if the generated name should be used
    const FLD_NAME_AUTO = 'name_generated'; // the generated name is saved in the database for database base unique check
    const FLD_VALUES = 'values';
    const FLD_COND_ID = 'triple_condition_id';
    const FLD_COND_TYPE = 'triple_condition_type_id';

    // all database field names excluding the id and excluding the user specific fields
    const FLD_NAMES = array(
        self::FLD_TYPE,
        self::FLD_COND_ID,
        self::FLD_COND_TYPE
    );
    // list of the link database field names
    const FLD_NAMES_LINK = array(
        self::FLD_FROM,
        verb::FLD_ID,
        self::FLD_TO
    );
    // list of the user specific database field names
    const FLD_NAMES_USR = array(
        self::FLD_NAME,
        self::FLD_NAME_GIVEN,
        self::FLD_NAME_AUTO,
        sql_db::FLD_DESCRIPTION
    );
    // list of the user specific numeric database field names
    const FLD_NAMES_NUM_USR = array(
        self::FLD_VALUES,
        user_sandbox::FLD_EXCLUDED,
        user_sandbox::FLD_SHARE,
        user_sandbox::FLD_PROTECT
    );

    /*
     * im- and export link
     */

    // the field names used for the im- and export in the json or yaml format
    const FLD_EX_FROM = 'from';
    const FLD_EX_TO = 'to';
    const FLD_EX_VERB = 'verb';

    /*
     * for system testing
     */

    const TN_READ = 'Pi';
    const TN_READ_NAME = 'Pi (math)';

    /*
     * object vars
     */

    // the word link object
    public phrase $from;            // the first object (either word, triple or group)
    public verb $verb;              // the link type object
    public phrase $to;              // the second object (either word, triple or group)
    private ?string $name_given;     // the name manually set by the user, which can be empty
    private string $name_generated; // the generated name based on the linked objects and saved in the database for faster searching
    public ?int $values;            // the total number of values linked to this triple as an indication how common the triple is and to sort the triples

    /*
     * construct and map
     */

    /**
     * define the settings for this triple object
     * @param user $usr the user who requested to see this triple
     */
    function __construct(
        user   $usr,
        string $name = '',
        string $from = '',
        string $verb = '',
        string $to = ''
    )
    {
        parent::__construct($usr);
        $this->reset();
        $this->obj_type = user_sandbox::TYPE_LINK;
        $this->obj_name = sql_db::TBL_TRIPLE;

        $this->name = $name;
        $this->name_given = null;
        $this->name_generated = '';
        $this->rename_can_switch = UI_CAN_CHANGE_triple_NAME;

        // also create the link objects because there is now case where they are supposed to be null
        $this->create_objects($from, $verb, $to);
    }

    /**
     * reset the in memory fields used e.g. if some ids are updated
     */
    function reset(): void
    {
        $this->id = null;
        $this->usr_cfg_id = null;
        $this->owner_id = null;
        $this->values = null;
        $this->excluded = null;
        $this->name = '';
        $this->name_given = null;
        $this->name_generated = '';

        $this->create_objects();
    }

    private function create_objects(
        string $from = '',
        string $verb = '',
        string $to = ''
    )
    {
        $this->from = new phrase($this->usr, $from);
        $this->verb = new verb(0, $verb);
        $this->to = new phrase($this->usr, $to);
    }

    /**
     * map the database fields to the object fields
     *
     * @param array $db_row with the data directly from the database
     * @param bool $map_usr_fields false for using the standard protection settings for the default triple used for all users
     * @param string $id_fld the name of the id field as defined in this child and given to the parent
     * @return bool true if the triple is loaded and valid
     */
    function row_mapper(array $db_row, bool $map_usr_fields = true, string $id_fld = self::FLD_ID): bool
    {
        $result = parent::row_mapper($db_row, $map_usr_fields, self::FLD_ID);
        if ($result) {
            $this->from->id = $db_row[self::FLD_FROM];
            $this->to->id = $db_row[self::FLD_TO];
            $this->verb->id = $db_row[verb::FLD_ID];
            $this->set_name($db_row[self::FLD_NAME]);
            $this->set_name_given($db_row[self::FLD_NAME_GIVEN]);
            $this->set_name_generated($db_row[self::FLD_NAME_AUTO]);
            $this->description = $db_row[sql_db::FLD_DESCRIPTION];
            $this->type_id = $db_row[self::FLD_TYPE];
            $this->values = $db_row[self::FLD_VALUES];
        }
        return $result;
    }

    /*
     * set and get
     */

    /**
     * set the name used object
     * @param string $name
     * @return void
     */
    public function set_name(string $name): void
    {
        $this->name = $name;
    }

    /**
     * set the name manually set by the user and set the used name if needed
     * @param string|null $name_given
     * @return void
     */
    public function set_name_given(?string $name_given): void
    {
        $this->name_given = $name_given;
    }

    /**
     *
     * @param string|null $name_generated the generated name as saved in the database
     * @return void
     */
    function set_name_generated(?string $name_generated): void
    {
        if ($name_generated != null) {
            // use the updated generated name or the generated name loaded from the database
            $this->name_generated = $name_generated;
        } else {
            // worst case use an empty string
            $this->name_generated = '';
            log_warning('No name found for triple ' . $this->id);
        }
    }

    /**
     * set the used name, update the generated name if needed
     * @return void
     */
    public function set_names(): void
    {
        // update the generated name if needed
        if ($this->generate_name() != '' and $this->generate_name() != ' ()') {
            $this->name_generated = $this->generate_name();
        }
        // remove the given name if not needed
        if ($this->name_given == $this->name_generated) {
            $this->name_given = null;
        } else {
            // or set the given name if needed e.g. when called be json import
            if ($this->name != '' and $this->name != $this->name_generated) {
                $this->name_given = $this->name;
            }
        }
        // use the generated name as fallback
        if ($this->name == '') {
            if ($this->name_given != null and $this->name_given != '') {
                $this->name = $this->name_given;
            } else {
                $this->name = $this->name_generated;
            }
        }
    }

    /**
     * @return string|null the name manually set by the user or null if the generated name should be used
     */
    public function name_given(): ?string
    {
        return $this->name_given;
    }

    /**
     * TODO check where the function or the db value should be used
     */
    public function name_generated(): ?string
    {
        return $this->name_generated;
    }

    /**
     * @return string|null the description of the link which should be shown to the user as mouseover
     */
    function description(): ?string
    {
        return $this->description();
    }

    /*
     * casting objects
     */

    /**
     * @return triple_api the triple frontend api object
     */
    function api_obj(): object
    {
        $api_obj = new triple_api();
        if (!$this->excluded) {
            parent::fill_min_obj($api_obj);
            $api_obj->name = $this->name;
            $api_obj->description = $this->description;
        }
        return $api_obj;
    }

    /**
     * @return triple_dsp the triple object with the display interface functions
     */
    function dsp_obj(): object
    {
        $dsp_obj = new triple_dsp();

        $dsp_obj = parent::fill_dsp_obj($dsp_obj);

        $dsp_obj->set_name($this->name);
        $dsp_obj->verb = $this->verb->dsp_obj();

        $dsp_obj->share_id = $this->share_id;
        $dsp_obj->protection_id = $this->protection_id;

        $dsp_obj->type_id = $this->type_id;
        $dsp_obj->values = $this->values;

        return $dsp_obj;
    }

    /*
     * loading
     */

    /**
     * create the SQL to load the default formula always by the id
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_standard_sql(sql_db $db_con, string $class = self::class): sql_par
    {
        $db_con->set_type(sql_db::TBL_TRIPLE);
        $qp = new sql_par($class, true);
        $qp->name .= $this->load_sql_name_ext();
        $db_con->set_name($qp->name);
        $db_con->set_usr($this->usr->id);
        $db_con->set_fields(array_merge(
            self::FLD_NAMES_LINK,
            self::FLD_NAMES,
            self::FLD_NAMES_USR,
            self::FLD_NAMES_NUM_USR,
            array(sql_db::FLD_USER_ID)
        ));

        return $this->load_sql_select_qp($db_con, $qp);
    }

    /**
     * load the triple parameters for all users
     *
     * @param sql_par|null $qp placeholder to align the function parameters with the parent
     * @param string $class the name of this class to be delivered to the parent function
     * @return bool true if the standard triple has been loaded
     */
    function load_standard(?sql_par $qp = null, string $class = self::class): bool
    {
        global $db_con;

        // after every load call from outside the class the order should be checked and reversed if needed
        $this->check_order();

        $qp = $this->load_standard_sql($db_con);

        $db_lnk = $db_con->get1($qp);
        $result = $this->row_mapper($db_lnk, false);
        if ($result) {
            $result = $this->load_owner();

            // automatically update the generic name
            if ($result) {
                $this->load_objects();
                $new_name = $this->name();
                log_debug('triple->load_standard check if name ' . $this->dsp_id() . ' needs to be updated to "' . $new_name . '"');
                if ($new_name <> $this->name) {
                    $db_con->set_type(sql_db::TBL_TRIPLE);
                    $result = $db_con->update($this->id, self::FLD_NAME_GIVEN, $new_name);
                    $this->name = $new_name;
                }
            }
            log_debug('triple->load_standard ... done (' . $this->description . ')');
        }

        return $result;
    }

    /**
     * create an SQL statement to retrieve the parameters of a triple from the database
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql(sql_db $db_con, string $class = self::class): sql_par
    {
        $db_con->set_type(sql_db::TBL_TRIPLE);
        $qp = new sql_par(self::class);
        $qp->name .= $this->load_sql_name_ext();

        // similar statement used in triple_list->load, check if changes should be repeated in triple_list.php
        $db_con->set_name($qp->name);
        $db_con->set_usr($this->usr->id);
        $db_con->set_link_fields(self::FLD_FROM, self::FLD_TO, verb::FLD_ID);
        $db_con->set_fields(self::FLD_NAMES);
        $db_con->set_usr_fields(self::FLD_NAMES_USR);
        $db_con->set_usr_num_fields(self::FLD_NAMES_NUM_USR);

        return $this->load_sql_select_qp($db_con, $qp);
    }

    /**
     * load the word link without the linked objects, because in many cases the object are already loaded by the caller
     */
    function load(): bool
    {
        global $db_con;
        $result = false;

        // after every load call from outside the class the order should be checked and reversed if needed
        $this->check_order();

        $qp = $this->load_sql($db_con);

        if ($qp->sql == '') {
            if (is_null($this->usr->id)) {
                log_err("The user id must be set to load a word.", "triple->load");
            } else {
                log_err('Either the database ID (' . $this->id . '), unique word link (' . $this->from->id . ',' . $this->verb->id . ',' . $this->to->id . ') or the name (' . $this->name . ') and the user (' . $this->usr->id . ') must be set to load a word link.', "triple->load");
            }
        } else {
            $db_lnk = $db_con->get1($qp);
            $this->row_mapper($db_lnk);
            if ($this->id > 0) {
                // automatically update the generic name
                $this->load_objects();
                $new_name = $this->name_generated();
                log_debug('triple->load check if name ' . $this->dsp_id() . ' needs to be updated to "' . $new_name . '"');
                if ($new_name <> $this->name) {
                    $db_con->set_type(sql_db::TBL_TRIPLE);
                    $db_con->update($this->id, self::FLD_NAME_AUTO, $new_name);
                    $this->set_name_generated($new_name);
                }
                $result = true;
            } else {
                $this->id = 0;
            }
            log_debug('triple->load ... done (' . $this->name() . ')');
        }
        return $result;
    }

    /**
     * if needed reverse the order if the user has entered it the other way round
     * e.g. "Cask Flow Statement" "contains" "Taxes" instead of "Taxes" "is part of" "Cask Flow Statement"
     */
    private function check_order()
    {
        if ($this->verb->id < 0) {
            $to = $this->to;
            $to_id = $this->to->id;
            $to_name = $this->to->name;
            $this->to = $this->from;
            $this->to->id = $this->from->id;
            $this->to->name = $this->from->name;
            $this->verb->id = $this->verb->id * -1;
            if (isset($this->verb)) {
                $this->verb->name = $this->verb->reverse;
            }
            $this->from = $to;
            $this->from->id = $to_id;
            $this->from->name = $to_name;
            log_debug('triple->check_order -> reversed');
        }
    }

    /**
     * load the word link without the linked objects, because in many cases the object are already loaded by the caller
     * similar to term->load, but with a different use of verbs
     */
    function load_objects(): bool
    {
        log_debug('triple->load_objects.' . $this->from->id . ' ' . $this->verb->id . ' ' . $this->to->id);
        $result = true;

        // after every load call from outside the class the order should be checked and reversed if needed
        $this->check_order();

        // load the "from" phrase
        if (!isset($this->from)) {
            log_err("The word (" . $this->from->id . ") must be set before it can be loaded.", "triple->load_objects");
        } else {
            if ($this->from->id <> 0 and !is_null($this->usr->id)) {
                if ($this->from->id > 0) {
                    $wrd = new word($this->usr);
                    $wrd->id = $this->from->id;
                    $wrd->load();
                    if ($wrd->name <> '') {
                        $this->from = $wrd->phrase();
                        $this->from->name = $wrd->name;
                    } else {
                        log_err('Failed to load first word of phrase ' . $this->dsp_id());
                        $result = false;
                    }
                } elseif ($this->from->id < 0) {
                    $lnk = new triple($this->usr);
                    $lnk->id = $this->from->id * -1;
                    $lnk->load();
                    if ($lnk->id > 0) {
                        $this->from = $lnk->phrase();
                        $this->from->name = $lnk->name();
                    } else {
                        log_err('Failed to load first phrase of phrase ' . $this->dsp_id());
                        $result = false;
                    }
                } else {
                    // if type is not (yet) set, create a dummy object to enable the selection
                    $phr = new phrase($this->usr);
                    $this->from = $phr;
                }
                log_debug('triple->load_objects -> from ' . $this->from->name);
            }
        }

        // load verb
        if (!isset($this->verb)) {
            log_err("The verb (" . $this->verb->id . ") must be set before it can be loaded.", "triple->load_objects");
        } else {
            if ($this->verb->id <> 0 and !is_null($this->usr->id)) {
                $vrb = new verb;
                $vrb->id = $this->verb->id;
                $vrb->usr = $this->usr;
                $vrb->load();
                $this->verb = $vrb;
                $this->verb->name = $vrb->name;
                log_debug('triple->load_objects -> verb ' . $this->verb->name);
            }
        }

        // load the "to" phrase
        if (!isset($this->to)) {
            if ($this->to->id == 0) {
                // set a dummy word
                $wrd_to = new word($this->usr);
                $this->to = $wrd_to->phrase();
            }
        } else {
            if ($this->to->id <> 0 and !is_null($this->usr->id)) {
                if ($this->to->id > 0) {
                    $wrd_to = new word($this->usr);
                    $wrd_to->id = $this->to->id;
                    $wrd_to->load();
                    if ($wrd_to->name <> '') {
                        $this->to = $wrd_to->phrase();
                        $this->to->name = $wrd_to->name;
                    } else {
                        log_err('Failed to load second word of phrase ' . $this->dsp_id());
                        $result = false;
                    }
                } elseif ($this->to->id < 0) {
                    $lnk = new triple($this->usr);
                    $lnk->id = $this->to->id * -1;
                    $lnk->load();
                    if ($lnk->id > 0) {
                        $this->to = $lnk->phrase();
                        $this->to->name = $lnk->name();
                    } else {
                        log_err('Failed to load second phrase of phrase ' . $this->dsp_id());
                        $result = false;
                    }
                } else {
                    // if type is not (yet) set, create a dummy object to enable the selection
                    $phr_to = new phrase($this->usr);
                    $this->to = $phr_to;
                }
                log_debug('triple->load_objects -> to ' . $this->to->name);
            }
        }
        return $result;
    }

    /**
     * @return string the name of the SQL statement name extension based on the filled fields
     */
    private function load_sql_name_ext(): string
    {
        if ($this->id != 0) {
            return 'id';
        } elseif ($this->name != '') {
            return 'name';
        } elseif ($this->has_objects()) {
            return 'link_ids';
        } else {
            log_err('Either the database ID (' . $this->id . ') or the ' .
                self::class . ' link objects (' . $this->dsp_id() . ') and the user (' . $this->usr->id . ') must be set to load a ' .
                self::class, self::class . '->load');
            return '';
        }
    }

    /**
     * add the select parameters to the query parameters
     *
     * @param sql_db $db_con the db connection object with the SQL name and others parameter already set
     * @param sql_par $qp the query parameters with the name already set
     * @return sql_par the query parameters with the select parameters added
     */
    private function load_sql_select_qp(sql_db $db_con, sql_par $qp): sql_par
    {
        if ($this->id != 0) {
            $db_con->add_par(sql_db::PAR_INT, $this->id);
            $qp->sql = $db_con->select_by_id();
        } elseif ($this->name != '') {
            $db_con->add_par(sql_db::PAR_TEXT, $this->name);
            //$qp->sql = $db_con->select_by_name();
            $qp->sql = $db_con->select_by_field(self::FLD_NAME);
        } elseif ($this->has_objects()) {
            $db_con->add_par(sql_db::PAR_INT, $this->from->id);
            $db_con->add_par(sql_db::PAR_INT, $this->to->id);
            $db_con->add_par(sql_db::PAR_INT, $this->verb->id);
            $qp->sql = $db_con->select_by_field_list(array(self::FLD_FROM, self::FLD_TO, verb::FLD_ID));
        } elseif ($this->name_generated() != '') {
            $db_con->add_par(sql_db::PAR_TEXT, $this->name_generated());
            $qp->sql = $db_con->select_by_field(self::FLD_NAME_AUTO);
        } elseif ($this->name_given() != '') {
            $db_con->add_par(sql_db::PAR_TEXT, $this->name_given());
            $qp->sql = $db_con->select_by_field(self::FLD_NAME_GIVEN);
        }
        $qp->par = $db_con->get_par();
        return $qp;
    }

    /**
     * @return true if no link objects is missing
     */
    private function has_objects(): bool
    {
        $result = true;
        if ($this->from->id == 0) {
            $result = false;
        }
        if ($this->verb->id == 0) {
            $result = false;
        }
        if ($this->to->id == 0) {
            $result = false;
        }
        return $result;
    }

    /**
     * recursive function to include the foaf words for this triple
     */
    function wrd_lst(): word_list
    {
        log_debug('triple->wrd_lst ' . $this->dsp_id());
        $wrd_lst = new word_list($this->usr);

        // add the "from" side
        if (isset($this->from)) {
            if ($this->from->id > 0) {
                $wrd_lst->add($this->from->obj);
            } elseif ($this->from->id < 0) {
                $sub_wrd_lst = $this->from->wrd_lst();
                foreach ($sub_wrd_lst as $wrd) {
                    $wrd_lst->add($wrd);
                }
            } else {
                log_err('The from phrase ' . $this->from->dsp_id() . ' should not have the id 0', 'triple->wrd_lst');
            }
        }

        // add the "to" side
        if (isset($this->to)) {
            if ($this->to->id > 0) {
                $wrd_lst->add($this->to->obj);
            } elseif ($this->to->id < 0) {
                $sub_wrd_lst = $this->to->wrd_lst();
                foreach ($sub_wrd_lst as $wrd) {
                    $wrd_lst->add($wrd);
                }
            } else {
                log_err('The to phrase ' . $this->to->dsp_id() . ' should not have the id 0', 'triple->wrd_lst');
            }
        }

        log_debug('triple->wrd_lst -> (' . $wrd_lst->name() . ')');
        return $wrd_lst;
    }

    /**
     * get a phrase based on the name (and save it if needed and requested)
     *
     * @param string $name the name of the phrase
     * @param bool $do_save to switch off saving for unit testing
     * @return phrase the created phrase object
     */
    private function import_phrase(string $name, bool $do_save = true): phrase
    {
        $result = new phrase($this->usr);
        $result->name = $name;
        if ($do_save) {
            $result->load();
            if ($result->id == 0) {
                $phr = new phrase($this->usr);
                $phr->name = $name;
                $phr->save();
                if ($phr->id == 0) {
                    log_err('Cannot add from word "' . $name . '" when importing ' . $this->dsp_id(), 'triple->import_obj');
                } else {
                    $result = $phr;
                }
            }
        }
        return $result;
    }

    /**
     * import a triple from a json object
     *
     * @param array $json_obj an array with the data of the json object
     * @param bool $do_save can be set to false for unit testing
     * @return user_message the status of the import and if needed the error messages that should be shown to the user
     */
    function import_obj(array $json_obj, bool $do_save = true): user_message
    {
        global $phrase_types;
        global $share_types;
        global $protection_types;

        log_debug();
        $result = new user_message();

        foreach ($json_obj as $key => $value) {
            if ($key == exp_obj::FLD_NAME) {
                $this->set_name($value);
            }
            if ($key == exp_obj::FLD_DESCRIPTION) {
                $this->description = $value;
            }
            if ($key == exp_obj::FLD_TYPE) {
                $this->type_id = $phrase_types->id($value);
            }
            if ($key == self::FLD_EX_FROM) {
                if ($value == "") {
                    $result->add_message('from name should not be empty at ' . dsp_array($json_obj));
                } else {
                    $this->from = $this->import_phrase($value, $do_save);
                }
            }
            if ($key == self::FLD_EX_TO) {
                if ($value == "") {
                    $result->add_message('to name should not be empty at ' . dsp_array($json_obj));
                } else {
                    $this->to = $this->import_phrase($value, $do_save);
                }
            }
            if ($key == self::FLD_EX_VERB) {
                $vrb = new verb;
                $vrb->name = $value;
                $vrb->usr = $this->usr;
                if ($result->is_ok() and $do_save) {
                    $vrb->load();
                    if ($vrb->id <= 0) {
                        $result->add_message('verb "' . $value . '" not found');
                        if ($this->name <> '') {
                            $result->add_message('for triple "' . $this->name . '"');
                        }
                    }
                }
                $this->verb = $vrb;
            }
            if ($key == share_type::JSON_FLD) {
                $this->share_id = $share_types->id($value);
            }
            if ($key == protection_type::JSON_FLD) {
                $this->protection_id = $protection_types->id($value);
            }
        }
        if ($result->is_ok() and $do_save) {
            // remove unneeded given names
            $this->set_names();
            $result->add_message($this->save());
        }

        return $result;
    }

    /**
     * create a triple object for the export
     * @return triple_exp a reduced triple object that can be used to create a JSON message
     */
    function export_obj(bool $do_load = true): exp_obj
    {
        log_debug('triple->export_obj');
        $result = new triple_exp();

        if ($this->name <> '') {
            $result->name = $this->name;
        }
        if ($this->description <> '') {
            $result->description = $this->description;
        }
        $result->from = $this->from->name;
        $result->verb = $this->verb->name;
        $result->to = $this->to->name;

        log_debug('triple->export_obj -> ' . json_encode($result));
        return $result;
    }

    /*
    display functions
    */

    /**
     * display the unique id fields
     * TODO check if $this->load_objects(); needs to be called from the calling function upfront
     */
    function dsp_id(): string
    {
        $result = '';

        if ($this->from->name <> '' and $this->verb->name <> '' and $this->to->name <> '') {
            $result .= $this->from->name . ' '; // e.g. Australia
            $result .= $this->verb->name . ' '; // e.g. is a
            $result .= $this->to->name;       // e.g. Country
        }
        $result .= ' (' . $this->from->id . ',' . $this->verb->id . ',' . $this->to->id;
        if ($this->id > 0) {
            $result .= ' -> ' . $this->id . ')';
        }
        if (isset($this->usr)) {
            $result .= ' for user ' . $this->usr->id . ' (' . $this->usr->name . ')';
        }
        return $result;
    }

    /**
     * either the user edited description
     * or the generic name e.g. Australia is a Country
     * or for the verb is 'is' the category in brackets e.g. Zurich (Canton) or Zurich (City)
     */
    function name(): string
    {
        $result = '';

        if ($this->excluded <> 1) {
            if ($this->name <> '') {
                // use the object
                $result = $this->name;
            } elseif ($this->name_given() <> '') {
                // use the user defined description
                $result = $this->name_given();
            } else {
                // or use the standard generic description
                $result = $this->name_generated();
            }
        }

        return $result;
    }

    /**
     * @return string the generated name based on the linked phrases
     */
    function generate_name(): string
    {
        if ($this->verb->id == cl(db_cl::VERB, verb::IS_A) and $this->from->name != '' and $this->to->name != '') {
            // use the user defined description
            return $this->from->name . ' (' . $this->to->name . ')';
        } elseif ($this->from->name != '' and $this->verb->name != '' and $this->to->name != '') {
            // or use the standard generic description
            return $this->from->name . ' ' . $this->verb->name . ' ' . $this->to->name;
        } elseif ($this->from->name != '' and $this->to->name != '') {
            // or use the short generic description
            return $this->from->name . ' ' . $this->to->name;
        } else {
            // or use the name as fallback
            if ($this->name_given() == null) {
                return '';
            } else {
                return $this->name_given();
            }
        }
    }

    /**
     * get the database id of the word link type
     * @return int the id of the word type
     */
    function type_id(): ?int
    {
        return $this->type_id;
    }

    /**
     * display one link to the user by returning the HTML code for the link to the calling function
     * TODO include the user sandbox in the selection
     */
    private
    function dsp(): string
    {
        log_debug("triple->dsp " . $this->id . ".");

        $result = ''; // reset the html code var

        // get the link from the database
        $this->load_objects();

        // prepare to show the word link
        $result .= $this->from->name . ' '; // e.g. Australia
        $result .= $this->verb->name . ' '; // e.g. is a
        $result .= $this->to->name;       // e.g. Country

        return $result;
    }

    /**
     * similar to dsp, but display the reverse expression
     */
    private
    function dsp_r(): string
    {
        log_debug("triple->dsp_r " . $this->id . ".");

        $result = ''; // reset the html code var

        // get the link from the database
        $this->load_objects();

        // prepare to show the word link
        $result .= $this->to->name . ' ';   // e.g. Countries
        $result .= $this->verb->name . ' '; // e.g. are
        $result .= $this->from->name;     // e.g. Australia (and others)

        return $result;
    }

    /**
     * display a form to create a triple
     */
    function dsp_add(string $back = ''): string
    {
        log_debug("triple->dsp_add.");
        $result = ''; // reset the html code var

        // at least to create the dummy objects to display the selectors
        $this->load_objects();

        // for creating a new triple the first word / triple is fixed
        $form_name = 'link_add';
        //$result .= 'Create a combined word (semantic triple):<br>';
        $result .= '<br>Define a new relation for <br><br>';
        $result .= '<b>' . $this->from->name . '</b> ';
        $result .= dsp_form_start($form_name);
        $result .= dsp_form_hidden("back", $back);
        $result .= dsp_form_hidden("confirm", '1');
        $result .= dsp_form_hidden("from", $this->from->id);
        $result .= '<div class="form-row">';
        if (isset($this->verb)) {
            $result .= $this->verb->dsp_selector('both', $form_name, "col-sm-6", $back);
        }
        if (isset($this->to)) {
            $result .= $this->to->dsp_selector(0, $form_name, 0, "col-sm-6", $back);
        }
        $result .= '</div>';
        $result .= '<br>';
        $result .= dsp_form_end('', $back);

        return $result;
    }

    /**
     * display a form to adjust the link between too words or triples
     */
    function dsp_edit(string $back = ''): string
    {
        log_debug("triple->dsp_edit id " . $this->id . " for user" . $this->usr->id . ".");
        $result = ''; // reset the html code var

        // at least to create the dummy objects to display the selectors
        $this->load();
        $this->load_objects();
        log_debug("triple->dsp_edit id " . $this->id . " load done.");

        // prepare to show the word link
        if ($this->id > 0) {
            $form_name = 'link_edit';
            $result .= dsp_text_h2('Change "' . $this->from->name . ' ' . $this->verb->name . ' ' . $this->to->name . '" to ');
            $result .= dsp_form_start($form_name);
            $result .= dsp_form_hidden("back", $back);
            $result .= dsp_form_hidden("confirm", '1');
            $result .= dsp_form_hidden("id", $this->id);
            $result .= '<div class="form-row">';
            if (isset($this->from)) {
                $result .= $this->from->dsp_selector(0, $form_name, 1, "col-sm-4", $back);
            }
            if (isset($this->verb)) {
                $result .= $this->verb->dsp_selector('forward', $form_name, "col-sm-4", $back);
            }
            if (isset($this->to)) {
                $result .= $this->to->dsp_selector(0, $form_name, 2, "col-sm-4", $back);
            }
            $result .= '</div>';
            $result .= dsp_form_end('', $back);
            $result .= '<br>';
        }

        return $result;
    }

    /**
     * display a form to adjust the link between too words or triples
     */
    function dsp_del(string $back = ''): string
    {
        log_debug("triple->dsp_del " . $this->id . ".");
        $result = ''; // reset the html code var

        $result .= \html\btn_yesno('Is "' . $this->dsp() . '" wrong?', '/http/link_del.php?id=' . $this->id . '&back=' . $back);
        $result .= '<br><br>... and "' . $this->dsp_r() . '" is also wrong.<br><br>If you press Yes, both rules will be removed.';

        return $result;
    }

    /**
     * simply to display a single triple in a table
     */
    function dsp_link(): string
    {
        return '<a href="/http/view.php?link=' . $this->id . '" title="' . $this->name() . '">' . $this->name() . '</a>';
    }

    /**
     * simply to display a single triple in a table
     */
    function dsp_tbl($intent): string
    {
        log_debug('triple->dsp_tbl');
        $result = '    <td>' . "\n";
        while ($intent > 0) {
            $result .= '&nbsp;';
            $intent = $intent - 1;
        }
        $result .= '      ' . $this->dsp_link() . "\n";
        $result .= '    </td>' . "\n";
        return $result;
    }

    function dsp_tbl_row(): string
    {
        $result = '  <tr>' . "\n";
        $result .= $this->dsp_tbl(0);
        $result .= '  </tr>' . "\n";
        return $result;
    }

    /*
     * convert functions
     */

    /**
     * convert the word object into a phrase object
     */
    function phrase(): phrase
    {
        $phr = new phrase($this->usr);
        // the triple has positive id, but the phrase uses a negative id
        $phr->id = $this->id * -1;
        $phr->name = $this->name;
        $phr->obj = $this;
        log_debug('triple->phrase of ' . $this->dsp_id());
        return $phr;
    }

    /**
     * @returns term the triple object cast into a term object
     */
    function term(): term
    {
        $trm = new term($this->usr);
        $trm->set_obj_id($this->id, self::class);
        $trm->name = $this->name;
        $trm->obj = $this;
        log_debug($this->dsp_id());
        return $trm;
    }

    /*
     * save functions
     */

    /**
     * true if no one has used this triple
     */
    function not_used(): bool
    {
        log_debug('triple->not_used (' . $this->id . ')');

        // TODO review: maybe replace by a database foreign key check
        return $this->not_changed();
    }

    /**
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     *                 to check if the triple has been changed
     */
    function not_changed_sql(sql_db $db_con): sql_par
    {
        $db_con->set_type(sql_db::TBL_TRIPLE);
        return $db_con->not_changed_sql($this->id, $this->owner_id);
    }

    /**
     * @returns bool true if no other user has modified the triple
     */
    function not_changed(): bool
    {
        log_debug('triple->not_changed (' . $this->id . ') by someone else than the owner (' . $this->owner_id . ')');

        global $db_con;
        $result = true;

        if ($this->id == 0) {
            log_err('The id must be set to check if the triple has been changed');
        } else {
            $qp = $this->not_changed_sql($db_con);
            $db_row = $db_con->get1($qp);
            if ($db_row[self::FLD_USER] > 0) {
                $result = false;
            }
        }
        log_debug('triple->not_changed for ' . $this->id . ' is ' . zu_dsp_bool($result));
        return $result;
    }

    /**
     * true if the user is the owner and no one else has changed the triple
     * because if another user has changed the triple and the original value is changed, maybe the user triple also needs to be updated
     */
    function can_change(): bool
    {
        log_debug('triple->can_change ' . $this->dsp_id() . ' by user "' . $this->usr->name . '" (id ' . $this->usr->id . ', owner id ' . $this->owner_id . ')');
        $can_change = false;
        if ($this->owner_id == $this->usr->id or $this->owner_id <= 0) {
            $can_change = true;
        }
        log_debug('triple->can_change -> (' . zu_dsp_bool($can_change) . ')');
        return $can_change;
    }

    /**
     * true if a record for a user specific configuration already exists in the database
     */
    function has_usr_cfg(): bool
    {
        $has_cfg = false;
        if ($this->usr_cfg_id > 0) {
            $has_cfg = true;
        }
        return $has_cfg;
    }

    /**
     * create a database record to save user specific settings for this triple
     */
    function add_usr_cfg(): bool
    {
        global $db_con;
        $result = true;

        if (!$this->has_usr_cfg()) {
            if (isset($this->from) and isset($this->to)) {
                log_debug('triple->add_usr_cfg for "' . $this->from->name . '"/"' . $this->to->name . '" by user "' . $this->usr->name . '"');
            } else {
                log_debug('triple->add_usr_cfg for "' . $this->id . '" and user "' . $this->usr->name . '"');
            }

            // check again if there ist not yet a record
            $db_con->set_type(sql_db::TBL_TRIPLE, true);
            $qp = new sql_par(self::class);
            $qp->name = 'triple_add_usr_cfg';
            $db_con->set_name($qp->name);
            $db_con->set_usr($this->usr->id);
            $db_con->set_where_std($this->id);
            $qp->sql = $db_con->select_by_id();
            $qp->par = $db_con->get_par();
            $db_row = $db_con->get1($qp);
            if ($db_row != null) {
                $this->usr_cfg_id = $db_row[self::FLD_ID];
            }
            if (!$this->has_usr_cfg()) {
                // create an entry in the user sandbox
                $db_con->set_type(sql_db::TBL_USER_PREFIX . sql_db::TBL_TRIPLE);
                $log_id = $db_con->insert(array(self::FLD_ID, user_sandbox::FLD_USER), array($this->id, $this->usr->id));
                if ($log_id <= 0) {
                    log_err('Insert of user_triple failed.');
                    $result = false;
                } else {
                    $result = true;
                }
            }
        }
        return $result;
    }

    /**
     * create an SQL statement to retrieve the user changes of the current triple
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function usr_cfg_sql(sql_db $db_con, string $class = self::class): sql_par
    {
        $db_con->set_type(sql_db::TBL_TRIPLE);
        $db_con->set_fields(array_merge(
            self::FLD_NAMES_USR,
            self::FLD_NAMES_NUM_USR
        ));
        return parent::usr_cfg_sql($db_con, $class);
    }

    /**
     * check if the database record for the user specific settings can be removed
     * @returns bool true if the unneeded user sandbox record has been deleted
     */
    function del_usr_cfg_if_not_needed(): bool
    {
        log_debug('pre check for "' . $this->dsp_id() . ' und user ' . $this->usr->name);

        global $db_con;
        $result = false;

        // check again if there is user sandbox row
        if ($this->id == 0) {
            log_err('cannot delete user sandbox if id is missing');
        } else {
            $qp = $this->usr_cfg_sql($db_con);
            $db_con->usr_id = $this->usr->id;
            $usr_cfg = $db_con->get1($qp);
            log_debug('check for "' . $this->dsp_id() . ' und user ' . $this->usr->name . ' with (' . $qp->sql . ')');
            if ($usr_cfg) {
                if ($usr_cfg[self::FLD_ID] > 0) {
                    // TODO use the FLD_NAMES array with all relevant field names
                    if ($usr_cfg[self::FLD_NAME_GIVEN] == Null
                        and $usr_cfg[sql_db::FLD_DESCRIPTION] == Null
                        and $usr_cfg[self::FLD_EXCLUDED] == Null) {
                        // delete the entry in the user sandbox
                        log_debug('any more for "' . $this->dsp_id() . ' und user ' . $this->usr->name);
                        $result = $this->del_usr_cfg_exe($db_con);
                    }
                }
            }
        }

        // don't throw an error message if another account has removed the user sandbox row in the meantime
        if (!$this->has_usr_cfg()) {
            $result = true;
        }

        return $result;
    }

    /**
     * set the log entry parameter for a new value
     * e.g. that the user can see "added ABB is a Company"
     */
    function log_link_add(): user_log_link
    {
        log_debug('triple->log_link_add for ' . $this->dsp_id() . ' by user "' . $this->usr->name . '"');
        $log = new user_log_link;
        $log->usr = $this->usr;
        $log->action = user_log::ACTION_ADD;
        $log->table = 'triples';
        $log->new_from = $this->from;
        $log->new_link = $this->verb;
        $log->new_to = $this->to;
        $log->row_id = 0;
        $log->add();

        return $log;
    }

    /**
     * set the main log entry parameters for updating the triple itself
     */
    function log_upd(): user_log_link
    {
        $log = new user_log_link;
        $log->usr = $this->usr;
        $log->action = user_log::ACTION_UPDATE;
        if ($this->can_change()) {
            $log->table = 'triples';
        } else {
            $log->table = 'user_triples';
        }

        return $log;
    }

    /**
     * set the log entry parameter to delete a triple
     * e.g. that the user can see "ABB is a Company not anymore"
     */
    function log_del_link(): user_log_link
    {
        log_debug('triple->log_link_del for ' . $this->dsp_id() . ' by user "' . $this->usr->name . '"');
        $log = new user_log_link;
        $log->usr = $this->usr;
        $log->action = user_log::ACTION_DELETE;
        $log->table = 'triples';
        $log->old_from = $this->from;
        $log->old_link = $this->verb;
        $log->old_to = $this->to;
        $log->row_id = $this->id;
        $log->add();

        return $log;
    }

    /**
     * set the main log entry parameters for updating one display word link field
     */
    function log_upd_field(): user_log_named
    {
        $log = new user_log_named;
        $log->usr = $this->usr;
        $log->action = user_log::ACTION_UPDATE;
        if ($this->can_change()) {
            $log->table = 'triples';
        } else {
            $log->table = 'user_triples';
        }

        return $log;
    }

    /**
     * set the update parameters for the triple name
     */
    function save_field_name(sql_db $db_con, user_sandbox $db_rec, user_sandbox $std_rec): string
    {
        $result = '';

        // the name field is a generic created field, so update it before saving
        // the generic name of $this is saved to the database for faster uniqueness check (TODO to be checked if this is really faster)
        $this->set_names();

        if ($db_rec->name <> $this->name) {
            $log = $this->log_upd_field();
            $log->old_value = $db_rec->name;
            $log->new_value = $this->name;
            $log->std_value = $std_rec->name;
            $log->row_id = $this->id;
            $log->field = self::FLD_NAME;
            $result .= $this->save_field_do($db_con, $log);
        }
        return $result;
    }

    /**
     * set the update parameters for the triple given name
     */
    private
    function save_field_name_given(sql_db $db_con, triple $db_rec, triple $std_rec): string
    {
        $result = '';

        if ($db_rec->name_given() <> $this->name_given()) {
            $log = $this->log_upd_field();
            $log->old_value = $db_rec->name_given();
            $log->new_value = $this->name_given();
            $log->std_value = $std_rec->name_given();
            $log->row_id = $this->id;
            $log->field = self::FLD_NAME_GIVEN;
            $result .= $this->save_field_do($db_con, $log);
        }
        return $result;
    }

    /**
     * set the update parameters for the triple generated name
     */
    private
    function save_field_name_generated(sql_db $db_con, triple $db_rec, triple $std_rec): string
    {
        $result = '';

        if ($db_rec->name_generated <> $this->name_generated()) {
            $log = $this->log_upd_field();
            $log->old_value = $db_rec->name_generated;
            $log->new_value = $this->name_generated();
            $log->std_value = $std_rec->name_generated;
            $log->row_id = $this->id;
            $log->field = self::FLD_NAME_AUTO;
            $result .= $this->save_field_do($db_con, $log);
        }
        return $result;
    }

    /**
     * set the update parameters for the triple description
     */
    function save_field_triple_description(sql_db $db_con, triple $db_rec, triple $std_rec): string
    {
        $result = '';
        if ($db_rec->description <> $this->description) {
            $log = $this->log_upd_field();
            $log->old_value = $db_rec->description;
            $log->new_value = $this->description;
            $log->std_value = $std_rec->description;
            $log->row_id = $this->id;
            $log->field = sql_db::FLD_DESCRIPTION;
            $result .= $this->save_field_do($db_con, $log);
        }
        return $result;
    }

    /**
     * save all updated triple fields excluding id fields (from, verb and to), because already done when adding a triple
     */
    function save_triple_fields(sql_db $db_con, triple $db_rec, triple $std_rec): string
    {
        $result = $this->save_field_name($db_con, $db_rec, $std_rec);
        $result .= $this->save_field_name_given($db_con, $db_rec, $std_rec);
        $result .= $this->save_field_name_generated($db_con, $db_rec, $std_rec);
        $result .= $this->save_field_triple_description($db_con, $db_rec, $std_rec);
        $result .= $this->save_field_excluded($db_con, $db_rec, $std_rec);
        //$result .= $this->save_field_type     ($db_con, $db_rec, $std_rec);
        log_debug('triple->save_fields all fields for ' . $this->dsp_id() . ' has been saved');
        return $result;
    }

    /**
     * save updated the triple id fields (from, verb and to)
     * should only be called if the user is the owner and nobody has used the triple
     */
    function save_id_fields(sql_db $db_con, user_sandbox $db_rec, user_sandbox $std_rec): string
    {
        $result = '';
        if ($db_rec->from->id <> $this->from->id
            or $db_rec->verb->id <> $this->verb->id
            or $db_rec->to->id <> $this->to->id) {
            log_debug('triple->save_id_fields to "' . $this->to->name . '" (' . $this->to->id . ') from "' . $db_rec->to->name . '" (' . $db_rec->to->id . ') standard ' . $std_rec->to->name . '" (' . $std_rec->to->id . ')');
            $log = $this->log_upd();
            $log->old_from = $db_rec->from;
            $log->new_from = $this->from;
            $log->std_from = $std_rec->from;
            $log->old_link = $db_rec->verb;
            $log->new_link = $this->verb;
            $log->std_link = $std_rec->verb;
            $log->old_to = $db_rec->to;
            $log->new_to = $this->to;
            $log->std_to = $std_rec->to;
            $log->row_id = $this->id;
            //$log->field    = self::FLD_FROM;
            if ($log->add()) {
                $db_con->set_type(sql_db::TBL_TRIPLE);
                if (!$db_con->update($this->id,
                    array("from_phrase_id", "verb_id", "to_phrase_id"),
                    array($this->from->id, $this->verb->id, $this->to->id))) {
                    $result = 'Update of work link name failed';
                }
            }
        }
        log_debug('triple->save_id_fields for ' . $this->dsp_id() . ' has been done');
        return $result;
    }

    /**
     * check if the id parameters are supposed to be changed
     */
    function save_id_if_updated(sql_db $db_con, user_sandbox $db_rec, user_sandbox $std_rec): string
    {
        $result = '';

        if ($db_rec->from->id <> $this->from->id
            or $db_rec->verb->id <> $this->verb->id
            or $db_rec->to->id <> $this->to->id) {
            $this->reset();
            // check if target link already exists
            log_debug('triple->save_id_if_updated check if target link already exists ' . $this->dsp_id() . ' (has been "' . $db_rec->dsp_id() . '")');
            $db_chk = clone $this;
            $db_chk->id = 0; // to force the load by the id fields
            $db_chk->load_standard();
            if ($db_chk->id > 0) {
                // ... if yes request to delete or exclude the record with the id parameters before the change
                $to_del = clone $db_rec;
                $msg = $to_del->del();
                $result .= $msg->get_last_message();
                if (!$msg->is_ok()) {
                    $result .= 'Failed to delete the unused work link';
                }
                if ($result = '') {
                    // ... and use it for the update
                    $this->id = $db_chk->id;
                    $this->owner_id = $db_chk->owner_id;
                    // force including again
                    $this->excluded = null;
                    $db_rec->excluded = '1';
                    if ($this->save_field_excluded($db_con, $db_rec, $std_rec)) {
                        log_debug('triple->save_id_if_updated found a triple with target ids "' . $db_chk->dsp_id() . '", so del "' . $db_rec->dsp_id() . '" and add ' . $this->dsp_id());
                    }
                }
            } else {
                if ($this->can_change() and $this->not_used()) {
                    // in this case change is allowed and done
                    log_debug('triple->save_id_if_updated change the existing triple ' . $this->dsp_id() . ' (db "' . $db_rec->dsp_id() . '", standard "' . $std_rec->dsp_id() . '")');
                    $this->load_objects();
                    $result .= $this->save_id_fields($db_con, $db_rec, $std_rec);
                } else {
                    // if the target link has not yet been created
                    // ... request to delete the old
                    $to_del = clone $db_rec;
                    $msg = $to_del->del();
                    $result .= $msg->get_last_message();
                    if (!$msg->is_ok()) {
                        $result .= 'Failed to delete the unused work link';
                    }
                    // ... and create a deletion request for all users ???

                    // ... and create a new triple
                    $this->id = 0;
                    $this->owner_id = $this->usr->id;
                    $result .= $this->add()->get_last_message();
                    log_debug('triple->save_id_if_updated recreate the triple del "' . $db_rec->dsp_id() . '" add ' . $this->dsp_id() . ' (standard "' . $std_rec->dsp_id() . '")');
                }
            }
        }

        log_debug('triple->save_id_if_updated for ' . $this->dsp_id() . ' has been done');
        return $result;
    }

    /**
     * add a new triple to the database
     * @return user_message with status ok
     *                      or if something went wrong
     *                      the message that should be shown to the user
     *                      including suggested solutions
     */
    function add(): user_message
    {
        log_debug('triple->add new triple for "' . $this->from->name . '" ' . $this->verb->name . ' "' . $this->to->name . '"');

        global $db_con;
        $result = new user_message();

        // log the insert attempt first
        $log = $this->log_link_add();
        if ($log->id > 0) {
            // insert the new triple
            $db_con->set_type(sql_db::TBL_TRIPLE);
            $this->id = $db_con->insert(array("from_phrase_id", "verb_id", "to_phrase_id", "user_id"),
                array($this->from->id, $this->verb->id, $this->to->id, $this->usr->id));
            // TODO make sure on all add functions that the database object is always set
            //array($this->from->id, $this->verb->id, $this->to->id, $this->usr->id));
            if ($this->id > 0) {
                // update the id in the log
                if (!$log->add_ref($this->id)) {
                    $result->add_message('Updating the reference in the log failed');
                    // TODO do rollback or retry?
                } else {

                    // create an empty db_rec element to force saving of all set fields
                    $db_rec = new triple($this->usr);
                    $db_rec->from = $this->from;
                    $db_rec->verb = $this->verb;
                    $db_rec->to = $this->to;
                    $std_rec = clone $db_rec;
                    // save the triple fields
                    $result->add_message($this->save_triple_fields($db_con, $db_rec, $std_rec));
                }

            } else {
                $result->add_message("Adding triple " . $this->name . " failed");
            }
        }

        return $result;
    }

    /**
     * update a triple in the database or create a user triple
     * @return string an empty string if everything is fine otherwise the message that should be shown to the user
     */
    function save(): string
    {
        log_debug('triple->save "' . $this->description . '" for user ' . $this->usr->id);

        global $db_con;
        $result = '';

        // load the objects if needed
        $this->load_objects();

        // build the database object because the is anyway needed
        $db_con->set_usr($this->usr->id);
        $db_con->set_type(sql_db::TBL_TRIPLE);

        // check if the opposite triple already exists and if yes, ask for confirmation
        if ($this->id <= 0) {
            log_debug('triple->save check if a new triple for "' . $this->from->name . '" and "' . $this->to->name . '" needs to be created');
            // check if the reverse triple is already in the database
            $db_chk_rev = clone $this;
            $db_chk_rev->from = $this->to;
            $db_chk_rev->from->id = $this->to->id;
            $db_chk_rev->to = $this->from;
            $db_chk_rev->to->id = $this->from->id;
            // remove the name in the object to prevent loading by name
            $db_chk_rev->name = '';
            $db_chk_rev->load_standard();
            if ($db_chk_rev->id > 0) {
                $this->id = $db_chk_rev->id;
                $result .= dsp_err('The reverse of "' . $this->from->name . ' ' . $this->verb->name . ' ' . $this->to->name . '" already exists. Do you really want to create both sides?');
            }
        }

        // check if the triple already exists and if yes, update it if needed
        if ($this->id <= 0 and $result == '') {
            log_debug('triple->save check if a new triple for "' . $this->from->name . '" and "' . $this->to->name . '" needs to be created');
            // check if the same triple is already in the database
            $db_chk = clone $this;
            $db_chk->load_standard();
            if ($db_chk->id > 0) {
                $this->id = $db_chk->id;
            }
        }

        // try to save the link only if no question has been raised utils now
        if ($result == '') {
            // check if a new value is supposed to be added
            if ($this->id <= 0) {
                $result .= $this->add()->get_last_message();
            } else {
                log_debug('triple->save update "' . $this->id . '"');
                // read the database values to be able to check if something has been changed;
                // done first, because it needs to be done for user and general phrases
                $db_rec = new triple($this->usr);
                $db_rec->id = $this->id;
                if (!$db_rec->load()) {
                    $result .= 'Reloading of triple failed';
                }
                log_debug('triple->save -> database triple "' . $db_rec->name . '" (' . $db_rec->id . ') loaded');
                $std_rec = new triple($this->usr); // the user must also be set to allow to take the ownership
                $std_rec->id = $this->id;
                if (!$std_rec->load_standard()) {
                    $result .= 'Reloading of the default values for triple failed';
                }
                log_debug('triple->save -> standard triple settings for "' . $std_rec->name . '" (' . $std_rec->id . ') loaded');

                // for a correct user word link detection (function can_change) set the owner even if the word link has not been loaded before the save
                if ($this->owner_id <= 0) {
                    $this->owner_id = $std_rec->owner_id;
                }

                // check if the id parameters are supposed to be changed
                if ($result == '') {
                    $result .= $this->save_id_if_updated($db_con, $db_rec, $std_rec);
                }

                // if a problem has appeared up to here, don't try to save the values
                // the problem is shown to the user by the calling interactive script
                if ($result == '') {
                    $result .= $this->save_triple_fields($db_con, $db_rec, $std_rec);
                }
            }
        }

        if ($result != '') {
            log_err($result);
        }

        return $result;
    }

    /**
     * delete the phrase groups which where this triple is used
     */
    function del_links(): user_message
    {
        global $db_con;
        $result = new user_message();

        // collect all phrase groups where this triple is used
        $grp_lst = new phrase_group_list($this->usr);
        $grp_lst->phr = $this->phrase();
        $grp_lst->load();

        // collect all values related to this triple
        $val_lst = new value_list($this->usr);
        $val_lst->load_by_phr($this->phrase());

        // if there are still values, ask if they really should be deleted
        if ($val_lst->has_values()) {
            $result->add($val_lst->del());
        }

        // if the user confirms the deletion, the removal process is started with a retry of the triple deletion at the end
        $result->add($grp_lst->del());

        return $result;
    }

}
