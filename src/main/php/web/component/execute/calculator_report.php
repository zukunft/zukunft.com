<?php

/*

    web/component/execute/calculator_report.php - the pdf report of a calculator page
    -------------------------------------------

    the suggested var name is $rpt
    the top of the report are the target positions per portfolio, read from the columns of a
    detail table that are filtered by the split count; everything below comes from the html
    tables of the page, so that the report shows the same numbers as the page; each portfolio, each
    main part of the page and the formulas start on a new portrait page, except a main part with a table
    that is too wide for a portrait page with the normal text size e.g. the splitting walk, which is on
    landscape pages

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
include_once html_paths::EXECUTE . 'ui_list.php';
include_once html_paths::HELPER . 'data_object.php';
include_once html_paths::HELPER . 'number_format.php';
include_once html_paths::HTML . 'html_base.php';
include_once html_paths::HTML . 'html_names.php';
include_once html_paths::HTML . 'pdf_writer.php';
include_once html_paths::HTML . 'styles.php';
include_once html_paths::PHRASE . 'phrase.php';
include_once html_paths::RESULT . 'result.php';
include_once html_paths::USER . 'user_message.php';
include_once html_paths::VALUE . 'value.php';
include_once html_paths::VALUE . 'value_list.php';
include_once html_paths::WORD . 'triple.php';
include_once html_paths::WORD . 'word.php';
include_once html_paths::SHARED_CONST . 'files.php';
include_once html_paths::SHARED_CONST . 'triples.php';
include_once html_paths::SHARED_ENUM . 'messages.php';
include_once html_paths::SHARED_TYPES . 'verbs.php';
include_once html_paths::SHARED . 'library.php';

use Closure;
use DateTime;
use DOMDocument;
use DOMElement;
use DOMNode;
use Zukunft\ZukunftCom\main\php\shared\const\files;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;
use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\helper\number_format;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\html\html_names;
use Zukunft\ZukunftCom\main\php\web\html\pdf_writer;
use Zukunft\ZukunftCom\main\php\web\html\styles;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase;
use Zukunft\ZukunftCom\main\php\web\result\result;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\value\value;
use Zukunft\ZukunftCom\main\php\web\value\value_list;
use Zukunft\ZukunftCom\main\php\web\word\triple;
use Zukunft\ZukunftCom\main\php\web\word\word;

class calculator_report
{

    // the report reads and calculates the complete page, which can take longer than the php limit of a page
    const int MAX_TIME_SEC = 600;
    const string FILE_NAME_UNSAFE = '/[^A-Za-z0-9 ._-]/';
    // the time when a report is created and the separator from the name of the report in the middle of the footer
    const string TIME_FORMAT = 'Y-m-d H:i';
    const string FOOTER_TIME_SEPARATOR = ' - ';
    // the phrases of the use case that the positions are read from, selected by their name
    const string SPLIT_COUNT = 'split count';
    const string UNIT_VALUE = 'unit value';
    const string PRICE = 'price';
    const string EXCHANGE_RATE = 'exchange rate';
    // a holding names its isin with a triple to a phrase that is an isin, which the portfolio import is going to add
    const string ISIN = 'ISIN';
    // the currency of the values and the name of the unit of an exchange rate e.g. CHF per EUR
    const string BASE_CURRENCY = 'CHF';
    const string RATE_UNIT_PREFIX = 'CHF per ';
    const string EUR = 'EUR';
    // the decimals of a position: a fractional quantity and an amount, and an exchange rate
    const int AMOUNT_DECIMALS = 2;
    const int RATE_DECIMALS = 4;
    // the html tags of the page that are a heading, a text or that the report does not show
    const array HEADING_TAGS = ['h1', 'h2', 'h3', 'summary'];
    const array SKIPPED_TAGS = ['svg', 'button', 'input', 'script', 'style', 'form'];
    // the parts of the page that only a screen can show e.g. the meter of the verdict and its scale
    const array SKIPPED_CLASSES = [styles::CALC_METER, styles::CALC_NEEDLE, styles::CALC_SCALE];
    // the toggle of a collapsed part, which the report skips if it is the "... more" of the hidden rest
    const string SUMMARY_TAG = 'summary';
    // the attributes of an input field of the page that the report shows
    const string INPUT_TYPE_NUMBER = 'number';
    const string ATTR_MIN = 'min';
    const string ATTR_MAX = 'max';
    const string ATTR_STEP = 'step';
    const string ATTR_CHECKED = 'checked';
    const string ATTR_SELECTED = 'selected';
    // a link to another site is followed by its url, because a paper cannot be clicked
    const string URL_START = 'http';
    const string URL_SEPARATOR = ': ';
    // the number of an item of a numbered list like the references of the page e.g. "[2] "
    const string NUMBER_START = '[';
    const string NUMBER_END = '] ';
    // the columns of a positions table and which of them are aligned to the right
    const array POSITION_COLUMNS = [
        msg_id::CALCULATOR_REPORT_QUANTITY,
        msg_id::CALCULATOR_REPORT_SECURITY,
        msg_id::CALCULATOR_REPORT_ISIN,
        msg_id::CALCULATOR_REPORT_CURRENCY,
        msg_id::CALCULATOR_REPORT_PRICE,
        msg_id::CALCULATOR_REPORT_FX_RATE,
        msg_id::CALCULATOR_REPORT_VALUE_CHF,
        msg_id::CALCULATOR_REPORT_VALUE_EUR
    ];
    const array POSITION_RIGHT = [true, false, false, false, true, true, true, true];
    // true while a part of the page is added whose main parts each start on a new page (see add_html)
    // and whose main parts with a wide table are on landscape pages
    private bool $main_on_new_page = false;
    private bool $wide_on_landscape = false;
    // the parts of each table of the html that is added, by the path of the table within the html (see table_parts)
    private array $tables = [];
    // the html wrapper to read a part of a page as utf-8
    const string DOM_PREFIX = '<?xml encoding="UTF-8"><div>';
    const string DOM_SUFFIX = '</div>';

    /**
     * @param word|triple|null $dbo the phrase of the page
     * @param user_message $msg to report a problem of the load
     * @param data_object $dto the request cache filled with the data of the page
     * @param DateTime $time when the report is created, which the footer of each page names
     * @param Closure|null $step called with the msg_id of each step before it starts, to show the progress
     * @return string the pdf file or an empty string if the phrase is no calculator
     */
    function pdf(word|triple|null $dbo, user_message $msg, data_object $dto, DateTime $time, ?Closure $step = null): string
    {
        $result = '';
        $calc = $dbo == null ? null : new calculator($dbo->phrase(), $dto->phr_lst);
        if ($calc?->is_calculator()) {
            $list = new ui_list();
            $step?->__invoke(msg_id::CALCULATOR_REPORT_STEP_NUMBERS);
            $lst = $list->calculator_numbers($dbo, $dto, $msg);
            $pdf = new pdf_writer($msg, true);
            $this->add_footer($pdf, $calc->title(), $time->format(self::TIME_FORMAT));
            $this->add_logo($pdf);
            $pdf->title($calc->title());
            $step?->__invoke(msg_id::CALCULATOR_REPORT_STEP_POSITIONS);
            $this->add_positions($pdf, $calc, $lst, $dto, $msg);
            $step?->__invoke(msg_id::CALCULATOR_REPORT_STEP_TABLES);
            $this->add_html($pdf, $list->calculator_warnings($calc, $dbo, $dto, $msg)
                . $list->calculation_table($calc, $dbo, $lst, $dto, $msg)
                . $list->calculator_check($calc, $lst, $dto, $msg)
                . $list->calculation_detail_tables($calc, $dbo, $lst, $msg, true)
                . $list->calculator_notes($calc)
                . $list->calculation_detail_tables($calc, $dbo, $lst, $msg)
                . $list->formula_notes($dbo, $msg, $dto)
                . $list->calculator_sources($calc, $dbo, $msg, $dto), true, true);
            $step?->__invoke(msg_id::CALCULATOR_REPORT_STEP_FORMULAS);
            $this->add_formulas($pdf, $lst, $dto);
            $step?->__invoke(msg_id::CALCULATOR_REPORT_STEP_FILE);
            $result = $pdf->output();
        }
        return $result;
    }

    /**
     * @param string $name the name of the page, which is the suggested file name
     * @param string $extension the file extension of the download e.g. the csv of a table
     * @return string the http header that starts the download, with only the chars that are safe in a header
     */
    static function attachment_header(string $name, string $extension = pdf_writer::FILE_EXTENSION): string
    {
        $file = preg_replace(self::FILE_NAME_UNSAFE, '_', $name) . $extension;
        return 'Content-Disposition: attachment; filename="' . $file . '"';
    }

    /**
     * add the footer to each page: the pod that creates the pdf on the left, the name of the report with its time
     * in the middle and the page number on the right, also used by the definition report of the calculator editor
     *
     * @param pdf_writer $pdf the report
     * @param string $name the name of the report e.g. the title of the page
     * @param string $time_txt when the report is created in the format self::TIME_FORMAT
     */
    function add_footer(pdf_writer $pdf, string $name, string $time_txt): void
    {
        global $mtr;
        $lib = new library();
        $pdf->set_footer(POD_NAME, $name . self::FOOTER_TIME_SEPARATOR . $time_txt,
            fn(int $page, int $count): string => $lib->msg_var_text([[msg_id::CALCULATOR_DEFINITION_PAGE, [
                msg_id::VAR_VALUE => (string)$page,
                msg_id::VAR_VALUE_COUNT => (string)$count
            ]]], $mtr));
    }

    /**
     * add the logo that the user has set in the config, else the logo of zukunft.com,
     * also used by the definition report of the calculator editor (calculator_definition_report)
     *
     * @param pdf_writer $pdf the report
     */
    function add_logo(pdf_writer $pdf): void
    {
        if (!$pdf->image($this->user_logo())) {
            $pdf->image(file_get_contents(files::REPORT_LOGO_DEFAULT));
        }
    }

    /**
     * @return string the content of the image of the config value of the user, empty if the user has set none or the file does not exist
     */
    private function user_logo(): string
    {
        global $ui_sys;
        $name = $ui_sys?->cfg?->text_by([...html_base::LAYOUT_PATH, triples::REPORT_LOGO]) ?? '';
        // only the file name, so that a config value of a user can never read a file outside of the images
        $path = files::IMAGE_PATH . basename($name);
        $result = '';
        if ($name != '' and is_file($path)) {
            $result = file_get_contents($path);
        } elseif ($name != '') {
            log_warning('the report logo ' . $name . ' of the user config does not exist');
        }
        return $result;
    }

    /**
     * add a table of the target positions per portfolio
     *
     * @param pdf_writer $pdf the report
     * @param calculator $calc the calculator reader of the page
     * @param value_list $lst the values and the results of the page
     * @param data_object $dto the request cache with the phrases of the page
     * @param user_message $msg to report a problem of the phrase types
     */
    private function add_positions(pdf_writer $pdf, calculator $calc, value_list $lst, data_object $dto, user_message $msg): void
    {
        global $mtr;
        $list = new ui_list();
        $tables = $calc->items(triples::CALCULATOR_DETAIL_TABLE);
        $cols = [];
        foreach ($tables as $tbl) {
            $cols = [...$cols, ...$calc->columns($calc->sub_items($tbl, verbs::HAS_ID))];
        }
        $unit_cols = array_values(array_filter($cols, fn($col) => $col[5]));
        foreach ($tables as $tbl) {
            $tbl_phr = $calc->item_phrase($tbl);
            foreach ($calc->columns($calc->sub_items($tbl, verbs::HAS_ID)) as [, $col_phr, $flt]) {
                if ($tbl_phr != null and $flt?->name() == self::SPLIT_COUNT) {
                    $rows = [];
                    foreach ($list->ranked_rows($calc, $tbl, $tbl_phr, $lst, $msg) as $row) {
                        $rows[] = $this->position_row($calc, $row, [$col_phr, $flt], $unit_cols, $lst, $dto, $msg);
                    }
                    $head = array_map(fn(msg_id $id) => $mtr->txt($id), self::POSITION_COLUMNS);
                    // portrait like a bank statement, with a table scaled down if a security has a long name
                    $pdf->page_break(true);
                    $pdf->heading(mb_ucfirst($col_phr->name()));
                    $pdf->table($head, array_values(array_filter($rows)), self::POSITION_RIGHT);
                }
            }
        }
    }

    /**
     * @param calculator $calc the calculator reader of the page
     * @param phrase $row the holding e.g. LISP
     * @param array $col_phrases the phrases of the quantity in the portfolio e.g. first child portfolio and split count
     * @param array $unit_cols the columns of the page that show the currency of a row (calculator::columns)
     * @param value_list $lst the values and the results of the page
     * @param data_object $dto the request cache with the phrases of the page
     * @param user_message $msg to report a problem of the phrase types
     * @return array the cells of the position or an empty array if the portfolio has none of the holding
     */
    private function position_row(calculator $calc, phrase $row, array $col_phrases, array $unit_cols, value_list $lst, data_object $dto, user_message $msg): array
    {
        $list = new ui_list();
        $qty = $list->calculator_general_value([$row, ...$col_phrases], $lst, $msg, [], true)?->number() ?? 0;
        $unit_phr = $dto->phr_lst->get_by_name(self::UNIT_VALUE, $msg);
        $unit = $list->calculator_general_value([$row, $unit_phr], $lst, $msg, [], true)?->number();
        $result = [];
        if ($qty != 0 and $unit !== null) {
            $currency = implode('', $list->calculator_phrase_cells($calc, $row, $unit_cols, $lst, $msg));
            $chf = $qty * $unit;
            $eur_rate = $this->rate(self::EUR, $lst);
            $rate = $this->rate($currency, $lst);
            $fmt = new number_format();
            $fmt->decimals = floor($qty) == $qty ? 0 : self::AMOUNT_DECIMALS;
            $result = [$fmt->text($qty), $calc->phrase_hint($row) ?: $calc->phrase_label($row), $this->isin($calc, $row), $currency,
                $this->price_text($calc, $row, $lst, $dto, $msg), $this->number_text($rate, self::RATE_DECIMALS),
                $this->number_text($chf, self::AMOUNT_DECIMALS),
                $this->number_text($eur_rate == null ? null : $chf / $eur_rate, self::AMOUNT_DECIMALS)];
        }
        return $result;
    }

    /**
     * @param calculator $calc the calculator reader of the page
     * @param phrase $row the holding e.g. LISP
     * @return string the name of the phrase that is an isin of the holding, empty until the import has added it
     */
    private function isin(calculator $calc, phrase $row): string
    {
        $result = '';
        foreach ($calc->sub_items($row, verbs::HAS_ID) as $item) {
            $phr = $calc->item_phrase($item);
            $kinds = $phr == null ? [] : $calc->row_kinds($phr);
            if (in_array(self::ISIN, array_map(fn(phrase $kind) => $kind->name(), $kinds))) {
                $result = $phr->name();
            }
        }
        return $result;
    }

    /**
     * @param calculator $calc the calculator reader of the page
     * @param phrase $row the holding e.g. LISP
     * @param value_list $lst the values and the results of the page
     * @param data_object $dto the request cache with the phrases of the page
     * @param user_message $msg to report a problem of the phrase types
     * @return string the price of the holding with its percent sign if it has one, empty for a holding without a price e.g. cash
     */
    private function price_text(calculator $calc, phrase $row, value_list $lst, data_object $dto, user_message $msg): string
    {
        $list = new ui_list();
        $fmt = new number_format();
        $fmt->decimals = self::AMOUNT_DECIMALS;
        $price = $dto->phr_lst->get_by_name(self::PRICE, $msg);
        $result = '';
        foreach ($price == null ? [] : $calc->kinds($price) as $kind) {
            $val = $list->calculator_general_value([$row, $kind], $lst, $msg);
            if ($val != null) {
                $result = $list->calculator_cell_text($val, $fmt, false, $msg, self::AMOUNT_DECIMALS);
            }
        }
        return $result;
    }

    /**
     * @param string $currency the code of the currency e.g. EUR
     * @param value_list $lst the values and the results of the page
     * @return float|null the value of one unit of the currency in the base currency, null if the page has no rate
     */
    private function rate(string $currency, value_list $lst): ?float
    {
        $names = [self::EXCHANGE_RATE, self::RATE_UNIT_PREFIX . $currency];
        $result = $currency == self::BASE_CURRENCY ? 1.0 : null;
        foreach ($lst->lst() as $val) {
            // exactly these phrases, because the calculation uses this rate and not e.g. a dated rate of another source
            if ($val instanceof value and count($val->phr_lst()->names()) == count($names) and $val->match_all($names)) {
                $result = $val->number();
            }
        }
        return $result;
    }

    /**
     * @param float|null $num the number of the cell
     * @param int $decimals the decimals to show
     * @return string the number as a text, empty for a number that the page does not have
     */
    private function number_text(?float $num, int $decimals): string
    {
        $fmt = new number_format();
        $fmt->decimals = $decimals;
        return $num === null ? '' : $fmt->text($num);
    }

    /**
     * add the formulas of the results of the page sorted by their name
     *
     * @param pdf_writer $pdf the report
     * @param value_list $lst the values and the results of the page
     * @param data_object $dto the request cache with the formulas of the page
     */
    private function add_formulas(pdf_writer $pdf, value_list $lst, data_object $dto): void
    {
        global $mtr;
        $rows = [];
        foreach ($lst->lst() as $res) {
            $id = $res instanceof result ? ($res->frm?->id() ?? 0) : 0;
            if ($id != 0 and $dto->frm_lst->has_id($id)) {
                $frm = $dto->frm_lst->get($id);
                $rows[$frm->name()] = [$frm->name(), html_entity_decode($frm->user_expression(), ENT_QUOTES)];
            }
        }
        ksort($rows);
        if ($rows != []) {
            $pdf->page_break(true);
            $pdf->heading($mtr->txt(msg_id::CALCULATOR_REPORT_FORMULAS));
            $pdf->table([], array_values($rows), []);
        }
    }

    /**
     * add a part of the page: its headings, tables and texts in their order
     *
     * @param pdf_writer $pdf the report
     * @param string $html the html code of the part of the page
     * @param bool $main_on_new_page true to start each main part of the page on a new page (see is_main_heading)
     * @param bool $wide_on_landscape true to write a main part on landscape pages if one of its tables does not fit
     *                                a portrait page with the normal text size and else on portrait pages
     */
    function add_html(pdf_writer $pdf, string $html, bool $main_on_new_page = false, bool $wide_on_landscape = false): void
    {
        $doc = new DOMDocument();
        // the html of a page is valid for a browser, but e.g. a section tag is unknown to the parser
        $libxml_errors = libxml_use_internal_errors(true);
        $doc->loadHTML(self::DOM_PREFIX . $html . self::DOM_SUFFIX);
        libxml_clear_errors();
        libxml_use_internal_errors($libxml_errors);
        $this->main_on_new_page = $main_on_new_page;
        $this->wide_on_landscape = $wide_on_landscape;
        $this->add_node($pdf, $doc->documentElement);
        $this->main_on_new_page = false;
        $this->wide_on_landscape = false;
        $this->tables = [];
    }

    /**
     * @param pdf_writer $pdf the report
     * @param DOMElement $heading a main heading of the page
     * @return bool|null true for portrait pages, false for landscape pages if a table of the part does not fit a portrait page,
     *                   null to keep the format of the pages if the report has only one format
     */
    private function part_format(pdf_writer $pdf, DOMElement $heading): ?bool
    {
        $result = null;
        if ($this->wide_on_landscape) {
            $result = true;
            foreach ($this->part_tables($heading) as $table) {
                [$head, $rows] = $this->table_parts($table);
                if (!$pdf->fits_portrait($head, $rows)) {
                    $result = false;
                }
            }
        }
        return $result;
    }

    /**
     * @param DOMElement $heading a main heading of the page
     * @return array the tables of the part of the heading: of the collapsed part of a summary,
     *               else the tables up to the next heading or collapsed part
     */
    private function part_tables(DOMElement $heading): array
    {
        $result = [];
        if ($heading->tagName == self::SUMMARY_TAG) {
            $result = $this->tables_of($heading->parentNode);
        } else {
            $node = $heading->nextSibling;
            while ($node != null and !$this->starts_part($node)) {
                $result = [...$result, ...$this->tables_of($node)];
                $node = $node->nextSibling;
            }
        }
        return $result;
    }

    /**
     * @param DOMNode $node a html element of the page
     * @return bool true if the element is a heading or a collapsed part, with which the next part of the page starts
     */
    private function starts_part(DOMNode $node): bool
    {
        $is_element = $node instanceof DOMElement;
        return ($is_element and in_array($node->tagName, [...self::HEADING_TAGS, html_base::DETAILS]));
    }

    /**
     * @param DOMNode $node a html element of the page
     * @return array the element if it is a table, else the tables within it
     */
    private function tables_of(DOMNode $node): array
    {
        $result = [];
        if ($node instanceof DOMElement and $node->tagName == html_base::TABLE) {
            $result = [$node];
        } elseif ($node instanceof DOMElement) {
            $result = iterator_to_array($node->getElementsByTagName(html_base::TABLE), false);
        }
        return $result;
    }

    /**
     * @param DOMElement $node a heading of the page or the summary of a collapsed part
     * @return bool true if the heading starts a main part of the page e.g. "Your assumptions" or "Main results",
     *              i.e. it is not within a collapsed part or a section such as "Sources" in "Formulas and sources"
     *              or the heading of the arguments of one side
     */
    private function is_main_heading(DOMElement $node): bool
    {
        $result = true;
        // the summary heads its own collapsed part, which therefore does not count
        $parent = $node->tagName == self::SUMMARY_TAG ? $node->parentNode?->parentNode : $node->parentNode;
        while ($parent instanceof DOMElement) {
            if (in_array($parent->tagName, [html_base::DETAILS, html_base::SECTION])) {
                $result = false;
            }
            $parent = $parent->parentNode;
        }
        return $result;
    }

    /**
     * @param pdf_writer $pdf the report
     * @param DOMNode $parent the html element of which the children are added
     */
    private function add_node(pdf_writer $pdf, DOMNode $parent): void
    {
        global $mtr;
        $is_numbered = ($parent instanceof DOMElement and $parent->tagName == html_base::OL);
        $number = 0;
        foreach ($parent->childNodes as $node) {
            if ($node instanceof DOMElement and !in_array($node->tagName, self::SKIPPED_TAGS)
                and !$this->has_class($node, self::SKIPPED_CLASSES)) {
                if ($node->tagName == self::SUMMARY_TAG and trim($node->textContent) == $mtr->txt(msg_id::AND_MORE)) {
                    // the toggle of the collapsed part, of which the report shows the content
                } elseif ($is_numbered and $node->tagName == html_base::LI) {
                    // the number that a browser shows, which the texts of the page cite e.g. the [2] of a source
                    $number++;
                    $pdf->text(self::NUMBER_START . $number . self::NUMBER_END . $this->text_with_links($node));
                } elseif (in_array($node->tagName, self::HEADING_TAGS)) {
                    if ($this->main_on_new_page and $this->is_main_heading($node)) {
                        $pdf->page_break($this->part_format($pdf, $node));
                    }
                    $pdf->heading($node->textContent);
                } elseif ($node->tagName == html_base::TABLE) {
                    $this->add_table($pdf, $node);
                } elseif ($this->has_class($node, [styles::INPUT_FIELD])) {
                    $this->add_field($pdf, $node);
                } elseif ($this->has_element($node)) {
                    $this->add_block($pdf, $node);
                } else {
                    $pdf->text($this->text_with_links($node));
                }
            }
        }
    }

    /**
     * an input field of the page as its label, the number with its unit and its limits, and its hint,
     * because the report shows the number that the page uses and not a field to change it
     *
     * @param pdf_writer $pdf the report
     * @param DOMElement $field the html element of the field with its label, its input and its hint
     */
    private function add_field(pdf_writer $pdf, DOMElement $field): void
    {
        global $mtr;
        $label = '';
        $number = '';
        $limits = '';
        $parts = [];
        foreach ($field->getElementsByTagName(html_base::LABEL) as $lbl) {
            $label = $label == '' ? $this->plain($lbl->textContent) : $label;
        }
        foreach ($field->getElementsByTagName(html_base::INPUT) as $input) {
            $type = $input->getAttribute(html_base::TYPE);
            if ($type == self::INPUT_TYPE_NUMBER or $type == html_base::INPUT_TEXT) {
                $number = $input->getAttribute(html_base::VALUE);
                if ($input->hasAttribute(self::ATTR_MIN) and $input->hasAttribute(self::ATTR_MAX)) {
                    $limits = new library()->msg_var_text([[msg_id::CALCULATOR_REPORT_LIMITS, [
                        msg_id::VAR_NAME_FROM => $input->getAttribute(self::ATTR_MIN),
                        msg_id::VAR_NAME_TO => $input->getAttribute(self::ATTR_MAX),
                        msg_id::VAR_VALUE => $input->getAttribute(self::ATTR_STEP)
                    ]]], $mtr);
                }
            } elseif ($type == html_base::INPUT_CHECKBOX) {
                $number = $mtr->txt($input->hasAttribute(self::ATTR_CHECKED) ? msg_id::YES : msg_id::NO);
            }
        }
        foreach ($field->getElementsByTagName(html_base::OPTION) as $opt) {
            if ($opt->hasAttribute(self::ATTR_SELECTED)) {
                $number = $this->plain($opt->textContent);
            }
        }
        foreach ($field->getElementsByTagName(html_names::SPAN) as $span) {
            if ($this->has_class($span, [styles::INPUT_UNIT])) {
                $number .= ' ' . $this->plain($span->textContent);
            }
        }
        $parts[] = trim($number . ($limits == '' ? '' : ' (' . $limits . ')'));
        foreach ($field->getElementsByTagName(html_names::SPAN) as $span) {
            if ($this->has_class($span, [styles::INPUT_HINT])) {
                $parts[] = $this->text_with_links($span);
            }
        }
        $pdf->text($label, pdf_writer::TEXT_SIZE, true);
        foreach ($parts as $part) {
            if ($part != '') {
                $pdf->text($part);
            }
        }
        $pdf->space();
    }

    /**
     * @param DOMElement $node a html element
     * @param array $classes the css classes of which one is searched e.g. styles::CALC_METER
     * @return bool true if the element has at least one of the classes
     */
    private function has_class(DOMElement $node, array $classes): bool
    {
        $own = explode(' ', $node->getAttribute(html_base::CLASS_HTML));
        return array_intersect($own, $classes) != [];
    }

    /**
     * @param DOMNode $node a html element with its text and links
     * @return string its text, in which each link to another site is followed by its url, because a paper cannot be clicked
     */
    private function text_with_links(DOMNode $node): string
    {
        $result = '';
        foreach ($node->childNodes as $child) {
            if ($child instanceof DOMElement and $child->tagName == html_base::A) {
                $url = $child->getAttribute(html_base::HREF);
                $text = $this->plain($child->textContent);
                $is_external = (str_starts_with($url, self::URL_START) and $url != $text);
                $result .= $is_external ? $text . self::URL_SEPARATOR . $url : $text;
            } elseif ($child instanceof DOMElement) {
                $result .= $this->text_with_links($child);
            } else {
                $result .= $child->textContent;
            }
        }
        return $this->plain($result);
    }

    /**
     * @param string $text a text of the page
     * @return string the text with one space between its words
     */
    private function plain(string $text): string
    {
        return trim(preg_replace('/\s+/u', ' ', $text));
    }

    /**
     * @param pdf_writer $pdf the report
     * @param DOMElement $node a paragraph with its inline elements or a block with its own elements
     */
    private function add_block(pdf_writer $pdf, DOMElement $node): void
    {
        if (in_array($node->tagName, [html_base::P, html_base::LI])) {
            $pdf->text($this->text_with_links($node));
        } else {
            $this->add_node($pdf, $node);
        }
    }

    /**
     * @param DOMNode $node a html element
     * @return bool true if the element contains another element
     */
    private function has_element(DOMNode $node): bool
    {
        $result = false;
        foreach ($node->childNodes as $child) {
            if ($child instanceof DOMElement) {
                $result = true;
            }
        }
        return $result;
    }

    /**
     * @param pdf_writer $pdf the report
     * @param DOMElement $table the html table of the page
     */
    private function add_table(pdf_writer $pdf, DOMElement $table): void
    {
        [$head, $rows, $right] = $this->table_parts($table);
        $pdf->table($head, $rows, $right);
    }

    /**
     * @param DOMElement $table the html table of the page
     * @return array the texts of the header cells, the texts of the cells per row and per column true if it is a number,
     *               read once for the format of the pages of its part and for the table of the report
     */
    private function table_parts(DOMElement $table): array
    {
        $path = $table->getNodePath();
        if (!array_key_exists($path, $this->tables)) {
            $this->tables[$path] = $this->read_table($table);
        }
        return $this->tables[$path];
    }

    /**
     * @param DOMElement $table the html table of the page
     * @return array the texts of the header cells, the texts of the cells per row and per column true if it is a number
     */
    private function read_table(DOMElement $table): array
    {
        $head = [];
        $rows = [];
        $right = [];
        foreach ($table->getElementsByTagName(html_base::TR) as $tr) {
            $cells = [];
            $is_head = false;
            foreach ($tr->childNodes as $cell) {
                if ($cell instanceof DOMElement) {
                    $is_head = ($is_head or $cell->tagName == html_base::TH);
                    $cells[] = $this->cell_text($cell);
                    $is_number = in_array(styles::CALC_NUMBER, explode(' ', $cell->getAttribute('class')));
                    $right[count($cells) - 1] = ($is_number or ($right[count($cells) - 1] ?? false));
                }
            }
            if ($is_head) {
                $head = $cells;
            } else {
                $rows[] = $cells;
            }
        }
        return [$head, $rows, $right];
    }

    /**
     * @param DOMElement $cell a cell of a html table
     * @return string the text of the cell, in which a sub text is separated from the label
     */
    private function cell_text(DOMElement $cell): string
    {
        foreach (iterator_to_array($cell->getElementsByTagName(html_names::SPAN)) as $span) {
            if ($span->getAttribute('class') == styles::CALC_SUB) {
                $span->parentNode->insertBefore($cell->ownerDocument->createTextNode(' '), $span);
            }
        }
        return trim(preg_replace('/\s+/u', ' ', $cell->textContent));
    }

}
