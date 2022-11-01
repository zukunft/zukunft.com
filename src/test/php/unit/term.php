<?php

/*

    test/unit/term.php - unit testing of the TERM functions
    ------------------


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

class term_unit_tests
{
    function run(testing $t): void
    {

        global $usr;

        // init
        $t->name = 'term->';

        $t->header('Unit tests of the term class (src/main/php/model/phrase/term.php)');

        $wrd = new word($usr, 1, word::TN_READ);
        $trm = $wrd->term();
        $t->assert($t->name . 'term->word id', $trm->id_obj(), $wrd->id());
        $t->assert($t->name . 'term->word name', $trm->name(), $wrd->name());

        $trp = new triple($usr, triple::TN_READ);
        $trp->id = 1;
        $trm = $trp->term();
        $t->assert($t->name . 'term->triple id', $trm->id_obj(), $trp->id());
        $t->assert($t->name . 'term->triple name', $trm->name(), $trp->name());

        $frm = new formula($usr, formula::TN_READ);
        $frm->id = 1;
        $trm = $frm->term();
        $t->assert($t->name . 'term->formula id', $trm->id_obj(), $frm->id());
        $t->assert($t->name . 'term->formula name', $trm->name(), $frm->name());

        $vrb = new verb(1, verb::IS_A);
        $vrb->usr = $usr;
        $trm = $vrb->term();
        $t->assert($t->name . 'term->verb id', $trm->id_obj(), $vrb->id());
        $t->assert($t->name . 'term->verb name', $trm->name(), $vrb->name());

    }

}
