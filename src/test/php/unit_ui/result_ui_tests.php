<?php

/*

    test/unit/html/result.php - testing of the html frontend functions for result
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

namespace Zukunft\ZukunftCom\test\php\unit_ui;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::SHARED_TYPES . 'api_types.php';

use Zukunft\ZukunftCom\main\php\web\component\execute\system_form;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\result\result;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\test\php\create\test_results;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class result_ui_tests
{
    function run(test_cleanup $t): void
    {
        $html = new html_base();
        $t_res = new test_results($t);
        $msg = new user_message();

        // start the test section (ts)
        $ts = 'unit ui html result ';
        $t->header($ts);

        $api_json = $t_res->result_simple()->api_json([api_types::TEST_MODE, api_types::INCL_PHRASES]);
        $res = new result($api_json);
        $test_page = $html->text_h2('result display test');
        $test_page .= 'with tooltip: ' . $res->display() . '<br>';
        $test_page .= 'with link: ' . $res->display_linked() . '<br>';
        $test_page .= $t->dsp_title_named_edit($res, $msg);
        $t->html_page_test($test_page, 'result', 'result', $msg);

        $t->subheader($ts . 'format');

        $test_name = 'big numbers use the user config thousand separator';
        $t->assert($test_name, $res->val_formatted($msg), "123'456");

        $test_name = 'percent values use the user config percent decimals';
        $api_json = $t_res->result_pct()->api_json([api_types::TEST_MODE, api_types::INCL_PHRASES]);
        $res = new result($api_json);
        $t->assert($test_name, $res->val_formatted($msg), '1.23%');

        $test_name = 'a missing number returns an empty text';
        $res = new result();
        $t->assert($test_name, $res->val_formatted($msg), '');

        // the overwrite form writes the changed number of the result without the confirm view
        $t->subheader($ts . 'value overwrite');
        $test_name = 'the result overwrite form shows the number field';
        $form = new system_form();
        $api_json = $t_res->result_simple()->api_json([api_types::TEST_MODE, api_types::INCL_PHRASES]);
        $res = new result($api_json);
        $res_id = (string)$res->id();
        $res_html = $form->form_value_overwrite($res);
        $t->assert_text_contains($test_name, $res_html, 'name="' . url_var::NUMERIC_VALUE . '"');
        $test_name = '... writes via the result edit mask';
        $t->assert_text_contains($test_name, $res_html, $html->form_hidden(url_var::MASK, (string)views::RESULT_EDIT_ID));
        $test_name = '... for the shown result';
        $t->assert_text_contains($test_name, $res_html, $html->form_hidden(url_var::ID, $res_id));
        $test_name = '... without the confirm view';
        $t->assert_text_contains($test_name, $res_html, $html->form_hidden(url_var::STEP, url_var::STEP_CONFIRMED));
        $test_name = '... shows the result with its default view';
        $res_back = $html->form_hidden(url_var::BACK . url_var::MASK, (string)views::RESULT_ID);
        $t->assert_text_contains($test_name, $res_html, $res_back);
    }

}