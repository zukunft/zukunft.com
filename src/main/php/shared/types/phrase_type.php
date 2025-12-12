<?php

/*

    shared/types/phrase_type.php - the phrase code_ids used in back- and frontend
    ----------------------------

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

class phrase_type
{

    // list of the phrase types that have a coded functionality
    // TODO add the missing functionality and unit tests
    const string NORMAL = "default";
    const string NORMAL_NAME = "standard";
    const string MATH_CONST = "constant"; // TODO add usage sample
    const string MATH_CONST_NAME = "math constant";
    const string TIME = "time";
    const string TIME_NAME = "time";
    const string TIME_JUMP = "time_jump";
    const string TIME_JUMP_NAME = "time jump";
    const string LATEST = "latest"; // TODO add usage sample
    const string LATEST_NAME = "latest";
    const string PERCENT = "percent";
    const string PERCENT_NAME = "format percent";
    const string MEASURE = "measure";
    const string MEASURE_NAME = "measure type";
    const string MEASURE_DIVISOR = "measure_divisor";
    const string MEASURE_DIVISOR_NAME = "measure divisor";
    const string SCALING = "scaling";
    const string SCALING_NAME = "scaling";
    const string SCALING_HIDDEN = "scaling_hidden";
    const string SCALING_HIDDEN_NAME = "hidden scaling";
    const string SCALING_PCT = "scaling_percent"; // TODO used to define the scaling formula word to scale percentage values ?
    const string SCALING_PCT_NAME = "scaling word percent";
    const string SCALED_MEASURE = "scaled_measure"; // TODO add usage sample
    const string SCALED_MEASURE_NAME = "scaled measure";
    const string FORMULA_LINK = "formula_link"; // special phrase type for functional words that are used to link values to formulas
    const string FORMULA_LINK_NAME = "formula link";
    const string CALC = "calc"; // TODO add usage sample
    const string CALC_NAME = "calc";
    const string LAYER = "view"; // TODO add usage sample
    const string LAYER_NAME = "view / layer";
    const string OTHER = "type_other";
    const string OTHER_NAME = "differentiator filler";
    const string KEY = "key";
    const string KEY_NAME = "key";
    const string INFO = "information";
    const string INFO_NAME = "information";
    const string TRIPLE_HIDDEN = "hidden_triple";
    const string TRIPLE_HIDDEN_NAME = "hidden triple";
    const string SYSTEM_HIDDEN = "hidden_system";
    const string SYSTEM_HIDDEN_NAME = "hidden system";
    const string GROUP = "group";
    const string GROUP_NAME = "group and select";
    const string SYMBOL = "symbol"; // is expected to be a symbol e.g. used to preselect columns for table import
    const string SYMBOL_NAME = "symbol";
    const string RANK = "rank"; // is expected to be a ranking number e.g. used to preselect columns for table import
    const string RANK_NAME = "rank";
    const string IGNORE = "ignore"; // e.g. to set column names to be excluded from the import
    const string IGNORE_NAME = "ignore";
    const string THIS = "this";
    const string THIS_NAME = "this";
    const string NEXT = "next";
    const string NEXT_NAME = "next";
    const string PRIOR = "previous";
    const string PRIOR_NAME = "prior";

    const string DEFAULT = self::NORMAL;

}
