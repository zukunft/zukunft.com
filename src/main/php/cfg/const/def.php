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

//include_once paths::MODEL_COMPONENT . 'component.php';
//include_once paths::MODEL_COMPONENT . 'component_list.php';
//include_once paths::MODEL_COMPONENT . 'component_type.php';
//include_once paths::MODEL_COMPONENT . 'component_link_type.php';
//include_once paths::MODEL_COMPONENT . 'position_type.php';
//include_once paths::MODEL_COMPONENT . 'view_style.php';
//include_once paths::MODEL_ELEMENT . 'element_type.php';
//include_once paths::MODEL_FORMULA . 'formula.php';
//include_once paths::MODEL_FORMULA . 'formula_type.php';
//include_once paths::MODEL_FORMULA . 'formula_link_type.php';
//include_once paths::MODEL_LANGUAGE . 'language.php';
//include_once paths::MODEL_LANGUAGE . 'language_form.php';
//include_once paths::MODEL_LOG . 'change_action.php';
//include_once paths::MODEL_LOG . 'change_table.php';
//include_once paths::MODEL_LOG . 'change_field.php';
//include_once paths::MODEL_PHRASE . 'phrase_types.php';
//include_once paths::MODEL_SYSTEM . 'job_type.php';
//include_once paths::MODEL_SYSTEM . 'sys_log_status.php';
//include_once paths::MODEL_SYSTEM . 'sys_log_type.php';
//include_once paths::MODEL_SYSTEM . 'system_time_type.php';
//include_once paths::MODEL_REF . 'ref.php';
//include_once paths::MODEL_REF . 'ref_type.php';
//include_once paths::MODEL_REF . 'source.php';
//include_once paths::MODEL_REF . 'source_list.php';
//include_once paths::MODEL_REF . 'source_type.php';
//include_once paths::MODEL_USER . 'user_official_type.php';
//include_once paths::MODEL_RESULT . 'result.php';
//include_once paths::MODEL_USER . 'user_profile.php';
//include_once paths::MODEL_USER . 'user.php';
//include_once paths::MODEL_USER . 'user_list.php';
//include_once paths::MODEL_USER . 'user_type.php';
//include_once paths::MODEL_VALUE . 'value.php';
//include_once paths::MODEL_VERB . 'verb.php';
//include_once paths::MODEL_VERB . 'verb_list.php';
//include_once paths::MODEL_VIEW . 'view.php';
//include_once paths::MODEL_VIEW . 'view_list.php';
//include_once paths::MODEL_VIEW . 'view_type.php';
//include_once paths::MODEL_VIEW . 'view_link_type.php';
//include_once paths::MODEL_WORD . 'triple.php';
//include_once paths::MODEL_WORD . 'triple_list.php';
//include_once paths::MODEL_WORD . 'word.php';
//include_once paths::MODEL_WORD . 'word_list.php';
//include_once paths::SHARED_TYPES . 'protection_type.php';
//include_once paths::SHARED_TYPES . 'share_type.php';

use cfg\component\component;
use cfg\component\component_list;
use cfg\component\component_type;
use cfg\component\component_link_type;
use cfg\component\position_type;
use cfg\component\view_style;
use cfg\element\element_type;
use cfg\formula\formula;
use cfg\formula\formula_link_type;
use cfg\formula\formula_type;
use cfg\ref\ref;
use cfg\ref\ref_type;
use cfg\ref\source;
use cfg\ref\source_list;
use cfg\ref\source_type;
use cfg\result\result;
use cfg\language\language;
use cfg\language\language_form;
use cfg\log\change_action;
use cfg\log\change_table;
use cfg\log\change_field;
use cfg\phrase\phrase_types;
use cfg\system\job_type;
use cfg\system\sys_log_status;
use cfg\system\sys_log_type;
use cfg\system\system_time_type;
use cfg\user\user;
use cfg\user\user_list;
use cfg\user\user_profile;
use cfg\user\user_type;
use cfg\user\user_official_type;
use cfg\value\value;
use cfg\verb\verb;
use cfg\verb\verb_list;
use cfg\view\view;
use cfg\view\view_link_type;
use cfg\view\view_list;
use cfg\view\view_type;
use cfg\word\triple;
use cfg\word\triple_list;
use cfg\word\word;
use cfg\word\word_list;
use shared\types\protection_type;
use shared\types\share_type;

class def
{

    /*
     * classes
     */

    // the main classes that have a
    // corresponding frontend object,
    // a database table and
    // can be im- and exported
    const MAIN_CLASSES = [
        word::class,
        verb::class,
        triple::class,
        source::class,
        ref::class,
        value::class,
        formula::class,
        result::class,
        view::class,
        component::class,
        user::class
    ];

    // classes that have a code id
    // to select single database rows from the code with a unique key
    // or a type which cannot be changed by IP users
    // so the requesting user needs to be included in the mapping request
    const CODE_ID_CLASSES = [
        word::class,
        word_list::class,
        verb::class,
        verb_list::class,
        triple::class,
        triple_list::class,
        source::class,
        source_list::class,
        user::class,
        user_list::class,
        view::class,
        view_list::class,
        component::class,
        component_list::class,
    ];

    // classes that have a user interface message code id
    const UI_MSG_CODE_ID_CLASSES = [
        component::class,
    ];

    // classes that does not need a foreign db key
    const NO_FOREIGN_DB_KEY_CLASSES = [
        verb::class,
    ];

    // type classes that have a csv file for the initial load
    const BASE_CODE_LINK_FILES = [
        sys_log_status::class,
        sys_log_type::class,
        job_type::class,
        change_action::class,
        change_table::class,
        change_field::class,
        element_type::class,
        formula_link_type::class,
        formula_type::class,
        language::class,
        language_form::class,
        protection_type::class,
        ref_type::class,
        share_type::class,
        source_type::class,
        system_time_type::class,
        user_official_type::class,
        user_profile::class,
        user_type::class,
        position_type::class,
        component_link_type::class,
        component_type::class,
        view_link_type::class,
        view_type::class,
        view_style::class,
        phrase_types::class
    ];

    // log type classes that have a csv file for the initial load
    const LOG_CODE_LINK_FILES = [
        change_action::class,
        change_table::class,
        change_field::class,
    ];

    // list of classes that are used in the api e.g. to receive the user changes
    const API_CLASSES = [
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

    // list of classes that have a csv with the code id for the initial user profile and type setup
    const CLASS_WITH_USER_CODE_LINK_CSV = [
        user_profile::class,
        user_type::class
    ];

    // list of classes that use the user sandbox
    const SANDBOX_CLASSES = [
        word::class,
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
