<?php

/*

    shared/const/chars.php - const symbols used for the formula expressions
    ----------------------


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

    Copyright (c) 1995-2025 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\main\php\shared\const;


class chars
{

    // text maker to convert phrase, formula or verb database reference to
    // a phrase or phrase list and in a second step to a value or value list
    const string TERM_START = '{'; //
    const string TERM_END = '}'; //
    const string WORD_SYMBOL = 'w'; //
    const string TRIPLE_SYMBOL = 't'; //
    const string FORMULA_SYMBOL = 'f'; //
    const string VERB_SYMBOL = 'v'; //
    const string WORD_START = '{w';   //
    const string WORD_END = '}';    //
    const string TRIPLE_START = '{t';   //
    const string TRIPLE_END = '}';    //
    const string FORMULA_START = '{f';   //
    const string FORMULA_END = '}';    //
    const string VERB_START = '{v';   //
    const string VERB_END = '}';    //

    // text conversion const string (used to convert word, formula or verbs text to a reference)
    const string BRACKET_OPEN = '(';    //
    const string BRACKET_CLOSE = ')';    //
    const string TXT_FIELD = '"';    // don't look for math symbols in text that is a high quotes

    // text conversion syntax elements
    // used to convert word, triple, verb or formula name to a database reference
    const string TERM_DELIMITER = '"';    // or a zukunft verb or a zukunft formula
    const string TERM_LIST_START = '[';    //
    const string TERM_LIST_END = ']';    //
    const string SEPARATOR = ',';    //
    const string RANGE = ':';    //
    const string CONCAT = '&';    //

    // math calc (probably not needed any more if r-project.org is used)
    const string CHAR_CALC = '=';    //
    const string ADD = '+';    //
    const string SUB = '-';    //
    const string MUL = '*';    //
    const string DIV = '/';    //

    const string AND = '&';   //
    const string OR = '|';    // probably not needed because can and should be solved by triples

    // fixed functions
    const string FUNC_IF = 'if';    //
    const string FUNC_SUM = 'sum';    //
    const string FUNC_IS_NUM = 'is.numeric';    //

}
