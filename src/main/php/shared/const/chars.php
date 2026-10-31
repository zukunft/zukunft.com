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
    const string DECIMAL_POINT = '.';    // the decimal separator of a number in a formula
    const string ADD = '+';    //
    const string SUB = '-';    //
    const string MUL = '*';    //
    const string DIV = '/';    //
    const string POW = '^';    // binds stronger than a multiplication and from right to left e.g. 2 ^ 3 ^ 2 is 2 ^ 9

    const string AND = '&';   //
    const string OR = '|';    // probably not needed because can and should be solved by triples

    // fixed functions
    const string FUNC_IF = 'if';    //
    const string FUNC_SUM = 'sum';    //
    const string FUNC_IS_NUM = 'is.numeric';    //
    // the smallest of its arguments e.g. the direct use as min( "PV production" ; "day share" * "household consumption" )
    const string FUNC_MIN = 'min';
    // the largest of its arguments e.g. a lower limit as max( 0 ; "PV production" - "household consumption" )
    const string FUNC_MAX = 'max';
    // the functions of one number; an angle is in radian and log is the logarithm to the base 10, ln the natural one
    const string FUNC_ABS = 'abs';
    const string FUNC_COS = 'cos';
    const string FUNC_EXP = 'exp';
    const string FUNC_LN = 'ln';
    const string FUNC_LOG = 'log';
    const string FUNC_ROUND = 'round';
    const string FUNC_SEC = 'sec';
    const string FUNC_SIN = 'sin';
    const string FUNC_SQRT = 'sqrt';
    const array FUNC_ONE_NUMBER = [
        self::FUNC_ABS,
        self::FUNC_COS,
        self::FUNC_EXP,
        self::FUNC_LN,
        self::FUNC_LOG,
        self::FUNC_ROUND,
        self::FUNC_SEC,
        self::FUNC_SIN,
        self::FUNC_SQRT
    ];
    // the functions of a list: a plain operand stands for all its values (formula::fill_list_element_group)
    const string FUNC_COUNT = 'count';
    const string FUNC_MEAN = 'mean';
    const array FUNC_LIST = [self::FUNC_SUM, self::FUNC_COUNT, self::FUNC_MEAN];
    // the functions that service/math/calc_internal.php can calculate; any other name before a bracket is reported as missing
    const array FUNC_CALCULATED = [self::FUNC_IF, self::FUNC_MIN, self::FUNC_MAX, ...chars::FUNC_ONE_NUMBER, ...chars::FUNC_LIST];
    // between the arguments of a function, because the SEPARATOR joins the phrase names of one operand
    const string FUNC_ARG_SEPARATOR = ';';

}
