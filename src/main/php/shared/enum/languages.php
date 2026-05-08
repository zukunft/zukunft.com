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

    const string TRANSLATE = "de";
    const string TRANSLATE_WIKI = "de";
    const int TRANSLATE_ID = 3;
    const string TRANSLATE_NAME = "German";
    const string TRANSLATE_COM = "a translation to standard German";
    const string TRANSLATE_LOCAL_NAME = "Deutsch";
    const int TRANSLATE_USAGE = 95000000;

    const string NICE = "fr";
    const int NICE_ID = 4;
    const string NICE_NAME = "French";
    const string NICE_COM = "Le français est une langue indo-européenne de la famille des langues romanes dont les locuteurs sont appelés francophones";
    const string NICE_LOCAL_NAME = "Français";

    const string OFTEN = "es";
    const int OFTEN_ID = 5;
    const string OFTEN_NAME = "Spanish";
    const string OFTEN_COM = "El español o castellano es una lengua romance procedente del latín hablado, perteneciente a la familia de lenguas indoeuropeas";
    const string OFTEN_LOCAL_NAME = "Español";

    const string LONG_CHAR = "zh";
    const int LONG_CHAR_ID = 6;
    const string LONG_CHAR_NAME = "Chinese";
    const string LONG_CHAR_COM = "漢語又稱中文 是源自东亚的一类分析语，为汉民族的母语";
    const string LONG_CHAR_LOCAL_NAME = "漢語";

    const string REVERSE = "ar";
    const int REVERSE_ID = 7;
    const string REVERSE_NAME = "Arabic";
    const string REVERSE_COM = "ٱللُّغَةُ ٱلْعَرَبِيَّة هي أكثر اللغات السامية تحدثًا، وإحدى أكثر اللغات انتشاراً في العالم، يتحدثها أكثر من 467 مليون نسمة.";
    const string REVERSE_LOCAL_NAME = "العربية";

}