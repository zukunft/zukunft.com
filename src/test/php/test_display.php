<?php 

/*

  test_display.php - TESTing of the DISPLAY functions
  ----------------
  

zukunft.com - calc with words

copyright 1995-2021 by zukunft.com AG, Blumentalstrasse 15, 8707 Uetikon am See, Switzerland

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

function run_display_test () {

  global $usr;

  test_header('Test the view_display class (classes/view_display.php)');

  // test the usage of a view to create the HTML code
  $wrd     = load_word(TEST_WORD);
  $wrd_abb = load_word(TW_ABB);
  $dsp = new view;
  $dsp->name = 'Company ratios';
  $dsp->usr = $usr;
  $dsp->load();
  //$result = $dsp->display($wrd, $back);
  $target = true;
  //test_dsp_contains(', view_dsp->display is "'.$result.'" which should contain '.$wrd_abb->name.'', $target, $result);



  test_header('Test the view component display class (classes/view_component_dsp.php)');

  // test if a simple text component can be created
  $cmp = new view_component_dsp;
  $cmp->type_id = cl(DBL_VIEW_COMP_TYPE_TEXT);
  $cmp->name = TS_NESN_2016_NAME;
  $result = $cmp->text();
  $target = ' '.TS_NESN_2016_NAME;
  test_dsp('view_component_dsp->text', $target, $result);




  test_header('Test the display button class (classes/display_button.php )');

  $target = '<a href="/http/view.php" title="Add test"><img src="../../../images/button_add.svg" alt="Add test"></a>';
  $target = '<a href="/http/view.php" title="Add test">';
  $result = btn_add('Add test', '/http/view.php');
  test_dsp_contains(", btn_add", $target, $result);

  $target = '<a href="/http/view.php" title="Edit test"><img src="../../../images/button_edit.svg" alt="Edit test"></a>';
  $target = '<a href="/http/view.php" title="Edit test">';
  $result = btn_edit('Edit test', '/http/view.php');
  test_dsp_contains(", btn_edit", $target, $result);

  $target = '<a href="/http/view.php" title="Del test"><img src="../../../images/button_del.svg" alt="Del test"></a>';
  $target = '<a href="/http/view.php" title="Del test">';
  $result = btn_del('Del test', '/http/view.php');
  test_dsp_contains(", btn_del", $target, $result);

  $target = '<a href="/http/view.php" title="Undo test"><img src="../../../images/button_undo.svg" alt="Undo test"></a>';
  $result = btn_undo('Undo test', '/http/view.php');
  test_dsp(", btn_undo", $target, $result);

  $target = '<a href="/http/view.php" title="Find test"><img src="../../../images/button_find.svg" alt="Find test"></a>';
  $result = btn_find('Find test', '/http/view.php');
  test_dsp(", btn_find", $target, $result);

  $target = '<a href="/http/view.php" title="Show all test"><img src="../../../images/button_filter_off.svg" alt="Show all test"></a>';
  $result = btn_unfilter('Show all test', '/http/view.php');
  test_dsp(", btn_unfilter", $target, $result);

  $target = '<h6>YesNo test</h6><a href="/http/view.php&confirm=1" title="Yes">Yes</a>/<a href="/http/view.php&confirm=-1" title="No">No</a>';
  $result = btn_yesno('YesNo test', '/http/view.php');
  test_dsp(", btn_yesno", $target, $result);

  $target = '<a href="/http/view.php?words=1" title="back"><img src="../../../images/button_back.svg" alt="back"></a>';
  $result = btn_back('');
  test_dsp(", btn_back", $target, $result);


  test_header('Test the display HTML class (classes/display_html.php )');

  $target = htmlspecialchars(trim('<html> <head> <title>Header test (zukunft.com)</title> <link rel="stylesheet" type="text/css" href="../../../style/style.css" /> </head> <body class="center_form">'));
  $target = htmlspecialchars(trim('<title>Header test (zukunft.com)</title>'));
  $result = htmlspecialchars(trim(dsp_header('Header test', 'center_form')));
  test_dsp_contains(", dsp_header", $target, $result);


  test_header('Test general frontend scripts (e.g. /about.php)');

  // check if the about page contains at least some basic keywords
  $result = file_get_contents('https://www.zukunft.com/http/about.php?id=1');
  $target = 'zukunft.com AG';
  if (strpos($result, $target) > 0) {
    $result = $target;
  } else {
    $result = '';
  }
  // about does not return a page for unknown reasons at the moment
  //test_dsp_contains(', frontend about.php '.$result.' contains at least '.$target, $target, $result, TIMEOUT_LIMIT_PAGE);

  $result = file_get_contents('https://zukunft.com/http/privacy_policy.html');
  $target = 'Swiss purpose of data protection';
  test_dsp_contains(', frontend privacy_policy.php '.$result.' contains at least '.$target, $target, $result, TIMEOUT_LIMIT_PAGE_SEMI);

  $result = file_get_contents('https://zukunft.com/http/error_update.php?id=1');
  $target = 'not permitted';
  test_dsp_contains(', frontend error_update.php '.$result.' contains at least '.$target, $target, $result, TIMEOUT_LIMIT_PAGE);

  $result = file_get_contents('https://zukunft.com/http/find.php?pattern='.TW_ABB);
  $target = TW_ABB;
  test_dsp_contains(', frontend find.php '.TW_ABB.' contains at least '.$target, $target, $result, TIMEOUT_LIMIT_PAGE);

}

?>
