<?php

/*

    test/php/unit_read/change_log.php - database unit testing of the user log functions
    ---------------------------------


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

namespace unit_read;

use cfg\const\paths;

include_once paths::SHARED_CONST . 'triples.php';
include_once paths::SHARED_CONST . 'words.php';
include_once paths::SHARED_ENUM . 'change_fields.php';

use cfg\log\change_log_list;
use cfg\word\word;
use shared\const\components;
use shared\const\formulas;
use shared\const\sources;
use shared\const\triples;
use shared\const\views;
use shared\const\words;
use shared\enum\change_fields;
use shared\types\verbs;
use test\test_cleanup;

class change_log_read_tests
{

    function run(test_cleanup $t): void
    {

        global $usr_sys;

        // init
        $t->name = 'user log read db->';

        $t->header('Unit database tests of the user log classes (src/main/php/model/log/* and src/main/php/model/user/log_*)');

        $t->subheader('Load user log tests');

        // prepare the objects for the tests
        // TODO use these test functions for all dummy object creations
        // TODO remove dummy from name because this is anyway know by the $test class
        $wrd = $t->word();
        $vrb = $t->verb();
        $trp = $t->triple_pi();
        $val = $t->value();
        $frm = $t->formula();
        $src = $t->source();
        $ref = $t->reference();
        $msk = $t->view();
        $cmp = $t->component();

        // check if loading the changes technically works
        $lst = new change_log_list();
        $result = $lst->load_by_fld_of_wrd($wrd, $t->usr1, change_fields::FLD_WORD_NAME);
        $t->assert('word name change', $result, true);

        // ... and if the first entry is the adding of the word name
        $first_change = $lst->lst()[0];
        $t->assert('first word change is adding', $first_change->old_value, '');
        $t->assert('... the name', $first_change->new_value, words::MATH);

        // check loading of verb name changes
        $lst = new change_log_list();
        $result = $lst->load_by_fld_of_vrb($vrb, $t->usr1, change_fields::FLD_VERB_NAME);
        $t->assert('verb name change', $result, true);

        // ... and if the first entry is the adding a verb name
        $first_change = $lst->lst()[0];
        $t->assert('first verb change is adding', $first_change->old_value, '');
        $t->assert('... the verb name', $first_change->new_value, verbs::NOT_SET_NAME);

        // check loading of triple name changes of triples
        $lst = new change_log_list();
        $result = $lst->load_by_fld_of_trp($trp, $t->usr1, change_fields::FLD_TRIPLE_NAME);
        $t->assert('triple name change', $result, true);

        // check loading of given name changes of triples
        // TODO replace with triple name ?
        $lst = new change_log_list();
        $result = $lst->load_by_fld_of_trp($trp, $t->usr1, change_fields::FLD_GIVEN_NAME);
        // TODO Prio 1 activate
        //$t->assert('given name change', $result, true);

        // ... and if the first entry is the setting the given name of a triple
        // TODO Prio 1 activate
        //$first_change = $lst->lst()[0];
        //$t->assert('first triple change is setting', $first_change->old_value, '');
        //$t->assert('... the given name', $first_change->new_value, triples::PI_NAME);

        // check loading of user value changes
        $lst = new change_log_list();
        $result = $lst->load_by_fld_of_val($val, $t->usr1, change_fields::FLD_NUMERIC_VALUE);
        $t->assert('value change', $result, true);

        // ... and if the first entry is the update Pi probably because not all decimals can be saved in the database
        $first_change = $lst->lst()[0];
        // TODO review
        //$t->assert('first value change is updating Pi', $first_change->old_value, values::TV_READ_SHORT);
        //$t->assert('... to empty', $first_change->new_value, values::TV_READ_SHORT);
        //$t->assert('first value change is updating Pi from empty', $first_change->old_value, "");
        //$t->assert('... to Pi', $first_change->new_value, values::TV_READ_SHORT);

        // check loading of user formula changes
        $lst = new change_log_list();
        $result = $lst->load_by_fld_of_frm($frm, $t->usr1, change_fields::FLD_FORMULA_USR_TEXT);
        $t->assert('formula expression change', $result, true);

        // ... and if the first entry is the adding the minute scale formula
        $first_change = $lst->lst()[0];
        $t->assert('first formula change is adding', $first_change->old_value, '');
        $t->assert('... the minute scale formula', $first_change->new_value, formulas::SCALE_TO_SEC_EXP);

        // check loading of name changes of source
        $lst = new change_log_list();
        $result = $lst->load_by_fld_of_src($src, $t->usr1, change_fields::FLD_SOURCE_NAME);
        $t->assert('source name change', $result, true);

        // ... and if the first entry is the setting the source name
        $first_change = $lst->lst()[0];
        $t->assert('first source change is setting', $first_change->old_value, '');
        $t->assert('... the name', $first_change->new_value, sources::SIB);

        // check loading of name changes of view
        $lst = new change_log_list();
        $result = $lst->load_by_fld_of_dsp($msk, $t->usr1, change_fields::FLD_VIEW_NAME);
        $t->assert('view name change', $result, true);

        // ... and if the first entry is the setting the view name
        $first_change = $lst->lst()[0];
        $t->assert('first view change is setting', $first_change->old_value, '');
        $t->assert('... the name', $first_change->new_value, views::START_NAME);

        // check loading of name changes of view component
        $lst = new change_log_list();
        $result = $lst->load_by_fld_of_cmp($cmp, $t->usr1, change_fields::FLD_COMPONENT_NAME);
        $t->assert('view component name change', $result, true);

        // ... and if the first entry is the setting the view component name
        $first_change = $lst->lst()[0];
        $t->assert('first view component change is setting', $first_change->old_value, '');
        $t->assert('... the name', $first_change->new_value, components::WORD_NAME);

        // TODO add ref

        $t->subheader('API unit db tests');

        $wrd = new word($t->usr1);
        $wrd->load_by_id(1);
        $log_lst = new change_log_list();
        $log_lst->load_by_fld_of_wrd($wrd, $usr_sys, change_fields::FLD_WORD_NAME);
        $t->assert_api($log_lst);

    }

}

