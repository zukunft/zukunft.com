# save fields func (2026-11-09)

Step 1 "split sandbox::save_fields_func into named writes" of the prompt "deprecate save_fields_func and the *_old
functions" of `docs/llm/pending.md`. The plan asks for one step per commit and a test run after each step, so this
log covers step 1 only; steps 2 to 6 get their own log file. The tables were written before the first code edit;
the line numbers are those of the start state (commit `eb2763b1a` plus the uncommitted row mapper guard change).

## Request

Rule 5 of `docs/llm/coding.md`: every write is one named prepared statement with its change log, created by the sql
creator and covered by a fixture and a unit test. `sandbox::save_fields_func` hides the decision (`can_change`,
`has_usr_cfg`, `no_diff`) and builds the user row update and insert inline, with a database read of the user row in
the middle. It is replaced by the decision in the caller and one named builder per write, each followed directly by
the execution. The table and the sample code of the prompt name each branch with its replacement and its test.

## Expected changes

| file | old lines | new lines | target / reason |
|---|---|---|---|
| `cfg/sandbox/sandbox.php`, `test/php/utils/test_base.php`, `test/php/unit/word_tests.php` | after `sql_exclude` 3867; `assert_sql_save_fields` 2390-2420; sql write update 167 | `sql_update_user`, `sql_insert_user`; same function; after 167 | the two new builders for the user row: the update with the delta to the user row as it is in the database, the insert with the fields that differ from the standard row; `assert_sql_save_fields` calls `sql_insert_user` instead of repeating its lines, and a new `assert_sql_update_user` tests the update with its negative (no changed field, no sql) |
| `cfg/sandbox/sandbox.php`, `cfg/formula/formula_map.php` | `save_fields_func` 2352-2517, the call in `save` 3304, `save_related` 3325; `add` 1679, `save_fields_func` 1708-1747 | `save_standard_row`, `save_user_row`, `sync_code_ids`, the decision in `save`, `save_related`; `add`, `save_related` | the decision `can_change` moves into `sandbox::save`; because `save` is already far over the ~50 lines of a function, each branch is a private function with the existing `sql_delete`, `sql_update`, `sql_exclude` and the two new builders, each followed by its execution; the code id sync of the user branch becomes `sync_code_ids`; the formula word rename of the formula override moves to `formula_map::save_related`, which `save` calls before the writes; `formula_map::add` updates with `sql_update`; both `save_fields_func` are removed |
| `cfg/ref/ref.php` | `save` 1420-1499 | `save` | `ref::save` keeps only the check of the type that is part of the prime index and continues with `parent::save`, so the decision of `sandbox::save` writes the ref too |

## Changes that the expected changes might cause

| file | old lines | new lines | why a change is expected |
|---|---|---|---|
| `cfg/sandbox/sandbox.php` | `save_fields_func` 2407-2411 | the writes in `save` | the delete of the user row after the update of the standard row got the types `[LOG, UPDATE, USER]`, so its query name had the update in it; `sql_delete` with `[LOG, USER]` writes the statement of the fixture of word_tests:273, triple_tests:154, formula_tests:142 and ref_tests:166 |
| `cfg/sandbox/sandbox.php` | `save_fields_func` 2398-2411 | the writes in `save` | if the do not update a link guard refuses the update of the standard row, the user row is kept; so far it was deleted although nothing had been written (the guard reports the refusal on `$msg`) |
| `cfg/sandbox/sandbox.php` | `save_fields_func` 2466-2471 | the writes in `save` | the exclusion of an object for a user with a user row compares with the row of the user (`sql_exclude` with `$db_rec`) instead of the standard row, so only the changed fields are written and not again every earlier overwrite of the user; if the user row of `$db_rec` is missing, `sql_exclude` inserts it |
| `cfg/sandbox/sandbox.php` | `save` 3288 | same comment | the comment names `save_fields_func` as the writer of the user overlay row |
| `cfg/sandbox/sandbox.php`, `cfg/user/user.php` | `update_standard_fields` 2540, 3324 | same doc blocks | the doc blocks say "similar to save_fields_func"; the comparison goes, the rest of the text stays |
| `cfg/formula/formula_map.php` | `add` 1674-1679 | same block | the update after the insert compares with the empty formula of the same name like before; it repeats the fields of the insert, which `pending.md` names as a check: if the insert already writes all fields, the update can be dropped in its own change |
| `cfg/ref/ref.php` | `save` 1457-1468 | removed | a ref found by `get_similar` (e.g. the same external key of an import) was updated with its id; `sandbox::save` takes over its id only if `is_same` (`sandbox_link.php:887`) is true and else reports the id as used: ref_write_tests and import_write_tests show which one happens |
| `cfg/ref/ref.php` | `save` 1477-1495 | removed | `sandbox::save` adds what `ref::save` skips: the permission check, the do not update a link guard (`refuse_link_change`), the protection check and the delete of the old key row; the standard row comes from `get_standard` instead of `load_standard`, which sets the owner that a request object misses from the standard row like `ref::save` did: ref_write_tests and ref_write_url_tests show if one of them refuses a ref write that worked so far |
| `test/php/unit_write/formula_write_tests.php` | after the rename check 457 | new block | the rename of the formula word moves, so a check that the formula word has the new name after the rename is added |
| `test/resources/db/word/` | — | new fixtures for Postgres and MySQL | the user row update of `assert_sql_update_user` has its own query name; the first run reports the fixture as missing and writes it, because `AUTO_UPDATE_TEST_FILES` is on; the developer reviews the two files |
| `test/php/unit_write/a_selected_test.php` | `run` 722, 746 | same lines | switch on ref_write_tests and import_write_tests, because the ref uses the parent save now; word, triple and formula write tests are already switched on |
| `cfg/sandbox/sandbox_multi.php`, `sandbox_value.php`, `test/php/unit/value_tests.php` | the comments that name `save_fields_func` | — | no change: they name the function of `sandbox_multi`, which is step 2 |
| `docs/code_test_coverage.md` | — | — | generated; may change with the new and the removed functions |

## Unexpected changes

| file | old lines | new lines | what and why |
|---|---|---|---|
| `cfg/sandbox/sandbox_link.php` | `is_same` 896-897, `is_key_updated` 1028-1032, `msg_id_already_used` 1347, `is_same_std` 1359-1366 | `is_to_link_key` and the same functions | `reset_db.php` stopped in the import of `units.json` with the fatal "Call to a member function id() on null" in `is_key_updated`: `ref::save` now reaches the key check of `sandbox::save`, which takes the to of every link as an object, but the to of a ref is its external key, a value that a user can overwrite (`ref::link_key_fields`); the to is compared only if `is_to_link_key` says that it is part of the key; the same assumption in `is_same` and `is_same_std` would have reported every ref found by `get_similar` as another link, so an import of an existing ref would have failed with "already exists" instead of updating it like `ref::save` did, and `msg_id_already_used` names the external key of a ref |

## Lessons learned by llm

1. **A class that moves to the parent flow gets every assumption of that flow.** The expected table named the
   checks that `sandbox::save` adds for a ref (permission, link guard, protection, duplicate check), but not that
   the parent path takes the to of every link as an object, which a ref does not have. For the expected table:
   when a class with its own save moves to the parent save, list the functions of the parent path that the class
   does not override and check each one against the data of the class, before the first test.
2. **A rule in a method helps only where it is asked.** `ref::link_key_fields` already said that the to of a ref
   is no key, but only the sql layer used it; the key checks of the save path had their own idea of the key.
   Searching for every check of the same concept (here every use of the to object in a key or duplicate check)
   found `is_same`, `is_same_std` and `msg_id_already_used` next to the reported `is_key_updated`, so one fix
   covered four places and the next run did not have to find the second error.
3. **A sample of a plan is older than the code.** The sample of `pending.md` had no janitor call after the update
   of the user row and an empty check that `sql_update_switch` already does, and it put the decision into `save`,
   which is far over the ~50 lines of a function. Comparing the sample line by line with the current code before
   the first edit kept the behaviour, and a deviation from the plan went into the expected table before the
   first test, so that the log shows it as planned and not as a surprise.
4. **A small step with a plan has few surprises.** One unexpected change (with its three sibling places) against
   23 in the log of the row mapper guard: the step was one of six, the plan named each branch with its
   replacement and its test, and the test selection was reduced to what the step touches.

## Lessons learned by human

Prepared planing works if the code base is more stable and the step is small enough
