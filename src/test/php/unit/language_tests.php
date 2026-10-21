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

use Zukunft\ZukunftCom\test\php\const\paths as test_paths;
use Zukunft\ZukunftCom\main\php\cfg\language\language;
use Zukunft\ZukunftCom\main\php\cfg\language\language_form;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\enum\language_forms;
use Zukunft\ZukunftCom\main\php\shared\enum\languages;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\types\language_list;
use Zukunft\ZukunftCom\main\php\web\system\language as ui_language;
use Zukunft\ZukunftCom\main\php\web\user\user_message as user_message_ui;
use Zukunft\ZukunftCom\test\php\create\test_languages;
use Zukunft\ZukunftCom\test\php\create\test_types;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class language_tests
{

    function run(test_cleanup $t): void
    {

        // init
        $t_lan = new test_languages();
        $t_typ = new test_types($t);
        $msg = new user_message();
        $msg_ui = new user_message_ui();
        $t->name = 'language->';
        $t->resource_path = 'db/language/';

        // start the test section (ts)
        $ts = 'unit language ';
        $t->header($ts);

        $t->subheader($ts . 'sql setup');
        $lan = new language('');
        $t->assert_sql_table_create($lan);
        $t->assert_sql_index_create($lan);

        $t->subheader($ts . 'form sql setup');
        $lan_for = new language_form('');
        $t->assert_sql_table_create($lan_for);
        $t->assert_sql_index_create($lan_for);
        $t->assert_sql_foreign_key_create($lan_for);


        $t->subheader($ts . 'api');

        global $sys;
        $lan = $sys->typ_lst->lan->get_by_code_id(languages::DEFAULT);
        $t->assert_api($lan, 'language');

        $t->subheader($ts . 'api mapper');

        $test_name = 'the language mapper sets the wikimedia code';
        $lan = new language('');
        $lan->api_mapper(json_decode($t_lan->language_translate()->api_json(), true), $msg);
        $t->assert($test_name, $lan->wiki_code, languages::TRANSLATE_WIKI);
        $msg->reset();

        // negative: an empty wikimedia code is no code, so the language keeps none
        $test_name = 'the language mapper ignores an empty wikimedia code';
        $lan = new language('');
        $lan->api_mapper([json_fields::WIKI_CODE => '', json_fields::LOCAL_NAME => '', json_fields::USAGE => ''], $msg);
        $t->assert_null($test_name, $lan->wiki_code);
        $msg->reset();


        // start the test section (ts)
        $ts = 'unit language form ';
        $t->header($ts);

        $t->subheader($ts . 'api');

        global $sys;
        $lan_typ = $sys->typ_lst->lan_for->get_by_code_id(language_forms::PLURAL);
        $t->assert_api($lan_typ, 'language_form_cache');

        $t->subheader($ts . 'api mapper');

        $test_name = 'the language form mapper sets the language';
        $api_json = json_decode($t_typ->language_form()->api_json(), true);
        $api_json[json_fields::LANGUAGE] = languages::DEFAULT_ID;
        $lan_for = new language_form('');
        $lan_for->api_mapper($api_json, $msg);
        $t->assert($test_name, $lan_for->lng_id, languages::DEFAULT_ID);
        $msg->reset();

        // negative: an empty language is no language, so the language form keeps none
        $test_name = 'the language form mapper ignores an empty language';
        $lan_for = new language_form('');
        $lan_for->api_mapper([json_fields::LANGUAGE => ''], $msg);
        $t->assert_null($test_name, $lan_for->lng_id);
        $msg->reset();


        // start the test section (ts)
        $ts = 'unit language list ';
        $t->header($ts);

        $t->subheader($ts . 'api');
        $lst = $t_lan->language_list();
        $t->assert_api($lst, 'language_list');

        $t->subheader($ts . 'ui');
        $test_name = 'header language select list';
        $lst = $t_lan->language_list();
        $api_json = $lst->api_json_array([], $msg);
        $ui_lst = new language_list();
        $ui_lst->set_from_json_array($api_json, $msg_ui, ui_language::class);
        $html = new html_base();
        $url = $html->url_back(views::START_ID);
        $target = $t->file('unit/language/header_select_list.html');
        $t->assert_html($test_name, $ui_lst->select_list_item($url), $target);

    }

}