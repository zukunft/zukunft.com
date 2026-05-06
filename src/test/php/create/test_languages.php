<?php

/*

    test/create/test_languages.php - create the test language objects
    ------------------------------


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

namespace Zukunft\ZukunftCom\test\php\create;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_LANGUAGE . 'language.php';
include_once paths::SHARED_ENUM . 'languages.php';

use Zukunft\ZukunftCom\main\php\cfg\language\language;
use Zukunft\ZukunftCom\main\php\shared\enum\languages;

class test_languages
{

    function language(): language
    {
        $lan = new language(
            languages::DEFAULT,
            languages::DEFAULT_NAME,
            languages::DEFAULT_COM,
            languages::DEFAULT_ID
        );
        $lan->local_name = languages::DEFAULT_LOCAL_NAME;
        return $lan;
    }

    function language_translate(): language
    {
        $lan = new language(
            languages::TRANSLATE_TEST,
            languages::TRANSLATE_TEST_NAME,
            languages::TRANSLATE_TEST_COM,
            languages::TRANSLATE_TEST_ID
        );
        $lan->local_name = languages::TRANSLATE_TEST_LOCAL_NAME;
        return $lan;
    }

}