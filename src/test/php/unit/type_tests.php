<?php

/*

    test/unit/language.php - unit testing of the language functions
    ----------------------


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

namespace Zukunft\ZukunftCom\test\php\unit;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_HELPER . 'type_lists.php';
include_once paths::SHARED_ENUM . 'sys_log_statuum.php';
include_once paths::SHARED_ENUM . 'user_statuum.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED_CONST_FIELDS . 'fields.php';

use Zukunft\ZukunftCom\main\php\cfg\component\component_link_type;
use Zukunft\ZukunftCom\main\php\cfg\component\view_style;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_type;
use Zukunft\ZukunftCom\main\php\cfg\helper\db_cache_status;
use Zukunft\ZukunftCom\main\php\cfg\helper\db_cache_type;
use Zukunft\ZukunftCom\main\php\cfg\helper\type_lists;
use Zukunft\ZukunftCom\main\php\cfg\helper\type_object;
use Zukunft\ZukunftCom\main\php\cfg\ref\ref_type;
use Zukunft\ZukunftCom\main\php\cfg\ref\source_type;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\protection_type;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\share_type;
use Zukunft\ZukunftCom\main\php\cfg\system\job_status;
use Zukunft\ZukunftCom\main\php\cfg\system\job_type;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase_type;
use Zukunft\ZukunftCom\main\php\cfg\system\sys_log_function;
use Zukunft\ZukunftCom\main\php\cfg\system\sys_log_level;
use Zukunft\ZukunftCom\main\php\cfg\system\sys_log_status;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_status_list;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\user\user_official_type;
use Zukunft\ZukunftCom\main\php\cfg\user\user_profile;
use Zukunft\ZukunftCom\main\php\cfg\user\user_status;
use Zukunft\ZukunftCom\main\php\cfg\user\user_type;
use Zukunft\ZukunftCom\main\php\cfg\verb\verb;
use Zukunft\ZukunftCom\main\php\cfg\view\view_link_type;
use Zukunft\ZukunftCom\main\php\cfg\view\view_relation_type;
use Zukunft\ZukunftCom\main\php\cfg\view\view_type;
use Zukunft\ZukunftCom\main\php\shared\enum\sys_log_statuum;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\types\phrase_types as phrase_type_shared;
use Zukunft\ZukunftCom\main\php\shared\enum\user_statuum;
use Zukunft\ZukunftCom\test\php\create\test_types;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;
use Zukunft\ZukunftCom\main\php\shared\const\fields\fields;

class type_tests
{

    function run(test_cleanup $t): void
    {

        // init
        $sc = new sql_creator();
        $db_con = new sql_db();
        $t_typ = new test_types($t);
        $t->name = 'type->';
        $t->resource_path = 'db/type/';

        // start the test section (ts)
        $ts = 'unit type ';
        $t->header($ts);

        // TODO job_types

        $t->subheader($ts . 'types json cache round trip');
        // the backend can load all types with one cached json instead of one select per list,
        // so the api json of the type lists must restore the complete type lists exactly
        $typ_lst_all = new type_lists();
        $typ_lst_all->load_dummy();
        $api_json = $typ_lst_all->api_json_array();
        $typ_lst_cached = new type_lists();
        $msg = new user_message(user::system());
        $test_name = 'the type lists are filled from the types api json';
        $t->assert_true($test_name, $typ_lst_cached->fill_from_api_json($api_json, $msg));
        // building and comparing the complete type list api json takes longer than a normal unit
        // function, so a calculation timeout is used to avoid a false timeout as the type list grows
        $test_name = 'the filled type lists recreate exactly the same types api json';
        $t->assert($test_name, json_encode($typ_lst_cached->api_json_array()), json_encode($api_json), $t::TIMEOUT_LIMIT_CALC);
        $test_name = 'a phrase type is selected by code id like after a database load';
        $t->assert($test_name,
            $typ_lst_cached->phr_typ->id(phrase_type_shared::PERCENT),
            $typ_lst_all->phr_typ->id(phrase_type_shared::PERCENT));

        $test_name = 'an api json without the verbs does not fill the type lists';
        $api_json_verbless = $api_json;
        unset($api_json_verbless[json_fields::LIST_VERBS]);
        $msg = new user_message(user::system());
        $t->assert_false($test_name, (new type_lists())->fill_from_api_json($api_json_verbless, $msg));
        $test_name = 'an empty api json does not fill the type lists';
        $msg = new user_message(user::system());
        $t->assert_false($test_name, (new type_lists())->fill_from_api_json([], $msg));

        // the code id links program code to the type,
        // so only a trusted source e.g. the db cached types json is allowed to set it
        $msg = new user_message(user::system());
        $typ = new type_object('original_code_id', 'type name', null, 1);
        $typ->api_mapper([json_fields::CODE_ID => 'changed_code_id'], $msg);
        $test_name = 'an untrusted api json cannot change the code id of a type';
        $t->assert($test_name, $typ->get_code_id(), 'original_code_id');
        $typ->api_mapper([json_fields::CODE_ID => 'cached_code_id'], $msg, true);
        $test_name = 'a trusted source e.g. the db cached types json sets the code id';
        $t->assert($test_name, $typ->get_code_id(), 'cached_code_id');

        // the same trust rule applies to the reference base url and the verb usage
        $msg = new user_message(user::system());
        $ref_typ = new ref_type('wikipedia', 'wikipedia', null, 1);
        $ref_typ->api_mapper([json_fields::URL => 'https://untrusted.example/'], $msg);
        $test_name = 'an untrusted api json cannot change the reference base url';
        $t->assert_null($test_name, $ref_typ->url);
        $ref_typ->api_mapper([json_fields::URL => 'https://en.wikipedia.org/wiki/'], $msg, true);
        $test_name = 'a trusted source e.g. the db cached types json sets the reference base url';
        $t->assert($test_name, $ref_typ->url, 'https://en.wikipedia.org/wiki/');
        $msg = new user_message(user::system());
        $vrb = new verb();
        $vrb->api_mapper([json_fields::USAGE => 7], $msg);
        $test_name = 'an untrusted api json cannot change the verb usage';
        $t->assert_null($test_name, $vrb->usage);
        $vrb->api_mapper([json_fields::USAGE => 7], $msg, true);
        $test_name = 'a trusted source e.g. the db cached types json sets the verb usage';
        $t->assert($test_name, $vrb->usage, 7);

        $t->subheader($ts . 'code link csv row mapper');
        // the statuum csv files are found by the status list class,
        // because statuum is the latin plural of status (see config_csv_get_file)
        $usr_sta_lst = new user_status_list();
        $usr_sta_lst->load_dummy();
        $test_name = 'the user statuum csv fills the user status dummy list';
        $t->assert_false($test_name, $usr_sta_lst->is_empty());

        // the code link csv loader maps each csv row via the id field name derived from the class,
        // so the derived name must match the csv and table column even for the enum classes
        $test_name = 'id field of the sys log status enum matches the csv and table column';
        $t->assert($test_name, $db_con->get_id_field_name(sys_log_statuum::class), sys_log_status::FLD_ID);
        $test_name = 'id field of the user status enum matches the csv and table column';
        $t->assert($test_name, $db_con->get_id_field_name(user_statuum::class), user_status::FLD_ID);

        $test_name = 'a code link csv row with the id column fills the sys log status';
        $log_sta = new sys_log_status('');
        $csv_row = [
            sys_log_status::FLD_ID => sys_log_statuum::OPEN_ID,
            sys_log_status::FLD_NAME => sys_log_statuum::OPEN_NAME,
            fields::FLD_CODE_ID => sys_log_statuum::OPEN,
            fields::FLD_DESCRIPTION => sys_log_statuum::OPEN_COM
        ];
        $t->assert_true($test_name, $log_sta->row_mapper_typ_obj($csv_row, sys_log_statuum::class));
        $test_name = 'the code id of the sys log status is filled from the csv row';
        $t->assert($test_name, $log_sta->code_id, sys_log_statuum::OPEN);
        $test_name = 'the name of the sys log status is filled from the csv row';
        $t->assert($test_name, $log_sta->name(), sys_log_statuum::OPEN_NAME);

        $test_name = 'a code link csv row without the id column reports a failed mapping';
        $log_sta = new sys_log_status('');
        $csv_row = [
            sys_log_status::FLD_NAME => sys_log_statuum::OPEN_NAME,
            fields::FLD_CODE_ID => sys_log_statuum::OPEN
        ];
        $t->assert_false($test_name, $log_sta->row_mapper_typ_obj($csv_row, sys_log_statuum::class));

        $t->subheader($ts . 'type load sql');
        // a type child class must create its load sql with its own table and id field
        // and never fall back to the type_list base class
        $log_fnc = new sys_log_function('', '', null, 1);
        $t->assert_sql_by_id($sc, $log_fnc);

        $t->subheader($ts . 'system log type sql setup');
        $log_typ = new sys_log_level('');
        $t->assert_sql_table_create($log_typ);
        $t->assert_sql_index_create($log_typ);

        $t->subheader($ts . 'system log status sql setup');
        $log_sta = new sys_log_status('');
        $t->assert_sql_table_create($log_sta);
        $t->assert_sql_index_create($log_sta);

        $t->subheader($ts . 'system log status sql setup');
        $log_fuc = new sys_log_function('');
        $t->assert_sql_table_create($log_fuc);
        $t->assert_sql_index_create($log_fuc);

        $t->subheader($ts . 'job status sql setup');
        $job_sta = new job_status('');
        $t->assert_sql_table_create($job_sta);
        $t->assert_sql_index_create($job_sta);

        $t->subheader($ts . 'job type sql setup');
        $job_typ = new job_type('');
        $t->assert_sql_table_create($job_typ);
        $t->assert_sql_index_create($job_typ);

        $t->subheader($ts . 'db_cache status sql setup');
        $dbc_sta = new db_cache_status('');
        $t->assert_sql_table_create($dbc_sta);
        $t->assert_sql_index_create($dbc_sta);

        $t->subheader($ts . 'db_cache type sql setup');
        $dbc_typ = new db_cache_type('');
        $t->assert_sql_table_create($dbc_typ);
        $t->assert_sql_index_create($dbc_typ);

        $t->subheader($ts . 'user profile sql setup');
        $usr_prf = new user_profile('');
        $t->assert_sql_table_create($usr_prf);
        $t->assert_sql_index_create($usr_prf);

        $t->subheader($ts . 'user type sql setup');
        $usr_typ = new user_type('');
        $t->assert_sql_table_create($usr_typ);
        $t->assert_sql_index_create($usr_typ);

        $t->subheader($ts . 'user status sql setup');
        $usr_sta = new user_status('');
        $t->assert_sql_table_create($usr_sta);
        $t->assert_sql_index_create($usr_sta);

        $t->subheader($ts . 'user identification sql setup');
        $usr_idt = new user_official_type('');
        $t->assert_sql_table_create($usr_idt);
        $t->assert_sql_index_create($usr_idt);

        $t->subheader($ts . 'protection type sql setup');
        $prt_typ = new protection_type('');
        $t->assert_sql_table_create($prt_typ);
        $t->assert_sql_index_create($prt_typ);

        $t->subheader($ts . 'share type sql setup');
        $shr_typ = new share_type('');
        $t->assert_sql_table_create($shr_typ);
        $t->assert_sql_index_create($shr_typ);

        $t->subheader($ts . 'phrase type sql setup');
        $phr_typ = new phrase_type('');
        $t->assert_sql_table_create($phr_typ);
        $t->assert_sql_index_create($phr_typ);

        $t->subheader($ts . 'source type sql setup');
        $src_typ = new source_type('');
        $t->assert_sql_table_create($src_typ);
        $t->assert_sql_index_create($src_typ);

        $t->subheader($ts . 'reference type sql setup');
        $ref_typ = new ref_type('');
        $t->assert_sql_table_create($ref_typ);
        $t->assert_sql_index_create($ref_typ);

        $t->subheader($ts . 'formula type sql setup');
        $frm_typ = new formula_type('');
        $t->assert_sql_table_create($frm_typ);
        $t->assert_sql_index_create($frm_typ);

        $t->subheader($ts . 'view type sql setup');
        $msk_typ = new view_type('');
        $t->assert_sql_table_create($msk_typ);
        $t->assert_sql_index_create($msk_typ);

        $t->subheader($ts . 'view style sql setup');
        $style = new view_style('');
        $t->assert_sql_table_create($style);
        $t->assert_sql_index_create($style);

        $t->subheader($ts . 'view term link type sql setup');
        $lnk_typ_ui = new view_link_type('');
        $t->assert_sql_table_create($lnk_typ_ui);
        $t->assert_sql_index_create($lnk_typ_ui);

        $t->subheader($ts . 'view relation type sql setup');
        $lnk_typ_ui = new view_relation_type('');
        $t->assert_sql_table_create($lnk_typ_ui);
        $t->assert_sql_index_create($lnk_typ_ui);

        $t->subheader($ts . 'component link type sql setup');
        $lnk_typ_ui = new component_link_type('');
        $t->assert_sql_table_create($lnk_typ_ui);
        $t->assert_sql_index_create($lnk_typ_ui);

        $t->subheader($ts . 'sql write insert of base change log types without log e.g. for system setup');
        $typ = $t_typ->change_action();
        $t->assert_sql_insert($sc, $typ);
        $typ = $t_typ->change_table();
        $t->assert_sql_insert($sc, $typ);
        $typ = $t_typ->change_field();
        $t->assert_sql_insert($sc, $typ);
        $typ = $t_typ->user_profile();
        $t->assert_sql_insert($sc, $typ);

        // TODO Prio 0 add language
        // TODO Prio 0 add  position_types, system_time_types
        // TODO Prio 3 revert [sql_type::LOG] to [sql_type::NO_LOG]

        $t->subheader($ts . 'sql write insert with log e.g. for system setup');
        $typ = $t_typ->sys_log_level();
        $t->assert_sql_insert($sc, $typ, [sql_type::LOG]);
        $typ = $t_typ->sys_log_status();
        $t->assert_sql_insert($sc, $typ, [sql_type::LOG]);
        $typ = $t_typ->sys_log_function();
        $t->assert_sql_insert($sc, $typ, [sql_type::LOG]);
        $typ = $t_typ->job_status();
        $t->assert_sql_insert($sc, $typ, [sql_type::LOG]);
        $typ = $t_typ->job_type();
        $t->assert_sql_insert($sc, $typ, [sql_type::LOG]);
        $typ = $t_typ->db_cache_status();
        $t->assert_sql_insert($sc, $typ, [sql_type::LOG]);
        $typ = $t_typ->db_cache_type();
        $t->assert_sql_insert($sc, $typ, [sql_type::LOG]);
        $typ = $t_typ->user_type();
        $t->assert_sql_insert($sc, $typ, [sql_type::LOG]);
        $typ = $t_typ->user_profile();
        $t->assert_sql_insert($sc, $typ, [sql_type::LOG]);
        $typ = $t_typ->user_official_type();
        $t->assert_sql_insert($sc, $typ, [sql_type::LOG]);
        $typ = $t_typ->protection_type();
        $t->assert_sql_insert($sc, $typ, [sql_type::LOG]);
        $typ = $t_typ->share_type();
        $t->assert_sql_insert($sc, $typ, [sql_type::LOG]);
        $typ = $t_typ->phrase_type();
        $t->assert_sql_insert($sc, $typ, [sql_type::LOG]);
        $typ = $t_typ->source_type();
        $t->assert_sql_insert($sc, $typ, [sql_type::LOG]);
        $typ = $t_typ->ref_type();
        $t->assert_sql_insert($sc, $typ, [sql_type::LOG]);
        $typ = $t_typ->formula_type();
        $t->assert_sql_insert($sc, $typ, [sql_type::LOG]);
        $typ = $t_typ->formula_link_type();
        $t->assert_sql_insert($sc, $typ, [sql_type::LOG]);
        $typ = $t_typ->element_type();
        $t->assert_sql_insert($sc, $typ, [sql_type::LOG]);
        $typ = $t_typ->view_type();
        $t->assert_sql_insert($sc, $typ, [sql_type::LOG]);
        $typ = $t_typ->view_style();
        $t->assert_sql_insert($sc, $typ, [sql_type::LOG]);
        $typ = $t_typ->view_link_type();
        $t->assert_sql_insert($sc, $typ, [sql_type::LOG]);
        $typ = $t_typ->view_relation_type();
        $t->assert_sql_insert($sc, $typ, [sql_type::LOG]);
        $typ = $t_typ->component_type();
        $t->assert_sql_insert($sc, $typ, [sql_type::LOG]);
        $typ = $t_typ->component_link_type();
        $t->assert_sql_insert($sc, $typ, [sql_type::LOG]);
        $typ = $t_typ->position_type();
        $t->assert_sql_insert($sc, $typ, [sql_type::LOG]);
        $typ = $t_typ->language();
        $t->assert_sql_insert($sc, $typ, [sql_type::LOG]);
        $typ = $t_typ->language_form();
        $t->assert_sql_insert($sc, $typ, [sql_type::LOG]);

        $t->subheader($ts . 'sql write update for admin use only');
        $typ = $t_typ->phrase_type();
        $typ_db = $typ->clone_all();
        $typ_db->description = 'changed description';
        $t->assert_sql_update($sc, $typ, $typ_db, [sql_type::LOG]);

        $t->subheader($ts . 'sql delete update for admin use only');
        $t->assert_sql_delete($sc, $typ, [sql_type::LOG]);
    }

}