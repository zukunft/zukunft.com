<?php

/*

    test/php/unit_read/phrase_list.php - database unit testing of the phrase list functions
    ----------------------------------


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

namespace Zukunft\ZukunftCom\test\php\unit_read;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::MODEL_CONST . 'def.php';
include_once paths::SHARED . 'library.php';
include_once paths::SHARED_CONST . 'formulas.php';
include_once paths::SHARED_ENUM . 'foaf_direction.php';
include_once paths::SHARED_CONST . 'triples.php';
include_once paths::SHARED_CONST . 'words.php';
include_once test_paths::CONST . 'files.php';

use Zukunft\ZukunftCom\main\php\cfg\const\def;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\word\triple_list;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\test\php\const\files as test_files;
use Zukunft\ZukunftCom\test\php\create\test_verbs;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phr_ids;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\shared\enum\foaf_direction;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\test\php\const\formula_names;
use Zukunft\ZukunftCom\test\php\const\triple_names;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\create\test_db_load;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class phrase_list_read_tests
{

    function run(test_cleanup $t): void
    {
        $msg = new user_message();


        // init
        $t_db = new test_db_load($t);
        $t->name = 'phrase list_read db->';
        $t->resource_path = 'db/phrase/';

        // start the test section (ts)
        $ts = 'db read phrase list ';
        $t->header($ts);

        $t->subheader($ts . 'load');

        $test_name = 'loading phrase names with pattern return the expected word';
        $lst = new phrase_list($t->usr1);
        $pattern = substr(word_names::MATH, 0, -1);
        $lst->load_names($pattern, $msg);
        $t->assert_contains($test_name, $lst->names(), word_names::MATH);
        $test_name = 'loading phrase names with pattern return the expected triple';
        $lst = new phrase_list($t->usr1);
        $pattern = substr(triple_names::MATH_CONST, 0, -1);
        $lst->load_names($pattern, $msg);
        $t->assert_contains($test_name, $lst->names(), triple_names::MATH_CONST);
        $test_name = 'formula names are not included in the normal phrase list';
        $lst = new phrase_list($t->usr1);
        $lst->load_names(formula_names::SCALE_TO_SEC, $msg);
        // TODO Prio 1 activate
        //$t->assert_contains_not($test_name, $lst->names(), formulas::TN_READ);
        $test_name = 'api message of phrases list';
        $lst = new phrase_list($t->usr1);
        $id_lst = [1, 2, 3, -1, -2];
        $lst->load_names_by_ids((new phr_ids($id_lst)), $msg);
        $result = $lst->obj_id_lst();
        $t->assert_contains($test_name, $result, $id_lst);
        $result = json_encode($result);
        $t->assert_text_contains($test_name, $result, '1');
        $test_name = 'Switzerland is part of the phrase list staring with S';
        $switzerland = new phrase($t->usr1);
        $switzerland->load_by_name(words::CH, $msg);
        $lst->load_like('S', $msg);
        $t->assert_contains($test_name, $lst->names(), words::CH);
        // the phrase select of e.g. the value add form sends the typed chars to the phrase list
        // api, which reads the matching words and triples with load_like (see ui_select::phrase_matches
        // and api/phraseList), so the list of a typed start is compared with a fixed list of the
        // phrases of the seed that start with it, in the order of the names
        $test_name = 'the phrases starting with "' . word_names::MATH_PATTERN
            . '" are the constant triple and the word';
        $lst = new phrase_list($t->usr1);
        $lst->load_like(word_names::MATH_PATTERN, $msg);
        $t->assert($test_name, $lst->names(), [triple_names::MATH_CONST, word_names::MATH]);

        // before anything is typed the phrase select offers the base phrases, which the frontend
        // knows by name and id without a backend call (see phrase_list_ui::load_fallback), so the
        // same phrases are read from the database and compared with the frontend constants and
        // saved as a fixed list, to detect a seed change that would let the select offer a wrong phrase
        $test_name = 'the base phrases offered by the phrase select match the frontend constants';
        $base_ids = [];
        $expected_names = [];
        foreach (words::BASE_WORDS as $wrd_array) {
            $base_ids[] = $wrd_array[1];
            $expected_names[$wrd_array[1]] = $wrd_array[0];
        }
        foreach (triples::BASE_TRIPLES as $trp_array) {
            $base_ids[] = $trp_array[1] * -1;
            $expected_names[$trp_array[1] * -1] = $trp_array[0];
        }
        $lst = new phrase_list($t->usr1);
        $lst->load_names_by_ids(new phr_ids($base_ids), $msg);
        ksort($expected_names);
        $actual_names = $this->names_by_id($lst);
        $t->assert($test_name, $actual_names, $expected_names);
        $this->assert_names_file($t, '... and are the saved fixed list', $actual_names, 'list_datalist.csv');

        // the phrase selects and pages that list the problems and solutions of the start page
        // have lost e.g. "global warming (global problem)", so the reads behind these pages are
        // repeated here and each list is saved, to see if the rows are read from the database
        $t->subheader($ts . 'global problems');
        // read by id (each page names a phrase by its id)
        $test_name = 'the problem and the solution triple of the start page are read by their ids';
        $lst = new phrase_list($t->usr1);
        $lst->load_names_by_ids(new phr_ids([
            triple_names::GLOBAL_WARMING_PROBLEM_ID * -1,
            triple_names::REDUCE_EMISSIONS_SOLUTION_ID * -1]), $msg);
        $t->assert($test_name, $this->names_by_id($lst), [
            triple_names::REDUCE_EMISSIONS_SOLUTION_ID * -1 => triple_names::REDUCE_EMISSIONS_SOLUTION,
            triple_names::GLOBAL_WARMING_PROBLEM_ID * -1 => triple_names::GLOBAL_WARMING_PROBLEM]);
        // read as the children of the page phrase (see ui_list::start_page_phrase, which asks the
        // phrase list api for the phrases linked to "global problem" downwards)
        $test_name = 'the phrases linked to "global problem" contain the global warming problem';
        $phr = new phrase($t->usr1);
        $phr->load_by_name(triple_names::GLOBAL_PROBLEM, $msg);
        $lst = new phrase_list($t->usr1);
        $lst->load_by_phr($phr, $msg, null, foaf_direction::DOWN);
        $t->assert_contains($test_name, $lst->names(), triple_names::GLOBAL_WARMING_PROBLEM);
        $this->assert_names_file($t, '... and are the saved fixed list',
            $this->names_by_id($lst), 'list_global_problem_children.csv');
        // read by the verb (the verb page lists the triples that use the verb)
        $test_name = 'the triples with the verb "is a" contain the global warming problem';
        $t_vrb = new test_verbs($t);
        $trp_lst = new triple_list($t->usr1);
        $trp_lst->load_by_verb($t_vrb->verb_is(), $msg, false, sql_db::ROW_MAX);
        $t->assert_contains($test_name, $trp_lst->names(), triple_names::GLOBAL_WARMING_PROBLEM);
        $names_by_id = [];
        foreach ($trp_lst->lst() as $trp) {
            $names_by_id[$trp->id() * -1] = $trp->name();
        }
        ksort($names_by_id);
        $this->assert_names_file($t, '... and are the saved fixed list', $names_by_id, 'list_is_a.csv');


        $t->subheader($ts . 'get related');

        // direct children
        $test_name = 'Switzerland is a country';
        $country = new phrase($t->usr1);
        $country->load_by_name(words::COUNTRY, $msg);
        $country_lst = $country->direct_children($msg);
        $t->assert_contains($test_name, $country_lst->names(), words::CH);
        $test_name = 'Zurich is a country (even if it is part of a country)';
        $zurich = new phrase($t->usr1);
        $zurich->load_by_name(word_names::ZH, $msg);
        $t->assert_contains_not($test_name, $country_lst->names(), word_names::ZH);
        $test_name = 'The word country is not part of the country list';
        $t->assert_contains_not($test_name, $country_lst->names(), words::COUNTRY);

        // all children
        $test_name = 'The default number of forecast years is a system configuration parameter';
        global $cfg;
        $auto_years = $cfg->get_by([triples::AUTOMATIC_CREATE, words::YEAR], def::FALLBACK_RETRY);
        $t->assert_greater($test_name, 0, $auto_years);

        // canton is related to Switzerland and Zurich
        $phr_canton = $t_db->load_phrase(word_names::CANTON, $msg);
        $phr_lst = $phr_canton->all_related($msg);
        $test_name = 'The word canton is related to Switzerland and Zurich';
        // TODO ABB is not expected to be related even if it is related via zurich and company
        //      but Switzerland is expected to be related
        //$t->assert_contains($test_name, $phr_lst->names(), array(words::TN_ZH, words::TN_CH));


        $t->subheader($ts . 'linked sides');

        // a list load fills the from and to of each link with the id and the name only, so a
        // triple nested as the from of a link carries no from and to of its own; the frontend
        // needs them e.g. to match the parts of the "potential loss" column, so load_linked_sides
        // adds them with one read; the column definitions of the mayor tier are such links,
        // because the "column potential loss" definition is built from the triple "potential loss"
        $lst = new phrase_list($t->usr1);
        $tier = new phrase($t->usr1);
        $tier->set_obj_from_id(triple_names::SYSTEM_COLUMN_MAYOR_ID * -1);
        $lst->load_by_phr($tier, $msg, null, foaf_direction::DOWN);
        $col_phr = $lst->get_by_name(triple_names::COLUMN_POTENTIAL_LOSS, $msg);
        $test_name = 'the column definitions of a tier are loaded';
        $t->assert_text_contains($test_name, $col_phr?->name() ?? '',
            triple_names::COLUMN_POTENTIAL_LOSS);
        // a triple always creates its from and to phrase (see triple create_objects), so a nested
        // triple that has not been loaded names them with an empty string instead of a null
        $test_name = 'a triple nested in a link has no from before the sides are loaded';
        $t->assert($test_name, $col_phr?->obj()?->get_from()?->obj()?->get_from()?->name(), '');
        $test_name = '... and the from of the nested triple after load_linked_sides';
        $lst->load_linked_sides($msg);
        $col_phr = $lst->get_by_name(triple_names::COLUMN_POTENTIAL_LOSS, $msg);
        $t->assert($test_name,
            $col_phr?->obj()?->get_from()?->obj()?->get_from()?->name(), word_names::LOSS);


        $t->subheader($ts . 'categories');

        // the phrase that names the pi value is the triple "Pi is a mathematical constant", so
        // the triple itself is the link to the category and nothing links away from it, which is
        // why is() finds nothing and categories() has to read the target of the triple
        $lst = new phrase_list($t->usr1);
        $pi = new phrase($t->usr1);
        $pi->set_obj_from_id(triple_names::PI_ID * -1);
        $lst->add($pi);

        $test_name = 'an is-a triple has no category by following the links up';
        $t->assert($test_name, $lst->is($msg)->names(), []);

        $test_name = '... but the target of the triple is its category';
        $t->assert_contains($test_name, $lst->categories($msg)->names(), triple_names::MATH_CONST);

        // the symbol triple links with "is symbol for" and not with "is a", so its target is the
        // named number and not a category, which the verb check keeps out of the list
        $lst_sym = new phrase_list($t->usr1);
        $e_sym = new phrase($t->usr1);
        $e_sym->set_obj_from_id(triple_names::E_ID * -1);
        $lst_sym->add($e_sym);

        $test_name = 'a triple with another verb than is a has no category';
        $t->assert($test_name, $lst_sym->categories($msg)->names(), []);

        // the members of a category are the triples that link to it, because a value is keyed by
        // that triple (the pi value by "Pi (math)") and not by the linked word "Pi"
        $test_name = 'the members of the category of pi are the math constant triples';
        $t->assert_contains($test_name, $lst->categories($msg)->category_members($msg)->names(),
            [triple_names::PI, triple_names::E_NUM]);

        // nothing is linked to the symbol triple as its category, so it has no member but itself
        $test_name = 'a phrase that is no category has no members but itself';
        $t->assert($test_name, $lst_sym->category_members($msg)->count(), 1);

    }

    /**
     * @param phrase_list $lst the loaded phrases
     * @return array the phrase names by the phrase id, sorted by the id, so that a compare does not
     *               depend on the order of the load
     */
    private function names_by_id(phrase_list $lst): array
    {
        $result = [];
        foreach ($lst->lst() as $phr) {
            $result[$phr->id()] = $phr->name();
        }
        ksort($result);
        return $result;
    }

    /**
     * compare a list of phrase names by id with the saved expected list in the phrase resources
     * a list that is checked for the first time has no expected file yet, which is created if the
     * auto update of the test files is on and reported like a missing csv of the fixed rows
     *
     * @param test_cleanup $t the test object that includes the test results collected until now
     * @param string $test_name the name of the test shown in the result
     * @param array $names_by_id the phrase names by the phrase id
     * @param string $file_name the name of the expected csv file in the phrase resource folder
     * @return void
     */
    private function assert_names_file(test_cleanup $t, string $test_name, array $names_by_id, string $file_name): void
    {
        $lib = new library();
        $csv_lines = [$lib->csv_line(['phrase_id', 'phrase_name'])];
        foreach ($names_by_id as $id => $name) {
            $csv_lines[] = $lib->csv_line([$id, $name]);
        }
        $csv_text = implode("\n", $csv_lines) . "\n";
        $csv_file_path = test_paths::UNIT_RES . 'phrase' . DIRECTORY_SEPARATOR . $file_name;
        if (file_exists($csv_file_path)) {
            $t->assert_file($test_name, $csv_text, $csv_file_path);
        } else {
            $t->assert_true($test_name . ' and the expected file exists', false);
            if (test_files::AUTO_UPDATE_TEST_FILES) {
                $t->update_path_file($csv_file_path, $csv_text);
            }
        }
    }

}

