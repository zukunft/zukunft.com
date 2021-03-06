<?php

/*

  zu_lib.php - the main ZUkunft.com LIBrary
  __________

  this code follows the principle of Antoine de Saint-Exupéry 

  "Il semble que la perfection soit atteinte non quand il n'y a plus rien à ajouter, 
   mais quand il n'y a plus rien à retrancher." 
   
  In English "reduce to the max"
  
  The code use for zukunft.com should be as simple as possible and have as less dependencies as possible,
  so basically follow the Zen of Python https://www.python.org/dev/peps/pep-0020/
  So you the minimal requirements are a LAMP server (https://wiki.debian.org/LaMp) and an HTML (using some HTML5 features) browser.
  If you see anything that does not look simple to you, 
  please request a change on https://github.com/zukunft/zukunft.com
  or write an email to timon@zukunft.com
  
  
  General coding principles:
   - init:        each function first collect the global vars, initiate the result variable and create a debug message if requested
   - check input: each method should check the consistency of the input parameters at the beginning
   - debug:       all methods have a "debug" message after the input check for easy debugging
   - $debug-1:    a call of the next level function should be with debug-1 so that the user can dig into step by step
   - $debug-10:   up to a debug level of 10 the debug messages should be a kind of user readable
                  debug levels above 10 are used for programmer debugging
   - each code file start with a table of contents and the copyright
   - each function should be tested in test.php
   - debug messages are display immediately using echo (always via the function zu_debug)
   - all other function usually return html code that is displayed only by one of the 8 main interface scripts
  
  Naming conventions:
  -------------------
  wrd (WoRD)               - a word that is used as a subject or object in a resource description framework (RDF / "triple") graph
                             and used to retrieve the numeric values
  val (VALue)              - a numeric value the can be used for calculations
  frm (FoRMula)            - a formula in the zukunft.com format,
                             which can be either in the usr (USeR) format with real words
                             or in the db (DataBase) format with database id references
                             or in the math (MATHematical) format, which should contain only numeric values
  
  vrb (VeRB)               - a predicate (mostly just a verb) that defines the type that links two words;
                             by default a verb can be used forward and backward e.g. ABB is a company and companies are ABB, ...
                             if the reverse name is empty, the verb can only be used the forward way
                             if a link should only be used one way for one phrase link, the negative verb is saved
                             verbs are also named as word_links
  lnk (LiNK)               - a triple, so a word, connected to another word with a verb (word_link.php is the related class)
  phr (PHRase)             - either a word or triple mainly used for selection
  grp (GrouP)              - a group of terms or triples excluding time terms to reduce the number of groups needed and speed up the system
  trm (TeRM)               - either a work, verb or triple (formula names have always a corresponding phrase)
  exp (EXPression)         - a formula text that implies a data selection and lead to a number
  elm (ELeMents)           - a structured reference for terms, verbs or formulas mostly used for formula elements
  fv (Formula Value)       - the calculated result of a formula (rename to result? and if use RESult)
  fig (FIGure)             - either a value set by the user or a calculated formula result
  usr (USeR)               - the person who is logged in
  log                      - to save all changes in a user readable format
  src (SouRCe)             - url or description where a value is taken from

  sbx (SandBoX)            - the user sandbox tables where the adjustments of the users are saved
  uso (User Sbx Object)    - an object (word, value, formula, ...) that uses the user sandbox

  id (IDentifier)          - internal prime key of a database row
  ids (IDentifierS)        - an simple array of database table IDs (ids_txt is the text / imploded version of the ids array)
  glst (Get LiST)          - is used to name the private internal functions that can also create the user list
  lst (LiST)               - an array of objects
  ulst (User LiST)         - an array of objects that should be shown to the user, so like lst, but without the objects exclude by the user
                             the user list should only be used to display something and never for checking if an item exists
                             this is the short for for sbx_lst

  dsp (DiSPlay)            - a view/mask that is shown to the user
  ui (UserInterface)       - the definition of the user interface, mainly used to display either the JavaScript based single page design, the bootstrap based HTML design, the design based on pure HTML code or a pure text output for testing
  djs (DiSPlay JavaScript) - functions for the vue.js JavaScript user interface implementation
  dbs (DiSPlay BootStrap)  - functions for the bootstrap user interface implementation
  dsp (DiSPlay html)       - functions for the pure html user interface implementation
                             a view object or a function that return HTML code that can be displayed
  dtx (DiSPlay TeXt)       - functions for the text interface implementation mainly for debugging
  cmp (CoMPonent)          - one part of a view so a kind of view component (ex view entry)
  dsl (DSp cmp Link)       - link of a view component to a view
  btn (BuTtoN)             - button
  tbl (TaBLe)              - HTML code for a table
  
  cl (Code Link)           - a text used to identify one predefined database entry that triggers to use of some program code
  sf (Sql Format)          - to convert a text for the database
  
  
  database change setup
  ---------------------
  
  User Sandbox: values, formulas, formula_links, views and view elements are included in the user sandbox, which means, each user can exclude or adjust single entries
  
  to avoid confusion words, formula names, word_links (verbs) and value_phrase_links are excluded from the user sandbox, but a normal user can change the name, which will hopefully not happen often. 
  
  for words, formulas and verbs the user can add a specific name in any language
  
  Admin edit: for word_links (verbs), word_types, link_types, formula_types there is only one valid record and only an admin user is allowed to change it, which is also because these tables have a code id
  
  Sources: every user can change it, but there is only one valid row
  
  Fixed server splitting (if not hadoop is used as the backend)
  To split the load between to several servers it is suggested to move one word and all it's related values and results to a second server
  further splitting can be done by another word to split in hierarchy order 
  e.g. use company as the first splitter and than ABB, Daimler, ... as the second or CO2 as the second tree
       in this case the CO2 balance of ABB will be on the "Company ABB server", but all other CO2 data will be on en "environment server"
  the word graph should stay on the main server for consistency reasons     
       
  function naming
  ---------------
  
  all classes should have these functions: 
  
  load                  - based on given id setting load an existing object; if no object is found, return null
  load_*_types          - load all types once from the database, because types are supposed to change almost never or with a program version change
                          e.g. the global function load_ref_types load all possible reference type to external databases
  get                   - based on given id setting load an existing object; if not found in database create it
  get_*_type            - get a type object by the id
  get_*_type_by_name    - get a type object by the code id
  get_*_type_by_code_id - get a type object by the code id
  save                  - update all changes in the database; if not found in database create it
  dsp_id                - like name, but with some ids for better debugging
  name                  - to show a useful name of the object to the user e.g. in case of a formula result this includes the phrases
  name_linked           - like name, but with HTML link to the single objects
  display               - the result and the name of the object e.g. ABB, Sales: 46'000
  display_linked        - like display, but with HTML links to the related objects

  All objects needs to have the functions dsp_id and name. These two functions should never all any debug functionality, because they can be called from the debug functions
  
  *_test         - the unit test function which should be below each function e.g. the function prg_version_is_older is tested by prg_version_is_older_test
  
  TODO create unit tests for all module classes
  TODO complete the database abstraction layer
  TODO name all queries with user data as prepared queries to prevent SQL code injections
  TODO split the load and the load_sql functions to be able to add unit tests for all sql statements
  TODO crawl all public available information from the web and add it as user preset to the database
  TODO rename dsp_text in formula to display
  TODO rename name_linked in formula_element to name_linked
  
  

  functions of this library
  ---------
  
  prefix for functions in this library: zu_* 

  This library contains general functions like debug or string
  that could also be taken from another framework
  
  all functions that could potentially go wrong have the parameter debug,
  so that the administrator can find out more details about what has gone wrong
  a positive debug value means that the user wants to see some debug message
  

  debug functions
  -----
  
  zu_debug   - for interactive debugging (since version 0.0.3 based on a global $debug var, because meanwhile the PhpStorm has a debugger)
  zu_msg     - write a message to the system log for later debugging
  zu_info    - info message
  zu_warning - log a warning message if log level is set to warning
  zu_err     - log an error message
  zu_fatal   - log an fatal error message and call a database cleanup
  zu_start   - open the database and display the header
  zu_end     - close the database
  
  display functions - that all objects should have
  -------
  
  name        - the most useful name of the object for the user
  dsp_id      - the name including the database id for debugging
  zu_dsp_bool - 
  
  
  admin
  - use once loaded arrays for all tables that are never expected to be changed like the type tables
  - allow the admin user to set the default value
  - create a daily? task that finds the median value, sets it as the default and recreate the user values
  - add median_user and set_owner to all user sandbox objects
  - check which functions can be private in the user_sandbox
  - use private zukunft data to manage the zukunft payments for keeping data private and 
  - don't check ip address if someone is trying to login

  Technical
  - move the JSON object creation to the classes
  - use the SQL LIMIT clause in all SQL statements and ...
  - ... auto page if result size is above the limit
  - capsule all function so that all parameters are checked before the start
  
  usability
  - add a view element that show the value differences related to a word; e.g. where other user use other values and formula results for ABB

  UI
  - review UI concept: click only for view, double click for change and right click for more related change functions (or three line menu)

  view
  - move the edit and add view to the change view mask instead show a pencil to edit the view
  - add a select box the the view name in the page header e.g. select box to select the view with a line to add a new view 
  - add for values, words, formulas, ... the tab "compare" additional to "Changes"
  
  Table view
  - a table headline should show a mouseover message e.g. the "not unhappy ratio" should show explain what it is if the mouse is moved over the word
  - allow to add a sub row to the table view and allow to select a formula for the sub row

  value view
  - when displaying a value allow several display formats (template formatting including ...
  - ... sub values for related formula result
  - ... other user plus minus indicator
  - ... other user range chart)
  - show the values of other users also if the user has just an IP

  word view
  - set and compare the default view for words e.g. the view for company should be company list
  - in link_edit.php?id=313 allow to change the name for the phrase and show the history
  - rename word_links to phrase links, because it should always be possible to link a phrase
  
  formula
  
  log
  - add paging to the log view
  - combine changes and changes usage to one list
  - allow also to see the log of deleted words, values and formulas
  - in the log view show in an mondial view the details of the change
  - move the undo button in the formula log view to the row
  - display the changes on display elements
  
  export
  - export yaml
  - for xml export use the parameters: standard values, your values or values of all users; topic word or all words
  
  import
  - if an admin does the import he has the possibility to be the owner for all imported values

  features
  - allow paying users to protect their values and offer them to a group of users
    - the user can set the default to open or closed 
    - the user can open or close all values related to a word
  - each user can define uo to 100 users as "preferred trust"
  - for each user show all values, formulas, words where the user has different settings than the other users and allow to move back to the standard
  - it should be possible to link an existing formula to a word/phrase (plus on formula should allow also to link an existing formula)
  - make the phrase to value links for fast searching user specific 
  - allow to undo just von change or to revert all changes (of this formulas or all formulas, words, values) up to this point of time
  - display in the formula (value, word) the values of other users
  - check the correct usage of verbs (see definition)
  - for the speed check use the speed log table with the url and the execution time if above a threshold
  - for wishes use the github issue tracker
  - base increase (this, prior) on the default time jump (e.g. for turnover the time jump would be "yoy")

  Bugs
  - solve the view sorting issue by combining the user settings for view, link and components
    e.g. if a user changes the mask, he probably wants that the complete mask is unchanged
  - bug: display linked words does not display the downward words e.g. "Company main ratio" does not show "Target Price"
  - don't write the same log message several times during the same call
  - don't write too many log message in on php script call
  - fix error when linking an existing formula to a phase
  - review the user sandbox for values
  - remove all old zu_ function calls


  Prio 2:
  - review user authentication (use fidoalliance.org/fido2/)
  - review the database indices and the foreign keys
  - include a list of basic values in test.php e.g. CO2 of rice
  

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
  along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.
  
  To contact the authors write to:
  Timon Zielonka <timon@zukunft.com>
  
  Copyright (c) 1995-2021 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com

*/

const PRG_VERSION = "0.0.3"; // to detect the correct update script and to mark the data export
const NEXT_VERSION = "0.0.4"; // to prevent importing incompatible data
const FIRST_VERSION = "0.0.3"; // the first program version which has a basic upgrade process

// the used database objects (the table name is in most cases with an extra 's', because each table contains the data for many objects)
// TODO use const for all object names
const DB_TYPE_USER = 'user';
const DB_TYPE_WORD = 'word';
const DB_TYPE_WORD_LINK = 'word_link';
const DB_TYPE_WORD_TYPE = 'word_type';
const DB_TYPE_VERB = 'verb';
const DB_TYPE_PHRASE = 'phrase';
const DB_TYPE_PHRASE_GROUP = 'phrase_group';
const DB_TYPE_VALUE = 'value';
const DB_TYPE_VALUE_TIME_SERIES = 'value_time_series';
const DB_TYPE_VALUE_PHRASE_LINK = 'value_phrase_link';
const DB_TYPE_SOURCE = 'source';
const DB_TYPE_SOURCE_TYPE = 'source_type';
const DB_TYPE_REF = 'ref';
const DB_TYPE_FORMULA = 'formula';
const DB_TYPE_FORMULA_LINK = 'formula_link';
const DB_TYPE_FORMULA_ELEMENT = 'formula_element';
const DB_TYPE_FORMULA_VALUE = 'formula_value';
const DB_TYPE_VIEW = 'view';
const DB_TYPE_VIEW_COMPONENT = 'view_component';
const DB_TYPE_VIEW_COMPONENT_LINK = 'view_component_link';
const DB_TYPE_VIEW_COMPONENT_TYPE = 'view_component_type';

const DB_TYPE_CHANGE = 'change';
const DB_TYPE_CHANGE_TABLE = 'change_table';
const DB_TYPE_CHANGE_FIELD = 'change_field';
const DB_TYPE_CHANGE_ACTION = 'change_action';
const DB_TYPE_CHANGE_LINK = 'change_link';
const DB_TYPE_CONFIG = 'config';
const DB_TYPE_SYS_LOG = 'sys_log';
const DB_TYPE_SYS_LOG_FUNCTION = 'sys_log_function';
const DB_TYPE_SYS_SCRIPT = 'sys_script'; // to log the execution times for code optimising
const DB_TYPE_TASK = 'calc_and_cleanup_task';

const DB_TYPE_USER_PREFIX = 'user_';

const DB_FIELD_EXT_ID = '_id';
const DB_FIELD_EXT_NAME = '_name';

// the fixed system user
const SYSTEM_USER_ID = 1; //


// the main global vars to shorten the code by avoiding them in many function calls as parameter
global $db_com; // the database connection
global $usr;    // the session user
global $debug;  // the debug level

// TODO check the primary index of all user tables
// TODO load the config, that is not expected to be changed during a session once at startup
// TODO start the backend only once and react to REST calls from the frontend
// TODO make use of __DIR__ ?

// global vars for system control
global $sys_script;      // name php script that has been call this library
global $sys_trace;       // names of the php functions
global $sys_time_start;  // to measure the execution time
global $sys_time_limit;  // to write too long execution times to the log to improve the code
global $sys_log_msg_lst; // to avoid to repeat the same message

$sys_script = "";
$sys_trace = "";
$sys_time_start = time();
$sys_time_limit = time() + 2;
$sys_log_msg_lst = array();

global $root_path;

if ($root_path == '') {
    $root_path = '../';
}

// database links
include_once $root_path . 'database/sql_db.php';
if ($debug > 9) {
    echo 'mysql link loaded<br>';
}
include_once $root_path . 'src/main/php/db/db_check.php';
if ($debug > 9) {
    echo 'db check loaded<br>';
}
// service
include_once $root_path . 'src/main/php/service/import/import_file.php';
if ($debug > 9) {
    echo 'service import loaded<br>';
}
include_once $root_path . 'src/main/php/service/import/import.php';
if ($debug > 9) {
    echo 'class import loaded<br>';
}
include_once $root_path . 'src/main/php/service/export/export.php';
if ($debug > 9) {
    echo 'class export loaded<br>';
}
include_once $root_path . 'src/main/php/service/export/json.php';
if ($debug > 9) {
    echo 'class json loaded<br>';
}
include_once $root_path . 'src/main/php/service/export/xml.php';
if ($debug > 9) {
    echo 'class xml loaded<br>';
}
// classes
include_once $root_path . 'src/main/php/model/user/user.php';
if ($debug > 9) {
    echo 'class user loaded<br>';
}
include_once $root_path . 'src/main/php/web/user_display.php';
if ($debug > 9) {
    echo 'class user display loaded<br>';
}
include_once $root_path . 'src/main/php/model/user/user_list.php';
if ($debug > 9) {
    echo 'class user list loaded<br>';
}
include_once $root_path . 'src/main/php/model/user/user_log.php';
if ($debug > 9) {
    echo 'class user log loaded<br>';
}
include_once $root_path . 'src/main/php/model/user/user_log_link.php';
if ($debug > 9) {
    echo 'class user log reference loaded<br>';
}
include_once $root_path . 'src/main/php/web/user_log_display.php';
if ($debug > 9) {
    echo 'class user log display loaded<br>';
}
include_once $root_path . 'src/main/php/model/user_sandbox.php';
if ($debug > 9) {
    echo 'class user sandbox loaded<br>';
}
include_once $root_path . 'src/main/php/web/user_sandbox_display.php';
if ($debug > 9) {
    echo 'class user sandbox display loaded<br>';
}
include_once $root_path . 'src/main/php/model/system/system_error_log.php';
if ($debug > 9) {
    echo 'class system error log loaded<br>';
}
include_once $root_path . 'src/main/php/model/system/system_error_log_list.php';
if ($debug > 9) {
    echo 'class system error log list loaded<br>';
}
include_once $root_path . 'src/main/php/web/display_interface.php';
if ($debug > 9) {
    echo 'class display interface loaded<br>';
}
include_once $root_path . 'src/main/php/web/display_html.php';
if ($debug > 9) {
    echo 'class display html loaded<br>';
}
include_once $root_path . 'src/main/php/web/display_button.php';
if ($debug > 9) {
    echo 'class display button loaded<br>';
}
include_once $root_path . 'src/main/php/web/display_selector.php';
if ($debug > 9) {
    echo 'class display selector loaded<br>';
}
include_once $root_path . 'src/main/php/web/display_list.php';
if ($debug > 9) {
    echo 'class display list loaded<br>';
}
include_once $root_path . 'src/main/php/model/helper/word_link_object.php';
if ($debug > 9) {
    echo 'class word link object loaded<br>';
}
include_once $root_path . 'src/main/php/model/word/word.php';
if ($debug > 9) {
    echo 'class word loaded<br>';
}
include_once $root_path . 'src/main/php/web/word_display.php';
if ($debug > 9) {
    echo 'class word display loaded<br>';
}
include_once $root_path . 'src/main/php/model/word/word_list.php';
if ($debug > 9) {
    echo 'class word list loaded<br>';
}
include_once $root_path . 'src/main/php/model/word/word_link.php';
if ($debug > 9) {
    echo 'class word link loaded<br>';
}
include_once $root_path . 'src/main/php/model/word/word_link_list.php';
if ($debug > 9) {
    echo 'class word link list loaded<br>';
}
include_once $root_path . 'src/main/php/model/phrase/phrase.php';
if ($debug > 9) {
    echo 'class phrase loaded<br>';
}
include_once $root_path . 'src/main/php/model/phrase/phrase_list.php';
if ($debug > 9) {
    echo 'class phrase list loaded<br>';
}
include_once $root_path . 'src/main/php/model/phrase/phrase_group.php';
if ($debug > 9) {
    echo 'class phrase group loaded<br>';
}
include_once $root_path . 'src/main/php/model/phrase/phrase_group_list.php';
if ($debug > 9) {
    echo 'class phrase group list loaded<br>';
}
include_once $root_path . 'src/main/php/model/verb/verb.php';
if ($debug > 9) {
    echo 'class verb loaded<br>';
}
include_once $root_path . 'src/main/php/model/verb/verb_list.php';
if ($debug > 9) {
    echo 'class verb list loaded<br>';
}
include_once $root_path . 'src/main/php/model/phrase/term.php';
if ($debug > 9) {
    echo 'class term loaded<br>';
}
include_once $root_path . 'src/main/php/model/value/value.php';
if ($debug > 9) {
    echo 'class value loaded<br>';
}
include_once $root_path . 'src/main/php/model/value/value_list.php';
if ($debug > 9) {
    echo 'class value list loaded<br>';
}
include_once $root_path . 'src/main/php/web/value_list_display.php';
if ($debug > 9) {
    echo 'class value list display loaded<br>';
}
include_once $root_path . 'src/main/php/model/value/value_phrase_link.php';
if ($debug > 9) {
    echo 'class value word link loaded<br>';
}
include_once $root_path . 'src/main/php/model/ref/source.php';
if ($debug > 9) {
    echo 'class source loaded<br>';
}
include_once $root_path . 'src/main/php/model/ref/ref.php';
if ($debug > 9) {
    echo 'class external reference loaded<br>';
}
include_once $root_path . 'src/main/php/model/ref/ref_type.php';
if ($debug > 9) {
    echo 'class external reference types loaded<br>';
}
include_once $root_path . 'src/main/php/model/formula/expression.php';
if ($debug > 9) {
    echo 'class expression loaded<br>';
}
include_once $root_path . 'src/main/php/model/formula/formula.php';
if ($debug > 9) {
    echo 'class formula loaded<br>';
}
include_once $root_path . 'src/main/php/model/formula/formula_list.php';
if ($debug > 9) {
    echo 'class formula list loaded<br>';
}
include_once $root_path . 'src/main/php/model/formula/formula_link.php';
if ($debug > 9) {
    echo 'class formula link loaded<br>';
}
include_once $root_path . 'src/main/php/model/formula/formula_link_list.php';
if ($debug > 9) {
    echo 'class formula link list loaded<br>';
}
include_once $root_path . 'src/main/php/model/formula/formula_value.php';
if ($debug > 9) {
    echo 'class formula value loaded<br>';
}
include_once $root_path . 'src/main/php/model/formula/formula_value_list.php';
if ($debug > 9) {
    echo 'class formula value list loaded<br>';
}
include_once $root_path . 'src/main/php/model/formula/formula_element.php';
if ($debug > 9) {
    echo 'class formula element loaded<br>';
}
include_once $root_path . 'src/main/php/model/formula/formula_element_list.php';
if ($debug > 9) {
    echo 'class formula element list loaded<br>';
}
include_once $root_path . 'src/main/php/model/formula/formula_element_group.php';
if ($debug > 9) {
    echo 'class formula element group loaded<br>';
}
include_once $root_path . 'src/main/php/model/formula/formula_element_group_list.php';
if ($debug > 9) {
    echo 'class formula element group list loaded<br>';
}
include_once $root_path . 'src/main/php/model/formula/figure.php';
if ($debug > 9) {
    echo 'class figure loaded<br>';
}
include_once $root_path . 'src/main/php/model/formula/figure_list.php';
if ($debug > 9) {
    echo 'class figure list loaded<br>';
}
include_once $root_path . 'src/main/php/model/system/batch_job.php';
if ($debug > 9) {
    echo 'class batch job loaded<br>';
}
include_once $root_path . 'src/main/php/model/system/batch_job_list.php';
if ($debug > 9) {
    echo 'class batch job list loaded<br>';
}
include_once $root_path . 'src/main/php/model/view/view.php';
if ($debug > 9) {
    echo 'class view loaded<br>';
}
include_once $root_path . 'src/main/php/web/view_display.php';
if ($debug > 9) {
    echo 'class view display loaded<br>';
}
include_once $root_path . 'src/main/php/model/view/view_component.php';
if ($debug > 9) {
    echo 'class view component loaded<br>';
}
include_once $root_path . 'src/main/php/model/view/view_component_dsp.php';
if ($debug > 9) {
    echo 'class view component display loaded<br>';
}
include_once $root_path . 'src/main/php/model/view/view_component_link.php';
if ($debug > 9) {
    echo 'class view component link loaded<br>';
}

// include all other libraries that are usually needed
include_once $root_path . 'db_link/zu_lib_sql_link.php';
if ($debug > 9) {
    echo 'lib sql link loaded<br>';
}
include_once $root_path . 'src/main/php/service/zu_lib_sql_code_link.php';
if ($debug > 9) {
    echo 'lib sql code link loaded<br>';
}
include_once $root_path . 'src/main/php/service/config.php';
if ($debug > 9) {
    echo 'lib config loaded<br>';
}

// used at the moment, but to be replaced with R-Project call
include_once $root_path . 'src/main/php/service/zu_lib_calc_math.php';
if ($debug > 9) {
    echo 'lib calc math loaded<br>';
}

// libraries that may be useful in the future
/*
include_once $root_path.'lib/test/zu_lib_auth.php';               if ($debug > 9) { echo 'user authentication loaded<br>'; }
include_once $root_path.'lib/test/config.php';             if ($debug > 9) { echo 'configuration loaded<br>'; }
*/

// libraries that can be dismissed, but still used for regression testing (using test.php)
/*
include_once $root_path.'lib/test/zu_lib_word_dsp.php';           if ($debug > 9) { echo 'lib word display loaded<br>'; }
include_once $root_path.'lib/test/zu_lib_sql.php';                if ($debug > 9) { echo 'lib sql loaded<br>'; }
include_once $root_path.'lib/test/zu_lib_link.php';               if ($debug > 9) { echo 'lib link loaded<br>'; }
include_once $root_path.'lib/test/zu_lib_sql_naming.php';         if ($debug > 9) { echo 'lib sql naming loaded<br>'; }
include_once $root_path.'lib/test/zu_lib_value.php';              if ($debug > 9) { echo 'lib value loaded<br>'; }
include_once $root_path.'lib/test/zu_lib_word.php';               if ($debug > 9) { echo 'lib word loaded<br>'; }
include_once $root_path.'lib/test/zu_lib_word_db.php';            if ($debug > 9) { echo 'lib word database link loaded<br>'; }
include_once $root_path.'lib/test/zu_lib_calc.php';               if ($debug > 9) { echo 'lib calc loaded<br>'; }
include_once $root_path.'lib/test/zu_lib_value_db.php';           if ($debug > 9) { echo 'lib value database link loaded<br>'; }
include_once $root_path.'lib/test/zu_lib_value_dsp.php';          if ($debug > 9) { echo 'lib value display loaded<br>'; }
include_once $root_path.'lib/test/zu_lib_user.php';               if ($debug > 9) { echo 'lib user loaded<br>'; }
include_once $root_path.'lib/test/zu_lib_html.php';               if ($debug > 9) { echo 'lib html loaded<br>'; }
*/

/*

Target is to have with version 0.1 a usable version for alpha testing. 
The roadmap for version 0.1 can be found here: https://zukunft.com/mantisbt/roadmap_page.php

The beta test is expected to start with version 0.7

*/

// global code settings
// TODO move the user interface setting to the user page, so that he can define which UI he wants to use
const UI_USE_BOOTSTRAP = 1; // IF FALSE a simple HTML frontend without javascript is used
const UI_MIN_RESPONSE_TIME = 2; // minimal time after that the user user should see an update e.g. during long calculations every 2 sec the user should seen the screen updated

/*
if UI_CAN_CHANGE_... setting is true renaming an object may switch to an object with the new name
if false the user gets an error message that the object with the new name exists already

e.g. if this setting is true
     user 1 creates     "Nestle" with id 1
     and user 2 creates "Nestlé" with id 2
     now the user 1 changes "Nestle" to "Nestlé"
     1. "Nestle" will be deleted, because it is not used any more
     2. "Nestlé" with id 2 will not be excluded any more
     
*/
const UI_CAN_CHANGE_VALUE = TRUE;
const UI_CAN_CHANGE_TIME_SERIES_VALUE = TRUE;
const UI_CAN_CHANGE_VIEW_NAME = TRUE;
const UI_CAN_CHANGE_VIEW_COMPONENT_NAME = TRUE; // dito for view components
const UI_CAN_CHANGE_VIEW_COMPONENT_LINK = TRUE; // dito for view component links
const UI_CAN_CHANGE_WORD_NAME = TRUE; // dito for words
const UI_CAN_CHANGE_WORD_LINK_NAME = TRUE; // dito for phrases
const UI_CAN_CHANGE_FORMULA_NAME = TRUE; // dito for formulas
const UI_CAN_CHANGE_VERB_NAME = TRUE; // dito for verbs
const UI_CAN_CHANGE_SOURCE_NAME = TRUE; // dito for sources

// program configuration names
const CFG_SITE_NAME = 'site_name';             // the name of the pod
const CFG_VERSION_DB = 'version_database';      // the version of the database at the moment to trigger an update script if needed

// data retrieval settings
const SQL_ROW_LIMIT = 20; // default number of rows per page/query if not defined
const SQL_ROW_MAX = 2000; // the max number of rows per query to avoid long response times

const MAX_LOOP = 10000; // maximal number of loops to avoid hanging while loops; used for example for the number of formula elements

// max number of recursive call to avoid endless looping in case of a program error
const MAX_RECURSIVE = 10;

// the standard word displayed to the user if she/he as not yet viewed any other word
const DEFAULT_WORD_ID = 1;
const DEFAULT_WORD_TYPE_ID = 1;
const DEFAULT_DEC_POINT = ".";
const DEFAULT_THOUSAND_SEP = "'";

// some standard settings used as a fallback
// move to code link?
const DEFAULT_VIEW = "dsp_start";

// text conversion const (used to convert word, verbs or formula text to a database reference)
const ZUP_CHAR_WORD = '"';    // or a zukunft verb or a zukunft formula
const ZUP_CHAR_WORDS_START = '[';    //
const ZUP_CHAR_WORDS_END = ']';    //
const ZUP_CHAR_SEPERATOR = ',';    //
const ZUP_CHAR_RANGE = ':';    //
const ZUP_CHAR_TEXT_CONCAT = '&';    //

// to convert word, formula or verbs database reference to word or word list and in a second step to a value or value list
const ZUP_CHAR_WORD_START = '{t';   //
const ZUP_CHAR_WORD_END = '}';    //
const ZUP_CHAR_LINK_START = '{l';   //
const ZUP_CHAR_LINK_END = '}';    //
const ZUP_CHAR_FORMULA_START = '{f';   //
const ZUP_CHAR_FORMULA_END = '}';    //

const ZUC_MAX_CALC_LAYERS = '10000';    // max number of calculation layers

// math calc (probably not needed any more if r-project.org is used)
const ZUP_CHAR_CALC = '=';    //
const ZUP_OPER_ADD = '+';    //
const ZUP_OPER_SUB = '-';    //
const ZUP_OPER_MUL = '*';    //
const ZUP_OPER_DIV = '/';    //

const ZUP_OPER_AND = '&';    //
const ZUP_OPER_OR = '|';    //

// fixed functions
const ZUP_FUNC_IF = 'if';    //
const ZUP_FUNC_SUM = 'sum';    //
const ZUP_FUNC_ISNUM = 'is.numeric';    //

// text conversion const (used to convert word, formula or verbs text to a reference)
const ZUP_CHAR_BRAKET_OPEN = '(';    //
const ZUP_CHAR_BRAKET_CLOSE = ')';    //
const ZUP_CHAR_TXT_FIELD = '"';    // don't look for math symbols in text that is a high quotes


// file links used
//const ZUH_IMG_ADD       = "../images/button_add_small.jpg";
//const ZUH_IMG_EDIT      = "../images/button_edit_small.jpg";
const ZUH_IMG_ADD = "../images/button_add.svg";
const ZUH_IMG_EDIT = ".../images/button_edit.svg";
const ZUH_IMG_DEL = "../images/button_del.svg";
const ZUH_IMG_UNDO = "../images/button_undo.svg";
const ZUH_IMG_FIND = ".../images/button_find.svg";
const ZUH_IMG_UN_FILTER = "../images/button_filter_off.svg";
const ZUH_IMG_BACK = "../images/button_back.svg";
const ZUH_IMG_LOGO = "../images/ZUKUNFT_logo.svg";

const ZUH_IMG_ADD_FA = "fa-plus-square";
const ZUH_IMG_EDIT_FA = "fa-edit";
const ZUH_IMG_DEL_FA = "fa-times-circle";

# list of JSON files that define the base configuration of zukunft.com that is supposed never to be changed
define("PATH_BASE_CONFIG_FILES", $root_path . 'src/main/resources/');
define("BASE_CONFIG_FILES", serialize(array('units.json')));

# list of all static import files for testing the system consistency
define("PATH_TEST_IMPORT_FILES", $root_path . 'src/test/resources/');
define("TEST_IMPORT_FILE_LIST", serialize(array('companies.json',
    'ABB_2013.json',
    'ABB_2017.json',
    'ABB_2019.json',
    'NESN_2019.json',
    'countries.json',
    'real_estate.json',
    'Ultimatum_game.json',
    'COVID-19.json',
    'personal_climate_gas_emissions_timon.json',
    'THOMY_test.json')));

# list of import files for quick win testing
/*
define ("TEST_IMPORT_FILE_LIST_QUICK", serialize (array ('COVID-19.json',
                                                         'countries.json', 
                                                         'real_estate.json', 
                                                         'Ultimatum_game.json')));
define ("TEST_IMPORT_FILE_LIST_QUICK", serialize (array ('ABB_2013.json','work.json')));
*/
define("TEST_IMPORT_FILE_LIST_QUICK", serialize(array('car_costs.json')));

// for internal functions debugging
// each complex function should call this at the beginning with the parameters and with -1 at the end with the result
// called function should use $debug-1
function log_debug($msg_text, $debug_overwrite = null)
{
    global $debug;

    $debug_used = $debug;

    if ($debug_overwrite != null) {
        $debug_used = $debug_overwrite;
    }

    if ($debug_used > 0) {
        echo $msg_text . '.<br>';
        //ob_flush();
        //flush();
    }
}

// for system messages no debug calls to avoid loops
// $msg_text        is a short description that is used to group and limit the number of error messages
// $msg_description is the description or the problem with all details if two errors have the same $msg_text only one is used
// $msg_type_id     is the criticality level e.g. debug, info, warning, error or fatal error
// $function_name   is the function name which has most likely caused the error
// $function_trace  is the complete system trace to get more details
// $usr             is the user id who has probably seen the error message
// return           the text that can be shown to the user in the navigation bar
function log_msg($msg_text, $msg_description, $msg_type_id, $function_name, $function_trace, $usr): string
{

    global $sys_log_msg_lst;
    global $db_con;
    $result = '';

    // fill up fields with default values
    if ($msg_description == '') {
        $msg_description = $msg_text;
    }
    if ($function_trace == '') {
        $function_trace = (new Exception)->getTraceAsString();
    }
    $user_id = SYSTEM_USER_ID; // fallback
    if (isset($usr)) {
        $user_id = $usr->id;
    } elseif (isset($_SESSION['usr_id'])) {
        $user_id = $_SESSION['usr_id'];
    }

    // assuming that the relevant part of the message is at the beginning of the message at least to avoid double entries
    $msg_type_text = $user_id . substr($msg_text, 0, 200);
    if (!in_array($msg_type_text, $sys_log_msg_lst)) {
        $db_con->usr_id = $user_id;
        $sys_log_id = 0;

        $sys_log_msg_lst[] = $msg_type_text;
        $log_level = cl(LOG_LEVEL);
        if ($msg_type_id > $log_level) {
            $db_con->set_type(DB_TYPE_SYS_LOG_FUNCTION);
            $function_id = $db_con->get_id($function_name);
            if ($function_id <= 0) {
                $function_id = $db_con->add_id($function_name);
            }
            $msg_text = str_replace("'", "", $msg_text);
            $msg_description = str_replace("'", "", $msg_description);
            $function_trace = str_replace("'", "", $function_trace);
            $msg_text = sf($msg_text);
            $msg_description = sf($msg_description);
            $function_trace = sf($function_trace);
            $fields = array();
            $values = array();
            $fields[] = "sys_log_type_id";
            $values[] = $msg_type_id;
            $fields[] = "sys_log_function_id";
            $values[] = $function_id;
            $fields[] = "sys_log_text";
            $values[] = $msg_text;
            $fields[] = "sys_log_description";
            $values[] = $msg_description;
            $fields[] = "sys_log_trace";
            $values[] = $function_trace;
            if ($user_id > 0) {
                $fields[] = "user_id";
                $values[] = $user_id;
            }
            $db_con->set_type(DB_TYPE_SYS_LOG);
            $sys_log_id = $db_con->insert($fields, $values, false);
            //$sql_result = mysqli_query($sql) or die('zukunft.com system log failed by query '.$sql.': '.mysqli_error().'. If this happens again, please send this message to errors@zukunft.com.');
            //$sys_log_id = mysqli_insert_id();
        }
        $msg_level = cl(MSG_LEVEL);
        if ($msg_type_id >= $msg_level) {
            echo "Zukunft.com has detected an critical internal error: <br><br>" . $msg_text . " by " . $function_name . ".<br><br>";
            if ($sys_log_id > 0) {
                echo 'You can track the solving of the error with this link: <a href="/http/error_log.php?id=' . $sys_log_id . '">www.zukunft.com/http/error_log.php?id=' . $sys_log_id . '</a><br>';
            }
        } else {
            $dsp = new view_dsp;
            $result .= $dsp->dsp_navbar_simple();
            $result .= $msg_text . " (by " . $function_name . ").<br><br>";
        }
    }
    return $result;
}

function log_info($msg_text, $function_name = '', $msg_description = '', $function_trace = '', $usr = null): string
{
    // todo cache the id at program start to avoid endless loops
    $msg_type_id = sql_code_link(DBL_SYSLOG_INFO, "Info");
    $msg_type_id = 1;
    return log_msg($msg_text, $msg_description = '', $msg_type_id, $function_name, $function_trace, $usr);
}

function log_warning($msg_text, $function_name = '', $msg_description = '', $function_trace = '', $usr = null): string
{
    // todo cache the id at program start to avoid endless loops
    $msg_type_id = sql_code_link(DBL_SYSLOG_WARNING, "Warning");
    $msg_type_id = 2;
    return log_msg($msg_text, $msg_description, $msg_type_id, $function_name, $function_trace, $usr);
}

function log_err($msg_text, $function_name = '', $msg_description = '', $function_trace = '', $usr = null): string
{
    // todo cache the id at program start to avoid endless loops
    $msg_type_id = sql_code_link(DBL_SYSLOG_ERROR, "Error");
    $msg_type_id = 3;
    return log_msg($msg_text, $msg_description, $msg_type_id, $function_name, $function_trace, $usr);
}

function log_fatal($msg_text, $function_name, $msg_description = '', $function_trace = '', $usr = null): string
{
    // todo cache the id at program start to avoid endless loops
    // TODO write first to the most secure system log because if the database connection is lost no writing to the database is possible
    //$msg_type_id = sql_code_link(DBL_SYSLOG_FATAL_ERROR, "FATAL ERROR");
    //$msg_type_id = 4;
    //return log_msg($msg_text, $msg_description, $msg_type_id, $function_name, $function_trace, $usr);
    echo 'FATAL ERROR! ' . $msg_text;
    return 'FATAL ERROR! ' . $msg_text;
}

// should be call from all code that can be accessed by an url
// return null if the db connection fails or the db is not compatible
function prg_start($code_name, $style = ""): sql_db
{
    global $sys_time_start, $sys_script;

    // resume session (based on cookies)
    session_start();

    log_debug($code_name . ' ...');

    $sys_time_start = time();
    $sys_script = $code_name;

    log_debug($code_name . ' ... session_start');

    // link to database
    $db_con = new sql_db;
    $db_con->db_type = SQL_DB_TYPE;
    log_debug($code_name . ' ... db set');
    $db_con->open();
    log_debug($code_name . ' ... database link open');

    // html header
    echo dsp_header("", $style);

    // check the system setup
    $result = db_check($db_con);
    if ($result != '') {
        echo $result;
        $db_con->close();
        $db_con = null;
    }

    // load default records
    //verbs_load;

    return $db_con;
}

function prg_start_api($code_name, $style): sql_db
{
    global $sys_time_start, $sys_script;

    log_debug($code_name . ' ..');

    $sys_time_start = time();
    $sys_script = $code_name;

    // resume session (based on cookies)
    session_start();

    // link to database
    $db_con = new sql_db;
    $db_con->open();
    log_debug($code_name . ' ... database link open');

    return $db_con;
}

function prg_end($db_con)
{
    global $sys_time_start, $sys_time_limit, $sys_script, $sys_log_msg_lst;

    echo dsp_footer();

    // write the execution time to the database if it is long
    $sys_time_end = time();
    if ($sys_time_end > $sys_time_limit) {
        $db_con->usr_id = SYSTEM_USER_ID;
        $db_con->set_type(DB_TYPE_SYS_SCRIPT);
        $sys_script_id = $db_con->get_id($sys_script);
        if ($sys_script_id <= 0) {
            $sys_script_id = $db_con->add_id($sys_script);
        }
        $start_time_sql = date("Y-m-d H:i:s", $sys_time_start);
        //$db_con->insert();
        $sql = "INSERT INTO sys_script_times (sys_script_start, sys_script_id, url) VALUES ('" . date("Y-m-d H:i:s", $sys_time_start) . "'," . $sys_script_id . "," . sf($_SERVER['REQUEST_URI']) . ");";
        $sql_result = $db_con->exe($sql, DBL_SYSLOG_FATAL_ERROR, "zu_end", (new Exception)->getTraceAsString());
    }

    // Free result test
    //mysqli_free_result($result);

    // Closing connection
    $db_con->close();

    // free the global vars
    unset($sys_log_msg_lst);
    unset($sys_script);
    unset($sys_time_limit);
    unset($sys_time_start);

    log_debug(' ... database link closed');
}

// special page closing only for the about page
function prg_end_about($link)
{
    global $sys_time_start, $sys_time_limit, $sys_script, $sys_log_msg_lst;

    echo dsp_footer(true);

    // Closing connection
    zu_sql_close($link);

    // free the global vars
    unset($sys_log_msg_lst);
    unset($sys_script);
    unset($sys_time_limit);
    unset($sys_time_start);

    log_debug(' ... database link closed');
}

// special page closing of api pages
// for the api e.g. the csv export no footer should be shown
function prg_end_api($link)
{
    global $sys_time_start, $sys_time_limit, $sys_script, $sys_log_msg_lst;

    // Closing connection
    zu_sql_close($link);

    // free the global vars
    unset($sys_log_msg_lst);
    unset($sys_script);
    unset($sys_time_limit);
    unset($sys_time_start);

    log_debug(' ... database link closed');
}

/*

display functions

*/

// to display a boolean var
function zu_dsp_bool($bool_var)
{
    if ($bool_var) {
        $result = 'true';
    } else {
        $result = 'false';
    }
    return $result;
}

/*

version control functions

*/


// returns true if the version to check is older than this program version
// used e.g. for import to allow importing of files of an older version without warning
function prg_version_is_newer($prg_version_to_check, $this_version = PRG_VERSION)
{
    $is_newer = false;

    $this_prg_version_parts = explode(".", $this_version);
    $to_check = explode(".", $prg_version_to_check);
    $is_older = false;
    foreach ($this_prg_version_parts as $key => $this_part) {
        if (!$is_newer and !$is_older) {
            if ($this_part < $to_check[$key]) {
                $is_newer = true;
            } else {
                if ($this_part > $to_check[$key]) {
                    $is_older = true;
                }
            }
        }
    }

    return $is_newer;
}

// unit_test for prg_version_is_newer
function prg_version_is_newer_test()
{
    global $exe_start_time;

    $result = zu_dsp_bool(prg_version_is_newer('0.0.1'));
    $target = 'false';
    $exe_start_time = test_show_result('prg_version 0.0.1 is newer than ' . PRG_VERSION, $target, $result, $exe_start_time, TIMEOUT_LIMIT);
    $result = zu_dsp_bool(prg_version_is_newer(PRG_VERSION));
    $target = 'false';
    $exe_start_time = test_show_result('prg_version ' . PRG_VERSION . ' is newer than ' . PRG_VERSION, $target, $result, $exe_start_time, TIMEOUT_LIMIT);
    $result = zu_dsp_bool(prg_version_is_newer(NEXT_VERSION));
    $target = 'true';
    $exe_start_time = test_show_result('prg_version ' . NEXT_VERSION . ' is newer than ' . PRG_VERSION, $target, $result, $exe_start_time, TIMEOUT_LIMIT);
    $result = zu_dsp_bool(prg_version_is_newer('0.1.0', '0.0.9'));
    $target = 'true';
    $exe_start_time = test_show_result('prg_version 0.1.0 is newer than 0.0.9', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
    $result = zu_dsp_bool(prg_version_is_newer('0.2.3', '1.1.1'));
    $target = 'false';
    $exe_start_time = test_show_result('prg_version 0.2.3 is newer than 1.1.1', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
}

/*

string functions

*/

function zu_trim($text)
{
    return trim(preg_replace('!\s+!', ' ', $text));
}

// 
function zu_str_left_of($text, $maker)
{
    $result = "";
    $pos = strpos($text, $maker);
    if ($pos > 0) {
        $result = substr($text, 0, strpos($text, $maker));
    }
    return $result;
}

function zu_str_right_of($text, $maker)
{
    $result = "";
    if ($text === $maker) {
        $result = "";
    } else {
        if (substr($text, strpos($text, $maker), strlen($maker)) === $maker) {
            $result = substr($text, strpos($text, $maker) + strlen($maker));
        }
    }
    return $result;
}

function zu_str_between($text, $maker_start, $maker_end)
{
    log_debug('zu_str_between "' . $text . '", start "' . $maker_start . '" end "' . $maker_end . '"');
    $result = '';
    $result = zu_str_right_of($text, $maker_start);
    log_debug('zu_str_between -> "' . $result . '" is right of "' . $maker_start . '"');
    $result = zu_str_left_of($result, $maker_end);
    log_debug('zu_str_between -> "' . $result . '"');
    return $result;
}

/*

string functions (to be dismissed)

*/

// some small string related functions to shorten code and make the code clearer
function zu_str_left($text, $pos)
{
    return substr($text, 0, $pos);
}

function zu_str_right($text, $pos)
{
    return substr($text, $pos * -1);
}

function zu_str_is_left($text, $maker)
{
    $result = false;
    if (substr($text, 0, strlen($maker)) == $maker) {
        $result = true;
    }
    return $result;
}

function zu_str_compute_diff($from, $to)
{
    $diffValues = array();
    $diffMask = array();

    $dm = array();
    $n1 = count($from);
    $n2 = count($to);

    for ($j = -1; $j < $n2; $j++) $dm[-1][$j] = 0;
    for ($i = -1; $i < $n1; $i++) $dm[$i][-1] = 0;
    for ($i = 0; $i < $n1; $i++) {
        for ($j = 0; $j < $n2; $j++) {
            if ($from[$i] == $to[$j]) {
                $ad = $dm[$i - 1][$j - 1];
                $dm[$i][$j] = $ad + 1;
            } else {
                $a1 = $dm[$i - 1][$j];
                $a2 = $dm[$i][$j - 1];
                $dm[$i][$j] = max($a1, $a2);
            }
        }
    }

    $i = $n1 - 1;
    $j = $n2 - 1;
    while (($i > -1) || ($j > -1)) {
        if ($j > -1) {
            if ($dm[$i][$j - 1] == $dm[$i][$j]) {
                $diffValues[] = $to[$j];
                $diffMask[] = 1;
                $j--;
                continue;
            }
        }
        if ($i > -1) {
            if ($dm[$i - 1][$j] == $dm[$i][$j]) {
                $diffValues[] = $from[$i];
                $diffMask[] = -1;
                $i--;
                continue;
            }
        }
        {
            $diffValues[] = $from[$i];
            $diffMask[] = 0;
            $i--;
            $j--;
        }
    }

    $diffValues = array_reverse($diffValues);
    $diffMask = array_reverse($diffMask);

    return array('values' => $diffValues, 'mask' => $diffMask);
}

function zu_str_diff($original_text, $compare_text)
{
    $diff = zu_str_compute_diff(str_split($original_text), str_split($compare_text));
    $diffval = $diff['values'];
    $diffmask = $diff['mask'];

    $n = count($diffval);
    $pmc = 0;
    $result = '';
    for ($i = 0; $i < $n; $i++) {
        $mc = $diffmask[$i];
        if ($mc != $pmc) {
            switch ($pmc) {
                case -1:
                    $result .= '</del>';
                    break;
                case 1:
                    $result .= '</ins>';
                    break;
            }
            switch ($mc) {
                case -1:
                    $result .= '<del>';
                    break;
                case 1:
                    $result .= '<ins>';
                    break;
            }
        }
        $result .= $diffval[$i];

        $pmc = $mc;
    }
    switch ($pmc) {
        case -1:
            $result .= '</del>';
            break;
        case 1:
            $result .= '</ins>';
            break;
    }

    return $result;
}

/*
function str_diff($text, $compare) {
  $result = '';
  $next = 0;
  for ($pos=0; $pos<strlen($text); $pos++) {
    if ($text[$i] == $compare[$i]) {
      $next = $next + 1;
    } else {  
      $result .= ' at '+ $pos + ': ';      
      $result .= $compare[$i] + ' instead of ' + $text[$i];      
    }
  }  
  return $result;
}
*/

/*

list functions (to be dismissed / replaced by objects)

*/

// get all entries of the list that are not in the second list
function zu_lst_not_in($in_lst, $exclude_lst)
{
    log_debug('zu_lst_not_in(' . implode(",", array_keys($in_lst)) . ',ex' . implode(",", $exclude_lst) . ')');
    $result = array();
    foreach (array_keys($in_lst) as $lst_entry) {
        if (!in_array($lst_entry, $exclude_lst)) {
            $result[$lst_entry] = $in_lst[$lst_entry];
        }
    }
    return $result;
}

// similar to zu_lst_not_in, but looking at the array value not the key
function zu_lst_not_in_no_key($in_lst, $exclude_lst)
{
    log_debug('zu_lst_not_in_no_key(' . implode(",", $in_lst) . 'ex' . implode(",", $exclude_lst) . ')');
    $result = array();
    foreach ($in_lst as $lst_entry) {
        if (!in_array($lst_entry, $exclude_lst)) {
            $result[] = $lst_entry;
        }
    }
    log_debug('zu_lst_not_in_no_key -> (' . implode(",", $result) . ')');
    return $result;
}

// similar to zu_lst_not_in, but excluding only one value (diff to in_array????)
function zu_lst_excluding($in_lst, $exclude_id)
{
    log_debug('zu_lst_excluding(' . implode(",", $in_lst) . 'ex' . $exclude_id . ')');
    $result = array();
    foreach ($in_lst as $lst_entry) {
        if ($lst_entry <> $exclude_id) {
            $result[] = $lst_entry;
        }
    }
    log_debug('zu_lst_excluding -> (' . implode(",", $result) . ')');
    return $result;
}

// get all entries of the list that are not in the second list
function zu_lst_in($in_lst, $only_if_lst)
{
    $result = array();
    foreach (array_keys($in_lst) as $lst_entry) {
        if (in_array($lst_entry, array_keys($only_if_lst))) {
            $result[$lst_entry] = $in_lst[$lst_entry];
        }
    }
    return $result;
}

// get all entries of the list that are not in the second list
function zu_lst_in_ids($in_lst, $only_if_ids)
{
    $result = array();
    foreach (array_keys($in_lst) as $lst_entry) {
        if (in_array($lst_entry, $only_if_ids)) {
            $result[$lst_entry] = $in_lst[$lst_entry];
        }
    }
    return $result;
}

// create an url parameter text out of an id array
function zu_ids_to_url($ids, $par_name)
{
    $result = "";
    foreach (array_keys($ids) as $pos) {
        $nbr = $pos + 1;
        if ($ids[$pos] <> "" or $ids[$pos] === 0) {
            $result .= "&" . $par_name . $nbr . "=" . $ids[$pos];
        }
    }
    return $result;
}

// flattens a complex array; if the list entry is an array the first field is shown
function zu_lst_to_array($complex_lst)
{
    //zu_debug("zu_lst_to_array");
    $result = array();
    foreach ($complex_lst as $lst_entry) {
        if (is_array($lst_entry)) {
            $result[] = $lst_entry[0];
            //zu_debug("zu_lst_to_array -> ".$lst_entry[0]." (first)");
        } else {
            $result[] = $lst_entry;
            //zu_debug("zu_lst_to_array -> ".$lst_entry);
        }
    }
    return $result;
}

// flattens a complex array; if the list entry is an array the first field is shown
function zu_ids_not_empty($old_ids)
{
    // fix wrd_ids if needed
    $result = array();
    foreach ($old_ids as $old_id) {
        if ($old_id > 0) {
            $result[] = $old_id;
        }
    }
    return $result;
}

// flattens a complex array; if the list entry is an array the first field is shown
function zu_ids_not_zero($old_ids)
{
    // fix wrd_ids if needed
    $result = array();
    foreach ($old_ids as $old_id) {
        if ($old_id <> 0) {
            $result[] = $old_id;
        }
    }
    return $result;
}

// gets on id list with all word ids from the value list, that already contain the word ids for each value
// no user id is needed because this is done already in the previous selection
function zu_val_lst_get_wrd_ids($val_lst)
{
    //zu_debug("zu_val_lst_get_wrd_ids");
    $result = array();
    foreach ($val_lst as $val_entry) {
        if (is_array($val_entry->wrd_lst)) {
            $wrd_ids = $val_entry->wrd_lst->ids();
            if (is_array($wrd_ids)) {
                foreach ($wrd_ids as $wrd_id) {
                    $result[] = $wrd_id;
                }
            }
        }
    }

    return $result;
}

// maybe use array_filter ???
function zu_lst_common($id_lst1, $id_lst2)
{
    //zu_debug("zu_lst_common (".implode(",",$id_lst1)."x".implode(",",$id_lst1).")");
    //zu_debug("zu_lst_to_array");
    $result = array();
    if (is_array($id_lst1) and is_array($id_lst2)) {
        foreach ($id_lst1 as $id1) {
            if (in_array($id1, $id_lst2)) {
                $result[] = $id1;
            }
        }
    }

    //zu_debug("zu_lst_common -> (".implode(",",$result).")");
    return $result;
}

// collects from an array in an array a list of common ids
// if this is used for a val_lst_wrd and the sub_array_pos is 1 the common list of word ids is returned
function zu_lst_get_common_ids($val_lst, $sub_array_pos)
{
    log_debug("zu_lst_get_common_ids (" . zu_lst_dsp($val_lst) . ")");
    $result = 0;
    //print_r ($val_lst);
    foreach ($val_lst as $val_entry) {
        if (is_array($val_entry)) {
            $wrd_ids = $val_entry[$sub_array_pos];
            if ($result == 0) {
                $result = $wrd_ids;
            } else {
                $result = zu_lst_common($result, $wrd_ids);
            }
        }
    }

    log_debug("zu_lst_get_common_ids -> (" . implode(",", $result) . ")");
    return $result;
}

// collects from an array in an array a list of all ids similar to zu_lst_get_common_ids
// if this is used for a val_lst_wrd and the sub_array_pos is 1 the common list of word ids is returned
function zu_lst_all_ids($val_lst, $sub_array_pos)
{
    log_debug("zu_lst_all_ids (" . zu_lst_dsp($val_lst) . ",pos" . $sub_array_pos . ")");
    $result = array();
    foreach ($val_lst as $val_entry) {
        if (is_array($val_entry)) {
            $wrd_ids = $val_entry[$sub_array_pos];
            if (empty($result)) {
                $result = $wrd_ids;
            } else {
                foreach ($wrd_ids as $wrd_id) {
                    if (!in_array($wrd_id, $result)) {
                        $result[] = $wrd_id;
                    }
                }
            }
        }
    }

    log_debug("zu_lst_all_ids -> (" . implode(",", $result) . ")");
    return $result;
}

// filter an array with a sub array by the id entries of the subarray
// if the subarray does not have any value of the filter id_lst it is not included in the result
// e.g. for a value list with all related words get only those values that are related to on of the time words given in the id_lst
function zu_lst_id_filter($val_lst, $id_lst, $sub_array_pos)
{
    log_debug("zu_lst_id_filter (" . zu_lst_dsp($val_lst) . ",t" . zu_lst_dsp($id_lst) . ",pos" . $sub_array_pos . ")");
    $result = array();
    foreach (array_keys($val_lst) as $val_key) {
        $val_entry = $val_lst[$val_key];
        if (is_array($val_entry)) {
            $wrd_ids = $val_entry[$sub_array_pos];
            $found = false;
            foreach ($wrd_ids as $wrd_id) {
                if (!$found) {
                    log_debug("zu_lst_id_filter -> test (" . $wrd_id . " in " . zu_lst_dsp($id_lst) . ")");
                    if (array_key_exists($wrd_id, $id_lst)) {
                        $found = true;
                        log_debug("zu_lst_id_filter -> found (" . $wrd_id . " in " . zu_lst_dsp($id_lst) . ")");
                    }
                }
            }
            if ($found) {
                $result[$val_key] = $val_entry;
            }
        }
    }

    log_debug("zu_lst_id_filter -> (" . zu_lst_dsp($result) . ")");
    return $result;
}

// flattens a complex array; if the list entry is an array the first field and the array key is returned
function zu_lst_to_flat_lst($complex_lst)
{
    //zu_debug("zu_lst_to_array");
    $result = array();
    foreach (array_keys($complex_lst) as $lst_key) {
        $lst_entry = $complex_lst[$lst_key];
        if (is_array($lst_entry)) {
            $result[$lst_key] = $lst_entry[0];
            //zu_debug("zu_lst_to_array -> ".$lst_entry[0]." (first)");
        } else {
            $result[$lst_key] = $lst_entry;
            //zu_debug("zu_lst_to_array -> ".$lst_entry);
        }
    }
    return $result;
}

// display a list; if the list is an array the first field is shown
function zu_lst_dsp($lst_to_dsp)
{
    //zu_debug("zu_lst_dsp");
    if (is_array($lst_to_dsp)) {
        $result_array = zu_lst_to_array($lst_to_dsp);
        //zu_debug("zu_lst_dsp -> converted");
        $result = implode(",", $result_array);
    } else {
        $result = $lst_to_dsp;
    }
    return $result;
}

function zu_lst_merge_with_key($lst_1, $lst_2)
{
    $result = array();
    foreach (array_keys($lst_1) as $lst_entry) {
        $result[$lst_entry] = $lst_1[$lst_entry];
    }
    foreach (array_keys($lst_2) as $lst_entry) {
        $result[$lst_entry] = $lst_2[$lst_entry];
    }
    return $result;
}

// best guess formatting of an value for debug lines
function dsp_var($var_to_format): string
{
    $result = '';
    if ($var_to_format != null) {
        if (is_array($var_to_format)) {
            $result = implode(',', $var_to_format);
        } else {
            $result = $var_to_format;
        }
    }
    return $result;
}