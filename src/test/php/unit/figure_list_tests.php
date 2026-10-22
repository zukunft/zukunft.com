<?php

/*

    test/unit/result_list.php - unit testing of the FORMULA VALUE functions
    --------------------------------
  

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

namespace Zukunft\ZukunftCom\test\php\unit;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once paths::DB . 'sql_creator.php';
include_once paths::MODEL_FORMULA . 'fig_ids.php';
include_once paths::MODEL_FORMULA . 'figure_list.php';
include_once html_paths::FIGURE . 'figure_list.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\formula\fig_ids;
use Zukunft\ZukunftCom\main\php\cfg\formula\figure_list;
use Zukunft\ZukunftCom\main\php\web\figure\figure_list as figure_list_ui;
use Zukunft\ZukunftCom\main\php\shared\const\results;
use Zukunft\ZukunftCom\main\php\shared\const\values;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\test\php\create\test_figures;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;
use Zukunft\ZukunftCom\test\php\utils\test_lib;

class figure_list_tests
{

    function run(test_cleanup $t): void
    {

        // init
        $msg = new user_message();
        $sc = new sql_creator();
        $t_fig = new test_figures($t);
        $tl = new test_lib();
        $t->name = 'figure->';
        $t->resource_path = 'db/figure/';
        $json_file = 'unit/figure/figure_list_import.json';

        // start the test section (ts)
        $ts = 'unit figure list ';
        $t->header($ts);

        $t->subheader($ts . 'sql statement');

        // load by figure ids
        $test_name = 'load figures by ids';
        $fig_lst = new figure_list($t->usr1);
        $t->assert_sql_by_ids($test_name, $sc, $fig_lst, new fig_ids([1, -1]));


        $t->subheader($ts . 'api');

        $fig_lst = $t_fig->figure_list($msg);
        $t->assert_api($fig_lst, 'figure_list_without_phrases');
        $t->assert_api($fig_lst, 'figure_list_with_phrases', [api_types::INCL_PHRASES]);

        $t->subheader($ts . 'api mapper');
        $test_name = 'the api mapper adds every figure of the api json';
        $fig_lst = new figure_list($t->usr1);
        $fig_lst->api_mapper($t_fig->figure_list($msg)->api_json_array([], $msg), $msg);
        $t->assert($test_name, $fig_lst->count(), $t_fig->figure_list($msg)->count());
        $msg->reset();
        $test_name = 'the api mapper refuses a figure with a text as the number';
        $fig_json = $t_fig->figure_value($msg)->api_json_array([], $msg);
        $fig_json[json_fields::NUMBER] = values::TEXT;
        $fig_lst = new figure_list($t->usr1);
        $t->assert_false($test_name, $fig_lst->api_mapper([$fig_json], $msg));
        $msg->reset();

        $t->subheader($ts . 'get');
        $test_name = 'the first id is the id of the first figure';
        $fig_lst = $t_fig->figure_list($msg);
        $first_id = $fig_lst->get_first_id($msg);
        $t->assert($test_name, $first_id, $t_fig->figure_value($msg)->id());
        $msg->reset();
        $test_name = 'an empty figure list has no first id';
        $fig_lst = new figure_list($t->usr1);
        $first_id = $fig_lst->get_first_id($msg);
        $t->assert($test_name, $first_id, 0);
        $msg->reset();

        $t->subheader($ts . 'add');
        // a figure of a value is added to an empty list
        $test_name = 'add a figure';
        $fig_lst = new figure_list($t->usr1);
        $added = $fig_lst->add($t_fig->figure_value($msg), $msg);
        $t->assert_true($test_name, $added);
        $msg->reset();

        // negative: the same figure again is refused as double
        $test_name = 'add refuses a double figure';
        $fig_lst = $t_fig->figure_list($msg);
        $added = $fig_lst->add($t_fig->figure_value($msg), $msg);
        $t->assert_false($test_name, $added);
        $msg->reset();

        $t->subheader($ts . 'debug id');

        // the debug id names the figures with their number
        $test_name = 'debug id with the figure numbers';
        $num_txt = (string)results::TV_INT;
        $fig_lst = $t_fig->figure_list($msg);
        $dsp = $fig_lst->dsp_id();
        $t->assert_text_contains($test_name, $dsp, $num_txt);
        $msg->reset();

        // negative: an empty figure list shows no figure number
        $test_name = 'debug id of an empty list';
        $fig_lst = new figure_list($t->usr1);
        $dsp = $fig_lst->dsp_id();
        $t->assert_text_not_contains($test_name, $dsp, $num_txt);

        // the frontend list uses the shared list debug id with the id of each figure
        $test_name = 'frontend debug id with the figure id';
        $id_txt = (string)values::PI_SYMBOL_ID;
        $fig_lst_ui = $tl->ui_obj($t_fig->figure_list($msg), new figure_list_ui());
        $dsp = $fig_lst_ui->dsp_id();
        $t->assert_text_contains($test_name, $dsp, $id_txt);
        $msg->reset();

        // negative: an empty frontend list has an empty debug id
        $test_name = 'frontend debug id of an empty list';
        $fig_lst_ui = new figure_list_ui();
        $dsp = $fig_lst_ui->dsp_id();
        $t->assert($test_name, $dsp, '');


        $t->subheader($ts . 'html frontend');

        $fig_lst = $t_fig->figure_list($msg);
        $t->assert_api_to_ui($fig_lst, new figure_list_ui());

    }

}