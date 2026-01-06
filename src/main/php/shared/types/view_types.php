<?php

/*

    shared/types/view_types.php - db based ENUM of the view types
    ---------------------------

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

class view_types
{

    // list of the view types that have a coded functionality
    const string DEFAULT = "default";
    const string ENTRY = "entry";
    const string MASK_DEFAULT = "mask_default";
    const string PRESENT = "presentation";
    const string WORD_DEFAULT = "word_default";
    const string DETAIL = "detail_view";
    const string SYSTEM = "system";
    const string EXPORT = "export";

    // object specific views
    const string WORD = "word";
    const string VERB = "verb";
    const string TRIPLE = "triple";
    const string SOURCE = "source";
    const string REF = "ref";
    const string LANGUAGE = "language";
    const string VALUE = "value";
    const string FORMULA = "formula";
    const string RESULT = "result";


    // list of view types that are used by the system
    // and should not be assignable by users
    const array SYSTEM_TYPES = array(
        self::ENTRY,
        self::MASK_DEFAULT,
        self::SYSTEM,
        self::EXPORT,
    );

    // list of view types that are specific for other objects than words or triples
    // and should not be assignable to words or triples
    const array NON_PHRASE_TYPES = array(
        self::VERB,
        self::SOURCE,
        self::REF,
        self::LANGUAGE,
        self::VALUE,
        self::FORMULA,
        self::RESULT,
    );

    // list of view types that can be used for values and results
    const array VALUE_TYPES = array(
        self::VALUE,
        self::RESULT,
    );

}
