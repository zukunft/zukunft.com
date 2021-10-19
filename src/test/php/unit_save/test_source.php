<?php

/*

    test_source.php - TESTing of the SOURCE class
    ---------------

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
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2021 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com


*/

function create_test_sources()
{

    test_header('Check if all base sources are exist');

    test_source(source::TN_READ);

}

function run_source_test()
{

    global $usr1;
    global $usr2;

    test_header('Test the source class (src/main/php/model/ref/source.php)');

    // load the main test source
    $src_read = test_word(source::TN_READ);

    // check if loading a source by name and id works
    $src_by_name = new source;
    $src_by_name->name = source::TN_READ;
    $src_by_name->usr = $usr1;
    $src_by_name->load();
    $src_by_id = new source;
    $src_by_id->id = $src_by_name->id;
    $src_by_id->usr = $usr1;
    $src_by_id->load();
    $target = source::TN_READ;
    $result = $src_by_id->name;
    test_dsp('source->load of ' . $src_read->id . ' by id ' . $src_by_name->id, $target, $result);

    // test the creation of a new source
    $src_add = new source;
    $src_add->name = source::TN_ADD;
    $src_add->usr = $usr1;
    $result = $src_add->save();
    $target = '';
    test_dsp('source->save for "' . source::TN_ADD . '"', $target, $result, TIMEOUT_LIMIT_DB);

    // ... check if the source creation has been logged
    if ($src_add->id > 0) {
        $log = new user_log;
        $log->table = 'sources';
        $log->field = 'source_name';
        $log->row_id = $src_add->id;
        $log->usr = $usr1;
        $result = $log->dsp_last(true);
    }
    $target = 'zukunft.com system test added ' . source::TN_ADD . '';
    test_dsp('source->save logged for "' . source::TN_ADD . '"', $target, $result);

    // ... test if the new source has been created
    $src_added = load_source(source::TN_ADD);
    $src_added->load();
    if ($src_added->id > 0) {
        $result = $src_added->name;
    }
    $target = source::TN_ADD;
    test_dsp('source->load of added source "' . source::TN_ADD . '"', $target, $result);

    // check if the source can be renamed
    $src_added->name = source::TN_RENAMED;
    $result = $src_added->save();
    $target = '';
    test_dsp('source->save rename "' . source::TN_ADD . '" to "' . source::TN_RENAMED . '".', $target, $result, TIMEOUT_LIMIT_DB);

    // check if the source renaming was successful
    $src_renamed = new source;
    $src_renamed->name = source::TN_RENAMED;
    $src_renamed->usr = $usr1;
    if ($src_renamed->load()) {
        if ($src_renamed->id > 0) {
            $result = $src_renamed->name;
        }
    }
    $target = source::TN_RENAMED;
    test_dsp('source->load renamed source "' . source::TN_RENAMED . '"', $target, $result);

    // check if the source renaming has been logged
    $log = new user_log;
    $log->table = 'sources';
    $log->field = 'source_name';
    $log->row_id = $src_renamed->id;
    $log->usr = $usr1;
    $result = $log->dsp_last(true);
    $target = 'zukunft.com system test changed ' . source::TN_ADD . ' to ' . source::TN_RENAMED . '';
    test_dsp('source->save rename logged for "' . source::TN_RENAMED . '"', $target, $result);

    // check if the source parameters can be added
    $src_renamed->url = source::TEST_URL;
    $src_renamed->comment = source::TEST_DESCRIPTION;
    $result = $src_renamed->save();
    $target = '';
    test_dsp('source->save all source fields beside the name for "' . source::TN_RENAMED . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

    // check if the source parameters have been added
    $src_reloaded = load_source(source::TN_RENAMED);
    $result = $src_reloaded->url;
    $target = source::TEST_URL;
    test_dsp('source->load url for "' . source::TN_RENAMED . '"', $target, $result);
    $result = $src_reloaded->comment;
    $target = source::TEST_DESCRIPTION;
    test_dsp('source->load description for "' . source::TN_RENAMED . '"', $target, $result);

    // check if the source parameter adding have been logged
    $log = new user_log;
    $log->table = 'sources';
    $log->field = 'url';
    $log->row_id = $src_reloaded->id;
    $log->usr = $usr1;
    $result = $log->dsp_last(true);
    //$target = 'zukunft.com system test added ' . source::TEST_URL;
    $target = 'zukunft.com system test partner changed ' . source::TEST_URL_CHANGED . ' to ' . source::TEST_URL;
    test_dsp('source->load url for "' . source::TN_RENAMED . '" logged', $target, $result);
    $log->field = 'comment';
    $result = $log->dsp_last(true);
    //$target = 'zukunft.com system test added ' . source::TEST_DESCRIPTION;
    $target = 'zukunft.com system test partner changed System Test Source Description Changed to System Test Source Description';
    test_dsp('source->load description for "' . source::TN_RENAMED . '" logged', $target, $result);

    // check if a user specific source is created if another user changes the source
    $src_usr2 = new source;
    $src_usr2->name = source::TN_RENAMED;
    $src_usr2->usr = $usr2;
    $src_usr2->load();
    $src_usr2->url = source::TEST_URL_CHANGED;
    $src_usr2->comment = source::TEST_DESCRIPTION_CHANGED;
    $result = $src_usr2->save();
    $target = '';
    test_dsp('source->save all source fields for user 2 beside the name for "' . source::TN_RENAMED . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

    // check if a user specific source changes have been saved
    $src_usr2_reloaded = new source;
    $src_usr2_reloaded->name = source::TN_RENAMED;
    $src_usr2_reloaded->usr = $usr2;
    $src_usr2_reloaded->load();
    $result = $src_usr2_reloaded->url;
    $target = source::TEST_URL_CHANGED;
    test_dsp('source->load url for "' . source::TN_RENAMED . '"', $target, $result);
    $result = $src_usr2_reloaded->comment;
    $target = source::TEST_DESCRIPTION_CHANGED;
    test_dsp('source->load description for "' . source::TN_RENAMED . '"', $target, $result);

    // check the source for the original user remains unchanged
    $src_reloaded = load_source(source::TN_RENAMED);
    $result = $src_reloaded->url;
    $target = source::TEST_URL;
    test_dsp('source->load url for "' . source::TN_RENAMED . '" unchanged for user 1', $target, $result);
    $result = $src_reloaded->comment;
    $target = source::TEST_DESCRIPTION;
    test_dsp('source->load description for "' . source::TN_RENAMED . '" unchanged for user 1', $target, $result);

    // check if undo all specific changes removes the user source
    $src_usr2 = new source;
    $src_usr2->name = source::TN_RENAMED;
    $src_usr2->usr = $usr2;
    $src_usr2->load();
    $src_usr2->url = source::TEST_URL;
    $src_usr2->comment = source::TEST_DESCRIPTION;
    $result = $src_usr2->save();
    $target = '';
    test_dsp('source->save undo the user source fields beside the name for "' . source::TN_RENAMED . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

    // check if a user specific source changes have been saved
    $src_usr2_reloaded = new source;
    $src_usr2_reloaded->name = source::TN_RENAMED;
    $src_usr2_reloaded->usr = $usr2;
    $src_usr2_reloaded->load();
    $result = $src_usr2_reloaded->url;
    $target = source::TEST_URL;
    test_dsp('source->load url for "' . source::TN_RENAMED . '" unchanged now also for user 2', $target, $result);
    $result = $src_usr2_reloaded->comment;
    $target = source::TEST_DESCRIPTION;
    test_dsp('source->load description for "' . source::TN_RENAMED . '" unchanged now also for user 2', $target, $result);

    // TODO create and check the display functions

}