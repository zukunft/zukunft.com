<?php

/*

    model/const/def.php - general system definitions
    -------------------


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

namespace cfg\const;

//include_once MODEL_COMPONENT_PATH . 'component.php';
//include_once MODEL_FORMULA_PATH . 'formula.php';
//include_once MODEL_REF_PATH . 'ref.php';
//include_once MODEL_REF_PATH . 'source.php';
//include_once MODEL_RESULT_PATH . 'result.php';
//include_once MODEL_USER_PATH . 'user_profile.php';
//include_once MODEL_USER_PATH . 'user_type.php';
//include_once MODEL_VALUE_PATH . 'value.php';
//include_once MODEL_VERB_PATH . 'verb.php';
//include_once MODEL_VIEW_PATH . 'view.php';
//include_once MODEL_WORD_PATH . 'triple.php';
//include_once MODEL_WORD_PATH . 'word.php';

use cfg\component\component;
use cfg\formula\formula;
use cfg\ref\ref;
use cfg\ref\source;
use cfg\result\result;
use cfg\user\user_profile;
use cfg\user\user_type;
use cfg\value\value;
use cfg\verb\verb;
use cfg\view\view;
use cfg\word\triple;
use cfg\word\word;

class def
{

    /*
     * classes
     */

    // list of classes that have a csv with the code id for the initial user profile and type setup
    const CLASS_WITH_USER_CODE_LINK_CSV = [
        user_profile::class,
        user_type::class
    ];

    // list of classes that use the user sandbox
    const SANDBOX_CLASSES = [
        word::class,
        verb::class,
        triple::class,
        source::class,
        ref::class,
        value::class,
        formula::class,
        result::class,
        view::class,
        component::class
    ];

}
