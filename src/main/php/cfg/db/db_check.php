<?php

/*

  db_check.php - test if the database exists and start the creation or upgrade process
  ------------
  

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

namespace cfg\db;

use cfg\component\component;
use cfg\config;
use cfg\formula_list;
use cfg\group\group;
use cfg\phrase;
use cfg\result\result_two;
use cfg\sandbox;
use cfg\sandbox_named;
use cfg\sys_log_function;
use cfg\system_time_type;
use cfg\user;
use cfg\user\user_profile;
use cfg\user_message;
use cfg\user_profile_list;
use cfg\value\value;
use shared\library;

class db_check
{

    const TBL_WORD = 'word';
    const TBL_TRIPLE = 'triple';

    /**
     * read the version number from the database and compare it with the backend version
     * if the database has a lower version than the backend program start the upgrade process
     * @param sql_db $db_con the database connection object to the database that should be tested
     * @return user_message the message that should be shown to the user immediately if not empty
     */
    function db_check(sql_db $db_con): user_message
    {

        $usr_msg = new user_message(); // the message that should be shown to the user immediately
        $do_consistency_check = false;
        $lib = new library();

        // check if essential config table exists and if not setup the database
        // TODO remove rewrite before moved to PROD
        $main_tbl_name = $lib->class_to_name(config::class);
        if (!$db_con->has_table($main_tbl_name)) {
            $usr_msg = $db_con->setup_db();
            if ($usr_msg->is_ok()) {
                $db_con->db_fill_code_links();
                $db_con->db_check_missing_owner();
                $cfg = new config();
                $cfg->set(config::LAST_CONSISTENCY_CHECK, gmdate(DATE_ATOM), $db_con);
            }
        }

        $cfg = new config();
        $cfg->check(config::SITE_NAME, POD_NAME, $db_con);

        // get the db version and start the upgrade process if needed
        $db_version = $cfg->get_db(config::VERSION_DB, $db_con);
        if ($db_version == '') {
            $cfg->set(config::VERSION_DB, FIRST_VERSION, $db_con);
        } elseif ($db_version != PRG_VERSION) {
            $do_consistency_check = true;
            if (prg_version_is_newer($db_version)) {
                log_warning('The zukunft.com backend is older than the database used. This may cause damage on the database. Please upgrade the backend program', 'db_check');
            } else {
                $diff_txt = match ($db_version) {
                    NEXT_VERSION => $this->db_upgrade_0_0_4($db_con),
                    FIRST_VERSION => $this->db_upgrade_0_0_3($db_con),
                };
                $usr_msg->add_message($diff_txt);
            }
        } else {
            $last_consistency_check = $cfg->get_db(config::LAST_CONSISTENCY_CHECK, $db_con);
            // run a database consistency check once every 24h if the database is the least busy
            $last_check = strtotime($last_consistency_check);
            $check_limit = strtotime("now -1 day");
            if ($last_check < $check_limit) {
                // TODO open a simple db connection just to write the log entry
                /*
                log_info('Last database consistency check has been at ' . date('Y-m-d H:i:s', $last_check)
                    . ' which is more than one day ago at ' . date('Y-m-d H:i:s', $check_limit)
                    . ' so start the database consistency check');
                */
                $do_consistency_check = true;
            } else {
                log_debug('Last database consistency check has been at ' . date('Y-m-d H:i:s', $last_check)
                    . ' which is less than one day ago at ' . date('Y-m-d H:i:s', $check_limit));
            }
        }

        // run a database consistency check now and remember the time
        if ($do_consistency_check) {
            $db_con->db_fill_code_links();
            $db_con->db_check_missing_owner();
            $cfg->set(config::LAST_CONSISTENCY_CHECK, gmdate(DATE_ATOM), $db_con);
        }

        return $usr_msg;

    }

// upgrade the database from any version prior of 0.0.3
// the version 0.0.3 is the first version, which has a build in upgrade process
    function db_upgrade_0_0_3(sql_db $db_con): string
    {
        global $sys_times;

        $cfg = new config();
        $lib = new library();
        $sys_times->switch(system_time_type::DB_UPGRADE);

        // prepare to remove the time word from the values
        $msg = $this->db_move_time_phrase_to_group();
        if ($msg->is_ok()) {
            //
            $msg->add($db_con->del_field($lib->class_to_name(value::class), 'time_word_id'));
            $msg->add($db_con->del_field('result', 'time_word_id'));
        }

        $result = ''; // if empty everything has been fine; if not the message that should be shown to the user
        $process_name = 'db_upgrade_0_0_3'; // the info text that is written to the database execution log
        // TODO check if change has been successful
        // rename word_link to triple
        $result .= $db_con->change_table_name('word_link', 'triple');
        $result .= $db_con->change_column_name('triple', 'word_link_id', 'triple_id');
        $result .= $db_con->change_column_name('triple', 'word_link_condition_id', 'triple_condition_id');
        $result .= $db_con->change_column_name('triple', 'word_link_condition_type_id', 'triple_condition_type_id');
        $result .= $db_con->change_table_name('user_' . 'word_link', 'user_' . 'triple');
        $result .= $db_con->change_column_name('user_' . 'triple', 'word_link_id', 'user_' . 'triple_id');
        $result .= $db_con->change_table_name('view_word_link', 'view_term_link');
        $result .= $db_con->change_table_name('formula_value', 'result');
        $result .= $db_con->change_column_name('result', 'formula_group_id', 'group_id');
        $result .= $db_con->change_column_name('result', 'formula_value', 'result');
        $result .= $db_con->change_table_name('view_component', 'component');
        $result .= $db_con->change_column_name('component', 'view_component_id', component::FLD_ID);
        $result .= $db_con->change_column_name('component', 'view_component_name', 'component_name');
        $result .= $db_con->change_column_name('component', 'linked_view_component_id', 'linked_component_id');
        $result .= $db_con->change_column_name('component', 'view_component_link_type_id', 'component_link_type_id');
        $result .= $db_con->change_table_name('user_' . 'view_components', 'user_' . 'component');
        $result .= $db_con->change_column_name('user_' . 'component', 'view_component_id', component::FLD_ID);
        $result .= $db_con->change_column_name('user_' . 'component', 'view_component_name', 'component_name');
        $result .= $db_con->change_table_name('view_component_link', 'component_link');
        $result .= $db_con->change_column_name('component_link', 'view_component_id', component::FLD_ID);
        $result .= $db_con->change_table_name('user_' . 'view_component_links', 'user_' . 'component_link');
        $result .= $db_con->change_column_name('user_' . 'component_link', 'view_component_id', component::FLD_ID);
        $result .= $db_con->change_table_name('view_component_link_type', 'component_link_type');
        $result .= $db_con->change_column_name('component_link_type', 'view_component_link_id', 'component_link_id');
        $result .= $db_con->change_column_name('component_link_type', 'view_component_id', component::FLD_ID);
        $result .= $db_con->change_table_name('view_component_position_type', 'position_type');
        $result .= $db_con->change_column_name('position_type', 'view_component_position_type_id', 'position_type_id');
        //
        $result .= $db_con->change_table_name('languages_form', 'language_form');
        $result .= $db_con->add_column('user_profile', 'right_level', 'smallint');
        $result .= $db_con->add_column('user_' . 'word', 'share_type_id', 'smallint');
        $result .= $db_con->add_column('user_' . 'word', 'protect_id', 'smallint');
        $result .= $db_con->add_column('user_' . 'word', 'values', 'bigint');
        $result .= $db_con->add_column('word', 'protect_id', 'smallint');
        $result .= $db_con->add_column('user_' . 'triple', 'values', 'bigint');
        $result .= $db_con->add_column('user_' . 'triple', 'share_type_id', 'smallint');
        $result .= $db_con->add_column('user_' . 'triple', 'protect_id', 'smallint');
        $result .= $db_con->add_column('triple', 'protect_id', 'smallint');
        $result .= $db_con->add_column('triple', 'word_type_id', 'bigint');
        $result .= $db_con->add_column('user_' . 'triple', 'word_type_id', 'bigint');
        $result .= $db_con->add_column('formula', 'share_type_id', 'smallint');
        $result .= $db_con->add_column('formula', 'protect_id', 'smallint');
        $result .= $db_con->add_column('user_' . 'formula', 'protect_id', 'smallint');
        $result .= $db_con->add_column('formula', 'usage', 'bigint');
        $result .= $db_con->add_column('user_' . 'formula', 'usage', 'bigint');
        $result .= $db_con->add_column('formula_link', 'order_nbr', 'smallint');
        $result .= $db_con->add_column('formula_link', 'share_type_id', 'smallint');
        $result .= $db_con->add_column('formula_link', 'protect_id', 'smallint');
        $result .= $db_con->add_column('user_' . 'formula_link', 'share_type_id', 'smallint');
        $result .= $db_con->add_column('user_' . 'formula_link', 'protect_id', 'smallint');
        $result .= $db_con->add_column('view', 'share_type_id', 'smallint');
        $result .= $db_con->add_column('view', 'protect_id', 'smallint');
        $result .= $db_con->add_column('user_' . 'view', 'share_type_id', 'smallint');
        $result .= $db_con->add_column('user_' . 'view', 'protect_id', 'smallint');
        $result .= $db_con->add_column('component', 'share_type_id', 'smallint');
        $result .= $db_con->add_column('component', 'protect_id', 'smallint');
        $result .= $db_con->add_column('user_' . 'component', 'share_type_id', 'smallint');
        $result .= $db_con->add_column('user_' . 'component', 'protect_id', 'smallint');
        $result .= $db_con->add_column('component_link', 'share_type_id', 'smallint');
        $result .= $db_con->add_column('component_link', 'protect_id', 'smallint');
        $result .= $db_con->add_column('user_' . 'component_link', 'share_type_id', 'smallint');
        $result .= $db_con->add_column('user_' . 'component_link', 'protect_id', 'smallint');
        $result .= $db_con->add_column('values_time_series', 'share_type_id', 'smallint');
        $result .= $db_con->add_column('values_time_series', 'protect_id', 'smallint');
        $result .= $db_con->add_column('user_' . 'values_time_series', 'share_type_id', 'smallint');
        $result .= $db_con->add_column('user_' . 'values_time_series', 'protect_id', 'smallint');
        $result .= $db_con->add_column('position_type', 'code_id', 'varchar(50)');
        $result .= $db_con->add_column('source_type', 'description', 'text');
        $result .= $db_con->add_column('ref', user::FLD_ID, 'bigint');
        $result .= $db_con->add_column('ref', 'source_id', 'bigint');
        $result .= $db_con->add_column('ref', 'url', 'text');
        $result .= $db_con->add_column('ref', 'description', 'text');
        $result .= $db_con->add_column('user', 'description', 'text');
        $result .= $db_con->add_column('change_action', 'description', 'text');
        $result .= $db_con->add_column('language_form', 'description', 'text');
        $result .= $db_con->change_column_name('language_form', 'lanuages_id', 'language_id');
        $result .= $db_con->change_column_name('language_form', 'lanuages_form_id', 'language_form_id');
        $result .= $db_con->change_column_name('language_form', 'lanuages_form_name', 'language_form_name');
        $result .= $db_con->change_column_name('user_' . $lib->class_to_name(value::class), 'user_value', 'word_value');
        $result .= $db_con->change_column_name('values_time_series', 'value_time_serie_id', 'value_time_series_id');
        $result .= $db_con->change_column_name('ip_range', 'isactive', 'is_active');
        $result .= $db_con->change_column_name('user', 'isactive', 'is_active');
        $result .= $db_con->change_column_name('user', 'email_alternativ', 'email_alternative');
        $result .= $db_con->change_column_name('element_type', 'formula_element_type_name', 'type_name');
        $result .= $db_con->change_column_name('view', 'comment', sandbox_named::FLD_DESCRIPTION);
        $result .= $db_con->change_column_name('user_' . 'view', 'comment', sandbox_named::FLD_DESCRIPTION);
        $result .= $db_con->change_column_name('component', 'comment', sandbox_named::FLD_DESCRIPTION);
        $result .= $db_con->change_column_name('user_' . 'component', 'comment', sandbox_named::FLD_DESCRIPTION);
        $result .= $db_con->change_column_name('component_type', 'component_type_name', 'type_name');
        $result .= $db_con->change_column_name('formula_type', 'name', 'type_name');
        $result .= $db_con->change_column_name('ref_type', 'ref_type_name', 'type_name');
        $result .= $db_con->change_column_name('ref_type', 'source_type_name', 'type_name');
        $result .= $db_con->change_column_name('source', 'comment', sandbox_named::FLD_DESCRIPTION);
        $result .= $db_con->change_column_name('user_' . 'source', 'comment', sandbox_named::FLD_DESCRIPTION);
        $result .= $db_con->change_column_name('share_type', 'share_type_name', 'type_name');
        $result .= $db_con->change_column_name('protection_type', 'protection_type_name', 'type_name');
        $result .= $db_con->change_column_name('user_profile', 'user_profile_name', 'type_name');
        $result .= $db_con->change_column_name('user_profile', 'commen', sandbox_named::FLD_DESCRIPTION);
        $result .= $db_con->change_column_name('sys_log_status', 'comment', sandbox_named::FLD_DESCRIPTION);
        $result .= $db_con->change_column_name('sys_log_status', 'sys_log_status_name', 'type_name');
        $result .= $db_con->change_column_name('job_type', 'calc_and_cleanup_task_type_name', 'type_name');
        $result .= $db_con->change_column_name('user_profile', 'comment', sandbox_named::FLD_DESCRIPTION);
        $result .= $db_con->change_column_name('formula', 'protection_type_id', 'protect_id');
        $result .= $db_con->change_column_name($lib->class_to_name(value::class), 'protection_type_id', 'protect_id');
        $result .= $db_con->change_column_name('user_' . $lib->class_to_name(value::class), 'protection_type_id', 'protect_id');
        $result .= $db_con->change_column_name('values_time_series', 'protection_type_id', 'protect_id');
        $result .= $db_con->change_column_name('user_' . 'values_time_series', 'protection_type_id', 'protect_id');
        $result .= $db_con->change_column_name('result', 'source_time_word_id', 'source_time_id');
        if (!$db_con->has_column($db_con->get_table_name('triple'), 'name_generated')) {
            $result .= $db_con->change_column_name('triple', 'name', 'triple_name');
            $result .= $db_con->change_column_name('triple', 'description', 'name_given');
            $result .= $db_con->add_column('triple', 'name_generated', 'text');
            $result .= $db_con->add_column('triple', 'description', 'text');
        }
        $result .= $db_con->add_column('triple', 'values', 'bigint');
        if (!$db_con->has_column($db_con->get_table_name('user_' . 'triple'), 'name_generated')) {
            $result .= $db_con->change_column_name('user_' . 'triple', 'name', 'triple_name');
            $result .= $db_con->change_column_name('user_' . 'triple', 'description', 'name_given');
            $result .= $db_con->add_column('user_' . 'triple', 'name_generated', 'text');
            $result .= $db_con->add_column('user_' . 'triple', 'description', 'text');
        }
        $result .= $db_con->add_column('user_' . 'triple', 'values', 'bigint');
        $result .= $db_con->add_column('user_' . 'word', 'values', 'bigint');
        $result .= $db_con->add_column('view_term_link', user::FLD_ID, 'bigint');
        $result .= $db_con->add_column('view_term_link', 'description', 'text');
        $result .= $db_con->add_column('view_term_link', 'excluded', 'smallint');
        $result .= $db_con->add_column('view_term_link', 'share_type_id', 'smallint');
        $result .= $db_con->add_column('view_term_link', 'protect_id', 'smallint');
        $result .= $db_con->add_column('component', 'code_id', 'varchar(100)');
        $result .= $db_con->add_column('component', 'ui_msg_code_id', 'varchar(100)');
        $result .= $db_con->remove_prefix('user_profile', 'code_id', 'usr_role_');
        $result .= $db_con->remove_prefix('sys_log_status', 'code_id', 'log_status_');
        $result .= $db_con->remove_prefix('job_type', 'code_id', 'job_');
        $result .= $db_con->remove_prefix('view', 'code_id', 'dsp_');
        $result .= $db_con->remove_prefix('component_type', 'code_id', 'dsp_comp_type_');
        $result .= $db_con->remove_prefix('verb', 'code_id', 'vrb_');
        $result .= $db_con->change_code_id('verb', 'vrb_contains', 'is_part_of');
        $result .= $db_con->column_allow_null('word', 'plural');
        $result .= $db_con->column_allow_null('phrase_type', 'word_symbol');
        $result .= $db_con->column_allow_null('change_table', 'description');
        $result .= $db_con->column_allow_null('change_field', 'code_id');
        $result .= $db_con->column_allow_null('view', sandbox_named::FLD_DESCRIPTION);
        $result .= $db_con->column_allow_null('component_type', 'description');
        $result .= $db_con->column_allow_null($lib->class_to_name(value::class), sandbox::FLD_EXCLUDED);
        $result .= $db_con->column_allow_null($lib->class_to_name(value::class), 'protect_id');
        $result .= $db_con->column_allow_null('formula_link', 'link_type_id');
        $result .= $db_con->column_allow_null('user_' . $lib->class_to_name(value::class), 'protect_id');
        $result .= $db_con->column_allow_null('values_time_series', 'protect_id');
        $result .= $db_con->column_allow_null('user_' . 'source', 'source_name');
        $result .= $db_con->column_allow_null('user_' . 'source', 'url');
        $result .= $db_con->column_allow_null(sys_log_function::class, 'sys_log_function_name');
        $result .= $db_con->column_allow_null('job', 'start_time');
        $result .= $db_con->column_allow_null('job', 'end_time');
        $result .= $db_con->column_allow_null('job', 'row_id');
        $result .= $db_con->column_force_not_null('user_' . 'source', user::FLD_ID);
        $result .= $db_con->change_column_name($lib->class_to_name(value::class), 'word_value', value::FLD_VALUE);
        $result .= $db_con->change_column_name('user_' . $lib->class_to_name(value::class), 'word_value', value::FLD_VALUE);
        $result .= $db_con->change_table_name('word_types', 'phrase_type');
        $result .= $db_con->change_column_name('phrase_type', 'word_type_id', phrase::FLD_TYPE);
        $result .= $db_con->change_column_name('word', 'word_type_id', phrase::FLD_TYPE);
        $result .= $db_con->change_column_name('triple', 'word_type_id', phrase::FLD_TYPE);
        $result .= $db_con->change_column_name('user_' . 'word', 'word_type_id', phrase::FLD_TYPE);
        $result .= $db_con->change_column_name('user_' . 'triple', 'word_type_id', phrase::FLD_TYPE);
        $result .= $db_con->change_column_name('formula_link_type', 'word_type_id', phrase::FLD_TYPE);

        $result .= $db_con->change_table_name('results', result_two::class);
        $result .= $db_con->change_table_name('user_phrase_groups', 'user_' . group::class);
        $result .= $db_con->change_column_name($lib->class_to_name(value::class), 'phrase_group_id', group::FLD_ID);

        // TODO set default profile_id in users to 1
        if ($db_con->db_type == sql_db::MYSQL) {
            $sql = 'UPDATE' . ' `users` SET `user_profile_id` = 1 WHERE `user_profile_id`= NULL';
            $result .= $db_con->exe_try('Setting missing user profiles', $sql);
            $sql = 'UPDATE' . ' `users` SET `dt` = CURRENT_TIMESTAMP WHERE `users`.`dt` = 0';
            $result .= $db_con->exe_try('Filling missing timestamps for users', $sql);
            $sql = 'UPDATE' . ' `users` SET `last_logoff` = CURRENT_TIMESTAMP WHERE `users`.`last_logoff` = 0';
            $result .= $db_con->exe_try('Filling missing logoff timestamps for users', $sql);
            $sql = 'UPDATE' . ' `users` SET `activation_timeout` = CURRENT_TIMESTAMP WHERE `users`.`activation_timeout` = 0';
            $result .= $db_con->exe_try('Filling missing activation timestamps for users', $sql);

            $sql = file_get_contents(PATH_BASE_CONFIG_FILES . 'db/upgrade/v0.0.3/upgrade_mysql.sql');
            $result .= $db_con->exe_try('Finally add the new views', $sql);
        }
        if ($db_con->db_type == sql_db::POSTGRES) {
            $sql = file_get_contents(PATH_BASE_CONFIG_FILES . 'db/upgrade/v0.0.3/upgrade_postgres.sql');
            //src/main/resources/db/upgrade/v0.0.3/upgrade_postgres.sql
            //$result .= $db_con->exe_try('Finally add the new views', $sql);
        }
        $result .= $db_con->add_foreign_key('users_fk_2', 'user', 'user_profile_id', 'user_profile', 'user_profile_id');
        // TODO change prime key for postgres user_sources, user_values, user_view, user_components and user_component_links

        if ($db_con->db_type == sql_db::MYSQL) {

            global $usr_pro_cac;
            $usr_pro_cac = new user_profile_list();
            $usr_pro_cac->load($db_con);

            // add missing system users if needed
            $sys_usr = new user();
            if (!$sys_usr->has_any_user_this_profile(user_profile::SYSTEM)) {
                $sys_usr->load_by_name(user::SYSTEM_NAME);
                $sys_usr->set_profile(user_profile::SYSTEM);
                $sys_usr->save($db_con);
            }
            // add missing system users if needed
            $usr_admin = new user();
            if (!$usr_admin->has_any_user_this_profile(user_profile::ADMIN)) {
                $usr_admin->load_by_name(user::SYSTEM_ADMIN_NAME);
                $usr_admin->set_profile(user_profile::ADMIN);
                $usr_admin->save($db_con);
            }

            // add missing system test users if needed
            $test_usr = new user();
            if (!$test_usr->has_any_user_this_profile(user_profile::TEST)) {
                $test_usr->load_by_name(user::SYSTEM_TEST_NAME);
                $test_usr->set_profile(user_profile::TEST);
                $test_usr->save($db_con);
                $test_usr2 = new user();
                $test_usr2->load_by_name(user::SYSTEM_TEST_PARTNER_NAME);
                $test_usr2->set_profile(user_profile::TEST);
                $test_usr2->save($db_con);
            }

            $test_usr_normal = new user();
            if (!$test_usr_normal->has_any_user_this_profile(user_profile::NORMAL)) {
                $test_usr_normal = new user();
                $test_usr_normal->load_by_name(user::SYSTEM_TEST_NORMAL_NAME);
                $test_usr_normal->set_profile(user_profile::NORMAL);
                $test_usr_normal->save($db_con);
            }
        }

        // prepare the high level upgrade
        $sys_usr = new user();
        $sys_usr->load_by_name(user::SYSTEM_NAME);

        // refresh the formula ref_text, because the coding has changed (use "{w" instead of "{t")
        $frm_lst = new formula_list($sys_usr);
        $frm_lst->db_ref_refresh($db_con);

        // Change code_id in verbs from contains to is_part_of

        // update the database version number in the config
        $cfg->set(config::VERSION_DB, PRG_VERSION, $db_con);


        // TODO create table user_value_time_series
        // check if the config save has been successful
        $db_version = $cfg->get_db(config::VERSION_DB, $db_con);
        if ($db_version != PRG_VERSION) {
            $result = 'Database upgrade to 0.0.3 has failed';
        }
        $sys_times->switch();

        return $result;
    }

// TODO finish
    function db_move_time_phrase_to_group(): user_message
    {
        $usr_msg = new user_message();
        // get all values where the time word is used
        $qp = new sql_par(value::class);

        // loop over values that needs to be adjusted
        // create the new group including the time
        // update the value
        return $usr_msg;
    }

    /**
     * upgrade the database from any version prior of 0.0.4
     */
    function db_upgrade_0_0_4($db_con): string
    {
        $cfg = new config();
        $result = ''; // if empty everything has been fine; if not the message that should be shown to the user
        $db_version = $cfg->get_db(config::VERSION_DB, $db_con);
        if ($db_version != PRG_VERSION) {
            $result = 'Database upgrade to 0.0.4 has failed';
        }

        return $result;
    }

}