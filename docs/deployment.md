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

### use case pages and their samples

A use case page, e.g. src/test/resources/web/html/use_cases/calculator_editor.html, is
written by the test run and checked only against its own last version: if the page has
changed, the test fails and you decide if the new page is right. The hand made target
next to it, e.g. calculator_editor_sample.html, is never checked by a test; compare the
two pages yourself in a browser, unless the page has the sample setup of the table below.

| sample | generated pages | generated from | diff report | input cases | diff report of the cases |
|---|---|---|---|---|---|
| pv_lifetime_balance_sample.html | pv_lifetime_balance_no_js.html, pv_lifetime_balance_js.html | use_cases/pv_lifetime_balance.json, calculator files | pv_lifetime_balance_diff.md | standard, high, low | pv_lifetime_balance_diff_standard.md, _high.md, _low.md |
| portfolio_split_calculator_sample.html | portfolio_split_calculator_no_js.html, portfolio_split_calculator_js.html | use_cases/portfolio_split_calculator.json, calculator files | portfolio_split_calculator_diff.md | standard, high, low | portfolio_split_calculator_diff_standard.md, _high.md, _low.md |
| CBAM_tree_calculator_sample.html | CBAM_tree_calculator.html | use_cases/cbam_calculator.json with its view CBAM Part Tree Calculator | none yet | none yet | none yet |
| ahv_vat_vote_sample.html | ahv_vat_vote.html, ahv_vat_vote_single.html (no test writes them yet) | use_cases/ahv_vat_vote.json, calculator files | none yet | none yet | none yet |
| npp_ch_vote_sample.html | npp_ch_vote.html (no test writes it yet) | use_cases/npp_ch_vote.json, calculator files | none yet | none yet | none yet |
| share_buy_sell_abb_sample.html | share_buy_sell_abb.html (no test writes it yet) | use_cases/share_buy_sell_abb.json, calculator files | none yet | none yet | none yet |
| calculator_editor_sample.html | calculator_editor.html | use_cases/pv_lifetime_balance.json, editor_views.json, calculator_views.json | none yet | none yet | none yet |
| formula_editor_sample.html | formula_editor.html | use_cases/pv_lifetime_balance.json, calculator_views.json | none yet | none yet | none yet |

The json files are in src/main/resources/messages/. The calculator files are calculator_keywords.json,
calculator_views.json and the setting "use javascript" "for calculator" of src/main/resources/config.yaml,
which the table of the PV lifetime balance below explains.

To add the sample setup for a use case, in system_views_read_tests and test_files:

1. write the page without javascript and the page of the browser with assert_view_by_name, like run_portfolio_split_calculator
2. report the differences of the server page to the sample with assert_sample_diff, like run_portfolio_split_calculator_diff; a sample whose script fills its tables gets its cases script, so that it is compared as a browser shows it, and a case `unchanged` without an input
3. add the input cases in src/test/js/`<name>_cases.js`, which use_case_cases.js types into the sample and the page of the browser, and report them with assert_sample_case, like run_portfolio_split_calculator_cases
4. add the run functions to system_views_read_tests::run and this table

assert_sample_case with $all_fields false lists a field that only one page has in the report; set it to true when both pages have the same fields, so that a lost field fails the test.
A new diff report starts with the text of the first run, which the developer accepts as the baseline.

The PV lifetime balance has these files in src/test/resources/web/html/use_cases/ and, with their
full path, the files with the data and the configuration that the html pages are generated from:

| file | made by | used for |
|---|---|---|
| pv_lifetime_balance_sample.html | hand | the target layout of the calculator page: a stand-alone mock up with its own script, which the generated pages should come close to |
| pv_lifetime_balance_no_js.html | test run | the calculator page as the server renders it without javascript (`sc=0`), which every browser shows and which the save of the inputs uses |
| pv_lifetime_balance_diff.md | test run | what pv_lifetime_balance_no_js.html misses, shows differently, has in addition or shows in another order compared with the sample, as a markdown text that can be given to an llm as the prompt to bring the generated page closer to the sample |
| pv_lifetime_balance_js.html | test run | the same page as the browser builds it by default from the phraseData answer with calculator_json.js, which recalculates the results while an input changes |
| pv_lifetime_balance_diff_standard.md, pv_lifetime_balance_diff_high.md, pv_lifetime_balance_diff_low.md | test run | what pv_lifetime_balance_js.html shows differently from the sample after the input case of the file name is entered in both pages in a headless browser, so it also compares the recalculated numbers |
| src/main/resources/messages/use_cases/pv_lifetime_balance.json | hand | the data and the configuration of the page: the words, triples, values and formulas of the example PV installation, the inputs, assumptions, labels and hints as calculator triples and the expected results; a change reaches the pages after an import of the file |
| src/main/resources/messages/calculator_keywords.json | hand | the system phrases that define the parts of a calculator page e.g. calculator input and calculator hint, which the triples of the use case link to |
| src/main/resources/messages/calculator_views.json | hand | the calculator view with its three components for the title, the inputs and the results, the same for every phrase |
| src/main/resources/config.yaml | hand | the setting "use javascript" "for calculator", which makes the page of the browser the default |

The two generated pages are written by system_views_read_tests::run_pv_lifetime_balance from
the system view Calculator for the phrase example PV installation and checked against their
last version like every use case page, so the diff of a file shows what a code or data
change did to the page. The two generated pages should show the same
content, so a difference between them points to a rule that calculator.php and
calculator_json.js implement differently.

The diff report is written by system_views_read_tests::run_pv_lifetime_balance_diff right
after the page (src/test/php/utils/html_page_diff.php): it compares what a reader sees, the
title, the headings, the fields with their unit, hint and value, the table rows with their
cells, the buttons and the notes and sources, and never the markup of the two files.
The reports of the input cases are written by system_views_read_tests::run_pv_lifetime_balance_cases
with the same comparison: pv_lifetime_balance_diff.md is about the server page with the stored
numbers, whereas pv_lifetime_balance_diff_standard.md is about the page of the browser, also
with the default inputs, so a finding that is only in one of them points to the page it names.
Both load the generated page from its snapshot, so a changed use case json shows in the reports
only after the snapshot has been written again.

Compare the sample with pv_lifetime_balance_no_js.html, because only that page has the
inputs, the labels, the units and the result rows as html like the sample;
pv_lifetime_balance_js.html holds only the json data that the browser builds the page from,
so a diff against it shows nothing of the layout. The numbers of the sample come from its
own script, so they can differ from the stored values and the formula results of the
generated pages.

## local web server for the api tests and the html snapshots

The api tests and the html snapshot pages use a local web server on http://localhost/
that serves the rsync copy of the repo in /var/www/html (see the testing steps above).
For this host apache two one-time setup steps are needed:

1. allow the .htaccess files of the repo to take effect, because the debian default
   for /var/www is `AllowOverride None`, which silently ignores every .htaccess -
   with it the security allow-list of the root .htaccess (e.g. the 404 for
   /src/main/php/) and the CORS header of the web fonts (see below):

   add inside the `<VirtualHost>` of /etc/apache2/sites-enabled/000-default.conf
   ```
   <Directory /var/www/html>
       AllowOverride All
   </Directory>
   ```

2. enable the headers module, which the font CORS header needs
   (the docker image enables it in docker/Dockerfile):
   ```
   sudo a2enmod headers
   sudo systemctl reload apache2
   ```

To verify both, the font request must return the CORS header:
```
curl -sI http://localhost/external_lib/fontawesome/webfonts/fa-solid-900.woff2 | grep -i access-control
```

### why the web fonts need a CORS header

A html snapshot page (e.g. src/test/resources/web/html/object_pages/value.html) can be
opened without a pod, e.g. from the file system or the ide preview server on another
port. The page loads its stylesheets from http://localhost/ (the THIS_URL fallback),
which works cross-origin, but the fonts named by the font awesome css are fetched by
the browser in CORS mode, because a @font-face request always is. Without the header
the font is blocked, and an icon (e.g. the add / edit / del buttons) stays invisible,
because a font awesome icon is only a glyph of that font. external_lib/.htaccess
therefore allows any origin for the font files of the folder - public third party
files, read without credentials, so nothing can leak.

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

