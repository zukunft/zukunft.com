<?php

/*

    web/html/pdf_writer.php - a minimal writer for a pdf with text, tables and one jpeg image
    -----------------------

    the suggested var name is $pdf
    embeds the glyphs that the pdf uses of a TrueType font with the Identity-H encoding (pdf_font),
    so that any char of the font can be shown e.g. the CO₂ of a page; DejaVu Sans by default or Noto Sans,
    whose missing chars e.g. the ≈ are shown in DejaVu Sans; a table is scaled down to the page width,
    so that no text of a cell is ever cut

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

namespace Zukunft\ZukunftCom\main\php\web\html;

use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::HTML . 'pdf_font.php';
include_once html_paths::SHARED_CONST . 'files.php';

use Closure;
use Zukunft\ZukunftCom\main\php\shared\const\files;

class pdf_writer
{

    const string MIME_TYPE = 'application/pdf';
    const string FILE_EXTENSION = '.pdf';
    // the long and the short side of an A4 page in points, which is landscape by default and portrait on request
    const int PAGE_LONG = 842;
    const int PAGE_SHORT = 595;
    const int MARGIN = 36;
    // the footer line of each page, below the content within the bottom margin
    const float FOOTER_SIZE = 7;
    const float FOOTER_Y = 18;
    const float FOOTER_GAP = 12;
    const string FOOTER_CUT = '...';
    const float TEXT_SIZE = 9;
    const float TITLE_SIZE = 16;
    const float HEADING_SIZE = 12;
    const float LINE_FACTOR = 1.35;
    const float CELL_PADDING = 6;
    const int IMAGE_WIDTH = 60;
    // the space a heading needs below itself, so that it is never the last line of a page
    const float HEADING_KEEP = 40;
    // the font families that a pdf can use with the files of their normal and bold font
    const string FAMILY_DEJAVU = 'DejaVu Sans';
    const string FAMILY_NOTO = 'Noto Sans';
    const array FAMILIES = [
        self::FAMILY_DEJAVU => [files::PDF_FONT_DEJAVU, files::PDF_FONT_DEJAVU_BOLD],
        self::FAMILY_NOTO => [files::PDF_FONT_NOTO, files::PDF_FONT_NOTO_BOLD]
    ];
    // the family with the most chars, which shows a char that the chosen family does not have e.g. the ≈ for Noto Sans
    const string FAMILY_FALLBACK = self::FAMILY_DEJAVU;
    // the names of the fonts on a page: the chosen family and its fallback
    const string FONT_NORMAL = 'F1';
    const string FONT_BOLD = 'F2';
    const string FONT_FALLBACK_NORMAL = 'F3';
    const string FONT_FALLBACK_BOLD = 'F4';
    // the ids of the pdf objects: the catalog, the page list, the image, then the objects of each font and then the pages
    const int OBJECT_IMAGE = 3;
    const int OBJECT_FIRST_FONT = 4;
    const string IMAGE_NAME = 'Im1';
    const string JPEG_START = "\xFF\xD8\xFF";

    // the size of the current page in points
    private int $width = self::PAGE_LONG;
    private int $height = self::PAGE_SHORT;
    // the content stream and the size of each finished page, which can be portrait or landscape
    private array $pages = [];
    private array $sizes = [];
    // the content stream of the current page
    private string $content = '';
    private float $y;
    // the texts on the left and in the middle of the footer of each page and the function that gives the text
    // on the right of a page from its number and the number of all pages, null for pages without a footer
    private string $footer_left = '';
    private string $footer_center = '';
    private ?Closure $footer_page = null;
    // the fonts by their name on a page and per font the unicode code point of each glyph that the pdf shows by the glyph id
    private array $fonts = [];
    private array $used = [];
    // per weight (0 normal, 1 bold) the names of the fonts in the order in which a char is looked up
    private array $lookup = [[], []];
    private string $img_data = '';
    private int $img_width = 0;
    private int $img_height = 0;
    private int $img_channels = 0;


    /**
     * @param bool $portrait true for A4 portrait pages e.g. for a document to read, else A4 landscape e.g. for wide tables;
     *                       a page break can change the format of the next pages
     * @param string $family the font family of the texts e.g. self::FAMILY_NOTO, DejaVu Sans if the family is unknown;
     *                       a char that the family does not have is shown in the fallback family
     */
    function __construct(bool $portrait = false, string $family = self::FAMILY_DEJAVU)
    {
        $this->set_format($portrait);
        if (!array_key_exists($family, self::FAMILIES)) {
            log_warning('the pdf font family ' . $family . ' is unknown, so the pdf uses ' . self::FAMILY_DEJAVU);
            $family = self::FAMILY_DEJAVU;
        }
        [$normal, $bold] = self::FAMILIES[$family];
        $this->add_font(self::FONT_NORMAL, $normal, false);
        $this->add_font(self::FONT_BOLD, $bold, true);
        if ($family != self::FAMILY_FALLBACK) {
            [$normal, $bold] = self::FAMILIES[self::FAMILY_FALLBACK];
            $this->add_font(self::FONT_FALLBACK_NORMAL, $normal, false);
            $this->add_font(self::FONT_FALLBACK_BOLD, $bold, true);
        }
    }

    /**
     * @param string $font the name of the font on a page e.g. self::FONT_NORMAL
     * @param string $file the path of the font file
     * @param bool $bold true for a bold font, which is looked up for a bold text
     */
    private function add_font(string $font, string $file, bool $bold): void
    {
        $this->fonts[$font] = new pdf_font($file);
        $this->used[$font] = [];
        $this->lookup[(int)$bold][] = $font;
    }


    /*
     * set
     */

    /**
     * add a footer line to each page, which is written with the file, because only then the number of pages is known
     *
     * @param string $left the text on the left e.g. the name of the pod that creates the document
     * @param string $center the text in the middle of the page e.g. the name of the document and its time,
     *                       shortened so that it reaches neither the left nor the right text
     * @param Closure $page_text gives the text on the right from the number of the page and the number of all pages
     *                           e.g. "Page 2 of 5"
     */
    function set_footer(string $left, string $center, Closure $page_text): void
    {
        $this->footer_left = $left;
        $this->footer_center = $center;
        $this->footer_page = $page_text;
    }


    /*
     * add
     */

    /**
     * add a jpeg image at the current position, of which a pdf can use the data as it is
     *
     * @param string $data the content of the image file
     * @return bool true if the data is a jpeg image and has been added, false for anything else e.g. a png or an empty file
     */
    function image(string $data): bool
    {
        // only a jpeg start, because php raises a notice for a short text
        $info = str_starts_with($data, self::JPEG_START) ? getimagesizefromstring($data) : false;
        $is_jpeg = $info !== false;
        if ($is_jpeg) {
            $this->img_data = $data;
            $this->img_width = $info[0];
            $this->img_height = $info[1];
            $this->img_channels = $info['channels'] ?? 3;
            $height = self::IMAGE_WIDTH * $info[1] / $info[0];
            $this->y -= $height;
            $this->content .= 'q ' . self::IMAGE_WIDTH . ' 0 0 ' . $this->num($height) . ' ' . self::MARGIN . ' ' . $this->num($this->y)
                . ' cm /' . self::IMAGE_NAME . " Do Q\n";
            $this->y -= self::TEXT_SIZE;
        }
        return $is_jpeg;
    }

    /**
     * @param string $text the title of the document
     */
    function title(string $text): void
    {
        $this->text($text, self::TITLE_SIZE, true);
    }

    /**
     * @param string $text the heading of the next section
     */
    function heading(string $text): void
    {
        $this->y -= self::HEADING_SIZE;
        $this->keep_space(self::HEADING_KEEP);
        $this->text($text, self::HEADING_SIZE, true);
    }

    /**
     * add a text that is wrapped at the page width
     *
     * @param string $text the text in utf-8
     * @param float $size the font size
     * @param bool $bold true for the bold font
     */
    function text(string $text, float $size = self::TEXT_SIZE, bool $bold = false): void
    {
        $width = $this->width - 2 * self::MARGIN;
        $line = '';
        foreach (preg_split('/\s+/u', trim($text)) as $word) {
            $next = $line == '' ? $word : $line . ' ' . $word;
            if ($line != '' and $this->text_width($next, $size, $bold) > $width) {
                $this->text_line($line, $size, $bold);
                $next = $word;
            }
            $line = $next;
        }
        if ($line != '') {
            $this->text_line($line, $size, $bold);
        }
    }

    /**
     * continue on a new page e.g. for an appendix, unless the current page is still empty
     *
     * @param bool|null $portrait true for a portrait page e.g. for a text, false for a landscape page e.g. for a wide table,
     *                            null to keep the format of the current page
     */
    function page_break(?bool $portrait = null): void
    {
        if ($this->content != '') {
            $this->new_page();
        }
        if ($portrait !== null) {
            $this->set_format($portrait);
        }
    }

    /**
     * @param array $head the text of each header cell, empty for a table without a header
     * @param array $rows per row the text of each cell
     * @return bool true if the table fits a portrait page with the normal text size, false if it would be scaled down
     */
    function fits_portrait(array $head, array $rows): bool
    {
        $widths = $this->column_widths($head, $rows);
        $table_width = array_sum($widths) * self::TEXT_SIZE + count($widths) * self::CELL_PADDING;
        return $table_width <= self::PAGE_SHORT - 2 * self::MARGIN;
    }

    /**
     * add half a line of space e.g. between two entries of a list of texts
     */
    function space(): void
    {
        $this->y -= self::TEXT_SIZE * self::LINE_FACTOR / 2;
    }

    /**
     * add a table that is as wide as its texts and scaled down if it is wider than the page
     *
     * @param array $head the text of each header cell, empty for a table without a header
     * @param array $rows per row the text of each cell
     * @param array $right per column true if its cells are aligned to the right e.g. a number
     */
    function table(array $head, array $rows, array $right): void
    {
        $widths = $this->column_widths($head, $rows);
        $padding = count($widths) * self::CELL_PADDING;
        $size = min(self::TEXT_SIZE, ($this->width - 2 * self::MARGIN - $padding) / max(1, array_sum($widths)));
        $height = $size * self::LINE_FACTOR;
        $this->keep_space(2 * $height);
        $this->table_row($head, $widths, $right, $size, true);
        foreach ($rows as $row) {
            if ($this->y - $height < self::MARGIN) {
                $this->new_page();
                $this->table_row($head, $widths, $right, $size, true);
            }
            $this->table_row($row, $widths, $right, $size, false);
        }
        $this->y -= $height;
    }


    /*
     * output
     */

    /**
     * @return string the complete pdf file with at least one page
     */
    function output(): string
    {
        // the last page only if it has content, so that a document never ends with an empty page
        if ($this->content != '' or $this->pages == []) {
            $this->new_page();
        }
        $this->add_footers();
        $objects = [
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            self::OBJECT_IMAGE => $this->image_object()
        ];
        $fonts = [];
        $font_id = self::OBJECT_FIRST_FONT;
        // only the fonts that a page uses e.g. not the fallback font of a text that the chosen font can show
        foreach ($this->fonts as $font => $fnt) {
            if ($this->used[$font] != []) {
                $fonts[] = '/' . $font . ' ' . $font_id . ' 0 R';
                $objects += $fnt->objects($font_id, $this->used[$font]);
                $font_id += pdf_font::OBJECT_COUNT;
            }
        }
        $fonts = '<< ' . implode(' ', $fonts) . ' >>';
        $image = $this->img_data == '' ? '' : ' /XObject << /' . self::IMAGE_NAME . ' ' . self::OBJECT_IMAGE . ' 0 R >>';
        $kids = [];
        foreach ($this->pages as $pos => $stream) {
            $page_id = $font_id + 2 * $pos;
            $kids[] = $page_id . ' 0 R';
            [$width, $height] = $this->sizes[$pos];
            $objects[$page_id] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 ' . $width . ' ' . $height . ']'
                . ' /Resources << /Font ' . $fonts . $image . ' >> /Contents ' . ($page_id + 1) . ' 0 R >>';
            $objects[$page_id + 1] = '<< /Length ' . strlen($stream) . " >>\nstream\n" . $stream . "\nendstream";
        }
        $objects[2] = '<< /Type /Pages /Kids [' . implode(' ', $kids) . '] /Count ' . count($kids) . ' >>';
        ksort($objects);
        return $this->file_of_objects($objects);
    }


    /*
     * internal
     */

    /**
     * add the footer line to each finished page
     */
    private function add_footers(): void
    {
        if ($this->footer_page !== null) {
            $count = count($this->pages);
            foreach ($this->pages as $pos => $stream) {
                $right = ($this->footer_page)($pos + 1, $count);
                $this->content = '';
                $this->add_footer($right, $this->sizes[$pos][0]);
                $this->pages[$pos] = $stream . $this->content;
            }
            $this->content = '';
        }
    }

    /**
     * write the footer line of one page: the text of the page on the right, the text on the left shortened so that
     * it never reaches the right text, and the text in the middle of the page shortened so that it reaches neither
     *
     * @param string $right the text of the page on the right e.g. "Page 2 of 5"
     * @param int $page_width the width of the page in points, which differs for a portrait and a landscape page
     */
    private function add_footer(string $right, int $page_width): void
    {
        $line = $page_width - 2 * self::MARGIN;
        $right_width = $this->text_width($right, self::FOOTER_SIZE, false);
        $left_space = $line - $right_width - self::FOOTER_GAP;
        $left = $this->shortened($this->footer_left, $left_space, self::FOOTER_SIZE);
        $left_width = $this->text_width($left, self::FOOTER_SIZE, false);
        // the middle text is centered on the page, so it has the space between the wider side text and the middle twice
        $side = max($left_width, $right_width) + self::FOOTER_GAP;
        $center_space = $line - 2 * $side;
        $center = $this->shortened($this->footer_center, $center_space, self::FOOTER_SIZE);
        $center_width = $this->text_width($center, self::FOOTER_SIZE, false);
        $this->footer_text($left, self::MARGIN, $left_width <= $left_space);
        $this->footer_text($center, ($page_width - $center_width) / 2, $center_width <= $center_space);
        $this->footer_text($right, $page_width - self::MARGIN - $right_width, true);
    }

    /**
     * @param string $text a text of the footer line
     * @param float $x the left side of the text in points from the left side of the page
     * @param bool $fits false if not even the cut sign fits the space of the text, which then is left out
     */
    private function footer_text(string $text, float $x, bool $fits): void
    {
        if ($text != '' and $fits) {
            $this->draw_text($text, $x, self::FOOTER_Y, self::FOOTER_SIZE, false);
        }
    }

    /**
     * @param string $text the text in utf-8
     * @param float $width the space for the text in points
     * @param float $size the font size
     * @return string the text, or its words up to the first that does not fit with the cut sign if it is wider than the space
     */
    private function shortened(string $text, float $width, float $size): string
    {
        $result = $text;
        if ($this->text_width($text, $size, false) > $width) {
            $result = '';
            $fits = true;
            foreach (preg_split('/\s+/u', trim($text)) as $word) {
                $next = $result == '' ? $word : $result . ' ' . $word;
                $fits = ($fits and $this->text_width($next . self::FOOTER_CUT, $size, false) <= $width);
                if ($fits) {
                    $result = $next;
                }
            }
            $result .= self::FOOTER_CUT;
        }
        return $result;
    }

    /**
     * @param array $head the text of each header cell
     * @param array $rows per row the text of each cell
     * @return array per column the width of its widest text at font size 1
     */
    private function column_widths(array $head, array $rows): array
    {
        $result = [];
        foreach ($head as $c => $text) {
            $result[$c] = $this->text_width($text, 1, true);
        }
        foreach ($rows as $row) {
            foreach ($row as $c => $text) {
                $result[$c] = max($result[$c] ?? 0, $this->text_width($text, 1, false));
            }
        }
        ksort($result);
        return $result;
    }

    /**
     * @param array $cells the text of each cell, empty for no row e.g. a table without a header
     * @param array $widths per column the width of its widest text at font size 1
     * @param array $right per column true if its cells are aligned to the right
     * @param float $size the font size
     * @param bool $bold true for the header row
     */
    private function table_row(array $cells, array $widths, array $right, float $size, bool $bold): void
    {
        if ($cells != []) {
            $this->y -= $size * self::LINE_FACTOR;
            $x = (float)self::MARGIN;
            foreach ($widths as $c => $width) {
                $text = $cells[$c] ?? '';
                $col_width = $width * $size;
                $text_x = ($right[$c] ?? false) ? $x + $col_width - $this->text_width($text, $size, $bold) : $x;
                $this->draw_text($text, $text_x, $this->y, $size, $bold);
                $x += $col_width + self::CELL_PADDING;
            }
        }
    }

    /**
     * @param string $line the text of one line
     * @param float $size the font size
     * @param bool $bold true for the bold font
     */
    private function text_line(string $line, float $size, bool $bold): void
    {
        $height = $size * self::LINE_FACTOR;
        $this->keep_space($height);
        $this->y -= $height;
        $this->draw_text($line, self::MARGIN, $this->y, $size, $bold);
    }

    /**
     * @param float $height the space that the next content needs
     */
    private function keep_space(float $height): void
    {
        if ($this->y - $height < self::MARGIN) {
            $this->new_page();
        }
    }

    /**
     * finish the current page and continue on a page of the same format
     */
    private function new_page(): void
    {
        $this->pages[] = $this->content;
        $this->sizes[] = [$this->width, $this->height];
        $this->content = '';
        $this->y = $this->height - self::MARGIN;
    }

    /**
     * set the format of the current page, which is still empty, and of the next pages
     *
     * @param bool $portrait true for A4 portrait, false for A4 landscape
     */
    private function set_format(bool $portrait): void
    {
        $this->width = self::PAGE_LONG;
        $this->height = self::PAGE_SHORT;
        if ($portrait) {
            $this->width = self::PAGE_SHORT;
            $this->height = self::PAGE_LONG;
        }
        $this->y = $this->height - self::MARGIN;
    }

    /**
     * write the text in runs of the same font, so that a char that the chosen font does not have is shown in the fallback font;
     * a run of a font whose file cannot be read is left out, because the pdf would else name a font that it does not have
     *
     * @param string $text the text in utf-8
     * @param float $x the left side of the text in points from the left side of the page
     * @param float $y the base line of the text in points from the bottom of the page
     * @param float $size the font size
     * @param bool $bold true for the bold font
     */
    private function draw_text(string $text, float $x, float $y, float $size, bool $bold): void
    {
        $this->content .= 'BT ' . $this->num($x) . ' ' . $this->num($y) . ' Td';
        foreach ($this->runs($text, $bold) as [$font, $codes]) {
            $fnt = $this->fonts[$font];
            if ($fnt->has_glyphs()) {
                foreach ($codes as $code) {
                    $this->used[$font][$fnt->glyph_id($code)] ??= $code;
                }
                $this->content .= ' /' . $font . ' ' . $this->num($size) . ' Tf ' . $this->glyph_string($font, $codes) . ' Tj';
            }
        }
        $this->content .= " ET\n";
    }

    /**
     * @param string $text the text in utf-8
     * @param float $size the font size
     * @param bool $bold true for the bold font
     * @return float the width of the text in points
     */
    function text_width(string $text, float $size, bool $bold): float
    {
        $sum = 0;
        foreach ($this->codes($text) as $code) {
            $fnt = $this->fonts[$this->font_of($code, $bold)];
            $sum += $fnt->width($fnt->glyph_id($code));
        }
        return $sum * $size / pdf_font::PDF_UNITS;
    }

    /**
     * @param string $text the text in utf-8
     * @param bool $bold true for the bold font
     * @return string the text as a pdf string of the glyph ids of the chosen font e.g. "<00260052>",
     *                as a page shows a text of which the chosen font has every char
     */
    function text_code(string $text, bool $bold = false): string
    {
        return $this->glyph_string($this->lookup[(int)$bold][0], $this->codes($text));
    }

    /**
     * @param string $text the text in utf-8
     * @param bool $bold true for the bold font
     * @return array the parts of the text that one font shows, each as the name of the font and the code points of the chars
     */
    private function runs(string $text, bool $bold): array
    {
        $result = [];
        $font = '';
        foreach ($this->codes($text) as $code) {
            $code_font = $this->font_of($code, $bold);
            if ($code_font != $font) {
                $result[] = [$code_font, []];
                $font = $code_font;
            }
            $result[count($result) - 1][1][] = $code;
        }
        return $result;
    }

    /**
     * @param int $code the unicode code point of a char
     * @param bool $bold true for the bold font
     * @return string the name of the first font that has the char, the chosen font if no font has it
     */
    private function font_of(int $code, bool $bold): string
    {
        $fonts = $this->lookup[(int)$bold];
        $result = '';
        foreach ($fonts as $font) {
            if ($result == '' and $this->fonts[$font]->has_glyph($code)) {
                $result = $font;
            }
        }
        if ($result == '') {
            $result = $fonts[0];
        }
        return $result;
    }

    /**
     * @param string $font the name of a font on a page
     * @param array $codes the unicode code points of the chars
     * @return string the chars as a pdf string of the glyph ids of the font e.g. "<00260052>"
     */
    private function glyph_string(string $font, array $codes): string
    {
        $result = '';
        foreach ($codes as $code) {
            $result .= sprintf('%04X', $this->fonts[$font]->glyph_id($code));
        }
        return '<' . $result . '>';
    }

    /**
     * @param string $text the text in utf-8
     * @return array the unicode code point of each char, an invalid byte replaced by a question mark
     */
    private function codes(string $text): array
    {
        $valid = mb_scrub($text, pdf_font::TEXT_ENCODING);
        $chars = mb_str_split($valid, 1, pdf_font::TEXT_ENCODING);
        return array_map(fn(string $chr): int => mb_ord($chr, pdf_font::TEXT_ENCODING), $chars);
    }

    /**
     * @param float $num a position or a size in points
     * @return string the number with two decimals and a dot, as a pdf expects it e.g. "36.00"
     */
    private function num(float $num): string
    {
        return number_format($num, 2, '.', '');
    }

    /**
     * @return string the object of the image, an empty stream if the document has no image, so that the object ids are always the same
     */
    private function image_object(): string
    {
        $colour = match ($this->img_channels) {
            1 => '/DeviceGray',
            4 => '/DeviceCMYK',
            default => '/DeviceRGB'
        };
        return '<< /Type /XObject /Subtype /Image /Width ' . $this->img_width . ' /Height ' . $this->img_height
            . ' /ColorSpace ' . $colour . ' /BitsPerComponent 8 /Filter /DCTDecode /Length ' . strlen($this->img_data)
            . " >>\nstream\n" . $this->img_data . "\nendstream";
    }

    /**
     * @param array $objects the content of each pdf object by its id, without a gap in the ids
     * @return string the pdf file with the objects and their position table
     */
    private function file_of_objects(array $objects): string
    {
        $result = "%PDF-1.4\n";
        $table = '';
        foreach ($objects as $id => $object) {
            $table .= sprintf("%010d 00000 n \n", strlen($result));
            $result .= $id . " 0 obj\n" . $object . "\nendobj\n";
        }
        $count = count($objects) + 1;
        $start = strlen($result);
        $result .= "xref\n0 " . $count . "\n0000000000 65535 f \n" . $table;
        $result .= 'trailer << /Size ' . $count . " /Root 1 0 R >>\nstartxref\n" . $start . "\n%%EOF\n";
        return $result;
    }

}
