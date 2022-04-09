<?php

/*

  zu_lib_word_dsp.php - old functions to display words (just just for regression code testing)
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
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/


// simply to display a single word and also get the name
function zut_html_id($id, $user_id)
{
    log_debug('zut_html_id(' . $id . ')');
    $result = zut_html($id, zut_name($id, $user_id));
    return $result;
}

// simply to display a single word
function zut_html($id, $name)
{
    log_debug('zut_html');
    $result = '  <tr>' . "\n";
    $result .= zut_html_tbl($id, $name);
    $result .= '  </tr>' . "\n";
    return $result;
}

// simply to display a single word and allow to delete it
// used by zuv_dsp_add
function zut_html_del($id, $name, $del_call)
{
    log_debug('zut_html');
    $result = '  <tr>' . "\n";
    $result .= zut_html_tbl($id, $name);
    $result .= '    <td>' . "\n";
    $result .= '      ' . btn_del("delete", $del_call) . '<br> ';
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
function zut_html_tbl($id, $name, $intent)
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
    $result .= btn_del("unlink word", "/http/link_del.php?id=" . $link_id . "&back=" . $word_id);
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
function zut_dsp_selector_link($id, $user_id, $back_link)
{
    log_debug('zut_dsp_selector_link ... word id ' . $id);
    $result = '';

    $sql = "SELECT * FROM (
          SELECT verb_id, 
                 IF (name_reverse <> '', CONCAT(verb_name, ' (', name_reverse, ')'), verb_name) AS name,
                 words
            FROM verbs 
    UNION SELECT verb_id*-1, 
                 CONCAT(name_reverse, ' (', verb_name, ')') AS name,
                 words
            FROM verbs 
           WHERE name_reverse <> '' ) AS links
        ORDER BY words DESC, name;";
    $result = zuh_selector("verb", "word_add", $sql, $id, "");

    if (zuu_is_admin($user_id)) {
        // admin users should always have the possibility to create a new link type
        $result .= btn_add('add new link type', '/http/verb_add.php?back=' . $back_link);
    }

    return $result;
}

// similar to zut_dsp_selector_link, but displays only the "forward" links, means not the reverse
function zut_dsp_selector_link_fwd($id, $back_link, $user_id)
{
    log_debug('zut_dsp_selector_link ... word id ' . $id);
    $result = '';

    $sql = "SELECT * FROM (
          SELECT verb_id, 
                 IF (name_reverse <> '', CONCAT(verb_name, ' (', name_reverse, ')'), verb_name) AS name,
                 words
            FROM verbs ) AS links
        ORDER BY words DESC, name;";
    $result = zuh_selector("verb", "link_edit", $sql, $id, "");

    if (zuu_is_admin($user_id)) {
        // admin users should always have the possibility to create a new link type
        $result .= btn_add('add new link type', '/http/verb_add.php?back=' . $back_link);
    }

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


// show a word with its the default view
function zut_dsp($id, $user_id)
{
    log_debug('zut_dsp(' . $id . ')');
    $result = '';

    // check input and set default if needed
    if ($id <= 0) {
        $id = 1;
    }
    if ($user_id <= 0) {
        $user_id = zuu_id();
    }

    $view_id = zum_default_id($user_id, $id);
    $result .= zum_html($view_id, $id, 0);

    log_debug('zut_dsp ... done');
    return $result;
}

// show the html form to add a new word
// add a word with a link type to a word list
// e.g. add time_pos:2013 to the word list if the word list contains "year" and "now" 
// maybe allow to enter the plural and the description in the same view
function zut_dsp_add($in_word, $in_link, $in_type, $user_id, $back_id)
{
    log_debug('zut_dsp_add (' . $in_word . ',' . $in_link . ',' . $in_link . ',' . $user_id . ',' . $back_id . ')');
    $result = '';

    $result .= zuh_text_h2('Add ');
    $result .= zuh_form_start("word_add");
    $result .= 'Enter the new word name <input type="text" name="word_name">';
    $result .= zut_html_selector_add($in_link) . ' ';
    $result .= ' (as a ' . zut_html_selector_type($in_type) . ')';
    $result .= '<br><br>';
    $result .= 'which ';
    $result .= zut_dsp_selector_link($in_link, $user_id, $back_id);
    $result .= zut_html_selector_word($in_word, 0, "word_add");
    if (trim($back_id) > 0) {
        $result .= '  <input type="hidden" name="back" value="' . $back_id . '">';
    }
    $result .= zuh_form_end();

    log_debug('zut_dsp_add ... done');
    return $result;
}

// display the history of a word
function zut_dsp_hist_links($wrd_id, $size, $back_link)
{
    log_debug("zut_dsp_hist_links (" . $wrd_id . ",size" . $size . ",b" . $size . ")");
    $result = ''; // reset the html code var

    // get changed links related to one word
    $sql = "SELECT c.change_time AS time, 
                 u.user_name AS user, 
                 a.change_action_name AS type, 
                 c.old_text_from, 
                 c.old_text_link, 
                 c.old_text_to, 
                 c.new_text_from, 
                 c.new_text_link, 
                 c.new_text_to
            FROM change_links c,
                 change_actions a,
                 users u
           WHERE (c.change_table_id = " . cl(db_cl::LOG_TABLE, change_log_table::WORD) . "      OR c.change_table_id = " . cl(db_cl::LOG_TABLE, change_log_table::WORD_USR) . " 
               OR c.change_table_id = " . cl(db_cl::LOG_TABLE, change_log_table::WORD_LINK) . " )
             AND (c.old_from_id = " . $wrd_id . " OR c.new_from_id = " . $wrd_id . " OR c.old_to_id = " . $wrd_id . " OR c.new_to_id = " . $wrd_id . ")
             AND c.change_action_id = a.change_action_id 
             AND c.user_id = u.user_id 
        ORDER BY c.change_time DESC
           LIMIT " . $size . ";";
    $sql_result = zu_sql_get_all($sql);

    // display the changes
    $row_nbr = 0;
    $result .= '<table class="change_hist">';
    while ($wrd_row = mysqli_fetch_array($sql_result, MySQLi_ASSOC)) {
        $row_nbr++;
        $result .= '<tr>';
        if ($row_nbr == 1) {
            $result .= '<th>time</th>';
            $result .= '<th>user</th>';
            $result .= '<th>from</th>';
            $result .= '<th>to</th>';
        }
        $result .= '</tr><tr>';
        $result .= '<td>' . $wrd_row["time"] . '</td>';
        $result .= '<td>' . $wrd_row["user"] . '</td>';
        $old_text = trim($wrd_row["old_text_from"] . " " . $wrd_row["old_text_link"] . " " . $wrd_row["old_text_to"]);
        $new_text = trim($wrd_row["new_text_from"] . " " . $wrd_row["new_text_link"] . " " . $wrd_row["new_text_to"]);
        if ($old_text == "") {
            $result .= '<td>' . $wrd_row["type"] . '</td>';
        } else {
            $result .= '<td>' . $old_text . '</td>';
        }
        if ($new_text == "") {
            $result .= '<td>' . $wrd_row["type"] . '</td>';
        } else {
            $result .= '<td>' . $new_text . '</td>';
        }
        $result .= '</tr>';
    }
    $result .= '</table>';

    log_debug("zut_dsp_hist_links -> done");
    return $result;
}

// display a botton to edit the word link in a table cell
function zutl_btn_edit($link_id, $word_id)
{
    log_debug("zutl_btn_edit (" . $link_id . ",b" . $word_id . ")");
    $result = ''; // reset the html code var

    // get the link from the database
    $result .= '    <td>' . "\n";
    $result .= btn_edit("edit word link", "/http/link_edit.php?id=" . $link_id . "&back=" . $word_id);
    $result .= '    </td>' . "\n";

    log_debug("zutl_btn_edit done");
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
