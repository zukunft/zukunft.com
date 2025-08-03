<?php

/*

    shared/const/rest_ctrl.php - constants used for the backend to frontend api of zukunft.com
    --------------------------


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

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace shared\const;

class rest_ctrl
{

    // methods used
    const PHP_SELF = 'PHP_SELF';
    const PHP_AUTH_USER = 'PHP_AUTH_USER';
    const PHP_AUTH_PW = 'PHP_AUTH_PW';
    const REMOTE_USER = 'REMOTE_USER';
    const REMOTE_ADDR = 'REMOTE_ADDR';
    const REQUEST_METHOD = 'REQUEST_METHOD';
    const REQUEST_URI = 'REQUEST_URI';
    const GET = 'GET';
    const POST = 'POST';
    const PUT = 'PUT';
    const DELETE = 'DELETE';

    // to get the json body included in the request
    const REQUEST_BODY_FILENAME = 'php://input';

    // url path of the api
    const PATH = 'api/';

    // url path to the fixed views
    const PATH_FIXED = '/http/';
    const URL_MAIN_SCRIPT = 'view';

    // url extension of the fixed views
    const EXT = '.php';

    // classes used to allow renaming of the API name independent of the class name
    const WORD = 'word';
    const VERB = 'verb';
    const TRIPLE = 'triple';
    const VALUE = 'value';
    const FORMULA = 'formula';
    const VIEW = 'view';
    const LINK = 'link';
    const SOURCE = 'source';
    const LANGUAGE = 'language';

    // class extensions of all possible the fixed views
    const CREATE = '_add';
    const UPDATE = '_edit';
    const REMOVE = '_del';
    const LIST = '';
    const SEARCH = 'find';

    // special api function independent of a class
    const LOGIN_RESET = 'login_reset';
    const ERROR_UPDATE = 'error_update';
    const URL_ABOUT = 'about';

    // view parameter names
    const PAR_VIEW_VERBS = 'verbs';  // to select the verbs that should be display
    const PAR_LOG_STATUS = 'status'; // to set the status of a log entry
    const PAR_VIEW_SOURCES = 'sources';  // to select the formulas that should be display
    const PAR_VIEW_LANGUAGES = 'languages';  // to select the formulas that should be display
    const PAR_VIEW_NEW_ID = 'new_id'; // if the user has changed the view for this word, save it
    const PAR_VIEW_ID = 'view'; // if the user has selected a special view, use it

    // classes used
    const CLASS_FORM_ROW = 'form-row';

    // to be reviewed
    const VALUE_EDIT = 'value_edit';
    const RESULT_EDIT = 'result_edit';

}
