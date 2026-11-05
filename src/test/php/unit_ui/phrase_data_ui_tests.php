<?php

/*

    test/php/unit_ui/phrase_data_ui_tests.php - testing of the request cache filled from a phraseData answer
    -----------------------------------------

    the answer is the sample api/phrase_data/phrase_data_pv_install.json, so the test needs no backend


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

use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once html_paths::EXECUTE . 'calculator.php';
include_once html_paths::EXECUTE . 'calculator_definition_report.php';
include_once html_paths::EXECUTE . 'calculator_report.php';
include_once html_paths::EXECUTE . 'system_page.php';
include_once html_paths::EXECUTE . 'ui_list.php';
include_once html_paths::HELPER . 'config.php';
include_once html_paths::HELPER . 'data_object.php';
include_once html_paths::HTML . 'html_base.php';
include_once html_paths::PHRASE . 'phrase_data.php';
include_once html_paths::USER . 'user_message.php';
include_once html_paths::VIEW . 'view.php';
include_once html_paths::SHARED_CONST . 'files.php';
include_once html_paths::SHARED_CONST . 'views.php';
include_once html_paths::SHARED_ENUM . 'messages.php';
include_once html_paths::SHARED_TYPES . 'api_types.php';
include_once html_paths::SHARED_TYPES . 'phrase_types.php';
include_once html_paths::SHARED_TYPES . 'verbs.php';
include_once html_paths::SHARED_TYPES . 'view_styles.php';
include_once html_paths::SHARED . 'json_fields.php';
include_once test_paths::CONST . 'files.php';
include_once test_paths::CREATE . 'test_values.php';
include_once test_paths::CONST . 'triple_names.php';
include_once test_paths::UTILS . 'test_cleanup.php';

use Zukunft\ZukunftCom\main\php\web\component\execute\calculator;
use DateTime;
use Zukunft\ZukunftCom\main\php\web\component\execute\calculator_definition_report;
use Zukunft\ZukunftCom\main\php\web\component\execute\calculator_report;
use Zukunft\ZukunftCom\main\php\web\component\execute\system_page;
use Zukunft\ZukunftCom\main\php\web\component\execute\ui_list;
use Zukunft\ZukunftCom\main\php\web\frontend;
use Zukunft\ZukunftCom\main\php\web\helper\config as config_ui;
use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\html\pdf_writer;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase_data;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\view\view;
use Zukunft\ZukunftCom\main\php\shared\const\files;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\main\php\shared\types\phrase_types;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;
use Zukunft\ZukunftCom\main\php\shared\types\view_styles;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\test\php\const\files as test_files;
use Zukunft\ZukunftCom\test\php\create\test_values;
use Zukunft\ZukunftCom\test\php\const\triple_names;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class phrase_data_ui_tests
{

    // the id of the result that the test adds to the sample, which has no results
    const int RESULT_ID = 1;
    // a phrase name that the sample does not contain
    const string UNKNOWN_NAME = 'System Test Phrase Not In The Phrase Data';
    // a word of an answer without calculator triples
    const int NO_CALC_ID = 1;
    const string NO_CALC_NAME = 'System Test Word Without Calculator';
    // the texts of the pdf writer test and the number of rows that do not fit on one page
    const string PDF_BRACKET_TEXT = 'a (b)';
    const string PDF_HEAD = 'head';
    // the text operator of a page and the font objects that a pdf needs to show the glyph ids of a text
    const string PDF_SHOW_TEXT = ' Tj';
    const string PDF_FONT_ENCODING = '/Encoding /Identity-H';
    const string PDF_FONT_FILE = '/FontFile2';
    const int PDF_LONG_ROWS = 60;
    // the footer of the pdf writer test: the text on the left, the text in the middle, the start of the page text
    // and how often a text is repeated so that it does not fit the line
    const string PDF_FOOTER_LEFT = 'System Test Pod';
    const string PDF_FOOTER_TEXT = 'System Test Footer';
    const string PDF_FOOTER_PAGE = 'System Test Page ';
    const int PDF_FOOTER_LONG_REPEAT = 40;
    // the font of the footer texts and where the middle text of a portrait page starts, which the width of its glyphs gives
    const string PDF_FOOTER_FONT = ' /F1 7.00 Tf ';
    const string PDF_FOOTER_CENTER_POS = '263.48 18.00 Td';
    // the time of the definition pdf test, which is in the middle of its footer
    const string PDF_TIME = '2026-11-05 19:55';
    // a footer of a short word, a word that does not fit the line and a short word that would fit after the first
    const string PDF_GAP_FIRST = 'System';
    const string PDF_GAP_LONG = 'W';
    const int PDF_GAP_REPEAT = 200;
    const string PDF_GAP_LAST = 'Gap';
    // the size of an A4 portrait page and the position of the footer on the left of a page as the pdf writes them
    const string PDF_PORTRAIT_BOX = '/MediaBox [0 0 595 842]';
    const string PDF_LANDSCAPE_BOX = '/MediaBox [0 0 842 595]';
    const string PDF_FOOTER_POS = '36.00 18.00 Td';
    // a page with three main parts: two headings and a collapsed part, whose heading and the heading of a section are within it
    const string PDF_MAIN_PARTS_HTML = '<h2>System Test Main A</h2><p>System Test Text</p><h2>System Test Main B</h2>'
        . '<details><summary>System Test Main C</summary><h2>System Test Sub D</h2></details>'
        . '<section><h2>System Test Sub E</h2></section>';
    // the headings of two positions tables of the portfolio split report
    const string REPORT_FIRST_PORTFOLIO = 'First child portfolio';
    const string REPORT_SECOND_PORTFOLIO = 'Second child portfolio';
    // the start and the end of the content of a page of a pdf
    const string PDF_PAGE_PATTERN = "~stream\n(.*?)\nendstream~s";
    // a collapsed part with a table that is too wide for a portrait page with the normal text size
    const string PDF_WIDE_HEAD = 'System Test Wide';
    const string PDF_WIDE_CELL = 'System Test Column';
    const int PDF_WIDE_COLUMNS = 12;
    // a char beyond the latin chars, the same text with a latin char and the char in the ToUnicode map of the font
    const string PDF_SUB_TEXT = 'CO₂';
    const string PDF_SUB_LATIN = 'CO2';
    const string PDF_SUB_UNICODE = '<2082>';
    // a char that the font does not have, which the pdf shows as the missing glyph of the font
    const string PDF_MISSING_CHAR = "\u{E000}";
    const string PDF_MISSING_CODE = '<0000>';
    const string PDF_MISSING_UNICODE = '<0000> <FFFD>';
    const string PDF_MISSING_WRONG_UNICODE = '<0000> <E000>';
    // the subset names of the two font families in a pdf and a text with a char that Noto Sans does not have
    const string PDF_FONT_NOTO = '+NotoSans-Regular';
    const string PDF_FONT_DEJAVU = '+DejaVuSans ';
    const string PDF_NOTO_MISSING_START = 'a ';
    const string PDF_NOTO_MISSING_CHAR = '≈';
    const string PDF_NOTO_MISSING_END = ' b';
    // texts of the pv installation page that its definition pdf contains: the page title with the CO₂,
    // the number of an assumption with its unit and the start of the first source
    const string PV_PAGE_TITLE = 'PV lifetime CO₂ balance';
    const string PV_FIELD_NUMBER = '976 kilowatthours per kilowatt peak and year';
    const string PV_SOURCE_START = 'Stucki';
    // a value and a formula of the pv installation data as the technical setup of its definition pdf lists them
    const string PV_SETUP_VALUE = 'example PV installation, PV capacity, kWp, assumed = 10';
    const string PV_SETUP_EXPRESSION = '"PV embodied emission" = "PV embodied intensity" * "PV capacity"';
    // a page with three values, of which the first has a source that is sorted after the source of the last,
    // the source of the last has a lower case start, and the middle one has no source
    const string SORT_PAGE = 'System Test Sorted Values';
    const string SORT_SOURCE_A = 'a System Test Source';
    const string SORT_SOURCE_B = 'B System Test Source';
    const string SORT_NUM_A = '1';
    const string SORT_NUM_B = '2';
    const string SORT_NUM_NONE = '3';
    // three results of the page in the same order: by a formula sorted later, without a formula and by a formula with a lower case start
    const string SORT_FORMULA_A = 'a System Test Formula';
    const string SORT_FORMULA_B = 'B System Test Formula';
    const string SORT_RES_A = '4';
    const string SORT_RES_B = '5';
    const string SORT_RES_NONE = '6';
    // the json fields of the objects of the answer without the results
    const array OBJECT_FIELDS = [
        json_fields::WORDS,
        json_fields::TRIPLES,
        json_fields::FORMULAS,
        json_fields::SOURCES,
        json_fields::VALUES
    ];

    function run(test_cleanup $t): void
    {
        global $ui_sys;

        // start the test section (ts)
        $ts = 'unit ui phrase data ';
        $t->header($ts);

        $sample = $this->with_db_ids(json_decode(file_get_contents(test_files::PHRASE_DATA_PV_INSTALL), true));

        $t->subheader($ts . 'fill the request cache');
        $test_name = 'the sample fills the request cache without a message';
        $msg = new user_message();
        $dto = $this->filled($sample, $msg);
        $t->assert_msg($test_name, $msg);
        $test_name = '... with every word and triple as a phrase';
        $phr_count = count($sample[json_fields::WORDS]) + count($sample[json_fields::TRIPLES]);
        $t->assert($test_name, $dto->phr_lst->count(), $phr_count);
        $test_name = '... with the page triple built from its from phrase';
        $phr = $dto->phr_lst->get_by_name(triple_names::EXAMPLE_PV_INSTALLATION, $msg);
        $t->assert($test_name, $phr?->get_from()?->name() ?? '', triple_names::PV_INSTALLATION);
        // the calculator reads its tree from the phrases of the cache, so the tree is complete
        $test_name = '... so that the page phrase is a calculator';
        $t->assert_true($test_name, $phr != null and new calculator($phr, $dto->phr_lst)->is_calculator());
        $test_name = '... with every value';
        $t->assert($test_name, $dto->val_lst->count(), count($sample[json_fields::VALUES]));
        $test_name = '... every formula';
        $t->assert($test_name, $dto->frm_lst->count(), count($sample[json_fields::FORMULAS]));
        $test_name = '... and every source';
        $t->assert($test_name, $dto->src_lst->count(), count($sample[json_fields::SOURCES]));
        // the sample names the type of a triple by its code id, which the type cache knows
        $test_name = '... and the type of a triple from the type cache';
        $trp = $dto->phr_lst->get_by_name(triple_names::GRAM_PER_KWH, $msg)?->obj();
        $typ_id = $ui_sys->typ_lst_cache->phr_typ->id(phrase_types::MEASURE);
        $t->assert($test_name, $trp?->type_id($msg) ?? 0, $typ_id);

        $t->subheader($ts . 'results');
        $test_name = 'a result of the answer is added with its context and formula';
        $frm = $sample[json_fields::FORMULAS][0];
        $json = $sample;
        $json[json_fields::RESULTS] = [[
            json_fields::ID => self::RESULT_ID,
            json_fields::WORDS => [triple_names::EXAMPLE_PV_INSTALLATION],
            json_fields::FORMULA_NAME => $frm[json_fields::NAME],
            json_fields::NUMBER => 1,
            json_fields::CONTEXT => [triple_names::EXAMPLE_PV_INSTALLATION, triple_names::PV_INSTALLATION]
        ]];
        $msg = new user_message();
        $dto = $this->filled($json, $msg);
        $t->assert_msg($test_name, $msg);
        $res = $dto->res_lst->lst()[0] ?? null;
        $test_name = '... with the phrases of its context';
        $t->assert($test_name, $res?->src_grp?->phr_lst()->count() ?? 0, 2);
        $test_name = '... and the id of its formula';
        $t->assert($test_name, $res?->frm?->id() ?? 0, $frm[json_fields::ID]);
        // the page does not load the results of the selected phrase again
        $test_name = '... and the results of the selected phrase are complete';
        $sel_phr = $dto->phr_lst->get_by_name(triple_names::EXAMPLE_PV_INSTALLATION, $msg);
        $t->assert_true($test_name, $dto->has_results_of($sel_phr?->id() ?? 0));
        // negative: without a selection the results are added, but not marked as complete
        $test_name = 'the results of an answer without selection are added';
        unset($json[json_fields::SELECTION]);
        $dto = $this->filled($json, $msg);
        $t->assert($test_name, $dto->res_lst->count(), 1);
        $test_name = '... but not as the complete results of a phrase';
        $t->assert_false($test_name, $dto->has_results_of($sel_phr?->id() ?? 0));

        // negative: a name that the answer does not contain is reported and not loaded from the backend
        $t->subheader($ts . 'names that the answer does not contain');
        $test_name = 'a value with an unknown phrase is reported';
        $json = $sample;
        $json[json_fields::VALUES][0][json_fields::WORDS][] = self::UNKNOWN_NAME;
        $msg = new user_message();
        $dto = $this->filled($json, $msg);
        $t->assert_true($test_name, $msg->has_msg_id(msg_id::PHRASE_NAME_NOT_FOUND));
        $test_name = '... and named in the message';
        $t->assert_text_contains($test_name, $msg->text(), self::UNKNOWN_NAME);
        $test_name = '... but the value is kept with its known phrases';
        $t->assert($test_name, $dto->val_lst->count(), count($sample[json_fields::VALUES]));
        $test_name = 'a result of an unknown formula is reported';
        $json = $sample;
        $json[json_fields::RESULTS] = [[
            json_fields::ID => self::RESULT_ID,
            json_fields::WORDS => [triple_names::EXAMPLE_PV_INSTALLATION],
            json_fields::FORMULA_NAME => self::UNKNOWN_NAME,
            json_fields::NUMBER => 1
        ]];
        $msg = new user_message();
        $this->filled($json, $msg);
        $t->assert_true($test_name, $msg->has_msg_id(msg_id::IMPORT_TERM_NOT_FOUND));
        // the page loads no values of its own, if the answer has filled the cache completely
        $test_name = 'a complete page cache loads no values';
        $dto = $this->filled($sample, $msg);
        $dto->online = true;
        $dto->page_data_complete = true;
        $dto->load_values_of($dto->phr_lst, $msg);
        $t->assert_false($test_name, $dto->has_values_of($dto->phr_lst));
        // negative: the answer of a phrase without calculator triples gives no calculator
        $test_name = 'the answer of a phrase without calculator triples is no calculator';
        $json = [
            json_fields::SELECTION => [[json_fields::ID => self::NO_CALC_ID, json_fields::NAME => self::NO_CALC_NAME]],
            json_fields::WORDS => [[json_fields::ID => self::NO_CALC_ID, json_fields::NAME => self::NO_CALC_NAME]]
        ];
        $msg = new user_message();
        $dto = $this->filled($json, $msg);
        $phr = $dto->phr_lst->get_by_name(self::NO_CALC_NAME, $msg);
        $t->assert_false($test_name, $phr == null or new calculator($phr, $dto->phr_lst)->is_calculator());
        $t->assert_msg($test_name, $msg);
        $test_name = 'an empty answer fills nothing';
        $msg = new user_message();
        $dto = $this->filled([], $msg);
        $t->assert($test_name, $dto->phr_lst->count(), 0);
        $t->assert_msg($test_name, $msg);
        // a triple without a from phrase is valid data e.g. in USD or per km
        $test_name = 'a triple without a from phrase is no problem';
        $no_from = $sample[json_fields::TRIPLES][0];
        unset($no_from[json_fields::EX_FROM]);
        $no_from[json_fields::ID] = triple_names::IN_USD_ID;
        $no_from[json_fields::NAME] = triple_names::IN_USD;
        $json = $sample;
        $json[json_fields::TRIPLES][] = $no_from;
        $msg = new user_message();
        $dto = $this->filled($json, $msg);
        $t->assert_msg($test_name, $msg);
        $test_name = '... and it is part of the request cache';
        $no_from_phr = $dto->phr_lst->get_by_name(triple_names::IN_USD, $msg);
        $t->assert($test_name, $no_from_phr?->name() ?? '', triple_names::IN_USD);
        // negative: a from phrase that the answer does not contain is still reported
        $test_name = 'a triple with an unknown from phrase is reported';
        $no_from[json_fields::EX_FROM] = self::UNKNOWN_NAME;
        $json = $sample;
        $json[json_fields::TRIPLES][] = $no_from;
        $msg = new user_message();
        $this->filled($json, $msg);
        $t->assert_text_contains($test_name, $msg->text(), self::UNKNOWN_NAME);
        // negative: a to phrase that the answer does not contain too
        $test_name = 'a triple with an unknown to phrase is reported';
        $no_to = $sample[json_fields::TRIPLES][0];
        $no_to[json_fields::ID] = triple_names::IN_USD_ID;
        $no_to[json_fields::NAME] = triple_names::IN_USD;
        $no_to[json_fields::EX_TO] = self::UNKNOWN_NAME;
        $json = $sample;
        $json[json_fields::TRIPLES][] = $no_to;
        $msg = new user_message();
        $this->filled($json, $msg);
        $t->assert_text_contains($test_name, $msg->text(), self::UNKNOWN_NAME);

        $this->page_tests($t, $ts, $sample);
        $this->report_tests($t, $ts);
    }

    /**
     * the pdf writer and the pdf report of the portfolio split, created from a phraseData answer of the page
     *
     * @param test_cleanup $t the test environment
     * @param string $ts the name of the test section
     */
    private function report_tests(test_cleanup $t, string $ts): void
    {
        $t->subheader($ts . 'pdf writer');
        $test_name = 'a pdf has the header, the end and at least one page';
        $pdf = new pdf_writer();
        $empty_pdf = $pdf->output();
        $t->assert_text_contains($test_name, $empty_pdf, '%PDF-');
        $t->assert_text_contains($test_name, $empty_pdf, '/Count 1');
        $t->assert_text_contains($test_name, $empty_pdf, '%%EOF');
        $test_name = 'a text is written as the glyph ids of the font, so that a bracket needs no escape';
        $pdf = new pdf_writer();
        $pdf->text(self::PDF_BRACKET_TEXT);
        $bracket_pdf = $pdf->output();
        $t->assert_text_contains($test_name, $bracket_pdf, $this->pdf_string(self::PDF_BRACKET_TEXT) . self::PDF_SHOW_TEXT);
        $test_name = '... with the subset of the font embedded';
        $t->assert_text_contains($test_name, $bracket_pdf, self::PDF_FONT_ENCODING);
        $t->assert_text_contains($test_name, $bracket_pdf, self::PDF_FONT_FILE);
        $test_name = 'a long table continues on the next page';
        $pdf = new pdf_writer();
        $pdf->table([self::PDF_HEAD], array_fill(0, self::PDF_LONG_ROWS, ['1']), [true]);
        $t->assert_text_contains($test_name, $pdf->output(), '/Count 2');
        $test_name = 'a jpeg image is embedded';
        $pdf = new pdf_writer();
        $is_jpeg = $pdf->image(file_get_contents(files::REPORT_LOGO_DEFAULT));
        $t->assert_true($test_name, $is_jpeg);
        // negative: anything else is refused, so that the report can fall back to the default logo
        $test_name = '... but no other file';
        $t->assert_false($test_name, $pdf->image(self::PDF_HEAD));
        $test_name = '... and no empty file';
        $t->assert_false($test_name, $pdf->image(''));
        $test_name = '... and no incomplete jpeg';
        $t->assert_false($test_name, $pdf->image(pdf_writer::JPEG_START));
        $test_name = 'a pdf can have portrait pages';
        $t->assert_text_contains($test_name, new pdf_writer(true)->output(), self::PDF_PORTRAIT_BOX);
        // negative: landscape stays the default, e.g. for the wide tables of the calculator report
        $test_name = '... but has landscape pages by default';
        $t->assert_text_contains($test_name, new pdf_writer()->output(), self::PDF_LANDSCAPE_BOX);
        // e.g. a wide table on landscape pages within a document on portrait pages
        $test_name = 'a page break can change the format of the next pages';
        $pdf = new pdf_writer(true);
        $pdf->text(self::PDF_HEAD);
        $pdf->page_break(false);
        $pdf->text(self::PDF_HEAD);
        $mixed_format_pdf = $pdf->output();
        $t->assert_text_contains($test_name, $mixed_format_pdf, self::PDF_PORTRAIT_BOX);
        $t->assert_text_contains($test_name, $mixed_format_pdf, self::PDF_LANDSCAPE_BOX);
        // negative: a page break without a format keeps the format of the current page
        $test_name = '... but a page break without a format keeps it';
        $pdf = new pdf_writer(true);
        $pdf->text(self::PDF_HEAD);
        $pdf->page_break();
        $pdf->text(self::PDF_HEAD);
        $t->assert_text_not_contains($test_name, $pdf->output(), self::PDF_LANDSCAPE_BOX);
        $test_name = 'a narrow table fits a portrait page';
        $pdf = new pdf_writer();
        $t->assert_true($test_name, $pdf->fits_portrait([self::PDF_HEAD], [[self::PDF_HEAD]]));
        // negative: a wide table would be scaled down on a portrait page
        $test_name = '... but not a wide table';
        $wide_row = array_fill(0, self::PDF_WIDE_COLUMNS, self::PDF_WIDE_CELL);
        $t->assert_false($test_name, $pdf->fits_portrait([], [$wide_row]));
        // the footer is written with the file, because only then the number of pages is known
        $test_name = 'each page has the footer with its number and the number of all pages';
        $pdf = new pdf_writer(true);
        $pdf->set_footer(self::PDF_FOOTER_LEFT, self::PDF_FOOTER_TEXT,
            fn(int $page, int $count): string => self::PDF_FOOTER_PAGE . $page . '/' . $count);
        $pdf->table([self::PDF_HEAD], array_fill(0, 2 * self::PDF_LONG_ROWS, ['1']), [true]);
        $footer_pdf = $pdf->output();
        $t->assert_text_contains($test_name, $footer_pdf, $this->pdf_string(self::PDF_FOOTER_PAGE . '1/2'));
        $t->assert_text_contains($test_name, $footer_pdf, $this->pdf_string(self::PDF_FOOTER_PAGE . '2/2'));
        $test_name = '... the text on the left';
        $left_txt = self::PDF_FOOTER_POS . self::PDF_FOOTER_FONT . $this->pdf_string(self::PDF_FOOTER_LEFT);
        $t->assert_text_contains($test_name, $footer_pdf, $left_txt);
        $test_name = '... and the text in the middle of the page';
        $center_txt = self::PDF_FOOTER_CENTER_POS . self::PDF_FOOTER_FONT . $this->pdf_string(self::PDF_FOOTER_TEXT);
        $t->assert_text_contains($test_name, $footer_pdf, $center_txt);
        // negative: the text in the middle is not written at the left side
        $test_name = '... and not on the left';
        $center_left_txt = self::PDF_FOOTER_POS . self::PDF_FOOTER_FONT . $this->pdf_string(self::PDF_FOOTER_TEXT);
        $t->assert_text_not_contains($test_name, $footer_pdf, $center_left_txt);
        // negative: a pdf without a footer has no footer line
        $test_name = '... but a pdf without a footer has none';
        $t->assert_text_not_contains($test_name, new pdf_writer(true)->output(), self::PDF_FOOTER_POS);
        $test_name = 'a footer text in the middle that does not fit the line is shortened';
        $pdf = new pdf_writer(true);
        $pdf->set_footer(self::PDF_FOOTER_LEFT, str_repeat(self::PDF_FOOTER_TEXT . ' ', self::PDF_FOOTER_LONG_REPEAT),
            fn(int $page, int $count): string => self::PDF_FOOTER_PAGE . $page);
        $short_pdf = $pdf->output();
        $t->assert_text_contains($test_name, $short_pdf, $this->pdf_string_end(pdf_writer::FOOTER_CUT) . self::PDF_SHOW_TEXT);
        // negative: the text on the left stays complete
        $test_name = '... but not the text on the left';
        $t->assert_text_contains($test_name, $short_pdf, $left_txt);
        $test_name = 'a footer text on the left that does not fit the line is shortened';
        $pdf = new pdf_writer(true);
        $pdf->set_footer(str_repeat(self::PDF_FOOTER_LEFT . ' ', self::PDF_FOOTER_LONG_REPEAT), self::PDF_FOOTER_TEXT,
            fn(int $page, int $count): string => self::PDF_FOOTER_PAGE . $page);
        $long_left_pdf = $pdf->output();
        $t->assert_text_contains($test_name, $long_left_pdf, $this->pdf_string_end(pdf_writer::FOOTER_CUT) . self::PDF_SHOW_TEXT);
        // negative: then the line has no space for the text in the middle, which is left out and never overlaps
        $test_name = '... and leaves out the text in the middle';
        $t->assert_text_not_contains($test_name, $long_left_pdf, $this->pdf_string(self::PDF_FOOTER_TEXT));
        // negative: the words up to the first that does not fit, never a later shorter word after a gap
        $test_name = 'a shortened footer text has its words up to the first that does not fit';
        $gap_txt = self::PDF_GAP_FIRST . ' ' . str_repeat(self::PDF_GAP_LONG, self::PDF_GAP_REPEAT) . ' ' . self::PDF_GAP_LAST;
        $pdf = new pdf_writer(true);
        $pdf->set_footer(self::PDF_FOOTER_LEFT, $gap_txt, fn(int $page, int $count): string => self::PDF_FOOTER_PAGE . $page);
        $gap_pdf = $pdf->output();
        $t->assert_text_contains($test_name, $gap_pdf, $this->pdf_string(self::PDF_GAP_FIRST . pdf_writer::FOOTER_CUT));
        $test_name = '... and not a shorter word after the first that does not fit';
        $t->assert_text_not_contains($test_name, $gap_pdf, $this->pdf_string_start(self::PDF_GAP_FIRST . ' ' . self::PDF_GAP_LAST));

        // the font has the chars beyond the latin chars of the old pdf encodings, e.g. the CO₂ of a page
        $test_name = 'a char beyond the latin chars is shown with its own glyph';
        $pdf = new pdf_writer();
        $pdf->text(self::PDF_SUB_TEXT);
        $sub_pdf = $pdf->output();
        $t->assert_text_contains($test_name, $sub_pdf, $this->pdf_string(self::PDF_SUB_TEXT));
        $t->assert_text_not_contains($test_name, $sub_pdf, $this->pdf_string(self::PDF_SUB_LATIN));
        $test_name = '... that a reader can copy as the char';
        $t->assert_text_contains($test_name, $sub_pdf, self::PDF_SUB_UNICODE);
        // negative: a char that the font does not have is the missing glyph and no other char
        $test_name = '... but a char that the font does not have is shown as the missing glyph';
        $missing_code = new pdf_writer()->text_code(self::PDF_MISSING_CHAR);
        $t->assert($test_name, $missing_code, self::PDF_MISSING_CODE);
        // the missing glyph stands for every char that the font does not have, so a reader copies the replacement char
        $test_name = '... which a reader copies as the replacement char';
        $pdf = new pdf_writer();
        $pdf->text(self::PDF_MISSING_CHAR);
        $missing_pdf = $pdf->output();
        $t->assert_text_contains($test_name, $missing_pdf, self::PDF_MISSING_UNICODE);
        // negative: not as the first char that the font does not have, which would be wrong for all others
        $test_name = '... and not as the char that it replaces';
        $t->assert_text_not_contains($test_name, $missing_pdf, self::PDF_MISSING_WRONG_UNICODE);

        // Noto Sans is the alternative font family, which lacks e.g. the ≈ that DejaVu Sans shows instead
        $test_name = 'a pdf can use Noto Sans';
        $pdf = new pdf_writer(false, pdf_writer::FAMILY_NOTO);
        $pdf->text(self::PDF_SUB_TEXT);
        $noto_pdf = $pdf->output();
        $t->assert_text_contains($test_name, $noto_pdf, self::PDF_FONT_NOTO);
        // negative: a text that Noto Sans can show completely embeds no other font
        $test_name = '... without the fallback font if Noto Sans has every char';
        $t->assert_text_not_contains($test_name, $noto_pdf, self::PDF_FONT_DEJAVU);
        $test_name = '... and a char that Noto Sans does not have in the fallback font DejaVu Sans';
        $pdf = new pdf_writer(false, pdf_writer::FAMILY_NOTO);
        $pdf->text(self::PDF_NOTO_MISSING_START . self::PDF_NOTO_MISSING_CHAR . self::PDF_NOTO_MISSING_END);
        $mixed_pdf = $pdf->output();
        $t->assert_text_contains($test_name, $mixed_pdf, self::PDF_FONT_NOTO);
        $t->assert_text_contains($test_name, $mixed_pdf, self::PDF_FONT_DEJAVU);
        $test_name = '... with the glyph of DejaVu Sans and not the missing glyph';
        $t->assert_text_contains($test_name, $mixed_pdf, $this->pdf_string(self::PDF_NOTO_MISSING_CHAR) . self::PDF_SHOW_TEXT);

        // each main part of a page starts on a new page, but not a part within it
        $test_name = 'each main part of the page starts on a new page of the pdf';
        $pdf = new pdf_writer();
        new calculator_report()->add_html($pdf, self::PDF_MAIN_PARTS_HTML, true);
        $t->assert_text_contains($test_name, $pdf->output(), '/Count 3 ');
        // negative: without the option the parts follow each other, e.g. in the portfolio report
        $test_name = '... but not without the option';
        $pdf = new pdf_writer();
        new calculator_report()->add_html($pdf, self::PDF_MAIN_PARTS_HTML);
        $t->assert_text_contains($test_name, $pdf->output(), '/Count 1 ');
        // a main part with a table that is too wide for a portrait page is on a landscape page, e.g. the splitting walk
        $test_name = 'a main part with a wide table is on a landscape page';
        $wide_cells = str_repeat('<td>' . self::PDF_WIDE_CELL . '</td>', self::PDF_WIDE_COLUMNS);
        $wide_html = self::PDF_MAIN_PARTS_HTML . '<details><summary>' . self::PDF_WIDE_HEAD . '</summary>'
            . '<table><tr>' . $wide_cells . '</tr></table></details>';
        $pdf = new pdf_writer(true);
        new calculator_report()->add_html($pdf, $wide_html, true, true);
        $wide_pdf = $pdf->output();
        $t->assert_text_contains($test_name, $wide_pdf, self::PDF_LANDSCAPE_BOX);
        $test_name = '... and the other main parts on portrait pages';
        $t->assert_text_contains($test_name, $wide_pdf, self::PDF_PORTRAIT_BOX);
        // negative: without the option the report keeps the format of its pages and scales the table down
        $test_name = '... but not without the option';
        $pdf = new pdf_writer(true);
        new calculator_report()->add_html($pdf, $wide_html, true);
        $t->assert_text_not_contains($test_name, $pdf->output(), self::PDF_LANDSCAPE_BOX);

        // the pdf of the calculator editor repeats the calculator page with the names of the editor fields
        $t->subheader($ts . 'calculator definition report');
        $msg = new user_message();
        $pv_json = json_decode(file_get_contents(test_files::PHRASE_DATA_PV_INSTALL), true);
        $def_pdf = new calculator_definition_report()->pdf($pv_json, new DateTime(self::PDF_TIME), $msg);
        $test_name = 'the definition pdf of the calculator editor has portrait pages';
        $t->assert_text_contains($test_name, $def_pdf, self::PDF_PORTRAIT_BOX);
        $test_name = '... the logo';
        $t->assert_text_contains($test_name, $def_pdf, '/DCTDecode');
        $test_name = '... the page title of the editor field';
        $field_name = msg_id::CALC_EDITOR_PAGE_TITLE->text() . calculator_definition_report::FIELD_END;
        $t->assert_text_contains($test_name, $def_pdf, $this->pdf_string($field_name));
        $t->assert_text_contains($test_name, $def_pdf, $this->pdf_string(self::PV_PAGE_TITLE));
        $test_name = '... the number of an input field with its unit';
        $t->assert_text_contains($test_name, $def_pdf, $this->pdf_string(self::PV_FIELD_NUMBER));
        $test_name = '... the sources numbered like the references of the page';
        $source_start = calculator_report::NUMBER_START . '1' . calculator_report::NUMBER_END . self::PV_SOURCE_START;
        $t->assert_text_contains($test_name, $def_pdf, $this->pdf_string_start($source_start));
        $test_name = '... and the footer on each page';
        preg_match('~/Count (\d+)~', $def_pdf, $count_match);
        $def_pages = (int)($count_match[1] ?? 0);
        $t->assert_greater($test_name, 1, $def_pages);
        $t->assert($test_name, substr_count($def_pdf, self::PDF_FOOTER_POS), $def_pages);
        $test_name = '... with the pod that has created the pdf on the left';
        $pod_txt = self::PDF_FOOTER_POS . self::PDF_FOOTER_FONT . $this->pdf_string(POD_NAME);
        $pod_count = substr_count($def_pdf, $pod_txt);
        $t->assert($test_name, $pod_count, $def_pages);
        // negative: the time is in the middle behind the name of the page and not again in the page number on the right
        $test_name = '... and the time once per page behind the name';
        $time_txt = $this->pdf_string_end(calculator_report::FOOTER_TIME_SEPARATOR . self::PDF_TIME);
        $time_count = substr_count($def_pdf, $time_txt);
        $t->assert($test_name, $time_count, $def_pages);
        // after the page every object of the data that defines it, also the ones that the page does not show
        $test_name = 'the definition pdf ends with the technical setup';
        $t->assert_text_contains($test_name, $def_pdf, $this->pdf_string(msg_id::CALCULATOR_DEFINITION_SETUP->text()));
        $test_name = '... with each value by its phrases and its number';
        $t->assert_text_contains($test_name, $def_pdf, $this->pdf_string(self::PV_SETUP_VALUE));
        $test_name = '... and each formula with its expression';
        $t->assert_text_contains($test_name, $def_pdf, $this->pdf_string(self::PV_SETUP_EXPRESSION));
        // the values of one source are together under a heading with the name of the source, sorted without the case
        $test_name = '... with the values grouped by their source';
        $src_doc = [json_fields::SELECTION => [self::SORT_PAGE], json_fields::VALUES => [
            [json_fields::WORDS => [self::SORT_PAGE], json_fields::NUMBER => self::SORT_NUM_B, json_fields::SOURCE => self::SORT_SOURCE_B],
            [json_fields::WORDS => [self::SORT_PAGE], json_fields::NUMBER => self::SORT_NUM_NONE],
            [json_fields::WORDS => [self::SORT_PAGE], json_fields::NUMBER => self::SORT_NUM_A, json_fields::SOURCE => self::SORT_SOURCE_A]
        ], json_fields::RESULTS => [
            [json_fields::WORDS => [self::SORT_PAGE], json_fields::NUMBER => self::SORT_RES_B, json_fields::FORMULA => self::SORT_FORMULA_B],
            [json_fields::WORDS => [self::SORT_PAGE], json_fields::NUMBER => self::SORT_RES_NONE],
            [json_fields::WORDS => [self::SORT_PAGE], json_fields::NUMBER => self::SORT_RES_A, json_fields::FORMULA => self::SORT_FORMULA_A]
        ]];
        $src_pdf = new calculator_definition_report()->pdf($src_doc, new DateTime(), $msg);
        $val_a = $this->pdf_string_end(calculator_definition_report::NUMBER_SEPARATOR . self::SORT_NUM_A);
        $val_b = $this->pdf_string_end(calculator_definition_report::NUMBER_SEPARATOR . self::SORT_NUM_B);
        $val_none = $this->pdf_string_end(calculator_definition_report::NUMBER_SEPARATOR . self::SORT_NUM_NONE);
        $t->assert_text_order($test_name, $src_pdf, $val_a, $val_b);
        $test_name = '... under a heading that names the source';
        $head_a = $this->pdf_string_end(' ' . self::SORT_SOURCE_A);
        $t->assert_text_order($test_name, $src_pdf, $head_a, $val_a);
        // negative: a value without a source is not sorted before the values of a source
        $test_name = '... and the values without a source last';
        $head_none = $this->pdf_string(msg_id::CALCULATOR_DEFINITION_VALUES_NO_SOURCE->text());
        $t->assert_text_order($test_name, $src_pdf, $val_b, $head_none);
        $test_name = '... under their own heading';
        $t->assert_text_order($test_name, $src_pdf, $head_none, $val_none);
        // the results of one formula are together under a heading with the name of the formula
        $test_name = '... and the results grouped by their formula';
        $res_a = $this->pdf_string_end(calculator_definition_report::NUMBER_SEPARATOR . self::SORT_RES_A);
        $res_b = $this->pdf_string_end(calculator_definition_report::NUMBER_SEPARATOR . self::SORT_RES_B);
        $res_none = $this->pdf_string_end(calculator_definition_report::NUMBER_SEPARATOR . self::SORT_RES_NONE);
        $t->assert_text_order($test_name, $src_pdf, $res_a, $res_b);
        $test_name = '... under a heading that names the formula';
        $frm_head_a = $this->pdf_string_end(' ' . self::SORT_FORMULA_A);
        $t->assert_text_order($test_name, $src_pdf, $frm_head_a, $res_a);
        // negative: a result without a formula is not sorted before the results of a formula
        $test_name = '... and the results without a formula last';
        $frm_head_none = $this->pdf_string(msg_id::CALCULATOR_DEFINITION_RESULTS_NO_FORMULA->text());
        $t->assert_text_order($test_name, $src_pdf, $res_b, $frm_head_none);
        $test_name = '... under their own heading of the results';
        $t->assert_text_order($test_name, $src_pdf, $frm_head_none, $res_none);
        // negative: data without the calculator triples e.g. the hand made sample of the editor defines no page
        $test_name = 'the definition pdf of data without a calculator has only the technical setup';
        $sample = json_decode(file_get_contents(test_files::CALCULATOR_EDITOR_SAMPLE), true);
        $no_calc_pdf = new calculator_definition_report()->pdf($sample, new DateTime(), $msg);
        $t->assert_text_contains($test_name, $no_calc_pdf, $this->pdf_string(msg_id::CALCULATOR_DEFINITION_SETUP->text()));
        $test_name = '... and no editor field';
        $t->assert_text_not_contains($test_name, $no_calc_pdf, $this->pdf_string($field_name));

        $t->subheader($ts . 'calculator report');
        $msg = new user_message();
        $json = json_decode(file_get_contents(test_files::PHRASE_DATA_PORTFOLIO_SPLIT), true);
        $dto = $this->complete_page_cache($json, $msg);
        $trp = $dto->phr_lst->get_by_name(triple_names::EXAMPLE_PORTFOLIO_SPLIT, $msg)?->obj();
        // the steps are collected by the same run, because a report takes long to create
        $steps = [];
        $collect = function (msg_id $id) use (&$steps) {
            $steps[] = $id->value;
        };
        $report = new calculator_report()->pdf($trp, $msg, $dto, new DateTime(self::PDF_TIME), $collect);
        $report_steps = implode(',', $steps);
        $test_name = 'the report has the footer on each page';
        preg_match('~/Count (\d+)~', $report, $report_match);
        $report_pages = (int)($report_match[1] ?? 0);
        $report_footers = substr_count($report, self::PDF_FOOTER_POS);
        $t->assert($test_name, $report_footers, $report_pages);
        $test_name = '... with the pod that has created the pdf on the left';
        $report_pod_count = substr_count($report, $pod_txt);
        $t->assert($test_name, $report_pod_count, $report_pages);
        // negative: the time is in the middle behind the title of the page and not again in the page number on the right
        $test_name = '... and the time once per page behind the title';
        $report_time_count = substr_count($report, $time_txt);
        $t->assert($test_name, $report_time_count, $report_pages);
        $test_name = 'the report has portrait pages';
        $t->assert_text_contains($test_name, $report, self::PDF_PORTRAIT_BOX);
        $test_name = '... with each portfolio on a page of its own';
        $first_code = $this->pdf_string(self::REPORT_FIRST_PORTFOLIO);
        $first_page = $this->pdf_page_with($report, $first_code);
        $t->assert_text_contains($test_name, $first_page, $first_code);
        // negative: the next portfolio is not on the same page
        $test_name = '... and not with the next portfolio';
        $t->assert_text_not_contains($test_name, $first_page, $this->pdf_string(self::REPORT_SECOND_PORTFOLIO));
        $test_name = 'the report has a positions table per portfolio';
        $t->assert_text_contains($test_name, $report, $this->pdf_string(self::REPORT_FIRST_PORTFOLIO));
        $t->assert_text_contains($test_name, $report, $this->pdf_string(self::REPORT_SECOND_PORTFOLIO));
        $t->assert_text_contains($test_name, $report, $this->pdf_string('Parent portfolio'));
        $test_name = '... with the name of the security';
        $t->assert_text_contains($test_name, $report, $this->pdf_string('Givaudan SA'));
        // the rate that the calculation uses, not a dated rate of another source
        $test_name = '... the exchange rate of the calculation';
        $t->assert_text_contains($test_name, $report, $this->pdf_string('0.9000'));
        $test_name = '... but not the dated exchange rate';
        $t->assert_text_not_contains($test_name, $report, $this->pdf_string('0.8300'));
        $test_name = '... the logo';
        $t->assert_text_contains($test_name, $report, '/DCTDecode');
        $test_name = '... the other results of the page';
        $t->assert_text_contains($test_name, $report, $this->pdf_string('Splitting walk'));
        $test_name = '... and the formulas used';
        $t->assert_text_contains($test_name, $report, $this->pdf_string('walk target of a child'));
        // negative: a phrase without calculator triples has no report, so the page is shown
        $test_name = 'a phrase without calculator triples has no report';
        $no_calc_dto = $this->complete_page_cache([
            json_fields::SELECTION => [[json_fields::ID => self::NO_CALC_ID, json_fields::NAME => self::NO_CALC_NAME]],
            json_fields::WORDS => [[json_fields::ID => self::NO_CALC_ID, json_fields::NAME => self::NO_CALC_NAME]]
        ], $msg);
        $no_calc_wrd = $no_calc_dto->phr_lst->get_by_name(self::NO_CALC_NAME, $msg)?->obj();
        $no_calc_report = new calculator_report()->pdf($no_calc_wrd, $msg, $no_calc_dto, new DateTime(self::PDF_TIME));
        $t->assert($test_name, $no_calc_report, '');
        $test_name = '... and no phrase too';
        $no_phr_report = new calculator_report()->pdf(null, $msg, $no_calc_dto, new DateTime(self::PDF_TIME));
        $t->assert($test_name, $no_phr_report, '');
        $test_name = 'the report names its steps in their order';
        $t->assert($test_name, $report_steps, implode(',', [
            msg_id::CALCULATOR_REPORT_STEP_NUMBERS->value,
            msg_id::CALCULATOR_REPORT_STEP_POSITIONS->value,
            msg_id::CALCULATOR_REPORT_STEP_TABLES->value,
            msg_id::CALCULATOR_REPORT_STEP_FORMULAS->value,
            msg_id::CALCULATOR_REPORT_STEP_FILE->value]));
        $steps = [];
        new calculator_report()->pdf($no_calc_wrd, $msg, $no_calc_dto, new DateTime(self::PDF_TIME), $collect);
        $test_name = '... and a phrase without calculator triples has no step';
        $t->assert($test_name, implode(',', $steps), '');
        $test_name = 'the report file is downloaded with the name of the page';
        $t->assert($test_name, calculator_report::attachment_header('example split'), 'Content-Disposition: attachment; filename="example split.pdf"');
        $test_name = '... and a char that could break the header is replaced';
        $t->assert($test_name, calculator_report::attachment_header("a\"b\r\nc"), 'Content-Disposition: attachment; filename="a_b__c.pdf"');
        $html_base = new html_base();
        $test_name = 'a step of a frame escapes its text';
        $t->assert_text_contains($test_name, $html_base->frame_step('a <b>'), 'a &lt;b&gt;');
        $test_name = '... and hides the step before';
        $t->assert_text_contains($test_name, $html_base->frame_start($msg), html_base::CLASS_FRAME_STEP . ':has(~ .' . html_base::CLASS_FRAME_STEP . ')');
        $test_name = 'the refresh of a frame escapes the url';
        $t->assert_text_contains($test_name, $html_base->refresh('a"b'), 'url=a&quot;b"');
        $ui = new frontend();
        $url = [url_var::MASK => views::CALCULATOR_ID, url_var::ID => self::NO_CALC_ID, url_var::REPORT => url_var::TRUE];
        $ui->report_pdf = pdf_writer::JPEG_START;
        $end_html = $ui->report_end_html($url, $msg);
        $test_name = 'a created report starts its download';
        $t->assert_text_contains($test_name, $end_html, url_var::REPORT_FILE . '=' . url_var::TRUE);
        $test_name = '... by the refresh of the frame';
        $t->assert_text_contains($test_name, $end_html, 'http-equiv="refresh"');
        $test_name = '... and does not create the report again';
        $t->assert_text_not_contains($test_name, $end_html, url_var::REPORT . '=' . url_var::TRUE);
        // negative: no download of a report that has not been created
        $ui->report_pdf = '';
        global $mtr;
        $no_file_html = $ui->report_end_html($url, $msg);
        $test_name = 'a report without a file has no refresh';
        $t->assert_text_not_contains($test_name, $no_file_html, 'http-equiv="refresh"');
        $test_name = '... and says so';
        $t->assert_text_contains($test_name, $no_file_html, $mtr->txt(msg_id::CALCULATOR_REPORT_NONE));
        $test_name = '... without naming a failure';
        $t->assert_text_not_contains($test_name, $no_file_html, $mtr->txt(msg_id::CALCULATOR_REPORT_FAILED));
        $test_name = 'the report button of a page is a link to the pdf';
        $calc = new calculator($trp->phrase(), $dto->phr_lst);
        $btn_html = new ui_list()->calculator_buttons($calc, $calc->group(triples::CALCULATOR_INPUT));
        $t->assert_text_contains($test_name, $btn_html, url_var::REPORT . '=' . url_var::TRUE);
        // negative: a list without the report keyword has no link
        $test_name = '... but a list without the keyword has none';
        $no_rpt_html = new ui_list()->calculator_buttons($calc, null);
        $t->assert_text_not_contains($test_name, $no_rpt_html, url_var::REPORT . '=');
    }

    /**
     * the calculator page of the example PV installation created from the phraseData sample only:
     * the request cache is filled and marked complete like view_exe::add_calculator_data, so that
     * every label, number and source of the page comes from the json; the snapshot of the page is
     * written by the database read test (system_views_read_tests::run_pv_lifetime_balance), so this
     * test checks only the parts of the page
     *
     * @param test_cleanup $t the test environment
     * @param string $ts the name of the test section
     * @param array $sample the phraseData sample with the objects that have a database id
     */
    private function page_tests(test_cleanup $t, string $ts, array $sample): void
    {
        global $ui_sys;
        global $mtr;
        $page = new system_page();
        $list = new ui_list();

        // the browser builds the page from the json data, so the page has only the host, the data and the script
        $t->subheader($ts . 'calculator page');
        $test_name = 'the calculator page is created from the phrase data only';
        $msg = new user_message();
        $dto = $this->complete_page_cache($sample, $msg);
        $trp = $dto->phr_lst->get_by_name(triple_names::EXAMPLE_PV_INSTALLATION, $msg)?->obj();
        $body = $page->calculator_json($trp, $msg, $dto, views::CALCULATOR, views::CALCULATOR);
        $html = new html_base();
        $t->assert_text_contains($test_name, $body, $html->script_data(system_page::CALC_EDITOR_SEED_ID, $sample));
        // the host is the use case page itself, so that the page has the width of the page of the server
        $test_name = '... into the empty host with the style of a use case page';
        $t->assert_text_contains($test_name, $body, $html->div('', view_styles::USE_CASE, system_page::CALC_PAGE_HOST_ID));
        $test_name = '... without a message';
        $t->assert_msg($test_name, $msg);
        $test_name = '... with the script that builds the page';
        $t->assert_text_contains($test_name, $body, $html->script_src(files::CALCULATOR_JSON_SCRIPT));
        // the script builds the field of the name of a case and its triple with these settings
        $test_name = '... with the label of the case name';
        $t->assert_text_contains($test_name, $body, $mtr->txt(msg_id::CALCULATOR_CASE_NAME));
        $test_name = '... and the verb of the triple of a case';
        $t->assert_text_contains($test_name, $body, '"of":"' . verbs::OF . '"');
        // negative: without javascript for the calculator the page has no html of its own
        $test_name = 'a calculator page without javascript is empty';
        $cfg_keep = $ui_sys->cfg;
        $ui_sys->cfg = new config_ui(new test_values($t)->value_list_calculator_javascript_off()->api_json([api_types::INCL_PHRASES]));
        $no_js_body = $page->calculator_json($trp, $msg, $dto, views::CALCULATOR, views::CALCULATOR);
        $ui_sys->cfg = $cfg_keep;
        $t->assert($test_name, $no_js_body, '');
        // negative: a phrase without calculator triples has no calculator page, so the view shows its components
        $test_name = 'a phrase without calculator triples has no calculator page';
        $no_calc_json = [
            json_fields::SELECTION => [[json_fields::ID => self::NO_CALC_ID, json_fields::NAME => self::NO_CALC_NAME]],
            json_fields::WORDS => [[json_fields::ID => self::NO_CALC_ID, json_fields::NAME => self::NO_CALC_NAME]]
        ];
        $no_calc_dto = $this->complete_page_cache($no_calc_json, $msg);
        $no_calc_wrd = $no_calc_dto->phr_lst->get_by_name(self::NO_CALC_NAME, $msg)?->obj();
        $no_calc_body = $page->calculator_json($no_calc_wrd, $msg, $no_calc_dto, views::CALCULATOR, views::CALCULATOR);
        $t->assert($test_name, $no_calc_body, '');
        // negative: an online but complete page cache calls no api, also not for the results and
        // the sources of another phrase; every api call adds its wait (rest_call::api_call)
        $test_name = 'a complete page cache calls no api';
        $msg = new user_message();
        $dto = $this->complete_page_cache($sample, $msg);
        $dto->online = true;
        $other = $dto->phr_lst->get_by_name(triple_names::PV_INSTALLATION, $msg)?->obj();
        $wait_before = $ui_sys->api_wait;
        if ($other != null) {
            $list->calculator_numbers($other, $dto, $msg);
            $list->calculator_sources(new calculator($other->phrase(), $dto->phr_lst), $other, $msg, $dto);
        }
        $wait_after = $ui_sys->api_wait;
        $t->assert($test_name, $wait_after, $wait_before);
        // the page cache step runs twice per page, also if the answer has left the cache incomplete
        $test_name = 'a page with its answer is not filled twice';
        $msg = new user_message();
        $dto = $this->complete_page_cache($sample, $msg);
        $dto->online = true;
        $dto->page_data_complete = false;
        $frm_count = $dto->frm_lst->count();
        $msk = new view();
        $msk->code_id = views::CALCULATOR;
        $msk->add_page_cache($trp, $msg, $dto);
        $t->assert_msg($test_name, $msg);
        $test_name = '... and keeps its formulas';
        $t->assert($test_name, $dto->frm_lst->count(), $frm_count);
    }

    /**
     * @param string $text a text that a pdf shows
     * @return string the text as a complete pdf string of its glyph ids, which the normal and the bold font share e.g. "<0026>"
     */
    private function pdf_string(string $text): string
    {
        return new pdf_writer()->text_code($text);
    }

    /**
     * @param string $text the start of a text that a pdf shows
     * @return string the glyph ids of the text at the start of a pdf string
     */
    private function pdf_string_start(string $text): string
    {
        return substr($this->pdf_string($text), 0, -1);
    }

    /**
     * @param string $pdf a pdf file
     * @param string $code a text as the pdf shows it (pdf_string)
     * @return string the content of the first page that shows the text, empty if no page shows it
     */
    private function pdf_page_with(string $pdf, string $code): string
    {
        preg_match_all(self::PDF_PAGE_PATTERN, $pdf, $matches);
        $result = '';
        foreach ($matches[1] as $page) {
            if ($result == '' and str_contains($page, $code)) {
                $result = $page;
            }
        }
        return $result;
    }

    /**
     * @param string $text the end of a text that a pdf shows
     * @return string the glyph ids of the text at the end of a pdf string
     */
    private function pdf_string_end(string $text): string
    {
        return substr($this->pdf_string($text), 1);
    }

    /**
     * @param array $sample the phraseData answer of the page
     * @param user_message $msg to collect the names that the answer does not contain
     * @return data_object an offline request cache filled from the answer and marked complete like view_exe::add_calculator_data
     */
    private function complete_page_cache(array $sample, user_message $msg): data_object
    {
        global $ui_sys;
        $dto = $this->filled($sample, $msg);
        $dto->typ_lst_cache = $ui_sys->typ_lst_cache;
        $dto->page_data = $sample;
        $dto->page_data_complete = true;
        return $dto;
    }

    /**
     * the sample is the expected minimum of an answer and also lists objects that have no database
     * id yet e.g. a word added to the use case after the ids of the sample have been taken; an
     * answer of the api has an id for each object (see phrase_data_read_tests)
     *
     * @param array $sample the json of the sample
     * @return array the sample with the objects that have a database id
     */
    private function with_db_ids(array $sample): array
    {
        foreach (self::OBJECT_FIELDS as $fld) {
            $sample[$fld] = array_values(array_filter(
                $sample[$fld] ?? [], fn($obj) => array_key_exists(json_fields::ID, $obj)));
        }
        return $sample;
    }

    /**
     * @param array $json a phraseData answer
     * @param user_message $msg to collect the names that the answer does not contain
     * @return data_object a request cache without api access, filled from the answer
     */
    private function filled(array $json, user_message $msg): data_object
    {
        $dto = new data_object();
        $dto->online = false;
        new phrase_data()->fill($json, $dto, $msg);
        return $dto;
    }

}
