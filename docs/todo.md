TODO notes
----------

A proper issue ticket should be created for these TODOs notes:

    but first this needs to be prioritized:

    TODO Release 0.0.3
    TODO complete the import db write tests (pending: )
    TODO make main backend object vars private (pending: ref, value, formula, result, view, component, user)
    TODO complete and test the url mapper in the frontend
    TODO clean up import_mapper and move all mapping from import_obj to the mapper

    TODO object chart 
         - add legend with object types and that dotted line is inheritance   
         - move result towards value
         - keep object inheritance tree
         - keep highlight of word, value formula

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

    TODO Release 0.0.4
    TODO save the config for backend, frontend and user as a cache json file and use to trigger for recreation
    TODO activate the class section test
    TODO include owner and user in im- and export

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
    TODO test if the redis memory db is faster than the object lists for caching
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
    TODO use the user_message object to collect all messages to the user and present the result to the user
    TODO use log only for unexpected errors an warnings where the user has probably no chance ti fix it
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
    TODO use the LLM-KI token weights to preload the triple weights
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



## notes

admin
- use once loaded arrays for all tables that are never expected to be changed like the type tables
- allow the admin user to set the default value
- create a daily? task that finds the median value, sets it as the default and recreate the user values
- add median_user and set_owner to all user sandbox objects
- check which functions can be private in the user sandbox
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
- add a select box the view name in the page header e.g. select box to select the view with a line to add a new view
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
- rename triples to phrase links, because it should always be possible to link a phrase

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
- bug: display linked words does not display the downward words e.g. "company main ratio" does not show "Target Price"
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
