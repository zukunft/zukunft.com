<?php

/*

    web/formula/expression.php - reformat the formula expression independent from the backend
    --------------------------

    repeating some backend functions in the frontend, but based on the frontend cache and frontend object



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

namespace Zukunft\ZukunftCom\main\php\web\formula;

use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::ELEMENT . 'element.php';
include_once html_paths::ELEMENT . 'element_list.php';
include_once html_paths::PHRASE . 'term.php';
include_once html_paths::PHRASE . 'term_list.php';
include_once html_paths::VERB . 'verb.php';
include_once html_paths::WORD . 'triple.php';
include_once html_paths::WORD . 'word.php';
include_once html_paths::USER . 'user_message.php';
include_once html_paths::MODEL_USER . 'user_message.php';
include_once html_paths::SHARED_CALC . 'expression.php';
include_once html_paths::SHARED_CONST . 'chars.php';
include_once html_paths::SHARED_HELPER . 'Message.php';
include_once html_paths::SHARED . 'library.php';

use Zukunft\ZukunftCom\main\php\web\element\element;
use Zukunft\ZukunftCom\main\php\web\element\element_list;
use Zukunft\ZukunftCom\main\php\web\phrase\term;
use Zukunft\ZukunftCom\main\php\web\phrase\term_list;
use Zukunft\ZukunftCom\main\php\web\verb\verb;
use Zukunft\ZukunftCom\main\php\web\word\triple;
use Zukunft\ZukunftCom\main\php\web\word\word;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message as backend_user_message;
use Zukunft\ZukunftCom\main\php\shared\calc\expression as shared_expression;
use Zukunft\ZukunftCom\main\php\shared\const\chars;
use Zukunft\ZukunftCom\main\php\shared\helper\Message;
use Zukunft\ZukunftCom\main\php\shared\library;

class expression extends shared_expression
{

    /**
     * get a list of all formula elements
     *
     * @param term_list|null $trm_lst a list of preloaded terms that should be used for the transformation
     * @return element_list a list of all formula elements
     * (don't use for number retrieval, use element_grp_lst instead, because )
     */
    function element_list(user_message $msg, ?term_list $trm_lst = null): element_list
    {
        $lib = new library();

        $elm_lst = new element_list();
        $work = $this->r_part();

        $obj_sym = $lib->str_between($work, chars::TERM_START, chars::TERM_END);
        while ($obj_sym != '') {
            $elm = $this->element_by_symbol($obj_sym, $msg, $trm_lst);
            $elm_lst->add_obj($elm, true);
            $work = $lib->str_right_of($work, chars::TERM_END);
            $obj_sym = $lib->str_between($work, chars::TERM_START, chars::TERM_END);
        }
        return $elm_lst;
    }



    /**
     * create a formula element based on the id symbol e.g. w2 for word with id 2
     * and get the word, triple, formula or verb either from the given preloaded term list
     * or load the object from the database
     *
     * @param string $obj_sym the formula element symbol e.g. t2 for triple with id 2
     * @param term_list|null $trm_lst a list of preloaded terms
     * @return element the filled formula element
     */
    private function element_by_symbol(string $obj_sym, user_message $msg, ?term_list $trm_lst = null): element
    {
        $elm = new element();
        $class = match ($obj_sym[0]) {
            chars::WORD_SYMBOL => word::class,
            chars::VERB_SYMBOL => verb::class,
            chars::TRIPLE_SYMBOL => triple::class,
            chars::FORMULA_SYMBOL => formula::class,
        };
        $id = substr($obj_sym, 1);
        $trm = $trm_lst?->term_by_obj_id($id, $class);
        if ($trm == null) {
            $trm = new term();
            $trm->load_by_obj_id($id, $class, $msg);
        }
        if ($trm != null) {
            if ($trm->id() != 0) {
                $elm->obj = $trm->obj();
                $elm->symbol = $this->get_db_sym($trm);
            } else {
                log_warning($class . ' with id ' . $id . ' not found');
            }
        }

        return $elm;
    }


    /*
     * overwrite
     */

    protected function get_formula_symbol(string $name, user_message|Message $msg): string
    {
        $frm = new formula();
        $frm->load_by_name($name, $msg);
        if ($frm->id > 0) {
            $db_sym = chars::FORMULA_START . $frm->id . chars::FORMULA_END;
            log_debug('found formula "' . $db_sym . '" for "' . $name . '"');
        } else {
            $db_sym = '';
        }
        return $db_sym;
    }

    protected function get_word_symbol(string $name, user_message|Message $msg): string
    {
        $wrd = new word();
        $wrd->load_by_name($name, $msg);
        if ($wrd->id > 0) {
            $db_sym = chars::WORD_START . $wrd->id . chars::WORD_END;
            log_debug('found word "' . $db_sym . '" for "' . $name . '"');
        } else {
            $db_sym = '';
        }
        return $db_sym;
    }

    protected function get_triple_symbol(string $name, backend_user_message|Message $msg): string
    {
        $trp = new triple();
        $trp->load_by_name($name, $msg);
        if ($trp->id > 0) {
            $db_sym = chars::TRIPLE_START . $trp->id . chars::TRIPLE_END;
            log_debug('found triple "' . $db_sym . '" for "' . $name . '"');
        } else {
            $db_sym = '';
        }
        return $db_sym;
    }

    protected function get_verb_symbol(string $name, backend_user_message|Message $msg): string
    {
        $vrb = new verb;
        $vrb->load_by_name($name, $msg);
        if ($vrb->id > 0) {
            $db_sym = chars::VERB_START . $vrb->id . chars::VERB_END;
            log_debug('found verb "' . $db_sym . '" for "' . $name . '"');
        } else {
            $db_sym = '';
        }
        return $db_sym;
    }

    protected function load_word(int $id, backend_user_message|Message $msg): word
    {
        $wrd = new word();
        $wrd->load_by_id($id, $msg);
        if ($wrd->id == 0) {
            $wrd = null;
        }
        return $wrd;
    }

    protected function load_triple(int $id, backend_user_message|Message $msg): triple
    {
        $trp = new triple();
        $trp->load_by_id($id, $msg);
        if ($trp->id == 0) {
            $trp = null;
        }
        return $trp;
    }

    protected function load_formula(int $id, backend_user_message|Message $msg): formula
    {
        $frm = new formula();
        $frm->load_by_id($id, $msg);
        if ($frm->id == 0) {
            $frm = null;
        }
        return $frm;
    }

    protected function load_verb(int $id, backend_user_message|Message $msg): verb
    {
        $vrb = new verb();
        $vrb->load_by_id($id, $msg);
        if ($vrb->id == 0) {
            $vrb = null;
        }
        return $vrb;
    }

}
