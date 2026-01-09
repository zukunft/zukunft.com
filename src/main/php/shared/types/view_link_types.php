<?php

/*

    shared/types/view_link_types.php - db based ENUM of the view link types
    --------------------------------

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

    Copyright (c) 1995-2023 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com
  
*/

namespace Zukunft\ZukunftCom\main\php\shared\types;

class view_link_types
{

    // list of selection types for a view starting from a word, triple or formula
    const string DEFAULT = self::MAIN_WORD; // use the main word as start for the view
    const string MAIN_WORD = "main_word"; // use the main word to select the view
    const int MAIN_WORD_ID = 1;
    const string MAIN_WORD_NAME = "main word";
    const string MAIN_WORD_COM = "use the main word as start for the view";
    const string SELECTED_WORD = "main_word"; // use the main word as start for the view

}
