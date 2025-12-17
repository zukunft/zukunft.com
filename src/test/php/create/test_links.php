<?php

/*

    test/create/test_links.php - create linked objects for unit testing
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

include_once paths::MODEL_VIEW . 'term_view.php';
include_once paths::MODEL_VIEW . 'view_link_type.php';
include_once test_paths::UTILS . 'test_cleanup.php';

use Zukunft\ZukunftCom\main\php\cfg\view\term_view;
use Zukunft\ZukunftCom\main\php\cfg\view\view_link_type;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class test_links
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

    function term_view(): term_view
    {
        $t_trm = new test_terms($this->env);
        $t_msk = new test_views($this->env);
        $lnk = new term_view($this->env->usr1);
        $lnk->set_view($t_msk->view());
        $lnk->set_predicate(view_link_type::DEFAULT);
        $lnk->set_term($t_trm->term());
        return $lnk;
    }

    function term_view_incomplete(): term_view
    {
        $t_wrd = new test_words($this->env);
        $lnk = $this->term_view();
        $lnk->set_term($t_wrd->word_incomplete()->term());
        return $lnk;
    }

}