<?php

/*

    shared/views.php - system views with name and id
    ----------------


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

namespace shared;

class views
{

    // code_id and database id of internal views used by the system
    // these views used by the program that are never supposed to be changed
    // MC_* is the Mask Code id that is expected never to change
    // MI_* is the Mask ID that is expected never to change
    const MC_START = 'start';
    const MI_START = 1;

    // curl views
    const MC_WORD_ADD = 'word_add';
    const MI_WORD_ADD = 2;
    const MC_WORD_EDIT = 'word_edit';
    const MI_WORD_EDIT = 3;
    const MC_WORD_DEL = 'word_del';
    const MI_WORD_DEL = 4;
    const MC_VERB_ADD = 'verb_add';
    const MC_VERB_EDIT = 'verb_edit';
    const MC_VERB_DEL = 'verb_del';
    const MC_TRIPLE_ADD = 'triple_add';
    const MC_TRIPLE_EDIT = 'triple_edit';
    const MC_TRIPLE_DEL = 'triple_del';
    const MC_VALUE_DISPLAY = 'value';
    const MC_VALUE_ADD = 'value_add';
    const MC_VALUE_EDIT = 'value_edit';
    const MC_VALUE_DEL = 'value_del';
    const MC_FORMULA_ADD = 'formula_add';
    const MC_FORMULA_EDIT = 'formula_edit';
    const MC_FORMULA_DEL = 'formula_del';
    const MC_FORMULA_EXPLAIN = 'formula_explain';
    const MC_FORMULA_TEST = 'formula_test';
    const MC_SOURCE_ADD = 'source_add';
    const MI_SOURCE_ADD = 11;
    const MC_SOURCE_EDIT = 'source_edit';
    const MI_SOURCE_EDIT = 12;
    const MC_SOURCE_DEL = 'source_del';
    const MI_SOURCE_DEL = 13;
    const MC_VERBS = 'verbs';
    const MC_USER = 'user';
    const MC_ERR_LOG = 'error_log';
    const MC_ERR_UPD = 'error_update';
    const MC_IMPORT = 'import';
    // views to edit views
    const MC_VIEW_ADD = 'view_add';
    const MC_VIEW_EDIT = 'view_edit';
    const MC_VIEW_DEL = 'view_del';
    const MC_COMPONENT_ADD = 'component_add';
    const MC_COMPONENT_EDIT = 'component_edit';
    const MC_COMPONENT_DEL = 'component_del';
    const MC_COMPONENT_LINK = 'component_link';
    const MC_COMPONENT_UNLINK = 'component_unlink';

    // default views
    const MC_WORD = 'word';
    const MI_WORD = 41;

    // functional views
    const MC_WORD_FIND = 'word_find';

    // system masks that have a word as the main object
    const WORD_MASKS_IDS = [
        self::MI_WORD_ADD,
        self::MI_WORD_EDIT,
        self::MI_WORD_DEL
    ];

    // system masks that have a word as the main object
    const SOURCE_MASKS_IDS = [
        self::MI_SOURCE_ADD,
        self::MI_SOURCE_EDIT,
        self::MI_SOURCE_DEL
    ];

    // system masks that have a word as the main object
    const EDIT_DEL_MASKS_IDS = [
        self::MI_WORD_EDIT,
        self::MI_WORD_DEL,
        self::MI_SOURCE_EDIT,
        self::MI_SOURCE_DEL,
    ];

}
