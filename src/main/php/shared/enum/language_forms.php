<?php

/*

    shared/enum/language_forms.php - a shared database based enum for language_forms
    ------------------------------


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

namespace Zukunft\ZukunftCom\main\php\shared\enum;

enum language_forms: string
{

    // list of the language forms that have a coded functionality
    const string DEFAULT = "standard";
    const string PLURAL = "plural";
    const int PLURAL_ID = 1;
    const string PLURAL_NAME = "plural";
    const string PLURAL_COM = "The noun denotes a quantity greater than the default quantity represented by that noun. This default quantity is most commonly one";

}