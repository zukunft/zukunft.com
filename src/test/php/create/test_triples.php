<?php

/*

    test/create/test_triples.php - create the test triple objects
    ----------------------------


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

namespace Zukunft\ZukunftCom\test\php\create;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::MODEL_PHRASE . 'phrase.php';
include_once paths::MODEL_PHRASE . 'phrase_list.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::MODEL_VERB . 'verb.php';
include_once paths::MODEL_WORD . 'triple.php';
include_once paths::MODEL_WORD . 'triple_list.php';
include_once paths::MODEL_WORD . 'word.php';
include_once paths::SHARED_CONST . 'impacts.php';
include_once paths::SHARED_CONST . 'triples.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED_CONST . 'words.php';
include_once paths::SHARED_TYPES . 'api_types.php';
include_once paths::SHARED_TYPES . 'phrase_types.php';
include_once paths::SHARED_TYPES . 'protection_types.php';
include_once paths::SHARED_TYPES . 'share_types.php';
include_once paths::SHARED_TYPES . 'verbs.php';
include_once paths::SHARED . 'url_var.php';
include_once html_paths::USER . 'user_message.php';
include_once html_paths::WORD . 'triple.php';
include_once html_paths::WORD . 'triple_list.php';
include_once test_paths::CONST . 'triple_names.php';
include_once test_paths::CONST . 'word_names.php';
include_once test_paths::UTILS . 'test_cleanup.php';
include_once test_paths::UTILS . 'test_lib.php';

use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\verb\verb;
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\cfg\word\triple_list;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\shared\const\impacts;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\main\php\shared\types\share_types;
use Zukunft\ZukunftCom\main\php\shared\types\phrase_types;
use Zukunft\ZukunftCom\main\php\shared\types\protection_types;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\web\user\user_message as user_message_ui;
use Zukunft\ZukunftCom\main\php\web\word\triple as triple_ui;
use Zukunft\ZukunftCom\main\php\web\word\triple_list as triple_list_ui;
use Zukunft\ZukunftCom\test\php\const\triple_names;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;
use Zukunft\ZukunftCom\test\php\utils\test_lib;

class test_triples extends test_objects
{

    /*
     * cleanup
     */

    /**
     * delete any remaining test triples for a clean test start
     */
    function cleanup(string $ts): void
    {
        global $db_con;

        parent::cleanup_objects($ts, triple_names::TEST_TRIPLES, new triple($this->env->usr1));

        // cleanup all triples that use a test verb
        $vrb = new verb();
        $trp_lst = new triple_list($this->env->usr1);
        $msg = new user_message();
        foreach (verbs::TEST_VERBS as $name) {
            $vrb->reset();
            $vrb->load_by_name($name, $msg);
            if ($vrb->has_id()) {
                $trp_lst->load_by_verb( $vrb, $msg, true );
                $trp_lst->del($msg);
            }
        }

        // also clean up the words and verbs used for the triples
        $t_wrd = new test_words($this->env);
        $t_wrd->cleanup($ts);
        parent::cleanup_objects($ts, [triple_names::SYSTEM_TEST_ADD_AUTO], new verb());
    }


    /*
     * unit
     */

    /**
     * @return triple "mathematical constant" used for unit testing
     */
    function triple(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::MATH_CONST_ID, triple_names::MATH_CONST);
        $trp->description = triple_names::MATH_CONST_COM;
        $trp->set_from($t_wrd->word_const()->phrase());
        $trp->set_verb($t_vrb->verb_part());
        $trp->set_to($t_wrd->word()->phrase());
        $trp->set_type(phrase_types::MATH_CONST, new user_message($this->env->usr1));
        global $sys;
        $trp->set_protection_id($sys->typ_lst->ptc_typ->id(protection_types::ADMIN));
        return $trp;
    }

    /**
     * @return triple "mathematical constant" used for unit testing
     */
    function triple_impact(): triple
    {
        $trp = $this->triple();
        $trp->impact = impacts::MAX;
        return $trp;
    }

    /**
     * @return triple object where the most specific mandatory var is not set which is in case of a word the id and the name of the to phrase
     */
    function triple_incomplete(): triple
    {
        $t_wrd = new test_words($this->env);
        $trp = $this->triple();
        $trp->set_to($t_wrd->word_incomplete()->phrase());
        return $trp;
    }

    /**
     * TODO PRIO 1
     * @return triple as it is returned at the moment via phrase list api, means without links
     */
    function triple_api(): triple
    {
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::MATH_CONST_ID, triple_names::MATH_CONST);
        $trp->description = triple_names::MATH_CONST_COM;
        $trp->set_type(phrase_types::MATH_CONST, new user_message($this->env->usr1));
        global $sys;
        $trp->set_protection_id($sys->typ_lst->ptc_typ->id(protection_types::ADMIN));
        return $trp;
    }

    /**
     * @return triple with all fields set and a reserved test name for testing the db write function
     */
    function triple_filled_public(): triple
    {
        global $sys;
        $trp = $this->triple();
        $trp->name_given = triple_names::MATH_CONST_GIVEN;
        $trp->weight = 0.5;
        $trp->set_view_id(views::MATH_CONST_ID);
        $trp->usage = triple_names::SYSTEM_TEST_ADD_USAGE;
        $trp->impact = impacts::MAX;
        $trp->exclude();
        $trp->set_protection_id($sys->typ_lst->ptc_typ->id(protection_types::ADMIN));
        return $trp;
    }

    /**
     * @return triple with all fields set and a reserved test name for testing the db write function
     */
    function triple_filled(): triple
    {
        global $sys;
        $trp = $this->triple_filled_public();
        $trp->set_share_id($sys->typ_lst->shr_typ->id(share_types::GROUP));
        return $trp;
    }

    /**
     * @return triple with all fields set and a reserved test name for testing the db write function
     */
    function triple_filled_included(): triple
    {
        $trp = $this->triple_filled();
        $trp->include();
        return $trp;
    }

    /**
     * @return triple with all fields set and a reserved test name for testing the db write function
     */
    function triple_filled_add_name(): triple
    {
        $t_wrd = new test_words($this->env);
        $trp = $this->triple_filled_included();
        $trp->id = 0;
        $trp->set_name(triple_names::SYSTEM_TEST_ADD);
        // overwrite the 'math const' given name of the filled base triple with the reserved test
        // given name so the workflow urls never carry seeded data
        $trp->name_given = triple_names::SYSTEM_TEST_ADD_GIVEN;
        $trp->set_code_id(triple_names::SYSTEM_TEST_ADD_CODE_ID, new user_message($this->env->usr1));
        $trp->set_from($t_wrd->word_filled_add()->phrase());
        $trp->set_to($t_wrd->word_filled_add_to()->phrase());
        return $trp;
    }

    /**
     * @return triple "mathematical constant" with only the name set as it may be created by the import
     */
    function triple_name_only(): triple
    {
        $trp = new triple($this->env->usr1);
        $trp->set_name(triple_names::MATH_CONST);
        return $trp;
    }

    /**
     * url array of the main test triple with the description updated
     *
     * @param int $id the database id of the changed triple, used as the '9'-prefixed back target id
     * @return array the edit form url parameters of the pending change
     */
    function change_description_url_array(int $id): array
    {
        $msg = new user_message_ui();
        // use the reserved test triple so the workflow url never carries seeded (e.g. math) data
        $trp = $this->triple_filled_add_name();
        $trp->description = 'a confirm change test description';
        $url_arr = test_mappers::object_to_url_array($trp, $msg);
        // back navigation is always a '9'-prefixed url var, never a standalone back field
        // (see docs/llm/state-and-messages.md), otherwise the url mapper reports the key as missing
        $url_arr[url_var::BACK . url_var::ID] = $id;
        return $url_arr;
    }


    /**
     * TODO Prio 0 use $t_trp->filled() and a to_url function
     * the filled triple url posted by the edit form in the second change_triple round, mirroring
     * test_words::fill_url_array: the first round did not touch the weight or the phrase type, so the
     * fill round adds them; the '8'-prefixed opening db values are taken from the change url so the
     * confirm view shows no change for the already-set fields and only the two new ones
     *
     * @param int $id the database id of the triple the workflow runs on, used as the back target
     * @return array the edit form url with every field set plus the '8'-prefixed opening db values
     */
    function fill_url_array(int $id): array
    {
        $url_arr = $this->change_description_url_array($id);
        $url_arr[url_var::WEIGHT] = '1';
        $url_arr[url_var::PHRASE_TYPE] = phrase_types::NORMAL_ID;
        $url_arr[url_var::PRE . url_var::NAME] = $url_arr[url_var::NAME];
        $url_arr[url_var::PRE . url_var::DESCRIPTION] = $url_arr[url_var::DESCRIPTION];
        $url_arr[url_var::PRE . url_var::SHARE] = $url_arr[url_var::SHARE];
        $url_arr[url_var::PRE . url_var::PROTECTION] = $url_arr[url_var::PROTECTION];
        return $url_arr;
    }

    /**
     * TODO Prio 0 use to_url function
     * the new triple fields posted by the add form on save and shown again in the confirm add view,
     * mirroring test_words::add_url_array; a triple is defined by its from phrase, verb and to phrase,
     * so those are posted instead of just a name (the reserved 'System Test Triple' name lets the
     * del_triple workflow load the added triple back)
     *
     * @return array the add form url parameters of the new triple
     */
    function add_url_array(): array
    {
        // the add form posts the phrase ids of the from and to phrases; only reserved test words are
        // used so the workflow never changes seeded data (e.g. the usage of the math words): the write
        // twin creates the two words before the workflows run, so their current db id is resolved by
        // name, and a read-only run (where the words are not written) falls back to the fixed test id;
        // two fresh test words have no triple between them in either direction, so the new triple
        // neither collides with an existing triple nor with its reverse (get_similar rejects a reverse)
        $trp = $this->triple_filled_add_name();
        $t_wrd = new test_words($this->env);
        return [
            url_var::PHRASE_FROM => $t_wrd->word_id_or_fixed(word_names::TEST_ADD, word_names::TEST_ADD_ID),
            url_var::VERB => $trp->get_verb_id(),
            url_var::PHRASE_TO => $t_wrd->word_id_or_fixed(word_names::TEST_ADD_TO, word_names::TEST_ADD_TO_ID),
            url_var::NAME => triple_names::SYSTEM_TEST_ADD,
            url_var::DESCRIPTION => triple_names::SYSTEM_TEST_ADD_COM,
            url_var::SHARE => share_types::PUBLIC_ID,
            url_var::PROTECTION => protection_types::NO_PROTECT_ID
        ];
    }

    /**
     * the invalid new triple posted by the add form on save when neither the from nor the to phrase is
     * entered, used by the add_triple_fail workflow to check that the frontend blocks a triple without a
     * from and a to phrase (see web/word/triple.php::input_valid); the name is kept so only the missing
     * phrases, not an empty name, trigger the warning
     *
     * @return array the add form url parameters of the new triple without the from, verb and to phrase
     */
    function add_missing_phrases_url_array(): array
    {
        $url_arr = $this->add_url_array();
        unset($url_arr[url_var::PHRASE_FROM]);
        unset($url_arr[url_var::VERB]);
        unset($url_arr[url_var::PHRASE_TO]);
        return $url_arr;
    }

    static function triple_add(phrase $wrd_from, verb $vrb, phrase $phr_to): triple
    {
        $trp = new triple(test_users::user_sys_test());
        $trp->set_name(triple_names::SYSTEM_TEST_ADD);
        $trp->set_from($wrd_from);
        $trp->set_verb($vrb);
        $trp->set_to($phr_to);
        return $trp;
    }

    static function triple_add_ui(): triple_ui
    {
        // link two reserved test words (with the fixed test ids) so the workflow urls built from this
        // factory never reference seeded data; use the same real verb as add_url_array, because a
        // change posted with the 'not set' verb would erase the verb of the created test triple
        $trp = self::triple_add(
            test_words::word_add()->phrase(), test_verbs::verb_part(), test_words::word_add_to()->phrase());
        return new triple_ui($trp->api_json());
    }

    static function triple_new_url(user_message_ui $msg): array
    {
        $trp_ui = new triple_ui();
        $trp_ui->set_verb(test_verbs::verb_ui());
        return $trp_ui->to_url_array($msg);
    }

    static function triple_add_url(user_message_ui $msg): array
    {
        $trp_ui = self::triple_add_ui();
        return $trp_ui->to_url_array($msg);
    }

    /**
     * the test triple url with the current db ids of the from and to test words: a write run must post
     * the real word ids, because the backend cannot load the fixed test ids and would reject the save;
     * in a read-only run (where the words are not written) the fixed test ids are kept
     *
     * @return array the triple url parameters with the resolved from and to phrase ids
     */
    function triple_add_url_resolved(user_message_ui $msg): array
    {
        $t_wrd = new test_words($this->env);
        $url_arr = self::triple_add_url($msg);
        $url_arr[url_var::PHRASE_FROM]
            = $t_wrd->word_id_or_fixed(word_names::TEST_ADD, word_names::TEST_ADD_ID);
        $url_arr[url_var::PHRASE_TO]
            = $t_wrd->word_id_or_fixed(word_names::TEST_ADD_TO, word_names::TEST_ADD_TO_ID);
        return $url_arr;
    }

    /**
     * @return triple with all fields set and a reserved test name for testing the db write function
     */
    function triple_filled_add(phrase $wrd_from, verb $vrb, phrase $phr_to): triple
    {
        global $sys;
        $trp = $this->triple_add($wrd_from, $vrb, $phr_to);
        $trp->id = 0;
        $trp->include();
        $trp->set_name(triple_names::SYSTEM_TEST_ADD);
        $trp->set_code_id(triple_names::SYSTEM_TEST_ADD_CODE_ID, new user_message($this->env->usr1));
        $trp->weight = 0.5;
        $trp->set_view_id(views::MATH_CONST_ID);
        $trp->usage = triple_names::SYSTEM_TEST_ADD_USAGE;
        $trp->impact = triple_names::SYSTEM_TEST_ADD_IMPACT;
        $trp->set_share_id($sys->typ_lst->shr_typ->id(share_types::GROUP));
        $trp->set_protection_id($sys->typ_lst->ptc_typ->id(protection_types::ADMIN));
        return $trp;
    }

    /**
     * @return triple "mathematical constant" with only the link names set as it may be created by the import
     */
    function triple_link_only(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set_from($t_wrd->word_const()->phrase());
        $trp->set_verb($t_vrb->verb_part());
        $trp->set_to($t_wrd->word()->phrase());
        return $trp;
    }

    /**
     * @return triple "pi (unit symbol)" used for unit testing
     */
    function triple_pi(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::PI_SYMBOL_ID, triple_names::PI_SYMBOL_NAME);
        $trp->description = triple_names::PI_COM;
        $trp->set_from($t_wrd->word_pi_symbol()->phrase());
        $trp->set_verb($t_vrb->verb_alias());
        $trp->set_to($t_wrd->word_pi()->phrase());
        return $trp;
    }

    /**
     * @return triple "pi (math)" used for unit testing
     */
    function triple_pi_name(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::PI_ID, triple_names::PI_NAME);
        $trp->description = triple_names::PI_COM;
        $trp->set_from($t_wrd->word_pi()->phrase());
        $trp->set_verb($t_vrb->verb_is());
        $trp->set_to($this->triple()->phrase());
        $trp->set_type(phrase_types::TRIPLE_HIDDEN, new user_message($this->env->usr1));
        return $trp;
    }

    /**
     * TODO PRIO 1
     * @return triple pi as it is returned at the moment via phrase list api, means without links
     */
    function triple_pi_api(): triple
    {
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::PI_ID, triple_names::PI_NAME);
        $trp->description = triple_names::PI_COM;
        $trp->set_type(phrase_types::TRIPLE_HIDDEN, new user_message($this->env->usr1));
        return $trp;
    }


    /*
     * si units
     */

    function second(): triple
    {
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::SECOND_ID, triple_names::SECOND);
        return $trp;
    }

    /**
     * @return triple hyperfine transition frequency of Cs for unit testing of source values
     */
    function transition_cs_133(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_trp = new test_triples($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::TRANSITION_CS_ID, triple_names::TRANSITION_CS);
        $trp->set_from($t_trp->hyperfine_transition_frequency()->phrase());
        $trp->set_verb($t_vrb->verb_of());
        $trp->set_to($this->cs_133()->phrase());
        return $trp;
    }

    /**
     * @return triple "Caesium-133" (Caesium kind of 133) used for unit testing
     */
    function cs_133(): triple
    {
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::CS_133_ID, triple_names::CS_133);
        $trp->description = triple_names::CS_133_COM;
        return $trp;
    }

    function hyperfine_transition_frequency(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_trp = new test_triples($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::TRANSITION_FREQUENCY_ID, triple_names::TRANSITION_FREQUENCY);
        $trp->set_from($t_trp->hyperfine_transition()->phrase());
        $trp->set_verb($t_vrb->verb_has());
        $trp->set_to($t_wrd->frequency()->phrase());
        return $trp;
    }

    function hyperfine_transition(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::HYPERFINE_TRANSITION_ID, triple_names::HYPERFINE_TRANSITION);
        $trp->set_from($t_wrd->transition()->phrase());
        $trp->set_verb($t_vrb->verb_can_be());
        $trp->set_to($t_wrd->hyperfine()->phrase());
        return $trp;
    }

    /**
     * @return triple speed of light for unit testing of source values
     */
    function speed_of_light(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::SPEED_OF_LIGHT_ID, triple_names::SPEED_OF_LIGHT);
        $trp->description = triple_names::SPEED_OF_LIGHT_COM;
        $trp->set_from($t_wrd->speed()->phrase());
        $trp->set_verb($t_vrb->verb_of());
        $trp->set_to($t_wrd->light()->phrase());
        return $trp;
    }

    function meter_per_second(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::M_PER_S_ID, triple_names::M_PER_S);
        $trp->description = triple_names::M_PER_S_COM;
        $trp->set_from($t_wrd->metre()->phrase());
        $trp->set_verb($t_vrb->verb_per());
        $trp->set_to($t_wrd->second()->phrase());
        $trp->set_type(phrase_types::MEASURE, new user_message($this->env->usr1));
        return $trp;
    }

    function definition_year_1983(): triple
    {
        $t_trp = new test_triples($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::DEFINITION_YEAR_1983_ID, triple_names::DEFINITION_YEAR_1983);
        $trp->set_from($t_trp->year_1983()->phrase());
        $trp->set_verb($t_vrb->verb_is());
        $trp->set_to($t_trp->definition_year()->phrase());
        $trp->set_type(phrase_types::INFO, new user_message($this->env->usr1));
        return $trp;
    }

    function definition_year_1967(): triple
    {
        $t_trp = new test_triples($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::DEFINITION_YEAR_1967_ID, triple_names::DEFINITION_YEAR_1967);
        $trp->set_from($t_trp->year_1967()->phrase());
        $trp->set_verb($t_vrb->verb_is());
        $trp->set_to($t_trp->definition_year()->phrase());
        $trp->set_type(phrase_types::INFO, new user_message($this->env->usr1));
        return $trp;
    }

    function definition_year(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::DEFINITION_YEAR_ID, triple_names::DEFINITION_YEAR);
        $trp->set_from($t_wrd->word_year()->phrase());
        $trp->set_verb($t_vrb->verb_of());
        $trp->set_to($t_wrd->definition()->phrase());
        return $trp;
    }

    function year_1983(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::YEAR_1983_ID, triple_names::YEAR_1983);
        $trp->set_from($t_wrd->word_1983()->phrase());
        $trp->set_verb($t_vrb->verb_is());
        $trp->set_to($t_wrd->word_year()->phrase());
        return $trp;
    }

    function year_1967(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::YEAR_1967_ID, triple_names::YEAR_1967);
        $trp->set_from($t_wrd->word_1967()->phrase());
        $trp->set_verb($t_vrb->verb_is());
        $trp->set_to($t_wrd->word_year()->phrase());
        return $trp;
    }

    /**
     * @return triple Global Warming Potential used for unit testing
     */
    function triple_global_warming(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::GLOBAL_WARMING_ID, triple_names::GLOBAL_WARMING);
        $trp->set_from($t_wrd->word_global()->phrase());
        $trp->set_verb($t_vrb->verb_is());
        $trp->set_to($t_wrd->word_warmer()->phrase());
        return $trp;
    }

    /**
     * @return triple Global Warming Potential used for unit testing
     */
    function triple_gwp(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::GWP_ID, triple_names::GWP);
        $trp->set_from($this->triple_global_warming()->phrase());
        $trp->set_verb($t_vrb->verb_is());
        $trp->set_to($t_wrd->word_potential()->phrase());
        return $trp;
    }

    /**
     * @return triple to select the system configuration
     */
    function triple_sys_config(): triple
    {
        $wrd = new triple($this->env->usr1);
        $wrd->set(triples::SYSTEM_CONFIG_ID, triples::SYSTEM_CONFIG);
        return $wrd;
    }

    /**
     * the database id is not set, because the id of a config triple depends on the import sequence
     * and the triple is only used to select a config value by name
     * @return triple to select the user without login in the config
     */
    function triple_ip_user(): triple
    {
        $trp = new triple($this->env->usr1);
        $trp->set_name(triples::IP_USER);
        return $trp;
    }

    /**
     * the database id is not set, see triple_ip_user
     * @return triple to select the database change permissions in the config
     */
    function triple_database_change(): triple
    {
        $trp = new triple($this->env->usr1);
        $trp->set_name(triples::DATABASE_CHANGE);
        return $trp;
    }

    /**
     * @return triple "Euler's number" (Euler name of number) used for unit testing
     *         and to test the handling of >'< in a triple name
     */
    function triple_euler_number(): triple
    {
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::EULER_NUMBER_ID, triple_names::EULER_NUMBER);
        $trp->description = triple_names::EULER_NUMBER_COM;
        return $trp;
    }

    /**
     * @return triple "e (math const)" used for unit testing
     */
    function triple_e(): triple
    {
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::E_ID, triple_names::E);
        $trp->description = triple_names::E_COM;
        $trp->set_from($this->triple_euler_number()->phrase());
        $trp->set_verb($t_vrb->verb_is());
        $trp->set_to($this->triple()->phrase());
        $trp->set_type(phrase_types::TRIPLE_HIDDEN, new user_message($this->env->usr1));
        return $trp;
    }

    /**
     * @return triple to test the sql insert via function
     */
    function triple_add_by_func(user_message $msg): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $t_db = new test_db_load($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set_name(triple_names::SYSTEM_TEST_ADD_VIA_FUNC);
        $wrd_add_func = $t_db->load_word($msg, word_names::TEST_ADD_VIA_FUNC);
        $wrd_math = $t_db->load_word($msg, word_names::MATH);
        $trp->set_from($wrd_add_func->phrase());
        $trp->set_verb($t_vrb->verb_is());
        $trp->set_to($wrd_math->phrase());
        return $trp;
    }

    /**
     * @return triple "Zurich (city)" used for unit testing
     */
    function zh_city(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::CITY_ZH_ID, triple_names::CITY_ZH_NAME);
        $trp->set_from($t_wrd->word_zh()->phrase());
        $trp->set_verb($t_vrb->verb_is());
        $trp->set_to($t_wrd->word_city()->phrase());
        $trp->set_description(triple_names::CITY_ZH_COM);
        $trp->set_impact(impacts::HTP_ZH_CITY);
        return $trp;
    }

    /**
     * @return triple "Zurich (city)" with a low impact to test sort by impact
     */
    function zh_city_low_impact(): triple
    {
        $trp = $this->zh_city();
        $trp->set_impact(impacts::LOW);
        return $trp;
    }

    /**
     * @return triple "Zurich (canton)" used for unit testing
     */
    function zh_canton(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::CANTON_ZURICH_ID, triple_names::CANTON_ZURICH_NAME);
        $trp->set_from($t_wrd->word_zh()->phrase());
        $trp->set_verb($t_vrb->verb_is());
        $trp->set_to($t_wrd->word_canton()->phrase());
        $trp->set_description(triple_names::CANTON_ZURICH_COM);
        $trp->set_impact(impacts::HTP_ZH_CANTON);
        return $trp;
    }

    /**
     * @return triple "Zurich (canton)" with a medium impact to test sort by impact
     */
    function zh_canton_low_impact(): triple
    {
        $trp = $this->zh_canton();
        $trp->set_impact(impacts::MEDIUM);
        return $trp;
    }

    /**
     * @return triple "Zurich Insurance" (Zurich is a company) used for unit testing
     */
    function company_zurich(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::COMPANY_ZURICH_ID, triple_names::COMPANY_ZURICH);
        $trp->set_from($t_wrd->word_zh()->phrase());
        $trp->set_verb($t_vrb->verb_is());
        $trp->set_to($t_wrd->word_company()->phrase());
        $trp->set_impact(impacts::HTP_ZH_COMPANY);
        return $trp;
    }

    /**
     * @return triple "Zurich Insurance" with a high impact
     */
    function company_zurich_high_impact(): triple
    {
        $trp = $this->company_zurich();
        $trp->set_impact(impacts::HIGH);
        return $trp;
    }

    /**
     * @return triple "Zurich Insurance" as a stock with the market capitalisation as impact
     */
    function company_zurich_market_cap(): triple
    {
        $trp = $this->company_zurich();
        $trp->set_impact(impacts::MARKET_CAP_ZURICH_INSURANCE);
        return $trp;
    }

    /**
     * @return triple "ABB (company)" as a stock with the market capitalisation as impact
     */
    function abb_company(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::COMPANY_ABB_ID, triple_names::COMPANY_ABB);
        $trp->set_from($t_wrd->word_abb()->phrase());
        $trp->set_verb($t_vrb->verb_is());
        $trp->set_to($t_wrd->word_company()->phrase());
        $trp->set_impact(impacts::MARKET_CAP_ABB);
        return $trp;
    }

    /**
     * @return triple "Vestas SA" as a stock with the market capitalisation as impact
     */
    function vestas_company(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::COMPANY_VESTAS_ID, triple_names::COMPANY_VESTAS);
        $trp->set_from($t_wrd->word_vestas()->phrase());
        $trp->set_verb($t_vrb->verb_is());
        $trp->set_to($t_wrd->word_company()->phrase());
        $trp->set_impact(impacts::MARKET_CAP_VESTAS);
        return $trp;
    }

    /**
     * @return triple "US dollar" (dollar kind of US) used for unit testing;
     *         the currency is a triple since the split of the multi-word words
     */
    function us_dollar(): triple
    {
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::US_DOLLAR_ID, triple_names::US_DOLLAR_NAME);
        $trp->description = triple_names::US_DOLLAR_COM;
        return $trp;
    }

    /**
     * @return triple the spelling variant "U.S. dollar" used as an alias of "US dollar"
     */
    function u_s_dollar(): triple
    {
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::U_S_DOLLAR_ID, triple_names::U_S_DOLLAR_NAME);
        $trp->description = triple_names::US_DOLLAR_COM;
        return $trp;
    }

    /**
     * @return triple_ui the "Swiss franc" triple for frontend unit testing (e.g. as the
     *                   symbol target a CHF page-title category subtitle links to);
     *                   the *_ui suffix marks this as a frontend/UI factory per the
     *                   naming rule in docs/llm/coding.md
     */
    function swiss_franc_ui(): triple_ui
    {
        return new triple_ui($this->swiss_franc()->api_json());
    }

    /**
     * @return triple_ui "Swiss franc" with the related symbol and category phrases as loaded
     *                   with the triple from the backend e.g. to test the related phrases
     *                   shown on the default phrase page
     */
    function swiss_franc_related_ui(): triple_ui
    {
        $t_phr = new test_phrases($this->env);
        $trp = $this->swiss_franc_ui();
        $trp->phr_lst = $t_phr->list_swiss_franc_related_ui();
        return $trp;
    }

    /**
     * @return triple_ui the "US dollar" triple for frontend unit testing
     */
    function us_dollar_ui(): triple_ui
    {
        return new triple_ui($this->us_dollar()->api_json());
    }

    /**
     * @return triple_ui "US dollar" with the related alias, symbol and category phrases as loaded
     *                   with the triple from the backend e.g. to test the alias and symbol lines
     *                   shown on the default phrase page
     */
    function us_dollar_related_ui(): triple_ui
    {
        $t_phr = new test_phrases($this->env);
        $trp = $this->us_dollar_ui();
        $trp->phr_lst = $t_phr->list_us_dollar_related_ui();
        return $trp;
    }

    /**
     * @return triple "Swiss franc" (franc kind of Swiss) used for unit testing;
     *         the currency is a triple since the split of the multi-word words
     */
    function swiss_franc(): triple
    {
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::SWISS_FRANC_ID, triple_names::SWISS_FRANC);
        $trp->description = triple_names::SWISS_FRANC_COM;
        return $trp;
    }

    /**
     * @return triple "mio is symbol for million" used to test that a symbol shows the
     *         description of the word it stands for as its tooltip
     */
    function mio_symbol(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::MIO_SYMBOL_ID, triple_names::MIO_SYMBOL);
        $trp->set_from($t_wrd->word_mio_symbol()->phrase());
        $trp->set_verb($t_vrb->verb_is_symbol());
        $trp->set_to($t_wrd->word_million()->phrase());
        return $trp;
    }

    /**
     * @return triple "CHF is symbol for Swiss franc" used for unit testing the
     *         page-title category subtitle for SYMBOL-typed related entries
     */
    function symbol_chf(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::CHF_SYMBOL_ID, triple_names::CHF_SYMBOL);
        $trp->set_from($t_wrd->word_chf()->phrase());
        $trp->set_verb($t_vrb->verb_is_symbol());
        $trp->set_to($this->swiss_franc()->phrase());
        $trp->set_impact(impacts::SYMBOL_CHF);
        return $trp;
    }

    /**
     * @return triple "Swiss franc is a currency" used for unit testing word::similar
     */
    function swiss_franc_currency(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::SWISS_FRANC_CURRENCY_ID, triple_names::SWISS_FRANC_CURRENCY);
        $trp->set_from($this->swiss_franc()->phrase());
        $trp->set_verb($t_vrb->verb_is());
        $trp->set_to($t_wrd->currency()->phrase());
        $trp->set_impact(impacts::CURRENCY_CHF);
        return $trp;
    }

    /**
     * @return triple "Euro is a currency" used for unit testing word::similar
     */
    function euro_currency(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::EURO_CURRENCY_ID, triple_names::EURO_CURRENCY);
        $trp->set_from($t_wrd->euro()->phrase());
        $trp->set_verb($t_vrb->verb_is());
        $trp->set_to($t_wrd->currency()->phrase());
        $trp->set_impact(impacts::CURRENCY_EURO);
        return $trp;
    }

    /**
     * @return triple "EUR is symbol for Euro" - the euro equivalent of symbol_usd
     */
    function eur_symbol(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::EUR_SYMBOL_ID, triple_names::EUR_SYMBOL);
        $trp->set_from($t_wrd->word_eur()->phrase());
        $trp->set_verb($t_vrb->verb_is_symbol());
        $trp->set_to($t_wrd->euro()->phrase());
        $trp->set_impact(impacts::SYMBOL_EUR);
        return $trp;
    }

    /**
     * @return triple "€ is alias of Euro" - the euro equivalent of alias_dollar
     */
    function euro_sign_alias(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::EURO_SIGN_ALIAS_ID, triple_names::EURO_SIGN_ALIAS);
        $trp->set_from($t_wrd->word_euro_sign()->phrase());
        $trp->set_verb($t_vrb->verb_alias());
        $trp->set_to($t_wrd->euro()->phrase());
        $trp->set_impact(impacts::ALIAS_EURO);
        return $trp;
    }

    /**
     * @return triple "in EUR" - the euro equivalent of in_usd
     */
    function in_eur(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::IN_EUR_ID, triple_names::IN_EUR);
        $trp->set_from($t_wrd->word_eur()->phrase());
        $trp->set_verb($t_vrb->verb_in());
        $trp->set_to($t_wrd->euro()->phrase());
        $trp->set_impact(impacts::IN_EUR);
        return $trp;
    }

    /**
     * @return triple "$ is alias of US dollar" used for unit testing the alias display
     */
    function alias_dollar(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::DOLLAR_ALIAS_ID, triple_names::DOLLAR_ALIAS);
        $trp->set_from($t_wrd->word_dollar()->phrase());
        $trp->set_verb($t_vrb->verb_alias());
        $trp->set_to($this->us_dollar()->phrase());
        $trp->set_impact(impacts::ALIAS_DOLLAR);
        return $trp;
    }

    /**
     * @return triple "U.S. dollar is alias of US dollar" used for unit testing the alias display
     */
    function alias_u_s_dollar(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::U_S_DOLLAR_ALIAS_ID, triple_names::U_S_DOLLAR_ALIAS);
        $trp->set_from($this->u_s_dollar()->phrase());
        $trp->set_verb($t_vrb->verb_alias());
        $trp->set_to($this->us_dollar()->phrase());
        $trp->set_impact(impacts::ALIAS_U_S_DOLLAR);
        return $trp;
    }

    /**
     * @return triple "USD is symbol for US dollar" used for unit testing the symbol display
     */
    function symbol_usd(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::USD_SYMBOL_ID, triple_names::USD_SYMBOL);
        $trp->set_from($t_wrd->word_usd()->phrase());
        $trp->set_verb($t_vrb->verb_is_symbol());
        $trp->set_to($this->us_dollar()->phrase());
        $trp->set_impact(impacts::SYMBOL_USD);
        return $trp;
    }

    /**
     * @return triple "in USD" used for unit testing the related phrases that are not an alias or symbol
     */
    function in_usd(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::IN_USD_ID, triple_names::IN_USD);
        $trp->set_from($t_wrd->word_usd()->phrase());
        $trp->set_verb($t_vrb->verb_in());
        $trp->set_to($this->us_dollar()->phrase());
        $trp->set_impact(impacts::IN_USD);
        return $trp;
    }

    /**
     * @return triple "USD is a currency" used for unit testing word::similar
     */
    function usd_currency(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::US_DOLLAR_CURRENCY_ID, triple_names::US_DOLLAR_CURRENCY);
        $trp->set_from($this->us_dollar()->phrase());
        $trp->set_verb($t_vrb->verb_is());
        $trp->set_to($t_wrd->currency()->phrase());
        $trp->set_impact(impacts::CURRENCY_USD);
        return $trp;
    }

    /**
     * @return triple "Bern (city)" used for unit testing
     */
    function triple_bern(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::CITY_BE_ID, triple_names::CITY_BE);
        $trp->set_from($t_wrd->word_bern()->phrase());
        $trp->set_verb($t_vrb->verb_is());
        $trp->set_to($t_wrd->word_city()->phrase());
        return $trp;
    }

    /**
     * @return triple "Geneva (city)" used for unit testing
     */
    function triple_ge(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::CITY_GE_ID, triple_names::CITY_GE);
        $trp->set_from($t_wrd->word_ge()->phrase());
        $trp->set_verb($t_vrb->verb_is());
        $trp->set_to($t_wrd->word_city()->phrase());
        return $trp;
    }

    /**
     * @return triple "global problem" used for start view unit testing
     */
    function global_problem(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::GLOBAL_PROBLEM_ID, triple_names::GLOBAL_PROBLEM);
        $trp->set_from($t_wrd->word_problem()->phrase());
        $trp->set_verb($t_vrb->verb_can_be());
        $trp->set_to($t_wrd->word_global()->phrase());
        return $trp;
    }

    /**
     * @return triple "global warming" used for start view unit testing
     */
    function global_warming(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::GLOBAL_WARMING_ID, triple_names::GLOBAL_WARMING);
        $trp->set_from($t_wrd->word_climate()->phrase());
        $trp->set_verb($t_vrb->verb_can_get());
        $trp->set_to($t_wrd->word_warmer()->phrase());
        return $trp;
    }

    /**
     * @return triple that "global warming" "is a" "global problem" used for start view unit testing
     */
    function global_warming_problem(): triple
    {
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::GLOBAL_WARMING_PROBLEM_ID);
        $trp->set_from($this->global_warming()->phrase());
        $trp->set_verb($t_vrb->verb_is());
        $trp->set_to($this->global_problem()->phrase());
        return $trp;
    }

    /**
     * @return triple that "global warming potential" "is a" "global warming" used for start view unit testing
     */
    function global_warming_potential(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::GWP_ID);
        $trp->set_name(triple_names::GWP);
        $trp->set_from($this->global_warming()->phrase());
        $trp->set_verb($t_vrb->verb_is());
        $trp->set_to($t_wrd->word_potential()->phrase());
        return $trp;
    }

    /**
     * @return triple that "populism" "is a" "global problem" used for start view unit testing
     */
    function populism_problem(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::POPULISM_PROBLEM_ID);
        $trp->set_from($t_wrd->word_populism()->phrase());
        $trp->set_verb($t_vrb->verb_is());
        $trp->set_to($this->global_problem()->phrase());
        return $trp;
    }

    /**
     * @return triple that "poverty" "is a" "global problem" used for start view unit testing
     */
    function poverty_problem(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::POVERTY_PROBLEM_ID);
        $trp->set_from($t_wrd->word_poverty()->phrase());
        $trp->set_verb($t_vrb->verb_is());
        $trp->set_to($this->global_problem()->phrase());
        return $trp;
    }

    /**
     * @return triple "mayor column (system)" - the tier of the columns shown on every screen
     */
    function column_mayor(): triple
    {
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::SYSTEM_COLUMN_MAYOR_ID, triple_names::SYSTEM_COLUMN_MAYOR);
        return $trp;
    }

    /**
     * @return triple "column solution (high prio)" that defines "solution" as a mayor table column
     */
    function column_solution(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::COLUMN_SOLUTION_ID, triple_names::COLUMN_SOLUTION);
        $trp->set_from($t_wrd->solution()->phrase());
        $trp->set_verb($t_vrb->verb_can_be());
        $trp->set_to($this->column_mayor()->phrase());
        return $trp;
    }

    /**
     * @return triple "column cost" that defines "cost" as a mayor table column
     */
    function column_cost(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::COLUMN_COST_ID, triple_names::COLUMN_COST);
        $trp->set_from($t_wrd->word_cost()->phrase());
        $trp->set_verb($t_vrb->verb_can_be());
        $trp->set_to($this->column_mayor()->phrase());
        return $trp;
    }

    /**
     * @return triple "column gain" that defines "gain" as a mayor table column
     */
    function column_gain(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::COLUMN_GAIN_ID, triple_names::COLUMN_GAIN);
        $trp->set_from($t_wrd->word_gain()->phrase());
        $trp->set_verb($t_vrb->verb_can_be());
        $trp->set_to($this->column_mayor()->phrase());
        return $trp;
    }

    /**
     * @return triple "column loss" that defines "loss" as a mayor table column
     */
    function column_loss(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::COLUMN_LOSS_ID, triple_names::COLUMN_LOSS);
        $trp->set_from($t_wrd->word_loss()->phrase());
        $trp->set_verb($t_vrb->verb_can_be());
        $trp->set_to($this->column_mayor()->phrase());
        return $trp;
    }

    /**
     * @return triple "reduce climate gas emissions" - the solution of the global warming problem
     */
    function reduce_emissions(): triple
    {
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::REDUCE_EMISSIONS_ID, triple_names::REDUCE_EMISSIONS);
        return $trp;
    }

    /**
     * @return triple "avoid wrong decisions" - the solution of the populism problem
     */
    function avoid_wrong_decisions(): triple
    {
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::AVOID_WRONG_DECISIONS_ID, triple_names::AVOID_WRONG_DECISIONS);
        return $trp;
    }

    /*
     * the triples that link a solution to "solution": the table asks which phrase of a row is a
     * solution, so unlike the solutions themselves these triples need their from/verb/to link
     */

    /**
     * @return triple that "reduce climate gas emissions" "is a" "solution"
     */
    function reduce_emissions_solution(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::REDUCE_EMISSIONS_SOLUTION_ID);
        $trp->set_from($this->reduce_emissions()->phrase());
        $trp->set_verb($t_vrb->verb_is());
        $trp->set_to($t_wrd->solution()->phrase());
        return $trp;
    }

    /**
     * @return triple that "avoid wrong decisions" "is a" "solution"
     */
    function avoid_wrong_decisions_solution(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::AVOID_WRONG_DECISIONS_SOLUTION_ID);
        $trp->set_from($this->avoid_wrong_decisions()->phrase());
        $trp->set_verb($t_vrb->verb_is());
        $trp->set_to($t_wrd->solution()->phrase());
        return $trp;
    }

    /**
     * @return triple that "research" "is a" "solution"
     */
    function research_solution(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::RESEARCH_SOLUTION_ID);
        $trp->set_from($t_wrd->word_research()->phrase());
        $trp->set_verb($t_vrb->verb_is());
        $trp->set_to($t_wrd->solution()->phrase());
        return $trp;
    }

    /**
     * @return triple that "taxes" "is a" "solution"
     */
    function taxes_solution(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::TAXES_SOLUTION_ID);
        $trp->set_from($t_wrd->word_taxes()->phrase());
        $trp->set_verb($t_vrb->verb_is());
        $trp->set_to($t_wrd->solution()->phrase());
        return $trp;
    }

    /**
     * @return triple that "spending" "is a" "solution"
     */
    function spending_solution(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::SPENDING_SOLUTION_ID);
        $trp->set_from($t_wrd->word_spending()->phrase());
        $trp->set_verb($t_vrb->verb_is());
        $trp->set_to($t_wrd->solution()->phrase());
        return $trp;
    }

    /*
     * the problems of solution_prio.json that are a triple and the solution of each; the phrases
     * are named by id and name only, because the start page table uses them as the phrase of a
     * value and never follows the from/verb/to link (see docs/llm/testing.md)
     */

    function wealth_concentration(): triple
    {
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::WEALTH_CONCENTRATION_ID, triple_names::WEALTH_CONCENTRATION);
        return $trp;
    }

    function basic_income(): triple
    {
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::BASIC_INCOME_ID, triple_names::BASIC_INCOME);
        return $trp;
    }

    function platform_regulation(): triple
    {
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::PLATFORM_REGULATION_ID, triple_names::PLATFORM_REGULATION);
        return $trp;
    }

    function market_power(): triple
    {
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::MARKET_POWER_ID, triple_names::MARKET_POWER);
        return $trp;
    }

    function market_share_tax(): triple
    {
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::MARKET_SHARE_TAX_ID, triple_names::MARKET_SHARE_TAX);
        return $trp;
    }

    function biased_information(): triple
    {
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::BIASED_INFORMATION_ID, triple_names::BIASED_INFORMATION);
        return $trp;
    }

    function delphi_method(): triple
    {
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::DELPHI_METHOD_ID, triple_names::DELPHI_METHOD);
        return $trp;
    }

    function black_box_ai(): triple
    {
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::BLACK_BOX_AI_ID, triple_names::BLACK_BOX_AI);
        return $trp;
    }

    function public_ai(): triple
    {
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::PUBLIC_AI_ID, triple_names::PUBLIC_AI);
        return $trp;
    }

    function citizen_participation(): triple
    {
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::CITIZEN_PARTICIPATION_ID, triple_names::CITIZEN_PARTICIPATION);
        return $trp;
    }

    function fluid_democracy(): triple
    {
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::FLUID_DEMOCRACY_ID, triple_names::FLUID_DEMOCRACY);
        return $trp;
    }

    function gdp_mismeasurement(): triple
    {
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::GDP_MISMEASUREMENT_ID, triple_names::GDP_MISMEASUREMENT);
        return $trp;
    }

    function gross_domestic_usage(): triple
    {
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::GROSS_DOMESTIC_USAGE_ID, triple_names::GROSS_DOMESTIC_USAGE);
        return $trp;
    }

    function proprietary_software(): triple
    {
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::PROPRIETARY_SOFTWARE_ID, triple_names::PROPRIETARY_SOFTWARE);
        return $trp;
    }

    function free_software(): triple
    {
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::FREE_SOFTWARE_ID, triple_names::FREE_SOFTWARE);
        return $trp;
    }

    /**
     * @return triple that defines that "health" "can be a" "global problem" used for start view unit testing
     */
    function potential_health_problem(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::POTENTIAL_HEALTH_PROBLEM_ID);
        $trp->set_from($t_wrd->word_health()->phrase());
        $trp->set_verb($t_vrb->verb_can_be());
        $trp->set_to($this->global_problem()->phrase());
        return $trp;
    }

    /**
     * @return triple that defines that "education" "can be a" "global problem" used for start view unit testing
     */
    function potential_education_problem(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::POTENTIAL_EDUCATION_PROBLEM_ID);
        $trp->set_from($t_wrd->word_education()->phrase());
        $trp->set_verb($t_vrb->verb_can_be());
        $trp->set_to($this->global_problem()->phrase());
        return $trp;
    }

    /**
     * @return triple that defines "time points"
     */
    function time_points(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::TIME_POINTS_ID, triple_names::TIME_POINTS);
        $trp->set_from($t_wrd->word_time()->phrase());
        $trp->set_verb($t_vrb->verb_can_be());
        $trp->set_to($t_wrd->word_points()->phrase());
        return $trp;
    }

    /**
     * @return triple that defines the "happy time points"
     */
    function happy_time_points(): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set(triple_names::HAPPY_TIME_POINTS_ID, triple_names::HAPPY_TIME_POINTS);
        $trp->set_from($t_wrd->word_happy()->phrase());
        $trp->set_verb($t_vrb->verb_can_be());
        $trp->set_to($this->time_points()->phrase());
        return $trp;
    }

    /**
     * @return triple_list with just one element to test the group id
     */
    function triple_list_one(): triple_list
    {
        $lst = new triple_list($this->env->usr1);
        $lst->add($this->triple_pi());
        return $lst;
    }

    /**
     * @return triple_list with only a few triples for efficient testing of the main functionalities
     */
    function triple_list_short(): triple_list
    {
        $lst = new triple_list($this->env->usr1);
        $lst->add($this->triple_filled());
        $lst->add($this->triple_pi());
        $lst->add($this->triple_gwp());
        return $lst;
    }

    /**
     * @return triple_list with many triples for testing the handling of longer lists including paging
     */
    function triple_list(): triple_list
    {
        $lst = new triple_list($this->env->usr1);
        $lst->add($this->triple_filled_included());
        $lst->add($this->triple_pi());
        $lst->add($this->zh_city());
        $lst->add($this->zh_canton());
        return $lst;
    }

    /**
     * @return triple_list with all triples for testing the handling of longer lists including paging
     */
    function triple_list_all(): triple_list
    {
        $lst = new triple_list($this->env->usr1);
        $lst->add($this->triple_filled_included());
        $lst->add($this->triple_pi());
        $lst->add($this->triple_pi_name());
        $lst->add($this->triple_e());
        $lst->add($this->global_problem());
        $lst->add($this->triple_global_warming());
        $lst->add($this->triple_gwp());
        $lst->add($this->global_warming_potential());
        $lst->add($this->populism_problem());
        $lst->add($this->poverty_problem());
        $lst->add($this->potential_health_problem());
        $lst->add($this->potential_education_problem());
        $lst->add($this->time_points());
        $lst->add($this->happy_time_points());
        $lst->add($this->zh_city());
        $lst->add($this->zh_canton());
        $lst->add($this->triple_bern());
        $lst->add($this->triple_ge());
        return $lst;
    }

    function triple_list_ui(): triple_list_ui
    {
        $tl = new test_lib();
        return $tl->list_to_ui($this->triple_list_all(), [api_types::INCL_PHRASES]);
    }


    /*
     * time
     */

    function year_2019(): triple
    {
        $t_wrd = new test_words($this->env);
        return $this->year_x(triple_names::YEAR_2019_ID, triple_names::YEAR_2019, $t_wrd->word_2019());
    }

    function year_2020(): triple
    {
        $t_wrd = new test_words($this->env);
        return $this->year_x(triple_names::YEAR_2020_ID, triple_names::YEAR_2020, $t_wrd->word_2020());
    }

    function year_2021(): triple
    {
        $t_wrd = new test_words($this->env);
        return $this->year_x(triple_names::YEAR_2021_ID, triple_names::YEAR_2021, $t_wrd->word_2021());
    }

    function year_2022(): triple
    {
        $t_wrd = new test_words($this->env);
        return $this->year_x(triple_names::YEAR_2022_ID, triple_names::YEAR_2022, $t_wrd->word_2022());
    }

    function year_2023(): triple
    {
        $t_wrd = new test_words($this->env);
        return $this->year_x(triple_names::YEAR_2023_ID, triple_names::YEAR_2023, $t_wrd->word_2023());
    }

    function year_2024(): triple
    {
        $t_wrd = new test_words($this->env);
        return $this->year_x(triple_names::YEAR_2024_ID, triple_names::YEAR_2024, $t_wrd->word_2024());
    }

    function year_2025(): triple
    {
        $t_wrd = new test_words($this->env);
        return $this->year_x(triple_names::YEAR_2025_ID, triple_names::YEAR_2025, $t_wrd->word_2025());
    }

    function year_2026(): triple
    {
        $t_wrd = new test_words($this->env);
        return $this->year_x(triple_names::YEAR_2026_ID, triple_names::YEAR_2026, $t_wrd->word_2026());
    }

    function year_2027(): triple
    {
        $t_wrd = new test_words($this->env);
        return $this->year_x(triple_names::YEAR_2027_ID, triple_names::YEAR_2027, $t_wrd->word_2027());
    }

    function year_2028(): triple
    {
        $t_wrd = new test_words($this->env);
        return $this->year_x(triple_names::YEAR_2028_ID, triple_names::YEAR_2028, $t_wrd->word_2028());
    }

    function year_2029(): triple
    {
        $t_wrd = new test_words($this->env);
        return $this->year_x(triple_names::YEAR_2029_ID, triple_names::YEAR_2029, $t_wrd->word_2029());
    }

    function year_2030(): triple
    {
        $t_wrd = new test_words($this->env);
        return $this->year_x(triple_names::YEAR_2030_ID, triple_names::YEAR_2030, $t_wrd->word_2030());
    }


    private function year_x(int $id, string $name, word $year): triple
    {
        $t_wrd = new test_words($this->env);
        $t_vrb = new test_verbs($this->env);
        $trp = new triple($this->env->usr1);
        $trp->set($id, $name);
        $trp->set_from($year->phrase());
        $trp->set_verb($t_vrb->verb_is());
        $trp->set_to($t_wrd->word_year()->phrase());
        return $trp;
    }

    /*
     * random
     */

    /**
     * create a triple with random parameters for speed testing
     *
     * @param int|null $id a given sequence number to assure that the triple name is unique
     * @param phrase_list $phr_lst list of the phrases created until now
     * @return triple the created triple object
     */
    function random(?int $id, phrase_list $phr_lst, test_cleanup $t): triple
    {
        global $sys;

        $t_vrb = new test_verbs($t);

        $from_id = rand(1, $phr_lst->count());
        $to_id = 1;
        if ($phr_lst->count() < 2) {
            log_err('phrase list too small for triple random');
        } elseif ($phr_lst->count() == 2) {
            if ($from_id == 1) {
                $to_id = 2;
            }
            $from_id = rand(1, $phr_lst->count());
        } else {
            $to_id = rand(1, $phr_lst->count());
            while ($from_id == $to_id) {
                $to_id = rand(1, $phr_lst->count());
            }
        }

        // make sure that from and to is not the same
        $trp = new triple($this->env->usr1);
        $trp->id = $id;
        $trp->set_from($phr_lst->get($from_id)->phrase());
        $trp->set_verb($t_vrb->random());
        $trp->set_to($phr_lst->get($to_id)->phrase());
        $trp->set_name(word_names::TEST_SPEED_PREFIX . $id);

        $type_id = rand(1, $sys->typ_lst->phr_typ->count());
        $trp->set_type_id($type_id, new user_message($this->env->usr1));
        return $trp;
    }

}