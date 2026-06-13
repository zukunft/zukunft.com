<?php

/*

    test/php/unit_write/xbrl_write_tests.php - test the database import of a converted XBRL fileset
    ----------------------------------------

    unpacks the XBRL fileset zip, creates the import json in the fileset folder,
    imports the json into the database and checks that the values can be loaded


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

namespace Zukunft\ZukunftCom\test\php\unit_write;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::MODEL_IMPORT . 'import_convert_xbrl.php';
include_once paths::MODEL_IMPORT . 'import_file.php';
include_once test_paths::CONST . 'files.php';
include_once test_paths::UTILS . 'test_base.php';

use Zukunft\ZukunftCom\main\php\cfg\formula\formula;
use Zukunft\ZukunftCom\main\php\cfg\import\import_convert_xbrl;
use Zukunft\ZukunftCom\main\php\cfg\import\import_file;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\cfg\ref\source;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\value\value;
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\test\php\const\files as test_files;
use Zukunft\ZukunftCom\test\php\utils\test_base;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class xbrl_write_tests
{

    function run(test_cleanup $t): void
    {

        // init
        $usr_msg = new user_message($t->usr1);

        // start the test section (ts)
        $ts = 'db write xbrl ';
        $t->header($ts);

        // unpack the fileset and create the import json in the fileset folder
        $test_name = 'create the import json from the XBRL fileset';
        $conv_xbrl = new import_convert_xbrl;
        $folder = $conv_xbrl->unzip(
            test_files::IMPORT_XBRL_ABB_2013_ZIP,
            test_paths::IMPORT_XBRL,
            ''
        );
        $json_path = $conv_xbrl->convert_folder_to_file($folder, $conv_xbrl->instance_file_name('2013'), test_base::TEST_TIMESTAMP, $usr_msg);
        $t->assert($test_name, $json_path, test_files::IMPORT_XBRL_ABB_2013);

        // import the created json file into the database
        $test_name = 'import the XBRL fileset json';
        $json_array = json_decode(file_get_contents($json_path), true);
        $imf = new import_file();
        // TODO Prio 0 activate
        //$imp_msg = $imf->json_file($json_path, $t->usr1, false);
        //$t->assert_true($test_name . ' ' . $imp_msg->all_message_text(), $imp_msg->is_ok());

        // check that the imported values can be loaded from the database
        $test_name = 'load the imported total sales value';
        $total_json = end($json_array[json_fields::VALUES]);
        // TODO Prio 0 activate
        //$val = $this->load_value_by_names($t, $total_json[json_fields::WORDS]);
        //$t->assert($test_name, $val->number(), floatval($total_json[json_fields::NUMBER]));

        // remove the imported test data so that the database stays unchanged
        //$this->cleanup($t, $json_array, $usr_msg);
    }

    /**
     * load a triple by the given or by the generated name
     * because the import may fill either of the name fields
     *
     * @param test_cleanup $t the test environment with the test user
     * @param string $trp_name the given or derived name of the triple
     * @return triple the loaded triple with the id zero if not found
     */
    private function load_triple_by_any_name(test_cleanup $t, string $trp_name): triple
    {
        $trp = new triple($t->usr1);
        $trp->load_by_name($trp_name);
        if ($trp->id() == 0) {
            $trp->load_by_name_generated($trp_name);
        }
        return $trp;
    }

    /**
     * the name of an import triple as derived by the importer
     * e.g. "Power Products (sector)" for "Power Products" "is a" "sector"
     *
     * @param array $trp_json the import json of one triple
     * @return string the given or derived name of the triple
     */
    private function triple_name(array $trp_json): string
    {
        if (key_exists(json_fields::NAME, $trp_json)) {
            $result = $trp_json[json_fields::NAME];
        } elseif ($trp_json[json_fields::EX_VERB] == import_convert_xbrl::VERB_IS_A) {
            $result = $trp_json[json_fields::EX_FROM] . ' (' . $trp_json[json_fields::EX_TO] . ')';
        } else {
            $result = $trp_json[json_fields::EX_FROM]
                . ' ' . $trp_json[json_fields::EX_VERB]
                . ' ' . $trp_json[json_fields::EX_TO];
        }
        return $result;
    }

    /**
     * load a value from the database by the phrase names of the import json
     *
     * @param test_cleanup $t the test environment with the test user
     * @param array $names the phrase names that select the value
     * @return value the loaded value
     */
    private function load_value_by_names(test_cleanup $t, array $names): value
    {
        $phr_lst = new phrase_list($t->usr1);
        $phr_lst->load_by_names(array_unique($names));
        $val = new value($t->usr1);
        $val->load_by_grp($phr_lst->get_grp_id());
        return $val;
    }

    /**
     * remove the data imported from the XBRL fileset json
     * only the words and triples that the test user owns are removed
     * so that the base words like the year, the currency and the scale
     * that are owned by the system users are kept
     * leftovers of a previous incomplete cleanup are removed as well
     * because the owner check selects them again
     *
     * @param test_cleanup $t the test environment with the test user
     * @param array $json_array the imported json as array to know what has been added
     * @param user_message $usr_msg to collect problems of the cleanup
     * @return void
     */
    private function cleanup(test_cleanup $t, array $json_array, user_message $usr_msg): void
    {
        $test_name = 'cleanup the imported XBRL data';

        // remove the values
        foreach ($json_array[json_fields::VALUES] as $val_json) {
            $val = $this->load_value_by_names($t, $val_json[json_fields::WORDS]);
            if (!$val->is_id_set()) {
                continue;
            }
            $val->del($usr_msg);
        }

        // remove the formula
        foreach ($json_array[json_fields::FORMULAS] as $frm_json) {
            $frm = new formula($t->usr1);
            $frm->load_by_name($frm_json[json_fields::NAME]);
            if ($frm->id() > 0) {
                $frm->del($usr_msg);
            }
        }

        // remove the source
        foreach ($json_array[json_fields::SOURCES] as $src_json) {
            $src = new source($t->usr1);
            $src->load_by_name($src_json[json_fields::NAME]);
            if ($src->id() > 0) {
                $src->del($usr_msg);
            }
        }

        // remove the triples added by the import before the words
        // because a triple may use a word that should be removed
        foreach ($json_array[json_fields::TRIPLES] as $trp_json) {
            $trp = $this->load_triple_by_any_name($t, $this->triple_name($trp_json));
            if ($trp->id() != 0 and $trp->owner_id() == $t->usr1->id()) {
                $trp->del($usr_msg);
            }
        }

        // remove the words added by the import
        foreach ($json_array[json_fields::WORDS] as $wrd_json) {
            $wrd = new word($t->usr1);
            $wrd->load_by_name($wrd_json[json_fields::NAME]);
            if ($wrd->id() > 0 and $wrd->owner_id() == $t->usr1->id()) {
                $wrd->del($usr_msg);
            }
        }

        $t->assert_true($test_name . ' ' . $usr_msg->all_message_text(), $usr_msg->is_ok());
    }

}