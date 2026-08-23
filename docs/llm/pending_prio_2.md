# pending prio 2

## temp

add a field 'last_update' to source

add a 'source_status' table and add a status field to source

1. files::FULL_LOAD_FILES is dead — nothing imports it (src/main/php/cfg/const/files.php:299)

The only reference outside its own declaration is json_validation.php:99. all_unit_write_tests::import_base_data() (src/test/php/unit_write/all_unit_write_tests.php:286-293) still loops over BASE_DATA_FILES and BASE_DATA_PATH_FILES only, and import_test_data() over the    
three test_files::TEST_DATA_* arrays. So refs.json, the three start-page estimates, theses_complex_simple.json and zurich_htp_impact.json are still loaded by nothing — and worse, the new check now счит counts them as loaded, so the one check that would have caught it stays
silent. Needs a third loop over files::FULL_LOAD_FILES in import_base_data().

2. The new "file not named by an import const array" check reports ~50 false findings (docs/json_findings.md:218)

53 entries, of which two are real. The rest — words.json, triples.json, values.json, users.json, sources.json, verbs.json, views.json, components.json, formulas.json plus their _update/_undo siblings, the merge/rename/no_update/warning fixtures — are imported, by an       
individual test through its own const (e.g. import_write_tests.php:132 uses test_files::IMPORT_WORDS). The report text asserts they are "never imported and never tested", which is wrong for all of them. The check needs a third category (a const a test references directly,
findable by reflection over the test classes or as an explicit allow-list), otherwise the two real findings drown.

3. heat_and_climate_politics.json is still wrapped in a zukunft.com top-level key

It is now the only such file — ai_mediated_communication.json was unwrapped in this change (the 810-line diff is that re-indent, semantically the same data one level up). The wrapper is not a json field the importer reads, so the whole file imports as empty, and it was    
just added to TEST_DATA_FILES_NOT_REVIEWED. The sibling with the identical structure was left unfixed.

4. CBAM_setup.json and THOMY_test.json are now orphaned

Both were dropped from TEST_DATA_FILES_NOT_REVIEWED because they set a code_id, with a comment saying so. But the doc section added in the same change (docs/llm/testing.md:654) prescribes moving such data to files::SYSTEM_DATA_FILES — that step is missing. Their consts    
IMPORT_CBAM_SETUP / IMPORT_THOMY_TEST are now referenced nowhere, and both files are permanent findings in the report.

5. Five triple names in co2_waste_kezo.json repeat the auto-generated name — mine, from the unit-field conversion

tonne per year, tonne of CO2, tonne of CO2 per year, kilogram per person, kilogram per person per year each carry an explicit "name" equal to <from> <verb> <to>, which coding.md:160 forbids (the importer builds it).

6. The new IMPORT_SOURCE_NOT_A_NAME branch has no test

value_base.php:521 guards the object-valued source and reports a new msg_id; no fixture exercises it. src/test/resources/import/inconsistency_tests/ is the established home for exactly this (a value_source_not_a_name.json next to triple_has_a_word_name.json).


he two assigned lists in CBAM_costs.json name children, not the parent phrase

coding.md:169 says to assign a formula "to the most parent phrase it applies to (bid-ask spread absolut → currency, not each single currency)". I assigned expected value of a tool build to its five operand phrases and ABB total sales 2013 to the six sector triples. For ABB
in particular, sector is the parent of all six via the file's own is a sector triples, and country.json's equivalent sum assigns to the single parent (assigned_word: "country"). Both are arguably one level too specific.

2. Two format version findings appeared that were not there before

republik_test_init_de.json and republik_test_init_en.json now report no "version" field. This is not from my edits — neither file was touched — but the check only runs on a file with no other finding, so fixing other things in the same run surfaced them. They need a       
"version": "0.0.3" line.

3. src/test/resources/api/ui_config/ui_config.json differs only in its timestamp (10:37 → 13:15) — a run artifact, worth dropping from the commit.

4. Two things I flagged earlier as id shifts are actually harmless — correcting my own warning: word/list.csv contains no word from any TEST_DATA_FILES_NOT_REVIEWED file (no AI-mediated, SCC, GtCO2eq, …). So the new word acceptability in ai_mediated_communication.json and
   the expected / usable probability / expected value additions in CBAM_costs.json do not require re-baselining that file. The only baseline touch needed was the second plural, and you have already regenerated it.

5. Still open, deliberately (reported before, unchanged): CBAM_costs.json has no calc-validation, because the Gt→tonne scale is unmodelled and the file's reported EV (29.2 × 10⁹) does not reproduce from its own inputs (30.69 × 10⁹). The two intermediates do match exactly.


## data validation

`test/json_validation.php` checks every json of `src/main/resources/messages` and
`src/test/resources/import` (scanned with a directory iterator, so a new file is included) and
writes `docs/json_findings.md`. the checks live in `test/php/utils/json_validation.php` and are
shared with `coding_rule_tests`, which asserts the same rules for the import data only.
the report covers: valid json, the "measured value" qualifier, a verb that is not defined in
verbs.json, a field that no import mapper reads (every field read by a mapper is a `json_fields`
const, so a key that is not one of them is dropped without a message) and, as the last check of a
file without any other finding, the format version (raised to `def::PRG_VERSION` if the file is
behind) and the data version (initial `0.0.1` added if missing).
what is left:
- the wikidata / wikipedia files below `src/test/resources/import` are foreign formats (raw api
  dumps that feed the converters), so every envelope rule reports them; scope the scan by folder
  or by a marker instead of reporting them forever
- the other rules of `docs/llm/json_structure.md` - the triple naming rules, "a word and a triple
  must never share a name", the value qualifier rules - are still only prose and can be added as
  further check functions to the same class

## change log object names of the link classes

the formula link is done: change_log_list::name_lists() has the formula_links entry, formula_link_list can load by ids with both linked names joined and formula_link::row_mapper_sandbox reads them, so the all user overwrites column of the user page names the changed formula link

the view relation is done the same way (view_relation_list::load_sql_by_ids joins both view names, view_relation::row_mapper_sandbox reads them and name_lists has the view_relations entry)

the component link is done the same way (component_link_list::load_sql_by_ids joins the view and the component name, component_link::row_mapper_sandbox reads them, component_link::name() is new and name_lists has the component_links entry)

the term view is done too: term_view_list has a load_sql and load_sql_by_ids that join the view name and the term name (via the terms database view that unions the term classes), term_view::row_mapper_sandbox reads them, term_view::name() is new, change_tables::VIEW_TERM_LINK_USR is no longer commented out and is in USER_TABLES, and name_lists has the term_views entry

so all four link classes name the changed object in the all user overwrites column; a link class added later needs the same four parts: a list load_by_ids, the names of the linked objects in the list query, a row mapper that reads the joined names and a name() that combines both

sql_format inserts no space before END when the column name is long: the postgres fixtures view_relation_list_by_ids.sql (ul2.descriptionEND) and the component link probe (ul4.component_type_idEND) show it; the raw sql is correct, so this is a pretty printer bug that makes the committed fixtures hard to read

## value overwrites in the all user overwrites column

the value overwrites are now listed: a value change is never written to the changes table (see its table comment), so change_log_list::load_by_user reads the twelve change_values_* tables of change_value::CHANGE_CLASSES with one query each, merges them, sorts by time and cuts to the page limit

so a user page load runs thirteen change log queries instead of one; if that becomes too slow, the alternative is one union query per group id type (prime, norm, big), which needs the same column count in every union arm and is why value_list::load_by_ids splits by type today

a value is named by the phrases of its group, so change_log_list::load_row_names calls the new sandbox_list::load_names_related after load_by_ids, which value_list overrides with load_phrases; that is one extra query per listed value, so a list of many value changes is worth revisiting once value_list can load the phrases of all its groups with one query

## code cleanup
The cleanup deleted 14 snapshots and the blast radius is wider than the orphan case. delete_unused_files() removes every .html under views_by_object/ that this run did not write. That is correct for a full run, but a partial run — test_part.php, a single test class, or
a test group temporarily commented out — writes only a subset, so with AUTO_UPDATE_TEST_FILES = true it silently deletes the rest. The pre-existing views_by_id loop has the same property; I extended it to a second folder without adding a guard against a partial run. A     
cheap fix would be to skip the cleanup unless the expected number of assert_view calls was reached, or to run it only from the full-suite entry point.
Some deletions are worth confirming before committing. Certain orphans are clear (triple_28/30/50/53/57/99, formula_26, value_32770 — all superseded by re-baselined ids). Less clear: word/word_add.html and word_default_word_198/214/258/363/400. These correspond to ids that no current assert_view uses, but if the covering test was disabled rather than removed, the baseline is now gone and will have to be regenerated when it is re-enabled. word_default_word_197/213/397 survived, which suggests the 198/214/400 files are simply the pre-shift twins — worth one look.
The keep-list matches by basename, not by path. SNAPSHOT_KEEP = ['word_add.html'] protects the root views_by_object/word_add.html you named and any word/word_add.html in a subfolder. For a filename that appears in several folders this is ambiguous; a path-relative entry would be precise. It also means the already-deleted word/word_add.html would be protected if it ever returns.
delete_path_file() still has its pre-existing redundant nested condition — if (AUTO_UPDATE_TEST_FILES) { log_warning(...); if (AUTO_UPDATE_TEST_FILES) { unlink(...) } }. The inner check can never differ from the outer one. Not introduced here, but now one level deeper inside my guard.
The changeset spans six unrelated topics — the json validation checks, the json data fixes (GDP/Zurich/Share), the note→add_info import, the db_ready from/to fix, the id-const re-baseline, and the snapshot cleanup. The db_ready fix is the one with real production impact and would be easier to bisect on its own.
docs/todo.md and the AUTO_UPDATE_TEST_FILES = false reset are in the tree from your side, not from this work — the flag being back to false is correct for committing.

### the user_message of a request — what is left

the rule (docs/llm/coding.md, docs/llm/state-and-messages.md): outside tests the user_message is
created **once per request** by the http resp. api entry point, travels below as the `$msg`
parameter and carries the requesting user on `$msg->usr`; every creation below an entry point is an
exception that needs a comment behind it saying why. the coded check is
`coding_rule_tests::php_user_message_creation_tests`, which regenerates
`docs/code_user_message_exceptions.md` — that doc is the work list and must shrink, never grow.
the nullable parameters, the unexplained creations and the "messages that never reach the
caller" sections are **empty**: every deliberate drop now carries the literal `not reported`
marker in its comment (a display path, a permission-check fill, a deprecated function, ...), so
a new lost message fails the coded check. only the 19 parameter defaults are left.
the done passes are in the git history; only what is still open is listed here.

1. **apply the readiness ladder to the named lists as well** (the link list is done): the ladder is
   written up in `docs/llm/architecture.md` - list membership goes by the key, the write by
   `db_ready()`. `sandbox_list_named::add` still keys on the id, so a caller that adds objects
   before they are saved needs `add_by_key` or `add_by_name_direct` (as
   `phrase_list::import_map_names` does); the plain `add` keeps only the first of several id less
   objects. all 47 `add_by_key` call sites ignore its return and pass no message, so the shape can
   be tightened further.
   **careful with its "name already in the list" branch**: it must stay on the **parent**
   `add_obj`, because an entry with the same name but another db id is a different object that
   belongs in the list (e.g. a renamed word next to its original); routing it to the named
   `add_obj`, which keys on the name, drops such an entry - that emptied the frontend cache phrase
   list, so `phrase::is_or_can_be` found nothing, `list_sort` fell into its hardcoded
   `load_by_name` fallback and rendered empty phrase links plus a "Mandatory field name missing in
   API JSON" warning on the start page.
   **watch the next import run**: `triples_to_add_to_db` offers a linked from/to triple once per
   referencing triple, so a shared not-yet-saved parent hits the "name already in the list" branch
   of `add_by_name_direct`, which now reports `LIST_DOUBLE_ENTRY` into `$msg_chk` instead of
   dropping it - `save_with_cache` ends with `$msg->is_ok()`, so a normal graph shape may fail the
   triple import save. if the run confirms it, fix the **collector**, not the adder:
   `triples_to_add_to_db` should skip a name it has already collected (the dedup is its intent)
   instead of routing the intended skip through the reporting branch.
   the silent drops still open on the same defect:
   - the double that `add_by_key` drops in that branch is not reported (TODO on the line)
   - `sandbox_list::add_obj` ignores an object that has no key at all without a message (TODO on
     the line)
   - the `list_sort` fallback adds objects that it could not load, so an empty cache renders empty
     phrase links instead of nothing
   two loose ends of the formula import: `formula_map::$lnk_lst` has no accessor, so a unit test
   cannot assert the created links (only that nothing is reported), and `assign_phrases`, which
   fills `lnk_lst` from `$this->phr_lst`, is called by `import_obj` but not by the data_object
   import - decide which of the two members the import fills.
   `data_object::add_term_view` / `term_view_list()` have no caller: decide whether the dto keeps
   the imported term view links or whether the pair is dead code.
   `component::set_col_sub_phrase` does not check whether the given sub phrase has a relation to
   the column phrase, so it can suggest nothing; that check needs the phrase relations and a `$msg`
   parameter.
   **watch the write test**: `word_write_tests` had the pseudo text of an untranslated `add_id`
   message as its expected value for "triple cannot by renamed to an already used word name" - the
   real expectation is restored, so the run now shows whether the triple save reports the name
   clash at all.
   opportunistic only: the `fill_by_name` treatment (take `$msg`, return `bool`) is the right shape
   for the *small* leaf families if one is touched anyway (`fill_by_id`, `add_id_by_name`,
   `sandbox_list::add_user_check`, `id_used_msg`), while the recursive `diff_msg` and `fill` cores
   stay as they are - their callers do merge the return, so threading them is high risk, low value.

2. **remove the 19 remaining message parameter defaults**: a caller that passes nothing silently
   loses its messages, so the default is the same drop in disguise.
   the two remaining families are measured, so do not re-size them by definition count:
   - `add_by_key` (6 defaults: `ListOfIdObjects`, both `sandbox_list_named`, `component_link_list`,
     `formula_list`, `view_relation_list`) - 47 call sites, 29 of them in functions **without** a
     message in scope
   - `add_obj` incl. `sandbox_list_named::add` (5 defaults: `ListOf`, `ListOfIdObjects`,
     `sandbox_list`, both `sandbox_list_named`) - 70 call sites, 31 without a message in scope
   **never rename by function name**: every one of these names exists on more than one class
   (`view::add_component`, `verb_list::add_verb`, `group::add_word`, `test_db_load::add_word`), so
   the receiver has to be resolved per call site.
   what is left before the defaults can go: the callers of `add_by_key` / `add_obj` in
   `phrase_list`, `word_list`, `triple_list`, `term_list`, `verb_list`, `formula_list`, `import` and
   `result_list`, one class per commit with a test run.
   **the `add_by_key` flip is blocked on the double-report semantics** (measured 2026-08-14): the
   five declarations are one override family (`ListOfIdObjects` is the base stub), so php forces
   all signatures and all 47 call sites into one commit, and 38 of the call sites pass no message
   today. many of those are **intended dedups** (`merge_by_name`, `concat_unique`, the
   `import_mapper` / `import_obj` re-imports of the same name), and the same-key branch reports
   `LIST_DOUBLE_ENTRY`, which sets the message to **not ok** - so threading a live `$msg` there
   would fail imports on normal data, like the `triples_to_add_to_db` case above. before the flip,
   decide how an intended dedup is reported: e.g. `Message::add(..., ok: true)` for a
   dropped-double notice, so the record survives without failing the `is_ok()` gates; then the
   family can switch to `(to_add, Message $msg, bool $allow_duplicates = false)` in one pass.
   also found: `component_link_list::add_by_key`, `view_relation_list::add_by_key` and the web
   `sandbox_list_named::add_by_key` have **zero callers** - candidates for deletion instead of
   threading
   - the 8 `api_json` defaults, and that family is the trap — read this before retrying. its 53
     **production** call sites already pass their message, so the drop is fixed there; removing the
     defaults breaks **232 test call sites**, each needing the right message *kind* (a backend
     receiver takes the cfg `user_message`, a frontend one the web `user_message`), and the receiver
     cannot be told apart statically with enough confidence — a receiver-name heuristic mis-reads
     `new formula_list_ui($t_msk->view_list_word()->api_json())` as a ui receiver. so it needs the
     test run in the loop, one test folder per commit, or it becomes trivial once the
     frontend/backend split makes the message kind unambiguous.
   **19 is a lower bound**: the coded check matches the class name, so a parameter typed with an
   alias is invisible to it, e.g. `frontend::set_type_cache(?string $api_msg = null,
   user_message_ui $msg_ui = new user_message_ui())` - `user_message_ui` does not match the
   `user_message` pattern. before this item is called done, `DEFAULT_PATTERN` (and `NULL_PATTERN`)
   need the frontend alias names too, and the count has to be measured again.

3. **remove the remaining `?user_message $msg = null` parameters** — the rule is in coding.md and
   `docs/llm/state-and-messages.md` ("$msg is never null"), and
   `code_user_message_exceptions` now lists every hit in its own report section, so this is
   measured the same way the defaults were. a nullable message is the same drop as a default,
   only written out honestly: `$msg?->add(...)` reports nothing at exactly the call sites that
   pass none. the parameter becomes **required**, and a caller without a message gets one itself,
   which is why each family is a cascade and not a rename.
   three lessons from the done families, they repeat in every remaining one:
   - php deprecates a required parameter after an optional one, so `$msg` moves **in front of** the
     optional parameters, which changes the argument order at every call site.
   - a message parameter with no caller that can use it is **not** a reason to delete the
     parameter and fall back to a plain `log_err` string - that trades a translatable msg_id for a
     hardcoded english one and moves the campaign backwards. thread it, or stop the cascade with a
     named local buffer, but keep the msg_id.
   - never insert an argument by **function name**: `del` has 21 declarations, so a name-based pass
     turned 50 correct `->del($msg)` calls into `->del($msg, $msg)` across 49 files. only resolve
     the receiver class (from the file's `use` imports, the enclosing class for `$this->`, or a
     `new X()` assignment) and look up **that** class's signature.

   **the section is empty** - the last one, the backend `sandbox_list_named::add_by_name_direct`,
   is done: `$msg` is required and second (before `$allow_duplicates`), the internal `$add_msg`
   buffer is gone, and the `missing_ids` family (all four declarations, for the override
   compatibility), `triples_to_add_to_db` and `triple_list::phrase_lst_of_names` thread the
   message from the save-block buffers (`$msg_chk` resp. `$msg`); only the cache getter
   `data_object::phrase_list()` stops the cascade with a read-and-log buffer, because a cache
   refresh has no caller message (the frontend hooks have the same shape).
   the three web `__construct(?string $api_json = null, ?user_message $msg = null)` are done: the
   decision was the plain constructor plus the existing `set_from_json($json, $msg)`, because
   building an **empty** list needs no message at all and a required parameter would force every
   `new verb_list()` to fill it with something; the 11 callers that pass an api json now hand over
   their message.
   **the same shape one level up is still open**: `web/sandbox/ListBase::__construct(?string
   $api_json = null)` calls `set_from_json($api_json)` and drops the `user_message` it returns, so
   every `*_ui` list built from an api json (`word_list`, `phrase_list`, `value_list`, ...) loses
   its mapping problems. the coded check does not see it, because no `new user_message(` appears
   there - it is the return value that is thrown away. same treatment: an empty constructor plus an
   explicit `set_from_json($json, $msg)` at the callers that have json; count the call sites first,
   it is the biggest family of the three.

4. **the deferred items of the log/buffer audit** — each needs a test run or a decision, not a quick
   edit:
   - `sandbox_named.php` ~215 and its twin `type_object.php` ~211 guard the missing-name log with
     "no NAME and no NAME_GIVEN and no ID", so the false positives are gone; the remaining step is
     to SURFACE a genuine no-name-at-all submit to `$msg` (`add(MANDATORY_FIELD_NAME_MISSING)` in
     the guarded branch) — deferred until a test run confirms no test posts a nameless url expecting
     success
   - `web/element/element_group.php` ~159 `dsp_values` returns HTML, so surfacing its dropped buffer
     risks a snapshot break (two such surfacings were already reverted: the import.php
     "not yet implemented" notices and the `sandbox_named` url_mapper name check); defer until it
     can be run against the tests
   - `cfg/value/value_base.php` ~2108 `save_field_trigger_update` is dead code (no callers), so its
     dropped job-save buffer has no runtime effect — surface it only when the function is wired up
   - deliberately NOT surfaced (they belong in the admin log, not the user message):
     `cfg/group/group.php` ~1156 "save of group description not yet implemented" and the import
     "not yet implemented" sections — both are dev limitations of the same category

5. **the `$msg->is_ok()` gate defect**, found by the import regression (the trap itself is written
   up in state-and-messages.md): 7 places gate a loop or a branch on `db_ready($msg)` /
   `can_be_ready($msg)` with a **passed in** message — `triple` ~513/541, `component_list` ~458,
   `list_db_write` ~187/217, `formula_map` ~1406, `sandbox_list_named` ~1351
   (`ref_list`, `add_by_key`, `formula_list::get_ready` and `formula::generate_ref_text` now judge
   with a local `$rdy_msg` resp. `$conv_msg` and are the pattern to copy; the formula pair was
   fixed because the country.json import chained formulas across levels and the shared level
   buffer blocked every formula after the first not-yet-ready one).
   the trap also sits inside the expression parser: `term_from_symbol`, `phrase_from_symbol` and
   `element_from_symbol` (cfg/formula/expression.php ~494/539/614) skip the symbol resolution
   once the **passed in** message is not ok, which is why the `formula_map` term loaders give the
   speculative pre-load conversion of the factory an own buffer - a report from that expected
   failure would switch off the very load that resolves it (found by the time_definition.json
   import: "increase" needs the predefined "this" and "prior" of the same file).
   those functions end with `return $msg->is_ok()`, so once one
   object fails every later one is skipped silently. they work today only because their callers
   happen to pass a message that is still ok. the fix makes more objects pass the gate, so it needs
   its own commit with a test run.

6. **migrate the bare `log_err` calls to `log_err_msg`** (the rule is coding.md and
   docs/llm/structure.md "log_err alone is the transitional channel"): today 514 bare `log_err`
   against 63 `log_err_msg`, and 203 `log_warning` against 2 `log_warning_msg` — so an internal
   error is still logged for the admin while the user sees a page that silently did nothing.
   this is explicitly **not** a sweep: it happens whenever a function that contains a `log_err` is
   touched for another reason, by adding the `user_message $msg` parameter and switching that call.
   the two documented carve-outs stay bare: no caller can hold a `$msg` (entry point, bootstrap,
   cron, HTML-only display), or the message fires on a normal path.
   **the cheap subset is ~216 of those 514**: they already sit in a function that HAS a `$msg`
   parameter, so they need no signature change and no caller touched - only `log_err($txt)` ->
   `log_err_msg($txt, $msg)`. that subset is also the natural coded check for
   unit/coding_rule_tests.php (a bare `log_err` inside a function with a `user_message` parameter),
   and it can be turned on the moment the subset is empty.

optional caller-side follow-ups found while evaluating the setter families (no signature change, so
each is a small standalone commit):
- `fill` keeps returning a `user_message`, but the few DISCARDING callers that already hold a
  threaded `$msg` should merge its return — `cfg/sandbox/sandbox.php:3029` and
  `cfg/helper/object_mapper.php:137/191`; leave the recursive / row-mapper / test callers as-is
- `sql_db` setup ~1249 still calls `import_system_data($usr)` and `import_sample_view_data($usr)`
  without the `$sys_msg` it holds — passing it would surface install-time failures
- give `triple` its own `set_code_id_db` so its `row_mapper` (and `test_triples`) can drop the
  buffer, matching word/source/component; likewise a `set_type_id_db` no-check variant would let the
  test factories and the row-sync/fill paths drop their throwaway messages
- pre-existing bug found in `job::set_status_id`: the allowed branch sets `$this->type_id` instead
  of `$this->status_id` (left as-is so far)
- move the save bookkeeping off the user message: `db_row_id_lst`, `checksum` and
  `added_depending` belong to `sql_message` (cfg/db/sql_message.php already has the same fields),
  not to the message the user reads. today `$msg_ui->merge($msg)` drops exactly these plus the
  `msg_id_lst` at the frontend boundary (state-and-messages.md "Crossing the boundary is a
  merge()"), which is accepted until they have moved - so do not repair the frontend `merge()` by
  copying the db ids over
- `web/user/user.php::dsp_errors` has no caller and would fatal if it got one: `$err_lst` is the
  **frontend** `sys_log_list`, which has `load_by_user()` but no `load()`; the page it once served
  is the user view rework further down in this file, so delete it there instead of repairing it
- in `test_lib::ui_test_cache` the `$base_msg` of the base view import is also handed to
  `list_all_ui`, so its name no longer says what it collects - split it when that function is
  touched again; the two test runners also name the same web class differently
  (`all_unit_tests` must alias it as `user_message_ui`, `all_ui_tests` uses `user_message`)

## general code cleanup to prevent future issues

find all '&back=' url parameters and list here the prompts to fix these issues by using instead the url_var::BACK prefix

fix the user type and status export/import round trip: the export writes the type display name under json_fields::TYPE ('type_id', see user::export_json using type_name()) but import_mapper reads json_fields::TYPE_NAME ('type'), so an exported type is silently ignored on import and the guest default fills it (the unit fixture user_import.json only passes because its value "Guest" equals the default); additionally set_type expects the code_id ('guest') while the export writes the name ("Guest"), so even with matching keys the value would not resolve (user_type_list has usr_can_add = false); decide whether the export switches to the code id under the 'type' key (json format change -> minor version raise and db_check upgrade script per docs/llm/versions.md) or the import accepts both; the status has the same name-vs-code-id issue (status_name() exported, usr_sta->id() on import)

### complete the frontend backend split — implementation steps

check if all fields and functionality from the old 'dsp_edit' and 'dsp_del' functions is transfered to the new system_views.json and view.php based views and create prompst to add missing fields or functions and remove the old e.g. 'src/main/php/web/component/component.php::dsp_edit' function

make the ui formatting function unit testable so that they don't reload data from the backend an instead use a preloaded data cache

the goal (docs/llm/frontend.md): frontend (web/ and http/) and backend (cfg/ and api/) are two
independent apps talking only over the api; code needed by both lives in shared/. the temporary
bridges are the backend paths include at the top of web/const/paths.php and the html_paths::MODEL_*
/ DB / API_OBJECT copies in the same file — they fall step by step with the prompts below. done so
far: env.php moved from cfg/const to shared/const (read by both apps); api/word/index.php reads the
request body via the backend controller::request_json instead of the frontend rest_call class. run
the steps one at a time, each as its own commit with tests written first:

1. move the pure text logging classes cfg/log_text/text_log.php, text_log_format.php and
   text_log_level.php to shared/log_text (check first that they have no db or model dependency);
   web/log_text/text_log.php then extends the shared class; the model-dependent
   text_log_functions.php (log_err writes to sys_log in the db) stays in the backend for now

2. give the frontend its own error reporting: the web/ code calls log_err / log_warning / log_debug
   from cfg/log_text/text_log_functions.php, which is backend code (db write to sys_log); create
   frontend log functions that report problems via the existing sys_log api endpoint (rest_call)
   and echo to standard io, then remove the text_log_functions include from web/init_ui.php

3. replace the backend $sys usage in web/ with a frontend-owned equivalent on $ui_sys: the timing
   (web/helper/config.php load($sys) and the api call timing TODO in web/html/rest_call.php) and
   the preloaded type list access (web/system/sys_log.php $sys->typ_lst->sys_log_sta) move to
   $ui_sys; then web/init_ui.php no longer creates the cfg system_object and $sys becomes a
   backend-only global in docs/code_object_name_exceptions.md

4. after steps 1-3 remove the remaining cfg includes from web/init_ui.php, remove the backend paths
   include from the top of web/const/paths.php and drop every html_paths::MODEL_* copy that only
   web/init_ui.php used; add the coded check to unit/coding_rule_tests.php that web/init_ui.php and
   web/const/paths.php include no cfg file

5. replace the deprecated direct-db bootstrap of web/frontend.php (start / open_db / end /
   load_cache) with api calls per the TODO in coding_rule_tests::php_web_only_allowed_globals_tests;
   then remove the 'frontend.php' exception from that rule, remove the db and model includes from
   frontend.php and drop the html_paths::DB and now unused html_paths::MODEL_* copies

6. move the reserved test name consts that production web code includes (test_paths::CONST
   formula_names.php / triple_names.php / word_names.php in web/frontend.php,
   web/formula/formula_list.php and web/component/execute/system_form.php - included "to avoid that
   names used for testing are used in production") to shared/const so production code never
   includes test code; then remove the test path block from http/const.php (its TODO Prio 2) and
   the test_paths use from the web files

7. migrate the remaining direct-db web files to api calls or delete the deprecated functions:
   web/log/user_log_display.php and web/user/user_display_old.php create their own sql_db, and
   web/word/word.php still builds sql; afterwards no web file references sql_db and the
   html_paths::DB copy falls if not already removed in step 5

8. extend coding_rule_tests::php_cfg_no_web_tests to also scan paths::API, paths::API_OBJECT and
   the /api scripts, so a backend file using a web class (like the api/word/index.php rest_call
   use fixed above) is caught by the unit tests

## to be sorted

fix the last violation of the default-value rule (docs/llm/constants.md "Default values are resolved at the point of use, never fabricated in a mapper"): user::api_mapper still fabricates user_profiles::NORMAL_ID for a missing PROFILE_ID (cfg/user/user.php ~515) and the profile branch in db_fields_changed (~3653) has no null guard, so a json-born user without the profile field saved by an admin requester silently demotes the stored profile to normal (enforce_profile_privilege only blocks unprivileged requesters, an admin passes can_set_profile(normal); the frontend api_array omits PROFILE_ID unless is_profile_valid()). Apply the same treatment as for the type and status: map a missing profile to null in api_mapper, add the !== null guard in db_fields_changed, let enforce_profile_privilege treat null as "keep stored" (its int $req_profile_id parameter needs ?int or an early guard), and add the matching negative and positive tests to the "preserved on save" blocks in src/test/php/unit/user_tests.php; also backfill profile_id in the user exception block of horizontal_ui_tests.php like type_id and status_id, because the add url does not transport the profile and the round trip only passes today because the fabricated normal profile matches the filled test user

cosmetic alignment with the default-value rule (no data-loss risk, round trip is safe): web/user/user.php api_mapper defaults profile_id, type_id and status_id to 0 instead of null (api_array drops them via the >0 and is_profile_valid() checks) and web/component/component.php api_mapper sets pos_type_id to position_types::DEFAULT_ID (the property is declared non-nullable with that default and api_array suppresses the default), so the frontend mirrors cannot express "not specified"; align them to nullable properties mapping to null when the field is missing

fix as many TODO Prio 0 as possible

if the cache type (with or without phrases / context) or the message type (with or without header) changes, clear the complete cache to make sure that the messages from cache are always correct but on the other hand keep the cache read and write as simple as possible.

Add to admin yaml a list of term that should be added on start to the document route. Add a "en" folder with static pages that store each request to a file that is read, executed and cleaned by the sceduled job runner. For non en languages use e.g de subfolder

### change log table deterministic order (carry the change id through the api)

the change log table rows still swap between runs: two changes that fall in the same wall-clock second are ordered only by change_log_list::sort_by_time_and_what, whose tie-break is the what text (alphabetical), not the insertion order. e.g. the initial `added "System Test Word"` and the later `added description "..."` flip depending on whether they land in the same second - alphabetical puts the name-add above the description-add even though the description was written later. the whole-second bucketing added for test mode makes this worse because it deliberately collapses the sub-second difference, so the what-text tie-break decides every same-second group. the root cause is that the change entries carry no unique key: change_log::api_json_array (src/main/php/cfg/log/change_log.php ~683) sends action_id, table_id, field_id, row_id and change_time but not the entry's own change_id (change_log::FLD_ID), so the frontend change_log_named entries all have id 0 (see the "api change entries carry no own id" note in change_log_list::head, which is why add() is called with allow-duplicates).

fix: add the change id (change_log::FLD_ID) to change_log::api_json_array, map it into the frontend change_log / change_log_named via api_mapper (into $this->id), and change change_log_list::sort_by_time_and_what to order by the change id descending as the primary key (the change id is monotonic with the db insert, so it is the true insertion order and never collides), keeping change_time only for the display; the test-mode whole-second bucketing and the what-text tie-break then become unnecessary and can be removed, and change_log_list::head can drop the allow-duplicates flag once the entries have real ids so the id-dedup of add() works normally. write the test first: extend the change_word workflow write snapshot so the `added "System Test Word"` and `added description` rows keep the insertion order regardless of the wall-clock second. note the docs/llm/frontend.md deterministic-order rule and the still-open change_log_list TODO Prio 1 about the write order.

### change log table paging (implement the prepared forward/back buttons)

the change log table pure already renders a forward button (icons::PAGE_FORWARD) when the row limit is reached and has a prepared back button (icons::PAGE_BACK) in change_log_list::tr_page_nav; the buttons are only icons so far and do not navigate yet. implement the actual paging with the prompts below in order.

add a change log page url parameter (a new url_var const, '9'-prefix rules do not apply as this is not a back param) that carries the zero-based page number of the change log table pure; read it in http/view.php resp. the word/triple page controller and pass it down as an explicit parameter (never a superglobal) to ui_log::change_log_table_pure.

thread the page number from ui_log::change_log_table_pure into change_log_list::tbl_when_who_what as a new $page parameter (default 0) next to $max_rows; replace the hard coded $first_page = true with $first_page = ($page == 0) and compute $more_rows from the page window (the list has more changes than $page * $max_rows + $max_rows), so the back button shows from page 1 on and the forward button hides on the last page.

show only the rows of the current page: instead of head($max_rows) use an offset+limit slice of the sorted list (add change_log_list::page(int $page, int $max_rows) returning the $max_rows changes starting at $page * $max_rows, mirroring head()); keep the newest-first sort from ui_log::prepared_change_log. note that prepared_change_log currently head()s the list to the 'word changes' row limit (20) before the pure table is rendered, which would cap paging at those 20 rows - remove that head() for the pure table (or raise it) so the paging can reach all changes of the object.

turn the prepared icons in change_log_list::tr_page_nav into real links: the forward button links to the same page with the change log page url param increased by one, the back button decreased by one, built via html_base::url_new / ref like ref::refresh_job_link; add a msg_id + en/de translation for the 'next page' and 'previous page' tooltip and pass it as the ref title.

add a unit workflow snapshot test for the change log paging under unit_ui: render the change log table pure for page 0 (forward only), page 1 (back and forward) and the last page (back only) from a change log longer than two pages, and assert the correct buttons and the correct row window per page; extend src/test/resources/web/html/object_pages/sys_log.html accordingly.

### reduce response time

if no prepared cached page is found, repeat the previous page with a 'processing' message and 'processing since 1 second', 2, 3 ... up to the timeout limit

include in the install.sh script the creation of a crontab job

create a job that checks for some users (the number of users to check should be defined in the config.yaml) if the 'uses_sandbox' is still valid and if not switch off the flag and set the 'last_update' time so that always the least updated users are checked with the next job run

**4. Backend job: sandbox page generation**
> Implement a backend job (queue-based) that regenerates the sandboxed HTML for a given user+view+object. On completion, write the result to the cache store keyed appropriately for that user's sandbox context, and mark it available for the frontend to fetch on next poll. Include a check to avoid enqueuing a duplicate job if one is already in flight for the same key.

**5. Reactive cache invalidation**
> Implement reactive invalidation: when a request finds the cached HTML for a view+object is stale (define staleness check — e.g. compare object's last-modified timestamp to cache timestamp), trigger the backend regeneration job instead of serving straight from cache, following the same stale-serve-with-refresh-flag pattern as sandbox users.

**6. Proactive invalidation via object dependency index**
> Implement a reverse index mapping object_id → list of dependent view cache keys. When an object is updated, look up dependents and enqueue regeneration jobs for each. Add a configurable limit on the number of dependent views invalidated per object update in a single pass, to prevent fan-out overload for widely-referenced objects (e.g. batch/throttle beyond the limit).

**7. Frontend polling with backoff**
> Implement frontend polling for pages served with the "refresh coming" flag: poll for the updated page with increasing interval (define starting interval and growth factor), up to a configured maximum number of attempts/time limit. On limit reached, fall back to [describe your auto-user/IP-whitelist fallback mechanism here — needs your existing spec, since I don't have those details].

**8. Request logging**
> Add a lightweight insert into the request log table for each page request, capturing at minimum: user_id, view_id, object_id, timestamp, and whether served from cache or freshly generated/regenerated.

Fill the gap: Compare with the actual spec for your existing "auto user and IP whitelist fallback" mechanism, so prompt 7's fallback description is a placeholder — you'll want to fill that in from your existing plan before comparing.

add to the config.yaml the size and age limit for the cache tables and use it to clean up the cache if needed

### prepare denial-of-service protection

use the cache for read only pages

limit the number of read requests per time unit by the webbrowser and auto switch to ip whitelist mode

count the number of db write requests by user and ip

add to config.yaml the max age for each db_cache_type

after each write to db_cache check if there are row older than defined in the setting and delete them using a prepared query

### denial of service test

The goal of this block is an end-to-end test that a flood of change requests first blacklists the abusing user and, if the abuse continues from more than one user, automatically raises the pod to user whitelist mode. The current protection is only the manual, file based whitelist in `src/main/php/web/server_guard.php` toggled from `http/server_admin.php` (state in `server_admin/state.json`); the automatic per-user rate limit, the database blacklist and the auto-switch to whitelist mode still have to be wired up. Build the prompts below in order: the first ones add the enforcement and the make-it-testable config knobs, the later ones are the actual DoS test that lowers those knobs, simulates the flood and asserts the reaction.

wire the existing `max change` config (config.yaml at `zukunft.com > system configuration > user > default > backend > max change > daily > ip user`, currently 1000 and only defined) to real enforcement: count the change requests a user has made in the configured period and reject a change that would exceed the limit. Add a second daily limit `logged in user` next to `ip user` so a registered user has its own threshold, and read both through the normal config accessor (the same path used for other `system configuration` values) instead of a hard coded number. Confirm no superglobals are read inside the enforcement method - the requesting user must be passed in as a parameter.

when a user exceeds its `max change` daily limit add that user to the database blacklist (the fallback that `server_guard.php` and `http/server_admin.php` already refer to as "the database based blacklist") and from then on reject every further change request from that user with a clear user_message that tells the user why the change was refused and how to contact the admin. Keep the blacklist in the database, not in the file based `user_whitelist.txt`, so the manual whitelist and the automatic blacklist stay independent.

add a new system configuration knob `user whitelist auto switch` = the number of distinct blacklisted users within the detection period that automatically activates the user whitelist mode (the same `user_whitelist_active` flag in `server_admin/state.json` that the admin page toggles). Default it to a high, production-safe value. When the number of freshly blacklisted users reaches this threshold, set `user_whitelist_active` to true exactly as the admin page would, and log a warning so the admins see why the pod switched to whitelist mode.

write the denial of service test itself (a dedicated test, e.g. under `src/test/php/` following the existing test structure, runnable via `php test_unit.php`, admin via the IP_ADMIN fallback on the CLI). As the first step the test must lower the two knobs to test values and remember the previous values so it can restore them at the end: set the `max change` daily limit for `ip user` and for `logged in user` to a very low number (e.g. 2 changes per period) and set `user whitelist auto switch` to 2 users. Assert that reading the knobs back returns the lowered values before continuing.

in the same test simulate one user that sends more change requests than the lowered `max change` limit in a short period. Assert that after crossing the limit the user is added to the database blacklist, and assert that the next change request from that already-blacklisted user is rejected (the change is not stored and the returned user_message says the request was refused). The user whitelist mode must still be inactive at this point because only one user has been blacklisted (threshold is 2).

extend the test with a second user that also sends too many change requests in a short period. Assert that once the second distinct user is blacklisted the `user whitelist auto switch` threshold of 2 is reached and `user_whitelist_active` in `server_admin/state.json` flips to true automatically. Then assert that an ip user (anonymous, not logged in) change request is now rejected by `server_guard.php` with the `optional/user_reject.html` reject page, and assert that the server admin page (`http/server_admin.php`) reports the user whitelist as active - check the rendered state that the page reads via `read_state()` / shows as "User whitelist: active".

as the final step of the test reset everything to the pre-test state: restore the `max change` daily limits and the `user whitelist auto switch` knob to their remembered default values, clear the two test users from the database blacklist, and switch the user whitelist mode off again through the same code path the server admin page uses to deactivate it (the `toggle user whitelist` POST action in `http/server_admin.php`), leaving `user_whitelist_active` false in `server_admin/state.json`. Assert that after the reset a normal change request from a fresh user succeeds again, so the test is self-cleaning and leaves no active whitelist or blacklist behind.

### distributed denial of service test

This block is the IP based sibling of the denial of service test above: instead of one logged in user flooding change requests, many different IP addresses each send too many requests in a short period, and the pod must blacklist each abusing IP and, once more than one IP is abusing, automatically raise the pod to IP whitelist mode. The existing pieces are the IP branch of `src/main/php/web/server_guard.php` (`ip_whitelist_active`, `server_admin/ip_whitelist.txt`, `ip_allowed()` with CIDR matching, `optional/ip_reject.html`), the IP toggle in `http/server_admin.php`, and the initial `ip_blacklist.json` (see `src/main/php/cfg/const/files.php::IP_BLACKLIST_FILE` and the `ip_ranges` test constants). The automatic per-IP request rate limit and the auto-switch to IP whitelist mode still have to be wired up. Build the prompts below in order, same as for the single-user test: enforcement and testable config knobs first, then the actual distributed test.

add a per-IP request rate limit: count the requests coming from one client IP (`$_SERVER['REMOTE_ADDR']`, passed in as a parameter, never read as a superglobal inside the enforcement method) within a configured period and reject requests from an IP that exceeds the limit. Add the knob to config.yaml next to the existing `max change` values, e.g. `zukunft.com > system configuration > user > default > backend > max requests > per minute > ip` for the raw request flood limit (distinct from `max change` which counts stored changes), and read it through the normal config accessor. Note that a DDoS is about request volume, not only stored changes, so this limit must also cover anonymous read/GET requests that never reach the change logic.

when an IP exceeds its request rate limit add that IP (or its /32 resp. /128 range) to the database IP blacklist that `server_guard.php` and `http/server_admin.php` already refer to as the fallback, reusing the same blacklist storage that `ip_blacklist.json` seeds. From then on reject every further request from that IP early in `server_guard.php` with `optional/ip_reject.html`, the same page an active IP whitelist uses for a non-listed IP. Keep the automatic IP blacklist independent of the file based `ip_whitelist.txt` so the manual whitelist and the automatic blacklist do not overwrite each other.

add a system configuration knob `ip whitelist auto switch` = the number of distinct blacklisted IPs within the detection period that automatically activates the IP whitelist mode (the `ip_whitelist_active` flag in `server_admin/state.json` that the admin page toggles). Default it to a high, production-safe value. When the number of freshly blacklisted IPs reaches the threshold, set `ip_whitelist_active` to true exactly as the admin page would, and log a warning so the admins see why the pod switched to IP whitelist mode. Note that activating an empty IP whitelist locks everyone out (the warning already emitted by `server_guard::warn_if_empty_ip_whitelist`), so on the auto-switch the current admin / server IPs must be seeded into `ip_whitelist.txt` first.

write the distributed denial of service test itself (a dedicated test under `src/test/php/`, runnable via `php test_unit.php`, admin via the IP_ADMIN fallback on the CLI). As the first step lower the two knobs to test values and remember the previous values for restore at the end: set the per-IP `max requests` limit to a very low number (e.g. 2 requests per period) and set `ip whitelist auto switch` to 2 IPs. Assert that reading the knobs back returns the lowered values before continuing. Since the CLI has no real remote address, drive the requests through a helper that lets the test set the client IP per request (the same value `server_guard.php` reads from `REMOTE_ADDR`), so the test can simulate distinct source IPs.

in the same test simulate one IP address that sends more requests than the lowered per-IP limit in a short period. Assert that after crossing the limit that IP is added to the database IP blacklist, and assert that the next request from that already-blacklisted IP is rejected by `server_guard.php` with the `optional/ip_reject.html` page (403). The IP whitelist mode must still be inactive at this point because only one IP has been blacklisted (threshold is 2).

extend the test with a second, different IP address that also sends too many requests in a short period. Assert that once the second distinct IP is blacklisted the `ip whitelist auto switch` threshold of 2 is reached and `ip_whitelist_active` in `server_admin/state.json` flips to true automatically. Then assert that a request from a further, non-whitelisted IP is now rejected with `optional/ip_reject.html`, that a request from an allowed IP in `ip_whitelist.txt` still passes, and that the server admin page (`http/server_admin.php`) reports the IP whitelist as active - check the rendered state it reads via `read_state()` and shows as "IP whitelist: active".

as the final step reset everything to the pre-test state: restore the per-IP `max requests` limit and the `ip whitelist auto switch` knob to their remembered default values, clear the test IPs from the database IP blacklist, and switch the IP whitelist mode off again through the same code path the server admin page uses to deactivate it (the `toggle ip whitelist` POST action in `http/server_admin.php`; note that only a full-access admin may switch the IP whitelist off, restricted admins may not), leaving `ip_whitelist_active` false in `server_admin/state.json`. Assert that after the reset a normal request from a fresh IP succeeds again, so the test is self-cleaning and leaves no active whitelist or blacklist behind.

## fine-tuning for next launch

add to the .env (and sample) parameter for the api to allow the cache (or deny) so that e.g. the api for the config just reads the env file checks the user / token and than returns the message from cache one-to-one. Review the debug call so that &debug=9 basically shows only these main steps

moved to [pending_next_launch.md](pending_next_launch.md) to keep this file small; see also [pending_fermi_live.md](pending_prio_2.md)

### security with low prio

still open (deliberately skipped as too risky / too large to change safely without a test run):
Content-Security-Policy is not set - needs an audit of the frontend inline styles first (the other
headers are in place). [the api-write anti-csrf gap and the in_array method detection are now FIXED -
see "security before go live" 2026-07-21; the api write path is enabled with origin + X-CSRF-Token +
checked code_id setters, but was previously dead code so it needs an end-to-end write test before
production.] the bcrypt DUMMY_PW_HASH is pinned at cost 12 while real hashes use the runtime default
cost - equal on
php 8.4 (default 12) but diverges on any php where the default is 10, re-opening the timing oracle the
dummy prevents; derive the dummy from the same cost (skipped: touches auth timing, wants a careful
test). save_user() has no general can_change() gate (safe today because no write caller passes an
attacker-influenced target, latent defense-in-depth; skipped: a guard risks breaking a normal user
saving their own profile without a test run). api/job (api/job/index.php) and api/changeLogList are
gated only by `id > 0` and leak another user's job / change-history metadata; api/job wants an owner
filter (skipped: job ownership model unclear), api/changeLogList is largely intended for the public
graph (needs an explicit access decision). login and the password-reset email have no throttle -
covered by the planned rate limiter, ensure it also bounds the reset endpoint. informational
(injection review): sql_par_field_list::par_sql() (line 785) builds inline unescaped sql but only into
`$qp->call`, the documented never-executed sample string - add a guard/comment so it is never routed
into exe(); finish deprecating sql_db::sf() in favour of bound parameters.

### Prio 2

allow at least admin users to overwrite the impact and usage via GUI

add to /docs/llm/* that instead of "is instance of" a const array should be used for a more specific selections

### fine-tuning for the next launch — backlog moved out of pending.md to keep the high-prio list small

before the program or database upgrade script are started the actual program version should be check and the execution should be rejected if there is no mathing script or no new version

before the database upgrade is executed the program should always be updated first, because the upgrade script is part of the program

if ip and user whitelist configuration are switched off and the server is back to the normal stat copy the backup /optional/index.html page to the www root to restore the normal frontpage.

create release scripts for mayor, main, minor and micro releases, which increase the specific release number and reset the minor release number e.g. main changes from 1.2.3.4 to 1.3.0.0

move the release number in a file that is included in git and that can be accessed from the sever admin page without db access

Create a page named 'create_release.php' that can be started on a development pc and starts test.php and if successful, increase the version and created a merge request to the release branche. This script can only be started if the development branch is checked out

Create a page named 'deploy_to_prod.php' that can be started on a test environment instance that starts test.php and if successful, deletes all test script (/test/* and /src/test/), creates a merge request to the master branche. This script can only be started if the .env ENV=test .

add to the 'create_release.php' page a refresh of the prod database to the test server

### tutorial

add a few tutorial pages that explain:

The main start page shows the "most relevant" global problems with the solution that most likely can fix these issues

"Most relevant" is defined by an extended UN DALY Index that includes physical needs and security called "Happy Time Point". Click on the blue Links for more details.

If you wonder how the list is calculated you can click on the number to see more details. The calculation is based on many assumptions and yes, they can be wrong.

Ich you create an account and log in, you can change the assumptions. The list is than adjusted based on your personal assumptions, but only for you.

Other user still see the original list, but they can see, that you have done some changes and they can also use your assumptions if they want.

So each your has its own "sandbox", that never changes, but can see everything that other users have done.

### fill the screen

for big screens add more data so that the screen is filled or increase the font size

### speed

check why the database loading takes longer if more data ist added and increase the cache usage

### workflow

add a formula del workflow similar to delete the added formula similar to src/test/resources/web/html/workflow/del_triple_wf6

add a formula add fail workflow to add a formula similar to src/test/resources/web/html/workflow/add_triple_fail_wf11

add a formula change fail workflow to change a formula similar to src/test/resources/web/html/workflow/chnage_triple_fail_wf12

add a formula del workflow fail similar to delete the added formula similar to src/test/resources/web/html/workflow/del_word_fail_wf10

add a value add workflow to add a value similar to src/test/resources/web/html/workflow/add_triple_wf4

add a value change workflow to change a value similar to src/test/resources/web/html/workflow/change_triple_wf5

add a value del workflow similar to delete the added value similar to src/test/resources/web/html/workflow/del_triple_wf6

add a value add fail workflow to add a value similar to src/test/resources/web/html/workflow/add_triple_fail_wf11

add a value change fail workflow to change a value similar to src/test/resources/web/html/workflow/chnage_triple_fail_wf12

add a value del workflow fail similar to delete the added value similar to src/test/resources/web/html/workflow/del_word_fail_wf10

reduce the number of load and save calls

why does src/test/resources/web/html/workflow/change_word_wf2/wf2_show.html contain 'the name of the word must not be empty'? I guess this should not be the case.

check that save() never fails add() silently (the rule is coding.md line 56 "never fail silently"; what is still missing is the audit that every save/del path complies)

in test_triples base the fill_url_array on the a ..._filled() function that returns an object and create the url_array using a object_to_url function. Note

rename the src/test/resources/web/html/workflow/change_word_invalid_wf7 to change_word_fail_wf7 because 'fail' is shorter and it is inline with 'add_word_fail_wf9' and change the 'invalid' to 'fail' within this wf code

for each positiv workflow test create a negativ with the test cases that can fail. Let's start with 'add_word_wf1' and create 'add_word_fail_wf9' that has e.g. the steps 'wf9_edit_no_name_save'.

make sure that all selectors create a hidden form field with the original values as done in sandbox::share_type_selector

create function like src/main/php/shared/helper/Translator.php::text_db_field for alle types that are part of src/main/resources/db_code_links

see /docs/llm/coding.md and in union queries created by the sql_creator the parameters are added to the par array, but if the parameter name matches, the parameter should not be repeated.

write a coded check in unit/coding_rule_tests.php that the $test_names are unique for all tests (the rule itself is coding.md "Keep $test_name short but unique")

after adding a word the word as it has been saved in the database should be shown. Because the db id is not yet known, that word name should be used to load the word. this implies that the url for /src/test/resources/web/html/workflow/add_word_wf1/wf1_edit_back_edit_save_cancel_edit_save_add_confirmed_url.txt should contain '"url_part_back": {"mask_id": "word_default", "name": "System Test Word"}' using the short url var for the name 'k'

update the confirm change view to shows the user changes (based on the '8' prefixed values) and call the page when in the word edit view save is pressed

complete the 'to_url_array' function for all word fields e.g. the sandbox fields and add a TODO that this should be moved the test object because it will probably only be used for testing. But this can only be decided after the workflows are complete

add a snap timestamp to the change log. The snap timestamp is the time when the user has called the edit view that he used to apply the changes so the db snap that has been the base for the change. Even if live update of the edit view would be possible, this is not recommended not tp break the user workflow. The max would be to show a refresh icon so that the user could refresh the edit view, but this is prio 4.

in the view 'Change word' adjust the url on the save button so that it fix the error messages 'url key "mask_id" is missing, url mapper for "mask" is missing, url mapper for "id" is missing, url mapper for "back" is missing, url mapper for "confirm" is missing, url mapper for "Name" is missing, url mapper for "py" is missing, url mapper for "Description" is missing, url mapper for "Plural" is missing, url mapper for "d" is missing, url mapper for "s" is missing, url mapper for "sp" is missing' caused by calling the url 'http://localhost/http/view.php?mask=3&id=259&back=259&confirm=1&Name=USD&py=3&Description=ISO+4217+alphabetic+code+for+the+United+States+dollar.&Plural=&d=0&s=1&sp=1' ; the expected result is that it should show the "Confirm update" view with the changes that the user has done and after pressing confirm that database row should be updated and the user should see th original page again, but with the updates , create first unit tests for the workflow using src/test/php/unit_workflow/all_workflow_tests.php

test.php gates this call under the WORKFLOW_TEST const, while the other DB-write tests run under a separate WRITE_TEST const. By folding the write workflow into all_workflow_tests::run, the write workflow now runs whenever WORKFLOW_TEST is on — independent of WRITE_TEST. That matches your "never differ" goal (it's how test_workflow.php already behaves — no WRITE_TEST gate there), but it does mean a WORKFLOW_TEST=true, WRITE_TEST=false run will now touch the DB.

move the field name const for all objects that might use the change_confirm view to src/main/php/shared/const/fields similar to src/main/php/shared/const/fields and use the shared ALL_FIELDS const to order the fields in the confirm_change view e.g. user_db, component_links

create a const for all db field comments e.g. move 'the user-specific geolocation change' in sandbox_value to a VALUE_GEO_COM const

fill in all placeholder

add all missing

use the '8' prefixed values (urlVar::PRE) to create a complex parallel change workflow. To detect the real user change requests and prevent overwriting other user change during the edit view is shown
-> test wordflow
1. user_a opens the edit view
2. user_b open the edit view and changes the phrase type
3. user_b press save and the changed phrased type is written to the database
4. user_a changes the description and press save
5. the changes of user_a are checked against the status when the edit mask has been opened and only the description is updated
6. the phrase type is left as user_b has changed it

check that all api interfaces can load user specific data independent of the session user

check the open api definition and the openapi check script and suggest updates in the definition and the script

### frontend requests

If the frontend requests data from the backend, the backend sends cached data without refresh whenever possible

but the backend adds a refresh trigger th the cache

the triggers are picked up by the crontab job and the cache is refreshed

a pure html frontend tries retries after an increasing time period to get the updated cache from the backend and stops the retry if it has received the update

a js frontend can use subscribe to get the updated cache data

additional the cache can be updated by time or by trigger words without frontend request

separate the api $db_con var from the frontend and backend

### word frontend

add a 'Word all values' view that show the values related to a word in up to 4 columns. For the column headline the four phrases with the most number of related values

apply the use of the html_base

create a component with the related formula that should show the formulas of the parent object connected with the verb 'is a' and add this component to the default word and triple views below the direct linked formulas. this component should include a small subheadline with 'from' and the name of the parent object

rename component "change log word" to "change log term" or add "change log triple"

show the description of the word in the word default page on the left 1/3 of the screen

The default view for a word should have four columns for width screens > 2800 pixel (config parameter):
1. a group of components with the description, the aliases / symbols and other related phrases
2. a group with the most relevant value by impact and if it exists a chart on the top and the position type 'side_or_first_below'
3. a group with the most relevant formulas and results (and later a result charts, just create a TODO) and the position type 'side_or_last_below'
4. the tab switch itself is done (ui_list::view_tab_box renders the Views / Changes / My / Others
   tabs with the preview placeholder and the view and switch buttons); what is missing is the
   miniature preview instead of the placeholder and the position type 'side_or_last_below'

add the formulas assigned to the parent phrase to the word_default view using also 1/3 of the screen width

add the values as a table where the word ist used to the word_default view using 2/3 of the screen width where often used phrases are column heads and the phrases are shown using a tree view

mainly copy the word default view to the triple default view

### word add view (missing parts of the retired http_old/word_add.php)

the legacy controller http_old/word_add.php has been replaced by the word_add view called via /http/view.php?m=2 (views::WORD_ADD_ID). The plain "create a word" case is fully covered by the new view (it even adds description, plural, default view, share and protection type plus the confirm step), but the following features of the old controller have no equivalent yet. Add them to the new view.

allow the user to link the new word to an existing word while adding it, as the old controller did with the url parameters 'add', 'verb' and 'word': add a verb and a target phrase selector to the word_add form, read them in web/word/word.php::url_mapper via url_var::VERB and url_var::WORD and, after the word has been created, save the corresponding triple. Without this the link parameters are silently dropped

fix the "Add similar word" button in src/main/php/web/word/triple_list.php that already calls url_new(views::WORD_ADD_ID, ...) with 'verb=...&word=...&type=...': it uses the human readable keys instead of the url_var codes (url_var::VERB 'b', url_var::WORD 'w', url_var::TYPE 'y' resp. url_var::PHRASE_TYPE 'py'), so even after the mapper is extended the parameters would not be picked up. The preset of the phrase type via the url is part of this

add the alternative "link an existing word" path of the old controller: a submit without a new name but with an existing word, a verb and a target word must create only the triple and not a new word. Extend web/word/word.php::input_valid with the old combined check "Either enter a name for the new word or select an existing word to link" instead of only the empty name warning of sandbox_named::input_valid

add the duplicate name pre-check of the old controller to web/word/word.php::input_valid: check via the term namespace (word, triple, formula and verb) if the name is already used and show the message of cfg/phrase/term.php::id_used_msg_text on the edit form. At the moment a name collision is only detected by the backend during the save, so the user gets the error only after the confirm step

add the duplicate and reverse link check of the old controller to web/word/triple.php::input_valid (which today only checks that both phrases are set): warn with '... already exists' if the triple exists in the same direction and with 'The reverse of ... already exists. Do you really want to add both sides?' if only the reverse link exists. This is needed for the triple_add view as well, not only for the word_add view

### word edit view (missing parts of the retired http_old/word_edit.php)

the legacy controller http_old/word_edit.php has been replaced by the word_edit view called via /http/view.php?m=3 (views::WORD_EDIT_ID). All fields of the old controller (name, plural, description, phrase type and default view) are part of the new edit form and the save now runs through the confirm step. Its only remaining caller was the 'switch' button of the view box (web/view/view.php::switch_link, used by web/component/execute/ui_list.php::view_tab_box), which called /http/word_edit.php?id=<word>&d=<view>&confirm=1 and wrote the new default view with one click. The button now only opens the word edit form, so the following parts are still missing.

restore the one-click 'switch' of the default view of a word: web/view/view.php::switch_link knows the target view, but /http/view.php?m=3 cannot write it yet. frontend.php::url_to_html already contains the mechanism (rest_ctrl::PAR_VIEW_NEW_ID 'new_id' -> $dbo->save_view($new_view_id) for the EDIT_DEL_MASKS_IDS), but web/sandbox/sandbox_named.php::save_view is an empty stub that returns an ok user_message without writing anything and that does not even accept the view id it is called with. Implement save_view (via the api, the same path as db_object::update) and let switch_link call /http/view.php?m=3&id=<word>&new_id=<view>, or route the switch through the standard confirm flow. Add a test that the default view of the word is really changed in the database after the switch

alternatively (or additionally) preselect the target view when the switch button opens the word edit form. This is not possible at the moment, because frontend.php::url_has_object_values treats any non control var in the url (e.g. url_var::VIEW 'd') as "the url carries the object values", so the word is no longer loaded from the database and the form would be shown with an empty name, description and plural. Either mark a prefilled edit link explicitly (e.g. load from the url only if all fields of the object are given) or merge the url values into the object loaded by id

the 'switch' and 'view' buttons of the view box are also rendered for a triple (ui_list::view_tab_box handles word and triple), but switch_link always uses the word edit mask. Use the edit mask of the object type (views::WORD_EDIT_ID resp. views::TRIPLE_EDIT_ID) so that the switch also works for a triple

the old controller answered an empty name with the hint 'An empty name should never be saved. Please delete the word instead.'; the new flow only shows the generic msg_id::NAME_EMPTY warning of sandbox_named::input_valid. Add the hint to delete the object instead to the empty name warning of an edit view (not of an add view)

### view add view (missing parts of the retired http_old/view_add.php)

the legacy controller http_old/view_add.php has been replaced by the view_add view called via /http/view.php?m=30 (views::VIEW_ADD_ID), which the 'add view' button of the navbar already uses. The new form has all fields of the old controller (name, description and view type) plus the view style, the share and the protection type, and it saves through the confirm step. The remaining callers of the old controller were the legacy render path web/view/view.php::dsp_navbar_html (only used if html_base::UI_USE_BOOTSTRAP is false) and the 'add view component' button of view.php resp. view_exe.php::linked_components; both now call url_new(views::VIEW_ADD_ID, ...). The following parts of the old controller have no equivalent yet.

after a new view has been created open the view edit view (m=31) for it, so the user can directly add the components: the old controller showed the new view with its component list (view::dsp_edit) instead of just returning to the calling page, and it carried the TODO to set the new view as the default view of the sample term. Today /http/view.php?m=30 only creates the view and goes back, so a new view stays empty and the user has to find it again to add any component

add the sample word of the old controller ('word' url parameter, used to simulate how the new view looks for a real term) to the view add and the view edit view. Without it the user creates a view without seeing its effect

the phrase type resp. view type could be preset via the url ('type') in the old controller. This is not possible in the new view for the same reason as for the word add view (frontend::url_has_object_values, see the word edit view section above)

### view edit view (missing parts of the retired http_old/view_edit.php)

the legacy controller http_old/view_edit.php has been replaced by the view_edit view called via /http/view.php?m=31 (views::VIEW_EDIT_ID), which the 'change view' link of the navbar and the 'design the view' button of the view selector already use. The new form has all fields of the old controller (name, description and view type) plus the view style, the share and the protection type, and it saves through the confirm step. But the old controller was not only a form for these fields, it was the view designer, and that part is completely missing in the new view.

add the component list of a view to the view edit view (m=31), so that a view can be designed again. The old controller could, for the view given by the id: link an existing component ('add_component' with the component id), create a new component and link it ('entry_name' plus 'new_entry_type'), unlink a component ('del' with the component id) and change the order of the components ('move_up' / 'move_down' with the component id, using view::entry_up and entry_down). Today /http/view.php?m=31 shows only the fields of the view itself, so a view can neither get a component nor lose one, and the order of the components cannot be changed from the frontend. Reuse the existing component views (views::COMPONENT_ADD_ID ff) and the component_link views for the single steps and add a test per step

add the sample word to the view edit view as well ('word' url parameter of the old controller, the term used to simulate how the view looks with real data), see the view add view section above

remove the now unreachable legacy view designer code: web/view/view.php and web/view/view_exe.php contain a nearly identical private linked_components() (a DRY violation) plus dsp_edit(), which were only called by http_old/view_add.php and http_old/view_edit.php. Their remaining buttons link to api::DSP_COMPONENT_LINK / DSP_COMPONENT_ADD, i.e. to controllers that are also already retired to /http_old, so these links are dead. Remove dsp_edit / linked_components (and the DSP_COMPONENT_* consts in shared/api.php) once the component list is part of the view edit view. The same applies to view::dsp_navbar_html (only used if html_base::UI_USE_BOOTSTRAP is false) and to view::selector_page and view::dsp_navbar_no_view, which have no caller at all since http_old/view_select.php is retired

remove the dead class web/user/user_display_old.php: it is only included by src/test/php/utils/test_base.php, is never instantiated, uses rest_ctrl without importing it (so it would fail if it were called) and links to controllers that are retired (view_edit.php, value_edit.php, user_triple.php, user_value.php, user_formula_link.php)

### view del view (missing parts of the retired http_old/view_del.php)

the legacy controller http_old/view_del.php has been replaced by the view_del view called via /http/view.php?m=32 (views::VIEW_DEL_ID), which the 'delete the view' button of the view selector already uses. The new view shows the name of the view to delete and writes the delete through the confirm step, so it covers the yes/no dialog of the old controller completely. No feature of the old controller is missing, but the delete itself is not protected.

add an in-use check to the delete of a view: web/view/view.php has no input_valid override, so unlike web/word/word.php::input_valid (msg_id::DELETE_IN_USE via is_in_use) a view can be deleted even if it is still the default view of a word or a triple, or if components are still linked to it. Add the check to the frontend view object (and the matching negative test) so the user gets a warning instead of dangling references. The old controller did not check this either, so this is not a regression of the migration but a gap of both

### formula add view (missing parts of the retired http_old/formula_add.php)

the legacy controller http_old/formula_add.php has been replaced by the formula_add view called via /http/view.php?m=24 (views::FORMULA_ADD_ID), which the 'add formula' button already uses (web/formula/formula.php::VIEW_ADD). The new form has all fields of the old controller (name, formula expression, description, formula type and the 'need all values' flag) plus the default view, the share and the protection type, and it saves through the confirm step. The old controller had no caller left in the program code, only in a commented out block of src/test/php/unit_ui/formula_ui_tests.php that calls the page of the productive server via file_get_contents. The following parts have no equivalent yet.

assign the new formula to a phrase while adding it, as the old controller did with the 'word' url parameter (formula::link_phrase_and_save after the save): a formula that is not assigned to any phrase is not shown anywhere, so today a formula added via /http/view.php?m=24 disappears for the user. Add the phrase selector to the formula add form, read it in web/formula/formula.php::url_mapper and save the formula link after the formula. The old check 'Word missing; Internal error, because a formula should always be linked to a word or a list of words.' belongs to this, but as a user warning of formula::input_valid, not as an internal error

add the missing input checks to web/formula/formula.php::input_valid, which today does not exist, so only the empty name warning of sandbox_named::input_valid is shown: warn if the formula expression is empty ('Formula text missing; Please define how the calculation should be done.') and if the name is already used by a word, verb, triple or formula (the term namespace check of the old controller via formula::get_term and term::id_used_msg_text, see also the word add view section above)

remove the commented out block in src/test/php/unit_ui/formula_ui_tests.php that tests the retired formula_add.php and formula_edit.php pages of the productive server via file_get_contents('https://zukunft.com/...'); a unit test must never call an external server. Replace it with a page test of the formula add view (m=24) based on the local html snapshot

### formula edit view (missing parts of the retired http_old/formula_edit.php)

the legacy controller http_old/formula_edit.php has been replaced by the formula_edit view called via /http/view.php?m=25 (views::FORMULA_EDIT_ID), which the 'change formula' button already uses (web/formula/formula.php::VIEW_EDIT). The new form has all fields of the old controller (name, formula expression, description, formula type and the 'need all values' flag) plus the default view, the share and the protection type, it shows the assigned words and triples, the results, the related formulas and the change log, and it saves through the confirm step. The old controller had no caller left in the program code, only in commented out blocks of src/test/php/unit_ui/formula_ui_tests.php and src/test/php/unit_write/formula_write_tests.php. What is missing is the maintenance of the phrase assignments.

make the assigned words and triples of the formula edit view (m=25) changeable: today they are only shown as links. The old controller could link another phrase to the formula (url_var::LINK_PHRASE 'fl' -> formula::link_phrase_and_save), unlink a phrase (url_var::UNLINK_PHRASE -> formula::unlink_phrase) and show a phrase selector to pick the phrase to link ('add_link'). Add an unlink button per assigned phrase and one add button with a phrase selector, both writing through the standard confirm flow, and add a test per step. This is the same missing assignment as in the formula add view above, so solve both together

after a formula has been changed the results that depend on it must be updated: the old controller had the trigger for it (formula::needs_res_upd -> assign_phr_lst, with the calc call already commented out). Check whether the backend save of a formula updates the depending results and if not, add the update and a test that a changed formula expression changes the result of an assigned phrase

### formula del view (missing parts of the retired http_old/formula_del.php)

the legacy controller http_old/formula_del.php has been replaced by the formula_del view called via /http/view.php?m=26 (views::FORMULA_DEL_ID), which the 'delete formula' button already uses (web/formula/formula.php::VIEW_DEL). The new view shows the name of the formula to delete and writes the delete through the confirm step. The old controller had no caller left in the program code, only in the commented out block of src/test/php/unit_ui/formula_ui_tests.php. What is missing is that the user is told what really happens with the formula.

tell the user in the formula del view (m=26) if the formula is only excluded and not removed: the old controller asked 'Exclude "<name>"?' instead of 'Delete "<name>"?' if formula::is_used() was true, because a formula that is still used by another user or by a result can only be excluded, not deleted. The new view always says 'delete'. Show the exclude wording (and the reason) if the object is still in use, best as a shared solution for all del views, because the same applies e.g. to the view del view (see the section above) and to the word del view, where web/word/word.php::input_valid already warns with msg_id::DELETE_IN_USE via is_in_use

### value add view (missing parts of the retired http_old/value_add.php)

the legacy controller http_old/value_add.php has been replaced by the value_add view called via /http/view.php?m=18 (views::VALUE_ADD_ID), which the 'add value' button already uses (web/html/button.php::add_value with url_new(views::VALUE_ADD_ID)). The new form has the value, the description, the source, the value type, the view style, the default view, the share and the protection type and it saves through the confirm step, so it has more fields than the old controller. But the phrases of the value, which are its key, are only a free text 'group' field, and the conversion of the user entry is lost.

give the value add view (m=18) the phrases of the new value: the old controller took them from the url ('phrase1', 'phrase2', ... with the matching 'type1', 'type2', ... to preselect the phrase type, or 'phrases' as a comma separated id list) and preloaded the value with them, so that 'add a new value similar to <phrase list>' really preselected the phrases of the calling page. The new view has only the free text field url_var::GROUP_NAME ('gn'), and the prefill is switched off in the button itself (web/html/button.php::add_value, '// TODO Prio 2 activate  //$url_phr = $phr_lst->id_url_long();'). Add a phrase selector to the value add form, fill it from the url of the calling page and activate the prefill in the button

call the value conversion again when a value is saved: cfg/value/value_base.php::convert() (it removes spaces and thousand separators from the user entry and sets the number) is no longer called by any live code, only the retired controllers called it. Check where the user entry of the value form (url_var::VALUE 'v') is converted to the database number today, add the conversion to the write path of the value if it is missing and add a test with a user entry like "1'000"

save the source of a value as the new default source of the user: the old controller stored the selected source on the user (user::src) and saved the user, so that the next value of the same user got the same source suggested. The new value add view has the source selector, but does not remember the choice

### value edit view (missing parts of the retired http_old/value_edit.php)

the legacy controller http_old/value_edit.php has been replaced by the value_edit view called via /http/view.php?m=19 (views::VALUE_EDIT_ID), which the 'change value' button already uses (web/value/value.php::VIEW_EDIT_ID). The new form has the same fields as the value add view (value, description, source, value type, view style, default view, share and protection type) and it saves through the confirm step. The old controller had no caller left in the program code, only in commented out blocks of the value tests (and in the dead class web/user/user_display_old.php, see above). The missing parts are the same as for the value add view plus the check of an empty value.

show and change the phrases of a value in the value edit view (m=19): the old controller loaded the value by id including its phrases and let the user add or remove a phrase ('phrase1', 'phrase2', ... with the matching 'type1', 'type2', ...), showing the form again after each change. The new view has only the free text field url_var::GROUP_NAME ('gn'), which is even empty in the rendered page, so the phrases of a value can neither be seen nor changed. Solve it together with the phrase selector of the value add view (see the section above)

add web/value/value.php::input_valid, which does not exist today, so an empty number is saved without any warning: the old controller answered an empty value with 'An empty number should not be saved. Please delete/exclude the value instead.' and used the value from the database as a fallback if only the phrases had been changed. Add both, with a negative test for the empty value

### value del view (missing parts of the retired http_old/value_del.php)

the legacy controller http_old/value_del.php has been replaced by the value_del view called via /http/view.php?m=20 (views::VALUE_DEL_ID), which the 'delete value' button already uses (web/value/value.php::VIEW_DEL). The new view asks for the confirmation and writes the delete through the confirm step. The old controller had no caller left in the program code, only in the commented out block of src/test/php/unit_ui/value_ui_tests.php. What is missing is the phrases of the value in the question to the user.

show the phrases of the value in the value del view (m=20): the old controller loaded them (value::load_phrases) and asked '<number> for <phrase names>?', while the new view shows only the number, e.g. '3.14', so the user cannot see which of his values he is about to delete. Add the phrase list of the value to the delete question (and the same for the value edit view, see the section above). Note that a value has no name, so unlike a word the number alone does not identify it

### view select page (missing parts of the retired http_old/view_select.php)

the legacy controller http_old/view_select.php ('Select the display format for "<word>"') has no mask of its own in the new frontend. Its replacement is the 'Views' tab of the word and triple page (component_types::VIEW_TAB_BOX -> web/component/execute/ui_list.php::view_tab_box), which lists the views of the object with the 'view' and the 'switch' button, and its second tab shows the change log that the old page appended via word::log_view. The old page had no caller left in the program code (only in a commented out navbar block of web/view/view.php). Two things of the old page are missing in the tab box.

add the 'design the view' and the 'delete the view' button to each view of the views tab box: the old page offered them per view (view::selector_page), so a view could be edited or deleted directly from the object page. The new tab box has only 'view' and 'switch'. Use the standard urls /http/view.php?m=31 and m=32 (views::VIEW_EDIT_ID and VIEW_DEL_ID) and remember that a delete must only be offered if the user may delete the view

the 'switch' button of the views tab box does not save the selected view as the default view of the word, see the word edit view section above; this was the main purpose of the old view select page, so without it the page has no full replacement yet

check the 'alternative view' link of the navbar (web/html/html_base.php::view_change_list): it points to the hard coded string '?m=view_change&id=2', but there is no view_change const in shared/const/views.php, so the link probably does not resolve to a view. Either add the missing view and its const or let the link open the views tab of the object

### verb list view (missing parts of the retired http_old/verbs.php)

the legacy controller http_old/verbs.php ('Word link types', the list of all verbs for the admin user) has a mask in the new frontend: the verbs view called via /http/view.php?m=86 (views::VERBS_ID). But the view is defined in src/main/resources/messages/system_views.json with a name, a description and the code_id 'verbs' only and has no components, so /http/view.php?m=86 shows an empty page and the verb list is nowhere visible. The old page had no caller in the program code either, so the verbs can currently not be seen or maintained in the frontend at all.

add a component to the verbs view (m=86) that shows the list of all verbs with the name, the reverse name, the description and the usage of each verb, and give each row an edit and a delete button plus one add button for the list, using the existing verb masks /http/view.php?m=6, m=7 and m=8 (views::VERB_ADD_ID, VERB_EDIT_ID and VERB_DEL_ID). Only an admin may change a verb, so show the buttons only for an admin user. Add a page test with the html snapshot of the view

remove or rewrite web/verb/verb_list.php::dsp_list, the renderer of the old page: it has no caller any more since http_old/verbs.php is retired, it builds the item urls from a hard coded script name ('link_type_edit.php' and 'link_type_add.php'), and these two scripts do not exist in the program at all, so even in the old page the links were dead. The new list component must build its urls with html_base::url_new and the views consts

### user settings view (missing parts of the retired http_old/user.php)

the legacy controller http_old/user.php (the settings page of the user) has a mask in the new frontend: the user view called via /http/view.php?m=74 (views::USER_ID), which the user reaches with the 'settings' link of the navbar (api::SETTINGS_REL). The new view shows 'Open issues related to you' (the same as the old dsp_errors of the user), but the main component is only the text 'user_setting placeholder' (see the snapshot src/test/resources/web/html/views_by_id/user/74_user.html). The old controller had no caller in the program code any more, only in the dead class web/user/user_display_old.php, and it would fail anyway, because it calls user_ui::dsp_sandbox, which exists only in that dead class. So the following parts of the old page have to be built in the new view.

replace the 'user_setting placeholder' component of the user view (m=74) with the real user settings form: web/user/user.php::form_edit already creates it (name, email, ...). Save the change through the standard confirm flow and add a page test

show the user sandbox in the user view (m=74): the old page listed under 'Your changes, which are not standard' all objects that the user has changed for himself only (words, triples, values, formulas, formula links, views and components) and offered an undo per object that removed the user overwrite (del_usr_cfg, called via the url parameters 'undo_word', 'undo_triple', 'undo_value', 'undo_formula', 'undo_formula_link', 'undo_view', 'undo_component'). Without this the user can no longer see or revert his own overwrites. There is already a views::UNDO_ID (73) mask that can be used for the confirmation of the undo. The renderer of the old page (web/user/user_display_old.php::dsp_sandbox_*) reads the database directly and must not be reused as it is, because web/ may only use the api (see docs/llm/frontend.md)

show 'Your latest changes' in the user view (m=74) with web/user/user.php::dsp_changes, the change log of the user

show the link to the json import (http/import.php) in the user view (m=74) for a user that may import (user::can_import), as the old page did. Do not bring back the links to /test/test.php and the other test scripts that the old page showed to an admin, because the tests must not be startable over the web; the admin functions belong to http/server_admin.php

show all open program issues to an admin user in the user view (m=74): the old page called dsp_errors with the type 'other' for an admin, the new view shows only the issues of the user himself

### triple page (nothing missing from the retired http_old/triple.php)

the legacy controller http_old/triple.php ('display a RDF triple') is replaced by the triple default view called via /http/view.php?m=92 (views::TRIPLE_ID) with the triple add, edit and del masks m=9, m=10 and m=11 (views::TRIPLE_ADD_ID, TRIPLE_EDIT_ID and TRIPLE_DEL_ID). No feature is missing: the old page had no caller, it loaded the triple from the url parameter 'triples' and echoed only triple::dsp_id(), the internal debug identification of the object, without a navbar and without any html page frame, so the new view shows much more than the old page ever did.

if a machine readable (RDF / linked data) representation of a triple is really wanted, as the title of the old page suggests, it belongs to the api (http/get_json.php resp. the rest controller), not to a separate html page, and it needs its own issue

### value page (missing parts of the retired http_old/value.php)

the legacy controller http_old/value.php has a mask in the new frontend: the value default view called via /http/view.php?m=96 (views::VALUE_DEFAULT_ID), which is used e.g. as the cancel target of the value edit view. It shows the phrases of the value as the headline with a link to each phrase, the share and protection type and the 'change value' button to m=19, but the number itself is not shown, because the main component is only the text 'main_value placeholder' (see the snapshot src/test/resources/web/html/views_by_id/value/96_value_*.html). The old page had no caller in the program code. The stray file http_old/value (an html page that redirected to http://www.zukunft.com/value.php via a meta refresh, unencrypted and to a page that no longer exists) is retired with it.

replace the 'main_value placeholder' component of the value default view (m=96) with the number of the value, so that a value page really shows the value. Show it with the unit of the phrases and add a page test

allow to open a value by the names of its phrases: the old page took the url parameter 't' with a comma separated list of word names, loaded the word list, showed the names and the value of the group of these words and offered the number directly as an input field (value_ui::value_edit), so a value could be looked up and changed without knowing its database id. Decide if this shortcut is still wanted (it is the natural url for 'calc with words', e.g. /http/view.php?m=96&t=zurich,inhabitants) and if yes read the phrase names in web/value/value.php::url_mapper and load the value by the group of the phrases

### phrase list page (nothing new missing from the retired http_old/phrase_list.php)

the legacy controller http_old/phrase_list.php had no caller and did not do what its header says ('return a phrase list API object'): its body is a copy of the retired word_add.php (it loads the word_add view, adds a word and links it with a triple to an existing word), it carries the TODO 'use view_shared::PHRASE_LIST instead of WORD_ADD' for a view that does not exist in shared/const/views.php, and its last render call (word_ui::dsp_add) is commented out, so the page showed only the navbar. Nothing has to be migrated: what it really did is the word add view /http/view.php?m=2, and the parts that are missing there are already listed in the word add view section above. A phrase list as an api object is delivered by http/get_json.php resp. the rest controller, and a single phrase is shown with the phrase default view /http/view.php?m=110 (views::PHRASE_ID)

### result explain view (missing parts of the retired http_old/formula_result.php)

the legacy controller http_old/formula_result.php ('explains one formula result') has a mask in the new frontend: the result explain view called via /http/view.php?m=70 (views::RESULT_EXPLAIN_ID). But the component of the view is a stub: web/component/execute/system_page.php::result_explain() returns the text 'result_explain placeholder' and carries the TODO Prio 0 'fill with real code' (see the snapshot src/test/resources/web/html/views_by_id/result/70_result_*.html). The old page had no caller in the program code, but it was the only place that really explained a result, so the explanation of a result is currently not available.

fill web/component/execute/system_page.php::result_explain() with the real code: web/result/result.php::explain($lead_phr_id, $back) already creates the explanation (which values and which formula lead to the result) and has no caller any more since http_old/formula_result.php is retired. Read the result of the view from the url (the old page took the result id, and alternatively the formula id, the leading phrase to sort the explanation and the time phrase) and add a page test with the html snapshot

the same TODO Prio 0 placeholders exist for the other system body components of web/component/execute/system_page.php: value_details, formula_test, sandbox and undo (and 'user_setting' resp. 'main_value' in the user and value view, see the sections above). Fill them all with the real code, so that the views that replace the retired controllers really show their content, and add a page test per component

### formula test view (missing parts of the retired http_old/formula_test.php)

the legacy controller http_old/formula_test.php ('to debug the formula results') has a mask in the new frontend: the formula test view called via /http/view.php?m=71 (views::FORMULA_TEST_ID). But as for the result explain view its component is a stub: web/component/execute/system_page.php::formula_test() returns 'formula_test placeholder' (see the snapshot src/test/resources/web/html/views_by_id/formula/71_formula_formula_test.html). The only caller of the old page were the 'Test' and 'Refresh results' buttons of web/formula/formula.php::dsp_test_and_samples, which is reached only via formula::dsp_edit, i.e. only from the retired formula_add.php and formula_edit.php, so the buttons are in dead code. This means that the formula calculation can currently not be tested from the frontend at all.

fill web/component/execute/system_page.php::formula_test() with the real code of the old page: for the formula given by the url show the phrases used, the values found and the calculated result step by step, with the 'more details' link that increases the debug level, and respect the configured frontend response time (the old page stopped the drill down when the response time was reached). Add the 'refresh' function of the old page (delete all results of the formula and calculate them again) as an own button that writes through the standard confirm flow, and add a page test

decide if the 'user' url parameter of the old formula test page is still wanted (it showed the formula calculation as another user sees it, to debug a user specific overwrite). If yes, allow it only for an admin user, because it shows the data of another user

remove the dead formula edit render path: web/formula/formula.php::dsp_edit and the functions that only it calls (dsp_test_and_samples with its links to the retired formula_test.php, dsp_used4words, dsp_hist, dsp_hist_links) have no caller any more since http_old/formula_add.php and http_old/formula_edit.php are retired. This is the same clean up as for view::dsp_edit and linked_components (see the view edit view section above)

### import view (missing parts of the retired http_old/import.php)

the legacy controller http_old/import.php (upload a json file and import it) has a mask in the new frontend: the import view called via /http/view.php?m=76 (views::IMPORT_ID). The view even shows the real upload form (html_base.php line 1758 creates a multipart form that posts to /http/view.php?m=import, see the snapshot src/test/resources/web/html/views_by_id/im_export/76_import.html), but the post is never processed: frontend::url_to_action has no arm for the import view and no code in web/ reads $_FILES, so the submit only reaches log_ignored_write_step. The old controller had no caller left either (only the retired http_old/user.php linked to it), so the json import over the web is currently not possible at all - it works only via the test and setup scripts.

process the upload of the import view (m=76): read $_FILES once in http/view.php (never inside a method, see docs/llm/state-and-messages.md) and pass it as a parameter to a new import action in frontend::url_to_action, which calls cfg/import/import.php::put with the decoded json. Take the checks of the old controller over: only .json files, not empty, not bigger than the configured limit (the old hard coded 10 MB should become a config value) and is_uploaded_file, and answer with a user_message and a msg_id, never with a hard coded english text

show the import result of the old controller to the user: it reported per object type how many entries have been loaded (words, verbs, triples, formulas, sources, references, values, simple values, views, components, validated results and validated views, plus users and system objects for an admin). The counters are still filled by the import object, so only the message to the user has to be created, with the counter names translated via the msg_id mechanism

allow the import only for a user that may import (user::can_import), and add a test for the negative case, because an import can change many objects at once and is therefore a good target for an attack. Note that the old controller did not check this at all: it imported the file for every user with an id

### json export (missing parts of the retired http_old/get_json.php)

the legacy endpoint http_old/get_json.php was not a html page but an api call: it took a comma separated list of phrase names (url_var::WORDS, e.g. 't=Nestlé,country,weight'), loaded the phrase list, added all related phrases (phrase_list::are) and returned the json of them created by service/export/json_io.php::export. It has no caller in the program code any more, only in the experimental js files src/test/php/dev/test_js.php and test_jquery.php (which partly call the productive server). Its replacement is not a view mask but the rest api endpoint api/json/index.php ('the json im- and export API controller'), and the export views m=77 to m=81 (views::EXPORT_ID, EXPORT_JSON_ID, EXPORT_XML_ID, EXPORT_CSV_ID and EXPORT_ODS_ID). Both are incomplete.

fix and complete api/json/index.php: it overwrites the url parameter with the hard coded line '$wrd_id = 1;', so it always returns the word with the id 1, it exports only a single word (word::export_json) instead of a phrase list with all related phrases (json_io::export as the old endpoint did), and it has no PUT branch although its header says that PUT imports data. Add the phrase name list parameter of the old endpoint, use json_io for the export, implement the PUT import (together with the import view, see the import view section above) and add an api test for each case

make the export views work: m=77 (export in the selected format), m=78 (json), m=79 (xml), m=80 (csv) and m=81 (ods) show a form with the name, the group and a phrase selector, but nothing performs the export - service/export/json_io.php::export has no caller any more except json_io::export_file. Wire the export button of the views to the export service and offer the result as a download

remove or rewrite the experimental js files src/test/php/dev/test_js.php and src/test/php/dev/test_jquery.php: they call the retired /http/get_json.php, partly on the productive server (https://zukunft.com/http/get_json.php), and they are not part of any test suite. The retired http_old/get_json_test.php belongs to them: it just echoed a hard coded json array with ten sample names ('balance sheet', 'BMW', ...) as the answer for the autocomplete experiments, so nothing has to be migrated from it. The real suggestion list for the search field comes from the word find view (m=67) resp. from the api (api/phraseList), and if a type ahead suggestion is wanted again, it must use the api and not a static file

### error log view (missing parts of the retired http_old/error_log.php)

the legacy controller http_old/error_log.php (show one internal error to the user, so that he can track the solving of it) has a mask in the new frontend: the error log view called via /http/view.php?m=65 (views::ERROR_LOG_ID), and frontend::url_to_action already has the action for the error update view (m=66, views::ERROR_UPDATE_ID). Its only caller was the critical error message of cfg/log_text/text_log_functions.php ('You can track the solving of the error with this link:'), which built the url from api::SCRIPT_PATH . api::ERROR_LOG_SCRIPT and therefore created the doubled and invalid path '/http//http/error_log.php'. The link now points to /http/view.php?m=65&id=<sys_log id> and the unused const api::ERROR_LOG_SCRIPT is removed. But the component of the view is a stub.

fill web/component/execute/system_page.php::error_log() with the real code: it returns only the text 'error_log placeholder' (see the snapshot src/test/resources/web/html/views_by_id/sys_log/65_sys_log_2.html), while web/system/sys_log.php::page_view already creates the page of one error entry and has no caller any more since http_old/error_log.php is retired. Load the sys log entry by the id from the url, show it and add a page test. Take care that a user may only see his own errors and that only an admin sees the errors of all users

### calculate all results (missing parts of the retired http_old/calculate.php)

the legacy controller http_old/calculate.php ('update all formula results', the batch version of the retired formula_test.php) had no caller in the program code. It loaded all formulas in blocks, built the calculation queue per formula and calculated all results, while it streamed the progress in percent to the browser with ob_flush every few seconds (the pause taken from the configured frontend response time). It has no direct replacement: the job views exist (m=82 job_async 'Progress', m=83 job_control 'Process list' and m=84 job_check, views::JOB_ASYNC_ID, JOB_CONTROL_ID and JOB_CHECK_ID), but they show only 'process_progress placeholder' resp. 'process_list placeholder', and shared/types/job_types.php has only the per object triggers (value_update, formula_update, ...), no job that recalculates all results.

add a job type that recalculates all formula results (the queue building of the old controller with result_list::frm_upd_lst and formula::calc can be taken over) and let the admin start it from the job control view (m=83). A long running recalculation must not block a http request, so it must run as an async job, not synchronously as the old controller did

fill the job views with real code: web/component/execute/system_page.php shows only 'process_list placeholder' for the job control view (m=83) and 'process_progress placeholder' for the job async view (m=82), so a running job can neither be seen nor followed. Show the job list with the status of each job and the progress in percent of the running job, which replaces the streaming progress of the old controller, and add a page test

note for the denial of service protection (see the section above): the old controller was a perfect attack target, because it recalculated all formulas of the pod on a simple GET request and checked only that the user id is set, so even an ip user without a login could start it. The new job must be startable only by an admin user, and there must be a test for the negative case

### reload the base configuration (missing parts of the retired http_old/async_process.php)

the legacy controller http_old/async_process.php had no caller in the program code and it did not do what its name and its header say ('display the progress of an asynchronous process'): for an admin user it reloaded the base configuration synchronously in the http request (import_file::import_system_data, the action that the retired http_old/user.php offered as 'Force reloading the base configuration e.g. to check that the units definition are still OK'), it showed the import view (m=76) as the header and printed only 'loading of base configuration started' and 'finished' around the import. The function import_system_data is still used by cfg/db/sql_db.php, so only the frontend action is missing.

add the 'reload the base configuration' action for an admin to http/server_admin.php (where the other admin functions live) or as a job type, with a confirm step, because the import overwrites the system objects. Do not run it synchronously in a http request as the old page did: a long running import belongs to an async job, whose progress is shown in the job async view (m=82), see the calculate all results section above

the mask that the name of the old page promises is the job async view (m=82, views::JOB_ASYNC_ID, 'Progress'), but it shows only 'process_progress placeholder', so the progress of an asynchronous process cannot be followed at all, see the calculate all results section above

### data load

are there any database or object fields that are not yet filled or set by one of the json import tests

add a table licences and add the field licence to the json message header with the possibility to overwrite the licence for each object

add the licence to the subtitle if not the standard cc0

in the footer add dynamically other licences if used

### backend

in src/main/php/shared/json_fields.php rename 'view-validation' to 'view_validations' and 'calc-validation' to 'calc_validations' and 'value-list' to 'value_list' and 'ip-blacklist' to 'ip_blacklist' to always use '_" instead of '-' for json field names

add a config section to the json import format that can be used to overwrite the system and user config for the import and add positive and negative unit tests for the overwrite of the number of decimals

add in the float value object the var 'precision' which defines how accurate the value is. include this field not only in the database (but not for standard values), the api, the frontend and also in the im- end export json

add to the json import a 'view_validation' section that contains some relevant screen outputs in the '.md' format based on a given human readable url

add to the json import a 'jobs_starts' section where jobs starts could be triggered before or after the import e.g. request adding a verb or creating a wikipedia article

add a job to add or link a new verb

add a script that updates the verb section in docs/llm/json_structure.md based on docs/llm/json_structure.md

split the jobs_starts into jobs_before and jobs_after

add the src/main/resources/messages/start_page/theses_complex_simple.json to the full import

add a job to create a wikipedia article

if type_list_check fails update the json and reload the config and try again

create a phrase_value_key table that contains the phrase_id and the value_table_id and the value key for a fast (db index based) selection of all values related to a phrase

add the default date format 'd-m-Y H:i' to the config.yaml that the user can overwrite to display a date and use the config value where 'd-m-Y H:i' is used until now. For any system tests used a fixed const to replace 'd-m-Y H:i'

move time zone setting to .env

create a job to update the usage of a word

add a config parameter that the api message should include the message header (or not) and apply this to the api

Allow the users to define their own workflow → which view follow which under which conditions

Add the component type 'form validation' that checks based on phrase list and formulas the changed form values and create an info, warning or error message and redirects the workflow if needed

add the user types 'corporation', 'government', ...

add the table 'user_relation' that defines the relation between two users e.g. if user_a has a high trust level for user_b

add the table 'user_relation_types' with the entires 'is part of', 'high trust', 'medium trust', 'low trust', 'ignores', ...

### import

create a copy of all words and triples used for system testing before the config import, so that adding a new config value with a new word or triple does not break the test cases

in json import a calc_validation list is created. Use this list to check if the results cen be reproduced based on the data_object $dto filled only with the values and formulas from the json import file. In case of any errors use the usual path via $msg to send a message to the user

in json import a calc_validation list is created. Use this list to reproduce the results after the import for the user

in json import add a view_validation part that contains the views in the pure text format created by the html_to_text function. use this to check if after the import the expected views for the user can be reproduced

fix the '// TODO add json_fields::VIEW_VALIDATION' in src/main/php/cfg/import/import.php (view-validation as the rendering twin of calc-validation). suggested steps (adjust as needed):
1. decide the format of one "view-validation" entry: the view selection (view name, or the human-readable url of pending line 93/95) plus the expected ".md" output; document it in docs/llm/json_structure.md next to calc-validation
2. add a view-check list to data_object (view_check_list + add_view_validation), filled at the TODO via a new dto_get_view_validation() that maps each entry to a view and stores its expected markdown (mirror dto_get_results(..., use_to_check=true) and result_check_list)
3. add data_object::validate_views(user_message) (mirror validate_results): render each imported view to HTML via its *_ui display function, normalise it with html_to_markdown, compare to the expected markdown and on a difference add a translatable msg_id error (new VIEW_VALIDATION_MISMATCH / VIEW_VALIDATION_VIEW_MISSING cases with en/de)
4. call validate_views at the end of get_data_object next to the validate_results block and count view_validations_done / view_validations_failed
5. add a small sample import file with a view-validation section and a $dto unit test in import_tests.php: one positive (the rendered view matches) and one negative (a wrong expected ".md" reports the mismatch via $msg)
   open questions for review: render the view against the imported $dto only or the database; which user context; is html_to_markdown the right normalisation or should both text and markdown be supported

### user frontend

fill the placeholders

add a page with the sys_log entries to the admin menu

dsp_sandbox_* family → one generic "user changes vs. standard" component

Add to src/test/php/unit_ui/user_ui_tests.php tests for a new frontend component user_sandbox that shows, per object type (value, formula, formula link, word, triple, view, component, view link, source), the user's changes that differ from the standard, with columns "your value / common value / other users" and an undo button (icon from web/const/icons.php, undo URL built from named url_var consts with the '9'-prefixed back param). Implement it once as a generic renderer over a list of sandbox-difference rows delivered by the backend API as JSON; the per-type functions reduce to thin typed wrappers, or better, to one parameter. Requirements implied:

1. Backend API endpoint (e.g. api/user/sandbox) that returns the user-vs-standard-vs-others diff list per object type — move all eight inline SQL statements out of web/ into prepared, parameterized SQL in the model layer (the current string-concatenated WHERE u.user_id = $id is also an injection risk).
2. The "if user value equals standard, call del_usr_cfg()" logic is a DB consistency cleanup, not display logic: move it to a backend job/check (e.g. into the system consistency checks) and remove it from the frontend entirely.
3. Column headers and the "deleted" marker become msg_id cases in messages.php with en/de translations.
4. Unit test first, per type one positive (a factory-built diff list renders the expected object_pages/user_sandbox.html fragment) and one negative (empty diff list → documented empty output); factories named like test_words::sandbox_diff_ui() without repeating the class object word.

or smaller tasks like:

- dsp_sandbox_wrd → "show words the user renamed vs. the common name, with an undo-to-standard button"
- dsp_sandbox_wrd_link → "show triples the user changed (name/excluded) vs. standard and other users' versions, undo button"
- dsp_sandbox_frm → "show formulas where the user's expression text differs from the standard, undo button"
- dsp_sandbox_frm_link → "show formula↔phrase link changes (link type/excluded) vs. standard and others, undo button"
- dsp_sandbox_val → "show values the user overrode (number/source/excluded) vs. standard and others, value linked to value_edit, undo button"
- dsp_sandbox_view → "show view changes (name/description/type/excluded) vs. standard and others, undo button" — note the old code has a real bug here (if ($usr_ui->set_name(...)) instead of a comparison) which the rewrite must not carry over
- dsp_sandbox_component → "show component changes vs. standard and others, undo button"
- dsp_sandbox_view_link → "show component-link changes (order/position) vs. standard and others, undo button" — the old function has dead code (if (SQL_DB_TYPE != POSTGRES) wrapping an if (== POSTGRES)), so on Postgres it currently renders nothing; treat the behaviour as new, not as a port
- dsp_sandbox_source → "show source changes (name/url/description/type) vs. standard and others, undo button" — resolve the open TODO whether sources get a real del_usr_cfg() in the backend instead of the frontend del() call

### remove the database access from src/main/php/web (load via the API only)

scan of 2026-06-13: the frontend must never open or query the database (see docs/llm/frontend.md "The frontend never accesses the database — load via the API"). The markers are `new sql_db` / `new sql_creator` / `global $db_con`; the coded check is coding_rule_tests::php_web_only_allowed_globals_tests. Remaining cases, solve step by step:

1. (live) web/frontend.php open_db()/start() bootstrap opens the database connection directly (already marked "TODO Prio 1 to be deprecated and use the api only for the frontend"); it is the only file excluded from coding_rule_tests::php_web_only_allowed_globals_tests. Move the bootstrap behind the API so web/ no longer needs $sys/$db_con/$cac/$cfg, then remove the 'frontend.php' exception from that coded check.

2. (dead) web/value/value.php::dsp_samples() uses `new sql_db()` + raw SQL but sits entirely inside a /* ... */ block comment (from line ~763). Remove it, or rebuild via the group/value API if the sample display is still wanted.

3. (dead) web/user/user_display_old.php contains 9 `new sql_db()` direct-DB display functions and is not referenced anywhere in src/main/php. Delete the file. This also removes the last '&back=' url parameters of the source (see the general cleanup section above).

4. fix web/log/user_log_display.php::dsp_hist(): it builds $result from change_log_list::tbl() and then returns '' (left over from the api migration of dsp_hist / dsp_hist_links), so the change history is loaded and thrown away.

after each step src/main/php/web must stay free of `new sql_db` / `new sql_creator` / `global $db_con`.

### fix error and warnings

### general

check where in the frontend a parameter / configuration values is used that is not yet taken from the config.yaml / user_configuration and at least mark it with a TODO Prio 1

create a script that updates all caches e.g. src/test/resources/api/type_lists/type_lists.json and src/test/resources/api/ui_config/ui_config.json after a change of any parameter in src/main/resources/db_code_links

cover the log_err_msg_ui guard branches of web/component/execute/ui_list.php (triple_list,
formula_list, values_by_source, result_list: missing selection object, missing cache list,
unexpected selection class) with unit tests: each branch is reached today only by calling the
function with a null $dbo, a null cache or a foreign class, and log_err_msg_ui writes a sys_log
row via log_msg, which needs $db_con and therefore cannot run in the unit_ui tier; either add a
test-mode short circuit to the log writer or move these tests to unit_write

### remaining potential security issues

add TOTP authentification for SERVER_ADMIN2 and 3, so that the first login can be done with the pure user name and password and than a page shows the QR code e.g. for an App like FreeOTP+ to add a second factor

status of the security check re-run on 2026-07-22, ordered by exploitability; only issues that are
NOT yet solved are listed here - the fixed ones (the 2026-07-17, 2026-07-20 and 2026-07-21 findings,
fix #334 parts 1-23, and the stored xss of the condensed change-log render) live in the git history,
and the reusable rules distilled from them - output encoding, the api read-access gate, privileged
fields via a checked setter, the write-path csrf + method-detection, never build a filesystem path
from a user name, env-gated debug, gitignored runtime files - live in docs/code_guidelines.md
("security" section). each open item below was verified against the code with file:line evidence.

no open finding at critical / high: the rest of the surface is clean. write path (now enabled for api/word only): all
three write methods (post/put/delete) go through controller::change_permitted (same-origin +
X-CSRF-Token + not-blocked) before any db access; put_json trusting the client id is safe because
sandbox::save routes a non-owner change to a user overlay (never the shared row); the checked
code_id/profile/protection setters hold and owner_id is not mappable from client json; the 256-bit
csrf token is compared with hash_equals with no bypass. access control / idor: every read endpoint
gates with is_readable_by / filter_readable_by (single, list and embedded INCL_RELATED lists),
sysLogList is admin-gated, api/user is admin-or-self, data_user impersonation is admin/system-only,
admin masks are centrally gated. injection: sql parameterised via sql_creator, no reachable exec /
unserialize / dynamic-include / xxe, mail recipient is a loaded address. secrets/config: no committed
real secret, docker uses env refs, .htaccess default-deny holds.

lower / latent (below the critical bar today, recorded for completeness, NOT counted as unsolved
critical): (1) button::html() (web/html/button.php:113) emits $this->title
raw into alt="..."; all current callers pass constant/translated titles so it is not reachable with
user data today (the html_fa() add/edit/del path escapes via ref()); escape the alt as defense in
depth. (2) formula::dsp_text() (web/formula/formula.php:686) emits usr_text raw, reachable only from
legacy display()/result-explain paths that are effectively dead (the live render uses name_link());
latent stored xss if revived. (3) legacy web-side raw-sql builders (user_display_old.php,
view_list::selector_page, value::dsp_samples) are all dead/commented - recommend deleting.
(4) object-level admin/no-change protection is enforced only for changes to the protection field
itself (check_protection_change), not consulted in the general edit/delete authz - not exploitable
today (a normal user's edit of an admin-protected system-owned object is routed to their own overlay),
becomes relevant only when a pod-level config must be read from the system value not the user overlay
(the pending rate-limiter work); same defense-in-depth character as the save_user() latent item.

### fermi live-adjust (the sign-flip loop)

Goal of this whole block: a visitor changes ONE assumed input value of a Fermi
estimate and sees the dependent result — and its sign — recompute live, without
a page reload and without a developer rebuilding anything. Success metric is not
"feature complete" but "one conversation partner changes the sign-determining
value and does not stall". Build the smallest path that delivers that loop;
defer everything generic. Work the prompts in order — each is independently
shippable and testable, and each starts by writing the unit test first
(`src/test/php/unit_ui/...` for rendering, `src/test/php/unit_workflow/...` for
the edit path), per `docs/llm/testing.md`. Use the `fermi_discussion.json`
import fixture (the status-quo-harm / rollback / VoI chains) as the live data —
the sign-determining input is the value tagged `overstimulation gap` + `factor`
+ `assumed value`, whose change flips `rollback net balance` between a wash and
  a clear win.

1. Write a unit test in `src/test/php/unit_ui/fermi_ui_tests.php` that asserts a
   single result value renders together with its full input chain: build a
   `result_ui` from a `create/test_result::sign_flip_chain_ui()` factory (do not
   repeat the class object name in the factory method), positive test asserts
   the snapshot fragment in `object_pages/fermi_chain.html` shows the result
   number, the formula name, and each input value with its provenance qualifier
   (`measured value` / `assumed value`) visible; negative test asserts an empty
   input chain reports the documented empty result, not just "no exception".
   Reuse `web/value/value.php::value()` and `web/result/result.php::display()`
   for the rendering — do not duplicate their html.

2. Add to `web/result/result.php` a `chain_ui(phrase_list $context, string $back = '')`
   method that returns the html of one result plus the list of input values it
   was computed from, each input rendered via the existing
   `value::value_edit($back)` so the input is already a click-to-edit link. Read
   the "how many inputs to show" limit from `$ui->cfg` (new pod config value,
   never `new config()`); pass `$size`/`$page` as explicit parameters, never
   from superglobals. Single-exit: assemble into one `$result` string and return
   it once. Provenance marker comes from the value's qualifier phrase, taken
   from the `words` group — never from a free-text note field.

3. Extend the value-edit workflow (the `mask`/`confirm` path already sketched in
   the "workflow" block above, the `view.php?mask=...&confirm=1` url) so that on
   confirm of a value that is an INPUT to at least one formula, the affected
   results are recomputed and their new numbers returned in the same response.
   Write the workflow test first in
   `src/test/php/unit_workflow/fermi_recalc_tests.php`: set the `overstimulation
   gap` input, confirm, assert that `rollback net balance` is recomputed to the
   expected reproduced number (use the value from the `calc-validation` block of
   the import fixture as the oracle, so the test fails loudly if the engine and
   the fixture ever diverge). Recompute uses the existing
   `web/formula/expression.php` element evaluation against the backend
   `cfg/formula/formula.php::calc` path — do not write a second evaluator in the
   frontend. On any reproduction mismatch, route a translatable error via `$msg`
   (new `msg_id` case in `messages.php` with en/de text), do not silently show a
   stale number.

4. Add an async (no full page reload) recompute endpoint: a thin arm on the
   existing `api/value` / `api/resultList` controllers that accepts one changed
   input value and returns the recomputed dependent results as json (the api
   json the frontend view-model already consumes). Move any inline SQL out of
   `web/` into prepared, parameterized statements in the model layer (a
   string-concatenated `WHERE ... = $id` is an injection risk). Test first:
   positive asserts the changed input yields the reproduced result number,
   negative asserts an unknown input phrase returns the documented `$msg` error,
   not a DB fallback (`import_mapper` rule: resolve only from the passed-in data
   object, never read the DB to paper over a missing reference).

5. Wire the front-end so editing an `assumed value` input in the `chain_ui`
   posts to the recompute endpoint and swaps in the new result number and its
   sign in place. Keep it to the one chain — no four-column layout, no tab
   switch, no miniature preview (those stay in the generic `word frontend` block
   and are NOT on this critical path). The only visible state that must change
   live is: the edited input number, the dependent result number, and a sign
   indicator (e.g. result styled differently when it crosses zero). Reuse the
   existing user-vs-standard styling (`styles::STYLE_USER`) so a visitor's
   override is visually distinct from the stored standard, mirroring
   `value::value()`.

6. Self-test the loop before inviting anyone: load the imported
   `fermi_discussion.json`, open the `rollback net balance` chain, and change the
   `overstimulation gap` input from 100 down through 30 to 10. Assert (manually
   and as a final workflow test) that the net result moves from ~2.2M HTP toward
   ~59.8M HTP and that the sign indicator changes as the wash becomes a clear
   win. If the change is visible and immediate, the live-adjust loop is done —
   independent of how much of the rest of `pending.md` is finished. If it is not
   immediate, the latency-killing gap is in one of steps 2–5; fix that before
   adding any generic feature.

## review

check first if these prompts are still needed

#### general

design and apply the "write only the changed fields" save flow in one deliberate change (the display side of the phantom 'added view id ""' rows is already fixed by resolving the view name from the cache in change_log_named::value_to_show): a description-only word edit still writes a view assignment because (1) word::view_selector preselects the default view (d=90) for a word without a stored view and fabricates the '8'-prefixed baseline from the same value, (2) ui_preview::popup_changes re-posts every field but drops the '8'-prefixed baselines, so action_crud cannot tell "unchanged" from "chosen" and the backend compares the full posted object against the db row; the fix needs an agreed null convention first: in a save request null must mean "field not carried, keep the stored value" while the user must still be able to set a value back to null with the normal save (e.g. an explicit empty string as the clear request that the write converts to null), and before changing anything the import null handling must be audited (import_mapper maps missing fields to null; the no_update re-import round-trips and sandbox::save fill/get_similar rely on the current compare semantics), then carry the '8' baselines through the confirm submit, drop the unchanged fields before the write, add the matching null guards to db_fields_changed (description, usage, plural, impact, view) plus positive and negative unit tests per field, and check the same pattern for triple, source, view and component edit forms

#### formula

add non latex formula to formula view

add the latex field to the formula add and edit views: latex is the only user-editable formula db
field (formula_db::FLD_LST_USER_CAN_CHANGE) without a form field - the url var (url_var::LATEX
'fx' / 'latex'), the url mapper read (web/formula/formula.php url_mapper) and to_url_array already
transport it, but no 'system form field latex' component exists; create the component (unique
ui_msg_code_id, see docs/llm/json_views.md), add it to the formula_add and formula_edit views in
src/main/resources/messages/system_views.json after the formula text field, and extend the
formula workflow tests (fill_url_array already carries every editable url field) plus the
object_pages snapshots

complete web/formula/formula.php db_fld_to_url: it maps only formula_name, formula_text and
description, so the confirm change preview cannot label a pending latex, formula type or
all_values_needed change with the changed db field; add formula_fields::FLD_LATEX,
formula_fields::FLD_TYPE and formula_fields::FLD_ALL_NEEDED with their url vars and add the
matching positive and negative unit tests (docs/llm/testing.md)

note for the review: the other formula db fields are absent from the edit view by design -
formula_text (internal {f1} expression) is derived from the posted user expression on save,
last_update is set by the calculation, usage is shown read-only as the usage number sub title,
impact and usage overwrites are the separate admin GUI pending entry, and excluded is handled by
the delete workflow
