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

## system db row changes

if the system configuration is adjusted that is saved in the database e.g. a change in src/main/resources/messages/system_views.json or src/main/resources/db_code_links/component_types.csv
the first step is to update the database, so 

1. run /test/reset_db.php and confirm the file changes if they are fine to update the database and the fixed csv data snaps like src/test/resources/unit/component/list.csv
2. update the local www files using a rsync command like 'rsync -av --delete /home/timon/PhpstormProjects/zukunft.com/ /var/www/html' to fix the local api tests
3. run /test/test.php and update the test files if needed to finish the test

## backend job scheduler

zukunft.com runs periodic backend jobs (a proactive cache refresh sweep and a database cleanup) without a user interaction. The dispatcher entry point is `bin/job_runner.php`; it refuses to run via a web request and executes the due jobs as the system user.

On a debian install `install.sh` sets up a systemd service and timer automatically (the unit files live in `deploy/systemd/`, the timer fires the dispatcher every minute). Reactive cache updates triggered by a single user request are handled in the request itself and are intentionally not part of the scheduler.

### check the job status

```bash
systemctl status zukunft-jobs.timer        # is the timer active and when is the next run
systemctl list-timers zukunft-jobs.timer   # last and next elapse
journalctl -u zukunft-jobs                 # the start, end and errors of the past job runs
journalctl -u zukunft-jobs -f              # follow the output of the next run live
```

### run the dispatcher once by hand

```bash
sudo -u www-data php /var/www/html/bin/job_runner.php   # use your WEB_USER and WWW_ROOT
```

### disable and remove the scheduler

```bash
systemctl disable --now zukunft-jobs.timer
rm -f /etc/systemd/system/zukunft-jobs.service /etc/systemd/system/zukunft-jobs.timer
systemctl daemon-reload
```

### add or change a job type

The job types are defined in `src/main/php/shared/types/job_types.php` and seeded from `src/main/resources/db_code_links/job_types.csv`, so adding one follows the "additional line in src/main/resources/db_code_links" step above (run `reset_db.php` and refresh the `ui_config.json` and `type_lists.json` fixtures). Each job type is a class in `src/main/php/cfg/system/` that implements the `job_exe` interface (see `job_cache_refresh` and `job_db_cleanup`); dispatch by the job type code id happens in `job->exe()`.

