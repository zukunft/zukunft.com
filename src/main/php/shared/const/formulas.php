<?php

/*

    shared/const/formulas.php - predefined formulas used in the backend and frontend as code id
    -------------------------

    all preserved words must always be owned by an administrator so that the standard cannot be renamed

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

use Zukunft\ZukunftCom\test\php\const\formula_names;

class formulas
{

    // formula names that are reserved either
    // for creating the test formulas, that are removed after the test
    // so these formula names cannot be used for user formulas
    // or for fixed of the default data set that are used for unit tests
    const array RESERVED_NAMES = array(
        formula_names::SCALE_TO_SEC,
        formula_names::SYSTEM_TEST_ADD,
        formula_names::SYSTEM_TEST_ADD_VIA_FUNC,
        formula_names::SYSTEM_TEST_RENAMED,
        formula_names::SYSTEM_TEST_EXCLUDED,
        formula_names::SYSTEM_TEST_THIS,
        formula_names::SYSTEM_TEST_RATIO,
        formula_names::SYSTEM_TEST_SECTOR,
        formula_names::SYSTEM_TEST_SCALE_K,
        formula_names::SYSTEM_TEST_SCALE_TO_K,
        formula_names::SYSTEM_TEST_SCALE_MIO,
        formula_names::SYSTEM_TEST_SCALE_BIL
    );

    // array of formula names that used for db read testing and that should not be renamed
    const array FIXED_NAMES = array(
        formula_names::SCALE_TO_SEC
    );

}
