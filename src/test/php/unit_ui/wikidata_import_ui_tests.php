<?php

/*

    test/php/unit_ui/wikidata_import_ui_tests.php - the html of the preview of the import of the values of wikidata
    ---------------------------------------------

    the preview is rendered without a backend: the import json is the offline convert of the
    population of Zurich (see unit/wikidata_tests.php) and the comparison with the pod, which the
    backend does (see cfg/import/import_wikidata_preview.php), is given by hand, one value per case


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

namespace Zukunft\ZukunftCom\test\php\unit_ui;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::MODEL_IMPORT . 'import_wikidata.php';
include_once paths::MODEL_IMPORT . 'import_wikidata_preview.php';
include_once html_paths::EXECUTE . 'system_page.php';
include_once html_paths::SYSTEM . 'wikidata_import.php';
include_once test_paths::UNIT . 'wikidata_tests.php';

use Zukunft\ZukunftCom\main\php\cfg\import\import_wikidata;
use Zukunft\ZukunftCom\main\php\cfg\import\import_wikidata_preview;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message as backend_user_message;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\web\component\execute\system_page;
use Zukunft\ZukunftCom\main\php\web\system\wikidata_import;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\test\php\const\files as test_files;
use Zukunft\ZukunftCom\test\php\unit\wikidata_tests;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class wikidata_import_ui_tests
{

    // the number of the pod that differs from wikidata in the conflict of the test preview
    const string NUMBER_OLD = '400000';
    // a word name of wikidata with html, which the preview must show as text
    const string WORD_WITH_HTML = '<b>Zurich</b>';
    const string WORD_WITH_HTML_ESCAPED = '&lt;b&gt;Zurich&lt;/b&gt;';

    function run(test_cleanup $t): void
    {
        global $mtr;

        // start the test section (ts)
        $ts = 'unit ui html wikidata import ';
        $t->header($ts);
        $page = new system_page();

        $t->subheader($ts . 'preview');
        // the refresh icon of the wikidata reference of Zurich (city) opens the preview of the phrase
        $url = [
            url_var::MASK => views::IMPORT_PREVIEW_ID,
            url_var::PHRASE => -1,
            url_var::BACK . url_var::MASK => views::TRIPLE_ID,
            url_var::BACK . url_var::ID => 1,
        ];
        $msg = new user_message();
        $imp = new wikidata_import($this->preview_of_zurich());
        $html = $page->import_preview($msg, $url, true, $imp);
        $t->html_page_test($html, 'import preview', 'import_preview_wikidata', $msg);

        $test_name = 'the confirm of the preview writes the import';
        $action = url_var::ACTION . '" value="' . url_var::ACTION_IMPORT;
        $t->assert_text_contains($test_name, $html, $action);

        // the confirm form is sent as a get request, so it carries only the checksum of the import json
        $test_name = '... of the import json that the user has seen';
        $t->assert_text_contains($test_name, $html, $imp->checksum());

        $test_name = '... and returns to the page of the phrase';
        $t->assert_text_contains($test_name, $html, url_var::BACK . url_var::MASK . '" value="' . views::TRIPLE_ID);

        $test_name = 'a conflict with an own value of the user is imported with the word wikidata';
        $vars = [msg_id::VAR_NAME => import_wikidata_preview::CONFLICT_WORD];
        $with_word = new library()->msg_var_text([[msg_id::IMPORT_PREVIEW_WITH_WORD, $vars]], $mtr);
        $t->assert_text_contains($test_name, $html, htmlspecialchars($with_word, ENT_QUOTES));

        // negative: the names come from wikidata, so they are never html of the page
        $test_name = 'a word of wikidata with html is shown as text';
        $preview = $this->preview_of_zurich();
        $preview[json_fields::PREVIEW_WORDS_NEW] = [self::WORD_WITH_HTML];
        $html_escaped = new wikidata_import($preview)->html($url, $msg);
        $t->assert_text_contains($test_name, $html_escaped, self::WORD_WITH_HTML_ESCAPED);

        $test_name = '... and never as html';
        $t->assert_text_not_contains($test_name, $html_escaped, self::WORD_WITH_HTML);

        // negative: if all values of wikidata are in the pod, nothing is to confirm
        $test_name = 'a preview without new values and conflicts has no confirm';
        $preview = $this->preview_of_zurich();
        $preview[json_fields::PREVIEW_VALUES_NEW] = [];
        $preview[json_fields::PREVIEW_VALUES_CONFLICT] = [];
        $in_line_msg = new user_message();
        $html_in_line = new wikidata_import($preview)->html($url, $in_line_msg);
        $t->assert_text_not_contains($test_name, $html_in_line, $action);

        $test_name = '... but the info that the values are in line with wikidata';
        $vars = [msg_id::VAR_NAME => wikidata_tests::ZURICH_CITY, msg_id::VAR_ID => wikidata_tests::ZURICH_KEY];
        $expected = new library()->msg_var_text([[msg_id::WIKIDATA_IN_LINE, $vars]], $mtr);
        $t->assert($test_name, $in_line_msg->text(), $expected);

        $t->subheader($ts . 'admin');
        // the admin view "Import from Wikidata" opens the same preview with a wikidata id
        $test_name = 'the admin enters the wikidata id of the entity to import';
        $form = $page->wikidata_key_form();
        $t->assert_text_contains($test_name, $form, url_var::EXTERNAL_KEY);

        $test_name = '... and sees the import preview';
        $t->assert_text_contains($test_name, $form, url_var::MASK . '" value="' . views::IMPORT_PREVIEW_ID);

        // negative: the form only opens the preview, so it is no action that writes
        $test_name = '... which writes nothing';
        $t->assert_text_not_contains($test_name, $form, 'name="' . url_var::POST_SUBMIT . '"');
    }

    /**
     * the preview of the population of Zurich as the backend would answer it: the import json is the offline
     * convert with the phrases of the pod, and the first value is new, the second in line and the third a conflict
     * with an own value of the user
     *
     * @return array the preview by the json_fields::PREVIEW_* keys (see import_wikidata_preview::compared)
     */
    private function preview_of_zurich(): array
    {
        $imp = new import_wikidata(test_paths::IMPORT_WIKIDATA_UNIT);
        $msg = new backend_user_message();
        $entity_json = file_get_contents(test_files::IMPORT_WIKIDATA_UNIT_Q72);
        $prp_json = import_wikidata::property_json(wikidata_tests::POPULATION_PROPERTY);
        $import = $imp->convert($entity_json, $prp_json, wikidata_tests::POD_ZURICH_POPULATION, [], $msg);
        [$new, $same, $conflict] = $import[json_fields::VALUES];
        $import_value = $conflict;
        $import_value[json_fields::WORDS][] = import_wikidata_preview::CONFLICT_WORD;
        $conflict += [
            json_fields::PREVIEW_NUMBER_OLD => self::NUMBER_OLD,
            json_fields::PREVIEW_CONFLICT_WORD => import_wikidata_preview::CONFLICT_WORD,
            json_fields::PREVIEW_IMPORT_VALUE => $import_value,
        ];
        $import[json_fields::VALUES] = [$new, $import_value];
        return [
            json_fields::NAME => wikidata_tests::ZURICH_CITY,
            json_fields::EXTERNAL_KEY => wikidata_tests::ZURICH_KEY,
            json_fields::PREVIEW_WORDS_NEW => array_column($import[json_fields::WORDS], json_fields::NAME),
            json_fields::PREVIEW_TRIPLES_NEW => [],
            json_fields::PREVIEW_VALUES_NEW => [$new],
            json_fields::PREVIEW_VALUES_SAME => [$same],
            json_fields::PREVIEW_VALUES_CONFLICT => [$conflict],
            json_fields::PREVIEW_PROBLEMS => '',
            json_fields::PREVIEW_IMPORT => $import,
        ];
    }
}
