<?php

/*

    shared/const/impacts.php - fixed impact values for system testing
    ------------------------


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

class impacts
{

    const float MAX = 9999999999;
    const float LOW = 12.34;
    const float MEDIUM = 123.4;
    const float HIGH = 1234.0;

    // based on src/test/resources/import/zurich_htp_impact.json
    const float HTP_ZH_CITY = 71152;
    const float HTP_ZH_CANTON = 32400;
    const float HTP_ZH_COMPANY = 22500;

}