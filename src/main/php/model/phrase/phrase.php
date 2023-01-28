<?php

/*

    phrase.php - either a word or a triple
    ----------

    this is not saved in a separate table
    e.g. to build a selector the entries are caught either from the words or triples table

    If the user wants to overwrite a formula result, there are two possibilities for the technical realisation

    1. for each formula automatically a word with the special type "formula link" is created
        advantages:
            user value handling is in one table (values)
            formulas can be part of a triple
        disadvantages:
            the formula name is saved twice

    2. The formula value can directly be overwritten by the user
        advantages:
            the formula name is only saved once
        disadvantages:
            the probably huge formula value table needs an extra field to indicate user overwrites which makes the use of key/value databases more complicated

    There is a word increase and a formula that calculates the increase, so the solution 1. with formula link words is implemented

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

use api\phrase_api;
use cfg\phrase_type;
use html\html_selector;
use html\phrase_dsp;
use html\triple_dsp;
use html\word_dsp;

class phrase extends db_object
{

    /*
     * database link
     */

    // the database and JSON object duplicate field names for combined word and triples mainly to link phrases
    const FLD_ID = 'phrase_id';
    const FLD_NAME = 'phrase_name';
    const FLD_VALUES = 'values';

    // the common phrase database field names excluding the id and excluding the user specific fields
    const FLD_NAMES = array(
        triple::FLD_TYPE
    );
    // list of the common user specific database field names of phrases excluding the standard name field
    const FLD_NAMES_USR_EX = array(
        sql_db::FLD_DESCRIPTION
    );
    // list of the common user specific database field names of phrases
    const FLD_NAMES_USR = array(
        phrase::FLD_NAME,
        sql_db::FLD_DESCRIPTION
    );
    // list of the common user specific database field names of phrases
    const FLD_NAMES_USR_NO_NAME = array(
        sql_db::FLD_DESCRIPTION
    );
    // list of the common user specific numeric database field names of phrases
    const FLD_NAMES_NUM_USR = array(
        self::FLD_VALUES,
        user_sandbox::FLD_EXCLUDED,
        user_sandbox::FLD_SHARE,
        user_sandbox::FLD_PROTECT
    );


    /*
     * object vars
     */

    // database duplicate fields
    public ?object $obj = null;        // if loaded the linked word or triple object
    // TODO deprecate
    private ?user $usr = null;         // the person for whom the word is loaded, so to say the viewer
    public ?int $usage = null;         // a higher number indicates a higher usage
    public string $description = '';   // simply the word or triple description to reduce the number of "->" on the code

    // in memory only fields
    public ?string $type_name = null;  //
    public ?int $link_type_id = null;  // used in the word list to know based on which relation the word was added to the list


    /*
     * construct and map
     */

    /**
     * always set the user because a phrase is always user specific
     * @param user $usr the user who requested to see this phrase
     */
    function __construct(
        user   $usr,
        int    $id = 0,
        string $name = '',
        string $from = '',
        string $verb = '',
        string $to = '')
    {
        parent::__construct();
        $this->set_user($usr);

        // create the automatically related objects if requested
        if ($from != ''
            and $verb != ''
            and $to != '') {
            $this->obj = new triple($usr);
            $this->obj->set($id * -1, $from, $verb, $to, $name);
        } else {
            if ($name != '') {
                $this->obj = new word($usr);
                $this->obj->set($id, $name);
            }
        }
    }

    /**
     * map the common word and triple database fields to the phrase fields
     *
     * @param array|null $db_row with the data directly from the database
     * @param string $id_fld the name of the id field as defined in this child and given to the parent
     * @return bool true if the triple is loaded and valid
     */
    function row_mapper(?array $db_row, string $id_fld = self::FLD_ID, string $fld_ext = ''): bool
    {
        $result = false;
        $this->id = 0;
        if ($db_row != null) {
            if ($db_row[$id_fld] > 0) {
                $this->id = $db_row[$id_fld];
                // map a word
                $wrd = new word($this->usr);
                $wrd->set_id($db_row[$id_fld]);
                $wrd->set_name($db_row[phrase::FLD_NAME . $fld_ext]);
                $wrd->description = $db_row[sql_db::FLD_DESCRIPTION . $fld_ext];
                $wrd->type_id = $db_row[word::FLD_TYPE . $fld_ext];
                $wrd->excluded = $db_row[user_sandbox::FLD_EXCLUDED . $fld_ext];
                $wrd->share_id = $db_row[user_sandbox::FLD_SHARE . $fld_ext];
                $wrd->protection_id = $db_row[user_sandbox::FLD_PROTECT . $fld_ext];
                //$wrd->owner_id = $db_row[user_sandbox::FLD_USER . $fld_ext];
                $this->obj = $wrd;
                $result = true;
            } elseif ($db_row[$id_fld] < 0) {
                $this->id = $db_row[$id_fld];
                // map a triple
                $trp = new triple($this->usr);
                $trp->set_id($db_row[$id_fld] * -1);
                $trp->set_name($db_row[phrase::FLD_NAME . $fld_ext]);
                $trp->description = $db_row[sql_db::FLD_DESCRIPTION . $fld_ext];
                $trp->type_id = $db_row[triple::FLD_TYPE . $fld_ext];
                $trp->excluded = $db_row[user_sandbox::FLD_EXCLUDED . $fld_ext];
                $trp->share_id = $db_row[user_sandbox::FLD_SHARE . $fld_ext];
                $trp->protection_id = $db_row[user_sandbox::FLD_PROTECT . $fld_ext];
                // not yet loaded with initial load
                // $trp->name = $db_row[triple::FLD_NAME_GIVEN . $fld_ext];
                // $trp->owner_id = $db_row[user_sandbox::FLD_USER . $fld_ext];
                // $trp->from->id = $db_row[triple::FLD_FROM];
                // $trp->to->id = $db_row[triple::FLD_TO];
                // $trp->verb->id = $db_row[verb::FLD_ID];
                $this->obj = $trp;
                $result = true;
            }
        }
        return $result;
    }


    /*
     * get and set
     */

    /**
     * set the phrase id based id the word or triple id
     *
     * @param int $id the object id that is converted to the phrase id
     * @param string $class the class of the phrase object
     * @return void
     */
    function set_id_from_obj(int $id, string $class): void
    {
        if ($class == word::class) {
            $this->obj = new word($this->usr);
            $this->id = $id;
        } elseif ($class == triple::class) {
            $this->obj = new triple($this->usr);
            $this->id = $id * -1;
        }
        $this->obj->set_id($id);
    }

    /**
     * create the expected object based on the given class
     * @param string $class the calling class name
     * @return void
     */
    private function set_obj(string $class): void
    {
        if ($class == word::class) {
            $this->obj = new word($this->usr);
        } elseif ($class == triple::class) {
            $this->obj = new triple($this->usr);
        } else {
            log_err('Unexpected class ' . $class . ' when creating phrase ' . $this->dsp_id());
        }
    }

    /**
     * set the name of the phrase object, which is also the name of the phrase
     *
     * @param string $name the name of the phrase set in the related object
     * @param string $class the class of the phrase object can be set to force the creation of the related object
     * @return void
     */
    function set_name(string $name, string $class = ''): void
    {
        if ($class != '' and $this->obj == null) {
            $this->set_obj($class);
        }
        $this->obj->set_name($name);
    }

    /**
     * set the user of the phrase
     *
     * @param user $usr the person who wants to access the phrase
     * @return void
     */
    function set_user(user $usr): void
    {
        $this->usr = $usr;
    }

    /**
     * @return int the id of the phrase witch is (corresponding to id_obj())
     * e.g 1 for a word, -1 for a triple
     */
    function id(): ?int
    {
        return $this->id;
    }

    /**
     * @return string the name of the phrase
     */
    function name(): string
    {
        if ($this->obj == null) {
            return '';
        } else {
            return $this->obj->name();
        }
    }

    /**
     * @return user the person who wants to see the phrase
     */
    function user(): user
    {
        return $this->usr;
    }


    /*
     * cast
     */

    /**
     * @return phrase_api the phrase frontend api object
     */
    function api_obj(): phrase_api
    {
        if ($this->is_word()) {
            return $this->get_word()->api_obj()->phrase();
        } else {
            return $this->get_triple()->api_obj()->phrase();
        }
    }

    /**
     * @return phrase_dsp the phrase object with the display interface functions
     */
    function dsp_obj(): phrase_dsp
    {
        if ($this->is_word()) {
            return $this->get_word()->dsp_obj()->phrase_dsp();
        } else {
            return $this->get_triple()->dsp_obj()->phrase_dsp();
        }
    }


    /*
     * loading / database access object (DAO) functions
     */

    /**
     * create the common part of an SQL statement to retrieve a phrase from the database view
     * uses the phrase view which includes only the most relevant fields of words or triples
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param string $query_name the name of the query use to prepare and call the query
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    private function load_sql(sql_db $db_con, string $query_name): sql_par
    {
        $qp = new sql_par(self::class);
        $qp->name .= $query_name;

        $db_con->set_type(sql_db::VT_PHRASE);
        $db_con->set_name($qp->name);

        $db_con->set_fields(self::FLD_NAMES);
        $db_con->set_usr_fields(self::FLD_NAMES_USR_EX);
        $db_con->set_usr_num_fields(self::FLD_NAMES_NUM_USR);

        return $qp;
    }

    /**
     * create an SQL statement to retrieve a phrase by phrase id (not the object id) from the database
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param int $id the id of the phrase as defined in the database phrase view
     * @param string $class the name of this class
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_id(sql_db $db_con, int $id, string $class = self::class): sql_par
    {
        $qp = $this->load_sql($db_con, 'id');
        $db_con->add_par_int($id);
        $qp->sql = $db_con->select_by_field(phrase::FLD_ID);
        $qp->par = $db_con->get_par();

        return $qp;
    }

    /**
     * create an SQL statement to retrieve a phrase by name from the database
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param string $name the name of the phrase and the related word, triple, formula or verb
     * @param string $class the name of this class
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_name(sql_db $db_con, string $name, string $class = self::class): sql_par
    {
        $qp = $this->load_sql($db_con, 'name');
        $db_con->add_par_txt($name);
        $qp->sql = $db_con->select_by_field(phrase::FLD_NAME);
        $qp->par = $db_con->get_par();

        return $qp;
    }

    /**
     * load either a word or triple
     * @return true if loading has been successful
     */
    function load_by_obj_par(): bool
    {
        log_debug($this->dsp_id());
        $result = false;

        // direct load if the type is known
        if ($this->is_triple()) {
            $trp = new triple($this->usr);
            // TODO use load_by_phrase_id to move the id logic into the triple class
            $result = $trp->load_by_id($this->id * -1, triple::class);
            $this->obj = $trp;
            $this->set_name($trp->name()); // is this really useful? better save execution time and have longer code using ->obj->name
            log_debug('triple ' . $this->dsp_id());
        } elseif ($this->is_word()) {
            $wrd = new word($this->usr);
            $result = $wrd->load_by_id($this->id, word::class);
            $this->obj = $wrd;
            $this->set_name($wrd->name());
            log_debug('word ' . $this->dsp_id());
        } elseif ($this->name() <> '') {
            // load via phrase if the type is not yet known
            $trm = new term($this->usr);
            $result = $trm->load_by_name($this->name());
            if ($trm->type() == word::class) {
                $this->obj = $trm->obj;
                $this->id = $trm->id_obj();
                log_debug('word ' . $this->dsp_id() . ' by name');
            } elseif ($trm->type() == triple::class) {
                $this->obj = $trm->obj;
                $this->id = $trm->id_obj() * -1;
                log_debug('triple ' . $this->dsp_id() . ' by name');
            } elseif ($trm->type() == formula::class) {
                // for the phrase load the related word instead of the formula
                // TODO integrate this into the phrase loading by load both object a once
                $wrd = new word($this->usr);
                $result = $wrd->load_by_name($this->name(), word::class);
                $this->obj = $wrd;
                $this->id = $wrd->id();
                log_debug('formula word ' . $this->dsp_id());
            } else {
                if ($this->type_name == '') {
                    // TODO check that this ($phrase->load) is never used for an error detection
                    log_warning('"' . $this->name() . '" not found.', "phrase->load");
                } else {
                    log_err('"' . $this->name() . '" has the type ' . $this->type_name . ' which is not expected for a phrase.', "phrase->load");
                }
            }
        }
        log_debug('done ' . $this->dsp_id());
        return $result;
    }

    public function load_obj(): bool
    {
        $result = 0;
        if ($this->is_triple()) {
            $trp = new triple($this->usr);
            // TODO use load_by_phrase_id to move the id logic into the triple class
            $result = $trp->load_by_id($this->id * -1, triple::class);
            $this->obj = $trp;
            $this->set_name($trp->name()); // is this really useful? better save execution time and have longer code using ->obj->name
            log_debug('triple ' . $this->dsp_id());
        } elseif ($this->is_word()) {
            $wrd = new word($this->usr);
            $result = $wrd->load_by_id($this->id, word::class);
            $this->obj = $wrd;
            $this->set_name($wrd->name());
            log_debug('word ' . $this->dsp_id());
        }
        if ($result != 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * load a phrase from the database view
     * @param sql_par $qp the query parameters created by the calling function
     * @return int the id of the object found and zero if nothing is found
     */
    private function load(sql_par $qp): int
    {
        global $db_con;

        $db_row = $db_con->get1($qp);
        $this->row_mapper($db_row);
        return $this->id();
    }

    /**
     * load the main phrase parameters by id from the database phrase view
     * @param int $id the id of the phrase as defined in the database phrase view
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_id(int $id, string $class = self::class): int
    {
        global $db_con;

        log_debug($id);
        $qp = $this->load_sql_by_id($db_con, $id, $class);
        return $this->load($qp);
    }

    /**
     * test if the name is used already via view table and just load the main parameters
     * @param string $name the name of the phrase and the related word, triple, formula or verb
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_name(string $name, string $class = self::class): int
    {
        global $db_con;

        log_debug($name);
        $qp = $this->load_sql_by_name($db_con, $name, self::class);
        return $this->load($qp);
    }

    /**
     * get the main word object
     * e.g. ???
     * assumes that the phrase has already been loaded
     *
     * @return object|null
     */
    function main_word(): ?object
    {
        log_debug($this->dsp_id());
        $result = null;

        if ($this->id == 0 or $this->name() == '') {
            $this->load_by_obj_par();
        }
        if ($this->id < 0) {
            $lnk = $this->obj;
            $lnk->load_objects(); // try to be on the save side, and it is anyway checked if loading is really needed
            $result = $lnk->from;
        } elseif ($this->id > 0) {
            $result = $this->obj;
        } else {
            log_err('"' . $this->name() . '" has the type ' . $this->type_name . ' which is not expected for a phrase.', "phrase->main_word");
        }
        log_debug('done ' . $result->dsp_id());
        return $result;
    }

    /**
     * to enable the recursive function in work_link
     */
    function wrd_lst(): word_list
    {
        $wrd_lst = new word_list($this->usr);
        if ($this->id < 0) {
            $sub_wrd_lst = $this->wrd_lst();
            foreach ($sub_wrd_lst as $wrd) {
                $wrd_lst->add($wrd);
            }
        } else {
            $wrd_lst->add($this->obj);
        }
        return $wrd_lst;
    }

    /**
     * return either the word type id or the word link type id
     * e.g. 2020 can be a year but also any other identification number e.g. a valor number,
     * so if there is both in the database the type must be saved on the word link instead of the word
     */
    function type_id(): ?int
    {
        $result = null;
        if ($this->obj != null) {
            $result = $this->obj->type_id();
        }
        if ($result == null or $result == 0) {
            $wrd = $this->main_word();
            $result = $wrd->type_id;
        }

        log_debug('for ' . $this->dsp_id() . ' is ' . $result);
        return $result;
    }

    /*
     * classification
     */

    /**
     * @return bool true if this phrase is a word or supposed to be a word
     */
    function is_word(): bool
    {
        $result = false;
        if (isset($this->obj)) {
            if (get_class($this->obj) == word::class or get_class($this->obj) == word_dsp::class) {
                $result = true;
            }
        } else {
            if ($this->id > 0) {
                $result = true;
            }
        }
        return $result;
    }

    /**
     * @return bool true if this phrase is a triple or supposed to be a triple
     */
    private function is_triple(): bool
    {
        $result = false;
        if (isset($this->obj)) {
            if (get_class($this->obj) == triple::class) {
                $result = true;
            }
        } else {
            if ($this->id < 0) {
                $result = true;
            }
        }
        return $result;
    }

    /**
     * @return bool true if this phrase is a formula or supposed to be a formula
     */
    private function is_formula(): bool
    {
        $result = false;
        if (isset($this->obj)) {
            if (get_class($this->obj) == formula::class or get_class($this->obj) == formula_dsp_old::class) {
                $result = true;
            }
        } else {
            if ($this->id < 0) {
                $result = true;
            }
        }
        return $result;
    }

    /*
     * conversion
     */

    function get_word(): word
    {
        $wrd = new word($this->usr);
        if (get_class($this->obj) == word::class) {
            $wrd->set_id($this->obj->id());
            $wrd->usr_cfg_id = $this->obj->usr_cfg_id;
            $wrd->owner_id = $this->obj->owner_id;
            $wrd->share_id = $this->obj->share_id;
            $wrd->protection_id = $this->obj->protection_id;
            $wrd->excluded = $this->obj->excluded;
            $wrd->set_name($this->obj->name());
            $wrd->description = $this->obj->description;
            $wrd->plural = $this->obj->plural;
            $wrd->type_id = $this->obj->type_id;
            $wrd->view_id = $this->obj->view_id;
            $wrd->values = $this->obj->values;
        }
        return $wrd;
    }

    protected function get_word_dsp(): word_dsp
    {
        $wrd_dsp = new word($this->usr);
        if (get_class($this->obj) == word_dsp::class) {
            $wrd_dsp = $this->obj;
        } elseif (get_class($this->obj) == word::class) {
            $wrd_dsp = $this->get_word()->dsp_obj();
        }
        return $wrd_dsp;
    }

    protected function get_triple(): triple
    {
        $lnk = new triple($this->usr);
        if (get_class($this->obj) == triple::class) {
            $lnk->set_id($this->obj->id());
            $lnk->fob = $this->obj->fob;
            $lnk->tob = $this->obj->tob;
            $lnk->usr_cfg_id = $this->obj->usr_cfg_id;
            $lnk->owner_id = $this->obj->owner_id;
            $lnk->share_id = $this->obj->share_id;
            $lnk->protection_id = $this->obj->protection_id;
            $lnk->excluded = $this->obj->excluded;
            $lnk->description = $this->obj->description;
            $lnk->type_id = $this->obj->type_id;
            $lnk->values = $this->obj->values;
        }
        return $lnk;
    }

    protected function get_triple_dsp(): triple
    {
        $lnk_dsp = new triple_dsp();
        if (get_class($this->obj) == triple_dsp::class) {
            $lnk_dsp = $this->obj;
        } elseif (get_class($this->obj) == triple::class) {
            $lnk_dsp = $this->get_triple()->dsp_obj();
        }
        return $lnk_dsp;
    }

    /**
     * get the related object
     * so either the word object
     * or the triple object
     */
    function get_obj(): ?object
    {
        $obj = '';
        if ($this->is_word()) {
            $obj = $this->get_word();
        } elseif ($this->is_triple()) {
            $obj = $this->get_triple();
        }
        return $obj;
    }

    /**
     * get the related display object
     * so either the word display object
     * or the triple display object
     */
    function get_dsp_obj(): ?phrase_dsp
    {
        $obj = new phrase_dsp();
        if ($this->is_word()) {
            $wrd = $this->get_word_dsp();
            $obj = $wrd->phrase()->dsp_obj();
        } elseif ($this->is_triple()) {
            $trp = $this->get_triple_dsp();
            $obj = $trp->phrase()->get_dsp_obj();
        }
        return $obj;
    }


    /*
     * im- and export
     */

    /**
     * import a phrase object from a JSON array object
     *
     * @param string $json_value an array with the data of the json object
     * @param bool $do_save can be set to false for unit testing
     * @return user_message the status of the import and if needed the error messages that should be shown to the user
     */
    function import_obj(string $json_value, bool $do_save = true): user_message
    {
        $result = new user_message();
        if ($do_save) {
            $this->load_by_name($json_value);
            if ($this->id == 0) {
                $wrd = new word($this->usr);
                $wrd->load_by_name($json_value, word::class);
                if ($wrd->id() == 0) {
                    $wrd->set_name($json_value);
                    $wrd->type_id = cl(db_cl::PHRASE_TYPE, phrase_type::TIME);
                    $result->add_message($wrd->save());
                }
                if ($wrd->id() == 0) {
                    $result->add_message('Cannot add word "' . $json_value . '" when importing ' . $this->dsp_id());
                } else {
                    $this->id = $wrd->id();
                }
            }
        } else {
            // used for unit testing
            $this->set_name($json_value, word::class);
        }

        return $result;
    }

    /*
     * data retrieval functions
     */

    /**
     * get a list of all values related to this phrase
     */
    function val_lst(): value_list
    {
        log_debug('for ' . $this->dsp_id() . ' and user "' . $this->user()->name . '"');
        $val_lst = new value_list($this->usr);
        $val_lst->phr = $this;
        $val_lst->limit = SQL_ROW_MAX;
        $val_lst->load();
        log_debug('got ' . dsp_count($val_lst->lst()));
        return $val_lst;
    }

    /**
     * get a list of verbs either pointing to or from this phrase
     * e.g. for Zurich and direction up the list contains at least the verb "is", because Zurich is a Canton is default triple
     *
     * @param string $direction UP or DOWN to select the direction
     * @returns verb_list with all used verbs in the given direction
     */
    function vrb_lst(string $direction): verb_list
    {
        global $db_con;

        log_debug('for ' . $this->dsp_id());
        $vrb_lst = new verb_list($this->usr);
        $vrb_lst->load_by_linked_phrases($db_con, $this, $direction);
        log_debug('got ' . dsp_count($vrb_lst->lst));
        return $vrb_lst;
    }

    /*
     * display functions
     */

    /**
     * display the unique id fields
     */
    function dsp_id(): string
    {
        $result = '';

        if ($this->name() <> '') {
            $result .= '"' . $this->name() . '"';
            if ($this->id > 0) {
                $result .= ' (' . $this->id . ')';
            }
        } else {
            $result .= $this->id;
        }
        if ($this->user()->is_set()) {
            $result .= ' for user ' . $this->user()->id() . ' (' . $this->user()->name . ')';
        }
        return $result;
    }

    // return the name (just because all objects should have a name function)
    function dsp_name(): string
    {
        //$result = $this->name();
        return '"' . $this->name() . '"';
    }

    function name_linked(): string
    {
        return '<a href="/http/view.php?words=' . $this->id . '" title="' . $this->obj->description . '">' . $this->name() . '</a>';
    }

    function dsp_tbl(int $intent = 0): string
    {
        $result = '';
        if ($this != null) {
            $this->load_by_obj_par();
            if ($this->obj != null) {
                // the function dsp_tbl should exist for words and triples
                if (get_class($this->obj) == word::class) {
                    $result = $this->obj->dsp_obj()->td('', '', $intent);
                } else {
                    $result = $this->obj->dsp_tbl($intent);
                }
            }
        }
        log_debug('for ' . $this->dsp_id());
        return $result;
    }

    function dsp_tbl_row()
    {
        // the function dsp_tbl_row should exist for words and triples
        if (isset($this->obj)) {
            $result = $this->obj->dsp_tbl_row();
        } else {
            log_err('The phrase object is missing for ' . $this->dsp_id() . '.', "formula_value->load");
        }
        return $result;
    }

    function dsp_graph(string $direction, ?verb_list $link_types = null, string $back = ''): string
    {
        $phr_lst = new phrase_list($this->usr);
        if ($link_types == null) {
            $link_types = $this->vrb_lst($direction);
        }
        if ($link_types != null) {
            foreach ($link_types->lst as $vrb) {
                $add_lst = new phrase_list($this->usr);
                $add_lst->load_by_phr($this, $vrb, $direction);
                $phr_lst->merge($add_lst);
            }
        }
        $phr_lst_dsp = $phr_lst->dsp_obj();
        return $phr_lst_dsp->dsp_graph($this, $back);
    }

    /**
     * return the html code to display a word
     */
    function display(): string
    {
        return '<a href="/http/view.php?words=' . $this->id . '">' . $this->name() . '</a>';
    }

    /**
     * simply to display a single word or triple link
     */
    function dsp_link(): string
    {
        return '<a href="/http/view.php?words=' . $this->id . '" title="' . $this->obj->description . '">' . $this->name() . '</a>';
    }

    // similar to dsp_link
    function dsp_link_style($style): string
    {
        return '<a href="/http/view.php?words=' . $this->id . '" title="' . $this->obj->description . '" class="' . $style . '">' . $this->name() . '</a>';
    }

    // helper function that returns a word list object just with the word object
    function lst(): phrase_list
    {
        $phr_lst = new phrase_list($this->usr);
        $phr_lst->add($this);
        log_debug($phr_lst->dsp_name());
        return $phr_lst;
    }

    // returns a list of phrase that are related to this word e.g. for "ABB" it will return "Company" (but not "ABB"???)
    function is(): phrase_list
    {
        $this_lst = $this->lst();
        $phr_lst = $this_lst->is();
        // in case of a triple use at least the initial parent phrase,
        if ($this->is_triple()) {
            $phr_lst->add($this->obj->to);
        }
        //$phr_lst->add($this,);
        log_debug($this->dsp_id() . ' is a ' . $phr_lst->dsp_name());
        return $phr_lst;
    }

    public static function cmp($a, $b)
    {
        return strcmp($a->name(), $b->name());
    }

    // returns a list of words that are related to this word e.g. for "ABB" it will return "Company" (but not "ABB"???)
    /*  function is () {
        if ($this->id > 0) {
          $wrd_lst = $this->parents();
        } else {
        }

        zu_debug('phrase->is -> '.$this->dsp_id().' is a '.$wrd_lst->name());
        return $wrd_lst;
      } */

    // true if the word id has an "is a" relation to the related word
    // e.g.for the given word string
    function is_a($related_phrase): bool
    {
        log_debug($this->dsp_id() . ',' . $related_phrase->name);

        $result = false;
        $is_phrases = $this->is(); // should be taken from the original array to increase speed
        if (in_array($related_phrase->id, $is_phrases->id_lst())) {
            $result = true;
        }

        log_debug(zu_dsp_bool($result) . $this->id);
        return $result;
    }

    // SQL to list the user phrases (related to a type if needed)
    function sql_list($type): string
    {
        log_debug();
        global $db_con;

        $sql_type_from = '';
        $sql_type_where = '';

        // if no phrase type is define, list all words and triples
        // TODO: but if word has several types don't offer to the user to select the simple word
        //                                                      ^
        $sql_words = 'SELECT DISTINCT w.word_id AS id, 
                             ' . $db_con->get_usr_field("word_name", "w", "u", sql_db::FLD_FORMAT_TEXT, "name") . ',
                             ' . $db_con->get_usr_field("excluded", "w", "u", sql_db::FLD_FORMAT_BOOL) . '
                        FROM words w   
                   LEFT JOIN user_words u ON u.word_id = w.word_id 
                                         AND u.user_id = ' . $this->user()->id() . ' ';
        $sql_triples = 'SELECT DISTINCT l.triple_id * -1 AS id, 
                               ' . $db_con->get_usr_field("name_given", "l", "u", sql_db::FLD_FORMAT_TEXT, "name") . ',
                               ' . $db_con->get_usr_field("excluded", "l", "u", sql_db::FLD_FORMAT_BOOL) . '
                          FROM triples l
                     LEFT JOIN user_triples u ON u.triple_id = l.triple_id 
                                                AND u.user_id = ' . $this->user()->id() . ' ';

        if (isset($type)) {
            if ($type->id() > 0) {

                // select all phrase ids of the given type e.g. ABB, DANONE, Zurich
                $sql_where_exclude = 'excluded = 0';
                $sql_field_names = 'id, name, excluded';
                $sql_wrd_all = 'SELECT from_phrase_id AS id FROM (
                                        SELECT DISTINCT
                                               l.from_phrase_id,    
                                               ' . $db_con->get_usr_field("excluded", "l", "u", sql_db::FLD_FORMAT_BOOL) . '
                                          FROM triples l
                                     LEFT JOIN user_triples u ON u.triple_id = l.triple_id 
                                                                AND u.user_id = ' . $this->user()->id() . '
                                         WHERE l.to_phrase_id = ' . $type->id() . ' 
                                           AND l.verb_id = ' . cl(db_cl::VERB, verb::IS_A) . ' ) AS a 
                                         WHERE ' . $sql_where_exclude . ' ';

                // ... out of all those get the phrase ids that have also other types e.g. Zurich (Canton)
                $sql_wrd_other = 'SELECT from_phrase_id FROM (
                                        SELECT DISTINCT
                                               l.from_phrase_id,    
                                               ' . $db_con->get_usr_field("excluded", "l", "u", sql_db::FLD_FORMAT_BOOL) . '
                                          FROM triples l
                                     LEFT JOIN user_triples u ON u.triple_id = l.triple_id 
                                                                AND u.user_id = ' . $this->user()->id() . '
                                         WHERE l.to_phrase_id <> ' . $type->id() . ' 
                                           AND l.verb_id = ' . cl(db_cl::VERB, verb::IS_A) . '
                                           AND l.from_phrase_id IN (' . $sql_wrd_all . ') ) AS o 
                                         WHERE ' . $sql_where_exclude . ' ';

                // if a word has no other type, use the word
                $sql_words = 'SELECT DISTINCT ' . $sql_field_names . ' FROM (
                      SELECT DISTINCT
                             w.word_id AS id, 
                             ' . $db_con->get_usr_field("word_name", "w", "u", sql_db::FLD_FORMAT_TEXT, "name") . ',
                             ' . $db_con->get_usr_field("excluded", "w", "u", sql_db::FLD_FORMAT_BOOL) . '
                        FROM ( ' . $sql_wrd_all . ' ) a, words w
                   LEFT JOIN user_words u ON u.word_id = w.word_id 
                                         AND u.user_id = ' . $this->user()->id() . '
                       WHERE w.word_id NOT IN ( ' . $sql_wrd_other . ' )                                        
                         AND w.word_id = a.id ) AS w 
                       WHERE ' . $sql_where_exclude . ' ';

                // if a word has another type, use the triple
                $sql_triples = 'SELECT DISTINCT ' . $sql_field_names . ' FROM (
                        SELECT DISTINCT
                               l.triple_id * -1 AS id, 
                               ' . $db_con->get_usr_field("name_given", "l", "u", sql_db::FLD_FORMAT_TEXT, "name") . ',
                               ' . $db_con->get_usr_field("excluded", "l", "u", sql_db::FLD_FORMAT_BOOL) . '
                          FROM triples l
                     LEFT JOIN user_triples u ON u.triple_id = l.triple_id 
                                                AND u.user_id = ' . $this->user()->id() . '
                         WHERE l.from_phrase_id IN ( ' . $sql_wrd_other . ')                                        
                           AND l.verb_id = ' . cl(db_cl::VERB, verb::IS_A) . '
                           AND l.to_phrase_id = ' . $type->id() . ' ) AS t 
                         WHERE ' . $sql_where_exclude . ' ';
                /*
                $sql_type_from = ', triples t LEFT JOIN user_triples ut ON ut.triple_id = t.triple_id
                                                                             AND ut.user_id = '.$this->user()->id.'';
                $sql_type_where_words   = 'WHERE w.word_id = t.from_phrase_id
                                             AND t.verb_id = '.cl(SQL_LINK_TYPE_IS).'
                                             AND t.to_phrase_id = '.$type->id.' ';
                $sql_type_where_triples = 'WHERE l.to_phrase_id = t.from_phrase_id
                                             AND t.verb_id = '.cl(SQL_LINK_TYPE_IS).'
                                             AND t.to_phrase_id = '.$type->id.' ';
                $sql_words   = 'SELECT w.word_id AS id,
                                      IF(u.word_name IS NULL, w.word_name, u.word_name) AS name,
                                      IF(u.excluded IS NULL, COALESCE(w.excluded, 0), COALESCE(u.excluded, 0)) AS excluded
                                  FROM words w
                            LEFT JOIN user_words u ON u.word_id = w.word_id
                                                  AND u.user_id = '.$this->user()->id.'
                                      '.$sql_type_from.'
                                      '.$sql_type_where_words.'
                              GROUP BY name';
                $sql_triples = 'SELECT l.triple_id * -1 AS id,
                                      IF(u.name IS NULL, l.name, u.name) AS name,
                                      IF(u.excluded IS NULL, COALESCE(l.excluded, 0), COALESCE(u.excluded, 0)) AS excluded
                                  FROM triples l
                            LEFT JOIN user_triples u ON u.triple_id = l.triple_id
                                                        AND u.user_id = '.$this->user()->id.'
                                      '.$sql_type_from.'
                                      '.$sql_type_where_triples.'
                              GROUP BY name';
                              */
            }
        }
        $sql_avoid_code_check_prefix = "SELECT";
        $sql = $sql_avoid_code_check_prefix . ' DISTINCT id, name
              FROM ( ' . $sql_words . ' UNION ' . $sql_triples . ' ) AS p
             WHERE excluded = 0
          ORDER BY p.name;';
        log_debug($sql);
        return $sql;
    }

    /*
     * display functions
     */

    // create a selector that contains the words and triples
    // if one form contains more than one selector, $pos is used for identification
    // $type is a word to preselect the list to only those phrases matching this type
    function dsp_selector($type, $form_name, $pos, $class, $back): string
    {
        if ($type != null) {
            log_debug('type "' . $type->dsp_id() . ' selected for form ' . $form_name . $pos);
        }
        $result = '';

        if ($pos > 0) {
            $field_name = "phrase" . $pos;
        } else {
            $field_name = "phrase";
        }
        $sel = new html_selector;
        $sel->form = $form_name;
        $sel->name = $field_name;
        if ($form_name == "value_add" or $form_name == "value_edit") {
            $sel->label = "";
        } else {
            if ($pos == 1) {
                $sel->label = "From:";
            } elseif ($pos == 2) {
                $sel->label = "To:";
            } else {
                $sel->label = "Word:";
            }
        }
        $sel->bs_class = $class;
        $sel->sql = $this->sql_list($type);
        $sel->selected = $this->id;
        $sel->dummy_text = '... please select';
        $result .= $sel->display();

        log_debug('done ');
        return $result;
    }

    // button to add a new word similar to this phrase
    function btn_add($back)
    {
        $wrd = $this->main_word();
        return $wrd->btn_add($back);
    }

    // returns the best guess category for a word  e.g. for "ABB" it will return only "Company"
    function is_mainly()
    {
        $result = null;
        $is_wrd_lst = $this->is();
        if (!$is_wrd_lst->is_empty()) {
            $result = $is_wrd_lst->lst()[0];
            log_debug($this->dsp_id() . ' is a ' . $result->name());
        }
        return $result;
    }

    /*
    word replication functions
    */

    function is_time()
    {
        $wrd = $this->main_word();
        return $wrd->is_time();
    }

    // return true if the word has the type "measure" (e.g. "meter" or "CHF")
    // in case of a division, these words are excluded from the result
    // in case of add, it is checked that the added value does not have a different measure
    function is_measure()
    {
        $wrd = $this->main_word();
        return $wrd->is_measure();
    }

    // return true if the word has the type "scaling" (e.g. "million", "million" or "one"; "one" is a hidden scaling type)
    function is_scaling()
    {
        $wrd = $this->main_word();
        return $wrd->is_scaling();
    }

    /**
     * @returns true if the phrase type is set to "scaling_percent" (e.g. "percent")
     */
    function is_percent(): bool
    {
        global $phrase_types;

        $result = false;
        if ($this->obj != null) {
            if ($this->obj->type_id == $phrase_types->id(phrase_type::PERCENT)) {
                $result = true;
            }
        } else {
            $wrd = $this->main_word();
            $result = $wrd->is_percent();
        }
        return $result;
    }

    // create a selector that contains the time words
    // e.g. Q1 can be the first Quarter of a year and in this case the four quarters of a year should be the default selection
    //      if this is the triple "Q1 of 2018" a list of triples of this year should be the default selection
    //      if Q1 is a wikidata qualifier a general time selector should be shown
    function dsp_time_selector($type, $form_name, $pos, $back)
    {

        $wrd = $this->main_word();
        return $wrd->dsp_time_selector($type, $form_name, $pos, $back);
    }

    /**
     * @return string
     */
    function save(): string
    {
        global $phrase_types;

        $result = '';

        /*
        if (isset($this->obj)) {
            $result = $this->obj->save();
        }
        */

        // try if the word exists
        $wrd = new word($this->usr);
        $wrd->load_by_name($this->name(), word::class);
        if ($wrd->id() > 0) {
            $this->id = $wrd->phrase()->id;
        } else {
            // try if the triple exists
            $trp = new triple($this->usr);
            $trp->load_by_name($this->name(), triple::class);
            if ($trp->id() > 0) {
                $this->id = $trp->phrase()->id * -1;
            } else {
                // create a word if neither the word nor the triple exists
                $wrd = new word($this->usr);
                $wrd->set_name($this->name());
                $wrd->type_id = $phrase_types->default_id();
                $result = $wrd->save();
                if ($wrd->id() == 0) {
                    log_err('Cannot add from word ' . $this->dsp_id(), 'phrase->save');
                } else {
                    $this->id = $wrd->phrase()->id;
                }
            }
        }

        return $result;
    }

    /**
     * delete either a word or triple
     * @return user_message an empty string if deleting has been successful
     */
    function del(): user_message
    {
        log_debug($this->dsp_id());
        $result = new user_message();

        // direct delete if the object is loaded
        if ($this->is_triple()) {
            $lnk = $this->obj;
            if ($lnk != null) {
                $result->add($lnk->del());
            }
        } elseif ($this->is_word()) {
            $wrd = $this->obj;
            if ($wrd != null) {
                $result->add($wrd->del());
            }
        } else {
            log_err('Unknown object type of ' . $this->dsp_id());
        }
        return $result;
    }

}