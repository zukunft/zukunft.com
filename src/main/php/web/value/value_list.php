<?php

/*

    web/value_list.php - the display extension of the api value list object
    ------------------

    to creat the HTML code to display a list of values


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

namespace html\value;

use cfg\const\paths;
use html\const\paths as html_paths;
include_once html_paths::SANDBOX . 'list_dsp.php';
include_once html_paths::HTML . 'button.php';
include_once html_paths::HTML . 'html_base.php';
include_once html_paths::HTML . 'rest_ctrl.php';
include_once html_paths::HTML . 'styles.php';
include_once html_paths::GROUP . 'group.php';
include_once html_paths::GROUP . 'group_list.php';
include_once html_paths::PHRASE . 'phrase_list.php';
include_once html_paths::USER . 'user_message.php';
include_once html_paths::VALUE . 'value.php';
include_once html_paths::WORD . 'word.php';
include_once html_paths::WORD . 'word_list.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED_HELPER . 'CombineObject.php';
include_once paths::SHARED_HELPER . 'IdObject.php';
include_once paths::SHARED_HELPER . 'TextIdObject.php';
include_once paths::SHARED . 'api.php';
include_once paths::SHARED . 'library.php';

use html\button;
use html\group\group;
use html\html_base;
use html\group\group_list;
use html\phrase\phrase_list;
use html\rest_ctrl;
use html\sandbox\list_dsp;
use html\styles;
use html\user\user_message;
use html\word\word;
use html\word\word_list;
use shared\api;
use shared\helper\CombineObject;
use shared\helper\IdObject;
use shared\helper\TextIdObject;
use shared\library;
use shared\const\views as view_shared;

class value_list extends list_dsp
{

    /*
     * set and get
     */

    /**
     * set the vars of a value object based on the given json
     * @param array $json_array an api single object json message
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function api_mapper(array $json_array): user_message
    {
        return parent::api_mapper_list($json_array, new value());
    }


    /*
     * load
     */

    function load_by_phr_lst(phrase_list $phr_lst): bool
    {
        $result = false;
        $rest = new rest_ctrl();

        $data = array();
        $data[api::JSON_LIST_PHRASE_IDS] = $phr_lst->ids();
        $json_body = $rest->api_get(self::class, $data);
        $this->api_mapper($json_body);
        if (!$this->is_empty()) {
            $result = true;
        }
        return $result;
    }


    /*
     * select
     */

    /**
     * get the first value of the list that is related to all given phrase names
     * TODO use a memory db
     * @param array $names list of phrase names
     * @return value|null this first matching value or null if no value is found
     */
    function get_by_names(array $names): ?value
    {
        $result = null;
        foreach ($this->lst() as $val) {
            if ($result == null) {
                if ($val->match_all($names)) {
                    $result = $val;
                }
            }
        }
        return $result;
    }


    /*
     * modify
     */

    /**
     * add a value to the list
     * @returns bool true if the value has been added
     */
    function add(value|IdObject|TextIdObject|CombineObject|null $to_add): bool
    {
        $result = false;
        if (!in_array($to_add->id(), $this->id_lst())) {
            $this->add_direct($to_add);
            $this->set_lst_dirty();
            $result = true;
        }
        return $result;
    }


    /*
     * display
     */

    /**
     * @param phrase_list|null $context_phr_lst list of phrases that are already known to the user by the context of this table and that does not need to be shown to the user again
     * @param string $back
     * @return string the html code to show the values as a table to the user
     */
    function table(phrase_list $context_phr_lst = null, string $back = ''): string
    {
        $html = new html_base();

        // prepare to show where the user uses different word than a normal viewer
        $row_nbr = 0;

        // get the common phrases of the value list e.g. inhabitants, 2019
        $common_phrases = $this->common_phrases();

        // remove the context phrases from the header e.g. inhabitants for a text just about inhabitants
        $header_phrases = clone $common_phrases;
        if ($context_phr_lst != null) {
            $header_phrases->remove($context_phr_lst);
        }

        // if no phrase is left for the header, show 'description' as a dummy replacement
        // TODO make the replacement language and user specific
        if ($header_phrases->count() <= 0) {
            $head_text = 'description';
        } else {
            $head_text = $header_phrases->name_link();
        }

        // TODO add a button to add a new value using
        //$btn_new = $common_phrases->btn_add_value();
        $btn_new = '';

        // display the single values
        $header_rows = '';
        $rows = '';
        foreach ($this->lst() as $val) {
            $row_nbr++;
            if ($row_nbr == 1) {
                $header = $html->th($head_text);
                $header .= $html->th('value');
                $header_rows = $html->tr($header);
            }
            $row = $html->td($val->grp()->name_link_list($common_phrases));
            $row .= $html->td($val->value_edit($back));
            $rows .= $html->tr($row);
            // TODO add button to delete a value or add a similar value
            //$btn_del = $val->btn_del();
            //$btn_add = $val->btn_add();
        }

        return $html->tbl($header_rows . $rows, $html::SIZE_HALF) . $btn_new;
    }


    /*
     * info
     */

    /**
     * @return phrase_list a list of phrases used for each value
     * similar to the model function with the same name
     */
    function common_phrases(): phrase_list
    {
        $lib = new library();
        $grp_lst = $this->phrase_groups();
        $phr_lst = $grp_lst->common_phrases();
        log_debug($lib->dsp_count($phr_lst->lst()));
        return $phr_lst;
    }

    /**
     * return a list of phrase groups for all values of this list
     */
    function phrase_groups(): group_list
    {
        log_debug();
        $lib = new library();
        $grp_lst = new group_list();
        foreach ($this->lst() as $val) {
            $grp = $val->grp();
            if ($grp != null) {
                $grp_lst->lst[] = $grp;
            } else {
                log_err("The phrase group for value " . $val->id() . " cannot be loaded.", "value_list->phrase_groups");
            }
        }

        log_debug($lib->dsp_count($grp_lst->lst));
        return $grp_lst;
    }

    /*
     * to review
     */

    // creates a table of all values related to a word and a related word and all the sub words of the related word
    // e.g. for "ABB" ($this->phr) list all values for the cash flow statement ($phr_row)
    function dsp_table($phr_row, $back): string
    {
        global $usr;

        $result = '';
        $html = new html_base();
        $lib = new library();

        // check the parameters
        if (!isset($this->phr)) {
            $result = log_warning('The main phrase is not set.', "value_list_dsp->dsp_table");
        }
        if ($phr_row->id() == 0) {
            $result = log_warning('The main phrase is not selected.', "value_list_dsp->dsp_table");
        }
        if (!isset($phr_row)) {
            $result = log_warning('The row type is not set.', "value_list_dsp->dsp_table");
        }
        if (get_class($phr_row) <> word::class) {
            $result = log_err('The row is of type ' . get_class($phr_row) . ' but should be a phrase.', "value_list_dsp->dsp_table");
        }
        // if (get_class($phr_row) <> phrase::class) { $result = zu_err('The row is of type '.get_class($phr_row).' but should be a phrase.', "value_list_dsp->dsp_table"); }
        if ($phr_row->id() == 0) {
            $result = log_warning('The row type is not selected.', "value_list_dsp->dsp_table");
        }

        // if parameters are fine display the table
        if ($result == '') {
            log_debug('"' . $phr_row->name . '" for "' . $this->phr->name() . '" and user "' . $usr->name . '"');

            // init the display vars
            $val_main = null; // the "main" value used as a sample for a new value
            $modal_nbr = 1;   // to create a unique id for each modal form; the total number of modal boxes will not get too high, because the user will only see a limited number of values at once

            // create the table headline e.g. cash flow statement
            log_debug('all pre head: ' . $phr_row->name);
            $result .= $phr_row->dsp_tbl_row();
            log_debug('all head: ' . $phr_row->name);
            $result .= '<br>';

            // get all values related to the selecting word, because this is probably the strongest selection and to save time reduce the number of records asap
            $val_lst = $this->phr->val_lst();
            log_debug('all values: ' . $lib->dsp_count($val_lst->lst));

            //$val_lst->load_phrases();
            /*foreach ($val_lst->lst AS $val) {
              zu_debug('value_list_dsp->dsp_table value: '.$val->number().' (group '.$val->grp_id.' and time '.$val->time_id.')');
            }*/

            // get all words related to the value list to be able to define the column and the row names
            $phr_lst_all = $val_lst->phr_lst();
            log_debug('all words: ' . $phr_lst_all->dsp_name());

            // get the time words for the column heads
            $all_time_lst = $val_lst->time_lst();
            log_debug('times ' . $all_time_lst->dsp_name());

            // adjust the time words to display
            $time_phr = $all_time_lst->time_useful();
            $time_lst = null;
            if ($time_phr != null) {
                $time_lst = new phrase_list($time_phr->user());
                $time_lst->add($time_phr);
                log_debug('times sorted ' . $time_lst->name());
            }

            // filter the value list by the time words used
            $used_value_lst = $val_lst->filter_by_time($time_lst);
            log_debug('values in the time period: ' . $lib->dsp_count($used_value_lst->lst));

            // get the word tree for the left side of the table
            $row_wrd_lst = $phr_row->are_and_contains();
            log_debug('row words: ' . $row_wrd_lst->name());

            // add potential differentiators to the word tree
            $word_incl_differentiator_lst = $row_wrd_lst->differentiators_filtered($phr_lst_all);
            log_debug('differentiator words: ' . $word_incl_differentiator_lst->name());
            log_debug('row words after differentiators added: ' . $row_wrd_lst->name());

            // filter the value list by the row words used
            $row_phr_lst_incl = clone $row_wrd_lst;
            log_debug('row phrase list: ' . $row_phr_lst_incl->name());
            $used_value_lst = $used_value_lst->filter_by_phrase_lst($row_phr_lst_incl);
            log_debug('used values for all rows: ' . $lib->dsp_count($used_value_lst->lst));

            // get the common words
            $common_lst = $used_value_lst->common_phrases();
            log_debug('common: ' . $common_lst->dsp_name());

            // get all words not yet part of the table rows, columns or common words
            $extra_phrases = clone $phr_lst_all;
            $extra_phrases->not_in($word_incl_differentiator_lst);
            $extra_phrases->not_in($common_lst);
            if ($time_lst != null) {
                $extra_phrases->not_in($time_lst);
            }
            log_debug('extra phrase, that might need to be added to each table cell: ' . $extra_phrases->dsp_name());

            // display the common words
            // TODO sort the words and use the short form e.g. in mio. CHF instead of in CHF millions
            if (count($common_lst->lst) > 0) {
                $common_text = '(in ';
                foreach ($common_lst->lst as $common_word) {
                    if ($common_word->id() <> $this->phr->id()) {
                        $common_text .= $common_word->dsp_tbl_row();
                    }
                }
                $common_text .= ')';
                $result .= $html->dsp_line_small($common_text);
            }
            $result .= '<br>';

            // display the table
            $result .= $html->dsp_tbl_start();
            $result .= '   <colgroup>' . "\n";
            //$result .= '<col span="'.sizeof($time_lst)+1.'">';
            $result .= '    <col span="7">' . "\n";
            $result .= '  </colgroup>' . "\n";
            $result .= '  <tbody>' . "\n";

            // display the column heads
            $result .= '  <tr>' . "\n";
            $result .= '    <th></th>' . "\n";
            foreach ($time_lst->lst() as $time_word) {
                $result .= $time_word->dsp_obj()->dsp_th($back, styles::STYLE_RIGHT);
            }
            $result .= '  </tr>' . "\n";

            // temp: display the word tree
            $last_words = '';
            $id = 0; // TODO review and rename
            foreach ($row_wrd_lst->lst as $sub_wrd) {
                $wrd_ids = array();
                $wrd_ids[] = $this->phr->id();
                $wrd_ids[] = $sub_wrd->id();
                foreach ($common_lst->id_lst() as $extra_id) {
                    if (!in_array($extra_id, $wrd_ids)) {
                        $wrd_ids[] = $extra_id;
                    }
                }

                // check if row is empty
                $row_has_value = false;
                $grp = new group($usr);
                $grp->load_by_ids(new phr_ids($wrd_ids));
                foreach ($time_lst->lst() as $time_wrd) {
                    $tbl_value = $used_value_lst->get_by_grp($grp, $time_wrd);
                    if ($tbl_value->number() <> "") {
                        $row_has_value = true;
                        $val_main = $tbl_value;
                    }
                }

                if (!$row_has_value) {
                    log_debug('no value found for ' . $grp->name() . ' skip row');
                } else {
                    $result .= '  <tr>' . "\n";
                    $result .= $sub_wrd->dsp_tbl(0);

                    foreach ($time_lst->lst() as $time_wrd) {
                        $val_wrd_ids = $wrd_ids;
                        if (!in_array($time_wrd->id, $val_wrd_ids)) {
                            $val_wrd_ids[] = $time_wrd->id();
                        }

                        // get the phrase group for the value row
                        // to be done for the list at once
                        $grp = new group($usr);
                        $grp->load_by_ids(new phr_ids($val_wrd_ids));
                        $lib = new library();
                        log_debug("val ids " . $lib->dsp_array($val_wrd_ids) . " = " . $grp->id() . ".");

                        $tbl_value = $used_value_lst->get_by_grp($grp, $time_wrd);
                        if ($tbl_value->number() == "") {
                            $result .= '      <td class="' . styles::STYLE_RIGHT . '">' . "\n";

                            // to review
                            $add_phr_lst = clone $common_lst;
                            $add_phr_ids = $common_lst->id_lst();
                            $type_ids = array();
                            foreach ($add_phr_lst->id_lst() as $pos) {
                                $type_ids[] = 0;
                            }

                            if ($sub_wrd->id() > 0) {
                                $add_phr_lst->add($sub_wrd->phrase());
                                $add_phr_ids[] = $sub_wrd->id();
                                $type_ids[] = $sub_wrd->id(); // TODO check if it should not be $type_word_id
                            }
                            // if values for just one column are added, the column head word id is already in the common id list and due to that does not need to be added
                            if (!in_array($time_wrd->id(), $add_phr_ids) and $time_wrd->id() > 0) {
                                $add_phr_lst->add($time_wrd->phrase());
                                $add_phr_ids[] = $time_wrd->id();
                                $type_ids[] = 0;
                            }

                            //$result .= '      '.btn_add_value_fast ($modal_nbr, $add_phr_lst, $common_lst, $back);
                            $result .= '      ' . \html\btn_add_value_fast($modal_nbr, $add_phr_lst, $this->phr, $common_lst, $back);
                            $modal_nbr++;
                            //$result .= '      '.btn_add_value ($add_phr_lst, $type_ids, $back);
                            $result .= '      </td>' . "\n";
                        } else {
                            $result .= $tbl_value->dsp_tbl($back);
                            // maybe display the extra words of this value
                        }
                    }
                    $result .= '  </tr>' . "\n";
                }

                // display the row differentiators
                $sub_wrd->usr = $usr; // to be fixed in the lines before
                log_debug("... get differentiator for " . $sub_wrd->id() . " and user " . $sub_wrd->usr->name . ".");
                // get all potential differentiator words
                $sub_wrd_lst = $sub_wrd->lst();
                $differentiator_words = $sub_wrd_lst->differentiators_filtered($phr_lst_all);
                $sub_phr_lst = $sub_wrd_lst->phrase_lst();
                $differentiator_phrases = $differentiator_words->phrase_lst();
                log_debug("... show differentiator of " . $differentiator_phrases->name() . ".");
                // select only the differentiator words that have a value for the main word
                //$differentiator_phrases = zu_lst_in($differentiator_phrases, $extra_phrases);
                $differentiator_phrases = $differentiator_phrases->filter($extra_phrases);

                // find direct differentiator words
                //$differentiator_type = cl(SQL_LINK_TYPE_DIFFERENTIATOR);
                log_debug("... get differentiator type " . $differentiator_phrases->name() . ".");
                $type_phrases = $sub_phr_lst->differentiators();

                // if there is more than one type of differentiator group the differentiators by type
                // and add on each one an "other" line, if the sum is not 100%

                //foreach ($type_word_ids as $type_word_id) {
                foreach ($type_phrases->lst as $type_phr) {
                    if ($type_phr->id <> 1) {
                        $result .= '  <tr>' . "\n";
                        //$result .= '      <td>&nbsp;</td>';
                        $result .= $type_phr->dsp_tbl(0);
                        $result .= '  </tr>' . "\n";
                    }
                    // display the differentiator rows that are matching to the word type (e.g. the country)
                    //foreach (array_keys($differentiator_phrases) as $diff_word_id) {
                    $time_wrd = null;
                    $diff_phrase = null;
                    foreach ($differentiator_phrases->lst as $diff_phrase) {
                        if ($diff_phrase->is_a($type_phr)) {
                            $result .= '  <tr>' . "\n";
                            //$result .= '      <td>&nbsp;</td>';
                            $result .= $sub_wrd->dsp_tbl(0);
                            $wrd_ids = array();
                            $wrd_ids[] = $this->phr->id();
                            if (!in_array($sub_wrd->id, $wrd_ids)) {
                                $wrd_ids[] = $sub_wrd->id();
                            }
                            if (!in_array($diff_phrase->id, $wrd_ids)) {
                                $wrd_ids[] = $diff_phrase->id();
                            }
                            foreach ($common_lst->id_lst() as $extra_id) {
                                if (!in_array($extra_id, $wrd_ids)) {
                                    $wrd_ids[] = $extra_id;
                                }
                            }

                            foreach ($time_lst->lst() as $time_wrd) {
                                $val_wrd_ids = $wrd_ids;
                                if (!in_array($time_wrd->id, $val_wrd_ids)) {
                                    $val_wrd_ids[] = $time_wrd->id();
                                }

                                // get the phrase group for the value row
                                // to be done for the list at once
                                $grp = new group($usr);
                                $grp->load_by_ids(new phr_ids($val_wrd_ids));
                                $lib = new library();
                                log_debug("val ids " . $lib->dsp_array($val_wrd_ids) . " = " . $grp->id() . ".");

                                $tbl_value = $used_value_lst->get_by_grp($grp, $time_wrd);
                                if ($tbl_value->number() == "") {
                                    $result .= '      <td class="' . styles::STYLE_RIGHT . '">' . "\n";

                                    // to review
                                    $add_phr_lst = $common_lst;
                                    $add_phr_ids = $common_lst->id_lst();
                                    $type_ids = array();
                                    foreach ($add_phr_lst->id_lst() as $pos) {
                                        $type_ids[] = 0;
                                    }

                                    if ($sub_wrd->id() > 0) {
                                        $add_phr_lst->add($sub_wrd->phrase());
                                        $add_phr_ids[] = $sub_wrd->id();
                                        $type_ids[] = $type_phr->id();
                                    }
                                    if ($diff_phrase->id() <> 0) {
                                        $add_phr_lst->add($diff_phrase);
                                        $add_phr_ids[] = $diff_phrase->id();
                                        $type_ids[] = 0;
                                    }
                                    // if values for just one column are added, the column head word id is already in the common id list and due to that does not need to be added
                                    if (!in_array($time_wrd->id, $add_phr_ids) and $time_wrd->id() > 0) {
                                        $add_phr_lst->add($time_wrd->phrase());
                                        $add_phr_ids[] = $time_wrd->id();
                                        $type_ids[] = 0;
                                    }

                                    $result .= '      ' . \html\btn_add_value($add_phr_lst, $type_ids, $back);
                                    $result .= '      </td>' . "\n";
                                } else {
                                    $result .= $tbl_value->dsp_tbl($back);
                                    // maybe display the extra words of this value
                                }
                            }
                            $result .= '  </tr>' . "\n";
                        }
                    }
                    // add a new part value for the sub_word
                    if (!empty($differentiator_phrases)) {
                        $result .= '  <tr>' . "\n";
                        $result .= '      <td class="' . styles::STYLE_RIGHT . '">' . "\n";

                        // to review
                        $add_phr_ids = $common_lst->id_lst();
                        $type_ids = array();
                        foreach ($add_phr_ids as $pos) {
                            $type_ids[] = 0;
                        }

                        $add_phr_ids[] = $sub_wrd->id();
                        if ($time_wrd != null) {
                            $add_phr_ids[] = $time_wrd->id();
                        }
                        if ($diff_phrase != null) {
                            $add_phr_ids[] = $diff_phrase->id();
                        }
                        $type_ids[] = $type_phr->id();
                        $type_ids[] = $type_phr->id();
                        $type_ids[] = $type_phr->id();

                        $result .= '      &nbsp;&nbsp;' . \html\btn_add_value($add_phr_ids, $type_ids, $back);
                        $result .= '      </td>' . "\n";
                        $result .= '  </tr>' . "\n";
                    }
                }

            }

            // allow the user to add a completely new value
            if ($last_words == '') {
                $last_words = $id;
            }

            // add an extra row to add new rows
            $result .= '  <tr>' . "\n";
            $result .= '      <td>' . "\n";

            // offer the user to add a new row related word
            $result .= $phr_row->btn_add($back);
            $result .= '&nbsp;&nbsp;';

            // offer the user to add a new value e.g. to add a value for a new year
            // this extra adds value button is needed for the case that all values are filled and due to that there is no other plus sign on the table
            if (isset($val_main)) {
                foreach ($time_lst->lst() as $time_wrd) {
                    $result .= '      <td class="' . styles::STYLE_RIGHT . '">' . "\n";
                    $result .= $val_main->btn_add($back);
                    $result .= '      </td>' . "\n";
                }
            }

            $result .= '      </td>' . "\n";
            $result .= '  </tr>' . "\n";

            $result .= '    </tbody>' . "\n";
            $result .= $html->dsp_tbl_end();

            $result .= '<br><br>';

        }
        log_debug("... done");

        return $result;
    }

    /**
     * return the html code to display all values related to a given word
     * $phr->id is the related word that should not be included in the display
     * $this->user()->id() is a parameter, because the viewer must not be the owner of the value
     * TODO add back
     */
    function html($back): string
    {
        $lib = new library();
        $html = new html_base();
        log_debug($lib->dsp_count($this->lst()));
        $result = '';

        $html = new html_base();

        // get common words
        $common_phr_ids = array();
        foreach ($this->lst() as $val) {
            if ($val->check() > 0) {
                log_warning('The group id for value ' . $val->id . ' has not been updated, but should now be correct.', "value_list->html");
            }
            $val->load_phrases();
            log_debug('value_list->html loaded');
            $val_phr_lst = $val->phr_lst;
            if ($val_phr_lst->count() > 0) {
                log_debug('get words ' . $val->phr_lst->dsp_id() . ' for "' . $val->number() . '" (' . $val->id . ')');
                if (empty($common_phr_ids)) {
                    $common_phr_ids = $val_phr_lst->id_lst();
                } else {
                    $common_phr_ids = array_intersect($common_phr_ids, $val_phr_lst->id_lst());
                }
            }
        }

        log_debug('common ');
        $common_phr_ids = array_diff($common_phr_ids, array($this->ids()));  // exclude the list word
        $common_phr_ids = array_values($common_phr_ids);            // cleanup the array

        // display the common words
        log_debug('common dsp');
        if (!empty($common_phr_ids)) {
            $common_phr_lst = new word_list();
            $common_phr_lst->load_by_ids($common_phr_ids);
            $common_phr_lst_dsp = $common_phr_lst->dsp_obj();
            $result .= ' in (' . implode(",", $common_phr_lst_dsp->names_linked()) . ')<br>';
        }

        // instead of the saved result maybe display the calculated result based on formulas that matches the word pattern
        log_debug('tbl_start');
        $result .= $html->dsp_tbl_start();

        // to avoid repeating the same words in each line and to offer a useful "add new value"
        $last_phr_lst = array();

        log_debug('add new button');
        foreach ($this->lst() as $val) {
            //$this->user()->id()  = $val->user()->id();

            // get the words
            $val->load_phrases();
            if (isset($val->phr_lst)) {
                $val_phr_lst = $val->phr_lst;

                // remove the main word from the list, because it should not be shown on each line
                log_debug('remove main ' . $val->id);
                $dsp_phr_lst = $val_phr_lst->dsp_obj();
                log_debug('cloned ' . $val->id);
                if (isset($this->phr)) {
                    if ($this->phr->id() != null) {
                        $dsp_phr_lst->diff_by_ids(array($this->phr->id()));
                    }
                }
                log_debug('removed ' . $this->phr->id());
                $dsp_phr_lst->diff_by_ids($common_phr_ids);
                // remove the words of the previous row, because it should not be shown on each line
                if (isset($last_phr_lst->ids)) {
                    $dsp_phr_lst->diff_by_ids($last_phr_lst->ids);
                }

                //if (isset($val->time_phr)) {
                log_debug('add time ' . $val->id);
                if ($val->time_phr != null) {
                    if ($val->time_phr->id() > 0) {
                        $time_phr = new phrase($val->user());
                        $time_phr->load_by_id($val->time_phr->id());
                        $val->time_phr = $time_phr;
                        $dsp_phr_lst->add($time_phr);
                        log_debug('add time word ' . $val->time_phr->name());
                    }
                }

                $result .= '  <tr>';
                $result .= '    <td>';
                log_debug('linked words ' . $val->id);
                $ref_edit = $val->dsp_obj()->ref_edit();
                $result .= '      ' . $dsp_phr_lst->name_linked() . $ref_edit;
                log_debug('linked words ' . $val->id . ' done');
                // to review
                // list the related results
                $res_lst = new result_list($this->user());
                $res_lst->load_by_val($val);
                $result .= $res_lst->frm_links_html();
                $result .= '    </td>';
                log_debug('formula results ' . $val->id . ' loaded');

                // the reused button object

                if ($last_phr_lst != $val_phr_lst) {
                    $last_phr_lst = $val_phr_lst;
                    $result .= '    <td>';
                    $url = $html->url(view_shared::VALUE_ADD, $val->id(), $back);
                    $btn = new button($url, $back);
                    $result .= \html\btn_add_value($val_phr_lst, Null, $this->phr->id());

                    $result .= '    </td>';
                }
                $result .= '    <td>';
                $url = $html->url(view_shared::VALUE_EDIT, $val->id(), $back);
                $btn = new button($url, $back);
                $result .= '      ' . $btn->edit_value($val_phr_lst, $val->id, $this->phr->id());
                $result .= '    </td>';
                $result .= '    <td>';
                $url = $html->url(view_shared::VALUE_DEL, $val->id(), $back);
                $btn = new button($url, $back);
                $result .= '      ' . $btn->del_value($val_phr_lst, $val->id, $this->phr->id());
                $result .= '    </td>';
                $result .= '  </tr>';
            }
        }
        log_debug('add new button done');

        $result .= $html->dsp_tbl_end();

        // allow the user to add a completely new value
        log_debug('new');
        if (empty($common_phr_ids)) {
            $common_phr_lst_new = new word_list($this->user());
            $common_phr_ids[] = $this->phr->id();
            $common_phr_lst_new->load_by_ids($common_phr_ids);
        }

        $common_phr_lst = $common_phr_lst->phrase_lst();

        // TODO review probably wrong call from /var/www/default/src/main/php/model/view/view.php(267): component_dsp->all(Object(word), 291, 17
        /*
        if (get_class($this->phr) == word::class or get_class($this->phr) == word::class) {
            $this->phr = $this->phr->phrase();
        }
        */
        if ($common_phr_lst->is_valid()) {
            if (!empty($common_phr_lst->lst())) {
                $common_phr_lst->add($this->phr);
                $phr_lst_dsp = new phrase_list($common_phr_lst->api_json());
                $result .= $phr_lst_dsp->btn_add_value($back);
            }
        }

        log_debug("value_list->html ... done");

        return $result;
    }


}
