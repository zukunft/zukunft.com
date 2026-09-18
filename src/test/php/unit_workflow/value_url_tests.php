<?php

/*

    test/php/unit_workflow/value_url_tests.php - check the url based value user workflows
    ------------------------------------------

    snapshots the html of each step of the add value workflows: the phrases of the new value are
    selected one after the other in the pure html view, one of them can be removed again and the
    detailed add form can be opened at any time; the shared run state, the frontend setup and the
    snapshot helpers live in url_test_base (see docs/llm/testing.md)

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

namespace Zukunft\ZukunftCom\test\php\unit_workflow;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::MODEL_GROUP . 'group.php';
include_once paths::MODEL_PHRASE . 'phrase_list.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::MODEL_VALUE . 'value.php';
include_once paths::SHARED_CONST . 'groups.php';
include_once paths::SHARED_CONST . 'values.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED . 'url_var.php';
include_once test_paths::CONST . 'word_names.php';
include_once test_paths::CONST . 'workflows.php';
include_once test_paths::CREATE . 'test_values.php';
include_once test_paths::CREATE . 'test_words.php';
include_once test_paths::UNIT_WORKFLOW . 'url_test_base.php';

use Zukunft\ZukunftCom\main\php\cfg\group\group;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\value\value;
use Zukunft\ZukunftCom\main\php\shared\const\groups;
use Zukunft\ZukunftCom\main\php\shared\const\values;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\const\workflows;
use Zukunft\ZukunftCom\test\php\create\test_values;
use Zukunft\ZukunftCom\test\php\create\test_words;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class value_url_tests extends url_test_base
{

    function run(test_cleanup $t): void
    {

        // load the shared frontend run state and print the section header
        $this->init($t, 'value url->', 'url value ');

        // the workflow snapshots that chain url_to_action and url_to_html per user action
        $this->workflow_tests($t);

    }

    /**
     * run the four add value workflows that the pure html value add view supports
     *
     * @param test_cleanup $t the test environment
     */
    protected function workflow_tests(test_cleanup $t): void
    {
        $t->subheader($this->ts . 'workflow');

        $this->add_value_workflow(workflows::WF_ADD_VALUE_NBR);
        $this->add_value_with_phrase_workflow(workflows::WF_ADD_VALUE_WITH_PHRASE_NBR);
        $this->add_value_remove_phrase_workflow(workflows::WF_ADD_VALUE_REMOVE_PHRASE_NBR);
        $this->add_value_details_workflow(workflows::WF_ADD_VALUE_DETAILS_NBR);
        $this->change_value_group_workflow(workflows::WF_CHANGE_VALUE_GROUP_NBR);
    }

    /**
     * run the add_value workflow and snapshot the html after every user action
     *
     * the user opens the pure html add value view without any phrase, selects two phrases one after
     * the other and enters the number; the add button writes the value without a confirm view, so
     * this workflow has no confirm step (see ui_select::value_add_fields). snapshots go into
     * src/test/resources/web/html/workflow/add_value_wf<nbr>/ (see docs/llm/testing.md)
     *
     * @param int $wf_nbr the workflow id selecting the snapshot folder and file prefix e.g. 31 for wf31
     * @param bool $do_it false to only render the steps, true to also write the new value
     */
    protected function add_value_workflow(int $wf_nbr, bool $do_it = false): void
    {
        $this->wf_start($wf_nbr, workflows::WF_ADD_VALUE, $this->t->usr1, 0, $do_it);
        $this->set_phrase_norm_ids();

        $one = $this->phrase_id(word_names::TEST_ADD, word_names::TEST_ADD_ID);
        $two = $this->phrase_id(word_names::TEST_ADD_TO, word_names::TEST_ADD_TO_ID);
        $base = $this->add_value_url();

        // edit: open the add value view without any phrase
        $this->assert_step(workflows::EDIT, $base, views::VALUE_ADD_NO_JS_ID);

        // phrase: select the first phrase of the new value
        $this->assert_step(workflows::PHRASE,
            $this->phrase_url($base, '', $one), views::VALUE_ADD_NO_JS_ID);

        // phrase: with the first phrase chosen select the second phrase
        $this->assert_step(workflows::PHRASE,
            $this->phrase_url($base, (string)$one, $two), views::VALUE_ADD_NO_JS_ID);

        // save: enter the number and press add, which writes the value
        $this->assert_value_save($base, $one . ',' . $two);

        // a write run must actually create the value, so check it is now in the database
        if ($do_it) {
            $this->assert_value_in_db('add_value workflow has written the value', [$one, $two]);
        }
    }

    /**
     * run the add_value_with_phrase workflow and snapshot the html after every user action
     *
     * like add_value, but the view is opened with one phrase already preset, as the add value icon
     * of a word or triple page does (see ui_list::value_add_link), so the user only adds the two
     * missing phrases; snapshots go into workflow/add_value_with_phrase_wf<nbr>/
     *
     * @param int $wf_nbr the workflow id selecting the snapshot folder and file prefix
     * @param bool $do_it false to only render the steps, true to also write the new value
     */
    protected function add_value_with_phrase_workflow(int $wf_nbr, bool $do_it = false): void
    {
        $this->wf_start($wf_nbr, workflows::WF_ADD_VALUE_WITH_PHRASE, $this->t->usr1, 0, $do_it);
        $this->set_phrase_norm_ids();

        $city = word_names::CITY_ID;
        $one = $this->phrase_id(word_names::TEST_ADD, word_names::TEST_ADD_ID);
        $two = $this->phrase_id(word_names::TEST_ADD_TO, word_names::TEST_ADD_TO_ID);
        $base = $this->add_value_url($city);

        // edit: the add value icon of the phrase page opens the view with the shown phrase preset
        $this->assert_step(workflows::EDIT, $base, views::VALUE_ADD_NO_JS_ID);

        // phrase: add the first phrase beside the preset phrase
        $this->assert_step(workflows::PHRASE,
            $this->phrase_url($base, '', $one), views::VALUE_ADD_NO_JS_ID);

        // phrase: add the second phrase beside the preset phrase
        $this->assert_step(workflows::PHRASE,
            $this->phrase_url($base, $city . ',' . $one, $two), views::VALUE_ADD_NO_JS_ID);

        // save: enter the number and press add, which writes the value
        $this->assert_value_save($base, $city . ',' . $one . ',' . $two);

        if ($do_it) {
            $this->assert_value_in_db('add_value_with_phrase workflow has written the value',
                [$city, $one, $two]);
        }
    }

    /**
     * run the add_value_remove_phrase workflow and snapshot the html after every user action
     *
     * like add_value_with_phrase, but the user notices that one chosen phrase is wrong, removes it
     * with its remove icon (url_var::UNLINK_PHRASE) and selects another phrase instead, so the
     * written value has the corrected phrases; snapshots go into
     * workflow/add_value_remove_phrase_wf<nbr>/
     *
     * @param int $wf_nbr the workflow id selecting the snapshot folder and file prefix
     * @param bool $do_it false to only render the steps, true to also write the new value
     */
    protected function add_value_remove_phrase_workflow(int $wf_nbr, bool $do_it = false): void
    {
        $this->wf_start($wf_nbr, workflows::WF_ADD_VALUE_REMOVE_PHRASE, $this->t->usr1, 0, $do_it);
        $this->set_phrase_norm_ids();

        $city = word_names::CITY_ID;
        $canton = word_names::CANTON_ID;
        $one = $this->phrase_id(word_names::TEST_ADD, word_names::TEST_ADD_ID);
        $two = $this->phrase_id(word_names::TEST_ADD_TO, word_names::TEST_ADD_TO_ID);
        $base = $this->add_value_url($city);

        // edit: open the add value view with the preset phrase
        $this->assert_step(workflows::EDIT, $base, views::VALUE_ADD_NO_JS_ID);

        // phrase: add the phrase that the user removes again below
        $this->assert_step(workflows::PHRASE,
            $this->phrase_url($base, '', $one), views::VALUE_ADD_NO_JS_ID);

        // phrase: add the second phrase
        $this->assert_step(workflows::PHRASE,
            $this->phrase_url($base, $city . ',' . $one, $two), views::VALUE_ADD_NO_JS_ID);

        // remove: press the remove icon of the first added phrase
        $rem_url = $base;
        $rem_url[url_var::PHRASE_LIST] = $city . ',' . $one . ',' . $two;
        $rem_url[url_var::UNLINK_PHRASE] = $one;
        $this->assert_step(workflows::REMOVE, $rem_url, views::VALUE_ADD_NO_JS_ID);

        // phrase: select another phrase instead of the removed one
        $this->assert_step(workflows::PHRASE,
            $this->phrase_url($base, $city . ',' . $two, $canton), views::VALUE_ADD_NO_JS_ID);

        // save: enter the number and press add, which writes the value with the corrected phrases
        $this->assert_value_save($base, $city . ',' . $two . ',' . $canton);

        if ($do_it) {
            $this->assert_value_in_db('add_value_remove_phrase workflow has written the value',
                [$city, $two, $canton]);
        }
    }

    /**
     * run the add_value_details workflow and snapshot the html after every user action
     *
     * like add_value_with_phrase, but instead of entering the number the user opens the detailed add
     * value view with the more details link, which takes the phrases chosen so far with it, e.g. to
     * name the source of the value; nothing is written, so this workflow has no save step and no
     * database check; snapshots go into workflow/add_value_details_wf<nbr>/
     *
     * @param int $wf_nbr the workflow id selecting the snapshot folder and file prefix
     * @param bool $do_it false to only render the steps (this workflow never writes)
     */
    protected function add_value_details_workflow(int $wf_nbr, bool $do_it = false): void
    {
        $this->wf_start($wf_nbr, workflows::WF_ADD_VALUE_DETAILS, $this->t->usr1, 0, $do_it);
        $this->set_phrase_norm_ids();

        $city = word_names::CITY_ID;
        $one = $this->phrase_id(word_names::TEST_ADD, word_names::TEST_ADD_ID);
        $two = $this->phrase_id(word_names::TEST_ADD_TO, word_names::TEST_ADD_TO_ID);
        $base = $this->add_value_url($city);

        // edit: open the add value view with the preset phrase
        $this->assert_step(workflows::EDIT, $base, views::VALUE_ADD_NO_JS_ID);

        // phrase: add the first phrase beside the preset phrase
        $this->assert_step(workflows::PHRASE,
            $this->phrase_url($base, '', $one), views::VALUE_ADD_NO_JS_ID);

        // phrase: add the second phrase beside the preset phrase
        $this->assert_step(workflows::PHRASE,
            $this->phrase_url($base, $city . ',' . $one, $two), views::VALUE_ADD_NO_JS_ID);

        // details: the more details link opens the detailed add form with the chosen phrases
        $det_url = $base;
        $det_url[url_var::PHRASE_LIST] = $city . ',' . $one . ',' . $two;
        $this->assert_step(workflows::DETAILS, $det_url, views::VALUE_ADD_DETAIL_ID);
    }

    /**
     * run the change_value_group workflow and snapshot the html after every user action
     *
     * the user opens the value of the add_value workflow in the change value view, types a name
     * for its group and confirms the change, which writes the name to the group row (see
     * value_base::save); the value id is the group id of its phrases, so a read run computes the
     * fixed snapshot id from the fixed word ids; snapshots go into workflow/change_value_group_wf<nbr>/
     *
     * @param int $wf_nbr the workflow id selecting the snapshot folder and file prefix
     * @param bool $do_it false to only render the steps, true to also write the group name
     */
    protected function change_value_group_workflow(int $wf_nbr, bool $do_it = false): void
    {
        $fixed_id = $this->value_id([word_names::TEST_ADD_ID, word_names::TEST_ADD_TO_ID]);
        $this->wf_start($wf_nbr, workflows::WF_CHANGE_VALUE_GROUP, $this->t->usr1, $fixed_id, $do_it);
        $this->set_phrase_norm_ids();

        $one = $this->phrase_id(word_names::TEST_ADD, word_names::TEST_ADD_ID);
        $two = $this->phrase_id(word_names::TEST_ADD_TO, word_names::TEST_ADD_TO_ID);
        $this->wf_id = $this->value_id([$one, $two]);

        // the url carries the value as the add_value workflow has written it, because a read run
        // renders the value from the url without a backend call
        $url_arr = $this->add_value_url();
        $url_arr[url_var::ID] = $this->wf_id;
        $url_arr[url_var::PHRASE_LIST] = $one . ',' . $two;
        $url_arr[url_var::NUMERIC_VALUE] = values::SAMPLE_FLOAT;
        // fix the values before the change in the url TODO Prio 2 should be done by the process automatic
        $url_arr = $url_arr + html_base::pre_url_array($url_arr);
        $url_arr[url_var::BACK . url_var::MASK] = views::VALUE_DEFAULT_ID;
        $url_arr[url_var::BACK . url_var::ID] = $this->wf_id;

        // show: display the value in its default view
        $this->assert_step(workflows::SHOW, $url_arr, views::VALUE_DEFAULT_ID);

        // edit: open the change value view, which shows an empty group field and the phrases in the title
        $this->assert_step(workflows::EDIT, $url_arr, views::VALUE_EDIT_ID);

        // user is typing the name of the group
        $url_arr[url_var::GROUP_NAME] = groups::TN_VALUE_WORKFLOW;

        // save: press save on the edit form which shows the confirm change view with the group name
        $this->assert_step(workflows::SAVE, $url_arr, views::VALUE_EDIT_ID);

        // update_confirmed: confirm the pending change so the group name is written to the database
        $this->assert_step(workflows::CONFIRM, $url_arr, views::CONFIRM_EDIT_ID);

        // a write run must actually persist the group name, so check the group row in the database
        if ($do_it) {
            $this->assert_group_name_in_db('change_value_group workflow has named the group', [$one, $two]);
        }
    }

    /**
     * the id of the value with the given phrases, which is the group id of the phrases
     *
     * @param array $phr_ids the ids of the phrases of the value
     * @return int the value id, an int because the phrases of the test value are prime phrases
     */
    private function value_id(array $phr_ids): int
    {
        $phr_lst = new phrase_list($this->t->usr1);
        foreach ($phr_ids as $phr_id) {
            $phr_lst->add_id($phr_id);
        }
        return $phr_lst->get_grp_id(false)->id();
    }

    /**
     * check that the confirm step of a write run has really written the group name and remove the
     * group row again, because the value cleanup deletes the value but not its named group
     *
     * @param string $test_name the description of the assertion
     * @param array $phr_ids the phrase ids that name the group of the value
     */
    private function assert_group_name_in_db(string $test_name, array $phr_ids): void
    {
        $msg = new user_message(); // a buffer for the check load, asserted by the group below
        $grp = new group($this->t->usr1);
        $grp->load_by_id($this->value_id($phr_ids), $msg);
        $this->t->assert($test_name, $grp->name_given(), groups::TN_VALUE_WORKFLOW);
        // the change log of the group name must never point to the deleted group row
        $this->t->cleanup_change_log_group($grp,
            [groups::TN_VALUE_WORKFLOW, [word_names::TEST_ADD, word_names::TEST_ADD_TO]]);
        $grp->del($msg);
    }

    /**
     * the url that opens the pure html add value view, built from the value factory so that the
     * factory shows centrally which test objects this test uses (see docs/llm/testing.md)
     *
     * @param int $preset_id the phrase that the add value icon of a phrase page presets, 0 for none
     * @return array the url parameters of the empty add value form
     */
    private function add_value_url(int $preset_id = 0): array
    {
        $url_arr = new test_values($this->t)->value_new_url($this->msg);
        // add the previous page to the url
        $url_arr[url_var::BACK . url_var::MASK] = views::START_ID;
        if ($preset_id != 0) {
            $url_arr[url_var::PHRASE_LIST] = (string)$preset_id;
        }
        return $url_arr;
    }

    /**
     * the url of one phrase selection step: the phrases chosen so far, the phrase that the user has
     * selected and the refresh var that the icon beside the pattern field posts
     *
     * @param array $url_arr the url of the shown add value form
     * @param string $chosen_ids the ids of the phrases chosen so far, empty to keep the preset phrase
     * @param int $selected the id of the phrase that the user has selected
     * @return array the url parameters of the phrase selection step
     */
    private function phrase_url(array $url_arr, string $chosen_ids, int $selected): array
    {
        if ($chosen_ids != '') {
            $url_arr[url_var::PHRASE_LIST] = $chosen_ids;
        }
        $url_arr[url_var::PHRASE] = $selected;
        $url_arr[url_var::REFRESH] = url_var::REFRESH_PHRASES;
        return $url_arr;
    }

    /**
     * the last step of an add value workflow: the user enters the number and presses add, which
     * writes the value directly and shows it with its default view (see ui_select::value_add_fields)
     *
     * @param array $url_arr the url of the shown add value form
     * @param string $chosen_ids the ids of the phrases of the new value
     */
    private function assert_value_save(array $url_arr, string $chosen_ids): void
    {
        $url_arr[url_var::PHRASE_LIST] = $chosen_ids;
        $url_arr[url_var::NUMERIC_VALUE] = values::SAMPLE_FLOAT;
        $url_arr[url_var::BACK . url_var::MASK] = views::VALUE_DEFAULT_ID;
        // the form posts the mask of the pure add view itself, which is an add mask like the detailed one
        $this->assert_step(workflows::CONFIRMED, $url_arr, views::VALUE_ADD_NO_JS_ID);
    }

    /**
     * the database id of a reserved test word, or its fixed snapshot id if the word is not in the
     * database e.g. in a read-only run where no workflow has written it
     *
     * @param string $name the reserved name of the test word
     * @param int $fixed_id the fixed test id used in the snapshot files
     * @return int the id of the phrase of the test word
     */
    private function phrase_id(string $name, int $fixed_id): int
    {
        return new test_words($this->t)->word_id_or_fixed($name, $fixed_id);
    }

    /**
     * set the additional snapshot id normalization for the reserved test words used as phrases of
     * the new value, so a write run snapshot shows the fixed test ids instead of the dynamic ids
     * (the ids of the base words like city are pinned, so they need no normalization)
     */
    private function set_phrase_norm_ids(): void
    {
        $this->wf_norm_ids = [
            $this->phrase_id(word_names::TEST_ADD, word_names::TEST_ADD_ID)
            => word_names::TEST_ADD_ID,
            $this->phrase_id(word_names::TEST_ADD_TO, word_names::TEST_ADD_TO_ID)
            => word_names::TEST_ADD_TO_ID,
        ];
    }

    /**
     * check that the add step of a write run has really written the value and remember the new value
     * so that the test cleanup removes it again (mirrors word_url_tests::assert_word_in_db)
     *
     * @param string $test_name the description of the assertion
     * @param array $phr_ids the phrase ids that name the group of the new value
     */
    private function assert_value_in_db(string $test_name, array $phr_ids): void
    {
        $msg = new user_message(); // a buffer for the check load, asserted by the value below
        $val = new value($this->t->usr1);
        $val->load_by_phr_ids($phr_ids, $msg);
        // the number is compared as text, because assert takes no float
        $this->t->assert($test_name, (string)$val->number(), (string)values::SAMPLE_FLOAT);
        // remember the added value so that the test cleanup deletes it again
        $this->t->test_val_ids[] = $val->id();
    }

}
