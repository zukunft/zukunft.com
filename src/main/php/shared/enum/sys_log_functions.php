<?php

/*

    shared/enum/sys_log_functions.php - enum of system log functions used for unit testing
    -----------------------------


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

namespace Zukunft\ZukunftCom\main\php\shared\enum;

enum sys_log_functions: string
{

    // list of system log functions used for unit testing
    const string IMPORT_BASE_CONFIG = "import_base_config";
    const int IMPORT_BASE_CONFIG_ID = 1;
    const string IMPORT_BASE_CONFIG_NAME = "Import system configuration";
    const string IMPORT_BASE_CONFIG_COM = "import all zukunft.com base configuration json files";
    const string IMPORT_POD_CONFIG = "import_test_config";
    const string IMPORT_POD_CONFIG_NAME = "Import pod configuration";
    const string IMPORT_TEST_CONFIG = "import_test_config";
    const string IMPORT_TEST_CONFIG_NAME = "Import test configuration";

    // only to test the write functions
    const string TEST_NAME = "System test function name";
    const string TEST_COM = "only to test the write functions";

}