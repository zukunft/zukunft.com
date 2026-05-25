<?php

/*

    web/const/icons.php - the css class strings of the icons used in the frontend
    -------------------

    the central place for the css class string of every icon rendered by the
    frontend (Font Awesome and any other icon set used). adding the literal
    e.g. 'fas fa-edit' inline is not allowed (see docs/llm_coding.md "use a
    named icon constant from web/const/icons.php — no inline icon literals");
    use the constant defined here so that an icon-set change can be made in
    one place and the rendered icon is searchable by its constant name.


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

namespace Zukunft\ZukunftCom\main\php\web\const;

class icons
{

    // Font Awesome solid (fas) — the full css class string ready to drop into class="..."
    const string EDIT = 'fas fa-edit';
    const string GLOBE = 'fas fa-globe';
    const string USER_CIRCLE = 'fas fa-user-circle';

}
