<?php

/*

    web/html/markdown.php - show a markdown document of /docs/ as html
    ---------------------

    covers the markdown that the documents of /docs/ use: headings, paragraphs, nested lists,
    tables, code blocks, quotes, lines, links, images, bold, italic and code;
    a link to another document of /docs/ opens it with http/docs.php


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

namespace Zukunft\ZukunftCom\main\php\web\html;

use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::SHARED . 'api.php';
include_once html_paths::SHARED_CONST . 'def.php';
include_once html_paths::SHARED_CONST . 'files.php';
include_once html_paths::SHARED_ENUM . 'messages.php';
include_once html_paths::SHARED . 'library.php';
include_once html_paths::SHARED . 'url_var.php';

use Zukunft\ZukunftCom\main\php\shared\api;
use Zukunft\ZukunftCom\main\php\shared\const\def;
use Zukunft\ZukunftCom\main\php\shared\const\files;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\url_var;

class markdown
{

    // the name of a document that may be shown: a file name of /docs/ without the extension and
    // without a path, so that no file outside /docs/ and no file of a sub folder can be read
    const string DOC_NAME = '/^[a-z0-9_]+$/D';
    const string DOC_EXT = '.md';
    // the document shown if the url names none
    const string DOC_DEFAULT = 'idea_and_intro';
    // the documents of /docs/ that the about page links
    const string DOC_INTRO = 'idea_and_intro';
    const string DOC_CONCEPT = 'concept';
    const string DOC_FAQ = 'faq';
    // the documents that the about page links in this order with the message id of the link text
    const array ABOUT_DOCS = [
        self::DOC_INTRO => msg_id::ABOUT_DOC_INTRO,
        self::DOC_CONCEPT => msg_id::ABOUT_DOC_CONCEPT,
        self::DOC_FAQ => msg_id::ABOUT_DOC_FAQ,
    ];
    // the char that ends a string in c, which a file path must not contain
    const string NULL_BYTE = "\0";
    // the columns of the csv of the document translations (files::DOC_TRANSLATIONS)
    const int TRANSLATION_COLUMNS = 3;
    // the start and the end of a code block and a heading with its level and its text e.g. "## Install"
    const string CODE_FENCE = '```';
    const string HEADING = '/^(#{1,6}) +(.+?) *#*$/';
    // a link to a document of /docs/ e.g. "faq.md" or "faq.md#an-anchor"
    const string DOC_LINK = '/^([a-z0-9_]+)\.md(#[A-Za-z0-9_-]*)?$/';
    // an image of /docs/ without a path e.g. "server_structure.svg", which the web server shows itself
    const string DOC_IMAGE = '/^[a-z0-9_]+\.(svg|png|jpe?g|gif|webp)$/D';
    // the url of the folder /docs/ on the web server
    const string DOCS_URL = '/docs/';
    // a link text that is the file name of a markdown document e.g. "exchange_json.md", which the user sees as "exchange json"
    const string DOC_FILE_TEXT = '/^([a-z0-9_]+)\.md$/';
    // the folder of /docs/ on github, to which a link to a file outside /docs/ points e.g. "../README.md"
    const string GITHUB_DOCS = def::LINK_GITHUB . '/blob/master/docs/';
    // the start of the placeholder of a code span, so that its text is not changed to bold or italic
    const string CODE_MARK = "\x01";
    // the html tags of a list, the same as html_base::UL and html_base::OL, which includes this class
    const string LIST_BULLETS = 'ul';
    const string LIST_NUMBERS = 'ol';

    /**
     * @param string $doc the name of the document without the extension e.g. "faq"
     * @return string the path of the markdown file or an empty text if the name is not allowed or the file does not exist
     */
    function doc_path(string $doc): string
    {
        $result = '';
        if (preg_match(self::DOC_NAME, $doc)) {
            $path = html_paths::DOCS . $doc . self::DOC_EXT;
            if ($this->is_doc_file($path)) {
                $result = $path;
            }
        }
        return $result;
    }

    /**
     * @param string $path the path of a file e.g. of a markdown document
     * @return bool true if the path is a file that is really in /docs/, also after resolving a symlink or a ".."
     */
    function is_doc_file(string $path): bool
    {
        $result = false;
        // a null byte is never part of a file name and would let realpath throw an error
        if (!str_contains($path, self::NULL_BYTE)) {
            $real_path = realpath($path);
            $real_docs = realpath(html_paths::DOCS);
            if ($real_path !== false and $real_docs !== false and is_file($real_path)) {
                $result = str_starts_with($real_path, $real_docs . DIRECTORY_SEPARATOR);
            }
        }
        return $result;
    }

    /**
     * @param string $doc the name of the document without the extension e.g. "faq"
     * @return string the url of http/docs.php that shows the document
     */
    function doc_url(string $doc): string
    {
        return api::DOCS_SCRIPT . url_var::PAR . url_var::DOC . url_var::EQ . $doc;
    }

    /**
     * the translations of the documents of /docs/, which must have the same headings as the document
     * (see coding_rule_tests::doc_translation_tests)
     *
     * @return array per document the name of its translation by the code id of the language e.g. ["concept" => ["de" => "konzept_de"]],
     *               empty if the csv files::DOC_TRANSLATIONS cannot be read
     */
    function doc_translations(): array
    {
        $lib = new library();
        $result = [];
        $lines = false;
        if (is_readable(files::DOC_TRANSLATIONS)) {
            $lines = file(files::DOC_TRANSLATIONS, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        }
        if ($lines === false) {
            log_warning('the document translations ' . files::DOC_TRANSLATIONS . ' cannot be read');
            $lines = [];
        }
        // the first line names the columns
        foreach (array_slice($lines, 1) as $line) {
            $cells = $lib->array_trim($lib->csv_line_to_array($line));
            if (count($cells) == self::TRANSLATION_COLUMNS) {
                [$doc, $lan, $translation] = $cells;
                $result[$doc][$lan] = $translation;
            } else {
                log_warning('the line "' . $line . '" of ' . files::DOC_TRANSLATIONS . ' has not the columns doc_name, language, translation');
            }
        }
        return $result;
    }

    /**
     * @param string $md the markdown text e.g. of a file of /docs/
     * @return string the text of the first heading of the first level or an empty text if there is none
     */
    function title(string $md): string
    {
        $result = '';
        if (preg_match('/^# +(.+)$/m', $md, $match)) {
            $result = trim(str_replace(['*', '`'], '', $match[1]));
        }
        return $result;
    }

    /**
     * @param string $md the markdown text e.g. of a file of /docs/
     * @return string the html code of the text without the page frame
     */
    function to_html(string $md): string
    {
        $lines = explode("\n", str_replace(["\r\n", "\r"], "\n", $md));
        return $this->blocks($lines);
    }

    /**
     * @param array $lines the markdown lines of a document, a quote or a list item
     * @return string the html code of the blocks
     */
    private function blocks(array $lines): string
    {
        $result = '';
        $count = count($lines);
        $i = 0;
        while ($i < $count) {
            $line = $lines[$i];
            $trimmed = trim($line);
            if ($trimmed == '') {
                $i++;
            } elseif (str_starts_with($trimmed, self::CODE_FENCE)) {
                // a code block up to the closing fence, shown as it is
                $code = [];
                $i++;
                while ($i < $count and !str_starts_with(trim($lines[$i]), self::CODE_FENCE)) {
                    $code[] = $lines[$i];
                    $i++;
                }
                $i++;
                $result .= '<pre><code>' . $this->esc(implode("\n", $code)) . '</code></pre>' . "\n";
            } elseif (preg_match(self::HEADING, $trimmed, $match)) {
                $level = strlen($match[1]);
                $id = $this->anchor($match[2]);
                $result .= '<h' . $level . ' id="' . $id . '">' . $this->inline($match[2]) . '</h' . $level . '>' . "\n";
                $i++;
            } elseif (preg_match('/^(-{3,}|\*{3,}|_{3,})$/', $trimmed)) {
                $result .= '<hr>' . "\n";
                $i++;
            } elseif (str_starts_with($trimmed, '>')) {
                $quote = [];
                while ($i < $count and str_starts_with(trim($lines[$i]), '>')) {
                    $quote[] = preg_replace('/^ *> ?/', '', $lines[$i]);
                    $i++;
                }
                $result .= '<blockquote>' . "\n" . $this->blocks($quote) . '</blockquote>' . "\n";
            } elseif (str_starts_with($trimmed, '|') and $i + 1 < $count
                and preg_match('/^\|? *:?-+:? *(\| *:?-+:? *)*\|? *$/', trim($lines[$i + 1]))) {
                $rows = [];
                while ($i < $count and str_starts_with(trim($lines[$i]), '|')) {
                    $rows[] = trim($lines[$i]);
                    $i++;
                }
                $result .= $this->table($rows);
            } elseif ($this->list_type($line) != '') {
                [$html, $i] = $this->list_block($lines, $i);
                $result .= $html;
            } elseif (str_starts_with($line, '    ') or str_starts_with($line, "\t")) {
                // an indented code block
                $code = [];
                while ($i < $count and (str_starts_with($lines[$i], '    ') or str_starts_with($lines[$i], "\t") or trim($lines[$i]) == '')) {
                    $code[] = preg_replace('/^( {4}|\t)/', '', $lines[$i]);
                    $i++;
                }
                $result .= '<pre><code>' . $this->esc(rtrim(implode("\n", $code))) . '</code></pre>' . "\n";
            } else {
                // a paragraph up to the next empty line or the next other block
                $para = [];
                while ($i < $count and trim($lines[$i]) != '' and !$this->starts_block($lines[$i])) {
                    $para[] = trim($lines[$i]);
                    $i++;
                }
                if ($para == []) {
                    $para[] = $trimmed;
                    $i++;
                }
                $result .= '<p>' . $this->inline(implode(' ', $para)) . '</p>' . "\n";
            }
        }
        return $result;
    }

    /**
     * @param string $line a markdown line
     * @return bool true if the line starts a block other than a paragraph
     */
    private function starts_block(string $line): bool
    {
        $trimmed = trim($line);
        return str_starts_with($trimmed, '#')
            or str_starts_with($trimmed, self::CODE_FENCE)
            or str_starts_with($trimmed, '>')
            or str_starts_with($trimmed, '|')
            or preg_match('/^(-{3,}|\*{3,}|_{3,})$/', $trimmed)
            or $this->list_type($line) != '';
    }

    /**
     * @param string $line a markdown line
     * @return string "ul" or "ol" if the line is a list item or an empty text if not
     */
    private function list_type(string $line): string
    {
        $result = '';
        if (preg_match('/^ *[-*+] +\S/', $line)) {
            $result = self::LIST_BULLETS;
        } elseif (preg_match('/^ *\d+[.)] +\S/', $line)) {
            $result = self::LIST_NUMBERS;
        }
        return $result;
    }

    /**
     * a list with its items; the lines indented more than the item belong to it e.g. a nested list
     *
     * @param array $lines the markdown lines
     * @param int $i the line of the first item
     * @return array the html code of the list and the line after it
     */
    private function list_block(array $lines, int $i): array
    {
        $count = count($lines);
        $type = $this->list_type($lines[$i]);
        $indent = strlen($lines[$i]) - strlen(ltrim($lines[$i]));
        $items = '';
        while ($i < $count and $this->list_type($lines[$i]) == $type
            and strlen($lines[$i]) - strlen(ltrim($lines[$i])) == $indent) {
            $text = preg_replace('/^ *([-*+]|\d+[.)]) +/', '', $lines[$i]);
            $i++;
            $sub = [];
            while ($i < $count and trim($lines[$i]) != ''
                and strlen($lines[$i]) - strlen(ltrim($lines[$i])) > $indent) {
                $sub[] = $lines[$i];
                $i++;
            }
            // a continuation line of the item text belongs to the text, a list below it is nested
            $nested = '';
            while ($sub != [] and $this->list_type($sub[0]) == '') {
                $text .= ' ' . trim(array_shift($sub));
            }
            if ($sub != []) {
                $nested = "\n" . $this->blocks($sub);
            }
            $items .= '<li>' . $this->inline($text) . $nested . '</li>' . "\n";
        }
        return ['<' . $type . '>' . "\n" . $items . '</' . $type . '>' . "\n", $i];
    }

    /**
     * @param array $rows the trimmed markdown table rows including the separator row
     * @return string the html table with the first row as header
     */
    private function table(array $rows): string
    {
        $result = '<table>' . "\n";
        $result .= '<thead><tr>';
        foreach ($this->cells($rows[0]) as $cell) {
            $result .= '<th>' . $this->inline($cell) . '</th>';
        }
        $result .= '</tr></thead>' . "\n" . '<tbody>' . "\n";
        foreach (array_slice($rows, 2) as $row) {
            $result .= '<tr>';
            foreach ($this->cells($row) as $cell) {
                $result .= '<td>' . $this->inline($cell) . '</td>';
            }
            $result .= '</tr>' . "\n";
        }
        return $result . '</tbody>' . "\n" . '</table>' . "\n";
    }

    /**
     * @param string $row a markdown table row e.g. "| a | b |"
     * @return array the trimmed cell texts
     */
    private function cells(string $row): array
    {
        $row = preg_replace('/^\||\|$/', '', $row);
        return array_map('trim', explode('|', $row));
    }

    /**
     * @param string $text the markdown text of a line
     * @return string the html code with the code spans, images, links, bold and italic text
     */
    private function inline(string $text): string
    {
        // the code spans are replaced by a placeholder first, so that their text stays as it is
        $codes = [];
        $text = preg_replace_callback('/`([^`]+)`/', function (array $m) use (&$codes): string {
            $codes[] = '<code>' . $this->esc($m[1]) . '</code>';
            return self::CODE_MARK . (count($codes) - 1) . self::CODE_MARK;
        }, $text);
        $text = $this->esc($text);
        $text = preg_replace_callback('/!\[([^]]*)]\(([^)\s]+)\)/', fn(array $m): string => $this->image($m[1], $m[2]), $text);
        $text = preg_replace_callback('/\[([^]]+)]\(([^)\s]+)\)/', fn(array $m): string => $this->link($m[1], $m[2]), $text);
        $text = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $text);
        // a single star only, because an underscore is part of many names e.g. idea_and_intro
        $text = preg_replace('/(?<![*\w])\*(?!\s)(.+?)(?<!\s)\*(?![*\w])/', '<em>$1</em>', $text);
        return preg_replace_callback('/' . self::CODE_MARK . '(\d+)' . self::CODE_MARK . '/',
            fn(array $m): string => $codes[(int)$m[1]], $text);
    }

    /**
     * @param string $name the already escaped link text
     * @param string $url the already escaped url of the markdown link
     * @return string the html link or just the text if the url scheme is not allowed
     */
    private function link(string $name, string $url): string
    {
        $href = $this->href(html_entity_decode($url, ENT_QUOTES));
        // the file name of a document is shown as a name e.g. "exchange_json.md" as "exchange json"
        if (preg_match(self::DOC_FILE_TEXT, $name, $match)) {
            $name = str_replace('_', ' ', $match[1]);
        }
        $result = $name;
        if ($href != '') {
            $result = '<a href="' . htmlspecialchars($href, ENT_QUOTES) . '">' . $name . '</a>';
        }
        return $result;
    }

    /**
     * @param string $alt the already escaped alternative text
     * @param string $url the already escaped url of the image
     * @return string the html image or just the alternative text if the url scheme is not allowed
     */
    private function image(string $alt, string $url): string
    {
        $src = html_entity_decode($url, ENT_QUOTES);
        // the page of an image on github is no image, so an image of /docs/ is taken from the own server
        if (preg_match(self::DOC_IMAGE, $src)) {
            $href = self::DOCS_URL . $src;
        } else {
            $href = $this->href($src);
        }
        $result = $alt;
        if ($href != '') {
            $result = '<img src="' . htmlspecialchars($href, ENT_QUOTES) . '" alt="' . $alt . '">';
        }
        return $result;
    }

    /**
     * @param string $url the url of a markdown link e.g. "faq.md", "../README.md" or "https://www.wikidata.org/"
     * @return string the url for the html page or an empty text if the url scheme is not allowed e.g. "javascript:"
     */
    private function href(string $url): string
    {
        $result = '';
        if (preg_match(self::DOC_LINK, $url, $match)) {
            // a document of /docs/ is shown by http/docs.php
            $result = $this->doc_url($match[1]) . ($match[2] ?? '');
        } elseif (preg_match('/^(https?:|mailto:|#)/i', $url)) {
            $result = $url;
        } elseif (!preg_match('/^[a-z][a-z0-9+.-]*:/i', $url) and !str_starts_with($url, '/')) {
            // any other relative file e.g. an image or the readme is shown by github
            $result = self::GITHUB_DOCS . $url;
        }
        return $result;
    }

    /**
     * @param string $heading the markdown text of a heading
     * @return string the id of the heading as github creates it, so that a link like "#the-idea" works
     */
    private function anchor(string $heading): string
    {
        $text = mb_strtolower(str_replace(['*', '`'], '', $heading));
        $text = preg_replace('/[^\p{L}\p{N} _-]/u', '', $text);
        return str_replace(' ', '-', trim($text));
    }

    /**
     * @param string $text a text e.g. of a code block
     * @return string the text with the html special chars escaped
     */
    private function esc(string $text): string
    {
        return htmlspecialchars($text, ENT_NOQUOTES);
    }

}
