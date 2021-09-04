<?php

// standard zukunft header for callable php files to allow debugging and lib loading
$debug = $_GET['debug'] ?? 0;
$root_path = '/home/timon/git/zukunft.com/';
include_once $root_path . 'src/main/php/zu_lib.php';

// open database
$db_con = prg_start("test_single");

$result = ''; // reset the html code var

$usr = new user;
$result .= $usr->get();

// todo review
if ($usr->id <= 0) {
    $result = log_err('User has is not permitted', 'test_single');
} else {

    load_usr_data();

    // load the testing functions
    include_once $root_path . 'src/test/php/test_base.php';
    if ($debug > 9) {
        echo 'test base loaded<br>';
    }

    $start_time = microtime(true);
    $exe_start_time = $start_time;

    // test the user ip
    $ip_addr = '2.204.210.217';
    $result = $usr->ip_check($ip_addr);
    $target = '';
    test_dsp(', usr->ip_check', $target, $result);

    test_word(TW_ABB);
    test_word(TW_SALES);
    test_word(TW_CHF);
    test_word(TW_MIO);
    test_word(TW_2014);
    $wrd_lst = load_word_list(array(TW_ABB, TW_SALES, TW_CHF, TW_MIO, TW_2014));
    $wrd_lst->ex_time();
    $grp = $wrd_lst->get_grp();
    if ($grp->id == 0) {
        $result = 'No word list found.';
        $target = sql_array($wrd_lst->names());
        test_dsp(', value->load for group id "' . $grp->id . '"', $target, $result, TIMEOUT_LIMIT);
    } else {
        $val = new value;
        $val->grp = $grp;
        $val->grp_id = $grp->id;
        $val->usr = $usr;
        $val->load();
        $result = '';
        if ($val->id <= 0) {
            $result = 'No value found for ' . $val->dsp_id() . '.';
        } else {
            if (isset($val->wrd_lst)) {
                $result = sql_array($val->wrd_lst->names());
            }
        }
        $target = sql_array($wrd_lst->names());
        test_dsp(', value->load for group id "' . $grp->id . '"', $target, $result, TIMEOUT_LIMIT);
    }

    // save a new word
    $wrd_new = new word;
    $wrd_new->name = word::TN_READ;
    $wrd_new->usr = $usr;
    $result = $wrd_new->save();
    //$target = 'A word with the name "'.word::TEST_NAME_READ.'" already exists. Please use another name.';
    $target = '';
    test_dsp('word->save for "' . word::TN_READ . '"', $target, $result, TIMEOUT_LIMIT_DB);
}

echo $result;

prg_end($db_con);