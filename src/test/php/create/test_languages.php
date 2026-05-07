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
include_once paths::MODEL_LANGUAGE . 'language_list.php';
include_once paths::SHARED_ENUM . 'languages.php';

use Zukunft\ZukunftCom\main\php\cfg\language\language;
use Zukunft\ZukunftCom\main\php\cfg\language\language_list;
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
            languages::TRANSLATE,
            languages::TRANSLATE_NAME,
            languages::TRANSLATE_COM,
            languages::TRANSLATE_ID
        );
        $lan->wiki_code = languages::TRANSLATE_WIKI;
        $lan->local_name = languages::TRANSLATE_LOCAL_NAME;
        $lan->usage = languages::TRANSLATE_USAGE;
        return $lan;
    }

    function language_long_char(): language
    {
        $lan = new language(
            languages::LONG_CHAR,
            languages::LONG_CHAR_NAME,
            languages::LONG_CHAR_COM,
            languages::LONG_CHAR_ID
        );
        $lan->local_name = languages::LONG_CHAR_LOCAL_NAME;
        return $lan;
    }

    function language_reverse(): language
    {
        $lan = new language(
            languages::REVERSE,
            languages::REVERSE_NAME,
            languages::REVERSE_COM,
            languages::REVERSE_ID
        );
        $lan->local_name = languages::REVERSE_LOCAL_NAME;
        return $lan;
    }

    function language_nice(): language
    {
        $lan = new language(
            languages::NICE,
            languages::NICE_NAME,
            languages::NICE_COM,
            languages::NICE_ID
        );
        $lan->local_name = languages::NICE_LOCAL_NAME;
        return $lan;
    }

    function language_often(): language
    {
        $lan = new language(
            languages::OFTEN,
            languages::OFTEN_NAME,
            languages::OFTEN_COM,
            languages::OFTEN_ID
        );
        $lan->local_name = languages::OFTEN_LOCAL_NAME;
        return $lan;
    }

    function language_list(): language_list
    {
        $lst = new language_list();
        $lst->add($this->language());
        $lst->add($this->language_translate());
        $lst->add($this->language_nice());
        $lst->add($this->language_often());
        $lst->add($this->language_long_char());
        $lst->add($this->language_reverse());
        return $lst;
    }

}