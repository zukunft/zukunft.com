zukunft.com 

Calculating with RDF data.

This program should
- allow each user have her/his own OLAP cube
- make efficient community learning easy by connecting all user OLAP cubes point to point
- allow double-sided tree structures within the cubes by using phrases
- use common sense by using opencyc via conceptnet.io
- enable data exchange to wikidata and other interlinking databases

Install
-------

To install this version 0.0.3 use a LAMP server (https://wiki.debian.org/LaMp) and
1) copy all files to the www root path (e.g. /var/www/html/)
2) copy all files of bootstrap 4.1.3 or higher to /var/www/html/lib_external/bootstrap/4.1.3/
3) copy all files of fontawesome to /var/www/html/lib_external/fontawesome/
4) create a user "zukunft_db_root" in PostgreSQL or MySQL and remember the password
5) execute the script "zukunft_structure.sql" in MySQL to create the database zukunft_structure
6) change the password "xxx" in db_link/zu_lib_sql_link.php with the password used in 2)
7) test if the installation is running fine by calling http://yourserver.com/test/test.php

Planned changes
---------------

For versions 0.0.4 these changes are planned
- fix the unit and integration tests

and for versions 0.0.5
- fix the setup script

Coding Guidelines
-----------------

this code follows the principle of Antoine de Saint-Exupéry

"Il semble que la perfection soit atteinte non quand il n'y a plus rien à ajouter,
mais quand il n'y a plus rien à retrancher."

Or in English: "reduce to the max"

The code use for zukunft.com should be as simple as possible and have only a few dependencies and each part as capsuled as possible,
so basically follow the Zen of Python https://www.python.org/dev/peps/pep-0020/
The minimal requirements are a LAMP server (https://wiki.debian.org/LaMp) and an HTML (using some HTML5 features) browser.
If you see anything that does not look simple to you, please request a change on https://github.com/zukunft/zukunft.com or write an email to timon@zukunft.com


General coding principles:
- one point of change (https://en.wikipedia.org/wiki/Don%27t_repeat_yourself)
- capsule: each method should check the consistency of the input parameters at the beginning
- least possible dependencies because a chain is only as strong as its weakest link (https://archive.fosdem.org/2021/schedule/event/dep_as_strong_as_the_weakest_link/)
- best guess: in case of incomplete data best guess assumptions should be used
- automatic error detection and tracking: in case something unexpected happens the code should try to create an internal error message to enable later debugging
- init:        each function first collect the global vars, initiate the result variable and create a debug message if requested
- full unit and integration testing: each function should be tested in test_units.php, test_unit_db.php and test.php
- debug messages are display immediately using echo (always via the function zu_debug)
- top down: the most important functions should be on top of each code file
- all others function usually return html code that is displayed only by one of the 8 main interface scripts

Naming conventions:
-------------------
- wrd (WoRD)               - a word that is used as a subject or object in a resource description framework (RDF / "triple") graph
and used to retrieve the numeric values
- val (VALue)              - a numeric value that can be used for calculations
- frm (FoRMula)            - a formula in the zukunft.com format,
which can be either in the usr (USeR) format with real words
or in the db (DataBase) format with database id references
or in the math (MATHematical) format, which should contain only numeric values

- vrb (VeRB)               - a predicate (mostly just a verb) that defines the type of links two words;
by default a verb can be used forward and backward e.g. ABB is a company and companies are ABB, ...
if the reverse name is empty, the verb can only be used the forward way
if a link should only be used one way for one phrase link, the negative verb is saved
verbs are also named as word_links
- trp (TRiPle)             - a triple/sentence, so a word connected to another word with a verb (word_link.php is the related class)
- lnk (LiNK)               - n-to-m connection e.g. between a phrase and a view
- phr (PHRase)             - transformed triple or word in order to use them together as one object
- grp (GrouP)              - a group of terms or triples excluding time terms to reduce the number of groups needed and speed up the system
- trm (TeRM)               - either a word, verb or triple (formula names have always a corresponding phrase)
(verb X creates term X so if word X wants to be added there already is a term X, therefore blocking the input)
- exp (EXPression)         - a formula text that implies a data selection and lead to a number
- elm (ELeMents)           - a structured reference for terms, verbs or formulas mostly used for formula elements (cancel? replace with term?)
- fv (Formula Value)       - the calculated result of a formula (rename to result? and if use RESult)
- fig (FIGure)             - either a value set by the user or a calculated formula result
- usr (USeR)               - the person who is logged in
- log                      - to save all changes in a user readable format
- src (SouRCe)             - url or description where a value is taken from

- sbx (SandBoX)            - the user sandbox tables where the adjustments of the users are saved
- uso (User Sbx Object)    - an object (word, value, formula, ...) that uses the user sandbox
(useless?)
- id (IDentifier)          - internal prime key of a database row
- ids (IDentifierS)        - an simple array of database table IDs (ids_txt is the text / imploded version of the ids array)
- lst (LiST)               - an array of objects
- glst (Get LiST)          - is used to name the private internal functions that can also create the user list
- ulst (User LiST)         - an array of objects that should be shown to the user, so like lst, but without the objects exclude by the user 
the user list should only be used to display something and never for checking if an item exists
this is the short for for sbx_lst

- dsp (DiSPlay)            - a view/mask that is shown to the user
- ui (UserInterface)       - the definition of the user interface, mainly used to display either the JavaScript based single page design, the bootstrap based HTML design, the design based on pure HTML code or a pure text output for testing
- djs (DiSPlay JavaScript) - functions for the vue.js JavaScript user interface implementation
- dbs (DiSPlay BootStrap)  - functions for the bootstrap user interface implementation
- dsp (DiSPlay html)       - functions for the pure html user interface implementation
a view object or a function that return HTML code that can be displayed
- dtx (DiSPlay TeXt)       - functions for the text interface implementation mainly for debugging
- cmp (CoMPonent)          - one part of a view so a kind of view component (ex view entry)
- dsl (DSp cmp Link)       - link of a view component to a view
- btn (BuTtoN)             - button
- tbl (TaBLe)              - HTML code for a table

- cl (Code Link)           - a text used to identify one predefined database entry that triggers to use of some program code
- sf (Sql Format)          - to convert a text for the database

object extensions
- _min (MINimal)           - the minimal object used for the frontend API and only valid for the session user
- _exp (EXPort)            - the export object that does not have any internal database references
- _dsp (DiSPlay)           - to create the HTML code to display the object
- _min_dsp                 - the display object based on the API object instead of the backend object

database change setup
---------------------

User Sandbox: values, formulas, formula_links, views and view elements are included in the user sandbox, which means, each user can exclude or adjust single entries

to avoid confusion words, formula names, word_links (verbs) and value_phrase_links are excluded from the user sandbox, but a normal user can change the name, which will hopefully not happen often.

for words, formulas and verbs the user can add a specific name in any language

Admin edit: for word_links (verbs), word_types, link_types, formula_types there is only one valid record and only an admin user is allowed to change it, which is also because these tables have a code id

Sources: every user can change it, but there is only one valid row

Saving: there are several methods to save user data
- not user specific data like verbs, which are saved with the standard process
- user specific data like formulas, which are saved base on the user sandbox functions
- user specific data, which change very rarely and has code functionality linked like view types
- not user specific data, which change only with a program update like the view component position type


Fixed server splitting (if not hadoop is used as the backend)
To split the load between to several servers it is suggested to move one word and all it's related values and results to a second server
further splitting can be done by another word to split in hierarchy order
e.g. use company as the first splitter and than ABB, Daimler, ... as the second or CO2 as the second tree
in this case the CO2 balance of ABB will be on the "Company ABB server", but all other CO2 data will be on en "environment server"
the word graph should stay on the main server for consistency reasons

code links
----------

types (like words types or view types) are used to assign coded functionality to some words or views. This implies
that a type always must have a code_id. This code_id is also used for system im- and export.

function naming
---------------

all classes should have these functions:

- load                  - based on given id setting load an existing object; if no object is found, return null
- load_*_types          - load all types once from the database, because types are supposed to change almost never or with a program version change
e.g. the global function load_ref_types load all possible reference type to external databases
- get                   - based on given id setting load an existing object; if not found in database create it
- get_*_type            - get a type object by the id
- get_*_type_by_name    - get a type object by the code id
- get_*_type_by_code_id - get a type object by the code id
- save                  - update all changes in the database; if not found in database create it
- dsp_id                - like name, but with some ids for better debugging
- name                  - to show a useful name of the object to the user e.g. in case of a formula result this includes the phrases
- name_linked           - like name, but with HTML link to the single objects
- display               - the result and the name of the object e.g. ABB, Sales: 46'000
- display_linked        - like display, but with HTML links to the related objects

All objects needs to have the functions dsp_id and name. These two functions should never all any debug functionality, because they can be called from the debug functions

*_test         - the unit test function which should be below each function e.g. the function prg_version_is_older is tested by prg_version_is_older_test



functions of this library
-------------------------

prefix for functions in this library: zu_*

This library contains general functions like debug or string
that could also be taken from another framework

all functions that could potentially go wrong have the parameter debug,
so that the administrator can find out more details about what has gone wrong
a positive debug value means that the user wants to see some debug message


debug functions
---------------

zu_debug   - for interactive debugging (since version 0.0.3 based on a global $debug var, because meanwhile the PhpStorm has a debugger)
zu_msg     - write a message to the system log for later debugging
zu_info    - info message
zu_warning - log a warning message if log level is set to warning
zu_err     - log an error message
zu_fatal   - log an fatal error message and call a database cleanup
zu_start   - open the database and display the header
zu_end     - close the database

display functions - that all objects should have
-----------------

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
- allow personal groups up to 100 persons and to join up 20 named groups
