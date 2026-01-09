<?php

/*

    shared/types/view_styles.php - db based ENUM of the view and component styles
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

    Copyright (c) 1995-2024 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com
  
*/

namespace Zukunft\ZukunftCom\main\php\shared\types;

class view_styles
{

    // list of the view and component styles that have a coded functionality
    // where *_COM is the description for the tooltip

    const string DEFAULT = self::COL_SM_4;
    const string DEFAULT_ROW = self::COL_SM_12;
    const string COL_SM_2 = 'col-md-2';
    // just to display a fixed text
    const string COL_SM_4_COM = 'use 1/3 of the width (col-md-4)';
    const string COL_SM_4 = 'col-md-4';
    const string COL_SM_4_NAME = '1/3 width';
    const int COL_SM_4_ID = 1;
    const string COL_SM_5 = 'col-md-5';
    const string COL_SM_6_COM = 'use half of the width (col-md-6)';
    const string COL_SM_6 = 'col-md-6';
    const string COL_SM_7 = 'col-md-7';
    const string COL_SM_8 = 'col-md-8';
    const string COL_SM_10 = 'col-md-10';
    const string COL_SM_12 = 'col-md-12';
    const string COL_SM_1 = 'col-md-1';
    const string COL_SM_3 = 'col-md-3';
    const string BS_SM_2 = 'mr-sm-2';


    // list of the styles used for unit testing
    const array TEST_TYPES = array(
        [view_styles::COL_SM_4, 1],
        [view_styles::COL_SM_8, 2],
    );

}
