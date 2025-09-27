<?php

/*

    test_lib.php - general functions used in several unit, read or write tests
    ------------

    TODO move all test resource file reading to this class
    TODO create a update_test_result function to overwrite a test file if confirmed by a developer


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

namespace Zukunft\ZukunftCom\test\php\utils;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\cfg\view\view_list;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::HELPER . 'data_object.php';
include_once html_paths::USER . 'user.php';
include_once html_paths::VIEW . 'view_list.php';
include_once paths::MODEL_IMPORT . 'import.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED_CONST . 'files.php';
include_once TEST_CONST_PATH . 'files.php';

use Zukunft\ZukunftCom\main\php\cfg\import\import;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message as backend_user_message;
use Zukunft\ZukunftCom\main\php\web\helper\data_object as data_object_dsp;
use Zukunft\ZukunftCom\main\php\web\types\type_lists;
use Zukunft\ZukunftCom\main\php\web\user\user as user_dsp;
use Zukunft\ZukunftCom\main\php\web\view\view_list as view_list_dsp;
use Zukunft\ZukunftCom\main\php\shared\const\files;
use Zukunft\ZukunftCom\test\php\const\files as test_files;

class test_lib
{

    /**
     * cast a backend user to a frontend user
     * @param user $usr the filled backend user object
     * @return user_dsp the filled frontend user object
     */
    function cast_user(user $usr): user_dsp
    {
        $usr_dsp = new user_dsp();
        $usr_dsp->set_from_json($usr->api_json());
        return $usr_dsp;
    }

    function cast_view_list(view_list $msk_lst): view_list_dsp
    {
        $api_msg = $msk_lst->api_json();
        return new view_list_dsp($api_msg);
    }

    /**
     * create the dummy frontend cache entries for the unit tests
     * @param user $usr the user for which the sample cache should be created
     * @return data_object_dsp
     */
    function dummy_test_cache(user $usr): data_object_dsp
    {
        $dto_dsp = new data_object_dsp();
        $dto_dsp->usr = $this->cast_user($usr);

        // load type lists from resource json file
        $api_msg = file_get_contents(test_files::TYPE_LISTS_CACHE);
        $dto_dsp->typ_lst_cache = new type_lists($api_msg);

        // import system views from resource json file
        $imp = new import();
        $imp->usr = $usr;
        $json_str = file_get_contents(files::SYSTEM_VIEWS);
        $size = strlen($json_str);
        $json_array = json_decode($json_str, true);
        $usr_msg = new backend_user_message();
        $dto = $imp->get_data_object($json_array, $usr_msg, $size);
        $dto_dsp->set_view_list($this->cast_view_list($dto->view_list()));

        return $dto_dsp;
    }

}
