<?php

/*

    web/html/pdf_writer.php - a minimal writer for a pdf with text, tables and one jpeg image
    -----------------------

    the suggested var name is $pdf
    uses the standard fonts of each pdf reader, so no font is embedded and only the characters
    of the Windows-1252 encoding can be shown; a table is scaled down to the page width, so that
    no text of a cell is ever cut

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

class pdf_writer
{

    const string MIME_TYPE = 'application/pdf';
    const string FILE_EXTENSION = '.pdf';
    // the size of a landscape A4 page in points
    const int PAGE_WIDTH = 842;
    const int PAGE_HEIGHT = 595;
    const int MARGIN = 36;
    const float TEXT_SIZE = 9;
    const float TITLE_SIZE = 16;
    const float HEADING_SIZE = 12;
    const float LINE_FACTOR = 1.35;
    const float CELL_PADDING = 6;
    const int IMAGE_WIDTH = 60;
    // the space a heading needs below itself, so that it is never the last line of a page
    const float HEADING_KEEP = 40;
    const string FONT_NORMAL = 'F1';
    const string FONT_BOLD = 'F2';
    const string IMAGE_NAME = 'Im1';
    const string JPEG_START = "\xFF\xD8\xFF";
    const string ENCODING = 'Windows-1252';
    // the width of the printable ascii chars from the space on in 1/1000 of the font size of the standard font Helvetica
    const array CHAR_WIDTHS = [
        278, 278, 355, 556, 556, 889, 667, 191, 333, 333, 389, 584, 278, 333, 278, 278,
        556, 556, 556, 556, 556, 556, 556, 556, 556, 556, 278, 278, 584, 584, 584, 556,
        1015, 667, 667, 722, 722, 667, 611, 778, 722, 278, 500, 667, 556, 833, 722, 778,
        667, 778, 722, 667, 611, 722, 667, 944, 667, 667, 611, 278, 278, 278, 469, 556,
        333, 556, 556, 500, 556, 556, 278, 556, 556, 222, 222, 500, 222, 833, 556, 556,
        556, 556, 333, 500, 278, 556, 500, 722, 500, 500, 500, 334, 260, 334, 584
    ];
    const int CHAR_WIDTH_FIRST = 32;
    const int CHAR_WIDTH_OTHER = 556;
    // the bold font is wider than the normal font for which the widths are given
    const float BOLD_FACTOR = 1.1;

    // the content stream of each finished page
    private array $pages = [];
    // the content stream of the current page
    private string $content = '';
    private float $y = self::PAGE_HEIGHT - self::MARGIN;
    private string $img_data = '';
    private int $img_width = 0;
    private int $img_height = 0;
    private int $img_channels = 0;


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
        $width = self::PAGE_WIDTH - 2 * self::MARGIN;
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
        $size = min(self::TEXT_SIZE, (self::PAGE_WIDTH - 2 * self::MARGIN - $padding) / max(1, array_sum($widths)));
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
        $this->new_page();
        $fonts = '<< /F1 3 0 R /F2 4 0 R >>';
        $objects = [
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            3 => $this->font_object('Helvetica'),
            4 => $this->font_object('Helvetica-Bold'),
            5 => $this->image_object()
        ];
        $image = $this->img_data == '' ? '' : ' /XObject << /' . self::IMAGE_NAME . ' 5 0 R >>';
        $kids = [];
        foreach ($this->pages as $pos => $stream) {
            $page_id = 6 + 2 * $pos;
            $kids[] = $page_id . ' 0 R';
            $objects[$page_id] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 ' . self::PAGE_WIDTH . ' ' . self::PAGE_HEIGHT . ']'
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

    private function new_page(): void
    {
        $this->pages[] = $this->content;
        $this->content = '';
        $this->y = self::PAGE_HEIGHT - self::MARGIN;
    }

    private function draw_text(string $text, float $x, float $y, float $size, bool $bold): void
    {
        $font = $bold ? self::FONT_BOLD : self::FONT_NORMAL;
        $this->content .= 'BT /' . $font . ' ' . $this->num($size) . ' Tf ' . $this->num($x) . ' ' . $this->num($y) . ' Td ('
            . $this->escaped($text) . ") Tj ET\n";
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
        foreach (unpack('C*', $this->encoded($text)) ?: [] as $code) {
            $sum += self::CHAR_WIDTHS[$code - self::CHAR_WIDTH_FIRST] ?? self::CHAR_WIDTH_OTHER;
        }
        return $sum * $size / 1000 * ($bold ? self::BOLD_FACTOR : 1);
    }

    /**
     * @param string $text the text in utf-8
     * @return string the text in the encoding of the fonts, with a question mark for a char that the encoding does not have
     */
    private function encoded(string $text): string
    {
        return mb_convert_encoding($text, self::ENCODING, 'UTF-8');
    }

    /**
     * @param string $text the text in utf-8
     * @return string the text as the content of a pdf string
     */
    private function escaped(string $text): string
    {
        return strtr($this->encoded($text), ['\\' => '\\\\', '(' => '\\(', ')' => '\\)']);
    }

    private function num(float $num): string
    {
        return number_format($num, 2, '.', '');
    }

    private function font_object(string $name): string
    {
        return '<< /Type /Font /Subtype /Type1 /BaseFont /' . $name . ' /Encoding /WinAnsiEncoding >>';
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
