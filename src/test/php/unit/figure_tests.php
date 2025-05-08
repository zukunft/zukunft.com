<?php

/*

    test/unit/figure.php - unit testing of the figure functions
    --------------------
  

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

namespace unit;

include_once WEB_FIGURE_PATH . 'figure.php';

use cfg\result\results;
use html\figure\figure as figure_dsp;
use html\rest_ctrl;
use shared\const\values;
use shared\types\api_type;
use test\test_cleanup;

class figure_tests
{
    function run(test_cleanup $t): void
    {

        // start the test section (ts)
        $ts = 'unit figure ';
        $t->header($ts);

        $t->subheader($ts . 'sql statement');

        // if the user has changed the formula, that related figure is not standard anymore
        /*
        $frm = new formula($usr);
        $frm->usr_cfg_id = 1;
        $fig = new figure($usr);
        $fig->obj = $frm;
        $result = $fig->is_std();
        $t->assert('figure->is_std if formula is changed by the user', $result, false);
        */


        $t->subheader($ts . 'set and get');

        $fig = $t->figure_value();
        $t->assert('figure value id', $fig->id(), values::PI_ID);
        $t->assert('figure value obj id', $fig->obj_id(), values::PI_ID);
        $t->assert('figure value number', $fig->number(), values::PI_SHORT);
        $fig = $t->figure_result();
        $t->assert('figure result id', $fig->id(), -1);
        $t->assert('figure result obj id', $fig->obj_id(), 1);
        $t->assert('figure result number', $fig->number(), results::TV_INT);

        $fig = $t->figure_value();
        $t->assert('figure value symbol', $fig->symbol(), "");
        $fig = $t->figure_result();
        // TODO review
        //$t->assert('figure result symbol', $fig->symbol(), "{f1}");


        $t->subheader($ts . 'api');

        $fig = $t->figure_value();
        $t->assert_api($fig, 'figure_value_without_phrases');
        $t->assert_api($fig, 'figure_value_with_phrases', [api_type::INCL_PHRASES]);

        $fig = $t->figure_result();
        $t->assert_api($fig, 'figure_result_without_phrases');
        $t->assert_api($fig, 'figure_result_with_phrases', [api_type::INCL_PHRASES]);


        $t->subheader($ts . 'html frontend');

        $fig = $t->figure_value();
        $t->assert_api_to_dsp($fig, new figure_dsp());
        $fig = $t->figure_result();
        $t->assert_api_to_dsp($fig, new figure_dsp());

        $fig = $t->figure_value();
        $dsp = $t->dsp_obj($fig, new figure_dsp());
        $html_link = $dsp->display_linked();
        $t->assert_text_contains('figure html link', $html_link, rest_ctrl::RESULT_EDIT);

    }

}