<?php

// standard zukunft header for callable php files to allow debugging and lib loading
if (isset($_GET['debug'])) { $debug = $_GET['debug']; } else { $debug = 0; }
include_once '../lib/zu_lib.php'; if ($debug > 0) { echo 'libs loaded<br>'; }

// open database
$db_con = zu_start("test_single", "", $debug);

    $result = ''; // reset the html code var

    $usr = New user;
    $result .= $usr->get($debug-1);

    // todo review
    if ($usr->id <= 0) {
        $result = zu_err('User has is not permitted', 'test_single');
    } else {

      // load the testing functions
      include_once '../classes/test_base.php'; if ($debug > 9) { echo 'test base loaded<br>'; }

      $start_time = microtime(true);
      $exe_start_time = $start_time;

      // save a new word
      $wrd_new = New word;
      $wrd_new->name = TEST_WORD;
      $wrd_new->usr = $usr;
      $result = $wrd_new->save($debug-1);
      //$target = 'A word with the name "'.TEST_WORD.'" already exists. Please use another name.';
      $target = '';
      $exe_start_time = test_show_result(', word->save for "'.TEST_WORD.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB);
    }

    echo $result;

zu_end($db_con, $debug);