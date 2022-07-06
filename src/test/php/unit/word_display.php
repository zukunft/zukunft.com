<?php

/*

  test/unit/word_display.php - TESTing of the WORD DISPLAY functions
  --------------------------
  

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
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

use html\word_dsp;

class word_display_unit_tests
{
    function run(testing $t)
    {
        global $usr;

        $t->subheader('Word tests');

        $wrd = new word_dsp(1, word::TN_READ);
        $t->html_test($wrd->dsp_link(), 'word', $t);

        $t->html_test($wrd->dsp_header(), 'word_header', $t);
    }

}