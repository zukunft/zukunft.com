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
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/agpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com


*/

use api\source_api;
use model\change_log_field;
use model\change_log_named;
use model\change_log_table;
use model\sandbox_named;
use model\source;
use test\testing;
use const test\TIMEOUT_LIMIT_DB;
use const test\TIMEOUT_LIMIT_DB_MULTI;

function create_test_sources(testing $t): void
{

    $t->header('Check if all base sources are exist');

    $t->test_source(source_api::TN_READ);

}

function run_source_test(testing $t): void
{
   $t->header('Test the source class (src/main/php/model/ref/source.php)');

    // load the main test source
    $src_read = $t->test_word(source_api::TN_READ);

    // check if loading a source by name and id works
    $src_by_name = new source($t->usr1);
    $src_by_name->load_by_name(source_api::TN_READ, source::class);
    $src_by_id = new source($t->usr1);
    $src_by_id->load_by_id($src_by_name->id(), source::class);
    $target = source_api::TN_READ;
    $result = $src_by_id->name();
    $t->display('source->load of ' . $src_read->id() . ' by id ' . $src_by_name->id(), $target, $result);

    // test the creation of a new source
    $src_add = new source($t->usr1);
    $src_add->set_name(source_api::TN_ADD);
    $result = $src_add->save();
    $target = '';
    $t->display('source->save for "' . source_api::TN_ADD . '"', $target, $result, TIMEOUT_LIMIT_DB);

    // ... check if the source creation has been logged
    if ($src_add->id() > 0) {
        $log = new change_log_named;
        $log->set_table(change_log_table::SOURCE);
        $log->set_field(change_log_field::FLD_SOURCE_NAME);
        $log->row_id = $src_add->id();
        $log->usr = $t->usr1;
        $result = $log->dsp_last(true);
    }
    $target = 'zukunft.com system test added ' . source_api::TN_ADD;
    $t->display('source->save logged for "' . source_api::TN_ADD . '"', $target, $result);

    // ... test if the new source has been created
    $src_added = $t->load_source(source_api::TN_ADD);
    $src_added->load_by_name(source_api::TN_ADD);
    if ($src_added->id() > 0) {
        $result = $src_added->name();
    }
    $target = source_api::TN_ADD;
    $t->display('source->load of added source "' . source_api::TN_ADD . '"', $target, $result);

    // check if the source can be renamed
    $src_added->set_name(source_api::TN_RENAMED);
    $result = $src_added->save();
    $target = '';
    $t->display('source->save rename "' . source_api::TN_ADD . '" to "' . source_api::TN_RENAMED . '".', $target, $result, TIMEOUT_LIMIT_DB);

    // check if the source renaming was successful
    $src_renamed = new source($t->usr1);
    if ($src_renamed->load_by_name(source_api::TN_RENAMED, source::class)) {
        if ($src_renamed->id() > 0) {
            $result = $src_renamed->name();
        }
    }
    $target = source_api::TN_RENAMED;
    $t->display('source->load renamed source "' . source_api::TN_RENAMED . '"', $target, $result);

    // check if the source renaming has been logged
    $log = new change_log_named;
    $log->set_table(change_log_table::SOURCE);
    $log->set_field(change_log_field::FLD_SOURCE_NAME);
    $log->row_id = $src_renamed->id();
    $log->usr = $t->usr1;
    $result = $log->dsp_last(true);
    $target = 'zukunft.com system test changed ' . source_api::TN_ADD . ' to ' . source_api::TN_RENAMED;
    $t->display('source->save rename logged for "' . source_api::TN_RENAMED . '"', $target, $result);

    // check if the source parameters can be added
    $src_renamed->url = source_api::TU_ADD;
    $src_renamed->description = source_api::TD_ADD;
    $result = $src_renamed->save();
    $target = '';
    $t->display('source->save all source fields beside the name for "' . source_api::TN_RENAMED . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

    // check if the source parameters have been added
    $src_reloaded = $t->load_source(source_api::TN_RENAMED);
    $result = $src_reloaded->url;
    $target = source_api::TU_ADD;
    $t->display('source->load url for "' . source_api::TN_RENAMED . '"', $target, $result);
    $result = $src_reloaded->description;
    $target = source_api::TD_ADD;
    $t->display('source->load description for "' . source_api::TN_RENAMED . '"', $target, $result);

    // check if the source parameter adding have been logged
    $log = new change_log_named;
    $log->set_table(change_log_table::SOURCE);
    $log->set_field(change_log_field::FLD_SOURCE_URL);
    $log->row_id = $src_reloaded->id();
    $log->usr = $t->usr1;
    $result = $log->dsp_last(true);
    $target = 'zukunft.com system test added ' . source_api::TU_ADD;
    //$target = 'zukunft.com system test partner changed ' . source_api::TEST_URL_CHANGED . ' to ' . source_api::TEST_URL;
    $t->display('source->load url for "' . source_api::TN_RENAMED . '" logged', $target, $result);
    $log->set_field(sandbox_named::FLD_DESCRIPTION);
    $result = $log->dsp_last(true);
    $target = 'zukunft.com system test added ' . source_api::TD_ADD;
    //$target = 'zukunft.com system test partner changed System Test Source Description Changed to System Test Source Description';
    $t->display('source->load description for "' . source_api::TN_RENAMED . '" logged', $target, $result);

    // check if a user specific source is created if another user changes the source
    $src_usr2 = new source($t->usr2);
    $src_usr2->load_by_name(source_api::TN_RENAMED, source::class);
    $src_usr2->url = source_api::TEST_URL_CHANGED;
    $src_usr2->description = source_api::TEST_DESCRIPTION_CHANGED;
    $result = $src_usr2->save();
    $target = '';
    $t->display('source->save all source fields for user 2 beside the name for "' . source_api::TN_RENAMED . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

    // check if a user specific source changes have been saved
    $src_usr2_reloaded = new source($t->usr2);
    $src_usr2_reloaded->load_by_name(source_api::TN_RENAMED, source::class);
    $result = $src_usr2_reloaded->url;
    $target = source_api::TEST_URL_CHANGED;
    $t->display('source->load url for "' . source_api::TN_RENAMED . '"', $target, $result);
    $result = $src_usr2_reloaded->description;
    $target = source_api::TEST_DESCRIPTION_CHANGED;
    $t->display('source->load description for "' . source_api::TN_RENAMED . '"', $target, $result);

    // check the source for the original user remains unchanged
    $src_reloaded = $t->load_source(source_api::TN_RENAMED);
    $result = $src_reloaded->url;
    $target = source_api::TU_ADD;
    $t->display('source->load url for "' . source_api::TN_RENAMED . '" unchanged for user 1', $target, $result);
    $result = $src_reloaded->description;
    $target = source_api::TD_ADD;
    $t->display('source->load description for "' . source_api::TN_RENAMED . '" unchanged for user 1', $target, $result);

    // check if undo all specific changes removes the user source
    $src_usr2 = new source($t->usr2);
    $src_usr2->load_by_name(source_api::TN_RENAMED, source::class);
    $src_usr2->url = source_api::TU_ADD;
    $src_usr2->description = source_api::TD_ADD;
    $result = $src_usr2->save();
    $target = '';
    $t->display('source->save undo the user source fields beside the name for "' . source_api::TN_RENAMED . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

    // check if a user specific source changes have been saved
    $src_usr2_reloaded = new source($t->usr2);
    $src_usr2_reloaded->load_by_name(source_api::TN_RENAMED, source::class);
    $result = $src_usr2_reloaded->url;
    $target = source_api::TU_ADD;
    $t->display('source->load url for "' . source_api::TN_RENAMED . '" unchanged now also for user 2', $target, $result);
    $result = $src_usr2_reloaded->description;
    $target = source_api::TD_ADD;
    $t->display('source->load description for "' . source_api::TN_RENAMED . '" unchanged now also for user 2', $target, $result);

    // TODO create and check the display functions

}