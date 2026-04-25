<?php

/*

    shared/calc/parameter_type.php - enum to link the class string to a db id for a formula element
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

    Copyright (c) 1995-2025 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\main\php\shared\calc;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\shared\types\element_types;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once paths::MODEL_SYSTEM . 'BasicEnum.php';
include_once paths::MODEL_FORMULA . 'formula.php';
include_once paths::MODEL_VERB . 'verb.php';
include_once paths::MODEL_WORD . 'triple.php';
include_once paths::MODEL_WORD . 'word.php';
//include_once html_paths::FORMULA . 'formula.php';
//include_once html_paths::VERB . 'verb.php';
//include_once html_paths::WORD . 'triple.php';
//include_once html_paths::WORD . 'word.php';

use Zukunft\ZukunftCom\main\php\cfg\formula\formula;
use Zukunft\ZukunftCom\main\php\cfg\system\BasicEnum;
use Zukunft\ZukunftCom\main\php\cfg\verb\verb;
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\web\formula\formula as formula_ui;
use Zukunft\ZukunftCom\main\php\web\verb\verb as verb_ui;
use Zukunft\ZukunftCom\main\php\web\word\triple as triple_ui;
use Zukunft\ZukunftCom\main\php\web\word\word as word_ui;

class parameter_type extends BasicEnum
{
    // for the frontend
    const string WORD_WEB_CLASS = word_ui::class;        // a word is used for an AND selection of values
    const string TRIPLE_WEB_CLASS = triple_ui::class;    // a triple is used for an AND selection of values
    const string VERB_WEB_CLASS = verb_ui::class;        // a verb is used for dynamic usage of linked words for an AND selection
    const string FORMULA_WEB_CLASS = formula_ui::class;  // a formula is used to include formula results of another formula

    protected static function get_description($value): string
    {
        $result = 'formula element type "' . $value . '" not yet defined';

        global $sys;

        $typ_lst = $sys->typ_lst->elm_typ;

        switch ($value) {

            // system log
            case $typ_lst->id(element_types::WORD_SELECTOR):
            case $typ_lst->id(element_types::WORD_RESULT):
                $result = 'a reference to a simple word';
                break;
            case $typ_lst->id(element_types::TRIPLE_SELECTOR):
            case $typ_lst->id(element_types::TRIPLE_RESULT):
                $result = 'a reference to word link';
                break;
            case $typ_lst->id(element_types::VERB_SELECTOR):
                $result = 'a reference to predicate';
                break;
            case $typ_lst->id(element_types::FORMULA_SELECTOR):
                $result = 'a reference to another formula';
                break;
        }

        return $result;
    }

}