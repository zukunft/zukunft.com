# Objects

## Object structure

the object structure is:

```
+-- component_db - a single display object like a headline or a table
+-- position - the position of a data_object within a sheet
+-- position_list - a list of positions of a data object within a sheet
+-- def - general system definitions
+-- files - resource file names used in the backend
+-- paths - set the path const for the backend php scripts
+-- db_check - test if the database exists and start the creation or upgrade process
+-- sql - all sql language const used in all database dialects
+-- sql_creator - create sql statements for different database dialects
+-- sql_db - the SQL database link and abstraction layer
+-- sql_field - to combine a sql field name with a value and a sql column type
+-- sql_field_list - a list of sql parameter fields
+-- sql_par - combine the query name, the sql statement and the parameters in one object
+-- sql_par_field - combine a sql parameter field name with the value and the parameter type
+-- sql_par_field_list - a list of sql parameter fields
+-- sql_par_list - a list of sql parameters and calls
+-- sql_pg - Postgres SQL elements and const used
+-- sql_sync_sequences - check and fix the sql sequences in all database dialects
+-- sql_type_list - a list of parameters to define which sql statement should be created
+-- sql_where - structure for one where parameter for a sql statement
+-- sql_where_list - list to create the sql where condition
+-- export - create an object to export data - the object can be converted to a json, yaml or XML message
+-- xml_serializer - turning an array or object into XML using PHP
+-- fig_ids - helper class for figure id lists
+-- formula_db - the database const for formula tables
+-- group_id_list - functions for a list of group ids
\-- id
    \-- group_id - e.g. to create a group_id based on a phrase list
    \-- result_id - e.g. to create a id based on a mix of source, result or both phrases and the formula id
+-- data_object - a header object for all data objects e.g. phrase_list, values, formulas
\-- db_object_no_id
    \-- value_ts_data - for a single time series value data entry
+-- id_list - a base object for a list of database IDs
\-- type_list
    \-- component_link_type_list - to define the behaviour if a component is linked to a view
    \-- component_type_list - to link coded functionality to a view component
    \-- position_type_list - to link coded functionality to a view component position
    \-- view_style_list - to define the view or component style e.g. the number of columns to use
    \-- element_type_list - to link coded functionality to a formula element type
    \-- formula_link_type_list - to link coded functionality to a formula link
    \-- formula_type_list - list to link coded functionality to a formula
    \-- language_form_list - a database based enum list of all languages
    \-- language_list - a database based enum list of all languages
    \-- change_action_list - the const for the change log action table
    \-- change_field_list - the const for the change log field table
    \-- change_table_list - to link coded functionality to a log log table
    \-- phrase_types - to link coded functionality to a word or a triple, which means to every phrase
    \-- ref_list - al list of ref objects
    \-- ref_type_list - to link coded functionality to a reference
    \-- source_type_list - to link coded functionality to a source
    \-- protection_type_list - a database based enum list for the data protection types
    \-- share_type_list - a database based enum list for the data share types
    \-- job_type_list - list of predefined system batch jobs
    \-- sys_log_function_list - to group the system log entries by function
    \-- sys_log_status_list - list of the system log statuus
    \-- user_profile_list - a list of possible user profiles with the database id
    \-- verb_list - al list of verb objects
    \-- view_link_type_list - to defined how a term is linked to a view
    \-- view_relation_type_list - to defined how a term is relationed to a view
    \-- view_sys_list - list of predefined system views
    \-- view_type_list - to link coded functionality to a view
+-- type_lists - helper class to combine all preloaded types in one class for the API
+-- convert_wikipedia_table - convert a wikipedia table to a
+-- import - import data - take a object from a json, yaml or XML message and trigger the object saves
+-- import_file - IMPORT a json in the zukunft.com exchange format
+-- change_table_field - helper class to create the database view with the log table and field name
+-- text_log - object to handle standard io logging
+-- phr_ids - helper class for phrase id lists
+-- trm_ids - helper class for term id lists
+-- ref_db - the database const for reference tables
+-- ref_link_wikidata - link for the reference type wikidata
+-- source_db - the database const for source tables
+-- result_db - the database const for triple tables
\-- user_service
    \-- xml - to im- and export xml files
+-- ip_range_exp - a base object for a list of database IDs
+-- log - the simple log interface object
+-- system_time_list - a list of system error objects
+-- user_list - a list of users
+-- user_message - a complex object that functions can return
+-- value_db - the database const for value tables
+-- value_obj - just to select the best fitting class for a value
+-- verb_db - the database const for predicate/verb tables
+-- view_db - the database const for view tables
+-- view_relation_db - the database const for view relation tables
+-- triple_db - the database const for triple tables
+-- word_db - the database const for word tables
+-- apiShared - constants used for the backend to frontend api of zukunft.com
+-- expressionShared - common parts of the formula expressing handling used in front- and backend
+-- charsShared - const symbols used for the formula expressions
+-- componentsShared - const components with name and id used by the system
+-- filesShared - resource file names used in backend and frontend
+-- formulasShared - predefined formulas used in the backend and frontend as code id
+-- groupsShared - phrase group or value names used by the system for testing
+-- refsShared - references used by the system for testing
+-- rest_ctrlShared - constants used for the backend to frontend api of zukunft.com
+-- resultsShared - results used by the system for testing only in the backend
+-- sourcesShared - sources used by the system for testing
+-- triplesShared - predefined triples used in the backend and frontend as code id
+-- usersShared - users used by the system
+-- valuesShared - values used by the system for testing
+-- viewsShared - system views with name and id
+-- wordsShared - predefined words used in the backend and frontend as code id
\-- CombineObjectShared
    \-- combine_object
        \-- figure - combine object for value and result
        \-- combine_named
            \-- phrase - either a word or a triple
            \-- term - either a word, verb, triple or formula
    \-- combine_objectUi
        \-- combine_namedUi
            \-- phraseUi - to create the html code to display a word or triple
+-- ConfigShared - const fallback configuration settings
\-- IdObjectShared
    \-- db_object
        \-- db_object_seq_id
            \-- group_link - only for fast selection of the phrase group assigned to one triple
            \-- db_id_object_non_sandbox
                \-- user - a person who uses zukunft.com
            \-- db_object_seq_id_user
                \-- element - either a word, triple, verb or formula with a link to a formula
                \-- change_log
                    \-- change
                        \-- changes_big - log group changes for values with more than 16 phrases
                        \-- changes_norm - log group changes for values with up to 16 phrases
                    \-- change_link - object to save updates of references (links) by the user in the database in a format, so that it can fast be displayed to the user
                    \-- change_value
                        \-- change_value_geo
                            \-- change_values_geo_big - log object for changes of values with a big group id
                            \-- change_values_geo_norm - log object for changes of values with a standard group id
                            \-- change_values_geo_prime - log object for changes of values with a prime group id
                        \-- change_value_text
                            \-- change_values_text_big - log object for changes of values with a big group id
                            \-- change_values_text_norm - log object for changes of values with a standard group id
                            \-- change_values_text_prime - log object for changes of values with a prime group id
                        \-- change_value_time
                            \-- change_values_time_big - log object for changes of values with a big group id
                            \-- change_values_time_norm - log object for changes of values with a standard group id
                            \-- change_values_time_prime - log object for changes of values with a prime group id
                        \-- change_values_big - log object for changes of values with a big group id
                        \-- change_values_norm - log object for changes of values with a standard group id
                        \-- change_values_prime - log object for changes of values with a prime group id
                \-- sandbox
                    \-- sandbox_link
                        \-- component_link - link a single display component/element to a view
                        \-- formula_link - link a formula to a word
                        \-- ref - a link between a phrase and another system such as wikidata
                        \-- sandbox_link_named
                            \-- triple_object - a base object that can be used for word links, so either a word, triple or group
                            \-- triple - the object that links two words (an RDF triple)
                        \-- sandbox_predicated_link - adding the type field to the user sandbox link superclass
                        \-- term_view - to define the view for a word, triple, verb or formula
                        \-- view_relation - to define the relation between two views e.g. to have a parent view where the child view have additional components
                    \-- sandbox_named
                        \-- sandbox_typed
                            \-- sandbox_code_id
                                \-- component - a single display object like a headline or a table
                                \-- formula - the main formula object
                                \-- source - the source object to define a source for values
                                \-- view - the main display object
                                \-- word - the main word object
                \-- job - object to combine all parameters for one calculation or cleanup request
            \-- type_object
                \-- component_link_type - db based ENUM of the component view link types
                \-- component_type - db based ENUM of the component types
                \-- position_type - db based ENUM of the view component position types
                \-- view_style - db based ENUM of the view and component display styles e.g. the width
                \-- element_type - to assign coded functionality to a formula element
                \-- formula_link_type - the formula link type object with the ENUM values for hardcoded formulas
                \-- formula_type - the formula type object with the ENUM values for hardcoded formulas
                \-- language - to define a language for the user interface
                \-- language_form - to define a language form e.g. plural
                \-- change_action - the change type done by a user
                \-- change_field - the field where a user has done a change including deprecated field names
                \-- change_table - the table where a user has done a change including deprecated table names
                \-- phrase_table_status - the status of a phrase table
                \-- phrase_type - the phrase type object for the frontend API
                \-- ref_type - the base object for links between a phrase and another system such as wikidata
                \-- source_type - the base object for external source type such as pubmed
                \-- protection_type - to define if and how an object can changed
                \-- share_type - to define if an object can be shared between the users
                \-- job_type - a predefined batch task that can be triggered by a user action or a scheduler
                \-- pod - the technical details of the mash network pods
                \-- pod_status - the status of a pod
                \-- pod_type - to assign predefined code to a some pods
                \-- sys_log_function - to group the system log entries by function
                \-- sys_log_status - to link coded functionality to a system log status
                \-- sys_log_type - to link coded functionality to a system log status
                \-- user_official_type - the superclass for word, formula and view types
                \-- user_profile - a database based enum for the user profiles
                \-- user_type - the superclass for word, formula and view types
                \-- verb - predicate object to link two words
                \-- view_link_type - to define the behaviour of the link between a term and a view
                \-- view_relation_type - to define the relation between two views
                \-- view_type - db based ENUM of the view types
                \-- system_time_typeShared - the areas of execution times
            \-- phrase_table - remember which phrases are stored in which table and pod
            \-- ip_range - a base object for a list of database IDs
            \-- job_time - to scheduled a job with predefined parameters
            \-- session - to control the user frontend sessions
            \-- sys_log - object to handle a system errors
            \-- system_time - object to log and optimize the execution times of the system
            \-- user_db - a person who uses zukunft.com
\-- ListOfShared
    \-- value_type_list - a list of value types e.g. to create the query extension
    \-- ListOfIdObjectsShared
        \-- base_list
            \-- element_group - a group of formula elements that, in combination, return a value or a list of values
            \-- change_log_list - read the changes from the database and forward them to the API
            \-- sandbox_list
                \-- element_group_list - simply a list of formula element groups to place the name function
                \-- element_list - a list of formula elements to place the name function
                \-- figure_list - a list of figures, so either a value of a formula result object
                \-- group_list - a list of word and triple groups
                \-- sandbox_link_list
                    \-- component_link_list - a list of links between a view and a component
                    \-- formula_link_list - a list of formula word links
                    \-- term_view_list - a list of assignments from terms to views
                \-- sandbox_list_named
                    \-- component_list - list of predefined system components
                    \-- formula_list - a simple list of formulas
                    \-- phrase_list - a list of phrase (word or triple) objects
                    \-- term_list - a list of word, triple, verb or formula objects
                    \-- source_list - al list of source objects
                    \-- view_list - list of predefined system views
                    \-- triple_list - a list of word links, mainly used to build a RDF graph
                    \-- word_list - a list of word objects
                \-- sandbox_value_list
                    \-- result_list - a list of formula results
                    \-- value_list
                        \-- config_numbers - additional behavior for the system and user config graph value tree
            \-- ip_range_list - a list of internet protocol address ranges
            \-- job_list - a list of calculation request
            \-- sys_log_list - a list of system error objects
        \-- ListBaseUi
            \-- element_groupUi - a group of formula elements that, in combination, return a value or a list of values
            \-- element_listUi - a list of formula elements to place the name function
            \-- figure_listUi - the display extension of the api figure list object
            \-- formula_link_listUi - create the html code for a list of formula links
            \-- formula_listUi - a list function to create the HTML code to display a formula list
            \-- change_log_listUi - a list function to create the HTML code to display a list of user changes
            \-- ref_listUi - create the HTML code to display a reference list
            \-- source_listUi - create the HTML code to display a source list
            \-- list_namedUi
                \-- word_listUi - a list function to create the HTML code to display a word list
            \-- sandbox_listUi
                \-- group_listUi - a list of word and triple groups
                \-- sandbox_list_namedUi
                    \-- component_listUi - a list function to create the HTML code to display a view component list
                    \-- phrase_listUi - create the html code to display a phrase list
                    \-- term_listUi - the display extension of the api phrase list object
                    \-- sandbox_list_valueUi
                        \-- result_listUi - the display extension of the api result list object
            \-- job_listUi - the display extension of the system error log api object
            \-- value_listUi
                \-- configUi - to cache and manage the user config in the frontend
            \-- view_listUi - a list function to create the HTML code to display a view list
            \-- triple_listUi - a list function to create the HTML code to display a triple list
\-- TextIdObjectShared
    \-- db_object_key
        \-- db_object_multi
            \-- db_object_multi_user
                \-- sandbox_multi
                    \-- group - a combination of a word list and a triple_list
                    \-- sandbox_value
                        \-- result
                            \-- result_two - overwrite the result functions for up to two assigned triples
                        \-- value_base
                            \-- value - the main numeric value object using the prime, norm and big value keys
                            \-- value_geo - the main geolocation value object using the prime, norm and big value keys
                            \-- value_text - the main text value object using the prime, norm and big value keys
                            \-- value_time - the main text value object using the prime, norm and big value keys
                        \-- value_time_series - the header object for time series values
    \-- db_objectUi
        \-- elementUi - either a word, triple, verb or formula with a link to a formula
        \-- userUi
            \-- user_display_oldUi - to display the user specific settings
+-- TranslatorShared - translates a message for the user into the user language
+-- json_fieldsShared - list of json field names used for the api and im- and export
+-- libraryShared - some useful function e.g. for string handling
+-- api_type_listShared - a list of parameters to configure the api message
+-- component_typeShared - db based ENUM of the component types
+-- file_typesShared - ENUM of the used file types
+-- formula_typesShared - db based ENUM of the formula types
+-- phrase_typeShared - the phrase code_ids used in back- and frontend
+-- position_typesShared - how view components can be placed for the user
+-- protection_typeShared - to define if and how an object can changed
+-- share_typeShared - to define if an object can be shared between the users
+-- verbsShared - to use the same verb code_id in frontend and backend
+-- view_relation_typesShared - db based ENUM of the view relation types
+-- view_stylesShared - db based ENUM of the view and component styles
+-- view_typeShared - db based ENUM of the view types
+-- url_varShared - all names used for the url and the form field names
\-- ui_baseUi
    \-- ui_im_exportUi - html user interface components for im- and export
    \-- ui_listUi - create the html for listed related to an object
    \-- ui_previewUi - the html user interface components to preview object changes
+-- ui_foafUi - html user interface components to show related objects as a tree
+-- ui_linkUi - html user interface components to link and unlink objects
+-- ui_logUi - html user interface components for change log
+-- ui_rankUi - html user interface components for ranking objects
+-- ui_selectUi - html interface components to select an object
+-- position_listUi - a list of positions of a data object within a sheet
+-- pathsUi - set the path const for the frontend php scripts
+-- frontendUi - the main html frontend application
+-- data_objectUi - frontend cache object
+-- url_mapperUi - create human-readable or pod exchangeable urls
+-- hist_logUi - display the past changes of an object
+-- buttonUi - create the html code to display a button to the user
\-- html_baseUi
    \-- display_listUi - to display a list that can be sorted
+-- html_namesUi - HTML language const used for the html zukunft.com frontend
+-- html_selectorUi - to select a word (or formula or verb)
+-- list_sortUi - create the html code to display a sortable list
+-- rest_callUi - functions used by the frontend to call the backend api of zukunft.com
+-- scopesUi - scope constants used for html frontend
+-- sheetUi - create the html code to display a spreadsheet
+-- stylesUi - style constants used for html frontend
+-- tableUi - create the html code to display a spreadsheet
+-- user_log_displayUi - a combined object to display single value changes or changes of links by the user
+-- user_sandbox_displayUi - extends the user sandbox superclass for common display functions
+-- back_traceUi - list of links that the user has called in the past
+-- sys_log_listUi - the display extension of the system error log api object
\-- type_listUi
    \-- change_action_listUi - the preloaded change log actions used for the html frontend
    \-- change_field_listUi - the preloaded change log fields used for the html frontend
    \-- change_table_listUi - the preloaded change log tables used for the html frontend
    \-- component_link_type_listUi - the preloaded data component link types used for the html frontend
    \-- component_type_listUi - the preloaded data component types used for the html frontend
    \-- formula_link_type_listUi - the preloaded data formula link types used for the html frontend
    \-- formula_type_listUi - the preloaded data formula types used for the html frontend
    \-- job_type_listUi - the preloaded data job types used for the html frontend
    \-- language_formsUi - the preloaded data language_forms used for the html frontend
    \-- languagesUi - the preloaded data languages used for the html frontend
    \-- phrase_typesUi - the preloaded data phrase types used for the html frontend
    \-- position_type_listUi - the preloaded data component position types used for the html frontend
    \-- protectionUi - the preloaded data protection types used for the html frontend
    \-- ref_type_listUi - the preloaded data ref types used for the html frontend
    \-- shareUi - the preloaded data share types used for the html frontend
    \-- source_type_listUi - the preloaded data source types used for the html frontend
    \-- sys_log_status_listUi - the preloaded system log actions used for the html frontend
    \-- user_profileUi - the preloaded user profiles used for the html frontend
    \-- verbsUi - the preloaded data verbs used for the html frontend
    \-- view_link_type_listUi - the preloaded data of view link types used for the html frontend
    \-- view_relation_type_listUi - the preloaded data of view relation types used for the html frontend
    \-- view_style_listUi - the preloaded view styles used for the html frontend
    \-- view_type_listUi - the preloaded data view types used for the html frontend
    \-- user_type_listUi - the display extension of the user specific api type list object
    \-- verb_listUi - al list of verb objects
+-- type_listsUi - parent object for all preloaded types used in the html frontend
\-- type_objectUi
    \-- ref_typeUi - the child class for reference types which has additional the url
+-- user_messageUi - messages created by the frontend for the user
+-- component_linkUi - create HTML code to display a view component links
```
