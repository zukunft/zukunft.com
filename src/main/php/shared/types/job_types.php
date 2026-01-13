<?php

/*

    shared/types/job_types.php - ENUM of the used job types
    --------------------------

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

class job_types
{

    // list of the job types that have a coded functionality
    // TODO Prio 2 add for all job types at least one unit test
    const string VALUE_UPDATE = "value_update";
    const int VALUE_UPDATE_ID = 1;
    const string VALUE_UPDATE_NAME = "update value";
    const string VALUE_UPDATE_COM = "if a value is updated all the depending results should be calculated again";
    const string WORD_DELETE = "word_delete";
    const string TRIPLE_DELETE = "triple_delete";

}
