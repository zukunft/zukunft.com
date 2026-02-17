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
include_once paths::SHARED_CONST . 'values.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED_CONST . 'words.php';
include_once paths::SHARED_ENUM . 'change_actions.php';
include_once paths::SHARED_ENUM . 'change_fields.php';
include_once paths::SHARED_ENUM . 'change_tables.php';
include_once paths::SHARED . 'library.php';
include_once paths::SHARED_TYPES . 'api_types.php';
include_once paths::SHARED_TYPES . 'phrase_types.php';
include_once paths::SHARED_TYPES . 'verbs.php';
include_once html_paths::LOG . 'change_log_list.php';
include_once test_paths::CREATE . 'test_const.php';
include_once test_paths::UTILS . 'test_cleanup.php';
include_once test_paths::UTILS . 'test_lib.php';

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
use Zukunft\ZukunftCom\main\php\cfg\value\value;
use Zukunft\ZukunftCom\main\php\cfg\value\value_db;
use Zukunft\ZukunftCom\main\php\cfg\value\value_geo;
use Zukunft\ZukunftCom\main\php\cfg\value\value_text;
use Zukunft\ZukunftCom\main\php\cfg\value\value_time;
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\cfg\word\word_db;
use Zukunft\ZukunftCom\main\php\shared\const\components;
use Zukunft\ZukunftCom\main\php\shared\const\formulas;
use Zukunft\ZukunftCom\main\php\shared\const\refs;
use Zukunft\ZukunftCom\main\php\shared\const\sources;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\main\php\shared\const\values;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\enum\change_actions;
use Zukunft\ZukunftCom\main\php\shared\enum\change_fields;
use Zukunft\ZukunftCom\main\php\shared\enum\change_tables;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\main\php\shared\types\phrase_types;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;
use Zukunft\ZukunftCom\main\php\web\log\change_log_list as change_log_list_ui;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;
use Zukunft\ZukunftCom\test\php\utils\test_lib;
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
        global $usr_sys;
        $chg = new change($usr_sys);
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
        $chg->new_value = words::MATH;
        $chg->row_id = words::MATH_ID;
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
        $chg->old_value = words::TEST_RENAMED;
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
        $chg->new_value = triples::MATH_CONST;
        $chg->row_id = triples::MATH_CONST_ID;
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
        global $usr_sys;
        $chg = new change_values_prime($usr_sys);
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
        $chg->new_value = formulas::SCALE_TO_SEC;
        $chg->row_id = formulas::SCALE_TO_SEC_ID;
        return $chg;
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
        global $usr_sys;

        $chg = new changes_norm($usr_sys);
        $chg->set_time_str(test_const::DUMMY_DATETIME);
        $chg->set_action(change_actions::ADD);
        $chg->set_table(change_tables::WORD);
        $chg->set_field(change_fields::FLD_WORD_NAME);
        $chg->new_value = words::MATH;
        $chg->row_id = 1;
        return $chg;
    }

    /**
     * @return changes_big a change log entry of a group where the id is a text field and not an id
     */
    function log_big(): changes_big
    {
        global $usr_sys;

        $chg = new changes_big($usr_sys);
        $chg->set_time_str(test_const::DUMMY_DATETIME);
        $chg->set_action(change_actions::ADD);
        $chg->set_table(change_tables::WORD);
        $chg->set_field(change_fields::FLD_WORD_NAME);
        $chg->new_value = words::MATH;
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
        global $usr_sys;

        if ($class == change::class) {
            $chg = new change($usr_sys);
        } elseif ($class == changes_norm::class) {
            $chg = new changes_norm($usr_sys);
        } elseif ($class == changes_big::class) {
            $chg = new changes_big($usr_sys);
        } elseif ($class == change_values_prime::class) {
            $chg = new change_values_prime($usr_sys);
        } elseif ($class == change_values_norm::class) {
            $chg = new change_values_norm($usr_sys);
        } elseif ($class == change_values_big::class) {
            $chg = new change_values_big($usr_sys);
        } elseif ($class == change_values_time_prime::class) {
            $chg = new change_values_time_prime($usr_sys);
        } elseif ($class == change_values_time_norm::class) {
            $chg = new change_values_time_norm($usr_sys);
        } elseif ($class == change_values_time_big::class) {
            $chg = new change_values_time_big($usr_sys);
        } elseif ($class == change_values_text_prime::class) {
            $chg = new change_values_text_prime($usr_sys);
        } elseif ($class == change_values_text_norm::class) {
            $chg = new change_values_text_norm($usr_sys);
        } elseif ($class == change_values_text_big::class) {
            $chg = new change_values_text_big($usr_sys);
        } elseif ($class == change_values_geo_prime::class) {
            $chg = new change_values_geo_prime($usr_sys);
        } elseif ($class == change_values_geo_norm::class) {
            $chg = new change_values_geo_norm($usr_sys);
        } elseif ($class == change_values_geo_big::class) {
            $chg = new change_values_geo_big($usr_sys);
        } else {
            log_err('change log class ' . $class . ' not expected');
            $chg = new change($usr_sys);
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
            => word_db::FLD_NAME,
            change_values_prime::class,
            change_values_big::class,
            change_values_norm::class
            => value_db::FLD_VALUE,
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
            => words::MATH,
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
        global $usr_sys;

        $t_grp = new test_groups($this->env);
        $chg = new change_values_norm($usr_sys);
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
        global $usr_sys;

        $chg = new change_values_prime($usr_sys);
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
        global $usr_sys;

        $chg = new change_values_big($usr_sys);
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
        global $usr_sys;

        $chg = new change_link($usr_sys);
        $chg->set_time_str(test_const::DUMMY_DATETIME);
        $chg->set_action(change_actions::ADD);
        $chg->set_table(change_tables::TRIPLE);
        $chg->new_from_id = words::CONST_ID;
        $chg->new_link_id = verbs::PART_ID;
        $chg->new_to_id = words::MATH_ID;
        $chg->row_id = 1;
        return $chg;
    }

    function log_list_named_ui(): change_log_list_ui
    {
        $tl = new test_lib();
        return $tl->list_to_ui($this->log_list_named(), [api_types::INCL_PHRASES]);
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

}