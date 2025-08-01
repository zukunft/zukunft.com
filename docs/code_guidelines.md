# Coding guidelines

for coding new features the target process is before committing:
    1. create a unit test for the new feature
    2. code the feature and fix the unit tests and code smells
    3. create and fix the database read, write and integration test for the new feature
    4. commit




 rules for this project (target, but not yet done)

    - be open
    - always sort by priority
    - one place (e.g. git / issue tracker / wiki)
    - not more than 6 information block per page
    - automatic log (who has changed what and when)
    - write business logic and test cases one-to-one

## naming conventions for vars:

backend - main
- wrd (WoRD)               - a word that is used as a subject or object in a resource description framework (RDF / "triple") graph
  and used to retrieve the numeric values
- val (VALue)              - a numeric value that can be used for calculations
- frm (FoRMula)            - a formula in the zukunft.com format,
  which can be either in the usr (USeR) format with real words
  or in the db (DataBase) format with database id references
  or in the math (MATHematical) format, which should contain only numeric values

backend - core
- vrb (VeRB)               - a predicate (mostly just a verb) that defines the type of links two words;
  by default a verb can be used forward and backward e.g. ABB is a company and companies are ABB, ...
  if the reverse name is empty, the verb can only be used the forward way
  if a link should only be used one way for one phrase link, the negative verb is saved
  verbs are also named as triples
- trp (TRiPle)             - a triple/sentence, so a word connected to another word with a verb (triple.php is the related class)
- lnk (LiNK)               - n-to-m connection e.g. between a phrase and a view
- phr (PHRase)             - transformed triple or word in order to use them together as one object
- grp (GrouP)              - a group of terms or triples excluding time terms to reduce the number of groups needed and speed up the system
- trm (TeRM)               - either a word, verb or triple (formula names have always a corresponding phrase)
  (verb X creates term X so if word X wants to be added there already is a term X, therefore blocking the input)
- exp (EXPression)         - a formula text that implies a data selection and lead to a number
- elm (ELeMents)           - a structured reference for terms, verbs or formulas mostly used for formula elements (cancel? replace with term?)
- res (RESult)             - the calculated number of a formula
- fig (FIGure)             - either a value set by the user or a calculated formula result
- src (SouRCe)             - url or description where a value is taken from
- ref (REFerence)          - url with and external unique id to sync data with external systems
- msk (MaSK)               - a view that is shown to the user (dsp - DiSPlay until now)
- cmp (CoMPonent)          - one part of a view so a kind of view component (ex view entry)

backend - admin
- usr (USeR)               - the person who is logged in
- pro (PROfile)            - a group of user rights
- log                      - to save all changes in a user readable format
- sta (STAtus)             - the status of a lod entry e.g. solved
- cng (ChaNGe)             - parts of the change log
- act (ACTion)             - the change log actions
- tbl (TaBLe)              - the change log tables
- fld (FieLD)              - the change log fields of a table

backend - internal
- sbx (SandBoX)            - the user sandbox tables where the adjustments of the users are saved
- shr (SHaRe)              - the sharing settings
- ptc (ProTeCt)            - the protection settings
- lst (LiST)               - an array of objects
- typ (TYPe)               - field name to connect predefined functionality to a core object
- pdi (PreDIcate)          - define the connection type between two core objects (verb is a special form of predicate for triples)
- id (IDentifier)          - internal prime key of a database row
- ids (IDentifierS)        - a simple array of database table IDs (ids_txt is the text / imploded version of the ids array)
- sc (Sql Creator)         - for writing SQL statements
- std (STanDard)           - a value that have not been changed and is public (for results additional "main" is used)
- nrm (NoRMal)             - data that is used by most users
- dsl (DSp cmp Link)       - link of a view component to a view
- sty (STYle)              - the HTML style class used for a view
- uso (User Sbx Object)    - an object (word, value, formula, ...) that uses the user sandbox
  (useless?)
- cl (Code Link)           - a text used to identify one predefined database entry that triggers to use of some program code
- sf (Sql Format)          - to convert a text for the database
- cac (CAChe)              - preload lists

object extensions
- _min (MINimal)           - the minimal object used for the frontend API and only valid for the session user
- _exp (EXPort)            - the export object that does not have any internal database references
- _dsp (DiSPlay)           - to create the HTML code to display the object
- _min_dsp                 - the display object based on the API object instead of the backend object

frontend:
- ui (UserInterface)       - the definition of the user interface, mainly used to display either the JavaScript based single page design, the bootstrap based HTML design, the design based on pure HTML code or a pure text output for testing
- djs (DiSPlay JavaScript) - functions for the vue.js JavaScript user interface implementation
- dbs (DiSPlay BootStrap)  - functions for the bootstrap user interface implementation
- dsp (DiSPlay html)       - functions for the pure html user interface implementation
  a view object or a function that return HTML code that can be displayed
- dtx (DiSPlay TeXt)       - functions for the text interface implementation mainly for debugging
- btn (BuTtoN)             - button
- tbl (TaBLe)              - HTML code for a table
- lan (LANguage)           - the language used for the frontend
- for (FORm)               - the language form of a word e.g. plural

to be deprecated:
- glst (Get LiST)          - is used to name the private internal functions that can also create the user list
- ulst (User LiST)         - an array of objects that should be shown to the user, so like lst, but without the objects exclude by the user
  the list should only be used to display something and never for checking if an item exists
  this is the short for the sbx_lst

## code links

types (like words types or view types) are used to assign coded functionality to some words or views. This implies
that a type always must have a code_id. This code_id is also used for system im- and export.

## function naming

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
- display               - the result and the name of the object e.g. ABB, sales: 46'000
- display_linked        - like display, but with HTML links to the related objects

All objects needs to have the functions dsp_id and name. These two functions should never all any debug functionality, because they can be called from the debug functions

*_test         - the unit test function which should be below each function e.g. the function prg_version_is_older is tested by prg_version_is_older_test

## debug functions

zu_debug   - for interactive debugging (since version 0.0.3 based on a global $debug var, because meanwhile the PhpStorm has a debugger)
zu_msg     - write a message to the system log for later debugging
zu_info    - info message
zu_warning - log a warning message if log level is set to warning
zu_err     - log an error message
zu_fatal   - log an fatal error message and call a database cleanup
zu_start   - open the database and display the header
zu_end     - close the database

## display functions

that all objects should have

name        - the most useful name of the object for the user
dsp_id      - the name including the database id for debugging
zu_dsp_bool -



