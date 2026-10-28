<?php

namespace Zukunft\ZukunftCom\test\php\unit;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once html_paths::EXECUTE . 'system_form.php';
include_once html_paths::HELPER . 'data_object.php';
include_once html_paths::USER . 'user_message.php';
include_once paths::SHARED_CONST_FIELDS . 'fields.php';
include_once paths::SHARED_CONST_FIELDS . 'triple_fields.php';
include_once paths::SHARED_CONST . 'triples.php';
include_once paths::SHARED_CONST . 'words.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED . 'url_var.php';
include_once test_paths::CONST . 'word_names.php';
include_once test_paths::CREATE . 'test_phrases.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type;
use Zukunft\ZukunftCom\main\php\cfg\helper\data_object;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\web\component\execute\system_form;
use Zukunft\ZukunftCom\main\php\web\helper\data_object as data_object_ui;
use Zukunft\ZukunftCom\main\php\web\user\user_message as user_message_ui;
use Zukunft\ZukunftCom\main\php\web\word\triple as triple_ui;
use Zukunft\ZukunftCom\main\php\shared\const\fields\fields;
use Zukunft\ZukunftCom\main\php\shared\const\fields\triple_fields;
use Zukunft\ZukunftCom\main\php\shared\const\impacts;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\main\php\shared\types\phrase_types;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\test\php\const\triple_names;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\create\test_const;
use Zukunft\ZukunftCom\test\php\create\test_phrases;
use Zukunft\ZukunftCom\test\php\create\test_triples;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class triple_tests
{
    function run(test_cleanup $t): void
    {
        $msg = new user_message();


        // init
        $sc = new sql_creator();
        $t_trp = new test_triples($t);
        $msg_ui = new user_message_ui();
        $t->name = 'triple->';
        $t->resource_path = 'db/triple/';

        // start the test section (ts)
        $ts = 'unit triple ';
        $t->header($ts);

        $t->subheader($ts . 'sql setup');
        $trp = $t_trp->triple();
        $t->assert_sql_table_create($trp);
        $t->assert_sql_index_create($trp);
        $t->assert_sql_foreign_key_create($trp);

        $t->subheader($ts . 'sql read');
        $trp = new triple($t->usr1);
        $t->assert_sql_by_id($sc, $trp);
        $t->assert_sql_by_name($sc, $trp);
        $t->assert_sql_by_link($sc, $trp);
        $this->assert_sql_by_name_generated($sc, $trp, $t);

        $t->subheader($ts . 'sql read standard and user changes by id');
        $trp = new triple($t->usr1);
        $trp->id = 2;
        $t->assert_sql_standard($sc, $trp);
        $t->assert_sql_user_changes($sc, $trp);
        // the same two queries for many objects at once, which the user page uses to read the
        // standard values and the other users of all changed objects of one type with one query
        $t->assert_sql_standard_by_ids($sc, $trp);
        $t->assert_sql_changing_users_by_ids($sc, $trp);

        $t->subheader($ts . 'sql read standard by name');
        $trp = new triple($t->usr1);
        $trp->set_name(triple_names::PI);
        $t->assert_sql_standard_by_name($sc, $trp);

        $t->subheader($ts . 'sql read standard by link');
        $trp = $t_trp->triple();
        $t->assert_sql_standard_by_type_link($sc, $trp);

        $t->subheader($ts . 'sql write insert');
        $trp = $t_trp->triple();
        $t->assert_sql_insert($sc, $trp);
        $t->assert_sql_insert($sc, $trp, [sql_type::USER]);
        $t->assert_sql_insert($sc, $trp, [sql_type::LOG, sql_type::USER]);
        $trp_excl = $t_trp->triple();
        $trp_excl->excluded = true;
        $t->assert_sql_insert($sc, $trp_excl);
        $trp_excl->description = '';
        $trp_excl->set_type('', new user_message($t->usr1));
        $t->assert_sql_insert($sc, $trp_excl, [sql_type::LOG, sql_type::USER]);
        $trp = $t_trp->triple_incomplete();
        $t->assert_sql_insert_fail($sc, $trp, [sql_type::LOG]);

        $t->subheader($ts . 'sql write update');
        $trp = $t_trp->triple();
        $trp_renamed = $trp->cloned_named(word_names::TEST_RENAMED);
        $t->assert_sql_update($sc, $trp_renamed, $trp);
        $t->assert_sql_update($sc, $trp_renamed, $trp, [sql_type::USER]);
        $t->assert_sql_update($sc, $trp_renamed, $trp, [sql_type::LOG]);
        $t->assert_sql_update($sc, $trp_renamed, $trp, [sql_type::LOG, sql_type::USER]);
        $t->assert_sql_update($sc, $trp_excl, $trp, [sql_type::LOG]);
        $t->assert_sql_update($sc, $trp_excl, $trp, [sql_type::LOG, sql_type::USER]);

        $t->subheader($ts . 'sql delete');
        // TODO Prio 0 activate db write
        $t->assert_sql_delete($sc, $trp);
        $t->assert_sql_delete($sc, $trp, [sql_type::USER]);
        // is covered already by the horizontal tests
        //$t->assert_sql_delete($sc, $trp, [sql_type::LOG]);
        $t->assert_sql_delete($sc, $trp, [sql_type::LOG, sql_type::USER]);
        $t->assert_sql_delete($sc, $trp, [sql_type::EXCLUDE]);
        $t->assert_sql_delete($sc, $trp, [sql_type::USER, sql_type::EXCLUDE]);

        $t->subheader($ts . 'view base object handling');
        $trp = $t_trp->triple_filled_add_name();
        $t->assert_reset($trp);

        $t->subheader($ts . 'code id on load');

        // the code id is read from the database for every user, because the permission
        // to set a code id is checked on the write and never on the read
        $test_name = 'a normal user reads the code id of a triple from the database';
        $db_row = [
            triple_fields::FLD_ID => triple_names::SYSTEM_TEST_ADD_ID,
            fields::FLD_CODE_ID => triple_names::SYSTEM_TEST_ADD_CODE_ID
        ];
        $trp = new triple($t->usr_normal);
        $trp->row_mapper_sandbox($db_row, $msg);
        $t->assert($test_name, $trp->get_code_id(), triple_names::SYSTEM_TEST_ADD_CODE_ID);
        $msg->reset();
        $test_name = 'a normal user still cannot set the code id of a triple';
        $trp = new triple($t->usr_normal);
        $t->assert_false($test_name, $trp->set_code_id(
            triple_names::SYSTEM_TEST_ADD_CODE_ID, new user_message($t->usr_normal)));

        $t->subheader($ts . 'view on load');

        // the default view is read from the database, so that the views tab of the triple offers it on top
        $test_name = 'the default view of a triple is read from the database';
        $db_row = [
            triple_fields::FLD_ID => triple_names::MATH_CONST_ID,
            fields::FLD_VIEW => views::MATH_CONST_ID
        ];
        $trp = new triple($t->usr1);
        $trp->row_mapper_sandbox($db_row, $msg);
        $t->assert($test_name, $trp->get_view_id(), views::MATH_CONST_ID);
        $msg->reset();
        $test_name = '... and a triple without a default view has none';
        $db_row = [
            triple_fields::FLD_ID => triple_names::MATH_CONST_ID,
            fields::FLD_VIEW => null
        ];
        $trp = new triple($t->usr1);
        $trp->row_mapper_sandbox($db_row, $msg);
        $t->assert($test_name, $trp->get_view_id(), 0);
        $msg->reset();

        $t->subheader($ts . 'reverse check');

        // e.g. a page part that moves up is the reverse of the order triple that the move removes
        global $sys;
        $test_name = 'an order triple can be the reverse of another order triple';
        $trp = $t_trp->triple();
        $trp->set_verb_id($sys->typ_lst->vrb->id(verbs::FOLLOW));
        $t->assert_false($test_name, $trp->needs_reverse_check());
        $test_name = '... but another triple must not be the reverse of an existing one';
        $trp = $t_trp->triple();
        $t->assert_true($test_name, $trp->needs_reverse_check());

        $t->subheader($ts . 'debug id');

        // the debug id names the verb between the linked phrases
        $test_name = 'the debug id shows the verb';
        $trp = $t_trp->triple();
        $dsp = $trp->dsp_id();
        $t->assert_text_contains($test_name, $dsp, verbs::PART_NAME);

        // ... and the id of the triple
        $test_name = 'the debug id shows the triple id';
        $trp = $t_trp->triple();
        $dsp = $trp->dsp_id();
        $t->assert_text_contains($test_name, $dsp, triple_fields::FLD_ID);

        // negative: a triple without id and links shows its name, but no triple id
        $test_name = 'the debug id of a triple without id';
        $trp = $t_trp->triple_name_only();
        $dsp = $trp->dsp_id();
        $t->assert_text_not_contains($test_name, $dsp, triple_fields::FLD_ID);

        $t->subheader($ts . 'rename routing');

        // a name change of a triple is a key update (so the duplicate name check of the save
        // runs), but it never identifies the database row: only a change of the link fields
        // (from, verb, to) forces a new row, so a rename keeps the id and the related values
        // (see sandbox::save and is_id_key_updated)
        $trp_db = $t_trp->triple();
        $trp_ren = $t_trp->triple();
        $trp_ren->set_name($trp_db->name() . ' renamed');
        $test_name = 'a triple rename is a key update for the duplicate check';
        $t->assert_true($test_name, $trp_ren->is_key_updated($trp_db));
        $test_name = 'a triple rename never changes the database row identity';
        $t->assert_false($test_name, $trp_ren->is_id_key_updated($trp_db));
        $trp_lnk = $t_trp->triple();
        $trp_lnk->set_from($t_trp->triple_pi()->phrase());
        $test_name = 'a changed from phrase changes the database row identity of a triple';
        $t->assert_true($test_name, $trp_lnk->is_id_key_updated($trp_db));

        $t->subheader($ts . 'no update diff treats an unset link end as empty');
        // under ex_def (the no_upd import mode) an unset from (id 0) is empty,
        // so filling it from the import is a fill-up, not a reported overwrite
        $trp_full = $t_trp->triple();
        $trp_empty_from = $t_trp->triple();
        $trp_empty_from->set_from(new phrase($t->usr1));
        $test_name = 'diff_msg reports no overwrite when the db from is empty and ex_def is set';
        $diff = $trp_empty_from->diff_msg($trp_full, true);
        $t->assert_true($test_name, $diff->is_ok());
        // without ex_def the same unset from is a real, reported difference
        $test_name = 'diff_msg reports the difference for an empty db from without ex_def';
        $diff = $trp_empty_from->diff_msg($trp_full, false);
        $t->assert_false($test_name, $diff->is_ok());
        // two set from ends that differ stay a reported overwrite even under ex_def
        $trp_other_from = $t_trp->triple();
        $trp_other_from->set_from($trp_other_from->get_to());
        $test_name = 'diff_msg reports an overwrite when both from ends are set but differ';
        $diff = $trp_full->diff_msg($trp_other_from, true);
        $t->assert_false($test_name, $diff->is_ok());

        $t->subheader($ts . 'fill');
        // the fill takes over only the fields that the request does not name, so a requested
        // type or name is the change and never a conflict (see update_put::fill_from_db)
        $trp_db = $t_trp->triple_filled_public();
        $trp_req = new triple($t->usr1);
        $trp_req->set_type(phrase_types::TIME, new user_message($t->usr1));
        $fill_msg = $trp_req->fill($trp_db, $t->usr1);
        $test_name = 'the fill with another requested type reports no conflict';
        $t->assert_true($test_name, $fill_msg->is_ok());
        $test_name = '... and keeps the requested type';
        $t->assert($test_name, $trp_req->type_code_id($msg), phrase_types::TIME);
        $test_name = '... but takes over the given name, because the name is not requested';
        $t->assert($test_name, $trp_req->name_given(), triple_names::MATH_CONST_GIVEN);
        $trp_ren = new triple($t->usr1);
        $trp_ren->set_name(triple_names::SYSTEM_TEST_RENAMED);
        $trp_ren->fill($trp_db, $t->usr1);
        $test_name = 'the fill of a renamed triple does not take over the old given name';
        $t->assert_null($test_name, $trp_ren->name_given());

        $t->subheader($ts . 'api');
        $trp = $t_trp->triple_filled_public();
        $t->assert_api_json($trp);
        // own file like word_full, because triple.json is the db triple of the api get test
        $t->assert_api($trp, 'triple_full');

        $t->subheader($ts . 'api mapping of an incomplete message');
        // an api message with only the id maps the id and does not fail
        $test_name = 'api_mapper with only the id keeps the id';
        $trp = new triple($t->usr1);
        $trp->api_mapper([json_fields::ID => triple_names::MATH_CONST_ID], $msg);
        $t->assert($test_name, $trp->id(), triple_names::MATH_CONST_ID);

        // an api message with only the name maps the name and leaves the id at 0
        $test_name = 'api_mapper with only the name keeps the name';
        $trp = new triple($t->usr1);
        $trp->api_mapper([json_fields::NAME => triple_names::MATH_CONST], $msg);
        $t->assert($test_name, $trp->name(), triple_names::MATH_CONST);
        $test_name = 'api_mapper with only the name leaves the id at 0';
        $t->assert($test_name, $trp->id(), 0);

        // an api message with neither the id nor the name maps nothing and leaves the id at 0
        $test_name = 'api_mapper with neither id nor name leaves the id at 0';
        $trp = new triple($t->usr1);
        $trp->api_mapper([], $msg);
        $t->assert($test_name, $trp->id(), 0);

        // an api message where the from, verb and to are present but null (an incomplete triple) maps
        // them to empty objects instead of throwing a TypeError; guards the phrase_from_api_json and
        // verb_from_api_json regression
        $test_name = 'api_mapper with a null from leaves the from phrase empty';
        $trp = new triple($t->usr1);
        $trp->api_mapper([
            json_fields::ID => triple_names::MATH_CONST_ID,
            json_fields::FROM => null,
            json_fields::VERB => null,
            json_fields::TO => null
        ], $msg);
        $t->assert($test_name, $trp->get_from()?->id() ?? 0, 0);
        $test_name = 'api_mapper with a null to leaves the to phrase empty';
        $t->assert($test_name, $trp->get_to()?->id() ?? 0, 0);
        $test_name = 'api_mapper with a null from/verb/to keeps the id';
        $t->assert($test_name, $trp->id(), triple_names::MATH_CONST_ID);
        $msg->reset();

        $t->subheader($ts . 'api mapper');

        // the to phrase of the api json is given by its id, so the mapper sets the to phrase by the id
        $test_name = 'the api mapper sets the to phrase of the triple';
        $trp_map = new triple($t->usr1);
        $trp_map->api_mapper($t_trp->triple()->api_json_array([], $msg), $msg);
        $t->assert($test_name, $trp_map->get_to()->id(), word_names::MATH_ID);
        $msg->reset();

        // a triple without the from phrase is incomplete, so the mapper reports the missing from phrase
        $test_name = 'the api mapper reports a missing from phrase';
        $trp_map = new triple($t->usr1);
        $api_json = [json_fields::ID => triple_names::MATH_CONST_ID, json_fields::FROM => null];
        $t->assert_false($test_name, $trp_map->api_mapper($api_json, $msg));
        $msg->reset();

        $t->subheader($ts . 'api json array');

        // with the phrases the from phrase is sent with its name for the page title
        $test_name = 'the from phrase is sent with the phrases';
        $trp = $t_trp->triple();
        $vars = $trp->api_json_array([api_types::INCL_PHRASES, api_types::TEST_MODE], $msg);
        $t->assert($test_name, $vars[json_fields::FROM_PHRASE][json_fields::NAME], word_names::CONST_NAME);
        $msg->reset();

        // negative: without the phrases only the id of the from phrase is sent
        $test_name = 'the from phrase is not sent without the phrases';
        $trp = $t_trp->triple();
        $vars = $trp->api_json_array([], $msg);
        $t->assert_key_not_exists($test_name, json_fields::FROM_PHRASE, $vars);
        $msg->reset();

        // with the phrases the verb is sent with its name
        $test_name = 'the verb name is sent with the phrases';
        $trp = $t_trp->triple();
        $vars = $trp->api_json_array([api_types::INCL_PHRASES, api_types::TEST_MODE], $msg);
        $t->assert($test_name, $vars[json_fields::VERB][json_fields::NAME], verbs::PART_NAME);
        $msg->reset();

        // negative: without the phrases the verb is sent only by its id
        $test_name = 'the verb id is sent without the phrases';
        $trp = $t_trp->triple();
        $vars = $trp->api_json_array([], $msg);
        $t->assert($test_name, $vars[json_fields::VERB], verbs::PART_ID);
        $msg->reset();

        // a triple without a given name is sent with the generated name
        $test_name = 'the generated name is sent if no name is given';
        $trp = $t_trp->triple_link_only();
        $vars = $trp->api_json_array([], $msg);
        $name = word_names::CONST_NAME . ' ' . verbs::PART_NAME . ' ' . word_names::MATH;
        $t->assert($test_name, $vars[json_fields::NAME], $name);
        $msg->reset();

        // negative: the name of a named triple is not replaced by the generated name
        $test_name = 'the name is sent if given';
        $trp = $t_trp->triple();
        $vars = $trp->api_json_array([], $msg);
        $t->assert($test_name, $vars[json_fields::NAME], triple_names::MATH_CONST);
        $msg->reset();

        // the weight is only sent if set
        $test_name = 'the weight is sent if set';
        $trp = $t_trp->triple_impact();
        $vars = $trp->api_json_array([], $msg);
        $t->assert($test_name, $vars[json_fields::WEIGHT], test_const::TRIPLE_WEIGHT);
        $msg->reset();

        // negative: a triple without weight sends no weight
        $test_name = 'no weight is sent if not set';
        $trp = $t_trp->triple();
        $vars = $trp->api_json_array([], $msg);
        $t->assert_key_not_exists($test_name, json_fields::WEIGHT, $vars);
        $msg->reset();

        $t->subheader($ts . 'frontend');
        $trp = $t_trp->triple_pi();
        $t->assert_api_to_ui($trp, new triple_ui());

        // the weight is a float, so the edit field must keep the fraction; without float in
        // the form_field value union the coercion tries int before string and truncates
        // 0.5 to 0 (see html_base::form_field)
        $form = new system_form();
        $test_name = 'the edit form shows the fractional weight of a triple unchanged';
        $t->assert_text_contains($test_name, $form->form_field_weight($t_trp->triple_impact_ui()), 'value="0.5"');
        $test_name = 'the edit form of a triple without a weight starts with an empty weight field';
        $t->assert_false($test_name, str_contains($form->form_field_weight($t_trp->swiss_franc_ui()), 'value='));

        // the url array is the inverse of url_mapper, so it carries the linked phrases, the verb and
        // the triple fields that the edit form posts; a field that is not set is left out, so that a
        // union with a posted url never masks a posted value with an empty one
        $test_name = 'the url array of a triple contains the linked phrases and the verb';
        $trp_url = test_triples::triple_add_ui()->to_url_array($msg_ui);
        $t->assert($test_name, $trp_url[url_var::PHRASE_FROM], word_names::TEST_ADD_ID);
        $t->assert($test_name . ' and the verb', $trp_url[url_var::VERB], verbs::PART_ID);
        $t->assert($test_name . ' and the to phrase', $trp_url[url_var::PHRASE_TO], word_names::TEST_ADD_TO_ID);

        // the expected weight is the same fractional one as in the edit form test above, which
        // test_triples::triple_impact sets to match the db row of units.json
        $test_name = 'the url array of a triple contains the weight, plural and impact';
        $trp_ui = $t_trp->triple_impact_ui();
        $trp_ui->plural = triple_names::MATH_CONST_PLURAL;
        $trp_url = $trp_ui->to_url_array($msg_ui);
        $t->assert($test_name, $trp_url[url_var::WEIGHT], 0.5);
        $t->assert($test_name . ' and the plural', $trp_url[url_var::PLURAL], triple_names::MATH_CONST_PLURAL);
        $t->assert($test_name . ' and the impact', $trp_url[url_var::IMPACT], impacts::MAX);

        // the usage and the impact are never null, so both are dropped by their value like in the
        // word url array, which leaves the id as the only field of a triple that is always sent
        $test_name = 'the url array of a new triple contains only the id';
        $trp_url = new triple_ui()->to_url_array($msg_ui);
        $t->assert($test_name, $trp_url[url_var::ID], 0);
        $t->assert_contains_not($test_name . ' and no unset field', array_keys($trp_url), [
            url_var::PHRASE_FROM, url_var::VERB, url_var::PHRASE_TO, url_var::WEIGHT,
            url_var::PLURAL, url_var::USAGE, url_var::IMPACT,
            url_var::NAME, url_var::DESCRIPTION, url_var::TYPE,
            url_var::SHARE, url_var::PROTECTION, url_var::OWNER]);

        $t->subheader($ts . 'url mapping of phrases posted by name');
        // the datalist edit fields submit the shown phrase name instead of the id, so the url
        // mapper must resolve the name (e.g. 'Zurich') via the request cache to the phrase
        $t_phr = new test_phrases($t);
        $dto_ui = new data_object_ui();
        $dto_ui->phr_lst = $t_phr->list_ui();
        $phr_name = $dto_ui->phr_lst->lst()[0]->name();
        $trp_ui = new triple_ui();
        $map_msg = new user_message_ui();
        $trp_ui->url_mapper([
            url_var::ID => 1,
            url_var::NAME => triple_names::SYSTEM_TEST_ADD,
            url_var::PHRASE_FROM => $phr_name,
            url_var::WEIGHT => ''
        ], $map_msg, $dto_ui);
        $test_name = 'a from phrase posted by name is resolved via the request cache';
        $t->assert($test_name, $trp_ui->get_from()?->name(), $phr_name);
        $test_name = 'an empty weight posted by the edit form is kept as not set';
        $t->assert_true($test_name, $trp_ui->weight === null);

        $test_name = 'an unknown phrase name is reported to the user';
        $trp_ui = new triple_ui();
        $map_msg = new user_message_ui();
        $trp_ui->url_mapper([
            url_var::ID => 1,
            url_var::NAME => triple_names::SYSTEM_TEST_ADD,
            url_var::PHRASE_FROM => test_const::PHRASE_NOT_EXISTING
        ], $map_msg, $dto_ui);
        $t->assert_true($test_name, $map_msg->has_msg_id(msg_id::PHRASE_NAME_NOT_FOUND));

        $test_name = 'a triple with only one linked phrase is rejected';
        $trp_ui = new triple_ui();
        $trp_ui->set_name(triple_names::SYSTEM_TEST_ADD);
        $val_msg = new user_message_ui();
        $trp_ui->set_from_by_id(1, $val_msg, $dto_ui);
        $t->assert_false($test_name, $trp_ui->input_valid($val_msg, url_var::CRUD_UPDATE));
        $test_name = '... and the user is told that both phrases are needed';
        $t->assert_true($test_name, $val_msg->has_msg_id(msg_id::TRIPLE_PHRASES_MISSING));

        // negative: an unknown phrase name posted as the from phrase is reported to the user
        $test_name = 'an unknown from phrase name is reported to the user';
        $trp_ui = new triple_ui();
        $from_msg = new user_message_ui();
        $trp_ui->set_from_by_id(test_const::PHRASE_NOT_EXISTING, $from_msg, $dto_ui);
        $t->assert_true($test_name, $from_msg->has_msg_id(msg_id::PHRASE_NAME_NOT_FOUND));

        $t->subheader($ts . 'frontend phrases_related round-trip');
        // build a target phrase ("Pi") that should appear in the triple's related list, and
        // wrap it in a one-entry json array. The frontend phrase_list api_mapper then turns
        // it into a phrase_list whose api_array round-trips back to the same json shape.
        $target_trp = $t_trp->triple_pi_name();
        $related_json = [[
            json_fields::OBJECT_CLASS => json_fields::CLASS_TRIPLE,
            json_fields::ID => $target_trp->id(),
            json_fields::NAME => $target_trp->name(),
        ]];
        $symbol_trp = $t_trp->triple_pi();
        $trp_json = json_decode($symbol_trp->api_json(), true);
        $trp_json[json_fields::PHRASES_RELATED] = $related_json;
        $trp_ui = new triple_ui(json_encode($trp_json));
        $test_name = 'triple ui api_mapper populates phrases_related from json';
        $t->assert_true($t->name . $test_name,
            $trp_ui->phr_lst !== null and !$trp_ui->phr_lst->is_empty());
        $test_name = 'triple ui api_array re-emits phrases_related';
        $t->assert_true($t->name . $test_name,
            array_key_exists(json_fields::PHRASES_RELATED, $trp_ui->api_array([], $msg_ui)));
        // negative: a triple without phrases_related in its json keeps the field null
        $bare_trp_ui = new triple_ui($symbol_trp->api_json());
        $test_name = 'triple ui phrases_related stays null when json key is absent';
        $t->assert_true($t->name . $test_name, $bare_trp_ui->phr_lst === null);
        $test_name = 'triple ui api_array omits phrases_related when null';
        $t->assert_true($t->name . $test_name,
            !array_key_exists(json_fields::PHRASES_RELATED, $bare_trp_ui->api_array([], $msg_ui)));

        $t->subheader($ts . 'import and export');
        $t->assert_ex_and_import($t_trp->triple(), $t->usr_system);
        $t->assert_ex_and_import($t_trp->triple_filled_add_name(), $t->usr_system);
        $json_file = 'unit/triple/pi.json';
        $t->assert_json_file(new triple($t->usr1), $json_file);

        // the name of the triple is exported
        $test_name = 'export includes the name';
        $trp = $t_trp->triple();
        $exp = $trp->export_json($msg, [], false);
        $t->assert($test_name, $exp[json_fields::NAME], triple_names::MATH_CONST);
        $msg->reset();

        // negative: a triple with only the links set exports no name
        $test_name = 'export without name';
        $trp = $t_trp->triple_link_only();
        $exp = $trp->export_json($msg, [], false);
        $t->assert_key_not_exists($test_name, json_fields::NAME, $exp);
        $msg->reset();

        // the description of the triple is exported
        $test_name = 'export includes the description';
        $trp = $t_trp->triple();
        $exp = $trp->export_json($msg, [], false);
        $t->assert($test_name, $exp[json_fields::DESCRIPTION], triple_names::MATH_CONST_COM);
        $msg->reset();

        // negative: a triple without description exports no description
        $test_name = 'export without description';
        $trp = $t_trp->triple_link_only();
        $exp = $trp->export_json($msg, [], false);
        $t->assert_key_not_exists($test_name, json_fields::DESCRIPTION, $exp);
        $msg->reset();

        // a not default phrase type is exported by its name
        $test_name = 'export includes a not default type';
        $trp = $t_trp->triple();
        $exp = $trp->export_json($msg, [], false);
        $t->assert($test_name, $exp[json_fields::TYPE_NAME], phrase_types::MATH_CONST_NAME);
        $msg->reset();

        // negative: the default phrase type is left out of the export
        $test_name = 'export leaves out the default type';
        $trp = $t_trp->triple_default_type();
        $exp = $trp->export_json($msg, [], false);
        $t->assert_key_not_exists($test_name, json_fields::TYPE_NAME, $exp);
        $msg->reset();

        // the linked phrases are exported by their names
        $test_name = 'export includes the from and to name';
        $trp = $t_trp->triple();
        $exp = $trp->export_json($msg, [], false);
        $t->assert($test_name, $exp[json_fields::EX_FROM], word_names::CONST_NAME);
        $t->assert($test_name, $exp[json_fields::EX_TO], word_names::MATH);
        $msg->reset();

        // negative: a triple without links exports no from and to
        $test_name = 'export without from and to';
        $trp = $t_trp->triple_api();
        $exp = $trp->export_json($msg, [], false);
        $t->assert_key_not_exists($test_name, json_fields::EX_FROM, $exp);
        $t->assert_key_not_exists($test_name, json_fields::EX_TO, $exp);
        $msg->reset();

        // the verb is exported by its name instead of the predicate code id
        $test_name = 'export includes the verb name';
        $trp = $t_trp->triple();
        $exp = $trp->export_json($msg, [], false);
        $t->assert($test_name, $exp[json_fields::EX_VERB], verbs::PART_NAME);
        $t->assert_key_not_exists($test_name, json_fields::PREDICATE, $exp);
        $msg->reset();

        // negative: a triple without verb exports no verb
        $test_name = 'export without verb';
        $trp = $t_trp->triple_api();
        $exp = $trp->export_json($msg, [], false);
        $t->assert_key_not_exists($test_name, json_fields::EX_VERB, $exp);
        $msg->reset();

        // the view is exported by its name
        $test_name = 'export includes the view name';
        $trp = $t_trp->triple_filled();
        $exp = $trp->export_json($msg, [], false);
        $t->assert($test_name, $exp[json_fields::VIEW], views::MATH_CONST_NAME);
        $msg->reset();

        // negative: a triple without view exports no view
        $test_name = 'export without view';
        $trp = $t_trp->triple();
        $exp = $trp->export_json($msg, [], false);
        $t->assert_key_not_exists($test_name, json_fields::VIEW, $exp);
        $msg->reset();

        // the usage is exported to validate the consistency
        $test_name = 'export includes the usage';
        $trp = $t_trp->triple_filled();
        $exp = $trp->export_json($msg, [], false);
        $t->assert($test_name, $exp[json_fields::USAGE], triple_names::SYSTEM_TEST_ADD_USAGE);
        $msg->reset();

        // negative: a triple without usage exports no usage
        $test_name = 'export without usage';
        $trp = $t_trp->triple();
        $exp = $trp->export_json($msg, [], false);
        $t->assert_key_not_exists($test_name, json_fields::USAGE, $exp);
        $msg->reset();

        // the impact is part of the export so that it round-trips
        $test_name = 'export includes the impact';
        $trp = $t_trp->triple_impact();
        $exp = $trp->export_json($msg, [], false);
        $t->assert($test_name, $exp[json_fields::IMPACT], impacts::MAX);
        $msg->reset();

        // negative: a triple without impact exports no impact
        $test_name = 'export without impact';
        $trp = $t_trp->triple();
        $exp = $trp->export_json($msg, [], false);
        $t->assert_key_not_exists($test_name, json_fields::IMPACT, $exp);
        $msg->reset();

        // the weight is part of the export so that it round-trips
        $test_name = 'export includes the weight';
        $trp = $t_trp->triple_impact();
        $exp = $trp->export_json($msg, [], false);
        $t->assert($test_name, $exp[json_fields::WEIGHT], test_const::TRIPLE_WEIGHT);
        $msg->reset();

        // negative: a triple without weight exports no weight
        $test_name = 'export without weight';
        $trp = $t_trp->triple();
        $exp = $trp->export_json($msg, [], false);
        $t->assert_key_not_exists($test_name, json_fields::WEIGHT, $exp);
        $msg->reset();

        // the impact field is part of the triple im- and export
        // even if the impact is expected to be calculated internal
        // it is included in the im- and export for an initial value
        // e.g. if the calculation definition is not yet set 
        $trp = $t_trp->triple();
        $trp->set_impact(impacts::HIGH);
        $json_ex = $trp->export_json($msg, [], false);
        // the assert follows the json export above, so a page timeout is used to avoid a false timeout
        $t->assert($ts . 'export includes the impact', $json_ex[json_fields::IMPACT] ?? null, impacts::HIGH, $t::TIMEOUT_LIMIT_PAGE);
        // re-import the exported json and check that the impact is read back
        $trp_in = new triple($t->usr_system);
        $trp_in->import_mapper($json_ex, new user_message($t->usr_system), new data_object($t->usr_system));
        $t->assert($ts . 'import reads the impact', $trp_in->impact, impacts::HIGH);


        $test_name = 'check if database would not be updated if only the name is given in import';
        $in_trp = $t_trp->triple_name_only();
        $db_trp = $t_trp->triple();
        $t->assert($t->name . 'needs_db_update ' . $test_name, $in_trp->needs_db_update($db_trp, $msg), false);

        $in_trp = $t_trp->triple_link_only();
        $db_trp = $t_trp->triple();
        $t->assert($t->name . 'needs_db_update ' . $test_name, $in_trp->needs_db_update($db_trp, $msg), false);

        // e.g. assigned_views.json adds the default view to the existing triple "mathematical constant"
        $test_name = 'an import that adds a default view to a triple updates the database';
        $in_trp = $t_trp->triple_name_only();
        $in_trp->set_view_id(views::MATH_CONST_ID);
        $db_trp = $t_trp->triple();
        $needs_upd = $in_trp->needs_db_update($db_trp, $msg);
        $t->assert_true($test_name, $needs_upd);
        $test_name = '... but not if the triple has this default view already';
        $db_trp->set_view_id(views::MATH_CONST_ID);
        $needs_upd = $in_trp->needs_db_update($db_trp, $msg);
        $t->assert_false($test_name, $needs_upd);

    }

    /**
     * similar to assert_load_sql of the test base but for the standard (generated) triple name
     * check the object load by name SQL statements for all allowed SQL database dialects
     *
     * @param sql_creator $sc does not need to be connected to a real database
     * @param triple $trp the user sandbox object e.g. a word
     */
    private function assert_sql_by_name_generated(sql_creator $sc, triple $trp, test_cleanup $t): void
    {
        // check the Postgres query syntax
        $sc->reset(sql_db::POSTGRES);
        $qp = $trp->load_sql_by_name_generated($sc, 'System test', $trp::class);
        $result = $t->assert_qp($qp, $sc->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $sc->reset(sql_db::MYSQL);
            $qp = $trp->load_sql_by_name_generated($sc, 'System test', $trp::class);
            $t->assert_qp($qp, $sc->db_type);
        }
    }

}
