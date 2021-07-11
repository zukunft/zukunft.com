<?php

/*

  word_link.php - the object that links two words (an RDF triple)
  -------------
  
  A link can also be used in replacement for a word
  e.g. Zurich (Company) where the the link "Zurich is a company" is used 
  
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

class word_link extends word_link_object
{

    // the word link object
    public ?word_link_object $from = null; // the first object (either word, triple or group)
    public ?verb $verb = null; // the link type object
    public ?word_link_object $to = null; // the second object (either word, triple or group)

    // database fields additional to the user sandbox fields
    // TODO split the db link object from the word link object
    public ?int $from_id = null; // the id of the first phrase (a positive id is a word and a negative a triple)
    public ?int $verb_id = null; // the id of the link_type (negative of only the reverse link is valid)
    public ?int $to_id = null; // the id of the second phrase (a positive id is a word and a negative a triple)
    public ?string $description = null;   // the description that may differ from the generic created text e.g. Zurich AG instead of Zurich (Company); if the description is empty the generic created name is used

    // in memory only fields
    public ?string $verb_name = null;   // the name of the link type object (verb)

    // user_sandbox usages
    // $name is the generic created name or the description if set is saved in the database for faster check on duplicates by using the database unique index function
    // $from_name the name of the first object (either word, triple or group)
    // $to_name the name of the second object (either word, triple or group)

    // not used any more
    //public $from_type   = null; // the type id of the first word (either word, word link or word group)
    //public $to_type     = null; // the type id of the second word (either word, word link or word group)


    function __construct()
    {
        $this->obj_type = user_sandbox::TYPE_LINK;
        $this->obj_name = DB_TYPE_WORD_LINK;
        $this->from_name = DB_TYPE_PHRASE;
        $this->to_name = DB_TYPE_PHRASE;

        $this->rename_can_switch = UI_CAN_CHANGE_WORD_LINK_NAME;
    }

    // reset the in memory fields used e.g. if some ids are updated
    function reset()
    {
        $this->id = null;
        $this->usr_cfg_id = null;
        $this->usr = null;
        $this->owner_id = null;
        $this->excluded = null;

        $this->from = null;
        $this->from_name = '';
        $this->verb = null;
        $this->verb_name = '';
        $this->to = null;
        $this->to_name = '';
    }

    private function row_mapper($db_row, $map_usr_fields = false)
    {
        if ($db_row != null) {
            if ($db_row['word_link_id'] > 0) {
                $this->id = $db_row['word_link_id'];
                $this->owner_id = $db_row['user_id'];
                $this->from_id = $db_row['from_phrase_id'];
                $this->to_id = $db_row['to_phrase_id'];
                $this->verb_id = $db_row['verb_id'];
                $this->name = $db_row['word_link_name'];
                $this->description = $db_row['description'];
                $this->excluded = $db_row['excluded'];
                if ($map_usr_fields) {
                    $this->usr_cfg_id = $db_row['user_word_link_id'];
                    $this->owner_id = $db_row['user_id'];
                    //$this->share_id = $db_row['share_type_id'];
                    //$this->protection_id = $db_row['protection_type_id'];
                } else {
                    //$this->share_id = cl(DBL_SHARE_PUBLIC);
                    //$this->protection_id = cl(DBL_PROTECT_NO);
                }
            } else {
                $this->id = 0;
            }
        } else {
            $this->id = 0;
        }
    }

    // if needed reverse the order if the user has entered it the other way round
    // e.g. "Cask Flow Statement" "contains" "Taxes" instead of "Taxes" "is part of" "Cask Flow Statement"
    private function check_order()
    {
        if ($this->verb_id < 0) {
            $to = $this->to;
            $to_id = $this->to_id;
            $to_name = $this->to_name;
            $this->to = $this->from;
            $this->to_id = $this->from_id;
            $this->to_name = $this->from_name;
            $this->verb_id = $this->verb_id * -1;
            if (isset($this->verb)) {
                $this->verb_name = $this->verb->reverse;
            }
            $this->from = $to;
            $this->from_id = $to_id;
            $this->from_name = $to_name;
            log_debug('word_link->check_order -> reversed');
        }
    }

    // load the word link without the linked objects, because in many cases the object are already loaded by the caller
    // similar to term->load, but with a different use of verbs
    function load_objects(): bool
    {
        log_debug('word_link->load_objects.' . $this->from_id . ' ' . $this->verb_id . ' ' . $this->to_id . '');
        $result = true;

        // after every load call from outside the class the order should be check and reversed if needed
        $this->check_order();

        // load word from
        if (!isset($this->from) and $this->from_id <> 0 and !is_null($this->usr->id)) {
            if ($this->from_id > 0) {
                $wrd = new word_dsp;
                $wrd->id = $this->from_id;
                $wrd->usr = $this->usr;
                $wrd->load();
                if ($wrd->name <> '') {
                    $this->from = $wrd;
                    $this->from_name = $wrd->name;
                } else {
                    log_err('Failed to load first word of phrase ' . $this->dsp_id());
                    $result = false;
                }
            } elseif ($this->from_id < 0) {
                $lnk = new word_link;
                $lnk->id = $this->from_id * -1;
                $lnk->usr = $this->usr;
                $lnk->load();
                if ($lnk->id > 0) {
                    $this->from = $lnk;
                    $this->from_name = $lnk->name();
                } else {
                    log_err('Failed to load first phrase of phrase ' . $this->dsp_id());
                    $result = false;
                }
            } else {
                // if type is not (yet) set, create a dummy object to enable the selection
                $phr = new phrase;
                $phr->usr = $this->usr;
                $this->from = $phr;
            }
            log_debug('word_link->load_objects -> from ' . $this->from_name);
        } else {
            if (!isset($this->from)) {
                log_err("The word (" . $this->from_id . ") must be set before it can be loaded.", "word_link->load_objects");
            }
        }

        // load verb
        if (!isset($this->verb) and $this->verb_id <> 0 and !is_null($this->usr->id)) {
            $vrb = new verb;
            $vrb->id = $this->verb_id;
            $vrb->usr = $this->usr;
            $vrb->load();
            $this->verb = $vrb;
            $this->verb_name = $vrb->name;
            log_debug('word_link->load_objects -> verb ' . $this->verb_name);
        } else {
            if (!isset($this->verb)) {
                log_err("The verb (" . $this->verb_id . ") must be set before it can be loaded.", "word_link->load_objects");
            }
        }

        // load word to
        if (!isset($this->to) and $this->to_id <> 0 and !is_null($this->usr->id)) {
            if ($this->to_id > 0) {
                $wrd_to = new word_dsp;
                $wrd_to->id = $this->to_id;
                $wrd_to->usr = $this->usr;
                $wrd_to->load();
                if ($wrd_to->name <> '') {
                    $this->to = $wrd_to;
                    $this->to_name = $wrd_to->name;
                } else {
                    log_err('Failed to load second word of phrase ' . $this->dsp_id());
                    $result = false;
                }
            } elseif ($this->to_id < 0) {
                $lnk = new word_link;
                $lnk->id = $this->to_id * -1;
                $lnk->usr = $this->usr;
                $lnk->load();
                if ($lnk->id > 0) {
                    $this->to = $lnk;
                    $this->to_name = $lnk->name();
                } else {
                    log_err('Failed to load second phrase of phrase ' . $this->dsp_id());
                    $result = false;
                }
            } else {
                // if type is not (yet) set, create a dummy object to enable the selection
                $phr_to = new phrase;
                $phr_to->usr = $this->usr;
                $this->to = $phr_to;
            }
            log_debug('word_link->load_objects -> to ' . $this->to_name);
        } else {
            if (!isset($this->to)) {
                if ($this->to_id == 0) {
                    // set a dummy word
                    $wrd_to = new word_dsp;
                    $wrd_to->usr = $this->usr;
                    $this->to = $wrd_to;
                }
            }
        }
        return $result;
    }

    function load_standard(): bool
    {
        global $db_con;
        $result = false;

        // after every load call from outside the class the order should be check and reversed if needed
        $this->check_order();

        // set the where clause depending on the values given
        // TODO create with $db_con->set_where_link
        $sql_where = '';
        if ($this->id > 0) {
            $sql_where = "word_link_id = " . $this->id;
        } elseif ($this->from_id <> 0
            and $this->verb_id > 0
            and $this->to_id <> 0) {
            $sql_where = "from_phrase_id = " . sf($this->from_id) . "
                      AND verb_id        = " . sf($this->verb_id) . "
                      AND to_phrase_id   = " . sf($this->to_id);
            // search for a backward link e.g. Cask Flow Statement contains Taxes
        } elseif ($this->from_id <> 0
            and $this->verb_id < 0
            and $this->to_id <> 0) {

            $sql_where = "from_phrase_id = " . sf($this->to_id) . "
                      AND verb_id        = " . sf($this->verb_id) . "
                      AND to_phrase_id   = " . sf($this->from_id);
        }

        if ($sql_where == '') {
            log_err('The database ID (' . $this->id . ') or the word and verb ids (' . $this->from_id . ',' . $this->verb_id . ',' . $this->to_id . ') must be set to load a triple.', "word_link->load");
        } else {
            $db_con->set_type(DB_TYPE_WORD_LINK);
            $db_con->set_usr($this->usr->id);
            $db_con->set_link_fields('from_phrase_id', 'to_phrase_id', 'verb_id');
            $db_con->set_fields(array('description', 'excluded'));
            $db_con->set_where_text($sql_where);
            $sql = $db_con->select();

            $db_lnk = $db_con->get1($sql);
            $this->row_mapper($db_lnk);
            $result = $this->load_owner();

            // automatically update the generic name
            if ($result) {
                $this->load_objects();
                $new_name = $this->name();
                log_debug('word_link->load_standard check if name ' . $this->dsp_id() . ' needs to be updated to "' . $new_name . '"');
                if ($new_name <> $this->name) {
                    $db_con->set_type(DB_TYPE_WORD_LINK);
                    $result = $db_con->update($this->id, 'word_link_name', $new_name);
                    $this->name = $new_name;
                }
            }
            log_debug('word_link->load_standard ... done (' . $this->description . ')');
        }
        return $result;
    }

    // load the word link without the linked objects, because in many cases the object are already loaded by the caller
    function load(): bool
    {
        global $db_con;
        $result = false;

        // after every load call from outside the class the order should be check and reversed if needed
        $this->check_order();

        // set the where clause depending on the values given
        $sql_where = '';
        if ($this->id > 0 and !is_null($this->usr->id)) {
            $sql_where = "s.word_link_id = " . $this->id;
            // search for a forward link e.g. Taxes is part of Cask Flow Statement
        } elseif ($this->from_id <> 0
            and $this->verb_id > 0
            and $this->to_id <> 0
            and !is_null($this->usr->id)) {
            $sql_where = "s.from_phrase_id = " . sf($this->from_id) . "
                      AND s.verb_id        = " . sf($this->verb_id) . "
                      AND s.to_phrase_id   = " . sf($this->to_id);
            // search for a backward link e.g. Cask Flow Statement contains Taxes
        } elseif ($this->from_id <> 0
            and $this->verb_id < 0
            and $this->to_id <> 0
            and !is_null($this->usr->id)) {
            $sql_where = "s.from_phrase_id = " . sf($this->to_id) . "
                      AND s.verb_id        = " . sf($this->verb_id) . "
                      AND s.to_phrase_id   = " . sf($this->from_id);
            /*
            // if the search including the type is not requested, try without the type
            } elseif ($this->from_id  <> 0
                  AND $this->verb_id   > 0
                  AND $this->to_id    <> 0
                  AND !is_null($this->usr->id)) {
              $sql_where  =      "s.from_phrase_id = ".sf($this->from_id)."
                              AND s.verb_id        = ".sf($this->verb_id)."
                              AND s.to_phrase_id   = ".sf($this->to_id);
            */
        } elseif ($this->name <> '' and !is_null($this->usr->id)) {
            $sql_where = "s.word_link_name = " . sf($this->name, sql_db::FLD_FORMAT_TEXT);
        }

        if ($sql_where == '') {
            if (is_null($this->usr->id)) {
                log_err("The user id must be set to load a word.", "word_link->load");
            } else {
                log_err('Either the database ID (' . $this->id . '), unique word link (' . $this->from_id . ',' . $this->verb_id . ',' . $this->to_id . ') or the name (' . $this->name . ') and the user (' . $this->usr->id . ') must be set to load a word link.', "word_link->load");
            }
        } else {
            // similar statement used in word_link_list->load, check if changes should be repeated in word_link_list.php
            $db_con->set_type(DB_TYPE_WORD_LINK);
            $db_con->set_usr($this->usr->id);
            $db_con->set_link_fields('from_phrase_id', 'to_phrase_id', 'verb_id');
            $db_con->set_usr_fields(array('description'));
            $db_con->set_usr_num_fields(array('excluded'));
            $db_con->set_where_text($sql_where);
            $sql = $db_con->select();
            $db_lnk = $db_con->get1($sql);
            $this->row_mapper($db_lnk, true);
            if ($this->id > 0) {
                // automatically update the generic name
                $this->load_objects();
                $new_name = $this->name();
                log_debug('word_link->load check if name ' . $this->dsp_id() . ' needs to be updated to "' . $new_name . '"');
                if ($new_name <> $this->name) {
                    $db_con->set_type(DB_TYPE_WORD_LINK);
                    $db_con->update($this->id, 'word_link_name', $new_name);
                    $this->name = $new_name;
                }
                $result = true;
            } else {
                $this->id = 0;
            }
            log_debug('word_link->load ... done (' . $this->name() . ')');
        }
        return $result;
    }

    // recursive function to include the foaf words for this triple
    function wrd_lst()
    {
        log_debug('word_link->wrd_lst ' . $this->dsp_id());
        $wrd_lst = new word_list;
        $wrd_lst->usr = $this->usr;

        // add the "from" side
        if (isset($this->from)) {
            if ($this->from->id > 0) {
                $wrd_lst->add($this->from);
            } elseif ($this->from->id < 0) {
                $sub_wrd_lst = $this->from->wrd_lst();
                foreach ($sub_wrd_lst as $wrd) {
                    $wrd_lst->add($wrd);
                }
            } else {
                log_err('The from phrase ' . $this->from->dsp_id() . ' should not have the id 0', 'word_link->wrd_lst');
            }
        }

        // add the "to" side
        if (isset($this->to)) {
            if ($this->to->id > 0) {
                $wrd_lst->add($this->to);
            } elseif ($this->to->id < 0) {
                $sub_wrd_lst = $this->to->wrd_lst();
                foreach ($sub_wrd_lst as $wrd) {
                    $wrd_lst->add($wrd);
                }
            } else {
                log_err('The to phrase ' . $this->to->dsp_id() . ' should not have the id 0', 'word_link->wrd_lst');
            }
        }

        log_debug('word_link->wrd_lst -> (' . $wrd_lst->name() . ')');
        return $wrd_lst;
    }


    // create an object for the export
    function export_obj()
    {
        log_debug('word_link->export_obj');
        $result = new word_link();

        if ($this->name <> '') {
            $result->name = $this->name;
        }
        if ($this->description <> '') {
            $result->description = $this->description;
        }
        $result->from = $this->from_name;
        $result->verb = $this->verb->name;
        $result->to = $this->to_name;

        log_debug('word_link->export_obj -> ' . json_encode($result));
        return $result;
    }

    // import a view from an object
    function import_obj($json_obj)
    {
        log_debug('word_link->import_obj');
        $result = '';

        foreach ($json_obj as $key => $value) {
            if ($key == 'word_link_name') {
                $this->name = $value;
            }
            if ($key == 'description') {
                $this->description = $value;
            }
            if ($key == 'from') {
                $phr_from = new phrase;
                $phr_from->name = $value;
                $phr_from->usr = $this->usr;
                $phr_from->load();
                if ($phr_from->id == 0) {
                    $wrd = new word;
                    $wrd->name = $value;
                    $wrd->usr = $this->usr;
                    $wrd->load();
                    if ($wrd->id == 0) {
                        $wrd->name = $value;
                        $wrd->type_id = cl(DBL_WORD_TYPE_NORMAL);
                        $wrd->save();
                    }
                    if ($wrd->id == 0) {
                        log_err('Cannot add from word "' . $value . '" when importing ' . $this->dsp_id(), 'word_link->import_obj');
                    } else {
                        $this->from = $wrd;
                        $this->from_id = $wrd->id;
                        $this->from_name = $wrd->name;
                    }
                } else {
                    $this->from = $phr_from;
                    $this->from_id = $phr_from->id;
                    $this->from_name = $phr_from->name;
                }
            }
            if ($key == 'to') {
                $phr_to = new phrase;
                $phr_to->name = $value;
                $phr_to->usr = $this->usr;
                $phr_to->load();
                if ($phr_to->id == 0) {
                    $wrd = new word;
                    $wrd->name = $value;
                    $wrd->usr = $this->usr;
                    $wrd->load();
                    if ($wrd->id == 0) {
                        $wrd->name = $value;
                        $wrd->type_id = cl(DBL_WORD_TYPE_NORMAL);
                        $wrd->save();
                    }
                    if ($wrd->id == 0) {
                        log_err('Cannot add to word "' . $value . '" when importing ' . $this->dsp_id(), 'word_link->import_obj');
                    } else {
                        $this->to = $wrd;
                        $this->to_id = $wrd->id;
                        $this->to_name = $wrd->name;
                    }
                } else {
                    $this->to = $phr_to;
                    $this->to_id = $phr_to->id;
                    $this->to_name = $phr_to->name;
                }
            }
            if ($key == 'verb') {
                $vrb = new verb;
                $vrb->name = $value;
                $vrb->usr = $this->usr;
                $vrb->load();
                if ($vrb->id <= 0) {
                    // TODO add an error message
                    $result .= ' verb "' . $value . '" not found';
                    if ($this->name <> '') {
                        $result .= ' for triple "' . $this->name . '"';
                    }
                } else {
                    $this->verb = $vrb;
                    $this->verb_id = $vrb->id;
                    $this->verb_name = $vrb->name;
                }
            }
        }
        if ($result == '') {
            $this->save();
            log_debug('word_link->import_obj -> ' . $this->dsp_id());
        } else {
            log_debug('word_link->import_obj -> ' . $result);
        }

        return $result;
    }

    /*

    display functions

    */

    // display the unique id fields
    // TODO check if $this->load_objects(); needs to be called from the calling function upfront
    function dsp_id(): string
    {
        $result = '';

        if ($this->from_name <> '' and $this->verb_name <> '' and $this->to_name <> '') {
            $result .= $this->from_name . ' '; // e.g. Australia
            $result .= $this->verb_name . ' '; // e.g. is a
            $result .= $this->to_name;       // e.g. Country
        }
        $result .= ' (' . $this->from_id . ',' . $this->verb_id . ',' . $this->to_id;
        if ($this->id > 0) {
            $result .= ' -> ' . $this->id . ')';
        }
        if (isset($this->usr)) {
            $result .= ' for user ' . $this->usr->id . ' (' . $this->usr->name . ')';
        }
        return $result;
    }

    // either the user edited description or the
    // Australia is a Country
    function name()
    {
        $result = '';

        if ($this->excluded <> 1) {
            // use the user defined description
            if ($this->description <> '') {
                $result = $this->description;
            } else {
                $result = $this->from_name . ' ' . $this->verb_name . ' ' . $this->to_name;
            }
        }

        return $result;
    }

    // same as name, but only for non debug usage
    // TODO check if name or name_usr should be used
    function name_usr()
    {
        $result = '';

        if ($this->excluded <> 1) {
            // use the user defined description
            if ($this->description <> '') {
                $result = $this->description;
                // or use special verb based generic description
            } elseif ($this->verb_id == cl(DBL_LINK_TYPE_IS)) {
                $result = $this->from_name . ' (' . $this->to_name . ')';
                // or use the standard generic description
            } else {
                $result = $this->from_name . ' ' . $this->verb_name . ' ' . $this->to_name;
            }
        }

        return $result;
    }

    // returns either the user defined description or the dynamic created description
    // TODO check where the function or the db value should be used
    function description()
    {
        return $this->name_usr();
    }

    // display one link to the user by returning the HTML code for the link to the calling function
    // to do: include the user sandbox in the selection
    private function dsp()
    {
        log_debug("word_link->dsp " . $this->id . ".");

        $result = ''; // reset the html code var

        // get the link from the database
        $this->load_objects();

        // prepare to show the word link
        $result .= $this->from_name . ' '; // e.g. Australia
        $result .= $this->verb_name . ' '; // e.g. is a
        $result .= $this->to_name;       // e.g. Country

        return $result;
    }

    // similar to dsp, but display the reverse expression
    private function dsp_r()
    {
        log_debug("word_link->dsp_r " . $this->id . ".");

        $result = ''; // reset the html code var

        // get the link from the database
        $this->load_objects();

        // prepare to show the word link
        $result .= $this->to_name . ' ';   // e.g. Countries
        $result .= $this->verb_name . ' '; // e.g. are
        $result .= $this->from_name;     // e.g. Australia (and others)

        return $result;
    }

    // display a bottom to edit the word link in a table cell
    function dsp_btn_edit($wrd)
    {
        log_debug("word_link->dsp_btn_edit (" . $this->id . ",b" . $wrd->id . ")");
        $result = ''; // reset the html code var

        // get the link from the database
        $result .= '    <td>' . "\n";
        $result .= btn_edit("edit word link", "/http/link_edit.php?id=" . $this->id . "&back=" . $wrd->id);
        $result .= '    </td>' . "\n";

        return $result;
    }

    // display a form to create a triple
    function dsp_add($back)
    {
        log_debug("word_link->dsp_add.");
        $result = ''; // reset the html code var

        // at least to create the dummy objects to display the selectors
        $this->load_objects();

        // for creating a new triple the first word / triple is fixed
        $form_name = 'link_add';
        //$result .= 'Create a combined word (semantic triple):<br>';
        $result .= '<br>Define a new relation for <br><br>';
        $result .= '<b>' . $this->from_name . '</b> ';
        $result .= dsp_form_start($form_name);
        $result .= dsp_form_hidden("back", $back);
        $result .= dsp_form_hidden("confirm", '1');
        $result .= dsp_form_hidden("from", $this->from_id);
        $result .= '<div class="form-row">';
        if (isset($this->verb)) {
            $result .= $this->verb->dsp_selector('both', $form_name, "col-sm-6", $back);
        }
        if (isset($this->to)) {
            $result .= $this->to->dsp_selector(0, $form_name, 0, "col-sm-6", $back);
        }
        $result .= '</div>';
        $result .= '<br>';
        $result .= dsp_form_end('', $back);

        return $result;
    }

    // display a form to adjust the link between too words or triples
    function dsp_edit($back)
    {
        log_debug("word_link->dsp_edit id " . $this->id . " for user" . $this->usr->id . ".");
        $result = ''; // reset the html code var

        // at least to create the dummy objects to display the selectors
        $this->load();
        $this->load_objects();
        log_debug("word_link->dsp_edit id " . $this->id . " load done.");

        // prepare to show the word link
        if ($this->id > 0) {
            $form_name = 'link_edit';
            $result .= dsp_text_h2('Change "' . $this->from_name . ' ' . $this->verb_name . ' ' . $this->to_name . '" to ');
            $result .= dsp_form_start($form_name);
            $result .= dsp_form_hidden("back", $back);
            $result .= dsp_form_hidden("confirm", '1');
            $result .= dsp_form_hidden("id", $this->id);
            $result .= '<div class="form-row">';
            if (isset($this->from)) {
                $result .= $this->from->dsp_selector(0, $form_name, 1, "col-sm-4", $back);
            }
            if (isset($this->verb)) {
                $result .= $this->verb->dsp_selector('forward', $form_name, "col-sm-4", $back);
            }
            if (isset($this->to)) {
                $result .= $this->to->dsp_selector(0, $form_name, 2, "col-sm-4", $back);
            }
            $result .= '</div>';
            $result .= dsp_form_end('', $back);
            $result .= '<br>';
        }

        return $result;
    }

    // display a form to adjust the link between too words or triples
    function dsp_del($back)
    {
        log_debug("word_link->dsp_del " . $this->id . ".");
        $result = ''; // reset the html code var

        $result .= btn_yesno('Is "' . $this->dsp() . '" wrong?', '/http/link_del.php?id=' . $this->id . '&back=' . $back);
        $result .= '<br><br>... and "' . $this->dsp_r() . '" is also wrong.<br><br>If you press Yes, both rules will be removed.';

        return $result;
    }

    // simply to display a single triple in a table
    function dsp_link()
    {
        $result = '<a href="/http/view.php?link=' . $this->id . '" title="' . $this->description . '">' . $this->name . '</a>';
        return $result;
    }

    // simply to display a single triple in a table
    function dsp_tbl($intent)
    {
        log_debug('word_link->dsp_tbl');
        $result = '    <td>' . "\n";
        while ($intent > 0) {
            $result .= '&nbsp;';
            $intent = $intent - 1;
        }
        $result .= '      ' . $this->dsp_link() . '' . "\n";
        $result .= '    </td>' . "\n";
        return $result;
    }

    function dsp_tbl_row()
    {
        $result = '  <tr>' . "\n";
        $result .= $this->dsp_tbl(0);
        $result .= '  </tr>' . "\n";
        return $result;
    }

    /*

    convert functions

    */

    // convert the word object into a phrase object
    function phrase()
    {
        $phr = new phrase;
        $phr->usr = $this->usr;
        $phr->id = $this->id;
        $phr->name = $this->name;
        $phr->obj = $this;
        log_debug('word_link->phrase of ' . $this->dsp_id());
        return $phr;
    }

    /*

    save functions

    */

    // true if no one has used this triple
    function not_used(): bool
    {
        log_debug('word_link->not_used (' . $this->id . ')');

        // todo review: maybe replace by a database foreign key check
        return $this->not_changed();
    }

    // true if no other user has modified the triple
    function not_changed(): bool
    {
        log_debug('word_link->not_changed (' . $this->id . ') by someone else than the owner (' . $this->owner_id . ')');

        global $db_con;
        $result = true;

        if ($this->owner_id > 0) {
            $sql = "SELECT user_id 
                FROM user_word_links 
               WHERE word_link_id = " . $this->id . "
                 AND user_id <> " . $this->owner_id . "
                 AND (excluded <> 1 OR excluded is NULL)";
        } else {
            $sql = "SELECT user_id 
                FROM user_word_links 
               WHERE word_link_id = " . $this->id . "
                 AND (excluded <> 1 OR excluded is NULL)";
        }
        //$db_con = new mysql;
        $db_con->usr_id = $this->usr->id;
        $db_row = $db_con->get1($sql);
        if ($db_row['user_id'] > 0) {
            $result = false;
        }
        log_debug('word_link->not_changed for ' . $this->id . ' is ' . zu_dsp_bool($result));
        return $result;
    }

    // true if the user is the owner and no one else has changed the word_link
    // because if another user has changed the word_link and the original value is changed, maybe the user word_link also needs to be updated
    function can_change(): bool
    {
        log_debug('word_link->can_change ' . $this->dsp_id() . ' by user "' . $this->usr->name . '" (id ' . $this->usr->id . ', owner id ' . $this->owner_id . ')');
        $can_change = false;
        if ($this->owner_id == $this->usr->id or $this->owner_id <= 0) {
            $can_change = true;
        }
        log_debug('word_link->can_change -> (' . zu_dsp_bool($can_change) . ')');
        return $can_change;
    }

    // true if a record for a user specific configuration already exists in the database
    function has_usr_cfg(): bool
    {
        $has_cfg = false;
        if ($this->usr_cfg_id > 0) {
            $has_cfg = true;
        }
        return $has_cfg;
    }

    // create a database record to save user specific settings for this word_link
    function add_usr_cfg(): bool
    {
        global $db_con;
        $result = true;

        if (!$this->has_usr_cfg()) {
            if (isset($this->from) and isset($this->to)) {
                log_debug('word_link->add_usr_cfg for "' . $this->from->name . '"/"' . $this->to->name . '" by user "' . $this->usr->name . '"');
            } else {
                log_debug('word_link->add_usr_cfg for "' . $this->id . '" and user "' . $this->usr->name . '"');
            }

            // check again if there ist not yet a record
            $db_con->set_type(DB_TYPE_WORD_LINK, true);
            $db_con->set_usr($this->usr->id);
            $db_con->set_where($this->id);
            $sql = $db_con->select();
            $db_row = $db_con->get1($sql);
            if ($db_row != null) {
                $this->usr_cfg_id = $db_row['word_link_id'];
            }
            if (!$this->has_usr_cfg()) {
                // create an entry in the user sandbox
                $db_con->set_type(DB_TYPE_USER_PREFIX . DB_TYPE_WORD_LINK);
                $log_id = $db_con->insert(array('word_link_id', 'user_id'), array($this->id, $this->usr->id));
                if ($log_id <= 0) {
                    log_err('Insert of user_word_link failed.');
                    $result = false;
                } else {
                    $result = true;
                }
            }
        }
        return $result;
    }

    // check if the database record for the user specific settings can be removed
    function del_usr_cfg_if_not_needed(): bool
    {
        log_debug('word_link->del_usr_cfg_if_not_needed pre check for "' . $this->dsp_id() . ' und user ' . $this->usr->name);

        global $db_con;
        $result = false;

        //if ($this->has_usr_cfg) {

        // check again if there ist not yet a record
        $sql = "SELECT word_link_id,
                     word_link_name,
                     description,
                     excluded
                FROM user_word_links
               WHERE word_link_id = " . $this->id . " 
                 AND user_id = " . $this->usr->id . ";";
        //$db_con = New mysql;
        $db_con->usr_id = $this->usr->id;
        $usr_cfg = $db_con->get1($sql);
        log_debug('word_link->del_usr_cfg_if_not_needed check for "' . $this->dsp_id() . ' und user ' . $this->usr->name . ' with (' . $sql . ')');
        if ($usr_cfg['word_link_id'] > 0) {
            if ($usr_cfg['word_link_name'] == Null
                and $usr_cfg['description'] == Null
                and $usr_cfg['excluded'] == Null) {
                // delete the entry in the user sandbox
                log_debug('word_link->del_usr_cfg_if_not_needed any more for "' . $this->dsp_id() . ' und user ' . $this->usr->name);
                $result = $this->del_usr_cfg_exe($db_con);
            }
        }
        //}
        return $result;
    }

    // set the log entry parameter for a new value
    // e.g. that the user can see "added ABB is a Company"
    function log_add()
    {
        log_debug('word_link->log_add for ' . $this->dsp_id() . ' by user "' . $this->usr->name . '"');
        $log = new user_log_link;
        $log->usr = $this->usr;
        $log->action = 'add';
        $log->table = 'word_links';
        $log->new_from = $this->from;
        $log->new_link = $this->verb;
        $log->new_to = $this->to;
        $log->row_id = 0;
        $log->add();

        return $log;
    }

    // set the main log entry parameters for updating the triple itself
    function log_upd()
    {
        $log = new user_log_link;
        $log->usr = $this->usr;
        $log->action = 'update';
        if ($this->can_change()) {
            $log->table = 'word_links';
        } else {
            $log->table = 'user_word_links';
        }

        return $log;
    }

    // set the log entry parameter to delete a triple
    // e.g. that the user can see "ABB is a Company not any more"
    function log_del()
    {
        log_debug('word_link->log_del for ' . $this->dsp_id() . ' by user "' . $this->usr->name . '"');
        $log = new user_log_link;
        $log->usr = $this->usr;
        $log->action = 'del';
        $log->table = 'word_links';
        $log->old_from = $this->from;
        $log->old_link = $this->verb;
        $log->old_to = $this->to;
        $log->row_id = $this->id;
        $log->add();

        return $log;
    }

    // set the main log entry parameters for updating one display word link field
    function log_upd_field(): user_log
    {
        $log = new user_log;
        $log->usr = $this->usr;
        $log->action = 'update';
        if ($this->can_change()) {
            $log->table = 'word_links';
        } else {
            $log->table = 'user_word_links';
        }

        return $log;
    }

    // set the update parameters for the phrase link name
    private function save_field_name($db_con, $db_rec, $std_rec)
    {
        $result = '';

        // the name field is a generic created field, so update it before saving
        $db_rec->name = $db_rec->name();
        $this->name = $this->name();
        $std_rec->name = $std_rec->name();

        if ($db_rec->name <> $this->name) {
            if ($this->name == '') {
                $this->name = null;
            }
            $log = $this->log_upd_field();
            $log->old_value = $db_rec->name;
            $log->new_value = $this->name;
            $log->std_value = $std_rec->name;
            $log->row_id = $this->id;
            $log->field = 'word_link_name';
            $result .= $this->save_field_do($db_con, $log);
        }
        return $result;
    }

    // set the update parameters for the phrase link description
    private function save_field_description($db_con, $db_rec, $std_rec)
    {
        $result = '';
        if ($db_rec->description <> $this->description) {
            $log = $this->log_upd_field();
            $log->old_value = $db_rec->description;
            $log->new_value = $this->description;
            $log->std_value = $std_rec->description;
            $log->row_id = $this->id;
            $log->field = 'description';
            $result .= $this->save_field_do($db_con, $log);
        }
        return $result;
    }

    // save all updated word_link fields excluding id fields (from, verb and to), because already done when adding a word_link
    function save_fields($db_con, $db_rec, $std_rec): bool
    {
        $result = $this->save_field_name($db_con, $db_rec, $std_rec);
        if ($result) {
            $result = $this->save_field_description($db_con, $db_rec, $std_rec);
        }
        if ($result) {
            $result = $this->save_field_excluded($db_con, $db_rec, $std_rec);
        }
        //$result .= $this->save_field_type     ($db_con, $db_rec, $std_rec);
        log_debug('word_link->save_fields all fields for ' . $this->dsp_id() . ' has been saved');
        return $result;
    }

    // save updated the word_link id fields (from, verb and to)
    // should only be called if the user is the owner and nobody has used the triple
    function save_id_fields($db_con, $db_rec, $std_rec): bool
    {
        $result = true;
        if ($db_rec->from_id <> $this->from_id
            or $db_rec->verb_id <> $this->verb_id
            or $db_rec->to_id <> $this->to_id) {
            log_debug('word_link->save_id_fields to "' . $this->to_name . '" (' . $this->to_id . ') from "' . $db_rec->to_name . '" (' . $db_rec->to_id . ') standard ' . $std_rec->to_name . '" (' . $std_rec->to_id . ')');
            $log = $this->log_upd();
            $log->old_from = $db_rec->from;
            $log->new_from = $this->from;
            $log->std_from = $std_rec->from;
            $log->old_link = $db_rec->verb;
            $log->new_link = $this->verb;
            $log->std_link = $std_rec->verb;
            $log->old_to = $db_rec->to;
            $log->new_to = $this->to;
            $log->std_to = $std_rec->to;
            $log->row_id = $this->id;
            //$log->field    = 'from_phrase_id';
            if ($log->add()) {
                $db_con->set_type(DB_TYPE_WORD_LINK);
                $result = $db_con->update($this->id,
                    array("from_phrase_id", "verb_id", "to_phrase_id"),
                    array($this->from->id, $this->verb->id, $this->to->id));
            }
        }
        log_debug('word_link->save_id_fields for ' . $this->dsp_id() . ' has been done');
        return $result;
    }

    // check if the id parameters are supposed to be changed
    function save_id_if_updated($db_con, $db_rec, $std_rec): string
    {
        $result = '';

        if ($db_rec->from_id <> $this->from_id
            or $db_rec->verb_id <> $this->verb_id
            or $db_rec->to_id <> $this->to_id) {
            $this->reset();
            // check if target link already exists
            log_debug('word_link->save_id_if_updated check if target link already exists ' . $this->dsp_id() . ' (has been "' . $db_rec->dsp_id() . '")');
            $db_chk = clone $this;
            $db_chk->id = 0; // to force the load by the id fields
            $db_chk->load_standard();
            if ($db_chk->id > 0) {
                // ... if yes request to delete or exclude the record with the id parameters before the change
                $to_del = clone $db_rec;
                if (!$to_del->del()) {
                    $result = 'Failed to delete the unused work link';
                    log_err($result);
                }
                if ($result = '') {
                    // .. and use it for the update
                    $this->id = $db_chk->id;
                    $this->owner_id = $db_chk->owner_id;
                    // force the include again
                    $this->excluded = null;
                    $db_rec->excluded = '1';
                    if ($this->save_field_excluded($db_con, $db_rec, $std_rec)) {
                        log_debug('word_link->save_id_if_updated found a triple with target ids "' . $db_chk->dsp_id() . '", so del "' . $db_rec->dsp_id() . '" and add ' . $this->dsp_id());
                    }
                }
            } else {
                if ($this->can_change() and $this->not_used()) {
                    // in this case change is allowed and done
                    log_debug('word_link->save_id_if_updated change the existing triple ' . $this->dsp_id() . ' (db "' . $db_rec->dsp_id() . '", standard "' . $std_rec->dsp_id() . '")');
                    $this->load_objects();
                    if (!$this->save_id_fields($db_con, $db_rec, $std_rec)) {
                        $result = 'Failed to update the recreated work link';
                        log_err($result);
                    }
                } else {
                    // if the target link has not yet been created
                    // ... request to delete the old
                    $to_del = clone $db_rec;
                    if (!$to_del->del()) {
                        $result = 'Failed to delete the unused work link';
                        log_err($result);
                    }
                    // .. and create a deletion request for all users ???

                    // ... and create a new triple
                    $this->id = 0;
                    $this->owner_id = $this->usr->id;
                    $result .= $this->add();
                    log_debug('word_link->save_id_if_updated recreate the triple del "' . $db_rec->dsp_id() . '" add ' . $this->dsp_id() . ' (standard "' . $std_rec->dsp_id() . '")');
                }
            }
        }

        log_debug('word_link->save_id_if_updated for ' . $this->dsp_id() . ' has been done');
        return $result;
    }

    // add a new triple to the database
    function add(): int
    {
        log_debug('word_link->add new word_link for "' . $this->from->name . '" ' . $this->verb->name . ' "' . $this->to->name . '"');

        global $db_con;
        $result = 0;

        // log the insert attempt first
        $log = $this->log_add();
        if ($log->id > 0) {
            // insert the new word_link
            $db_con->set_type(DB_TYPE_WORD_LINK);
            $this->id = $db_con->insert(array("from_phrase_id", "verb_id", "to_phrase_id", "user_id"),
                array($this->from->id, $this->verb->id, $this->to->id, $this->usr->id));
            // TODO make sure on all add functions that the database object is always set
            //array($this->from_id, $this->verb_id, $this->to_id, $this->usr->id));
            if ($this->id > 0) {
                // update the id in the log
                if (!$log->add_ref($this->id)) {
                    log_err('Updating the reference in the log failed');
                    // TODO do rollback or retry?
                } else {

                    // create an empty db_rec element to force saving of all set fields
                    $db_rec = new word_link;
                    $db_rec->from = $this->from;
                    $db_rec->verb = $this->verb;
                    $db_rec->to = $this->to;
                    $db_rec->usr = $this->usr;
                    $std_rec = clone $db_rec;
                    // save the word_link fields
                    if ($this->save_fields($db_con, $db_rec, $std_rec)) {
                        $result = $this->id;
                    }
                }

            } else {
                log_err("Adding word_link " . $this->name . " failed", "word_link->add");
            }
        }

        return $result;
    }

    // update a triple in the database or create a user triple
    function save(): string
    {
        log_debug('word_link->save "' . $this->description . '" for user ' . $this->usr->id);

        global $db_con;
        $result = '';

        // load the objects if needed
        $this->load_objects();

        // build the database object because the is anyway needed
        $db_con->set_usr($this->usr->id);
        $db_con->set_type(DB_TYPE_WORD_LINK);

        // check if the opposite triple already exists and if yes, ask for confirmation
        if ($this->id <= 0) {
            log_debug('word_link->save check if a new word_link for "' . $this->from->name . '" and "' . $this->to->name . '" needs to be created');
            // check if the same triple is already in the database
            $db_chk_rev = clone $this;
            $db_chk_rev->from = $this->to;
            $db_chk_rev->from_id = $this->to_id;
            $db_chk_rev->to = $this->from;
            $db_chk_rev->to_id = $this->from_id;
            $db_chk_rev->load_standard();
            if ($db_chk_rev->id > 0) {
                $this->id = $db_chk_rev->id;
                $result .= dsp_err('The reverse of "' . $this->from->name . ' ' . $this->verb->name . ' ' . $this->to->name . '" already exists. Do you really want to create both sides?');
            }
        }

        // check if the triple already exists and if yes, update it if needed
        if ($this->id <= 0 and $result == '') {
            log_debug('word_link->save check if a new word_link for "' . $this->from->name . '" and "' . $this->to->name . '" needs to be created');
            // check if the same triple is already in the database
            $db_chk = clone $this;
            $db_chk->load_standard();
            if ($db_chk->id > 0) {
                $this->id = $db_chk->id;
            }
        }

        // try to save the link only if no question has been raised until now
        if ($result == '') {
            // check if a new value is supposed to be added
            if ($this->id <= 0) {
                $result = strval($this->add());
            } else {
                log_debug('word_link->save update "' . $this->id . '"');
                // read the database values to be able to check if something has been changed;
                // done first, because it needs to be done for user and general phrases
                $db_rec = new word_link;
                $db_rec->id = $this->id;
                $db_rec->usr = $this->usr;
                if (!$db_rec->load()) {
                    $result = 'Reloading of word_link failed';
                    log_err($result);
                }
                log_debug('word_link->save -> database triple "' . $db_rec->name . '" (' . $db_rec->id . ') loaded');
                $std_rec = new word_link;
                $std_rec->id = $this->id;
                $std_rec->usr = $this->usr; // must also be set to allow to take the ownership
                if (!$std_rec->load_standard()) {
                    $result = 'Reloading of the default values for word_link failed';
                    log_err($result);
                }
                log_debug('word_link->save -> standard triple settings for "' . $std_rec->name . '" (' . $std_rec->id . ') loaded');

                // for a correct user word link detection (function can_change) set the owner even if the word link has not been loaded before the save
                if ($this->owner_id <= 0) {
                    $this->owner_id = $std_rec->owner_id;
                }

                // check if the id parameters are supposed to be changed
                if ($result == '') {
                    $result = $this->save_id_if_updated($db_con, $db_rec, $std_rec);
                }

                // if a problem has appeared up to here, don't try to save the values
                // the problem is shown to the user by the calling interactive script
                if ($result == '') {
                    if (!$this->save_fields($db_con, $db_rec, $std_rec)) {
                        $result = 'Saving of fields for word_link failed';
                        log_err($result);
                    }
                }
            }
        }

        return $result;
    }

}
