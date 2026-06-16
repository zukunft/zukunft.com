Deployment
----------

To fix a bug or add a feature to this project follow these steps

1. create an issue e.g. that should at least contain a definition of done (DoD)
2. create a branch for this issue that starts with "bigfix/" or "feature/" the issue number and the description e.g. "feature/72-system-views"
3. use this branch to do the code changes
4. make sure that all tests are running fine before the commit
5. request a review and if it is fine create a pull request to the development branch
6. once the change is in the development branch the [build process](build_process.md) will start the automatic full test and the deployment process towards production

## testing

The recommended steps to test any code changes are

1. run /test/test_unit.php to check if all unit tests are fine
2. if a test fails, try to fix it and rerun test_unit
3. if all unit tests are fine and something on the database has changed run /test/reset_db.php to update the database and prepare for the db read tests
4. if this also fails, try to fix the issue and worst case try /test/reset_db_forced.php to rebuild the database without any parameters from the database
5. if the database update is fine use rsync to move the updates to http://localhost/ for the api tests
6. run /test/test.php to perform all tests required for deployment 
7. in case of errors fix them and repeat step 6 or worst case even step 1
8. if /test/test.php is fine the commit can be done
9. to fill the local server with all data run /test/test_full_load.php

### additional line in src/main/resources/db_code_links

if an additional entry in any of the files in src/main/resources/db_code_links is done run test/reset_db.php at least once and update src/test/resources/api/ui_config/ui_config.json and src/test/resources/api/type_lists/type_lists.json based on the difference reported in the run to refresh the preloaded type list. After that refresh the local deployment to update the api tests. 