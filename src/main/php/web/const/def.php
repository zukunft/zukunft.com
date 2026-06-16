<?php

/*

    web/const/def.php - general frontend definitions
    -----------------


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

namespace Zukunft\ZukunftCom\main\php\web\const;

use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::COMPONENT . 'component.php';
include_once html_paths::FORMULA . 'formula.php';
include_once html_paths::REF . 'ref.php';
include_once html_paths::REF . 'source.php';
include_once html_paths::VIEW . 'view.php';
include_once html_paths::WORD . 'word.php';

use Zukunft\ZukunftCom\main\php\web\component\component;
use Zukunft\ZukunftCom\main\php\web\formula\formula;
use Zukunft\ZukunftCom\main\php\web\ref\ref;
use Zukunft\ZukunftCom\main\php\web\ref\source;
use Zukunft\ZukunftCom\main\php\web\view\view;
use Zukunft\ZukunftCom\main\php\web\word\word;

class def
{

    // list of classes that have a data type
    const array TYPE_CLASSES = [
        word::class,
        source::class,
        ref::class,
        formula::class,
        view::class,
        component::class,
    ];

}
