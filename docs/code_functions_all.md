# Object functions

## Functions sections

the function sections are:

```
\-- main backend
    \-- construct and map
        \-- __construct
            \-- component - define the settings for this view component object
            \-- formula - define the settings for this formula object
            \-- source - 
            \-- value - set the user, which is needed in all cases and the main vars with the object creation
        \-- reset
            \-- component - clear the view component object values
            \-- formula - clear the view component object values
            \-- source - 
        \-- row_mapper_sandbox
            \-- component - map the database fields to the object fields
            \-- formula - map the database fields to the object fields
            \-- source - map the database object to this source class fields
        \-- api_mapper
            \-- component - map a component api json to this model component object
            \-- formula - map a formula api json to this model formula object
            \-- source - map a source api json to this model source object
        \-- import_mapper_user
            \-- component - import a view component from a JSON object
            \-- formula - import a formula and its links from an import JSON object
            \-- source - set the object vars of this source object based on the import json array
        \-- import_mapper
            \-- formula - set the vars of this formula object based on the given json without writing to the database
    \-- api
        \-- api_json_array
            \-- component - create an array for the api json creation
            \-- formula - create an array for the api json creation
            \-- source - create an array for the api json creation
            \-- value - create an array for the api json creation
        \-- save_from_api_msg
            \-- source - set the source object vars based on an api json array
    \-- im- and export
        \-- 5
            \-- formula - assign the formula to the words and triple
        \-- export_json
            \-- component - create an array with the export json fields
            \-- formula - create an array with the export json fields
            \-- source - create an array with the export json fields
            \-- value - create an array with the export json fields
    \-- set and get
        \-- set_type
            \-- component - set the predefined view component type by the given code id or name
            \-- formula - set the predefined type of this formula by the given code id or name
            \-- source - set the predefined source type by the given code id or name
        \-- set_style
            \-- component - set the default style for this component by the code id
        \-- set_style_by_id
            \-- component - set the default style for this component by the database id
        \-- get_style
            \-- component - @return view_style|type_object|null the view style for this component or null if the parent style should be used
        \-- get_style_id
            \-- component - @return int|null the database id of the view style or null
        \-- set_row_phrase
            \-- component - define or remove the phrase that is used to select the table rows
        \-- get_row_phrase_id
            \-- component - define or remove the phrase that is used to select the table rows
        \-- get_row_phrase_name
            \-- component - define or remove the phrase that is used to select the table rows
        \-- set_col_phrase
            \-- component - define or remove the phrase that is used to select the table columns
        \-- get_col_phrase_id
            \-- component - define or remove the phrase that is used to select the table columns
        \-- get_col_phrase_name
            \-- component - define or remove the phrase that is used to select the table columns
        \-- set_col_sub_phrase
            \-- component - define or remove the phrase that is used as the second selection for table columns
        \-- get_col_sub_phrase_id
            \-- component - define or remove the phrase that is used as the second selection for table columns
        \-- get_col_sub_phrase_name
            \-- component - define or remove the phrase that is used as the second selection for table columns
        \-- set_ui_msg_code_id
            \-- component - set the ui message code id of this object to write the change to the db
        \-- get_ui_msg_code_id
            \-- component - @return msg_id|null the message id or null
        \-- set_ui_msg_code_id_vars
            \-- component - set the ui message code id to be used after the number to write the change to the db
        \-- get_ui_msg_code_id_vars
            \-- component - @return msg_id|null the message id or null
        \-- set_ui_msg_code_id_exception
            \-- component - set the ui message code id to be used as an exception to write the change to the db
        \-- get_ui_msg_code_id_exception
            \-- component - @return msg_id|null the message id or null
        \-- set_ui_msg_value_exception
            \-- component - set the value to select the exception message to write the change to the db
        \-- get_ui_msg_value_exception
            \-- component - @return float|null the message id or null
        \-- set_formula_by_id
            \-- component - set the formula of the component by the id
        \-- set_formula
            \-- component - set the formula used for the component
        \-- get_formula
            \-- component - set the formula used for the component
        \-- get_formula_id
            \-- component - set the formula used for the component
        \-- set_link_type
            \-- component - set the type of linked components
        \-- set_join
            \-- component - TODO use a set_join function for all not simple sql joins
        \-- set_view_id
            \-- formula - @param int $id the id of the default view that should be remembered
        \-- view_id
            \-- formula - @return int the id of the default view for this word or null if no view is preferred
        \-- set_user_text
            \-- formula - update the expression by setting the human-readable format and try to update the database reference format
        \-- usr_text
            \-- formula - update the expression by setting the human-readable format and try to update the database reference format
        \-- ref_text
            \-- formula - update the expression by setting the human-readable format and try to update the database reference format
        \-- set_impact
            \-- formula - set the cache value to sort this sandbox object by relevance
        \-- impact
            \-- formula - @return float|null a higher number indicates a higher relevance
        \-- set_url
            \-- source - set the predefined source type by the given code id or name
        \-- url
            \-- source - set the predefined source type by the given code id or name
        \-- set_value
            \-- value - overwrite the sandbox_value set_value() function to set the numeric value
        \-- value
            \-- value - overwrite the sandbox_value value() function to return the numeric value
    \-- preloaded
        \-- type_code_id
            \-- component - @return string|null the code_id of the component type
            \-- formula - @return string|null the code_id of the formula type
            \-- source - @return string|null the code_id of the source type
        \-- type_name
            \-- component - @return string the name of the component type
            \-- formula - @return string the name of the formula type
            \-- source - @return string the source type name from the array preloaded from the database
        \-- type_name_or_null
            \-- component - get the name of the component type or null if no type is set
    \-- load
        \-- load_by_name
            \-- component - just set the class name for the user sandbox function
        \-- load_by_id
            \-- component - just set the class name for the user sandbox function
        \-- load_standard
            \-- component - load the view component parameters for all users
            \-- formula - load the formula parameters for all users
            \-- source - load the source parameters for all users
        \-- load_standard_sql
            \-- component - create the SQL to load the default view always by the id
            \-- formula - create the SQL to load the default formula always by the id
            \-- source - create the SQL to load the default source always by the id
        \-- load_sql
            \-- formula - create the common part of an SQL statement to retrieve
            \-- source - create the common part of an SQL statement to retrieve the parameters of a source from the database
        \-- load_wrd
            \-- formula - load the corresponding name word for the formula name
        \-- name_field
            \-- formula - load the corresponding name word for the formula name
        \-- all_sandbox_fields
            \-- formula - load the corresponding name word for the formula name
    \-- load sql
        \-- load_sql
            \-- component - create the common part of an SQL statement to retrieve the parameters of a view component from the database
        \-- load_sql_user_changes
            \-- component - create an SQL statement to retrieve the user changes of the current view component
    \-- sql fields
        \-- name_field
            \-- component - create an SQL statement to retrieve the user changes of the current view component
            \-- source - check if the source in the database needs to be updated
        \-- all_sandbox_fields
            \-- component - create an SQL statement to retrieve the user changes of the current view component
            \-- source - check if the source in the database needs to be updated
    \-- retrieval
        \-- reload_phrases
            \-- component - load the related word and formula objects
        \-- reload_row_phrase
            \-- component - load the phrase that should be used for the rows of a table
        \-- reload_col_phrase
            \-- component - load the phrase that should be used for the columns of a table
        \-- reload_wrd_col2
            \-- component - load a phrase if the id is valid
        \-- reload_formula
            \-- component - load a phrase if the id is valid
    \-- modify
        \-- fill
            \-- component - fill this component based on the given component
            \-- formula - fill this formula based on the given formula
    \-- info
        \-- diff_msg
            \-- component - create human-readable messages of the differences between the objects
        \-- 3
            \-- formula - return the true if the formula has a special type and the result is a kind of hardcoded
        \-- needs_db_update
            \-- component - check if the named object in the database needs to be updated
            \-- formula - check if the formula in the database needs to be updated
            \-- source - check if the source in the database needs to be updated
        \-- next_nbr
            \-- component - returns the next free order number for a new view component
        \-- special_result
            \-- formula - return the result of a special formula
        \-- special_time_phr
            \-- formula - return the time word id used for the special formula results
        \-- special_phr_lst
            \-- formula - get all phrases included by a special formula element for a list of phrases
    \-- log
        \-- log_link
            \-- component - returns the next free order number for a new view component
        \-- log_unlink
            \-- component - returns the next free order number for a new view component
    \-- link
        \-- link
            \-- component - link this component to a view
        \-- unlink
            \-- component - remove a view component from a view
        \-- link_phr
            \-- formula - TODO Prio 0 add user_message as parameter
        \-- unlink_phr
            \-- formula - TODO Prio 0 add user_message as parameter
    \-- save
        \-- save_field_ui_msg_id
            \-- component - set the update parameters for the component user interface message id
        \-- save_field_ui_msg_id_vars
            \-- component - set the update parameters for the component user interface after message id
        \-- save_field_ui_msg_id_exception
            \-- component - set the update parameters for the component user interface exception message id
        \-- save_field_ui_msg_val_exception
            \-- component - set the update parameters for the component user interface exception message value
        \-- save_field_wrd_row
            \-- component - set the update parameters for the word row
        \-- save_field_wrd_col
            \-- component - set the update parameters for the word col
        \-- save_field_wrd_col2
            \-- component - set the update parameters for the word col2
        \-- save_field_formula
            \-- component - set the update parameters for the formula
        \-- save_all_fields
            \-- component - save all updated component fields excluding the name, because already done when adding a component
            \-- source - save all updated source fields excluding the name, because already done when adding a source
        \-- generate_ref_text
            \-- formula - update the database reference text based on the user text
        \-- generate_usr_text
            \-- formula - update the user text based on the database reference text
        \-- is_std
            \-- formula - @return bool true if the formula or formula assignment has not been overwritten by the user
        \-- is_used
            \-- formula - @return bool true if the formula or formula assignment has not been overwritten by the user
        \-- not_used
            \-- formula - @return bool true if the formula or formula assignment has not been overwritten by the user
    \-- del
        \-- del_links
            \-- component - delete the view component links of linked to this view component
            \-- formula - remove depending on objects
            \-- source - delete the references to this source
    \-- sql write fields
        \-- db_fields_all
            \-- component - get a list of all database fields that might be changed
            \-- formula - get a list of all database fields that might be changed
            \-- source - get a list of all database fields that might be changed
        \-- db_fields_changed
            \-- component - get a list of database field names, values and types that have been updated
            \-- formula - get a list of database field names, values and types that have been updated
            \-- source - get a list of database field names, values and types that have been updated
    \-- debug
        \-- assigned_msk_ids
            \-- component - @return array with all view ids that are directly assigned to this view component
    \-- word
        \-- formula_word
            \-- formula - create the corresponding name word object for the formula name
        \-- wrd_add
            \-- formula - add the corresponding name word for the formula name to the database
        \-- wrd_rename
            \-- formula - rename the corresponding name word if the formula is renamed
        \-- wrd_del
            \-- formula - remove the corresponding name word if the formula is deleted
        \-- wrd_add_fix
            \-- formula - add the corresponding name word for the formula name to the database without similar check
    \-- assign
        \-- assign_phr_glst_direct
            \-- formula - lists of all words directly assigned to a formula and where the formula should be used
        \-- assign_phr_lst_direct
            \-- formula - the complete list of a phrases assigned to a formula
        \-- assign_phr_ulst_direct
            \-- formula - the user specific list of a phrases assigned to a formula
        \-- assign_phr_glst
            \-- formula - returns a list of all words that the formula is assigned to
        \-- assign_phr_lst
            \-- formula - the complete list of a phrases assigned to a formula
        \-- assign_phr_ulst
            \-- formula - the user specific list of a phrases assigned to a formula
    \-- result
        \-- res_del
            \-- formula - delete all results for this formula
    \-- calc
        \-- to_num
            \-- formula - fill the formula in the reference format with numbers
        \-- calc_requests
            \-- formula - fill the formula in the reference format with numbers
        \-- calc
            \-- formula - calculate the result for one formula for one user
    \-- calculate the formula results based on a given figure list
        \-- calc_with
            \-- formula - calculate the formula results based on a given figure list
        \-- expression
            \-- formula - @param term_list|null $trm_lst a list of preloaded terms that should be used for the transformation
        \-- get_res_lst
            \-- formula - @return result_list a list of all formula results linked to this formula
    \-- cast
        \-- term
            \-- formula - @returns term the formula object cast into a term object
    \-- expression
        \-- term_list
            \-- formula - get all terms used in this formula
        \-- wrd_ids
            \-- formula - @param string $frm_text the formula expression in the reference format
        \-- trp_ids
            \-- formula - @param string $frm_text the formula expression in the reference format
        \-- vrb_ids
            \-- formula - @param string $frm_text the formula expression in the reference format
        \-- frm_ids
            \-- formula - @param string $frm_text the formula expression in the reference format
        \-- element_refresh_type
            \-- formula - update formula links
        \-- element_refresh
            \-- formula - update the database references to the formula elements
    \-- @return sql_par the SQL statement, the name of the SQL statement and the parameter list
        \-- not_changed_sql
            \-- formula - @return sql_par the SQL statement, the name of the SQL statement and the parameter list
        \-- not_changed
            \-- formula - true if no other user has modified the formula
        \-- load_sql_user_changes_frm
            \-- formula - create an SQL statement to retrieve all user specific changes of this formula
        \-- load_sql_user_changes
            \-- formula - create an SQL statement to retrieve the user changes of the current formula
        \-- del_usr_cfg_exe
            \-- formula - overwrite of the user sandbox function to remove also the related elements
        \-- save_field_trigger_update
            \-- formula - update the time stamp to trigger an update of the depending on results
        \-- save_field_usr_text
            \-- formula - set the update parameters for the formula text as written by the user if needed
        \-- save_field_ref_text
            \-- formula - set the update parameters for the formula in the database reference format
        \-- save_field_need_all
            \-- formula - set the update parameters that define if all results are needed to calculate a result
        \-- save_all_fields
            \-- formula - save all updated formula fields
        \-- save_field_name
            \-- formula - set the update parameters for the formula text as written by the user if needed
        \-- save_id_fields
            \-- formula - updated the view component name (which is the id field)
        \-- is_term_the_same
            \-- formula - updated the view component name (which is the id field)
        \-- save_id_if_updated
            \-- formula - check if the id parameters are supposed to be changed
        \-- add
            \-- formula - create a new formula
        \-- save
            \-- formula - add or update a formula in the database or create a user formula
    \-- sandbox
        \-- not_used
            \-- source - @return bool true if no one has used this source
        \-- not_changed_sql
            \-- source - @return sql_par the SQL statement, the name of the SQL statement and the parameter list
        \-- not_changed
            \-- source - @return bool true if no other user has modified the source
        \-- load_sql_user_changes
            \-- source - create an SQL statement to retrieve the user changes of the current source
\-- errors
    \-- application
        \-- start_api_core - section for function start_api_core missing in /application.php
        \-- start_api - section for function start_api missing in /application.php
        \-- end_api - section for function end_api missing in /application.php
        \-- start - section for function start missing in /application.php
        \-- open_db - section for function open_db missing in /application.php
        \-- end - section for function end missing in /application.php
    \-- component_list
        \-- save - section for function save is expected to be save in /component/component_list.php
        \-- get_ready - section for function get_ready is expected to be set and get in /component/component_list.php
    \-- db_check
        \-- db_check - section for function db_check not yet defined that it should be main in /db/db_check.php
        \-- db_upgrade_0_0_3 - section for function db_upgrade_0_0_3 not yet defined that it should be main in /db/db_check.php
        \-- db_move_time_phrase_to_group - section for function db_move_time_phrase_to_group not yet defined that it should be main in /db/db_check.php
        \-- db_upgrade_0_0_4 - section for function db_upgrade_0_0_4 not yet defined that it should be main in /db/db_check.php
    \-- sql_creator
        \-- db_type - section for function db_type not yet defined that it should be set and get in /db/sql_creator.php
        \-- par_list - section for function par_list not yet defined that it should be set and get in /db/sql_creator.php
        \-- par_values - section for function par_values not yet defined that it should be set and get in /db/sql_creator.php
        \-- table_id - section for function table_id not yet defined that it should be set and get in /db/sql_creator.php
        \-- sql_par - section for function sql_par not yet defined that it should be basic interface function for the private class parameter in /db/sql_creator.php
        \-- set_class - section for function set_class is expected to be set and get in /db/sql_creator.php
        \-- set_name - section for function set_name is expected to be set and get in /db/sql_creator.php
        \-- name - section for function name not yet defined that it should be basic interface function for the private class parameter in /db/sql_creator.php
        \-- set_usr - section for function set_usr is expected to be set and get in /db/sql_creator.php
        \-- set_fields - section for function set_fields is expected to be set and get in /db/sql_creator.php
        \-- set_fields_dummy - section for function set_fields_dummy is expected to be set and get in /db/sql_creator.php
        \-- set_fields_num_dummy - section for function set_fields_num_dummy is expected to be set and get in /db/sql_creator.php
        \-- set_fields_date_dummy - section for function set_fields_date_dummy is expected to be set and get in /db/sql_creator.php
        \-- set_usr_query - section for function set_usr_query is expected to be set and get in /db/sql_creator.php
        \-- set_join_sql - section for function set_join_sql is expected to be set and get in /db/sql_creator.php
        \-- set_grp_query - section for function set_grp_query is expected to be set and get in /db/sql_creator.php
        \-- set_usr_fields - section for function set_usr_fields is expected to be set and get in /db/sql_creator.php
        \-- set_usr_num_fields - section for function set_usr_num_fields is expected to be set and get in /db/sql_creator.php
        \-- set_usr_geo_fields - section for function set_usr_geo_fields is expected to be set and get in /db/sql_creator.php
        \-- set_usr_only_fields - section for function set_usr_only_fields is expected to be set and get in /db/sql_creator.php
        \-- set_order_text - section for function set_order_text is expected to be set and get in /db/sql_creator.php
        \-- set_join_fields - section for function set_join_fields is expected to be set and get in /db/sql_creator.php
        \-- set_join_usr_fields - section for function set_join_usr_fields is expected to be set and get in /db/sql_creator.php
        \-- set_join_usr_num_fields - section for function set_join_usr_num_fields is expected to be set and get in /db/sql_creator.php
        \-- set_join_usr_geo_fields - section for function set_join_usr_geo_fields is expected to be set and get in /db/sql_creator.php
        \-- add_where_fvt - section for function add_where_fvt not yet defined that it should be where in /db/sql_creator.php
        \-- add_where - section for function add_where not yet defined that it should be where in /db/sql_creator.php
        \-- add_where_par - section for function add_where_par not yet defined that it should be where in /db/sql_creator.php
        \-- add_where_no_par - section for function add_where_no_par not yet defined that it should be where in /db/sql_creator.php
        \-- sql - section for function sql not yet defined that it should be statement in /db/sql_creator.php
        \-- create_sql_insert - section for function create_sql_insert not yet defined that it should be statement in /db/sql_creator.php
        \-- create_sql_update - section for function create_sql_update not yet defined that it should be statement in /db/sql_creator.php
        \-- create_sql_update_fvt - section for function create_sql_update_fvt not yet defined that it should be statement in /db/sql_creator.php
        \-- sql_func_start - section for function sql_func_start not yet defined that it should be statement in /db/sql_creator.php
        \-- sql_func_end - section for function sql_func_end not yet defined that it should be statement in /db/sql_creator.php
        \-- sql_func_log - section for function sql_func_log not yet defined that it should be statement in /db/sql_creator.php
        \-- sql_func_log_update - section for function sql_func_log_update not yet defined that it should be statement in /db/sql_creator.php
        \-- sql_func_log_link - section for function sql_func_log_link not yet defined that it should be statement in /db/sql_creator.php
        \-- sql_func_log_user_link - section for function sql_func_log_user_link not yet defined that it should be statement in /db/sql_creator.php
        \-- sql_func_log_value - section for function sql_func_log_value not yet defined that it should be statement in /db/sql_creator.php
        \-- sql_call - section for function sql_call not yet defined that it should be statement in /db/sql_creator.php
        \-- var_name_row_id - section for function var_name_row_id not yet defined that it should be statement in /db/sql_creator.php
        \-- var_name_new_id - section for function var_name_new_id not yet defined that it should be statement in /db/sql_creator.php
        \-- create_sql_delete_fvt - section for function create_sql_delete_fvt not yet defined that it should be statement in /db/sql_creator.php
        \-- create_sql_delete_fvt_new - section for function create_sql_delete_fvt_new not yet defined that it should be statement in /db/sql_creator.php
        \-- create_sql_delete - section for function create_sql_delete not yet defined that it should be statement in /db/sql_creator.php
        \-- add_usr_grp_field - section for function add_usr_grp_field not yet defined that it should be statement in /db/sql_creator.php
        \-- get_order - section for function get_order is expected to be set and get in /db/sql_creator.php
        \-- set_order - section for function set_order is expected to be set and get in /db/sql_creator.php
        \-- set_page - section for function set_page is expected to be set and get in /db/sql_creator.php
        \-- get_page - section for function get_page is expected to be set and get in /db/sql_creator.php
        \-- par_count - section for function par_count not yet defined that it should be internal where in /db/sql_creator.php
        \-- prepare_sql - section for function prepare_sql not yet defined that it should be internal where in /db/sql_creator.php
        \-- table_create - section for function table_create not yet defined that it should be internal where in /db/sql_creator.php
        \-- index_create - section for function index_create not yet defined that it should be internal where in /db/sql_creator.php
        \-- foreign_key_create - section for function foreign_key_create not yet defined that it should be internal where in /db/sql_creator.php
        \-- sql_separator - section for function sql_separator not yet defined that it should be internal where in /db/sql_creator.php
        \-- sql_view_header - section for function sql_view_header not yet defined that it should be internal where in /db/sql_creator.php
        \-- get_par - section for function get_par is expected to be set and get in /db/sql_creator.php
        \-- get_par_types - section for function get_par_types is expected to be set and get in /db/sql_creator.php
        \-- count_qp - section for function count_qp not yet defined that it should be internal where in /db/sql_creator.php
        \-- count_sql - section for function count_sql not yet defined that it should be internal where in /db/sql_creator.php
        \-- move_where_to_sub - section for function move_where_to_sub not yet defined that it should be internal where in /db/sql_creator.php
        \-- select_by_id_not_owner - section for function select_by_id_not_owner not yet defined that it should be internal where in /db/sql_creator.php
        \-- name_sql_esc - section for function name_sql_esc not yet defined that it should be public sql helpers in /db/sql_creator.php
        \-- get_id_field_name - section for function get_id_field_name is expected to be set and get in /db/sql_creator.php
        \-- get_table - section for function get_table is expected to be set and get in /db/sql_creator.php
        \-- get_table_name - section for function get_table_name is expected to be set and get in /db/sql_creator.php
        \-- fix_table_name - section for function fix_table_name not yet defined that it should be final exception correction of the table name in /db/sql_creator.php
        \-- par_name - section for function par_name not yet defined that it should be final exception correction of the table name in /db/sql_creator.php
        \-- par_value - section for function par_value not yet defined that it should be final exception correction of the table name in /db/sql_creator.php
        \-- par_type_to_postgres - section for function par_type_to_postgres not yet defined that it should be final exception correction of the table name in /db/sql_creator.php
        \-- load_sql_not_changed_multi - section for function load_sql_not_changed_multi is expected to be load sql in /db/sql_creator.php
        \-- load_sql_not_changed - section for function load_sql_not_changed is expected to be load sql in /db/sql_creator.php
        \-- del_sql_list_without_log - section for function del_sql_list_without_log is expected to be del in /db/sql_creator.php
        \-- sql_all - section for function sql_all not yet defined that it should be final exception correction of the table name in /db/sql_creator.php
        \-- get_usr_field - section for function get_usr_field is expected to be set and get in /db/sql_creator.php
        \-- create_db_role - section for function create_db_role not yet defined that it should be db roles in /db/sql_creator.php
        \-- class_to_name - section for function class_to_name not yet defined that it should be internal helper in /db/sql_creator.php
        \-- is_user - section for function is_user not yet defined that it should be internal helper in /db/sql_creator.php
        \-- is_MySQL - section for function is_MySQL not yet defined that it should be internal helper in /db/sql_creator.php
        \-- id_field - section for function id_field not yet defined that it should be internal helper in /db/sql_creator.php
        \-- id_field_name - section for function id_field_name not yet defined that it should be internal helper in /db/sql_creator.php
        \-- get_fields - section for function get_fields is expected to be set and get in /db/sql_creator.php
        \-- get_values - section for function get_values is expected to be set and get in /db/sql_creator.php
        \-- get_types - section for function get_types is expected to be set and get in /db/sql_creator.php
    \-- sql_db
        \-- __construct - section for function __construct is expected to be construct and map in /db/sql_db.php
        \-- retry_delay - section for function retry_delay not yet defined that it should be set and get in /db/sql_db.php
        \-- open - section for function open not yet defined that it should be open/close the connection to MySQL in /db/sql_db.php
        \-- open_via_db_admin - section for function open_via_db_admin not yet defined that it should be open/close the connection to MySQL in /db/sql_db.php
        \-- is_open - section for function is_open not yet defined that it should be open/close the connection to MySQL in /db/sql_db.php
        \-- open_with_retry - section for function open_with_retry not yet defined that it should be open/close the connection to MySQL in /db/sql_db.php
        \-- close - section for function close not yet defined that it should be open/close the connection to MySQL in /db/sql_db.php
        \-- setup - section for function setup is expected to be set and get in /db/sql_db.php
        \-- setup_db_zukunft_user_via_db_admin - section for function setup_db_zukunft_user_via_db_admin is expected to be set and get in /db/sql_db.php
        \-- setup_db - section for function setup_db is expected to be set and get in /db/sql_db.php
        \-- reset_db_core - section for function reset_db_core not yet defined that it should be open/close the connection to MySQL in /db/sql_db.php
        \-- run_db_truncate - section for function run_db_truncate not yet defined that it should be open/close the connection to MySQL in /db/sql_db.php
        \-- truncate_cache - section for function truncate_cache not yet defined that it should be open/close the connection to MySQL in /db/sql_db.php
        \-- connected - section for function connected not yet defined that it should be open/close the connection to MySQL in /db/sql_db.php
        \-- db_check_missing_owner - section for function db_check_missing_owner not yet defined that it should be open/close the connection to MySQL in /db/sql_db.php
        \-- db_fill_code_links - section for function db_fill_code_links not yet defined that it should be open/close the connection to MySQL in /db/sql_db.php
        \-- db_log_code_links - section for function db_log_code_links not yet defined that it should be open/close the connection to MySQL in /db/sql_db.php
        \-- load_db_code_link_file - section for function load_db_code_link_file is expected to be load in /db/sql_db.php
        \-- db_fill_code_link_sql - section for function db_fill_code_link_sql not yet defined that it should be open/close the connection to MySQL in /db/sql_db.php
        \-- set_class - section for function set_class is expected to be set and get in /db/sql_db.php
        \-- get_class - section for function get_class is expected to be set and get in /db/sql_db.php
        \-- set_usr - section for function set_usr is expected to be set and get in /db/sql_db.php
        \-- add_par - section for function add_par not yet defined that it should be basic interface function for the private class parameter in /db/sql_db.php
        \-- par_name - section for function par_name not yet defined that it should be basic interface function for the private class parameter in /db/sql_db.php
        \-- par_value - section for function par_value not yet defined that it should be basic interface function for the private class parameter in /db/sql_db.php
        \-- set_name - section for function set_name is expected to be set and get in /db/sql_db.php
        \-- set_fields - section for function set_fields is expected to be set and get in /db/sql_db.php
        \-- set_link_fields - section for function set_link_fields is expected to be set and get in /db/sql_db.php
        \-- set_join_fields - section for function set_join_fields is expected to be set and get in /db/sql_db.php
        \-- set_join_usr_fields - section for function set_join_usr_fields is expected to be set and get in /db/sql_db.php
        \-- set_join_usr_num_fields - section for function set_join_usr_num_fields is expected to be set and get in /db/sql_db.php
        \-- set_join_usr_count_fields - section for function set_join_usr_count_fields is expected to be set and get in /db/sql_db.php
        \-- class_to_name - section for function class_to_name not yet defined that it should be basic interface function for the private class parameter in /db/sql_db.php
        \-- set_all - section for function set_all is expected to be set and get in /db/sql_db.php
        \-- set_usr_fields - section for function set_usr_fields is expected to be set and get in /db/sql_db.php
        \-- set_usr_num_fields - section for function set_usr_num_fields is expected to be set and get in /db/sql_db.php
        \-- set_usr_count_fields - section for function set_usr_count_fields is expected to be set and get in /db/sql_db.php
        \-- set_usr_bool_fields - section for function set_usr_bool_fields is expected to be set and get in /db/sql_db.php
        \-- set_usr_only_fields - section for function set_usr_only_fields is expected to be set and get in /db/sql_db.php
        \-- get_usr_field - section for function get_usr_field is expected to be set and get in /db/sql_db.php
        \-- get_table_name - section for function get_table_name is expected to be set and get in /db/sql_db.php
        \-- get_table_name_esc - section for function get_table_name_esc is expected to be set and get in /db/sql_db.php
        \-- get_id_field_name - section for function get_id_field_name is expected to be set and get in /db/sql_db.php
        \-- set_id_field - section for function set_id_field is expected to be set and get in /db/sql_db.php
        \-- get_name_field - section for function get_name_field is expected to be set and get in /db/sql_db.php
        \-- exe_try - section for function exe_try not yet defined that it should be the main database call function including an automatic error tracking in /db/sql_db.php
        \-- exe_par - section for function exe_par not yet defined that it should be the main database call function including an automatic error tracking in /db/sql_db.php
        \-- exe - section for function exe not yet defined that it should be the main database call function including an automatic error tracking in /db/sql_db.php
        \-- exe_script - section for function exe_script not yet defined that it should be the main database call function including an automatic error tracking in /db/sql_db.php
        \-- exe_mysql - section for function exe_mysql not yet defined that it should be the main database call function including an automatic error tracking in /db/sql_db.php
        \-- has_query - section for function has_query not yet defined that it should be the main database call function including an automatic error tracking in /db/sql_db.php
        \-- mysql_array_to_types - section for function mysql_array_to_types not yet defined that it should be the main database call function including an automatic error tracking in /db/sql_db.php
        \-- fetch - section for function fetch not yet defined that it should be the main database call function including an automatic error tracking in /db/sql_db.php
        \-- fetch_first - section for function fetch_first not yet defined that it should be the main database call function including an automatic error tracking in /db/sql_db.php
        \-- fetch_all - section for function fetch_all not yet defined that it should be the main database call function including an automatic error tracking in /db/sql_db.php
        \-- debug_msg - section for function debug_msg not yet defined that it should be the main database call function including an automatic error tracking in /db/sql_db.php
        \-- get_old - section for function get_old is expected to be set and get in /db/sql_db.php
        \-- get - section for function get not yet defined that it should be the main database call function including an automatic error tracking in /db/sql_db.php
        \-- get_internal - section for function get_internal is expected to be set and get in /db/sql_db.php
        \-- get1_internal - section for function get1_internal not yet defined that it should be the main database call function including an automatic error tracking in /db/sql_db.php
        \-- get1 - section for function get1 not yet defined that it should be the main database call function including an automatic error tracking in /db/sql_db.php
        \-- get1_int - section for function get1_int not yet defined that it should be the main database call function including an automatic error tracking in /db/sql_db.php
        \-- get_value - section for function get_value is expected to be set and get in /db/sql_db.php
        \-- get_value_2key - section for function get_value_2key is expected to be set and get in /db/sql_db.php
        \-- get_id - section for function get_id is expected to be set and get in /db/sql_db.php
        \-- get_name - section for function get_name is expected to be set and get in /db/sql_db.php
        \-- get_id_2key - section for function get_id_2key is expected to be set and get in /db/sql_db.php
        \-- sql_std_lst_usr - section for function sql_std_lst_usr not yet defined that it should be the main database call function including an automatic error tracking in /db/sql_db.php
        \-- sql_std_lst - section for function sql_std_lst not yet defined that it should be the main database call function including an automatic error tracking in /db/sql_db.php
        \-- where_par - section for function where_par not yet defined that it should be the main database call function including an automatic error tracking in /db/sql_db.php
        \-- set_where_id - section for function set_where_id is expected to be set and get in /db/sql_db.php
        \-- set_where_name - section for function set_where_name is expected to be set and get in /db/sql_db.php
        \-- set_where_link_no_fld - section for function set_where_link_no_fld is expected to be set and get in /db/sql_db.php
        \-- set_where_std - section for function set_where_std is expected to be set and get in /db/sql_db.php
        \-- set_where - section for function set_where is expected to be set and get in /db/sql_db.php
        \-- get_where - section for function get_where is expected to be set and get in /db/sql_db.php
        \-- set_where_text - section for function set_where_text is expected to be set and get in /db/sql_db.php
        \-- set_order - section for function set_order is expected to be set and get in /db/sql_db.php
        \-- set_order_text - section for function set_order_text is expected to be set and get in /db/sql_db.php
        \-- set_page_par - section for function set_page_par is expected to be set and get in /db/sql_db.php
        \-- set_user_join - section for function set_user_join is expected to be set and get in /db/sql_db.php
        \-- set_from - section for function set_from is expected to be set and get in /db/sql_db.php
        \-- get_par - section for function get_par is expected to be set and get in /db/sql_db.php
        \-- select_all - section for function select_all not yet defined that it should be because the object name can be user specific, in /db/sql_db.php
        \-- select_by_set_id - section for function select_by_set_id not yet defined that it should be because the object name can be user specific, in /db/sql_db.php
        \-- select_by_code_id - section for function select_by_code_id not yet defined that it should be because the object name can be user specific, in /db/sql_db.php
        \-- select_by_field - section for function select_by_field not yet defined that it should be because the object name can be user specific, in /db/sql_db.php
        \-- select_by_field_list - section for function select_by_field_list not yet defined that it should be because the object name can be user specific, in /db/sql_db.php
        \-- select_by_id_not_owner - section for function select_by_id_not_owner not yet defined that it should be because the object name can be user specific, in /db/sql_db.php
        \-- select_value_by_id_not_owner - section for function select_value_by_id_not_owner not yet defined that it should be because the object name can be user specific, in /db/sql_db.php
        \-- select_union - section for function select_union not yet defined that it should be because the object name can be user specific, in /db/sql_db.php
        \-- count - section for function count not yet defined that it should be because the object name can be user specific, in /db/sql_db.php
        \-- par_types_to_postgres - section for function par_types_to_postgres not yet defined that it should be because the object name can be user specific, in /db/sql_db.php
        \-- prepare_sql - section for function prepare_sql not yet defined that it should be because the object name can be user specific, in /db/sql_db.php
        \-- end_sql - section for function end_sql not yet defined that it should be because the object name can be user specific, in /db/sql_db.php
        \-- select_by - section for function select_by not yet defined that it should be because the object name can be user specific, in /db/sql_db.php
        \-- missing_owner_sql - section for function missing_owner_sql not yet defined that it should be because the object name can be user specific, in /db/sql_db.php
        \-- missing_owner - section for function missing_owner not yet defined that it should be because the object name can be user specific, in /db/sql_db.php
        \-- set_default_owner - section for function set_default_owner is expected to be set and get in /db/sql_db.php
        \-- insert - section for function insert not yet defined that it should be because the object name can be user specific, in /db/sql_db.php
        \-- update - section for function update not yet defined that it should be because the object name can be user specific, in /db/sql_db.php
        \-- delete - section for function delete is expected to be del in /db/sql_db.php
        \-- insert_old - section for function insert_old not yet defined that it should be because the object name can be user specific, in /db/sql_db.php
        \-- add_id - section for function add_id not yet defined that it should be because the object name can be user specific, in /db/sql_db.php
        \-- add_id_2key - section for function add_id_2key not yet defined that it should be because the object name can be user specific, in /db/sql_db.php
        \-- update_old - section for function update_old not yet defined that it should be because the object name can be user specific, in /db/sql_db.php
        \-- update_name - section for function update_name not yet defined that it should be because the object name can be user specific, in /db/sql_db.php
        \-- delete_old - section for function delete_old is expected to be del in /db/sql_db.php
        \-- name_sql_esc - section for function name_sql_esc not yet defined that it should be private supporting functions in /db/sql_db.php
        \-- sf - section for function sf not yet defined that it should be private supporting functions in /db/sql_db.php
        \-- postgres_format - section for function postgres_format not yet defined that it should be private supporting functions in /db/sql_db.php
        \-- mysqli_format - section for function mysqli_format not yet defined that it should be private supporting functions in /db/sql_db.php
        \-- sql_escape - section for function sql_escape not yet defined that it should be private supporting functions in /db/sql_db.php
        \-- seq_reset - section for function seq_reset not yet defined that it should be private supporting functions in /db/sql_db.php
        \-- has_table - section for function has_table not yet defined that it should be private supporting functions in /db/sql_db.php
        \-- has_column - section for function has_column not yet defined that it should be private supporting functions in /db/sql_db.php
        \-- get_tables - section for function get_tables is expected to be set and get in /db/sql_db.php
        \-- get_fields - section for function get_fields is expected to be set and get in /db/sql_db.php
        \-- get_functions - section for function get_functions is expected to be set and get in /db/sql_db.php
        \-- resource_file - section for function resource_file not yet defined that it should be private supporting functions in /db/sql_db.php
        \-- has_key - section for function has_key not yet defined that it should be private supporting functions in /db/sql_db.php
        \-- add_foreign_key - section for function add_foreign_key not yet defined that it should be private supporting functions in /db/sql_db.php
        \-- add_column - section for function add_column not yet defined that it should be private supporting functions in /db/sql_db.php
        \-- del_field - section for function del_field is expected to be del in /db/sql_db.php
        \-- change_column_name - section for function change_column_name not yet defined that it should be private supporting functions in /db/sql_db.php
        \-- change_table_name - section for function change_table_name not yet defined that it should be private supporting functions in /db/sql_db.php
        \-- column_allow_null - section for function column_allow_null not yet defined that it should be private supporting functions in /db/sql_db.php
        \-- column_force_not_null - section for function column_force_not_null not yet defined that it should be private supporting functions in /db/sql_db.php
        \-- remove_prefix_sql - section for function remove_prefix_sql not yet defined that it should be private supporting functions in /db/sql_db.php
        \-- remove_prefix - section for function remove_prefix not yet defined that it should be private supporting functions in /db/sql_db.php
        \-- change_code_id - section for function change_code_id not yet defined that it should be private supporting functions in /db/sql_db.php
        \-- get_column_names - section for function get_column_names is expected to be set and get in /db/sql_db.php
        \-- check_column_names - section for function check_column_names not yet defined that it should be private supporting functions in /db/sql_db.php
        \-- sql_setup_header - section for function sql_setup_header not yet defined that it should be private supporting functions in /db/sql_db.php
        \-- sql_setup_footer - section for function sql_setup_footer not yet defined that it should be private supporting functions in /db/sql_db.php
        \-- sql_separator_index - section for function sql_separator_index not yet defined that it should be private supporting functions in /db/sql_db.php
        \-- sql_separator_foreign_key - section for function sql_separator_foreign_key not yet defined that it should be private supporting functions in /db/sql_db.php
        \-- sql_creator - section for function sql_creator not yet defined that it should be private supporting functions in /db/sql_db.php
        \-- path - section for function path not yet defined that it should be private supporting functions in /db/sql_db.php
        \-- ext - section for function ext not yet defined that it should be private supporting functions in /db/sql_db.php
        \-- truncate_table_all - section for function truncate_table_all not yet defined that it should be private supporting functions in /db/sql_db.php
        \-- truncate_table - section for function truncate_table not yet defined that it should be private supporting functions in /db/sql_db.php
        \-- drop_table - section for function drop_table not yet defined that it should be private supporting functions in /db/sql_db.php
        \-- reset_seq_all - section for function reset_seq_all not yet defined that it should be private supporting functions in /db/sql_db.php
        \-- reset_seq - section for function reset_seq not yet defined that it should be private supporting functions in /db/sql_db.php
        \-- load_user_profiles - section for function load_user_profiles is expected to be load in /db/sql_db.php
        \-- reset_config - section for function reset_config not yet defined that it should be private supporting functions in /db/sql_db.php
        \-- import_system_users - section for function import_system_users not yet defined that it should be private supporting functions in /db/sql_db.php
        \-- import_verbs - section for function import_verbs not yet defined that it should be private supporting functions in /db/sql_db.php
        \-- create_internal_words - section for function create_internal_words not yet defined that it should be private supporting functions in /db/sql_db.php
        \-- import_system_views - section for function import_system_views not yet defined that it should be private supporting functions in /db/sql_db.php
        \-- csv_from_class - section for function csv_from_class not yet defined that it should be private supporting functions in /db/sql_db.php
        \-- order error - order of section basic interface function for the private class parameter has difference at set_name should be after set_usr, set_usr_fields should be after set_join_usr_num_fields, set_usr_num_fields should be after set_join_usr_num_fields, set_usr_only_fields should be after set_join_usr_num_fields of set_class,get_class,set_usr,add_par,par_name,par_value,set_name,set_fields,set_link_fields,set_join_fields,set_join_usr_fields,set_join_usr_num_fields,set_join_usr_count_fields,class_to_name,set_all,set_usr_fields,set_usr_num_fields,set_usr_count_fields,set_usr_bool_fields,set_usr_only_fields,get_usr_field,get_table_name does not match sql_par,set_class,set_name,name,set_usr,set_fields,set_fields_dummy,set_fields_num_dummy,set_fields_date_dummy,set_usr_query,set_join_sql,set_grp_query,set_usr_fields,set_usr_num_fields,set_usr_geo_fields,set_usr_only_fields,set_order_text,set_join_fields,set_join_usr_fields,set_join_usr_num_fields,set_join_usr_geo_fields,order of section basic interface function for the private class parameter has difference at set_name should be after set_usr, set_usr_fields should be after set_join_usr_num_fields, set_usr_num_fields should be after set_join_usr_num_fields, set_usr_only_fields should be after set_join_usr_num_fields of set_class,get_class,set_usr,add_par,par_name,par_value,set_name,set_fields,set_link_fields,set_join_fields,set_join_usr_fields,set_join_usr_num_fields,set_join_usr_count_fields,class_to_name,set_all,set_usr_fields,set_usr_num_fields,set_usr_count_fields,set_usr_bool_fields,set_usr_only_fields,get_usr_field,get_table_name does not match sql_par,set_class,set_name,name,set_usr,set_fields,set_fields_dummy,set_fields_num_dummy,set_fields_date_dummy,set_usr_query,set_join_sql,set_grp_query,set_usr_fields,set_usr_num_fields,set_usr_geo_fields,set_usr_only_fields,set_order_text,set_join_fields,set_join_usr_fields,set_join_usr_num_fields,set_join_usr_geo_fields,order of section basic interface function for the private class parameter has difference at set_name should be after set_usr, set_usr_fields should be after set_join_usr_num_fields, set_usr_num_fields should be after set_join_usr_num_fields, set_usr_only_fields should be after set_join_usr_num_fields of set_class,get_class,set_usr,add_par,par_name,par_value,set_name,set_fields,set_link_fields,set_join_fields,set_join_usr_fields,set_join_usr_num_fields,set_join_usr_count_fields,class_to_name,set_all,set_usr_fields,set_usr_num_fields,set_usr_count_fields,set_usr_bool_fields,set_usr_only_fields,get_usr_field,get_table_name does not match sql_par,set_class,set_name,name,set_usr,set_fields,set_fields_dummy,set_fields_num_dummy,set_fields_date_dummy,set_usr_query,set_join_sql,set_grp_query,set_usr_fields,set_usr_num_fields,set_usr_geo_fields,set_usr_only_fields,set_order_text,set_join_fields,set_join_usr_fields,set_join_usr_num_fields,set_join_usr_geo_fields,order of section basic interface function for the private class parameter has difference at set_name should be after set_usr, set_usr_fields should be after set_join_usr_num_fields, set_usr_num_fields should be after set_join_usr_num_fields, set_usr_only_fields should be after set_join_usr_num_fields of set_class,get_class,set_usr,add_par,par_name,par_value,set_name,set_fields,set_link_fields,set_join_fields,set_join_usr_fields,set_join_usr_num_fields,set_join_usr_count_fields,class_to_name,set_all,set_usr_fields,set_usr_num_fields,set_usr_count_fields,set_usr_bool_fields,set_usr_only_fields,get_usr_field,get_table_name does not match sql_par,set_class,set_name,name,set_usr,set_fields,set_fields_dummy,set_fields_num_dummy,set_fields_date_dummy,set_usr_query,set_join_sql,set_grp_query,set_usr_fields,set_usr_num_fields,set_usr_geo_fields,set_usr_only_fields,set_order_text,set_join_fields,set_join_usr_fields,set_join_usr_num_fields,set_join_usr_geo_fields,order of section basic interface function for the private class parameter has difference at set_name should be after set_usr, set_usr_fields should be after set_join_usr_num_fields, set_usr_num_fields should be after set_join_usr_num_fields, set_usr_only_fields should be after set_join_usr_num_fields of set_class,get_class,set_usr,add_par,par_name,par_value,set_name,set_fields,set_link_fields,set_join_fields,set_join_usr_fields,set_join_usr_num_fields,set_join_usr_count_fields,class_to_name,set_all,set_usr_fields,set_usr_num_fields,set_usr_count_fields,set_usr_bool_fields,set_usr_only_fields,get_usr_field,get_table_name does not match sql_par,set_class,set_name,name,set_usr,set_fields,set_fields_dummy,set_fields_num_dummy,set_fields_date_dummy,set_usr_query,set_join_sql,set_grp_query,set_usr_fields,set_usr_num_fields,set_usr_geo_fields,set_usr_only_fields,set_order_text,set_join_fields,set_join_usr_fields,set_join_usr_num_fields,set_join_usr_geo_fields,order of section basic interface function for the private class parameter has difference at set_name should be after set_usr, set_usr_fields should be after set_join_usr_num_fields, set_usr_num_fields should be after set_join_usr_num_fields, set_usr_only_fields should be after set_join_usr_num_fields of set_class,get_class,set_usr,add_par,par_name,par_value,set_name,set_fields,set_link_fields,set_join_fields,set_join_usr_fields,set_join_usr_num_fields,set_join_usr_count_fields,class_to_name,set_all,set_usr_fields,set_usr_num_fields,set_usr_count_fields,set_usr_bool_fields,set_usr_only_fields,get_usr_field,get_table_name does not match sql_par,set_class,set_name,name,set_usr,set_fields,set_fields_dummy,set_fields_num_dummy,set_fields_date_dummy,set_usr_query,set_join_sql,set_grp_query,set_usr_fields,set_usr_num_fields,set_usr_geo_fields,set_usr_only_fields,set_order_text,set_join_fields,set_join_usr_fields,set_join_usr_num_fields,set_join_usr_geo_fields,order of section basic interface function for the private class parameter has difference at set_name should be after set_usr, set_usr_fields should be after set_join_usr_num_fields, set_usr_num_fields should be after set_join_usr_num_fields, set_usr_only_fields should be after set_join_usr_num_fields of set_class,get_class,set_usr,add_par,par_name,par_value,set_name,set_fields,set_link_fields,set_join_fields,set_join_usr_fields,set_join_usr_num_fields,set_join_usr_count_fields,class_to_name,set_all,set_usr_fields,set_usr_num_fields,set_usr_count_fields,set_usr_bool_fields,set_usr_only_fields,get_usr_field,get_table_name does not match sql_par,set_class,set_name,name,set_usr,set_fields,set_fields_dummy,set_fields_num_dummy,set_fields_date_dummy,set_usr_query,set_join_sql,set_grp_query,set_usr_fields,set_usr_num_fields,set_usr_geo_fields,set_usr_only_fields,set_order_text,set_join_fields,set_join_usr_fields,set_join_usr_num_fields,set_join_usr_geo_fields,order of section basic interface function for the private class parameter has difference at set_name should be after set_usr, set_usr_fields should be after set_join_usr_num_fields, set_usr_num_fields should be after set_join_usr_num_fields, set_usr_only_fields should be after set_join_usr_num_fields of set_class,get_class,set_usr,add_par,par_name,par_value,set_name,set_fields,set_link_fields,set_join_fields,set_join_usr_fields,set_join_usr_num_fields,set_join_usr_count_fields,class_to_name,set_all,set_usr_fields,set_usr_num_fields,set_usr_count_fields,set_usr_bool_fields,set_usr_only_fields,get_usr_field,get_table_name does not match sql_par,set_class,set_name,name,set_usr,set_fields,set_fields_dummy,set_fields_num_dummy,set_fields_date_dummy,set_usr_query,set_join_sql,set_grp_query,set_usr_fields,set_usr_num_fields,set_usr_geo_fields,set_usr_only_fields,set_order_text,set_join_fields,set_join_usr_fields,set_join_usr_num_fields,set_join_usr_geo_fields,order of section basic interface function for the private class parameter has difference at set_name should be after set_usr, set_usr_fields should be after set_join_usr_num_fields, set_usr_num_fields should be after set_join_usr_num_fields, set_usr_only_fields should be after set_join_usr_num_fields of set_class,get_class,set_usr,add_par,par_name,par_value,set_name,set_fields,set_link_fields,set_join_fields,set_join_usr_fields,set_join_usr_num_fields,set_join_usr_count_fields,class_to_name,set_all,set_usr_fields,set_usr_num_fields,set_usr_count_fields,set_usr_bool_fields,set_usr_only_fields,get_usr_field,get_table_name does not match sql_par,set_class,set_name,name,set_usr,set_fields,set_fields_dummy,set_fields_num_dummy,set_fields_date_dummy,set_usr_query,set_join_sql,set_grp_query,set_usr_fields,set_usr_num_fields,set_usr_geo_fields,set_usr_only_fields,set_order_text,set_join_fields,set_join_usr_fields,set_join_usr_num_fields,set_join_usr_geo_fields,order of section basic interface function for the private class parameter has difference at set_name should be after set_usr, set_usr_fields should be after set_join_usr_num_fields, set_usr_num_fields should be after set_join_usr_num_fields, set_usr_only_fields should be after set_join_usr_num_fields of set_class,get_class,set_usr,add_par,par_name,par_value,set_name,set_fields,set_link_fields,set_join_fields,set_join_usr_fields,set_join_usr_num_fields,set_join_usr_count_fields,class_to_name,set_all,set_usr_fields,set_usr_num_fields,set_usr_count_fields,set_usr_bool_fields,set_usr_only_fields,get_usr_field,get_table_name does not match sql_par,set_class,set_name,name,set_usr,set_fields,set_fields_dummy,set_fields_num_dummy,set_fields_date_dummy,set_usr_query,set_join_sql,set_grp_query,set_usr_fields,set_usr_num_fields,set_usr_geo_fields,set_usr_only_fields,set_order_text,set_join_fields,set_join_usr_fields,set_join_usr_num_fields,set_join_usr_geo_fields,order of section basic interface function for the private class parameter has difference at set_name should be after set_usr, set_usr_fields should be after set_join_usr_num_fields, set_usr_num_fields should be after set_join_usr_num_fields, set_usr_only_fields should be after set_join_usr_num_fields of set_class,get_class,set_usr,add_par,par_name,par_value,set_name,set_fields,set_link_fields,set_join_fields,set_join_usr_fields,set_join_usr_num_fields,set_join_usr_count_fields,class_to_name,set_all,set_usr_fields,set_usr_num_fields,set_usr_count_fields,set_usr_bool_fields,set_usr_only_fields,get_usr_field,get_table_name does not match sql_par,set_class,set_name,name,set_usr,set_fields,set_fields_dummy,set_fields_num_dummy,set_fields_date_dummy,set_usr_query,set_join_sql,set_grp_query,set_usr_fields,set_usr_num_fields,set_usr_geo_fields,set_usr_only_fields,set_order_text,set_join_fields,set_join_usr_fields,set_join_usr_num_fields,set_join_usr_geo_fields,order of section basic interface function for the private class parameter has difference at set_name should be after set_usr, set_usr_fields should be after set_join_usr_num_fields, set_usr_num_fields should be after set_join_usr_num_fields, set_usr_only_fields should be after set_join_usr_num_fields of set_class,get_class,set_usr,add_par,par_name,par_value,set_name,set_fields,set_link_fields,set_join_fields,set_join_usr_fields,set_join_usr_num_fields,set_join_usr_count_fields,class_to_name,set_all,set_usr_fields,set_usr_num_fields,set_usr_count_fields,set_usr_bool_fields,set_usr_only_fields,get_usr_field,get_table_name does not match sql_par,set_class,set_name,name,set_usr,set_fields,set_fields_dummy,set_fields_num_dummy,set_fields_date_dummy,set_usr_query,set_join_sql,set_grp_query,set_usr_fields,set_usr_num_fields,set_usr_geo_fields,set_usr_only_fields,set_order_text,set_join_fields,set_join_usr_fields,set_join_usr_num_fields,set_join_usr_geo_fields,order of section basic interface function for the private class parameter has difference at set_name should be after set_usr, set_usr_fields should be after set_join_usr_num_fields, set_usr_num_fields should be after set_join_usr_num_fields, set_usr_only_fields should be after set_join_usr_num_fields of set_class,get_class,set_usr,add_par,par_name,par_value,set_name,set_fields,set_link_fields,set_join_fields,set_join_usr_fields,set_join_usr_num_fields,set_join_usr_count_fields,class_to_name,set_all,set_usr_fields,set_usr_num_fields,set_usr_count_fields,set_usr_bool_fields,set_usr_only_fields,get_usr_field,get_table_name does not match sql_par,set_class,set_name,name,set_usr,set_fields,set_fields_dummy,set_fields_num_dummy,set_fields_date_dummy,set_usr_query,set_join_sql,set_grp_query,set_usr_fields,set_usr_num_fields,set_usr_geo_fields,set_usr_only_fields,set_order_text,set_join_fields,set_join_usr_fields,set_join_usr_num_fields,set_join_usr_geo_fields,order of section basic interface function for the private class parameter has difference at set_name should be after set_usr, set_usr_fields should be after set_join_usr_num_fields, set_usr_num_fields should be after set_join_usr_num_fields, set_usr_only_fields should be after set_join_usr_num_fields of set_class,get_class,set_usr,add_par,par_name,par_value,set_name,set_fields,set_link_fields,set_join_fields,set_join_usr_fields,set_join_usr_num_fields,set_join_usr_count_fields,class_to_name,set_all,set_usr_fields,set_usr_num_fields,set_usr_count_fields,set_usr_bool_fields,set_usr_only_fields,get_usr_field,get_table_name does not match sql_par,set_class,set_name,name,set_usr,set_fields,set_fields_dummy,set_fields_num_dummy,set_fields_date_dummy,set_usr_query,set_join_sql,set_grp_query,set_usr_fields,set_usr_num_fields,set_usr_geo_fields,set_usr_only_fields,set_order_text,set_join_fields,set_join_usr_fields,set_join_usr_num_fields,set_join_usr_geo_fields,order of section basic interface function for the private class parameter has difference at set_name should be after set_usr, set_usr_fields should be after set_join_usr_num_fields, set_usr_num_fields should be after set_join_usr_num_fields, set_usr_only_fields should be after set_join_usr_num_fields of set_class,get_class,set_usr,add_par,par_name,par_value,set_name,set_fields,set_link_fields,set_join_fields,set_join_usr_fields,set_join_usr_num_fields,set_join_usr_count_fields,class_to_name,set_all,set_usr_fields,set_usr_num_fields,set_usr_count_fields,set_usr_bool_fields,set_usr_only_fields,get_usr_field,get_table_name does not match sql_par,set_class,set_name,name,set_usr,set_fields,set_fields_dummy,set_fields_num_dummy,set_fields_date_dummy,set_usr_query,set_join_sql,set_grp_query,set_usr_fields,set_usr_num_fields,set_usr_geo_fields,set_usr_only_fields,set_order_text,set_join_fields,set_join_usr_fields,set_join_usr_num_fields,set_join_usr_geo_fields,order of section basic interface function for the private class parameter has difference at set_name should be after set_usr, set_usr_fields should be after set_join_usr_num_fields, set_usr_num_fields should be after set_join_usr_num_fields, set_usr_only_fields should be after set_join_usr_num_fields of set_class,get_class,set_usr,add_par,par_name,par_value,set_name,set_fields,set_link_fields,set_join_fields,set_join_usr_fields,set_join_usr_num_fields,set_join_usr_count_fields,class_to_name,set_all,set_usr_fields,set_usr_num_fields,set_usr_count_fields,set_usr_bool_fields,set_usr_only_fields,get_usr_field,get_table_name does not match sql_par,set_class,set_name,name,set_usr,set_fields,set_fields_dummy,set_fields_num_dummy,set_fields_date_dummy,set_usr_query,set_join_sql,set_grp_query,set_usr_fields,set_usr_num_fields,set_usr_geo_fields,set_usr_only_fields,set_order_text,set_join_fields,set_join_usr_fields,set_join_usr_num_fields,set_join_usr_geo_fields,order of section basic interface function for the private class parameter has difference at set_name should be after set_usr, set_usr_fields should be after set_join_usr_num_fields, set_usr_num_fields should be after set_join_usr_num_fields, set_usr_only_fields should be after set_join_usr_num_fields of set_class,get_class,set_usr,add_par,par_name,par_value,set_name,set_fields,set_link_fields,set_join_fields,set_join_usr_fields,set_join_usr_num_fields,set_join_usr_count_fields,class_to_name,set_all,set_usr_fields,set_usr_num_fields,set_usr_count_fields,set_usr_bool_fields,set_usr_only_fields,get_usr_field,get_table_name does not match sql_par,set_class,set_name,name,set_usr,set_fields,set_fields_dummy,set_fields_num_dummy,set_fields_date_dummy,set_usr_query,set_join_sql,set_grp_query,set_usr_fields,set_usr_num_fields,set_usr_geo_fields,set_usr_only_fields,set_order_text,set_join_fields,set_join_usr_fields,set_join_usr_num_fields,set_join_usr_geo_fields,order of section basic interface function for the private class parameter has difference at set_name should be after set_usr, set_usr_fields should be after set_join_usr_num_fields, set_usr_num_fields should be after set_join_usr_num_fields, set_usr_only_fields should be after set_join_usr_num_fields of set_class,get_class,set_usr,add_par,par_name,par_value,set_name,set_fields,set_link_fields,set_join_fields,set_join_usr_fields,set_join_usr_num_fields,set_join_usr_count_fields,class_to_name,set_all,set_usr_fields,set_usr_num_fields,set_usr_count_fields,set_usr_bool_fields,set_usr_only_fields,get_usr_field,get_table_name does not match sql_par,set_class,set_name,name,set_usr,set_fields,set_fields_dummy,set_fields_num_dummy,set_fields_date_dummy,set_usr_query,set_join_sql,set_grp_query,set_usr_fields,set_usr_num_fields,set_usr_geo_fields,set_usr_only_fields,set_order_text,set_join_fields,set_join_usr_fields,set_join_usr_num_fields,set_join_usr_geo_fields,order of section basic interface function for the private class parameter has difference at set_name should be after set_usr, set_usr_fields should be after set_join_usr_num_fields, set_usr_num_fields should be after set_join_usr_num_fields, set_usr_only_fields should be after set_join_usr_num_fields of set_class,get_class,set_usr,add_par,par_name,par_value,set_name,set_fields,set_link_fields,set_join_fields,set_join_usr_fields,set_join_usr_num_fields,set_join_usr_count_fields,class_to_name,set_all,set_usr_fields,set_usr_num_fields,set_usr_count_fields,set_usr_bool_fields,set_usr_only_fields,get_usr_field,get_table_name does not match sql_par,set_class,set_name,name,set_usr,set_fields,set_fields_dummy,set_fields_num_dummy,set_fields_date_dummy,set_usr_query,set_join_sql,set_grp_query,set_usr_fields,set_usr_num_fields,set_usr_geo_fields,set_usr_only_fields,set_order_text,set_join_fields,set_join_usr_fields,set_join_usr_num_fields,set_join_usr_geo_fields,order of section basic interface function for the private class parameter has difference at set_name should be after set_usr, set_usr_fields should be after set_join_usr_num_fields, set_usr_num_fields should be after set_join_usr_num_fields, set_usr_only_fields should be after set_join_usr_num_fields of set_class,get_class,set_usr,add_par,par_name,par_value,set_name,set_fields,set_link_fields,set_join_fields,set_join_usr_fields,set_join_usr_num_fields,set_join_usr_count_fields,class_to_name,set_all,set_usr_fields,set_usr_num_fields,set_usr_count_fields,set_usr_bool_fields,set_usr_only_fields,get_usr_field,get_table_name does not match sql_par,set_class,set_name,name,set_usr,set_fields,set_fields_dummy,set_fields_num_dummy,set_fields_date_dummy,set_usr_query,set_join_sql,set_grp_query,set_usr_fields,set_usr_num_fields,set_usr_geo_fields,set_usr_only_fields,set_order_text,set_join_fields,set_join_usr_fields,set_join_usr_num_fields,set_join_usr_geo_fields,order of section basic interface function for the private class parameter has difference at set_name should be after set_usr, set_usr_fields should be after set_join_usr_num_fields, set_usr_num_fields should be after set_join_usr_num_fields, set_usr_only_fields should be after set_join_usr_num_fields of set_class,get_class,set_usr,add_par,par_name,par_value,set_name,set_fields,set_link_fields,set_join_fields,set_join_usr_fields,set_join_usr_num_fields,set_join_usr_count_fields,class_to_name,set_all,set_usr_fields,set_usr_num_fields,set_usr_count_fields,set_usr_bool_fields,set_usr_only_fields,get_usr_field,get_table_name does not match sql_par,set_class,set_name,name,set_usr,set_fields,set_fields_dummy,set_fields_num_dummy,set_fields_date_dummy,set_usr_query,set_join_sql,set_grp_query,set_usr_fields,set_usr_num_fields,set_usr_geo_fields,set_usr_only_fields,set_order_text,set_join_fields,set_join_usr_fields,set_join_usr_num_fields,set_join_usr_geo_fields,order of section basic interface function for the private class parameter has difference at set_name should be after set_usr, set_usr_fields should be after set_join_usr_num_fields, set_usr_num_fields should be after set_join_usr_num_fields, set_usr_only_fields should be after set_join_usr_num_fields of set_class,get_class,set_usr,add_par,par_name,par_value,set_name,set_fields,set_link_fields,set_join_fields,set_join_usr_fields,set_join_usr_num_fields,set_join_usr_count_fields,class_to_name,set_all,set_usr_fields,set_usr_num_fields,set_usr_count_fields,set_usr_bool_fields,set_usr_only_fields,get_usr_field,get_table_name does not match sql_par,set_class,set_name,name,set_usr,set_fields,set_fields_dummy,set_fields_num_dummy,set_fields_date_dummy,set_usr_query,set_join_sql,set_grp_query,set_usr_fields,set_usr_num_fields,set_usr_geo_fields,set_usr_only_fields,set_order_text,set_join_fields,set_join_usr_fields,set_join_usr_num_fields,set_join_usr_geo_fields
    \-- sql_field_list
        \-- add - section for function add missing in /db/sql_field_list.php
        \-- add_field - section for function add_field missing in /db/sql_field_list.php
        \-- add_value - section for function add_value missing in /db/sql_field_list.php
        \-- add_par_field - section for function add_par_field missing in /db/sql_field_list.php
        \-- add_par_field_id - section for function add_par_field_id missing in /db/sql_field_list.php
        \-- get - section for function get missing in /db/sql_field_list.php
        \-- name - section for function name missing in /db/sql_field_list.php
        \-- value - section for function value missing in /db/sql_field_list.php
        \-- type - section for function type missing in /db/sql_field_list.php
        \-- pos - section for function pos missing in /db/sql_field_list.php
        \-- names - section for function names is expected to be info in /db/sql_field_list.php
        \-- values - section for function values missing in /db/sql_field_list.php
        \-- types - section for function types missing in /db/sql_field_list.php
        \-- has - section for function has missing in /db/sql_field_list.php
        \-- count - section for function count missing in /db/sql_field_list.php
        \-- names_or_const - section for function names_or_const missing in /db/sql_field_list.php
        \-- sql_names - section for function sql_names missing in /db/sql_field_list.php
    \-- sql_par
        \-- __construct - section for function __construct is expected to be construct and map in /db/sql_par.php
        \-- has_par - section for function has_par missing in /db/sql_par.php
        \-- merge - section for function merge missing in /db/sql_par.php
        \-- combine - section for function combine missing in /db/sql_par.php
    \-- sql_par_field_list
        \-- set - section for function set is expected to be set and get in /db/sql_par_field_list.php
        \-- add - section for function add missing in /db/sql_par_field_list.php
        \-- add_with_split - section for function add_with_split missing in /db/sql_par_field_list.php
        \-- add_id_part - section for function add_id_part missing in /db/sql_par_field_list.php
        \-- add_name_part - section for function add_name_part missing in /db/sql_par_field_list.php
        \-- add_list - section for function add_list missing in /db/sql_par_field_list.php
        \-- add_field - section for function add_field missing in /db/sql_par_field_list.php
        \-- add_link_field - section for function add_link_field missing in /db/sql_par_field_list.php
        \-- add_type_field - section for function add_type_field missing in /db/sql_par_field_list.php
        \-- add_id_and_user - section for function add_id_and_user missing in /db/sql_par_field_list.php
        \-- add_user - section for function add_user missing in /db/sql_par_field_list.php
        \-- add_name_and_description - section for function add_name_and_description missing in /db/sql_par_field_list.php
        \-- del - section for function del is expected to be del in /db/sql_par_field_list.php
        \-- fill_from_arrays - section for function fill_from_arrays missing in /db/sql_par_field_list.php
        \-- is_empty - section for function is_empty missing in /db/sql_par_field_list.php
        \-- is_empty_except_internal_fields - section for function is_empty_except_internal_fields missing in /db/sql_par_field_list.php
        \-- names - section for function names is expected to be info in /db/sql_par_field_list.php
        \-- values - section for function values missing in /db/sql_par_field_list.php
        \-- par_names - section for function par_names missing in /db/sql_par_field_list.php
        \-- db_values - section for function db_values missing in /db/sql_par_field_list.php
        \-- types - section for function types missing in /db/sql_par_field_list.php
        \-- intersect - section for function intersect missing in /db/sql_par_field_list.php
        \-- get_intersect - section for function get_intersect is expected to be set and get in /db/sql_par_field_list.php
        \-- get_diff - section for function get_diff is expected to be set and get in /db/sql_par_field_list.php
        \-- get - section for function get missing in /db/sql_par_field_list.php
        \-- get_value - section for function get_value is expected to be set and get in /db/sql_par_field_list.php
        \-- get_id - section for function get_id is expected to be set and get in /db/sql_par_field_list.php
        \-- get_old - section for function get_old is expected to be set and get in /db/sql_par_field_list.php
        \-- get_old_id - section for function get_old_id is expected to be set and get in /db/sql_par_field_list.php
        \-- get_type - section for function get_type is expected to be set and get in /db/sql_par_field_list.php
        \-- get_type_id - section for function get_type_id is expected to be set and get in /db/sql_par_field_list.php
        \-- get_par_name - section for function get_par_name is expected to be set and get in /db/sql_par_field_list.php
        \-- has_name - section for function has_name missing in /db/sql_par_field_list.php
        \-- merge - section for function merge missing in /db/sql_par_field_list.php
        \-- esc_names - section for function esc_names missing in /db/sql_par_field_list.php
        \-- sql_field_list - section for function sql_field_list missing in /db/sql_par_field_list.php
        \-- par_sql - section for function par_sql missing in /db/sql_par_field_list.php
        \-- par_types - section for function par_types missing in /db/sql_par_field_list.php
        \-- par_vars - section for function par_vars missing in /db/sql_par_field_list.php
        \-- sql_par_names - section for function sql_par_names missing in /db/sql_par_field_list.php
        \-- order error - order of section  has difference at get should be after types of set,add,add_with_split,add_id_part,add_name_part,add_list,add_field,add_link_field,add_type_field,add_id_and_user,add_user,add_name_and_description,del,fill_from_arrays,is_empty,is_empty_except_internal_fields,names,values,par_names,db_values,types,intersect,get_intersect,get_diff,get,get_value,get_id,get_old,get_old_id,get_type,get_type_id,get_par_name,has_name,merge,esc_names,sql_field_list,par_sql,par_types,par_vars,sql_par_names does not match start_api_core,start_api,end_api,start,open_db,end,load_dummy,default_id,add,add_field,add_value,add_par_field,add_par_field_id,get,name,value,type,pos,names,values,types,has,count,names_or_const,sql_names,__construct,has_par,merge,combine,order of section  has difference at get should be after types of set,add,add_with_split,add_id_part,add_name_part,add_list,add_field,add_link_field,add_type_field,add_id_and_user,add_user,add_name_and_description,del,fill_from_arrays,is_empty,is_empty_except_internal_fields,names,values,par_names,db_values,types,intersect,get_intersect,get_diff,get,get_value,get_id,get_old,get_old_id,get_type,get_type_id,get_par_name,has_name,merge,esc_names,sql_field_list,par_sql,par_types,par_vars,sql_par_names does not match start_api_core,start_api,end_api,start,open_db,end,load_dummy,default_id,add,add_field,add_value,add_par_field,add_par_field_id,get,name,value,type,pos,names,values,types,has,count,names_or_const,sql_names,__construct,has_par,merge,combine,order of section  has difference at get should be after types of set,add,add_with_split,add_id_part,add_name_part,add_list,add_field,add_link_field,add_type_field,add_id_and_user,add_user,add_name_and_description,del,fill_from_arrays,is_empty,is_empty_except_internal_fields,names,values,par_names,db_values,types,intersect,get_intersect,get_diff,get,get_value,get_id,get_old,get_old_id,get_type,get_type_id,get_par_name,has_name,merge,esc_names,sql_field_list,par_sql,par_types,par_vars,sql_par_names does not match start_api_core,start_api,end_api,start,open_db,end,load_dummy,default_id,add,add_field,add_value,add_par_field,add_par_field_id,get,name,value,type,pos,names,values,types,has,count,names_or_const,sql_names,__construct,has_par,merge,combine,order of section  has difference at get should be after types of set,add,add_with_split,add_id_part,add_name_part,add_list,add_field,add_link_field,add_type_field,add_id_and_user,add_user,add_name_and_description,del,fill_from_arrays,is_empty,is_empty_except_internal_fields,names,values,par_names,db_values,types,intersect,get_intersect,get_diff,get,get_value,get_id,get_old,get_old_id,get_type,get_type_id,get_par_name,has_name,merge,esc_names,sql_field_list,par_sql,par_types,par_vars,sql_par_names does not match start_api_core,start_api,end_api,start,open_db,end,load_dummy,default_id,add,add_field,add_value,add_par_field,add_par_field_id,get,name,value,type,pos,names,values,types,has,count,names_or_const,sql_names,__construct,has_par,merge,combine,order of section  has difference at get should be after types of set,add,add_with_split,add_id_part,add_name_part,add_list,add_field,add_link_field,add_type_field,add_id_and_user,add_user,add_name_and_description,del,fill_from_arrays,is_empty,is_empty_except_internal_fields,names,values,par_names,db_values,types,intersect,get_intersect,get_diff,get,get_value,get_id,get_old,get_old_id,get_type,get_type_id,get_par_name,has_name,merge,esc_names,sql_field_list,par_sql,par_types,par_vars,sql_par_names does not match start_api_core,start_api,end_api,start,open_db,end,load_dummy,default_id,add,add_field,add_value,add_par_field,add_par_field_id,get,name,value,type,pos,names,values,types,has,count,names_or_const,sql_names,__construct,has_par,merge,combine,order of section  has difference at get should be after types of set,add,add_with_split,add_id_part,add_name_part,add_list,add_field,add_link_field,add_type_field,add_id_and_user,add_user,add_name_and_description,del,fill_from_arrays,is_empty,is_empty_except_internal_fields,names,values,par_names,db_values,types,intersect,get_intersect,get_diff,get,get_value,get_id,get_old,get_old_id,get_type,get_type_id,get_par_name,has_name,merge,esc_names,sql_field_list,par_sql,par_types,par_vars,sql_par_names does not match start_api_core,start_api,end_api,start,open_db,end,load_dummy,default_id,add,add_field,add_value,add_par_field,add_par_field_id,get,name,value,type,pos,names,values,types,has,count,names_or_const,sql_names,__construct,has_par,merge,combine,order of section  has difference at get should be after types of set,add,add_with_split,add_id_part,add_name_part,add_list,add_field,add_link_field,add_type_field,add_id_and_user,add_user,add_name_and_description,del,fill_from_arrays,is_empty,is_empty_except_internal_fields,names,values,par_names,db_values,types,intersect,get_intersect,get_diff,get,get_value,get_id,get_old,get_old_id,get_type,get_type_id,get_par_name,has_name,merge,esc_names,sql_field_list,par_sql,par_types,par_vars,sql_par_names does not match start_api_core,start_api,end_api,start,open_db,end,load_dummy,default_id,add,add_field,add_value,add_par_field,add_par_field_id,get,name,value,type,pos,names,values,types,has,count,names_or_const,sql_names,__construct,has_par,merge,combine,order of section  has difference at get should be after types of set,add,add_with_split,add_id_part,add_name_part,add_list,add_field,add_link_field,add_type_field,add_id_and_user,add_user,add_name_and_description,del,fill_from_arrays,is_empty,is_empty_except_internal_fields,names,values,par_names,db_values,types,intersect,get_intersect,get_diff,get,get_value,get_id,get_old,get_old_id,get_type,get_type_id,get_par_name,has_name,merge,esc_names,sql_field_list,par_sql,par_types,par_vars,sql_par_names does not match start_api_core,start_api,end_api,start,open_db,end,load_dummy,default_id,add,add_field,add_value,add_par_field,add_par_field_id,get,name,value,type,pos,names,values,types,has,count,names_or_const,sql_names,__construct,has_par,merge,combine,order of section  has difference at get should be after types of set,add,add_with_split,add_id_part,add_name_part,add_list,add_field,add_link_field,add_type_field,add_id_and_user,add_user,add_name_and_description,del,fill_from_arrays,is_empty,is_empty_except_internal_fields,names,values,par_names,db_values,types,intersect,get_intersect,get_diff,get,get_value,get_id,get_old,get_old_id,get_type,get_type_id,get_par_name,has_name,merge,esc_names,sql_field_list,par_sql,par_types,par_vars,sql_par_names does not match start_api_core,start_api,end_api,start,open_db,end,load_dummy,default_id,add,add_field,add_value,add_par_field,add_par_field_id,get,name,value,type,pos,names,values,types,has,count,names_or_const,sql_names,__construct,has_par,merge,combine,order of section  has difference at get should be after types of set,add,add_with_split,add_id_part,add_name_part,add_list,add_field,add_link_field,add_type_field,add_id_and_user,add_user,add_name_and_description,del,fill_from_arrays,is_empty,is_empty_except_internal_fields,names,values,par_names,db_values,types,intersect,get_intersect,get_diff,get,get_value,get_id,get_old,get_old_id,get_type,get_type_id,get_par_name,has_name,merge,esc_names,sql_field_list,par_sql,par_types,par_vars,sql_par_names does not match start_api_core,start_api,end_api,start,open_db,end,load_dummy,default_id,add,add_field,add_value,add_par_field,add_par_field_id,get,name,value,type,pos,names,values,types,has,count,names_or_const,sql_names,__construct,has_par,merge,combine,order of section  has difference at get should be after types of set,add,add_with_split,add_id_part,add_name_part,add_list,add_field,add_link_field,add_type_field,add_id_and_user,add_user,add_name_and_description,del,fill_from_arrays,is_empty,is_empty_except_internal_fields,names,values,par_names,db_values,types,intersect,get_intersect,get_diff,get,get_value,get_id,get_old,get_old_id,get_type,get_type_id,get_par_name,has_name,merge,esc_names,sql_field_list,par_sql,par_types,par_vars,sql_par_names does not match start_api_core,start_api,end_api,start,open_db,end,load_dummy,default_id,add,add_field,add_value,add_par_field,add_par_field_id,get,name,value,type,pos,names,values,types,has,count,names_or_const,sql_names,__construct,has_par,merge,combine,order of section  has difference at get should be after types of set,add,add_with_split,add_id_part,add_name_part,add_list,add_field,add_link_field,add_type_field,add_id_and_user,add_user,add_name_and_description,del,fill_from_arrays,is_empty,is_empty_except_internal_fields,names,values,par_names,db_values,types,intersect,get_intersect,get_diff,get,get_value,get_id,get_old,get_old_id,get_type,get_type_id,get_par_name,has_name,merge,esc_names,sql_field_list,par_sql,par_types,par_vars,sql_par_names does not match start_api_core,start_api,end_api,start,open_db,end,load_dummy,default_id,add,add_field,add_value,add_par_field,add_par_field_id,get,name,value,type,pos,names,values,types,has,count,names_or_const,sql_names,__construct,has_par,merge,combine,order of section  has difference at get should be after types of set,add,add_with_split,add_id_part,add_name_part,add_list,add_field,add_link_field,add_type_field,add_id_and_user,add_user,add_name_and_description,del,fill_from_arrays,is_empty,is_empty_except_internal_fields,names,values,par_names,db_values,types,intersect,get_intersect,get_diff,get,get_value,get_id,get_old,get_old_id,get_type,get_type_id,get_par_name,has_name,merge,esc_names,sql_field_list,par_sql,par_types,par_vars,sql_par_names does not match start_api_core,start_api,end_api,start,open_db,end,load_dummy,default_id,add,add_field,add_value,add_par_field,add_par_field_id,get,name,value,type,pos,names,values,types,has,count,names_or_const,sql_names,__construct,has_par,merge,combine,order of section  has difference at get should be after types of set,add,add_with_split,add_id_part,add_name_part,add_list,add_field,add_link_field,add_type_field,add_id_and_user,add_user,add_name_and_description,del,fill_from_arrays,is_empty,is_empty_except_internal_fields,names,values,par_names,db_values,types,intersect,get_intersect,get_diff,get,get_value,get_id,get_old,get_old_id,get_type,get_type_id,get_par_name,has_name,merge,esc_names,sql_field_list,par_sql,par_types,par_vars,sql_par_names does not match start_api_core,start_api,end_api,start,open_db,end,load_dummy,default_id,add,add_field,add_value,add_par_field,add_par_field_id,get,name,value,type,pos,names,values,types,has,count,names_or_const,sql_names,__construct,has_par,merge,combine,order of section  has difference at get should be after types of set,add,add_with_split,add_id_part,add_name_part,add_list,add_field,add_link_field,add_type_field,add_id_and_user,add_user,add_name_and_description,del,fill_from_arrays,is_empty,is_empty_except_internal_fields,names,values,par_names,db_values,types,intersect,get_intersect,get_diff,get,get_value,get_id,get_old,get_old_id,get_type,get_type_id,get_par_name,has_name,merge,esc_names,sql_field_list,par_sql,par_types,par_vars,sql_par_names does not match start_api_core,start_api,end_api,start,open_db,end,load_dummy,default_id,add,add_field,add_value,add_par_field,add_par_field_id,get,name,value,type,pos,names,values,types,has,count,names_or_const,sql_names,__construct,has_par,merge,combine,order of section  has difference at get should be after types of set,add,add_with_split,add_id_part,add_name_part,add_list,add_field,add_link_field,add_type_field,add_id_and_user,add_user,add_name_and_description,del,fill_from_arrays,is_empty,is_empty_except_internal_fields,names,values,par_names,db_values,types,intersect,get_intersect,get_diff,get,get_value,get_id,get_old,get_old_id,get_type,get_type_id,get_par_name,has_name,merge,esc_names,sql_field_list,par_sql,par_types,par_vars,sql_par_names does not match start_api_core,start_api,end_api,start,open_db,end,load_dummy,default_id,add,add_field,add_value,add_par_field,add_par_field_id,get,name,value,type,pos,names,values,types,has,count,names_or_const,sql_names,__construct,has_par,merge,combine,order of section  has difference at get should be after types of set,add,add_with_split,add_id_part,add_name_part,add_list,add_field,add_link_field,add_type_field,add_id_and_user,add_user,add_name_and_description,del,fill_from_arrays,is_empty,is_empty_except_internal_fields,names,values,par_names,db_values,types,intersect,get_intersect,get_diff,get,get_value,get_id,get_old,get_old_id,get_type,get_type_id,get_par_name,has_name,merge,esc_names,sql_field_list,par_sql,par_types,par_vars,sql_par_names does not match start_api_core,start_api,end_api,start,open_db,end,load_dummy,default_id,add,add_field,add_value,add_par_field,add_par_field_id,get,name,value,type,pos,names,values,types,has,count,names_or_const,sql_names,__construct,has_par,merge,combine,order of section  has difference at get should be after types of set,add,add_with_split,add_id_part,add_name_part,add_list,add_field,add_link_field,add_type_field,add_id_and_user,add_user,add_name_and_description,del,fill_from_arrays,is_empty,is_empty_except_internal_fields,names,values,par_names,db_values,types,intersect,get_intersect,get_diff,get,get_value,get_id,get_old,get_old_id,get_type,get_type_id,get_par_name,has_name,merge,esc_names,sql_field_list,par_sql,par_types,par_vars,sql_par_names does not match start_api_core,start_api,end_api,start,open_db,end,load_dummy,default_id,add,add_field,add_value,add_par_field,add_par_field_id,get,name,value,type,pos,names,values,types,has,count,names_or_const,sql_names,__construct,has_par,merge,combine,order of section  has difference at get should be after types of set,add,add_with_split,add_id_part,add_name_part,add_list,add_field,add_link_field,add_type_field,add_id_and_user,add_user,add_name_and_description,del,fill_from_arrays,is_empty,is_empty_except_internal_fields,names,values,par_names,db_values,types,intersect,get_intersect,get_diff,get,get_value,get_id,get_old,get_old_id,get_type,get_type_id,get_par_name,has_name,merge,esc_names,sql_field_list,par_sql,par_types,par_vars,sql_par_names does not match start_api_core,start_api,end_api,start,open_db,end,load_dummy,default_id,add,add_field,add_value,add_par_field,add_par_field_id,get,name,value,type,pos,names,values,types,has,count,names_or_const,sql_names,__construct,has_par,merge,combine,order of section  has difference at get should be after types of set,add,add_with_split,add_id_part,add_name_part,add_list,add_field,add_link_field,add_type_field,add_id_and_user,add_user,add_name_and_description,del,fill_from_arrays,is_empty,is_empty_except_internal_fields,names,values,par_names,db_values,types,intersect,get_intersect,get_diff,get,get_value,get_id,get_old,get_old_id,get_type,get_type_id,get_par_name,has_name,merge,esc_names,sql_field_list,par_sql,par_types,par_vars,sql_par_names does not match start_api_core,start_api,end_api,start,open_db,end,load_dummy,default_id,add,add_field,add_value,add_par_field,add_par_field_id,get,name,value,type,pos,names,values,types,has,count,names_or_const,sql_names,__construct,has_par,merge,combine,order of section  has difference at get should be after types of set,add,add_with_split,add_id_part,add_name_part,add_list,add_field,add_link_field,add_type_field,add_id_and_user,add_user,add_name_and_description,del,fill_from_arrays,is_empty,is_empty_except_internal_fields,names,values,par_names,db_values,types,intersect,get_intersect,get_diff,get,get_value,get_id,get_old,get_old_id,get_type,get_type_id,get_par_name,has_name,merge,esc_names,sql_field_list,par_sql,par_types,par_vars,sql_par_names does not match start_api_core,start_api,end_api,start,open_db,end,load_dummy,default_id,add,add_field,add_value,add_par_field,add_par_field_id,get,name,value,type,pos,names,values,types,has,count,names_or_const,sql_names,__construct,has_par,merge,combine,order of section  has difference at get should be after types of set,add,add_with_split,add_id_part,add_name_part,add_list,add_field,add_link_field,add_type_field,add_id_and_user,add_user,add_name_and_description,del,fill_from_arrays,is_empty,is_empty_except_internal_fields,names,values,par_names,db_values,types,intersect,get_intersect,get_diff,get,get_value,get_id,get_old,get_old_id,get_type,get_type_id,get_par_name,has_name,merge,esc_names,sql_field_list,par_sql,par_types,par_vars,sql_par_names does not match start_api_core,start_api,end_api,start,open_db,end,load_dummy,default_id,add,add_field,add_value,add_par_field,add_par_field_id,get,name,value,type,pos,names,values,types,has,count,names_or_const,sql_names,__construct,has_par,merge,combine,order of section  has difference at get should be after types of set,add,add_with_split,add_id_part,add_name_part,add_list,add_field,add_link_field,add_type_field,add_id_and_user,add_user,add_name_and_description,del,fill_from_arrays,is_empty,is_empty_except_internal_fields,names,values,par_names,db_values,types,intersect,get_intersect,get_diff,get,get_value,get_id,get_old,get_old_id,get_type,get_type_id,get_par_name,has_name,merge,esc_names,sql_field_list,par_sql,par_types,par_vars,sql_par_names does not match start_api_core,start_api,end_api,start,open_db,end,load_dummy,default_id,add,add_field,add_value,add_par_field,add_par_field_id,get,name,value,type,pos,names,values,types,has,count,names_or_const,sql_names,__construct,has_par,merge,combine,order of section  has difference at get should be after types of set,add,add_with_split,add_id_part,add_name_part,add_list,add_field,add_link_field,add_type_field,add_id_and_user,add_user,add_name_and_description,del,fill_from_arrays,is_empty,is_empty_except_internal_fields,names,values,par_names,db_values,types,intersect,get_intersect,get_diff,get,get_value,get_id,get_old,get_old_id,get_type,get_type_id,get_par_name,has_name,merge,esc_names,sql_field_list,par_sql,par_types,par_vars,sql_par_names does not match start_api_core,start_api,end_api,start,open_db,end,load_dummy,default_id,add,add_field,add_value,add_par_field,add_par_field_id,get,name,value,type,pos,names,values,types,has,count,names_or_const,sql_names,__construct,has_par,merge,combine,order of section  has difference at get should be after types of set,add,add_with_split,add_id_part,add_name_part,add_list,add_field,add_link_field,add_type_field,add_id_and_user,add_user,add_name_and_description,del,fill_from_arrays,is_empty,is_empty_except_internal_fields,names,values,par_names,db_values,types,intersect,get_intersect,get_diff,get,get_value,get_id,get_old,get_old_id,get_type,get_type_id,get_par_name,has_name,merge,esc_names,sql_field_list,par_sql,par_types,par_vars,sql_par_names does not match start_api_core,start_api,end_api,start,open_db,end,load_dummy,default_id,add,add_field,add_value,add_par_field,add_par_field_id,get,name,value,type,pos,names,values,types,has,count,names_or_const,sql_names,__construct,has_par,merge,combine,order of section  has difference at get should be after types of set,add,add_with_split,add_id_part,add_name_part,add_list,add_field,add_link_field,add_type_field,add_id_and_user,add_user,add_name_and_description,del,fill_from_arrays,is_empty,is_empty_except_internal_fields,names,values,par_names,db_values,types,intersect,get_intersect,get_diff,get,get_value,get_id,get_old,get_old_id,get_type,get_type_id,get_par_name,has_name,merge,esc_names,sql_field_list,par_sql,par_types,par_vars,sql_par_names does not match start_api_core,start_api,end_api,start,open_db,end,load_dummy,default_id,add,add_field,add_value,add_par_field,add_par_field_id,get,name,value,type,pos,names,values,types,has,count,names_or_const,sql_names,__construct,has_par,merge,combine,order of section  has difference at get should be after types of set,add,add_with_split,add_id_part,add_name_part,add_list,add_field,add_link_field,add_type_field,add_id_and_user,add_user,add_name_and_description,del,fill_from_arrays,is_empty,is_empty_except_internal_fields,names,values,par_names,db_values,types,intersect,get_intersect,get_diff,get,get_value,get_id,get_old,get_old_id,get_type,get_type_id,get_par_name,has_name,merge,esc_names,sql_field_list,par_sql,par_types,par_vars,sql_par_names does not match start_api_core,start_api,end_api,start,open_db,end,load_dummy,default_id,add,add_field,add_value,add_par_field,add_par_field_id,get,name,value,type,pos,names,values,types,has,count,names_or_const,sql_names,__construct,has_par,merge,combine,order of section  has difference at get should be after types of set,add,add_with_split,add_id_part,add_name_part,add_list,add_field,add_link_field,add_type_field,add_id_and_user,add_user,add_name_and_description,del,fill_from_arrays,is_empty,is_empty_except_internal_fields,names,values,par_names,db_values,types,intersect,get_intersect,get_diff,get,get_value,get_id,get_old,get_old_id,get_type,get_type_id,get_par_name,has_name,merge,esc_names,sql_field_list,par_sql,par_types,par_vars,sql_par_names does not match start_api_core,start_api,end_api,start,open_db,end,load_dummy,default_id,add,add_field,add_value,add_par_field,add_par_field_id,get,name,value,type,pos,names,values,types,has,count,names_or_const,sql_names,__construct,has_par,merge,combine,order of section  has difference at get should be after types of set,add,add_with_split,add_id_part,add_name_part,add_list,add_field,add_link_field,add_type_field,add_id_and_user,add_user,add_name_and_description,del,fill_from_arrays,is_empty,is_empty_except_internal_fields,names,values,par_names,db_values,types,intersect,get_intersect,get_diff,get,get_value,get_id,get_old,get_old_id,get_type,get_type_id,get_par_name,has_name,merge,esc_names,sql_field_list,par_sql,par_types,par_vars,sql_par_names does not match start_api_core,start_api,end_api,start,open_db,end,load_dummy,default_id,add,add_field,add_value,add_par_field,add_par_field_id,get,name,value,type,pos,names,values,types,has,count,names_or_const,sql_names,__construct,has_par,merge,combine,order of section  has difference at get should be after types of set,add,add_with_split,add_id_part,add_name_part,add_list,add_field,add_link_field,add_type_field,add_id_and_user,add_user,add_name_and_description,del,fill_from_arrays,is_empty,is_empty_except_internal_fields,names,values,par_names,db_values,types,intersect,get_intersect,get_diff,get,get_value,get_id,get_old,get_old_id,get_type,get_type_id,get_par_name,has_name,merge,esc_names,sql_field_list,par_sql,par_types,par_vars,sql_par_names does not match start_api_core,start_api,end_api,start,open_db,end,load_dummy,default_id,add,add_field,add_value,add_par_field,add_par_field_id,get,name,value,type,pos,names,values,types,has,count,names_or_const,sql_names,__construct,has_par,merge,combine,order of section  has difference at get should be after types of set,add,add_with_split,add_id_part,add_name_part,add_list,add_field,add_link_field,add_type_field,add_id_and_user,add_user,add_name_and_description,del,fill_from_arrays,is_empty,is_empty_except_internal_fields,names,values,par_names,db_values,types,intersect,get_intersect,get_diff,get,get_value,get_id,get_old,get_old_id,get_type,get_type_id,get_par_name,has_name,merge,esc_names,sql_field_list,par_sql,par_types,par_vars,sql_par_names does not match start_api_core,start_api,end_api,start,open_db,end,load_dummy,default_id,add,add_field,add_value,add_par_field,add_par_field_id,get,name,value,type,pos,names,values,types,has,count,names_or_const,sql_names,__construct,has_par,merge,combine,order of section  has difference at get should be after types of set,add,add_with_split,add_id_part,add_name_part,add_list,add_field,add_link_field,add_type_field,add_id_and_user,add_user,add_name_and_description,del,fill_from_arrays,is_empty,is_empty_except_internal_fields,names,values,par_names,db_values,types,intersect,get_intersect,get_diff,get,get_value,get_id,get_old,get_old_id,get_type,get_type_id,get_par_name,has_name,merge,esc_names,sql_field_list,par_sql,par_types,par_vars,sql_par_names does not match start_api_core,start_api,end_api,start,open_db,end,load_dummy,default_id,add,add_field,add_value,add_par_field,add_par_field_id,get,name,value,type,pos,names,values,types,has,count,names_or_const,sql_names,__construct,has_par,merge,combine,order of section  has difference at get should be after types of set,add,add_with_split,add_id_part,add_name_part,add_list,add_field,add_link_field,add_type_field,add_id_and_user,add_user,add_name_and_description,del,fill_from_arrays,is_empty,is_empty_except_internal_fields,names,values,par_names,db_values,types,intersect,get_intersect,get_diff,get,get_value,get_id,get_old,get_old_id,get_type,get_type_id,get_par_name,has_name,merge,esc_names,sql_field_list,par_sql,par_types,par_vars,sql_par_names does not match start_api_core,start_api,end_api,start,open_db,end,load_dummy,default_id,add,add_field,add_value,add_par_field,add_par_field_id,get,name,value,type,pos,names,values,types,has,count,names_or_const,sql_names,__construct,has_par,merge,combine,order of section  has difference at get should be after types of set,add,add_with_split,add_id_part,add_name_part,add_list,add_field,add_link_field,add_type_field,add_id_and_user,add_user,add_name_and_description,del,fill_from_arrays,is_empty,is_empty_except_internal_fields,names,values,par_names,db_values,types,intersect,get_intersect,get_diff,get,get_value,get_id,get_old,get_old_id,get_type,get_type_id,get_par_name,has_name,merge,esc_names,sql_field_list,par_sql,par_types,par_vars,sql_par_names does not match start_api_core,start_api,end_api,start,open_db,end,load_dummy,default_id,add,add_field,add_value,add_par_field,add_par_field_id,get,name,value,type,pos,names,values,types,has,count,names_or_const,sql_names,__construct,has_par,merge,combine,order of section  has difference at get should be after types of set,add,add_with_split,add_id_part,add_name_part,add_list,add_field,add_link_field,add_type_field,add_id_and_user,add_user,add_name_and_description,del,fill_from_arrays,is_empty,is_empty_except_internal_fields,names,values,par_names,db_values,types,intersect,get_intersect,get_diff,get,get_value,get_id,get_old,get_old_id,get_type,get_type_id,get_par_name,has_name,merge,esc_names,sql_field_list,par_sql,par_types,par_vars,sql_par_names does not match start_api_core,start_api,end_api,start,open_db,end,load_dummy,default_id,add,add_field,add_value,add_par_field,add_par_field_id,get,name,value,type,pos,names,values,types,has,count,names_or_const,sql_names,__construct,has_par,merge,combine,order of section  has difference at get should be after types of set,add,add_with_split,add_id_part,add_name_part,add_list,add_field,add_link_field,add_type_field,add_id_and_user,add_user,add_name_and_description,del,fill_from_arrays,is_empty,is_empty_except_internal_fields,names,values,par_names,db_values,types,intersect,get_intersect,get_diff,get,get_value,get_id,get_old,get_old_id,get_type,get_type_id,get_par_name,has_name,merge,esc_names,sql_field_list,par_sql,par_types,par_vars,sql_par_names does not match start_api_core,start_api,end_api,start,open_db,end,load_dummy,default_id,add,add_field,add_value,add_par_field,add_par_field_id,get,name,value,type,pos,names,values,types,has,count,names_or_const,sql_names,__construct,has_par,merge,combine,order of section  has difference at get should be after types of set,add,add_with_split,add_id_part,add_name_part,add_list,add_field,add_link_field,add_type_field,add_id_and_user,add_user,add_name_and_description,del,fill_from_arrays,is_empty,is_empty_except_internal_fields,names,values,par_names,db_values,types,intersect,get_intersect,get_diff,get,get_value,get_id,get_old,get_old_id,get_type,get_type_id,get_par_name,has_name,merge,esc_names,sql_field_list,par_sql,par_types,par_vars,sql_par_names does not match start_api_core,start_api,end_api,start,open_db,end,load_dummy,default_id,add,add_field,add_value,add_par_field,add_par_field_id,get,name,value,type,pos,names,values,types,has,count,names_or_const,sql_names,__construct,has_par,merge,combine,order of section  has difference at get should be after types of set,add,add_with_split,add_id_part,add_name_part,add_list,add_field,add_link_field,add_type_field,add_id_and_user,add_user,add_name_and_description,del,fill_from_arrays,is_empty,is_empty_except_internal_fields,names,values,par_names,db_values,types,intersect,get_intersect,get_diff,get,get_value,get_id,get_old,get_old_id,get_type,get_type_id,get_par_name,has_name,merge,esc_names,sql_field_list,par_sql,par_types,par_vars,sql_par_names does not match start_api_core,start_api,end_api,start,open_db,end,load_dummy,default_id,add,add_field,add_value,add_par_field,add_par_field_id,get,name,value,type,pos,names,values,types,has,count,names_or_const,sql_names,__construct,has_par,merge,combine,order of section  has difference at get should be after types of set,add,add_with_split,add_id_part,add_name_part,add_list,add_field,add_link_field,add_type_field,add_id_and_user,add_user,add_name_and_description,del,fill_from_arrays,is_empty,is_empty_except_internal_fields,names,values,par_names,db_values,types,intersect,get_intersect,get_diff,get,get_value,get_id,get_old,get_old_id,get_type,get_type_id,get_par_name,has_name,merge,esc_names,sql_field_list,par_sql,par_types,par_vars,sql_par_names does not match start_api_core,start_api,end_api,start,open_db,end,load_dummy,default_id,add,add_field,add_value,add_par_field,add_par_field_id,get,name,value,type,pos,names,values,types,has,count,names_or_const,sql_names,__construct,has_par,merge,combine,order of section  has difference at get should be after types of set,add,add_with_split,add_id_part,add_name_part,add_list,add_field,add_link_field,add_type_field,add_id_and_user,add_user,add_name_and_description,del,fill_from_arrays,is_empty,is_empty_except_internal_fields,names,values,par_names,db_values,types,intersect,get_intersect,get_diff,get,get_value,get_id,get_old,get_old_id,get_type,get_type_id,get_par_name,has_name,merge,esc_names,sql_field_list,par_sql,par_types,par_vars,sql_par_names does not match start_api_core,start_api,end_api,start,open_db,end,load_dummy,default_id,add,add_field,add_value,add_par_field,add_par_field_id,get,name,value,type,pos,names,values,types,has,count,names_or_const,sql_names,__construct,has_par,merge,combine
    \-- sql_par_list
        \-- add - section for function add missing in /db/sql_par_list.php
        \-- add_by_name - section for function add_by_name is expected to be modify in /db/sql_par_list.php
        \-- names - section for function names is expected to be info in /db/sql_par_list.php
        \-- object_names - section for function object_names missing in /db/sql_par_list.php
        \-- count - section for function count missing in /db/sql_par_list.php
        \-- exe - section for function exe missing in /db/sql_par_list.php
        \-- exe_update - section for function exe_update missing in /db/sql_par_list.php
        \-- exe_delete - section for function exe_delete missing in /db/sql_par_list.php
        \-- sql_functions_missing - section for function sql_functions_missing not yet defined that it should be filter in /db/sql_par_list.php
        \-- order error - order of section  has difference at names should be after add_by_name, count should be after add_by_name of add,add_by_name,names,object_names,count,exe,exe_update,exe_delete does not match start_api_core,start_api,end_api,start,open_db,end,load_dummy,default_id,add,add_field,add_value,add_par_field,add_par_field_id,get,name,value,type,pos,names,values,types,has,count,names_or_const,sql_names,__construct,has_par,merge,combine,add_by_name,order of section  has difference at names should be after add_by_name, count should be after add_by_name of add,add_by_name,names,object_names,count,exe,exe_update,exe_delete does not match start_api_core,start_api,end_api,start,open_db,end,load_dummy,default_id,add,add_field,add_value,add_par_field,add_par_field_id,get,name,value,type,pos,names,values,types,has,count,names_or_const,sql_names,__construct,has_par,merge,combine,add_by_name,order of section  has difference at names should be after add_by_name, count should be after add_by_name of add,add_by_name,names,object_names,count,exe,exe_update,exe_delete does not match start_api_core,start_api,end_api,start,open_db,end,load_dummy,default_id,add,add_field,add_value,add_par_field,add_par_field_id,get,name,value,type,pos,names,values,types,has,count,names_or_const,sql_names,__construct,has_par,merge,combine,add_by_name,order of section  has difference at names should be after add_by_name, count should be after add_by_name of add,add_by_name,names,object_names,count,exe,exe_update,exe_delete does not match start_api_core,start_api,end_api,start,open_db,end,load_dummy,default_id,add,add_field,add_value,add_par_field,add_par_field_id,get,name,value,type,pos,names,values,types,has,count,names_or_const,sql_names,__construct,has_par,merge,combine,add_by_name,order of section  has difference at names should be after add_by_name, count should be after add_by_name of add,add_by_name,names,object_names,count,exe,exe_update,exe_delete does not match start_api_core,start_api,end_api,start,open_db,end,load_dummy,default_id,add,add_field,add_value,add_par_field,add_par_field_id,get,name,value,type,pos,names,values,types,has,count,names_or_const,sql_names,__construct,has_par,merge,combine,add_by_name,order of section  has difference at names should be after add_by_name, count should be after add_by_name of add,add_by_name,names,object_names,count,exe,exe_update,exe_delete does not match start_api_core,start_api,end_api,start,open_db,end,load_dummy,default_id,add,add_field,add_value,add_par_field,add_par_field_id,get,name,value,type,pos,names,values,types,has,count,names_or_const,sql_names,__construct,has_par,merge,combine,add_by_name
    \-- 
        \-- log_debug - section for function log_debug is expected to be log in /log_text/text_log_functions.php
        \-- log_info - section for function log_info is expected to be log in /log_text/text_log_functions.php
        \-- log_warning - section for function log_warning is expected to be log in /log_text/text_log_functions.php
        \-- log_err - section for function log_err is expected to be log in /log_text/text_log_functions.php
        \-- log_fatal_db - section for function log_fatal_db is expected to be log in /log_text/text_log_functions.php
        \-- log_fatal - section for function log_fatal is expected to be log in /log_text/text_log_functions.php
        \-- log_msg - section for function log_msg is expected to be log in /log_text/text_log_functions.php
    \-- sql_type_list
        \-- __construct - section for function __construct is expected to be construct and map in /db/sql_type_list.php
        \-- add - section for function add not yet defined that it should be modify in /db/sql_type_list.php
        \-- remove - section for function remove not yet defined that it should be modify in /db/sql_type_list.php
        \-- is_call_only - section for function is_call_only not yet defined that it should be info sql type in /db/sql_type_list.php
        \-- is_insert - section for function is_insert not yet defined that it should be info sql type in /db/sql_type_list.php
        \-- is_update - section for function is_update not yet defined that it should be info sql type in /db/sql_type_list.php
        \-- is_delete - section for function is_delete not yet defined that it should be info sql type in /db/sql_type_list.php
        \-- is_select - section for function is_select not yet defined that it should be info sql type in /db/sql_type_list.php
        \-- is_most - section for function is_most not yet defined that it should be info for value table selection in /db/sql_type_list.php
        \-- is_prime - section for function is_prime not yet defined that it should be info for value table selection in /db/sql_type_list.php
        \-- is_big - section for function is_big not yet defined that it should be info for value table selection in /db/sql_type_list.php
        \-- value_table_type - section for function value_table_type not yet defined that it should be info for value table selection in /db/sql_type_list.php
        \-- is_usr_tbl - section for function is_usr_tbl not yet defined that it should be info for sql functions in /db/sql_type_list.php
        \-- is_usr_tbl_and_select - section for function is_usr_tbl_and_select not yet defined that it should be info for sql functions in /db/sql_type_list.php
        \-- is_norm - section for function is_norm not yet defined that it should be info for sql functions in /db/sql_type_list.php
        \-- is_standard - section for function is_standard not yet defined that it should be info for sql functions in /db/sql_type_list.php
        \-- get_all - section for function get_all is expected to be set and get in /db/sql_type_list.php
        \-- is_geo - section for function is_geo not yet defined that it should be info for sql functions in /db/sql_type_list.php
        \-- is_sub_tbl - section for function is_sub_tbl not yet defined that it should be info for sql functions in /db/sql_type_list.php
    \-- element
        \-- name - section for function name not yet defined that it should be set and get in /element/element.php
        \-- id - section for function id not yet defined that it should be set and get in /element/element.php
        \-- trm_id - section for function trm_id not yet defined that it should be set and get in /element/element.php
        \-- term - section for function term not yet defined that it should be set and get in /element/element.php
        \-- load_sql - section for function load_sql is expected to be load sql in /element/element.php
        \-- load_sql_by_id - section for function load_sql_by_id is expected to be load sql in /element/element.php
        \-- include - section for function include not yet defined that it should be forward in /element/element.php
        \-- exclude - section for function exclude not yet defined that it should be forward in /element/element.php
        \-- is_excluded - section for function is_excluded not yet defined that it should be forward in /element/element.php
        \-- is_word - section for function is_word not yet defined that it should be forward in /element/element.php
        \-- is_triple - section for function is_triple not yet defined that it should be forward in /element/element.php
        \-- is_verb - section for function is_verb not yet defined that it should be forward in /element/element.php
        \-- is_formula - section for function is_formula not yet defined that it should be forward in /element/element.php
        \-- db_ready - section for function db_ready not yet defined that it should be forward in /element/element.php
        \-- sql_insert - section for function sql_insert not yet defined that it should be sql write in /element/element.php
        \-- sql_update - section for function sql_update not yet defined that it should be sql write in /element/element.php
    \-- element_group
        \-- name - section for function name not yet defined that it should be display in /element/element_group.php
        \-- id - section for function id not yet defined that it should be display in /element/element_group.php
        \-- build_symbol - section for function build_symbol not yet defined that it should be display in /element/element_group.php
        \-- figures - section for function figures not yet defined that it should be display in /element/element_group.php
        \-- ids - section for function ids not yet defined that it should be debug in /element/element_group.php
    \-- element_list
        \-- term_list - section for function term_list not yet defined that it should be set and get in /element/element_list.php
        \-- load_sql_by_frm_id - section for function load_sql_by_frm_id is expected to be load sql in /element/element_list.php
        \-- load_sql_by_frm_and_type_id - section for function load_sql_by_frm_and_type_id is expected to be load sql in /element/element_list.php
        \-- add - section for function add not yet defined that it should be modify in /element/element_list.php
    \-- export
        \-- get - section for function get not yet defined that it should be set and get in /export/export.php
    \-- export_type_list
        \-- __construct - section for function __construct is expected to be construct and map in /export/export_type_list.php
        \-- add - section for function add not yet defined that it should be modify in /export/export_type_list.php
        \-- remove - section for function remove not yet defined that it should be modify in /export/export_type_list.php
    \-- expression
        \-- phr_lst - section for function phr_lst not yet defined that it should be interface in /formula/expression.php
        \-- terms - section for function terms not yet defined that it should be interface in /formula/expression.php
        \-- result_phrases - section for function result_phrases not yet defined that it should be interface in /formula/expression.php
        \-- is_valid - section for function is_valid not yet defined that it should be interface in /formula/expression.php
        \-- element_grp_lst - section for function element_grp_lst not yet defined that it should be interface in /formula/expression.php
        \-- element_list - section for function element_list not yet defined that it should be interface in /formula/expression.php
        \-- element_special_following - section for function element_special_following not yet defined that it should be interface in /formula/expression.php
        \-- element_special_following_frm - section for function element_special_following_frm not yet defined that it should be interface in /formula/expression.php
        \-- phr_id_lst - section for function phr_id_lst not yet defined that it should be internal in /formula/expression.php
        \-- phr_id_lst_as_phr_lst - section for function phr_id_lst_as_phr_lst not yet defined that it should be internal in /formula/expression.php
        \-- has_ref - section for function has_ref not yet defined that it should be internal in /formula/expression.php
        \-- get_usr_names - section for function get_usr_names is expected to be set and get in /formula/expression.php
        \-- phr_verb_lst - section for function phr_verb_lst not yet defined that it should be to review in /formula/expression.php
        \-- name - section for function name not yet defined that it should be debug in /formula/expression.php
    \-- fig_ids
        \-- order error - order of section  has difference at count should be after __construct of __construct,set_by_txt,count does not match start_api_core,start_api,end_api,start,open_db,end,load_dummy,default_id,add,add_field,add_value,add_par_field,add_par_field_id,get,name,value,type,pos,names,values,types,has,count,names_or_const,sql_names,__construct,has_par,merge,combine,is_function,is_or,is_list,is_text,prefix,is_sql_change,is_val_type,export_by_phrase_list,order of section  has difference at count should be after __construct of __construct,set_by_txt,count does not match start_api_core,start_api,end_api,start,open_db,end,load_dummy,default_id,add,add_field,add_value,add_par_field,add_par_field_id,get,name,value,type,pos,names,values,types,has,count,names_or_const,sql_names,__construct,has_par,merge,combine,is_function,is_or,is_list,is_text,prefix,is_sql_change,is_val_type,export_by_phrase_list,order of section  has difference at count should be after __construct of __construct,set_by_txt,count does not match start_api_core,start_api,end_api,start,open_db,end,load_dummy,default_id,add,add_field,add_value,add_par_field,add_par_field_id,get,name,value,type,pos,names,values,types,has,count,names_or_const,sql_names,__construct,has_par,merge,combine,is_function,is_or,is_list,is_text,prefix,is_sql_change,is_val_type,export_by_phrase_list
    \-- figure
        \-- row_mapper - section for function row_mapper not yet defined that it should be construct and map in /formula/figure.php
        \-- id - section for function id not yet defined that it should be set and get in /formula/figure.php
        \-- obj_id - section for function obj_id not yet defined that it should be set and get in /formula/figure.php
        \-- user - section for function user not yet defined that it should be set and get in /formula/figure.php
        \-- number - section for function number not yet defined that it should be set and get in /formula/figure.php
        \-- symbol - section for function symbol not yet defined that it should be set and get in /formula/figure.php
        \-- last_update - section for function last_update not yet defined that it should be set and get in /formula/figure.php
        \-- is_std - section for function is_std not yet defined that it should be set and get in /formula/figure.php
        \-- is_result - section for function is_result not yet defined that it should be classification in /formula/figure.php
        \-- name - section for function name not yet defined that it should be debug in /formula/figure.php
        \-- order error - order of section construct and map has difference at api_mapper should be after row_mapper of __construct,row_mapper,api_mapper does not match __construct,row_mapper_sandbox,api_mapper,row_mapper
    \-- figure_list
        \-- load - section for function load not yet defined that it should be load in /formula/figure_list.php
        \-- load_sql_by_ids - section for function load_sql_by_ids is expected to be load sql in /formula/figure_list.php
        \-- load_sql - section for function load_sql is expected to be load sql in /formula/figure_list.php
        \-- add - section for function add not yet defined that it should be modify in /formula/figure_list.php
        \-- get_first_id - section for function get_first_id is expected to be set and get in /formula/figure_list.php
        \-- name - section for function name not yet defined that it should be debug in /formula/figure_list.php
        \-- ids_txt - section for function ids_txt not yet defined that it should be debug in /formula/figure_list.php
        \-- order error - order of section load has difference at load_sql should be after load of load_by_ids,load,load_sql_by_ids,load_sql,load_phrases does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load,order of section load has difference at load_sql should be after load of load_by_ids,load,load_sql_by_ids,load_sql,load_phrases does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load,order of section load has difference at load_sql should be after load of load_by_ids,load,load_sql_by_ids,load_sql,load_phrases does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load
    \-- formula_link_list
        \-- load_sql - section for function load_sql is expected to be load sql in /formula/formula_link_list.php
        \-- load_sql_by_frm_id - section for function load_sql_by_frm_id is expected to be load sql in /formula/formula_link_list.php
        \-- phrase_ids - section for function phrase_ids not yet defined that it should be load in /formula/formula_link_list.php
        \-- del_without_log - section for function del_without_log is expected to be del in /formula/formula_link_list.php
    \-- formula_list
        \-- load_sql - section for function load_sql is expected to be load sql in /formula/formula_list.php
        \-- load_sql_by_ids - section for function load_sql_by_ids is expected to be load sql in /formula/formula_list.php
        \-- load_sql_by_names - section for function load_sql_by_names is expected to be load sql in /formula/formula_list.php
        \-- load_sql_like - section for function load_sql_like is expected to be load sql in /formula/formula_list.php
        \-- load_sql_by_phr - section for function load_sql_by_phr is expected to be load sql in /formula/formula_list.php
        \-- load_sql_by_phr_lst - section for function load_sql_by_phr_lst is expected to be load sql in /formula/formula_list.php
        \-- load_sql_by_ref - section for function load_sql_by_ref is expected to be load sql in /formula/formula_list.php
        \-- load_sql_by_word_ref - section for function load_sql_by_word_ref is expected to be load sql in /formula/formula_list.php
        \-- load_sql_by_triple_ref - section for function load_sql_by_triple_ref is expected to be load sql in /formula/formula_list.php
        \-- load_sql_by_verb_ref - section for function load_sql_by_verb_ref is expected to be load sql in /formula/formula_list.php
        \-- load_sql_by_formula_ref - section for function load_sql_by_formula_ref is expected to be load sql in /formula/formula_list.php
        \-- load_sql_all - section for function load_sql_all is expected to be load sql in /formula/formula_list.php
        \-- add - section for function add not yet defined that it should be modify in /formula/formula_list.php
        \-- count_db - section for function count_db not yet defined that it should be info in /formula/formula_list.php
        \-- term_lst_of_names - section for function term_lst_of_names not yet defined that it should be info in /formula/formula_list.php
        \-- db_ref_refresh - section for function db_ref_refresh not yet defined that it should be upgrade functions in /formula/formula_list.php
        \-- name - section for function name not yet defined that it should be display in /formula/formula_list.php
        \-- names - section for function names is expected to be info in /formula/formula_list.php
        \-- calc_blocks - section for function calc_blocks not yet defined that it should be display in /formula/formula_list.php
        \-- save_with_cache - section for function save_with_cache is expected to be save in /formula/formula_list.php
        \-- term_list - section for function term_list not yet defined that it should be convert in /formula/formula_list.php
        \-- missing_ids - section for function missing_ids not yet defined that it should be convert in /formula/formula_list.php
        \-- save_with_cache_slow - section for function save_with_cache_slow is expected to be save in /formula/formula_list.php
        \-- get_ready - section for function get_ready is expected to be set and get in /formula/formula_list.php
        \-- fill_by_name - section for function fill_by_name not yet defined that it should be convert in /formula/formula_list.php
        \-- order error - order of section load has difference at load_names should be after load_sql, load_by_ids should be after load_sql of load_sql,load_sql_by_ids,load_sql_by_names,load_sql_like,load_sql_by_phr,load_sql_by_phr_lst,load_sql_by_ref,load_sql_by_word_ref,load_sql_by_triple_ref,load_sql_by_verb_ref,load_sql_by_formula_ref,load_sql_all,load_names,load_by_ids,load_like,load_by_phr,load_by_phr_lst,load_by_word_ref,load_by_triple_ref,load_by_verb_ref,load_by_formula_ref,load_all does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,order of section load has difference at load_names should be after load_sql, load_by_ids should be after load_sql of load_sql,load_sql_by_ids,load_sql_by_names,load_sql_like,load_sql_by_phr,load_sql_by_phr_lst,load_sql_by_ref,load_sql_by_word_ref,load_sql_by_triple_ref,load_sql_by_verb_ref,load_sql_by_formula_ref,load_sql_all,load_names,load_by_ids,load_like,load_by_phr,load_by_phr_lst,load_by_word_ref,load_by_triple_ref,load_by_verb_ref,load_by_formula_ref,load_all does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,order of section load has difference at load_names should be after load_sql, load_by_ids should be after load_sql of load_sql,load_sql_by_ids,load_sql_by_names,load_sql_like,load_sql_by_phr,load_sql_by_phr_lst,load_sql_by_ref,load_sql_by_word_ref,load_sql_by_triple_ref,load_sql_by_verb_ref,load_sql_by_formula_ref,load_sql_all,load_names,load_by_ids,load_like,load_by_phr,load_by_phr_lst,load_by_word_ref,load_by_triple_ref,load_by_verb_ref,load_by_formula_ref,load_all does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,order of section load has difference at load_names should be after load_sql, load_by_ids should be after load_sql of load_sql,load_sql_by_ids,load_sql_by_names,load_sql_like,load_sql_by_phr,load_sql_by_phr_lst,load_sql_by_ref,load_sql_by_word_ref,load_sql_by_triple_ref,load_sql_by_verb_ref,load_sql_by_formula_ref,load_sql_all,load_names,load_by_ids,load_like,load_by_phr,load_by_phr_lst,load_by_word_ref,load_by_triple_ref,load_by_verb_ref,load_by_formula_ref,load_all does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,order of section load has difference at load_names should be after load_sql, load_by_ids should be after load_sql of load_sql,load_sql_by_ids,load_sql_by_names,load_sql_like,load_sql_by_phr,load_sql_by_phr_lst,load_sql_by_ref,load_sql_by_word_ref,load_sql_by_triple_ref,load_sql_by_verb_ref,load_sql_by_formula_ref,load_sql_all,load_names,load_by_ids,load_like,load_by_phr,load_by_phr_lst,load_by_word_ref,load_by_triple_ref,load_by_verb_ref,load_by_formula_ref,load_all does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,order of section load has difference at load_names should be after load_sql, load_by_ids should be after load_sql of load_sql,load_sql_by_ids,load_sql_by_names,load_sql_like,load_sql_by_phr,load_sql_by_phr_lst,load_sql_by_ref,load_sql_by_word_ref,load_sql_by_triple_ref,load_sql_by_verb_ref,load_sql_by_formula_ref,load_sql_all,load_names,load_by_ids,load_like,load_by_phr,load_by_phr_lst,load_by_word_ref,load_by_triple_ref,load_by_verb_ref,load_by_formula_ref,load_all does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,order of section load has difference at load_names should be after load_sql, load_by_ids should be after load_sql of load_sql,load_sql_by_ids,load_sql_by_names,load_sql_like,load_sql_by_phr,load_sql_by_phr_lst,load_sql_by_ref,load_sql_by_word_ref,load_sql_by_triple_ref,load_sql_by_verb_ref,load_sql_by_formula_ref,load_sql_all,load_names,load_by_ids,load_like,load_by_phr,load_by_phr_lst,load_by_word_ref,load_by_triple_ref,load_by_verb_ref,load_by_formula_ref,load_all does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,order of section load has difference at load_names should be after load_sql, load_by_ids should be after load_sql of load_sql,load_sql_by_ids,load_sql_by_names,load_sql_like,load_sql_by_phr,load_sql_by_phr_lst,load_sql_by_ref,load_sql_by_word_ref,load_sql_by_triple_ref,load_sql_by_verb_ref,load_sql_by_formula_ref,load_sql_all,load_names,load_by_ids,load_like,load_by_phr,load_by_phr_lst,load_by_word_ref,load_by_triple_ref,load_by_verb_ref,load_by_formula_ref,load_all does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,order of section load has difference at load_names should be after load_sql, load_by_ids should be after load_sql of load_sql,load_sql_by_ids,load_sql_by_names,load_sql_like,load_sql_by_phr,load_sql_by_phr_lst,load_sql_by_ref,load_sql_by_word_ref,load_sql_by_triple_ref,load_sql_by_verb_ref,load_sql_by_formula_ref,load_sql_all,load_names,load_by_ids,load_like,load_by_phr,load_by_phr_lst,load_by_word_ref,load_by_triple_ref,load_by_verb_ref,load_by_formula_ref,load_all does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,order of section load has difference at load_names should be after load_sql, load_by_ids should be after load_sql of load_sql,load_sql_by_ids,load_sql_by_names,load_sql_like,load_sql_by_phr,load_sql_by_phr_lst,load_sql_by_ref,load_sql_by_word_ref,load_sql_by_triple_ref,load_sql_by_verb_ref,load_sql_by_formula_ref,load_sql_all,load_names,load_by_ids,load_like,load_by_phr,load_by_phr_lst,load_by_word_ref,load_by_triple_ref,load_by_verb_ref,load_by_formula_ref,load_all does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,order of section load has difference at load_names should be after load_sql, load_by_ids should be after load_sql of load_sql,load_sql_by_ids,load_sql_by_names,load_sql_like,load_sql_by_phr,load_sql_by_phr_lst,load_sql_by_ref,load_sql_by_word_ref,load_sql_by_triple_ref,load_sql_by_verb_ref,load_sql_by_formula_ref,load_sql_all,load_names,load_by_ids,load_like,load_by_phr,load_by_phr_lst,load_by_word_ref,load_by_triple_ref,load_by_verb_ref,load_by_formula_ref,load_all does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,order of section load has difference at load_names should be after load_sql, load_by_ids should be after load_sql of load_sql,load_sql_by_ids,load_sql_by_names,load_sql_like,load_sql_by_phr,load_sql_by_phr_lst,load_sql_by_ref,load_sql_by_word_ref,load_sql_by_triple_ref,load_sql_by_verb_ref,load_sql_by_formula_ref,load_sql_all,load_names,load_by_ids,load_like,load_by_phr,load_by_phr_lst,load_by_word_ref,load_by_triple_ref,load_by_verb_ref,load_by_formula_ref,load_all does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,order of section load has difference at load_names should be after load_sql, load_by_ids should be after load_sql of load_sql,load_sql_by_ids,load_sql_by_names,load_sql_like,load_sql_by_phr,load_sql_by_phr_lst,load_sql_by_ref,load_sql_by_word_ref,load_sql_by_triple_ref,load_sql_by_verb_ref,load_sql_by_formula_ref,load_sql_all,load_names,load_by_ids,load_like,load_by_phr,load_by_phr_lst,load_by_word_ref,load_by_triple_ref,load_by_verb_ref,load_by_formula_ref,load_all does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,order of section load has difference at load_names should be after load_sql, load_by_ids should be after load_sql of load_sql,load_sql_by_ids,load_sql_by_names,load_sql_like,load_sql_by_phr,load_sql_by_phr_lst,load_sql_by_ref,load_sql_by_word_ref,load_sql_by_triple_ref,load_sql_by_verb_ref,load_sql_by_formula_ref,load_sql_all,load_names,load_by_ids,load_like,load_by_phr,load_by_phr_lst,load_by_word_ref,load_by_triple_ref,load_by_verb_ref,load_by_formula_ref,load_all does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,order of section load has difference at load_names should be after load_sql, load_by_ids should be after load_sql of load_sql,load_sql_by_ids,load_sql_by_names,load_sql_like,load_sql_by_phr,load_sql_by_phr_lst,load_sql_by_ref,load_sql_by_word_ref,load_sql_by_triple_ref,load_sql_by_verb_ref,load_sql_by_formula_ref,load_sql_all,load_names,load_by_ids,load_like,load_by_phr,load_by_phr_lst,load_by_word_ref,load_by_triple_ref,load_by_verb_ref,load_by_formula_ref,load_all does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,order of section load has difference at load_names should be after load_sql, load_by_ids should be after load_sql of load_sql,load_sql_by_ids,load_sql_by_names,load_sql_like,load_sql_by_phr,load_sql_by_phr_lst,load_sql_by_ref,load_sql_by_word_ref,load_sql_by_triple_ref,load_sql_by_verb_ref,load_sql_by_formula_ref,load_sql_all,load_names,load_by_ids,load_like,load_by_phr,load_by_phr_lst,load_by_word_ref,load_by_triple_ref,load_by_verb_ref,load_by_formula_ref,load_all does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,order of section load has difference at load_names should be after load_sql, load_by_ids should be after load_sql of load_sql,load_sql_by_ids,load_sql_by_names,load_sql_like,load_sql_by_phr,load_sql_by_phr_lst,load_sql_by_ref,load_sql_by_word_ref,load_sql_by_triple_ref,load_sql_by_verb_ref,load_sql_by_formula_ref,load_sql_all,load_names,load_by_ids,load_like,load_by_phr,load_by_phr_lst,load_by_word_ref,load_by_triple_ref,load_by_verb_ref,load_by_formula_ref,load_all does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,order of section load has difference at load_names should be after load_sql, load_by_ids should be after load_sql of load_sql,load_sql_by_ids,load_sql_by_names,load_sql_like,load_sql_by_phr,load_sql_by_phr_lst,load_sql_by_ref,load_sql_by_word_ref,load_sql_by_triple_ref,load_sql_by_verb_ref,load_sql_by_formula_ref,load_sql_all,load_names,load_by_ids,load_like,load_by_phr,load_by_phr_lst,load_by_word_ref,load_by_triple_ref,load_by_verb_ref,load_by_formula_ref,load_all does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,order of section load has difference at load_names should be after load_sql, load_by_ids should be after load_sql of load_sql,load_sql_by_ids,load_sql_by_names,load_sql_like,load_sql_by_phr,load_sql_by_phr_lst,load_sql_by_ref,load_sql_by_word_ref,load_sql_by_triple_ref,load_sql_by_verb_ref,load_sql_by_formula_ref,load_sql_all,load_names,load_by_ids,load_like,load_by_phr,load_by_phr_lst,load_by_word_ref,load_by_triple_ref,load_by_verb_ref,load_by_formula_ref,load_all does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,order of section load has difference at load_names should be after load_sql, load_by_ids should be after load_sql of load_sql,load_sql_by_ids,load_sql_by_names,load_sql_like,load_sql_by_phr,load_sql_by_phr_lst,load_sql_by_ref,load_sql_by_word_ref,load_sql_by_triple_ref,load_sql_by_verb_ref,load_sql_by_formula_ref,load_sql_all,load_names,load_by_ids,load_like,load_by_phr,load_by_phr_lst,load_by_word_ref,load_by_triple_ref,load_by_verb_ref,load_by_formula_ref,load_all does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,order of section load has difference at load_names should be after load_sql, load_by_ids should be after load_sql of load_sql,load_sql_by_ids,load_sql_by_names,load_sql_like,load_sql_by_phr,load_sql_by_phr_lst,load_sql_by_ref,load_sql_by_word_ref,load_sql_by_triple_ref,load_sql_by_verb_ref,load_sql_by_formula_ref,load_sql_all,load_names,load_by_ids,load_like,load_by_phr,load_by_phr_lst,load_by_word_ref,load_by_triple_ref,load_by_verb_ref,load_by_formula_ref,load_all does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,order of section load has difference at load_names should be after load_sql, load_by_ids should be after load_sql of load_sql,load_sql_by_ids,load_sql_by_names,load_sql_like,load_sql_by_phr,load_sql_by_phr_lst,load_sql_by_ref,load_sql_by_word_ref,load_sql_by_triple_ref,load_sql_by_verb_ref,load_sql_by_formula_ref,load_sql_all,load_names,load_by_ids,load_like,load_by_phr,load_by_phr_lst,load_by_word_ref,load_by_triple_ref,load_by_verb_ref,load_by_formula_ref,load_all does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log
    \-- group
        \-- row_mapper - section for function row_mapper not yet defined that it should be construct and map in /group/group.php
        \-- description - section for function description not yet defined that it should be set and get in /group/group.php
        \-- phrase_list - section for function phrase_list not yet defined that it should be set and get in /group/group.php
        \-- has_phrase_list - section for function has_phrase_list not yet defined that it should be set and get in /group/group.php
        \-- id - section for function id not yet defined that it should be set and get in /group/group.php
        \-- id_lst - section for function id_lst not yet defined that it should be set and get in /group/group.php
        \-- id_names - section for function id_names not yet defined that it should be set and get in /group/group.php
        \-- id_fvt - section for function id_fvt not yet defined that it should be set and get in /group/group.php
        \-- id_fvt_main - section for function id_fvt_main not yet defined that it should be set and get in /group/group.php
        \-- table_extension - section for function table_extension not yet defined that it should be set and get in /group/group.php
        \-- table_type - section for function table_type not yet defined that it should be set and get in /group/group.php
        \-- is_id_set - section for function is_id_set not yet defined that it should be set and get in /group/group.php
        \-- renamed - section for function renamed not yet defined that it should be set and get in /group/group.php
        \-- name_field - section for function name_field is expected to be sql fields in /group/group.php
        \-- grp - section for function grp not yet defined that it should be set and get in /group/group.php
        \-- is_saved - section for function is_saved not yet defined that it should be info in /group/group.php
        \-- set_saved - section for function set_saved is expected to be set and get in /group/group.php
        \-- sql_table - section for function sql_table not yet defined that it should be sql create in /group/group.php
        \-- sql_index - section for function sql_index not yet defined that it should be sql create in /group/group.php
        \-- sql_foreign_key - section for function sql_foreign_key not yet defined that it should be sql create in /group/group.php
        \-- sql_truncate - section for function sql_truncate not yet defined that it should be sql create in /group/group.php
        \-- load_sql_by_id - section for function load_sql_by_id is expected to be load sql in /group/group.php
        \-- load_sql_by_name - section for function load_sql_by_name is expected to be load sql in /group/group.php
        \-- load_sql_by_name_single - section for function load_sql_by_name_single is expected to be load sql in /group/group.php
        \-- load_sql_by_phrase_list - section for function load_sql_by_phrase_list is expected to be load sql in /group/group.php
        \-- load_sql_obj_vars - section for function load_sql_obj_vars is expected to be load sql in /group/group.php
        \-- load_by_obj_vars - section for function load_by_obj_vars is expected to be load in /group/group.php
        \-- load_phrase_names - section for function load_phrase_names is expected to be load in /group/group.php
        \-- load_phrases - section for function load_phrases is expected to be load in /group/group.php
        \-- get_by_phrase_list - section for function get_by_phrase_list is expected to be set and get in /group/group.php
        \-- get - section for function get not yet defined that it should be get functions - to load or create with one call in /group/group.php
        \-- get_id - section for function get_id is expected to be set and get in /group/group.php
        \-- get_by_wrd_lst_sql - section for function get_by_wrd_lst_sql is expected to be set and get in /group/group.php
        \-- add_word - section for function add_word not yet defined that it should be modify in /group/group.php
        \-- add_phrase_names - section for function add_phrase_names not yet defined that it should be modify in /group/group.php
        \-- is_prime - section for function is_prime not yet defined that it should be info in /group/group.php
        \-- is_main - section for function is_main not yet defined that it should be info in /group/group.php
        \-- is_big - section for function is_big not yet defined that it should be info in /group/group.php
        \-- add - section for function add not yet defined that it should be save in /group/group.php
        \-- value - section for function value not yet defined that it should be display in /group/group.php
        \-- time - section for function time not yet defined that it should be display in /group/group.php
        \-- result - section for function result not yet defined that it should be display in /group/group.php
        \-- get_ex_time - section for function get_ex_time is expected to be set and get in /group/group.php
        \-- save_from_api_msg - section for function save_from_api_msg is expected to be save in /group/group.php
        \-- del - section for function del is expected to be del in /group/group.php
        \-- sql_insert - section for function sql_insert not yet defined that it should be sql write in /group/group.php
        \-- sql_update - section for function sql_update not yet defined that it should be sql write in /group/group.php
        \-- db_changed - section for function db_changed not yet defined that it should be sql write fields in /group/group.php
        \-- load_link_ids_for_testing - section for function load_link_ids_for_testing is expected to be load in /group/group.php
        \-- dsp_id_medium - section for function dsp_id_medium not yet defined that it should be debug in /group/group.php
        \-- dsp_id_short - section for function dsp_id_short not yet defined that it should be debug in /group/group.php
        \-- name - section for function name not yet defined that it should be debug in /group/group.php
        \-- name_generated - section for function name_generated not yet defined that it should be debug in /group/group.php
        \-- names - section for function names is expected to be info in /group/group.php
        \-- order error - order of section construct and map has difference at api_mapper should be after reset of __construct,reset,row_mapper,api_mapper does not match __construct,row_mapper_sandbox,api_mapper,reset,order of section construct and map has difference at api_mapper should be after reset of __construct,reset,row_mapper,api_mapper does not match __construct,row_mapper_sandbox,api_mapper,reset,order of section load has difference at load_sql_by_id should be after load_standard_by_id of load_by_id,load_by_name,load_by_phr_lst,load_by_ids,load_standard_by_id,load_standard_by_name,load_sql_by_id,load_sql_by_name,load_sql_by_name_single,load_sql_by_phrase_list,load_standard_sql,load_standard_by_name_sql does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,3,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,load_standard_by_id,order of section load has difference at load_sql_by_id should be after load_standard_by_id of load_by_id,load_by_name,load_by_phr_lst,load_by_ids,load_standard_by_id,load_standard_by_name,load_sql_by_id,load_sql_by_name,load_sql_by_name_single,load_sql_by_phrase_list,load_standard_sql,load_standard_by_name_sql does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,3,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,load_standard_by_id,order of section load has difference at load_sql_by_id should be after load_standard_by_id of load_by_id,load_by_name,load_by_phr_lst,load_by_ids,load_standard_by_id,load_standard_by_name,load_sql_by_id,load_sql_by_name,load_sql_by_name_single,load_sql_by_phrase_list,load_standard_sql,load_standard_by_name_sql does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,3,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,load_standard_by_id,order of section load has difference at load_sql_by_id should be after load_standard_by_id of load_by_id,load_by_name,load_by_phr_lst,load_by_ids,load_standard_by_id,load_standard_by_name,load_sql_by_id,load_sql_by_name,load_sql_by_name_single,load_sql_by_phrase_list,load_standard_sql,load_standard_by_name_sql does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,3,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,load_standard_by_id,order of section load has difference at load_sql_by_id should be after load_standard_by_id of load_by_id,load_by_name,load_by_phr_lst,load_by_ids,load_standard_by_id,load_standard_by_name,load_sql_by_id,load_sql_by_name,load_sql_by_name_single,load_sql_by_phrase_list,load_standard_sql,load_standard_by_name_sql does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,3,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,load_standard_by_id,order of section load has difference at load_sql_by_id should be after load_standard_by_id of load_by_id,load_by_name,load_by_phr_lst,load_by_ids,load_standard_by_id,load_standard_by_name,load_sql_by_id,load_sql_by_name,load_sql_by_name_single,load_sql_by_phrase_list,load_standard_sql,load_standard_by_name_sql does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,3,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,load_standard_by_id,order of section load has difference at load_sql_by_id should be after load_standard_by_id of load_by_id,load_by_name,load_by_phr_lst,load_by_ids,load_standard_by_id,load_standard_by_name,load_sql_by_id,load_sql_by_name,load_sql_by_name_single,load_sql_by_phrase_list,load_standard_sql,load_standard_by_name_sql does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,3,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,load_standard_by_id,order of section debug has difference at name should be after dsp_id_medium of dsp_id,dsp_id_medium,dsp_id_short,name,name_generated,names does not match dsp_id,ids,name,dsp_id_medium,order of section debug has difference at name should be after dsp_id_medium of dsp_id,dsp_id_medium,dsp_id_short,name,name_generated,names does not match dsp_id,ids,name,dsp_id_medium,order of section debug has difference at name should be after dsp_id_medium of dsp_id,dsp_id_medium,dsp_id_short,name,name_generated,names does not match dsp_id,ids,name,dsp_id_medium,order of section debug has difference at name should be after dsp_id_medium of dsp_id,dsp_id_medium,dsp_id_short,name,name_generated,names does not match dsp_id,ids,name,dsp_id_medium
    \-- group_id
        \-- get_id - section for function get_id is expected to be set and get in /group/group_id.php
        \-- max_number_of_phrase - section for function max_number_of_phrase not yet defined that it should be database link in /group/group_id.php
        \-- get_array - section for function get_array is expected to be set and get in /group/group_id.php
        \-- count - section for function count not yet defined that it should be database link in /group/group_id.php
        \-- table_extension - section for function table_extension not yet defined that it should be database link in /group/group_id.php
        \-- table_type - section for function table_type not yet defined that it should be database link in /group/group_id.php
        \-- table_extension_list - section for function table_extension_list not yet defined that it should be database link in /group/group_id.php
        \-- is_prime - section for function is_prime not yet defined that it should be database link in /group/group_id.php
        \-- is_big - section for function is_big not yet defined that it should be database link in /group/group_id.php
        \-- int_array - section for function int_array not yet defined that it should be database link in /group/group_id.php
    \-- group_link
        \-- __construct - section for function __construct is expected to be construct and map in /group/group_link.php
        \-- row_mapper - section for function row_mapper missing in /group/group_link.php
        \-- load_sql - section for function load_sql is expected to be load sql in /group/group_link.php
    \-- group_list
        \-- load_sql_by_phr - section for function load_sql_by_phr is expected to be load sql in /group/group_list.php
        \-- load_sql_by_phr_single - section for function load_sql_by_phr_single is expected to be load sql in /group/group_list.php
        \-- load_sql_init - section for function load_sql_init is expected to be load sql in /group/group_list.php
        \-- load_sql_by_ids - section for function load_sql_by_ids is expected to be load sql in /group/group_list.php
        \-- del - section for function del is expected to be del in /group/group_list.php
        \-- add - section for function add not yet defined that it should be add in /group/group_list.php
        \-- get_by_val_with_one_phr_each - section for function get_by_val_with_one_phr_each is expected to be set and get in /group/group_list.php
        \-- get_by_val_special - section for function get_by_val_special is expected to be set and get in /group/group_list.php
        \-- get_by_res_with_one_phr_each - section for function get_by_res_with_one_phr_each is expected to be set and get in /group/group_list.php
        \-- get_by_res_special - section for function get_by_res_special is expected to be set and get in /group/group_list.php
        \-- remove_wrd_lst - section for function remove_wrd_lst not yet defined that it should be add in /group/group_list.php
        \-- common_phrases - section for function common_phrases not yet defined that it should be info in /group/group_list.php
        \-- name - section for function name not yet defined that it should be debug in /group/group_list.php
        \-- names - section for function names is expected to be info in /group/group_list.php
        \-- check - section for function check not yet defined that it should be debug in /group/group_list.php
    \-- id
        \-- int2alpha_num - section for function int2alpha_num missing in /group/id.php
    \-- result_id
        \-- get_id - section for function get_id is expected to be set and get in /group/result_id.php
    \-- combine_named
        \-- obj_id - section for function obj_id not yet defined that it should be set and get in /helper/combine_named.php
        \-- name - section for function name not yet defined that it should be set and get in /helper/combine_named.php
        \-- description - section for function description not yet defined that it should be set and get in /helper/combine_named.php
        \-- type_id - section for function type_id not yet defined that it should be set and get in /helper/combine_named.php
        \-- exclude - section for function exclude not yet defined that it should be set and get in /helper/combine_named.php
        \-- include - section for function include not yet defined that it should be set and get in /helper/combine_named.php
        \-- is_excluded - section for function is_excluded not yet defined that it should be set and get in /helper/combine_named.php
        \-- is_exclusion_set - section for function is_exclusion_set not yet defined that it should be set and get in /helper/combine_named.php
        \-- sql_view - section for function sql_view not yet defined that it should be SQL creation in /helper/combine_named.php
        \-- sql_create_view - section for function sql_create_view not yet defined that it should be SQL creation in /helper/combine_named.php
    \-- combine_object
        \-- obj - section for function obj not yet defined that it should be set and get in /helper/combine_object.php
        \-- isset - section for function isset not yet defined that it should be set and get in /helper/combine_object.php
        \-- api_json - section for function api_json not yet defined that it should be api in /helper/combine_object.php
        \-- id_field - section for function id_field not yet defined that it should be info in /helper/combine_object.php
        \-- db_ready - section for function db_ready not yet defined that it should be info in /helper/combine_object.php
    \-- config_numbers
        \-- default_json - section for function default_json not yet defined that it should be default in /helper/config_numbers.php
        \-- language - section for function language not yet defined that it should be predefined in /helper/config_numbers.php
    \-- data_object
        \-- api_json - section for function api_json not yet defined that it should be api in /helper/data_object.php
        \-- api_array - section for function api_array not yet defined that it should be api in /helper/data_object.php
        \-- user - section for function user not yet defined that it should be set and get in /helper/data_object.php
        \-- word_list - section for function word_list not yet defined that it should be set and get in /helper/data_object.php
        \-- verb_list - section for function verb_list not yet defined that it should be set and get in /helper/data_object.php
        \-- triple_list - section for function triple_list not yet defined that it should be set and get in /helper/data_object.php
        \-- phrase_list - section for function phrase_list not yet defined that it should be set and get in /helper/data_object.php
        \-- term_list - section for function term_list not yet defined that it should be set and get in /helper/data_object.php
        \-- source_list - section for function source_list not yet defined that it should be set and get in /helper/data_object.php
        \-- reference_list - section for function reference_list not yet defined that it should be set and get in /helper/data_object.php
        \-- value_list - section for function value_list not yet defined that it should be set and get in /helper/data_object.php
        \-- formula_list - section for function formula_list not yet defined that it should be set and get in /helper/data_object.php
        \-- formula_link_list - section for function formula_link_list not yet defined that it should be set and get in /helper/data_object.php
        \-- view_list - section for function view_list not yet defined that it should be set and get in /helper/data_object.php
        \-- component_list - section for function component_list not yet defined that it should be set and get in /helper/data_object.php
        \-- term_view_list - section for function term_view_list not yet defined that it should be set and get in /helper/data_object.php
        \-- user_list - section for function user_list not yet defined that it should be set and get in /helper/data_object.php
        \-- ip_range_list - section for function ip_range_list not yet defined that it should be set and get in /helper/data_object.php
        \-- view_relation_types - section for function view_relation_types not yet defined that it should be set and get in /helper/data_object.php
        \-- get_object_by_name - section for function get_object_by_name is expected to be set and get in /helper/data_object.php
        \-- get_word_by_name - section for function get_word_by_name is expected to be set and get in /helper/data_object.php
        \-- get_phrase_by_id - section for function get_phrase_by_id is expected to be set and get in /helper/data_object.php
        \-- get_phrase_by_name - section for function get_phrase_by_name is expected to be set and get in /helper/data_object.php
        \-- get_source_by_name - section for function get_source_by_name is expected to be set and get in /helper/data_object.php
        \-- get_formula_by_id - section for function get_formula_by_id is expected to be set and get in /helper/data_object.php
        \-- get_formula_by_name - section for function get_formula_by_name is expected to be set and get in /helper/data_object.php
        \-- get_term_by_name - section for function get_term_by_name is expected to be set and get in /helper/data_object.php
        \-- get_view_by_name - section for function get_view_by_name is expected to be set and get in /helper/data_object.php
        \-- load - section for function load not yet defined that it should be load in /helper/data_object.php
        \-- has_view_list - section for function has_view_list not yet defined that it should be info in /helper/data_object.php
        \-- add_word - section for function add_word not yet defined that it should be modify in /helper/data_object.php
        \-- add_verb - section for function add_verb not yet defined that it should be modify in /helper/data_object.php
        \-- add_triple - section for function add_triple not yet defined that it should be modify in /helper/data_object.php
        \-- add_triple_without_ready_check - section for function add_triple_without_ready_check not yet defined that it should be modify in /helper/data_object.php
        \-- add_phrase - section for function add_phrase not yet defined that it should be modify in /helper/data_object.php
        \-- add_source - section for function add_source not yet defined that it should be modify in /helper/data_object.php
        \-- add_reference - section for function add_reference not yet defined that it should be modify in /helper/data_object.php
        \-- add_formula - section for function add_formula not yet defined that it should be modify in /helper/data_object.php
        \-- add_formula_without_ready_check - section for function add_formula_without_ready_check not yet defined that it should be modify in /helper/data_object.php
        \-- add_term - section for function add_term not yet defined that it should be modify in /helper/data_object.php
        \-- add_view - section for function add_view not yet defined that it should be modify in /helper/data_object.php
        \-- add_component - section for function add_component not yet defined that it should be modify in /helper/data_object.php
        \-- add_term_view - section for function add_term_view not yet defined that it should be modify in /helper/data_object.php
        \-- add_user - section for function add_user not yet defined that it should be modify in /helper/data_object.php
        \-- add_ip_range - section for function add_ip_range not yet defined that it should be modify in /helper/data_object.php
        \-- add_value - section for function add_value not yet defined that it should be modify in /helper/data_object.php
        \-- add_message - section for function add_message not yet defined that it should be modify in /helper/data_object.php
        \-- get_component_by_name - section for function get_component_by_name is expected to be set and get in /helper/data_object.php
        \-- get_value_by_names - section for function get_value_by_names is expected to be set and get in /helper/data_object.php
        \-- expected_word_import_time - section for function expected_word_import_time not yet defined that it should be modify in /helper/data_object.php
        \-- expected_triple_import_time - section for function expected_triple_import_time not yet defined that it should be modify in /helper/data_object.php
        \-- expected_value_import_time - section for function expected_value_import_time not yet defined that it should be modify in /helper/data_object.php
        \-- expected_total_import_time - section for function expected_total_import_time not yet defined that it should be modify in /helper/data_object.php
        \-- count - section for function count not yet defined that it should be modify in /helper/data_object.php
        \-- save - section for function save is expected to be save in /helper/data_object.php
        \-- diff_msg - section for function diff_msg is expected to be info in /helper/data_object.php
    \-- db_id_object_non_sandbox
        \-- unique_value - section for function unique_value not yet defined that it should be set and get in /helper/db_id_object_non_sandbox.php
        \-- is_value_obj - section for function is_value_obj not yet defined that it should be settings in /helper/db_id_object_non_sandbox.php
        \-- sql_delete - section for function sql_delete not yet defined that it should be sql in /helper/db_id_object_non_sandbox.php
        \-- load_by_ip - section for function load_by_ip is expected to be load in /helper/db_id_object_non_sandbox.php
        \-- load_by_email - section for function load_by_email is expected to be load in /helper/db_id_object_non_sandbox.php
        \-- key_field - section for function key_field not yet defined that it should be overwrite in /helper/db_id_object_non_sandbox.php
        \-- import_mapper_user - section for function import_mapper_user is expected to be construct and map in /helper/db_id_object_non_sandbox.php
    \-- db_object
        \-- sql_table_create - section for function sql_table_create not yet defined that it should be sql create in /helper/db_object.php
        \-- sql_truncate_create - section for function sql_truncate_create not yet defined that it should be sql create in /helper/db_object.php
        \-- sql_index_create - section for function sql_index_create not yet defined that it should be sql create in /helper/db_object.php
        \-- sql_foreign_key_create - section for function sql_foreign_key_create not yet defined that it should be sql create in /helper/db_object.php
        \-- load_sql_multi - section for function load_sql_multi is expected to be load sql in /helper/db_object.php
        \-- load_sql - section for function load_sql is expected to be load sql in /helper/db_object.php
        \-- load_sql_by_id_str - section for function load_sql_by_id_str is expected to be load sql in /helper/db_object.php
        \-- id_field - section for function id_field not yet defined that it should be info in /helper/db_object.php
    \-- db_object_key
        \-- row_mapper - section for function row_mapper not yet defined that it should be construct and map in /helper/db_object_key.php
        \-- sql_table_create - section for function sql_table_create not yet defined that it should be sql create in /helper/db_object_key.php
        \-- sql_truncate_create - section for function sql_truncate_create not yet defined that it should be sql create in /helper/db_object_key.php
        \-- sql_index_create - section for function sql_index_create not yet defined that it should be sql create in /helper/db_object_key.php
        \-- sql_foreign_key_create - section for function sql_foreign_key_create not yet defined that it should be sql create in /helper/db_object_key.php
        \-- load_sql_multi - section for function load_sql_multi is expected to be load sql in /helper/db_object_key.php
        \-- load_sql - section for function load_sql is expected to be load sql in /helper/db_object_key.php
        \-- load_sql_by_id_str - section for function load_sql_by_id_str is expected to be load sql in /helper/db_object_key.php
        \-- id_field - section for function id_field not yet defined that it should be info in /helper/db_object_key.php
    \-- db_object_multi
        \-- row_mapper_multi - section for function row_mapper_multi not yet defined that it should be construct and map in /helper/db_object_multi.php
        \-- load_sql_by_id - section for function load_sql_by_id is expected to be load sql in /helper/db_object_multi.php
        \-- api_json - section for function api_json not yet defined that it should be api in /helper/db_object_multi.php
        \-- import_mapper - section for function import_mapper is expected to be construct and map in /helper/db_object_multi.php
        \-- id - section for function id not yet defined that it should be set and get in /helper/db_object_multi.php
        \-- isset - section for function isset not yet defined that it should be info in /helper/db_object_multi.php
        \-- name - section for function name not yet defined that it should be dummy functions that should always be overwritten by the child in /helper/db_object_multi.php
        \-- load_by_id - section for function load_by_id is expected to be load in /helper/db_object_multi.php
        \-- order error - order of section api has difference at api_json_array should be after api_json of api_json,api_json_array does not match 1,api_json_array,api_json,api_array,order of section api has difference at api_json_array should be after api_json of api_json,api_json_array does not match 1,api_json_array,api_json,api_array
    \-- db_object_multi_user
        \-- user - section for function user not yet defined that it should be set and get in /helper/db_object_multi_user.php
        \-- user_id - section for function user_id not yet defined that it should be set and get in /helper/db_object_multi_user.php
        \-- dsp_id_user - section for function dsp_id_user not yet defined that it should be debug in /helper/db_object_multi_user.php
    \-- db_object_no_id
        \-- row_mapper - section for function row_mapper not yet defined that it should be construct and map in /helper/db_object_no_id.php
        \-- sql_table_create - section for function sql_table_create not yet defined that it should be sql create in /helper/db_object_no_id.php
        \-- sql_truncate_create - section for function sql_truncate_create not yet defined that it should be sql create in /helper/db_object_no_id.php
        \-- sql_index_create - section for function sql_index_create not yet defined that it should be sql create in /helper/db_object_no_id.php
        \-- sql_foreign_key_create - section for function sql_foreign_key_create not yet defined that it should be sql create in /helper/db_object_no_id.php
        \-- load_sql_multi - section for function load_sql_multi is expected to be load sql in /helper/db_object_no_id.php
        \-- load_sql - section for function load_sql is expected to be load sql in /helper/db_object_no_id.php
        \-- load_sql_by_id_str - section for function load_sql_by_id_str is expected to be load sql in /helper/db_object_no_id.php
        \-- id_field - section for function id_field not yet defined that it should be info in /helper/db_object_no_id.php
    \-- db_object_seq_id
        \-- row_mapper - section for function row_mapper not yet defined that it should be construct and map in /helper/db_object_seq_id.php
        \-- sql_table - section for function sql_table not yet defined that it should be sql create in /helper/db_object_seq_id.php
        \-- sql_index - section for function sql_index not yet defined that it should be sql create in /helper/db_object_seq_id.php
        \-- sql_foreign_key - section for function sql_foreign_key not yet defined that it should be sql create in /helper/db_object_seq_id.php
        \-- load_sql_by_id - section for function load_sql_by_id is expected to be load sql in /helper/db_object_seq_id.php
        \-- api_json - section for function api_json not yet defined that it should be api in /helper/db_object_seq_id.php
        \-- import_mapper - section for function import_mapper is expected to be construct and map in /helper/db_object_seq_id.php
        \-- is_loaded - section for function is_loaded not yet defined that it should be info in /helper/db_object_seq_id.php
        \-- import_mapper_user - section for function import_mapper_user is expected to be construct and map in /helper/db_object_seq_id.php
        \-- name - section for function name not yet defined that it should be overwrite in /helper/db_object_seq_id.php
        \-- name_or_null - section for function name_or_null not yet defined that it should be overwrite in /helper/db_object_seq_id.php
        \-- load_by_id - section for function load_by_id is expected to be load in /helper/db_object_seq_id.php
        \-- load_by_name - section for function load_by_name is expected to be load in /helper/db_object_seq_id.php
        \-- save - section for function save is expected to be save in /helper/db_object_seq_id.php
        \-- del - section for function del is expected to be del in /helper/db_object_seq_id.php
        \-- order error - order of section construct and map has difference at api_mapper should be after row_mapper of row_mapper,api_mapper does not match __construct,row_mapper_sandbox,api_mapper,reset,row_mapper,order of section construct and map has difference at api_mapper should be after row_mapper of row_mapper,api_mapper does not match __construct,row_mapper_sandbox,api_mapper,reset,row_mapper,order of section api has difference at api_json_array should be after api_json of api_json,api_json_array does not match 1,api_json_array,api_json,api_array,order of section api has difference at api_json_array should be after api_json of api_json,api_json_array does not match 1,api_json_array,api_json,api_array
    \-- db_object_seq_id_user
        \-- user - section for function user not yet defined that it should be set and get in /helper/db_object_seq_id_user.php
        \-- user_id - section for function user_id not yet defined that it should be set and get in /helper/db_object_seq_id_user.php
        \-- has_id - section for function has_id not yet defined that it should be info in /helper/db_object_seq_id_user.php
        \-- dsp_id_user - section for function dsp_id_user not yet defined that it should be debug in /helper/db_object_seq_id_user.php
    \-- system_object
        \-- view_relation_types - section for function view_relation_types not yet defined that it should be interface in /helper/system_object.php
        \-- view_relation_name - section for function view_relation_name not yet defined that it should be interface in /helper/system_object.php
        \-- view_relation_code_id - section for function view_relation_code_id not yet defined that it should be interface in /helper/system_object.php
        \-- system_users - section for function system_users not yet defined that it should be interface in /helper/system_object.php
    \-- type_list
        \-- lst - section for function lst not yet defined that it should be set and get in /helper/type_list.php
        \-- hash - section for function hash not yet defined that it should be set and get in /helper/type_list.php
        \-- add - section for function add not yet defined that it should be interface set and get in /helper/type_list.php
        \-- add_direct - section for function add_direct not yet defined that it should be interface set and get in /helper/type_list.php
        \-- load_sql - section for function load_sql is expected to be load sql in /helper/type_list.php
        \-- load_sql_all - section for function load_sql_all is expected to be load sql in /helper/type_list.php
        \-- get_hash - section for function get_hash is expected to be set and get in /helper/type_list.php
        \-- get_name_hash - section for function get_name_hash is expected to be set and get in /helper/type_list.php
        \-- load - section for function load not yet defined that it should be database (dao) functions in /helper/type_list.php
        \-- api_json - section for function api_json not yet defined that it should be api in /helper/type_list.php
        \-- id - section for function id not yet defined that it should be im- and export in /helper/type_list.php
        \-- id_by_name - section for function id_by_name not yet defined that it should be im- and export in /helper/type_list.php
        \-- name - section for function name not yet defined that it should be im- and export in /helper/type_list.php
        \-- names - section for function names is expected to be info in /helper/type_list.php
        \-- name_or_null - section for function name_or_null not yet defined that it should be im- and export in /helper/type_list.php
        \-- get - section for function get not yet defined that it should be im- and export in /helper/type_list.php
        \-- has_code_id - section for function has_code_id not yet defined that it should be im- and export in /helper/type_list.php
        \-- get_by_code_id - section for function get_by_code_id is expected to be set and get in /helper/type_list.php
        \-- has_name - section for function has_name not yet defined that it should be im- and export in /helper/type_list.php
        \-- get_by_name - section for function get_by_name is expected to be set and get in /helper/type_list.php
        \-- code_id - section for function code_id not yet defined that it should be im- and export in /helper/type_list.php
        \-- count - section for function count not yet defined that it should be im- and export in /helper/type_list.php
        \-- is_empty - section for function is_empty not yet defined that it should be im- and export in /helper/type_list.php
        \-- load_dummy - section for function load_dummy is expected to be load in /helper/type_list.php
        \-- view_id_list - section for function view_id_list not yet defined that it should be unit test support functions in /helper/type_list.php
        \-- component_id_list - section for function component_id_list not yet defined that it should be unit test support functions in /helper/type_list.php
        \-- order error - order of section api has difference at api_json_array should be after api_json of api_json,api_json_array does not match 1,api_json_array,api_json,api_array,order of section api has difference at api_json_array should be after api_json of api_json,api_json_array does not match 1,api_json_array,api_json,api_array
    \-- type_lists
        \-- load - section for function load not yet defined that it should be load in /helper/type_lists.php
        \-- api_json - section for function api_json not yet defined that it should be api in /helper/type_lists.php
        \-- load_dummy - section for function load_dummy is expected to be load in /helper/type_lists.php
        \-- order error - order of section api has difference at api_json_array should be after api_json of api_json,api_json_array,load_dummy does not match 1,api_json_array,api_json,api_array,order of section api has difference at api_json_array should be after api_json of api_json,api_json_array,load_dummy does not match 1,api_json_array,api_json,api_array,order of section api has difference at api_json_array should be after api_json of api_json,api_json_array,load_dummy does not match 1,api_json_array,api_json,api_array
    \-- type_object
        \-- row_mapper_typ_obj - section for function row_mapper_typ_obj not yet defined that it should be construct and map in /helper/type_object.php
        \-- name - section for function name not yet defined that it should be set and get in /helper/type_object.php
        \-- code_id - section for function code_id not yet defined that it should be set and get in /helper/type_object.php
        \-- description - section for function description not yet defined that it should be set and get in /helper/type_object.php
        \-- is_type - section for function is_type not yet defined that it should be info in /helper/type_object.php
        \-- sql_table - section for function sql_table not yet defined that it should be sql create in /helper/type_object.php
        \-- sql_index - section for function sql_index not yet defined that it should be sql create in /helper/type_object.php
        \-- load_sql_by_id - section for function load_sql_by_id is expected to be load sql in /helper/type_object.php
        \-- load_sql_by_id_fwd - section for function load_sql_by_id_fwd is expected to be load sql in /helper/type_object.php
        \-- load_sql_by_name - section for function load_sql_by_name is expected to be load sql in /helper/type_object.php
        \-- load_sql_by_code_id - section for function load_sql_by_code_id is expected to be load sql in /helper/type_object.php
        \-- order error - order of section construct and map has difference at api_mapper should be after reset of __construct,reset,row_mapper_typ_obj,api_mapper,import_mapper does not match __construct,row_mapper_sandbox,api_mapper,reset,row_mapper,order of section construct and map has difference at api_mapper should be after reset of __construct,reset,row_mapper_typ_obj,api_mapper,import_mapper does not match __construct,row_mapper_sandbox,api_mapper,reset,row_mapper,order of section construct and map has difference at api_mapper should be after reset of __construct,reset,row_mapper_typ_obj,api_mapper,import_mapper does not match __construct,row_mapper_sandbox,api_mapper,reset,row_mapper,order of section construct and map has difference at api_mapper should be after reset of __construct,reset,row_mapper_typ_obj,api_mapper,import_mapper does not match __construct,row_mapper_sandbox,api_mapper,reset,row_mapper,order of section construct and map has difference at api_mapper should be after reset of __construct,reset,row_mapper_typ_obj,api_mapper,import_mapper does not match __construct,row_mapper_sandbox,api_mapper,reset,row_mapper,order of section set and get has difference at set_description should be after set_from_api, name should be after set_from_api, description should be after set_from_api of set_from_api,set_name,set_code_id,set_code_id_db,set_description,name,code_id,description does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,order of section set and get has difference at set_description should be after set_from_api, name should be after set_from_api, description should be after set_from_api of set_from_api,set_name,set_code_id,set_code_id_db,set_description,name,code_id,description does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,order of section set and get has difference at set_description should be after set_from_api, name should be after set_from_api, description should be after set_from_api of set_from_api,set_name,set_code_id,set_code_id_db,set_description,name,code_id,description does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,order of section set and get has difference at set_description should be after set_from_api, name should be after set_from_api, description should be after set_from_api of set_from_api,set_name,set_code_id,set_code_id_db,set_description,name,code_id,description does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,order of section set and get has difference at set_description should be after set_from_api, name should be after set_from_api, description should be after set_from_api of set_from_api,set_name,set_code_id,set_code_id_db,set_description,name,code_id,description does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,order of section set and get has difference at set_description should be after set_from_api, name should be after set_from_api, description should be after set_from_api of set_from_api,set_name,set_code_id,set_code_id_db,set_description,name,code_id,description does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,order of section set and get has difference at set_description should be after set_from_api, name should be after set_from_api, description should be after set_from_api of set_from_api,set_name,set_code_id,set_code_id_db,set_description,name,code_id,description does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,order of section set and get has difference at set_description should be after set_from_api, name should be after set_from_api, description should be after set_from_api of set_from_api,set_name,set_code_id,set_code_id_db,set_description,name,code_id,description does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id
    \-- convert_wikipedia_table
        \-- convert - section for function convert missing in /import/convert_wikipedia_table.php
        \-- convert_wiki_json - section for function convert_wiki_json missing in /import/convert_wikipedia_table.php
    \-- import
        \-- __construct - section for function __construct is expected to be construct and map in /import/import.php
        \-- set_start_time - section for function set_start_time is expected to be set and get in /import/import.php
        \-- display_progress - section for function display_progress not yet defined that it should be use to apply the time of the parent process for continuous timestamp reporting in /import/import.php
        \-- put_yaml - section for function put_yaml not yet defined that it should be use to apply the time of the parent process for continuous timestamp reporting in /import/import.php
        \-- put_json - section for function put_json not yet defined that it should be use to apply the time of the parent process for continuous timestamp reporting in /import/import.php
        \-- put_json_direct - section for function put_json_direct not yet defined that it should be use to apply the time of the parent process for continuous timestamp reporting in /import/import.php
        \-- get_data_object - section for function get_data_object is expected to be set and get in /import/import.php
        \-- step_main_start - section for function step_main_start not yet defined that it should be use to apply the time of the parent process for continuous timestamp reporting in /import/import.php
        \-- step_main_end - section for function step_main_end not yet defined that it should be use to apply the time of the parent process for continuous timestamp reporting in /import/import.php
        \-- step_start - section for function step_start not yet defined that it should be use to apply the time of the parent process for continuous timestamp reporting in /import/import.php
        \-- step_end - section for function step_end not yet defined that it should be use to apply the time of the parent process for continuous timestamp reporting in /import/import.php
        \-- end - section for function end not yet defined that it should be use to apply the time of the parent process for continuous timestamp reporting in /import/import.php
        \-- get_data_object_yaml - section for function get_data_object_yaml is expected to be set and get in /import/import.php
        \-- yaml_data_object_map_triple - section for function yaml_data_object_map_triple not yet defined that it should be use to apply the time of the parent process for continuous timestamp reporting in /import/import.php
        \-- status_text - section for function status_text not yet defined that it should be use to apply the time of the parent process for continuous timestamp reporting in /import/import.php
        \-- summary - section for function summary not yet defined that it should be use to apply the time of the parent process for continuous timestamp reporting in /import/import.php
        \-- seq_id - section for function seq_id not yet defined that it should be use to apply the time of the parent process for continuous timestamp reporting in /import/import.php
    \-- import_file
        \-- __construct - section for function __construct is expected to be construct and map in /import/import_file.php
        \-- set_start_time - section for function set_start_time is expected to be set and get in /import/import_file.php
        \-- json_file - section for function json_file not yet defined that it should be use to apply the time of the parent process for continuous timestamp reporting in /import/import_file.php
        \-- yaml_file - section for function yaml_file not yet defined that it should be use to apply the time of the parent process for continuous timestamp reporting in /import/import_file.php
        \-- import_config_yaml - section for function import_config_yaml not yet defined that it should be use to apply the time of the parent process for continuous timestamp reporting in /import/import_file.php
        \-- import_base_config - section for function import_base_config not yet defined that it should be use to apply the time of the parent process for continuous timestamp reporting in /import/import_file.php
        \-- import_pod_config - section for function import_pod_config not yet defined that it should be use to apply the time of the parent process for continuous timestamp reporting in /import/import_file.php
        \-- import_test_config - section for function import_test_config not yet defined that it should be use to apply the time of the parent process for continuous timestamp reporting in /import/import_file.php
        \-- echo - section for function echo not yet defined that it should be use to apply the time of the parent process for continuous timestamp reporting in /import/import_file.php
    \-- change
        \-- row_mapper - section for function row_mapper not yet defined that it should be construct and map in /log/change.php
        \-- load_sql - section for function load_sql is expected to be load sql in /log/change.php
        \-- load_sql_by_user - section for function load_sql_by_user is expected to be load sql in /log/change.php
        \-- load_sql_old - section for function load_sql_old is expected to be load sql in /log/change.php
        \-- use_type_id - section for function use_type_id not yet defined that it should be info in /log/change.php
        \-- add_ref - section for function add_ref not yet defined that it should be save in /log/change.php
        \-- sql_type - section for function sql_type not yet defined that it should be sql write in /log/change.php
        \-- sql_sub_type - section for function sql_sub_type not yet defined that it should be sql write in /log/change.php
        \-- db_field_values_types - section for function db_field_values_types not yet defined that it should be sql write fields in /log/change.php
        \-- db_fields - section for function db_fields not yet defined that it should be sql write fields in /log/change.php
        \-- db_values - section for function db_values not yet defined that it should be sql write fields in /log/change.php
        \-- dsp - section for function dsp not yet defined that it should be format in /log/change.php
        \-- date_time_format - section for function date_time_format not yet defined that it should be format in /log/change.php
    \-- change_link
        \-- row_mapper - section for function row_mapper not yet defined that it should be database link in /log/change_link.php
        \-- load_sql_by_user - section for function load_sql_by_user is expected to be load sql in /log/change_link.php
        \-- load_sql_by_vars - section for function load_sql_by_vars is expected to be load sql in /log/change_link.php
        \-- load_last_by_user - section for function load_last_by_user is expected to be load in /log/change_link.php
        \-- add_link_ref - section for function add_link_ref not yet defined that it should be database link in /log/change_link.php
        \-- add_link - section for function add_link not yet defined that it should be database link in /log/change_link.php
        \-- dsp_last - section for function dsp_last not yet defined that it should be database link in /log/change_link.php
        \-- add - section for function add not yet defined that it should be database link in /log/change_link.php
        \-- add_ref - section for function add_ref not yet defined that it should be database link in /log/change_link.php
        \-- sql_insert_link - section for function sql_insert_link not yet defined that it should be sql write in /log/change_link.php
        \-- db_field_values_link_types - section for function db_field_values_link_types not yet defined that it should be sql write fields in /log/change_link.php
        \-- db_fields - section for function db_fields not yet defined that it should be sql write fields in /log/change_link.php
        \-- db_values - section for function db_values not yet defined that it should be sql write fields in /log/change_link.php
    \-- change_log
        \-- action - section for function action not yet defined that it should be set and get in /log/change_log.php
        \-- table - section for function table not yet defined that it should be set and get in /log/change_log.php
        \-- field - section for function field not yet defined that it should be set and get in /log/change_log.php
        \-- time - section for function time not yet defined that it should be set and get in /log/change_log.php
        \-- sql_table - section for function sql_table not yet defined that it should be sql create in /log/change_log.php
        \-- sql_index - section for function sql_index not yet defined that it should be sql create in /log/change_log.php
        \-- sql_foreign_key - section for function sql_foreign_key not yet defined that it should be sql create in /log/change_log.php
        \-- create_log_references - section for function create_log_references not yet defined that it should be init in /log/change_log.php
        \-- load_sql_by_field_row - section for function load_sql_by_field_row is expected to be load sql in /log/change_log.php
        \-- dsp_last - section for function dsp_last not yet defined that it should be modify in /log/change_log.php
        \-- add_ref - section for function add_ref not yet defined that it should be modify in /log/change_log.php
        \-- use_type_id - section for function use_type_id not yet defined that it should be modify in /log/change_log.php
        \-- sql_insert - section for function sql_insert not yet defined that it should be sql write in /log/change_log.php
        \-- sql_type - section for function sql_type not yet defined that it should be sql write in /log/change_log.php
        \-- sql_sub_type - section for function sql_sub_type not yet defined that it should be sql write in /log/change_log.php
        \-- sql_insert_link - section for function sql_insert_link not yet defined that it should be sql write in /log/change_log.php
        \-- db_field_values_types - section for function db_field_values_types not yet defined that it should be sql write fields in /log/change_log.php
        \-- db_fields - section for function db_fields not yet defined that it should be sql write fields in /log/change_log.php
        \-- db_values - section for function db_values not yet defined that it should be sql write fields in /log/change_log.php
        \-- add - section for function add not yet defined that it should be save in /log/change_log.php
    \-- change_log_list
        \-- load_sql_obj_fld - section for function load_sql_obj_fld is expected to be load sql in /log/change_log_list.php
        \-- load_sql_obj_last - section for function load_sql_obj_last is expected to be load sql in /log/change_log_list.php
        \-- add - section for function add not yet defined that it should be modify in /log/change_log_list.php
        \-- first_msg - section for function first_msg not yet defined that it should be info in /log/change_log_list.php
    \-- change_table_field
        \-- sql_view_link - section for function sql_view_link not yet defined that it should be SQL creation in /log/change_table_field.php
    \-- change_value
        \-- row_mapper - section for function row_mapper not yet defined that it should be construct and map in /log/change_value.php
        \-- load_sql - section for function load_sql is expected to be load sql in /log/change_value.php
        \-- sql_type - section for function sql_type not yet defined that it should be sql write in /log/change_value.php
        \-- db_field_values_types - section for function db_field_values_types not yet defined that it should be sql write fields in /log/change_value.php
        \-- db_fields - section for function db_fields not yet defined that it should be sql write fields in /log/change_value.php
        \-- db_values - section for function db_values not yet defined that it should be sql write fields in /log/change_value.php
        \-- name - section for function name not yet defined that it should be debug in /log/change_value.php
    \-- change_value_geo
        \-- load_sql - section for function load_sql is expected to be load sql in /log/change_value_geo.php
        \-- db_field_values_types - section for function db_field_values_types not yet defined that it should be sql write fields in /log/change_value_geo.php
    \-- change_value_text
        \-- load_sql - section for function load_sql is expected to be load sql in /log/change_value_text.php
        \-- db_field_values_types - section for function db_field_values_types not yet defined that it should be sql write fields in /log/change_value_text.php
    \-- change_value_time
        \-- load_sql - section for function load_sql is expected to be load sql in /log/change_value_time.php
        \-- db_field_values_types - section for function db_field_values_types not yet defined that it should be sql write fields in /log/change_value_time.php
    \-- text_log
        \-- start_time - section for function start_time not yet defined that it should be set and get in /log_text/text_log.php
        \-- header - section for function header not yet defined that it should be display in /log_text/text_log.php
        \-- subheader - section for function subheader not yet defined that it should be display in /log_text/text_log.php
        \-- echo_log - section for function echo_log not yet defined that it should be display in /log_text/text_log.php
    \-- phr_ids
        \-- __construct - section for function __construct is expected to be construct and map in /phrase/phr_ids.php
        \-- count - section for function count missing in /phrase/phr_ids.php
        \-- wrd_ids - section for function wrd_ids missing in /phrase/phr_ids.php
        \-- trp_ids - section for function trp_ids missing in /phrase/phr_ids.php
        \-- trm_ids - section for function trm_ids missing in /phrase/phr_ids.php
        \-- order error - order of section  has difference at count should be after __construct of __construct,count,wrd_ids,trp_ids,trm_ids does not match start_api_core,start_api,end_api,start,open_db,end,load_dummy,default_id,add,add_field,add_value,add_par_field,add_par_field_id,get,name,value,type,pos,names,values,types,has,count,names_or_const,sql_names,__construct,has_par,merge,combine,is_function,is_or,is_list,is_text,prefix,is_sql_change,is_val_type,export_by_phrase_list,table_ext_list,row_mapper,int2alpha_num,convert,convert_wiki_json,log_debug,log_info,log_warning,log_err,log_fatal_db,log_fatal,log_msg,order of section  has difference at count should be after __construct of __construct,count,wrd_ids,trp_ids,trm_ids does not match start_api_core,start_api,end_api,start,open_db,end,load_dummy,default_id,add,add_field,add_value,add_par_field,add_par_field_id,get,name,value,type,pos,names,values,types,has,count,names_or_const,sql_names,__construct,has_par,merge,combine,is_function,is_or,is_list,is_text,prefix,is_sql_change,is_val_type,export_by_phrase_list,table_ext_list,row_mapper,int2alpha_num,convert,convert_wiki_json,log_debug,log_info,log_warning,log_err,log_fatal_db,log_fatal,log_msg,order of section  has difference at count should be after __construct of __construct,count,wrd_ids,trp_ids,trm_ids does not match start_api_core,start_api,end_api,start,open_db,end,load_dummy,default_id,add,add_field,add_value,add_par_field,add_par_field_id,get,name,value,type,pos,names,values,types,has,count,names_or_const,sql_names,__construct,has_par,merge,combine,is_function,is_or,is_list,is_text,prefix,is_sql_change,is_val_type,export_by_phrase_list,table_ext_list,row_mapper,int2alpha_num,convert,convert_wiki_json,log_debug,log_info,log_warning,log_err,log_fatal_db,log_fatal,log_msg,order of section  has difference at count should be after __construct of __construct,count,wrd_ids,trp_ids,trm_ids does not match start_api_core,start_api,end_api,start,open_db,end,load_dummy,default_id,add,add_field,add_value,add_par_field,add_par_field_id,get,name,value,type,pos,names,values,types,has,count,names_or_const,sql_names,__construct,has_par,merge,combine,is_function,is_or,is_list,is_text,prefix,is_sql_change,is_val_type,export_by_phrase_list,table_ext_list,row_mapper,int2alpha_num,convert,convert_wiki_json,log_debug,log_info,log_warning,log_err,log_fatal_db,log_fatal,log_msg,order of section  has difference at count should be after __construct of __construct,count,wrd_ids,trp_ids,trm_ids does not match start_api_core,start_api,end_api,start,open_db,end,load_dummy,default_id,add,add_field,add_value,add_par_field,add_par_field_id,get,name,value,type,pos,names,values,types,has,count,names_or_const,sql_names,__construct,has_par,merge,combine,is_function,is_or,is_list,is_text,prefix,is_sql_change,is_val_type,export_by_phrase_list,table_ext_list,row_mapper,int2alpha_num,convert,convert_wiki_json,log_debug,log_info,log_warning,log_err,log_fatal_db,log_fatal,log_msg
    \-- phrase
        \-- obj - section for function obj not yet defined that it should be set and get in /phrase/phrase.php
        \-- user_id - section for function user_id not yet defined that it should be set and get in /phrase/phrase.php
        \-- owner_id - section for function owner_id not yet defined that it should be set and get in /phrase/phrase.php
        \-- code_id - section for function code_id not yet defined that it should be set and get in /phrase/phrase.php
        \-- share_id - section for function share_id not yet defined that it should be set and get in /phrase/phrase.php
        \-- protection_id - section for function protection_id not yet defined that it should be set and get in /phrase/phrase.php
        \-- id - section for function id not yet defined that it should be set and get in /phrase/phrase.php
        \-- id_obj - section for function id_obj not yet defined that it should be set and get in /phrase/phrase.php
        \-- name - section for function name not yet defined that it should be set and get in /phrase/phrase.php
        \-- user - section for function user not yet defined that it should be set and get in /phrase/phrase.php
        \-- usage - section for function usage not yet defined that it should be set and get in /phrase/phrase.php
        \-- impact - section for function impact not yet defined that it should be set and get in /phrase/phrase.php
        \-- can_be_ready - section for function can_be_ready not yet defined that it should be set and get in /phrase/phrase.php
        \-- db_ready - section for function db_ready not yet defined that it should be set and get in /phrase/phrase.php
        \-- is_valid - section for function is_valid not yet defined that it should be set and get in /phrase/phrase.php
        \-- word - section for function word not yet defined that it should be cast in /phrase/phrase.php
        \-- triple - section for function triple not yet defined that it should be cast in /phrase/phrase.php
        \-- term - section for function term not yet defined that it should be cast in /phrase/phrase.php
        \-- import_mapper - section for function import_mapper is expected to be construct and map in /phrase/phrase.php
        \-- load - section for function load not yet defined that it should be load in /phrase/phrase.php
        \-- load_sql_by_name - section for function load_sql_by_name is expected to be load sql in /phrase/phrase.php
        \-- load_sql_by_id - section for function load_sql_by_id is expected to be load sql in /phrase/phrase.php
        \-- load_sql - section for function load_sql is expected to be load sql in /phrase/phrase.php
        \-- load_obj - section for function load_obj is expected to be load in /phrase/phrase.php
        \-- main_word - section for function main_word not yet defined that it should be load related in /phrase/phrase.php
        \-- wrd_lst - section for function wrd_lst not yet defined that it should be load related in /phrase/phrase.php
        \-- type_id - section for function type_id not yet defined that it should be load related in /phrase/phrase.php
        \-- type_code_id - section for function type_code_id is expected to be preloaded in /phrase/phrase.php
        \-- formula - section for function formula not yet defined that it should be load related in /phrase/phrase.php
        \-- val_lst - section for function val_lst not yet defined that it should be data retrieval in /phrase/phrase.php
        \-- vrb_lst - section for function vrb_lst not yet defined that it should be data retrieval in /phrase/phrase.php
        \-- all_parents - section for function all_parents not yet defined that it should be data retrieval in /phrase/phrase.php
        \-- all_children - section for function all_children not yet defined that it should be data retrieval in /phrase/phrase.php
        \-- all_related - section for function all_related not yet defined that it should be data retrieval in /phrase/phrase.php
        \-- groups - section for function groups not yet defined that it should be data retrieval in /phrase/phrase.php
        \-- lst - section for function lst not yet defined that it should be data retrieval in /phrase/phrase.php
        \-- direct_children - section for function direct_children not yet defined that it should be data retrieval in /phrase/phrase.php
        \-- is - section for function is not yet defined that it should be data retrieval in /phrase/phrase.php
        \-- is_word - section for function is_word not yet defined that it should be classification in /phrase/phrase.php
        \-- is_triple - section for function is_triple not yet defined that it should be classification in /phrase/phrase.php
        \-- is_formula - section for function is_formula not yet defined that it should be classification in /phrase/phrase.php
        \-- is_type - section for function is_type not yet defined that it should be info in /phrase/phrase.php
        \-- no_id_but_name - section for function no_id_but_name not yet defined that it should be info in /phrase/phrase.php
        \-- is_excluded - section for function is_excluded not yet defined that it should be info in /phrase/phrase.php
        \-- is_a - section for function is_a not yet defined that it should be info in /phrase/phrase.php
        \-- sql_list - section for function sql_list not yet defined that it should be info in /phrase/phrase.php
        \-- is_mainly - section for function is_mainly not yet defined that it should be display functions in /phrase/phrase.php
        \-- is_time - section for function is_time not yet defined that it should be forwards in /phrase/phrase.php
        \-- is_measure - section for function is_measure not yet defined that it should be forwards in /phrase/phrase.php
        \-- is_scaling - section for function is_scaling not yet defined that it should be forwards in /phrase/phrase.php
        \-- is_percent - section for function is_percent not yet defined that it should be forwards in /phrase/phrase.php
        \-- next - section for function next not yet defined that it should be forwards in /phrase/phrase.php
        \-- prior - section for function prior not yet defined that it should be forwards in /phrase/phrase.php
        \-- del - section for function del is expected to be del in /phrase/phrase.php
        \-- get_or_add - section for function get_or_add is expected to be set and get in /phrase/phrase.php
        \-- dsp_name - section for function dsp_name not yet defined that it should be display functions in /phrase/phrase.php
        \-- name_linked - section for function name_linked not yet defined that it should be display functions in /phrase/phrase.php
        \-- phrases - section for function phrases not yet defined that it should be display functions in /phrase/phrase.php
        \-- display - section for function display not yet defined that it should be display functions in /phrase/phrase.php
        \-- display_linked - section for function display_linked not yet defined that it should be display functions in /phrase/phrase.php
        \-- dsp_link_style - section for function dsp_link_style not yet defined that it should be display functions in /phrase/phrase.php
        \-- dsp_time_selector - section for function dsp_time_selector not yet defined that it should be display functions in /phrase/phrase.php
        \-- predicate_id - section for function predicate_id not yet defined that it should be display functions in /phrase/phrase.php
        \-- order error - order of section set and get has difference at id should be after user_id, name should be after user_id, user should be after user_id of obj,set_id,set_obj_from_id,set_id_from_obj,set_name,set_user,user_id,owner_id,code_id,share_id,protection_id,set_usage,set_impact,id,id_obj,name,user,usage,impact,can_be_ready,db_ready,is_valid does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,order of section set and get has difference at id should be after user_id, name should be after user_id, user should be after user_id of obj,set_id,set_obj_from_id,set_id_from_obj,set_name,set_user,user_id,owner_id,code_id,share_id,protection_id,set_usage,set_impact,id,id_obj,name,user,usage,impact,can_be_ready,db_ready,is_valid does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,order of section set and get has difference at id should be after user_id, name should be after user_id, user should be after user_id of obj,set_id,set_obj_from_id,set_id_from_obj,set_name,set_user,user_id,owner_id,code_id,share_id,protection_id,set_usage,set_impact,id,id_obj,name,user,usage,impact,can_be_ready,db_ready,is_valid does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,order of section set and get has difference at id should be after user_id, name should be after user_id, user should be after user_id of obj,set_id,set_obj_from_id,set_id_from_obj,set_name,set_user,user_id,owner_id,code_id,share_id,protection_id,set_usage,set_impact,id,id_obj,name,user,usage,impact,can_be_ready,db_ready,is_valid does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,order of section set and get has difference at id should be after user_id, name should be after user_id, user should be after user_id of obj,set_id,set_obj_from_id,set_id_from_obj,set_name,set_user,user_id,owner_id,code_id,share_id,protection_id,set_usage,set_impact,id,id_obj,name,user,usage,impact,can_be_ready,db_ready,is_valid does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,order of section set and get has difference at id should be after user_id, name should be after user_id, user should be after user_id of obj,set_id,set_obj_from_id,set_id_from_obj,set_name,set_user,user_id,owner_id,code_id,share_id,protection_id,set_usage,set_impact,id,id_obj,name,user,usage,impact,can_be_ready,db_ready,is_valid does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,order of section set and get has difference at id should be after user_id, name should be after user_id, user should be after user_id of obj,set_id,set_obj_from_id,set_id_from_obj,set_name,set_user,user_id,owner_id,code_id,share_id,protection_id,set_usage,set_impact,id,id_obj,name,user,usage,impact,can_be_ready,db_ready,is_valid does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,order of section set and get has difference at id should be after user_id, name should be after user_id, user should be after user_id of obj,set_id,set_obj_from_id,set_id_from_obj,set_name,set_user,user_id,owner_id,code_id,share_id,protection_id,set_usage,set_impact,id,id_obj,name,user,usage,impact,can_be_ready,db_ready,is_valid does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,order of section set and get has difference at id should be after user_id, name should be after user_id, user should be after user_id of obj,set_id,set_obj_from_id,set_id_from_obj,set_name,set_user,user_id,owner_id,code_id,share_id,protection_id,set_usage,set_impact,id,id_obj,name,user,usage,impact,can_be_ready,db_ready,is_valid does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,order of section set and get has difference at id should be after user_id, name should be after user_id, user should be after user_id of obj,set_id,set_obj_from_id,set_id_from_obj,set_name,set_user,user_id,owner_id,code_id,share_id,protection_id,set_usage,set_impact,id,id_obj,name,user,usage,impact,can_be_ready,db_ready,is_valid does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,order of section set and get has difference at id should be after user_id, name should be after user_id, user should be after user_id of obj,set_id,set_obj_from_id,set_id_from_obj,set_name,set_user,user_id,owner_id,code_id,share_id,protection_id,set_usage,set_impact,id,id_obj,name,user,usage,impact,can_be_ready,db_ready,is_valid does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,order of section set and get has difference at id should be after user_id, name should be after user_id, user should be after user_id of obj,set_id,set_obj_from_id,set_id_from_obj,set_name,set_user,user_id,owner_id,code_id,share_id,protection_id,set_usage,set_impact,id,id_obj,name,user,usage,impact,can_be_ready,db_ready,is_valid does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,order of section set and get has difference at id should be after user_id, name should be after user_id, user should be after user_id of obj,set_id,set_obj_from_id,set_id_from_obj,set_name,set_user,user_id,owner_id,code_id,share_id,protection_id,set_usage,set_impact,id,id_obj,name,user,usage,impact,can_be_ready,db_ready,is_valid does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,order of section set and get has difference at id should be after user_id, name should be after user_id, user should be after user_id of obj,set_id,set_obj_from_id,set_id_from_obj,set_name,set_user,user_id,owner_id,code_id,share_id,protection_id,set_usage,set_impact,id,id_obj,name,user,usage,impact,can_be_ready,db_ready,is_valid does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,order of section set and get has difference at id should be after user_id, name should be after user_id, user should be after user_id of obj,set_id,set_obj_from_id,set_id_from_obj,set_name,set_user,user_id,owner_id,code_id,share_id,protection_id,set_usage,set_impact,id,id_obj,name,user,usage,impact,can_be_ready,db_ready,is_valid does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,order of section set and get has difference at id should be after user_id, name should be after user_id, user should be after user_id of obj,set_id,set_obj_from_id,set_id_from_obj,set_name,set_user,user_id,owner_id,code_id,share_id,protection_id,set_usage,set_impact,id,id_obj,name,user,usage,impact,can_be_ready,db_ready,is_valid does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,order of section set and get has difference at id should be after user_id, name should be after user_id, user should be after user_id of obj,set_id,set_obj_from_id,set_id_from_obj,set_name,set_user,user_id,owner_id,code_id,share_id,protection_id,set_usage,set_impact,id,id_obj,name,user,usage,impact,can_be_ready,db_ready,is_valid does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,order of section set and get has difference at id should be after user_id, name should be after user_id, user should be after user_id of obj,set_id,set_obj_from_id,set_id_from_obj,set_name,set_user,user_id,owner_id,code_id,share_id,protection_id,set_usage,set_impact,id,id_obj,name,user,usage,impact,can_be_ready,db_ready,is_valid does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,order of section set and get has difference at id should be after user_id, name should be after user_id, user should be after user_id of obj,set_id,set_obj_from_id,set_id_from_obj,set_name,set_user,user_id,owner_id,code_id,share_id,protection_id,set_usage,set_impact,id,id_obj,name,user,usage,impact,can_be_ready,db_ready,is_valid does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,order of section set and get has difference at id should be after user_id, name should be after user_id, user should be after user_id of obj,set_id,set_obj_from_id,set_id_from_obj,set_name,set_user,user_id,owner_id,code_id,share_id,protection_id,set_usage,set_impact,id,id_obj,name,user,usage,impact,can_be_ready,db_ready,is_valid does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,order of section set and get has difference at id should be after user_id, name should be after user_id, user should be after user_id of obj,set_id,set_obj_from_id,set_id_from_obj,set_name,set_user,user_id,owner_id,code_id,share_id,protection_id,set_usage,set_impact,id,id_obj,name,user,usage,impact,can_be_ready,db_ready,is_valid does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,order of section set and get has difference at id should be after user_id, name should be after user_id, user should be after user_id of obj,set_id,set_obj_from_id,set_id_from_obj,set_name,set_user,user_id,owner_id,code_id,share_id,protection_id,set_usage,set_impact,id,id_obj,name,user,usage,impact,can_be_ready,db_ready,is_valid does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,order of section load has difference at load_by_id should be after load_by_name, load should be after load_by_name, load_sql_by_id should be after load_by_name, load_sql should be after load_by_name of load_by_name,load_by_id,load,load_sql_by_name,load_sql_by_id,load_sql does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,load_by_ids,load_by_view_id,1,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,load_by_group_id_sql,load_by_phr,load_sql_by_phr,load_sql_by_phr_single,load_sql_init,load_names_sql_by_ids,load_sql_by_ids,del,load_cfg,load_frontend_cfg,load_usr_cfg,load,load_system_views,load_by_key,load_sql_by_id_str,load_type_lists,load_system_users,load_by_id,load_by_name,load_sql_by_user,load_by_user_sql,load_sql_old,load_by_field_row,load_sql_by_field_row,load_by_user,load_obj_last,load_obj_field_last,load_by_obj_fld,load_by_fld_of_wrd,load_by_fld_of_vrb,load_by_fld_of_trp,load_by_fld_of_val,load_by_fld_of_frm,load_by_fld_of_src,load_by_fld_of_ui,load_by_fld_of_cmp,order of section load has difference at load_by_id should be after load_by_name, load should be after load_by_name, load_sql_by_id should be after load_by_name, load_sql should be after load_by_name of load_by_name,load_by_id,load,load_sql_by_name,load_sql_by_id,load_sql does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,load_by_ids,load_by_view_id,1,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,load_by_group_id_sql,load_by_phr,load_sql_by_phr,load_sql_by_phr_single,load_sql_init,load_names_sql_by_ids,load_sql_by_ids,del,load_cfg,load_frontend_cfg,load_usr_cfg,load,load_system_views,load_by_key,load_sql_by_id_str,load_type_lists,load_system_users,load_by_id,load_by_name,load_sql_by_user,load_by_user_sql,load_sql_old,load_by_field_row,load_sql_by_field_row,load_by_user,load_obj_last,load_obj_field_last,load_by_obj_fld,load_by_fld_of_wrd,load_by_fld_of_vrb,load_by_fld_of_trp,load_by_fld_of_val,load_by_fld_of_frm,load_by_fld_of_src,load_by_fld_of_ui,load_by_fld_of_cmp,order of section load has difference at load_by_id should be after load_by_name, load should be after load_by_name, load_sql_by_id should be after load_by_name, load_sql should be after load_by_name of load_by_name,load_by_id,load,load_sql_by_name,load_sql_by_id,load_sql does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,load_by_ids,load_by_view_id,1,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,load_by_group_id_sql,load_by_phr,load_sql_by_phr,load_sql_by_phr_single,load_sql_init,load_names_sql_by_ids,load_sql_by_ids,del,load_cfg,load_frontend_cfg,load_usr_cfg,load,load_system_views,load_by_key,load_sql_by_id_str,load_type_lists,load_system_users,load_by_id,load_by_name,load_sql_by_user,load_by_user_sql,load_sql_old,load_by_field_row,load_sql_by_field_row,load_by_user,load_obj_last,load_obj_field_last,load_by_obj_fld,load_by_fld_of_wrd,load_by_fld_of_vrb,load_by_fld_of_trp,load_by_fld_of_val,load_by_fld_of_frm,load_by_fld_of_src,load_by_fld_of_ui,load_by_fld_of_cmp,order of section load has difference at load_by_id should be after load_by_name, load should be after load_by_name, load_sql_by_id should be after load_by_name, load_sql should be after load_by_name of load_by_name,load_by_id,load,load_sql_by_name,load_sql_by_id,load_sql does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,load_by_ids,load_by_view_id,1,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,load_by_group_id_sql,load_by_phr,load_sql_by_phr,load_sql_by_phr_single,load_sql_init,load_names_sql_by_ids,load_sql_by_ids,del,load_cfg,load_frontend_cfg,load_usr_cfg,load,load_system_views,load_by_key,load_sql_by_id_str,load_type_lists,load_system_users,load_by_id,load_by_name,load_sql_by_user,load_by_user_sql,load_sql_old,load_by_field_row,load_sql_by_field_row,load_by_user,load_obj_last,load_obj_field_last,load_by_obj_fld,load_by_fld_of_wrd,load_by_fld_of_vrb,load_by_fld_of_trp,load_by_fld_of_val,load_by_fld_of_frm,load_by_fld_of_src,load_by_fld_of_ui,load_by_fld_of_cmp,order of section load has difference at load_by_id should be after load_by_name, load should be after load_by_name, load_sql_by_id should be after load_by_name, load_sql should be after load_by_name of load_by_name,load_by_id,load,load_sql_by_name,load_sql_by_id,load_sql does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,load_by_ids,load_by_view_id,1,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,load_by_group_id_sql,load_by_phr,load_sql_by_phr,load_sql_by_phr_single,load_sql_init,load_names_sql_by_ids,load_sql_by_ids,del,load_cfg,load_frontend_cfg,load_usr_cfg,load,load_system_views,load_by_key,load_sql_by_id_str,load_type_lists,load_system_users,load_by_id,load_by_name,load_sql_by_user,load_by_user_sql,load_sql_old,load_by_field_row,load_sql_by_field_row,load_by_user,load_obj_last,load_obj_field_last,load_by_obj_fld,load_by_fld_of_wrd,load_by_fld_of_vrb,load_by_fld_of_trp,load_by_fld_of_val,load_by_fld_of_frm,load_by_fld_of_src,load_by_fld_of_ui,load_by_fld_of_cmp,order of section load has difference at load_by_id should be after load_by_name, load should be after load_by_name, load_sql_by_id should be after load_by_name, load_sql should be after load_by_name of load_by_name,load_by_id,load,load_sql_by_name,load_sql_by_id,load_sql does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,load_by_ids,load_by_view_id,1,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,load_by_group_id_sql,load_by_phr,load_sql_by_phr,load_sql_by_phr_single,load_sql_init,load_names_sql_by_ids,load_sql_by_ids,del,load_cfg,load_frontend_cfg,load_usr_cfg,load,load_system_views,load_by_key,load_sql_by_id_str,load_type_lists,load_system_users,load_by_id,load_by_name,load_sql_by_user,load_by_user_sql,load_sql_old,load_by_field_row,load_sql_by_field_row,load_by_user,load_obj_last,load_obj_field_last,load_by_obj_fld,load_by_fld_of_wrd,load_by_fld_of_vrb,load_by_fld_of_trp,load_by_fld_of_val,load_by_fld_of_frm,load_by_fld_of_src,load_by_fld_of_ui,load_by_fld_of_cmp
    \-- phrase_list
        \-- load_sql_like - section for function load_sql_like is expected to be load sql in /phrase/phrase_list.php
        \-- load_sql_by_names - section for function load_sql_by_names is expected to be load sql in /phrase/phrase_list.php
        \-- load_sql_by_ids - section for function load_sql_by_ids is expected to be load sql in /phrase/phrase_list.php
        \-- load_sql - section for function load_sql is expected to be load sql in /phrase/phrase_list.php
        \-- load_names_sql_by_ids - section for function load_names_sql_by_ids is expected to be load in /phrase/phrase_list.php
        \-- load_sql_by_phr_lst - section for function load_sql_by_phr_lst is expected to be load sql in /phrase/phrase_list.php
        \-- load_names_by_ids - section for function load_names_by_ids is expected to be load in /phrase/phrase_list.php
        \-- load_names - section for function load_names is expected to be load in /phrase/phrase_list.php
        \-- term_list - section for function term_list not yet defined that it should be cast in /phrase/phrase_list.php
        \-- fill_missing_verbs - section for function fill_missing_verbs not yet defined that it should be set and get in /phrase/phrase_list.php
        \-- name_pos_lst - section for function name_pos_lst not yet defined that it should be set and get in /phrase/phrase_list.php
        \-- import_lst - section for function import_lst not yet defined that it should be im- and export in /phrase/phrase_list.php
        \-- import_map_names - section for function import_map_names not yet defined that it should be im- and export in /phrase/phrase_list.php
        \-- import_names - section for function import_names not yet defined that it should be im- and export in /phrase/phrase_list.php
        \-- import_context - section for function import_context not yet defined that it should be im- and export in /phrase/phrase_list.php
        \-- load_linked_phrases - section for function load_linked_phrases is expected to be load in /phrase/phrase_list.php
        \-- load_linking_triples - section for function load_linking_triples is expected to be load in /phrase/phrase_list.php
        \-- parents - section for function parents not yet defined that it should be im- and export in /phrase/phrase_list.php
        \-- all_children - section for function all_children not yet defined that it should be im- and export in /phrase/phrase_list.php
        \-- foaf_children - section for function foaf_children not yet defined that it should be im- and export in /phrase/phrase_list.php
        \-- foaf_parents - section for function foaf_parents not yet defined that it should be im- and export in /phrase/phrase_list.php
        \-- foaf_related - section for function foaf_related not yet defined that it should be im- and export in /phrase/phrase_list.php
        \-- direct_children - section for function direct_children not yet defined that it should be im- and export in /phrase/phrase_list.php
        \-- is - section for function is not yet defined that it should be im- and export in /phrase/phrase_list.php
        \-- are - section for function are not yet defined that it should be im- and export in /phrase/phrase_list.php
        \-- contains - section for function contains not yet defined that it should be im- and export in /phrase/phrase_list.php
        \-- empty - section for function empty not yet defined that it should be info in /phrase/phrase_list.php
        \-- id - section for function id not yet defined that it should be info in /phrase/phrase_list.php
        \-- missing_ids - section for function missing_ids not yet defined that it should be info in /phrase/phrase_list.php
        \-- prime_only - section for function prime_only not yet defined that it should be info in /phrase/phrase_list.php
        \-- one_positiv - section for function one_positiv not yet defined that it should be info in /phrase/phrase_list.php
        \-- id_lst - section for function id_lst not yet defined that it should be info in /phrase/phrase_list.php
        \-- obj_id_lst - section for function obj_id_lst not yet defined that it should be info in /phrase/phrase_list.php
        \-- phrase_ids - section for function phrase_ids not yet defined that it should be info in /phrase/phrase_list.php
        \-- wrd_ids - section for function wrd_ids not yet defined that it should be info in /phrase/phrase_list.php
        \-- trp_ids - section for function trp_ids not yet defined that it should be info in /phrase/phrase_list.php
        \-- id_url - section for function id_url not yet defined that it should be info in /phrase/phrase_list.php
        \-- id_url_long - section for function id_url_long not yet defined that it should be info in /phrase/phrase_list.php
        \-- loaded - section for function loaded not yet defined that it should be info in /phrase/phrase_list.php
        \-- are_and_contains - section for function are_and_contains not yet defined that it should be info in /phrase/phrase_list.php
        \-- differentiators - section for function differentiators not yet defined that it should be info in /phrase/phrase_list.php
        \-- differentiators_all - section for function differentiators_all not yet defined that it should be info in /phrase/phrase_list.php
        \-- differentiators_filtered - section for function differentiators_filtered not yet defined that it should be info in /phrase/phrase_list.php
        \-- is_valid - section for function is_valid not yet defined that it should be check in /phrase/phrase_list.php
        \-- add_wrd_lst - section for function add_wrd_lst not yet defined that it should be modify in /phrase/phrase_list.php
        \-- add_trp_lst - section for function add_trp_lst not yet defined that it should be modify in /phrase/phrase_list.php
        \-- add_id - section for function add_id not yet defined that it should be modify in /phrase/phrase_list.php
        \-- add_name - section for function add_name not yet defined that it should be modify in /phrase/phrase_list.php
        \-- del - section for function del is expected to be del in /phrase/phrase_list.php
        \-- merge_by_name - section for function merge_by_name not yet defined that it should be modify in /phrase/phrase_list.php
        \-- del_list - section for function del_list is expected to be del in /phrase/phrase_list.php
        \-- filter_by_ids - section for function filter_by_ids not yet defined that it should be modify in /phrase/phrase_list.php
        \-- filter_valid - section for function filter_valid not yet defined that it should be modify in /phrase/phrase_list.php
        \-- get_diff - section for function get_diff is expected to be set and get in /phrase/phrase_list.php
        \-- diff - section for function diff not yet defined that it should be modify in /phrase/phrase_list.php
        \-- not_in - section for function not_in not yet defined that it should be modify in /phrase/phrase_list.php
        \-- diff_by_ids - section for function diff_by_ids not yet defined that it should be modify in /phrase/phrase_list.php
        \-- keep_only_specific - section for function keep_only_specific not yet defined that it should be modify in /phrase/phrase_list.php
        \-- has_time - section for function has_time not yet defined that it should be modify in /phrase/phrase_list.php
        \-- has_measure - section for function has_measure not yet defined that it should be modify in /phrase/phrase_list.php
        \-- has_scaling - section for function has_scaling not yet defined that it should be modify in /phrase/phrase_list.php
        \-- has_percent - section for function has_percent not yet defined that it should be modify in /phrase/phrase_list.php
        \-- time_lst_old - section for function time_lst_old not yet defined that it should be modify in /phrase/phrase_list.php
        \-- time_word_list - section for function time_word_list not yet defined that it should be modify in /phrase/phrase_list.php
        \-- time_list - section for function time_list not yet defined that it should be modify in /phrase/phrase_list.php
        \-- get_by_type - section for function get_by_type is expected to be set and get in /phrase/phrase_list.php
        \-- get_names_by_type - section for function get_names_by_type is expected to be set and get in /phrase/phrase_list.php
        \-- time_useful - section for function time_useful not yet defined that it should be modify in /phrase/phrase_list.php
        \-- assume_time - section for function assume_time not yet defined that it should be modify in /phrase/phrase_list.php
        \-- measure_lst - section for function measure_lst not yet defined that it should be modify in /phrase/phrase_list.php
        \-- scaling_lst - section for function scaling_lst not yet defined that it should be modify in /phrase/phrase_list.php
        \-- ex_time - section for function ex_time not yet defined that it should be modify in /phrase/phrase_list.php
        \-- ex_measure - section for function ex_measure not yet defined that it should be modify in /phrase/phrase_list.php
        \-- ex_scaling - section for function ex_scaling not yet defined that it should be modify in /phrase/phrase_list.php
        \-- name_sort - section for function name_sort not yet defined that it should be modify in /phrase/phrase_list.php
        \-- sort_by_id - section for function sort_by_id not yet defined that it should be modify in /phrase/phrase_list.php
        \-- sort_rev_by_id - section for function sort_rev_by_id not yet defined that it should be modify in /phrase/phrase_list.php
        \-- max_time - section for function max_time not yet defined that it should be modify in /phrase/phrase_list.php
        \-- get_grp_id - section for function get_grp_id is expected to be set and get in /phrase/phrase_list.php
        \-- common - section for function common not yet defined that it should be modify in /phrase/phrase_list.php
        \-- concat_unique - section for function concat_unique not yet defined that it should be modify in /phrase/phrase_list.php
        \-- val_lst - section for function val_lst not yet defined that it should be data request function in /phrase/phrase_list.php
        \-- frm_lst - section for function frm_lst not yet defined that it should be data request function in /phrase/phrase_list.php
        \-- value - section for function value not yet defined that it should be data request function in /phrase/phrase_list.php
        \-- value_scaled - section for function value_scaled not yet defined that it should be data request function in /phrase/phrase_list.php
        \-- name_linked - section for function name_linked not yet defined that it should be display in /phrase/phrase_list.php
        \-- dsp_name - section for function dsp_name not yet defined that it should be display in /phrase/phrase_list.php
        \-- name - section for function name not yet defined that it should be display in /phrase/phrase_list.php
        \-- names - section for function names is expected to be info in /phrase/phrase_list.php
        \-- does_contain - section for function does_contain not yet defined that it should be display in /phrase/phrase_list.php
        \-- get_by_ids - section for function get_by_ids is expected to be set and get in /phrase/phrase_list.php
        \-- load_by_phr - section for function load_by_phr is expected to be load in /phrase/phrase_list.php
        \-- load_by_phr_vrb_and_type - section for function load_by_phr_vrb_and_type is expected to be load in /phrase/phrase_list.php
        \-- load_sql_linked_phrases - section for function load_sql_linked_phrases is expected to be load sql in /phrase/phrase_list.php
        \-- wrd_lst_all - section for function wrd_lst_all not yet defined that it should be if ($pos > 0) { in /phrase/phrase_list.php
        \-- words - section for function words not yet defined that it should be if ($pos > 0) { in /phrase/phrase_list.php
        \-- triples - section for function triples not yet defined that it should be if ($pos > 0) { in /phrase/phrase_list.php
        \-- triples_by_name - section for function triples_by_name not yet defined that it should be if ($pos > 0) { in /phrase/phrase_list.php
    \-- phrase_type
        \-- code_id - section for function code_id not yet defined that it should be construct and map in /phrase/phrase_type.php
    \-- phrase_types
        \-- load_dummy - section for function load_dummy is expected to be load in /phrase/phrase_types.php
        \-- default_id - section for function default_id not yet defined that it should be construct and map in /phrase/phrase_types.php
    \-- term
        \-- row_mapper - section for function row_mapper not yet defined that it should be construct and map in /phrase/term.php
        \-- user_id - section for function user_id not yet defined that it should be set and get in /phrase/term.php
        \-- owner_id - section for function owner_id not yet defined that it should be set and get in /phrase/term.php
        \-- code_id - section for function code_id not yet defined that it should be set and get in /phrase/term.php
        \-- share_id - section for function share_id not yet defined that it should be set and get in /phrase/term.php
        \-- protection_id - section for function protection_id not yet defined that it should be set and get in /phrase/term.php
        \-- id - section for function id not yet defined that it should be set and get in /phrase/term.php
        \-- id_obj - section for function id_obj not yet defined that it should be set and get in /phrase/term.php
        \-- name - section for function name not yet defined that it should be set and get in /phrase/term.php
        \-- user - section for function user not yet defined that it should be set and get in /phrase/term.php
        \-- type - section for function type not yet defined that it should be set and get in /phrase/term.php
        \-- usage - section for function usage not yet defined that it should be set and get in /phrase/term.php
        \-- impact - section for function impact not yet defined that it should be set and get in /phrase/term.php
        \-- phrase - section for function phrase not yet defined that it should be cast in /phrase/term.php
        \-- load_sql_by_id - section for function load_sql_by_id is expected to be load sql in /phrase/term.php
        \-- load_sql_by_name - section for function load_sql_by_name is expected to be load sql in /phrase/term.php
        \-- load_by_id - section for function load_by_id is expected to be load in /phrase/term.php
        \-- load_by_name - section for function load_by_name is expected to be load in /phrase/term.php
        \-- load_by_obj_id - section for function load_by_obj_id is expected to be load in /phrase/term.php
        \-- load_word_by_id - section for function load_word_by_id is expected to be load in /phrase/term.php
        \-- load_triple_by_id - section for function load_triple_by_id is expected to be load in /phrase/term.php
        \-- load_by_obj_name - section for function load_by_obj_name is expected to be load in /phrase/term.php
        \-- load_word_by_name - section for function load_word_by_name is expected to be load in /phrase/term.php
        \-- load_triple_by_name - section for function load_triple_by_name is expected to be load in /phrase/term.php
        \-- load_formula_by_name - section for function load_formula_by_name is expected to be load in /phrase/term.php
        \-- load_verb_by_name - section for function load_verb_by_name is expected to be load in /phrase/term.php
        \-- name_field - section for function name_field is expected to be sql fields in /phrase/term.php
        \-- is_word - section for function is_word not yet defined that it should be classification in /phrase/term.php
        \-- is_triple - section for function is_triple not yet defined that it should be classification in /phrase/term.php
        \-- is_formula - section for function is_formula not yet defined that it should be classification in /phrase/term.php
        \-- is_verb - section for function is_verb not yet defined that it should be classification in /phrase/term.php
        \-- get_word - section for function get_word is expected to be set and get in /phrase/term.php
        \-- get_triple - section for function get_triple is expected to be set and get in /phrase/term.php
        \-- get_formula - section for function get_formula is expected to be set and get in /phrase/term.php
        \-- get_verb - section for function get_verb is expected to be set and get in /phrase/term.php
        \-- get_phrase - section for function get_phrase is expected to be set and get in /phrase/term.php
        \-- id_used_msg - section for function id_used_msg not yet defined that it should be user interface language specific functions in /phrase/term.php
        \-- id_used_msg_text - section for function id_used_msg_text not yet defined that it should be user interface language specific functions in /phrase/term.php
        \-- is_time - section for function is_time not yet defined that it should be info functions in /phrase/term.php
        \-- can_be_ready - section for function can_be_ready not yet defined that it should be info in /phrase/term.php
        \-- db_ready - section for function db_ready not yet defined that it should be info in /phrase/term.php
        \-- is_valid - section for function is_valid not yet defined that it should be info in /phrase/term.php
        \-- import_mapper - section for function import_mapper is expected to be construct and map in /phrase/term.php
        \-- order error - order of section construct and map has difference at row_mapper_sandbox should be after row_mapper of __construct,reset,row_mapper,row_mapper_sandbox does not match __construct,row_mapper_sandbox,api_mapper,reset,row_mapper,import_mapper,code_id,load_dummy,default_id,order of section construct and map has difference at row_mapper_sandbox should be after row_mapper of __construct,reset,row_mapper,row_mapper_sandbox does not match __construct,row_mapper_sandbox,api_mapper,reset,row_mapper,import_mapper,code_id,load_dummy,default_id,order of section construct and map has difference at row_mapper_sandbox should be after row_mapper of __construct,reset,row_mapper,row_mapper_sandbox does not match __construct,row_mapper_sandbox,api_mapper,reset,row_mapper,import_mapper,code_id,load_dummy,default_id,order of section construct and map has difference at row_mapper_sandbox should be after row_mapper of __construct,reset,row_mapper,row_mapper_sandbox does not match __construct,row_mapper_sandbox,api_mapper,reset,row_mapper,import_mapper,code_id,load_dummy,default_id,order of section set and get has difference at id should be after user_id, name should be after user_id, user should be after user_id of set_obj_from_class,set_obj_from_id,set_id,set_id_from_obj,set_name,set_user,user_id,owner_id,code_id,share_id,protection_id,set_usage,set_impact,id,id_obj,name,user,type,usage,impact does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,order of section set and get has difference at id should be after user_id, name should be after user_id, user should be after user_id of set_obj_from_class,set_obj_from_id,set_id,set_id_from_obj,set_name,set_user,user_id,owner_id,code_id,share_id,protection_id,set_usage,set_impact,id,id_obj,name,user,type,usage,impact does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,order of section set and get has difference at id should be after user_id, name should be after user_id, user should be after user_id of set_obj_from_class,set_obj_from_id,set_id,set_id_from_obj,set_name,set_user,user_id,owner_id,code_id,share_id,protection_id,set_usage,set_impact,id,id_obj,name,user,type,usage,impact does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,order of section set and get has difference at id should be after user_id, name should be after user_id, user should be after user_id of set_obj_from_class,set_obj_from_id,set_id,set_id_from_obj,set_name,set_user,user_id,owner_id,code_id,share_id,protection_id,set_usage,set_impact,id,id_obj,name,user,type,usage,impact does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,order of section set and get has difference at id should be after user_id, name should be after user_id, user should be after user_id of set_obj_from_class,set_obj_from_id,set_id,set_id_from_obj,set_name,set_user,user_id,owner_id,code_id,share_id,protection_id,set_usage,set_impact,id,id_obj,name,user,type,usage,impact does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,order of section set and get has difference at id should be after user_id, name should be after user_id, user should be after user_id of set_obj_from_class,set_obj_from_id,set_id,set_id_from_obj,set_name,set_user,user_id,owner_id,code_id,share_id,protection_id,set_usage,set_impact,id,id_obj,name,user,type,usage,impact does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,order of section set and get has difference at id should be after user_id, name should be after user_id, user should be after user_id of set_obj_from_class,set_obj_from_id,set_id,set_id_from_obj,set_name,set_user,user_id,owner_id,code_id,share_id,protection_id,set_usage,set_impact,id,id_obj,name,user,type,usage,impact does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,order of section set and get has difference at id should be after user_id, name should be after user_id, user should be after user_id of set_obj_from_class,set_obj_from_id,set_id,set_id_from_obj,set_name,set_user,user_id,owner_id,code_id,share_id,protection_id,set_usage,set_impact,id,id_obj,name,user,type,usage,impact does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,order of section set and get has difference at id should be after user_id, name should be after user_id, user should be after user_id of set_obj_from_class,set_obj_from_id,set_id,set_id_from_obj,set_name,set_user,user_id,owner_id,code_id,share_id,protection_id,set_usage,set_impact,id,id_obj,name,user,type,usage,impact does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,order of section set and get has difference at id should be after user_id, name should be after user_id, user should be after user_id of set_obj_from_class,set_obj_from_id,set_id,set_id_from_obj,set_name,set_user,user_id,owner_id,code_id,share_id,protection_id,set_usage,set_impact,id,id_obj,name,user,type,usage,impact does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,order of section set and get has difference at id should be after user_id, name should be after user_id, user should be after user_id of set_obj_from_class,set_obj_from_id,set_id,set_id_from_obj,set_name,set_user,user_id,owner_id,code_id,share_id,protection_id,set_usage,set_impact,id,id_obj,name,user,type,usage,impact does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,order of section set and get has difference at id should be after user_id, name should be after user_id, user should be after user_id of set_obj_from_class,set_obj_from_id,set_id,set_id_from_obj,set_name,set_user,user_id,owner_id,code_id,share_id,protection_id,set_usage,set_impact,id,id_obj,name,user,type,usage,impact does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,order of section set and get has difference at id should be after user_id, name should be after user_id, user should be after user_id of set_obj_from_class,set_obj_from_id,set_id,set_id_from_obj,set_name,set_user,user_id,owner_id,code_id,share_id,protection_id,set_usage,set_impact,id,id_obj,name,user,type,usage,impact does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,order of section set and get has difference at id should be after user_id, name should be after user_id, user should be after user_id of set_obj_from_class,set_obj_from_id,set_id,set_id_from_obj,set_name,set_user,user_id,owner_id,code_id,share_id,protection_id,set_usage,set_impact,id,id_obj,name,user,type,usage,impact does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,order of section set and get has difference at id should be after user_id, name should be after user_id, user should be after user_id of set_obj_from_class,set_obj_from_id,set_id,set_id_from_obj,set_name,set_user,user_id,owner_id,code_id,share_id,protection_id,set_usage,set_impact,id,id_obj,name,user,type,usage,impact does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,order of section set and get has difference at id should be after user_id, name should be after user_id, user should be after user_id of set_obj_from_class,set_obj_from_id,set_id,set_id_from_obj,set_name,set_user,user_id,owner_id,code_id,share_id,protection_id,set_usage,set_impact,id,id_obj,name,user,type,usage,impact does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,order of section set and get has difference at id should be after user_id, name should be after user_id, user should be after user_id of set_obj_from_class,set_obj_from_id,set_id,set_id_from_obj,set_name,set_user,user_id,owner_id,code_id,share_id,protection_id,set_usage,set_impact,id,id_obj,name,user,type,usage,impact does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,order of section set and get has difference at id should be after user_id, name should be after user_id, user should be after user_id of set_obj_from_class,set_obj_from_id,set_id,set_id_from_obj,set_name,set_user,user_id,owner_id,code_id,share_id,protection_id,set_usage,set_impact,id,id_obj,name,user,type,usage,impact does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,order of section set and get has difference at id should be after user_id, name should be after user_id, user should be after user_id of set_obj_from_class,set_obj_from_id,set_id,set_id_from_obj,set_name,set_user,user_id,owner_id,code_id,share_id,protection_id,set_usage,set_impact,id,id_obj,name,user,type,usage,impact does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,order of section set and get has difference at id should be after user_id, name should be after user_id, user should be after user_id of set_obj_from_class,set_obj_from_id,set_id,set_id_from_obj,set_name,set_user,user_id,owner_id,code_id,share_id,protection_id,set_usage,set_impact,id,id_obj,name,user,type,usage,impact does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst
    \-- term_list
        \-- name_pos_lst - section for function name_pos_lst not yet defined that it should be set and get in /phrase/term_list.php
        \-- load_sql_by_ids - section for function load_sql_by_ids is expected to be load sql in /phrase/term_list.php
        \-- load_sql_like - section for function load_sql_like is expected to be load sql in /phrase/term_list.php
        \-- id_lst - section for function id_lst not yet defined that it should be get function in /phrase/term_list.php
        \-- ids_txt - section for function ids_txt not yet defined that it should be get function in /phrase/term_list.php
        \-- term_ids - section for function term_ids not yet defined that it should be get function in /phrase/term_list.php
        \-- term_by_obj_id - section for function term_by_obj_id not yet defined that it should be get function in /phrase/term_list.php
        \-- get_by_ids - section for function get_by_ids is expected to be set and get in /phrase/term_list.php
        \-- word_by_id - section for function word_by_id not yet defined that it should be get function in /phrase/term_list.php
        \-- triple_by_id - section for function triple_by_id not yet defined that it should be get function in /phrase/term_list.php
        \-- formula_by_id - section for function formula_by_id not yet defined that it should be get function in /phrase/term_list.php
        \-- verb_by_id - section for function verb_by_id not yet defined that it should be get function in /phrase/term_list.php
        \-- phrase_list - section for function phrase_list not yet defined that it should be cast in /phrase/term_list.php
        \-- intersect - section for function intersect not yet defined that it should be modify in /phrase/term_list.php
        \-- remove - section for function remove not yet defined that it should be modify in /phrase/term_list.php
        \-- merge_by_name - section for function merge_by_name not yet defined that it should be modify in /phrase/term_list.php
        \-- filter_valid - section for function filter_valid not yet defined that it should be modify in /phrase/term_list.php
        \-- dsp_id - section for function dsp_id is expected to be debug in /phrase/term_list.php
        \-- dsp_name - section for function dsp_name not yet defined that it should be display functions in /phrase/term_list.php
        \-- name - section for function name not yet defined that it should be display functions in /phrase/term_list.php
        \-- names - section for function names is expected to be info in /phrase/term_list.php
        \-- order error - order of section load has difference at load_names should be after load_sql_by_ids, load_by_ids should be after load_sql_by_ids of load_sql_by_ids,load_sql_like,load_names,load_by_ids,load_like does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,1,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,load_by_group_id_sql,load_by_phr,load_sql_by_phr,load_sql_by_phr_single,load_sql_init,load_names_sql_by_ids,load_sql_by_ids,del,load_cfg,load_frontend_cfg,load_usr_cfg,load,load_system_views,load_by_key,load_sql_by_id_str,load_type_lists,load_system_users,load_by_id,load_by_name,load_sql_by_user,load_by_user_sql,load_sql_old,load_by_field_row,load_sql_by_field_row,load_by_user,load_obj_last,load_obj_field_last,load_by_obj_fld,load_by_fld_of_wrd,load_by_fld_of_vrb,load_by_fld_of_trp,load_by_fld_of_val,load_by_fld_of_frm,load_by_fld_of_src,load_by_fld_of_ui,load_by_fld_of_cmp,order of section load has difference at load_names should be after load_sql_by_ids, load_by_ids should be after load_sql_by_ids of load_sql_by_ids,load_sql_like,load_names,load_by_ids,load_like does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,1,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,load_by_group_id_sql,load_by_phr,load_sql_by_phr,load_sql_by_phr_single,load_sql_init,load_names_sql_by_ids,load_sql_by_ids,del,load_cfg,load_frontend_cfg,load_usr_cfg,load,load_system_views,load_by_key,load_sql_by_id_str,load_type_lists,load_system_users,load_by_id,load_by_name,load_sql_by_user,load_by_user_sql,load_sql_old,load_by_field_row,load_sql_by_field_row,load_by_user,load_obj_last,load_obj_field_last,load_by_obj_fld,load_by_fld_of_wrd,load_by_fld_of_vrb,load_by_fld_of_trp,load_by_fld_of_val,load_by_fld_of_frm,load_by_fld_of_src,load_by_fld_of_ui,load_by_fld_of_cmp,order of section load has difference at load_names should be after load_sql_by_ids, load_by_ids should be after load_sql_by_ids of load_sql_by_ids,load_sql_like,load_names,load_by_ids,load_like does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,1,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,load_by_group_id_sql,load_by_phr,load_sql_by_phr,load_sql_by_phr_single,load_sql_init,load_names_sql_by_ids,load_sql_by_ids,del,load_cfg,load_frontend_cfg,load_usr_cfg,load,load_system_views,load_by_key,load_sql_by_id_str,load_type_lists,load_system_users,load_by_id,load_by_name,load_sql_by_user,load_by_user_sql,load_sql_old,load_by_field_row,load_sql_by_field_row,load_by_user,load_obj_last,load_obj_field_last,load_by_obj_fld,load_by_fld_of_wrd,load_by_fld_of_vrb,load_by_fld_of_trp,load_by_fld_of_val,load_by_fld_of_frm,load_by_fld_of_src,load_by_fld_of_ui,load_by_fld_of_cmp,order of section load has difference at load_names should be after load_sql_by_ids, load_by_ids should be after load_sql_by_ids of load_sql_by_ids,load_sql_like,load_names,load_by_ids,load_like does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,1,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,load_by_group_id_sql,load_by_phr,load_sql_by_phr,load_sql_by_phr_single,load_sql_init,load_names_sql_by_ids,load_sql_by_ids,del,load_cfg,load_frontend_cfg,load_usr_cfg,load,load_system_views,load_by_key,load_sql_by_id_str,load_type_lists,load_system_users,load_by_id,load_by_name,load_sql_by_user,load_by_user_sql,load_sql_old,load_by_field_row,load_sql_by_field_row,load_by_user,load_obj_last,load_obj_field_last,load_by_obj_fld,load_by_fld_of_wrd,load_by_fld_of_vrb,load_by_fld_of_trp,load_by_fld_of_val,load_by_fld_of_frm,load_by_fld_of_src,load_by_fld_of_ui,load_by_fld_of_cmp,order of section load has difference at load_names should be after load_sql_by_ids, load_by_ids should be after load_sql_by_ids of load_sql_by_ids,load_sql_like,load_names,load_by_ids,load_like does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,1,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,load_by_group_id_sql,load_by_phr,load_sql_by_phr,load_sql_by_phr_single,load_sql_init,load_names_sql_by_ids,load_sql_by_ids,del,load_cfg,load_frontend_cfg,load_usr_cfg,load,load_system_views,load_by_key,load_sql_by_id_str,load_type_lists,load_system_users,load_by_id,load_by_name,load_sql_by_user,load_by_user_sql,load_sql_old,load_by_field_row,load_sql_by_field_row,load_by_user,load_obj_last,load_obj_field_last,load_by_obj_fld,load_by_fld_of_wrd,load_by_fld_of_vrb,load_by_fld_of_trp,load_by_fld_of_val,load_by_fld_of_frm,load_by_fld_of_src,load_by_fld_of_ui,load_by_fld_of_cmp
    \-- trm_ids
        \-- order error - order of section  has difference at count should be after __construct of __construct,count does not match start_api_core,start_api,end_api,start,open_db,end,load_dummy,default_id,add,add_field,add_value,add_par_field,add_par_field_id,get,name,value,type,pos,names,values,types,has,count,names_or_const,sql_names,__construct,has_par,merge,combine,is_function,is_or,is_list,is_text,prefix,is_sql_change,is_val_type,export_by_phrase_list,table_ext_list,row_mapper,int2alpha_num,convert,convert_wiki_json,log_debug,log_info,log_warning,log_err,log_fatal_db,log_fatal,log_msg,order of section  has difference at count should be after __construct of __construct,count does not match start_api_core,start_api,end_api,start,open_db,end,load_dummy,default_id,add,add_field,add_value,add_par_field,add_par_field_id,get,name,value,type,pos,names,values,types,has,count,names_or_const,sql_names,__construct,has_par,merge,combine,is_function,is_or,is_list,is_text,prefix,is_sql_change,is_val_type,export_by_phrase_list,table_ext_list,row_mapper,int2alpha_num,convert,convert_wiki_json,log_debug,log_info,log_warning,log_err,log_fatal_db,log_fatal,log_msg
    \-- ref_list
        \-- user - section for function user not yet defined that it should be set and get in /ref/ref_list.php
        \-- key_list - section for function key_list not yet defined that it should be set and get in /ref/ref_list.php
        \-- load - section for function load not yet defined that it should be load in /ref/ref_list.php
        \-- load_sql_by_names - section for function load_sql_by_names is expected to be load sql in /ref/ref_list.php
        \-- load_sql_by_source - section for function load_sql_by_source is expected to be load sql in /ref/ref_list.php
        \-- ids - section for function ids not yet defined that it should be extract in /ref/ref_list.php
        \-- add_by_name_type_and_key - section for function add_by_name_type_and_key not yet defined that it should be modify in /ref/ref_list.php
        \-- add_direct - section for function add_direct not yet defined that it should be modify in /ref/ref_list.php
        \-- del - section for function del is expected to be del in /ref/ref_list.php
    \-- ref_type_list
        \-- load_dummy - section for function load_dummy is expected to be load in /ref/ref_type_list.php
        \-- default_id - section for function default_id not yet defined that it should be database link in /ref/ref_type_list.php
    \-- source_list
        \-- load_sql_by_ids - section for function load_sql_by_ids is expected to be load sql in /ref/source_list.php
        \-- load_sql_like - section for function load_sql_like is expected to be load sql in /ref/source_list.php
        \-- load_sql_by_names - section for function load_sql_by_names is expected to be load sql in /ref/source_list.php
        \-- order error - order of section load has difference at load_by_ids should be after load_sql_by_ids of load_sql_by_ids,load_sql_like,load_by_ids,load_sql_by_names,load_like does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,1,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,load_by_group_id_sql,load_by_phr,load_sql_by_phr,load_sql_by_phr_single,load_sql_init,load_names_sql_by_ids,load_sql_by_ids,del,load_cfg,load_frontend_cfg,load_usr_cfg,load,load_system_views,load_by_key,load_sql_by_id_str,load_type_lists,load_system_users,load_by_id,load_by_name,load_sql_by_user,load_by_user_sql,load_sql_old,load_by_field_row,load_sql_by_field_row,load_by_user,load_obj_last,load_obj_field_last,load_by_obj_fld,load_by_fld_of_wrd,load_by_fld_of_vrb,load_by_fld_of_trp,load_by_fld_of_val,load_by_fld_of_frm,load_by_fld_of_src,load_by_fld_of_ui,load_by_fld_of_cmp,load_by_keys,load_sql_by_names,load_sql_by_source,load_dummy,order of section load has difference at load_by_ids should be after load_sql_by_ids of load_sql_by_ids,load_sql_like,load_by_ids,load_sql_by_names,load_like does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,1,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,load_by_group_id_sql,load_by_phr,load_sql_by_phr,load_sql_by_phr_single,load_sql_init,load_names_sql_by_ids,load_sql_by_ids,del,load_cfg,load_frontend_cfg,load_usr_cfg,load,load_system_views,load_by_key,load_sql_by_id_str,load_type_lists,load_system_users,load_by_id,load_by_name,load_sql_by_user,load_by_user_sql,load_sql_old,load_by_field_row,load_sql_by_field_row,load_by_user,load_obj_last,load_obj_field_last,load_by_obj_fld,load_by_fld_of_wrd,load_by_fld_of_vrb,load_by_fld_of_trp,load_by_fld_of_val,load_by_fld_of_frm,load_by_fld_of_src,load_by_fld_of_ui,load_by_fld_of_cmp,load_by_keys,load_sql_by_names,load_sql_by_source,load_dummy,order of section load has difference at load_by_ids should be after load_sql_by_ids of load_sql_by_ids,load_sql_like,load_by_ids,load_sql_by_names,load_like does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,1,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,load_by_group_id_sql,load_by_phr,load_sql_by_phr,load_sql_by_phr_single,load_sql_init,load_names_sql_by_ids,load_sql_by_ids,del,load_cfg,load_frontend_cfg,load_usr_cfg,load,load_system_views,load_by_key,load_sql_by_id_str,load_type_lists,load_system_users,load_by_id,load_by_name,load_sql_by_user,load_by_user_sql,load_sql_old,load_by_field_row,load_sql_by_field_row,load_by_user,load_obj_last,load_obj_field_last,load_by_obj_fld,load_by_fld_of_wrd,load_by_fld_of_vrb,load_by_fld_of_trp,load_by_fld_of_val,load_by_fld_of_frm,load_by_fld_of_src,load_by_fld_of_ui,load_by_fld_of_cmp,load_by_keys,load_sql_by_names,load_sql_by_source,load_dummy,order of section load has difference at load_by_ids should be after load_sql_by_ids of load_sql_by_ids,load_sql_like,load_by_ids,load_sql_by_names,load_like does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,1,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,load_by_group_id_sql,load_by_phr,load_sql_by_phr,load_sql_by_phr_single,load_sql_init,load_names_sql_by_ids,load_sql_by_ids,del,load_cfg,load_frontend_cfg,load_usr_cfg,load,load_system_views,load_by_key,load_sql_by_id_str,load_type_lists,load_system_users,load_by_id,load_by_name,load_sql_by_user,load_by_user_sql,load_sql_old,load_by_field_row,load_sql_by_field_row,load_by_user,load_obj_last,load_obj_field_last,load_by_obj_fld,load_by_fld_of_wrd,load_by_fld_of_vrb,load_by_fld_of_trp,load_by_fld_of_val,load_by_fld_of_frm,load_by_fld_of_src,load_by_fld_of_ui,load_by_fld_of_cmp,load_by_keys,load_sql_by_names,load_sql_by_source,load_dummy,order of section load has difference at load_by_ids should be after load_sql_by_ids of load_sql_by_ids,load_sql_like,load_by_ids,load_sql_by_names,load_like does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,1,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,load_by_group_id_sql,load_by_phr,load_sql_by_phr,load_sql_by_phr_single,load_sql_init,load_names_sql_by_ids,load_sql_by_ids,del,load_cfg,load_frontend_cfg,load_usr_cfg,load,load_system_views,load_by_key,load_sql_by_id_str,load_type_lists,load_system_users,load_by_id,load_by_name,load_sql_by_user,load_by_user_sql,load_sql_old,load_by_field_row,load_sql_by_field_row,load_by_user,load_obj_last,load_obj_field_last,load_by_obj_fld,load_by_fld_of_wrd,load_by_fld_of_vrb,load_by_fld_of_trp,load_by_fld_of_val,load_by_fld_of_frm,load_by_fld_of_src,load_by_fld_of_ui,load_by_fld_of_cmp,load_by_keys,load_sql_by_names,load_sql_by_source,load_dummy
    \-- source_type_list
        \-- load_dummy - section for function load_dummy is expected to be load in /ref/source_type_list.php
        \-- default_id - section for function default_id not yet defined that it should be database link in /ref/source_type_list.php
        \-- get_by_id - section for function get_by_id is expected to be set and get in /ref/source_type_list.php
        \-- get_source_type - section for function get_source_type is expected to be set and get in /ref/source_type_list.php
        \-- get_source_type_id - section for function get_source_type_id is expected to be set and get in /ref/source_type_list.php
        \-- get_source_type_by_id - section for function get_source_type_by_id is expected to be set and get in /ref/source_type_list.php
    \-- result_list
        \-- load - section for function load not yet defined that it should be load in /result/result_list.php
        \-- load_sql_by_phr_lst - section for function load_sql_by_phr_lst is expected to be load sql in /result/result_list.php
        \-- load_sql_by_frm - section for function load_sql_by_frm is expected to be load sql in /result/result_list.php
        \-- load_sql_by_src - section for function load_sql_by_src is expected to be load sql in /result/result_list.php
        \-- load_sql_by_frm_single - section for function load_sql_by_frm_single is expected to be load sql in /result/result_list.php
        \-- load_sql_by_ids - section for function load_sql_by_ids is expected to be load sql in /result/result_list.php
        \-- load_sql_by_grp - section for function load_sql_by_grp is expected to be load sql in /result/result_list.php
        \-- load_sql_by_src_grp - section for function load_sql_by_src_grp is expected to be load sql in /result/result_list.php
        \-- load_sql_by_obj_old - section for function load_sql_by_obj_old is expected to be load sql in /result/result_list.php
        \-- load_by_grp - section for function load_by_grp is expected to be load in /result/result_list.php
        \-- load_by_obj - section for function load_by_obj is expected to be load in /result/result_list.php
        \-- name - section for function name not yet defined that it should be display in /result/result_list.php
        \-- names - section for function names is expected to be info in /result/result_list.php
        \-- add_frm_val - section for function add_frm_val not yet defined that it should be TODO check in /result/result_list.php
        \-- frm_upd_lst_usr - section for function frm_upd_lst_usr not yet defined that it should be TODO check in /result/result_list.php
        \-- frm_upd_lst - section for function frm_upd_lst not yet defined that it should be get the result that needs to be recalculated if one formula has been updated in /result/result_list.php
        \-- get_first - section for function get_first is expected to be set and get in /result/result_list.php
        \-- val_upd_lst - section for function val_upd_lst not yet defined that it should be get the result that needs to be recalculated if one formula has been updated in /result/result_list.php
        \-- load_by_val - section for function load_by_val is expected to be load in /result/result_list.php
        \-- add - section for function add not yet defined that it should be get the result that needs to be recalculated if one formula has been updated in /result/result_list.php
        \-- merge - section for function merge not yet defined that it should be get the result that needs to be recalculated if one formula has been updated in /result/result_list.php
        \-- order error - order of section load has difference at load_by_ids should be after load_by_frm, load_sql_by_ids should be after load of load_by_phr_lst,load_by_frm,load_by_src,load_by_ids,load,load_sql_by_phr_lst,load_sql_by_frm,load_sql_by_src,load_sql_by_frm_single,load_sql_by_ids does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,1,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,load_by_group_id_sql,load_by_phr,load_sql_by_phr,load_sql_by_phr_single,load_sql_init,load_names_sql_by_ids,load_sql_by_ids,del,load_cfg,load_frontend_cfg,load_usr_cfg,load,load_system_views,load_by_key,load_sql_by_id_str,load_type_lists,load_system_users,load_by_id,load_by_name,load_sql_by_user,load_by_user_sql,load_sql_old,load_by_field_row,load_sql_by_field_row,load_by_user,load_obj_last,load_obj_field_last,load_by_obj_fld,load_by_fld_of_wrd,load_by_fld_of_vrb,load_by_fld_of_trp,load_by_fld_of_val,load_by_fld_of_frm,load_by_fld_of_src,load_by_fld_of_ui,load_by_fld_of_cmp,load_by_keys,load_sql_by_names,load_sql_by_source,load_dummy,order of section load has difference at load_by_ids should be after load_by_frm, load_sql_by_ids should be after load of load_by_phr_lst,load_by_frm,load_by_src,load_by_ids,load,load_sql_by_phr_lst,load_sql_by_frm,load_sql_by_src,load_sql_by_frm_single,load_sql_by_ids does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,1,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,load_by_group_id_sql,load_by_phr,load_sql_by_phr,load_sql_by_phr_single,load_sql_init,load_names_sql_by_ids,load_sql_by_ids,del,load_cfg,load_frontend_cfg,load_usr_cfg,load,load_system_views,load_by_key,load_sql_by_id_str,load_type_lists,load_system_users,load_by_id,load_by_name,load_sql_by_user,load_by_user_sql,load_sql_old,load_by_field_row,load_sql_by_field_row,load_by_user,load_obj_last,load_obj_field_last,load_by_obj_fld,load_by_fld_of_wrd,load_by_fld_of_vrb,load_by_fld_of_trp,load_by_fld_of_val,load_by_fld_of_frm,load_by_fld_of_src,load_by_fld_of_ui,load_by_fld_of_cmp,load_by_keys,load_sql_by_names,load_sql_by_source,load_dummy,order of section load has difference at load_by_ids should be after load_by_frm, load_sql_by_ids should be after load of load_by_phr_lst,load_by_frm,load_by_src,load_by_ids,load,load_sql_by_phr_lst,load_sql_by_frm,load_sql_by_src,load_sql_by_frm_single,load_sql_by_ids does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,1,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,load_by_group_id_sql,load_by_phr,load_sql_by_phr,load_sql_by_phr_single,load_sql_init,load_names_sql_by_ids,load_sql_by_ids,del,load_cfg,load_frontend_cfg,load_usr_cfg,load,load_system_views,load_by_key,load_sql_by_id_str,load_type_lists,load_system_users,load_by_id,load_by_name,load_sql_by_user,load_by_user_sql,load_sql_old,load_by_field_row,load_sql_by_field_row,load_by_user,load_obj_last,load_obj_field_last,load_by_obj_fld,load_by_fld_of_wrd,load_by_fld_of_vrb,load_by_fld_of_trp,load_by_fld_of_val,load_by_fld_of_frm,load_by_fld_of_src,load_by_fld_of_ui,load_by_fld_of_cmp,load_by_keys,load_sql_by_names,load_sql_by_source,load_dummy,order of section load has difference at load_by_ids should be after load_by_frm, load_sql_by_ids should be after load of load_by_phr_lst,load_by_frm,load_by_src,load_by_ids,load,load_sql_by_phr_lst,load_sql_by_frm,load_sql_by_src,load_sql_by_frm_single,load_sql_by_ids does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,1,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,load_by_group_id_sql,load_by_phr,load_sql_by_phr,load_sql_by_phr_single,load_sql_init,load_names_sql_by_ids,load_sql_by_ids,del,load_cfg,load_frontend_cfg,load_usr_cfg,load,load_system_views,load_by_key,load_sql_by_id_str,load_type_lists,load_system_users,load_by_id,load_by_name,load_sql_by_user,load_by_user_sql,load_sql_old,load_by_field_row,load_sql_by_field_row,load_by_user,load_obj_last,load_obj_field_last,load_by_obj_fld,load_by_fld_of_wrd,load_by_fld_of_vrb,load_by_fld_of_trp,load_by_fld_of_val,load_by_fld_of_frm,load_by_fld_of_src,load_by_fld_of_ui,load_by_fld_of_cmp,load_by_keys,load_sql_by_names,load_sql_by_source,load_dummy,order of section load has difference at load_by_ids should be after load_by_frm, load_sql_by_ids should be after load of load_by_phr_lst,load_by_frm,load_by_src,load_by_ids,load,load_sql_by_phr_lst,load_sql_by_frm,load_sql_by_src,load_sql_by_frm_single,load_sql_by_ids does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,1,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,load_by_group_id_sql,load_by_phr,load_sql_by_phr,load_sql_by_phr_single,load_sql_init,load_names_sql_by_ids,load_sql_by_ids,del,load_cfg,load_frontend_cfg,load_usr_cfg,load,load_system_views,load_by_key,load_sql_by_id_str,load_type_lists,load_system_users,load_by_id,load_by_name,load_sql_by_user,load_by_user_sql,load_sql_old,load_by_field_row,load_sql_by_field_row,load_by_user,load_obj_last,load_obj_field_last,load_by_obj_fld,load_by_fld_of_wrd,load_by_fld_of_vrb,load_by_fld_of_trp,load_by_fld_of_val,load_by_fld_of_frm,load_by_fld_of_src,load_by_fld_of_ui,load_by_fld_of_cmp,load_by_keys,load_sql_by_names,load_sql_by_source,load_dummy,order of section load has difference at load_by_ids should be after load_by_frm, load_sql_by_ids should be after load of load_by_phr_lst,load_by_frm,load_by_src,load_by_ids,load,load_sql_by_phr_lst,load_sql_by_frm,load_sql_by_src,load_sql_by_frm_single,load_sql_by_ids does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,1,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,load_by_group_id_sql,load_by_phr,load_sql_by_phr,load_sql_by_phr_single,load_sql_init,load_names_sql_by_ids,load_sql_by_ids,del,load_cfg,load_frontend_cfg,load_usr_cfg,load,load_system_views,load_by_key,load_sql_by_id_str,load_type_lists,load_system_users,load_by_id,load_by_name,load_sql_by_user,load_by_user_sql,load_sql_old,load_by_field_row,load_sql_by_field_row,load_by_user,load_obj_last,load_obj_field_last,load_by_obj_fld,load_by_fld_of_wrd,load_by_fld_of_vrb,load_by_fld_of_trp,load_by_fld_of_val,load_by_fld_of_frm,load_by_fld_of_src,load_by_fld_of_ui,load_by_fld_of_cmp,load_by_keys,load_sql_by_names,load_sql_by_source,load_dummy,order of section load has difference at load_by_ids should be after load_by_frm, load_sql_by_ids should be after load of load_by_phr_lst,load_by_frm,load_by_src,load_by_ids,load,load_sql_by_phr_lst,load_sql_by_frm,load_sql_by_src,load_sql_by_frm_single,load_sql_by_ids does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,1,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,load_by_group_id_sql,load_by_phr,load_sql_by_phr,load_sql_by_phr_single,load_sql_init,load_names_sql_by_ids,load_sql_by_ids,del,load_cfg,load_frontend_cfg,load_usr_cfg,load,load_system_views,load_by_key,load_sql_by_id_str,load_type_lists,load_system_users,load_by_id,load_by_name,load_sql_by_user,load_by_user_sql,load_sql_old,load_by_field_row,load_sql_by_field_row,load_by_user,load_obj_last,load_obj_field_last,load_by_obj_fld,load_by_fld_of_wrd,load_by_fld_of_vrb,load_by_fld_of_trp,load_by_fld_of_val,load_by_fld_of_frm,load_by_fld_of_src,load_by_fld_of_ui,load_by_fld_of_cmp,load_by_keys,load_sql_by_names,load_sql_by_source,load_dummy,order of section load has difference at load_by_ids should be after load_by_frm, load_sql_by_ids should be after load of load_by_phr_lst,load_by_frm,load_by_src,load_by_ids,load,load_sql_by_phr_lst,load_sql_by_frm,load_sql_by_src,load_sql_by_frm_single,load_sql_by_ids does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,1,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,load_by_group_id_sql,load_by_phr,load_sql_by_phr,load_sql_by_phr_single,load_sql_init,load_names_sql_by_ids,load_sql_by_ids,del,load_cfg,load_frontend_cfg,load_usr_cfg,load,load_system_views,load_by_key,load_sql_by_id_str,load_type_lists,load_system_users,load_by_id,load_by_name,load_sql_by_user,load_by_user_sql,load_sql_old,load_by_field_row,load_sql_by_field_row,load_by_user,load_obj_last,load_obj_field_last,load_by_obj_fld,load_by_fld_of_wrd,load_by_fld_of_vrb,load_by_fld_of_trp,load_by_fld_of_val,load_by_fld_of_frm,load_by_fld_of_src,load_by_fld_of_ui,load_by_fld_of_cmp,load_by_keys,load_sql_by_names,load_sql_by_source,load_dummy,order of section load has difference at load_by_ids should be after load_by_frm, load_sql_by_ids should be after load of load_by_phr_lst,load_by_frm,load_by_src,load_by_ids,load,load_sql_by_phr_lst,load_sql_by_frm,load_sql_by_src,load_sql_by_frm_single,load_sql_by_ids does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,1,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,load_by_group_id_sql,load_by_phr,load_sql_by_phr,load_sql_by_phr_single,load_sql_init,load_names_sql_by_ids,load_sql_by_ids,del,load_cfg,load_frontend_cfg,load_usr_cfg,load,load_system_views,load_by_key,load_sql_by_id_str,load_type_lists,load_system_users,load_by_id,load_by_name,load_sql_by_user,load_by_user_sql,load_sql_old,load_by_field_row,load_sql_by_field_row,load_by_user,load_obj_last,load_obj_field_last,load_by_obj_fld,load_by_fld_of_wrd,load_by_fld_of_vrb,load_by_fld_of_trp,load_by_fld_of_val,load_by_fld_of_frm,load_by_fld_of_src,load_by_fld_of_ui,load_by_fld_of_cmp,load_by_keys,load_sql_by_names,load_sql_by_source,load_dummy,order of section load has difference at load_by_ids should be after load_by_frm, load_sql_by_ids should be after load of load_by_phr_lst,load_by_frm,load_by_src,load_by_ids,load,load_sql_by_phr_lst,load_sql_by_frm,load_sql_by_src,load_sql_by_frm_single,load_sql_by_ids does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,1,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,load_by_group_id_sql,load_by_phr,load_sql_by_phr,load_sql_by_phr_single,load_sql_init,load_names_sql_by_ids,load_sql_by_ids,del,load_cfg,load_frontend_cfg,load_usr_cfg,load,load_system_views,load_by_key,load_sql_by_id_str,load_type_lists,load_system_users,load_by_id,load_by_name,load_sql_by_user,load_by_user_sql,load_sql_old,load_by_field_row,load_sql_by_field_row,load_by_user,load_obj_last,load_obj_field_last,load_by_obj_fld,load_by_fld_of_wrd,load_by_fld_of_vrb,load_by_fld_of_trp,load_by_fld_of_val,load_by_fld_of_frm,load_by_fld_of_src,load_by_fld_of_ui,load_by_fld_of_cmp,load_by_keys,load_sql_by_names,load_sql_by_source,load_dummy
    \-- sandbox
        \-- clone_reset - section for function clone_reset not yet defined that it should be construct and map in /sandbox/sandbox.php
        \-- row_mapper_usr - section for function row_mapper_usr not yet defined that it should be construct and map in /sandbox/sandbox.php
        \-- row_mapper_std - section for function row_mapper_std not yet defined that it should be construct and map in /sandbox/sandbox.php
        \-- exclude - section for function exclude not yet defined that it should be set and get in /sandbox/sandbox.php
        \-- include - section for function include not yet defined that it should be set and get in /sandbox/sandbox.php
        \-- is_excluded - section for function is_excluded not yet defined that it should be set and get in /sandbox/sandbox.php
        \-- is_exclusion_set - section for function is_exclusion_set not yet defined that it should be set and get in /sandbox/sandbox.php
        \-- owner - section for function owner not yet defined that it should be set and get in /sandbox/sandbox.php
        \-- owner_id - section for function owner_id not yet defined that it should be set and get in /sandbox/sandbox.php
        \-- share_id - section for function share_id not yet defined that it should be set and get in /sandbox/sandbox.php
        \-- protection_id - section for function protection_id not yet defined that it should be set and get in /sandbox/sandbox.php
        \-- share_type_code_id - section for function share_type_code_id not yet defined that it should be preloaded in /sandbox/sandbox.php
        \-- share_type_name - section for function share_type_name not yet defined that it should be preloaded in /sandbox/sandbox.php
        \-- protection_type_code_id - section for function protection_type_code_id not yet defined that it should be preloaded in /sandbox/sandbox.php
        \-- protection_type_name - section for function protection_type_name not yet defined that it should be preloaded in /sandbox/sandbox.php
        \-- type_name_field - section for function type_name_field not yet defined that it should be preloaded in /sandbox/sandbox.php
        \-- name_field - section for function name_field is expected to be sql fields in /sandbox/sandbox.php
        \-- type_field - section for function type_field is expected to be sql fields in /sandbox/sandbox.php
        \-- from_field - section for function from_field is expected to be sql fields in /sandbox/sandbox.php
        \-- from_name - section for function from_name not yet defined that it should be placeholder in /sandbox/sandbox.php
        \-- to_field - section for function to_field is expected to be sql fields in /sandbox/sandbox.php
        \-- to_name - section for function to_name not yet defined that it should be placeholder in /sandbox/sandbox.php
        \-- to_value - section for function to_value not yet defined that it should be placeholder in /sandbox/sandbox.php
        \-- fob - section for function fob not yet defined that it should be placeholder in /sandbox/sandbox.php
        \-- tob - section for function tob not yet defined that it should be placeholder in /sandbox/sandbox.php
        \-- fill_api_obj - section for function fill_api_obj not yet defined that it should be cast in /sandbox/sandbox.php
        \-- load_standard - section for function load_standard is expected to be load in /sandbox/sandbox.php
        \-- load_standard_sql - section for function load_standard_sql is expected to be load in /sandbox/sandbox.php
        \-- load_sql_usr_num - section for function load_sql_usr_num is expected to be load sql in /sandbox/sandbox.php
        \-- load_sql_fields - section for function load_sql_fields is expected to be load sql in /sandbox/sandbox.php
        \-- load_owner - section for function load_owner is expected to be load in /sandbox/sandbox.php
        \-- reload_objects - section for function reload_objects is expected to be retrieval in /sandbox/sandbox.php
        \-- load_sql_median_user - section for function load_sql_median_user is expected to be load sql in /sandbox/sandbox.php
        \-- median_user - section for function median_user not yet defined that it should be info in /sandbox/sandbox.php
        \-- take_ownership - section for function take_ownership not yet defined that it should be owner and access in /sandbox/sandbox.php
        \-- set_owner - section for function set_owner is expected to be set and get in /sandbox/sandbox.php
        \-- not_changed - section for function not_changed not yet defined that it should be owner and access in /sandbox/sandbox.php
        \-- not_used - section for function not_used not yet defined that it should be owner and access in /sandbox/sandbox.php
        \-- changer - section for function changer not yet defined that it should be owner and access in /sandbox/sandbox.php
        \-- load_sql_changer - section for function load_sql_changer is expected to be load sql in /sandbox/sandbox.php
        \-- changed_by - section for function changed_by not yet defined that it should be owner and access in /sandbox/sandbox.php
        \-- load_sql_of_users_that_changed - section for function load_sql_of_users_that_changed is expected to be load sql in /sandbox/sandbox.php
        \-- used_by_someone_else - section for function used_by_someone_else not yet defined that it should be owner and access in /sandbox/sandbox.php
        \-- can_change - section for function can_change not yet defined that it should be owner and access in /sandbox/sandbox.php
        \-- has_usr_cfg - section for function has_usr_cfg not yet defined that it should be sandbox in /sandbox/sandbox.php
        \-- del_usr_cfg_exe - section for function del_usr_cfg_exe is expected to be del in /sandbox/sandbox.php
        \-- del_usr_cfg - section for function del_usr_cfg is expected to be del in /sandbox/sandbox.php
        \-- load_sql_user_changes - section for function load_sql_user_changes is expected to be load sql in /sandbox/sandbox.php
        \-- db_ready - section for function db_ready not yet defined that it should be sandbox in /sandbox/sandbox.php
        \-- save_all_fields - section for function save_all_fields is expected to be save in /sandbox/sandbox.php
        \-- save_fields_func - section for function save_fields_func is expected to be save in /sandbox/sandbox.php
        \-- db_fields_all - section for function db_fields_all is expected to be sql write fields in /sandbox/sandbox.php
        \-- save_field_user - section for function save_field_user is expected to be save in /sandbox/sandbox.php
        \-- save_field - section for function save_field is expected to be save in /sandbox/sandbox.php
        \-- no_diff - section for function no_diff not yet defined that it should be save fields in /sandbox/sandbox.php
        \-- save_field_excluded_log - section for function save_field_excluded_log is expected to be save in /sandbox/sandbox.php
        \-- save_field_excluded - section for function save_field_excluded is expected to be save in /sandbox/sandbox.php
        \-- is_id_updated - section for function is_id_updated not yet defined that it should be save id in /sandbox/sandbox.php
        \-- get_obj_with_same_id_fields - section for function get_obj_with_same_id_fields is expected to be set and get in /sandbox/sandbox.php
        \-- msg_id_already_used - section for function msg_id_already_used not yet defined that it should be save id in /sandbox/sandbox.php
        \-- save_id_if_updated - section for function save_id_if_updated is expected to be save in /sandbox/sandbox.php
        \-- save_id_fields - section for function save_id_fields is expected to be save in /sandbox/sandbox.php
        \-- save_id_fields_link - section for function save_id_fields_link is expected to be save in /sandbox/sandbox.php
        \-- is_same_std - section for function is_same_std not yet defined that it should be similar in /sandbox/sandbox.php
        \-- is_same - section for function is_same not yet defined that it should be similar in /sandbox/sandbox.php
        \-- is_similar - section for function is_similar not yet defined that it should be similar in /sandbox/sandbox.php
        \-- get_similar - section for function get_similar is expected to be save in /sandbox/sandbox.php
        \-- add - section for function add not yet defined that it should be add in /sandbox/sandbox.php
        \-- del - section for function del is expected to be del in /sandbox/sandbox.php
        \-- id_used_msg - section for function id_used_msg not yet defined that it should be delete in /sandbox/sandbox.php
        \-- del_links - section for function del_links is expected to be del in /sandbox/sandbox.php
        \-- save_field_type - section for function save_field_type is expected to be save in /sandbox/sandbox.php
        \-- type_name - section for function type_name is expected to be preloaded in /sandbox/sandbox.php
        \-- insert - section for function insert not yet defined that it should be sql write in /sandbox/sandbox.php
        \-- update - section for function update not yet defined that it should be sql write in /sandbox/sandbox.php
        \-- sql_insert - section for function sql_insert not yet defined that it should be sql write in /sandbox/sandbox.php
        \-- sql_update - section for function sql_update not yet defined that it should be sql write in /sandbox/sandbox.php
        \-- sql_delete - section for function sql_delete not yet defined that it should be sql write in /sandbox/sandbox.php
        \-- sql_default_script_usage - section for function sql_default_script_usage not yet defined that it should be sql write in /sandbox/sandbox.php
        \-- sql_write_prepared - section for function sql_write_prepared not yet defined that it should be sql write in /sandbox/sandbox.php
        \-- db_fields_all_sandbox - section for function db_fields_all_sandbox not yet defined that it should be sql write fields in /sandbox/sandbox.php
        \-- db_changed_sandbox_list - section for function db_changed_sandbox_list not yet defined that it should be sql write fields in /sandbox/sandbox.php
        \-- sql_table - section for function sql_table not yet defined that it should be sql create in /sandbox/sandbox.php
        \-- sql_index - section for function sql_index not yet defined that it should be sql create in /sandbox/sandbox.php
        \-- sql_foreign_key - section for function sql_foreign_key not yet defined that it should be sql create in /sandbox/sandbox.php
        \-- sql_insert_switch - section for function sql_insert_switch not yet defined that it should be sql write in /sandbox/sandbox.php
        \-- sql_insert_with_log - section for function sql_insert_with_log not yet defined that it should be sql write in /sandbox/sandbox.php
        \-- sql_insert_key_field - section for function sql_insert_key_field not yet defined that it should be sql write in /sandbox/sandbox.php
        \-- sql_update_switch - section for function sql_update_switch not yet defined that it should be sql write in /sandbox/sandbox.php
        \-- sql_key_fields_text - section for function sql_key_fields_text not yet defined that it should be sql write in /sandbox/sandbox.php
        \-- sql_key_fields_text_old - section for function sql_key_fields_text_old not yet defined that it should be sql write in /sandbox/sandbox.php
        \-- sql_key_fields_id - section for function sql_key_fields_id not yet defined that it should be sql write in /sandbox/sandbox.php
        \-- sql_key_fields_id_old - section for function sql_key_fields_id_old not yet defined that it should be sql write in /sandbox/sandbox.php
        \-- db_fields_changed - section for function db_fields_changed is expected to be sql write fields in /sandbox/sandbox.php
        \-- sql_extension - section for function sql_extension not yet defined that it should be sql helper in /sandbox/sandbox.php
        \-- fld_id - section for function fld_id not yet defined that it should be internal check in /sandbox/sandbox.php
        \-- fld_name - section for function fld_name not yet defined that it should be internal check in /sandbox/sandbox.php
        \-- is_named_obj - section for function is_named_obj not yet defined that it should be settings in /sandbox/sandbox.php
        \-- is_link_obj - section for function is_link_obj not yet defined that it should be settings in /sandbox/sandbox.php
        \-- is_link_type_obj - section for function is_link_type_obj not yet defined that it should be settings in /sandbox/sandbox.php
        \-- is_value_obj - section for function is_value_obj not yet defined that it should be settings in /sandbox/sandbox.php
        \-- set_code_id - section for function set_code_id is expected to be set and get in /sandbox/sandbox.php
        \-- code_id - section for function code_id not yet defined that it should be overwrite in /sandbox/sandbox.php
        \-- set_ui_msg_code_id - section for function set_ui_msg_code_id is expected to be set and get in /sandbox/sandbox.php
        \-- get_ui_msg_code_id - section for function get_ui_msg_code_id is expected to be set and get in /sandbox/sandbox.php
        \-- set_ui_msg_code_id_vars - section for function set_ui_msg_code_id_vars is expected to be set and get in /sandbox/sandbox.php
        \-- get_ui_msg_code_id_vars - section for function get_ui_msg_code_id_vars is expected to be set and get in /sandbox/sandbox.php
        \-- set_ui_msg_code_id_exception - section for function set_ui_msg_code_id_exception is expected to be set and get in /sandbox/sandbox.php
        \-- get_ui_msg_code_id_exception - section for function get_ui_msg_code_id_exception is expected to be set and get in /sandbox/sandbox.php
        \-- set_ui_msg_value_exception - section for function set_ui_msg_value_exception is expected to be set and get in /sandbox/sandbox.php
        \-- get_ui_msg_value_exception - section for function get_ui_msg_value_exception is expected to be set and get in /sandbox/sandbox.php
        \-- chk_owner - section for function chk_owner not yet defined that it should be check functions in /sandbox/sandbox.php
        \-- order error - order of section construct and map has difference at row_mapper_sandbox should be after reset, api_mapper should be after reset of __construct,reset,clone_reset,row_mapper_sandbox,row_mapper_usr,row_mapper_std,api_mapper,import_mapper does not match __construct,row_mapper_sandbox,api_mapper,reset,row_mapper,import_mapper,code_id,load_dummy,default_id,order of section construct and map has difference at row_mapper_sandbox should be after reset, api_mapper should be after reset of __construct,reset,clone_reset,row_mapper_sandbox,row_mapper_usr,row_mapper_std,api_mapper,import_mapper does not match __construct,row_mapper_sandbox,api_mapper,reset,row_mapper,import_mapper,code_id,load_dummy,default_id,order of section construct and map has difference at row_mapper_sandbox should be after reset, api_mapper should be after reset of __construct,reset,clone_reset,row_mapper_sandbox,row_mapper_usr,row_mapper_std,api_mapper,import_mapper does not match __construct,row_mapper_sandbox,api_mapper,reset,row_mapper,import_mapper,code_id,load_dummy,default_id,order of section construct and map has difference at row_mapper_sandbox should be after reset, api_mapper should be after reset of __construct,reset,clone_reset,row_mapper_sandbox,row_mapper_usr,row_mapper_std,api_mapper,import_mapper does not match __construct,row_mapper_sandbox,api_mapper,reset,row_mapper,import_mapper,code_id,load_dummy,default_id,order of section construct and map has difference at row_mapper_sandbox should be after reset, api_mapper should be after reset of __construct,reset,clone_reset,row_mapper_sandbox,row_mapper_usr,row_mapper_std,api_mapper,import_mapper does not match __construct,row_mapper_sandbox,api_mapper,reset,row_mapper,import_mapper,code_id,load_dummy,default_id,order of section construct and map has difference at row_mapper_sandbox should be after reset, api_mapper should be after reset of __construct,reset,clone_reset,row_mapper_sandbox,row_mapper_usr,row_mapper_std,api_mapper,import_mapper does not match __construct,row_mapper_sandbox,api_mapper,reset,row_mapper,import_mapper,code_id,load_dummy,default_id,order of section construct and map has difference at row_mapper_sandbox should be after reset, api_mapper should be after reset of __construct,reset,clone_reset,row_mapper_sandbox,row_mapper_usr,row_mapper_std,api_mapper,import_mapper does not match __construct,row_mapper_sandbox,api_mapper,reset,row_mapper,import_mapper,code_id,load_dummy,default_id,order of section construct and map has difference at row_mapper_sandbox should be after reset, api_mapper should be after reset of __construct,reset,clone_reset,row_mapper_sandbox,row_mapper_usr,row_mapper_std,api_mapper,import_mapper does not match __construct,row_mapper_sandbox,api_mapper,reset,row_mapper,import_mapper,code_id,load_dummy,default_id,order of section set and get has difference at set_share should be after set_from_api, set_protection should be after set_from_api, exclude should be after set_from_api, include should be after set_from_api, is_excluded should be after set_from_api, is_exclusion_set should be after set_from_api of set_from_api,set_share,set_protection,set_excluded,exclude,include,is_excluded,is_exclusion_set,set_owner_id,owner,owner_id,set_share_id,share_id,set_protection_id,protection_id does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,key_list,order of section set and get has difference at set_share should be after set_from_api, set_protection should be after set_from_api, exclude should be after set_from_api, include should be after set_from_api, is_excluded should be after set_from_api, is_exclusion_set should be after set_from_api of set_from_api,set_share,set_protection,set_excluded,exclude,include,is_excluded,is_exclusion_set,set_owner_id,owner,owner_id,set_share_id,share_id,set_protection_id,protection_id does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,key_list,order of section set and get has difference at set_share should be after set_from_api, set_protection should be after set_from_api, exclude should be after set_from_api, include should be after set_from_api, is_excluded should be after set_from_api, is_exclusion_set should be after set_from_api of set_from_api,set_share,set_protection,set_excluded,exclude,include,is_excluded,is_exclusion_set,set_owner_id,owner,owner_id,set_share_id,share_id,set_protection_id,protection_id does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,key_list,order of section set and get has difference at set_share should be after set_from_api, set_protection should be after set_from_api, exclude should be after set_from_api, include should be after set_from_api, is_excluded should be after set_from_api, is_exclusion_set should be after set_from_api of set_from_api,set_share,set_protection,set_excluded,exclude,include,is_excluded,is_exclusion_set,set_owner_id,owner,owner_id,set_share_id,share_id,set_protection_id,protection_id does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,key_list,order of section set and get has difference at set_share should be after set_from_api, set_protection should be after set_from_api, exclude should be after set_from_api, include should be after set_from_api, is_excluded should be after set_from_api, is_exclusion_set should be after set_from_api of set_from_api,set_share,set_protection,set_excluded,exclude,include,is_excluded,is_exclusion_set,set_owner_id,owner,owner_id,set_share_id,share_id,set_protection_id,protection_id does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,key_list,order of section set and get has difference at set_share should be after set_from_api, set_protection should be after set_from_api, exclude should be after set_from_api, include should be after set_from_api, is_excluded should be after set_from_api, is_exclusion_set should be after set_from_api of set_from_api,set_share,set_protection,set_excluded,exclude,include,is_excluded,is_exclusion_set,set_owner_id,owner,owner_id,set_share_id,share_id,set_protection_id,protection_id does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,key_list,order of section set and get has difference at set_share should be after set_from_api, set_protection should be after set_from_api, exclude should be after set_from_api, include should be after set_from_api, is_excluded should be after set_from_api, is_exclusion_set should be after set_from_api of set_from_api,set_share,set_protection,set_excluded,exclude,include,is_excluded,is_exclusion_set,set_owner_id,owner,owner_id,set_share_id,share_id,set_protection_id,protection_id does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,key_list,order of section set and get has difference at set_share should be after set_from_api, set_protection should be after set_from_api, exclude should be after set_from_api, include should be after set_from_api, is_excluded should be after set_from_api, is_exclusion_set should be after set_from_api of set_from_api,set_share,set_protection,set_excluded,exclude,include,is_excluded,is_exclusion_set,set_owner_id,owner,owner_id,set_share_id,share_id,set_protection_id,protection_id does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,key_list,order of section set and get has difference at set_share should be after set_from_api, set_protection should be after set_from_api, exclude should be after set_from_api, include should be after set_from_api, is_excluded should be after set_from_api, is_exclusion_set should be after set_from_api of set_from_api,set_share,set_protection,set_excluded,exclude,include,is_excluded,is_exclusion_set,set_owner_id,owner,owner_id,set_share_id,share_id,set_protection_id,protection_id does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,key_list,order of section set and get has difference at set_share should be after set_from_api, set_protection should be after set_from_api, exclude should be after set_from_api, include should be after set_from_api, is_excluded should be after set_from_api, is_exclusion_set should be after set_from_api of set_from_api,set_share,set_protection,set_excluded,exclude,include,is_excluded,is_exclusion_set,set_owner_id,owner,owner_id,set_share_id,share_id,set_protection_id,protection_id does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,key_list,order of section set and get has difference at set_share should be after set_from_api, set_protection should be after set_from_api, exclude should be after set_from_api, include should be after set_from_api, is_excluded should be after set_from_api, is_exclusion_set should be after set_from_api of set_from_api,set_share,set_protection,set_excluded,exclude,include,is_excluded,is_exclusion_set,set_owner_id,owner,owner_id,set_share_id,share_id,set_protection_id,protection_id does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,key_list,order of section set and get has difference at set_share should be after set_from_api, set_protection should be after set_from_api, exclude should be after set_from_api, include should be after set_from_api, is_excluded should be after set_from_api, is_exclusion_set should be after set_from_api of set_from_api,set_share,set_protection,set_excluded,exclude,include,is_excluded,is_exclusion_set,set_owner_id,owner,owner_id,set_share_id,share_id,set_protection_id,protection_id does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,key_list,order of section set and get has difference at set_share should be after set_from_api, set_protection should be after set_from_api, exclude should be after set_from_api, include should be after set_from_api, is_excluded should be after set_from_api, is_exclusion_set should be after set_from_api of set_from_api,set_share,set_protection,set_excluded,exclude,include,is_excluded,is_exclusion_set,set_owner_id,owner,owner_id,set_share_id,share_id,set_protection_id,protection_id does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,key_list,order of section set and get has difference at set_share should be after set_from_api, set_protection should be after set_from_api, exclude should be after set_from_api, include should be after set_from_api, is_excluded should be after set_from_api, is_exclusion_set should be after set_from_api of set_from_api,set_share,set_protection,set_excluded,exclude,include,is_excluded,is_exclusion_set,set_owner_id,owner,owner_id,set_share_id,share_id,set_protection_id,protection_id does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,key_list,order of section set and get has difference at set_share should be after set_from_api, set_protection should be after set_from_api, exclude should be after set_from_api, include should be after set_from_api, is_excluded should be after set_from_api, is_exclusion_set should be after set_from_api of set_from_api,set_share,set_protection,set_excluded,exclude,include,is_excluded,is_exclusion_set,set_owner_id,owner,owner_id,set_share_id,share_id,set_protection_id,protection_id does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,key_list
    \-- sandbox_code_id
        \-- code_id - section for function code_id not yet defined that it should be set and get in /sandbox/sandbox_code_id.php
        \-- order error - order of section construct and map has difference at row_mapper_sandbox should be after reset, api_mapper should be after reset of reset,row_mapper_sandbox,api_mapper,import_mapper_user does not match __construct,row_mapper_sandbox,api_mapper,reset,row_mapper,import_mapper,code_id,load_dummy,default_id,order of section construct and map has difference at row_mapper_sandbox should be after reset, api_mapper should be after reset of reset,row_mapper_sandbox,api_mapper,import_mapper_user does not match __construct,row_mapper_sandbox,api_mapper,reset,row_mapper,import_mapper,code_id,load_dummy,default_id,order of section construct and map has difference at row_mapper_sandbox should be after reset, api_mapper should be after reset of reset,row_mapper_sandbox,api_mapper,import_mapper_user does not match __construct,row_mapper_sandbox,api_mapper,reset,row_mapper,import_mapper,code_id,load_dummy,default_id,order of section construct and map has difference at row_mapper_sandbox should be after reset, api_mapper should be after reset of reset,row_mapper_sandbox,api_mapper,import_mapper_user does not match __construct,row_mapper_sandbox,api_mapper,reset,row_mapper,import_mapper,code_id,load_dummy,default_id
    \-- sandbox_link
        \-- fob - section for function fob not yet defined that it should be set and get in /sandbox/sandbox_link.php
        \-- from_id - section for function from_id not yet defined that it should be set and get in /sandbox/sandbox_link.php
        \-- from_name - section for function from_name not yet defined that it should be set and get in /sandbox/sandbox_link.php
        \-- from_empty - section for function from_empty not yet defined that it should be set and get in /sandbox/sandbox_link.php
        \-- predicate_id - section for function predicate_id not yet defined that it should be set and get in /sandbox/sandbox_link.php
        \-- predicate_name - section for function predicate_name is expected to be preloaded in /sandbox/sandbox_link.php
        \-- verb_empty - section for function verb_empty not yet defined that it should be set and get in /sandbox/sandbox_link.php
        \-- tob - section for function tob not yet defined that it should be set and get in /sandbox/sandbox_link.php
        \-- to_id - section for function to_id not yet defined that it should be set and get in /sandbox/sandbox_link.php
        \-- to_name - section for function to_name not yet defined that it should be set and get in /sandbox/sandbox_link.php
        \-- to_empty - section for function to_empty not yet defined that it should be set and get in /sandbox/sandbox_link.php
        \-- cloned - section for function cloned not yet defined that it should be set and get in /sandbox/sandbox_link.php
        \-- is_link_type_obj - section for function is_link_type_obj not yet defined that it should be settings in /sandbox/sandbox_link.php
        \-- load_sql_by_link - section for function load_sql_by_link is expected to be load sql in /sandbox/sandbox_link.php
        \-- is_unique - section for function is_unique not yet defined that it should be info in /sandbox/sandbox_link.php
        \-- can_be_ready - section for function can_be_ready not yet defined that it should be info in /sandbox/sandbox_link.php
        \-- needs_from - section for function needs_from not yet defined that it should be info in /sandbox/sandbox_link.php
        \-- needs_to - section for function needs_to not yet defined that it should be info in /sandbox/sandbox_link.php
        \-- db_ready - section for function db_ready not yet defined that it should be info in /sandbox/sandbox_link.php
        \-- is_valid - section for function is_valid not yet defined that it should be info in /sandbox/sandbox_link.php
        \-- fill_api_obj - section for function fill_api_obj not yet defined that it should be set the vars of the minimal api object based on this link object in /sandbox/sandbox_link.php
        \-- add - section for function add not yet defined that it should be save in /sandbox/sandbox_link.php
        \-- is_id_updated_link - section for function is_id_updated_link not yet defined that it should be save in /sandbox/sandbox_link.php
        \-- msg_id_already_used - section for function msg_id_already_used not yet defined that it should be save in /sandbox/sandbox_link.php
        \-- is_same_std - section for function is_same_std not yet defined that it should be save in /sandbox/sandbox_link.php
        \-- sql_insert_key_field - section for function sql_insert_key_field not yet defined that it should be sql write in /sandbox/sandbox_link.php
        \-- sql_key_fields_text - section for function sql_key_fields_text not yet defined that it should be sql write in /sandbox/sandbox_link.php
        \-- sql_key_fields_text_old - section for function sql_key_fields_text_old not yet defined that it should be sql write in /sandbox/sandbox_link.php
        \-- sql_key_fields_id - section for function sql_key_fields_id not yet defined that it should be sql write in /sandbox/sandbox_link.php
        \-- sql_key_fields_id_old - section for function sql_key_fields_id_old not yet defined that it should be sql write in /sandbox/sandbox_link.php
        \-- del_links - section for function del_links is expected to be del in /sandbox/sandbox_link.php
        \-- db_all_fields_link - section for function db_all_fields_link not yet defined that it should be sql write fields in /sandbox/sandbox_link.php
        \-- is_link_obj - section for function is_link_obj not yet defined that it should be internal in /sandbox/sandbox_link.php
        \-- is_named_obj - section for function is_named_obj not yet defined that it should be internal in /sandbox/sandbox_link.php
        \-- sql_insert - section for function sql_insert not yet defined that it should be sql write in /sandbox/sandbox_link.php
        \-- sql_update - section for function sql_update not yet defined that it should be sql write in /sandbox/sandbox_link.php
        \-- link_id - section for function link_id not yet defined that it should be debug in /sandbox/sandbox_link.php
        \-- order error - order of section construct and map has difference at api_mapper should be after reset of reset,api_mapper does not match __construct,row_mapper_sandbox,api_mapper,reset,row_mapper,import_mapper,code_id,load_dummy,default_id,order of section construct and map has difference at api_mapper should be after reset of reset,api_mapper does not match __construct,row_mapper_sandbox,api_mapper,reset,row_mapper,import_mapper,code_id,load_dummy,default_id
    \-- sandbox_link_list
        \-- add - section for function add not yet defined that it should be modify in /sandbox/sandbox_link_list.php
        \-- add_link - section for function add_link not yet defined that it should be modify in /sandbox/sandbox_link_list.php
        \-- add_link_by_key - section for function add_link_by_key not yet defined that it should be modify in /sandbox/sandbox_link_list.php
    \-- sandbox_link_named
        \-- name - section for function name not yet defined that it should be set and get in /sandbox/sandbox_link_named.php
        \-- name_or_null - section for function name_or_null not yet defined that it should be set and get in /sandbox/sandbox_link_named.php
        \-- name_field - section for function name_field is expected to be sql fields in /sandbox/sandbox_link_named.php
        \-- cloned_named - section for function cloned_named not yet defined that it should be set and get in /sandbox/sandbox_link_named.php
        \-- description - section for function description not yet defined that it should be set and get in /sandbox/sandbox_link_named.php
        \-- type_id - section for function type_id not yet defined that it should be set and get in /sandbox/sandbox_link_named.php
        \-- fill_api_obj - section for function fill_api_obj not yet defined that it should be cast in /sandbox/sandbox_link_named.php
        \-- no_id_but_name - section for function no_id_but_name not yet defined that it should be info in /sandbox/sandbox_link_named.php
        \-- log_last_msg - section for function log_last_msg is expected to be log in /sandbox/sandbox_link_named.php
        \-- log_last_field_msg - section for function log_last_field_msg is expected to be log in /sandbox/sandbox_link_named.php
        \-- save_field_description - section for function save_field_description is expected to be save in /sandbox/sandbox_link_named.php
        \-- sql_insert_key_field - section for function sql_insert_key_field not yet defined that it should be sql write in /sandbox/sandbox_link_named.php
        \-- is_named_obj - section for function is_named_obj not yet defined that it should be settings in /sandbox/sandbox_link_named.php
        \-- order error - order of section construct and map has difference at row_mapper_sandbox should be after reset, api_mapper should be after reset of reset,row_mapper_sandbox,api_mapper,import_mapper does not match __construct,row_mapper_sandbox,api_mapper,reset,row_mapper,import_mapper,code_id,load_dummy,default_id,order of section construct and map has difference at row_mapper_sandbox should be after reset, api_mapper should be after reset of reset,row_mapper_sandbox,api_mapper,import_mapper does not match __construct,row_mapper_sandbox,api_mapper,reset,row_mapper,import_mapper,code_id,load_dummy,default_id,order of section construct and map has difference at row_mapper_sandbox should be after reset, api_mapper should be after reset of reset,row_mapper_sandbox,api_mapper,import_mapper does not match __construct,row_mapper_sandbox,api_mapper,reset,row_mapper,import_mapper,code_id,load_dummy,default_id,order of section construct and map has difference at row_mapper_sandbox should be after reset, api_mapper should be after reset of reset,row_mapper_sandbox,api_mapper,import_mapper does not match __construct,row_mapper_sandbox,api_mapper,reset,row_mapper,import_mapper,code_id,load_dummy,default_id,order of section set and get has difference at set_description should be after name_or_null, description should be after name_or_null, set_type_id should be after name_or_null, type_id should be after name_or_null of set_name,name,name_or_null,name_field,cloned_named,set_description,description,set_type_id,type_id,set_type_by_code_id,set_type_by_name does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,1,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,key_list,name_or_null,order of section set and get has difference at set_description should be after name_or_null, description should be after name_or_null, set_type_id should be after name_or_null, type_id should be after name_or_null of set_name,name,name_or_null,name_field,cloned_named,set_description,description,set_type_id,type_id,set_type_by_code_id,set_type_by_name does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,1,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,key_list,name_or_null,order of section set and get has difference at set_description should be after name_or_null, description should be after name_or_null, set_type_id should be after name_or_null, type_id should be after name_or_null of set_name,name,name_or_null,name_field,cloned_named,set_description,description,set_type_id,type_id,set_type_by_code_id,set_type_by_name does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,1,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,key_list,name_or_null,order of section set and get has difference at set_description should be after name_or_null, description should be after name_or_null, set_type_id should be after name_or_null, type_id should be after name_or_null of set_name,name,name_or_null,name_field,cloned_named,set_description,description,set_type_id,type_id,set_type_by_code_id,set_type_by_name does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,1,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,key_list,name_or_null,order of section set and get has difference at set_description should be after name_or_null, description should be after name_or_null, set_type_id should be after name_or_null, type_id should be after name_or_null of set_name,name,name_or_null,name_field,cloned_named,set_description,description,set_type_id,type_id,set_type_by_code_id,set_type_by_name does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,1,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,key_list,name_or_null,order of section set and get has difference at set_description should be after name_or_null, description should be after name_or_null, set_type_id should be after name_or_null, type_id should be after name_or_null of set_name,name,name_or_null,name_field,cloned_named,set_description,description,set_type_id,type_id,set_type_by_code_id,set_type_by_name does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,1,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,key_list,name_or_null,order of section set and get has difference at set_description should be after name_or_null, description should be after name_or_null, set_type_id should be after name_or_null, type_id should be after name_or_null of set_name,name,name_or_null,name_field,cloned_named,set_description,description,set_type_id,type_id,set_type_by_code_id,set_type_by_name does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,1,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,key_list,name_or_null,order of section set and get has difference at set_description should be after name_or_null, description should be after name_or_null, set_type_id should be after name_or_null, type_id should be after name_or_null of set_name,name,name_or_null,name_field,cloned_named,set_description,description,set_type_id,type_id,set_type_by_code_id,set_type_by_name does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,1,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,key_list,name_or_null
    \-- sandbox_list
        \-- user - section for function user not yet defined that it should be set and get in /sandbox/sandbox_list.php
        \-- load_sql_names - section for function load_sql_names is expected to be load sql in /sandbox/sandbox_list.php
        \-- add_obj - section for function add_obj not yet defined that it should be modify in /sandbox/sandbox_list.php
        \-- same_user - section for function same_user not yet defined that it should be check in /sandbox/sandbox_list.php
        \-- name - section for function name not yet defined that it should be debug in /sandbox/sandbox_list.php
        \-- names - section for function names is expected to be info in /sandbox/sandbox_list.php
        \-- table_extension - section for function table_extension not yet defined that it should be sql_type_list in /sandbox/sandbox_list.php
    \-- sandbox_list_named
        \-- name_pos_lst - section for function name_pos_lst not yet defined that it should be set and get in /sandbox/sandbox_list_named.php
        \-- name_pos_lst_all - section for function name_pos_lst_all not yet defined that it should be set and get in /sandbox/sandbox_list_named.php
        \-- load_sql_by_names - section for function load_sql_by_names is expected to be load sql in /sandbox/sandbox_list_named.php
        \-- load_sql_by_code_id_list - section for function load_sql_by_code_id_list is expected to be load sql in /sandbox/sandbox_list_named.php
        \-- missing_ids - section for function missing_ids not yet defined that it should be info in /sandbox/sandbox_list_named.php
        \-- add - section for function add not yet defined that it should be modify in /sandbox/sandbox_list_named.php
        \-- add_by_name_direct - section for function add_by_name_direct not yet defined that it should be modify in /sandbox/sandbox_list_named.php
        \-- fill_by_id - section for function fill_by_id not yet defined that it should be modify in /sandbox/sandbox_list_named.php
        \-- fill_by_name - section for function fill_by_name not yet defined that it should be modify in /sandbox/sandbox_list_named.php
        \-- add_id_by_name - section for function add_id_by_name not yet defined that it should be modify in /sandbox/sandbox_list_named.php
        \-- merge - section for function merge not yet defined that it should be modify in /sandbox/sandbox_list_named.php
        \-- get_by_name - section for function get_by_name is expected to be set and get in /sandbox/sandbox_list_named.php
        \-- filter_by_name - section for function filter_by_name not yet defined that it should be search in /sandbox/sandbox_list_named.php
        \-- select_by_name - section for function select_by_name not yet defined that it should be search in /sandbox/sandbox_list_named.php
        \-- add_obj - section for function add_obj not yet defined that it should be modify in /sandbox/sandbox_list_named.php
        \-- sort_by_name - section for function sort_by_name not yet defined that it should be modify in /sandbox/sandbox_list_named.php
        \-- name_id_list - section for function name_id_list not yet defined that it should be modify in /sandbox/sandbox_list_named.php
        \-- code_id_list - section for function code_id_list not yet defined that it should be modify in /sandbox/sandbox_list_named.php
        \-- update_list - section for function update_list not yet defined that it should be select in /sandbox/sandbox_list_named.php
        \-- delete_list - section for function delete_list is expected to be del in /sandbox/sandbox_list_named.php
        \-- insert - section for function insert not yet defined that it should be save in /sandbox/sandbox_list_named.php
        \-- update - section for function update not yet defined that it should be save in /sandbox/sandbox_list_named.php
        \-- delete - section for function delete is expected to be del in /sandbox/sandbox_list_named.php
        \-- sql_insert - section for function sql_insert not yet defined that it should be save in /sandbox/sandbox_list_named.php
        \-- sql_update - section for function sql_update not yet defined that it should be save in /sandbox/sandbox_list_named.php
        \-- sql_delete - section for function sql_delete not yet defined that it should be save in /sandbox/sandbox_list_named.php
        \-- sql_insert_call_with_par - section for function sql_insert_call_with_par not yet defined that it should be save in /sandbox/sandbox_list_named.php
        \-- sql_update_call_with_par - section for function sql_update_call_with_par not yet defined that it should be save in /sandbox/sandbox_list_named.php
        \-- sql_delete_call_with_par - section for function sql_delete_call_with_par not yet defined that it should be save in /sandbox/sandbox_list_named.php
        \-- save - section for function save is expected to be save in /sandbox/sandbox_list_named.php
        \-- names - section for function names is expected to be info in /sandbox/sandbox_list_named.php
        \-- order error - order of section modify has difference at add_by_name should be after add of add,add_by_name,add_by_name_direct,fill_by_id,fill_by_name,add_id_by_name,merge,add_obj,sort_by_name,name_id_list,code_id_list does not match add_by_name,add,remove,add_word,add_verb,add_triple,add_triple_without_ready_check,add_phrase,add_source,add_reference,add_formula,add_formula_without_ready_check,add_term,add_view,add_component,add_term_view,add_user,add_ip_range,add_value,add_message,get_component_by_name,get_value_by_names,expected_word_import_time,expected_triple_import_time,expected_value_import_time,expected_total_import_time,count,save,diff_msg,fill,query_extension,dsp_last,add_ref,use_type_id,add_wrd_lst,add_trp_lst,add_id,add_name,2,del,merge_by_name,del_list,filter_by_ids,filter_valid,get_diff,diff,not_in,diff_by_ids,keep_only_specific,has_time,has_measure,has_scaling,has_percent,time_lst_old,time_word_list,time_list,get_by_type,get_names_by_type,time_useful,assume_time,measure_lst,scaling_lst,ex_time,ex_measure,ex_scaling,name_sort,sort_by_id,sort_rev_by_id,max_time,get_grp_id,common,concat_unique,add_link,add_link_by_key,add_obj,order of section modify has difference at add_by_name should be after add of add,add_by_name,add_by_name_direct,fill_by_id,fill_by_name,add_id_by_name,merge,add_obj,sort_by_name,name_id_list,code_id_list does not match add_by_name,add,remove,add_word,add_verb,add_triple,add_triple_without_ready_check,add_phrase,add_source,add_reference,add_formula,add_formula_without_ready_check,add_term,add_view,add_component,add_term_view,add_user,add_ip_range,add_value,add_message,get_component_by_name,get_value_by_names,expected_word_import_time,expected_triple_import_time,expected_value_import_time,expected_total_import_time,count,save,diff_msg,fill,query_extension,dsp_last,add_ref,use_type_id,add_wrd_lst,add_trp_lst,add_id,add_name,2,del,merge_by_name,del_list,filter_by_ids,filter_valid,get_diff,diff,not_in,diff_by_ids,keep_only_specific,has_time,has_measure,has_scaling,has_percent,time_lst_old,time_word_list,time_list,get_by_type,get_names_by_type,time_useful,assume_time,measure_lst,scaling_lst,ex_time,ex_measure,ex_scaling,name_sort,sort_by_id,sort_rev_by_id,max_time,get_grp_id,common,concat_unique,add_link,add_link_by_key,add_obj,order of section modify has difference at add_by_name should be after add of add,add_by_name,add_by_name_direct,fill_by_id,fill_by_name,add_id_by_name,merge,add_obj,sort_by_name,name_id_list,code_id_list does not match add_by_name,add,remove,add_word,add_verb,add_triple,add_triple_without_ready_check,add_phrase,add_source,add_reference,add_formula,add_formula_without_ready_check,add_term,add_view,add_component,add_term_view,add_user,add_ip_range,add_value,add_message,get_component_by_name,get_value_by_names,expected_word_import_time,expected_triple_import_time,expected_value_import_time,expected_total_import_time,count,save,diff_msg,fill,query_extension,dsp_last,add_ref,use_type_id,add_wrd_lst,add_trp_lst,add_id,add_name,2,del,merge_by_name,del_list,filter_by_ids,filter_valid,get_diff,diff,not_in,diff_by_ids,keep_only_specific,has_time,has_measure,has_scaling,has_percent,time_lst_old,time_word_list,time_list,get_by_type,get_names_by_type,time_useful,assume_time,measure_lst,scaling_lst,ex_time,ex_measure,ex_scaling,name_sort,sort_by_id,sort_rev_by_id,max_time,get_grp_id,common,concat_unique,add_link,add_link_by_key,add_obj,order of section modify has difference at add_by_name should be after add of add,add_by_name,add_by_name_direct,fill_by_id,fill_by_name,add_id_by_name,merge,add_obj,sort_by_name,name_id_list,code_id_list does not match add_by_name,add,remove,add_word,add_verb,add_triple,add_triple_without_ready_check,add_phrase,add_source,add_reference,add_formula,add_formula_without_ready_check,add_term,add_view,add_component,add_term_view,add_user,add_ip_range,add_value,add_message,get_component_by_name,get_value_by_names,expected_word_import_time,expected_triple_import_time,expected_value_import_time,expected_total_import_time,count,save,diff_msg,fill,query_extension,dsp_last,add_ref,use_type_id,add_wrd_lst,add_trp_lst,add_id,add_name,2,del,merge_by_name,del_list,filter_by_ids,filter_valid,get_diff,diff,not_in,diff_by_ids,keep_only_specific,has_time,has_measure,has_scaling,has_percent,time_lst_old,time_word_list,time_list,get_by_type,get_names_by_type,time_useful,assume_time,measure_lst,scaling_lst,ex_time,ex_measure,ex_scaling,name_sort,sort_by_id,sort_rev_by_id,max_time,get_grp_id,common,concat_unique,add_link,add_link_by_key,add_obj,order of section modify has difference at add_by_name should be after add of add,add_by_name,add_by_name_direct,fill_by_id,fill_by_name,add_id_by_name,merge,add_obj,sort_by_name,name_id_list,code_id_list does not match add_by_name,add,remove,add_word,add_verb,add_triple,add_triple_without_ready_check,add_phrase,add_source,add_reference,add_formula,add_formula_without_ready_check,add_term,add_view,add_component,add_term_view,add_user,add_ip_range,add_value,add_message,get_component_by_name,get_value_by_names,expected_word_import_time,expected_triple_import_time,expected_value_import_time,expected_total_import_time,count,save,diff_msg,fill,query_extension,dsp_last,add_ref,use_type_id,add_wrd_lst,add_trp_lst,add_id,add_name,2,del,merge_by_name,del_list,filter_by_ids,filter_valid,get_diff,diff,not_in,diff_by_ids,keep_only_specific,has_time,has_measure,has_scaling,has_percent,time_lst_old,time_word_list,time_list,get_by_type,get_names_by_type,time_useful,assume_time,measure_lst,scaling_lst,ex_time,ex_measure,ex_scaling,name_sort,sort_by_id,sort_rev_by_id,max_time,get_grp_id,common,concat_unique,add_link,add_link_by_key,add_obj,order of section modify has difference at add_by_name should be after add of add,add_by_name,add_by_name_direct,fill_by_id,fill_by_name,add_id_by_name,merge,add_obj,sort_by_name,name_id_list,code_id_list does not match add_by_name,add,remove,add_word,add_verb,add_triple,add_triple_without_ready_check,add_phrase,add_source,add_reference,add_formula,add_formula_without_ready_check,add_term,add_view,add_component,add_term_view,add_user,add_ip_range,add_value,add_message,get_component_by_name,get_value_by_names,expected_word_import_time,expected_triple_import_time,expected_value_import_time,expected_total_import_time,count,save,diff_msg,fill,query_extension,dsp_last,add_ref,use_type_id,add_wrd_lst,add_trp_lst,add_id,add_name,2,del,merge_by_name,del_list,filter_by_ids,filter_valid,get_diff,diff,not_in,diff_by_ids,keep_only_specific,has_time,has_measure,has_scaling,has_percent,time_lst_old,time_word_list,time_list,get_by_type,get_names_by_type,time_useful,assume_time,measure_lst,scaling_lst,ex_time,ex_measure,ex_scaling,name_sort,sort_by_id,sort_rev_by_id,max_time,get_grp_id,common,concat_unique,add_link,add_link_by_key,add_obj,order of section modify has difference at add_by_name should be after add of add,add_by_name,add_by_name_direct,fill_by_id,fill_by_name,add_id_by_name,merge,add_obj,sort_by_name,name_id_list,code_id_list does not match add_by_name,add,remove,add_word,add_verb,add_triple,add_triple_without_ready_check,add_phrase,add_source,add_reference,add_formula,add_formula_without_ready_check,add_term,add_view,add_component,add_term_view,add_user,add_ip_range,add_value,add_message,get_component_by_name,get_value_by_names,expected_word_import_time,expected_triple_import_time,expected_value_import_time,expected_total_import_time,count,save,diff_msg,fill,query_extension,dsp_last,add_ref,use_type_id,add_wrd_lst,add_trp_lst,add_id,add_name,2,del,merge_by_name,del_list,filter_by_ids,filter_valid,get_diff,diff,not_in,diff_by_ids,keep_only_specific,has_time,has_measure,has_scaling,has_percent,time_lst_old,time_word_list,time_list,get_by_type,get_names_by_type,time_useful,assume_time,measure_lst,scaling_lst,ex_time,ex_measure,ex_scaling,name_sort,sort_by_id,sort_rev_by_id,max_time,get_grp_id,common,concat_unique,add_link,add_link_by_key,add_obj,order of section modify has difference at add_by_name should be after add of add,add_by_name,add_by_name_direct,fill_by_id,fill_by_name,add_id_by_name,merge,add_obj,sort_by_name,name_id_list,code_id_list does not match add_by_name,add,remove,add_word,add_verb,add_triple,add_triple_without_ready_check,add_phrase,add_source,add_reference,add_formula,add_formula_without_ready_check,add_term,add_view,add_component,add_term_view,add_user,add_ip_range,add_value,add_message,get_component_by_name,get_value_by_names,expected_word_import_time,expected_triple_import_time,expected_value_import_time,expected_total_import_time,count,save,diff_msg,fill,query_extension,dsp_last,add_ref,use_type_id,add_wrd_lst,add_trp_lst,add_id,add_name,2,del,merge_by_name,del_list,filter_by_ids,filter_valid,get_diff,diff,not_in,diff_by_ids,keep_only_specific,has_time,has_measure,has_scaling,has_percent,time_lst_old,time_word_list,time_list,get_by_type,get_names_by_type,time_useful,assume_time,measure_lst,scaling_lst,ex_time,ex_measure,ex_scaling,name_sort,sort_by_id,sort_rev_by_id,max_time,get_grp_id,common,concat_unique,add_link,add_link_by_key,add_obj,order of section modify has difference at add_by_name should be after add of add,add_by_name,add_by_name_direct,fill_by_id,fill_by_name,add_id_by_name,merge,add_obj,sort_by_name,name_id_list,code_id_list does not match add_by_name,add,remove,add_word,add_verb,add_triple,add_triple_without_ready_check,add_phrase,add_source,add_reference,add_formula,add_formula_without_ready_check,add_term,add_view,add_component,add_term_view,add_user,add_ip_range,add_value,add_message,get_component_by_name,get_value_by_names,expected_word_import_time,expected_triple_import_time,expected_value_import_time,expected_total_import_time,count,save,diff_msg,fill,query_extension,dsp_last,add_ref,use_type_id,add_wrd_lst,add_trp_lst,add_id,add_name,2,del,merge_by_name,del_list,filter_by_ids,filter_valid,get_diff,diff,not_in,diff_by_ids,keep_only_specific,has_time,has_measure,has_scaling,has_percent,time_lst_old,time_word_list,time_list,get_by_type,get_names_by_type,time_useful,assume_time,measure_lst,scaling_lst,ex_time,ex_measure,ex_scaling,name_sort,sort_by_id,sort_rev_by_id,max_time,get_grp_id,common,concat_unique,add_link,add_link_by_key,add_obj,order of section modify has difference at add_by_name should be after add of add,add_by_name,add_by_name_direct,fill_by_id,fill_by_name,add_id_by_name,merge,add_obj,sort_by_name,name_id_list,code_id_list does not match add_by_name,add,remove,add_word,add_verb,add_triple,add_triple_without_ready_check,add_phrase,add_source,add_reference,add_formula,add_formula_without_ready_check,add_term,add_view,add_component,add_term_view,add_user,add_ip_range,add_value,add_message,get_component_by_name,get_value_by_names,expected_word_import_time,expected_triple_import_time,expected_value_import_time,expected_total_import_time,count,save,diff_msg,fill,query_extension,dsp_last,add_ref,use_type_id,add_wrd_lst,add_trp_lst,add_id,add_name,2,del,merge_by_name,del_list,filter_by_ids,filter_valid,get_diff,diff,not_in,diff_by_ids,keep_only_specific,has_time,has_measure,has_scaling,has_percent,time_lst_old,time_word_list,time_list,get_by_type,get_names_by_type,time_useful,assume_time,measure_lst,scaling_lst,ex_time,ex_measure,ex_scaling,name_sort,sort_by_id,sort_rev_by_id,max_time,get_grp_id,common,concat_unique,add_link,add_link_by_key,add_obj,order of section modify has difference at add_by_name should be after add of add,add_by_name,add_by_name_direct,fill_by_id,fill_by_name,add_id_by_name,merge,add_obj,sort_by_name,name_id_list,code_id_list does not match add_by_name,add,remove,add_word,add_verb,add_triple,add_triple_without_ready_check,add_phrase,add_source,add_reference,add_formula,add_formula_without_ready_check,add_term,add_view,add_component,add_term_view,add_user,add_ip_range,add_value,add_message,get_component_by_name,get_value_by_names,expected_word_import_time,expected_triple_import_time,expected_value_import_time,expected_total_import_time,count,save,diff_msg,fill,query_extension,dsp_last,add_ref,use_type_id,add_wrd_lst,add_trp_lst,add_id,add_name,2,del,merge_by_name,del_list,filter_by_ids,filter_valid,get_diff,diff,not_in,diff_by_ids,keep_only_specific,has_time,has_measure,has_scaling,has_percent,time_lst_old,time_word_list,time_list,get_by_type,get_names_by_type,time_useful,assume_time,measure_lst,scaling_lst,ex_time,ex_measure,ex_scaling,name_sort,sort_by_id,sort_rev_by_id,max_time,get_grp_id,common,concat_unique,add_link,add_link_by_key,add_obj
    \-- sandbox_multi
        \-- row_mapper_sandbox_multi - section for function row_mapper_sandbox_multi not yet defined that it should be construct and map in /sandbox/sandbox_multi.php
        \-- row_mapper_usr - section for function row_mapper_usr not yet defined that it should be construct and map in /sandbox/sandbox_multi.php
        \-- row_mapper_std - section for function row_mapper_std not yet defined that it should be construct and map in /sandbox/sandbox_multi.php
        \-- exclude - section for function exclude not yet defined that it should be set and get in /sandbox/sandbox_multi.php
        \-- include - section for function include not yet defined that it should be set and get in /sandbox/sandbox_multi.php
        \-- is_excluded - section for function is_excluded not yet defined that it should be set and get in /sandbox/sandbox_multi.php
        \-- grp - section for function grp not yet defined that it should be set and get in /sandbox/sandbox_multi.php
        \-- owner_id - section for function owner_id not yet defined that it should be set and get in /sandbox/sandbox_multi.php
        \-- owner - section for function owner not yet defined that it should be set and get in /sandbox/sandbox_multi.php
        \-- share_id - section for function share_id not yet defined that it should be set and get in /sandbox/sandbox_multi.php
        \-- protection_id - section for function protection_id not yet defined that it should be set and get in /sandbox/sandbox_multi.php
        \-- source_id - section for function source_id not yet defined that it should be set and get in /sandbox/sandbox_multi.php
        \-- is_exclusion_set - section for function is_exclusion_set not yet defined that it should be set and get in /sandbox/sandbox_multi.php
        \-- load_sql_fields - section for function load_sql_fields is expected to be load sql in /sandbox/sandbox_multi.php
        \-- load_sql_usr_num - section for function load_sql_usr_num is expected to be load sql in /sandbox/sandbox_multi.php
        \-- load_sql_obj_vars - section for function load_sql_obj_vars is expected to be load sql in /sandbox/sandbox_multi.php
        \-- chk_owner - section for function chk_owner not yet defined that it should be check functions in /sandbox/sandbox_multi.php
        \-- share_type_code_id - section for function share_type_code_id not yet defined that it should be load types in /sandbox/sandbox_multi.php
        \-- share_type_name - section for function share_type_name not yet defined that it should be load types in /sandbox/sandbox_multi.php
        \-- protection_type_code_id - section for function protection_type_code_id not yet defined that it should be load types in /sandbox/sandbox_multi.php
        \-- protection_type_name - section for function protection_type_name not yet defined that it should be load types in /sandbox/sandbox_multi.php
        \-- load_sql_median_user - section for function load_sql_median_user is expected to be load sql in /sandbox/sandbox_multi.php
        \-- median_user - section for function median_user not yet defined that it should be info in /sandbox/sandbox_multi.php
        \-- is_standard - section for function is_standard not yet defined that it should be info in /sandbox/sandbox_multi.php
        \-- is_saved - section for function is_saved not yet defined that it should be save helper - ownership and access in /sandbox/sandbox_multi.php
        \-- take_ownership - section for function take_ownership not yet defined that it should be save helper - ownership and access in /sandbox/sandbox_multi.php
        \-- set_owner - section for function set_owner is expected to be set and get in /sandbox/sandbox_multi.php
        \-- not_changed - section for function not_changed not yet defined that it should be save helper - ownership and access in /sandbox/sandbox_multi.php
        \-- not_used - section for function not_used not yet defined that it should be save helper - ownership and access in /sandbox/sandbox_multi.php
        \-- changer_sql - section for function changer_sql not yet defined that it should be save helper - ownership and access in /sandbox/sandbox_multi.php
        \-- changer - section for function changer not yet defined that it should be save helper - ownership and access in /sandbox/sandbox_multi.php
        \-- load_sql_of_users_that_changed - section for function load_sql_of_users_that_changed is expected to be load sql in /sandbox/sandbox_multi.php
        \-- changed_by - section for function changed_by not yet defined that it should be save helper - ownership and access in /sandbox/sandbox_multi.php
        \-- used_by_someone_else - section for function used_by_someone_else not yet defined that it should be save helper - ownership and access in /sandbox/sandbox_multi.php
        \-- can_change - section for function can_change not yet defined that it should be save helper - ownership and access in /sandbox/sandbox_multi.php
        \-- is_numeric - section for function is_numeric not yet defined that it should be save helper - user sandbox in /sandbox/sandbox_multi.php
        \-- is_time_value - section for function is_time_value not yet defined that it should be save helper - user sandbox in /sandbox/sandbox_multi.php
        \-- is_text_value - section for function is_text_value not yet defined that it should be save helper - user sandbox in /sandbox/sandbox_multi.php
        \-- is_geo_value - section for function is_geo_value not yet defined that it should be save helper - user sandbox in /sandbox/sandbox_multi.php
        \-- has_usr_cfg - section for function has_usr_cfg not yet defined that it should be save helper - user sandbox in /sandbox/sandbox_multi.php
        \-- del_usr_cfg_exe - section for function del_usr_cfg_exe is expected to be del in /sandbox/sandbox_multi.php
        \-- del_usr_cfg - section for function del_usr_cfg is expected to be del in /sandbox/sandbox_multi.php
        \-- usr_cfg_cleanup - section for function usr_cfg_cleanup not yet defined that it should be save helper - user sandbox in /sandbox/sandbox_multi.php
        \-- db_ready - section for function db_ready not yet defined that it should be save helper - save fields in /sandbox/sandbox_multi.php
        \-- save_fields - section for function save_fields is expected to be save in /sandbox/sandbox_multi.php
        \-- save_field_user - section for function save_field_user is expected to be save in /sandbox/sandbox_multi.php
        \-- sql_update_multi - section for function sql_update_multi not yet defined that it should be save helper - save fields in /sandbox/sandbox_multi.php
        \-- sql_delete - section for function sql_delete not yet defined that it should be save helper - save fields in /sandbox/sandbox_multi.php
        \-- name_field - section for function name_field is expected to be sql fields in /sandbox/sandbox_multi.php
        \-- save_field - section for function save_field is expected to be save in /sandbox/sandbox_multi.php
        \-- save_field_excluded_log - section for function save_field_excluded_log is expected to be save in /sandbox/sandbox_multi.php
        \-- save_field_excluded - section for function save_field_excluded is expected to be save in /sandbox/sandbox_multi.php
        \-- save_field_share - section for function save_field_share is expected to be save in /sandbox/sandbox_multi.php
        \-- save_field_protection - section for function save_field_protection is expected to be save in /sandbox/sandbox_multi.php
        \-- save_set_log_id - section for function save_set_log_id is expected to be save in /sandbox/sandbox_multi.php
        \-- is_id_updated - section for function is_id_updated not yet defined that it should be save helper - check id in /sandbox/sandbox_multi.php
        \-- get_obj_with_same_id_fields - section for function get_obj_with_same_id_fields is expected to be set and get in /sandbox/sandbox_multi.php
        \-- msg_id_already_used - section for function msg_id_already_used not yet defined that it should be save helper - check id in /sandbox/sandbox_multi.php
        \-- save_id_if_updated - section for function save_id_if_updated is expected to be save in /sandbox/sandbox_multi.php
        \-- save_id_fields - section for function save_id_fields is expected to be save in /sandbox/sandbox_multi.php
        \-- is_same_std - section for function is_same_std not yet defined that it should be save helper - check similar in /sandbox/sandbox_multi.php
        \-- is_same - section for function is_same not yet defined that it should be save helper - check similar in /sandbox/sandbox_multi.php
        \-- is_similar - section for function is_similar not yet defined that it should be save helper - check similar in /sandbox/sandbox_multi.php
        \-- get_similar - section for function get_similar is expected to be save in /sandbox/sandbox_multi.php
        \-- add - section for function add not yet defined that it should be add in /sandbox/sandbox_multi.php
        \-- del - section for function del is expected to be del in /sandbox/sandbox_multi.php
        \-- save_fields_func - section for function save_fields_func is expected to be save in /sandbox/sandbox_multi.php
        \-- sql_write - section for function sql_write not yet defined that it should be delete in /sandbox/sandbox_multi.php
        \-- sql_write_with_log - section for function sql_write_with_log not yet defined that it should be delete in /sandbox/sandbox_multi.php
        \-- id_fvt_lst - section for function id_fvt_lst not yet defined that it should be delete in /sandbox/sandbox_multi.php
        \-- db_fields_all - section for function db_fields_all is expected to be sql write fields in /sandbox/sandbox_multi.php
        \-- no_diff - section for function no_diff not yet defined that it should be delete in /sandbox/sandbox_multi.php
        \-- db_fields_changed - section for function db_fields_changed is expected to be sql write fields in /sandbox/sandbox_multi.php
        \-- sql_insert_switch - section for function sql_insert_switch not yet defined that it should be delete in /sandbox/sandbox_multi.php
        \-- sql_update_switch - section for function sql_update_switch not yet defined that it should be delete in /sandbox/sandbox_multi.php
        \-- sql_insert_key_field - section for function sql_insert_key_field not yet defined that it should be delete in /sandbox/sandbox_multi.php
        \-- id_used_msg - section for function id_used_msg not yet defined that it should be delete in /sandbox/sandbox_multi.php
        \-- is_prime - section for function is_prime not yet defined that it should be delete in /sandbox/sandbox_multi.php
        \-- is_main - section for function is_main not yet defined that it should be delete in /sandbox/sandbox_multi.php
        \-- is_big - section for function is_big not yet defined that it should be delete in /sandbox/sandbox_multi.php
        \-- id_names - section for function id_names not yet defined that it should be delete in /sandbox/sandbox_multi.php
        \-- id_lst - section for function id_lst not yet defined that it should be delete in /sandbox/sandbox_multi.php
        \-- id_or_lst - section for function id_or_lst not yet defined that it should be delete in /sandbox/sandbox_multi.php
        \-- del_links - section for function del_links is expected to be del in /sandbox/sandbox_multi.php
        \-- sql_default_script_usage - section for function sql_default_script_usage not yet defined that it should be delete in /sandbox/sandbox_multi.php
        \-- db_changed_sandbox_list - section for function db_changed_sandbox_list not yet defined that it should be sql write fields in /sandbox/sandbox_multi.php
        \-- save_field_type - section for function save_field_type is expected to be save in /sandbox/sandbox_multi.php
        \-- type_name - section for function type_name is expected to be preloaded in /sandbox/sandbox_multi.php
        \-- db_fields_all_sandbox - section for function db_fields_all_sandbox not yet defined that it should be sql write fields in /sandbox/sandbox_multi.php
        \-- db_fields_changed_sandbox - section for function db_fields_changed_sandbox not yet defined that it should be sql write fields in /sandbox/sandbox_multi.php
        \-- table_type - section for function table_type not yet defined that it should be sql helper in /sandbox/sandbox_multi.php
        \-- value_type - section for function value_type not yet defined that it should be sql helper in /sandbox/sandbox_multi.php
        \-- table_extension - section for function table_extension not yet defined that it should be sql helper in /sandbox/sandbox_multi.php
        \-- id_field_type - section for function id_field_type not yet defined that it should be sql helper in /sandbox/sandbox_multi.php
        \-- is_named_obj - section for function is_named_obj not yet defined that it should be internal in /sandbox/sandbox_multi.php
        \-- is_link_obj - section for function is_link_obj not yet defined that it should be internal in /sandbox/sandbox_multi.php
        \-- is_value_obj - section for function is_value_obj not yet defined that it should be internal in /sandbox/sandbox_multi.php
        \-- fld_id - section for function fld_id not yet defined that it should be internal check in /sandbox/sandbox_multi.php
        \-- fld_usr_id - section for function fld_usr_id not yet defined that it should be internal check in /sandbox/sandbox_multi.php
        \-- fld_name - section for function fld_name not yet defined that it should be internal check in /sandbox/sandbox_multi.php
        \-- fill_api_obj - section for function fill_api_obj not yet defined that it should be internal check in /sandbox/sandbox_multi.php
        \-- fill_ui_obj - section for function fill_ui_obj not yet defined that it should be internal check in /sandbox/sandbox_multi.php
        \-- load - section for function load not yet defined that it should be internal check in /sandbox/sandbox_multi.php
        \-- order error - order of section construct and map has difference at api_mapper should be after reset of __construct,reset,row_mapper_sandbox_multi,row_mapper_usr,row_mapper_std,api_mapper,import_mapper does not match __construct,row_mapper_sandbox,api_mapper,reset,row_mapper,import_mapper,code_id,load_dummy,default_id,order of section construct and map has difference at api_mapper should be after reset of __construct,reset,row_mapper_sandbox_multi,row_mapper_usr,row_mapper_std,api_mapper,import_mapper does not match __construct,row_mapper_sandbox,api_mapper,reset,row_mapper,import_mapper,code_id,load_dummy,default_id,order of section construct and map has difference at api_mapper should be after reset of __construct,reset,row_mapper_sandbox_multi,row_mapper_usr,row_mapper_std,api_mapper,import_mapper does not match __construct,row_mapper_sandbox,api_mapper,reset,row_mapper,import_mapper,code_id,load_dummy,default_id,order of section construct and map has difference at api_mapper should be after reset of __construct,reset,row_mapper_sandbox_multi,row_mapper_usr,row_mapper_std,api_mapper,import_mapper does not match __construct,row_mapper_sandbox,api_mapper,reset,row_mapper,import_mapper,code_id,load_dummy,default_id,order of section construct and map has difference at api_mapper should be after reset of __construct,reset,row_mapper_sandbox_multi,row_mapper_usr,row_mapper_std,api_mapper,import_mapper does not match __construct,row_mapper_sandbox,api_mapper,reset,row_mapper,import_mapper,code_id,load_dummy,default_id,order of section construct and map has difference at api_mapper should be after reset of __construct,reset,row_mapper_sandbox_multi,row_mapper_usr,row_mapper_std,api_mapper,import_mapper does not match __construct,row_mapper_sandbox,api_mapper,reset,row_mapper,import_mapper,code_id,load_dummy,default_id,order of section construct and map has difference at api_mapper should be after reset of __construct,reset,row_mapper_sandbox_multi,row_mapper_usr,row_mapper_std,api_mapper,import_mapper does not match __construct,row_mapper_sandbox,api_mapper,reset,row_mapper,import_mapper,code_id,load_dummy,default_id,order of section set and get has difference at exclude should be after set_from_api, include should be after set_from_api, is_excluded should be after set_from_api, is_exclusion_set should be after set_from_api of set_from_api,set_excluded,exclude,include,is_excluded,grp,set_owner_id,owner_id,owner,set_share_id,share_id,set_protection_id,protection_id,set_source,source_id,is_exclusion_set does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,key_list,order of section set and get has difference at exclude should be after set_from_api, include should be after set_from_api, is_excluded should be after set_from_api, is_exclusion_set should be after set_from_api of set_from_api,set_excluded,exclude,include,is_excluded,grp,set_owner_id,owner_id,owner,set_share_id,share_id,set_protection_id,protection_id,set_source,source_id,is_exclusion_set does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,key_list,order of section set and get has difference at exclude should be after set_from_api, include should be after set_from_api, is_excluded should be after set_from_api, is_exclusion_set should be after set_from_api of set_from_api,set_excluded,exclude,include,is_excluded,grp,set_owner_id,owner_id,owner,set_share_id,share_id,set_protection_id,protection_id,set_source,source_id,is_exclusion_set does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,key_list,order of section set and get has difference at exclude should be after set_from_api, include should be after set_from_api, is_excluded should be after set_from_api, is_exclusion_set should be after set_from_api of set_from_api,set_excluded,exclude,include,is_excluded,grp,set_owner_id,owner_id,owner,set_share_id,share_id,set_protection_id,protection_id,set_source,source_id,is_exclusion_set does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,key_list,order of section set and get has difference at exclude should be after set_from_api, include should be after set_from_api, is_excluded should be after set_from_api, is_exclusion_set should be after set_from_api of set_from_api,set_excluded,exclude,include,is_excluded,grp,set_owner_id,owner_id,owner,set_share_id,share_id,set_protection_id,protection_id,set_source,source_id,is_exclusion_set does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,key_list,order of section set and get has difference at exclude should be after set_from_api, include should be after set_from_api, is_excluded should be after set_from_api, is_exclusion_set should be after set_from_api of set_from_api,set_excluded,exclude,include,is_excluded,grp,set_owner_id,owner_id,owner,set_share_id,share_id,set_protection_id,protection_id,set_source,source_id,is_exclusion_set does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,key_list,order of section set and get has difference at exclude should be after set_from_api, include should be after set_from_api, is_excluded should be after set_from_api, is_exclusion_set should be after set_from_api of set_from_api,set_excluded,exclude,include,is_excluded,grp,set_owner_id,owner_id,owner,set_share_id,share_id,set_protection_id,protection_id,set_source,source_id,is_exclusion_set does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,key_list,order of section set and get has difference at exclude should be after set_from_api, include should be after set_from_api, is_excluded should be after set_from_api, is_exclusion_set should be after set_from_api of set_from_api,set_excluded,exclude,include,is_excluded,grp,set_owner_id,owner_id,owner,set_share_id,share_id,set_protection_id,protection_id,set_source,source_id,is_exclusion_set does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,key_list,order of section set and get has difference at exclude should be after set_from_api, include should be after set_from_api, is_excluded should be after set_from_api, is_exclusion_set should be after set_from_api of set_from_api,set_excluded,exclude,include,is_excluded,grp,set_owner_id,owner_id,owner,set_share_id,share_id,set_protection_id,protection_id,set_source,source_id,is_exclusion_set does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,key_list,order of section set and get has difference at exclude should be after set_from_api, include should be after set_from_api, is_excluded should be after set_from_api, is_exclusion_set should be after set_from_api of set_from_api,set_excluded,exclude,include,is_excluded,grp,set_owner_id,owner_id,owner,set_share_id,share_id,set_protection_id,protection_id,set_source,source_id,is_exclusion_set does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,key_list,order of section set and get has difference at exclude should be after set_from_api, include should be after set_from_api, is_excluded should be after set_from_api, is_exclusion_set should be after set_from_api of set_from_api,set_excluded,exclude,include,is_excluded,grp,set_owner_id,owner_id,owner,set_share_id,share_id,set_protection_id,protection_id,set_source,source_id,is_exclusion_set does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,key_list,order of section set and get has difference at exclude should be after set_from_api, include should be after set_from_api, is_excluded should be after set_from_api, is_exclusion_set should be after set_from_api of set_from_api,set_excluded,exclude,include,is_excluded,grp,set_owner_id,owner_id,owner,set_share_id,share_id,set_protection_id,protection_id,set_source,source_id,is_exclusion_set does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,key_list,order of section set and get has difference at exclude should be after set_from_api, include should be after set_from_api, is_excluded should be after set_from_api, is_exclusion_set should be after set_from_api of set_from_api,set_excluded,exclude,include,is_excluded,grp,set_owner_id,owner_id,owner,set_share_id,share_id,set_protection_id,protection_id,set_source,source_id,is_exclusion_set does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,key_list,order of section set and get has difference at exclude should be after set_from_api, include should be after set_from_api, is_excluded should be after set_from_api, is_exclusion_set should be after set_from_api of set_from_api,set_excluded,exclude,include,is_excluded,grp,set_owner_id,owner_id,owner,set_share_id,share_id,set_protection_id,protection_id,set_source,source_id,is_exclusion_set does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,key_list,order of section set and get has difference at exclude should be after set_from_api, include should be after set_from_api, is_excluded should be after set_from_api, is_exclusion_set should be after set_from_api of set_from_api,set_excluded,exclude,include,is_excluded,grp,set_owner_id,owner_id,owner,set_share_id,share_id,set_protection_id,protection_id,set_source,source_id,is_exclusion_set does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,key_list,order of section set and get has difference at exclude should be after set_from_api, include should be after set_from_api, is_excluded should be after set_from_api, is_exclusion_set should be after set_from_api of set_from_api,set_excluded,exclude,include,is_excluded,grp,set_owner_id,owner_id,owner,set_share_id,share_id,set_protection_id,protection_id,set_source,source_id,is_exclusion_set does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,key_list
    \-- sandbox_named
        \-- name - section for function name not yet defined that it should be set and get in /sandbox/sandbox_named.php
        \-- name_or_null - section for function name_or_null not yet defined that it should be set and get in /sandbox/sandbox_named.php
        \-- description - section for function description not yet defined that it should be set and get in /sandbox/sandbox_named.php
        \-- usage - section for function usage not yet defined that it should be set and get in /sandbox/sandbox_named.php
        \-- cloned - section for function cloned not yet defined that it should be set and get in /sandbox/sandbox_named.php
        \-- term - section for function term not yet defined that it should be cast in /sandbox/sandbox_named.php
        \-- get_term - section for function get_term is expected to be set and get in /sandbox/sandbox_named.php
        \-- fill_api_obj - section for function fill_api_obj not yet defined that it should be cast in /sandbox/sandbox_named.php
        \-- load_standard_sql - section for function load_standard_sql is expected to be load in /sandbox/sandbox_named.php
        \-- can_be_ready - section for function can_be_ready not yet defined that it should be info in /sandbox/sandbox_named.php
        \-- db_ready - section for function db_ready not yet defined that it should be info in /sandbox/sandbox_named.php
        \-- is_valid - section for function is_valid not yet defined that it should be info in /sandbox/sandbox_named.php
        \-- no_id_but_name - section for function no_id_but_name not yet defined that it should be info in /sandbox/sandbox_named.php
        \-- log_last_msg - section for function log_last_msg is expected to be log in /sandbox/sandbox_named.php
        \-- log_last_field_msg - section for function log_last_field_msg is expected to be log in /sandbox/sandbox_named.php
        \-- log_add - section for function log_add is expected to be log in /sandbox/sandbox_named.php
        \-- log_del - section for function log_del is expected to be log in /sandbox/sandbox_named.php
        \-- add - section for function add not yet defined that it should be add in /sandbox/sandbox_named.php
        \-- is_id_updated - section for function is_id_updated not yet defined that it should be save helper in /sandbox/sandbox_named.php
        \-- msg_id_already_used - section for function msg_id_already_used not yet defined that it should be save helper in /sandbox/sandbox_named.php
        \-- save_field_description - section for function save_field_description is expected to be save in /sandbox/sandbox_named.php
        \-- save_fields_named - section for function save_fields_named is expected to be save in /sandbox/sandbox_named.php
        \-- save_id_fields - section for function save_id_fields is expected to be save in /sandbox/sandbox_named.php
        \-- is_same_std - section for function is_same_std not yet defined that it should be save helper in /sandbox/sandbox_named.php
        \-- is_similar_named - section for function is_similar_named not yet defined that it should be save helper in /sandbox/sandbox_named.php
        \-- get_similar - section for function get_similar is expected to be save in /sandbox/sandbox_named.php
        \-- sql_insert_switch - section for function sql_insert_switch not yet defined that it should be sql write in /sandbox/sandbox_named.php
        \-- sql_insert_key_field - section for function sql_insert_key_field not yet defined that it should be sql write in /sandbox/sandbox_named.php
        \-- is_named_obj - section for function is_named_obj not yet defined that it should be internal in /sandbox/sandbox_named.php
        \-- order error - order of section construct and map has difference at row_mapper_sandbox should be after reset, api_mapper should be after reset of reset,row_mapper_sandbox,api_mapper,import_mapper does not match __construct,row_mapper_sandbox,api_mapper,reset,row_mapper,import_mapper,code_id,load_dummy,default_id,order of section construct and map has difference at row_mapper_sandbox should be after reset, api_mapper should be after reset of reset,row_mapper_sandbox,api_mapper,import_mapper does not match __construct,row_mapper_sandbox,api_mapper,reset,row_mapper,import_mapper,code_id,load_dummy,default_id,order of section construct and map has difference at row_mapper_sandbox should be after reset, api_mapper should be after reset of reset,row_mapper_sandbox,api_mapper,import_mapper does not match __construct,row_mapper_sandbox,api_mapper,reset,row_mapper,import_mapper,code_id,load_dummy,default_id,order of section construct and map has difference at row_mapper_sandbox should be after reset, api_mapper should be after reset of reset,row_mapper_sandbox,api_mapper,import_mapper does not match __construct,row_mapper_sandbox,api_mapper,reset,row_mapper,import_mapper,code_id,load_dummy,default_id,order of section set and get has difference at set_description should be after name_or_null, description should be after name_or_null of set,set_name,name,name_or_null,set_description,description,set_usage,usage,cloned does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,2,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,key_list,name_or_null,order of section set and get has difference at set_description should be after name_or_null, description should be after name_or_null of set,set_name,name,name_or_null,set_description,description,set_usage,usage,cloned does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,2,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,key_list,name_or_null,order of section set and get has difference at set_description should be after name_or_null, description should be after name_or_null of set,set_name,name,name_or_null,set_description,description,set_usage,usage,cloned does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,2,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,key_list,name_or_null,order of section set and get has difference at set_description should be after name_or_null, description should be after name_or_null of set,set_name,name,name_or_null,set_description,description,set_usage,usage,cloned does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,2,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,key_list,name_or_null,order of section set and get has difference at set_description should be after name_or_null, description should be after name_or_null of set,set_name,name,name_or_null,set_description,description,set_usage,usage,cloned does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,2,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,key_list,name_or_null,order of section info has difference at db_ready should be after diff_msg of diff_msg,needs_db_update,can_be_ready,db_ready,is_valid,no_id_but_name does not match view_ids,cmp_ids,names,common_phrases,id_field,db_ready,has_view_list,diff_msg,has_id,use_type_id,first_msg,empty,id,missing_ids,prime_only,one_positiv,id_lst,obj_id_lst,phrase_ids,wrd_ids,trp_ids,id_url,id_url_long,loaded,are_and_contains,differentiators,differentiators_all,differentiators_filtered,order of section info has difference at db_ready should be after diff_msg of diff_msg,needs_db_update,can_be_ready,db_ready,is_valid,no_id_but_name does not match view_ids,cmp_ids,names,common_phrases,id_field,db_ready,has_view_list,diff_msg,has_id,use_type_id,first_msg,empty,id,missing_ids,prime_only,one_positiv,id_lst,obj_id_lst,phrase_ids,wrd_ids,trp_ids,id_url,id_url_long,loaded,are_and_contains,differentiators,differentiators_all,differentiators_filtered,order of section info has difference at db_ready should be after diff_msg of diff_msg,needs_db_update,can_be_ready,db_ready,is_valid,no_id_but_name does not match view_ids,cmp_ids,names,common_phrases,id_field,db_ready,has_view_list,diff_msg,has_id,use_type_id,first_msg,empty,id,missing_ids,prime_only,one_positiv,id_lst,obj_id_lst,phrase_ids,wrd_ids,trp_ids,id_url,id_url_long,loaded,are_and_contains,differentiators,differentiators_all,differentiators_filtered,order of section info has difference at db_ready should be after diff_msg of diff_msg,needs_db_update,can_be_ready,db_ready,is_valid,no_id_but_name does not match view_ids,cmp_ids,names,common_phrases,id_field,db_ready,has_view_list,diff_msg,has_id,use_type_id,first_msg,empty,id,missing_ids,prime_only,one_positiv,id_lst,obj_id_lst,phrase_ids,wrd_ids,trp_ids,id_url,id_url_long,loaded,are_and_contains,differentiators,differentiators_all,differentiators_filtered,order of section info has difference at db_ready should be after diff_msg of diff_msg,needs_db_update,can_be_ready,db_ready,is_valid,no_id_but_name does not match view_ids,cmp_ids,names,common_phrases,id_field,db_ready,has_view_list,diff_msg,has_id,use_type_id,first_msg,empty,id,missing_ids,prime_only,one_positiv,id_lst,obj_id_lst,phrase_ids,wrd_ids,trp_ids,id_url,id_url_long,loaded,are_and_contains,differentiators,differentiators_all,differentiators_filtered,order of section info has difference at db_ready should be after diff_msg of diff_msg,needs_db_update,can_be_ready,db_ready,is_valid,no_id_but_name does not match view_ids,cmp_ids,names,common_phrases,id_field,db_ready,has_view_list,diff_msg,has_id,use_type_id,first_msg,empty,id,missing_ids,prime_only,one_positiv,id_lst,obj_id_lst,phrase_ids,wrd_ids,trp_ids,id_url,id_url_long,loaded,are_and_contains,differentiators,differentiators_all,differentiators_filtered
    \-- sandbox_typed
        \-- type_id - section for function type_id not yet defined that it should be set and get in /sandbox/sandbox_typed.php
        \-- fill_api_obj - section for function fill_api_obj not yet defined that it should be cast in /sandbox/sandbox_typed.php
        \-- save_fields_typed - section for function save_fields_typed is expected to be save in /sandbox/sandbox_typed.php
        \-- order error - order of section construct and map has difference at row_mapper_sandbox should be after reset, api_mapper should be after reset of reset,row_mapper_sandbox,api_mapper,import_mapper_user does not match __construct,row_mapper_sandbox,api_mapper,reset,row_mapper,import_mapper,code_id,load_dummy,default_id,order of section construct and map has difference at row_mapper_sandbox should be after reset, api_mapper should be after reset of reset,row_mapper_sandbox,api_mapper,import_mapper_user does not match __construct,row_mapper_sandbox,api_mapper,reset,row_mapper,import_mapper,code_id,load_dummy,default_id,order of section construct and map has difference at row_mapper_sandbox should be after reset, api_mapper should be after reset of reset,row_mapper_sandbox,api_mapper,import_mapper_user does not match __construct,row_mapper_sandbox,api_mapper,reset,row_mapper,import_mapper,code_id,load_dummy,default_id,order of section construct and map has difference at row_mapper_sandbox should be after reset, api_mapper should be after reset of reset,row_mapper_sandbox,api_mapper,import_mapper_user does not match __construct,row_mapper_sandbox,api_mapper,reset,row_mapper,import_mapper,code_id,load_dummy,default_id,order of section set and get has difference at type_id should be after set_type of set_type_id,set_type,set_type_by_code_id,set_type_by_name,type_id does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,key_list,set_type,order of section set and get has difference at type_id should be after set_type of set_type_id,set_type,set_type_by_code_id,set_type_by_name,type_id does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,key_list,set_type,order of section set and get has difference at type_id should be after set_type of set_type_id,set_type,set_type_by_code_id,set_type_by_name,type_id does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,key_list,set_type
    \-- sandbox_value
        \-- clone_all - section for function clone_all not yet defined that it should be construct and map in /sandbox/sandbox_value.php
        \-- row_mapper_multi - section for function row_mapper_multi not yet defined that it should be construct and map in /sandbox/sandbox_value.php
        \-- grp - section for function grp not yet defined that it should be set and get in /sandbox/sandbox_value.php
        \-- grp_id - section for function grp_id not yet defined that it should be set and get in /sandbox/sandbox_value.php
        \-- phrase_list - section for function phrase_list not yet defined that it should be set and get in /sandbox/sandbox_value.php
        \-- ids - section for function ids not yet defined that it should be set and get in /sandbox/sandbox_value.php
        \-- description - section for function description not yet defined that it should be set and get in /sandbox/sandbox_value.php
        \-- value - section for function value not yet defined that it should be set and get in /sandbox/sandbox_value.php
        \-- is_id_set - section for function is_id_set not yet defined that it should be set and get in /sandbox/sandbox_value.php
        \-- number - section for function number not yet defined that it should be set and get in /sandbox/sandbox_value.php
        \-- source - section for function source not yet defined that it should be set and get in /sandbox/sandbox_value.php
        \-- last_update - section for function last_update not yet defined that it should be set and get in /sandbox/sandbox_value.php
        \-- table_type - section for function table_type not yet defined that it should be set and get in /sandbox/sandbox_value.php
        \-- value_type - section for function value_type not yet defined that it should be set and get in /sandbox/sandbox_value.php
        \-- is_numeric - section for function is_numeric not yet defined that it should be set and get in /sandbox/sandbox_value.php
        \-- is_time_value - section for function is_time_value not yet defined that it should be set and get in /sandbox/sandbox_value.php
        \-- is_text_value - section for function is_text_value not yet defined that it should be set and get in /sandbox/sandbox_value.php
        \-- is_geo_value - section for function is_geo_value not yet defined that it should be set and get in /sandbox/sandbox_value.php
        \-- table_extension - section for function table_extension not yet defined that it should be set and get in /sandbox/sandbox_value.php
        \-- formula_id - section for function formula_id not yet defined that it should be set and get in /sandbox/sandbox_value.php
        \-- is_prime - section for function is_prime not yet defined that it should be forward group get in /sandbox/sandbox_value.php
        \-- is_main - section for function is_main not yet defined that it should be forward group get in /sandbox/sandbox_value.php
        \-- is_big - section for function is_big not yet defined that it should be forward group get in /sandbox/sandbox_value.php
        \-- is_text - section for function is_text not yet defined that it should be forward group get in /sandbox/sandbox_value.php
        \-- is_time - section for function is_time not yet defined that it should be forward group get in /sandbox/sandbox_value.php
        \-- is_geo - section for function is_geo not yet defined that it should be forward group get in /sandbox/sandbox_value.php
        \-- id_lst - section for function id_lst not yet defined that it should be forward group get in /sandbox/sandbox_value.php
        \-- sql_table - section for function sql_table not yet defined that it should be sql create in /sandbox/sandbox_value.php
        \-- sql_index - section for function sql_index not yet defined that it should be sql create in /sandbox/sandbox_value.php
        \-- sql_foreign_key - section for function sql_foreign_key not yet defined that it should be sql create in /sandbox/sandbox_value.php
        \-- load_sql_by_id - section for function load_sql_by_id is expected to be load sql in /sandbox/sandbox_value.php
        \-- load_sql_by_grp - section for function load_sql_by_grp is expected to be load sql in /sandbox/sandbox_value.php
        \-- load_sql_user_changes - section for function load_sql_user_changes is expected to be load sql in /sandbox/sandbox_value.php
        \-- load_sql_changer - section for function load_sql_changer is expected to be load sql in /sandbox/sandbox_value.php
        \-- changer - section for function changer not yet defined that it should be load in /sandbox/sandbox_value.php
        \-- load_sql_median_user - section for function load_sql_median_user is expected to be load sql in /sandbox/sandbox_value.php
        \-- is_saved - section for function is_saved not yet defined that it should be info in /sandbox/sandbox_value.php
        \-- id_fvt_lst - section for function id_fvt_lst not yet defined that it should be info in /sandbox/sandbox_value.php
        \-- id_field - section for function id_field not yet defined that it should be info in /sandbox/sandbox_value.php
        \-- id_fields_both - section for function id_fields_both not yet defined that it should be info in /sandbox/sandbox_value.php
        \-- id_fields_prime - section for function id_fields_prime not yet defined that it should be info in /sandbox/sandbox_value.php
        \-- id_fields_main - section for function id_fields_main not yet defined that it should be info in /sandbox/sandbox_value.php
        \-- id_field_group - section for function id_field_group not yet defined that it should be info in /sandbox/sandbox_value.php
        \-- id_field_list - section for function id_field_list not yet defined that it should be info in /sandbox/sandbox_value.php
        \-- wrd_lst - section for function wrd_lst not yet defined that it should be load in /sandbox/sandbox_value.php
        \-- trp_lst - section for function trp_lst not yet defined that it should be load in /sandbox/sandbox_value.php
        \-- log_add_value - section for function log_add_value is expected to be log in /sandbox/sandbox_value.php
        \-- log_del - section for function log_del is expected to be log in /sandbox/sandbox_value.php
        \-- sql_update_value - section for function sql_update_value not yet defined that it should be save in /sandbox/sandbox_value.php
        \-- sql_insert - section for function sql_insert not yet defined that it should be sql write in /sandbox/sandbox_value.php
        \-- sql_update - section for function sql_update not yet defined that it should be sql write in /sandbox/sandbox_value.php
        \-- sql_update_fields - section for function sql_update_fields not yet defined that it should be sql write in /sandbox/sandbox_value.php
        \-- sql_delete - section for function sql_delete not yet defined that it should be sql write in /sandbox/sandbox_value.php
        \-- db_changed - section for function db_changed not yet defined that it should be TODO check if sandbox named function match this logic in /sandbox/sandbox_value.php
        \-- db_fields_changed_value - section for function db_fields_changed_value not yet defined that it should be TODO check if sandbox named function match this logic in /sandbox/sandbox_value.php
        \-- db_values_changed_value - section for function db_values_changed_value not yet defined that it should be TODO check if sandbox named function match this logic in /sandbox/sandbox_value.php
        \-- cloned - section for function cloned not yet defined that it should be clone in /sandbox/sandbox_value.php
        \-- updated - section for function updated not yet defined that it should be clone in /sandbox/sandbox_value.php
        \-- is_value_obj - section for function is_value_obj not yet defined that it should be internal in /sandbox/sandbox_value.php
        \-- is_named_obj - section for function is_named_obj not yet defined that it should be internal in /sandbox/sandbox_value.php
        \-- dsp_id_short - section for function dsp_id_short not yet defined that it should be debug in /sandbox/sandbox_value.php
        \-- dsp - section for function dsp not yet defined that it should be debug in /sandbox/sandbox_value.php
        \-- dsp_db - section for function dsp_db not yet defined that it should be debug in /sandbox/sandbox_value.php
        \-- order error - order of section construct and map has difference at api_mapper should be after reset of __construct,reset,clone_all,row_mapper_multi,api_mapper does not match __construct,row_mapper_sandbox,api_mapper,reset,row_mapper,import_mapper,code_id,load_dummy,default_id,order of section construct and map has difference at api_mapper should be after reset of __construct,reset,clone_all,row_mapper_multi,api_mapper does not match __construct,row_mapper_sandbox,api_mapper,reset,row_mapper,import_mapper,code_id,load_dummy,default_id,order of section construct and map has difference at api_mapper should be after reset of __construct,reset,clone_all,row_mapper_multi,api_mapper does not match __construct,row_mapper_sandbox,api_mapper,reset,row_mapper,import_mapper,code_id,load_dummy,default_id,order of section construct and map has difference at api_mapper should be after reset of __construct,reset,clone_all,row_mapper_multi,api_mapper does not match __construct,row_mapper_sandbox,api_mapper,reset,row_mapper,import_mapper,code_id,load_dummy,default_id,order of section construct and map has difference at api_mapper should be after reset of __construct,reset,clone_all,row_mapper_multi,api_mapper does not match __construct,row_mapper_sandbox,api_mapper,reset,row_mapper,import_mapper,code_id,load_dummy,default_id,order of section info has difference at id_field should be after diff_msg of is_saved,diff_msg,id_fvt_lst,id_field,id_fields_both,id_fields_prime,id_fields_main,id_field_group,id_field_list does not match view_ids,cmp_ids,names,common_phrases,id_field,db_ready,has_view_list,diff_msg,has_id,use_type_id,first_msg,empty,id,missing_ids,prime_only,one_positiv,id_lst,obj_id_lst,phrase_ids,wrd_ids,trp_ids,id_url,id_url_long,loaded,are_and_contains,differentiators,differentiators_all,differentiators_filtered,order of section info has difference at id_field should be after diff_msg of is_saved,diff_msg,id_fvt_lst,id_field,id_fields_both,id_fields_prime,id_fields_main,id_field_group,id_field_list does not match view_ids,cmp_ids,names,common_phrases,id_field,db_ready,has_view_list,diff_msg,has_id,use_type_id,first_msg,empty,id,missing_ids,prime_only,one_positiv,id_lst,obj_id_lst,phrase_ids,wrd_ids,trp_ids,id_url,id_url_long,loaded,are_and_contains,differentiators,differentiators_all,differentiators_filtered,order of section info has difference at id_field should be after diff_msg of is_saved,diff_msg,id_fvt_lst,id_field,id_fields_both,id_fields_prime,id_fields_main,id_field_group,id_field_list does not match view_ids,cmp_ids,names,common_phrases,id_field,db_ready,has_view_list,diff_msg,has_id,use_type_id,first_msg,empty,id,missing_ids,prime_only,one_positiv,id_lst,obj_id_lst,phrase_ids,wrd_ids,trp_ids,id_url,id_url_long,loaded,are_and_contains,differentiators,differentiators_all,differentiators_filtered,order of section info has difference at id_field should be after diff_msg of is_saved,diff_msg,id_fvt_lst,id_field,id_fields_both,id_fields_prime,id_fields_main,id_field_group,id_field_list does not match view_ids,cmp_ids,names,common_phrases,id_field,db_ready,has_view_list,diff_msg,has_id,use_type_id,first_msg,empty,id,missing_ids,prime_only,one_positiv,id_lst,obj_id_lst,phrase_ids,wrd_ids,trp_ids,id_url,id_url_long,loaded,are_and_contains,differentiators,differentiators_all,differentiators_filtered,order of section info has difference at id_field should be after diff_msg of is_saved,diff_msg,id_fvt_lst,id_field,id_fields_both,id_fields_prime,id_fields_main,id_field_group,id_field_list does not match view_ids,cmp_ids,names,common_phrases,id_field,db_ready,has_view_list,diff_msg,has_id,use_type_id,first_msg,empty,id,missing_ids,prime_only,one_positiv,id_lst,obj_id_lst,phrase_ids,wrd_ids,trp_ids,id_url,id_url_long,loaded,are_and_contains,differentiators,differentiators_all,differentiators_filtered,order of section info has difference at id_field should be after diff_msg of is_saved,diff_msg,id_fvt_lst,id_field,id_fields_both,id_fields_prime,id_fields_main,id_field_group,id_field_list does not match view_ids,cmp_ids,names,common_phrases,id_field,db_ready,has_view_list,diff_msg,has_id,use_type_id,first_msg,empty,id,missing_ids,prime_only,one_positiv,id_lst,obj_id_lst,phrase_ids,wrd_ids,trp_ids,id_url,id_url_long,loaded,are_and_contains,differentiators,differentiators_all,differentiators_filtered,order of section info has difference at id_field should be after diff_msg of is_saved,diff_msg,id_fvt_lst,id_field,id_fields_both,id_fields_prime,id_fields_main,id_field_group,id_field_list does not match view_ids,cmp_ids,names,common_phrases,id_field,db_ready,has_view_list,diff_msg,has_id,use_type_id,first_msg,empty,id,missing_ids,prime_only,one_positiv,id_lst,obj_id_lst,phrase_ids,wrd_ids,trp_ids,id_url,id_url_long,loaded,are_and_contains,differentiators,differentiators_all,differentiators_filtered,order of section info has difference at id_field should be after diff_msg of is_saved,diff_msg,id_fvt_lst,id_field,id_fields_both,id_fields_prime,id_fields_main,id_field_group,id_field_list does not match view_ids,cmp_ids,names,common_phrases,id_field,db_ready,has_view_list,diff_msg,has_id,use_type_id,first_msg,empty,id,missing_ids,prime_only,one_positiv,id_lst,obj_id_lst,phrase_ids,wrd_ids,trp_ids,id_url,id_url_long,loaded,are_and_contains,differentiators,differentiators_all,differentiators_filtered,order of section info has difference at id_field should be after diff_msg of is_saved,diff_msg,id_fvt_lst,id_field,id_fields_both,id_fields_prime,id_fields_main,id_field_group,id_field_list does not match view_ids,cmp_ids,names,common_phrases,id_field,db_ready,has_view_list,diff_msg,has_id,use_type_id,first_msg,empty,id,missing_ids,prime_only,one_positiv,id_lst,obj_id_lst,phrase_ids,wrd_ids,trp_ids,id_url,id_url_long,loaded,are_and_contains,differentiators,differentiators_all,differentiators_filtered
    \-- sandbox_value_list
        \-- name_lst - section for function name_lst not yet defined that it should be set and get in /sandbox/sandbox_value_list.php
        \-- load_sql_by_phr_lst_multi - section for function load_sql_by_phr_lst_multi is expected to be load sql in /sandbox/sandbox_value_list.php
        \-- load_sql_by_phr_lst_single - section for function load_sql_by_phr_lst_single is expected to be load sql in /sandbox/sandbox_value_list.php
        \-- load_sql_init - section for function load_sql_init is expected to be load sql in /sandbox/sandbox_value_list.php
        \-- id_lst - section for function id_lst not yet defined that it should be info in /sandbox/sandbox_value_list.php
        \-- add_by_group - section for function add_by_group not yet defined that it should be modify in /sandbox/sandbox_value_list.php
        \-- add - section for function add not yet defined that it should be modify in /sandbox/sandbox_value_list.php
    \-- base_list
        \-- api_lst - section for function api_lst not yet defined that it should be set and get in /system/base_list.php
        \-- offset - section for function offset not yet defined that it should be set and get in /system/base_list.php
        \-- lst_key - section for function lst_key not yet defined that it should be set and get in /system/base_list.php
        \-- api_json - section for function api_json not yet defined that it should be api in /system/base_list.php
        \-- order error - order of section api has difference at api_json_array should be after api_json of api_json,api_json_array does not match 1,api_json_array,api_json,api_array,order of section api has difference at api_json_array should be after api_json of api_json,api_json_array does not match 1,api_json_array,api_json,api_array
    \-- ip_range_exp
        \-- reset - section for function reset is expected to be construct and map in /system/ip_range.php
        \-- row_mapper - section for function row_mapper not yet defined that it should be construct and map in /system/ip_range.php
        \-- user - section for function user not yet defined that it should be set and get in /system/ip_range.php
        \-- load_sql - section for function load_sql is expected to be load sql in /system/ip_range.php
        \-- load_sql_by_vars - section for function load_sql_by_vars is expected to be load sql in /system/ip_range.php
        \-- load_by_id - section for function load_by_id is expected to be load in /system/ip_range.php
        \-- includes - section for function includes not yet defined that it should be check in /system/ip_range.php
        \-- log_add - section for function log_add is expected to be log in /system/ip_range.php
        \-- id_field - section for function id_field not yet defined that it should be save in /system/ip_range.php
        \-- name - section for function name not yet defined that it should be debug in /system/ip_range.php
    \-- ip_range_list
        \-- add - section for function add not yet defined that it should be modify in /system/ip_range_list.php
        \-- load_sql_obj_vars - section for function load_sql_obj_vars is expected to be load sql in /system/ip_range_list.php
        \-- load - section for function load not yet defined that it should be loading in /system/ip_range_list.php
        \-- includes - section for function includes not yet defined that it should be using ip range list in /system/ip_range_list.php
    \-- job
        \-- row_mapper - section for function row_mapper not yet defined that it should be construct and map in /system/job.php
        \-- type_id - section for function type_id not yet defined that it should be set and get in /system/job.php
        \-- type_code_id - section for function type_code_id is expected to be preloaded in /system/job.php
        \-- load_sql - section for function load_sql is expected to be load sql in /system/job.php
        \-- load_sql_by_id - section for function load_sql_by_id is expected to be load sql in /system/job.php
        \-- id_field - section for function id_field not yet defined that it should be load in /system/job.php
        \-- add - section for function add not yet defined that it should be modify in /system/job.php
        \-- exe_val_upd - section for function exe_val_upd not yet defined that it should be modify in /system/job.php
        \-- exe - section for function exe not yet defined that it should be modify in /system/job.php
        \-- del - section for function del is expected to be del in /system/job.php
        \-- name - section for function name not yet defined that it should be debug in /system/job.php
        \-- order error - order of section modify has difference at del should be after exe_val_upd of add,exe_val_upd,exe,del does not match add_by_name,1,add,remove,add_word,add_verb,add_triple,add_triple_without_ready_check,add_phrase,add_source,add_reference,add_formula,add_formula_without_ready_check,add_term,add_view,add_component,add_term_view,add_user,add_ip_range,add_value,add_message,get_component_by_name,get_value_by_names,expected_word_import_time,expected_triple_import_time,expected_value_import_time,expected_total_import_time,count,save,diff_msg,fill,query_extension,dsp_last,add_ref,use_type_id,add_wrd_lst,add_trp_lst,add_id,add_name,2,del,merge_by_name,del_list,filter_by_ids,filter_valid,get_diff,diff,not_in,diff_by_ids,keep_only_specific,has_time,has_measure,has_scaling,has_percent,time_lst_old,time_word_list,time_list,get_by_type,get_names_by_type,time_useful,assume_time,measure_lst,scaling_lst,ex_time,ex_measure,ex_scaling,name_sort,sort_by_id,sort_rev_by_id,max_time,get_grp_id,common,concat_unique,add_link,add_link_by_key,add_obj,exe_val_upd,order of section modify has difference at del should be after exe_val_upd of add,exe_val_upd,exe,del does not match add_by_name,1,add,remove,add_word,add_verb,add_triple,add_triple_without_ready_check,add_phrase,add_source,add_reference,add_formula,add_formula_without_ready_check,add_term,add_view,add_component,add_term_view,add_user,add_ip_range,add_value,add_message,get_component_by_name,get_value_by_names,expected_word_import_time,expected_triple_import_time,expected_value_import_time,expected_total_import_time,count,save,diff_msg,fill,query_extension,dsp_last,add_ref,use_type_id,add_wrd_lst,add_trp_lst,add_id,add_name,2,del,merge_by_name,del_list,filter_by_ids,filter_valid,get_diff,diff,not_in,diff_by_ids,keep_only_specific,has_time,has_measure,has_scaling,has_percent,time_lst_old,time_word_list,time_list,get_by_type,get_names_by_type,time_useful,assume_time,measure_lst,scaling_lst,ex_time,ex_measure,ex_scaling,name_sort,sort_by_id,sort_rev_by_id,max_time,get_grp_id,common,concat_unique,add_link,add_link_by_key,add_obj,exe_val_upd
    \-- job_list
        \-- load_by_type - section for function load_by_type is expected to be load in /system/job_list.php
        \-- load_sql_by_type - section for function load_sql_by_type is expected to be load sql in /system/job_list.php
        \-- add - section for function add not yet defined that it should be modify in /system/job_list.php
        \-- merge - section for function merge not yet defined that it should be modify in /system/job_list.php
    \-- sys_log
        \-- row_mapper - section for function row_mapper not yet defined that it should be construct and map in /system/sys_log.php
        \-- user - section for function user not yet defined that it should be set and get in /system/sys_log.php
        \-- status_name - section for function status_name not yet defined that it should be preloaded in /system/sys_log.php
        \-- sql_table - section for function sql_table not yet defined that it should be sql create in /system/sys_log.php
        \-- sql_index - section for function sql_index not yet defined that it should be sql create in /system/sys_log.php
        \-- sql_foreign_key - section for function sql_foreign_key not yet defined that it should be sql create in /system/sys_log.php
        \-- load_sql - section for function load_sql is expected to be load sql in /system/sys_log.php
        \-- load_sql_by_id - section for function load_sql_by_id is expected to be load sql in /system/sys_log.php
        \-- id_field - section for function id_field not yet defined that it should be load in /system/sys_log.php
        \-- save - section for function save is expected to be save in /system/sys_log.php
    \-- sys_log_list
        \-- user - section for function user not yet defined that it should be set and get in /system/sys_log_list.php
        \-- load_sql - section for function load_sql is expected to be load sql in /system/sys_log_list.php
        \-- load - section for function load not yet defined that it should be loading / database access object (DAO) functions in /system/sys_log_list.php
        \-- load_all - section for function load_all is expected to be load in /system/sys_log_list.php
        \-- add - section for function add not yet defined that it should be loading / database access object (DAO) functions in /system/sys_log_list.php
    \-- system_time_list
        \-- switch - section for function switch not yet defined that it should be interface in /system/system_time_list.php
        \-- report - section for function report not yet defined that it should be interface in /system/system_time_list.php
        \-- section_report - section for function section_report not yet defined that it should be interface in /system/system_time_list.php
        \-- add - section for function add not yet defined that it should be modify in /system/system_time_list.php
    \-- user_list
        \-- lst - section for function lst not yet defined that it should be set and get in /user/user_list.php
        \-- load_sql_by_ids - section for function load_sql_by_ids is expected to be load sql in /user/user_list.php
        \-- load_sql_by_code_id - section for function load_sql_by_code_id is expected to be load sql in /user/user_list.php
        \-- load_sql_by_profile_and_higher - section for function load_sql_by_profile_and_higher is expected to be load sql in /user/user_list.php
        \-- load_sql_count_changes - section for function load_sql_count_changes is expected to be load sql in /user/user_list.php
        \-- user - section for function user not yet defined that it should be set and get in /user/user_list.php
        \-- load_active - section for function load_active is expected to be load in /user/user_list.php
        \-- name_lst - section for function name_lst not yet defined that it should be set and get in /user/user_list.php
        \-- id_lst - section for function id_lst not yet defined that it should be set and get in /user/user_list.php
        \-- names - section for function names is expected to be info in /user/user_list.php
        \-- emails - section for function emails not yet defined that it should be set and get in /user/user_list.php
        \-- id - section for function id not yet defined that it should be set and get in /user/user_list.php
        \-- load_dummy - section for function load_dummy is expected to be load in /user/user_list.php
        \-- count - section for function count not yet defined that it should be info in /user/user_list.php
        \-- is_empty - section for function is_empty not yet defined that it should be info in /user/user_list.php
        \-- add - section for function add not yet defined that it should be modify in /user/user_list.php
        \-- order error - order of section set and get has difference at id should be after name_lst of lst,set_user,user,load_active,name_lst,id_lst,names,emails,set_hash,id,load_dummy does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,key_list,name_lst,order of section load has difference at load_by_ids should be after load_sql_by_ids of load_sql_by_ids,load_sql_by_code_id,load_sql_by_profile_and_higher,load_sql_count_changes,load_by_ids,load_by_code_id,load_by_profile_and_higher does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,1,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,load_by_group_id_sql,load_by_phr,load_sql_by_phr,load_sql_by_phr_single,3,load_sql_init,load_names_sql_by_ids,load_sql_by_ids,del,load_cfg,load_frontend_cfg,load_usr_cfg,load,load_system_views,load_by_key,load_sql_by_id_str,load_type_lists,load_system_users,load_by_id,load_by_name,load_sql_by_user,load_by_user_sql,load_sql_old,load_by_field_row,load_sql_by_field_row,load_by_user,load_obj_last,load_obj_field_last,load_by_obj_fld,load_by_fld_of_wrd,load_by_fld_of_vrb,load_by_fld_of_trp,load_by_fld_of_val,load_by_fld_of_frm,load_by_fld_of_src,load_by_fld_of_ui,load_by_fld_of_cmp,load_by_keys,load_sql_by_names,load_sql_by_source,load_dummy,load_sql_names,load_sbx_names,load_user_changes,id_field,order of section load has difference at load_by_ids should be after load_sql_by_ids of load_sql_by_ids,load_sql_by_code_id,load_sql_by_profile_and_higher,load_sql_count_changes,load_by_ids,load_by_code_id,load_by_profile_and_higher does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,1,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,load_by_group_id_sql,load_by_phr,load_sql_by_phr,load_sql_by_phr_single,3,load_sql_init,load_names_sql_by_ids,load_sql_by_ids,del,load_cfg,load_frontend_cfg,load_usr_cfg,load,load_system_views,load_by_key,load_sql_by_id_str,load_type_lists,load_system_users,load_by_id,load_by_name,load_sql_by_user,load_by_user_sql,load_sql_old,load_by_field_row,load_sql_by_field_row,load_by_user,load_obj_last,load_obj_field_last,load_by_obj_fld,load_by_fld_of_wrd,load_by_fld_of_vrb,load_by_fld_of_trp,load_by_fld_of_val,load_by_fld_of_frm,load_by_fld_of_src,load_by_fld_of_ui,load_by_fld_of_cmp,load_by_keys,load_sql_by_names,load_sql_by_source,load_dummy,load_sql_names,load_sbx_names,load_user_changes,id_field,order of section load has difference at load_by_ids should be after load_sql_by_ids of load_sql_by_ids,load_sql_by_code_id,load_sql_by_profile_and_higher,load_sql_count_changes,load_by_ids,load_by_code_id,load_by_profile_and_higher does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,1,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,load_by_group_id_sql,load_by_phr,load_sql_by_phr,load_sql_by_phr_single,3,load_sql_init,load_names_sql_by_ids,load_sql_by_ids,del,load_cfg,load_frontend_cfg,load_usr_cfg,load,load_system_views,load_by_key,load_sql_by_id_str,load_type_lists,load_system_users,load_by_id,load_by_name,load_sql_by_user,load_by_user_sql,load_sql_old,load_by_field_row,load_sql_by_field_row,load_by_user,load_obj_last,load_obj_field_last,load_by_obj_fld,load_by_fld_of_wrd,load_by_fld_of_vrb,load_by_fld_of_trp,load_by_fld_of_val,load_by_fld_of_frm,load_by_fld_of_src,load_by_fld_of_ui,load_by_fld_of_cmp,load_by_keys,load_sql_by_names,load_sql_by_source,load_dummy,load_sql_names,load_sbx_names,load_user_changes,id_field,order of section load has difference at load_by_ids should be after load_sql_by_ids of load_sql_by_ids,load_sql_by_code_id,load_sql_by_profile_and_higher,load_sql_count_changes,load_by_ids,load_by_code_id,load_by_profile_and_higher does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,1,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,load_by_group_id_sql,load_by_phr,load_sql_by_phr,load_sql_by_phr_single,3,load_sql_init,load_names_sql_by_ids,load_sql_by_ids,del,load_cfg,load_frontend_cfg,load_usr_cfg,load,load_system_views,load_by_key,load_sql_by_id_str,load_type_lists,load_system_users,load_by_id,load_by_name,load_sql_by_user,load_by_user_sql,load_sql_old,load_by_field_row,load_sql_by_field_row,load_by_user,load_obj_last,load_obj_field_last,load_by_obj_fld,load_by_fld_of_wrd,load_by_fld_of_vrb,load_by_fld_of_trp,load_by_fld_of_val,load_by_fld_of_frm,load_by_fld_of_src,load_by_fld_of_ui,load_by_fld_of_cmp,load_by_keys,load_sql_by_names,load_sql_by_source,load_dummy,load_sql_names,load_sbx_names,load_user_changes,id_field,order of section load has difference at load_by_ids should be after load_sql_by_ids of load_sql_by_ids,load_sql_by_code_id,load_sql_by_profile_and_higher,load_sql_count_changes,load_by_ids,load_by_code_id,load_by_profile_and_higher does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,1,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,load_by_group_id_sql,load_by_phr,load_sql_by_phr,load_sql_by_phr_single,3,load_sql_init,load_names_sql_by_ids,load_sql_by_ids,del,load_cfg,load_frontend_cfg,load_usr_cfg,load,load_system_views,load_by_key,load_sql_by_id_str,load_type_lists,load_system_users,load_by_id,load_by_name,load_sql_by_user,load_by_user_sql,load_sql_old,load_by_field_row,load_sql_by_field_row,load_by_user,load_obj_last,load_obj_field_last,load_by_obj_fld,load_by_fld_of_wrd,load_by_fld_of_vrb,load_by_fld_of_trp,load_by_fld_of_val,load_by_fld_of_frm,load_by_fld_of_src,load_by_fld_of_ui,load_by_fld_of_cmp,load_by_keys,load_sql_by_names,load_sql_by_source,load_dummy,load_sql_names,load_sbx_names,load_user_changes,id_field,order of section load has difference at load_by_ids should be after load_sql_by_ids of load_sql_by_ids,load_sql_by_code_id,load_sql_by_profile_and_higher,load_sql_count_changes,load_by_ids,load_by_code_id,load_by_profile_and_higher does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,1,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,load_by_group_id_sql,load_by_phr,load_sql_by_phr,load_sql_by_phr_single,3,load_sql_init,load_names_sql_by_ids,load_sql_by_ids,del,load_cfg,load_frontend_cfg,load_usr_cfg,load,load_system_views,load_by_key,load_sql_by_id_str,load_type_lists,load_system_users,load_by_id,load_by_name,load_sql_by_user,load_by_user_sql,load_sql_old,load_by_field_row,load_sql_by_field_row,load_by_user,load_obj_last,load_obj_field_last,load_by_obj_fld,load_by_fld_of_wrd,load_by_fld_of_vrb,load_by_fld_of_trp,load_by_fld_of_val,load_by_fld_of_frm,load_by_fld_of_src,load_by_fld_of_ui,load_by_fld_of_cmp,load_by_keys,load_sql_by_names,load_sql_by_source,load_dummy,load_sql_names,load_sbx_names,load_user_changes,id_field,order of section load has difference at load_by_ids should be after load_sql_by_ids of load_sql_by_ids,load_sql_by_code_id,load_sql_by_profile_and_higher,load_sql_count_changes,load_by_ids,load_by_code_id,load_by_profile_and_higher does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,1,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,load_by_group_id_sql,load_by_phr,load_sql_by_phr,load_sql_by_phr_single,3,load_sql_init,load_names_sql_by_ids,load_sql_by_ids,del,load_cfg,load_frontend_cfg,load_usr_cfg,load,load_system_views,load_by_key,load_sql_by_id_str,load_type_lists,load_system_users,load_by_id,load_by_name,load_sql_by_user,load_by_user_sql,load_sql_old,load_by_field_row,load_sql_by_field_row,load_by_user,load_obj_last,load_obj_field_last,load_by_obj_fld,load_by_fld_of_wrd,load_by_fld_of_vrb,load_by_fld_of_trp,load_by_fld_of_val,load_by_fld_of_frm,load_by_fld_of_src,load_by_fld_of_ui,load_by_fld_of_cmp,load_by_keys,load_sql_by_names,load_sql_by_source,load_dummy,load_sql_names,load_sbx_names,load_user_changes,id_field,order of section set and get has difference at id should be after name_lst of lst,set_user,user,load_active,name_lst,id_lst,names,emails,set_hash,id,load_dummy does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,key_list,name_lst,order of section set and get has difference at id should be after name_lst of lst,set_user,user,load_active,name_lst,id_lst,names,emails,set_hash,id,load_dummy does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,key_list,name_lst,order of section set and get has difference at id should be after name_lst of lst,set_user,user,load_active,name_lst,id_lst,names,emails,set_hash,id,load_dummy does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,key_list,name_lst,order of section set and get has difference at id should be after name_lst of lst,set_user,user,load_active,name_lst,id_lst,names,emails,set_hash,id,load_dummy does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,key_list,name_lst,order of section set and get has difference at id should be after name_lst of lst,set_user,user,load_active,name_lst,id_lst,names,emails,set_hash,id,load_dummy does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,key_list,name_lst,order of section set and get has difference at id should be after name_lst of lst,set_user,user,load_active,name_lst,id_lst,names,emails,set_hash,id,load_dummy does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,key_list,name_lst,order of section set and get has difference at id should be after name_lst of lst,set_user,user,load_active,name_lst,id_lst,names,emails,set_hash,id,load_dummy does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,key_list,name_lst,order of section set and get has difference at id should be after name_lst of lst,set_user,user,load_active,name_lst,id_lst,names,emails,set_hash,id,load_dummy does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,key_list,name_lst,order of section set and get has difference at id should be after name_lst of lst,set_user,user,load_active,name_lst,id_lst,names,emails,set_hash,id,load_dummy does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,key_list,name_lst,order of section set and get has difference at id should be after name_lst of lst,set_user,user,load_active,name_lst,id_lst,names,emails,set_hash,id,load_dummy does not match set_db_type,db_type,set_id_field,set_id_field_dummy,set_id_field_num_dummy,set_id_field_usr_dummy,set_par_list,par_list,par_values,table_id,3,name,id,trm_id,term,6,term_list,get,set_description,description,set_type_id,set_share,set_protection,type_id,exclude,include,is_excluded,is_exclusion_set,set_plural,set_obj,obj,isset,get_by,source_list,reference_list,value_list,formula_list,formula_link_list,set_view_list,view_list,component_list,term_view_list,user_list,ip_range_list,view_relation_types,unique_value,set_from_api,set_user,user,user_id,set_action,action,set_class,set_table,table,set_field,field,set_time,set_time_str,time,start_time,fill_missing_verbs,name_pos_lst,key_list,name_lst
    \-- user_message
        \-- __construct - section for function __construct is expected to be construct and map in /user/user_message.php
        \-- url - section for function url not yet defined that it should be set and get in /user/user_message.php
        \-- checksum - section for function checksum not yet defined that it should be set and get in /user/user_message.php
        \-- db_row_id_lst - section for function db_row_id_lst not yet defined that it should be set and get in /user/user_message.php
        \-- added_depending - section for function added_depending not yet defined that it should be set and get in /user/user_message.php
        \-- api_array - section for function api_array not yet defined that it should be api in /user/user_message.php
        \-- api_json - section for function api_json not yet defined that it should be api in /user/user_message.php
        \-- api_mapper - section for function api_mapper is expected to be construct and map in /user/user_message.php
        \-- add_id - section for function add_id not yet defined that it should be add in /user/user_message.php
        \-- add_info_id - section for function add_info_id not yet defined that it should be add in /user/user_message.php
        \-- add_info_with_vars - section for function add_info_with_vars not yet defined that it should be add in /user/user_message.php
        \-- add_warning_with_vars - section for function add_warning_with_vars not yet defined that it should be add in /user/user_message.php
        \-- add_err_with_vars - section for function add_err_with_vars not yet defined that it should be add in /user/user_message.php
        \-- add_id_with_vars - section for function add_id_with_vars not yet defined that it should be add in /user/user_message.php
        \-- add_type_message - section for function add_type_message not yet defined that it should be add in /user/user_message.php
        \-- add_message_text - section for function add_message_text not yet defined that it should be add in /user/user_message.php
        \-- add_warning_text - section for function add_warning_text not yet defined that it should be add in /user/user_message.php
        \-- add_info_text - section for function add_info_text not yet defined that it should be add in /user/user_message.php
        \-- add - section for function add not yet defined that it should be add in /user/user_message.php
        \-- add_list_name_id - section for function add_list_name_id not yet defined that it should be add in /user/user_message.php
        \-- set_added_depending - section for function set_added_depending is expected to be set and get in /user/user_message.php
        \-- unset_added_depending - section for function unset_added_depending not yet defined that it should be add in /user/user_message.php
        \-- is_ok - section for function is_ok not yet defined that it should be get in /user/user_message.php
        \-- all_info_text - section for function all_info_text not yet defined that it should be get in /user/user_message.php
        \-- all_message_text - section for function all_message_text not yet defined that it should be get in /user/user_message.php
        \-- var_message_text - section for function var_message_text not yet defined that it should be get in /user/user_message.php
        \-- get_message - section for function get_message is expected to be set and get in /user/user_message.php
        \-- get_message_translated - section for function get_message_translated is expected to be set and get in /user/user_message.php
        \-- get_last_message - section for function get_last_message is expected to be set and get in /user/user_message.php
        \-- get_last_message_translated - section for function get_last_message_translated is expected to be set and get in /user/user_message.php
        \-- get_row_id - section for function get_row_id is expected to be set and get in /user/user_message.php
        \-- has_row - section for function has_row not yet defined that it should be get in /user/user_message.php
        \-- order error - order of section api has difference at api_json should be after api_array of api_array,api_json,api_mapper does not match 1,api_json_array,api_json,api_array,save,order of section api has difference at api_json should be after api_array of api_array,api_json,api_mapper does not match 1,api_json_array,api_json,api_array,save,order of section api has difference at api_json should be after api_array of api_array,api_json,api_mapper does not match 1,api_json_array,api_json,api_array,save
    \-- value_base
        \-- row_mapper_sandbox_multi - section for function row_mapper_sandbox_multi not yet defined that it should be construct and map in /value/value_base.php
        \-- id - section for function id not yet defined that it should be set and get in /value/value_base.php
        \-- source - section for function source not yet defined that it should be set and get in /value/value_base.php
        \-- source_id - section for function source_id not yet defined that it should be set and get in /value/value_base.php
        \-- symbol - section for function symbol not yet defined that it should be set and get in /value/value_base.php
        \-- load_sql_multi - section for function load_sql_multi is expected to be load sql in /value/value_base.php
        \-- load_standard_sql - section for function load_standard_sql is expected to be load in /value/value_base.php
        \-- load_objects - section for function load_objects is expected to be load in /value/value_base.php
        \-- load_phrases - section for function load_phrases is expected to be load in /value/value_base.php
        \-- load_source - section for function load_source is expected to be load in /value/value_base.php
        \-- load_grp_by_id - section for function load_grp_by_id is expected to be load in /value/value_base.php
        \-- name - section for function name not yet defined that it should be info in /value/value_base.php
        \-- get_source_id - section for function get_source_id is expected to be set and get in /value/value_base.php
        \-- set_source_id - section for function set_source_id is expected to be set and get in /value/value_base.php
        \-- source_name - section for function source_name not yet defined that it should be info in /value/value_base.php
        \-- phr_lst - section for function phr_lst not yet defined that it should be info in /value/value_base.php
        \-- phr_names - section for function phr_names not yet defined that it should be info in /value/value_base.php
        \-- match_all - section for function match_all not yet defined that it should be select in /value/value_base.php
        \-- check - section for function check not yet defined that it should be check in /value/value_base.php
        \-- is_same_val - section for function is_same_val not yet defined that it should be check in /value/value_base.php
        \-- scale - section for function scale not yet defined that it should be TODO activate in /value/value_base.php
        \-- import_phrase_value - section for function import_phrase_value not yet defined that it should be im- and export in /value/value_base.php
        \-- save_from_api_msg - section for function save_from_api_msg is expected to be save in /value/value_base.php
        \-- figure - section for function figure not yet defined that it should be get functions that return other linked objects in /value/value_base.php
        \-- convert - section for function convert not yet defined that it should be get functions that return other linked objects in /value/value_base.php
        \-- res_lst_depending - section for function res_lst_depending not yet defined that it should be Select functions in /value/value_base.php
        \-- used - section for function used not yet defined that it should be Select functions in /value/value_base.php
        \-- not_used - section for function not_used not yet defined that it should be Select functions in /value/value_base.php
        \-- not_changed_sql - section for function not_changed_sql not yet defined that it should be Select functions in /value/value_base.php
        \-- not_changed - section for function not_changed not yet defined that it should be Select functions in /value/value_base.php
        \-- get_std - section for function get_std is expected to be set and get in /value/value_base.php
        \-- set_std - section for function set_std is expected to be set and get in /value/value_base.php
        \-- is_std - section for function is_std not yet defined that it should be Select functions in /value/value_base.php
        \-- has_usr_cfg - section for function has_usr_cfg not yet defined that it should be Select functions in /value/value_base.php
        \-- log_upd - section for function log_upd is expected to be log in /value/value_base.php
        \-- log_update_parameter - section for function log_update_parameter is expected to be log in /value/value_base.php
        \-- log_del - section for function log_del is expected to be log in /value/value_base.php
        \-- log_add_link - section for function log_add_link is expected to be log in /value/value_base.php
        \-- log_del_link - section for function log_del_link is expected to be log in /value/value_base.php
        \-- add_wrd - section for function add_wrd not yet defined that it should be Select functions in /value/value_base.php
        \-- del_wrd - section for function del_wrd is expected to be del in /value/value_base.php
        \-- save_field_trigger_update - section for function save_field_trigger_update is expected to be save in /value/value_base.php
        \-- save_field_number - section for function save_field_number is expected to be save in /value/value_base.php
        \-- save_field_source - section for function save_field_source is expected to be save in /value/value_base.php
        \-- save_set_log_id - section for function save_set_log_id is expected to be save in /value/value_base.php
        \-- save_fields - section for function save_fields is expected to be save in /value/value_base.php
        \-- save_id_fields - section for function save_id_fields is expected to be save in /value/value_base.php
        \-- save_id_if_updated - section for function save_id_if_updated is expected to be save in /value/value_base.php
        \-- add - section for function add not yet defined that it should be Select functions in /value/value_base.php
        \-- save - section for function save is expected to be save in /value/value_base.php
        \-- order error - order of section construct and map has difference at api_mapper should be after reset of __construct,reset,row_mapper_sandbox_multi,api_mapper,import_mapper does not match __construct,row_mapper_sandbox,api_mapper,reset,row_mapper,import_mapper,code_id,load_dummy,default_id,order of section construct and map has difference at api_mapper should be after reset of __construct,reset,row_mapper_sandbox_multi,api_mapper,import_mapper does not match __construct,row_mapper_sandbox,api_mapper,reset,row_mapper,import_mapper,code_id,load_dummy,default_id,order of section construct and map has difference at api_mapper should be after reset of __construct,reset,row_mapper_sandbox_multi,api_mapper,import_mapper does not match __construct,row_mapper_sandbox,api_mapper,reset,row_mapper,import_mapper,code_id,load_dummy,default_id,order of section construct and map has difference at api_mapper should be after reset of __construct,reset,row_mapper_sandbox_multi,api_mapper,import_mapper does not match __construct,row_mapper_sandbox,api_mapper,reset,row_mapper,import_mapper,code_id,load_dummy,default_id,order of section construct and map has difference at api_mapper should be after reset of __construct,reset,row_mapper_sandbox_multi,api_mapper,import_mapper does not match __construct,row_mapper_sandbox,api_mapper,reset,row_mapper,import_mapper,code_id,load_dummy,default_id
    \-- value_geo
        \-- value - section for function value not yet defined that it should be set and get in /value/value_geo.php
    \-- value_list
        \-- grp_ids - section for function grp_ids not yet defined that it should be set and get in /value/value_list.php
        \-- get_by_names - section for function get_by_names is expected to be set and get in /value/value_list.php
        \-- load_sql_by_phr_lst - section for function load_sql_by_phr_lst is expected to be load sql in /value/value_list.php
        \-- load_sql_by_phr - section for function load_sql_by_phr is expected to be load sql in /value/value_list.php
        \-- load_sql_multi - section for function load_sql_multi is expected to be load sql in /value/value_list.php
        \-- load_sql_by_ids - section for function load_sql_by_ids is expected to be load sql in /value/value_list.php
        \-- load_sql_by_grp_lst - section for function load_sql_by_grp_lst is expected to be load sql in /value/value_list.php
        \-- load_sql_by_phr_single - section for function load_sql_by_phr_single is expected to be load sql in /value/value_list.php
        \-- load_phrases - section for function load_phrases is expected to be load in /value/value_list.php
        \-- add_value_direct - section for function add_value_direct not yet defined that it should be modify in /value/value_list.php
        \-- remove - section for function remove not yet defined that it should be modify in /value/value_list.php
        \-- time_list - section for function time_list not yet defined that it should be data retrieval functions in /value/value_list.php
        \-- phr_lst - section for function phr_lst not yet defined that it should be data retrieval functions in /value/value_list.php
        \-- phr_lst_all - section for function phr_lst_all not yet defined that it should be data retrieval functions in /value/value_list.php
        \-- wrd_lst - section for function wrd_lst not yet defined that it should be data retrieval functions in /value/value_list.php
        \-- source_lst - section for function source_lst not yet defined that it should be data retrieval functions in /value/value_list.php
        \-- numbers - section for function numbers not yet defined that it should be data retrieval functions in /value/value_list.php
        \-- fill_phrase_ids_by_names - section for function fill_phrase_ids_by_names not yet defined that it should be data retrieval functions in /value/value_list.php
        \-- filter_by_time - section for function filter_by_time not yet defined that it should be filter and select functions in /value/value_list.php
        \-- filter_by_phrase_lst - section for function filter_by_phrase_lst not yet defined that it should be filter and select functions in /value/value_list.php
        \-- filter_by_phrase - section for function filter_by_phrase not yet defined that it should be filter and select functions in /value/value_list.php
        \-- get_from_lst - section for function get_from_lst is expected to be set and get in /value/value_list.php
        \-- get_by_grp - section for function get_by_grp is expected to be set and get in /value/value_list.php
        \-- has_values - section for function has_values not yet defined that it should be filter and select functions in /value/value_list.php
        \-- diff - section for function diff not yet defined that it should be filter and select functions in /value/value_list.php
        \-- phrase_groups - section for function phrase_groups not yet defined that it should be convert in /value/value_list.php
        \-- common_phrases - section for function common_phrases not yet defined that it should be convert in /value/value_list.php
        \-- check_all - section for function check_all not yet defined that it should be check / database consistency functions in /value/value_list.php
        \-- del - section for function del is expected to be del in /value/value_list.php
        \-- order error - order of section load has difference at load_by_ids should be after load_by_phr, load_sql_by_phr should be after load_by_id, load_sql_by_ids should be after load_by_id of load_by_phr_lst,load_by_phr,load_by_ids,load_by_id,load_sql_by_phr_lst,load_sql_by_phr,load_sql_multi,load_sql_by_ids does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,1,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,load_by_group_id_sql,load_by_phr,load_sql_by_phr,load_sql_by_phr_single,3,load_sql_init,load_names_sql_by_ids,load_sql_by_ids,del,load_cfg,load_frontend_cfg,load_usr_cfg,load,load_system_views,load_by_key,load_sql_by_id_str,load_type_lists,load_system_users,load_by_id,load_by_name,load_sql_by_user,load_by_user_sql,load_sql_old,load_by_field_row,load_sql_by_field_row,load_by_user,load_obj_last,load_obj_field_last,load_by_obj_fld,load_by_fld_of_wrd,load_by_fld_of_vrb,load_by_fld_of_trp,load_by_fld_of_val,load_by_fld_of_frm,load_by_fld_of_src,load_by_fld_of_ui,load_by_fld_of_cmp,load_by_keys,load_sql_by_names,load_sql_by_source,load_dummy,load_sql_names,load_sbx_names,load_user_changes,id_field,order of section load has difference at load_by_ids should be after load_by_phr, load_sql_by_phr should be after load_by_id, load_sql_by_ids should be after load_by_id of load_by_phr_lst,load_by_phr,load_by_ids,load_by_id,load_sql_by_phr_lst,load_sql_by_phr,load_sql_multi,load_sql_by_ids does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,1,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,load_by_group_id_sql,load_by_phr,load_sql_by_phr,load_sql_by_phr_single,3,load_sql_init,load_names_sql_by_ids,load_sql_by_ids,del,load_cfg,load_frontend_cfg,load_usr_cfg,load,load_system_views,load_by_key,load_sql_by_id_str,load_type_lists,load_system_users,load_by_id,load_by_name,load_sql_by_user,load_by_user_sql,load_sql_old,load_by_field_row,load_sql_by_field_row,load_by_user,load_obj_last,load_obj_field_last,load_by_obj_fld,load_by_fld_of_wrd,load_by_fld_of_vrb,load_by_fld_of_trp,load_by_fld_of_val,load_by_fld_of_frm,load_by_fld_of_src,load_by_fld_of_ui,load_by_fld_of_cmp,load_by_keys,load_sql_by_names,load_sql_by_source,load_dummy,load_sql_names,load_sbx_names,load_user_changes,id_field,order of section load has difference at load_by_ids should be after load_by_phr, load_sql_by_phr should be after load_by_id, load_sql_by_ids should be after load_by_id of load_by_phr_lst,load_by_phr,load_by_ids,load_by_id,load_sql_by_phr_lst,load_sql_by_phr,load_sql_multi,load_sql_by_ids does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,1,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,load_by_group_id_sql,load_by_phr,load_sql_by_phr,load_sql_by_phr_single,3,load_sql_init,load_names_sql_by_ids,load_sql_by_ids,del,load_cfg,load_frontend_cfg,load_usr_cfg,load,load_system_views,load_by_key,load_sql_by_id_str,load_type_lists,load_system_users,load_by_id,load_by_name,load_sql_by_user,load_by_user_sql,load_sql_old,load_by_field_row,load_sql_by_field_row,load_by_user,load_obj_last,load_obj_field_last,load_by_obj_fld,load_by_fld_of_wrd,load_by_fld_of_vrb,load_by_fld_of_trp,load_by_fld_of_val,load_by_fld_of_frm,load_by_fld_of_src,load_by_fld_of_ui,load_by_fld_of_cmp,load_by_keys,load_sql_by_names,load_sql_by_source,load_dummy,load_sql_names,load_sbx_names,load_user_changes,id_field,order of section load has difference at load_by_ids should be after load_by_phr, load_sql_by_phr should be after load_by_id, load_sql_by_ids should be after load_by_id of load_by_phr_lst,load_by_phr,load_by_ids,load_by_id,load_sql_by_phr_lst,load_sql_by_phr,load_sql_multi,load_sql_by_ids does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,1,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,load_by_group_id_sql,load_by_phr,load_sql_by_phr,load_sql_by_phr_single,3,load_sql_init,load_names_sql_by_ids,load_sql_by_ids,del,load_cfg,load_frontend_cfg,load_usr_cfg,load,load_system_views,load_by_key,load_sql_by_id_str,load_type_lists,load_system_users,load_by_id,load_by_name,load_sql_by_user,load_by_user_sql,load_sql_old,load_by_field_row,load_sql_by_field_row,load_by_user,load_obj_last,load_obj_field_last,load_by_obj_fld,load_by_fld_of_wrd,load_by_fld_of_vrb,load_by_fld_of_trp,load_by_fld_of_val,load_by_fld_of_frm,load_by_fld_of_src,load_by_fld_of_ui,load_by_fld_of_cmp,load_by_keys,load_sql_by_names,load_sql_by_source,load_dummy,load_sql_names,load_sbx_names,load_user_changes,id_field,order of section load has difference at load_by_ids should be after load_by_phr, load_sql_by_phr should be after load_by_id, load_sql_by_ids should be after load_by_id of load_by_phr_lst,load_by_phr,load_by_ids,load_by_id,load_sql_by_phr_lst,load_sql_by_phr,load_sql_multi,load_sql_by_ids does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,1,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,load_by_group_id_sql,load_by_phr,load_sql_by_phr,load_sql_by_phr_single,3,load_sql_init,load_names_sql_by_ids,load_sql_by_ids,del,load_cfg,load_frontend_cfg,load_usr_cfg,load,load_system_views,load_by_key,load_sql_by_id_str,load_type_lists,load_system_users,load_by_id,load_by_name,load_sql_by_user,load_by_user_sql,load_sql_old,load_by_field_row,load_sql_by_field_row,load_by_user,load_obj_last,load_obj_field_last,load_by_obj_fld,load_by_fld_of_wrd,load_by_fld_of_vrb,load_by_fld_of_trp,load_by_fld_of_val,load_by_fld_of_frm,load_by_fld_of_src,load_by_fld_of_ui,load_by_fld_of_cmp,load_by_keys,load_sql_by_names,load_sql_by_source,load_dummy,load_sql_names,load_sbx_names,load_user_changes,id_field,order of section load has difference at load_by_ids should be after load_by_phr, load_sql_by_phr should be after load_by_id, load_sql_by_ids should be after load_by_id of load_by_phr_lst,load_by_phr,load_by_ids,load_by_id,load_sql_by_phr_lst,load_sql_by_phr,load_sql_multi,load_sql_by_ids does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,1,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,load_by_group_id_sql,load_by_phr,load_sql_by_phr,load_sql_by_phr_single,3,load_sql_init,load_names_sql_by_ids,load_sql_by_ids,del,load_cfg,load_frontend_cfg,load_usr_cfg,load,load_system_views,load_by_key,load_sql_by_id_str,load_type_lists,load_system_users,load_by_id,load_by_name,load_sql_by_user,load_by_user_sql,load_sql_old,load_by_field_row,load_sql_by_field_row,load_by_user,load_obj_last,load_obj_field_last,load_by_obj_fld,load_by_fld_of_wrd,load_by_fld_of_vrb,load_by_fld_of_trp,load_by_fld_of_val,load_by_fld_of_frm,load_by_fld_of_src,load_by_fld_of_ui,load_by_fld_of_cmp,load_by_keys,load_sql_by_names,load_sql_by_source,load_dummy,load_sql_names,load_sbx_names,load_user_changes,id_field,order of section load has difference at load_by_ids should be after load_by_phr, load_sql_by_phr should be after load_by_id, load_sql_by_ids should be after load_by_id of load_by_phr_lst,load_by_phr,load_by_ids,load_by_id,load_sql_by_phr_lst,load_sql_by_phr,load_sql_multi,load_sql_by_ids does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,1,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,load_by_group_id_sql,load_by_phr,load_sql_by_phr,load_sql_by_phr_single,3,load_sql_init,load_names_sql_by_ids,load_sql_by_ids,del,load_cfg,load_frontend_cfg,load_usr_cfg,load,load_system_views,load_by_key,load_sql_by_id_str,load_type_lists,load_system_users,load_by_id,load_by_name,load_sql_by_user,load_by_user_sql,load_sql_old,load_by_field_row,load_sql_by_field_row,load_by_user,load_obj_last,load_obj_field_last,load_by_obj_fld,load_by_fld_of_wrd,load_by_fld_of_vrb,load_by_fld_of_trp,load_by_fld_of_val,load_by_fld_of_frm,load_by_fld_of_src,load_by_fld_of_ui,load_by_fld_of_cmp,load_by_keys,load_sql_by_names,load_sql_by_source,load_dummy,load_sql_names,load_sbx_names,load_user_changes,id_field,order of section load has difference at load_by_ids should be after load_by_phr, load_sql_by_phr should be after load_by_id, load_sql_by_ids should be after load_by_id of load_by_phr_lst,load_by_phr,load_by_ids,load_by_id,load_sql_by_phr_lst,load_sql_by_phr,load_sql_multi,load_sql_by_ids does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,1,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,load_by_group_id_sql,load_by_phr,load_sql_by_phr,load_sql_by_phr_single,3,load_sql_init,load_names_sql_by_ids,load_sql_by_ids,del,load_cfg,load_frontend_cfg,load_usr_cfg,load,load_system_views,load_by_key,load_sql_by_id_str,load_type_lists,load_system_users,load_by_id,load_by_name,load_sql_by_user,load_by_user_sql,load_sql_old,load_by_field_row,load_sql_by_field_row,load_by_user,load_obj_last,load_obj_field_last,load_by_obj_fld,load_by_fld_of_wrd,load_by_fld_of_vrb,load_by_fld_of_trp,load_by_fld_of_val,load_by_fld_of_frm,load_by_fld_of_src,load_by_fld_of_ui,load_by_fld_of_cmp,load_by_keys,load_sql_by_names,load_sql_by_source,load_dummy,load_sql_names,load_sbx_names,load_user_changes,id_field
    \-- value_text
        \-- value - section for function value not yet defined that it should be set and get in /value/value_text.php
    \-- value_time
        \-- value - section for function value not yet defined that it should be set and get in /value/value_time.php
    \-- value_time_series
        \-- row_mapper_sandbox - section for function row_mapper_sandbox is expected to be construct and map in /value/value_time_series.php
        \-- load_standard_sql - section for function load_standard_sql is expected to be load in /value/value_time_series.php
        \-- load_sql - section for function load_sql is expected to be load sql in /value/value_time_series.php
        \-- load_standard - section for function load_standard is expected to be load in /value/value_time_series.php
        \-- load_sql_multi - section for function load_sql_multi is expected to be load sql in /value/value_time_series.php
        \-- load_sql_by_grp - section for function load_sql_by_grp is expected to be load sql in /value/value_time_series.php
        \-- load_by_id - section for function load_by_id is expected to be load in /value/value_time_series.php
        \-- load_by_grp - section for function load_by_grp is expected to be load in /value/value_time_series.php
        \-- add - section for function add not yet defined that it should be database load functions that reads the object from the database in /value/value_time_series.php
        \-- id_field - section for function id_field not yet defined that it should be info in /value/value_time_series.php
        \-- save - section for function save is expected to be save in /value/value_time_series.php
    \-- value_ts_data
        \-- sql_table - section for function sql_table not yet defined that it should be sql create in /value/value_ts_data.php
        \-- sql_index - section for function sql_index not yet defined that it should be sql create in /value/value_ts_data.php
        \-- sql_foreign_key - section for function sql_foreign_key not yet defined that it should be sql create in /value/value_ts_data.php
    \-- verb_list
        \-- user - section for function user not yet defined that it should be set and get in /verb/verb_list.php
        \-- load_by_linked_phrases_sql - section for function load_by_linked_phrases_sql is expected to be load in /verb/verb_list.php
        \-- load_by_linked_phrases - section for function load_by_linked_phrases is expected to be load in /verb/verb_list.php
        \-- load_dummy - section for function load_dummy is expected to be load in /verb/verb_list.php
        \-- term_list - section for function term_list not yet defined that it should be cast in /verb/verb_list.php
        \-- term_lst_of_names - section for function term_lst_of_names not yet defined that it should be info in /verb/verb_list.php
        \-- add_verb - section for function add_verb not yet defined that it should be modify in /verb/verb_list.php
        \-- calc_usage - section for function calc_usage not yet defined that it should be modify in /verb/verb_list.php
        \-- db_id_list - section for function db_id_list not yet defined that it should be extract in /verb/verb_list.php
        \-- ids - section for function ids not yet defined that it should be extract in /verb/verb_list.php
        \-- get_verb - section for function get_verb is expected to be set and get in /verb/verb_list.php
        \-- get_verb_by_id - section for function get_verb_by_id is expected to be set and get in /verb/verb_list.php
        \-- selector_list - section for function selector_list not yet defined that it should be extract in /verb/verb_list.php
        \-- order error - order of section modify has difference at add_by_name should be after add_verb of add_verb,add_by_name,calc_usage does not match add_by_name,1,add,remove,add_word,add_verb,add_triple,add_triple_without_ready_check,add_phrase,add_source,add_reference,add_formula,add_formula_without_ready_check,add_term,add_view,add_component,add_term_view,add_user,add_ip_range,add_value,add_message,get_component_by_name,get_value_by_names,expected_word_import_time,expected_triple_import_time,expected_value_import_time,expected_total_import_time,count,save,diff_msg,fill,query_extension,dsp_last,add_ref,use_type_id,add_wrd_lst,add_trp_lst,add_id,add_name,2,del,merge_by_name,del_list,filter_by_ids,filter_valid,get_diff,diff,not_in,diff_by_ids,keep_only_specific,has_time,has_measure,has_scaling,has_percent,time_lst_old,time_word_list,time_list,get_by_type,get_names_by_type,time_useful,assume_time,measure_lst,scaling_lst,ex_time,ex_measure,ex_scaling,name_sort,sort_by_id,sort_rev_by_id,max_time,get_grp_id,common,concat_unique,add_link,add_link_by_key,add_obj,merge,order of section modify has difference at add_by_name should be after add_verb of add_verb,add_by_name,calc_usage does not match add_by_name,1,add,remove,add_word,add_verb,add_triple,add_triple_without_ready_check,add_phrase,add_source,add_reference,add_formula,add_formula_without_ready_check,add_term,add_view,add_component,add_term_view,add_user,add_ip_range,add_value,add_message,get_component_by_name,get_value_by_names,expected_word_import_time,expected_triple_import_time,expected_value_import_time,expected_total_import_time,count,save,diff_msg,fill,query_extension,dsp_last,add_ref,use_type_id,add_wrd_lst,add_trp_lst,add_id,add_name,2,del,merge_by_name,del_list,filter_by_ids,filter_valid,get_diff,diff,not_in,diff_by_ids,keep_only_specific,has_time,has_measure,has_scaling,has_percent,time_lst_old,time_word_list,time_list,get_by_type,get_names_by_type,time_useful,assume_time,measure_lst,scaling_lst,ex_time,ex_measure,ex_scaling,name_sort,sort_by_id,sort_rev_by_id,max_time,get_grp_id,common,concat_unique,add_link,add_link_by_key,add_obj,merge,order of section modify has difference at add_by_name should be after add_verb of add_verb,add_by_name,calc_usage does not match add_by_name,1,add,remove,add_word,add_verb,add_triple,add_triple_without_ready_check,add_phrase,add_source,add_reference,add_formula,add_formula_without_ready_check,add_term,add_view,add_component,add_term_view,add_user,add_ip_range,add_value,add_message,get_component_by_name,get_value_by_names,expected_word_import_time,expected_triple_import_time,expected_value_import_time,expected_total_import_time,count,save,diff_msg,fill,query_extension,dsp_last,add_ref,use_type_id,add_wrd_lst,add_trp_lst,add_id,add_name,2,del,merge_by_name,del_list,filter_by_ids,filter_valid,get_diff,diff,not_in,diff_by_ids,keep_only_specific,has_time,has_measure,has_scaling,has_percent,time_lst_old,time_word_list,time_list,get_by_type,get_names_by_type,time_useful,assume_time,measure_lst,scaling_lst,ex_time,ex_measure,ex_scaling,name_sort,sort_by_id,sort_rev_by_id,max_time,get_grp_id,common,concat_unique,add_link,add_link_by_key,add_obj,merge
    \-- view_list
        \-- user - section for function user not yet defined that it should be set and get in /view/view_list.php
        \-- load_sql_names - section for function load_sql_names is expected to be load sql in /view/view_list.php
        \-- load_sql_by_names - section for function load_sql_by_names is expected to be load sql in /view/view_list.php
        \-- load_sql - section for function load_sql is expected to be load sql in /view/view_list.php
        \-- load_sql_by_component_id - section for function load_sql_by_component_id is expected to be load sql in /view/view_list.php
        \-- save - section for function save is expected to be save in /view/view_list.php
        \-- order error - order of section load has difference at load_sql_by_names should be after load_sql_names, load_sql should be after load_sql_names, load_names should be after load_sql_names of load_sql_names,load_sql_by_names,load_sql,load_sql_by_component_id,load_names,load_by_component_id does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,1,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,load_by_group_id_sql,load_by_phr,load_sql_by_phr,load_sql_by_phr_single,3,load_sql_init,load_names_sql_by_ids,load_sql_by_ids,del,load_cfg,load_frontend_cfg,load_usr_cfg,load,load_system_views,load_by_key,load_sql_by_id_str,load_type_lists,load_system_users,load_by_id,load_by_name,load_sql_by_user,load_by_user_sql,load_sql_old,load_by_field_row,load_sql_by_field_row,load_by_user,load_obj_last,load_obj_field_last,load_by_obj_fld,load_by_fld_of_wrd,load_by_fld_of_vrb,load_by_fld_of_trp,load_by_fld_of_val,load_by_fld_of_frm,load_by_fld_of_src,load_by_fld_of_ui,load_by_fld_of_cmp,load_by_keys,load_sql_by_names,load_sql_by_source,load_dummy,load_sql_names,load_sbx_names,load_user_changes,id_field,order of section load has difference at load_sql_by_names should be after load_sql_names, load_sql should be after load_sql_names, load_names should be after load_sql_names of load_sql_names,load_sql_by_names,load_sql,load_sql_by_component_id,load_names,load_by_component_id does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,1,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,load_by_group_id_sql,load_by_phr,load_sql_by_phr,load_sql_by_phr_single,3,load_sql_init,load_names_sql_by_ids,load_sql_by_ids,del,load_cfg,load_frontend_cfg,load_usr_cfg,load,load_system_views,load_by_key,load_sql_by_id_str,load_type_lists,load_system_users,load_by_id,load_by_name,load_sql_by_user,load_by_user_sql,load_sql_old,load_by_field_row,load_sql_by_field_row,load_by_user,load_obj_last,load_obj_field_last,load_by_obj_fld,load_by_fld_of_wrd,load_by_fld_of_vrb,load_by_fld_of_trp,load_by_fld_of_val,load_by_fld_of_frm,load_by_fld_of_src,load_by_fld_of_ui,load_by_fld_of_cmp,load_by_keys,load_sql_by_names,load_sql_by_source,load_dummy,load_sql_names,load_sbx_names,load_user_changes,id_field,order of section load has difference at load_sql_by_names should be after load_sql_names, load_sql should be after load_sql_names, load_names should be after load_sql_names of load_sql_names,load_sql_by_names,load_sql,load_sql_by_component_id,load_names,load_by_component_id does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,1,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,load_by_group_id_sql,load_by_phr,load_sql_by_phr,load_sql_by_phr_single,3,load_sql_init,load_names_sql_by_ids,load_sql_by_ids,del,load_cfg,load_frontend_cfg,load_usr_cfg,load,load_system_views,load_by_key,load_sql_by_id_str,load_type_lists,load_system_users,load_by_id,load_by_name,load_sql_by_user,load_by_user_sql,load_sql_old,load_by_field_row,load_sql_by_field_row,load_by_user,load_obj_last,load_obj_field_last,load_by_obj_fld,load_by_fld_of_wrd,load_by_fld_of_vrb,load_by_fld_of_trp,load_by_fld_of_val,load_by_fld_of_frm,load_by_fld_of_src,load_by_fld_of_ui,load_by_fld_of_cmp,load_by_keys,load_sql_by_names,load_sql_by_source,load_dummy,load_sql_names,load_sbx_names,load_user_changes,id_field,order of section load has difference at load_sql_by_names should be after load_sql_names, load_sql should be after load_sql_names, load_names should be after load_sql_names of load_sql_names,load_sql_by_names,load_sql,load_sql_by_component_id,load_names,load_by_component_id does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,1,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,load_by_group_id_sql,load_by_phr,load_sql_by_phr,load_sql_by_phr_single,3,load_sql_init,load_names_sql_by_ids,load_sql_by_ids,del,load_cfg,load_frontend_cfg,load_usr_cfg,load,load_system_views,load_by_key,load_sql_by_id_str,load_type_lists,load_system_users,load_by_id,load_by_name,load_sql_by_user,load_by_user_sql,load_sql_old,load_by_field_row,load_sql_by_field_row,load_by_user,load_obj_last,load_obj_field_last,load_by_obj_fld,load_by_fld_of_wrd,load_by_fld_of_vrb,load_by_fld_of_trp,load_by_fld_of_val,load_by_fld_of_frm,load_by_fld_of_src,load_by_fld_of_ui,load_by_fld_of_cmp,load_by_keys,load_sql_by_names,load_sql_by_source,load_dummy,load_sql_names,load_sbx_names,load_user_changes,id_field,order of section load has difference at load_sql_by_names should be after load_sql_names, load_sql should be after load_sql_names, load_names should be after load_sql_names of load_sql_names,load_sql_by_names,load_sql,load_sql_by_component_id,load_names,load_by_component_id does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,1,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,load_by_group_id_sql,load_by_phr,load_sql_by_phr,load_sql_by_phr_single,3,load_sql_init,load_names_sql_by_ids,load_sql_by_ids,del,load_cfg,load_frontend_cfg,load_usr_cfg,load,load_system_views,load_by_key,load_sql_by_id_str,load_type_lists,load_system_users,load_by_id,load_by_name,load_sql_by_user,load_by_user_sql,load_sql_old,load_by_field_row,load_sql_by_field_row,load_by_user,load_obj_last,load_obj_field_last,load_by_obj_fld,load_by_fld_of_wrd,load_by_fld_of_vrb,load_by_fld_of_trp,load_by_fld_of_val,load_by_fld_of_frm,load_by_fld_of_src,load_by_fld_of_ui,load_by_fld_of_cmp,load_by_keys,load_sql_by_names,load_sql_by_source,load_dummy,load_sql_names,load_sbx_names,load_user_changes,id_field,order of section load has difference at load_sql_by_names should be after load_sql_names, load_sql should be after load_sql_names, load_names should be after load_sql_names of load_sql_names,load_sql_by_names,load_sql,load_sql_by_component_id,load_names,load_by_component_id does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,1,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,load_by_group_id_sql,load_by_phr,load_sql_by_phr,load_sql_by_phr_single,3,load_sql_init,load_names_sql_by_ids,load_sql_by_ids,del,load_cfg,load_frontend_cfg,load_usr_cfg,load,load_system_views,load_by_key,load_sql_by_id_str,load_type_lists,load_system_users,load_by_id,load_by_name,load_sql_by_user,load_by_user_sql,load_sql_old,load_by_field_row,load_sql_by_field_row,load_by_user,load_obj_last,load_obj_field_last,load_by_obj_fld,load_by_fld_of_wrd,load_by_fld_of_vrb,load_by_fld_of_trp,load_by_fld_of_val,load_by_fld_of_frm,load_by_fld_of_src,load_by_fld_of_ui,load_by_fld_of_cmp,load_by_keys,load_sql_by_names,load_sql_by_source,load_dummy,load_sql_names,load_sbx_names,load_user_changes,id_field
    \-- view_relation_list
        \-- del - section for function del is expected to be del in /view/view_relation_list.php
    \-- view_sys_list
        \-- __construct - section for function __construct is expected to be construct and map in /view/view_sys_list.php
        \-- user - section for function user not yet defined that it should be set and get in /view/view_sys_list.php
        \-- load_sql_list - section for function load_sql_list is expected to be load sql in /view/view_sys_list.php
        \-- load - section for function load not yet defined that it should be load in /view/view_sys_list.php
        \-- default_id - section for function default_id not yet defined that it should be load in /view/view_sys_list.php
    \-- triple_list
        \-- load_sql_names - section for function load_sql_names is expected to be load sql in /word/triple_list.php
        \-- load_sql_by_names - section for function load_sql_by_names is expected to be load sql in /word/triple_list.php
        \-- load_sql_by_ids - section for function load_sql_by_ids is expected to be load sql in /word/triple_list.php
        \-- load_sql_by_phr - section for function load_sql_by_phr is expected to be load sql in /word/triple_list.php
        \-- load_sql_by_phr_lst - section for function load_sql_by_phr_lst is expected to be load sql in /word/triple_list.php
        \-- load_sql - section for function load_sql is expected to be load sql in /word/triple_list.php
        \-- has_values - section for function has_values not yet defined that it should be information in /word/triple_list.php
        \-- del - section for function del is expected to be del in /word/triple_list.php
        \-- diff_msg - section for function diff_msg is expected to be info in /word/triple_list.php
        \-- phrase_list - section for function phrase_list not yet defined that it should be convert in /word/triple_list.php
        \-- phrase_lst_of_names - section for function phrase_lst_of_names not yet defined that it should be convert in /word/triple_list.php
        \-- triples_to_add_to_db - section for function triples_to_add_to_db not yet defined that it should be convert in /word/triple_list.php
        \-- phrase_parts - section for function phrase_parts not yet defined that it should be parts in /word/triple_list.php
        \-- missing_ids - section for function missing_ids not yet defined that it should be parts in /word/triple_list.php
        \-- fill_by_name - section for function fill_by_name not yet defined that it should be save in /word/triple_list.php
        \-- fill_missing_verbs - section for function fill_missing_verbs not yet defined that it should be save in /word/triple_list.php
        \-- get_ready - section for function get_ready is expected to be set and get in /word/triple_list.php
        \-- order error - order of section sql has difference at load_sql should be after load_sql_by_phr_lst of load_sql_names,load_sql_by_names,load_sql_by_ids,load_sql_by_phr,load_sql_by_phr_lst,load_sql does not match sql_delete,load_sql_like,load_sql_by_names,load_sql_by_ids,load_sql,load_names_sql_by_ids,load_sql_by_phr_lst,load_names_by_ids,load_names,order of section sql has difference at load_sql should be after load_sql_by_phr_lst of load_sql_names,load_sql_by_names,load_sql_by_ids,load_sql_by_phr,load_sql_by_phr_lst,load_sql does not match sql_delete,load_sql_like,load_sql_by_names,load_sql_by_ids,load_sql,load_names_sql_by_ids,load_sql_by_phr_lst,load_names_by_ids,load_names,order of section sql has difference at load_sql should be after load_sql_by_phr_lst of load_sql_names,load_sql_by_names,load_sql_by_ids,load_sql_by_phr,load_sql_by_phr_lst,load_sql does not match sql_delete,load_sql_like,load_sql_by_names,load_sql_by_ids,load_sql,load_names_sql_by_ids,load_sql_by_phr_lst,load_names_by_ids,load_names,order of section sql has difference at load_sql should be after load_sql_by_phr_lst of load_sql_names,load_sql_by_names,load_sql_by_ids,load_sql_by_phr,load_sql_by_phr_lst,load_sql does not match sql_delete,load_sql_like,load_sql_by_names,load_sql_by_ids,load_sql,load_names_sql_by_ids,load_sql_by_phr_lst,load_names_by_ids,load_names,order of section sql has difference at load_sql should be after load_sql_by_phr_lst of load_sql_names,load_sql_by_names,load_sql_by_ids,load_sql_by_phr,load_sql_by_phr_lst,load_sql does not match sql_delete,load_sql_like,load_sql_by_names,load_sql_by_ids,load_sql,load_names_sql_by_ids,load_sql_by_phr_lst,load_names_by_ids,load_names,order of section sql has difference at load_sql should be after load_sql_by_phr_lst of load_sql_names,load_sql_by_names,load_sql_by_ids,load_sql_by_phr,load_sql_by_phr_lst,load_sql does not match sql_delete,load_sql_like,load_sql_by_names,load_sql_by_ids,load_sql,load_names_sql_by_ids,load_sql_by_phr_lst,load_names_by_ids,load_names
    \-- word_list
        \-- load_user_changes_sql - section for function load_user_changes_sql is expected to be load in /word/word_list.php
        \-- load - section for function load not yet defined that it should be load sql in /word/word_list.php
        \-- load_linked_words - section for function load_linked_words is expected to be load in /word/word_list.php
        \-- foaf_parents - section for function foaf_parents not yet defined that it should be im- and export in /word/word_list.php
        \-- parents - section for function parents not yet defined that it should be im- and export in /word/word_list.php
        \-- children - section for function children not yet defined that it should be im- and export in /word/word_list.php
        \-- direct_children - section for function direct_children not yet defined that it should be im- and export in /word/word_list.php
        \-- is - section for function is not yet defined that it should be im- and export in /word/word_list.php
        \-- are - section for function are not yet defined that it should be im- and export in /word/word_list.php
        \-- contains - section for function contains not yet defined that it should be im- and export in /word/word_list.php
        \-- are_and_contains - section for function are_and_contains not yet defined that it should be im- and export in /word/word_list.php
        \-- differentiators - section for function differentiators not yet defined that it should be im- and export in /word/word_list.php
        \-- differentiators_all - section for function differentiators_all not yet defined that it should be im- and export in /word/word_list.php
        \-- differentiators_filtered - section for function differentiators_filtered not yet defined that it should be im- and export in /word/word_list.php
        \-- keep_only_specific - section for function keep_only_specific not yet defined that it should be im- and export in /word/word_list.php
        \-- add_id - section for function add_id not yet defined that it should be modify in /word/word_list.php
        \-- add_name - section for function add_name not yet defined that it should be modify in /word/word_list.php
        \-- diff - section for function diff not yet defined that it should be modify in /word/word_list.php
        \-- diff_by_ids - section for function diff_by_ids not yet defined that it should be modify in /word/word_list.php
        \-- ex_time - section for function ex_time not yet defined that it should be modify in /word/word_list.php
        \-- ex_measure - section for function ex_measure not yet defined that it should be modify in /word/word_list.php
        \-- ex_scaling - section for function ex_scaling not yet defined that it should be modify in /word/word_list.php
        \-- ex_percent - section for function ex_percent not yet defined that it should be modify in /word/word_list.php
        \-- wlsort - section for function wlsort not yet defined that it should be modify in /word/word_list.php
        \-- filter - section for function filter not yet defined that it should be filter in /word/word_list.php
        \-- time_lst - section for function time_lst not yet defined that it should be filter in /word/word_list.php
        \-- time_useful - section for function time_useful not yet defined that it should be filter in /word/word_list.php
        \-- measure_lst - section for function measure_lst not yet defined that it should be filter in /word/word_list.php
        \-- scaling_lst - section for function scaling_lst not yet defined that it should be filter in /word/word_list.php
        \-- percent_lst - section for function percent_lst not yet defined that it should be filter in /word/word_list.php
        \-- get_grp - section for function get_grp is expected to be set and get in /word/word_list.php
        \-- phrase_list - section for function phrase_list not yet defined that it should be convert in /word/word_list.php
        \-- phrase_lst_of_names - section for function phrase_lst_of_names not yet defined that it should be convert in /word/word_list.php
        \-- value - section for function value not yet defined that it should be convert in /word/word_list.php
        \-- value_scaled - section for function value_scaled not yet defined that it should be convert in /word/word_list.php
        \-- does_contain - section for function does_contain not yet defined that it should be info in /word/word_list.php
        \-- has_time - section for function has_time not yet defined that it should be info in /word/word_list.php
        \-- has_measure - section for function has_measure not yet defined that it should be info in /word/word_list.php
        \-- has_scaling - section for function has_scaling not yet defined that it should be info in /word/word_list.php
        \-- has_percent - section for function has_percent not yet defined that it should be info in /word/word_list.php
        \-- view_lst - section for function view_lst not yet defined that it should be select linked in /word/word_list.php
        \-- max_time - section for function max_time not yet defined that it should be select functions - predefined data retrieval in /word/word_list.php
        \-- max_val_time - section for function max_val_time not yet defined that it should be select functions - predefined data retrieval in /word/word_list.php
        \-- assume_time - section for function assume_time not yet defined that it should be select functions - predefined data retrieval in /word/word_list.php
        \-- order error - order of section load has difference at load_names should be after load_by_ids of load_like,load_by_ids,load_names,load_by_grp_id,load_by_type does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,1,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,load_by_group_id_sql,load_by_phr,load_sql_by_phr,load_sql_by_phr_single,3,load_sql_init,load_names_sql_by_ids,load_sql_by_ids,del,load_cfg,load_frontend_cfg,load_usr_cfg,load,load_system_views,load_by_key,load_sql_by_id_str,load_type_lists,load_system_users,load_by_id,load_by_name,load_sql_by_user,load_by_user_sql,load_sql_old,load_by_field_row,load_sql_by_field_row,load_by_user,load_obj_last,load_obj_field_last,load_by_obj_fld,load_by_fld_of_wrd,load_by_fld_of_vrb,load_by_fld_of_trp,load_by_fld_of_val,load_by_fld_of_frm,load_by_fld_of_src,load_by_fld_of_ui,load_by_fld_of_cmp,load_by_keys,load_sql_by_names,load_sql_by_source,load_dummy,load_sql_names,load_sbx_names,load_user_changes,id_field,default_id,order of section load has difference at load_names should be after load_by_ids of load_like,load_by_ids,load_names,load_by_grp_id,load_by_type does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,1,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,load_by_group_id_sql,load_by_phr,load_sql_by_phr,load_sql_by_phr_single,3,load_sql_init,load_names_sql_by_ids,load_sql_by_ids,del,load_cfg,load_frontend_cfg,load_usr_cfg,load,load_system_views,load_by_key,load_sql_by_id_str,load_type_lists,load_system_users,load_by_id,load_by_name,load_sql_by_user,load_by_user_sql,load_sql_old,load_by_field_row,load_sql_by_field_row,load_by_user,load_obj_last,load_obj_field_last,load_by_obj_fld,load_by_fld_of_wrd,load_by_fld_of_vrb,load_by_fld_of_trp,load_by_fld_of_val,load_by_fld_of_frm,load_by_fld_of_src,load_by_fld_of_ui,load_by_fld_of_cmp,load_by_keys,load_sql_by_names,load_sql_by_source,load_dummy,load_sql_names,load_sbx_names,load_user_changes,id_field,default_id,order of section load has difference at load_names should be after load_by_ids of load_like,load_by_ids,load_names,load_by_grp_id,load_by_type does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,1,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,load_by_group_id_sql,load_by_phr,load_sql_by_phr,load_sql_by_phr_single,3,load_sql_init,load_names_sql_by_ids,load_sql_by_ids,del,load_cfg,load_frontend_cfg,load_usr_cfg,load,load_system_views,load_by_key,load_sql_by_id_str,load_type_lists,load_system_users,load_by_id,load_by_name,load_sql_by_user,load_by_user_sql,load_sql_old,load_by_field_row,load_sql_by_field_row,load_by_user,load_obj_last,load_obj_field_last,load_by_obj_fld,load_by_fld_of_wrd,load_by_fld_of_vrb,load_by_fld_of_trp,load_by_fld_of_val,load_by_fld_of_frm,load_by_fld_of_src,load_by_fld_of_ui,load_by_fld_of_cmp,load_by_keys,load_sql_by_names,load_sql_by_source,load_dummy,load_sql_names,load_sbx_names,load_user_changes,id_field,default_id,order of section load has difference at load_names should be after load_by_ids of load_like,load_by_ids,load_names,load_by_grp_id,load_by_type does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,1,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,load_by_group_id_sql,load_by_phr,load_sql_by_phr,load_sql_by_phr_single,3,load_sql_init,load_names_sql_by_ids,load_sql_by_ids,del,load_cfg,load_frontend_cfg,load_usr_cfg,load,load_system_views,load_by_key,load_sql_by_id_str,load_type_lists,load_system_users,load_by_id,load_by_name,load_sql_by_user,load_by_user_sql,load_sql_old,load_by_field_row,load_sql_by_field_row,load_by_user,load_obj_last,load_obj_field_last,load_by_obj_fld,load_by_fld_of_wrd,load_by_fld_of_vrb,load_by_fld_of_trp,load_by_fld_of_val,load_by_fld_of_frm,load_by_fld_of_src,load_by_fld_of_ui,load_by_fld_of_cmp,load_by_keys,load_sql_by_names,load_sql_by_source,load_dummy,load_sql_names,load_sbx_names,load_user_changes,id_field,default_id,order of section load has difference at load_names should be after load_by_ids of load_like,load_by_ids,load_names,load_by_grp_id,load_by_type does not match load_by_view,load_by_view_with_components,load_by_component,load_components,load_names,1,load_by_ids,load_by_view_id,load_sql,load_obj_by_id,load_sql_by_id,load_by_frm,load_by_frm_and_type_id,load_sql_by_frm_id,load_sql_by_frm_and_type_id,load_by_frm_id,phrase_ids,del_without_log,load_by_group_id_sql,load_by_phr,load_sql_by_phr,load_sql_by_phr_single,3,load_sql_init,load_names_sql_by_ids,load_sql_by_ids,del,load_cfg,load_frontend_cfg,load_usr_cfg,load,load_system_views,load_by_key,load_sql_by_id_str,load_type_lists,load_system_users,load_by_id,load_by_name,load_sql_by_user,load_by_user_sql,load_sql_old,load_by_field_row,load_sql_by_field_row,load_by_user,load_obj_last,load_obj_field_last,load_by_obj_fld,load_by_fld_of_wrd,load_by_fld_of_vrb,load_by_fld_of_trp,load_by_fld_of_val,load_by_fld_of_frm,load_by_fld_of_src,load_by_fld_of_ui,load_by_fld_of_cmp,load_by_keys,load_sql_by_names,load_sql_by_source,load_dummy,load_sql_names,load_sbx_names,load_user_changes,id_field,default_id,order of section im- and export has difference at parents should be after foaf_parents of import_obj,export_json,foaf_parents,parents,children,direct_children,is,are,contains,are_and_contains,differentiators,differentiators_all,differentiators_filtered,keep_only_specific does not match 1,4,export_json,save,get_ready,load_linked_phrases,load_linking_triples,parents,all_children,foaf_children,foaf_parents,foaf_related,direct_children,is,are,contains,order of section im- and export has difference at parents should be after foaf_parents of import_obj,export_json,foaf_parents,parents,children,direct_children,is,are,contains,are_and_contains,differentiators,differentiators_all,differentiators_filtered,keep_only_specific does not match 1,4,export_json,save,get_ready,load_linked_phrases,load_linking_triples,parents,all_children,foaf_children,foaf_parents,foaf_related,direct_children,is,are,contains,order of section im- and export has difference at parents should be after foaf_parents of import_obj,export_json,foaf_parents,parents,children,direct_children,is,are,contains,are_and_contains,differentiators,differentiators_all,differentiators_filtered,keep_only_specific does not match 1,4,export_json,save,get_ready,load_linked_phrases,load_linking_triples,parents,all_children,foaf_children,foaf_parents,foaf_related,direct_children,is,are,contains,order of section im- and export has difference at parents should be after foaf_parents of import_obj,export_json,foaf_parents,parents,children,direct_children,is,are,contains,are_and_contains,differentiators,differentiators_all,differentiators_filtered,keep_only_specific does not match 1,4,export_json,save,get_ready,load_linked_phrases,load_linking_triples,parents,all_children,foaf_children,foaf_parents,foaf_related,direct_children,is,are,contains,order of section im- and export has difference at parents should be after foaf_parents of import_obj,export_json,foaf_parents,parents,children,direct_children,is,are,contains,are_and_contains,differentiators,differentiators_all,differentiators_filtered,keep_only_specific does not match 1,4,export_json,save,get_ready,load_linked_phrases,load_linking_triples,parents,all_children,foaf_children,foaf_parents,foaf_related,direct_children,is,are,contains,order of section im- and export has difference at parents should be after foaf_parents of import_obj,export_json,foaf_parents,parents,children,direct_children,is,are,contains,are_and_contains,differentiators,differentiators_all,differentiators_filtered,keep_only_specific does not match 1,4,export_json,save,get_ready,load_linked_phrases,load_linking_triples,parents,all_children,foaf_children,foaf_parents,foaf_related,direct_children,is,are,contains,order of section im- and export has difference at parents should be after foaf_parents of import_obj,export_json,foaf_parents,parents,children,direct_children,is,are,contains,are_and_contains,differentiators,differentiators_all,differentiators_filtered,keep_only_specific does not match 1,4,export_json,save,get_ready,load_linked_phrases,load_linking_triples,parents,all_children,foaf_children,foaf_parents,foaf_related,direct_children,is,are,contains,order of section im- and export has difference at parents should be after foaf_parents of import_obj,export_json,foaf_parents,parents,children,direct_children,is,are,contains,are_and_contains,differentiators,differentiators_all,differentiators_filtered,keep_only_specific does not match 1,4,export_json,save,get_ready,load_linked_phrases,load_linking_triples,parents,all_children,foaf_children,foaf_parents,foaf_related,direct_children,is,are,contains,order of section im- and export has difference at parents should be after foaf_parents of import_obj,export_json,foaf_parents,parents,children,direct_children,is,are,contains,are_and_contains,differentiators,differentiators_all,differentiators_filtered,keep_only_specific does not match 1,4,export_json,save,get_ready,load_linked_phrases,load_linking_triples,parents,all_children,foaf_children,foaf_parents,foaf_related,direct_children,is,are,contains,order of section im- and export has difference at parents should be after foaf_parents of import_obj,export_json,foaf_parents,parents,children,direct_children,is,are,contains,are_and_contains,differentiators,differentiators_all,differentiators_filtered,keep_only_specific does not match 1,4,export_json,save,get_ready,load_linked_phrases,load_linking_triples,parents,all_children,foaf_children,foaf_parents,foaf_related,direct_children,is,are,contains,order of section im- and export has difference at parents should be after foaf_parents of import_obj,export_json,foaf_parents,parents,children,direct_children,is,are,contains,are_and_contains,differentiators,differentiators_all,differentiators_filtered,keep_only_specific does not match 1,4,export_json,save,get_ready,load_linked_phrases,load_linking_triples,parents,all_children,foaf_children,foaf_parents,foaf_related,direct_children,is,are,contains,order of section im- and export has difference at parents should be after foaf_parents of import_obj,export_json,foaf_parents,parents,children,direct_children,is,are,contains,are_and_contains,differentiators,differentiators_all,differentiators_filtered,keep_only_specific does not match 1,4,export_json,save,get_ready,load_linked_phrases,load_linking_triples,parents,all_children,foaf_children,foaf_parents,foaf_related,direct_children,is,are,contains,order of section im- and export has difference at parents should be after foaf_parents of import_obj,export_json,foaf_parents,parents,children,direct_children,is,are,contains,are_and_contains,differentiators,differentiators_all,differentiators_filtered,keep_only_specific does not match 1,4,export_json,save,get_ready,load_linked_phrases,load_linking_triples,parents,all_children,foaf_children,foaf_parents,foaf_related,direct_children,is,are,contains,order of section im- and export has difference at parents should be after foaf_parents of import_obj,export_json,foaf_parents,parents,children,direct_children,is,are,contains,are_and_contains,differentiators,differentiators_all,differentiators_filtered,keep_only_specific does not match 1,4,export_json,save,get_ready,load_linked_phrases,load_linking_triples,parents,all_children,foaf_children,foaf_parents,foaf_related,direct_children,is,are,contains
\-- other backend
    \-- 
        \-- start_api_core
            \-- application - open the database connection to answer an api request
        \-- start_api
            \-- application - open the database connection to answer an api request
        \-- end_api
            \-- application - open the database connection to answer an api request
        \-- start
            \-- application - should be called from all code that can be accessed by an url
        \-- open_db
            \-- application - open the database connection and load the base cache
        \-- end
            \-- application - open the database connection and load the base cache
        \-- load_dummy
            \-- component_link_type_list - adding the view component position types used for unit tests to the dummy list
            \-- component_type_list - adding the view component types used for unit tests to the dummy list
            \-- position_type_list - adding the view component position types used for unit tests to the dummy list
            \-- view_style_list - adding the view component types used for unit tests to the dummy list
            \-- element_type_list - adding the view component types used for unit tests to the dummy list
            \-- formula_link_type_list - adding the formula link types used for unit tests to the dummy list
            \-- formula_type_list - adding the formula types used for unit tests to the dummy list
            \-- language_form_list - create dummy type list for the unit tests without database connection
            \-- language_list - create dummy type list for the unit tests without database connection
            \-- change_action_list - adding the system log statuus used for unit tests to the dummy list
            \-- change_field_list - adding the system log statuus used for unit tests to the dummy list
            \-- change_table_list - adding the system log statuus used for unit tests to the dummy list
            \-- protection_type_list - create dummy type list for the unit tests without database connection
            \-- share_type_list - create dummy type list for the unit tests without database connection
            \-- job_type_list - adding the job type used for unit tests to a dummy list
            \-- sys_log_function_list - adding the system log functions used for unit tests to the dummy list
            \-- sys_log_status_list - adding the system log statuus used for unit tests to the dummy list
            \-- user_profile_list - create dummy type list for the unit tests without database connection
            \-- view_link_type_list - adding the view link types used for unit tests to the dummy list
            \-- view_relation_type_list - adding the view relation types used for unit tests to the dummy list
            \-- view_type_list - adding the view types used for unit tests to the dummy list
        \-- default_id
            \-- component_type_list - return the database id of the default view component type
            \-- position_type_list - return the database id of the default view component position type
            \-- view_style_list - return the database id of the default view component type
            \-- element_type_list - return the database id of the default formula element type
            \-- formula_link_type_list - return the database id of the default formula link type
            \-- formula_type_list - return the database id of the default formula type
            \-- language_form_list - return the database id of the default share type
            \-- language_list - return the database id of the default share type
            \-- change_action_list - return the database id of the default log type
            \-- change_field_list - return the database id of the default log type
            \-- change_table_list - return the database id of the default log type
            \-- protection_type_list - return the database id of the default protection type
            \-- share_type_list - return the database id of the default share type
            \-- job_type_list - return the database id of the default job type
            \-- sys_log_function_list - return the database id of the default system log function
            \-- sys_log_status_list - return the database id of the default system log status
            \-- user_profile_list - return the database id of the default user profile
            \-- view_link_type_list - return the database id of the default view type
            \-- view_relation_type_list - return the database id of the default view type
            \-- view_type_list - return the database id of the default view type
        \-- add
            \-- sql_field_list - add a field to the list
            \-- sql_where_list - add a field to the list
        \-- add_field
            \-- sql_field_list - add a field based on the separate name, value and type
        \-- add_value
            \-- sql_field_list - add a value without name e.g. for the sql function Now()
        \-- add_par_field
            \-- sql_field_list - add a sql par field and ignore the id and old part of the sql par field
        \-- add_par_field_id
            \-- sql_field_list - add the id part of a sql par field
        \-- get
            \-- sql_field_list - get the sql_field of the given position
            \-- value_obj - get the best fitting value object for the given value
        \-- name
            \-- sql_field_list - the name of a parameter from a given position
        \-- value
            \-- sql_field_list - the value of a parameter from a given position
        \-- type
            \-- sql_field_list - the type of parameter from a given position
        \-- pos
            \-- sql_field_list - the type of parameter from a given position
        \-- names
            \-- sql_field_list - @return array with the field names of the list
        \-- values
            \-- sql_field_list - @return array with the field values of the list
        \-- types
            \-- sql_field_list - @return array with the field types of the list
        \-- has
            \-- sql_field_list - @return array with the field types of the list
        \-- count
            \-- sql_field_list - @return int get the number of named parameters (excluding the const like Now())
        \-- names_or_const
            \-- sql_field_list - @return array with the field names of the list or the const value
        \-- sql_names
            \-- sql_field_list - @return string with the parameter names formatted for sql
        \-- __construct
            \-- sql_par - @param string $class the name of the calling class used for the unique query name
            \-- sql_type_list - @param array $lst with the initial sql create parameter
            \-- sql_where - 
            \-- export_type_list - @param array $lst with the initial sql create parameter
            \-- group_link - 
            \-- import - 
            \-- import_file - 
            \-- view_sys_list - 
        \-- has_par
            \-- sql_par - @return bool true if the query has at least one parameter set
        \-- merge
            \-- sql_par - merge two sql and the related parameters to one sql statement
        \-- combine
            \-- sql_par - combine two sql and the related parameters to one sql statement
        \-- is_function
            \--  - 
        \-- is_or
            \--  - 
        \-- is_list
            \--  - @return bool true if the selection is based on a list e.g. "IN (1,2,3)"
        \-- is_text
            \--  - @return bool true if the selection is based on a list e.g. "IN (1,2,3)"
        \-- prefix
            \--  - @return string the name prefix for the query name
        \-- is_sql_change
            \--  - @return bool true if the sql type changes the database e.g. an update query
        \-- is_val_type
            \--  - @return bool true if the sql type is used to select the value type
        \-- export_by_phrase_list
            \-- xml - create a xml for export based on a given phrase list
        \-- table_ext_list
            \-- group_id_list - list of the table extension / types where the value or result rows might be found
        \-- row_mapper
            \-- group_link - map the database fields to one db row to this phrase group triple link object
        \-- int2alpha_num
            \-- id - @param int $id a phrase id
        \-- convert
            \-- convert_wikipedia_table - convert a wikipedia table to a zukunft.com json string
        \-- convert_wiki_json
            \-- convert_wikipedia_table - convert a wikitable2json to a zukunft.com json
        \-- log_debug
            \--  - for internal functions debugging
        \-- log_info
            \--  - log an info message to the text log and the log table depending on the log settings
        \-- log_warning
            \--  - log an info message to the text log and the log table depending on the log settings
        \-- log_err
            \--  - log an info message to the text log and the log table depending on the log settings
        \-- log_fatal_db
            \--  - if still possible write the fatal error message to the database and stop the execution
        \-- log_fatal
            \--  - try to write the error message to any possible out device if database connection is lost
        \-- log_msg
            \--  - write a log message to the database and return the message that should be shown to the user
        \-- read
            \-- ref_link_wikidata - 
    \-- load
        \-- load_by_view
            \-- component_link_list - interface function to load all component links of the given view
            \-- view_relation_list - interface function to load all view relations of the given view
        \-- load_by_view_with_components
            \-- component_link_list - interface function to load all component links and the components of the given view
        \-- load_by_component
            \-- component_link_list - interface function to load all views linked to a given component
        \-- load_components
            \-- component_link_list - load the components of this list
        \-- load_names
            \-- component_list - load a list of component names
        \-- 1
            \-- db_object - parent function to create the common part of an SQL statement for group, value and result tables
            \-- db_object_key - parent function to create the common part of an SQL statement for group, value and result tables
            \-- db_object_no_id - parent function to create the common part of an SQL statement for group, value and result tables
            \-- change - load the last change of given user
            \-- view_sys_list - set the SQL query parameters to load a list of views from the database that have a used code id
        \-- load_by_ids
            \-- component_list - load the components selected by the id
            \-- phrase_list - load the phrases including the related word or triple object
        \-- load_by_view_id
            \-- component_list - load the components of a view from the database selected by id
        \-- load_sql
            \-- element - create the common part of an SQL statement to get the formula element from the database
            \-- formula_link_list - set the SQL query parameters to load a list of formula links
            \-- group_link - create the common part of an SQL statement to get the phrase group triple link from the database
            \-- db_object - parent function to create the common part of an SQL statement
            \-- db_object_key - parent function to create the common part of an SQL statement
            \-- db_object_no_id - parent function to create the common part of an SQL statement
            \-- change - create the common part of an SQL statement to retrieve the parameters of the change log
            \-- change_value - create the common part of an SQL statement to retrieve the parameters of the value change log
            \-- change_value_geo - create the common part of an SQL statement to retrieve the parameters of the value change log
            \-- change_value_text - create the common part of an SQL statement to retrieve the parameters of the value change log
            \-- change_value_time - create the common part of an SQL statement to retrieve the parameters of the value change log
            \-- sys_log - create the SQL statement to load one system log entry
        \-- load_obj_by_id
            \-- element - get the related object (term?) from the database
        \-- load_sql_by_id
            \-- element - create an SQL statement to retrieve a formula element by id from the database
            \-- sys_log - create an SQL statement to retrieve a system log entry by id from the database
        \-- load_by_frm
            \-- element_list - add the element object
        \-- load_by_frm_and_type_id
            \-- element_list - add the element object
        \-- load_sql_by_frm_id
            \-- element_list - set the SQL query parameters to load a list of formula elements by the formula id
            \-- formula_link_list - set the SQL query parameters to load a list of formula links by the formula id
        \-- load_sql_by_frm_and_type_id
            \-- element_list - set the SQL query parameters to load a list of formula elements by the formula id and filter by the element type
        \-- load_by_frm_id
            \-- formula_link_list - load a list of formula links with the direct linked phrases related to the given formula id
        \-- phrase_ids
            \-- formula_link_list - get an array with all phrases linked of this list e.g. linked to one formula
        \-- del_without_log
            \-- formula_link_list - delete all links without log because this is used only when deleting a formula
        \-- load_by_group_id_sql
            \-- group_link - create an SQL statement to retrieve the phrase group triple links related to a group id
        \-- load_by_phr
            \-- group_list - load all phrase groups that contain the given phrase
        \-- load_sql_by_phr
            \-- group_list - create an SQL statement to retrieve a list of groups linked to the given phrase from the database
        \-- load_sql_by_phr_single
            \-- group_list - create an SQL statement to retrieve a list of values linked to a phrase from the database
        \-- 3
            \-- sandbox_value_list - create an SQL statement to retrieve a list of values linked to a phrase from the database
        \-- load_sql_init
            \-- group_list - set the SQL query parameters to load a list of groups
            \-- sandbox_value_list - set the SQL query parameters to load a list of values or results
        \-- load_names_sql_by_ids
            \-- group_list - set the SQL query parameters to load a list of phrase groups names by the ids
        \-- load_sql_by_ids
            \-- group_list - set the SQL query parameters to load a list of phrase groups by the ids
        \-- del
            \-- group_list - delete all loaded phrase groups e.g. to delete al the phrase groups linked to a phrase
        \-- load_cfg
            \-- config_numbers - load the system configuration from the database to this object
        \-- load_frontend_cfg
            \-- config_numbers - load the system configuration values relevant for the frontend
        \-- load_usr_cfg
            \-- config_numbers - load the system configuration values that the user can change
        \-- load
            \-- data_object - load the objects from the database and fill in missing db id
            \-- ref_list - force to reload the complete list of refs from the database
            \-- view_sys_list - overwrite the general user sys list load function to keep the link to the table sys capsuled
        \-- load_system_views
            \-- data_object - load the base data (types, system views) from the database
        \-- load_by_key
            \-- db_id_object_non_sandbox - load the object by the given unique key
        \-- load_sql_by_id_str
            \-- db_object - create an SQL statement to retrieve a user sandbox object by id from the database
            \-- db_object_key - create an SQL statement to retrieve a user sandbox object by id from the database
            \-- db_object_no_id - create an SQL statement to retrieve a user sandbox object by id from the database
        \-- load_type_lists
            \-- system_object - load the base data (types, system views) from the database
        \-- load_system_users
            \-- system_object - load all system users that have a code id
        \-- load_by_id
            \-- language - load a language object by database id
            \-- language_form - load a language form object by database id
            \-- phrase_type - load a phrase type object by database id
            \-- sys_log - load a system error from the database e.g. to be able to display more details
        \-- load_by_name
            \-- language - load a language object by database id
        \-- load_sql_by_user
            \-- change - create an SQL statement to retrieve a change long entry by the changing user
        \-- load_by_user_sql
            \-- change - create the SQL statement to retrieve the parameters of the change log by name
        \-- load_sql_old
            \-- change - create the SQL statement to retrieve the parameters of the change log by name
        \-- load_by_field_row
            \-- change_log - load the last change of given user
        \-- load_sql_by_field_row
            \-- change_log - create the SQL statement to retrieve the parameters of the change log by field and row id
        \-- load_by_user
            \-- change_log_list - load the changes of one user
        \-- load_obj_last
            \-- change_log_list - load the latest changes of one object
        \-- load_obj_field_last
            \-- change_log_list - load the latest changes of one object
        \-- load_by_obj_fld
            \-- change_log_list - load a list of sandbox object changes
        \-- load_by_fld_of_wrd
            \-- change_log_list - load a list of the view changes of a word
        \-- load_by_fld_of_vrb
            \-- change_log_list - load a list of the view changes of a verb
        \-- load_by_fld_of_trp
            \-- change_log_list - load a list of the view changes of a triple
        \-- load_by_fld_of_val
            \-- change_log_list - load a list of the view changes of a value
        \-- load_by_fld_of_frm
            \-- change_log_list - load a list of the view changes of a formula
        \-- load_by_fld_of_src
            \-- change_log_list - load a list of the view changes of a source
        \-- load_by_fld_of_ui
            \-- change_log_list - load a list of the view changes of a view
        \-- load_by_fld_of_cmp
            \-- change_log_list - load a list of the view changes of a view component
        \-- load_by_keys
            \-- ref_list - load a list of sources by the names
        \-- load_sql_by_names
            \-- ref_list - load a list of sources by the names
        \-- load_sql_by_source
            \-- ref_list - load a list of sources by the names
        \-- load_dummy
            \-- ref_list - adding the refs used for unit tests to the dummy list
            \-- view_sys_list - adding the system views used for unit tests to the dummy list
        \-- load_sql_names
            \-- sandbox_list - build the SQL statement to load only the id and name to save time and memory
        \-- load_sbx_names
            \-- sandbox_list - load only the id and name of sandbox objects (e.g. phrases or values) based on the given query parameters
        \-- load_user_changes
            \-- sandbox_list - load the changes that the given user has done compared to the standard
        \-- id_field
            \-- sys_log - @return string sys_log_id instead of sys_log_id
        \-- default_id
            \-- view_sys_list - return the database id of the default view sys
    \-- load sql
        \-- load_sql_by_view
            \-- component_link_list - set the SQL query parameters to load all components linked to a view
            \-- view_relation_list - set the SQL query parameters to load all components linked to a view
        \-- load_sql_by_component
            \-- component_link_list - set the SQL query parameters to load all views linked to a component
        \-- 4
            \-- component_list - set the SQL query parameters to load a list of components by the view id
        \-- load_sql
            \-- component_link_list - set the common part of the SQL query component links
            \-- component_list - set the common SQL query parameters to load a list of components
            \-- view_relation_list - set the common part of the SQL query view relations
        \-- load_sql_by_user
            \-- change_log_list - create an SQL statement to retrieve the changes done by the given user
    \-- im- and export
        \-- 1
            \-- component_list - import a list of components from a JSON array object
            \-- ip_range_exp - import an ip range from an imported json object
        \-- 4
            \-- phrase_list - fill this list with the phrases of the given json without writing to the database
        \-- export_json
            \-- component_link_list - create an array with the export json fields
            \-- component_list - create an array with the export json fields
            \-- phrase_list - create an array with the export json phrases
            \-- sandbox_list - create an array with one export json array for each list item
            \-- ip_range_exp - create an array with the export json fields
            \-- value_geo - create an array with the export json fields
            \-- value_text - create an array with the export json fields
            \-- value_time - create an array with the export json fields
            \-- view_relation_list - create an array with the export json fields
        \-- save
            \-- component_list - add or update all components to the database
        \-- get_ready
            \-- component_list - get a list of components that are ready to be added to the database
        \-- load_linked_phrases
            \-- phrase_list - add the direct linked phrases to the list
        \-- load_linking_triples
            \-- phrase_list - add the direct linking triples to the list
        \-- parents
            \-- phrase_list - similar to foaf_parents, but for only one level
        \-- all_children
            \-- phrase_list - get all children
        \-- foaf_children
            \-- phrase_list - get the words and triples "below" the given phrases
        \-- foaf_parents
            \-- phrase_list - get the words and triples "below" the given phrases
        \-- foaf_related
            \-- phrase_list - get the words and triples related the given phrases
        \-- direct_children
            \-- phrase_list - get the direct children
        \-- is
            \-- phrase_list - @return phrase_list list of phrases that are related to this phrase list
        \-- are
            \-- phrase_list - get the related phrase
        \-- contains
            \-- phrase_list - @returns phrase_list a list of phrases that are related to this phrase list
    \-- modify
        \-- add_by_name
            \-- component_link_list - add a view component link to the list without saving it to the database
            \-- view_relation_list - add a view relation to the list without saving it to the database
        \-- 1
            \-- sandbox_value_list - add one value to the value list, but only if it is not yet part of the list
        \-- add
            \-- sql_type_list - add a type to the list
            \-- element_list - add one formula element to the list and keep the order (contrary to the parent function)
            \-- export_type_list - add a type to the list
            \-- change_log_list - add one change log entry to the change list
            \-- sandbox_link_list - add a link based on parts to this list without saving it to the database
            \-- sandbox_value_list - add one value to the value list, but only if it is not yet part of the list
            \-- ip_range_list - add an ip range to the list
            \-- job_list - add another job to the list, but only if needed
            \-- system_time_list - @return string description of the execution times by category of the last section
        \-- remove
            \-- sql_type_list - remove a type from the list if it has been in the list
            \-- export_type_list - remove a type from the list if it has been in the list
        \-- add_word
            \-- data_object - add a named word without db id to the list
        \-- add_verb
            \-- data_object - add a named verb without db id to the list
        \-- add_triple
            \-- data_object - add a triple with the names of the linked phrase names but without db id to the list
        \-- add_triple_without_ready_check
            \-- data_object - add a triple by the triple name without checking the links
        \-- add_phrase
            \-- data_object - add a name phrase without db id to the list
        \-- add_source
            \-- data_object - add a source with the names but without db id to the list
        \-- add_reference
            \-- data_object - add a reference with the names but without db id to the list
        \-- add_formula
            \-- data_object - add a formula with word and triple names but without db id to the list
        \-- add_formula_without_ready_check
            \-- data_object - add a formula by the formula name without checking the links
        \-- add_term
            \-- data_object - add a name term without db id to the list
        \-- add_view
            \-- data_object - add a view with name but without db id to the list
        \-- add_component
            \-- data_object - add a component with name but without db id to the list
        \-- add_term_view
            \-- data_object - add a term view with term and the view but without db id to the list
        \-- add_user
            \-- data_object - add a user without db id to the list
        \-- add_ip_range
            \-- data_object - add an ip range without db id to the list
        \-- add_value
            \-- data_object - add a value to the list
        \-- add_message
            \-- data_object - add a value to the list
        \-- get_component_by_name
            \-- data_object - add a value to the list
        \-- get_value_by_names
            \-- data_object - add a value to the list
        \-- expected_word_import_time
            \-- data_object - add a value to the list
        \-- expected_triple_import_time
            \-- data_object - add a value to the list
        \-- expected_value_import_time
            \-- data_object - add a value to the list
        \-- expected_total_import_time
            \-- data_object - add a value to the list
        \-- count
            \-- data_object - add a value to the list
        \-- save
            \-- data_object - add all words, triples and values to the database
        \-- diff_msg
            \-- data_object - TODO add the missing lists and vars of the dto object
        \-- fill
            \-- db_object_multi_user - fill this db user object based on the given object
            \-- db_object_seq_id_user - fill this db user object based on the given object
        \-- query_extension
            \-- value_type_list - 
        \-- dsp_last
            \-- change_log - display the last change related to one object (word, formula, value, verb, ...)
        \-- add_ref
            \-- change_log - display the last change related to one object (word, formula, value, verb, ...)
        \-- use_type_id
            \-- change_log - display the last change related to one object (word, formula, value, verb, ...)
        \-- add_wrd_lst
            \-- phrase_list - add a list of words to the phrase list, but only if it is not yet part of the phrase list
        \-- add_trp_lst
            \-- phrase_list - add a list of triples to the phrase list, but only if it is not yet part of the phrase list
        \-- add_id
            \-- phrase_list - add one phrase by the id to the phrase list, but only if it is not yet part of the phrase list
        \-- add_name
            \-- phrase_list - add one phrase to the phrase list defined by the phrase name
        \-- 2
            \-- ref_list - add a reference to the list that does not yet have an id but has the phrase name, the type and the external key set
        \-- del
            \-- phrase_list - del one phrase to the phrase list, but only if it is not yet part of the phrase list
            \-- ref_list - add a reference to the list that does not yet have an id but has the phrase name, the type and the external key set
            \-- view_relation_list - delete all loaded view relations e.g. to delete all the links assigned to a view
        \-- merge_by_name
            \-- phrase_list - add the phrases of the given list to this list
        \-- del_list
            \-- phrase_list - remove a list of phrases from this phrase list
        \-- filter_by_ids
            \-- phrase_list - filters a phrase list by an id list
        \-- filter_valid
            \-- phrase_list - leave only the valid words and triples in this list
        \-- get_diff
            \-- phrase_list - diff as a function, because the array_diff does not seem to work for an object list
        \-- diff
            \-- phrase_list - diff as a function, because the array_diff does not seem to work for an object list
        \-- not_in
            \-- phrase_list - same as diff but sometimes this name looks better
        \-- diff_by_ids
            \-- phrase_list - similar to diff, but using an id array to exclude instead of a phrase list object
        \-- keep_only_specific
            \-- phrase_list - look at a phrase list and remove the general phrase, if there is a more specific phrase also part of the list e.g. remove "Country", but keep "Switzerland"
        \-- has_time
            \-- phrase_list - @return bool true if a phrase lst contains a time phrase
        \-- has_measure
            \-- phrase_list - @return bool true if a phrase lst contains a measure phrase
        \-- has_scaling
            \-- phrase_list - @return bool true if a phrase lst contains a scaling phrase
        \-- has_percent
            \-- phrase_list - @return bool true if a phrase lst contains a percent scaling phrase, which is used for a predefined formatting of the value
        \-- time_lst_old
            \-- phrase_list - get all phrases of this phrase list that have at least one time term
        \-- time_word_list
            \-- phrase_list - get all words of this phrase list that have at least one time term
        \-- time_list
            \-- phrase_list - get all words of this phrase list that have at least one time term
        \-- get_by_type
            \-- phrase_list - @param string $phr_typ code_id of the type that should be selected
        \-- get_names_by_type
            \-- phrase_list - @param string $phr_typ code_id of the type that should be selected
        \-- time_useful
            \-- phrase_list - @return phrase with the most useful time phrase
        \-- assume_time
            \-- phrase_list - get the most useful time for the given words
        \-- measure_lst
            \-- phrase_list - filter the measure phrases out of the list of phrases
        \-- scaling_lst
            \-- phrase_list - filter the scaling phrases out of the list of phrases
        \-- ex_time
            \-- phrase_list - Exclude all time phrases out of the list of phrases
        \-- ex_measure
            \-- phrase_list - Exclude all measure phrases out of the list of phrases
        \-- ex_scaling
            \-- phrase_list - Exclude all scaling phrases out of the list of phrases
        \-- name_sort
            \-- phrase_list - sort the phrase object list by name
        \-- sort_by_id
            \-- phrase_list - sort the phrase object list by id
        \-- sort_rev_by_id
            \-- phrase_list - sort the phrase object list by id in reverse order
        \-- max_time
            \-- phrase_list - get the last time phrase of the phrase list
        \-- get_grp_id
            \-- phrase_list - @return group|null the group with only the id set based to this list or null if no group matches
        \-- common
            \-- phrase_list - @return array all phrases that are part of each phrase group of the list
        \-- concat_unique
            \-- phrase_list - @return phrase_list the combined list of this list and the given list without changing this phrase list
        \-- add_link
            \-- sandbox_link_list - add a link to this list without saving it to the database
        \-- add_link_by_key
            \-- sandbox_link_list - add one link to the list of user sandbox objects,
        \-- add_obj
            \-- sandbox_list - add one object to the list of user sandbox objects, but only if it is not yet part of the list
        \-- merge
            \-- job_list - merge all jobs of the given batch job list to this list
    \-- del
        \-- del
            \-- component_link_list - delete all loaded view component links e.g. to delete all the links assigned to a view
            \-- db_id_object_non_sandbox - delete the related db row and log the deletion
        \-- del_without_log
            \-- element_list - add one formula element to the list and keep the order (contrary to the parent function)
        \-- del_sql_without_log
            \-- element_list - create a sql statement that deletes all formula elements of this list
    \-- info
        \-- view_ids
            \-- component_link_list - @return array with all view ids
            \-- view_relation_list - @return array with all view ids
        \-- cmp_ids
            \-- component_link_list - @return array with all component ids
        \-- names
            \-- component_link_list - @return array with all component names linked usually to one view
            \-- view_relation_list - @return array with all component names linked usually to one view
        \-- common_phrases
            \-- group_list - get the common phrases of a groups
        \-- id_field
            \-- combine_object - @return string the field name of the unique id of the combine database view
            \-- db_object - name of prime index field of the table
            \-- db_object_key - name of prime index field of the table
            \-- db_object_no_id - name of prime index field of the table
            \-- value_time_series - temp overwrite of the id_field function of sandbox_value class until this class is reviewed
        \-- db_ready
            \-- combine_object - @return user_message empty if all vars of the underlying object are set and the phrase can be stored in the database
        \-- has_view_list
            \-- data_object - @return bool true if this context object contains a view list
        \-- diff_msg
            \-- db_object_multi_user - create human-readable messages of the differences between the db id objects
            \-- db_object_seq_id_user - create human-readable messages of the differences between the db id objects
            \-- sandbox_value_list - reports the difference to the given value list as a human-readable messages
        \-- has_id
            \-- db_object_seq_id_user - create human-readable messages of the differences between the db id objects
        \-- use_type_id
            \-- change - create an array for the json api message
        \-- first_msg
            \-- change_log_list - @return string with the first change description of this list
        \-- empty
            \-- phrase_list - @returns bool true if the phrase list is empty
        \-- id
            \-- phrase_list - @return string a unique id of the phrase list
        \-- missing_ids
            \-- phrase_list - @return phrase_list with all phrases that does not yet have a database id
        \-- prime_only
            \-- phrase_list - @returns bool true if none of the phrase list id needs more than 16 bit
        \-- one_positiv
            \-- phrase_list - @returns bool true if at least one id is positive or not used to avoid exceeding PHP_INT_MAX
        \-- id_lst
            \-- phrase_list - get the phrase ids as an array
            \-- sandbox_value_list - @return array with the sorted value ids
        \-- obj_id_lst
            \-- phrase_list - get the phrase ids as an array
        \-- phrase_ids
            \-- phrase_list - @return phr_ids with the sorted phrase ids where a triple has a negative id
        \-- wrd_ids
            \-- phrase_list - @return array with the word ids
        \-- trp_ids
            \-- phrase_list - @return array with the triple ids (converted from the negative phrase ids)
        \-- id_url
            \-- phrase_list - return an url with the phrase ids
        \-- id_url_long
            \-- phrase_list - the old long form to encode
        \-- loaded
            \-- phrase_list - @returns bool true if all phrases of the list have a name and an id
        \-- are_and_contains
            \-- phrase_list - makes sure that all combinations of "are" and "contains" are included
        \-- differentiators
            \-- phrase_list - add all potential differentiator phrases of the phrase lst e.g. get "energy" for "sector"
        \-- differentiators_all
            \-- phrase_list - same as differentiators, but including the subtypes e.g. get "energy" and "wind energy" for "sector" if "wind energy" is part of "energy"
        \-- differentiators_filtered
            \-- phrase_list - similar to differentiators, but only a filtered list of differentiators is viewed to increase speed
        \-- is_system
            \-- user_profile - 
    \-- save
        \-- 2
            \-- ip_range_exp - get a similar or overlapping ip range
        \-- save
            \-- component_link_list - simple but slow function to add of update all list items in the database
            \-- phrase_list - save all changes of the phrase list to the database
            \-- ref_list - store all references from this list in the database using grouped calls of predefined sql functions
            \-- ip_range_exp - update an ip range in the database or update the existing
            \-- ip_range_list - store all ip ranges from this list in the database using grouped calls of predefined sql functions
            \-- view_relation_list - simple but slow function to add of update all list items in the database
        \-- add_ref
            \-- change - add the row id to an existing log entry
        \-- add
            \-- change_log - log a user change of a word, value or formula
        \-- id_field
            \-- ip_range_exp - helper because the db id field differs from the class name
    \-- main
        \-- db_check
            \-- db_check - read the version number from the database and compare it with the backend version
        \-- db_upgrade_0_0_3
            \-- db_check - upgrade the database from any version prior of 0.0.3
        \-- db_move_time_phrase_to_group
            \-- db_check - upgrade the database from any version prior of 0.0.3
        \-- db_upgrade_0_0_4
            \-- db_check - upgrade the database from any version prior of 0.0.4
    \-- construct and map
        \-- __construct
            \-- sql_creator - set the default sql_creator configuration
            \-- element - always set the user because a formula element is always user specific
            \-- expression - 
            \-- combine_object - a combine object always covers an existing object
            \-- data_object - always set the user because always someone must have requested to create the list
            \-- db_object_multi_user - @param user $usr the user how has requested to see his view on the object
            \-- db_object_seq_id_user - @param user $usr the user how has requested to see his view on the object
            \-- system_object - always set the user because always someone must have requested to create the list
            \-- change_log - always set the user because a change log list is always user specific
            \-- text_log - 
            \-- phrase_type - 
            \-- phrase_types - @param bool $usr_can_add true by default to allow searching by name for new added phrase types
            \-- ref_list - define the settings for this ref list object
            \-- sandbox_link_list - 
            \-- sandbox_list - always set the user because a link list is always user specific
            \-- sandbox_value_list - 
            \-- job_list - always set the user because a job list either user specific or linked to the system user
            \-- value_geo - set the user, which is needed in all cases and the main vars with the object creation
            \-- value_text - set the user, which is needed in all cases and the main vars with the object creation
            \-- value_time - set the user, which is needed in all cases and the main vars with the object creation
            \-- value_time_series - set the user sandbox type for a value time series object and set the user, which is needed in all cases
        \-- row_mapper_sandbox
            \-- element - map the formula element database fields for later load of the object
        \-- api_mapper
            \-- element - map an element api json to this model element object
            \-- db_id_object_non_sandbox - fill the vars with this db id object based on the given api json array
            \-- phrase_list - map a phrase list api json to this model phrase list object
            \-- sandbox_value_list - map a figure list api json to this model figure list object
        \-- reset
            \-- combine_named - set the object vars of a phrase or term to the neutral initial value
            \-- sandbox_predicated_link - reset the type of the link object
            \-- value_time_series - set the user sandbox type for a value time series object and set the user, which is needed in all cases
        \-- row_mapper
            \-- db_object_key - dummy map function to be overwritten by the child object
            \-- db_object_no_id - dummy map function to be overwritten by the child object
            \-- change - map the database fields to one change log entry to this log object
            \-- change_value - map the database fields to one change log entry to this log object
            \-- ip_range_exp - map the database fields to this ip range object
            \-- sys_log - map the database fields to one system log entry to this log object
        \-- import_mapper
            \-- phrase_list - import a phrase list from an inner part of a JSON array object
            \-- ip_range_exp - set the vars of this ip range object based on the given json without writing to the database
        \-- code_id
            \-- phrase_type - 
        \-- load_dummy
            \-- phrase_types - adding the word types used for unit tests to the dummy list
        \-- default_id
            \-- phrase_types - @return int the database id of the default word type
    \-- set and get
        \-- set_db_type
            \-- sql_creator - set the database type and reset the object
        \-- db_type
            \-- sql_creator - @return string $db_type the database type as string
        \-- set_id_field
            \-- sql_creator - set the id field based on the table name of not overwritten
        \-- set_id_field_dummy
            \-- sql_creator - set a dummy id field for a union query
        \-- set_id_field_num_dummy
            \-- sql_creator - set a dummy id field for a union query
        \-- set_id_field_usr_dummy
            \-- sql_creator - set a dummy id field for a union query
        \-- set_par_list
            \-- sql_creator - set the complete par list e.g. for unition queries to get the parameters from the previous union part
        \-- par_list
            \-- sql_creator - set the complete par list e.g. for unition queries to get the parameters from the previous union part
        \-- par_values
            \-- sql_creator - set the complete par list e.g. for unition queries to get the parameters from the previous union part
        \-- table_id
            \-- sql_creator - get the preloaded table id for change log entries
        \-- 3
            \-- combine_named - @param string $name the name of the word, triple, formula or verb
        \-- name
            \-- element - @return string the element name to the user in the most simple form (without any ids)
            \-- combine_named - @return string|null the name of the word, triple, formula or verb
        \-- id
            \-- element - @return int the database id of the related object
        \-- trm_id
            \-- element - @return int the database id of the related object
        \-- term
            \-- element - @return int the database id of the related object
        \-- 6
            \-- data_object - the phrase list merged by the name, not the database id
        \-- term_list
            \-- element_list - add the element object
            \-- data_object - the term list merged by the name, not the database id
        \-- get
            \-- export - export zukunft.com data as object for creating e.g. a json message
        \-- set_description
            \-- combine_named - @param string|null $description the description of the word, triple, formula or verb
        \-- description
            \-- combine_named - @return string|null the description of the word, triple, formula or verb
        \-- set_type_id
            \-- combine_named - @param int|null $type_id the type id of the word, triple, formula or verb
        \-- set_share
            \-- combine_named - @param string|null $code_id the code id of the target share type or null to remove the parent overwrite
        \-- set_protection
            \-- combine_named - @param string|null $code_id the code id of the target protection or null to remove the parent overwrite
        \-- type_id
            \-- combine_named - @return int|null the type id of the word, triple, formula or verb
        \-- exclude
            \-- combine_named - set excluded to 'true' to switch off the usage of this named combine object
        \-- include
            \-- combine_named - set excluded to 'false' to switch on the usage of this user sandbox object
        \-- is_excluded
            \-- combine_named - @return bool true if the user does not want to use this object at all
        \-- is_exclusion_set
            \-- combine_named - @return bool true if the excluded field is set
        \-- set_plural
            \-- combine_named - @param string|null $plural the code id of the target protection or null to remove the parent overwrite
        \-- set_obj
            \-- combine_object - a combine object always covers an existing object
        \-- obj
            \-- combine_object - a combine object always covers an existing object
        \-- isset
            \-- combine_object - a combine object always covers an existing object
        \-- get_by
            \-- config_numbers - get a frontend config value selected by the phrase names
        \-- source_list
            \-- data_object - @return source_list with the sources of this data object
        \-- reference_list
            \-- data_object - @return ref_list with the references of this data object
        \-- value_list
            \-- data_object - @return value_list with the values of this data object
        \-- formula_list
            \-- data_object - @return formula_list with the formulas of this data object
        \-- formula_link_list
            \-- data_object - @return formula_link_list with the formula links of this data object
        \-- set_view_list
            \-- data_object - set the view_list of this data object
        \-- view_list
            \-- data_object - @return view_list with the views of this data object
        \-- component_list
            \-- data_object - @return component_list with the components of this data object
        \-- term_view_list
            \-- data_object - @return term_view_list with the list how the words, triples, verbs or formulas should be shown
        \-- user_list
            \-- data_object - @return user_list with the user of this data object
        \-- ip_range_list
            \-- data_object - @return ip_range_list with the ip ranges of this data object
        \-- view_relation_types
            \-- data_object - @return ip_range_list with the ip ranges of this data object
        \-- unique_value
            \-- db_id_object_non_sandbox - get the most relevant unique value of the object
        \-- set_from_api
            \-- db_id_object_non_sandbox - set the vars of this object based on json string from the frontend object
        \-- set_user
            \-- db_object_multi_user - set the user of the user sandbox object
            \-- db_object_seq_id_user - set the user of the user sandbox object
            \-- ref_list - set the user of the ref list
            \-- sandbox_list - set the user of the phrase list
            \-- user_service - set the user of the user sandbox service
            \-- ip_range_exp - set the user of the ip range if needed
            \-- sys_log - set the user of the error log
            \-- sys_log_list - set the user of the error log
            \-- view_sys_list - set the user of the phrase list
        \-- user
            \-- db_object_multi_user - @return user the person who wants to see a word, verb, triple, formula, view or result
            \-- db_object_seq_id_user - @return user the person who wants to see a word, verb, triple, formula, view or result
            \-- ref_list - @return user|null the person who wants to see the refs
            \-- sandbox_list - @return user the person who wants to see the phrases
            \-- user_service - @return user the person who wants to see a word, verb, triple, formula, view or result
            \-- ip_range_exp - @return user|null the person who uses the ip range and null if for all users
            \-- sys_log - @return user|null the person who wants to see the error log
            \-- sys_log_list - @return user|null the person who wants to see the error log
            \-- view_sys_list - @return user the person who wants to see the phrases
        \-- user_id
            \-- db_object_multi_user - @return int the id of the user or 0 if the user is not set
            \-- db_object_seq_id_user - @return int the id of the user or 0 if the user is not set
        \-- set_action
            \-- change_log - set the action of this change log object and to add a new action to the database if needed
        \-- action
            \-- change_log - get the action name base on the action_id
        \-- set_class
            \-- change_log - set the table of this change log object by the class name
        \-- set_table
            \-- change_log - set the table of this change log object and to add a new table to the database if needed
        \-- table
            \-- change_log - get the table name base on the table_id
        \-- set_field
            \-- change_log - set the field of this change log object and to add a new field to the database if needed
        \-- field
            \-- change_log - get the field name base on the field_id
        \-- set_time
            \-- change_log - get the field name base on the field_id
        \-- set_time_str
            \-- change_log - get the field name base on the field_id
        \-- time
            \-- change_log - get the field name base on the field_id
        \-- start_time
            \-- text_log - 
        \-- fill_missing_verbs
            \-- phrase_list - @return term_list filled with all phrases from this phrase list
        \-- name_pos_lst
            \-- phrase_list - @returns array with all unique names of this list with the keys within this list
        \-- key_list
            \-- ref_list - @return user|null the person who wants to see the refs
        \-- name_lst
            \-- sandbox_value_list - @return array with the value group names
        \-- set_value
            \-- value_geo - overwrite the sandbox_value set_value() function to set the geolocation value
            \-- value_text - overwrite the sandbox_value set_value() function to set the text string
            \-- value_time - overwrite the sandbox_value set_value() function to set the geolocation value
        \-- value
            \-- value_geo - overwrite the sandbox_value value() function to return the geolocation value
            \-- value_text - overwrite the sandbox_value value() function to return the text string
            \-- value_time - overwrite the sandbox_value value() function to return the DateTime value
        \-- set_text_value
            \-- value_text - accept only strings as value
    \-- basic interface function for the private class parameter
        \-- sql_par
            \-- sql_creator - create a sql parameter object with presets based on the class and a sql type list
        \-- set_class
            \-- sql_creator - define the table that should be used for the next select, insert, update or delete statement
        \-- set_name
            \-- sql_creator - set the query name for the prepared query
        \-- name
            \-- sql_creator - set the query name for the prepared query
        \-- set_usr
            \-- sql_creator - set the user id of the user who has requested the database access
        \-- set_fields
            \-- sql_creator - define the fields that should be returned in a select query
        \-- set_fields_dummy
            \-- sql_creator - define the fields that should be returned in a select query with dummy values for a union query
        \-- set_fields_num_dummy
            \-- sql_creator - define the fields that should be returned in a select query with dummy values for a union query
        \-- set_fields_date_dummy
            \-- sql_creator - define the fields that should be returned in a select query with dummy values for a union query
        \-- set_usr_query
            \-- sql_creator - activate that in the SQL statement the user sandbox name field should be included
        \-- set_join_sql
            \-- sql_creator - define a field that is taken from a complex sub query that is not yet created
        \-- set_grp_query
            \-- sql_creator - activate that in the SQL statement the user sandbox name field should be included
        \-- set_usr_fields
            \-- sql_creator - set the SQL statement for the user sandbox fields that should be returned in a select query
        \-- set_usr_num_fields
            \-- sql_creator - set the SQL statement for the numeric user sandbox fields that should be returned in a select query
        \-- set_usr_geo_fields
            \-- sql_creator - set the SQL statement for the geo point user sandbox fields that should be returned in a select query
        \-- set_usr_only_fields
            \-- sql_creator - set the SQL statement for the geo point user sandbox fields that should be returned in a select query
        \-- set_order_text
            \-- sql_creator - set the order SQL statement
        \-- set_join_fields
            \-- sql_creator - add a list of fields to the result that are taken from another table
        \-- set_join_usr_fields
            \-- sql_creator - similar to set_join_fields but for usr specific fields
        \-- set_join_usr_num_fields
            \-- sql_creator - similar to set_join_fields but for usr specific fields
        \-- set_join_usr_geo_fields
            \-- sql_creator - similar to set_join_fields but for usr specific fields
    \-- where
        \-- add_where_fvt
            \-- sql_creator - set the where condition based on a field. value and type list
        \-- add_where
            \-- sql_creator - add a where condition a list of id are one field or another
        \-- add_where_par
            \-- sql_creator - add the parameter for a where condition a list of id are one field or another
        \-- add_where_no_par
            \-- sql_creator - add the parameter for a where condition a list of id are one field or another
    \-- statement
        \-- sql
            \-- sql_creator - create a SQL select statement for the select database
        \-- create_sql_insert
            \-- sql_creator - create the sql statement to add a row to the database
        \-- create_sql_update
            \-- sql_creator - create the sql statement to update a row to the database
        \-- create_sql_update_fvt
            \-- sql_creator - create the sql statement to update a row to the database
        \-- sql_func_start
            \-- sql_creator - @return string that starts a sql function
        \-- sql_func_end
            \-- sql_creator - @return string that starts a sql function
        \-- sql_func_log
            \-- sql_creator - create the sql function part to log the insert changes
        \-- sql_func_log_update
            \-- sql_creator - create the sql function part to log the update changes
        \-- sql_func_log_link
            \-- sql_creator - create the sql function part to log adding a link
        \-- sql_func_log_user_link
            \-- sql_creator - create the sql function part to log adding a link
        \-- sql_func_log_value
            \-- sql_creator - create the sql function part to log adding or updating only a value in the database
        \-- sql_call
            \-- sql_creator - create the call statement for insert and update sql functions
        \-- var_name_row_id
            \-- sql_creator - create the sql function variable name for the db row id e.g. _word_id
        \-- var_name_new_id
            \-- sql_creator - create the sql function variable name for the new sequence db row id e.g. new_word_id
        \-- create_sql_delete_fvt
            \-- sql_creator - create a sql statement to delete or exclude a database row
        \-- create_sql_delete_fvt_new
            \-- sql_creator - create a sql statement to delete or exclude a database row
        \-- create_sql_delete
            \-- sql_creator - create a sql statement to delete or exclude a database row
        \-- add_usr_grp_field
            \-- sql_creator - define the fields that should be returned in a select query
    \-- internal where
        \-- get_order
            \-- sql_creator - get the order SQL statement
        \-- set_order
            \-- sql_creator - set the order SQL statement based on the given field name
        \-- set_page
            \-- sql_creator - set the limit and offset SQL statement for pagination
        \-- get_page
            \-- sql_creator - @return string the page statement that needs to be created after all other parameter have been requested
        \-- par_count
            \-- sql_creator - @return int the number of parameters used excluding constants
        \-- prepare_sql
            \-- sql_creator - warp the prepare clause around a given sql statement
        \-- table_create
            \-- sql_creator - generate a sql statement to create one database table
        \-- index_create
            \-- sql_creator - generate a sql statement to create the indices for one database table
        \-- foreign_key_create
            \-- sql_creator - generate a sql statement to create the foreign keys for one database table
        \-- sql_separator
            \-- sql_creator - @return string a sql separator just to improve formatting
        \-- sql_view_header
            \-- sql_creator - @return string a sql separator just to improve formatting
        \-- get_par
            \-- sql_creator - @return array with the parameter values in the same order as the given SQL parameter placeholders
        \-- get_par_types
            \-- sql_creator - @return array with the parameter values in the same order as the given SQL parameter placeholders
        \-- count_qp
            \-- sql_creator - create the SQL parameters to count the number of rows related to a database table type
        \-- count_sql
            \-- sql_creator - create a SQL select statement to count the number of rows related to a database table type
        \-- move_where_to_sub
            \-- sql_creator - remove the where condition at the given position $pos
        \-- select_by_id_not_owner
            \-- sql_creator - @return string the SQL statement to for the user specific data
    \-- public sql helpers
        \-- name_sql_esc
            \-- sql_creator - escape or reformat the reserved SQL names
        \-- get_id_field_name
            \-- sql_creator - get the database field name of the primary index of a database table
    \-- private sql helpers
        \-- get_table
            \-- sql_creator - @return string the name of the table as defined by set_table, so including the prefix and extension
    \-- for all tables some standard fields such as "word_name" are used
        \-- get_table_name
            \-- sql_creator - functions for the standard naming of tables
    \-- final exception correction of the table name
        \-- fix_table_name
            \-- sql_creator - final exception correction of the table name
        \-- par_name
            \-- sql_creator - get the SQL parameter placeholder in the used SQL dialect
        \-- par_value
            \-- sql_creator - get the SQL parameter value of a parameter position
        \-- par_type_to_postgres
            \-- sql_creator - convert one internal sql parameter type to a postgres db parameter type
        \-- load_sql_not_changed_multi
            \-- sql_creator - create a SQL select statement for the connected database
        \-- load_sql_not_changed
            \-- sql_creator - create a SQL select statement for the connected database
        \-- del_sql_list_without_log
            \-- sql_creator - create a sql statement to delete all rows that have one of the given ids
        \-- sql_all
            \-- sql_creator - return a sql statement to get all rows and all fields of the given class
    \-- user sandbox fields
        \-- get_usr_field
            \-- sql_creator - interface function for sql_usr_field
    \-- db roles
        \-- create_db_role
            \-- sql_creator - create the SQL statement to add a role to the database
    \-- internal helper
        \-- class_to_name
            \-- sql_creator - @param string $class where the namespace should be removed to get the db type name
        \-- is_user
            \-- sql_creator - @param array $tbl_types list of sql table types that specifies the current case
        \-- is_MySQL
            \-- sql_creator - @param array $tbl_types list of sql table types that specifies the current case
        \-- id_field
            \-- sql_creator - function that can be overwritten by the child object
        \-- id_field_name
            \-- sql_creator - @return string|array with the name of the id field
    \-- field, value and sql field type list
        \-- get_fields
            \-- sql_creator - @param array $fld_val_typ_lst an array with an array of the field name, value and the sql field type
        \-- get_values
            \-- sql_creator - @param array $fld_val_typ_lst an array with an array of the field name, value and the sql field type
        \-- get_types
            \-- sql_creator - @param array $fld_val_typ_lst an array with an array of the field name, value and the sql field type
    \-- info sql type
        \-- is_call_only
            \-- sql_type_list - @return bool true if only the sql function name should be created
        \-- is_insert
            \-- sql_type_list - @return bool true if an insert sql statement should be created
        \-- is_update
            \-- sql_type_list - @return bool true if an update sql statement should be created
        \-- is_delete
            \-- sql_type_list - @return bool true if a delete sql statement should be created
        \-- is_select
            \-- sql_type_list - @return bool true if a delete sql statement should be created
    \-- info for value table selection
        \-- is_most
            \-- sql_type_list - @return bool true to select the table for values and results linked to an average number of phrases
        \-- is_prime
            \-- sql_type_list - @return bool true to select the table for values and results linked to only a few fields
        \-- is_big
            \-- sql_type_list - @return bool true to select the table for values and results linked to many fields
        \-- value_table_type
            \-- sql_type_list - @return bool true to select the table for values and results linked to many fields
    \-- info for sql functions
        \-- is_usr_tbl
            \-- sql_type_list - @return bool true if sql should point to the user sandbox table
        \-- is_usr_tbl_and_select
            \-- sql_type_list - @return bool true if sql should point to the user sandbox table and only the values for the given user should be selected
        \-- is_norm
            \-- sql_type_list - @return bool true if sql should return the normal values and not the user specific
        \-- is_standard
            \-- sql_type_list - @return bool true if only the value without the sandbox parameters like share and protection should be saved
        \-- get_all
            \-- sql_type_list - @return bool true if sql return all database rows
        \-- is_geo
            \-- sql_type_list - @return bool true if sql uses geo point values
        \-- is_sub_tbl
            \-- sql_type_list - @return bool true if sql is supposed to be part of another sql statement
    \-- api
        \-- 1
            \-- combine_object - create the api json message string of this combine object for the frontend
        \-- api_json_array
            \-- element - create an array for the api json creation
            \-- combine_object - create an array for the json api message
            \-- language - create an array for the api json creation
            \-- change - create an array for the json api message
            \-- change_log - create an array for the api json creation
            \-- change_value - create an array for the json api message
            \-- ref_list - @return user|null the person who wants to see the refs
            \-- ref_type - TODO use parent function for setting the name, ...
            \-- sys_log - create the array for the api message
            \-- value_geo - create an array for the api json creation
            \-- value_text - create an array for the api json creation
            \-- value_time - create an array for the api json creation
        \-- api_json
            \-- data_object - create the api json message string of this data object that can be sent to the frontend
        \-- api_array
            \-- data_object - create an api json array for the backend based on this frontend object
        \-- save
            \-- sys_log - @param user_message $usr_msg the message object that is enriched in case something went wrong to show the user the problem and the suggested solutions
    \-- forward
        \-- include
            \-- element - create an SQL statement to retrieve a formula element by id from the database
        \-- exclude
            \-- element - create an SQL statement to retrieve a formula element by id from the database
        \-- is_excluded
            \-- element - create an SQL statement to retrieve a formula element by id from the database
        \-- is_word
            \-- element - create an SQL statement to retrieve a formula element by id from the database
        \-- is_triple
            \-- element - create an SQL statement to retrieve a formula element by id from the database
        \-- is_verb
            \-- element - create an SQL statement to retrieve a formula element by id from the database
        \-- is_formula
            \-- element - create an SQL statement to retrieve a formula element by id from the database
        \-- db_ready
            \-- element - @return user_message empty if all vars of the underlying object are set and the phrase can be stored in the database
    \-- sql write
        \-- sql_insert
            \-- element - create the sql statement to add an element to the database
            \-- change_log - create the sql statement to add a log entry to the database
        \-- sql_update
            \-- element - create the sql statement to update a word in the database
        \-- sql_type
            \-- change - @return sql_type the sql type of the change e.g. if a name is changes it returns sql_type::UPDATE
            \-- change_log - @return sql_type the sql type of the change e.g. if a value is changes it returns sql_type::UPDATE
            \-- change_value - @return sql_type the sql type of the change e.g. if a value is changes it returns sql_type::UPDATE
        \-- sql_sub_type
            \-- change - @return sql_type an addition sql type for the change e.g. if the phrase type is changed REF is added
            \-- change_log - @return sql_type an addition sql type for the change e.g. if the phrase type is changed REF is added
        \-- sql_insert_link
            \-- change_link - create the sql statement to add a log link entry to the database
            \-- change_log - dummy function overwritten by the child object
    \-- sql write fields
        \-- db_fields_all
            \-- element - get a list of all database fields that might be changed
        \-- db_fields_changed
            \-- element - get a list of database field names, values and types that have been updated
        \-- db_field_values_types
            \-- change - get a list of all database fields
            \-- change_log - get a list of all database fields
            \-- change_value - get a list of all database fields
            \-- change_value_geo - get a list of all database fields
            \-- change_value_text - get a list of all database fields
            \-- change_value_time - get a list of all database fields
        \-- 1
            \-- change_link - get a list of all database fields to log am link
        \-- db_fields
            \-- change - get a list of all database fields
            \-- change_link - get a list of all database fields
            \-- change_log - get a list of all database fields
            \-- change_value - get a list of all database fields
        \-- db_values
            \-- change - get a list of database field values that have been updated
            \-- change_link - get a list of database field values that have been updated
            \-- change_log - get a list of database field values that have been updated
            \-- change_value - get a list of database field values that have been updated
    \-- debug
        \-- 1
            \-- ip_range_exp - the helper class to im- and export an ip range filter
        \-- dsp_id
            \-- element - @return string best possible id for this element mainly used for debugging
            \-- element_group - @return string with the unique id fields
            \-- element_group_list - @return string to display the unique id fields
            \-- expression - @return string with the expression name to use it for debugging
            \-- group_list - @param term_list|null $trm_lst a cached list of terms
            \-- change - TODO move to the backend config class
            \-- change_link - @return string with the unique log entry description for debugging
            \-- change_log - @return string with the unique database id mainly for child dsp_id() functions
            \-- change_value - get a list of database field values that have been updated
            \-- sandbox_list - create a text that describes the list for unique identification
            \-- ip_range_exp - @return string to display the identifying ip range fields e.g. for a debug message
            \-- sys_log - @return string with the unique database id mainly for child dsp_id() functions
        \-- ids
            \-- element_group - @param ?int $limit the max number of ids to show
        \-- name
            \-- expression - @return string with the expression name to use it for debugging
            \-- group_list - create a useful (but not unique!) name of the phrase group list mainly used for debugging
            \-- change_value - @return string with the best possible id for this value mainly used for debugging
            \-- sandbox_list - to show the list name to the user in the most simple form (without any ids)
            \-- ip_range_exp - @return string with the unique name of the ip range
        \-- names
            \-- group_list - return a list of the word names
            \-- sandbox_list - @param bool $ignore_excluded if true also the excluded names are included
        \-- check
            \-- group_list - return a list of the word names
        \-- dsp_id_user
            \-- db_object_multi_user - @returns string best possible identification for this object mainly used for debugging
            \-- db_object_seq_id_user - @returns string best possible identification for this object mainly used for debugging
    \-- display
        \-- 2
            \-- phrase_list - @return string one string with all names of the list and reduced in size mainly for debugging
        \-- name
            \-- element_group - show the element group name to the user in the most simple form (without any ids)
            \-- phrase_list - @return string one string with all names of the list
        \-- id
            \-- element_group - show the element group name to the user in the most simple form (without any ids)
        \-- build_symbol
            \-- element_group - show the element group name to the user in the most simple form (without any ids)
        \-- figures
            \-- element_group - get a list of figures related to the formula element group and a context defined by a list of words
        \-- header
            \-- text_log - write a header text to the standard io
        \-- subheader
            \-- text_log - write a subheader text to the standard io
        \-- echo_log
            \-- text_log - write a test result text or a log entry text to the standard io
        \-- names
            \-- phrase_list - @return array with all phrase names in alphabetic order
        \-- does_contain
            \-- phrase_list - @return bool true if the phrase is part of the phrase list
        \-- get_by_ids
            \-- phrase_list - get the phrases of the list selected by the given ids
    \-- interface
        \-- phr_lst
            \-- expression - get the phrases that are user to calculate the expression result
        \-- terms
            \-- expression - get all terms used for this formula expression
        \-- result_phrases
            \-- expression - get the phrases that should be added to the result of a formula
        \-- is_valid
            \-- expression - get the phrases that should be added to the result of a formula
        \-- element_grp_lst
            \-- expression - a formula element group is a group of words, verbs, phrases or formula
        \-- element_list
            \-- expression - get a list of all formula elements
        \-- element_special_following
            \-- expression - list of elements (in this case only formulas) that are of the predefined type "following"
        \-- element_special_following_frm
            \-- expression - similar to element_special_following, but returns the formula and not the word
        \-- view_relation_types
            \-- system_object - load all system users that have a code id
        \-- view_relation_name
            \-- system_object - load all system users that have a code id
        \-- view_relation_code_id
            \-- system_object - load all system users that have a code id
        \-- system_users
            \-- system_object - load all system users that have a code id
        \-- switch
            \-- system_time_list - start the timing of set the user of the error log
        \-- report
            \-- system_time_list - @return string description of the execution times by category
        \-- section_report
            \-- system_time_list - @return string description of the execution times by category of the last section
    \-- internal
        \-- phr_id_lst
            \-- expression - @returns phr_ids with the word and triple ids from a given formula text
        \-- phr_id_lst_as_phr_lst
            \-- expression - @returns phrase_list with the word and triple ids from a given formula text
        \-- has_ref
            \-- expression - @returns bool true if the formula contains a word, verb or formula link
        \-- get_usr_names
            \-- expression - @return array of the term names used in the expression based on the user text
    \-- to review
        \-- phr_verb_lst
            \-- expression - similar to phr_lst, but
    \-- database link
        \-- get_id
            \-- group_id - @param phrase_list $phr_lst the list of phrases that define the value
            \-- result_id - @param phrase_list $phr_lst the list of phrases that define the result
        \-- max_number_of_phrase
            \-- group_id - get the max number if phrases for type of the given id
        \-- get_array
            \-- group_id - get the sorted array of phrase ids from the given group id
        \-- count
            \-- group_id - TODO use directly the phrase list without converting to a group id and back
        \-- table_extension
            \-- group_id - get the table name extension for value, result and group tables
        \-- table_type
            \-- group_id - get the table name extension for value, result and group tables
        \-- table_extension_list
            \-- group_id - @return array with the possible table extension
        \-- is_prime
            \-- group_id - @param int|string $grp_id
        \-- is_big
            \-- group_id - @param int|string $grp_id
        \-- int_array
            \-- group_id - @param int|string $grp_id
        \-- row_mapper
            \-- change_link - map the database fields to one change log entry to this log object
        \-- load_sql_by_user
            \-- change_link - TODO make sure that always a order is defined to allow page views
        \-- load_sql_by_vars
            \-- change_link - TODO make sure that always a order is defined to allow page views
        \-- load_last_by_user
            \-- change_link - get the last link changed by a user
        \-- add_link_ref
            \-- change_link - get the last link changed by a user
        \-- add_link
            \-- change_link - get the last link changed by a user
        \-- dsp_last
            \-- change_link - display the last change related to one object (word, formula, value, verb, ...)
        \-- add
            \-- change_link - similar to add_link, but additional fix the references as a text for fast displaying
        \-- add_ref
            \-- change_link - similar to add_link, but additional fix the references as a text for fast displaying
        \-- load_dummy
            \-- ref_type_list - adding the ref types used for unit tests to the dummy list
            \-- source_type_list - adding the source types used for unit tests to the dummy list
        \-- default_id
            \-- ref_type_list - return the database id of the default ref type
            \-- source_type_list - return the database id of the default source type
        \-- get_by_id
            \-- source_type_list - overwrite the user_type_list get function to be able to return the correct object
        \-- get_source_type
            \-- source_type_list - exception to get_type that returns an extended user_type object
        \-- get_source_type_id
            \-- source_type_list - exception to get_type that returns an extended user_type object
        \-- get_source_type_by_id
            \-- source_type_list - exception to get_type that returns an extended user_type object
    \-- add
        \-- add
            \-- group_list - add a phrase group if it is not yet part of the list
        \-- get_by_val_with_one_phr_each
            \-- group_list - query to get the value or formula result phrase groups and time words that contains at least one phrase of two lists based on the user sandbox
        \-- get_by_val_special
            \-- group_list - query to get the value or formula result phrase groups and time words that contains at least one phrase of two lists based on the user sandbox
        \-- get_by_res_with_one_phr_each
            \-- group_list - query to get the value or formula result phrase groups and time words that contains at least one phrase of two lists based on the user sandbox
        \-- get_by_res_special
            \-- group_list - query to get the value or formula result phrase groups and time words that contains at least one phrase of two lists based on the user sandbox
        \-- remove_wrd_lst
            \-- group_list - query to get the value or formula result phrase groups and time words that contains at least one phrase of two lists based on the user sandbox
    \-- SQL creation
        \-- sql_view
            \-- combine_named - @return string the SQL script to create the views
        \-- sql_create_view
            \-- combine_named - @return string the SQL script to create the views
        \-- sql_view_link
            \-- change_table_field - @return string the SQL script to create the views
    \-- default
        \-- default_json
            \-- config_numbers - load the system configuration values that the user can change
    \-- predefined
        \-- language
            \-- config_numbers - @return string the code_id of the user frontend language
    \-- get
        \-- get_object_by_name
            \-- data_object - @return ip_range_list with the ip ranges of this data object
        \-- get_word_by_name
            \-- data_object - get a word by the name from this cache object
        \-- get_phrase_by_id
            \-- data_object - get a phrase by the id from this cache object
        \-- get_phrase_by_name
            \-- data_object - get a word or triple by the name from this cache object
        \-- get_source_by_name
            \-- data_object - get a source by the name from this cache object
        \-- get_formula_by_id
            \-- data_object - get a formula by the id from this cache object
        \-- get_formula_by_name
            \-- data_object - get a formula by the name from this cache object
        \-- get_term_by_name
            \-- data_object - get a word, verb, triple or formula by the name from this cache object
        \-- get_view_by_name
            \-- data_object - get a source by the name from this cache object
    \-- settings
        \-- is_value_obj
            \-- db_id_object_non_sandbox - @return bool true if this sandbox object is a value or result
        \-- is_link_type_obj
            \-- sandbox_predicated_link - @return bool true because all child objects use the link type
    \-- sql
        \-- sql_delete
            \-- db_id_object_non_sandbox - create the sql statement to delete or exclude a named sandbox object e.g. word to the database
        \-- load_sql_like
            \-- phrase_list - create an SQL statement to retrieve a list of phrase objects
        \-- load_sql_by_names
            \-- phrase_list - create an SQL statement to retrieve a list of phrase objects by the name from the database
        \-- load_sql_by_ids
            \-- phrase_list - create an SQL statement to retrieve a list of phrase objects by the id from the database
        \-- load_sql
            \-- phrase_list - set the SQL query parameters to load a list of phrase objects
        \-- load_names_sql_by_ids
            \-- phrase_list - create an SQL statement to retrieve a list of phrase names by the id from the database
        \-- load_sql_by_phr_lst
            \-- phrase_list - set the SQL query parameters to load a list of phrase by a phrase list, verb and direction
        \-- load_names_by_ids
            \-- phrase_list - load the phrase names by the given id list from the database
        \-- load_names
            \-- phrase_list - load a list of phrase names
    \-- overwrite
        \-- load_by_ip
            \-- db_id_object_non_sandbox - the common part of the sql_insert, sql_update and sql_delete functions
        \-- load_by_email
            \-- db_id_object_non_sandbox - the common part of the sql_insert, sql_update and sql_delete functions
        \-- key_field
            \-- db_id_object_non_sandbox - the common part of the sql_insert, sql_update and sql_delete functions
        \-- import_mapper_user
            \-- db_id_object_non_sandbox - the common part of the sql_insert, sql_update and sql_delete functions
    \-- sql create
        \-- sql_table_create
            \-- db_object - the sql statement to create the table for this (or a child) object
            \-- db_object_key - the sql statement to create the table for this (or a child) object
            \-- db_object_no_id - the sql statement to create the table for this (or a child) object
        \-- sql_truncate_create
            \-- db_object - the name of the sql table for this (or a child) object
            \-- db_object_key - the name of the sql table for this (or a child) object
            \-- db_object_no_id - the name of the sql table for this (or a child) object
        \-- sql_index_create
            \-- db_object - the sql statement to create the indices for this (or a child) object
            \-- db_object_key - the sql statement to create the indices for this (or a child) object
            \-- db_object_no_id - the sql statement to create the indices for this (or a child) object
        \-- sql_foreign_key_create
            \-- db_object - the sql statement to create the foreign keys for this (or a child) object
            \-- db_object_key - the sql statement to create the foreign keys for this (or a child) object
            \-- db_object_no_id - the sql statement to create the foreign keys for this (or a child) object
        \-- sql_table
            \-- change_log - the sql statements to create a change log table
            \-- sys_log - the sql statement to create the tables of a system log table
            \-- value_ts_data - the sql statement to create the table
        \-- sql_index
            \-- change_log - the sql statements to create all indices for a change log table
            \-- sys_log - the sql statement to create the database indices of a system log table
            \-- value_ts_data - the sql statement to create the database indices
        \-- sql_foreign_key
            \-- change_log - the sql statements to create all foreign keys for a change log table
            \-- sys_log - the sql statements to create all foreign keys of a system log table
            \-- value_ts_data - the sql statements to create all foreign keys
    \-- use to apply the time of the parent process for continuous timestamp reporting
        \-- set_start_time
            \-- import - 
            \-- import_file - 
        \-- display_progress
            \-- import - show the progress of an import process
        \-- put_yaml
            \-- import - drop a zukunft.com yaml object to the database
        \-- put_json
            \-- import - drop a zukunft.com json message object to the database
        \-- put_json_direct
            \-- import - drop a zukunft.com json message direct to the database
        \-- get_data_object
            \-- import - create a data object based on a json zukunft.com import array
        \-- step_main_start
            \-- import - start a group of steps and remember the estimated time
        \-- step_main_end
            \-- import - remember how much is done by adding the estimated time done
        \-- step_start
            \-- import - remember how much is done by adding the estimated time done
        \-- step_end
            \-- import - @param int $nbr the number of precessed objects e.g. count(word)
        \-- end
            \-- import - calc the import times and show the result to the user
        \-- get_data_object_yaml
            \-- import - create a data object based on a yaml zukunft.com import array
        \-- yaml_data_object_map_triple
            \-- import - @param string $key
        \-- status_text
            \-- import - @param string $key
        \-- summary
            \-- import - @param string $key
        \-- seq_id
            \-- import - @return int the next dummy id for unit testing
        \-- json_file
            \-- import_file - import a single json file
        \-- yaml_file
            \-- import_file - import a single yaml file
        \-- import_config_yaml
            \-- import_file - import the initial system configuration
        \-- import_base_config
            \-- import_file - TODO move HTML code to frontend
        \-- import_pod_config
            \-- import_file - import the default pod base configuration json files
        \-- import_test_config
            \-- import_file - import the default pod base configuration json files
        \-- echo
            \-- import_file - display a message immediately to the user
    \-- format
        \-- dsp
            \-- change - @return string the current change as a human-readable text
        \-- date_time_format
            \-- change - TODO move to the backend config class
    \-- init
        \-- create_log_references
            \-- change_log - create the change log references (tables, fields and actions)
    \-- load internals
        \-- load_sql_obj_fld
            \-- change_log_list - prepare sql to get the changes of one field of one user sandbox object
        \-- load_sql_obj_last
            \-- change_log_list - prepare sql to get the last changes of a user sandbox object
        \-- load_sql_by_type
            \-- job_list - prepare sql to get all open job of one type
    \-- cast
        \-- term_list
            \-- phrase_list - @return term_list filled with all phrases from this phrase list
    \-- check
        \-- is_valid
            \-- phrase_list - @return bool true if this phrase list has at least one entry
        \-- same_user
            \-- sandbox_list - check if the user of the object to add matches the user of the list
        \-- includes
            \-- ip_range_exp - check if an ip address is within this range
    \-- data request function
        \-- val_lst
            \-- phrase_list - @return value_list all values related to this phrase list
        \-- frm_lst
            \-- phrase_list - @return formula_list all formulas related to this phrase list
        \-- value
            \-- phrase_list - get the best matching value or value list for this phrase list
        \-- value_scaled
            \-- phrase_list - @return value the best matching value scaled to one
    \-- review - to be moved to the sql creator
        \-- load_by_phr
            \-- phrase_list - load a list of phrases by a given phrase, verb and direction
        \-- load_by_phr_vrb_and_type
            \-- phrase_list - load the related phrases of a given type
    \-- if ($pos > 0) {
        \-- load_sql_linked_phrases
            \-- phrase_list - create the sql statement to select the related phrases
        \-- wrd_lst_all
            \-- phrase_list - build a word list including the triple words or in other words flatten the list e.g. for parent inclusions
        \-- words
            \-- phrase_list - get a word list from the phrase list
        \-- triples
            \-- phrase_list - get a triple list from the phrase list
        \-- triples_by_name
            \-- phrase_list - get a triple list from the phrase list
    \-- extract
        \-- ids
            \-- ref_list - @retur array the list of the ref ids
    \-- sql_type_list
        \-- table_extension
            \-- sandbox_list - @param array $tbl_types list of sql table types that specifies the current case
    \-- preloaded
        \-- predicate_name
            \-- sandbox_predicated_link - dummy function that should be overwritten by the child object
        \-- status_name
            \-- sys_log - get the name of the system log entry status
    \-- object vars
        \-- __construct
            \-- user_service - set the user that has requested service process
    \-- loading
        \-- load_sql
            \-- ip_range_exp - create the common part of an SQL statement to retrieve an ip range from the database
        \-- load_sql_by_vars
            \-- ip_range_exp - create an SQL statement to retrieve the ip range from the database
        \-- load_by_id
            \-- ip_range_exp - load an ip range from the database selected by id
        \-- load_sql_obj_vars
            \-- ip_range_list - create an SQL statement to retrieve the all active ip ranges from the database
        \-- load
            \-- ip_range_list - load the active ip ranges
    \-- using ip range list
        \-- includes
            \-- ip_range_list - checks if the given ip range is within any of the ip range of this list
    \-- load interface
        \-- load_by_type
            \-- job_list - load a list of batch jobs of the given type
    \-- loading / database access object (DAO) functions
        \-- load_sql
            \-- sys_log_list - create the SQL statement to load a list of system log entries
        \-- load
            \-- sys_log_list - load a list of system errors from the database
        \-- load_all
            \-- sys_log_list - load a list of all system errors from the database
        \-- add
            \-- sys_log_list - simple add another system log entry to the list
    \-- log
        \-- log_object
            \-- value_geo - @return change_value_geo the object that is used to log the user changes
            \-- value_text - @return change_value_text the object that is used to log the user changes
            \-- value_time - @return change_value_time the object that is used to log the user changes
    \-- database load functions that reads the object from the database
        \-- row_mapper_sandbox
            \-- value_time_series - map the database fields to the object fields
        \-- load_standard_sql
            \-- value_time_series - create the SQL to load the default time series always by the id
        \-- load_sql
            \-- value_time_series - create the common part of an SQL statement to retrieve the parameters of a time series from the database
        \-- load_standard
            \-- value_time_series - load the standard value use by most users
        \-- load_sql_multi
            \-- value_time_series - create the common part of an SQL statement to retrieve the parameters of a value time series
        \-- load_sql_by_grp
            \-- value_time_series - create an SQL statement to retrieve a time series by the phrase group from the database
        \-- load_by_id
            \-- value_time_series - just set the class name for the user sandbox function
        \-- load_by_grp
            \-- value_time_series - load a row from the database selected by id
        \-- add
            \-- value_time_series - add a new time series
    \-- write
        \-- save
            \-- value_time_series - insert or update a time series in the database or save user specific time series numbers
```
