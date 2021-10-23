<?php

/*

    test/php/unit/phrase_list.php - unit tests related to a phrase list
    -----------------------------


    zukunft.com - calc with words

    copyright 1995-2021 by zukunft.com AG, Blumentalstrasse 15, 8707 Uetikon am See, Switzerland

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

class phrase_list_unit_tests
{

    /**
     * execute all phrase list unit tests and return the test result
     * TODO create a common test result object to return
     * TODO capsule all unit tests in a class like this example
     */
    function run()
    {

        test_header('Unit tests of the phrase list class (src/main/php/model/phrase/phrase_list.php)');

        test_subheader('Selection tests');

        // check that a time phrase is correctly removed from a phrase list
        $phr_lst = $this->get_phrase_list();
        $phr_lst_ex_time = clone $phr_lst;
        $phr_lst_ex_time->ex_time();
        $result = true;
        $target = true;
        test_dsp('phrase_list->ex_time', $target, $result);
        $result = $phr_lst_ex_time->dsp_id();
        $target = $this->get_phrase_list_ex_time()->dsp_id();
        test_dsp('phrase_list->ex_time names', $target, $result);

    }

    /**
     * create the standard phrase list test object without using a database connection
     */
    public function get_phrase_list(): phrase_list
    {
        global $usr;
        $phr_lst = new phrase_list;
        $phr_lst->usr = $usr;
        $phr_lst->add($this->get_phrase());
        $phr_lst->add($this->get_time_phrase());
        return $phr_lst;
    }

    /**
     * same as get_phrase_list but without time phrase
     */
    private function get_phrase_list_ex_time(): phrase_list
    {
        global $usr;
        $phr_lst = new phrase_list;
        $phr_lst->usr = $usr;
        $phr_lst->add($this->get_phrase());
        return $phr_lst;
    }

    /**
     * create the standard filled phrase object
     */
    private function get_phrase(): phrase
    {
        global $usr;
        $wrd = new word();
        $wrd->id = 1;
        $wrd->name = word::TN_ADD;
        $wrd->usr = $usr;
        return $wrd->phrase();
    }

    /**
     * create the filled time phrase object
     */
    private function get_time_phrase(): phrase
    {
        global $usr;
        $wrd = new word();
        $wrd->id = 2;
        $wrd->name = word::TN_RENAMED;
        $wrd->usr = $usr;
        $wrd->type_id = cl(db_cl::WORD_TYPE, word_type_list::DBL_TIME);
        return $wrd->phrase();
    }

}