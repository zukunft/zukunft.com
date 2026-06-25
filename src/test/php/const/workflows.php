<?php

/*

    test/php/const/workflows.php - predefined const for the url based user workflow tests
    ----------------------------

    the snapshot folder and file names of a workflow test are built from these const
    so the workflow id and the name parts are changed in one place (docs/llm/testing.md)

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

    Copyright (c) 1995-2026 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\test\php\const;

class workflows
{

    // separator between the name parts of a workflow snapshot file name e.g. 'wf2_show_edit'
    const string NAME_SEP = '_';

    // fixed text that replaces the volatile change log entry (add time + add user) in the snapshots
    const string WF_CHANGE_LOG = 'system test change log entry';

    // the snapshot file name prefix that is followed by the workflow id e.g. 'wf2'
    const string WF_PREFIX = 'wf';

    // the add_word workflow name used for the snapshot folder and the test subheader
    const string WF_ADD_WORD = 'add_word';
    // the id of the current add_word workflow; increase it to add the next workflow snapshot set
    const int WF_ADD_WORD_NBR = 1;

    // the change_word workflow name used for the snapshot folder and the test subheader
    const string WF_CHANGE_WORD = 'change_word';
    // the id of the current change_word workflow; increase it to add the next workflow snapshot set
    const int WF_CHANGE_WORD_NBR = 2;

    // the del_word workflow name used for the snapshot folder and the test subheader
    const string WF_DEL_WORD = 'del_word';
    // the id of the current del_word workflow; increase it to add the next workflow snapshot set
    const int WF_DEL_WORD_NBR = 3;

    // the change_triple workflow name used for the snapshot folder and the test subheader
    const string WF_CHANGE_TRIPLE = 'change_triple';
    // the id of the current change_triple workflow; increase it to add the next workflow snapshot set
    const int WF_CHANGE_TRIPLE_NBR = 5;

}