<?php

/*

    test/unit/html/language.php - testing of the html frontend functions for languages
    ---------------------------

    the display tests for languages are added because a user (or admin)
    should have the possibility to add a new language via the GUI

    the selection list of languages is tested with the type list test
  

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

namespace Zukunft\ZukunftCom\test\php\unit_ui;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::EXECUTE . 'system_form.php';
include_once html_paths::EXECUTE . 'ui_preview.php';
include_once html_paths::SYSTEM . 'language.php';
include_once paths::SHARED . 'url_var.php';

use Zukunft\ZukunftCom\main\php\web\component\execute\system_form;
use Zukunft\ZukunftCom\main\php\web\component\execute\ui_preview;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\system\language;
use Zukunft\ZukunftCom\main\php\web\user\user as user_ui;
use Zukunft\ZukunftCom\main\php\web\user\user_message as user_message_ui;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\enum\languages;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\test\php\create\test_languages;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class language_ui_tests
{
    function run(test_cleanup $t): void
    {
        $html = new html_base();
        $t_lan = new test_languages($t);
        $msg_ui = new user_message_ui();

        // start the test section (ts)
        $ts = 'unit ui html language ';
        $t->header($ts);

        $src = new language(
            $t_lan->language()->id(),
            $t_lan->language()->code_id,
            $t_lan->language()->name,
            $t_lan->language()->description
        );
        $test_page = $html->text_h2('language display test');
        $test_page .= 'with tooltip: ' . $src->name_tip() . '<br>';
        $test_page .= 'with link: ' . $src->name_link() . '<br>';
        $t->html_page_test($test_page, 'language', 'language', $msg_ui);

        // the popup form class must also accept a language (e.g. of the language add form),
        // which is a type object but not a db object
        $test_name = 'the popup form class of a language form is the language class name';
        $preview = new ui_preview();
        $t->assert_true($test_name, $preview->popup_class($src) != '');

        // the popup change table must also accept a language; without an own db field order the
        // change labels are derived from the url keys
        $test_name = 'the popup change table of a language form shows the changed name';
        $chg_html = $preview->popup_changes($msg_ui, [
            url_var::NAME => $src->name,
            url_var::PRE . url_var::NAME => ''
        ], $src);
        $t->assert_text_contains($test_name, $chg_html, $src->name);

        $t->subheader($ts . 'debug id');

        // the type object debug text shows the name, the id and the code id of the language
        $test_name = 'shows the name of the language';
        $dsp = $src->dsp_id();
        $t->assert_text_contains($test_name, $dsp, languages::DEFAULT_NAME);
        $test_name = '... its id';
        $id_txt = '(' . languages::DEFAULT_ID . ')';
        $t->assert_text_contains($test_name, $dsp, $id_txt);
        $test_name = '... and its code id';
        $t->assert_text_contains($test_name, $dsp, languages::DEFAULT);

        // negative: a language without a name has no name to show
        $test_name = 'shows no name for a language without a name';
        $lan_no_name = new language(languages::DEFAULT_ID, languages::DEFAULT);
        $dsp = $lan_no_name->dsp_id();
        $t->assert_text_not_contains($test_name, $dsp, languages::DEFAULT_NAME);

        $t->subheader($ts . 'symbol');

        // the symbol of a language is its wikimedia code e.g. "de" for German
        $test_name = 'the language del view shows the wikimedia code as the symbol';
        $sfm = new system_form();
        $lan_trn = $t_lan->language_translate();
        $lan_de = new language($lan_trn->id(), $lan_trn->code_id, $lan_trn->name, $lan_trn->description);
        $lan_de->wiki_code = $lan_trn->wiki_code;
        $t->assert($test_name, $sfm->show_language_symbol($lan_de), languages::TRANSLATE_WIKI);
        // negative: a language without wikimedia code shows no symbol and not its name instead
        $test_name = 'a language without wikimedia code shows no symbol';
        $t->assert($test_name, $sfm->show_language_symbol($src), '');
        $test_name = 'the admin symbol field of a language is filled with the wikimedia code';
        $sym_fld = $sfm->admin_form_language_symbol($lan_de);
        $t->assert_text_contains($test_name, $sym_fld, 'value="' . languages::TRANSLATE_WIKI . '"');
        // negative: the field of a language without wikimedia code is empty
        $test_name = 'the admin symbol field of a language without wikimedia code is empty';
        $sym_fld = $sfm->admin_form_language_symbol($src);
        $t->assert_text_not_contains($test_name, $sym_fld, 'value=');

        $t->subheader($ts . 'request');

        // only an admin can add a language, so only an admin gets the link to the language add view
        global $mtr;
        $test_name = 'an admin gets the link to the language add view';
        $msg_ui->usr = new user_ui($t->usr_admin->api_json());
        $req_html = $sfm->button_request($msg_ui);
        $t->assert_text_contains($test_name, $req_html, url_var::MASK . url_var::EQ . views::LANGUAGE_ADD_ID);
        $test_name = '... with the text to add languages';
        $t->assert_text_contains($test_name, $req_html, $mtr->txt(msg_id::BUTTON_ADD_LANGUAGES));
        // negative: a user who is no admin sees no button
        $test_name = 'a user who is no admin gets no language request button';
        $msg_ui->usr = new user_ui($t->usr1->api_json());
        $t->assert($test_name, $sfm->button_request($msg_ui), '');
        $msg_ui->usr = null;
    }

}