<?php

/*

    test/unit/html/triple_list.php - testing of the html frontend functions for triples
    ------------------------------
  

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

namespace unit\html;

use api\triple_api;
use api\word_api;
use html\html_base;
use html\word\word as word_dsp;
use html\word\triple as triple_dsp;
use html\word\triple_list as triple_list_dsp;
use cfg\verb;
use test\test_cleanup;

class triple_list
{
    function run(test_cleanup $t): void
    {

        $html = new html_base();

        $t->subheader('HTML triple list tests');

        // fill the triple list based on the api message
        $db_lst = $t->dummy_triple_list();
        $lst = new triple_list_dsp($db_lst->api_json());
        $t->assert('HTML triple list names match backend names', $lst->names(), $db_lst->names());

        // create the triple list test set
        $lst = new triple_list_dsp();
        $phr_city = $this->triple_api_triple(1,  triple_api::TN_ZH_CITY_NAME,
            word_api::TN_ZH, verb::IS, word_api::TN_CITY);
        $phr_canton = $this->triple_api_triple(2,  triple_api::TN_ZH_CANTON_NAME,
            word_api::TN_ZH, verb::IS, word_api::TN_CANTON);
        $lst->add($phr_city);
        $lst->add($phr_canton);

        // test the triple list display functions
        $test_page = $html->text_h2('triple list display test');
        /*
        $test_page .= 'names with links: ' . $lst->display() . '<br>';
        $test_page .= 'table cells<br>';
        $test_page .= $lst->tbl();
        */

        $test_page .= 'selector: ' . '<br>';
        $test_page .= $lst->selector('triple list test selector', '', 'please select') . '<br>';

        $t->html_test($test_page, 'triple_list', $t);
    }

    function triple_api_triple(
        int $id,
        string $name,
        string $from = '',
        string $verb = '',
        string $to = ''
    ): triple_dsp {
        $trp = new triple_api($id, $name, $from, $verb, $to);
        return new triple_dsp($trp->get_json());
    }

}