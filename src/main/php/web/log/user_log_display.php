<?php

/*

    web/log/user_log_display.php - a combined object to display single value changes or changes of links by the user
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

    Copyright (c) 1995-2025 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com
  
*/


namespace Zukunft\ZukunftCom\main\php\web\log;

use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::DB . 'sql.php';
include_once html_paths::DB . 'sql_db.php';
// change_log_link_list (same namespace) is loaded by the frontend bootstrap (component_exe.php);
// it must not be included here because that would close an include cycle via change_log -> user
//include_once html_paths::HTML . 'button.php';
//include_once html_paths::HTML . 'html_base.php';
//include_once html_paths::COMPONENT . 'component_exe.php';
//include_once html_paths::FORMULA . 'formula.php';
//include_once html_paths::USER . 'user.php';
//include_once html_paths::USER . 'user_message.php';
//include_once html_paths::VALUE . 'value.php';
//include_once html_paths::VIEW . 'view.php';
//include_once html_paths::WORD . 'word.php';
include_once html_paths::SHARED_CONST . 'views.php';
include_once html_paths::SHARED_ENUM . 'change_tables.php';
include_once html_paths::SHARED_ENUM . 'change_fields.php';
include_once html_paths::SHARED_ENUM . 'messages.php';
include_once html_paths::SHARED . 'library.php';
include_once html_paths::SHARED_CONST_FIELDS . 'fields.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\web\component\component_exe as component;
use Zukunft\ZukunftCom\main\php\web\formula\formula;
use Zukunft\ZukunftCom\main\php\web\html\button;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\user\user;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\value\value;
use Zukunft\ZukunftCom\main\php\web\view\view;
use Zukunft\ZukunftCom\main\php\web\word\word;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\enum\change_tables;
use Zukunft\ZukunftCom\main\php\shared\enum\change_fields;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\const\fields\fields;

class user_log_display
{

    public int $id;                // the database id of the word, phrase, value or formula object
    public ?object $obj = null;    // the calling object
    public ?user $usr = null;      // the user of the person for whom the value is loaded, so to say the viewer
    public string $type;           // either "word", "phrase", "value" or "formula" to select the object to display
    public int $page;              // the page to display
    public bool $condensed = True; // display the changes in a few columns with reduced details
    public int $size;              // the page size
    public string $call = '';      // the html page which has call the hist display object
    public array $url_arr = [];    // the url vars of the calling page for the back link of the undo buttons

    /**
     * for a user log it is always needed to know who wants to seen the log
     */
    function __construct()
    {
        //$this->usr = $usr;
    }

    /**
     * @param array $url_arr the url vars of the calling page for the back link of the undo buttons
     */
    function dsp_hist(
        string       $class,
        int|string   $id,
        int          $size,
        int          $page,
        user_message $msg,
        string       $call = '',
        array        $url_arr = []
    ): string
    {
        $lst = new change_log_list();
        $lst->load_by_object_field($class, $msg, $id, '', null, $size, $page);
        $result = $lst->tbl($url_arr, $this->condensed);
        return '';
    }

    // display change of links
    // e.g. if a formula is linked to another word
    //   or if a component is added to a display view
    function dsp_hist_links(user_message $msg): string
    {
        log_debug('user_log_display->dsp_hist_links ' . $this->type . ' id ' . $this->id . ' size ' . $this->size . ' page ' . $this->page . ' call from ' . $this->call);

        // loaded here (not at the top of the file) because change_log_link extends change_log_named,
        // which sits at the root of the bootstrap include chain; a top-level include would close a cycle
        include_once html_paths::LOG . 'change_log_link_list.php';

        // load the link change history from the backend via the api (no direct database access in web/)
        $lst = new change_log_link_list();
        $lst->load_by_object($this->type, $msg, $this->id, $this->usr);

        log_debug("done");
        // the undo links return to the calling page
        return $lst->tbl($this->url_arr);
    }

}