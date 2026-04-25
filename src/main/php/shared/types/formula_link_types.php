<?php

/*

    shared/types/formula_link_types.php - db based ENUM of the formula link types
    -----------------------------------

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

namespace Zukunft\ZukunftCom\main\php\shared\types;

class formula_link_types
{

    // list of the formula link types that have a coded functionality
    const string DEFAULT = "default";               // a simple link between a formula and a phrase
    const int DEFAULT_ID = 1;
    const string DEFAULT_NAME = "default";
    const string DEFAULT_COM = "default";
    const string TIME_PERIOD = "time_period_based"; // for time based links

}
