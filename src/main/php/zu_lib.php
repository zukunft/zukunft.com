<?php

/*use html\phrase\phrase_group as phrase_group_dsp;
use html\phrase\phrase_group as phrase_group_dsp;


    zu_lib.php - the main ZUkunft.com LIBrary
    ----------

    for coding new features the target process is before committing:
    1. create a unit test for the new feature
    2. code the feature and fix the unit tests and code smells
    3. create and fix the database read, write and integration test for the new feature
    4. commit

    but first this needs to be prioritized:

    TODO Release 0.0.3
    TODO Release 0.0.4

    TODO Substeps: create insert, update and delete sql create tests for the main objects (TODO activate db write)
                   include the log in the prepared sql write statement
    TODO combine db_row and std_row for with-log use of update word
    TODO start all tests with setting the var $test_name
    TODO move the read db tests from the write test classes to the read test classes
    TODO create the test objects always based on functions in create_test_objects
    TODO use with-log for insert links e.g. triple
    TODO use with-log for insert values
    TODO Step 26: deprecate the get_old in result
    TODO Substeps: create insert, update and delete sql create tests for the remaining objects
    TODO Step 25: deprecate the get_old in group_list
    TODO Substeps: move all display functions from the backend objects to the frontend
    TODO Step 24: deprecate the get_old in user_list
    TODO Substeps: sort and group the functions in a class e.g. based on the word class sample
    TODO Step 23: deprecate the get_old in value_dsp
    TODO remove backend classes from frontend
    TODO Step 22: deprecate the get_old in value_list
    TODO remove frontend classes from backend
    TODO Step 21: deprecate the get_old in ???
    TODO review the main unit tests
    TODO Step 20: deprecate the get_old in ???
    TODO review the remaining unit tests
    TODO Step 19: deprecate the get_old in ???
    TODO review the main db read tests
    TODO Step 18: deprecate the get_old in ???
    TODO review the remaining db read tests
    TODO Step 17: deprecate the get_old in ???
    TODO review the main db write tests
    TODO Step 16: deprecate the get_old in ???
    TODO review the remaining db write tests
    TODO activate the tests and create a unit and read test if possible

    TODO set the vars of the backend objects to private e.g. to make sure that missing db updates can be detected
    TODO set vars in the frontend object to public and reduce the set and get function because frontend objects never save directly to the database

    TODO api review
    TODO all api messages should be optional with or without header
    TODO move common api code parts to a separate class
    TODO include the field names in the openapi definition check

    TODO validate the word id const used in the program
    TODO make sure that import of a word without type does not overwrite the type defined in the database use e.g. percent as a test case
    TODO code review
    TODO check the order of the class sections
    TODO used different name for each type e.g. view_style_name instead of type_name to be able to log the name of the change

    TODO for user_values allow a source 0=not set or exclude the source_id from the prime index?
    TODO add import fail test to check the error message creation
    TODO on import create a fail message if the import tries to import a word that exists already as a formula (or create a user specific word and rename the formula)
    TODO test if a table with 1, 2, 4, 8, 16, 32 or 64 smallint key is faster and more efficient than a table with one bigger index
    TODO create an use the figure database view
    TODO clean up the phrase_list (and triple_list and word_list) cfg/class and add unit and db read tests for all
    TODO use $t->assert_sql_by_ids for all lists
    TODO use the load_sql object function for all list load sql functions like in group_list
    TODO add a useful and self speaking verb unit test for all verbs
    TODO for some verbs such as "is part of" the triple itself should by de fault not be included in the foaf list
    TODO use phrase get_or_add in test
    TODO add the word type "one level parent" e.g. to suggest that for City the direct children is the default selection
    TODO add properties to verbs so that the same behavior con be used for several verbs
    TODO use the $load_all parameter for all load functions to include excluded rows for admins
    TODO add a unit and db test
    TODO check which arrays cam be converted to a class
    TODO add system parameter to include the log write into the curl sql statements or use separate statements for log
    TODO combine phrase_group_word_links and phrase_group_triple_links to group_phrase_links
    TODO add a simple value format where the json key is used as the phrase name e.g "system config target number of selection entries": 7
    TODO add system and user config parameter that are e.g. 100 views a view is automatically frozen for the user
    TODO add a trigger to the message header to force the frontend update of types, verbs und user configuration if needed
    TODO use words and values also for the system and user config
    TODO create a config get function for the frontend
    TODO cleanup the object vars and use objects instead repeating ids
    TODO remove the old frontend objects based on the api object
    TODO remove the dsp_obj() functions (without api objects where it can be used for unit tests) and base the frontend objects only on the json api message
    TODO add at least one HTML test for each class
    TODO remove all dsp_obj functions from the model classes
    TODO make sure that im-and export and api check all objects fields
    TODO move all test const to the api class or a test class
    TODO check the all used object are loaded with include once
    TODO base the html frontend objects (_dsp) on the api JSON using the set_from_json function
    TODO check that in the API            messages the database id is used for all preloaded types e.g. phrase type
    TODO check that in the im- and export messages the     code id is used for all preloaded types e.g. phrase type
    TODO refactor the web classes (dismiss all _old classes)
    TODO always use a function of the test_new_obj class to create a object for testing
    TODO create unit tests for all display object functions
    TODO remove the set and get functions from the api objects and make them as simple as possible
    TODO move the include_once calls from zu_lib to the classes
    TODO check that the child classes do not repeat the parent functions
    TODO do not base the html frontend objects on the api object because the api object should be as small as possible
    TODO cast api object in model object and dsp object in api object and add the dsp_obj() function to model object
    TODO define all database field names as const
    TODO for reference field names use the destination object
            e.g. for the field name phrase_group_id use phrase_group::FLD_ID
    TODO if a translation is missing offer the user to translate the message
    TODO rename phrase_group to group
    TODO move the time field of phrase groups to the group
    TODO check that all times include the time zone
    TODO unit test: create a unit test for all possible class functions next to review: formula expression
    TODO check that all dummy function that are supposed to be overwritten by the child object create a error if overwrite is missing
    TODO api load: expose all load functions to the api (with security check!)
    TODO fix error in upgrade process for MySQL
    TODO fix syntax suggestions in existing code
    TODO add the view result at least as simple text to the JSON export
    TODO split mathematical constant in Math and constant
    TODO per km in 'one' 'per' 'km'
    TODO split acronym in 'one to one' and 'one to many'
    TODO replace db field 'triple_name' with a virtual field based on name_generated and name_given
    TODO add api unit test (assert_api_to_dsp) to all objects
    TODO add limit and offset to all list sql statements
    TODO align the namespace with PSR-0 as much as possible
    TODO sort the phrases by usage, so that the values with the smallest group id are the most relevant
    TODO so the phrase sort by usage separate for each pod
    TODO for a list a phrase load more values than needed from the backend and filter the values in the frontend
    TODO resort the classes functions so that each section start with the most often used function
    TODO resort the classes by the sections
         const, vars, construct and map, cast, set and get
         load, im- and export, filter, modify, check, save, del

    after that this should be done while keeping step 1. to 4. for each commit:
    TODO check the consistency of the object var default values e.g. if == null is used it must be possible that the var is null
    TODO in api use always field names from the api object
    TODO reduce the api objects as much as possible and move functionality to the cfg object
    TODO review unit, read and write tests
         each test should be with one line e.g. $t->assert_sql_table_create($wrd);
         3 to 7 tests should be within a block starting with $t->subheader(' ....
         sort load functions (done in: view
         group function within a class e.g. by load, save ....
         use $this::class for load functions
    TODO make write tests autonomies (no pre requires, no dependencies, no left overs)
    TODO check if MySQL create script is working
    TODO add unit test for all system views
    TODO convert from null e.g. to empty string at the last possible point e.g. to distinguish between not set
    TODO cleanup set and get functions:
            1. start with set for the core values
            2. group set and get
            3. order the functions by importance
            4. remove unneeded overwrites
    TODO use list of most often used words for the prime word selection
    TODO define a phrase range for global prime phrases (e.g. 5124)
         and a range for pot prime terms
    TODO add a frontend cache e.g. for terms, formulas and view
    TODO allow the user to configure the frontend cache size and show to the user suggestion in increase speed
    TODO add a backend cache e.g. for terms, formulas and view
    TODO allow the admin to configure the backend cache size and show to the user suggestion in increase speed
    TODO add the option to separate the user config to be able to move the user config to a separate database so that each user group can run its own databaser server
    TODO check that all vars that can be empty allow null and replace null e.g. with an empty string as late as possible
    TODO check that all relevant vars are forwarded from the backend object to the api and display object
    TODO add a system execution time measurement that covers e.g. the import of words (values, ...) ber second
    TODO validate the import before staring the import e.g. check if a triple has always from, verb and to
    TODO create a word-list for import where just the names are listed without further indications
    TODO the first frontend should look like Excel wit a big empty sheet and file save and load and only a few suggestions while writing to a cell
    TODO remove the time phrase from result
    TODO rename change and change_log to change
    TODO rename sys_log to log
    TODO log the changes of system tables like the ip blacklist with the SQL based standard log
    TODO ... and switch off the SQL standard log for tables that are using the user friendly log process of this code e.g. the changes tables
    TODO use LLM KI systems like deepSeek to fill up and validate the phrases semi automatically
    TODO fix the button frontend issue
    TODO use the json api message header for all api messages
    TODO check if reading triples should use a view to generate the triple name and the generated name
    TODO use the sandbox list for all user lists
    TODO always sort the phrase list by id before creating the group id
    TODO to force sorting of the phrase for a group use triples
    TODO use in the frontend only the code id of types
    TODO use in the backend always the type object instead of the db type id
    TODO always use the frontend path CONST instead of 'http'
    TODO replace the fixed edit masks with a view call of a mask with a code id
    TODO review cast to display objects to always use display objects
    TODO make all vars of display objects private or protected
    TODO move display functions to frontend objects
    TODO check that all queries are parameterized by setting $db_con->set_name
    TODO add text report table to save a text related to a phrase group (and a timestamp)
    TODO check that all load function have an API and are added in the OpenAPI document
    TODO use the api functions and the html frontend function
    TODO create a vue.js based frontend
    TODO add users to add python based hooks on types and verbs with a zukunft.com object model
    TODO add terraform.io script for deployments
    TODO automatically measure the test coverage (e.g. the number of facade functions that have a positive and negative test)
    TODO capsule (change from public to private or protected) all class vars that have dependencies e.g lst of user_type_list
    TODO split frontend and backend an connect them using api objects
    TODO add a text export format to the display objects and use it for JSON import validation e.g. for the travel list
    TODO add simple value list import example
    TODO add environment variables e.g. for the database connection
    TODO check the add foreign database keys are defined
    TODO check that all fields used in the frontend API are referenced from a controller::FLD const
    TODO check that all fields used for the export are referenced from a export::FLD const
    TODO add a key store for secure saving of the passwords
    TODO add a trust store for the base url certificates to avoid man in the middle attacks
    TODO add simple value list table with the hashed phrase list as key and the value
    TODO add a calculation validation section to the import
    TODO add a text based view validation section to the import
    TODO by default do not use SQL joins also to be able to move the user overwrites to a separate database server
    TODO add a second line of defence e.g. prevent that the web client is using all resources (CPU, memory)
         or that data objects are synced between the pod too often and are blocking more critical data updates
    TODO for the frontend use three level of objects: normal, full and small
         where the full additional contains the share and protection type
         and the small object contains basically e.g. the id and the name
    TODO add a simple UI API JSON to text frontend for the view validation
    TODO exclude any search objects from list objects e.g. remove the phrase from the value list which implies to split the list loading into single functions such as load_by_phr
    TODO use a key-value table without a phrase group if a value is not user specific and none of the default settings has been changed
         for the key-value table without a phrase group encode the key, so that automatically a virtual phrase group can be created
         e.g. convert -12,3,67 to something like 4c48d5685a7e with the possibility to reverse
    TODO create db id sync tables (with this_pod_db_id, foreign_pod, foreign_db_id)
         each pod can create its own database id for words, triple, formulas and users
         if the id request from the master pod takes too long
         or if the word or triple should be a preferred / prime phrase for the pod
    TODO use the PHPUnit test coverage check
    TODO create a value_ps_data table additional to the value_ts_data table for phrase series data
    TODO create a result_ts_data table additional to the value_ts_data table for result series data
    TODO create a result_ps_data table additional to the result_ts_data table for result phrase series data
    TODO create value_ts_data_* tables with a short group id
    TODO move all sample SQL statements from the unit test to separate files for auto syntax check
    TODO check that all sample SQL statements are checked for the unique name and for mysql syntax
    TODO cleanup the objects and remove all vars not needed any more e.g. id arrays
    TODO if a functions failure needs some user action a string the the suggested action is returned e.g. save() and add()
    TODO if a function failure needs only admin or dev action an exception is raised and the function returns true or false
    TODO if an internal failure is expected not to be fixable without user interaction, the user should ge a failure link for the follow up actions
    TODO review the handling of excluded: suggestion for single object allow the loading of excluded, but for lists do not include it in the list
    TODO capsule in classes
    TODO create unit tests for all relevant functions
    TODO allow the triple name to be the same as the word name e.g. to define tha Pi and π are math const e.g implement the phrase type hidden_triple
    TODO order the phrase types by behaviors
    TODO create a least only test case for each phrase type
    TODO create a behavior table to assign several behaviors to one type
    TODO complete rename word_type to phrase_type
    TODO cleanup object by removing duplicates
    TODO call include only if needed
    TODO allow to link views, components and formulas to define a successor
    TODO for phrases define the successor via special verbs
    TODO use the git concept of merge and rebase for group changes e.g. if some formulas are assigned to a group these formulas can be used by all members of a group
    TODO additional to the git concept of merge allow also subscribe or auto merge
    TODO create a simple value table with the compressed phrase ids as a key and the value as a key-value table
    TODO check that all class function follow the setup suggested in user_message
    TODO move all tests to a class that is extended step by step e.g. test_unit extends test_base, ...
    TODO make sure that no word, phrase, verb and formula have the same name by using a name view table for each user
    TODO add JSON tests that check if a just imported JSON file can be exactly recreated with export
    TODO if a formula is supposed to be created with the same name of a phrase suggest to add (formula) at the end
    TODO create a test case where one user has created a word and another user has created a formula with the same name
    TODO all save and import functions should return an empty string, if everything is fine and otherwise the error message that should be shown to the user
    TODO in load_standard the user id of db_con does not need to be set -> remove it
    TODO create json config files for the default and system views
    TODO add JSON im- and export port for verbs
    TODO remove the database fields from the objects, that are already part of a linked object e.g. use ref->phr->id instead of ref->phr_id
    TODO allow to load user via im- and export, but make sure that no one can get higher privileges
    TODO replace to id search with object based search e.g. use wrd_lnk->from->id instead of wrd_lnk->from_id
    TODO add im- and export of users and move the system user loading to one json
    TODO create the unit tests for the core elements such as word, value, formula, view
    TODO review types again and capsule (move const to to base object e.g. the word type time to the word object)
    TODO for import offer to use all time phrases e.g. "year of fixation": 1975 for "speed of light"
    TODO create an automatic database split based on a phrase and auto sync overlapping values
    TODO split the database from the memory object to save memory
    TODO add an im- and export code_id that is only unique for each type
    TODO move init data to one class that creates the initial records for all databases and create the documentation for the wiki
    TODO use the type hash tables for words, formulas, view and components
    TODO create all export objects and add all import export unit tests
    TODO complete the database abstraction layer
    TODO create unit tests for all module classes
    TODO name all queries with user data as prepared queries to prevent SQL code injections
    TODO split the load and the load_sql functions to be able to add unit tests for all sql statements
    TODO crawl all public available information from the web and add it as user preset to the database
    TODO rename dsp_text in formula to display
    TODO rename name_linked in element to name_linked
    TODO separate the API JSON from the HTML building e.g. dsp_graph should return an JSON file for the one page JS frontend, which can be converted to HTML code
    TODO use separate db users for the db creation (user zukunft_root), admin (user zukunft_admin), the other user roles and (zukunft_insert und zukunft_select) as s second line of defence
    TODO check all data from an URL or from a user form that it contains no SQL code
    TODO move the init database fillings to on class instead of on SQL statement for each database
    TODO prevent XSS attacks and script attacks
    TODO check the primary index of all user tables
    TODO load the config, that is not expected to be changed during a session once at startup
    TODO start the backend only once and react to REST calls from the frontend
    TODO make use of __DIR__ ?
    TODO create a package size optimizer to detect the optimal number of db rows saved with one commit or the message size for the frontend by running a bigger and smaller size parallel and switch to the better if the result have a high confidence level
    TODO display the Aggregated Mean World Usage (AMWU) with the range and the hist with and without adjustments and display the estimated personal distribution
    TODO check the install of needed packages e.g. to make sure curl_init() works
    TODO create a User Interface API
    TODO offer to use FreeOTP for two factor authentication
    TODO change config files from json to yaml to complete "reduce to the max"
    TODO create a user cache with all the data that the user usually uses for fast reactions
    TODO move the user fields to words with the reserved words with the prefix "system user"
    TODO for the registration mask first preselect the country based on the geolocation and offer to switch language, than select the places based on country and geolocation and the street
    TODO in the user registration mask allow to add places and streets on the fly and show a link to add missing street on open street map
    TODO use the object constructor if useful
    TODO capsule all critical functions in classes for security reason, to make sure that they never call be called without check e.g. database reset
    TODO to speed up create one database statement for each user action if possible
    TODO split the user sandbox object into a user sandbox base object and extend it either for a named or a link object
    TODO remove e.g. the word->type_id field and use word->type->id instead to reduce the number of fields
    TODO try to use interface function and make var private to have a well defined interface
    TODO remove all duplicates in objects like the list of ids and replace it by a creation function; if cache is needed do this in the calling function because this knows when to refresh
    TODO allow admin users to change IP blacklist
    TODO include IP blacklist by default for admin users
    TODO add log_info on all database actions to detect the costly code parts
    TODO move the environment variables to a setting YAML like application.yaml, application-dev.yaml, application-int.yaml or application-prod.yaml in springboot
    TODO create a sanity API for monitor tools like checkMK or platforms like openshift
    TODO create an "always on" thread for the backend
    TODO add a test with the query "inhabitants of Zurich" which should return the number of inhabitants of the city and the canton of Zurich, but not the employies of zurich insurance
    TODO add a user parameter "default search levels" - users that use more specific words and a larger short term memory might want to increase this to more than 1
    TODO create a LaTeX extension for charts and values, so that studies can be recreated based on the LaTeX document
    TODO for fail over in the underlying technologies, create a another backend in python and java  and allow the user to select or auto select the backend technology
    TODO for fail over in the underlying database technologies, auto sync the casandra, hadoop, Postgres and mariaDB databases
    TODO auto create two triple for an OR condition in a value selection; this implies that to select a list of values only AND needs to be used and brackets are also not needed
    TODO add a phrase group to sources and allow to import it with "keys:"
    TODO allow to assign more phrases to a source for better suggestion of sources
    TODO add a request time to each frontend request to check the automatically the response times
    TODO check that all external links from external libraries are removed, so that the cookie disclaimer can be avoided
    TODO reduce the size of the api messages to improve speed
    TODO add a slider for admin to set the balance between speed and memory usage in the backend (with a default balanced setting and a auto optimize function)
    TODO add a slider for the user to set the balance between speed and memory usage in the frontend and display the effect in a chart with speed increase vs memory usage
    TODO add example how a tax at least in the height of the micro market share at the customer would prevent monopoly
    TODO add example why democracy sometimes do wrong decisions e.g. because the feedback loop is too long or to rare
    TODO explain why the target build up user needs to be intelligent, but without targeting power
    TODO add example why nobody should own more than the community is spending to save one persons life
    TODO add example how the car insurance uses the value of one person life to calculate the premium and the health insurance for the starting age for gastro check
    TODO make sure that "sudo apt-get install php-dom" is part of the install process
    TODO before deleting a word make sure that there are not depending triples
    TODO Include in the message the user@pot or usergroup@pot that can read, write and export the data and who is owner
    TODO base all messages to the final user on a language translation (whereas all log messages are for admin only and only in english)
    TODO Export of restricted data is always pgp secured and the header includes the access rights,
    TODO rename phrase_group to group
    TODO rename element to element
    TODO check if handling of negative ids is correct
    TODO check that all modules used are loaded with include_once before the use statement
    TODO create a undo und redo function for a change_log entry
    TODO for behavior that should apply to several types create a property/behavior table with an n:m reration to phrase types e.g. "show preferred as column" for time phrases
    TODO create a user view for contradicting behaviour e.g. if time should be shown in column, but days in rows
    TODO save time series data in datetime/value tables where the table name contains the group id e.g. TS_....0C+....0e+....12+....13+ for inhabitant of Switzerland in Mio
    TODO add a text table for string and prosa that never should be used for selection
    TODO add a date table to save dates in an efficient way
    TODO create a alternative backend based on Rust for better speed
    TODO all pods write change requests first to its own database
    TODO if the local pod is not the master pod for the phrase, the change is transferred to the master pod
    TODO in case of a conflict, that later change wins and because all changes are user specific the probability of a ultimate conflict is nearly zero
    TODO use zeroMQ or Kafka to sync the insert and update statements between the pod
    TODO use separate kafka topics for values and results of each pod e.g. switzerland_values for all updates related to Switzerland
    TODO allow to assign users to an admin and offer each admin to use different settings for "his" users so that different behavior due to setting changes can be tested to the same pod
    TODO use prioritized change streams in the frontend e.g. updates of values have a higher priority than updates of results
    TODO use prioritized change streams to synchronize the databases with out and in cache tables to avoid loss of data due to communication issues
    TODO for prioritized change streams use transfer and process block size parameters e.g. 100 changes are send to another pod and removed from the out cache not before the destination pod has confirmed the writing to the in cache table
    TODO add a table with process steps with step_id, name, description, code_id
    TODO add a table with process_next_step with step_next_id, from_step_id, to_step_id, name, description, user_profile, user_id, job_id
    TODO some index words like can have many items and need to be only valid / unique within a phrase e.g. the ISIN is a phrase within the phrase security identifier (finance)
         create an additional value_index table where the one big and two small int values are the key or
    TODO add a global_id to the word and triple table and reserve the prime ids
         or create a table for the pod prime, index and big_index phrases with the global phrase_id
    TODO save in the local pod setting the value and result tables actually used to speed up value searches
    TODO offer syntactic sugar translation for PL SQL
    TODO reduce the function parameters to 3 or less wherever possible
    TODO use popular Open Source LLM systems to fill the word and triple (and value) tables
    TODO do not allow any HTML or script code fragments in the text fields
    TODO if options are excluded show them in grey with the mouseover reason, why they have been excluded
    TODO ad sample how the use Reuters RIC where the price is in pence
    TODO create a function to earn cooperative parts by work
    TODO creat a pod prime phrase mapping table, so that each pod can have its own prime phrases without losing the connection to other pod
    TODO create an id range for all pod prime phrases e.g. 1 to 16'384 and a range for this pod only prime phrases e.g. 16'385 to 32'768 and reserve an temp id range used during the relocation process
    TODO because MySQL does not keep the interface stable (e.g. https://dev.mysql.com/doc/refman/8.4/en/charset-unicode-utf8.html) switch to postgres and MariaDB
    TODO use th principles of compression for database optimisation e.g. to sort phrases by usage to increase the number of prime value keys
    TODO use a universal type to create the value tables, so instead of prime, main and big use value 1,2 and 3
    TODO allow to add screenshots to the import file to check if the recreated screen matches the given screenshot
    TODO create a tool for rules base confirm of screen result changes
    TODO create a smart-vote for initiatives path where the first question is yes or no and if the answer is yes,
         the next question is based on an argument against it. if the argument is denied the answer if moving more the to yes path
         and another argument against is presented and so on ...
    TODO create a related table with the phrase, the context as a group, the weight and the related phrase
         for fast selection of phrases related to a given phrase within a given context
         this related table should be automatically filled by a batch job based e.g. on the number and usage
    TODO create different related tables for prime and big context and user specific
    TODO move code id const to a code_id.yaml file for better sharing between the different code languages
    TODO add a test case to check missing or invalid code id const e.g. in php, java, JavaScript, python
    TODO check if all backend api calls are actually called from the frontend

    TODO message handling
        in dought return a user message to the highest level, so that it can be shown to the user
        in case of an exception convert it to a user message as soon as all relevant information are available

    TODO use cases:
        create a sample how to create a personal pension plan for 1. 2. and 3. pillar independent from banks and pension funds
        these the optimal tax rates are
            from -10% needed to fulfill the basic needed
            to 99% for everything more than the community is able to invest to save one live
            reason: this is the optimal combination between safety and prestige
        Show the roof top potential solar potential of 120 Gigawatt vs. usage in percent of solar energy in turkey https://de.dsisolar.com/info/t-rkiye-s-rooftop-solar-potential-enough-to-me-89783977.html

    TODO because some changes e.g. a formula change might cause costly calculations estimate the cost upfront and ask the user to pay for it

    TODO term names are expected to change not very often
         that's why each frontend instance should subscribe to term name changes of standard term names
         and user term names, because a user is allowed to have several clients open at the same time
         and each user client should be live synchronised
         this way the front end term cache is always up to date and can be used as a read database cache

    TODO create software patents and allow everybody to use them to prevent other from making money with the patents

    TODO keep in the frontend the phrases that are relevant for the user at the moment
         calculate the the frontend real-time the value with the relevant precision
         calculate in the backend the value with may precision but only if the server is idle
         or the precision might get below the threshold relevant for the user


    TODO add data optimizers for read time, write time and space usage
         e.g. select the queries most often used with the longest exe time by data transferred
              if at least 1000 values share the same owner, share and protection parameters and context
              create a value group and a value group table for these values
              estimate the speed and size saving potential
              create a separate pure key-value data table
              copy the data to the optimized structure
              switch over the read and write queries
              check if the real query time match the estimates
              and adjust the parameters if needed
              if the time or space saving is real remove the old and unused data (fixed reorg)
              set the max number of value group tables per pod to e.g. 900
              check the context overlapping between two pods
              and suggest data transfer if this will reduce traffic

    TODO create a table optimizer that
        1. get the most often used phrase e.g. inhabitants and create a table e.g. phrase_inhabitants
        2. get the most often used parent phrase within the table and use it for the row index of the table e.g. city and rename the table e.g. to phrase_city_inhabitants
        3. get the most often used phrase that match the table an row phrase e.q. 2019, 2020 and create a field for each phrase
        4. add result columns to the table and add formula id to the column name (source phrases?)
        5. add text and date columns to the table
        this is limited by the pod settings for much phrase tables e.g. 3 for testing
        and by the max db columns e.g. 256

    TODO test the table optimizer based on the system configuration
        1. the able optimizer should detect that tha triple "system configuration" creates a value group with clear borders
        2.

    TODO import the system configuration of other pods with the "slave" keyword means
         that the system configuration is read only in this pod
         but can be used e.g. to detect if the messages should use the database id (if the phrases are in sync)
         or if im- and export messages should be used because the database id are not in sync e.g. because teh other pod uses different prime phrases

    TODO When saving (or loading) data do these checks
         is table phrase
            -> if yes: is row and col phrase -> get from table
            -> if no, get from value_standard or value_two or value or value_big table
         when loading data try to read many rows with all col at once
    TODO check mix of value and result (source phrases?)
         add text, date and other data format columns

    TODO create a table startup page with a
         Table with two col and two rows and four last used pages below. If now last used pages show the demo pages.
         Typing words in the top left cell select a word with the default page
         Typing in the top right cell adds one more column and two rows and typing offer to select a word and also adds related row names based on child words
         Typing in the lower left cell also adds one more row and two cols and typing shows related parent words as column headers
         Typing in the lower right cell starts the formula selection and an = is added as first char.
         Typing = in any cell starts the formula selection
         Typing an operator sign after a space starts the formula creation and a formula name is suggested

    TODO add a multi unique key auto merge test case
        add Euro and Swissfranc and an Euro to Swissfrance rate of 1.1
        add EUR and CHF and a EUR/CHF rate of 1.0
        add a Name and ISO code unique Index to currencies
         -> use the later rate


    TODO split the frontend from the backend
         the namespaced should be
         - api: for the frontend to backend api objects that e.g. does not contain data of other users and the access rights
         - html: for the pure html frontend
         - vue: for the vue.js based frontend, which can cache api objects for read only. This implies that the backend has an api to reload single objects
         - db: for the persistence layer

    TODO create a double side multicast stream layer for system config, name change, value and result streams
         - each client subscribes the frontend cache at his client stream pod
         - the client stream pod subscribes the combined cache of all connected clients at the backend stream pods
         - the changes are wighted by impact for priority
         e.g. if word is renamed the database backend pod sends the change to the backend stream pod
              if the change is relevant for any client, the change is forwarded to the client stream pods
              the client stream pod distributes the change to the clients on his list
         maybe later use IP multicast for the distribution

    TODO for all objects (in progress: user)
        1. complete phpDOCS
        2. add type to all function parameter
        3. create unit test for all functions
            a) prefer assert_qp vs assert_sql
            b) prefer assert vs dsp
        4. create db and api tests
        5. update the OpenAPI doc
        6. use parametrized queries
        7. use translated user interface messages
        8. use const
            a) for db fields
        9. remove class and function from debug
       10. capsule object vars
        done:


    the target model object structure is:

    db_object - all database objects with the sql_table_create function
        db_object_seq_id - all database objects that have a unique id
            db_object_seq_id_user - all objects that are user specific
                sandbox - a user sandbox object
                    sandbox_named - user sandbox objects that have a given name
                        sandbox_typed - named sandbox object that have a type and a predefined behavior
                            word - the base object to find values
                            source - a non automatic source for a value
                            formulas - a calculation rule
                            view - to show an object to the user
                            component - an formatting element for the user view e.g. to show a word or number
                    sandbox_Link - user sandbox objects that link two objects
                        sandbox_link_named - user sandbox objects that link two objects
                            triple - link two words with a predicate / verb
                        formula_link - link a formula to a phrase
                        term_view - link a view to a term
                        component_link - to assign a component to a view
                        ref - to link a value to an external source
                    sandbox_value - to save a user specific numbers
                        value - a single number added by the user
                        result - one calculated numeric result
                        value_time_series - a list of very similar numbers added by the user e.g. that only have a different timestamp  (TODO rename to series)
                phrase_group - a sorted list of phrases
                element - the parameters / parts of a formula expression for fast finding of dependencies (not the db normal form to speed up)
                change_log - to log a change done by a user
                    change_named - log of user changes in named objects e.g. word, triple, ...
                    change_link - log of the link changes by a user
                job - to handle processes that takes longer than the user is expected to wait
            phrase_group_link - db index to find a phrase group by the phrase (not the db normal form to speed up)
                phrase_group_word_link - phrase_group_link for a word
                phrase_group_triple_link - phrase_group_link for a triple
            sys_log - log entries by the system to improve the setup and code
            ip_range - to filter requests from the internet
    base_list - a list with pages
        change_log_list - to forward changes to the UI
        sys_log_list - to forward the system log entries to the UI
        job_list - to forward the batch jobs to the UI
        ip_range_list - list of the ip ranges
        sandbox_list - a user specific paged list
            value_list - a list of values
            formula_list - a list of formulas
            element_list - a list of formula elements
            element_group_list - a list of formula element groups
            formula_link_list - a list of formula links
            result_list - a list of results
            figure_list - a list of figures
            view_list - a list of views
            component_list - a list of components
            component_link_list - a list of component_links
            sandbox_list_named - a paged list of named objects
                word_list - a list of words
                triple_list - a list of triples
                phrase_list - a list of phrases
                term_list - a list of terms
    type_object - to assign program code to a single object
        phrase_type - to assign predefined behaviour to a single word (and its children) (TODO combine with phrase type?)
        phrase_type - to assign predefined behaviour to a single word (and its children)
        formula_type - to assign predefined behaviour to formulas
        ref_type - to assign predefined behaviour to reference
        source_type - to assign predefined behaviour to source
        language - to define how the UI should look like
        language_form - to differentiate the word and triple name forms e.g. plural
    type_list - list of type_objects that is only load once a startup in the frontend
        verb - named object not part of the user sandbox because each verb / predicate is expected to have it own behavior; user can only request new verbs
        view_sys_list - list of all view used by the system itself
        phrase_types - list of all word or triple types
        verb_list - list of all verbs
        formula_type_list - a list of all formula types
        element_type_list - list of all formula element types
        formula_link_type_list - list of all formula link types
        view_type_list - list of all view types
        component_type_list - list of all component types
        component_link_type_list - list of all link types how to assign a component to a view
        component_pos_type_list - list of all component_pos_type
        ref_list - list of all refs (TODO use a sandbox_link list?)
        ref_type_list - list of all ref types
        source_type_list - list of all source types
        language_list - list of all UI languages
        language_form_list - list of all language forms
        change_action_list - list of all change types
        change_table_list - list of all db tables that can be changed by the user (including table of past versions)
        change_field_list - list of all fields in table that a user can change (including fields of past versions)
        job_type_list - list of all batch job types
    combine_object - a object that combines two objects
        combine_named - a combine object with a unique name
            phrase - a word or triple
            term - a word, triple, verb or formula
        figure - a value or result

    helpers
        phr_ids - just to avoid mixing a phrase with a triple id
        trm_ids - just to avoid mixing a term with a triple, verb or formula id
        fig_ids - just to avoid mixing a result with a figure id
        expression - to convert the user format of a formula to the internal reference format and backward

    model objects to be reviewed
        phrase_group_list - a list of phrase group that is supposed to be a sandbox_list
        element_group - to combine several formula elements that are depending on each other
        component_type - TODO rename to component_type and move to type_object?
        component_pos_type - TODO use a simple enum?
        ref_link_wikidata - the link to wikidata



    rules for this projects (target, but not yet done)

    - be open
    - always sort by priority
    - one place (e.g. git / issue tracker / wiki)
    - not more than 6 information block per page
    - automatic log (who has changed what and when)
    - write business logic and test cases one-to-one


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

use cfg\component\component_link_type;
use cfg\component\component_type;
use cfg\component\position_type;
use cfg\component\view_style;
use cfg\db\db_check;
use cfg\db\sql_creator;
use cfg\db\sql_db;
use cfg\element\element;
use cfg\element\element_type;
use cfg\formula\formula;
use cfg\formula\formula_link_type;
use cfg\formula\formula_type;
use cfg\helper\config_numbers;
use cfg\helper\type_lists;
use cfg\ref\ref;
use cfg\ref\ref_type;
use cfg\ref\source;
use cfg\ref\source_type;
use cfg\result\result;
use cfg\system\job;
use cfg\system\job_type;
use cfg\language\language;
use cfg\language\language_form;
use cfg\log\change_action;
use cfg\log\change_field;
use cfg\log\change_link;
use cfg\log\change_log;
use cfg\log\change_table;
use cfg\log\change_value;
use cfg\phrase\phrase_types;
use cfg\system\session;
use cfg\system\sys_log;
use cfg\system\sys_log_function;
use cfg\system\sys_log_level;
use cfg\system\sys_log_status;
use cfg\system\sys_log_status_list;
use cfg\system\sys_log_type;
use cfg\system\system_time;
use cfg\system_time_list;
use cfg\system\system_time_type;
use cfg\user\user;
use cfg\user\user_profile;
use cfg\user\user_type;
use cfg\user\user_official_type;
use cfg\user\user_profile_list;
use cfg\value\value;
use cfg\verb\verb;
use cfg\view\view;
use cfg\view\view_link_type;
use cfg\view\view_type;
use cfg\word\triple;
use cfg\word\word;
use html\component\component_exe as component;
use html\html_base;
use html\view\view as view_dsp;
use shared\helper\Translator;
use shared\library;
use shared\types\protection_type;
use shared\types\share_type;
use test\test_cleanup;

// the fixed system user
const SYSTEM_USER_ID = 1; //
const SYSTEM_USER_TEST_ID = 2; //

// parameters for internal testing and debugging
const LIST_MIN_NAMES = 4; // number of object names that should at least be shown
const LIST_MIN_NUM = 20; // number of object ids that should at least be shown
const DEBUG_SHOW_USER = 10; // starting from this debug level the user should be shown in the debug text

// set all path for the program code here at once
const SRC_PATH = ROOT_PATH . 'src' . DIRECTORY_SEPARATOR;
const MAIN_PATH = SRC_PATH . 'main' . DIRECTORY_SEPARATOR;
const PHP_PATH_LIB = MAIN_PATH . 'php' . DIRECTORY_SEPARATOR; // recreation of the PHP_PATH for library use only
const MODEL_PATH = PHP_PATH_LIB . 'cfg' . DIRECTORY_SEPARATOR; // path of the main model objects for db saving, api feed and processing
const DB_LINK_PATH = ROOT_PATH . 'db_link' . DIRECTORY_SEPARATOR;
const DB_PATH = MODEL_PATH . 'db' . DIRECTORY_SEPARATOR;
const UTIL_PATH = PHP_PATH_LIB . 'utils' . DIRECTORY_SEPARATOR;
const SERVICE_PATH = PHP_PATH_LIB . 'service' . DIRECTORY_SEPARATOR;
const MODEL_IMPORT_PATH = MODEL_PATH . 'import' . DIRECTORY_SEPARATOR;
const SERVICE_EXPORT_PATH = SERVICE_PATH . 'export' . DIRECTORY_SEPARATOR;
const EXPORT_PATH = MODEL_PATH . 'export' . DIRECTORY_SEPARATOR;
const SERVICE_MATH_PATH = SERVICE_PATH . 'math' . DIRECTORY_SEPARATOR;
const MODEL_CONST_PATH = MODEL_PATH . 'const' . DIRECTORY_SEPARATOR;
const MODEL_HELPER_PATH = MODEL_PATH . 'helper' . DIRECTORY_SEPARATOR;
const MODEL_SYSTEM_PATH = MODEL_PATH . 'system' . DIRECTORY_SEPARATOR;
const MODEL_LOG_PATH = MODEL_PATH . 'log' . DIRECTORY_SEPARATOR;
const MODEL_DB_PATH = MODEL_PATH . 'db' . DIRECTORY_SEPARATOR;
const MODEL_LANGUAGE_PATH = MODEL_PATH . 'language' . DIRECTORY_SEPARATOR;
const MODEL_USER_PATH = MODEL_PATH . 'user' . DIRECTORY_SEPARATOR;
const MODEL_SANDBOX_PATH = MODEL_PATH . 'sandbox' . DIRECTORY_SEPARATOR;
const MODEL_WORD_PATH = MODEL_PATH . 'word' . DIRECTORY_SEPARATOR;
const MODEL_PHRASE_PATH = MODEL_PATH . 'phrase' . DIRECTORY_SEPARATOR;
const MODEL_GROUP_PATH = MODEL_PATH . 'group' . DIRECTORY_SEPARATOR;
const MODEL_VERB_PATH = MODEL_PATH . 'verb' . DIRECTORY_SEPARATOR;
const MODEL_VALUE_PATH = MODEL_PATH . 'value' . DIRECTORY_SEPARATOR;
const MODEL_REF_PATH = MODEL_PATH . 'ref' . DIRECTORY_SEPARATOR;
const MODEL_ELEMENT_PATH = MODEL_PATH . 'element' . DIRECTORY_SEPARATOR;
const MODEL_FORMULA_PATH = MODEL_PATH . 'formula' . DIRECTORY_SEPARATOR;
const MODEL_RESULT_PATH = MODEL_PATH . 'result' . DIRECTORY_SEPARATOR;
const MODEL_VIEW_PATH = MODEL_PATH . 'view' . DIRECTORY_SEPARATOR;
const MODEL_COMPONENT_PATH = MODEL_PATH . 'component' . DIRECTORY_SEPARATOR;
const MODEL_SHEET_PATH = MODEL_COMPONENT_PATH . 'sheet' . DIRECTORY_SEPARATOR;

const SHARED_PATH = PHP_PATH_LIB . 'shared' . DIRECTORY_SEPARATOR;
const SHARED_CALC_PATH = SHARED_PATH . 'calc' . DIRECTORY_SEPARATOR;
const SHARED_CONST_PATH = SHARED_PATH . 'const' . DIRECTORY_SEPARATOR;
const SHARED_ENUM_PATH = SHARED_PATH . 'enum' . DIRECTORY_SEPARATOR;
const SHARED_HELPER_PATH = SHARED_PATH . 'helper' . DIRECTORY_SEPARATOR;
const SHARED_TYPES_PATH = SHARED_PATH . 'types' . DIRECTORY_SEPARATOR;

const API_PATH = ROOT_PATH . 'api' . DIRECTORY_SEPARATOR; // path of the api objects for the message creation to the frontend

const API_OBJECT_PATH = PHP_PATH_LIB . 'api' . DIRECTORY_SEPARATOR; // path of the api objects for the message creation to the frontend
const API_SANDBOX_PATH = API_OBJECT_PATH . 'sandbox' . DIRECTORY_SEPARATOR;
const API_SYSTEM_PATH = API_OBJECT_PATH . 'system' . DIRECTORY_SEPARATOR;
const API_USER_PATH = API_OBJECT_PATH . 'user' . DIRECTORY_SEPARATOR;
const API_LOG_PATH = API_OBJECT_PATH . 'log' . DIRECTORY_SEPARATOR;
const API_LANGUAGE_PATH = API_OBJECT_PATH . 'language' . DIRECTORY_SEPARATOR;
const API_WORD_PATH = API_OBJECT_PATH . 'word' . DIRECTORY_SEPARATOR;
const API_PHRASE_PATH = API_OBJECT_PATH . 'phrase' . DIRECTORY_SEPARATOR;
const API_VERB_PATH = API_OBJECT_PATH . 'verb' . DIRECTORY_SEPARATOR;
const API_VALUE_PATH = API_OBJECT_PATH . 'value' . DIRECTORY_SEPARATOR;
const API_FORMULA_PATH = API_OBJECT_PATH . 'formula' . DIRECTORY_SEPARATOR;
const API_RESULT_PATH = API_OBJECT_PATH . 'result' . DIRECTORY_SEPARATOR;
const API_VIEW_PATH = API_OBJECT_PATH . 'view' . DIRECTORY_SEPARATOR;
const API_COMPONENT_PATH = API_OBJECT_PATH . 'component' . DIRECTORY_SEPARATOR;
const API_REF_PATH = API_OBJECT_PATH . 'ref' . DIRECTORY_SEPARATOR;
const WEB_PATH = PHP_PATH_LIB . 'web' . DIRECTORY_SEPARATOR; // path of the pure html frontend objects
const WEB_ELEMENT_PATH = WEB_PATH . 'element' . DIRECTORY_SEPARATOR;
const WEB_LOG_PATH = WEB_PATH . 'log' . DIRECTORY_SEPARATOR;
const WEB_USER_PATH = WEB_PATH . 'user' . DIRECTORY_SEPARATOR;
const WEB_SYSTEM_PATH = WEB_PATH . 'system' . DIRECTORY_SEPARATOR;
const WEB_HELPER_PATH = WEB_PATH . 'helper' . DIRECTORY_SEPARATOR;
const WEB_TYPES_PATH = WEB_PATH . 'types' . DIRECTORY_SEPARATOR;
const WEB_SANDBOX_PATH = WEB_PATH . 'sandbox' . DIRECTORY_SEPARATOR;
const WEB_HTML_PATH = WEB_PATH . 'html' . DIRECTORY_SEPARATOR;
const WEB_HIST_PATH = WEB_PATH . 'hist' . DIRECTORY_SEPARATOR;
const WEB_WORD_PATH = WEB_PATH . 'word' . DIRECTORY_SEPARATOR;
const WEB_PHRASE_PATH = WEB_PATH . 'phrase' . DIRECTORY_SEPARATOR;
const WEB_GROUP_PATH = WEB_PATH . 'group' . DIRECTORY_SEPARATOR;
const WEB_VERB_PATH = WEB_PATH . 'verb' . DIRECTORY_SEPARATOR;
const WEB_VALUE_PATH = WEB_PATH . 'value' . DIRECTORY_SEPARATOR;
const WEB_FORMULA_PATH = WEB_PATH . 'formula' . DIRECTORY_SEPARATOR;
const WEB_RESULT_PATH = WEB_PATH . 'result' . DIRECTORY_SEPARATOR;
const WEB_FIGURE_PATH = WEB_PATH . 'figure' . DIRECTORY_SEPARATOR;
const WEB_VIEW_PATH = WEB_PATH . 'view' . DIRECTORY_SEPARATOR;
const WEB_COMPONENT_PATH = WEB_PATH . 'component' . DIRECTORY_SEPARATOR;
const WEB_FORM_PATH = WEB_COMPONENT_PATH . 'form' . DIRECTORY_SEPARATOR;
const WEB_SHEET_PATH = WEB_COMPONENT_PATH . 'sheet' . DIRECTORY_SEPARATOR;
const WEB_REF_PATH = WEB_PATH . 'ref' . DIRECTORY_SEPARATOR;

// resource paths
const RES_PATH = MAIN_PATH . 'resources' . DIRECTORY_SEPARATOR;
const IMAGE_RES_PATH = RES_PATH . 'images' . DIRECTORY_SEPARATOR;
const DB_RES_SUB_PATH = 'db' . DIRECTORY_SEPARATOR;
const DB_SETUP_SUB_PATH = 'setup' . DIRECTORY_SEPARATOR;

// resource paths used for testing to avoid local paths in the test resources
const REL_ROOT_PATH = DIRECTORY_SEPARATOR;
const REL_SRC_PATH = REL_ROOT_PATH . 'src' . DIRECTORY_SEPARATOR;
const REL_MAIN_PATH = REL_SRC_PATH . 'main' . DIRECTORY_SEPARATOR;
const REL_RES_PATH = REL_MAIN_PATH . 'resources' . DIRECTORY_SEPARATOR;
const REL_IMAGE_PATH = REL_RES_PATH . 'images' . DIRECTORY_SEPARATOR;

// test path for the initial load of the test files
const TEST_PATH = SRC_PATH . 'test' . DIRECTORY_SEPARATOR;
// the test code path
const TEST_PHP_PATH = TEST_PATH . 'php' . DIRECTORY_SEPARATOR;
// the test const path
const TEST_CONST_PATH = TEST_PHP_PATH . 'const' . DIRECTORY_SEPARATOR;


const DB_SETUP_SQL_FILE = 'zukunft_structure.sql';

// the main global vars to shorten the code by avoiding them in many function calls as parameter
global $db_com; // the database connection
global $usr;    // the session user
global $debug;  // the debug level

// global vars for system control
global $sys_script;      // name php script that has been call this library
global $sys_trace;       // names of the php functions
global $sys_time_start;  // to measure the execution time
global $sys_time_limit;  // to write too long execution times to the log to improve the code
global $sys_log_msg_lst; // to avoid repeating the same message

$sys_script = "";
$sys_trace = "";
$sys_time_start = time();
$sys_time_limit = time() + 2;
$sys_log_msg_lst = array();

// check php version
$version = explode('.', PHP_VERSION);
if ($version[0] < 8) {
    if ($version[1] < 1) {
        echo 'at least php version 8.1 is needed';
    }
}
// TODO check if "sudo apt-get install php-curl" is done for testing
//phpinfo();

// database links
include_once DB_PATH . 'sql_db.php';
include_once DB_PATH . 'db_check.php';

include_once WEB_HTML_PATH . 'html_base.php';

// include all other libraries that are usually needed
include_once DB_LINK_PATH . 'zu_lib_sql_link.php';
include_once SERVICE_PATH . 'db_code_link.php';
include_once SERVICE_PATH . 'config.php';

// to avoid circle include
include_once MODEL_VALUE_PATH . 'value.php';
include_once MODEL_LOG_PATH . 'change_link.php';

// preloaded lists
include_once MODEL_HELPER_PATH . 'type_list.php';
include_once MODEL_HELPER_PATH . 'type_lists.php';
include_once MODEL_SYSTEM_PATH . 'BasicEnum.php';
include_once MODEL_SYSTEM_PATH . 'sys_log_level.php';
include_once MODEL_SYSTEM_PATH . 'sys_log_status_list.php';
include_once MODEL_SYSTEM_PATH . 'system_time_list.php';
include_once MODEL_SYSTEM_PATH . 'system_time_type.php';
include_once MODEL_USER_PATH . 'user_list.php';
include_once MODEL_USER_PATH . 'user_profile_list.php';
include_once MODEL_PHRASE_PATH . 'phrase_types.php';
include_once MODEL_ELEMENT_PATH . 'element_type_list.php';
include_once MODEL_FORMULA_PATH . 'formula_type_list.php';
include_once MODEL_FORMULA_PATH . 'formula_link_type_list.php';
include_once MODEL_VIEW_PATH . 'view_type.php';
include_once MODEL_VIEW_PATH . 'view_type_list.php';
include_once MODEL_COMPONENT_PATH . 'component_type_list.php';
include_once MODEL_COMPONENT_PATH . 'position_type_list.php';
include_once MODEL_REF_PATH . 'ref_type_list.php';
include_once MODEL_REF_PATH . 'source_type_list.php';
include_once MODEL_SANDBOX_PATH . 'share_type_list.php';
include_once MODEL_SANDBOX_PATH . 'protection_type_list.php';
include_once MODEL_LANGUAGE_PATH . 'language_list.php';
include_once MODEL_LANGUAGE_PATH . 'language_form_list.php';
include_once MODEL_SYSTEM_PATH . 'job_type_list.php';
include_once MODEL_LOG_PATH . 'change_action.php';
include_once MODEL_LOG_PATH . 'change_action_list.php';
include_once MODEL_LOG_PATH . 'change_table.php';
include_once MODEL_LOG_PATH . 'change_table_list.php';
include_once MODEL_LOG_PATH . 'change_field.php';
include_once MODEL_LOG_PATH . 'change_field_list.php';
include_once MODEL_VERB_PATH . 'verb_list.php';
include_once MODEL_VIEW_PATH . 'view_sys_list.php';


// used at the moment, but to be replaced with R-Project call
include_once SERVICE_MATH_PATH . 'calc_internal.php';

// settings
include_once PHP_PATH_LIB . 'application.php';

// potentially to be loaded by composer
//include_once $path_php . 'utils/json-diff/JsonDiff.php';
//include_once $path_php . 'utils/json-diff/JsonPatch.php';
//include_once $path_php . 'utils/json-diff/JsonPointer.php';

// libraries that may be useful in the future
/*
include_once $root_path.'lib/test/zu_lib_auth.php';               if ($debug > 9) { echo 'user authentication loaded<br>'; }
include_once $root_path.'lib/test/config.php';             if ($debug > 9) { echo 'configuration loaded<br>'; }
*/

/*

Target is to have with version 0.1 a usable version for alpha testing. 
The roadmap for version 0.1 can be found here: https://zukunft.com/mantisbt/roadmap_page.php

The beta test is expected to start with version 0.7

*/

/*
if UI_CAN_CHANGE_... setting is true renaming an object may switch to an object with the new name
if false the user gets an error message that the object with the new name exists already

e.g. if this setting is true
     user 1 creates     "Nestle" with id 1
     and user 2 creates "Nestlé" with id 2
     now the user 1 changes "Nestle" to "Nestlé"
     1. "Nestle" will be deleted, because it is not used any more
     2. "Nestlé" with id 2 will not be excluded anymore
     
*/
const UI_CAN_CHANGE_VALUE = TRUE;
const UI_CAN_CHANGE_TIME_SERIES_VALUE = TRUE;
const UI_CAN_CHANGE_VIEW_NAME = TRUE;
const UI_CAN_CHANGE_VIEW_COMPONENT_NAME = TRUE; // dito for view components
const UI_CAN_CHANGE_VIEW_COMPONENT_LINK = TRUE; // dito for view component links
const UI_CAN_CHANGE_WORD_NAME = TRUE; // dito for words
const UI_CAN_CHANGE_triple_NAME = TRUE; // dito for phrases
const UI_CAN_CHANGE_FORMULA_NAME = TRUE; // dito for formulas
const UI_CAN_CHANGE_VERB_NAME = TRUE; // dito for verbs
const UI_CAN_CHANGE_SOURCE_NAME = TRUE; // dito for sources

// data retrieval settings

// the possible SQL DB names (must be the same as in sql_db)
const POSTGRES = "Postgres";
const MYSQL = "MySQL";
const SQL_DB_TYPE = POSTGRES;
// const SQL_DB_TYPE = sql_db::MYSQL;

const MAX_LOOP = 10000; // maximal number of loops to avoid hanging while loops; used for example for the number of formula elements

// max number of recursive call to avoid endless looping in case of a program error
const MAX_RECURSIVE = 10;

// the standard word displayed to the user if she/he as not yet viewed any other word
const DEFAULT_WORD_ID = 1;
const DEFAULT_WORD_TYPE_ID = 1;
const DEFAULT_DEC_POINT = ".";
const DEFAULT_THOUSAND_SEP = "'";
const DEFAULT_PERCENT_DECIMALS = 2;

const ZUC_MAX_CALC_LAYERS = '10000';    // max number of calculation layers

// classes that use a standard sql sequence for the database id
const SQL_STD_CLASSES = [
    sys_log_status_list::class,
    sys_log_function::class
];

// type classes that have a csv file for the initial load
const BASE_CODE_LINK_FILES = [
    sys_log_status::class,
    sys_log_type::class,
    job_type::class,
    change_action::class,
    change_table::class,
    change_field::class,
    element_type::class,
    formula_link_type::class,
    formula_type::class,
    language::class,
    language_form::class,
    protection_type::class,
    ref_type::class,
    share_type::class,
    source_type::class,
    system_time_type::class,
    user_official_type::class,
    user_profile::class,
    user_type::class,
    position_type::class,
    component_link_type::class,
    component_type::class,
    view_link_type::class,
    view_type::class,
    view_style::class,
    phrase_types::class
];

// list of classes that use a database table but where the changes do not need to be logged
const CLASSES_NO_CHANGE_LOG = [
    sys_log_status::class,
    sys_log_function::class,
    sys_log_type::class,
    system_time_type::class,
    system_time::class,
    change_action::class,
    change_table::class,
    change_field::class,
    change_link::class,
    change_value::class,
    'change*',
    session::class,
    job::class,
    element::class,
    'phrase*',
    'user_phrase*',
    'prime_phrase*',
    'user_prime_phrase*',
    'term*',
    'user_term*',
    'prime_term*',
    'user_prime_term*',
    'result*',
    'user_result*',
];

// list of classes that are used in the api e.g. to receive the user changes
// TODO Prio 2 move to const/def class?
const API_CLASSES = [
    word::class,
    verb::class,
    triple::class,
    source::class,
    ref::class,
    value::class,
    formula::class,
    result::class,
    view::class,
    component::class
];

const CLASS_WITH_USER_CODE_LINK_CSV = [
    user_profile::class,
    user_type::class
];
// list of all sequences used in the database
// TODO base the list on the class list const and a sequence name function
const DB_SEQ_LIST = [
    'sys_log_status_sys_log_status_id_seq',
    'sys_log_sys_log_id_seq',
    'elements_element_id_seq',
    'element_types_element_type_id_seq',
    'formula_links_formula_link_id_seq',
    'formulas_formula_id_seq',
    'formula_types_formula_type_id_seq',
    'component_links_component_link_id_seq',
    'component_link_types_component_link_type_id_seq',
    'components_component_id_seq',
    'component_types_component_type_id_seq',
    'views_view_id_seq',
    'view_types_view_type_id_seq',
    'verbs_verb_id_seq',
    'triples_triple_id_seq',
    'words_word_id_seq',
    'phrase_types_phrase_type_id_seq',
    'sources_source_id_seq',
    'source_types_source_type_id_seq',
    'refs_ref_id_seq',
    'ref_types_ref_type_id_seq',
    'change_links_change_link_id_seq',
    'changes_change_id_seq',
    'change_actions_change_action_id_seq',
    'change_fields_change_field_id_seq',
    'change_tables_change_table_id_seq',
    'config_config_id_seq',
    'job_types_job_type_id_seq',
    'jobs_job_id_seq',
    'sys_log_status_sys_log_status_id_seq',
    'sys_log_functions_sys_log_function_id_seq',
    'share_types_share_type_id_seq',
    'protection_types_protection_type_id_seq',
    'users_user_id_seq',
    'user_profiles_user_profile_id_seq'
];
const DB_TABLE_LIST = [
    'config',
    'sys_log_types',
    'sys_log',
    'sys_log_status',
    'sys_log_functions',
    'system_times',
    'system_time_types',
    'job_times',
    'jobs',
    'job_types',
    'user_official_types',
    'ip_ranges',
    'sessions',
    'changes',
    'changes_norm',
    'changes_big',
    'change_values_norm',
    'change_values_prime',
    'change_values_big',
    'change_values_time_norm',
    'change_values_time_prime',
    'change_values_time_big',
    'change_values_text_prime',
    'change_values_text_norm',
    'change_values_text_big',
    'change_values_geo_norm',
    'change_values_geo_prime',
    'change_values_geo_big',
    'change_fields',
    'change_links',
    'change_actions',
    'change_tables',
    'protection_types',
    'share_types',
    'language_forms',
    'user_words',
    'words',
    'user_triples',
    'phrase_tables',
    'pods',
    'pod_types',
    'pod_status',
    'triples',
    'phrase_types',
    'verbs',
    'phrase_table_status',
    'groups',
    'user_groups',
    'groups_prime',
    'user_groups_prime',
    'groups_big',
    'user_groups_big',
    'user_sources',
    'user_refs',
    'refs',
    'ref_types',
    'values_standard_prime',
    'values_standard',
    'values',
    'user_values',
    'values_prime',
    'user_values_prime',
    'values_big',
    'user_values_big',
    'values_text_standard_prime',
    'values_text_standard',
    'values_text',
    'user_values_text',
    'values_text_prime',
    'user_values_text_prime',
    'values_text_big',
    'user_values_text_big',
    'values_time_standard_prime',
    'values_time_standard',
    'values_time',
    'user_values_time',
    'values_time_prime',
    'user_values_time_prime',
    'values_time_big',
    'user_values_time_big',
    'values_geo_standard_prime',
    'values_geo_standard',
    'values_geo',
    'user_values_geo',
    'values_geo_prime',
    'user_values_geo_prime',
    'values_geo_big',
    'user_values_geo_big',
    'sources',
    'source_types',
    'user_values_time_series',
    'value_time_series_prime',
    'user_value_time_series_prime',
    'value_ts_data',
    'values_time_series',
    'elements',
    'element_types',
    'user_formulas',
    'user_formula_links',
    'formula_link_types',
    'formula_links',
    'results_standard_prime',
    'results_standard_main',
    'results_standard',
    'results',
    'user_results',
    'results_prime',
    'user_results_prime',
    'results_main',
    'user_results_main',
    'results_big',
    'user_results_big',
    'results_text_standard_prime',
    'results_text_standard_main',
    'results_text_standard',
    'results_text',
    'user_results_text',
    'results_text_prime',
    'user_results_text_prime',
    'results_text_main',
    'user_results_text_main',
    'results_text_big',
    'user_results_text_big',
    'results_time_standard_prime',
    'results_time_standard_main',
    'results_time_standard',
    'results_time',
    'user_results_time',
    'results_time_prime',
    'user_results_time_prime',
    'results_time_main',
    'user_results_time_main',
    'results_time_big',
    'user_results_time_big',
    'results_geo_standard_prime',
    'results_geo_standard_main',
    'results_geo_standard',
    'results_geo',
    'user_results_geo',
    'results_geo_prime',
    'user_results_geo_prime',
    'results_geo_main',
    'user_results_geo_main',
    'results_geo_big',
    'user_results_geo_big',
    'user_views',
    'languages',
    'component_link_types',
    'user_components',
    'user_component_links',
    'component_links',
    'position_types',
    'components',
    'formulas',
    'formula_types',
    'views',
    'users',
    'user_types',
    'user_profiles',
    'view_types',
    'view_styles',
    'component_types',
    'view_link_types',
    'term_views',
    'user_term_views',
    'value_formula_links',
    'value_time_series',
    'user_value_time_series',
    'values_time_series_prime',
    'user_values_time_series_prime',
    'values_time_series_big',
    'user_values_time_series_big',
    'results_time_series',
    'user_results_time_series',
    'results_time_series_prime',
    'user_results_time_series_prime',
    'results_time_series_big',
    'user_results_time_series_big'
];


/**
 * for internal functions debugging
 * each complex function should call this at the beginning with the parameters and with -1 at the end with the result
 * called function should use $debug-1
 * TODO focus debug on time consuming function calls e.g. all database accesses
 *
 * @param string $msg_text debug information additional to the class and function
 * @param int|null $debug_overwrite used to force the output
 * @return string the final output text
 */
function log_debug(string $msg_text = '', int $debug_overwrite = null): string
{
    global $debug;

    if ($debug_overwrite == null) {
        $debug_used = $debug;
    } else {
        $debug_used = $debug_overwrite;
    }

    // add the standard prefix
    if ($msg_text != '') {
        $msg_text = ': ' . $msg_text;
    }

    // get the last script before this script
    $backtrace = debug_backtrace();
    if (array_key_exists(1, $backtrace)) {
        $last = $backtrace[1];
    } else {
        $last = $backtrace[0];
    }

    // extract the relevant part from backtrace
    if ($last != null) {
        if (array_key_exists('class', $last)) {
            $msg_text = $last['class'] . '->' . $last['function'] . $msg_text;
        } else {
            $msg_text = $last['function'] . $msg_text;
        }
    } else {
        $msg_text = $last['function'] . $msg_text;
    }

    if ($debug_used > 0) {
        echo $msg_text . '.<br>';
        //ob_flush();
        //flush();
    }

    return $msg_text;
}

/**
 * write a log message to the database and return the message that should be shown to the user
 * with the link for more details and to trace the resolution process
 * used also for system messages so no debug calls from here to avoid loops
 *
 * @param string $msg_text is a short description that is used to group and limit the number of error messages
 * @param string $msg_description is the description or the problem with all details if two errors have the same $msg_text only one is used
 * @param string $msg_log_level is the criticality level e.g. debug, info, warning, error or fatal error
 * @param string $function_name is the function name which has most likely caused the error
 * @param string $function_trace is the complete system trace to get more details
 * @param user|null $usr is the user who has probably seen the error message
 * @return string the text that can be shown to the user in the navigation bar
 * TODO return the link to the log message so that the user can trace the bug fixing
 * TODO check that log_msg is never called from any function used here
 */
function log_msg(string  $msg_text,
                 string  $msg_description,
                 string  $msg_log_level,
                 string  $function_name,
                 string  $function_trace,
                 ?user   $usr = null,
                 bool    $force_log = false,
                 ?sql_db $given_db_con = null): string
{

    global $sys_log_msg_lst;
    global $db_con;

    $result = '';

    // use an alternative database connection if requested
    $used_db_con = $db_con;
    if ($given_db_con != null) {
        $used_db_con = $given_db_con;
    }

    // create a database object if needed
    if ($used_db_con == null) {
        $used_db_con = new sql_db();
    }
    // try to reconnect to the database
    // TODO activate Prio 3
    /*
    if (!$used_db_con->connected()) {
        if (!$used_db_con->open_with_retry($msg_text, $msg_description, $function_name, $function_trace, $usr)) {
            log_fatal('Stopped database connection retry', 'log_msg');
        }
    }
    */

    if ($used_db_con->connected()) {

        $lib = new library();

        // fill up fields with default values
        if ($msg_description == '') {
            $msg_description = $msg_text;
        }
        if ($function_name == '' or $function_name == null) {
            $function_name = (new Exception)->getTraceAsString();
            $function_name = $lib->str_right_of($function_name, '/git/zukunft.com/');
            $function_name = $lib->str_left_of($function_name, ': log_');
        }
        if ($function_trace == '') {
            $function_trace = (new Exception)->getTraceAsString();
        }
        $user_id = 0;
        if ($usr != null) {
            $user_id = $usr->id();
        }
        if ($user_id <= 0) {
            $user_id = $_SESSION['usr_id'] ?? SYSTEM_USER_ID;
        }

        // assuming that the relevant part of the message is at the beginning of the message at least to avoid double entries
        $msg_type_text = $user_id . substr($msg_text, 0, 200);
        if (!in_array($msg_type_text, $sys_log_msg_lst)) {
            $used_db_con->usr_id = $user_id;
            $sys_log_id = 0;

            $sys_log_msg_lst[] = $msg_type_text;
            if ($msg_log_level > LOG_LEVEL or $force_log) {
                $used_db_con->set_class(sys_log_function::class);
                $function_id = $used_db_con->get_id($function_name);
                if ($function_id <= 0) {
                    $function_id = $used_db_con->add_id($function_name);
                }
                $msg_text = str_replace("'", "", $msg_text);
                $msg_description = str_replace("'", "", $msg_description);
                $function_trace = str_replace("'", "", $function_trace);
                $msg_text = $used_db_con->sf($msg_text);
                $msg_description = $used_db_con->sf($msg_description);
                $function_trace = $used_db_con->sf($function_trace);
                $fields = array();
                $values = array();
                $fields[] = "sys_log_type_id";
                $values[] = $msg_log_level;
                $fields[] = "sys_log_function_id";
                $values[] = $function_id;
                $fields[] = "sys_log_text";
                $values[] = $msg_text;
                $fields[] = "sys_log_description";
                $values[] = $msg_description;
                $fields[] = "sys_log_trace";
                $values[] = $function_trace;
                if ($user_id > 0) {
                    $fields[] = user::FLD_ID;
                    $values[] = $user_id;
                }
                $used_db_con->set_class(sys_log::class);

                $sys_log_id = $used_db_con->insert_old($fields, $values, false);
                //$sql_result = mysqli_query($sql) or die('zukunft.com system log failed by query '.$sql.': '.mysqli_error().'. If this happens again, please send this message to errors@zukunft.com.');
                //$sys_log_id = mysqli_insert_id();
            }
            if ($msg_log_level >= MSG_LEVEL) {
                echo "Zukunft.com has detected a critical internal error: <br><br>" . $msg_text . " by " . $function_name . ".<br><br>";
                if ($sys_log_id > 0) {
                    echo 'You can track the solving of the error with this link: <a href="/http/error_log.php?id=' . $sys_log_id . '">www.zukunft.com/http/error_log.php?id=' . $sys_log_id . '</a><br>';
                }
            } else {
                if ($msg_log_level >= DSP_LEVEL) {
                    $usr = new user();
                    $usr->load_by_id($user_id);
                    $msk = new view($usr);
                    $msk_dsp = new view_dsp($msk->api_json());
                    $result .= $msk_dsp->dsp_navbar_simple();
                    $result .= $msg_text . " (by " . $function_name . ").<br><br>";
                }
            }
        }
    }
    return $result;
}


function get_user_id(?user $calling_usr = null): ?int
{
    global $usr;
    $user_id = 0;
    if ($calling_usr != null) {
        $user_id = $calling_usr->id();
    } else {
        if ($usr != null) {
            $user_id = $usr->id();
        }
    }
    return $user_id;
}

function log_info(string $msg_text,
                  string $function_name = '',
                  string $msg_description = '',
                  string $function_trace = '',
                  ?user  $calling_usr = null,
                  bool   $force_log = false): string
{
    return log_msg($msg_text,
        $msg_description,
        sys_log_level::INFO,
        $function_name, $function_trace,
        $calling_usr,
        $force_log);
}

function log_warning(string  $msg_text,
                     string  $function_name = '',
                     string  $msg_description = '',
                     string  $function_trace = '',
                     ?user   $calling_usr = null,
                     ?sql_db $given_db_con = null): string
{
    return log_msg($msg_text,
        $msg_description,
        sys_log_level::WARNING,
        $function_name,
        $function_trace,
        $calling_usr,
        false,
        $given_db_con
    );
}

function log_err(string $msg_text,
                 string $function_name = '',
                 string $msg_description = '',
                 string $function_trace = '',
                 ?user  $calling_usr = null): string
{
    global $errors;
    $errors++;
    // TODO move the next lines to a class and a private function "get_function_name"
    $lib = new library();
    if ($function_name == '' or $function_name == null) {
        $function_name = (new Exception)->getTraceAsString();
        $function_name = $lib->str_right_of($function_name, '#1 ');
        $function_name = $lib->str_left_of($function_name, '): ');
        $function_name = $lib->str_right_of($function_name, '/main/php/');
        $function_name = $lib->str_left_of($function_name, '.php(');
    }
    if ($function_name == '' or $function_name == null) {
        $function_name = 'no function name detected';
    }
    if ($function_trace == '') {
        $function_trace = (new Exception)->getTraceAsString();
    }
    return log_msg($msg_text,
        $msg_description,
        sys_log_level::ERROR,
        $function_name,
        $function_trace,
        $calling_usr);
}

/**
 * if still possible write the fatal error message to the database and stop the execution
 * @param string $msg_text is a short description that is used to group and limit the number of error messages
 * @param string $msg_description is the description or the problem with all details if two errors have the same $msg_text only one is used
 * @param string $function_name is the function name which has most likely caused the error
 * @param string $function_trace is the complete system trace to get more details
 * @param user|null $calling_usr the user who has trigger the error
 * @return string
 */
function log_fatal_db(
    string $msg_text,
    string $function_name,
    string $msg_description = '',
    string $function_trace = '',
    ?user  $calling_usr = null): string
{
    echo 'FATAL ERROR! ' . $msg_text;
    $lib = new library();
    if ($function_name == '' or $function_name == null) {
        $function_name = (new Exception)->getTraceAsString();
        $function_name = $lib->str_right_of($function_name, '/git/zukunft.com/');
        $function_name = $lib->str_left_of($function_name, ': log_');
    }
    if ($function_trace == '') {
        $function_trace = (new Exception)->getTraceAsString();
    }
    return log_msg(
        'FATAL ERROR! ' . $msg_text,
        $msg_description,
        sys_log_level::FATAL,
        $function_name,
        $function_trace,
        $calling_usr);
}

/**
 * try to write the error message to any possible out device if database connection is lost
 * TODO move to a log class and expose only the interface function
 * @param string $msg_text is a short description that is used to group and limit the number of error messages
 * @param string $msg_description is the description or the problem with all details if two errors have the same $msg_text only one is used
 * @param string $function_name is the function name which has most likely caused the error
 * @param string $function_trace is the complete system trace to get more details
 * @param user|null $calling_usr the user who has trigger the error
 * @return string the message that should be shown to the user if possible
 */
function log_fatal(string $msg_text,
                   string $function_name,
                   string $msg_description = '',
                   string $function_trace = '',
                   ?user  $calling_usr = null): string
{
    $time = (new DateTime())->format('c');
    echo $time . ': FATAL ERROR! ' . $msg_text;
    $STDERR = fopen('error.log', 'a');
    fwrite($STDERR, $time . ': FATAL ERROR! ' . $msg_text . "\n");
    $write_with_more_info = false;
    $usr_txt = '';
    if ($calling_usr != null) {
        $usr_txt = $calling_usr->dsp_id();
        $write_with_more_info = true;
    }
    if ($write_with_more_info) {
        fwrite($STDERR, $time . ': FATAL ERROR! ' . $msg_text
            . '", by user "' . $usr_txt . "\n");
    }
    $lib = new library();
    if ($function_name == '' or $function_name == null) {
        $function_name = (new Exception)->getTraceAsString();
        $function_name = $lib->str_right_of($function_name, '/git/zukunft.com/');
        $function_name = $lib->str_left_of($function_name, ': log_');
        $write_with_more_info = true;
    }
    if ($function_trace == '') {
        $function_trace = (new Exception)->getTraceAsString();
        $write_with_more_info = true;
    }
    if ($write_with_more_info) {
        fwrite($STDERR, $time . ': FATAL ERROR! ' . $msg_text . "\n"
            . $msg_description . "\n"
            . 'function ' . $function_name . "\n"
            . 'trace ' . "\n" . $function_trace . "\n"
            . 'by user ' . $usr_txt . "\n");
    }
    return $msg_text;
}

/**
 * display a message immediately to the user
 * @param string $txt the text that should be should to the user
 */
function log_echo(string $txt): void
{
    echo $txt;
    echo "\n";
}


/**
 * should be called from all code that can be accessed by an url
 * return null if the db connection fails or the db is not compatible
 * TODO create a separate class for starting the backend and frontend
 *
 * @param string $code_name the place that is displayed to the user e.g. add word
 * @param string $style the display style used to show the place
 * @return sql_db the open database connection
 */
function prg_start(string $code_name, string $style = "", $echo_header = true): sql_db
{
    global $sys_time_start, $sys_script, $errors;
    global $sys_times;

    // TODO check if cookies are actually needed
    // resume session (based on cookies)
    session_start();

    $sys_time_start = time();
    $sys_times = new system_time_list();
    $sys_times->switch(system_time_type::DEFAULT);
    $sys_script = $code_name;
    $errors = 0;

    log_debug($code_name . ': session_start');

    // html header
    if ($echo_header) {
        $html = new html_base();
        echo $html->header("", $style);
    }

    return prg_restart($code_name, $style);
}

/**
 * open the database connection and load the base cache
 * @param string $code_name the place that is displayed to the user e.g. add word
 * @return sql_db the open database connection
 */
function prg_restart(string $code_name): sql_db
{

    global $db_con;
    global $cfg;
    global $mtr;

    // link to database
    $db_con = new sql_db;
    $db_con->db_type = SQL_DB_TYPE;
    $sc = new sql_creator();
    $sc->set_db_type($db_con->db_type);
    $db_con->open();
    if ($db_con->postgres_link === false) {
        log_debug($code_name . ': start db setup');
        $db_con->setup();
        $db_con->open();
        if ($db_con->postgres_link === false) {
            log_fatal('Cannot connect to database', 'prg_restart');
        }
    } else {
        log_debug($code_name . ': db open');

        // check the system setup
        $db_chk = new db_check();
        $usr_msg = $db_chk->db_check($db_con);
        if (!$usr_msg->is_ok()) {
            echo '\n';
            echo $usr_msg->all_message_text();
            $db_con->close();
            $db_con = null;
        }

        // create a virtual one-time system user to load the system users
        $usr_sys = new user();
        $usr_sys->set_id(user::SYSTEM_ID);
        $usr_sys->name = user::SYSTEM_NAME;

        // load system configuration
        $cfg = new config_numbers($usr_sys);
        $cfg->load_cfg($usr_sys);
        $mtr = new Translator($cfg->language());

        // preload all types from the database
        $sys_typ_lst = new type_lists();
        $sys_typ_lst->load($db_con, $usr_sys);

        $log = new change_log($usr_sys);
        $db_changed = $log->create_log_references($db_con);

        // reload the type list if needed and trigger an update in the frontend
        // even tough the update of the preloaded list should already be done by the single adds
        if ($db_changed) {
            $sys_typ_lst->load($db_con, $usr_sys);
        }

    }
    return $db_con;
}

function prg_start_api($code_name): sql_db
{
    global $sys_time_start, $sys_script, $usr_pro_cac;
    global $sys_times;

    log_debug($code_name . ' ..');

    $sys_time_start = time();
    $sys_times = new system_time_list();
    $sys_script = $code_name;

    // resume session (based on cookies)
    session_start();

    // link to database
    $db_con = new sql_db;
    $db_con->db_type = SQL_DB_TYPE;
    $db_con->open();
    log_debug($code_name . ' ... database link open');

    return $db_con;
}

/**
 *
 * @param $code_name
 * @return sql_db
 */
function prg_start_system($code_name): sql_db
{
    global $sys_time_start, $sys_script, $usr_pro_cac;
    global $sys_times;

    log_debug($code_name . ' ..');

    $sys_time_start = time();
    $sys_times = new system_time_list();
    $sys_script = $code_name;

    // resume session (based on cookies)
    session_start();

    // link to database
    $db_con = new sql_db;
    $db_con->db_type = SQL_DB_TYPE;
    $db_con->open();
    log_debug($code_name . ' ... database link open');

    // load user profiles
    $usr_pro_cac = new user_profile_list();
    $lib = new library();
    $tbl_name = $lib->class_to_name(user_profile::class);
    if ($db_con->has_table($tbl_name)) {
        $usr_pro_cac->load($db_con);
    } else {
        $usr_pro_cac->load_dummy();
    }

    return $db_con;
}

/**
 * write the execution time to the database if it is long
 */
function prg_end_write_time($db_con): void
{
    global $sys_time_start, $sys_time_limit, $sys_script, $sys_log_msg_lst;
    global $sys_times;

    $time_report = $sys_times->report();
    $sys_time_end = time();
    if ($sys_time_end > $sys_time_limit) {
        $db_con->usr_id = SYSTEM_USER_ID;
        $db_con->set_class(system_time_type::class);
        $sys_script_id = $db_con->get_id($sys_script);
        if ($sys_script_id <= 0) {
            $sys_script_id = $db_con->add_id($sys_script);
        }
        $start_time_sql = date("Y-m-d H:i:s", $sys_time_start);
        $end_time_sql = date("Y-m-d H:i:s", $sys_time_end);
        $interval = $sys_time_end - $sys_time_start;
        $milliseconds = $interval;

        //$db_con->insert();
        if (in_array('REQUEST_URI', $_SERVER)) {
            $calling_uri = $_SERVER['REQUEST_URI'];
        } else {
            $calling_uri = 'localhost';
        }
        $sql = "INSERT INTO system_times (start_time, system_time_type_id, end_time, milliseconds) VALUES ('" . $start_time_sql . "'," . $sys_script_id . ",'" . $end_time_sql . "', " . $milliseconds . ");";
        $db_con->exe($sql);
    }

    // free the global vars
    unset($sys_log_msg_lst);
    unset($sys_script);
    unset($sys_time_limit);
    unset($sys_time_start);
}

function prg_end($db_con, $echo_header = true): void
{
    global $sys_time_start, $sys_time_limit, $sys_script, $sys_log_msg_lst;

    if ($echo_header) {
        $html = new html_base();
        echo $html->footer();
    }

    prg_end_write_time($db_con);

    // Free result test
    //mysqli_free_result($result);

    // Closing connection
    $db_con->close();

    log_debug(' ... database link closed');
}

// special page closing only for the about page
function prg_end_about($link)
{
    global $db_con;
    global $sys_time_start, $sys_time_limit, $sys_script, $sys_log_msg_lst;

    $html = new html_base();
    echo $html->footer(true);

    prg_end_write_time($db_con);

    // Closing connection
    $db_con->close();

    log_debug(' ... database link closed');
}

// special page closing of api pages
// for the api e.g. the csv export no footer should be shown
function prg_end_api($link)
{
    global $db_con;
    global $sys_time_start, $sys_time_limit, $sys_script, $sys_log_msg_lst;

    prg_end_write_time($db_con);

    // Closing connection
    $db_con->close();

    log_debug(' ... database link closed');
}

/**
 * @return string the content of a resource file
 */
function resource_file(string $resource_path): string
{
    $result = file_get_contents(RES_PATH . $resource_path);
    if ($result === false) {
        $result = 'Cannot get file from ' . RES_PATH . $resource_path;
    }
    return $result;
}


/*
 * display functions
 */

// to display a boolean var
function zu_dsp_bool($bool_var): string
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


/**
 * returns true if the version to check is older than this program version
 * used e.g. for import to allow importing of files of an older version without warning
 */
function prg_version_is_newer($prg_version_to_check, $this_version = PRG_VERSION): bool
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

/**
 * unit_test for prg_version_is_newer
 */
function prg_version_is_newer_test(test_cleanup $t): void
{
    $result = zu_dsp_bool(prg_version_is_newer('0.0.1'));
    $target = 'false';
    $t->display('prg_version 0.0.1 is newer than ' . PRG_VERSION, $target, $result);
    $result = zu_dsp_bool(prg_version_is_newer(PRG_VERSION));
    $target = 'false';
    $t->display('prg_version ' . PRG_VERSION . ' is newer than ' . PRG_VERSION, $target, $result);
    $result = zu_dsp_bool(prg_version_is_newer(NEXT_VERSION));
    $target = 'true';
    $t->display('prg_version ' . NEXT_VERSION . ' is newer than ' . PRG_VERSION, $target, $result);
    $result = zu_dsp_bool(prg_version_is_newer('0.1.0', '0.0.9'));
    $target = 'true';
    $t->display('prg_version 0.1.0 is newer than 0.0.9', $target, $result);
    $result = zu_dsp_bool(prg_version_is_newer('0.2.3', '1.1.1'));
    $target = 'false';
    $t->display('prg_version 0.2.3 is newer than 1.1.1', $target, $result);
}

