<?php

/*

    web/component/execute/calculator_definition_report.php - the pdf of the definition of a calculator
    ------------------------------------------------------

    the suggested var name is $def_rpt
    the pdf of the calculator editor (script/calculator_editor.js): the data of the page as the editor
    exports it, with the unsaved changes, in the layout of the calculator page and named by the fields
    of the editor: the page title and the lead text, then the arguments, the input fields with their
    numbers, units, limits and hints, the assumptions, the verdict, the results and the details until
    the sources with their links; on A4 portrait pages with the logo on the first page and a footer
    with the name of the page, the page number and the time on each page

    This file is part of the frontend of zukunft.com - calc with words

    zukunft.com is free software: you can redistribute it and/or modify it
    under the terms of the GNU Affero General Public License as
    published by the Free Software Foundation, either version 3 of
    the License, or (at your option) any later version.
    zukunft.com is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/agpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2026 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\main\php\web\component\execute;

use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::EXECUTE . 'calculator.php';
include_once html_paths::EXECUTE . 'calculator_report.php';
include_once html_paths::EXECUTE . 'system_page.php';
include_once html_paths::EXECUTE . 'ui_list.php';
include_once html_paths::HELPER . 'data_object.php';
include_once html_paths::HTML . 'pdf_writer.php';
include_once html_paths::PHRASE . 'phrase_data.php';
include_once html_paths::USER . 'user_message.php';
include_once html_paths::WORD . 'triple.php';
include_once html_paths::WORD . 'word.php';
include_once html_paths::SHARED . 'json_fields.php';
include_once html_paths::SHARED . 'library.php';
include_once html_paths::SHARED_ENUM . 'messages.php';

use DateTime;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\html\pdf_writer;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase_data;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\word\triple;
use Zukunft\ZukunftCom\main\php\web\word\word;

class calculator_definition_report
{

    // the time in the footer of each page and below the title
    const string TIME_FORMAT = 'Y-m-d H:i';
    // the end of the name of an editor field before its text e.g. "Page title:"
    const string FIELD_END = ':';
    // between the phrases of a value or a result and between the words without a description
    const string NAME_SEPARATOR = ', ';
    // between the phrases and the number of a value or a result e.g. "lifetime, field limit up = 80"
    const string NUMBER_SEPARATOR = ' = ';
    // between the parts of a triple e.g. "CHF per litre"
    const string PART_SEPARATOR = ' ';
    // the lists of the data whose objects need an id to be shown, which an object that the editor has added has not yet
    const array OBJECT_LISTS = [
        json_fields::WORDS,
        json_fields::TRIPLES,
        json_fields::FORMULAS,
        json_fields::SOURCES,
        json_fields::VALUES,
        json_fields::RESULTS
    ];

    /**
     * @param array $doc the data of the page as the calculator editor exports it (the format of the phraseData api)
     * @param DateTime $time when the pdf is created, which each page names
     * @param user_message $msg to report a name that the data does not contain
     * @return string the pdf file
     */
    function pdf(array $doc, DateTime $time, user_message $msg): string
    {
        $name = $this->page_name($doc);
        $time_txt = $time->format(self::TIME_FORMAT);
        $title = $this->txt(msg_id::CALCULATOR_DEFINITION_FOOTER, [msg_id::VAR_NAME => $name]);
        $pdf = new pdf_writer(true);
        $pdf->set_footer($title, fn(int $page, int $count): string => $this->txt(msg_id::CALCULATOR_DEFINITION_PAGE, [
            msg_id::VAR_VALUE => (string)$page,
            msg_id::VAR_VALUE_COUNT => (string)$count,
            msg_id::VAR_TIME => $time_txt
        ]));
        $rpt = new calculator_report();
        $rpt->add_logo($pdf);
        $pdf->title($title);
        $pdf->text($this->txt(msg_id::CALCULATOR_DEFINITION_CREATED, [msg_id::VAR_TIME => $time_txt]));
        $pdf->space();
        $dto = $this->page_cache($doc, $msg);
        $dbo = $dto->phr_lst->get_by_name($name, $msg)?->obj();
        $calc = null;
        if ($dbo instanceof word or $dbo instanceof triple) {
            $calc = new calculator($dbo->phrase(), $dto->phr_lst);
        }
        if ($calc?->is_calculator()) {
            $list = new ui_list();
            $this->add_field($pdf, $rpt, msg_id::CALC_EDITOR_PAGE_TITLE, $calc->title(), '');
            // the lead text and its collapsed rest, which a paper shows as one text
            $this->add_field($pdf, $rpt, msg_id::CALC_EDITOR_LEAD_TEXT, $calc->lead(), $list->calculator_more($calc, $dbo, $dto, $msg));
            $page = new system_page();
            // the page without a form, because the pdf shows the numbers and has no field to change them;
            // from the inputs on each main part on a new page e.g. "Your assumptions", "Assumptions" and "Main results"
            $rpt->add_html($pdf, $list->calculator_arguments($calc, $dbo, $dto, $msg, false));
            $rpt->add_html($pdf, $page->calculator_input($dbo, $msg, $dto, '', '', [])
                . $page->calculation_result($dbo, $msg, $dto, true), true);
        } else {
            $pdf->text($this->txt(msg_id::CALCULATOR_DEFINITION_NO_CALCULATOR, [msg_id::VAR_NAME => $name]));
        }
        $this->add_setup($pdf, $doc);
        return $pdf->output();
    }

    /**
     * the technical setup on its own pages: every object of the data that defines the page, also the ones
     * that the page does not show e.g. the limits of a field, so that the pdf documents the page completely
     *
     * @param pdf_writer $pdf the report
     * @param array $doc the data of the page as the editor exports it, with the unsaved changes
     */
    private function add_setup(pdf_writer $pdf, array $doc): void
    {
        $pdf->page_break();
        $pdf->title($this->txt(msg_id::CALCULATOR_DEFINITION_SETUP));
        $this->add_values($pdf, $doc[json_fields::VALUES] ?? []);
        $this->add_results($pdf, $doc[json_fields::RESULTS] ?? []);
        $this->add_formulas($pdf, $doc[json_fields::FORMULAS] ?? []);
        $this->add_sources($pdf, $doc[json_fields::SOURCES] ?? []);
        $this->add_words($pdf, $doc[json_fields::WORDS] ?? []);
        $this->add_triples($pdf, $doc[json_fields::TRIPLES] ?? []);
    }

    /**
     * the values with their phrases, their number and their description, grouped by their source under a
     * heading that names the source e.g. "Values from BAK economiesuisse study 2026"; the sources sorted by
     * their name, the values without a source last and the values of one source in the order of the data
     *
     * @param pdf_writer $pdf the report
     * @param array $values the values of the page
     */
    private function add_values(pdf_writer $pdf, array $values): void
    {
        foreach ($this->grouped($values, json_fields::SOURCE) as $src => $src_values) {
            $pdf->heading($src == ''
                ? $this->txt(msg_id::CALCULATOR_DEFINITION_VALUES_NO_SOURCE)
                : $this->txt(msg_id::CALCULATOR_DEFINITION_VALUES_FROM, [msg_id::VAR_NAME => $src]));
            foreach ($src_values as $val) {
                $this->add_entry($pdf, $this->number_line($val), [(string)($val[json_fields::DESCRIPTION] ?? '')]);
            }
        }
    }

    /**
     * the results with their phrases and their number, grouped by the formula that has calculated them under
     * a heading that names the formula e.g. "Results of annuity factor formula"; the formulas sorted by their
     * name, the results without a formula last and the results of one formula in the order of the data
     *
     * @param pdf_writer $pdf the report
     * @param array $results the results of the page
     */
    private function add_results(pdf_writer $pdf, array $results): void
    {
        foreach ($this->grouped($results, json_fields::FORMULA) as $frm => $frm_results) {
            $pdf->heading($frm == ''
                ? $this->txt(msg_id::CALCULATOR_DEFINITION_RESULTS_NO_FORMULA)
                : $this->txt(msg_id::CALCULATOR_DEFINITION_RESULTS_OF, [msg_id::VAR_NAME => $frm]));
            foreach ($frm_results as $res) {
                $this->add_entry($pdf, $this->number_line($res), []);
            }
        }
    }

    /**
     * @param array $objects the values or the results of the page
     * @param string $field the json field of the name that groups them e.g. json_fields::SOURCE
     * @return array the objects by the name, sorted by the name without its case so that e.g. ElCom is before ETH,
     *               with the objects without a name last and the objects of one name in the order of the data
     */
    private function grouped(array $objects, string $field): array
    {
        $result = [];
        foreach ($objects as $obj) {
            $result[(string)($obj[$field] ?? '')][] = $obj;
        }
        uksort($result, fn(string|int $a, string|int $b): int
            => [$a === '', mb_strtolower((string)$a)] <=> [$b === '', mb_strtolower((string)$b)]);
        return $result;
    }

    /**
     * @param pdf_writer $pdf the report
     * @param array $formulas the formulas of the page with their expression and their description
     */
    private function add_formulas(pdf_writer $pdf, array $formulas): void
    {
        if ($formulas != []) {
            $pdf->heading($this->txt(msg_id::CALCULATOR_REPORT_FORMULAS));
            foreach ($formulas as $frm) {
                $this->add_entry($pdf, (string)($frm[json_fields::NAME] ?? ''), [
                    (string)($frm[json_fields::EXPRESSION] ?? ''),
                    (string)($frm[json_fields::DESCRIPTION] ?? '')
                ]);
            }
        }
    }

    /**
     * @param pdf_writer $pdf the report
     * @param array $sources the sources of the values of the page with their link and their description
     */
    private function add_sources(pdf_writer $pdf, array $sources): void
    {
        if ($sources != []) {
            $pdf->heading($this->txt(msg_id::CALCULATOR_DEFINITION_SOURCES));
            foreach ($sources as $src) {
                $this->add_entry($pdf, (string)($src[json_fields::NAME] ?? ''), [
                    (string)($src[json_fields::URL] ?? ''),
                    (string)($src[json_fields::DESCRIPTION] ?? '')
                ]);
            }
        }
    }

    /**
     * the words with their description; the words without one together in one text, because their name says what they are
     *
     * @param pdf_writer $pdf the report
     * @param array $words the words of the page
     */
    private function add_words(pdf_writer $pdf, array $words): void
    {
        if ($words != []) {
            $pdf->heading($this->txt(msg_id::CALCULATOR_DEFINITION_WORDS));
            $plain = [];
            foreach ($words as $wrd) {
                $name = (string)($wrd[json_fields::NAME] ?? '');
                $description = (string)($wrd[json_fields::DESCRIPTION] ?? '');
                if ($description == '') {
                    $plain[] = $name;
                } else {
                    $this->add_entry($pdf, $name, [$description]);
                }
            }
            if ($plain != []) {
                $pdf->text(implode(self::NAME_SEPARATOR, $plain));
            }
        }
    }

    /**
     * the triples, which define the structure of the page and the texts and the formatting of its fields, columns and rows
     *
     * @param pdf_writer $pdf the report
     * @param array $triples the triples of the page
     */
    private function add_triples(pdf_writer $pdf, array $triples): void
    {
        if ($triples != []) {
            $pdf->heading($this->txt(msg_id::CALCULATOR_DEFINITION_TRIPLES));
            foreach ($triples as $trp) {
                $parts = implode(self::PART_SEPARATOR, array_filter([
                    (string)($trp[json_fields::EX_FROM] ?? ''),
                    (string)($trp[json_fields::EX_VERB] ?? ''),
                    (string)($trp[json_fields::EX_TO] ?? '')
                ]));
                $name = (string)($trp[json_fields::NAME] ?? '');
                // the parts only if they say more than the name, e.g. not for "CHF per litre" of CHF, per and litre
                $this->add_entry($pdf, $name == '' ? $parts : $name, [
                    ($name == '' or $name == $parts) ? '' : $parts,
                    (string)($trp[json_fields::TYPE_NAME] ?? ''),
                    (string)($trp[json_fields::DESCRIPTION] ?? '')
                ]);
            }
        }
    }

    /**
     * @param array $val a value or a result of the page
     * @return string its phrases and its number e.g. "lifetime, field limit up = 80"
     */
    private function number_line(array $val): string
    {
        $words = implode(self::NAME_SEPARATOR, array_map('strval', $val[json_fields::WORDS] ?? []));
        return $words . self::NUMBER_SEPARATOR . (string)($val[json_fields::NUMBER] ?? '');
    }

    /**
     * add an entry of a list: its name in bold and below each line that is not empty
     *
     * @param pdf_writer $pdf the report
     * @param string $name the bold first line of the entry
     * @param array $lines the texts below the name, an empty text is skipped
     */
    private function add_entry(pdf_writer $pdf, string $name, array $lines): void
    {
        $pdf->text($name, pdf_writer::TEXT_SIZE, true);
        foreach ($lines as $line) {
            if ($line != '') {
                $pdf->text($line);
            }
        }
        $pdf->space();
    }

    /**
     * @param array $doc the data of the page
     * @return string the name of the page phrase, which the selection names by its name or as an object
     */
    function page_name(array $doc): string
    {
        $sel = $doc[json_fields::SELECTION][0] ?? '';
        return is_array($sel) ? (string)($sel[json_fields::NAME] ?? '') : (string)$sel;
    }

    /**
     * @param array $doc the data of the page as the editor exports it
     * @param user_message $msg to report a name that the data does not contain
     * @return data_object the request cache of the page filled only from the data, so that the page is shown without the database
     */
    function page_cache(array $doc, user_message $msg): data_object
    {
        global $ui_sys;
        $doc = $this->with_ids($doc);
        $dto = new data_object();
        $dto->online = false;
        new phrase_data()->fill($doc, $dto, $msg);
        $dto->typ_lst_cache = $ui_sys?->typ_lst_cache;
        $dto->page_data = $doc;
        $dto->page_data_complete = true;
        return $dto;
    }

    /**
     * @param array $doc the data of the page as the editor exports it
     * @return array the data in which an object that the editor has added and that has no id yet has an id
     *               above the ids of its list, so that the page can show it like a saved object
     */
    private function with_ids(array $doc): array
    {
        foreach (self::OBJECT_LISTS as $fld) {
            $objects = $doc[$fld] ?? [];
            $next = $this->max_id($objects) + 1;
            foreach ($objects as $pos => $obj) {
                if (is_array($obj) and !is_int($obj[json_fields::ID] ?? null)) {
                    $doc[$fld][$pos][json_fields::ID] = $next;
                    $next++;
                }
            }
        }
        return $doc;
    }

    /**
     * @param array $objects the objects of a list of the data e.g. the words
     * @return int the highest id of the objects, zero if none has an id
     */
    private function max_id(array $objects): int
    {
        $result = 0;
        foreach ($objects as $obj) {
            $id = is_array($obj) ? ($obj[json_fields::ID] ?? null) : null;
            if (is_int($id)) {
                $result = max($result, $id);
            }
        }
        return $result;
    }

    /**
     * add a field of the editor with its name and its text e.g. "Page title:" and the title of the page
     *
     * @param pdf_writer $pdf the report
     * @param calculator_report $rpt the converter of the html of the page
     * @param msg_id $field the name of the field in the editor e.g. msg_id::CALC_EDITOR_PAGE_TITLE
     * @param string $text the text of the field
     * @param string $html the html of the page that belongs to the field e.g. the collapsed rest of the lead text
     */
    private function add_field(pdf_writer $pdf, calculator_report $rpt, msg_id $field, string $text, string $html): void
    {
        if ($text != '' or $html != '') {
            $pdf->text($this->txt($field) . self::FIELD_END, pdf_writer::TEXT_SIZE, true);
            if ($text != '') {
                $pdf->text($text);
            }
            if ($html != '') {
                $rpt->add_html($pdf, $html);
            }
            $pdf->space();
        }
    }

    /**
     * @param msg_id $msg_id the text of the report
     * @param array $vars the values of the variables of the text by their name e.g. msg_id::VAR_NAME
     * @return string the text in the language of the user
     */
    private function txt(msg_id $msg_id, array $vars = []): string
    {
        global $mtr;
        return new library()->msg_var_text([[$msg_id, $vars]], $mtr);
    }

}
