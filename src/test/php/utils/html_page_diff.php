<?php

/*

    test/php/utils/html_page_diff.php - compare what a generated page shows with a hand made sample page
    ---------------------------------

    $hpd is the suggested var name

    a text diff of the two files is useless, because the generated page has the frame, the
    styles and the ids of the pod, so both pages are read as the items that a reader sees: the
    title, the lead text, the headings, the input fields, the table rows, the buttons and the
    list items of the notes and sources; the report lists what the generated page misses, what
    differs, what only the generated page has and where the order differs, as an html page that
    can be given to an llm as the prompt to bring the generated page closer to the sample


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

namespace Zukunft\ZukunftCom\test\php\utils;

use DOMDocument;
use DOMNode;
use DOMXPath;

class html_page_diff
{

    // the html classes that the generated page and the sample use for the same parts
    const string CLASS_FIELD = 'field';
    const string CLASS_TAG = 'tag';
    const string CLASS_UNIT = 'unit';
    const string CLASS_HINT = 'hint';
    const string CLASS_SECTION = 'sec';
    const string CLASS_KEY = 'key';
    const string CLASS_SUB = 'sub';

    // the kinds of the items, which start the key of an item and name the order checks
    const string KIND_TITLE = 'title';
    const string KIND_LEAD = 'lead';
    const string KIND_HEADING = 'heading';
    const string KIND_FIELD = 'field';
    const string KIND_TABLE = 'table';
    const string KIND_SECTION = 'table section';
    const string KIND_ROW = 'row';
    const string KIND_BUTTON = 'button';
    const string KIND_LIST_ITEM = 'list item';
    // the kinds whose order is compared, because the reader sees them in this order
    const array ORDER_KINDS = [self::KIND_HEADING, self::KIND_FIELD, self::KIND_SECTION, self::KIND_ROW];

    // the properties of an item
    const string PROP_TEXT = 'text';
    const string PROP_TAG = 'tag';
    const string PROP_UNIT = 'unit';
    const string PROP_HINT = 'hint';
    const string PROP_VALUE = 'value';
    const string PROP_SUB = 'sub text';
    const string PROP_BOLD = 'bold';
    const string PROP_CELLS = 'cells';


    /*
     * read
     */

    /**
     * @param string $html the html code of a whole page
     * @return array per item key, e.g. "field: System size", the properties of the item in the order of the page
     */
    function page_items(string $html): array
    {
        $result = [];
        $xpath = $this->xpath($html);
        if ($xpath != null) {
            $this->add_header_items($xpath, $result);
            $this->add_text_items($xpath, '//main//h2 | //main//summary', self::KIND_HEADING, $result);
            $this->add_field_items($xpath, $result);
            $this->add_table_items($xpath, $result);
            $this->add_text_items($xpath, '//main//button', self::KIND_BUTTON, $result);
            $this->add_text_items($xpath, '//main//li', self::KIND_LIST_ITEM, $result);
        }
        return $result;
    }

    /**
     * @param string $html the html code of a whole page
     * @return DOMXPath|null the query of the page or null if the html is empty
     */
    private function xpath(string $html): ?DOMXPath
    {
        $result = null;
        if (trim($html) != '') {
            $dom = new DOMDocument();
            // the html5 tags are unknown to libxml, which reports them but reads the page anyway
            libxml_use_internal_errors(true);
            // the declaration makes libxml read the page as utf-8 also without a meta tag it knows
            $dom->loadHTML('<?xml encoding="utf-8"?>' . $html);
            libxml_clear_errors();
            $result = new DOMXPath($dom);
        }
        return $result;
    }

    /**
     * @param DOMXPath $xpath the query of the page
     * @param array $items the items of the page to which the title and the lead text are added
     */
    private function add_header_items(DOMXPath $xpath, array &$items): void
    {
        foreach ($xpath->query('//main//header/h4') as $node) {
            $this->add_item($items, self::KIND_TITLE, [self::PROP_TEXT => $this->text($node)]);
        }
        foreach ($xpath->query('//main//header/p') as $node) {
            $this->add_item($items, self::KIND_LEAD, [self::PROP_TEXT => $this->text($node)]);
        }
    }

    /**
     * @param DOMXPath $xpath the query of the page
     * @param string $query the xpath of the nodes e.g. the buttons
     * @param string $kind the kind of the items e.g. self::KIND_BUTTON
     * @param array $items the items of the page to which an item per node is added
     */
    private function add_text_items(DOMXPath $xpath, string $query, string $kind, array &$items): void
    {
        foreach ($xpath->query($query) as $node) {
            $this->add_item($items, $kind . ': ' . $this->text($node), []);
        }
    }

    /**
     * @param DOMXPath $xpath the query of the page
     * @param array $items the items of the page to which an item per input field is added
     */
    private function add_field_items(DOMXPath $xpath, array &$items): void
    {
        foreach ($xpath->query('//main//div[' . $this->has_class(self::CLASS_FIELD) . ']') as $field) {
            $label = $xpath->query('.//label', $field)->item(0);
            $name = $label == null ? '' : $this->text_without($xpath, $label, self::CLASS_TAG);
            $this->add_item($items, self::KIND_FIELD . ': ' . $name, [
                self::PROP_TAG => $this->first_text($xpath, './/label//span[' . $this->has_class(self::CLASS_TAG) . ']', $field),
                self::PROP_UNIT => $this->first_text($xpath, './/span[' . $this->has_class(self::CLASS_UNIT) . ']', $field),
                self::PROP_HINT => $this->first_text($xpath, './/span[' . $this->has_class(self::CLASS_HINT) . ']', $field),
                self::PROP_VALUE => $this->field_value($xpath, $field),
            ]);
        }
    }

    /**
     * @param DOMXPath $xpath the query of the page
     * @param DOMNode $field the node of the input field
     * @return string the number of the field or the selected option of a selector
     */
    private function field_value(DOMXPath $xpath, DOMNode $field): string
    {
        $result = '';
        $input = $xpath->query('.//input[@type="number"]', $field)->item(0);
        if ($input != null) {
            $result = $this->attribute($input, 'value');
        } else {
            $result = $this->first_text($xpath, './/select/option[@selected]', $field);
        }
        return $result;
    }

    /**
     * @param DOMXPath $xpath the query of the page
     * @param array $items the items of the page to which the header, the sections and the rows of each table are added
     */
    private function add_table_items(DOMXPath $xpath, array &$items): void
    {
        foreach ($xpath->query('//main//table') as $table) {
            $heads = [];
            foreach ($xpath->query('.//thead//th', $table) as $head) {
                $heads[] = $this->text($head);
            }
            $this->add_item($items, self::KIND_TABLE . ': ' . implode(' | ', $heads), []);
            // a row before any section row is named by the first column header e.g. "Month / Jan"
            $section = $heads[0] ?? '';
            foreach ($xpath->query('.//tbody/tr', $table) as $row) {
                $section = $this->add_row_item($xpath, $row, $section, $items);
            }
        }
    }

    /**
     * @param DOMXPath $xpath the query of the page
     * @param DOMNode $row the table row
     * @param string $section the label of the section that the row is part of
     * @param array $items the items of the page to which the section or the row is added
     * @return string the section of the next row, which is the label of this row if it starts a section
     */
    private function add_row_item(DOMXPath $xpath, DOMNode $row, string $section, array &$items): string
    {
        $result = $section;
        $cells = $xpath->query('./td', $row);
        $first = $cells->item(0);
        if ($first != null and $this->node_has_class($row, self::CLASS_SECTION)) {
            $result = $this->text($first);
            $this->add_item($items, self::KIND_SECTION . ': ' . $result, []);
        } elseif ($first != null) {
            $numbers = [];
            for ($i = 1; $i < $cells->length; $i++) {
                $numbers[] = $this->text($cells->item($i));
            }
            $label = $this->text_without($xpath, $first, self::CLASS_SUB);
            $this->add_item($items, self::KIND_ROW . ': ' . $section . ' / ' . $label, [
                self::PROP_SUB => $this->first_text($xpath, './/span[' . $this->has_class(self::CLASS_SUB) . ']', $first),
                self::PROP_BOLD => $this->node_has_class($row, self::CLASS_KEY) ? 'yes' : 'no',
                self::PROP_CELLS => implode(' | ', $numbers),
            ]);
        }
        return $result;
    }

    /**
     * add an item; a key that the page has already gets its count, because a page may show the
     * same text twice, e.g. a button in two places, and both are part of the comparison
     *
     * @param array $items the items of the page
     * @param string $key the key of the item e.g. "button: Save"
     * @param array $props the properties of the item
     */
    private function add_item(array &$items, string $key, array $props): void
    {
        $unique = $key;
        $count = 1;
        while (array_key_exists($unique, $items)) {
            $count++;
            $unique = $key . ' (' . $count . ')';
        }
        $items[$unique] = $props;
    }


    /*
     * text
     */

    /**
     * @param DOMNode $node any node of the page
     * @return string the text of the node as the reader sees it, with single spaces
     */
    private function text(DOMNode $node): string
    {
        return trim(preg_replace('/\s+/u', ' ', $node->textContent));
    }

    /**
     * @param DOMXPath $xpath the query of the page
     * @param DOMNode $node e.g. a label with a tag
     * @param string $cls the html class of the inner spans whose text is not part of the result e.g. self::CLASS_TAG
     * @return string the text of the node without the text of these spans
     */
    private function text_without(DOMXPath $xpath, DOMNode $node, string $cls): string
    {
        $copy = $node->cloneNode(true);
        foreach (iterator_to_array($xpath->query('.//span[' . $this->has_class($cls) . ']', $copy)) as $span) {
            $span->parentNode->removeChild($span);
        }
        return $this->text($copy);
    }

    /**
     * @param DOMXPath $xpath the query of the page
     * @param string $query the xpath relative to the node
     * @param DOMNode $node the node from which the query starts
     * @return string the text of the first node that the query finds or an empty string
     */
    private function first_text(DOMXPath $xpath, string $query, DOMNode $node): string
    {
        $found = $xpath->query($query, $node)->item(0);
        return $found == null ? '' : $this->text($found);
    }

    /**
     * @param string $cls an html class e.g. self::CLASS_FIELD
     * @return string the xpath condition that a node has this class among others
     */
    private function has_class(string $cls): string
    {
        return 'contains(concat(" ", normalize-space(@class), " "), " ' . $cls . ' ")';
    }

    /**
     * @param DOMNode $node an element of the page
     * @param string $cls an html class e.g. self::CLASS_KEY
     * @return bool true if the element has this class
     */
    private function node_has_class(DOMNode $node, string $cls): bool
    {
        $classes = explode(' ', $this->attribute($node, 'class'));
        return in_array($cls, $classes);
    }

    /**
     * @param DOMNode $node an element of the page
     * @param string $name the name of the attribute e.g. class
     * @return string the value of the attribute or an empty string if the element has none
     */
    private function attribute(DOMNode $node, string $name): string
    {
        $result = '';
        $attr = $node->attributes?->getNamedItem($name);
        if ($attr != null) {
            $result = $attr->nodeValue;
        }
        return $result;
    }


    /*
     * report
     */

    /**
     * @param array $sample the items of the hand made sample page (page_items)
     * @param array $page the items of the generated page (page_items)
     * @param string $sample_name the file name of the sample e.g. pv_lifetime_balance_sample.html
     * @param string $page_name the file name of the generated page e.g. pv_lifetime_balance_no_js.html
     * @return string the html page with the differences and the prompt for an llm
     */
    function report(array $sample, array $page, string $sample_name, string $page_name): string
    {
        $missing = array_keys(array_diff_key($sample, $page));
        $extra = array_keys(array_diff_key($page, $sample));
        $title = 'Differences of ' . $page_name . ' to ' . $sample_name;
        $prompt = 'Change the generated page ' . $page_name . ' so that it shows what the hand made sample '
            . $sample_name . ' shows: add what is missing, correct what differs and remove what only the'
            . ' generated page has, unless the sample lacks it by mistake. A number can differ for a good'
            . ' reason, because the sample calculates its numbers with its own script, so check the value'
            . ' or the formula before changing it. Fix a finding in the use case json or in the calculator'
            . ' renderer, never in the sample or in the generated snapshot.';
        return '<!DOCTYPE html>' . "\n" . '<html lang="en">' . "\n"
            . '<head><meta charset="utf-8"><title>' . $this->esc($title) . '</title></head>' . "\n"
            . '<body>' . "\n" . '<h1>' . $this->esc($title) . '</h1>' . "\n"
            . '<p>' . $this->esc($prompt) . '</p>' . "\n"
            . $this->report_section('Missing in the generated page', $missing)
            . $this->report_section('Different', $this->different_lines($sample, $page))
            . $this->report_section('Only in the generated page', $extra)
            . $this->report_section('Different order', $this->order_lines($sample, $page))
            . '</body>' . "\n" . '</html>' . "\n";
    }

    /**
     * @param array $sample the items of the sample page
     * @param array $page the items of the generated page
     * @return array per property of an item that both pages have, one line with both texts
     */
    private function different_lines(array $sample, array $page): array
    {
        $result = [];
        foreach (array_intersect_key($sample, $page) as $key => $props) {
            foreach (array_keys(array_merge($props, $page[$key])) as $prop) {
                $sample_txt = $props[$prop] ?? '';
                $page_txt = $page[$key][$prop] ?? '';
                if ($sample_txt != $page_txt) {
                    $result[] = $key . ', ' . $prop . ': the sample shows "' . $sample_txt
                        . '" and the generated page "' . $page_txt . '"';
                }
            }
        }
        return $result;
    }

    /**
     * @param array $sample the items of the sample page
     * @param array $page the items of the generated page
     * @return array per kind of item, whose common items are in another order, one line with both orders
     */
    private function order_lines(array $sample, array $page): array
    {
        $result = [];
        foreach (self::ORDER_KINDS as $kind) {
            $sample_order = $this->common_keys_of_kind($sample, $page, $kind);
            $page_order = $this->common_keys_of_kind($page, $sample, $kind);
            if ($sample_order != $page_order) {
                $result[] = $kind . ': the sample shows "' . implode('", "', $sample_order)
                    . '" and the generated page "' . implode('", "', $page_order) . '"';
            }
        }
        return $result;
    }

    /**
     * @param array $items the items of one page in their order
     * @param array $other the items of the other page
     * @param string $kind the kind of the items e.g. self::KIND_FIELD
     * @return array the keys of the items of this kind that both pages have, in the order of the first page
     */
    private function common_keys_of_kind(array $items, array $other, string $kind): array
    {
        $result = [];
        foreach (array_keys($items) as $key) {
            if (str_starts_with($key, $kind . ': ') and array_key_exists($key, $other)) {
                $result[] = $key;
            }
        }
        return $result;
    }

    /**
     * @param string $title the heading of the section
     * @param array $lines the findings of the section
     * @return string the html code of the section with the number of findings in the heading
     */
    private function report_section(string $title, array $lines): string
    {
        $result = '<h2>' . $this->esc($title) . ' (' . count($lines) . ')</h2>' . "\n";
        if ($lines != []) {
            $result .= '<ul>' . "\n";
            foreach ($lines as $line) {
                $result .= '<li>' . $this->esc($line) . '</li>' . "\n";
            }
            $result .= '</ul>' . "\n";
        }
        return $result;
    }

    /**
     * @param string $text a text of a page
     * @return string the text escaped for the html of the report
     */
    private function esc(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES);
    }

}
