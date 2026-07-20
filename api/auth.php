<?php

/*

  api/auth.php - the word API controller: send a word to the frontend
  ------------
  
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

include_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'api_const.php';

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL . 'application.php';

use Zukunft\ZukunftCom\main\php\cfg\application;

// open database
$app = new application();
$db_con = $app->start_api("auth", "", false);

if ($db_con->is_open()) {

    // this basic-auth endpoint is not implemented yet: the previous body read the credentials but
    // never checked them and called an undefined helper, so any request without an Authorization
    // header raised a php fatal. return a clean 501 instead until real token auth is added, so the
    // endpoint can neither be used nor probed (see docs/llm/pending.md)
    http_response_code(501);
    echo 'Not implemented';

    $app->end_api($db_con);
}