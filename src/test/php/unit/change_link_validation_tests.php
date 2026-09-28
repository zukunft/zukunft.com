<?php

/*

    test/unit/change_link_validation_tests.php - unit testing of the check of the renumbered change tables and fields
    ------------------------------------------


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

namespace Zukunft\ZukunftCom\test\php\unit;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::MODEL_LOG . 'change_field.php';
include_once paths::MODEL_LOG . 'change_table.php';
include_once paths::SHARED . 'library.php';
include_once paths::SHARED_CONST_FIELDS . 'fields.php';
include_once paths::SHARED_ENUM . 'change_fields.php';
include_once paths::SHARED_ENUM . 'change_tables.php';
include_once test_paths::CONST . 'files.php';
include_once test_paths::UTILS . 'change_link_validation.php';
include_once test_paths::UTILS . 'test_cleanup.php';

use Zukunft\ZukunftCom\main\php\cfg\log\change_field;
use Zukunft\ZukunftCom\main\php\cfg\log\change_table;
use Zukunft\ZukunftCom\main\php\shared\const\fields\fields;
use Zukunft\ZukunftCom\main\php\shared\enum\change_fields;
use Zukunft\ZukunftCom\main\php\shared\enum\change_tables;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\test\php\const\files as test_files;
use Zukunft\ZukunftCom\test\php\utils\change_link_validation;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class change_link_validation_tests
{
    function run(test_cleanup $t): void
    {

        // init
        $lib = new library();
        $lnk_chk = new change_link_validation();
        $t->name = 'change_link_validation->';

        $ts = 'unit change link validation ';
        $t->header($ts);

        $t->subheader($ts . 'check');
        $test_name = 'renumbered tables and fields keep all rows and table links';
        $t->assert($test_name, $lnk_chk->check(), []);
        $test_name = 'an unchanged text ref file is not rewritten';
        $file = $lib->class_csv_file_path(change_field::class, test_files::FIXED_DB_SORTED_TEXT_REF_CSV);
        $old_time = filemtime($file) - 1;
        touch($file, $old_time);
        $lnk_chk->check();
        clearstatcache(true, $file);
        $t->assert($test_name, (string)filemtime($file), (string)$old_time);

        $t->subheader($ts . 'read csv');
        $test_name = 'an empty line is skipped and a quoted comma stays in the value';
        $file = $lib->class_csv_file_path(change_field::class, test_files::FIXED_DB_BROKEN_CSV);
        $findings = [];
        $rows = $lnk_chk->read_csv($file, $findings);
        $t->assert($test_name, array_column($rows, fields::FLD_DESCRIPTION), ['', 'the plural, if it is not the word with an s']);
        $test_name = 'a line with fewer values than the header is reported';
        $findings = [];
        $lnk_chk->read_csv($file, $findings);
        $t->assert($test_name, $findings, ['line 5 of ' . $file . ' does not match the header: 3,27']);
        $test_name = 'a missing file is reported';
        $file = $lib->class_csv_file_path(change_field::class, test_files::FIXED_DB_MISSING_CSV);
        $findings = [];
        $rows = $lnk_chk->read_csv($file, $findings);
        $t->assert($test_name, [$rows, $findings], [[], ['cannot read ' . $file]]);

        $t->subheader($ts . 'text ref');
        $test_name = 'the table id of a field is replaced by the table name';
        $tbl_rows = [[change_table::FLD_ID => change_tables::USER_ID, change_table::FLD_NAME => change_tables::USER]];
        $fld_rows = [[
            change_field::FLD_ID => change_fields::FLD_WORD_NAME_ID,
            change_field::FLD_TABLE => change_tables::USER_ID,
            change_field::FLD_NAME => change_fields::FLD_WORD_NAME,
        ]];
        $findings = [];
        $lines = $lnk_chk->text_ref_lines($fld_rows, $tbl_rows, $findings);
        $t->assert($test_name, [$lines, $findings], [[change_tables::USER . ',' . change_fields::FLD_WORD_NAME . ',,'], []]);
        $test_name = 'a field whose table id is unknown is reported';
        $findings = [];
        $lines = $lnk_chk->text_ref_lines($fld_rows, [], $findings);
        $target = 'unknown table id "' . change_tables::USER_ID . '" of change field ' . change_fields::FLD_WORD_NAME_ID . ',' . change_fields::FLD_WORD_NAME;
        $t->assert($test_name, [$lines, $findings], [[',' . change_fields::FLD_WORD_NAME . ',,'], [$target]]);

        $t->subheader($ts . 'diff');
        $usr_line = change_tables::USER . ',' . change_fields::FLD_USER_ID . ',,';
        $wrd_line = change_tables::WORD . ',' . change_fields::FLD_WORD_NAME . ',,';
        $trp_line = change_tables::TRIPLE . ',' . change_fields::FLD_WORD_NAME . ',,';
        $fld_name = $lib->class_to_name(change_field::class);
        $test_name = 'the same lines in another order are no difference';
        $result = $lnk_chk->diff(change_field::class, [$usr_line, $wrd_line], [$wrd_line, $usr_line]);
        $t->assert($test_name, $result, []);
        $test_name = 'a missing, a lost repeat and an added line are reported';
        $result = $lnk_chk->diff(change_field::class, [$usr_line, $wrd_line, $wrd_line], [$wrd_line, $trp_line]);
        $target = [$fld_name . ' missing: ' . $usr_line, $fld_name . ' missing: ' . $wrd_line, $fld_name . ' added: ' . $trp_line];
        $t->assert($test_name, $result, $target);
    }

}
