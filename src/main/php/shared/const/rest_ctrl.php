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

namespace Zukunft\ZukunftCom\main\php\shared\const;

class rest_ctrl
{

    // methods used
    const string PHP_SELF = 'PHP_SELF';
    const string PHP_AUTH_USER = 'PHP_AUTH_USER';
    const string PHP_AUTH_PW = 'PHP_AUTH_PW';
    const string REMOTE_USER = 'REMOTE_USER';
    const string REMOTE_ADDR = 'REMOTE_ADDR';
    const string REQUEST_METHOD = 'REQUEST_METHOD';
    const string REQUEST_URI = 'REQUEST_URI';
    const string GET = 'GET';
    const string POST = 'POST';
    const string PUT = 'PUT';
    const string DELETE = 'DELETE';

    // to get the json body included in the request
    const string REQUEST_BODY_FILENAME = 'php://input';

    // url path of the api
    const string PATH = 'api/';

    // url path to the fixed views
    const string PATH_FIXED = '/http/';
    const string URL_MAIN_SCRIPT = 'view';

    // url extension of the fixed views
    const string EXT = '.php';

    // classes used to allow renaming of the API name independent of the class name
    const string WORD = 'word';
    const string VERB = 'verb';
    const string TRIPLE = 'triple';
    const string VALUE = 'value';
    const string FORMULA = 'formula';
    const string VIEW = 'view';
    const string LINK = 'link';
    const string SOURCE = 'source';
    const string LANGUAGE = 'language';

    // class extensions of all possible the fixed views
    const string CREATE = '_add';
    const string UPDATE = '_edit';
    const string REMOVE = '_del';
    const string LIST = '';
    const string SEARCH = 'find';

    // special api function independent of a class
    const string LOGIN_RESET = 'login_reset';
    const string ERROR_UPDATE = 'error_update';
    const string URL_ABOUT = 'about';

    // view parameter names
    const string PAR_VIEW_VERBS = 'verbs';  // to select the verbs that should be display
    const string PAR_LOG_STATUS = 'status'; // to set the status of a log entry
    const string PAR_VIEW_SOURCES = 'sources';  // to select the formulas that should be display
    const string PAR_VIEW_LANGUAGES = 'languages';  // to select the formulas that should be display
    const string PAR_VIEW_NEW_ID = 'new_id'; // if the user has changed the view for this word, save it
    const string PAR_VIEW_ID = 'view'; // if the user has selected a special view, use it

    // classes used
    const string CLASS_FORM_ROW = 'form-row';

    // to be reviewed
    const string VALUE_EDIT = 'value_edit';
    const string RESULT_EDIT = 'result_edit';

}
