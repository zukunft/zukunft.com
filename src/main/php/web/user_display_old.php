<?php

/*

  user.php - to display the user specific settings
  --------
  
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

class user_dsp_old extends user
{

    /**
     * display the latest changes by the user
     */
    function dsp_changes($call, $size, $page, $back): string
    {
        log_debug('user_dsp->dsp_changes (u' . $this->id . ',b' . $back . ')');
        $result = ''; // reset the html code var

        // get value changes by the user that are not standard
        $log_dsp = new user_log_display($this);
        $log_dsp->id = $this->id;
        $log_dsp->type = user::class;
        $log_dsp->page = $page;
        $log_dsp->size = $size;
        $log_dsp->call = $call;
        $log_dsp->back = $back;
        $result .= $log_dsp->dsp_hist();

        log_debug('done');
        return $result;
    }

    // display the error that are related to the user, so that he can track when they are closed
    // or display the error that are related to the user, so that he can track when they are closed
    function dsp_errors($dsp_type, $size, $page, $back): string
    {
        log_debug($dsp_type . ' errors for user ' . $this->name);

        $result = '';
        $err_lst = new system_error_log_list;
        $err_lst->set_user($this);
        $err_lst->page = $page;
        $err_lst->size = $size;
        $err_lst->dsp_type = $dsp_type;
        $err_lst->back = $back;
        if ($err_lst->load()) {
            $result = $err_lst->dsp_obj()->get_html();
        }

        log_debug('done');
        return $result;
    }

    /**
     * display word changes by the user which are not (yet) standard
     */
    function dsp_sandbox_wrd($back): string
    {
        log_debug($this->id);

        global $db_con;
        $result = ''; // reset the html code var

        // get word changes by the user that are not standard
        $sql = "SELECT u.word_name AS usr_word_name, 
                   t.word_name, 
                   t.word_id 
              FROM user_words u,
                   words t
             WHERE u.user_id = " . $this->id . "
               AND u.word_id = t.word_id;";
        $db_con->usr_id = $this->id;
        $wrd_lst = $db_con->get_old($sql);

        // prepare to show the word link
        $row_nbr = 0;
        $result .= dsp_tbl_start();
        foreach ($wrd_lst as $wrd_row) {
            $row_nbr++;
            $result .= '<tr>';
            if ($row_nbr == 1) {
                $result .= '<th>Your name vs. </th><th>common name</th></tr><tr>';
            }
            $result .= '<td>' . $wrd_row['usr_word_name'] . '</td><td>' . $wrd_row['word_name'] . '</td>';
            //$result .= '<td><a href="/http/user.php?id='.$this->id.'&undo_word='.$log_row['type_table'].'&back='.$id.'"><img src="/src/main/resources/images/button_del_small.jpg" alt="undo change"></a></td>';
            $url = '/http/user.php?id=' . $this->id . '&undo_word=' . $wrd_row['word_id'] . '&back=' . $back . '';
            $result .= '<td>' . \html\btn_del("Undo your change and use the standard word " . $wrd_row['word_name'], $url) . '</td>';
            $result .= '</tr>';
        }
        $result .= dsp_tbl_end();

        log_debug('done');
        return $result;
    }

    /**
     * display triple changes by the user which are not (yet) standard
     */
    function dsp_sandbox_wrd_link($back): string
    {
        log_debug($this->id);

        global $db_con;
        $result = ''; // reset the html code var

        // create the databased link
        $db_con->usr_id = $this->id;

        // get all values changed by the user to a non standard triple
        if (SQL_DB_TYPE == sql_db::POSTGRES) {
            $sql = "SELECT u.triple_id AS id, 
                   l.user_id      AS owner_id, 
                   l.from_phrase_id, 
                   l.verb_id, 
                   l.to_phrase_id, 
                   CASE WHEN (u.name     <> '' IS NOT TRUE) THEN l.name     ELSE u.name     END AS usr_name, 
                   l.name                                                        AS std_name, 
                   CASE WHEN (u.excluded <> '' IS NOT TRUE) THEN l.excluded ELSE u.excluded END AS usr_excluded,
                   l.excluded                                                    AS std_excluded
              FROM user_triples u,
                   triples l
             WHERE u.user_id = " . $this->id . "
               AND u.triple_id = l.triple_id;";
        } else {
            $sql = "SELECT u.triple_id AS id, 
                   l.user_id      AS owner_id, 
                   l.from_phrase_id, 
                   l.verb_id, 
                   l.to_phrase_id, 
                   IF(u.name     IS NULL, l.name,     u.name)     AS usr_name, 
                   l.name                                         AS std_name, 
                   IF(u.excluded IS NULL, l.excluded, u.excluded) AS usr_excluded,
                   l.excluded                                     AS std_excluded
              FROM user_triples u,
                   triples l
             WHERE u.user_id = " . $this->id . "
               AND u.triple_id = l.triple_id;";
        }
        $sbx_lst = $db_con->get_old($sql);

        if (count($sbx_lst) > 0) {
            // prepare to show where the user uses different word_entry_link than a normal viewer
            $row_nbr = 0;
            $result .= dsp_tbl_start();
            foreach ($sbx_lst as $sbx_row) {
                $row_nbr++;

                // create the triple objects with the minimal parameter needed
                // TODO maybe use row mapper
                $wrd_usr = new triple($this);
                $wrd_usr->set_id($sbx_row['id']);
                $wrd_usr->from->set_id($sbx_row['from_phrase_id']);
                $wrd_usr->verb->set_id($sbx_row[verb::FLD_ID]);
                $wrd_usr->to->set_id($sbx_row['to_phrase_id']);
                $wrd_usr->set_name($sbx_row['usr_name']);
                $wrd_usr->excluded = $sbx_row['usr_excluded'];
                $wrd_usr->load_obj_vars();

                // to review: try to avoid using load_test_user
                $usr_std = new user;
                $usr_std->id = $sbx_row['owner_id'];
                $usr_std->load_test_user();

                $wrd_std = clone $wrd_usr;
                $wrd_std->set_user($usr_std);
                $wrd_std->load_obj_vars();
                $wrd_std->set_name($sbx_row['std_name']);
                $wrd_std->excluded = $sbx_row['std_excluded'];

                // check database consistency and correct it if needed
                if ($wrd_usr->name() == $wrd_std->name()
                    and $wrd_usr->excluded == $wrd_std->excluded) {
                    $wrd_usr->del_usr_cfg();
                } else {

                    // prepare the row triples
                    //$sandbox_item_name = $wrd_usr->name_linked($back);

                    // format the user triple
                    if ($wrd_usr->excluded == 1) {
                        $sandbox_usr_txt = "deleted";
                    } else {
                        $sandbox_usr_txt = $wrd_usr->name();
                    }

                    // format the standard triple
                    if ($wrd_std->excluded == 1) {
                        $sandbox_std_txt = "deleted";
                    } else {
                        $sandbox_std_txt = $wrd_std->name();
                    }

                    // format the triple of other users
                    $sandbox_other = '';
                    $sql_other = "SELECT l.triple_id, 
                               u.user_id, 
                               u.name, 
                               u.excluded
                          FROM user_triples u,
                               triples l
                         WHERE u.user_id <> " . $this->id . "
                           AND u.triple_id = l.triple_id
                           AND u.triple_id = " . $sbx_row['id'] . "
                           AND (u.excluded <> 1 OR u.excluded is NULL);";
                    log_debug('user_dsp->dsp_sandbox_val other sql (' . $sql_other . ')');
                    $sbx_lst_other = $db_con->get_old($sql_other);
                    foreach ($sbx_lst_other as $wrd_lnk_other_row) {
                        $usr_other = new user;
                        $usr_other->id = $wrd_lnk_other_row[user::FLD_ID];
                        $usr_other->load_test_user();

                        // to review: load all user triples with one query
                        $wrd_lnk_other = clone $wrd_usr;
                        $wrd_lnk_other->set_user($usr_other);
                        $wrd_lnk_other->load_obj_vars();
                        $wrd_lnk_other->set_name($wrd_lnk_other_row['name']);
                        $wrd_lnk_other->excluded = $wrd_lnk_other_row[user_sandbox::FLD_EXCLUDED];
                        if ($sandbox_other <> '') {
                            $sandbox_other .= ',';
                        }
                        $sandbox_other .= $wrd_lnk_other->name();
                    }
                    $sandbox_other = '<a href="/http/user_triple.php?id=' . $this->id . '&back=' . $back . '">' . $sandbox_other . '</a> ';

                    // create the button
                    $url = '/http/user.php?id=' . $this->id . '&undo_triple=' . $sbx_row['id'] . '&back=' . $back;
                    $sandbox_undo_btn = '<td>' . \html\btn_del("Undo your change and use the standard triple " . $sbx_row['std_triple'], $url) . '</td>';

                    // display the triple changes by the user
                    $result .= '<tr>';
                    // display headline
                    if ($row_nbr == 1) {
                        //$result .= '<th>Triple</th>';
                        $result .= '<th>Your triple vs. </th>';
                        $result .= '<th>common</th>';
                        $result .= '<th>other user</th>';
                        $result .= '<th></th>'; // for the buttons
                        $result .= '</tr><tr>';
                    }

                    // display one user adjustment
                    //$result .= '<td>'.$sandbox_item_name.'</td>';
                    $result .= '<td>' . $sandbox_usr_txt . '</td>';
                    $result .= '<td>' . $sandbox_std_txt . '</td>';
                    $result .= '<td>' . $sandbox_other . '</td>';
                    $result .= '<td>' . $sandbox_undo_btn . '</td>';

                    $result .= '</tr>';
                }

            }
            $result .= dsp_tbl_end();
        }

        log_debug('done');
        return $result;
    }

    /**
     * display formula changes by the user which are not (yet) standard
     */
    function dsp_sandbox_frm($back): string
    {
        log_debug('user_dsp->dsp_sandbox_frm(u' . $this->id . ')');

        global $db_con;
        $result = ''; // reset the html code var

        // get word changes by the user that are not standard
        $sql = "SELECT u.formula_name, 
                  u.resolved_text AS usr_formula_text, 
                  f.resolved_text AS formula_text, 
                  f.formula_id 
              FROM user_formulas u,
                  formulas f
            WHERE u.user_id = " . $this->id . "
              AND u.formula_id = f.formula_id;";
        $db_con->usr_id = $this->id;
        $frm_lst = $db_con->get_old($sql);

        // prepare to show the word link
        $row_nbr = 0;
        $result .= dsp_tbl_start();
        foreach ($frm_lst as $frm_row) {
            $row_nbr++;
            $result .= '<tr>';
            if ($row_nbr == 1) {
                $result .= '<th>Formula name </th>';
                $result .= '<th>Your formula vs. </th>';
                $result .= '<th>common formula</th>';
                $result .= '</tr><tr>';
            }
            $result .= '<td>' . $frm_row['formula_name'] . '</td>';
            $result .= '<td>' . $frm_row['usr_formula_text'] . '</td>';
            $result .= '<td>' . $frm_row['formula_text'] . '</td>';
            //$result .= '<td><a href="/http/user.php?id='.$this->id.'&undo_formula='.$frm_row[formula::FLD_ID].'&back='.$id.'"><img src="/src/main/resources/images/button_del_small.jpg" alt="undo change"></a></td>';
            $url = '/http/user.php?id=' . $this->id . '&undo_formula=' . $frm_row[formula::FLD_ID] . '&back=' . $back . '';
            $result .= '<td>' . \html\btn_del("Undo your change and use the standard formula " . $frm_row['formula_text'], $url) . '</td>';
            $result .= '</tr>';
        }
        $result .= dsp_tbl_end();

        log_debug('done');
        return $result;
    }

    /**
     * display formula_link changes by the user which are not (yet) standard
     */
    function dsp_sandbox_frm_link($back): string
    {
        log_debug($this->id);

        global $db_con;
        $result = ''; // reset the html code var

        // create the databased link
        $db_con->usr_id = $this->id;

        // get all values changed by the user to a non standard formula_link
        if (SQL_DB_TYPE == sql_db::POSTGRES) {
            $sql = "SELECT u.formula_link_id AS id, 
                   l.user_id              AS owner_id, 
                   l.formula_id, 
                   l.phrase_id, 
                   CASE WHEN (u.link_type_id <> '' IS NOT TRUE) THEN l.link_type_id ELSE u.link_type_id END AS usr_type, 
                   l.link_type_id                                                            AS std_type, 
                   CASE WHEN (u.excluded     <> '' IS NOT TRUE) THEN l.excluded     ELSE u.excluded     END AS usr_excluded,
                   l.excluded                                                                AS std_excluded
              FROM user_formula_links u,
                   formula_links l
             WHERE u.user_id = " . $this->id . "
               AND u.formula_link_id = l.formula_link_id;";
        } else {
            $sql = "SELECT u.formula_link_id AS id, 
                   l.user_id              AS owner_id, 
                   l.formula_id, 
                   l.phrase_id, 
                   IF(u.link_type_id IS NULL, l.link_type_id, u.link_type_id) AS usr_type, 
                   l.link_type_id                                             AS std_type, 
                   IF(u.excluded     IS NULL, l.excluded,     u.excluded)     AS usr_excluded,
                   l.excluded                                                 AS std_excluded
              FROM user_formula_links u,
                   formula_links l
             WHERE u.user_id = " . $this->id . "
               AND u.formula_link_id = l.formula_link_id;";
        }
        $sbx_lst = $db_con->get_old($sql);

        if (count($sbx_lst) > 0) {
            // prepare to show where the user uses different formula_entry_link than a normal viewer
            $row_nbr = 0;
            $result .= dsp_tbl_start();
            foreach ($sbx_lst as $sbx_row) {
                $row_nbr++;

                // create the formula_link objects with the minimal parameter needed
                $frm_usr = new formula_link($this);
                $frm_usr->set_id($sbx_row['id']);
                $frm_usr->fob->id = $sbx_row[formula::FLD_ID];
                $frm_usr->tob->id = $sbx_row[phrase::FLD_ID];
                $frm_usr->link_type_id = $sbx_row['usr_type'];
                $frm_usr->excluded = $sbx_row['usr_excluded'];
                $frm_usr->load_objects();

                // to review: try to avoid using load_test_user
                $usr_std = new user;
                $usr_std->id = $sbx_row['owner_id'];
                $usr_std->load_test_user();

                $frm_std = clone $frm_usr;
                $frm_std->set_user($usr_std);
                $frm_std->link_type_id = $sbx_row['std_type'];
                $frm_std->excluded = $sbx_row['std_excluded'];

                // check database consistency and correct it if needed
                if ($frm_usr->link_type_id == $frm_std->link_type_id
                    and $frm_usr->excluded == $frm_std->excluded) {
                    $frm_usr->del_usr_cfg();
                } else {

                    // prepare the row formula_links
                    $sandbox_item_name = $frm_usr->fob->name_linked($back);
                    //$sandbox_item_name = $frm_usr->name_linked($back);

                    // format the user formula_link
                    if ($frm_usr->excluded == 1) {
                        $sandbox_usr_txt = "deleted";
                    } else {
                        $sandbox_usr_txt = $frm_usr->tob->dsp_link();
                        //$sandbox_usr_txt = $frm_usr->link_name;
                    }

                    // format the standard formula_link
                    if ($frm_std->excluded == 1) {
                        $sandbox_std_txt = "deleted";
                    } else {
                        $sandbox_std_txt = $frm_std->tob->dsp_link();
                        //$sandbox_std_txt = $frm_std->link_name;
                    }

                    // format the formula_link of other users
                    $sandbox_other = '';
                    $sql_other = "SELECT l.formula_link_id, 
                               u.user_id, 
                               u.link_type_id, 
                               u.excluded
                          FROM user_formula_links u,
                               formula_links l
                         WHERE u.user_id <> " . $this->id . "
                           AND u.formula_link_id = l.formula_link_id
                           AND u.formula_link_id = " . $sbx_row['id'] . "
                           AND (u.excluded <> 1 OR u.excluded is NULL);";
                    log_debug('user_dsp->dsp_sandbox_val other sql (' . $sql_other . ')');
                    $sbx_lst_other = $db_con->get_old($sql_other);
                    foreach ($sbx_lst_other as $frm_lnk_other_row) {
                        $usr_other = new user;
                        $usr_other->id = $frm_lnk_other_row[user::FLD_ID];
                        $usr_other->load_test_user();

                        // to review: load all user formula_links with one query
                        $frm_lnk_other = clone $frm_usr;
                        $frm_lnk_other->set_user($usr_other);
                        $frm_lnk_other->link_type_id = $frm_lnk_other_row['link_type_id'];
                        $frm_lnk_other->excluded = $frm_lnk_other_row[user_sandbox::FLD_EXCLUDED];
                        $frm_lnk_other->load_objects();
                        if ($sandbox_other <> '') {
                            $sandbox_other .= ',';
                        }
                        $sandbox_other .= $frm_lnk_other->tob->dsp_link();
                    }
                    $sandbox_other = '<a href="/http/user_formula_link.php?id=' . $this->id . '&back=' . $back . '">' . $sandbox_other . '</a> ';

                    // create the button
                    $url = '/http/user.php?id=' . $this->id . '&undo_formula_link=' . $sbx_row['id'] . '&back=' . $back;
                    $sandbox_undo_btn = '<td>' . \html\btn_del("Undo your change and use the standard formula_link " . $sbx_row['std_formula_link'], $url) . '</td>';

                    // display the formula_link changes by the user
                    $result .= '<tr>';
                    // display headline
                    if ($row_nbr == 1) {
                        $result .= '<th>Formula</th>';
                        $result .= '<th>you linked to word vs. </th>';
                        $result .= '<th>common</th>';
                        $result .= '<th>other user</th>';
                        $result .= '<th></th>'; // for the buttons
                        $result .= '</tr><tr>';
                    }

                    // display one user adjustment
                    $result .= '<td>' . $sandbox_item_name . '</td>';
                    $result .= '<td>' . $sandbox_usr_txt . '</td>';
                    $result .= '<td>' . $sandbox_std_txt . '</td>';
                    $result .= '<td>' . $sandbox_other . '</td>';
                    $result .= '<td>' . $sandbox_undo_btn . '</td>';

                    $result .= '</tr>';
                }

            }
            $result .= dsp_tbl_end();
        }

        log_debug('done');
        return $result;
    }

    /**
     * display value changes by the user which are not (yet) standard
     */
    function dsp_sandbox_val($back): string
    {
        log_debug($this->id);

        global $db_con;
        $result = ''; // reset the html code var

        // create the databased link
        $db_con->usr_id = $this->id;

        // get all values changed by the user to a non standard value
        if (SQL_DB_TYPE == sql_db::POSTGRES) {
            $sql = "SELECT 
                    u.value_id                                                                         AS id, 
                    v.user_id                                                                          AS owner_id, 
                    CASE WHEN (u.user_value <> '' IS NOT TRUE) THEN v.word_value ELSE u.user_value END AS usr_value, 
                    v.word_value                                                                       AS std_value, 
                    CASE WHEN (u.source_id  <> '' IS NOT TRUE) THEN v.source_id  ELSE u.source_id  END AS usr_source, 
                    v.source_id                                                                        AS std_source, 
                    CASE WHEN (u.excluded   <> '' IS NOT TRUE) THEN v.excluded   ELSE u.excluded   END AS usr_excluded,
                    v.excluded                                                                         AS std_excluded, 
                    v.phrase_group_id,
                    v.time_word_id
               FROM user_values u,
                    values v
              WHERE u.user_id = " . $this->id . "
                AND u.value_id = v.value_id;";
        } else {
            $sql = "SELECT 
                    u.value_id                                           AS id, 
                    v.user_id                                            AS owner_id, 
                    IF(u.user_value IS NULL, v.word_value, u.user_value) AS usr_value, 
                    v.word_value                                         AS std_value, 
                    IF(u.source_id  IS NULL, v.source_id,  u.source_id)  AS usr_source, 
                    v.source_id                                          AS std_source, 
                    IF(u.excluded   IS NULL, v.excluded,   u.excluded)   AS usr_excluded,
                    v.excluded                                           AS std_excluded, 
                    v.phrase_group_id,
                    v.time_word_id
               FROM user_values u,
                    `values` v
              WHERE u.user_id = " . $this->id . "
                AND u.value_id = v.value_id;";
        }
        $val_lst = $db_con->get_old($sql);

        if (count($val_lst) > 0) {
            // prepare to show where the user uses different value than a normal viewer
            $row_nbr = 0;
            $result .= dsp_tbl_start();
            foreach ($val_lst as $val_row) {
                $row_nbr++;

                // create the value objects with the minimal parameter needed
                $val_usr = new value($this);
                $val_usr->set_id($val_row['id']);
                $val_usr->number = $val_row['usr_value'];
                $val_usr->set_source_id($val_row['usr_source']);
                $val_usr->excluded = $val_row['usr_excluded'];
                $val_usr->grp->set_id($val_row['phrase_group_id']);
                $val_usr->set_time_id($val_row[value::FLD_TIME_WORD]);
                $val_usr->load_phrases();

                // to review: try to avoid using load_test_user
                $usr_std = new user;
                $usr_std->id = $val_row['owner_id'];
                $usr_std->load_test_user();

                $val_std = clone $val_usr;
                $val_std->set_user($usr_std);
                $val_std->number = $val_row['std_value'];
                $val_std->set_source_id($val_row['std_source']);
                $val_std->excluded = $val_row['std_excluded'];

                // check database consistency and correct it if needed
                if ($val_usr->number == $val_std->number
                    and $val_usr->source === $val_std->source
                    and $val_usr->excluded == $val_std->excluded) {
                    $val_usr->del_usr_cfg();
                } else {

                    // prepare the row values
                    $sandbox_item_name = '';
                    if (isset($val_usr->wrd_lst)) {
                        $sandbox_item_name = $val_usr->wrd_lst->dsp_obj()->name_linked();
                    }

                    // format the user value
                    if ($val_usr->excluded == 1) {
                        $sandbox_usr_txt = "deleted";
                    } else {
                        $sandbox_usr_txt = $val_usr->val_formatted();
                    }
                    $sandbox_usr_txt = '<a href="/http/value_edit.php?id=' . $val_usr->id() . '&back=' . $back . '">' . $sandbox_usr_txt . '</a>';

                    // format the standard value
                    if ($val_std->excluded == 1) {
                        $sandbox_std_txt = "deleted";
                    } else {
                        $sandbox_std_txt = $val_std->val_formatted();
                    }

                    // format the value of other users
                    $sandbox_other = '';
                    $sql_other = "SELECT v.value_id, 
                               u.user_id, 
                               u.user_value, 
                               u.source_id, 
                               u.excluded
                          FROM user_values u,
                               `values` v
                         WHERE u.user_id <> " . $this->id . "
                           AND u.value_id = v.value_id
                           AND u.value_id = " . $val_row['id'] . "
                           AND (u.excluded <> 1 OR u.excluded is NULL);";
                    log_debug('user_dsp->dsp_sandbox_val other sql (' . $sql_other . ')');
                    $val_lst_other = $db_con->get_old($sql_other);
                    foreach ($val_lst_other as $val_other_row) {
                        $usr_other = new user;
                        $usr_other->id = $val_other_row[user::FLD_ID];
                        $usr_other->load_test_user();

                        // to review: load all user values with one query
                        $val_other = clone $val_usr;
                        $val_other->set_user($usr_other);
                        $val_other->number = $val_other_row['user_value'];
                        $val_other->set_source_id($val_other_row['source_id']);
                        $val_other->excluded = $val_other_row[user_sandbox::FLD_EXCLUDED];
                        if ($sandbox_other <> '') {
                            $sandbox_other .= ',';
                        }
                        $sandbox_other .= $val_other->val_formatted();
                    }
                    $sandbox_other = '<a href="/http/user_value.php?id=' . $this->id . '&back=' . $back . '">' . $sandbox_other . '</a> ';

                    // create the button
                    $url = '/http/user.php?id=' . $this->id . '&undo_value=' . $val_row['id'] . '&back=' . $back;
                    $sandbox_undo_btn = '<td>' . \html\btn_del("Undo your change and use the standard value " . $val_row['std_value'], $url) . '</td>';

                    // display the value changes by the user
                    $result .= '<tr>';
                    // display headline
                    if ($row_nbr == 1) {
                        $result .= '<th>Value</th>';
                        $result .= '<th>your vs. </th>';
                        $result .= '<th>common</th>';
                        $result .= '<th>other user</th>';
                        $result .= '<th></th>'; // for the buttons
                        $result .= '</tr><tr>';
                    }

                    //
                    $result .= '<td>' . $sandbox_item_name . '</td>';
                    $result .= '<td>' . $sandbox_usr_txt . '</td>';
                    $result .= '<td>' . $sandbox_std_txt . '</td>';
                    $result .= '<td>' . $sandbox_other . '</td>';
                    $result .= '<td>' . $sandbox_undo_btn . '</td>';

                    $result .= '</tr>';
                }
            }
            $result .= dsp_tbl_end();
        }

        log_debug('done');
        return $result;
    }

    /**
     * display view changes by the user which are not (yet) standard
     */
    function dsp_sandbox_view($back): string
    {
        log_debug($this->id);

        global $db_con;
        $result = ''; // reset the html code var

        // create the databased link
        $db_con->usr_id = $this->id;

        // get all values changed by the user to a non standard view
        if (SQL_DB_TYPE == sql_db::POSTGRES) {
            $sql = "SELECT 
                    u.view_id AS id, 
                    m.user_id AS owner_id, 
                    CASE WHEN (u.view_name    <> '' IS NOT TRUE) THEN m.view_name    ELSE u.view_name    END AS usr_name, 
                    m.view_name                                                               AS std_name, 
                    CASE WHEN (u.description  <> '' IS NOT TRUE) THEN m.description  ELSE u.description      END AS usr_description, 
                    m.description                                                             AS std_description, 
                    CASE WHEN (u.view_type_id <> '' IS NOT TRUE) THEN m.view_type_id ELSE u.view_type_id END AS usr_type, 
                    m.view_type_id                                                            AS std_type, 
                    CASE WHEN (u.excluded     <> '' IS NOT TRUE) THEN m.excluded     ELSE u.excluded     END AS usr_excluded,
                    m.excluded                                                                AS std_excluded
                FROM user_views u,
                    views m
              WHERE u.user_id = " . $this->id . "
                AND u.view_id = m.view_id;";
        } else {
            $sql = "SELECT 
                    u.view_id AS id, 
                    m.user_id AS owner_id, 
                    IF(u.view_name    IS NULL, m.view_name,    u.view_name)    AS usr_name, 
                    m.view_name                                                AS std_name, 
                    IF(u.description  IS NULL, m.description,  u.description ) AS usr_description, 
                    m.description                                              AS std_description, 
                    IF(u.view_type_id IS NULL, m.view_type_id, u.view_type_id) AS usr_type, 
                    m.view_type_id                                             AS std_type, 
                    IF(u.excluded     IS NULL, m.excluded,     u.excluded)     AS usr_excluded,
                    m.excluded                                                 AS std_excluded
                FROM user_views u,
                    views m
              WHERE u.user_id = " . $this->id . "
                AND u.view_id = m.view_id;";
        }
        $sbx_lst = $db_con->get_old($sql);

        if (count($sbx_lst) > 0) {
            // prepare to show where the user uses different view than a normal viewer
            $row_nbr = 0;
            $result .= dsp_tbl_start();
            foreach ($sbx_lst as $sbx_row) {
                $row_nbr++;

                // create the view objects with the minimal parameter needed
                $dsp_usr = new view_dsp_old($this);
                $dsp_usr->set_id($sbx_row['id']);
                $dsp_usr->set_name($sbx_row['usr_name']);
                $dsp_usr->description = $sbx_row['usr_description'];
                $dsp_usr->type_id = $sbx_row['usr_type'];
                $dsp_usr->excluded = $sbx_row['usr_excluded'];
                $dsp_usr->set_user($this);

                // to review: try to avoid using load_test_user
                $usr_std = new user;
                $usr_std->id = $sbx_row['owner_id'];
                $usr_std->load_test_user();

                $dsp_std = clone $dsp_usr;
                $dsp_std->set_user($usr_std);
                $dsp_std->set_name($sbx_row['std_name']);
                $dsp_std->description = $sbx_row['std_description'];
                $dsp_std->type_id = $sbx_row['std_type'];
                $dsp_std->excluded = $sbx_row['std_excluded'];

                // check database consistency and correct it if needed
                if ($dsp_usr->set_name($dsp_std->name())
                    and $dsp_usr->description == $dsp_std->description
                    and $dsp_usr->type_id == $dsp_std->type_id
                    and $dsp_usr->excluded == $dsp_std->excluded) {
                    $dsp_usr->del_usr_cfg();
                } else {

                    // format the user view
                    if ($dsp_usr->excluded == 1) {
                        $sandbox_usr_txt = "deleted";
                    } else {
                        $sandbox_usr_txt = $dsp_usr->name();
                    }
                    $sandbox_usr_txt = '<a href="/http/view_edit.php?id=' . $dsp_usr->id() . '&back=' . $back . '">' . $sandbox_usr_txt . '</a>';

                    // format the standard view
                    if ($dsp_std->excluded == 1) {
                        $sandbox_std_txt = "deleted";
                    } else {
                        $sandbox_std_txt = $dsp_std->name();
                    }

                    // format the view of other users
                    $sandbox_other = '';
                    $sql_other = "SELECT m.view_id, 
                               u.user_id, 
                               u.view_name, 
                               u.description, 
                               u.view_type_id, 
                               u.excluded
                          FROM user_views u,
                               views m
                         WHERE u.user_id <> " . $this->id . "
                           AND u.view_id = m.view_id
                           AND u.view_id = " . $sbx_row['id'] . "
                           AND (u.excluded <> 1 OR u.excluded is NULL);";
                    log_debug('user_dsp->dsp_sandbox_val other sql (' . $sql_other . ')');
                    $sbx_lst_other = $db_con->get_old($sql_other);
                    foreach ($sbx_lst_other as $dsp_other_row) {
                        $usr_other = new user;
                        $usr_other->id = $dsp_other_row[user::FLD_ID];
                        $usr_other->load_test_user();

                        // to review: load all user views with one query
                        $dsp_other = clone $dsp_usr;
                        $dsp_other->set_user($usr_other);
                        $dsp_other->set_name($dsp_other_row[view::FLD_NAME]);
                        $dsp_other->description = $dsp_other_row[user_sandbox_named::FLD_DESCRIPTION];
                        $dsp_other->type_id = $dsp_other_row[view::FLD_TYPE];
                        $dsp_other->excluded = $dsp_other_row[user_sandbox::FLD_EXCLUDED];
                        if ($sandbox_other <> '') {
                            $sandbox_other .= ',';
                        }
                        $sandbox_other .= $dsp_other->name();
                    }
                    $sandbox_other = '<a href="/http/user_view.php?id=' . $this->id . '&back=' . $back . '">' . $sandbox_other . '</a> ';

                    // create the button
                    $url = '/http/user.php?id=' . $this->id . '&undo_view=' . $sbx_row['id'] . '&back=' . $back;
                    $sandbox_undo_btn = '<td>' . \html\btn_del("Undo your change and use the standard view " . $sbx_row['std_view'], $url) . '</td>';

                    // display the view changes by the user
                    $result .= '<tr>';
                    // display headline
                    if ($row_nbr == 1) {
                        $result .= '<th>View name vs. </th>';
                        $result .= '<th>common</th>';
                        $result .= '<th>other user</th>';
                        $result .= '<th></th>'; // for the buttons
                        $result .= '</tr><tr>';
                    }

                    //
                    //$result .= '<td>'.$sandbox_item_name.'</td>';
                    $result .= '<td>' . $sandbox_usr_txt . '</td>';
                    $result .= '<td>' . $sandbox_std_txt . '</td>';
                    $result .= '<td>' . $sandbox_other . '</td>';
                    $result .= '<td>' . $sandbox_undo_btn . '</td>';

                    $result .= '</tr>';
                }
            }
            $result .= dsp_tbl_end();
        }

        log_debug('done');
        return $result;
    }

    /**
     * display view_component changes by the user which are not (yet) standard
     */
    function dsp_sandbox_view_component($back): string
    {
        log_debug($this->id);

        global $db_con;
        $result = ''; // reset the html code var

        // create the databased link
        $db_con->usr_id = $this->id;

        // get all values changed by the user to a non standard view_component
        if (SQL_DB_TYPE == sql_db::POSTGRES) {
            $sql = "SELECT
                    u.view_component_id AS id, 
                    m.user_id AS owner_id, 
                    CASE WHEN (u.view_component_name    <> '' IS NOT TRUE) THEN m.view_component_name    ELSE u.view_component_name    END AS usr_name, 
                    m.view_component_name                                                                                   AS std_name, 
                    CASE WHEN (u.description            <> '' IS NOT TRUE) THEN m.description            ELSE u.description            END AS usr_description, 
                    m.description                                                                                           AS std_description, 
                    CASE WHEN (u.view_component_type_id <> '' IS NOT TRUE) THEN m.view_component_type_id ELSE u.view_component_type_id END AS usr_type, 
                    m.view_component_type_id                                                                                AS std_type, 
                    CASE WHEN (u.excluded               <> '' IS NOT TRUE) THEN m.excluded               ELSE u.excluded               END AS usr_excluded,
                    m.excluded                                                                                              AS std_excluded
               FROM user_view_components u,
                    view_components m
              WHERE u.user_id = " . $this->id . "
                AND u.view_component_id = m.view_component_id;";
        } else {
            $sql = "SELECT
                    u.view_component_id AS id, 
                    m.user_id AS owner_id, 
                    IF(u.view_component_name    IS NULL, m.view_component_name,    u.view_component_name)    AS usr_name, 
                    m.view_component_name                                                                    AS std_name, 
                    IF(u.description            IS NULL, m.description,            u.description)            AS usr_description, 
                    m.description                                                                            AS std_description, 
                    IF(u.view_component_type_id IS NULL, m.view_component_type_id, u.view_component_type_id) AS usr_type, 
                    m.view_component_type_id                                                                 AS std_type, 
                    IF(u.excluded               IS NULL, m.excluded,               u.excluded)               AS usr_excluded,
                    m.excluded                                                                               AS std_excluded
               FROM user_view_components u,
                    view_components m
              WHERE u.user_id = " . $this->id . "
                AND u.view_component_id = m.view_component_id;";
        }
        $sbx_lst = $db_con->get_old($sql);

        if (count($sbx_lst) > 0) {
            // prepare to show where the user uses different view_component than a normal viewer
            $row_nbr = 0;
            $result .= dsp_tbl_start();
            foreach ($sbx_lst as $sbx_row) {
                $row_nbr++;

                // create the view_component object with the minimal parameter needed
                $dsp_usr = new view_cmp_dsp_old($this);
                $dsp_usr->set_id($sbx_row['id']);
                $dsp_usr->set_name($sbx_row['usr_name']);
                $dsp_usr->description = $sbx_row['usr_comment'];
                $dsp_usr->type_id = $sbx_row['usr_type'];
                $dsp_usr->excluded = $sbx_row['usr_excluded'];

                // to review: try to avoid using load_test_user
                $usr_std = new user;
                $usr_std->id = $sbx_row['owner_id'];
                $usr_std->load_test_user();

                $dsp_std = clone $dsp_usr;
                $dsp_std->set_user($usr_std);
                $dsp_std->set_name($sbx_row['std_name']);
                $dsp_std->description = $sbx_row['std_comment'];
                $dsp_std->type_id = $sbx_row['std_type'];
                $dsp_std->excluded = $sbx_row['std_excluded'];

                // check database consistency and correct it if needed
                if ($dsp_usr->name() == $dsp_std->name()
                    and $dsp_usr->description == $dsp_std->description
                    and $dsp_usr->type_id == $dsp_std->type_id
                    and $dsp_usr->excluded == $dsp_std->excluded) {
                    //$dsp_usr->del_usr_cfg();
                } else {

                    // format the user view_component
                    if ($dsp_usr->excluded == 1) {
                        $sandbox_usr_txt = "deleted";
                    } else {
                        $sandbox_usr_txt = $dsp_usr->name();
                    }
                    $sandbox_usr_txt = '<a href="/http/view_component_edit.php?id=' . $dsp_usr->id() . '&back=' . $back . '">' . $sandbox_usr_txt . '</a>';

                    // format the standard view_component
                    if ($dsp_std->excluded == 1) {
                        $sandbox_std_txt = "deleted";
                    } else {
                        $sandbox_std_txt = $dsp_std->name();
                    }

                    // format the view_component of other users
                    $sandbox_other = '';
                    $sql_other = "SELECT m.view_component_id, 
                               u.user_id, 
                               u.view_component_name, 
                               u.comment, 
                               u.view_component_type_id, 
                               u.excluded
                          FROM user_view_components u,
                               view_components m
                         WHERE u.user_id <> " . $this->id . "
                           AND u.view_component_id = m.view_component_id
                           AND u.view_component_id = " . $sbx_row['id'] . "
                           AND (u.excluded <> 1 OR u.excluded is NULL);";
                    log_debug('user_dsp->dsp_sandbox_val other sql (' . $sql_other . ')');
                    $sbx_lst_other = $db_con->get_old($sql_other);
                    foreach ($sbx_lst_other as $cmp_other_row) {
                        $usr_other = new user;
                        $usr_other->id = $cmp_other_row[user::FLD_ID];
                        $usr_other->load_test_user();

                        // to review: load all user view_components with one query
                        $cmp_other = clone $dsp_usr;
                        $cmp_other->set_user($usr_other);
                        $cmp_other->set_name($cmp_other_row[view_cmp::FLD_NAME]);
                        $cmp_other->description = $cmp_other_row[user_sandbox_named::FLD_DESCRIPTION];
                        $cmp_other->type_id = $cmp_other_row['view_component_type_id'];
                        $cmp_other->excluded = $cmp_other_row[user_sandbox::FLD_EXCLUDED];
                        if ($sandbox_other <> '') {
                            $sandbox_other .= ',';
                        }
                        $sandbox_other .= $cmp_other->name();
                    }
                    $sandbox_other = '<a href="/http/user.php?id=' . $this->id . '&back=' . $back . '">' . $sandbox_other . '</a> ';

                    // create the button
                    $url = '/http/user.php?id=' . $this->id . '&undo_view_component=' . $sbx_row['id'] . '&back=' . $back;
                    $sandbox_undo_btn = '<td>' . \html\btn_del("Undo your change and use the standard view_component " . $sbx_row['std_view_component'], $url) . '</td>';

                    // display the view_component changes by the user
                    $result .= '<tr>';
                    // display headline
                    if ($row_nbr == 1) {
                        $result .= '<th>View component vs. </th>';
                        $result .= '<th>common</th>';
                        $result .= '<th>other user</th>';
                        $result .= '<th></th>'; // for the buttons
                        $result .= '</tr><tr>';
                    }

                    //
                    //$result .= '<td>'.$sandbox_item_name.'</td>';
                    $result .= '<td>' . $sandbox_usr_txt . '</td>';
                    $result .= '<td>' . $sandbox_std_txt . '</td>';
                    $result .= '<td>' . $sandbox_other . '</td>';
                    $result .= '<td>' . $sandbox_undo_btn . '</td>';

                    $result .= '</tr>';
                }
            }
            $result .= dsp_tbl_end();
        }

        log_debug('done');
        return $result;
    }

    /**
     * display view_component_link changes by the user which are not (yet) standard
     */
    function dsp_sandbox_view_link($back): string
    {
        log_debug($this->id);

        global $db_con;
        $result = ''; // reset the html code var

        // create the databased link
        $db_con->usr_id = $this->id;

        // get all values changed by the user to a non standard view_component_link
        if (SQL_DB_TYPE == sql_db::POSTGRES) {
        } else {
            if (SQL_DB_TYPE == sql_db::POSTGRES) {
                $sql = "SELECT 
                    u.view_component_link_id AS id, 
                    l.user_id            AS owner_id, 
                    l.view_id, 
                    l.view_component_id, 
                    CASE WHEN (u.order_nbr     <> '' IS NOT TRUE) THEN l.order_nbr     ELSE u.order_nbr     END AS usr_order, 
                    l.order_nbr                                                                  AS std_order, 
                    CASE WHEN (u.position_type <> '' IS NOT TRUE) THEN l.position_type ELSE u.position_type END AS usr_type, 
                    l.position_type                                                              AS std_type, 
                    CASE WHEN (u.excluded      <> '' IS NOT TRUE) THEN l.excluded      ELSE u.excluded      END AS usr_excluded,
                    l.excluded                                                                   AS std_excluded
               FROM user_view_component_links u,
                    view_component_links l
              WHERE u.user_id = " . $this->id . "
                AND u.view_component_link_id = l.view_component_link_id;";
            } else {
                $sql = "SELECT 
                    u.view_component_link_id AS id, 
                    l.user_id            AS owner_id, 
                    l.view_id, 
                    l.view_component_id, 
                    IF(u.order_nbr     IS NULL, l.order_nbr,     u.order_nbr)     AS usr_order, 
                    l.order_nbr                                                   AS std_order, 
                    IF(u.position_type IS NULL, l.position_type, u.position_type) AS usr_type, 
                    l.position_type                                               AS std_type, 
                    IF(u.excluded      IS NULL, l.excluded,      u.excluded)      AS usr_excluded,
                    l.excluded                                                    AS std_excluded
               FROM user_view_component_links u,
                    view_component_links l
              WHERE u.user_id = " . $this->id . "
                AND u.view_component_link_id = l.view_component_link_id;";
            }
        }
        $sbx_lst = $db_con->get_old($sql);

        if (count($sbx_lst) > 0) {
            // prepare to show where the user uses different view_entry_link than a normal viewer
            $row_nbr = 0;
            $result .= dsp_tbl_start();
            foreach ($sbx_lst as $sbx_row) {
                $row_nbr++;

                // create the view_component_link objects with the minimal parameter needed
                $dsp_usr = new view_cmp_link($this);
                $dsp_usr->set_id($sbx_row['id']);
                $dsp_usr->dsp->set_id($sbx_row[view::FLD_ID]);
                $dsp_usr->cmp->set_id($sbx_row['view_component_id']);
                $dsp_usr->order_nbr = $sbx_row['usr_order'];
                $dsp_usr->position_type = $sbx_row['usr_type'];
                $dsp_usr->excluded = $sbx_row['usr_excluded'];
                $dsp_usr->load_objects();

                // to review: try to avoid using load_test_user
                $usr_std = new user;
                $usr_std->id = $sbx_row['owner_id'];
                $usr_std->load_test_user();

                $dsp_std = clone $dsp_usr;
                $dsp_std->set_user($usr_std);
                $dsp_std->order_nbr = $sbx_row['std_order'];
                $dsp_std->position_type = $sbx_row['std_type'];
                $dsp_std->excluded = $sbx_row['std_excluded'];

                // check database consistency and correct it if needed
                if ($dsp_usr->order_nbr == $dsp_std->order_nbr
                    and $dsp_usr->position_type == $dsp_std->position_type
                    and $dsp_usr->excluded == $dsp_std->excluded) {
                    $dsp_usr->del_usr_cfg();
                } else {

                    // prepare the row view_component_links
                    $sandbox_item_name = $dsp_usr->name_linked($back);

                    // format the user view_component_link
                    if ($dsp_usr->excluded == 1) {
                        $sandbox_usr_txt = "deleted";
                    } else {
                        $sandbox_usr_txt = $dsp_usr->order_nbr;
                    }

                    // format the standard view_component_link
                    if ($dsp_std->excluded == 1) {
                        $sandbox_std_txt = "deleted";
                    } else {
                        $sandbox_std_txt = $dsp_std->order_nbr;
                    }

                    // format the view_component_link of other users
                    $sandbox_other = '';
                    $sql_other = "SELECT l.view_component_link_id, 
                               u.user_id, 
                               u.order_nbr, 
                               u.position_type, 
                               u.excluded
                          FROM user_view_component_links u,
                               view_component_links l
                         WHERE u.user_id <> " . $this->id . "
                           AND u.view_component_link_id = l.view_component_link_id
                           AND u.view_component_link_id = " . $sbx_row['id'] . "
                           AND (u.excluded <> 1 OR u.excluded is NULL);";
                    log_debug('user_dsp->dsp_sandbox_val other sql (' . $sql_other . ')');
                    $sbx_lst_other = $db_con->get_old($sql_other);
                    foreach ($sbx_lst_other as $dsp_lnk_other_row) {
                        $usr_other = new user;
                        $usr_other->id = $dsp_lnk_other_row[user::FLD_ID];
                        $usr_other->load_test_user();

                        // to review: load all user view_component_links with one query
                        $dsp_lnk_other = clone $dsp_usr;
                        $dsp_lnk_other->set_user($usr_other);
                        $dsp_lnk_other->order_nbr = $dsp_lnk_other_row['order_nbr'];
                        $dsp_lnk_other->position_type = $dsp_lnk_other_row['position_type'];
                        $dsp_lnk_other->excluded = $dsp_lnk_other_row[user_sandbox::FLD_EXCLUDED];
                        if ($sandbox_other <> '') {
                            $sandbox_other .= ',';
                        }
                        $sandbox_other .= $dsp_lnk_other->name();
                    }
                    $sandbox_other = '<a href="/http/user_view_component_link.php?id=' . $this->id . '&back=' . $back . '">' . $sandbox_other . '</a> ';

                    // create the button
                    $url = '/http/user.php?id=' . $this->id . '&undo_view_component_link=' . $sbx_row['id'] . '&back=' . $back;
                    $sandbox_undo_btn = '<td>' . \html\btn_del("Undo your change and use the standard view_component_link " . $sbx_row['std_view_component_link'], $url) . '</td>';

                    // display the view_component_link changes by the user
                    $result .= '<tr>';
                    // display headline
                    if ($row_nbr == 1) {
                        $result .= '<th>View link</th>';
                        $result .= '<th>your position vs. </th>';
                        $result .= '<th>common</th>';
                        $result .= '<th>other user</th>';
                        $result .= '<th></th>'; // for the buttons
                        $result .= '</tr><tr>';
                    }

                    // display one user adjustment
                    $result .= '<td>' . $sandbox_item_name . '</td>';
                    $result .= '<td>' . $sandbox_usr_txt . '</td>';
                    $result .= '<td>' . $sandbox_std_txt . '</td>';
                    $result .= '<td>' . $sandbox_other . '</td>';
                    $result .= '<td>' . $sandbox_undo_btn . '</td>';

                    $result .= '</tr>';
                }

            }
            $result .= dsp_tbl_end();
        }

        log_debug('done');
        return $result;
    }

    /**
     * display source changes by the user which are not (yet) standard
     */
    function dsp_sandbox_source($back): string
    {
        log_debug($this->id);

        global $db_con;
        $result = ''; // reset the html code var

        // create the databased link
        $db_con->usr_id = $this->id;

        // get all values changed by the user to a non standard source
        if (SQL_DB_TYPE == sql_db::POSTGRES) {
            $sql = "SELECT
                    u.source_id AS id, 
                    m.user_id   AS owner_id, 
                    CASE WHEN (u.source_name    <> '' IS NOT TRUE) THEN m.source_name    ELSE u.source_name    END AS usr_name, 
                    m.source_name                                                                   AS std_name, 
                    CASE WHEN (u.url            <> '' IS NOT TRUE) THEN m.url            ELSE u.url            END AS usr_url, 
                    m.url                                                                           AS std_url, 
                    CASE WHEN (u.description        <> '' IS NOT TRUE) THEN m.description        ELSE u.description        END AS usr_comment, 
                    m.description                                                                       AS std_comment, 
                    CASE WHEN (u.source_type_id <> '' IS NOT TRUE) THEN m.source_type_id ELSE u.source_type_id END AS usr_type, 
                    m.source_type_id                                                                AS std_type, 
                    CASE WHEN (u.excluded       <> '' IS NOT TRUE) THEN m.excluded       ELSE u.excluded       END AS usr_excluded,
                    m.excluded                                                                      AS std_excluded
               FROM user_sources u,
                    sources m
              WHERE u.user_id = " . $this->id . "
                AND u.source_id = m.source_id;";
        } else {
            $sql = "SELECT
                    u.source_id AS id, 
                    m.user_id   AS owner_id, 
                    IF(u.source_name    IS NULL, m.source_name,    u.source_name)    AS usr_name, 
                    m.source_name                                                    AS std_name, 
                    IF(u.`url`          IS NULL, m.`url`,          u.`url`  )        AS usr_url, 
                    m.url                                                            AS std_url, 
                    IF(u.description        IS NULL, m.description,        u.description)        AS usr_comment, 
                    m.description                                                        AS std_comment, 
                    IF(u.source_type_id IS NULL, m.source_type_id, u.source_type_id) AS usr_type, 
                    m.source_type_id                                                 AS std_type, 
                    IF(u.excluded       IS NULL, m.excluded,       u.excluded)       AS usr_excluded,
                    m.excluded                                                       AS std_excluded
               FROM user_sources u,
                    sources m
              WHERE u.user_id = " . $this->id . "
                AND u.source_id = m.source_id;";
        }
        $sbx_lst = $db_con->get_old($sql);

        if (count($sbx_lst) > 0) {
            // prepare to show where the user uses different source than a normal viewer
            $row_nbr = 0;
            $result .= dsp_tbl_start();
            foreach ($sbx_lst as $sbx_row) {
                $row_nbr++;

                // create the source objects with the minimal parameter needed
                $dsp_usr = new source($this);
                $dsp_usr->set_id($sbx_row['id']);
                $dsp_usr->set_name($sbx_row['usr_name']);
                $dsp_usr->url = $sbx_row['usr_url'];
                $dsp_usr->description = $sbx_row['usr_comment'];
                $dsp_usr->type_id = $sbx_row['usr_type'];
                $dsp_usr->excluded = $sbx_row['usr_excluded'];

                // to review: try to avoid using load_test_user
                $usr_std = new user;
                $usr_std->id = $sbx_row['owner_id'];
                $usr_std->load_test_user();

                $dsp_std = clone $dsp_usr;
                $dsp_std->set_user($usr_std);
                $dsp_std->set_name($sbx_row['std_name']);
                $dsp_std->url = $sbx_row['std_url'];
                $dsp_std->description = $sbx_row['std_comment'];
                $dsp_std->type_id = $sbx_row['std_type'];
                $dsp_std->excluded = $sbx_row['std_excluded'];

                // check database consistency and correct it if needed
                if ($dsp_usr->name() == $dsp_std->name()
                    and $dsp_usr->url == $dsp_std->url
                    and $dsp_usr->description == $dsp_std->description
                    and $dsp_usr->type_id == $dsp_std->type_id
                    and $dsp_usr->excluded == $dsp_std->excluded) {
                    // TODO: add user config also to source?
                    //$dsp_usr->del_usr_cfg();
                    $dsp_usr->del();
                } else {

                    // format the user source
                    if ($dsp_usr->excluded == 1) {
                        $sandbox_usr_txt = "deleted";
                    } else {
                        $sandbox_usr_txt = $dsp_usr->name();
                    }
                    $sandbox_usr_txt = '<a href="/http/source_edit.php?id=' . $dsp_usr->id() . '&back=' . $back . '">' . $sandbox_usr_txt . '</a>';

                    // format the standard source
                    if ($dsp_std->excluded == 1) {
                        $sandbox_std_txt = "deleted";
                    } else {
                        $sandbox_std_txt = $dsp_std->name();
                    }

                    // format the source of other users
                    $sandbox_other = '';
                    $sql_other = "SELECT m.source_id, 
                               u.user_id, 
                               u.source_name, 
                               u.url, 
                               u.description, 
                               u.source_type_id, 
                               u.excluded
                          FROM user_sources u,
                               sources m
                         WHERE u.user_id <> " . $this->id . "
                           AND u.source_id = m.source_id
                           AND u.source_id = " . $sbx_row['id'] . "
                           AND (u.excluded <> 1 OR u.excluded is NULL);";
                    log_debug('user_dsp->dsp_sandbox_val other sql (' . $sql_other . ')');
                    $sbx_lst_other = $db_con->get_old($sql_other);
                    foreach ($sbx_lst_other as $dsp_other_row) {
                        $usr_other = new user;
                        $usr_other->id = $dsp_other_row[user::FLD_ID];
                        $usr_other->load_test_user();

                        // to review: load all user sources with one query
                        $dsp_other = clone $dsp_usr;
                        $dsp_other->set_user($usr_other);
                        $dsp_other->set_name($dsp_other_row['source_name']);
                        $dsp_other->url = $dsp_other_row['url'];
                        $dsp_other->description = $dsp_other_row[user_sandbox_named::FLD_DESCRIPTION];
                        $dsp_other->type_id = $dsp_other_row['source_type_id'];
                        $dsp_other->excluded = $dsp_other_row[user_sandbox::FLD_EXCLUDED];
                        if ($sandbox_other <> '') {
                            $sandbox_other .= ',';
                        }
                        $sandbox_other .= $dsp_other->name();
                    }
                    $sandbox_other = '<a href="/http/user_source.php?id=' . $this->id . '&back=' . $back . '">' . $sandbox_other . '</a> ';

                    // create the button
                    $url = '/http/user.php?id=' . $this->id . '&undo_source=' . $sbx_row['id'] . '&back=' . $back;
                    $sandbox_undo_btn = '<td>' . \html\btn_del("Undo your change and use the standard source " . $sbx_row['std_source'], $url) . '</td>';

                    // display the source changes by the user
                    $result .= '<tr>';
                    // display headline
                    if ($row_nbr == 1) {
                        $result .= '<th>Source name vs. </th>';
                        $result .= '<th>common</th>';
                        $result .= '<th>other user</th>';
                        $result .= '<th></th>'; // for the buttons
                        $result .= '</tr><tr>';
                    }

                    //
                    //$result .= '<td>'.$sandbox_item_name.'</td>';
                    $result .= '<td>' . $sandbox_usr_txt . '</td>';
                    $result .= '<td>' . $sandbox_std_txt . '</td>';
                    $result .= '<td>' . $sandbox_other . '</td>';
                    $result .= '<td>' . $sandbox_undo_btn . '</td>';

                    $result .= '</tr>';
                }
            }
            $result .= dsp_tbl_end();
        }

        log_debug('done');
        return $result;
    }

    /**
     * display changes by the user which are not (yet) standard
     */
    function dsp_sandbox($back): string
    {
        log_debug($this->id . ',b' . $back);
        $result = $this->dsp_sandbox_val($back);
        $result .= $this->dsp_sandbox_frm($back);
        $result .= $this->dsp_sandbox_frm_link($back);
        $result .= $this->dsp_sandbox_wrd($back);
        $result .= $this->dsp_sandbox_wrd_link($back);
        $result .= $this->dsp_sandbox_view($back);
        $result .= $this->dsp_sandbox_view_component($back);
        $result .= $this->dsp_sandbox_view_link($back);
        $result .= $this->dsp_sandbox_source($back);
        return $result;
    }

}
