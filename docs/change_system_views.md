Change a system view
--------------------

to change the system views these steps are needed at the moment:

1) add, change or remove component in the view (e.g. "name": "Change word") in the src/main/resources/messages/system_views.json
2) update the position numbers in components if needed because json does not have a fixed order
3) activate the line 86 ( $t->type_list_recreate($t); ) in test/reset_db.php so that the test/resources/api/type_lists/type_lists.json file is checked and can be updated
4) run test/reset_db.php to update the database and update the test/resources/api/type_lists/type_lists.json with the result created based on the updated database 
5) undo the removal of "7weight" and "8weight" (this is probably due to a bug in the system)
6) deploy the changes to your local test host
7) if test/reset_db.php runs without error (beside the two missing lines in type_lists) deactivate the line 86 ( $t->type_list_recreate($t); ) in test/reset_db.php
8) run test/test.php and update the expected results of the test case where the changes are expected changes
9) repeat the deployment of the changes to your local test host and step 8 until the test runs without error
10) git commit & git push

add component type
------------------

if a component type needs to be added the steps are

1) add the component type in src/main/resources/db_code_links/component_types.csv by adding a new line at the end
2) add the related const in src/main/php/shared/types/component_type.php
3) if the component is a system component and user should not be able to use the component and it to "const array SYSTEM_TYPES"
4) add the const with the ID to "const array TEST_TYPES" to include the component to the set of test components
5) add the call of the corresponding function in src/main/php/web/component/component_exe.php
6) continue with step 3) from the steps above to change a system view

add a translatable frontend text
--------------------------------

if the new component used an ui_msg_code_id perform these steps

1) add a related case in system_sub_title_usage
2) add at least the English text in src/main/resources/translations/en.yaml
3) continue with step 3) from the steps above to change a system view
