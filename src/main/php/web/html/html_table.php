<?php

/*

    table.php - to display a table with pure HTML code
    ---------

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

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace html;

class html_table
{

    const SIZE_FULL = 'full';
    const SIZE_HALF = 'half';

    const WIDTH_FULL = '800px';
    const WIDTH_HALF = '400px';

    /**
     * get the normal table width (should be based on the display size)
     */
    private function width(): string
    {
        $result = self::WIDTH_FULL;
        return $result;
    }

    private function width_half(): string
    {
        $result = self::WIDTH_HALF;
        return $result;
    }

    function start(string $size_type = self::SIZE_FULL): string
    {
        if (UI_USE_BOOTSTRAP) {
            $result = '<table class="table table-striped table-bordered">' . "\n";
        } else {
            if ($size_type = self::SIZE_FULL) {
                $result = '<table style="width:' . $this->width() . '">' . "\n";
            } else {
                $result = '<table style="width:' . $this->width_half() . '">' . "\n";
            }
        }
        return $result;
    }

    function row_start(): string
    {
        return '<tr>';
    }

    function row_end(): string
    {
        return '</tr>';
    }

    function row(): string
    {
        return $this->row_end() . $this->row_start();
    }

    function cell(string $cell_text): string
    {
        return '<td>' . $cell_text . '</td>';
    }

    function header(string $headl_text): string
    {
        return '<th>' . $headl_text . '</th>';
    }

}
