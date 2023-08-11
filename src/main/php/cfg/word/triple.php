<?php

/*

    model/word/triple.php - the object that links two words (an RDF triple)
    ---------------------

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

    Copyright (c) 1995-2023 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace cfg;

include_once DB_PATH . 'sql_par_type.php';
include_once MODEL_SANDBOX_PATH . 'sandbox_link_typed.php';
include_once SERVICE_EXPORT_PATH . 'triple_exp.php';

use api\api;
use cfg\db\sql_creator;
use cfg\db\sql_par_type;
use model\export\exp_obj;
use model\export\triple_exp;
use controller\controller;
use api\triple_api;
use html\html_base;
use html\word\triple as triple_dsp;
use JsonSerializable;

global $phrase_types;

class triple extends sandbox_link_typed implements JsonSerializable
{

    /*
     * database link
     */

    // object specific database and JSON object field names
    const FLD_ID = 'triple_id';
    const FLD_FROM = 'from_phrase_id';
    const FLD_TO = 'to_phrase_id';
    const FLD_NAME = 'triple_name';  // the name used which must be unique within the terms of the user
    const FLD_NAME_GIVEN = 'name_given'; // the name set by the user, which can be null if the generated name should be used
    const FLD_NAME_AUTO = 'name_generated'; // the generated name is saved in the database for database base unique check
    const FLD_VALUES = 'values';
    const FLD_COND_ID = 'triple_condition_id';
    const FLD_COND_TYPE = 'triple_condition_type_id';
    const FLD_REFS = 'refs';

    // all database field names excluding the id and excluding the user specific fields
    const FLD_NAMES = array(
        phrase::FLD_TYPE,
        self::FLD_COND_ID,
        self::FLD_COND_TYPE
    );
    // list of the link database field names
    // TODO use this name for all links
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
        sandbox_named::FLD_DESCRIPTION
    );
    // list of the user specific numeric database field names
    const FLD_NAMES_NUM_USR = array(
        self::FLD_VALUES,
        sandbox::FLD_EXCLUDED,
        sandbox::FLD_SHARE,
        sandbox::FLD_PROTECT
    );
    // all database field names excluding the id used to identify if there are some user specific changes
    const ALL_SANDBOX_FLD_NAMES = array(
        self::FLD_NAME,
        self::FLD_NAME_GIVEN,
        self::FLD_NAME_AUTO,
        sandbox_named::FLD_DESCRIPTION,
        phrase::FLD_TYPE,
        self::FLD_VALUES,
        sandbox::FLD_EXCLUDED,
        sandbox::FLD_SHARE,
        sandbox::FLD_PROTECT
    );


    /*
     * im- and export link
     */

    // the field names used for the im- and export in the json or yaml format
    const FLD_EX_FROM = 'from';
    const FLD_EX_TO = 'to';
    const FLD_EX_VERB = 'verb';


    /*
     * object vars
     */

    // triple vars additional to the name and link vars of the parent user sandbox object
    // TODO replace with type
    public verb $verb;              // the link type object
    private ?string $name_given;     // the name manually set by the user, which can be empty
    private string $name_generated; // the generated name based on the linked objects and saved in the database for faster searching
    public ?int $values;            // the total number of values linked to this triple as an indication how common the triple is and to sort the triples

    // only used for the export object
    private ?view $view; // name of the default view for this word
    private ?array $ref_lst = [];


    /*
     * construct and map
     */

    /**
     * define the settings for this triple object
     * @param user $usr the user who requested to see this triple
     */
    function __construct(user $usr)
    {
        $this->id = 0;

        parent::__construct($usr);

        $this->obj_name = sql_db::TBL_TRIPLE;
        $this->rename_can_switch = UI_CAN_CHANGE_triple_NAME;
        $this->obj_type = sandbox::TYPE_LINK;

        $this->reset();
        $this->name_given = null;
        $this->name_generated = '';

        // also create the link objects because there is now case where they are supposed to be null
        $this->create_objects();
    }

    /**
     * reset the in memory fields used e.g. if some ids are updated
     */
    function reset(): void
    {
        parent::reset();
        $this->set_name('');
        $this->name_given = null;
        $this->name_generated = '';
        $this->values = null;

        $this->view = null;
        $this->ref_lst = [];

        $this->create_objects();
    }

    private function create_objects(
        string $from = '',
        string $verb = '',
        string $to = ''
    ): void
    {
        $this->fob = new phrase($this->user());
        $this->fob->set_name($from);
        $this->verb = new verb(0, $verb);
        $this->tob = new phrase($this->user());
        $this->tob->set_name($to);
    }

    /**
     * map the database fields to the object fields
     *
     * @param array|null $db_row with the data directly from the database
     * @param bool $load_std true if only the standard user sandbox object ist loaded
     * @param bool $allow_usr_protect false for using the standard protection settings for the default object used for all users
     * @param string $id_fld the name of the id field as defined in this child and given to the parent
     * @return bool true if the triple is loaded and valid
     */
    function row_mapper_sandbox(
        ?array $db_row,
        bool   $load_std = false,
        bool   $allow_usr_protect = true,
        string $id_fld = self::FLD_ID,
        string $name_fld = self::FLD_NAME,
        string $type_fld = phrase::FLD_TYPE): bool
    {
        $result = parent::row_mapper_sandbox($db_row, $load_std, $allow_usr_protect, $id_fld, $name_fld);
        if ($result) {
            if (array_key_exists(self::FLD_FROM, $db_row)) {
                $phr_id = $db_row[self::FLD_FROM];
                if ($phr_id != null) {
                    $this->fob->set_obj_from_id($phr_id);
                }
            }
            if (array_key_exists(self::FLD_TO, $db_row)) {
                $phr_id = $db_row[self::FLD_TO];
                if ($phr_id != null) {
                    $this->tob->set_obj_from_id($phr_id);
                }
            }
            if (array_key_exists(verb::FLD_ID, $db_row)) {
                if ($db_row[verb::FLD_ID] != null) {
                    $this->verb->set_id($db_row[verb::FLD_ID]);
                }
            }
            if (array_key_exists(self::FLD_NAME_GIVEN, $db_row)) {
                $this->set_name_given($db_row[self::FLD_NAME_GIVEN]);
            }
            if (array_key_exists(self::FLD_NAME_AUTO, $db_row)) {
                $this->set_name_generated($db_row[self::FLD_NAME_AUTO]);
            }
            if (array_key_exists($type_fld, $db_row)) {
                $this->type_id = $db_row[$type_fld];
            }
            if (array_key_exists(self::FLD_VALUES, $db_row)) {
                $this->values = $db_row[self::FLD_VALUES];
            }
        }
        return $result;
    }


    /*
     * set and get
     */

    /**
     * set the most used object vars with one set statement
     * @param int $id mainly for test creation the database id of the word
     * @param string $name mainly for test creation the name of the word
     */
    function set(
        int    $id = 0,
        string $name = '',
        string $from = '',
        string $verb = '',
        string $to = ''
    ): void
    {
        parent::set_id($id);
        if ($name != '') {
            $this->set_name($name);
        }
        $this->create_objects($from, $verb, $to);
    }

    /**
     * set the "from" phrase of this triple
     * e.g. "Zurich" for "Zurich (city)" based on "Zurich" (from) "is a" (verb) "city" (to)
     *
     * @param phrase $from_phr the "from" phrase
     * @return void
     */
    function set_from(phrase $from_phr): void
    {
        $this->fob = $from_phr;
    }

    /**
     * set the "from" phrase of this triple
     * e.g. "Zurich" for "Zurich (city)" based on "Zurich" (from) "is a" (verb) "city" (to)
     *
     * @param verb $vrb the verb
     * @return void
     */
    function set_verb(verb $vrb): void
    {
        $this->verb = $vrb;
    }

    /**
     * set the "from" phrase of this triple
     * e.g. "city" for "Zurich (city)" based on "Zurich" (from) "is a" (verb) "city" (to)
     *
     * @param phrase $to_phr the code id that should be added to this triple
     * @return void
     */
    function set_to(phrase $to_phr): void
    {
        $this->tob = $to_phr;
    }

    /**
     * set the phrase type of this triple
     * if the type id is null or 0 the phrase type from the "to" phrase is returned
     *
     * @param string $type_code_id the code id that should be added to this triple
     * @return void
     */
    function set_type(string $type_code_id): void
    {
        global $phrase_types;
        $this->type_id = $phrase_types->id($type_code_id);
    }

    /**
     * set the name used object
     * @param string $name
     * @return void
     */
    function set_name(string $name): void
    {
        $this->name = $name;
    }

    /**
     * set the name manually set by the user and set the used name if needed
     * @param string|null $name_given
     * @return void
     */
    function set_name_given(?string $name_given): void
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
            log_warning('No name found for triple ' . $this->id());
        }
    }

    /**
     * set the used name, update the generated name if needed
     * @return void
     */
    function set_names(): void
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
     * set the value to rank the triple by usage
     *
     * @param int|null $usage a higher value moves the triple to the top of the selection list
     * @return void
     */
    function set_usage(?int $usage): void
    {
        $this->values = $usage;
    }

    /**
     * @return int|null a higher number indicates a higher usage
     */
    function usage(): ?int
    {
        return $this->values;
    }

    /**
     * @return string|null the name manually set by the user or null if the generated name should be used
     */
    function name_given(): ?string
    {
        return $this->name_given;
    }

    /**
     * TODO check where the function or the db value should be used
     */
    function name_generated(): ?string
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
     * get preloaded information
     */

    /**
     * get the name of the triple type
     * @return string the name of the triple type
     */
    function type_name(): string
    {
        global $phrase_types;
        return $phrase_types->name($this->type_id);
    }

    /**
     * get the code_id of the word type
     * @return string the code_id of the word type
     */
    function type_code_id(): string
    {
        global $phrase_types;
        return $phrase_types->code_id($this->type_id);
    }


    /*
     * cast
     */

    /**
     * @return triple_api the triple frontend api object
     */
    function api_obj(): object
    {
        $api_obj = new triple_api();
        if (!$this->is_excluded()) {
            $this->fill_api_obj($api_obj);
            $api_obj->name = $this->name();
            $api_obj->type_id = $this->type_id();
            $api_obj->description = $this->description;
            if ($this->fob->obj() != null) {
                if ($this->fob->obj()->id() <> 0 or $this->fob->obj()->name() != '') {
                    $api_obj->set_from($this->fob->obj()->api_obj()->phrase());
                }
            }
            $api_obj->set_verb($this->verb->api_obj());
            if ($this->tob->obj() != null) {
                if ($this->tob->obj()->id() <> 0 or $this->tob->obj()->name() != '') {
                    $api_obj->set_to($this->tob->obj()?->api_obj()->phrase());
                }
            }
        }
        return $api_obj;
    }

    /**
     * @returns string the api json message for the object as a string
     */
    function api_json(): string
    {
        return $this->api_obj()->get_json();
    }

    /**
     * @return triple_dsp the triple object with the display interface functions
     */
    function dsp_obj(): object
    {
        $dsp_obj = new triple_dsp();

        parent::fill_dsp_obj($dsp_obj);

        $dsp_obj->set_name($this->name);
        $dsp_obj->set_verb($this->verb->dsp_obj());

        $dsp_obj->set_type_id($this->type_id);

        return $dsp_obj;
    }


    /*
     * set and get
     */

    /**
     * map a triple api json to this model triple object
     * similar to the import_obj function but using the database id instead of names as the unique key
     * @param array $api_json the api array with the triple values that should be mapped
     */
    function set_by_api_json(array $api_json): user_message
    {
        global $phrase_types;

        $msg = new user_message();

        // make sure that there are no unexpected leftovers
        $usr = $this->user();
        $this->reset();
        $this->set_user($usr);

        foreach ($api_json as $key => $value) {

            if ($key == api::FLD_ID) {
                $this->set_id($value);
            }
            if ($key == api::FLD_NAME) {
                $this->set_name($value);
            }
            if ($key == api::FLD_DESCRIPTION) {
                if ($value <> '') {
                    $this->description = $value;
                }
            }
            if ($key == api::FLD_TYPE) {
                $this->type_id = $phrase_types->id($value);
            }

            /* TODO
            if ($key == self::FLD_PLURAL) {
                if ($value <> '') {
                    $this->plural = $value;
                }
            }
            if ($key == share_type::JSON_FLD) {
                $this->share_id = $share_types->id($value);
            }
            if ($key == protection_type::JSON_FLD) {
                $this->protection_id = $protection_types->id($value);
            }
            if ($key == exp_obj::FLD_VIEW) {
                $wrd_view = new view($this->user());
                if ($do_save) {
                    $wrd_view->load_by_name($value, view::class);
                    if ($wrd_view->id == 0) {
                        $result->add_message('Cannot find view "' . $value . '" when importing ' . $this->dsp_id());
                    } else {
                        $this->view_id = $wrd_view->id;
                    }
                } else {
                    $wrd_view->set_name($value);
                }
                $this->view = $wrd_view;
            }

            if ($key == api::FLD_PHRASES) {
                $phr_lst = new phrase_list($this->user());
                $msg->add($phr_lst->db_obj($value));
                if ($msg->is_ok()) {
                    $this->grp->phr_lst = $phr_lst;
                }
            }
            */

        }

        return $msg;
    }


    /*
     * loading / database access object (DAO) functions
     */

    /**
     * create the SQL to load the default triple always by the id
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_standard_sql(sql_creator $sc, string $class = self::class): sql_par
    {
        $sc->set_type($class);
        $qp = new sql_par($class, true);
        $qp->name .= $this->load_sql_name_ext();
        $sc->set_name($qp->name);
        $sc->set_usr($this->user()->id());
        $sc->set_fields(array_merge(
            self::FLD_NAMES_LINK,
            self::FLD_NAMES,
            self::FLD_NAMES_USR,
            self::FLD_NAMES_NUM_USR,
            array(user::FLD_ID)
        ));

        return $this->load_sql_select_qp($sc, $qp);
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

        $qp = $this->load_standard_sql($db_con->sql_creator());

        $db_lnk = $db_con->get1($qp);
        $result = $this->row_mapper_sandbox($db_lnk, true);
        if ($result) {
            $result = $this->load_owner();

            // automatically update the generic name
            if ($result) {
                $this->load_objects();
                $new_name = $this->name();
                log_debug('triple->load_standard check if name ' . $this->dsp_id() . ' needs to be updated to "' . $new_name . '"');
                if ($new_name <> $this->name) {
                    $db_con->set_type(sql_db::TBL_TRIPLE);
                    $result = $db_con->update($this->id(), self::FLD_NAME_GIVEN, $new_name);
                    $this->name = $new_name;
                }
            }
            log_debug('triple->load_standard ... done (' . $this->description . ')');
        }

        return $result;
    }

    /**
     * create the common part of an SQL statement to retrieve the parameters of a triple from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $query_name the name extension to make the query name unique
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    protected function load_sql(sql_creator $sc, string $query_name, string $class = self::class): sql_par
    {
        $qp = parent::load_sql_obj_vars($sc, $class);
        $qp->name .= $query_name;

        $sc->set_type(sql_db::TBL_TRIPLE);
        $sc->set_name($qp->name);
        $sc->set_usr($this->user()->id());
        $sc->set_fields(array_merge(self::FLD_NAMES_LINK, self::FLD_NAMES));
        $sc->set_usr_fields(self::FLD_NAMES_USR);
        $sc->set_usr_num_fields(self::FLD_NAMES_NUM_USR);

        return $qp;
    }

    /**
     * create an SQL statement to retrieve a formula by id from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param int $id the id of the user sandbox object
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_id(sql_creator $sc, int $id, string $class = self::class): sql_par
    {
        return parent::load_sql_by_id($sc, $id, $class);
    }

    /**
     * create an SQL statement to retrieve a triple by name from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $name the name of the triple and the related word, triple, formula or verb
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_name(sql_creator $sc, string $name, string $class): sql_par
    {
        $qp = $this->load_sql($sc, sql_db::FLD_NAME, $class);
        $sc->add_where($this->name_field(), $name, sql_par_type::TEXT_USR);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * create an SQL statement to retrieve a triple by the generated name from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $name the generated name of the triple and the related word, triple, formula or verb
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_name_generated(sql_creator $sc, string $name, string $class): sql_par
    {
        $qp = $this->load_sql($sc, 'name_generated', $class);
        $sc->add_where(self::FLD_NAME_AUTO, $name, sql_par_type::TEXT_USR);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * create an SQL statement to retrieve a triple by name from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param int $from the id of the phrase that is linked
     * @param int $type the type id of the link
     * @param int $to the id of the phrase to which is the link directed
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_link(sql_creator $sc, int $from, int $type, int $to, string $class): sql_par
    {
        $qp = $this->load_sql($sc, 'link_ids', $class);
        $sc->add_where(self::FLD_FROM, $from);
        $sc->add_where(self::FLD_TO, $to);
        $sc->add_where(verb::FLD_ID, $type);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * set the generated triple name base on the view
     */
    private function reload_generated_name(): void
    {
        global $db_con;

        if ($this->id() > 0) {
            // automatically update the generic name
            $this->load_objects();
            $new_name = $this->name_generated();
            log_debug('triple->load check if name ' . $this->dsp_id() . ' needs to be updated to "' . $new_name . '"');
            if ($new_name <> $this->name_generated) {
                $db_con->set_type(sql_db::TBL_TRIPLE);
                $db_con->update($this->id(), self::FLD_NAME_AUTO, $new_name);
                $this->set_name_generated($new_name);
            }
        }
    }

    /**
     * load a named user sandbox object e.g. word, triple, formula, verb or view from the database
     * @param sql_par $qp the query parameters created by the calling function
     * @return int the id of the object found and zero if nothing is found
     */
    protected function load(sql_par $qp): int
    {
        global $db_con;

        $db_row = $db_con->get1($qp);
        $this->row_mapper_sandbox($db_row);
        $this->reload_generated_name();
        return $this->id();
    }

    /**
     * load a triple by database id
     * @param int $id the id of the word, triple, formula, verb, view or view component
     * @param string $class the name of the child class from where the call has been triggered
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_id(int $id, string $class = self::class): int
    {
        global $db_con;

        log_debug($id);
        $qp = $this->load_sql_by_id($db_con->sql_creator(), $id, $class);
        return $this->load($qp);
    }

    /**
     * load a triple by name
     * @param string $name the name of the word, triple, formula, verb, view or view component
     * @param string $class the name of the child class from where the call has been triggered
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_name(string $name, string $class = self::class): int
    {
        global $db_con;

        log_debug($name);
        $qp = $this->load_sql_by_name($db_con->sql_creator(), $name, $class);
        return $this->load($qp);
    }

    /**
     * load a triple by the generated name (the name that the triple would have if the user has done not overwrite)
     * @param string $name the generated name of the triple
     * @param string $class the name of the child class from where the call has been triggered
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_name_generated(string $name, string $class = self::class): int
    {
        global $db_con;

        log_debug($name);
        $qp = $this->load_sql_by_name_generated($db_con->sql_creator(), $name, $class);
        return $this->load($qp);
    }

    /**
     * load a triple by the ids of the linked objects
     * @param int $from the id of the phrase that is linked
     * @param int $type the type id of the link
     * @param int $to the id of the phrase to which is the link directed
     * @param string $class the name of the child class from where the call has been triggered
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_link_id(int $from, int $type, int $to, string $class = self::class): int
    {
        global $db_con;

        log_debug($from . ' ' . $type . ' ' . $to);
        $qp = $this->load_sql_by_link($db_con->sql_creator(), $from, $type, $to, $class);
        return $this->load($qp);
    }

    function name_field(): string
    {
        return self::FLD_NAME;
    }

    function all_sandbox_fields(): array
    {
        return self::ALL_SANDBOX_FLD_NAMES;
    }

    /**
     * if needed reverse the order if the user has entered it the other way round
     * e.g. "Cask Flow Statement" "contains" "Taxes" instead of "Taxes" "is part of" "Cask Flow Statement"
     */
    private function check_order()
    {
        if ($this->verb->id() < 0) {
            $to = $this->tob;
            $to_id = $this->tob->id();
            $to_name = $this->tob->name();
            $this->tob = $this->fob;
            $this->tob->set_id($this->fob->id());
            $this->tob->set_name($this->fob->name());
            $this->verb->set_id($this->verb->id() * -1);
            if (isset($this->verb)) {
                $this->verb->set_name($this->verb->reverse);
            }
            $this->fob = $to;
            $this->fob->set_id($to_id);
            $this->fob->set_name($to_name);
            log_debug('reversed');
        }
    }

    /**
     * load the triple without the linked objects, because in many cases the object are already loaded by the caller
     * similar to term->load, but with a different use of verbs
     */
    function load_objects(): bool
    {
        log_debug($this->fob->id() . ' ' . $this->verb->id() . ' ' . $this->tob->id());
        $result = true;

        // after every load call from outside the class the order should be checked and reversed if needed
        $this->check_order();

        // load the "from" phrase
        if (!isset($this->fob)) {
            log_err("The word (" . $this->fob->id() . ") must be set before it can be loaded.", "triple->load_objects");
        } else {
            if ($this->fob->id() <> 0 and !is_null($this->user()->id())) {
                if ($this->fob->id() > 0) {
                    $wrd = new word($this->user());
                    $wrd->load_by_id($this->fob->id(), word::class);
                    if ($wrd->name() <> '') {
                        $this->fob = $wrd->phrase();
                        $this->fob->set_name($wrd->name());
                    } else {
                        log_err('Failed to load first word of phrase ' . $this->dsp_id());
                        $result = false;
                    }
                } elseif ($this->fob->id() < 0) {
                    $lnk = new triple($this->user());
                    $lnk->load_by_id($this->fob->obj_id(), triple::class);
                    if ($lnk->id() > 0) {
                        $this->fob = $lnk->phrase();
                        $this->fob->set_name($lnk->name());
                    } else {
                        log_err('Failed to load first phrase of phrase ' . $this->dsp_id());
                        $result = false;
                    }
                } else {
                    // if type is not (yet) set, create a dummy object to enable the selection
                    $phr = new phrase($this->user());
                    $this->fob = $phr;
                }
                log_debug('from ' . $this->fob->name());
            }
        }

        // load verb
        if (!isset($this->verb)) {
            log_err("The verb (" . $this->verb->id() . ") must be set before it can be loaded.", "triple->load_objects");
        } else {
            if ($this->verb->id() <> 0 and !is_null($this->user()->id())) {
                $vrb = new verb;
                $vrb->set_user($this->user());
                $vrb->load_by_id($this->verb->id());
                $this->verb = $vrb;
                $this->verb->set_name($vrb->name());
                log_debug('verb ' . $this->verb->name());
            }
        }

        // load the "to" phrase
        if (!isset($this->tob)) {
            if ($this->tob->id() == 0) {
                // set a dummy word
                $wrd_to = new word($this->user());
                $this->tob = $wrd_to->phrase();
            }
        } else {
            if ($this->tob->id() <> 0 and !is_null($this->user()->id())) {
                if ($this->tob->id() > 0) {
                    $wrd_to = new word($this->user());
                    $wrd_to->load_by_id($this->tob->id(), word::class);
                    if ($wrd_to->name() <> '') {
                        $this->tob = $wrd_to->phrase();
                        $this->tob->set_name($wrd_to->name());
                    } else {
                        log_err('Failed to load second word of phrase ' . $this->dsp_id());
                        $result = false;
                    }
                } elseif ($this->tob->id() < 0) {
                    $lnk = new triple($this->user());
                    $lnk->load_by_id($this->tob->obj_id(), triple::class);
                    if ($lnk->id() > 0) {
                        $this->tob = $lnk->phrase();
                        $this->tob->set_name($lnk->name());
                    } else {
                        log_err('Failed to load second phrase of phrase ' . $this->dsp_id());
                        $result = false;
                    }
                } else {
                    // if type is not (yet) set, create a dummy object to enable the selection
                    $phr_to = new phrase($this->user());
                    $this->tob = $phr_to;
                }
                log_debug('to ' . $this->tob->name());
            }
        }
        return $result;
    }

    /**
     * @return string the name of the SQL statement name extension based on the filled fields
     */
    private function load_sql_name_ext(): string
    {
        if ($this->id() != 0) {
            return sql_db::FLD_ID;
        } elseif ($this->name != '') {
            return sql_db::FLD_NAME;
        } elseif ($this->has_objects()) {
            return 'link_ids';
        } else {
            log_err('Either the database ID (' . $this->id() . ') or the ' .
                self::class . ' link objects (' . $this->dsp_id() . ') and the user (' . $this->user()->id() . ') must be set to load a ' .
                self::class, self::class . '->load');
            return '';
        }
    }

    /**
     * add the select parameters to the query parameters
     *
     * @param sql_creator $sc with the target db_type set
     * @param sql_par $qp the query parameters with the name already set
     * @return sql_par the query parameters with the select parameters added
     */
    private function load_sql_select_qp(sql_creator $sc, sql_par $qp): sql_par
    {
        if ($this->id() != 0) {
            $sc->add_where($this->id_field(), $this->id());
        } elseif ($this->name != '') {
            $sc->add_where($this->name_field(), $this->name());
        } elseif ($this->has_objects()) {
            $sc->add_where(self::FLD_FROM, $this->fob->id());
            $sc->add_where(self::FLD_TO, $this->tob->id());
            $sc->add_where(verb::FLD_ID, $this->verb->id());
        } elseif ($this->name_generated() != '') {
            $sc->add_where(self::FLD_NAME_AUTO, $this->name_generated());
        } elseif ($this->name_given() != '') {
            $sc->add_where(self::FLD_NAME_GIVEN, $this->name_given());
        } else {
            log_err('Cannot load default triple because no unique field is set');
        }
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();
        return $qp;
    }

    /**
     * @return true if no link objects is missing
     */
    private function has_objects(): bool
    {
        $result = true;
        if ($this->fob->id() == 0) {
            $result = false;
        }
        if ($this->verb->id() == 0) {
            $result = false;
        }
        if ($this->tob->id() == 0) {
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
        $wrd_lst = new word_list($this->user());

        // add the "from" side
        if (isset($this->fob)) {
            if ($this->fob->id() > 0) {
                $wrd_lst->add($this->fob->obj());
            } elseif ($this->fob->id() < 0) {
                $sub_wrd_lst = $this->fob->wrd_lst();
                foreach ($sub_wrd_lst as $wrd) {
                    $wrd_lst->add($wrd);
                }
            } else {
                log_err('The from phrase ' . $this->fob->dsp_id() . ' should not have the id 0', 'triple->wrd_lst');
            }
        }

        // add the "to" side
        if (isset($this->tob)) {
            if ($this->tob->id() > 0) {
                $wrd_lst->add($this->tob->obj());
            } elseif ($this->tob->id() < 0) {
                $sub_wrd_lst = $this->tob->wrd_lst();
                foreach ($sub_wrd_lst as $wrd) {
                    $wrd_lst->add($wrd);
                }
            } else {
                log_err('The to phrase ' . $this->tob->dsp_id() . ' should not have the id 0', 'triple->wrd_lst');
            }
        }

        log_debug($wrd_lst->name());
        return $wrd_lst;
    }

    /*
     * interface
     */

    /**
     * an array of the value vars including the private vars
     */
    function jsonSerialize(): array
    {
        $vars = get_object_vars($this);
        if ($this->fob->obj() != null) {
            $vars['from'] = $this->fob->obj()->name_dsp();
        }
        if ($this->tob->obj() != null) {
            $vars['to'] = $this->tob->obj()->name_dsp();
        }
        return $vars;
    }

    /*
     * import ans export
     */

    /**
     * get a phrase based on the name (and save it if needed and requested)
     *
     * @param string $name the name of the phrase
     * @param object|null $test_obj if not null the unit test object to get a dummy seq id
     * @return phrase the created phrase object
     */
    private function import_phrase(string $name, object $test_obj = null): phrase
    {
        $result = new phrase($this->user());
        if (!$test_obj) {
            $result->load_by_name($name);
            if ($result->id() == 0) {
                // if there is no word or triple with the name yet, automatically create a word
                $wrd = new word($this->user());
                $wrd->set_name($name);
                $wrd->save();
                if ($wrd->id() == 0) {
                    log_err('Cannot add from word "' . $name . '" when importing ' . $this->dsp_id(), 'triple->import_obj');
                } else {
                    $result = $wrd->phrase();
                }
            }
        } else {
            $result->set_name($name, word::class);
        }
        return $result;
    }


    /*
     * im- and export
     */

    /**
     * import a triple from a json object
     *
     * @param array $in_ex_json an array with the data of the json object
     * @param object|null $test_obj if not null the unit test object to get a dummy seq id
     * @return user_message the status of the import and if needed the error messages that should be shown to the user
     */
    function import_obj(array $in_ex_json, object $test_obj = null): user_message
    {
        global $phrase_types;

        log_debug();
        $result = parent::import_obj($in_ex_json, $test_obj);

        foreach ($in_ex_json as $key => $value) {
            if ($key == exp_obj::FLD_TYPE) {
                $this->type_id = $phrase_types->id($value);
            }
            if ($key == self::FLD_EX_FROM) {
                if ($value == "") {
                    $lib = new library();
                    $result->add_message('from name should not be empty at ' . $lib->dsp_array($in_ex_json));
                } else {
                    $this->fob = $this->import_phrase($value, $test_obj);
                }
            }
            if ($key == self::FLD_EX_TO) {
                if ($value == "") {
                    $lib = new library();
                    $result->add_message('to name should not be empty at ' . $lib->dsp_array($in_ex_json));
                } else {
                    $this->tob = $this->import_phrase($value, $test_obj);
                }
            }
            if ($key == self::FLD_EX_VERB) {
                $vrb = new verb;
                $vrb->set_user($this->user());
                if (!$test_obj) {
                    if ($result->is_ok()) {
                        $vrb->load_by_name($value);
                        if ($vrb->id() <= 0) {
                            $result->add_message('verb "' . $value . '" not found');
                            if ($this->name <> '') {
                                $result->add_message('for triple "' . $this->name . '"');
                            }
                        }
                    }
                } else {
                    $vrb->set_name($value);
                }
                $this->verb = $vrb;
            }
            if ($key == exp_obj::FLD_VIEW) {
                $trp_view = new view($this->user());
                if (!$test_obj) {
                    $trp_view->load_by_name($value, view::class);
                    if ($trp_view->id == 0) {
                        $result->add_message('Cannot find view "' . $value . '" when importing ' . $this->dsp_id());
                    }
                } else {
                    $trp_view->set_name($value);
                }
                $this->view = $trp_view;
            }
        }

        // save the triple in the database
        if (!$test_obj) {
            if ($result->is_ok()) {
                // remove unneeded given names
                $this->set_names();
                $result->add_message($this->save());
            }
        }

        // add related parameters to the word object
        if ($result->is_ok()) {
            log_debug('saved ' . $this->dsp_id());

            if (!$test_obj) {
                if ($this->id() <= 0) {
                    $result->add_message('Triple ' . $this->dsp_id() . ' cannot be saved');
                } else {
                    foreach ($in_ex_json as $key => $value) {
                        if ($result->is_ok()) {
                            if ($key == self::FLD_REFS) {
                                foreach ($value as $ref_data) {
                                    $ref_obj = new ref($this->user());
                                    $ref_obj->phr = $this->phrase();
                                    $result->add($ref_obj->import_obj($ref_data, $test_obj));
                                    $this->ref_lst[] = $ref_obj;
                                }
                            }
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * create a triple object for the export
     * @return triple_exp a reduced triple object that can be used to create a JSON message
     */
    function export_obj(bool $do_load = true): exp_obj
    {
        global $phrase_types;
        global $share_types;
        global $protection_types;

        log_debug();
        $result = new triple_exp();

        if ($this->name <> '') {
            $result->name = $this->name;
        }
        if ($this->description <> '') {
            $result->description = $this->description;
        }
        if ($this->type_id > 0) {
            if ($this->type_id <> $phrase_types->default_id()) {
                $result->type = $this->type_code_id();
            }
        }
        if ($this->fob->name() <> '') {
            $result->from = $this->fob->name();
        }
        if ($this->verb->name() <> '') {
            $result->verb = $this->verb->name();
        }
        if ($this->tob->name() <> '') {
            $result->to = $this->tob->name();
        }

        // add the share type
        if ($this->share_id > 0 and $this->share_id <> $share_types->id(share_type::PUBLIC)) {
            $result->share = $this->share_type_code_id();
        }

        // add the protection type
        if ($this->protection_id > 0 and $this->protection_id <> $protection_types->id(protection_type::NO_PROTECT)) {
            $result->protection = $this->protection_type_code_id();
        }

        if (isset($this->view)) {
            $result->view = $this->view->name();
        }
        if (isset($this->ref_lst)) {
            foreach ($this->ref_lst as $ref) {
                $result->refs[] = $ref->export_obj();
            }
        }

        log_debug(json_encode($result));
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

        if ($this->fob->name() <> '' and $this->verb->name() <> '' and $this->tob->name() <> '') {
            $result .= $this->fob->name() . ' '; // e.g. Australia
            $result .= $this->verb->name() . ' '; // e.g. is a
            $result .= $this->tob->name();       // e.g. Country
        }
        $result .= ' (' . $this->fob->id() . ',' . $this->verb->id() . ',' . $this->tob->id();
        if ($this->id() > 0) {
            $result .= ' -> ' . $this->id() . ')';
        }
        if ($this->user() != null) {
            $result .= ' for user ' . $this->user()->id() . ' (' . $this->user()->name . ')';
        }
        return $result;
    }

    /**
     * either the user edited description
     * or the generic name e.g. Australia is a Country
     * or for the verb is 'is' the category in brackets e.g. Zurich (Canton) or Zurich (City)
     */
    function name(bool $ignore_excluded = false): string
    {
        $result = '';

        if (!$this->is_excluded() or $ignore_excluded) {
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
        global $verbs;
        if ($this->verb->id() == $verbs->id(verb::IS) and $this->fob->name() != '' and $this->tob->name() != '') {
            // use the user defined description
            return $this->fob->name() . ' (' . $this->tob->name() . ')';
        } elseif ($this->fob->name() != '' and $this->verb->name() != '' and $this->tob->name() != '') {
            // or use the standard generic description
            return $this->fob->name() . ' ' . $this->verb->name() . ' ' . $this->tob->name();
        } elseif ($this->fob->name() != '' and $this->tob->name() != '') {
            // or use the short generic description
            return $this->fob->name() . ' ' . $this->tob->name();
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
     * get the database id of the phrase type
     * @return int|null the id of the word type
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
    function display(): string
    {
        log_debug("triple->dsp " . $this->id() . ".");

        $result = ''; // reset the html code var

        // get the link from the database
        $this->load_objects();

        // prepare to show the triple
        $result .= $this->fob->name() . ' '; // e.g. Australia
        $result .= $this->verb->name() . ' '; // e.g. is a
        $result .= $this->tob->name();       // e.g. Country

        return $result;
    }

    /**
     * similar to dsp, but display the reverse expression
     */
    private
    function dsp_r(): string
    {
        log_debug("triple->dsp_r " . $this->id() . ".");

        $result = ''; // reset the html code var

        // get the link from the database
        $this->load_objects();

        // prepare to show the triple
        $result .= $this->tob->name() . ' ';   // e.g. Countries
        $result .= $this->verb->name() . ' '; // e.g. are
        $result .= $this->fob->name();     // e.g. Australia (and others)

        return $result;
    }

    /**
     * display a form to create a triple
     */
    function dsp_add(string $back = ''): string
    {
        log_debug();
        $html = new html_base();
        $result = ''; // reset the html code var

        // at least to create the dummy objects to display the selectors
        $this->load_objects();

        // for creating a new triple the first word / triple is fixed
        $form_name = 'link_add';
        //$result .= 'Create a combined word (semantic triple):<br>';
        $result .= '<br>Define a new relation for <br><br>';
        $result .= '<b>' . $this->fob->name() . '</b> ';
        $result .= $html->dsp_form_start($form_name);
        $result .= $html->input("back", $back);
        $result .= $html->input("confirm", '1');
        $result .= $html->input("from", $this->fob->id());
        $result .= '<div class="form-row">';
        if (isset($this->verb)) {
            $result .= $this->verb->dsp_selector('both', $form_name, "col-sm-6", $back);
        }
        if (isset($this->tob)) {
            $result .= $this->tob->dsp_selector(0, $form_name, 0, "col-sm-6", $back);
        }
        $result .= '</div>';
        $result .= '<br>';
        $result .= $html->dsp_form_end('', $back);

        return $result;
    }

    /**
     * display a form to adjust the link between too words or triples
     */
    function dsp_del(string $back = ''): string
    {
        log_debug("triple->dsp_del " . $this->id() . ".");
        $result = ''; // reset the html code var

        $result .= \html\btn_yesno('Is "' . $this->display() . '" wrong?', '/http/link_del.php?id=' . $this->id() . '&back=' . $back);
        $result .= '<br><br>... and "' . $this->dsp_r() . '" is also wrong.<br><br>If you press Yes, both rules will be removed.';

        return $result;
    }

    /**
     * simply to display a single triple in a table
     */
    function display_linked(): string
    {
        return '<a href="/http/view.php?link=' . $this->id() . '" title="' . $this->name() . '">' . $this->name() . '</a>';
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
        $result .= '      ' . $this->display_linked() . "\n";
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
        $phr = new phrase($this->user());
        // the triple has positive id, but the phrase uses a negative id
        $phr->set_name($this->name, triple::class);
        $phr->set_obj($this);
        log_debug('triple->phrase of ' . $this->dsp_id());
        return $phr;
    }

    /**
     * @returns term the triple object cast into a term object
     * TODO remove lines not needed any more
     */
    function term(): term
    {
        $trm = new term($this->user());
        $trm->set_id_from_obj($this->id(), self::class);
        $trm->set_name($this->name(), triple::class);
        $trm->set_obj($this);
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
        log_debug('triple->not_used (' . $this->id() . ')');

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
        return $db_con->load_sql_not_changed($this->id(), $this->owner_id);
    }

    /**
     * @returns bool true if no other user has modified the triple
     */
    function not_changed(): bool
    {
        log_debug('triple->not_changed (' . $this->id() . ') by someone else than the owner (' . $this->owner_id . ')');

        global $db_con;
        $result = true;

        if ($this->id() == 0) {
            log_err('The id must be set to check if the triple has been changed');
        } else {
            $qp = $this->not_changed_sql($db_con);
            $db_row = $db_con->get1($qp);
            if ($db_row[user::FLD_ID] > 0) {
                $result = false;
            }
        }
        log_debug('triple->not_changed for ' . $this->id() . ' is ' . zu_dsp_bool($result));
        return $result;
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
    protected function add_usr_cfg(string $class = self::class): bool
    {
        global $db_con;
        $result = true;

        if (!$this->has_usr_cfg()) {
            if (isset($this->fob) and isset($this->tob)) {
                log_debug('triple->add_usr_cfg for "' . $this->fob->name() . '"/"' . $this->tob->name() . '" by user "' . $this->user()->name . '"');
            } else {
                log_debug('triple->add_usr_cfg for "' . $this->id() . '" and user "' . $this->user()->name . '"');
            }

            // check again if there ist not yet a record
            $db_con->set_type(sql_db::TBL_TRIPLE, true);
            $qp = new sql_par(self::class);
            $qp->name = 'triple_add_usr_cfg';
            $db_con->set_name($qp->name);
            $db_con->set_usr($this->user()->id());
            $db_con->set_where_std($this->id());
            $qp->sql = $db_con->select_by_set_id();
            $qp->par = $db_con->get_par();
            $db_row = $db_con->get1($qp);
            if ($db_row != null) {
                $this->usr_cfg_id = $db_row[self::FLD_ID];
            }
            if (!$this->has_usr_cfg()) {
                // create an entry in the user sandbox
                $db_con->set_type(sql_db::TBL_USER_PREFIX . sql_db::TBL_TRIPLE);
                $log_id = $db_con->insert(array(self::FLD_ID, user::FLD_ID), array($this->id(), $this->user()->id()));
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
     * @param sql_creator $sc with the target db_type set
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_user_changes(sql_creator $sc, string $class = self::class): sql_par
    {
        $sc->set_type(sql_db::TBL_TRIPLE, true);
        $sc->set_fields(array_merge(
            self::FLD_NAMES_USR,
            self::FLD_NAMES_NUM_USR
        ));
        return parent::load_sql_user_changes($sc, $class);
    }

    /**
     * set the log entry parameter for a new value
     * e.g. that the user can see "added ABB is a Company"
     */
    function log_link_add(): change_log_link
    {
        log_debug('triple->log_link_add for ' . $this->dsp_id() . ' by user "' . $this->user()->name . '"');
        $log = new change_log_link($this->user());
        $log->action = change_log_action::ADD;
        $log->set_table(change_log_table::TRIPLE);
        $log->new_from = $this->fob;
        $log->new_link = $this->verb;
        $log->new_to = $this->tob;
        $log->row_id = 0;
        $log->add();

        return $log;
    }

    /**
     * set the main log entry parameters for updating the triple itself
     */
    function log_upd(): change_log_link
    {
        $log = new change_log_link($this->user());
        $log->action = change_log_action::UPDATE;
        if ($this->can_change()) {
            $log->set_table(change_log_table::TRIPLE);
        } else {
            $log->set_table(change_log_table::TRIPLE_USR);
        }

        return $log;
    }

    /**
     * set the log entry parameter to delete a triple
     * e.g. that the user can see "ABB is a Company not anymore"
     */
    function log_del_link(): change_log_link
    {
        log_debug('triple->log_link_del for ' . $this->dsp_id() . ' by user "' . $this->user()->name . '"');
        $log = new change_log_link($this->user());
        $log->action = change_log_action::DELETE;
        $log->set_table(change_log_table::TRIPLE);
        $log->old_from = $this->fob;
        $log->old_link = $this->verb;
        $log->old_to = $this->tob;
        $log->row_id = $this->id;
        $log->add();

        return $log;
    }

    /**
     * set the main log entry parameters for updating one display triple field
     */
    function log_upd_field(): change_log_named
    {
        $log = new change_log_named($this->user());
        $log->action = change_log_action::UPDATE;
        if ($this->can_change()) {
            $log->set_table(change_log_table::TRIPLE);
        } else {
            $log->set_table(change_log_table::TRIPLE_USR);
        }

        return $log;
    }


    /**
     * check if a term with the unique name already exists
     * returns null if no similar object is found
     * or returns the term with the same unique key that is not the actual object
     * similar to sandbox named get_similar but
     * @return term|null a filled object that has the same name
     *                or a sandbox object with id() = 0 if nothing similar has been found
     */
    function get_similar_named(): ?term
    {
        $trm = new term($this->user());
        if ($this->name() != '') {
            $trm->load_by_name($this->name());
            if ($trm->id_obj() == 0) {
                $similar_trp = new triple($this->user());
                $similar_trp->load_by_name_generated($this->name());
                if ($similar_trp->id() != 0) {
                    $trm = $similar_trp->term();
                }
            }
        }
        if ($trm->id_obj() == 0 or $trm->id_obj() == $this->id()) {
            $trm = null;
        }

        return $trm;
    }

    /**
     * check if the given name can be used for this triple
     * @return string
     */
    private function is_name_used_msg(): string
    {
        $result = '';
        // check if the name is used
        $similar = $this->get_similar_named();
        // if the similar object is not the same as $this object, suggest renaming $this object
        if ($similar != null) {
            $result = $similar->id_used_msg($this);
        }
        return $result;
    }

    /**
     * set the update parameters for the triple name
     */
    function save_field_name(sql_db $db_con, sandbox $db_rec, sandbox $std_rec): string
    {
        $result = '';

        // the name field is a generic created field, so update it before saving
        // the generic name of $this is saved to the database for faster uniqueness check (TODO to be checked if this is really faster)
        $this->set_names();

        if ($db_rec->name() <> $this->name()) {
            $result = $this->is_name_used_msg($this->name());
            if ($result == '') {
                $log = $this->log_upd_field();
                $log->old_value = $db_rec->name();
                // ignore excluded to not overwrite an existing name
                $log->new_value = $this->name(true);
                $log->std_value = $std_rec->name();
                $log->row_id = $this->id;
                $log->set_field(self::FLD_NAME);
                $result .= $this->save_field_user($db_con, $log);
            }
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
            if ($this->name_given() != null) {
                $result = $this->is_name_used_msg($this->name_given());
            }
            if ($result == '') {
                $log = $this->log_upd_field();
                $log->old_value = $db_rec->name_given();
                $log->new_value = $this->name_given();
                $log->std_value = $std_rec->name_given();
                $log->row_id = $this->id;
                $log->set_field(self::FLD_NAME_GIVEN);
                $result .= $this->save_field_user($db_con, $log);
            }
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
            $result = $this->is_name_used_msg($this->name_generated());
            if ($result == '') {
                $log = $this->log_upd_field();
                $log->old_value = $db_rec->name_generated;
                $log->new_value = $this->name_generated();
                $log->std_value = $std_rec->name_generated;
                $log->row_id = $this->id;
                $log->set_field(self::FLD_NAME_AUTO);
                $result .= $this->save_field_user($db_con, $log);
            }
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
            $log->set_field(sandbox_named::FLD_DESCRIPTION);
            $result .= $this->save_field_user($db_con, $log);
        }
        return $result;
    }

    /**
     * save all updated triple fields excluding id fields (from, verb and to), because already done when adding a triple
     */
    function save_triple_fields(sql_db $db_con, triple $db_rec, triple $std_rec): string
    {
        $result = $this->save_field_triple_description($db_con, $db_rec, $std_rec);
        $result .= $this->save_field_excluded($db_con, $db_rec, $std_rec);
        $result .= $this->save_field_type($db_con, $db_rec, $std_rec);
        log_debug('triple->save_fields all fields for ' . $this->dsp_id() . ' has been saved');
        return $result;
    }

    /**
     * save all updated triple fields excluding id fields (from, verb and to), because already done when adding a triple
     */
    function save_name_fields(sql_db $db_con, triple $db_rec, triple $std_rec): string
    {
        $result = $this->save_field_name($db_con, $db_rec, $std_rec);
        if ($result == '') {
            $result .= $this->save_field_name_given($db_con, $db_rec, $std_rec);
        }
        if ($result == '') {
            $result .= $this->save_field_name_generated($db_con, $db_rec, $std_rec);
        }
        return $result;
    }

    /**
     * save updated the triple id fields (from, verb and to)
     * should only be called if the user is the owner and nobody has used the triple
     */
    function save_id_fields(sql_db $db_con, sandbox|triple $db_rec, sandbox|triple $std_rec): string
    {
        $result = '';
        if ($db_rec->fob->id() <> $this->fob->id()
            or $db_rec->verb->id() <> $this->verb->id()
            or $db_rec->tob->id() <> $this->tob->id()) {
            log_debug('triple->save_id_fields to "' . $this->tob->name() . '" (' . $this->tob->id() . ') from "' . $db_rec->tob->name() . '" (' . $db_rec->tob->id() . ') standard ' . $std_rec->tob->name() . '" (' . $std_rec->tob->id() . ')');
            $log = $this->log_upd();
            $log->old_from = $db_rec->fob;
            $log->new_from = $this->fob;
            $log->std_from = $std_rec->fob;
            $log->old_link = $db_rec->verb;
            $log->new_link = $this->verb;
            $log->std_link = $std_rec->verb;
            $log->old_to = $db_rec->tob;
            $log->new_to = $this->tob;
            $log->std_to = $std_rec->tob;
            $log->row_id = $this->id;
            //$log->set_field(self::FLD_FROM);
            if ($log->add()) {
                $db_con->set_type(sql_db::TBL_TRIPLE);
                if (!$db_con->update($this->id(),
                    array("from_phrase_id", "verb_id", "to_phrase_id"),
                    array($this->fob->id(), $this->verb->id(), $this->tob->id()))) {
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
    function save_id_if_updated(
        sql_db         $db_con,
        triple|sandbox $db_rec,
        triple|sandbox $std_rec): string
    {
        $result = '';

        if ($db_rec->fob->id() <> $this->fob->id()
            or $db_rec->verb->id() <> $this->verb->id()
            or $db_rec->tob->id() <> $this->tob->id()) {
            // check if target link already exists
            log_debug('triple->save_id_if_updated check if target link already exists ' . $this->dsp_id() . ' (has been "' . $db_rec->dsp_id() . '")');
            $db_chk = clone $this;
            $db_chk->set_id(0); // to force the load by the id fields
            $db_chk->load_standard();
            if ($db_chk->id() > 0) {
                // ... if yes request to delete or exclude the record with the id parameters before the change
                $to_del = clone $db_rec;
                $msg = $to_del->del();
                $result .= $msg->get_last_message();
                if (!$msg->is_ok()) {
                    $result .= 'Failed to delete the unused work link';
                }
                if ($result = '') {
                    // ... and use it for the update
                    $this->set_id($db_chk->id());
                    $this->owner_id = $db_chk->owner_id;
                    // force including again
                    $this->include();
                    $db_rec->exclude();
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
                    $this->set_id(0);
                    $this->owner_id = $this->user()->id();
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
        log_debug('triple->add new triple for "' . $this->fob->name() . '" ' . $this->verb->name() . ' "' . $this->tob->name() . '"');

        global $db_con;
        $result = new user_message();

        // log the insert attempt first
        $log = $this->log_link_add();
        if ($log->id() > 0) {
            // insert the new triple
            $db_con->set_type(sql_db::TBL_TRIPLE);
            $this->set_id($db_con->insert(array("from_phrase_id", "verb_id", "to_phrase_id", "user_id"),
                array($this->fob->id(), $this->verb->id(), $this->tob->id(), $this->user()->id())));
            // TODO make sure on all add functions that the database object is always set
            //array($this->fob->id(), $this->verb->id() , $this->tob->id(), $this->user()->id()));
            if ($this->id() > 0) {
                // update the id in the log
                if (!$log->add_ref($this->id())) {
                    $result->add_message('Updating the reference in the log failed');
                    // TODO do rollback or retry?
                } else {

                    // create an empty db_rec element to force saving of all set fields
                    $db_rec = new triple($this->user());
                    $db_rec->fob = $this->fob;
                    $db_rec->verb = $this->verb;
                    $db_rec->tob = $this->tob;
                    $std_rec = clone $db_rec;
                    // save the triple fields
                    $result->add_message($this->save_name_fields($db_con, $db_rec, $std_rec));
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
        log_debug($this->dsp_id());

        global $db_con;
        $html = new html_base();

        // check the preserved names
        $result = $this->check_preserved();

        if ($result == '') {

            // load the objects if needed
            $this->load_objects();

            // build the database object because the is anyway needed
            $db_con->set_usr($this->user()->id());
            $db_con->set_type(sql_db::TBL_TRIPLE);

            // check if the opposite triple already exists and if yes, ask for confirmation
            if ($this->id() <= 0) {
                log_debug('check if a new triple for "' . $this->fob->name() . '" and "' . $this->tob->name() . '" needs to be created');
                // check if the reverse triple is already in the database
                $db_chk_rev = clone $this;
                $db_chk_rev->fob = $this->tob;
                $db_chk_rev->fob->set_id($this->tob->id());
                $db_chk_rev->tob = $this->fob;
                $db_chk_rev->tob->set_id($this->fob->id());
                // remove the name in the object to prevent loading by name
                $db_chk_rev->name = '';
                $db_chk_rev->load_standard();
                if ($db_chk_rev->id() > 0) {
                    $this->set_id($db_chk_rev->id());
                    $result .= $html->dsp_err('The reverse of "' . $this->fob->name() . ' ' . $this->verb->name() . ' ' . $this->tob->name() . '" already exists. Do you really want to create both sides?');
                }
            }

            // check if the triple already exists and if yes, update it if needed
            if ($this->id() <= 0 and $result == '') {
                log_debug('check if a new triple for "' . $this->fob->name() . '" and "' . $this->tob->name() . '" needs to be created');
                // check if the same triple is already in the database
                $db_chk = clone $this;
                $db_chk->load_standard();
                if ($db_chk->id() > 0) {
                    $this->set_id($db_chk->id());
                }
            }
        }

        // try to save the link only if no question has been raised utils now
        if ($result == '') {
            // check if a new value is supposed to be added
            if ($this->id() <= 0) {
                $result .= $this->is_name_used_msg($this->name());
                if ($result == '') {
                    $result .= $this->add()->get_last_message();
                    if ($result != '') {
                        log_info($result);
                    }
                }
            } else {
                log_debug('update ' . $this->dsp_id());
                // read the database values to be able to check if something has been changed;
                // done first, because it needs to be done for user and general phrases
                $db_rec = new triple($this->user());
                if (!$db_rec->load_by_id($this->id())) {
                    $result .= 'Reloading of triple failed';
                    if ($result != '') {
                        log_err($result);
                    }
                }
                log_debug('database triple "' . $db_rec->name() . '" (' . $db_rec->id() . ') loaded');
                $std_rec = new triple($this->user()); // the user must also be set to allow to take the ownership
                $std_rec->set_id($this->id());
                if (!$std_rec->load_standard()) {
                    $result .= 'Reloading of the default values for triple failed';
                    if ($result != '') {
                        log_err($result);
                    }
                }
                log_debug('standard triple settings for "' . $std_rec->name() . '" (' . $std_rec->id() . ') loaded');

                // for a correct user triple detection (function can_change) set the owner even if the triple has not been loaded before the save
                if ($this->owner_id <= 0) {
                    $this->owner_id = $std_rec->owner_id;
                }

                // check if the id parameters are supposed to be changed
                if ($result == '') {
                    $result .= $this->save_id_if_updated($db_con, $db_rec, $std_rec);
                    if ($result != '') {
                        log_err($result);
                    }
                }

                if ($result == '') {
                    $result .= $this->save_name_fields($db_con, $db_rec, $std_rec);
                }

                // if a problem has appeared up to here, don't try to save the values
                // the problem is shown to the user by the calling interactive script
                if ($result == '') {
                    $result .= $this->save_triple_fields($db_con, $db_rec, $std_rec);
                    if ($result != '') {
                        log_err($result);
                    }
                }
            }
        }


        return $result;
    }

    /**
     * delete the phrase groups which where this triple is used
     */
    function del_links(): user_message
    {
        $result = new user_message();

        // collect all phrase groups where this triple is used
        $grp_lst = new phrase_group_list($this->user());
        $grp_lst->phr = $this->phrase();
        $grp_lst->load_by_vars();

        // collect all values related to this triple
        $val_lst = new value_list($this->user());
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
