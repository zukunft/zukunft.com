<?php 

/*

  test_graph.php - TESTing of the GRAPH functions
  --------------
  

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

function run_graph_test () {

  global $usr;

  $back = 0;

  test_header('Test the graph class (classes/word_link_list.php)');

  // get all phrase links used for a phrase and its related values 
  // e.g. for the phrase "Company" the link "Company has a balance sheet" should be returned

  // step 1: define the phrase list e.g. in this case only word "Company"
  $phr_lst = New phrase_list;
  $phr_lst->usr = $usr;
  $phr_lst->add_name(TEST_WORD);
  $phr_lst->load();

  // step 2: get all values related to the phrases
  $val_lst = New value_list;
  $val_lst->usr     = $usr;
  $val_lst->phr_lst = $phr_lst;
  $val_lst->load_all();
  $wrd_lst_all = $val_lst->phr_lst->wrd_lst_all();

  // step 3: get all phrases used for the value descriptions
  $phr_lst_used      = New phrase_list;
  $phr_lst_used->usr = $usr;
  foreach ($wrd_lst_all->lst AS $wrd) {
    if (!array_key_exists($wrd->id, $phr_lst_used->ids)) {
      $phr_lst_used->add($wrd->phrase());
    }
  }
  // step 4: get the word links for the used phrases
  //         these are the word links that are needed for a complete export
  $lnk_lst = New word_link_list;
  $lnk_lst->usr       = $usr;
  $lnk_lst->wrd_lst   = $phr_lst_used;
  $lnk_lst->direction = 'up';
  $lnk_lst->load();
  $result = $lnk_lst->name();
  // check if at least the basic relations are in the database
  $target = ''.TEST_WORD.' has a balance sheet';
  test_dsp_contains(', word_link_list->load for '.$phr_lst->dsp_id(), $target, $result, TIMEOUT_LIMIT_PAGE);
  $target = 'Company has a forecast';
  test_dsp_contains(', word_link_list->load for '.$phr_lst->dsp_id(), $target, $result, TIMEOUT_LIMIT_PAGE);
  $target = 'Company uses employee';
  test_dsp_contains(', word '.$phr_lst->dsp_id(), $target, $result, TIMEOUT_LIMIT_PAGE);

  // similar to above, but just for ABB
  $phr_lst = New phrase_list;
  $phr_lst->usr = $usr;
  $phr_lst->add_name(TW_ABB);
  $phr_lst->add_name(TW_SALES);
  $phr_lst->add_name(TW_MIO);
  $phr_lst->load();
  $lnk_lst = New word_link_list;
  $lnk_lst->usr       = $usr;
  $lnk_lst->wrd_lst   = $phr_lst;
  $lnk_lst->direction = 'up';
  $lnk_lst->load();
  $result = $lnk_lst->name();
  // to be reviewed
  //$target = 'ABB (Company),million (scaling)';
  $target = 'Sales is part of cash flow statement';
  test_dsp_contains(', word_link_list->load for '.$phr_lst->dsp_id(), $target, $result, TIMEOUT_LIMIT_PAGE);


  // load the words related to ABB in compare with the old function
  $ABB = New word_dsp;
  $ABB->usr = $usr;
  $ABB->name = TW_ABB;
  $ABB->load();
  $is = New verb;
  $is->id= cl(db_cl::VERB, verb::DBL_IS);
  $is->usr = $usr->id;
  $is->load();
  $graph = New word_link_list;
  $graph->wrd = $ABB;
  $graph->vrb = $is;
  $graph->usr = $usr;
  $graph->direction = 'down';
  $graph->load();
  $target = zut_html_list_related ($ABB->id, $graph->direction, $usr->id);
  $result = $graph->display($back);
  $diff = str_diff($result, $target); if ($diff['view'][0] == 0) { $target = $result; }
  test_dsp('graph->load for ABB down is', $target, $result);

  // the other side
  $graph->direction = 'up';
  $graph->load();
  $target = zut_html_list_related ($ABB->id, $graph->direction, $usr->id);
  $result = $graph->display($back);
  $diff = str_diff($result, $target); if ($diff['view'][0] == 0) { $target = $result; }
  test_dsp('graph->load for ABB up is', $target, $result);

}