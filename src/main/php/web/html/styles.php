<?php

/*

    web/html/styles.php - css class constants used for html frontend
    -------------------


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

namespace Zukunft\ZukunftCom\main\php\web\html;

class styles
{

    // styles used
    const string STYLE_GREY = 'grey';
    const string STYLE_GLYPH = 'glyphicon glyphicon-pencil';
    const string STYLE_USER = 'user_specific';
    const string STYLE_RIGHT = 'right_ref';
    const string STYLE_BORDERLESS = 'borderless';
    const string TABLE_PUR = 'table';
    const string TEXT_RIGHT = 'text-right';
    // bootstrap css class to keep a short line like 'has aliases: $, U.S. dollar' unbroken
    const string TEXT_NOWRAP = 'text-nowrap';

    // css class wrapping a page-title heading and its action icon so that they
    // are shown to the user on the same line
    const string HEADING_LINE = 'heading-line';

    // css class to render a page-title heading inline so that an action icon
    // (e.g. the rename link) is shown on the same line as the heading text
    const string HEADING_INLINE = 'heading-inline';

    // css class for an icon link inside a heading: body-text size and vertically
    // centred, so the heading stays prominent while the inline action stays unobtrusive
    const string HEADING_ICON_INLINE = 'heading-icon-inline';

    // css class for the small category line under a page-title heading
    const string SUBTITLE = 'subtitle';


}
