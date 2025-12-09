<?php

/*

    test/create/test_objects.php - create the unit test figure objects
    ----------------------------


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
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::MODEL_FORMULA . 'figure.php';
include_once paths::MODEL_FORMULA . 'figure_list.php';
include_once test_paths::CREATE . 'test_const.php';
include_once test_paths::UTILS . 'test_cleanup.php';

use Zukunft\ZukunftCom\main\php\cfg\formula\figure;
use Zukunft\ZukunftCom\main\php\cfg\formula\figure_list;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;
use DateTime;

class test_figures
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
     * unit
     */

    /**
     * @return figure with all vars set for unit testing - user value case
     */
    function figure_value(): figure
    {
        $t_val = new test_values($this->env);
        $val = $t_val->value();
        $val->set_last_update(new DateTime(test_const::DUMMY_DATETIME));
        return $val->figure();
    }

    /**
     * @return figure with all vars set for unit testing - user value case
     */
    function figure_result(): figure
    {
        $t_res = new test_results($this->env);
        $res = $t_res->result_simple_1();
        return $res->figure();
    }

    function figure_list(): figure_list
    {
        $lst = new figure_list($this->env->usr1);
        $lst->add($this->figure_value());
        $lst->add($this->figure_result());
        return $lst;
    }

}