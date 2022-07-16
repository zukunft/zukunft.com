<?php

/*

  zu_lib_word_dsp.php - old functions to display words (just for regression code testing)
  -------------------

  prefix: zut_dsp_* 

  simple functions
  ------
  
  zut_html          - simply to display a single word in a table row    e.g. used to create a word list 
  zut_html_tbl      - simply to display a single word in a table column e.g. used to create a value table 
  zut_html_tbl_head - simply to display a single word in a table header e.g. used to create a value table 
  
  zut_unlink_html   - allow the user to unlick a word
  
  zut_dsp_add       - show the html form to add a new word


  deprecated functions
  ----------
  
  zut_html_id - because the word name should be retrived already with the initial database call

  
  Var name convention
  
  $id         - number of a database primary index
  $word_ids   - comma seperated string of word word_ids
  $word_names - comma seperated string with word string, each capsulet by highquotes
  $word_array - array of comma seperated string with word string, each capsulet by highquotes
  
  for selectors four parameters are used
  $selected:  word id that is now selected by the user and used for to display the values
  $suggested: word id that is most often used in this eviroment
  $useful:    list of word ids that are likely to be used (target 3 to 4, max 7)
  $possible:  list of all possible word ids; the user can select from this list by typing
  $all:       list of all words mainly as a fallback used only if the typed name does not lead to a result and these word should be displayed in a different format
  
  
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


// simply to display a single word and allow to delete it
// used by zuv_dsp_add
function zut_html_del($id, $name, $del_call)
{
    log_debug('zut_html');
    $result = '  <tr>' . "\n";
    $result .= zut_html_tbl($id, $name);
    $result .= '    <td>' . "\n";
    $result .= '      ' . \html\btn_del("delete", $del_call) . '<br> ';
    $result .= '    </td>' . "\n";
    $result .= '  </tr>' . "\n";
    return $result;
}

// simply to display a single word in a table
function zut_link($id, $name)
{
    // to be replace by object code
    $user_id = 0;
    $description = zut_description($id, $user_id);
    $result = '<a href="/http/view.php?words=' . $id . '" title="' . $description . '">' . $name . '</a>';
    return $result;
}

// similar to zut_link 
function zut_link_style($id, $name, $style)
{
    $result = '<a href="/http/view.php?words=' . $id . '" class="' . $style . '">' . $name . '</a>';
    return $result;
}

// simply to display a single word in a table
function zut_html_tbl($id, $name, int $intent = 0)
{
    log_debug('zut_tbl_html');
    $result = '    <td>' . "\n";
    while ($intent > 0) {
        $result .= '&nbsp;';
        $intent = $intent - 1;
    }
    $result .= '      ' . zut_link($id, $name) . '' . "\n";
    $result .= '    </td>' . "\n";
    return $result;
}

// simply to display a single word in a table as a header
function zut_html_tbl_head($id, $name)
{
    log_debug('zut_html_tbl_head');
    $result = '    <th>' . "\n";
    $result .= '      ' . zut_link($id, $name) . "\n";
    $result .= '    </th>' . "\n";
    return $result;
}

// simply to display a single word in a table as a header
function zut_html_tbl_head_right($id, $name)
{
    log_debug('zut_html_tbl_head_right');
    $result = '    <th>' . "\n";
    $result .= '      <p class="right_ref">' . zut_link($id, $name) . '</p>' . "\n";
    $result .= '    </th>' . "\n";
    return $result;
}

// allow the user to unlick a word
function zut_unlink_html($link_id, $word_id)
{
    log_debug('zut_unlink_html(' . $link_id . ')');
    $result = '    <td>' . "\n";
    $result .= \html\btn_del("unlink word", "/http/link_del.php?id=" . $link_id . "&back=" . $word_id);
    $result .= '    </td>' . "\n";
    return $result;
}

// display a word as the view header
function zut_dsp_header($wrd_id, $user_id)
{
    log_debug('zut_dsp_header (' . $wrd_id . ')');
    $result = '';

    if ($wrd_id <= 0) {
        $result .= 'no word selected';
    } else {
        $is_part_of = zut_is_name($wrd_id);
        $result .= '<h2>';
        $result .= zut_name($wrd_id, $user_id);
        if ($is_part_of <> '' and $is_part_of <> 'not set') {
            $result .= ' (<a href="/http/view.php?words=' . zut_is_id($wrd_id) . '">' . $is_part_of . '</a>)';
        }
        /*    $result .= '  '.'<a href="/http/word_edit.php?id='.$wrd_id.'&back='.$wrd_id.'" title="Rename word"><img src="'.ZUH_IMG_EDIT.'" alt="Rename word" style="height: 0.65em;"></a>'; */
        $result .= '  ' . '<a href="/http/word_edit.php?id=' . $wrd_id . '&back=' . $wrd_id . '" title="Rename word"><span class="glyphicon glyphicon-pencil"></span></a>';
        $result .= '</h2>';
    }

    log_debug('zut_dsp_header done');
    return $result;
}

// display a word list as a text, means word by word within one line
function zut_dsp_lst_txt($wrd_lst)
{
    log_debug('zut_dsp_lst_txt (' . implode(",", $wrd_lst) . ')');
    $result = '';

    foreach (array_keys($wrd_lst) as $wrd_id) {
        if ($result <> '') {
            $result .= ', ';
        }
        if (is_array($wrd_lst[$wrd_id])) {
            $result .= '<a href="/http/view.php?words=' . $wrd_id . '">' . $wrd_lst[$wrd_id][0] . '</a>';
        } else {
            $result .= '<a href="/http/view.php?words=' . $wrd_id . '">' . $wrd_lst[$wrd_id] . '</a>';
        }
    }

    return $result;
}


// returns the html code to select a word
// database link must be open
function zut_html_selector_word($id, $pos, $form_name)
{
    log_debug('zut_html_selector_word ... word id ' . $id);

    //$result = zuh_selector("word",      "word_add", "SELECT word_id, word_name FROM words;", $id);
    if ($pos > 0) {
        $field_id = "word" . $pos;
    } else {
        $field_id = "word";
    }
    $result = zuh_selector($field_id, $form_name, "SELECT word_id, word_name FROM words ORDER BY word_name;", $id, "");
    //zuh_selector ($name, $form, $query, $selected)

    log_debug('zut_html_selector_word ... done ' . $id);
    return $result;
}

function zut_html_selector_word_time($id)
{
    log_debug('zut_html_selector_word_time ... word id ' . $id);
    $result = zuh_selector("word", "word_add", "SELECT word_id, word_name FROM words WHERE word_type_id = 2 ORDER BY word_name;", $id, "");
    return $result;
}

// to select a existing word to be added
function zut_html_selector_add($id)
{
    log_debug('zut_html_selector_add ... word id ' . $id);
    $result = zuh_selector("add", "word_add", "SELECT word_id, word_name FROM words WHERE word_id <> " . $id . " ORDER BY word_name;", 0, "... or select an existing word to link it");
    return $result;
}

// returns the html code to select a word link type
// database link must be open
function zut_html_selector_type($id)
{
    log_debug('zut_html_selector_type ... word id ' . $id);
    $result = zuh_selector("type", "word_add", "SELECT word_type_id, type_name FROM word_types;", $id, "");

    return $result;
}


// return the word name for more than one
function zut_plural($wrd_id, $user_id)
{
    global $db_con;

    log_debug('zut_plural (' . $wrd_id . ',u' . $user_id . ')');
    $result = null;
    if ($wrd_id > 0) {
        $wrd_del = $db_con->get1_old("SELECT word_id FROM user_words WHERE word_id = " . $wrd_id . " AND user_id = " . $user_id . " AND excluded = 1;");
        //$wrd_del = zu_sql_get1("SELECT word_id FROM user_words WHERE word_id = " . $wrd_id . " AND user_id = " . $user_id . " AND excluded = 1;");
        // only return a word if the user has not yet excluded the word
        if ($wrd_id <> $wrd_del) {
            $result = $db_con->get1_old("SELECT plural FROM user_words WHERE word_id = " . $wrd_id . " AND user_id = " . $user_id . " AND (excluded is NULL OR excluded = 0);");
            //$result = zu_sql_get1("SELECT plural FROM user_words WHERE word_id = " . $wrd_id . " AND user_id = " . $user_id . " AND (excluded is NULL OR excluded = 0);");
            if ($result == NULL) {
                $result = zu_sql_get_field('word', $wrd_id, 'plural');
            }
        }
    }

    log_debug('zut_plural (' . $wrd_id . '->' . $result . ')');
    return $result;
}

// return the word name for the user
// TODO: combine to one query
function zut_name($wrd_id, $user_id)
{
    log_debug('zut_name (' . $wrd_id . ',u' . $user_id . ')');
    $result = null;
    if ($wrd_id > 0) {
        if ($user_id > 0) {
            $wrd_del = zu_sql_get1("SELECT word_id FROM user_words WHERE word_id = " . $wrd_id . " AND user_id = " . $user_id . " AND excluded = 1;");
            // only return a word if the user has not yet excluded the word
            if ($wrd_id <> $wrd_del) {
                $result = zu_sql_get1("SELECT word_name FROM user_words WHERE word_id = " . $wrd_id . " AND user_id = " . $user_id . " AND (excluded is NULL OR excluded = 0);");
                if ($result == NULL) {
                    $result = zu_sql_get_name('word', $wrd_id);
                }
            }
        } else {
            // if no user is selected, simply return the standard name
            $result = zu_sql_get_name('word', $wrd_id);
        }
    }

    log_debug('zut_name (' . $wrd_id . '->' . $result . ')');
    return $result;
}

// return the word name for more than one
function zut_description($wrd_id, $user_id)
{
    log_debug('zut_description (' . $wrd_id . ',u' . $user_id . ')');
    $result = null;
    if ($wrd_id > 0) {
        $wrd_del = zu_sql_get1("SELECT word_id FROM user_words WHERE word_id = " . $wrd_id . " AND user_id = " . $user_id . " AND excluded = 1;");
        // only return a word if the user has not yet excluded the word
        if ($wrd_id <> $wrd_del) {
            $result = zu_sql_get1("SELECT description FROM user_words WHERE word_id = " . $wrd_id . " AND user_id = " . $user_id . " AND (excluded is NULL OR excluded = 0);");
            if ($result == NULL) {
                $result = zu_sql_get_field('word', $wrd_id, sql_db::FLD_DESCRIPTION);
            }
        }
    }

    log_debug('zut_description (' . $wrd_id . '->' . $result . ')');
    return $result;
}


?>
