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

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace shared\const;


class chars
{

    // text maker to convert phrase, formula or verb database reference to
    // a phrase or phrase list and in a second step to a value or value list
    const TERM_START = '{'; //
    const TERM_END = '}'; //
    const WORD_SYMBOL = 'w'; //
    const TRIPLE_SYMBOL = 't'; //
    const FORMULA_SYMBOL = 'f'; //
    const VERB_SYMBOL = 'v'; //
    const WORD_START = '{w';   //
    const WORD_END = '}';    //
    const TRIPLE_START = '{t';   //
    const TRIPLE_END = '}';    //
    const FORMULA_START = '{f';   //
    const FORMULA_END = '}';    //
    const VERB_START = '{v';   //
    const VERB_END = '}';    //

    // text conversion const (used to convert word, formula or verbs text to a reference)
    const BRACKET_OPEN = '(';    //
    const BRACKET_CLOSE = ')';    //
    const TXT_FIELD = '"';    // don't look for math symbols in text that is a high quotes

    // text conversion syntax elements
    // used to convert word, triple, verb or formula name to a database reference
    const TERM_DELIMITER = '"';    // or a zukunft verb or a zukunft formula
    const TERM_LIST_START = '[';    //
    const TERM_LIST_END = ']';    //
    const SEPARATOR = ',';    //
    const RANGE = ':';    //
    const CONCAT = '&';    //

    // math calc (probably not needed any more if r-project.org is used)
    const CHAR_CALC = '=';    //
    const ADD = '+';    //
    const SUB = '-';    //
    const MUL = '*';    //
    const DIV = '/';    //

    const AND = '&';   //
    const OR = '|';    // probably not needed because can and should be solved by triples

    // fixed functions
    const FUNC_IF = 'if';    //
    const FUNC_SUM = 'sum';    //
    const FUNC_IS_NUM = 'is.numeric';    //

}
