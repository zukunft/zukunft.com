<?php

/*

    shared/enum/languages.php - a shared database based enum for fixed languages
    -------------------------


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

enum languages: string
{

    // list of the languages that have a coded functionality
    const string DEFAULT = "en";
    const int DEFAULT_ID = 1;
    const string DEFAULT_NAME = "English";
    const string DEFAULT_COM = "the system language, so each word must be unique for all users in this language";
    const string TN_READ = "English";
    const string DEFAULT_LOCAL_NAME = "English";

    const string TRANSLATE_TEST = "de";
    const string TRANSLATE_TEST_WIKI = "de";
    const int TRANSLATE_TEST_ID = 3;
    const string TRANSLATE_TEST_NAME = "German";
    const string TRANSLATE_TEST_COM = "a translation to standard German";
    const string TRANSLATE_TEST_LOCAL_NAME = "Deutsch";
    const int TRANSLATE_USAGE = 95000000;

}