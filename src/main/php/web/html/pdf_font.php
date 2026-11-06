<?php

/*

    web/html/pdf_font.php - a TrueType font that a pdf embeds as a subset with the Identity-H encoding
    ---------------------

    the suggested var name is $fnt
    reads the glyph of each char and its width from the font file and writes the pdf objects of the
    font with only the glyphs that the pdf uses; the code of a char in the pdf is its glyph id in the
    font (Identity-H), so any char of the font can be shown, and the ToUnicode map lets a reader copy
    and search the text; this needs a font that shows each char with its own glyph, which DejaVu Sans
    and Noto Sans do, and the missing glyph is copied as the replacement char

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

include_once html_paths::USER . 'user_message.php';

use Zukunft\ZukunftCom\main\php\web\user\user_message;

class pdf_font
{

    // the number of pdf objects of a font: the font, its glyphs, its descriptor, its file and its ToUnicode map
    const int OBJECT_COUNT = 5;
    // the glyph that a font shows for a char that it does not have and the char that a reader copies for it
    const int MISSING_GLYPH = 0;
    const int REPLACEMENT_CHAR = 0xFFFD;
    // the encoding of the texts and the encoding of the chars in the ToUnicode map
    const string TEXT_ENCODING = 'UTF-8';
    const string UNICODE_ENCODING = 'UTF-16BE';
    // the widths of a pdf font are in 1/1000 of the font size
    const int PDF_UNITS = 1000;
    // the tables of the font file that the font reader needs and the ones that a pdf needs to show the glyphs (ISO 32000-1, 9.9)
    const array REQUIRED_TABLES = ['cmap', 'glyf', 'head', 'hhea', 'hmtx', 'loca', 'maxp'];
    const array SUBSET_TABLES = ['cvt ', 'fpgm', 'glyf', 'head', 'hhea', 'hmtx', 'loca', 'maxp', 'prep'];
    // the position and the size of the values in the font file and in its tables (see the OpenType specification)
    const int FILE_VERSION = 0x00010000;
    const int FILE_TABLE_COUNT = 4;
    const int FILE_HEADER = 12;
    const int TABLE_TAG_LENGTH = 4;
    const int TABLE_RECORD = 16;
    const int TABLE_RECORD_POS = 8;
    const int TABLE_RECORD_LENGTH = 12;
    const int SIZE_U16 = 2;
    const int SIZE_U32 = 4;
    const int U16_RANGE = 0x10000;
    const int I16_NEGATIVE = 0x8000;
    const int U32_MASK = 0xFFFFFFFF;
    const int HEAD_CHECKSUM_ADJUSTMENT = 8;
    const int HEAD_UNITS_PER_EM = 18;
    const int HEAD_X_MIN = 36;
    const int HEAD_BOX_VALUES = 4;
    const int HEAD_LOCA_FORMAT = 50;
    // the loca table has the long glyph positions or the short ones, which are the half of the position
    const int LOCA_LONG = 1;
    const int LOCA_SHORT_FACTOR = 2;
    const int HHEA_ASCENDER = 4;
    const int HHEA_DESCENDER = 6;
    const int HHEA_METRICS = 34;
    const int HMTX_METRIC = 4;
    const int MAXP_GLYPHS = 4;
    const int OS2_CAP_HEIGHT = 88;
    const int OS2_CAP_HEIGHT_VERSION = 2;
    // a glyph starts with the number of its outlines, which is negative for a glyph composed of other glyphs
    const int GLYPH_HEADER = 10;
    // a part of a composed glyph starts with its flags and its glyph id; the flags say how many bytes follow
    const int PART_HEADER = 4;
    const int PART_WORDS = 0x0001;
    const int PART_SCALE = 0x0008;
    const int PART_MORE = 0x0020;
    const int PART_XY_SCALE = 0x0040;
    const int PART_MATRIX = 0x0080;
    const int PART_ARGS_WORDS = 4;
    const int PART_ARGS_BYTES = 2;
    const int PART_SCALE_SIZE = 2;
    const int PART_XY_SCALE_SIZE = 4;
    const int PART_MATRIX_SIZE = 8;
    // the char maps that the font reader can use: the full unicode map first and the map of the basic plane
    const int CMAP_COUNT = 2;
    const int CMAP_RECORDS = 4;
    const int CMAP_RECORD = 8;
    const int CMAP_RECORD_OFFSET = 4;
    const int CMAP_FULL_FORMAT = 12;
    const int CMAP_FULL_COUNT = 12;
    const int CMAP_FULL_GROUPS = 16;
    const int CMAP_FULL_GROUP = 12;
    const int CMAP_FULL_END = 4;
    const int CMAP_FULL_GLYPH = 8;
    const int CMAP_BASIC_FORMAT = 4;
    const int CMAP_BASIC_SEGMENTS = 6;
    const int CMAP_BASIC_ENDS = 14;
    const int CMAP_BASIC_END = 0xFFFF;
    // the descriptor of a font with latin chars that is not a symbol font (ISO 32000-1, 9.8.2)
    const int DESCRIPTOR_FLAGS = 32;
    const int DESCRIPTOR_STEM = 80;
    // the entries of one block of the ToUnicode map, which may have at most 100
    const int TO_UNICODE_BLOCK = 100;
    // the length of the tag that names the subset of a font e.g. "ABCDEF+DejaVuSans"
    const int SUBSET_TAG_LENGTH = 6;

    // the name of the font, which is the name of its file without the extension e.g. DejaVuSans-Bold
    private string $name;
    // the content of the font file and the position and length of each table by its tag
    private string $data = '';
    private array $tables = [];
    // the size of the glyphs in the font file, the glyph of each unicode char and the width of each glyph
    private int $units_per_em = self::PDF_UNITS;
    private array $glyphs = [];
    private array $widths = [];


    /**
     * @param string $file the path of the TrueType font file e.g. files::PDF_FONT_DEJAVU
     * @param user_message $msg to tell the user that the pdf shows no text in this font, because the file cannot be used
     */
    function __construct(string $file, user_message $msg)
    {
        $this->name = pathinfo($file, PATHINFO_FILENAME);
        $data = false;
        if (is_readable($file)) {
            $data = file_get_contents($file);
        }
        if ($data === false) {
            log_err_msg_ui('the pdf font ' . $file . ' cannot be read, so the pdf shows no text in this font', $msg);
        } else {
            $this->data = $data;
            $this->read_tables();
            $missing = array_diff(self::REQUIRED_TABLES, array_keys($this->tables));
            if ($missing != []) {
                log_err_msg_ui('the pdf font ' . $file . ' has no ' . implode(', ', $missing) . ' table, so the pdf shows no text in this font', $msg);
                $this->data = '';
                $this->tables = [];
            } else {
                $this->units_per_em = max(1, $this->u16('head', self::HEAD_UNITS_PER_EM));
                $this->read_widths();
                $this->read_glyphs($msg);
            }
        }
    }


    /*
     * get
     */

    /**
     * @return bool true if the font file has been read with the glyphs of its chars, false e.g. if the file is missing
     */
    function has_glyphs(): bool
    {
        return $this->glyphs != [];
    }

    /**
     * @param int $code the unicode code point of a char
     * @return int the id of the glyph of the char in the font, the missing glyph if the font does not have it
     */
    function glyph_id(int $code): int
    {
        return $this->glyphs[$code] ?? self::MISSING_GLYPH;
    }

    /**
     * @param int $code the unicode code point of a char
     * @return bool true if the font has a glyph for the char
     */
    function has_glyph(int $code): bool
    {
        return isset($this->glyphs[$code]);
    }

    /**
     * @param int $gid the id of a glyph of the font
     * @return float the width of the glyph in 1/1000 of the font size
     */
    function width(int $gid): float
    {
        // the glyphs after the last width of the font file have the last width
        $width = (float)($this->widths[$gid] ?? end($this->widths));
        return $width * self::PDF_UNITS / $this->units_per_em;
    }


    /*
     * output
     */

    /**
     * @param int $first_id the id of the first of the pdf objects of the font, which is the font that a page uses
     * @param array $used the unicode code point of each glyph that the pdf shows by the glyph id
     * @return array the content of the pdf objects of the font by their id
     */
    function objects(int $first_id, array $used): array
    {
        ksort($used);
        $font_name = $this->subset_tag($used) . '+' . $this->name;
        $file = $this->subset(array_keys($used));
        $stream = gzcompress($file);
        return [
            $first_id => '<< /Type /Font /Subtype /Type0 /BaseFont /' . $font_name . ' /Encoding /Identity-H'
                . ' /DescendantFonts [' . ($first_id + 1) . ' 0 R] /ToUnicode ' . ($first_id + 4) . ' 0 R >>',
            $first_id + 1 => '<< /Type /Font /Subtype /CIDFontType2 /BaseFont /' . $font_name
                . ' /CIDSystemInfo << /Registry (Adobe) /Ordering (Identity) /Supplement 0 >>'
                . ' /FontDescriptor ' . ($first_id + 2) . ' 0 R /W [' . $this->width_list($used) . '] /CIDToGIDMap /Identity >>',
            $first_id + 2 => $this->descriptor($font_name, $first_id + 3),
            $first_id + 3 => '<< /Length ' . strlen($stream) . ' /Length1 ' . strlen($file) . " /Filter /FlateDecode >>\nstream\n"
                . $stream . "\nendstream",
            $first_id + 4 => $this->stream($this->to_unicode($used))
        ];
    }


    /*
     * read
     */

    /**
     * remember the position and the length of each table of the font file
     */
    private function read_tables(): void
    {
        $count = $this->u16_at(self::FILE_TABLE_COUNT);
        for ($i = 0; $i < $count; $i++) {
            $pos = self::FILE_HEADER + self::TABLE_RECORD * $i;
            $tag = substr($this->data, $pos, self::TABLE_TAG_LENGTH);
            $this->tables[$tag] = [$this->u32_at($pos + self::TABLE_RECORD_POS), $this->u32_at($pos + self::TABLE_RECORD_LENGTH)];
        }
    }

    /**
     * read the width of each glyph, of which the font file lists only the ones that differ from the last
     */
    private function read_widths(): void
    {
        $count = $this->u16('hhea', self::HHEA_METRICS);
        for ($gid = 0; $gid < $count; $gid++) {
            $this->widths[$gid] = $this->u16('hmtx', self::HMTX_METRIC * $gid);
        }
    }

    /**
     * read the glyph of each char from the full unicode map of the font or else from the map of the basic plane
     *
     * @param user_message $msg to tell the user that the pdf shows no text in this font, because it has no unicode map
     */
    private function read_glyphs(user_message $msg): void
    {
        $basic = null;
        $full = null;
        [$cmap_pos] = $this->tables['cmap'];
        $count = $this->u16('cmap', self::CMAP_COUNT);
        for ($i = 0; $i < $count; $i++) {
            $record = $cmap_pos + self::CMAP_RECORDS + self::CMAP_RECORD * $i;
            $pos = $cmap_pos + $this->u32_at($record + self::CMAP_RECORD_OFFSET);
            $format = $this->u16_at($pos);
            if ($format == self::CMAP_FULL_FORMAT) {
                $full = $pos;
            } elseif ($format == self::CMAP_BASIC_FORMAT) {
                $basic = $pos;
            }
        }
        if ($full !== null) {
            $this->read_full_map($full);
        } elseif ($basic !== null) {
            $this->read_basic_map($basic);
        } else {
            log_err_msg_ui('the pdf font ' . $this->name . ' has no unicode map, so the pdf shows no text in this font', $msg);
        }
    }

    /**
     * @param int $pos the position of the char map of format 12 in the font file, which lists ranges of chars
     */
    private function read_full_map(int $pos): void
    {
        $count = $this->u32_at($pos + self::CMAP_FULL_COUNT);
        for ($i = 0; $i < $count; $i++) {
            $group = $pos + self::CMAP_FULL_GROUPS + self::CMAP_FULL_GROUP * $i;
            $start = $this->u32_at($group);
            $end = $this->u32_at($group + self::CMAP_FULL_END);
            $gid = $this->u32_at($group + self::CMAP_FULL_GLYPH);
            for ($code = $start; $code <= $end; $code++) {
                $this->glyphs[$code] = $gid + $code - $start;
            }
        }
    }

    /**
     * @param int $pos the position of the char map of format 4 in the font file, which lists segments of chars
     */
    private function read_basic_map(int $pos): void
    {
        $segments = intdiv($this->u16_at($pos + self::CMAP_BASIC_SEGMENTS), self::SIZE_U16);
        $list_size = self::SIZE_U16 * $segments;
        $ends = $pos + self::CMAP_BASIC_ENDS;
        // the list of the ends is followed by a reserved number
        $starts = $ends + $list_size + self::SIZE_U16;
        $deltas = $starts + $list_size;
        $offsets = $deltas + $list_size;
        for ($i = 0; $i < $segments; $i++) {
            $seg = self::SIZE_U16 * $i;
            $start = $this->u16_at($starts + $seg);
            $end = min($this->u16_at($ends + $seg), self::CMAP_BASIC_END - 1);
            $delta = $this->u16_at($deltas + $seg);
            for ($code = $start; $code <= $end; $code++) {
                $gid = $this->basic_glyph_id($code, $start, $delta, $offsets + $seg);
                if ($gid != self::MISSING_GLYPH) {
                    $this->glyphs[$code] = $gid;
                }
            }
        }
    }

    /**
     * @param int $code the unicode code point of a char of the segment
     * @param int $start the first code point of the segment
     * @param int $delta the number that the segment adds to the glyph id
     * @param int $offset_pos the position of the offset of the segment, which points to its glyph ids if it is not zero
     * @return int the glyph id of the char, the missing glyph if the font does not have it
     */
    private function basic_glyph_id(int $code, int $start, int $delta, int $offset_pos): int
    {
        $offset = $this->u16_at($offset_pos);
        if ($offset == 0) {
            $result = ($code + $delta) & self::CMAP_BASIC_END;
        } else {
            $result = $this->u16_at($offset_pos + $offset + self::SIZE_U16 * ($code - $start));
            if ($result != self::MISSING_GLYPH) {
                $result = ($result + $delta) & self::CMAP_BASIC_END;
            }
        }
        return $result;
    }


    /*
     * subset
     */

    /**
     * @param array $gids the ids of the glyphs that the pdf shows
     * @return string the font file with only these glyphs and the glyphs that they are composed of,
     *                with the glyph ids of the full font, so that the code of a char stays its glyph id
     */
    private function subset(array $gids): string
    {
        $keep = $this->with_parts(array_merge([self::MISSING_GLYPH], $gids));
        $glyf = '';
        $loca = [];
        $count = $this->u16('maxp', self::MAXP_GLYPHS);
        for ($gid = 0; $gid < $count; $gid++) {
            $loca[] = strlen($glyf);
            if (isset($keep[$gid])) {
                $glyf .= $this->padded($this->glyph_data($gid));
            }
        }
        $loca[] = strlen($glyf);
        $tables = [];
        foreach (self::SUBSET_TABLES as $tag) {
            if (isset($this->tables[$tag])) {
                $tables[$tag] = $this->table($tag);
            }
        }
        $tables['glyf'] = $glyf;
        $tables['loca'] = pack('N*', ...$loca);
        // the long glyph positions of the new loca table and no checksum of the whole file, which a pdf does not need
        $tables['head'] = substr_replace($tables['head'], pack('n', self::LOCA_LONG), self::HEAD_LOCA_FORMAT, self::SIZE_U16);
        $tables['head'] = substr_replace($tables['head'], pack('N', 0), self::HEAD_CHECKSUM_ADJUSTMENT, self::SIZE_U32);
        return $this->font_file($tables);
    }

    /**
     * @param array $gids the ids of the glyphs that the pdf shows
     * @return array the glyph ids as keys, with the ids of the glyphs that a composed glyph uses
     */
    private function with_parts(array $gids): array
    {
        $result = [];
        $open = $gids;
        while ($open != []) {
            $gid = array_pop($open);
            if (!isset($result[$gid])) {
                $result[$gid] = true;
                $open = array_merge($open, $this->glyph_parts($gid));
            }
        }
        return $result;
    }

    /**
     * @param int $gid the id of a glyph of the font
     * @return array the ids of the glyphs that a composed glyph uses, empty for a simple glyph
     */
    private function glyph_parts(int $gid): array
    {
        $data = $this->glyph_data($gid);
        $result = [];
        $pos = self::GLYPH_HEADER;
        $more = $this->is_composed($data);
        while ($more and $pos + self::PART_HEADER <= strlen($data)) {
            [, $flags, $part] = unpack('n2', $data, $pos);
            $result[] = $part;
            $pos += self::PART_HEADER + $this->part_size($flags);
            $more = ($flags & self::PART_MORE) != 0;
        }
        return $result;
    }

    /**
     * @param string $data the outline of a glyph in the font file
     * @return bool true if the glyph is composed of other glyphs, which its negative number of outlines says
     */
    private function is_composed(string $data): bool
    {
        $has_header = strlen($data) >= self::GLYPH_HEADER;
        return ($has_header and unpack('n', $data)[1] >= self::I16_NEGATIVE);
    }

    /**
     * @param int $flags the flags of a part of a composed glyph
     * @return int the number of bytes of the position and of the scale of the part
     */
    private function part_size(int $flags): int
    {
        $result = self::PART_ARGS_BYTES;
        if (($flags & self::PART_WORDS) != 0) {
            $result = self::PART_ARGS_WORDS;
        }
        if (($flags & self::PART_SCALE) != 0) {
            $result += self::PART_SCALE_SIZE;
        } elseif (($flags & self::PART_XY_SCALE) != 0) {
            $result += self::PART_XY_SCALE_SIZE;
        } elseif (($flags & self::PART_MATRIX) != 0) {
            $result += self::PART_MATRIX_SIZE;
        }
        return $result;
    }

    /**
     * @param int $gid the id of a glyph of the font
     * @return string the outline of the glyph in the font file, empty e.g. for the space
     */
    private function glyph_data(int $gid): string
    {
        if ($this->u16('head', self::HEAD_LOCA_FORMAT) == self::LOCA_LONG) {
            $start = $this->u32('loca', self::SIZE_U32 * $gid);
            $end = $this->u32('loca', self::SIZE_U32 * ($gid + 1));
        } else {
            $start = self::LOCA_SHORT_FACTOR * $this->u16('loca', self::SIZE_U16 * $gid);
            $end = self::LOCA_SHORT_FACTOR * $this->u16('loca', self::SIZE_U16 * ($gid + 1));
        }
        [$glyf_pos] = $this->tables['glyf'];
        $result = '';
        if ($end > $start) {
            $result = substr($this->data, $glyf_pos + $start, $end - $start);
        }
        return $result;
    }

    /**
     * @param array $tables the content of each table of the font by its tag
     * @return string the font file with the tables sorted by their tag
     */
    private function font_file(array $tables): string
    {
        ksort($tables, SORT_STRING);
        $count = count($tables);
        $selector = (int)floor(log($count, 2));
        $range = self::TABLE_RECORD * (2 ** $selector);
        $head = pack('Nn4', self::FILE_VERSION, $count, $range, $selector, self::TABLE_RECORD * $count - $range);
        $pos = self::FILE_HEADER + self::TABLE_RECORD * $count;
        $body = '';
        foreach ($tables as $tag => $table) {
            $head .= $tag . pack('N3', $this->checksum($table), $pos + strlen($body), strlen($table));
            $body .= $this->padded($table);
        }
        return $head . $body;
    }

    /**
     * @param string $table a table of the font file
     * @return int the sum of the table as 32-bit numbers, which the font file has in its table list
     */
    private function checksum(string $table): int
    {
        $sum = 0;
        foreach (unpack('N*', $this->padded($table)) ?: [] as $part) {
            $sum = ($sum + $part) & self::U32_MASK;
        }
        return $sum;
    }

    /**
     * @param string $data a table or a glyph of the font file
     * @return string the data with zero bytes up to a multiple of four bytes, which the font file expects
     */
    private function padded(string $data): string
    {
        $length = (int)ceil(strlen($data) / self::SIZE_U32) * self::SIZE_U32;
        return str_pad($data, $length, "\0");
    }


    /*
     * pdf objects
     */

    /**
     * @param array $used the unicode code point of each glyph that the pdf shows by the glyph id
     * @return string the tag of the subset, which differs for another subset of the same font e.g. "KXQBNA"
     */
    private function subset_tag(array $used): string
    {
        $hash = md5(implode(',', array_keys($used)));
        $result = '';
        for ($i = 0; $i < self::SUBSET_TAG_LENGTH; $i++) {
            $result .= chr(ord('A') + hexdec($hash[$i]) + 10 * ($i % 2));
        }
        return $result;
    }

    /**
     * @param array $used the unicode code point of each glyph that the pdf shows by the glyph id
     * @return string the width of each used glyph for the W array of the font e.g. "36 [633] 37 [596]"
     */
    private function width_list(array $used): string
    {
        $parts = [];
        foreach (array_keys($used) as $gid) {
            $parts[] = $gid . ' [' . round($this->width($gid)) . ']';
        }
        return implode(' ', $parts);
    }

    /**
     * @param string $font_name the name of the subset
     * @param int $file_id the id of the pdf object of the font file
     * @return string the font descriptor with the size of the font in 1/1000 of the font size
     */
    private function descriptor(string $font_name, int $file_id): string
    {
        $box = [];
        for ($i = 0; $i < self::HEAD_BOX_VALUES; $i++) {
            $box[] = $this->scaled($this->i16('head', self::HEAD_X_MIN + self::SIZE_U16 * $i));
        }
        $ascent = $this->scaled($this->i16('hhea', self::HHEA_ASCENDER));
        $cap_height = $ascent;
        if ($this->u16('OS/2', 0) >= self::OS2_CAP_HEIGHT_VERSION) {
            $cap_height = $this->scaled($this->i16('OS/2', self::OS2_CAP_HEIGHT));
        }
        return '<< /Type /FontDescriptor /FontName /' . $font_name . ' /Flags ' . self::DESCRIPTOR_FLAGS
            . ' /FontBBox [' . implode(' ', $box) . '] /ItalicAngle 0 /Ascent ' . $ascent
            . ' /Descent ' . $this->scaled($this->i16('hhea', self::HHEA_DESCENDER))
            . ' /CapHeight ' . $cap_height . ' /StemV ' . self::DESCRIPTOR_STEM . ' /FontFile2 ' . $file_id . ' 0 R >>';
    }

    /**
     * @param array $used the unicode code point of each glyph that the pdf shows by the glyph id
     * @return string the map from the glyph ids to the chars, with which a pdf reader can copy and search the text;
     *                the missing glyph is the replacement char, because it stands for every char that the font does not have
     */
    private function to_unicode(array $used): string
    {
        $blocks = '';
        foreach (array_chunk($used, self::TO_UNICODE_BLOCK, true) as $block) {
            $blocks .= count($block) . " beginbfchar\n";
            foreach ($block as $gid => $code) {
                if ($gid == self::MISSING_GLYPH) {
                    $code = self::REPLACEMENT_CHAR;
                }
                $chr = mb_chr($code, self::TEXT_ENCODING);
                $utf16 = bin2hex(mb_convert_encoding($chr, self::UNICODE_ENCODING, self::TEXT_ENCODING));
                $blocks .= sprintf('<%04X> <%s>', $gid, strtoupper($utf16)) . "\n";
            }
            $blocks .= "endbfchar\n";
        }
        return "/CIDInit /ProcSet findresource begin\n12 dict begin\nbegincmap\n"
            . "/CIDSystemInfo << /Registry (Adobe) /Ordering (UCS) /Supplement 0 >> def\n"
            . "/CMapName /Adobe-Identity-UCS def\n/CMapType 2 def\n"
            . "1 begincodespacerange\n<0000> <FFFF>\nendcodespacerange\n"
            . $blocks
            . "endcmap\nCMapName currentdict /CMap defineresource pop\nend\nend";
    }

    /**
     * @param string $data the content of a stream
     * @return string the pdf object of the stream
     */
    private function stream(string $data): string
    {
        return '<< /Length ' . strlen($data) . " >>\nstream\n" . $data . "\nendstream";
    }

    /**
     * @param int $size a size in the units of the font file
     * @return int the size in 1/1000 of the font size
     */
    private function scaled(int $size): int
    {
        return (int)round($size * self::PDF_UNITS / $this->units_per_em);
    }


    /*
     * bytes
     */

    /**
     * @param string $tag the tag of a table of the font file e.g. "glyf"
     * @return string the content of the table, empty if the font file does not have it
     */
    private function table(string $tag): string
    {
        [$pos, $length] = $this->tables[$tag] ?? [0, 0];
        return substr($this->data, $pos, $length);
    }

    /**
     * @param string $tag the tag of a table of the font file e.g. "head"
     * @param int $offset the position of the number in the table
     * @return int the unsigned 16-bit number, zero if the font file does not have the table
     */
    private function u16(string $tag, int $offset): int
    {
        $result = 0;
        if (isset($this->tables[$tag])) {
            $result = $this->u16_at($this->tables[$tag][0] + $offset);
        }
        return $result;
    }

    /**
     * @param string $tag the tag of a table of the font file e.g. "hhea"
     * @param int $offset the position of the number in the table
     * @return int the signed 16-bit number e.g. the descender below the base line, zero if the font file does not have the table
     */
    private function i16(string $tag, int $offset): int
    {
        $result = $this->u16($tag, $offset);
        if ($result >= self::I16_NEGATIVE) {
            $result -= self::U16_RANGE;
        }
        return $result;
    }

    /**
     * @param string $tag the tag of a table of the font file e.g. "loca"
     * @param int $offset the position of the number in the table
     * @return int the unsigned 32-bit number, zero if the font file does not have the table
     */
    private function u32(string $tag, int $offset): int
    {
        $result = 0;
        if (isset($this->tables[$tag])) {
            $result = $this->u32_at($this->tables[$tag][0] + $offset);
        }
        return $result;
    }

    /**
     * @param int $pos the position of the number in the font file
     * @return int the unsigned 16-bit number, zero if the font file ends before
     */
    private function u16_at(int $pos): int
    {
        $result = 0;
        if ($pos + self::SIZE_U16 <= strlen($this->data)) {
            $result = unpack('n', $this->data, $pos)[1];
        }
        return $result;
    }

    /**
     * @param int $pos the position of the number in the font file
     * @return int the unsigned 32-bit number, zero if the font file ends before
     */
    private function u32_at(int $pos): int
    {
        $result = 0;
        if ($pos + self::SIZE_U32 <= strlen($this->data)) {
            $result = unpack('N', $this->data, $pos)[1];
        }
        return $result;
    }

}
