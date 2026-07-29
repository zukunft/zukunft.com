<?php

/*

    test/create/test_log.php - create the test change log entries
    ------------------------


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

namespace Zukunft\ZukunftCom\test\php\create;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::MODEL_LOG . 'change.php';
include_once paths::MODEL_LOG . 'change_field.php';
include_once paths::MODEL_LOG . 'change_table.php';
include_once paths::MODEL_LOG . 'change_link.php';
include_once paths::MODEL_LOG . 'change_log_list.php';
include_once paths::MODEL_LOG . 'change_values_big.php';
include_once paths::MODEL_LOG . 'change_values_geo_big.php';
include_once paths::MODEL_LOG . 'change_values_geo_norm.php';
include_once paths::MODEL_LOG . 'change_values_geo_prime.php';
include_once paths::MODEL_LOG . 'change_values_norm.php';
include_once paths::MODEL_LOG . 'change_values_prime.php';
include_once paths::MODEL_LOG . 'change_values_text_big.php';
include_once paths::MODEL_LOG . 'change_values_text_norm.php';
include_once paths::MODEL_LOG . 'change_values_text_prime.php';
include_once paths::MODEL_LOG . 'change_values_time_big.php';
include_once paths::MODEL_LOG . 'change_values_time_norm.php';
include_once paths::MODEL_LOG . 'change_values_time_prime.php';
include_once paths::MODEL_LOG . 'changes_big.php';
include_once paths::MODEL_LOG . 'changes_norm.php';
include_once paths::MODEL_USER . 'user_db.php';
include_once paths::MODEL_VALUE . 'value.php';
include_once paths::MODEL_VALUE . 'value_db.php';
include_once paths::MODEL_VALUE . 'value_geo.php';
include_once paths::MODEL_VALUE . 'value_text.php';
include_once paths::MODEL_VALUE . 'value_time.php';
include_once paths::MODEL_WORD . 'triple.php';
include_once paths::MODEL_WORD . 'word.php';
include_once paths::MODEL_WORD . 'word_db.php';
include_once paths::SHARED_CONST . 'components.php';
include_once paths::SHARED_CONST . 'formulas.php';
include_once paths::SHARED_CONST . 'refs.php';
include_once paths::SHARED_CONST . 'sources.php';
include_once paths::SHARED_CONST . 'triples.php';
include_once paths::SHARED_CONST . 'users.php';
include_once paths::SHARED_CONST . 'values.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED_CONST . 'words.php';
include_once paths::SHARED_ENUM . 'change_actions.php';
include_once paths::SHARED_ENUM . 'change_fields.php';
include_once paths::SHARED_ENUM . 'change_tables.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED . 'library.php';
include_once paths::SHARED_TYPES . 'api_types.php';
include_once paths::SHARED_TYPES . 'phrase_types.php';
include_once paths::SHARED_TYPES . 'protection_types.php';
include_once paths::SHARED_TYPES . 'verbs.php';
include_once html_paths::LOG . 'change_log_list.php';
include_once test_paths::CONST . 'formula_names.php';
include_once test_paths::CONST . 'triple_names.php';
include_once test_paths::CONST . 'word_names.php';
include_once test_paths::CREATE . 'test_const.php';
include_once test_paths::UTILS . 'test_cleanup.php';
include_once test_paths::UTILS . 'test_lib.php';
include_once paths::SHARED_CONST_FIELDS . 'fields.php';
include_once paths::SHARED_CONST_FIELDS . 'word_fields.php';
include_once paths::SHARED_CONST_FIELDS . 'value_fields.php';

use Zukunft\ZukunftCom\main\php\cfg\log\change;
use Zukunft\ZukunftCom\main\php\cfg\log\change_field;
use Zukunft\ZukunftCom\main\php\cfg\log\change_table;
use Zukunft\ZukunftCom\main\php\cfg\log\change_link;
use Zukunft\ZukunftCom\main\php\cfg\log\change_log_list;
use Zukunft\ZukunftCom\main\php\cfg\log\change_values_big;
use Zukunft\ZukunftCom\main\php\cfg\log\change_values_geo_big;
use Zukunft\ZukunftCom\main\php\cfg\log\change_values_geo_norm;
use Zukunft\ZukunftCom\main\php\cfg\log\change_values_geo_prime;
use Zukunft\ZukunftCom\main\php\cfg\log\change_values_norm;
use Zukunft\ZukunftCom\main\php\cfg\log\change_values_prime;
use Zukunft\ZukunftCom\main\php\cfg\log\change_values_text_big;
use Zukunft\ZukunftCom\main\php\cfg\log\change_values_text_norm;
use Zukunft\ZukunftCom\main\php\cfg\log\change_values_text_prime;
use Zukunft\ZukunftCom\main\php\cfg\log\change_values_time_big;
use Zukunft\ZukunftCom\main\php\cfg\log\change_values_time_norm;
use Zukunft\ZukunftCom\main\php\cfg\log\change_values_time_prime;
use Zukunft\ZukunftCom\main\php\cfg\log\changes_big;
use Zukunft\ZukunftCom\main\php\cfg\log\changes_norm;
use Zukunft\ZukunftCom\main\php\cfg\user\user_db;
use Zukunft\ZukunftCom\main\php\cfg\value\value;
use Zukunft\ZukunftCom\main\php\cfg\value\value_db;
use Zukunft\ZukunftCom\main\php\cfg\value\value_geo;
use Zukunft\ZukunftCom\main\php\cfg\value\value_text;
use Zukunft\ZukunftCom\main\php\cfg\value\value_time;
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\cfg\word\word_db;
use Zukunft\ZukunftCom\main\php\shared\const\components;
use Zukunft\ZukunftCom\main\php\shared\const\refs;
use Zukunft\ZukunftCom\main\php\shared\const\sources;
use Zukunft\ZukunftCom\main\php\shared\const\users;
use Zukunft\ZukunftCom\main\php\shared\const\values;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\enum\change_actions;
use Zukunft\ZukunftCom\main\php\shared\enum\change_fields;
use Zukunft\ZukunftCom\main\php\shared\enum\change_tables;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\main\php\shared\types\phrase_types;
use Zukunft\ZukunftCom\main\php\shared\types\protection_types;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;
use Zukunft\ZukunftCom\main\php\web\log\change_log_list as change_log_list_ui;
use Zukunft\ZukunftCom\test\php\const\formula_names;
use Zukunft\ZukunftCom\test\php\const\triple_names;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;
use Zukunft\ZukunftCom\test\php\utils\test_lib;
use Zukunft\ZukunftCom\main\php\shared\const\fields\fields;
use Zukunft\ZukunftCom\main\php\shared\const\fields\word_fields;
use Zukunft\ZukunftCom\main\php\shared\const\fields\value_fields;
use DateTime;


class test_log
{

    /*
     * init
     */

    // use the global test environment
    private test_cleanup $env;

    function __construct(test_cleanup $env) {
        $this->env = $env;
    }


    /*
     * vars
     */

    private int $chg_log_seq = 0;

    function chg_log_seq(): int
    {
        $this->chg_log_seq++;
        return $this->chg_log_seq;
    }


    /*
     * unit
     */

    function log_table(): change_table
    {
        $tbl = new change_table('System Test Table');
        $tbl->id = 3;
        return $tbl;
    }

    function log_field(): change_field
    {
        $fld = new change_field('System Test Field');
        $fld->id = 4;
        $fld->tbl_id = $this->log_table()->id;
        return $fld;
    }

    private function log_entry(): change
    {
        $chg = new change($this->env->usr_system);
        $chg->id = $this->chg_log_seq();
        $chg->set_time_str(test_const::DUMMY_DATETIME);
        return $chg;
    }

    private function log_entry_add(): change
    {
        $chg = $this->log_entry();
        $chg->set_action(change_actions::ADD);
        return $chg;
    }

    /**
     * an insert change log entry of an added named user sandbox object with some dummy values
     * @return change with a change log entry of adding a word name as a sample
     */
    function log_word_add(): change
    {
        $chg = $this->log_entry_add();
        $chg->set_table(change_tables::WORD);
        $chg->set_field(change_fields::FLD_WORD_NAME);
        $chg->new_value = word_names::MATH;
        $chg->row_id = word_names::MATH_ID;
        return $chg;
    }

    /**
     * an insert change log entry of updating a named user sandbox object
     * @return change with a change log entry of updating a word name as a sample
     */
    function log_word_update(): change
    {
        $chg = $this->log_word_add();
        $chg->set_action(change_actions::UPDATE);
        $chg->old_value = word_names::TEST_RENAMED;
        return $chg;
    }

    /**
     * an insert change log entry of deleting a named user sandbox object
     * @return change with a change log entry of deleting a word as a sample
     */
    function log_word_delete(): change
    {
        $chg = $this->log_word_update();
        $chg->set_action(change_actions::DELETE);
        $chg->new_value = null;
        return $chg;
    }

    /**
     * an insert change log entry for a reference value of a named user sandbox object
     * @return change with a change log entry of adding a word type as a sample
     */
    function log_word_add_type(): change
    {
        global $sys;
        $chg = $this->log_word_add();
        $chg->set_field(change_fields::FLD_PHRASE_TYPE);
        $chg->new_value = phrase_types::TIME;
        $chg->new_id = $sys->typ_lst->phr_typ->id(phrase_types::TIME);
        return $chg;
    }

    /**
     * an insert change log entry for a reference of a named user sandbox object
     * @return change with a change log entry of updating a word type as a sample
     */
    function log_word_update_type(): change
    {
        global $sys;
        $chg = $this->log_word_add_type();
        $chg->set_action(change_actions::UPDATE);
        $chg->old_value = phrase_types::MEASURE;
        $chg->old_id = $sys->typ_lst->phr_typ->id(phrase_types::MEASURE);
        return $chg;
    }

    /**
     * an insert change log entry for a reference of a named user sandbox object
     * @return change with a change log entry of unsetting a word type as a sample
     */
    function log_word_delete_type(): change
    {
        $chg = $this->log_word_update_type();
        $chg->set_action(change_actions::DELETE);
        $chg->new_value = null;
        $chg->new_id = null;
        return $chg;
    }

    /**
     * an insert change log entry for the description of a named user sandbox object
     * @return change with a change log entry of adding a word description as a sample
     */
    function log_word_add_description(): change
    {
        $chg = $this->log_word_add();
        $chg->set_field(fields::FLD_DESCRIPTION);
        $chg->new_value = word_names::MATH_COM;
        return $chg;
    }

    /**
     * an insert change log entry for the cached impact number of a named user sandbox object;
     * the initial impact of an added object is zero, so the change log table pure
     * shows 'added impact "0"'
     * @return change with a change log entry of adding the word impact as a sample
     */
    function log_word_add_impact(): change
    {
        $chg = $this->log_word_add();
        $chg->set_field(fields::FLD_IMPACT);
        $chg->new_value = '0';
        return $chg;
    }

    /**
     * an insert change log entry for the cached usage counter of a named user sandbox object;
     * like the impact the usage is a system internal, so the change log table pure
     * shows 'added usage "0"' to admin users only
     * @return change with a change log entry of adding the word usage as a sample
     */
    function log_word_add_usage(): change
    {
        $chg = $this->log_word_add();
        $chg->set_field(fields::FLD_USAGE);
        $chg->new_value = '0';
        return $chg;
    }

    /**
     * an insert change log entry for the protection type of a named user sandbox object;
     * the protection is logged with the numeric type id as the value (like sandbox_multi::add_field,
     * not add_type_field), so the change log table pure must resolve the id to the protection name
     * @return change with a change log entry of increasing the word protection as a sample
     */
    function log_word_update_protection(): change
    {
        global $sys;
        $chg = $this->log_word_add();
        $chg->set_action(change_actions::UPDATE);
        $chg->set_field(fields::FLD_PROTECT);
        $chg->old_value = (string)$sys->typ_lst->ptc_typ->id(protection_types::NO_PROTECT);
        $chg->new_value = (string)$sys->typ_lst->ptc_typ->id(protection_types::ADMIN);
        return $chg;
    }

    /**
     * an insert change log entry for the owner (user id) of a named user sandbox object; the owner is
     * set to the change author (the system user), so the change log table pure resolves the user id to
     * the author name and shows 'set owner to zukunft.com system' (see change_log_named::what_text)
     * @return change with a change log entry of setting the word owner to the system user as a sample
     */
    function log_word_add_user_id(): change
    {
        $chg = $this->log_word_add();
        $chg->set_field(user_db::FLD_ID);
        $chg->new_value = (string)users::SYSTEM_ID;
        return $chg;
    }

    /**
     * an insert change log entry for the default view (view_id) of a named object in the user sandbox
     * (the user_words overlay table); the view name is stored as the value and the view id as the
     * reference (like sql_par_field_list::add_link_field), and because it is a user sandbox change the
     * change log table pure shows 'user added view id "Word"' (see change_log_named::what_text)
     * @return change with a change log entry of setting the word view in the user sandbox
     */
    function log_word_add_view(): change
    {
        $chg = $this->log_word_add();
        $chg->set_table(change_tables::WORD_USR);
        $chg->set_field(fields::FLD_VIEW);
        $chg->new_value = views::WORD_NAME;
        $chg->new_id = views::WORD_ID;
        return $chg;
    }

    /**
     * an insert change log entry for the default view of a named object where only the view id is
     * logged (like a save that only carries the view id, see word::set_view_id); the change log
     * display resolves the view name from the cache so that not an empty value is shown to the user
     * @return change with a change log entry of setting the word view by id as a sample
     */
    function log_word_add_view_id(): change
    {
        $chg = $this->log_word_add();
        $chg->set_field(fields::FLD_VIEW);
        $chg->new_value = null;
        $chg->new_id = views::START_ID;
        return $chg;
    }

    /**
     * an insert change log entry that adds an empty view (view_id) in the user sandbox (user_words);
     * adding an empty value in the sandbox removes the user's overwrite for that field, so the change
     * log table pure shows 'remove user overwrite for view' instead of 'user added view id ""'
     * (see change_log_named::what_text)
     * @return change with a change log entry of removing the user view overwrite
     */
    function log_word_remove_view(): change
    {
        $chg = $this->log_word_add();
        $chg->set_table(change_tables::WORD_USR);
        $chg->set_field(fields::FLD_VIEW);
        $chg->new_value = '';
        return $chg;
    }

    /**
     * @return change log entry created by adding a verb
     */
    function log_verb_add(): change
    {
        $chg = $this->log_entry_add();
        $chg->set_table(change_tables::VERB);
        $chg->set_field(change_fields::FLD_VERB_NAME);
        $chg->new_value = verbs::IS;
        $chg->row_id = verbs::IS_ID;
        return $chg;
    }

    /**
     * @return change log entry created by adding a triple
     */
    function log_triple_add(): change
    {
        $chg = $this->log_entry_add();
        $chg->set_table(change_tables::TRIPLE);
        $chg->set_field(change_fields::FLD_TRIPLE_NAME);
        $chg->new_value = triple_names::MATH_CONST;
        $chg->row_id = triple_names::MATH_CONST_ID;
        return $chg;
    }

    /**
     * @return change log entry created by adding a source
     */
    function log_source_add(): change
    {
        $chg = $this->log_entry_add();
        $chg->set_table(change_tables::SOURCE);
        $chg->set_field(change_fields::FLD_SOURCE_NAME);
        $chg->new_value = sources::SIB;
        $chg->row_id = sources::SIB_ID;
        return $chg;
    }

    /**
     * @return change an insert change log entry for a reference of a named user sandbox object
     */
    function log_ref_add(): change
    {
        $chg = $this->log_entry_add();
        $chg->set_table(change_tables::REF);
        $chg->set_field(change_fields::FLD_REF_KEY);
        $chg->new_value = refs::PI_KEY;
        $chg->row_id = refs::PI_ID;
        return $chg;
    }

    /**
     * @return change an insert change log entry for a reference of a named user sandbox object
     */
    function log_ref_update(): change
    {
        global $sys;
        $chg = $this->log_ref_add();
        $chg->set_action(change_actions::UPDATE);
        $chg->old_value = phrase_types::MEASURE;
        $chg->old_id = $sys->typ_lst->phr_typ->id(phrase_types::MEASURE);
        return $chg;
    }

    /**
     * @return change an insert change log entry for a reference of a named user sandbox object
     */
    function log_ref_delete(): change
    {
        $chg = $this->log_ref_update();
        $chg->new_value = null;
        $chg->new_id = null;
        return $chg;
    }

    /**
     * @return change_values_prime log entry created by adding a value
     */
    function log_value_add(): change_values_prime
    {
        $chg = new change_values_prime($this->env->usr_system);
        $chg->id = $this->chg_log_seq();
        $chg->set_time_str(test_const::DUMMY_DATETIME);
        $chg->set_action(change_actions::ADD);
        $chg->set_table(change_tables::VALUE);
        $chg->set_field(change_fields::FLD_NUMERIC_VALUE);
        $chg->new_value = values::PI;
        $chg->row_id = values::PI_ID;
        return $chg;
    }

    /**
     * @return change log entry created by adding a formula
     */
    function log_formula_add(): change
    {
        $chg = $this->log_entry_add();
        $chg->set_table(change_tables::FORMULA);
        $chg->set_field(change_fields::FLD_FORMULA_NAME);
        $chg->new_value = formula_names::SCALE_TO_SEC;
        $chg->row_id = formula_names::SCALE_TO_SEC_ID;
        return $chg;
    }

    /**
     * @return change log entry created by adding the increase formula name
     */
    function log_formula_increase_add(): change
    {
        $chg = $this->log_entry_add();
        $chg->set_table(change_tables::FORMULA);
        $chg->set_field(change_fields::FLD_FORMULA_NAME);
        $chg->new_value = formula_names::INCREASE;
        $chg->row_id = formula_names::INCREASE_ID;
        return $chg;
    }

    /**
     * @return change log entry created by setting the expression of the increase formula
     */
    function log_formula_increase_exp(): change
    {
        $chg = $this->log_entry_add();
        $chg->set_table(change_tables::FORMULA);
        $chg->set_field(change_fields::FLD_FORMULA_USR_TEXT);
        $chg->new_value = formula_names::INCREASE_EXP;
        $chg->row_id = formula_names::INCREASE_ID;
        return $chg;
    }

    /**
     * @return change_log_list the changes of creating the increase formula (name and expression)
     */
    function log_list_formula_increase(): change_log_list
    {
        $log_lst = new change_log_list();
        $log_lst->add($this->log_formula_increase_add());
        $log_lst->add($this->log_formula_increase_exp());
        return $log_lst;
    }

    /**
     * @return change log entry created by adding a view
     */
    function log_view_add(): change
    {
        $chg = $this->log_entry_add();
        $chg->set_table(change_tables::VIEW);
        $chg->set_field(change_fields::FLD_VIEW_NAME);
        $chg->new_value = views::START;
        $chg->row_id = views::START_ID;
        return $chg;
    }

    /**
     * @return change log entry created by adding a component
     */
    function log_component_add(): change
    {
        $chg = $this->log_entry_add();
        $chg->set_table(change_tables::VIEW_COMPONENT);
        $chg->set_field(change_fields::FLD_COMPONENT_NAME);
        $chg->new_value = components::MATRIX_NAME;
        $chg->row_id = components::MATRIX_ID;
        return $chg;
    }

    /**
     * @return changes_norm a change log entry of a group where the id is a 512bit field and not an id
     */
    function log_norm(): changes_norm
    {
        $chg = new changes_norm($this->env->usr_system);
        $chg->set_time_str(test_const::DUMMY_DATETIME);
        $chg->set_action(change_actions::ADD);
        $chg->set_table(change_tables::WORD);
        $chg->set_field(change_fields::FLD_WORD_NAME);
        $chg->new_value = word_names::MATH;
        $chg->row_id = 1;
        return $chg;
    }

    /**
     * @return changes_big a change log entry of a group where the id is a text field and not an id
     */
    function log_big(): changes_big
    {
        $chg = new changes_big($this->env->usr_system);
        $chg->set_time_str(test_const::DUMMY_DATETIME);
        $chg->set_action(change_actions::ADD);
        $chg->set_table(change_tables::WORD);
        $chg->set_field(change_fields::FLD_WORD_NAME);
        $chg->new_value = word_names::MATH;
        $chg->row_id = 1;
        return $chg;
    }

    /**
     * @return object an insert change log entry of a value with some dummy values and a standard group id
     */
    function log_obj_from_class(string $class): object
    {
        $lib = new library();

        $t_grp = new test_groups($this->env);
        $log = $this->log_class_to_object($class);
        $val_class = $this->log_class_to_value_class($class);
        $val_fld = $this->log_class_to_value_field($class);
        $val = $this->log_class_to_value($class);
        $log->set_time_str(test_const::DUMMY_DATETIME);
        $log->set_action(change_actions::ADD);
        $log->set_table($lib->class_to_table($val_class));
        $log->set_field($val_fld);
        $log->group_id = $t_grp->group()->id();
        $log->new_value = $val;
        $log->row_id = 1;
        return $log;
    }

    /**
     * create the change log object based on the log class name
     * @param string $class the name of the log class
     * @return change|change_values_big|change_values_geo_big|change_values_geo_norm|change_values_geo_prime|change_values_norm|change_values_prime|change_values_text_prime|change_values_text_norm|change_values_text_big|change_values_time_big|change_values_time_norm|change_values_time_prime|changes_big|changes_norm
     */
    private function log_class_to_object(string $class): change|change_values_big|change_values_geo_big|change_values_geo_norm|change_values_geo_prime|change_values_norm|change_values_prime|change_values_text_prime|change_values_text_norm|change_values_text_big|change_values_time_big|change_values_time_norm|change_values_time_prime|changes_big|changes_norm
    {

        if ($class == change::class) {
            $chg = new change($this->env->usr_system);
        } elseif ($class == changes_norm::class) {
            $chg = new changes_norm($this->env->usr_system);
        } elseif ($class == changes_big::class) {
            $chg = new changes_big($this->env->usr_system);
        } elseif ($class == change_values_prime::class) {
            $chg = new change_values_prime($this->env->usr_system);
        } elseif ($class == change_values_norm::class) {
            $chg = new change_values_norm($this->env->usr_system);
        } elseif ($class == change_values_big::class) {
            $chg = new change_values_big($this->env->usr_system);
        } elseif ($class == change_values_time_prime::class) {
            $chg = new change_values_time_prime($this->env->usr_system);
        } elseif ($class == change_values_time_norm::class) {
            $chg = new change_values_time_norm($this->env->usr_system);
        } elseif ($class == change_values_time_big::class) {
            $chg = new change_values_time_big($this->env->usr_system);
        } elseif ($class == change_values_text_prime::class) {
            $chg = new change_values_text_prime($this->env->usr_system);
        } elseif ($class == change_values_text_norm::class) {
            $chg = new change_values_text_norm($this->env->usr_system);
        } elseif ($class == change_values_text_big::class) {
            $chg = new change_values_text_big($this->env->usr_system);
        } elseif ($class == change_values_geo_prime::class) {
            $chg = new change_values_geo_prime($this->env->usr_system);
        } elseif ($class == change_values_geo_norm::class) {
            $chg = new change_values_geo_norm($this->env->usr_system);
        } elseif ($class == change_values_geo_big::class) {
            $chg = new change_values_geo_big($this->env->usr_system);
        } else {
            log_err('change log class ' . $class . ' not expected');
            $chg = new change($this->env->usr_system);
        }
        return $chg;
    }

    private function log_class_to_value_class(string $class): string
    {
        return match ($class) {
            change::class,
            changes_norm::class,
            changes_big::class
            => word::class,
            change_values_prime::class,
            change_values_big::class,
            change_values_norm::class
            => value::class,
            change_values_time_prime::class,
            change_values_time_big::class,
            change_values_time_norm::class
            => value_time::class,
            change_values_text_prime::class,
            change_values_text_norm::class,
            change_values_text_big::class
            => value_text::class,
            change_values_geo_prime::class,
            change_values_geo_norm::class,
            change_values_geo_big::class
            => value_geo::class,
            change_link::class => triple::class,
        };
    }

    private function log_class_to_value_field(string $class): string
    {
        return match ($class) {
            change::class,
            changes_norm::class,
            changes_big::class
            => word_fields::FLD_NAME,
            change_values_prime::class,
            change_values_big::class,
            change_values_norm::class
            => value_fields::FLD_VALUE,
            change_values_time_prime::class,
            change_values_time_big::class,
            change_values_time_norm::class
            => value_time::FLD_VALUE,
            change_values_text_prime::class,
            change_values_text_norm::class,
            change_values_text_big::class
            => value_text::FLD_VALUE,
            change_values_geo_prime::class,
            change_values_geo_norm::class,
            change_values_geo_big::class
            => value_geo::FLD_VALUE,
            change_link::class => triple::class,
        };
    }

    private function log_class_to_value(string $class): string|float|Datetime
    {
        return match ($class) {
            change::class,
            changes_norm::class,
            changes_big::class
            => word_names::MATH,
            change_values_prime::class,
            change_values_big::class,
            change_values_norm::class
            => values::PI_SHORT,
            change_values_time_prime::class,
            change_values_time_big::class,
            change_values_time_norm::class
            => (new DateTime(values::TIME)),
            change_values_text_prime::class,
            change_values_text_norm::class,
            change_values_text_big::class
            => values::TEXT,
            change_values_geo_prime::class,
            change_values_geo_norm::class,
            change_values_geo_big::class
            => values::GEO,
            change_link::class => triple::class,
        };
    }

    /**
     * @return change_values_norm an insert change log entry of a value with some dummy values and a standard group id
     */
    function log_value(): change_values_norm
    {
        $t_grp = new test_groups($this->env);
        $chg = new change_values_norm($this->env->usr1);
        $chg->set_time_str(test_const::DUMMY_DATETIME);
        $chg->set_action(change_actions::ADD);
        $chg->set_table(change_tables::VALUE);
        $chg->set_field(change_fields::FLD_NUMERIC_VALUE);
        $chg->group_id = $t_grp->group()->id();
        $chg->new_value = values::PI_SHORT;
        $chg->row_id = 1;
        return $chg;
    }

    /**
     * @return change_values_prime a change log entry of a value with some dummy values and a prime group id
     */
    function log_value_prime(): change_values_prime
    {
        $chg = new change_values_prime($this->env->usr1);
        $chg->set_time_str(test_const::DUMMY_DATETIME);
        $chg->set_action(change_actions::ADD);
        $chg->set_table(change_tables::WORD);
        $chg->set_field(change_fields::FLD_WORD_NAME);
        $chg->new_value = values::PI_SHORT;
        $chg->row_id = 1;
        return $chg;
    }

    /**
     * @return change_values_big a change log entry of a value with some dummy values and a big group id
     */
    function log_value_big(): change_values_big
    {
        $chg = new change_values_big($this->env->usr1);
        $chg->set_time_str(test_const::DUMMY_DATETIME);
        $chg->set_action(change_actions::ADD);
        $chg->set_table(change_tables::WORD);
        $chg->set_field(change_fields::FLD_WORD_NAME);
        $chg->new_value = values::PI_SHORT;
        $chg->row_id = 1;
        return $chg;
    }

    /**
     * @return change_values_norm an update change log entry of a value
     */
    function log_value_update(): change_values_norm
    {
        $chg = $this->log_value();
        $chg->old_value = values::SAMPLE_INT;
        return $chg;
    }

    /**
     * @return change_values_norm a delete change log entry of a value
     */
    function log_value_delete(): change_values_norm
    {
        $chg = $this->log_value_update();
        $chg->new_value = null;
        return $chg;
    }

    /**
     * @return change_link a change log entry of a link change
     */
    function log_link(): change_link
    {
        $chg = new change_link($this->env->usr1);
        $chg->set_time_str(test_const::DUMMY_DATETIME);
        $chg->set_action(change_actions::ADD);
        $chg->set_table(change_tables::TRIPLE);
        $chg->new_from_id = word_names::CONST_ID;
        $chg->new_link_id = verbs::PART_ID;
        $chg->new_to_id = word_names::MATH_ID;
        $chg->row_id = 1;
        return $chg;
    }

    function log_list_named_ui(): change_log_list_ui
    {
        $tl = new test_lib();
        return $tl->list_to_ui($this->log_list_named(), [api_types::INCL_PHRASES]);
    }

    /**
     * @return change_log_list the changes of one word (name, phrase type, description, impact,
     *                         usage and protection type), used e.g. to show the change log table
     *                         pure with a deterministic row per change field type
     */
    function log_list_word_changes(): change_log_list
    {
        $log_lst = new change_log_list();
        $log_lst->add($this->log_word_add());
        $log_lst->add($this->log_word_update_type());
        $log_lst->add($this->log_word_add_description());
        $log_lst->add($this->log_word_add_impact());
        $log_lst->add($this->log_word_add_usage());
        $log_lst->add($this->log_word_update_protection());
        $log_lst->add($this->log_word_add_user_id());
        $log_lst->add($this->log_word_add_view());
        $log_lst->add($this->log_word_add_view_id());
        $log_lst->add($this->log_word_remove_view());
        return $log_lst;
    }

    /**
     * @return change_log_list a list of change log entries with some dummy values
     *
     * TODO add at least one sample for rename and delete
     * TODO add at least one sample for verb, triple, value, formula, source, ref, view and component
     */
    function log_list_short(): change_log_list
    {
        $log_lst = new change_log_list();
        $log_lst->add($this->log_word_add());
        $log_lst->add($this->log_verb_add());
        $log_lst->add($this->log_triple_add());
        return $log_lst;
    }

    /**
     * @return change_log_list a list of change log entries with some dummy values
     *
     * TODO add at least one sample for rename and delete
     * TODO add at least one sample for verb, triple, value, formula, source, ref, view and component
     */
    function log_list_named(): change_log_list
    {
        $log_lst = new change_log_list();
        $log_lst->add($this->log_word_add());
        $log_lst->add($this->log_word_update());
        $log_lst->add($this->log_word_update_type());
        $log_lst->add($this->log_word_delete());
        $log_lst->add($this->log_verb_add());
        $log_lst->add($this->log_triple_add());
        $log_lst->add($this->log_source_add());
        $log_lst->add($this->log_ref_add());
        $log_lst->add($this->log_formula_add());
        $log_lst->add($this->log_view_add());
        $log_lst->add($this->log_component_add());
        return $log_lst;
    }

    /**
     * @return change_log_list two changes of one word within the same second where the
     *         alphabetical order of the what texts equals the write order, so that only
     *         the change id can prove that the sort shows the newest change first
     */
    function log_list_same_second(): change_log_list
    {
        $log_lst = new change_log_list();
        // written first: the lower change id and the alphabetically first what text
        $log_lst->add($this->log_word_add());
        $log_lst->add($this->log_word_add_description());
        return $log_lst;
    }

    /**
     * @return change_log_list two changes of one word one second apart where the newer
     *         change has the lower change id, to test that the change time stays the
     *         first sort key before the change id
     */
    function log_list_second_apart(): change_log_list
    {
        $log_lst = new change_log_list();
        $newer = $this->log_word_add();
        $newer->set_time_str(test_const::DUMMY_DATETIME_LATER);
        $log_lst->add($newer);
        $log_lst->add($this->log_word_add_description());
        return $log_lst;
    }

    /**
     * @return change_log_list_ui the same second changes like an api message from before
     *         the change id was added (all id 0), to test the what text fallback sort
     */
    function log_list_same_second_no_id_ui(): change_log_list_ui
    {
        $json = json_decode($this->log_list_same_second()->api_json(), true);
        foreach (array_keys($json) as $i) {
            unset($json[$i][json_fields::ID]);
        }
        return new change_log_list_ui(json_encode($json));
    }

}