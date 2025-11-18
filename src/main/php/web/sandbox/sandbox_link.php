<?php

/*

    web/sandbox/sandbox.php - extends the frontend db object superclass for user sandbox functions such as share type
    -----------------------


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

namespace Zukunft\ZukunftCom\main\php\web\sandbox;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::FORMULA . 'formula.php';
include_once html_paths::PHRASE . 'phrase.php';
include_once html_paths::SANDBOX . 'sandbox.php';
//include_once html_paths::VIEW . 'view.php';

use Zukunft\ZukunftCom\main\php\web\formula\formula;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase;
use Zukunft\ZukunftCom\main\php\web\view\view;

class sandbox_link extends sandbox
{

    /*
     * object vars
     */

    protected formula|view|sandbox_named|combine_named|null $fob = null; // the (F)rom (OB)ject which this linked object is creating the connection
    protected phrase|view|sandbox_named|combine_named|string|null $tob = null; // the (T)o (OB)ject which this linked object is creating the connection (can be a string for external keys)
    protected int|null $type_id = null; // the link type

}


