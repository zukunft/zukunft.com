<?php

/*

    test/php/const/workflows.php - predefined const for the url based user workflow tests
    ----------------------------

    the snapshot folder and file names of a workflow test are built from these const
    so the workflow id and the name parts are changed in one place (docs/llm/testing.md)

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

    Copyright (c) 1995-2026 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\test\php\const;

use Zukunft\ZukunftCom\main\php\shared\url_var;

class workflows
{

    // workflow step names
    // used to create the workflow file name
    // and to define the url action parameter 'z' (url_var::STEP)
    // and to select the http method
    const string SHOW = 'show'; // just show a html page
    const string BACK = 'back'; // leave the page and got back to the previous view
    const string ADD = 'add'; // call the page to add an object
    const string EDIT = 'edit'; // call the page to change an object
    const string DEL = 'del'; // call the page to delete an object
    const string SAVE = 'save'; // press the save button
    const string FILL = 'fill'; // fill all object fields and press save
    const string CANCEL = 'cancel'; // press the cancel button
    const string CONFIRM = 'confirm'; // call the page to delete an object
    const string CONFIRMED = 'confirmed'; // show the original view with the changes

    // separator between the name parts of a workflow snapshot file name e.g. 'wf2_show_edit'
    const string NAME_SEP = '_';

    // the dummy start time that replaces the volatile change log time in the snapshots; the change log
    // rows keep a deterministic time sequence by increasing this by one second per shown entry (see
    // url_test_base::normalize_change_log_time)
    const string WF_CHANGE_LOG_START = '1997-06-07 16:01:00';

    // the snapshot file name prefix that is followed by the workflow id e.g. 'wf2'
    const string WF_PREFIX = 'wf';

    // the add_word workflow name used for the snapshot folder and the test subheader
    const string ADD_WORD = 'add_word';
    // the id of the current add_word workflow; increase it to add the next workflow snapshot set
    const int WF_ADD_WORD_NBR = 1;

    // the change_word workflow name used for the snapshot folder and the test subheader
    const string WF_CHANGE_WORD = 'change_word';
    // the id of the current change_word workflow; increase it to add the next workflow snapshot set
    const int WF_CHANGE_WORD_NBR = 2;

    // the del_word workflow name used for the snapshot folder and the test subheader
    const string WF_DEL_WORD = 'del_word';
    // the id of the current del_word workflow; increase it to add the next workflow snapshot set
    const int WF_DEL_WORD_NBR = 3;

    // the add_triple workflow name used for the snapshot folder and the test subheader
    const string WF_ADD_TRIPLE = 'add_triple';
    // the id of the current add_triple workflow; increase it to add the next workflow snapshot set
    const int WF_ADD_TRIPLE_NBR = 4;

    // the change_triple workflow name used for the snapshot folder and the test subheader
    const string WF_CHANGE_TRIPLE = 'change_triple';
    // the id of the current change_triple workflow; increase it to add the next workflow snapshot set
    const int WF_CHANGE_TRIPLE_NBR = 5;

    // the del_triple workflow name used for the snapshot folder and the test subheader
    const string WF_DEL_TRIPLE = 'del_triple';
    // the id of the current del_triple workflow; increase it to add the next workflow snapshot set
    const int WF_DEL_TRIPLE_NBR = 6;

    // the change_word_fail workflow name used for the snapshot folder and the test subheader:
    // a save with an invalid change (e.g. an empty name) keeps the edit view instead of confirming
    const string WF_CHANGE_WORD_FAIL = 'change_word_fail';
    // the id of the current change_word_fail workflow; 4 to 6 are reserved for the triple workflows
    const int WF_CHANGE_WORD_FAIL_NBR = 7;

    // the change_triple_by_name workflow name used for the snapshot folder and the test subheader:
    // a save where the from and to phrases are posted as the phrase names the datalist fields submit
    const string WF_CHANGE_TRIPLE_BY_NAME = 'change_triple_by_name';
    // the id of the current change_triple_by_name workflow; increase it to add the next snapshot set
    const int WF_CHANGE_TRIPLE_BY_NAME_NBR = 8;

    // the add_word_fail workflow name used for the snapshot folder and the test subheader:
    // the negative twin of add_word where a save with an invalid entry (e.g. an empty name) keeps the
    // add form with a warning instead of confirming the new word
    const string WF_ADD_WORD_FAIL = 'add_word_fail';
    // the id of the current add_word_fail workflow; increase it to add the next snapshot set
    const int WF_ADD_WORD_FAIL_NBR = 9;

    // the del_word_fail workflow name used for the snapshot folder and the test subheader:
    // the negative twin of del_word where confirming the deletion of a word that is still in use keeps
    // the delete form with a warning instead of confirming the deletion
    const string WF_DEL_WORD_FAIL = 'del_word_fail';
    // the id of the current del_word_fail workflow; increase it to add the next snapshot set
    const int WF_DEL_WORD_FAIL_NBR = 10;

    // snapshot file name marker for the failing input variant of a negative workflow step, e.g. the
    // 'no_name' in 'wf9_edit_no_name_save' where the save is pressed with an empty name
    const string STEP_NO_NAME = 'no_name';

    // snapshot file name marker for a delete blocked because the object is still in use, e.g. the
    // 'in_use' in 'wf10_show_edit_in_use_save' where delete is pressed on an in-use word
    const string STEP_IN_USE = 'in_use';

    // the add_triple_fail workflow name used for the snapshot folder and the test subheader:
    // the negative twin of add_triple where a save without a from and a to phrase keeps the add form
    // with a warning instead of confirming the invalid triple
    const string WF_ADD_TRIPLE_FAIL = 'add_triple_fail';
    // the id of the current add_triple_fail workflow; increase it to add the next snapshot set
    const int WF_ADD_TRIPLE_FAIL_NBR = 11;

    // snapshot file name marker for an add blocked because the from and to phrases are missing, e.g.
    // the 'no_phrases' in 'wf11_edit_no_phrases_save' where save is pressed on a triple without them
    const string STEP_NO_PHRASES = 'no_phrases';

    // the change_triple_fail workflow name used for the snapshot folder and the test subheader:
    // the negative twin of change_triple where a save that clears the from and to phrases keeps the
    // edit view with a warning instead of confirming the invalid change
    const string WF_CHANGE_TRIPLE_FAIL = 'change_triple_fail';
    // the id of the current change_triple_fail workflow; increase it to add the next snapshot set
    const int WF_CHANGE_TRIPLE_FAIL_NBR = 12;

    // the del_triple_fail workflow name used for the snapshot folder and the test subheader:
    // the negative twin of del_triple where confirming the deletion of a triple that is still in use
    // keeps the delete form with a warning instead of confirming the deletion
    const string WF_DEL_TRIPLE_FAIL = 'del_triple_fail';
    // the id of the current del_triple_fail workflow; increase it to add the next snapshot set
    const int WF_DEL_TRIPLE_FAIL_NBR = 13;

    // the add_formula workflow name used for the snapshot folder and the test subheader:
    // a new formula is entered in the add form and written after the user has confirmed the add
    const string WF_ADD_FORMULA = 'add_formula';
    // the id of the current add_formula workflow; increase it to add the next snapshot set
    const int WF_ADD_FORMULA_NBR = 14;

    // the change_formula workflow name used for the snapshot folder and the test subheader:
    // a formula field is changed in the edit form and written after the user has confirmed the change
    const string WF_CHANGE_FORMULA = 'change_formula';
    // the id of the current change_formula workflow; increase it to add the next snapshot set
    const int WF_CHANGE_FORMULA_NBR = 15;

    // the change_formula workflow name used for the snapshot folder and the test subheader:
    // a formula field is changed in the edit form and written after the user has confirmed the change
    const string WF_DEL_FORMULA = 'del_formula';
    const int WF_DEL_FORMULA_NBR = 16;

    // the change_word_all_sandbox_fields workflow name used for the snapshot folder and the test
    // subheader: a user that does not own the word (usr2) fills almost all sandbox fields of the
    // word in one edit round, so the confirmed change lands in a usr2 user sandbox overlay row
    const string WF_CHANGE_WORD_ALL_SANDBOX_FIELDS = 'change_word_all_sandbox_fields';
    // the id of the current change_word_all_sandbox_fields workflow; increase it to add the next snapshot set
    const int WF_CHANGE_WORD_ALL_SANDBOX_FIELDS_NBR = 17;

    /**
     * the user process step that a user reaction action triggers
     *
     * @param string $action the user reaction action const e.g. self::ACTION_SAVE
     * @return string the matching process step const e.g. self::STEP_CONFIRM
     */
    static function url_step(string $action): string
    {
        return match ($action) {
            self::SAVE,
            self::FILL => url_var::STEP_CONFIRM,
            self::CONFIRM,
            self::CONFIRMED => url_var::STEP_CONFIRMED,
            self::CANCEL => url_var::STEP_CANCEL,
            default => url_var::STEP_BASE // show, edit and back just navigate, they do not change the process step
        };
    }

    /**
     * true if the workflow action submits a form (save, fill and the confirmed actions), false for the
     * plain get navigation actions (show, edit, back, cancel). a form submit carries the named submit
     * button marker (url_var::POST_SUBMIT) that view.php needs to route the request through url_to_action
     *
     * @param string $step the user reaction action const e.g. workflows::SAVE
     * @return bool true if the step submits a form
     */
    static function is_form_submit(string $step): bool
    {
        return in_array($step, [
            self::SAVE,
            self::FILL,
            self::CONFIRM,
            self::CONFIRMED
        ], true);
    }

}