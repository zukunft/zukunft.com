# pending - list of planned llm prompts with prio 1

## lesson learned from changes

for each non-trivial and non bug fix change create in docs/llm/log a file 
with a one word (max 3 words) description of the change and the start date
this file should contain 
- the description / prompt of the change
- a table of the wanted and expected changes e.g. in which file which old lines are replaced with which new lines and a short description to the target / reason
- a table which changes might be caused with the filename, the old lines, the new lines and  

## a fresh message for each row of a list mapping and no mapping after an earlier error

`docs/llm/dependent-errors.md`: a mapping does not start if `$msg` already carries an error, returns true only
if it has mapped the row, and each try of an independent entry gets a fresh message that is merged afterwards
and never reads the shared request message. The steps belong into one change, because the guard of step 2
without step 1 would add the rows after a row with an error as empty objects to the list.

1. **Fresh message per row:** `sandbox_list::rows_mapper_obj` (and every other list loop that maps rows) maps
   each row with its own message (with the buffer comment), merges it into `$msg` after the row and adds the
   object to the list only if its mapping has returned true.
2. **No mapping after an earlier error:** `db_object_seq_id::row_mapper` starts with a guard that returns false
   without setting the id if `$msg` is not ok; `sandbox::row_mapper_sandbox` returns true only if a row has
   been mapped (id set and `$msg` ok), so its docblock and the comment about the half mapped object change.
3. **`if ($result)` in the child mappers:** `component.php`, `component_link.php`, `formula_link.php` and
   `formula_map.php` continue with `if ($result)` after the parent mapper instead of `if ($this->id() != 0)`
   and drop the "TODO Prio 1 can result be false" line.
4. **Not saved, not added:** an entry whose mapping has returned false is neither saved nor added to a list of
   objects that are saved; check the import and the save path for callers that continue with such an entry.
5. **Tests:** add a test to `formula_link_tests`, `term_view_tests` and `formula_tests` that a row with an
   earlier error on the message is not mapped at all (result false, id 0) and its negative with a clean
   message; a list test checks that the row after a row with a mapping error is mapped completely and that
   the row with the error is not added.

if done remove this prompt from pending.md

**next prompt**

**full test run: /test/test.php**

## deprecate save_fields_func and the *_old functions

Rule 5 of docs/llm/coding.md: every write is one named prepared statement with its change log, created by the
sql creator and covered by a fixture in `src/test/resources/db/<class>/` and a unit test. Do one step per commit,
in this order, and ask the developer to run the unit and write tests after each step.

Each `save_fields_func` call is replaced by the decision (`can_change`, `has_usr_cfg`, `no_diff`) in the caller
and one named builder per write, followed directly by the execution, like the change log insert:

```php
$qp = $this->sql_insert_log($sc, $msg);
$db_con->insert($qp, 'insert log link ' . $this->dsp_id(), $msg);
```

### save_fields_func locations, callers and replacements

`$sc = $db_con->sql_creator()`, `$norm` is the standard row (`$std_rec`), `$db` the row before the change
(`$db_rec`), `[LOG, USER]` is short for `new sql_type_list([sql_type::LOG, sql_type::USER])`.
"new" marks a builder that does not exist yet; "test" names the unit test line with its fixture assert.

| where in the code                                                    | planned change                                                       | called from                                                                     | `$qp = ...` replacement                                                                                                                                                                                                                        | sql tested in                                                                                                                                                                                                                                                                                                    |
|----------------------------------------------------------------------|----------------------------------------------------------------------|---------------------------------------------------------------------------------|------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `sandbox.php:2358` definition                                        | remove the function, the decision moves into `sandbox::save`         | update of an existing object after the permission, link and rename checks: `sandbox::save` (`sandbox.php:3296`); formula save or add: `formula_map::save_fields_func` (parent) | removed, the branches below move into `sandbox::save`                                                                                                                                                                                          | `assert_sql_save_fields` (`test_base.php:2349`) mirrors only the user row insert                                                                                                                                                                                                                                 |
| `sandbox.php:2384` norm, no diff: delete user row                    | use the existing `sql_delete` with log and user                      | update of an existing object: `sandbox::save`                                   | `$qp = $this->sql_delete($sc, $msg, [LOG, USER]);` then `$db_con->delete($qp, ...)`                                                                                                                                                            | word_tests:273, triple_tests:154, formula_tests:142, ref_tests:166 (`assert_sql_delete` with `[LOG, USER]`)                                                                                                                                                                                                      |
| `sandbox.php:2393` norm: update standard row                         | use the existing `sql_update` with log instead of `sql_update_switch` | update of an existing object: `sandbox::save`                                   | `$qp = $this->sql_update($sc, $db, $msg, [LOG]);` then `if ($qp != null) $db_con->update($qp, ...)`; `sql_update` adds the `can_update` check that the direct `sql_update_switch` call skips                                                   | word_tests:171, 195, triple_tests:133, formula_tests:134, ref_tests:146 (`assert_sql_update` with `[LOG]`)                                                                                                                                                                                                       |
| `sandbox.php:2401` norm updated: delete user row                     | use the existing `sql_delete` with log and user                      | update of an existing object: `sandbox::save`                                   | `$qp = $this->sql_delete($sc, $msg, [LOG, USER]);` (check that the query name has no `UPDATE` left from the update before)                                                                                       | same as `sandbox.php:2384`                                                                                                                                                                                                                                                                                       |
| `sandbox.php:2452` user, no diff: delete user row                    | use the existing `sql_delete` with log and user                      | update of an existing object: `sandbox::save`                                   | `$qp = $this->sql_delete($sc, $msg, [LOG, USER]);`                                                                                                                                                                                             | same as `sandbox.php:2384`                                                                                                                                                                                                                                                                                       |
| `sandbox.php:2463` user row exists, excluded: update user row        | use the existing `sql_exclude`                                       | update of an existing object: `sandbox::save`                                   | `$qp = $this->sql_exclude($sc, $db, $msg);` (exists; compares with the user row, the branch compares with `$norm`: check which row gives the right fields)                                                                                                     | word_tests:277, triple_tests:158 (`assert_sql_exclude`); not for formula, ref, source, view, component                                                                                                                                                                                                           |
| `sandbox.php:2477` user row exists: update user row with the delta   | add `sql_update_user`, the delta and the user row load stay in the caller | update of an existing object: `sandbox::save`                                   | new `$qp = $usr_chg->sql_update_user($sc, $usr_db, $msg);` with `$usr_chg = $this->delta($norm, $this->clone_reset(true))` and `$usr_db->load_user_changes(...)` in the caller (no db read inside a builder)                                   | word_tests:167, 190, triple_tests:132, 134, formula_tests:135, ref_tests:147 (`assert_sql_update` with `[LOG, USER]`, but without the delta step)                                                                                                                                                                |
| `sandbox.php:2497` no user row: insert user row with the delta       | add `sql_insert_user` that writes the fields changed vs the standard row | update of an existing object: `sandbox::save`                                   | new `$qp = $this->sql_insert_user($sc, $norm, $msg);` (`[LOG, USER, INSERT, NO_ID_RETURN]`, fields changed vs `$norm`); then `$db_con->insert($qp, ..., new sql_message(), true)` and `set_uses_sandbox`                                       | word_tests:155, triple_tests:122 (`assert_sql_save_fields`); not tested for formula, ref, source, view, component (`assert_sql_insert` with `[LOG, USER]` compares with an empty object, not with `$norm`)                                                                                                       |
| `formula_map.php:1722` override                                      | remove the override, the formula word rename moves to `save_related` | update of an existing formula: `sandbox::save` (`sandbox.php:3296`); new formula after its insert and its formula word: `formula_map::add` (`formula_map.php:1679`) | the rename of the formula word (`wrd_rename`) moves to `formula_map::save_related`, called by `sandbox::save` before the writes; no own sql                                                                                                                                  | no sql; the formula rename in formula_write_tests:449, the formula word name is not checked                                                                                                                                                                                                                                                         |
| `formula_map.php:1679` call                                          | drop the call if the insert writes all fields, else `sql_update`     | new formula after its insert with log and its formula word: `formula_map::add`  | `$qp = $this->sql_update($sc, $db_rec, $msg, [LOG]);` with the empty `$db_rec`; first check if the insert at `formula_map.php:1660` already writes all fields, then the call can be dropped                                                    | formula_tests:134 (`[LOG]`), the insert in formula_tests:125                                                                                                                                                                                                                                                      |
| `ref.php:1478` call                                                  | `ref::save` uses the parent `sandbox::save` for the update           | update of an existing ref: `ref::save`                                          | the same branches as `sandbox.php:2384` to `2497`, called from `sandbox::save` once `ref::save` uses the parent save                                                                                                                           | ref_tests:146, 147, 166; user row insert vs `$norm` not tested                                                                                                                                                                                                                                                   |
| `sandbox_multi.php:3591` definition                                  | remove the function, the decision moves into the callers             | update of an existing group: `sandbox_multi::save` (`:3366`, result has its own save without this call); delete of a value or result that another user uses: `sandbox_multi::del` (`:3568`); update of an existing value: `value_base::save_for_user` (`value_base.php:2375`); update of an existing time series: `value_time_series::save` (`value_time_series.php:425`) | removed, the branches below move into the update callers, `del` writes only the user row                                                                                                                                                    | -                                                                                                                                                                                                                                                                                                                |
| `sandbox_multi.php:3617` norm, no diff: delete user row              | use the existing `sql_delete_user_row` with log                      | see definition                                                                  | `$qp = $db->sql_delete_user_row($sc, $msg, [LOG]);` (exists)                                                                                                                                                                                   | value_tests:1021, 1026, 1028 (`assert_sql_delete` `[LOG, USER]`), value_tests:573 (source key); result only without log (result_tests:201, 204)                                                                                                                                                                 |
| `sandbox_multi.php:3627` norm: update standard row                   | use the existing `sql_update` with log instead of `sql_write`        | see definition                                                                  | `$qp = $this->sql_update($sc, $db, $msg, [LOG]);` (`sandbox_value::sql_update` wraps the same `sql_write`); then `report_empty_write` on null                                                                                                  | value_tests:846, 853, 855, 859 (`[LOG]`); result only without log (result_tests:189 to 197)                                                                                                                                                                                                                      |
| `sandbox_multi.php:3636` norm updated: delete user row               | use the existing `sql_delete_user_row` with log                      | see definition                                                                  | `$qp = $this->sql_delete_user_row($sc, $msg, [LOG]);`                                                                                                                                                                                          | same as `sandbox_multi.php:3617`                                                                                                                                                                                                                                                                                 |
| `sandbox_multi.php:3651` user, no diff: delete user row              | use the existing `sql_delete_user_row` with log                      | see definition                                                                  | `$qp = $db->sql_delete_user_row($sc, $msg, [LOG]);`                                                                                                                                                                                            | same as `sandbox_multi.php:3617`                                                                                                                                                                                                                                                                                 |
| `sandbox_multi.php:3664` user row exists: update user row            | add `sql_update_user` for values and results                         | see definition                                                                  | new `$qp = $this->sql_update_user($sc, $db, $msg);` (`set_user_table_source`, `[LOG, USER, UPDATE]`, null if only key fields differ)                                                                                                          | value_tests:847, 854, 856 (`[LOG, USER]`), value_tests:502 to 518 (no update without set clause); result only without log (result_tests:191, 198)                                                                                                                                                               |
| `sandbox_multi.php:3676` no user row: insert user row                | add `sql_insert_user` for values and results                         | see definition                                                                  | new `$qp = $this->sql_insert_user($sc, $norm, $msg);` (`set_user_table_source`, `[LOG, USER, INSERT, NO_ID_RETURN]`); the branch passes `$db` to `sql_write` while its comment names the norm row: decide which one is right                           | not tested as such: value_tests:818, 822 (`assert_sql_insert` `[LOG, USER]`, without `NO_ID_RETURN` and vs an empty row), value_tests:494 (excluded field in the insert)                                                                                                                                       |
| `sandbox_multi.php:3568` call in `del`                               | write the user row with `sql_update_user` or `sql_insert_user`      | delete of a value or result that another user uses: `sandbox_multi::del`        | `$qp = $this->sql_update_user($sc, $db, $msg);` if the user row exists, else `$qp = $this->sql_insert_user($sc, $db, $msg);`                                                                                                                 | value_tests:1022 (`assert_sql_delete` `[USER, EXCLUDE]`), value_tests:494; a test of the excluded user row update and insert is missing                                                                                                                                                                              |
| `value_base.php:2375` call                                           | use the `sandbox_multi` branches directly                            | update of an existing value: `value_base::save_for_user` from `value_base::save` (`value_base.php:2258`) | the branches of `sandbox_multi.php:3617` to `3676`                                                                                                                                                                                             | see the `sandbox_multi` rows                                                                                                                                                                                                                                                                                     |
| `value_time_series.php:425` call                                     | use the `sandbox_multi` branches directly and add a write test       | update of an existing time series: `value_time_series::save`                    | the branches of `sandbox_multi.php:3617` to `3676`                                                                                                                                                                                             | not tested: no sql write fixture for `value_time_series` (only the log delete in change_log_tests:486)                                                                                                                                                                                                           |

#### sample code changes for `sandbox.php:2358` definition: remove the function, the decision moves into `sandbox::save`

The line numbers are those of the file before the change.

add at `sandbox.php:3830`, after `sql_exclude`:

```php
    /**
     * create the sql statement to update the user row with the delta of this object to the standard row
     *
     * @param sql_creator $sc with the target db_type set
     * @param sandbox $usr_db the user row as it is in the database
     * @param user_message $msg the user message object that collects any issues during the sql creation
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation e.g. with log
     * @return sql_par|null the sql statement or null if no field of the user row has changed
     */
    function sql_update_user(
        sql_creator   $sc,
        sandbox       $usr_db,
        user_message  $msg,
        sql_type_list $sc_par_lst = new sql_type_list([sql_type::LOG])
    ): sql_par|null
    {
        $par_lst = clone $sc_par_lst;
        $par_lst->add(sql_type::USER);
        $par_lst->add(sql_type::UPDATE);
        $fvt_lst = $this->db_fields_changed($usr_db, $msg, $par_lst);
        if ($fvt_lst->is_empty_except_internal_fields()) {
            return null;
        }
        return $usr_db->sql_update_switch($sc, $fvt_lst, $this->db_fields_all(), $msg, $par_lst);
    }

    /**
     * create the sql statement to insert the user row with the fields that differ from the standard row
     *
     * @param sql_creator $sc with the target db_type set
     * @param sandbox $norm the standard row
     * @param user_message $msg the user message object that collects any issues during the sql creation
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation e.g. with log
     * @return sql_par the sql statement to insert the user row
     */
    function sql_insert_user(
        sql_creator   $sc,
        sandbox       $norm,
        user_message  $msg,
        sql_type_list $sc_par_lst = new sql_type_list([sql_type::LOG])
    ): sql_par
    {
        $par_lst = clone $sc_par_lst;
        $par_lst->add(sql_type::USER);
        $par_lst->add(sql_type::INSERT);
        $par_lst->add(sql_type::NO_ID_RETURN);
        $fvt_lst = $this->db_fields_changed($norm, $msg, $par_lst);
        return $this->sql_insert_switch($sc, $fvt_lst, $this->db_fields_all(), $msg, $par_lst);
    }
```

add at `sandbox.php:2348`, in place of `save_fields_func`: the code id sync of `sandbox.php:2419` to `2447`
unchanged as `private function sync_code_ids(sandbox $norm): void` (`$norm_obj` renamed to `$norm`).

add at `sandbox.php:3296`, in place of the `save_fields_func` call:

```php
                        $sc = $db_con->sql_creator();
                        $obj_name = new library()->class_to_name($this::class);
                        $usr_par_lst = clone $sc_par_lst;
                        $usr_par_lst->add(sql_type::USER);
                        if ($this->can_change($msg)) {
                            // the user may change the standard row, so a user row is not needed
                            $no_diff = $this->no_diff($std_rec, $msg, $sc_par_lst);
                            // null e.g. if the do not update a link guard has refused the update
                            $qp = $no_diff ? null : $this->sql_update($sc, $db_rec, $msg, $sc_par_lst);
                            if ($qp != null) {
                                $db_con->update($qp, 'update ' . $obj_name . ' ' . $this->dsp_id(), $msg);
                            }
                            if ($this->has_usr_cfg() and ($no_diff or $qp != null)) {
                                $qp = $this->sql_delete($sc, $msg, $usr_par_lst);
                                $db_con->delete($qp, 'remove user overwrites of ' . $this->dsp_id(), $msg);
                            }
                        } else {
                            $this->sync_code_ids($std_rec);
                            if ($this->has_usr_cfg()) {
                                if ($this->no_diff($std_rec, $msg, $usr_par_lst)) {
                                    $qp = $this->sql_delete($sc, $msg, $usr_par_lst);
                                    $db_con->delete($qp, 'remove user overwrites of ' . $this->dsp_id(), $msg);
                                } else {
                                    if ($this->is_excluded()) {
                                        $qp = $this->sql_exclude($sc, $db_rec, $msg);
                                    } else {
                                        $usr_db = $this->clone_reset(true);
                                        $usr_db->load_user_changes($this->id(), $msg);
                                        $usr_chg = $this->delta($std_rec, $this->clone_reset(true));
                                        $qp = $usr_chg->sql_update_user($sc, $usr_db, $msg, $sc_par_lst);
                                    }
                                    if ($qp != null) {
                                        $db_con->update($qp, 'update user ' . $obj_name, $msg);
                                    }
                                }
                            } elseif (!$this->no_diff($std_rec, $msg, $usr_par_lst)) {
                                $qp = $this->sql_insert_user($sc, $std_rec, $msg, $sc_par_lst);
                                if ($db_con->insert($qp, 'add user ' . $obj_name, $msg, new sql_message(), true)) {
                                    // the pages for this user must now be created from the user sandbox
                                    $this->get_user()->set_uses_sandbox($msg);
                                }
                            }
                        }
                        if ($msg->is_ok()) {
                            // remove the user rows that have no overwrite left
                            $this->del_usr_cfg_if_not_needed($msg);
                        }
```

add at `test_base.php:2349`, in `assert_sql_save_fields` for both dialects:

```php
        $qp = $usr_obj->sql_insert_user($sc, $norm_obj, $msg, new sql_type_list($sc_par_lst_in));
```

del at `sandbox.php:2348` to `2509`: the doc block and the body of `save_fields_func`.

del at `test_base.php:2357` to `2362`: the "prepare like in save_fields_func" lines of `assert_sql_save_fields`.

Check after the change: the fixtures of word_tests:155 and triple_tests:122 are unchanged; the excluded user row
uses `sql_exclude` with `$db_rec` (see the table); if the update is refused by the link guard, the user row is kept.

#### sample code changes for `sandbox_multi.php:3591` definition: remove the function, the decision moves into the callers

The line numbers are those of the file before the change. The two builders have the same signature as in
`sandbox`, so `assert_sql_save_fields` works for both.

add at `sandbox_multi.php:3738`, after `sql_delete_user_row`:

```php
    /**
     * create the sql statement to update the user row of this value, result or group
     *
     * @param sql_creator $sc with the target db_type set
     * @param sandbox_multi $usr_db the user row as it is in the database
     * @param user_message $msg collect the messages for the user
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation e.g. with log
     * @return sql_par|null the sql statement or null if only the key fields differ
     */
    function sql_update_user(
        sql_creator   $sc,
        sandbox_multi $usr_db,
        user_message  $msg,
        sql_type_list $sc_par_lst = new sql_type_list([sql_type::LOG])
    ): sql_par|null
    {
        $this->set_user_table_source();
        $par_lst = clone $sc_par_lst;
        $par_lst->add(sql_type::USER);
        // an update of only the key fields would be an sql without a set clause
        if ($this->db_fields_changed($usr_db, $msg, $par_lst)->is_empty_except_internal_fields()) {
            return null;
        }
        $par_lst->add(sql_type::UPDATE);
        return $this->sql_write($sc, $usr_db, $this->db_fields_all(), $msg, $par_lst);
    }

    /**
     * create the sql statement to insert the user row of this value, result or group
     *
     * @param sql_creator $sc with the target db_type set
     * @param sandbox_multi $db_row the row that the user row is compared with
     * @param user_message $msg collect the messages for the user
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation e.g. with log
     * @return sql_par|null the sql statement or null if only the key fields differ
     */
    function sql_insert_user(
        sql_creator   $sc,
        sandbox_multi $db_row,
        user_message  $msg,
        sql_type_list $sc_par_lst = new sql_type_list([sql_type::LOG])
    ): sql_par|null
    {
        $this->set_user_table_source();
        $par_lst = clone $sc_par_lst;
        $par_lst->add(sql_type::USER);
        $par_lst->add(sql_type::INSERT);
        $par_lst->add(sql_type::NO_ID_RETURN);
        return $this->sql_write($sc, $db_row, $this->db_fields_all(), $msg, $par_lst);
    }
```

add at `sandbox_multi.php:3366` (group), `value_base.php:2375` (value) and `value_time_series.php:425`
(time series), each in place of the `save_fields_func` call:

```php
                        $sc = $db_con->sql_creator();
                        $obj_name = new library()->class_to_name($this::class);
                        $log_lst = new sql_type_list([sql_type::LOG]);
                        if ($this->can_change()) {
                            // the user may change the standard row, so a user row is not needed
                            $no_diff = $this->no_diff($std_rec, $msg);
                            $qp = $no_diff ? null : $this->sql_update($sc, $db_rec, $msg, $log_lst);
                            if ($qp != null) {
                                $db_con->update($qp, 'update ' . $obj_name . ' ' . $this->dsp_id(), $msg);
                            } elseif (!$no_diff) {
                                $this->report_empty_write('update', $msg);
                            }
                            if ($this->has_usr_cfg() and ($no_diff or $qp != null)) {
                                // logged with the user row as stored, so that the log shows the number that the user had
                                $qp = $db_rec->sql_delete_user_row($sc, $msg, $log_lst);
                                $db_con->delete($qp, 'remove user overwrites of ' . $this->dsp_id(), $msg);
                            }
                            if ($msg->is_ok()) {
                                // remove the user rows that have no overwrite left
                                $this->del_usr_cfg_if_not_needed($msg);
                            }
                        } elseif ($db_rec->has_usr_cfg()) {
                            if ($this->no_diff($std_rec, $msg)) {
                                $qp = $db_rec->sql_delete_user_row($sc, $msg, $log_lst);
                                $db_con->delete($qp, 'remove user overwrites of ' . $this->dsp_id(), $msg);
                            } else {
                                // null e.g. if a leftover user row already matches the request
                                $qp = $this->sql_update_user($sc, $db_rec, $msg);
                                if ($qp != null) {
                                    $db_con->update($qp, 'update user ' . $obj_name, $msg);
                                }
                            }
                        } elseif (!$this->no_diff($std_rec, $msg)) {
                            $qp = $this->sql_insert_user($sc, $db_rec, $msg);
                            if ($qp != null) {
                                $db_con->insert($qp, 'add user ' . $obj_name, $msg, new sql_message(), true, true);
                            } else {
                                $this->report_empty_write('user overwrite', $msg);
                            }
                        }
```

add at `sandbox_multi.php:3568`, in `del` in place of the `save_fields_func` call: the exclude is always a write
of the user row, because the object is used by another user

```php
                            $sc = $db_con->sql_creator();
                            if ($db_rec->has_usr_cfg()) {
                                $qp = $this->sql_update_user($sc, $db_rec, $msg);
                                if ($qp != null) {
                                    $db_con->update($qp, 'exclude ' . $this->dsp_id(), $msg);
                                }
                            } else {
                                $qp = $this->sql_insert_user($sc, $db_rec, $msg);
                                if ($qp != null) {
                                    $db_con->insert($qp, 'exclude ' . $this->dsp_id(), $msg, new sql_message(), true, true);
                                }
                            }
```

add at `sandbox_multi.php:3818`: `report_empty_write` becomes `protected`, because `value_base` and
`value_time_series` call it.

add at `value_tests.php:854`, with a new fixture for Postgres and MySQL:

```php
        $t->assert_sql_save_fields($sc, $val_16, $db_val_16, [sql_type::LOG]);
```

del at `sandbox_multi.php:3580` to `3690`: the doc block and the body of `save_fields_func`.

Check after the change:
- the block is written three times (group, value, time series); if `value_base::save_for_user` and
  `value_time_series::save` can use `sandbox_multi::save` for the update, it is written once
- the user row delete after the update of the standard row uses `$db_rec` like the no diff case, the function
  uses `$this` there: check that the logged number is the one the user had
- `del` writes only the user row; the function updates the standard row if no median user is found and the
  deleting user is still the owner, check that this case is meant to exclude only for the user

#### sample code changes for `formula_map.php:1722` override: remove the override, the formula word rename moves to `save_related`

The line numbers are those of the file before the change. `formula` has no own `save`, so the rename uses the
related object hook `sandbox::save_related` (`sandbox.php:3317`, so far not called): the formula word is the
related object of the formula, and the hook gets the database row to see the old name.

add at `sandbox.php:3317`, in place of the `save_related` signature:

```php
    function save_related(user_message $msg, ?sandbox $db_rec = null): bool
```

add at `sandbox.php:3296`, before the writes of the `sandbox.php:2358` sample:

```php
                        // e.g. rename the formula word together with the formula
                        $this->save_related($msg, $db_rec);
```

and wrap the writes in `if ($msg->is_ok()) { ... }`, so that a failed related save writes nothing.

add at `formula_map.php:1712`, in place of the `save_fields_func` override:

```php
    /**
     * rename the formula word if the formula has been renamed
     *
     * @param user_message $msg to enrich with warnings, problems and solutions
     * @param sandbox|null $db_rec the formula as it is in the database before the change
     * @return bool true if everything has been successful
     */
    function save_related(user_message $msg, ?sandbox $db_rec = null): bool
    {
        if ($db_rec != null and $db_rec->name() <> $this->name()) {
            if (!$this->wrd_rename($db_rec->name(), $msg)) {
                $msg->add(msg_id::FORMULA_WORD_RENAME_FAILED, [
                    msg_id::VAR_FORMULA => $db_rec->name(),
                    msg_id::VAR_NAME => $this->name()
                ]);
            }
        }
        return $msg->is_ok();
    }
```

add at `formula_map.php:1679`, in `add` in place of the `save_fields_func` call (the new formula has the name of
`$db_rec`, so no rename is needed):

```php
                $qp = $this->sql_update($sc, $db_rec, $msg, new sql_type_list([sql_type::LOG]));
                if ($qp != null) {
                    $db_con->update($qp, 'update and log ' . $this->dsp_id(), $msg);
                }
```

add at `formula_write_tests.php:464`: a check that the formula word has the new name after the rename.

del at `formula_map.php:1712` to `1747`: the doc block and the body of the `save_fields_func` override.

Check after the change:
- the update in `add` compares with an empty formula, so it writes and logs the fields of the insert again;
  if the insert at `formula_map.php:1659` already writes all fields, drop these lines instead (see the table)
- the rename of the formula word runs before the formula writes, like in the override; if the formula write fails,
  the word keeps the new name

#### sample code changes for `ref.php:1478` call: `ref::save` uses the parent `sandbox::save` for the update

The line numbers are those of the file before the change. `ref` extends `sandbox_link`, so `sandbox::save` with
the writes of the `sandbox.php:2358` sample covers the check, the reload of the linked objects, the duplicate
check, the add and the update; `ref::save` keeps only the check of the type that is part of the prime index.

add at `ref.php:1409`, in place of the doc block of `save`:

```php
    /**
     * add or update a ref in the database
     * the type is checked upfront, because it is part of the prime index
     *
     * @param user_message $msg the message object that is enriched in case something went wrong to show the user the problem and the suggested solutions
     * @param sql_type_list|array $sc_par_lst the parameters for the sql statement creation
     * @return bool true if everything has been fine
     */
```

add at `ref.php:1436`, after the type check, in place of the rest of the body:

```php
        return parent::save($msg, $sc_par_lst);
```

del at `ref.php:1436` to `1482`: from the preserved name check to the end of the update branch, including the
`save_fields_func` call and the `global $db_con` that is no longer used.

Check after the change:
- a ref found by `get_similar` (e.g. the same external key of an import) was updated with its id; `sandbox::save`
  updates it only if `is_same` (`sandbox_link.php:887`) is true and else reports the id as used: check with
  ref_write_tests and import_write_tests
- `sandbox::save` adds what `ref::save` skips: the permission check, the owner of the standard row, the do not
  update a link guard (`refuse_link_change`), the protection check and the delete of the old key row; check that
  ref_write_tests still passes with them
- the standard row comes from `get_standard` instead of `load_standard`: check that the owner is set, which the
  TODO in `ref::save` names as missing

#### sample code changes for `value_time_series.php:425` call: use the `sandbox_multi` branches directly and add a write test

The line numbers are those of the file before the change. `value_time_series` extends `sandbox_value`, so it uses
the `sql_update`, `sql_update_user`, `sql_insert_user` and `sql_delete_user_row` builders of the
`sandbox_multi.php:3591` sample; `library`, `sql_message`, `sql_type` and `sql_type_list` are already imported.

add at `value_time_series.php:420`, in place of the `save_fields_func` call and its comments (`420` to `426`):

```php
            if ($msg->is_ok()) {
                // the same lines as at sandbox_multi.php:3366 in the sandbox_multi.php:3591 sample,
                // from $sc = $db_con->sql_creator(); to the insert of the user row
            }
```

add at `value_tests.php:1220`, after the by group query, with new fixtures for Postgres and MySQL:

```php
        // sql to update the standard row and to insert the user row of a time series
        $vts_excl = clone $vts;
        $vts_excl->exclude();
        $t->assert_sql_update($sc, $vts_excl, $vts, [sql_type::LOG]);
        $t->assert_sql_save_fields($sc, $vts_excl, $vts, [sql_type::LOG]);
```

del at `value_time_series.php:416` to `418`: the commented out `save_id_if_updated` call and its TODO, because the
id of a time series is the group and the key change is not part of this step.

Check after the change:
- the user table of the time series is listed in `def.php:849`, but `db_check.php:474` still names its creation
  as TODO: check that `user_value_time_series` exists before the user row branches can be reached
- `assert_sql_standard` of the time series is not yet active (value_tests:1214), so the load of `$std_rec` that
  the decision uses is not tested either
- the add (`value_time_series.php:345`) does not save the data list yet (TODO Prio 1), so a write test can only
  check the time series row

#### sample code changes for `value_base.php:2375` call: use the `sandbox_multi` branches directly

The line numbers are those of the file before the change. `value_base` extends `sandbox_value`, so it uses the
builders of the `sandbox_multi.php:3591` sample; `set_user_table_source` of `value_base` sets the source of the
user row inside `sql_update_user`, `sql_insert_user` and `sql_delete_user_row`. `save_as_default`
(`value_base.php:2418`) already writes this way with `sql_update` and `$db_con->update`.

add at `value_base.php:2374`, in place of the `save_fields_func` call and its comment (`2374` to `2375`), after
`check_protection_change`:

```php
                    // the same lines as at sandbox_multi.php:3366 in the sandbox_multi.php:3591 sample,
                    // from $sc = $db_con->sql_creator(); to the insert of the user row
```

add at `value_tests.php:856`, after the user row updates, with new fixtures for Postgres and MySQL:

```php
        // the user row of a value without own source is written with the placeholder source
        $val_no_src = $t_val->value_16();
        $val_no_src->set_source(null);
        $t->assert_sql_save_fields($sc, $val_no_src, $db_val_16, [sql_type::LOG]);
```

add at `value_write_tests.php:423` to `424`: activate the check that the change of the user row is logged, because the
writes now always include the log.

del at `value_base.php:2338`: the TODO to change the default value if no one else has changed it,
because `can_change` in the decision does this.

Check after the change:
- the standard row is loaded with its own message `$std_msg` that is not merged, so a failed load lets the
  decision compare with an empty standard row; merge `$std_msg` or stop if the standard row is missing
- the open point of the `sandbox_multi.php:3676` row matters most here: the insert of the user row compares with
  `$db_rec`, which for a value without source has the placeholder source (value_tests:508 to 518)
- the write tests for the user row are value_write_tests:407 (insert of the overwrite of `usr2`) and :440 (the
  undo deletes the user row)

### step 1: split sandbox::save_fields_func into named writes

Add the two new builders `sql_update_user` and `sql_insert_user` with a fixture for Postgres and MySQL
and close the test gaps of the `sandbox` rows in the table. Then `sandbox::save`, `ref::save` and
`formula_map::add` use the replacements of the table, the rename moves to `formula_map::save_related`, and
`sandbox::save_fields_func` and its override are removed.

### step 2: the same for sandbox_multi (values and results)

Add `sql_update_user` and `sql_insert_user` to `sandbox_multi` with fixtures for each value
and result table (`value_db::TBL_LIST`, `result_db::TBL_LIST`) and close the test gaps of the `sandbox_multi`
rows in the table. Then the four callers use the replacements of the table and
`sandbox_multi::save_fields_func` is removed. Check that no `*_fields_func` is left (docs/llm/coding.md rule 5).

### step 3: replace sql_db::get_old by named prepared statements

`get_old` runs a raw sql text. Its callers each get a `load_sql_*` builder with a fixture and use `get()`:
`cfg/value/value_list.php`, `cfg/group/group_list.php`, `cfg/result/result.php`,
`cfg/log/change_log_link_list.php` and `web/value/value.php` (the frontend reads through the api instead).
Then remove `sql_db::get_old`.

### step 4: remove sql_creator::where_old

`where()` falls back to `where_old` if no parameter has been added with `add_where_par`. Log the builders that
still reach `where_old` during a full test run, switch them to `add_where_par` (the fixtures must not change),
then remove `where_old` and the fallback.

### step 5: remove the unused legacy functions

Without a caller in src: `result_list::load_by_obj` with `load_sql_by_obj_old`, `change::load_sql_old`,
`phrase_list::time_lst_old`, `expression::element_part_list_old`, `word::calc_usage_old` and
`library::str_diff_old`. Check each again for a caller (also by a string or a dynamic call) before removing it.

### step 6: the frontend *_old functions

`html_base::url_old` (12 callers in web/ and base_ui_tests) moves to `html_base::url` or the `url()` of the
object; `result_list::display_old` and `formula_list::display_old` (web), `element_group::dsp_values_old` and
`db_object::phrase_selector_old` move to their current replacements. The html snapshots must not change.

### not part of this plan

These names mean the value before a change or an age, not legacy code, and stay:
`sql_par_field_list::get_old`, `sandbox::sql_key_fields_text_old` and `sql_key_fields_id_old` (also in
`sandbox_link`), `user_log::load_sql_old` (the ended sessions) and `job_db_cleanup::is_old`.

if done remove this prompt from pending.md

## wikipedia link

if a user is logged in and on a page like this http://localhost/http/view.php?m=4&id=139
the refresh icon right of wikipedia is pressed the corresponding page from wikipedia should be 
received and the first part of the description should be taken from wikipedia without pronouciation and links
and used as the description . so the result should be that the change confirm page is shown with the adjusted 
description.
Additional to the description the wikidata link should be taken from the wikipedia page and if needed included in the update

create a unit test based on the city of geneva, so load the wikipedia page once and write it to a html file 
that will not be changed any more and from this extract the description and the wikidata link as a unit test.

check if the external key of a wikipedia ref should be the url slug e.g. `Canton_of_Zurich` or the title
as shown on the screen e.g. `Canton of Zurich`. The import files use the title for now
(see the one external key rule in docs/llm/json_structure.md), so check that the link built from the key opens
the right page with both forms, decide on one, and update the rule and the import files to it.

## language

### import sample

make here a plan how to add other languages. this should include

allow in import json files to import a translation for where a name is used a translation section like this 

`"words": [
    {
        "name": "problem",
        "translations": [
            "de": {
                  {
                    "name": "Problem",
                    "description": "Ein Problem ist ..."
                  }
             }
        ]
    },
`

where 'translations' is the json field name and 'de' is the wikipedia_code as saved in the table languages

add the translations to src/main/resources/messages/solution_prio.json

add how translations can be imported to json_structure.md

import the translation and save it to user_words. define how the standard translation should be saved. 
If possible without a user or a default user or the owner of the related object.

add the translations to the read queries but only if needed, so probably almost all sql statements will be dublicated with an '*_lan' extentions. 
So additional to src/test/resources/db/word/word_by_id.sql there will be a word_by_id_lan.sql file which loads the word in a given language

apply the translation to all objects that have language specific fields

add the translations to the export and check where changes due to translations might be useful 
e.g. test if the Calculator Editor can be used with translated data including write tests of translation changes  

and finally show the translated page if the url has something link =de

## this document

**How to use this file:** each block from a `**Prompt:**` line to the next `**next prompt**` marker is one multi agent
prompt together with the sections it names, which follow the block. Every prompt follows the same rules:
- see `/docs/llm/coding.md`; the lead reads the sections of the prompt, checks first which part is already done and
  removes it from this file, asks the developer the decisions that the sections name, starts the agents of a phase in
  parallel and waits for all of them before the next phase
- a phase that says "stop for a run" ends with the lead asking the developer for the named test run; the lead continues
  with the next phase only after the developer has reported the result
- each agent gets the prompt, the sections of its task and the result of the earlier phases as context, changes only
  the files of its task (a change that another agent of the same phase owns goes as a note to the lead), uses
  Edit/Write only, never commits, never runs a `/test/*` script and reports the changed files and the open points
- in the end the lead reviews the whole change as one (one style, no doubled code, lint clean), keeps every open point
  here or moves it to `docs/llm/pending_prio_2.md`, removes the done sections and reports the id shift, the tests to
  run and a short commit message
- the size is S (one agent, a few files), M (two or three agents) or L (four or more agents or several phases)
- the marker after `**next prompt**` is the test run of the developer before the next prompt:
  - `**full test run: /test/test.php**` if no seed data changes
  - `**full test run with re-pin: first /test/reset_db.php, then at least twice /test/test.php**` if a json of the
    seed data or the database structure changes, so that the ids shift; the first `/test/test.php` with
    `files::AUTO_RE_PIN_OBJECT_ID` and `files::AUTO_UPDATE_TEST_FILES` true, the last with both false

**Prompt:** calculator editor JSON tab, Swiss NPP and the prime group id limit (size L; re-pin, because the NPP data
gets the CO2-eq costs). One phase, four agents in parallel:
- agent editor: "JSON tab" and the share button of the calculator editor in `script/calculator_editor.js`
- agent npp: "Swiss NPP": the rest of `npp_ch_vote_editor_diff.md` in the editor preview and the CO2-eq reduction
  costs in the NPP use case json
- agent share: the "Share assumptions and vote" button of the calculator page
- agent prime: "prime group id within 2^53" in the group id and value table selection, with a test at the limit

## Calculator editor

### Swiss NPP

in the Calculator editor e.g. http://localhost/http/view.php?m=195&id=-3321&9m=16&9id=-3321&u=11
in 'Page preview' the same view should be shown as e.g. http://localhost/http/view.php?m=calculator&id=-3321
For the 'new CH npp' case e.g. the 'Undecided on cost - decide on the other arguments' and the 
'Arguments for lifting the ban (yes)' and 'Arguments for keeping the ban (no)' seems to be missing
done: the preview is built by `calculator_json.js` from the edited data (docs/llm/frontend.md) and compared by
`system_views_read_tests::run_npp_ch_vote_editor`; the only difference left in
`src/test/resources/web/html/use_cases/npp_ch_vote_editor_diff.md` should be the save, which the preview leaves out

add costs of CO2-eq reduction to the npp

add to the calculator a 'Share assumptions and vote' button
and to the "Calculator Editor" a share Calculator vie Mastodon, email, Nostr, ...button

## prime group id within 2^53

limit the use of the prime value table to the javascript int limit of 9,007,199,254,740,991 (or 2^53 - 1)
if a value id would be bigger than this limit, force the use of the alpha group_id as unique key for values
and the use of the values table instead of values_prime

if done remove this prompt from pending.md

**next prompt**

**full test run with re-pin: first /test/reset_db.php, then at least twice /test/test.php**

**Prompt:** portfolio split page compared with its sample (size L, three phases; re-pin, because the formulas of
`portfolio_split_calculator.json` change). The lead asks the developer the decision of step 8 first.
- phase 1, five agents in parallel:
  - agent import: "import": the csv of the sample, the csv of the existing test data and the json created from each
  - agent cases: "step 2", the add two children and remove one child test case
  - agent look: "check", step 4, step 5 (the colours and the hint) and the icons of step 7
  - agent prior: part 1 of step 9, the write test of the previous row
  - agent cell: the unit test of a cell with a result of part 4 of step 9
  - stop for a run: `/test/test.php`
- phase 2, one agent: parts 2 and 3 of step 9 (the walk order and the formulas, because they decide together);
  stop for a run with re-pin
- phase 3, two agents in parallel: agent fields with part 5 of step 9 (the number fields of the holdings) and agent
  child with step 3 (add child and remove, the warnings by the instances)

### portfolio split page compared with its sample

The remaining steps fix src/test/resources/web/html/use_cases/portfolio_split_calculator_js.html 
and the related reports.

#### import

based on a csv and a list of context phrases e.g. 'example portfolio split' a zukunft import json should be created.

additional to the json creation create test case files for this sample csv.
The portfolio number 040270501213 and all other data of this csv are generated and have no connection to any real life case:

Portfolio,040270501213,Date,06.02.26,,,,,,,,,,,,,,,,,,,
List of Positions,,,,,,,,,,,,,,,,,,,,,,
,,,,,,,,,,,,,,,,,,,,,,
Asset Class,Instrument Name,Instrument,Quantity,Region,Currency,Price,Price Curr,Quote Date,Maturity Date,Exchange Rate,Market Value,Ref. Curr,MV%,Cost Price,Ref. Curr,Exchange Rate,Net Cost Value,Ref. Curr,Capital P%L,Currency P&L,P&L(%),Indicative LTV (%)
Cash and short-term investments , CH00 0000 0000 0000 0000 1,2120.001.01,484296.24,Other Region,CHF,1,CHF,,,1,484296.24,CHF,2.769954656,,CHF,1,424896.24,CHF,0,0,0,
Cash and short-term investments , CH00 0000 0000 0000 0000 2,2120.814.01,209848.13,Other Region,EUR,1,EUR,,,0.9323,195641.41,CHF,1.275412178,,CHF,,,CHF,,,,
Cash and short-term investments , TREUHAND CALLGELD - Deutsch Bank BONDS S.A.,N987272-,320000,Eurozone,EUR,1,EUR,,,0.9323,270571.28,CHF,1.763889878,1,CHF,0.93,297600.00,CHF,-9.33,0.25,-9.08,
Cash and short-term investments , CH00 0000 0000 0000 0000 3,2120.333.01,205534.45,Other Region,USD,1,USD,,,0.8036,165167.48,CHF,1.076748623,,CHF,,,CHF,,,,
Bonds and similar positions , Oak Lu Fd Gl RhC,LU0854923223,934.784,Eurozone,CHF,150.269,CHF,05.02.26,,1,140469.06,CHF,0.915736338,120.58,CHF,1,112713.36,CHF,24.63,0,24.63,70
Bonds and similar positions , Oak Lu Fd Gl RhC,LU0854923140,200000,Eurozone,EUR,149.03,EUR,05.02.26,,0.9323,277881.34,CHF,1.811545111,,CHF,,,CHF,,,,90
Bonds and similar positions , 4.1 PG 26 Bds,US742718FY05,150000,North America,USD,99.04,USD,05.02.26,,0.8036,119382.82,CHF,0.778272331,,CHF,,,CHF,,,,90
Bonds and similar positions , 4.375 EBRD 28 Bds,US29874QEX88,200000,United Kingdom,USD,101.51,USD,05.02.26,09.03.28,0.8036,165659.9,CHF,1.079958748,100.28,CHF,0.8948,179467.46,CHF,1.1,-10.2,-9.1,90
Bonds and similar positions , 4 Inter-Am 28 Bds,US4581X0EH74,250000,North America,USD,100.19,USD,05.02.26,,0.8036,201281.71,CHF,1.312182028,,CHF,,,CHF,,,,90
Equities and similar positions , ABB Rg,CH0012221716,6000,Switzerland,CHF,61.74,CHF,05.02.26,,1,370440,CHF,2.414947242,17.34,CHF,1,104036.52,CHF,256.07,0,256.07,70
Equities and similar positions , Accelleron Ind Rg,CH1169360919,3000,Switzerland,CHF,57.84,CHF,05.02.26,,1,173520.00,CHF,1.131199777,,CHF,1,,CHF,,0.00,,70
Equities and similar positions , Alcon Rg,CH0432492467,2500,Switzerland,CHF,77.13,CHF,05.02.26,,1,192825.00,CHF,1.257051620,,CHF,1,,CHF,,0.00,,70
Equities and similar positions , Amrize Rg,CH1430134226,5000,Switzerland,CHF,48.46,CHF,05.02.26,,1,242300.00,CHF,1.579585673,,CHF,1,,CHF,,0.00,,70
Equities and similar positions , BB Biotech N,CH0038389992,3500,Switzerland,CHF,41.61,CHF,05.02.26,,1,145635.00,CHF,0.949413783,,CHF,1,,CHF,,0.00,,70
Equities and similar positions , CieFinRichemont N,CH0210483332,1000,Switzerland,CHF,157.69,CHF,05.02.26,,1,157690.00,CHF,1.028001918,,CHF,1,,CHF,,0.00,,70
Equities and similar positions , Geberit N,CH0030170408,175,Switzerland,CHF,558.00,CHF,05.02.26,,1,97650.00,CHF,0.636593236,,CHF,1,,CHF,,0.00,,70
Equities and similar positions , Givaudan N,CH0010645932,35,Switzerland,CHF,3955.70,CHF,05.02.26,,1,138449.50,CHF,0.902570560,,CHF,1,,CHF,,0.00,,70
Equities and similar positions , Helvetia Bal Hl Rg,CH0466642201,700,Switzerland,CHF,160.39,CHF,05.02.26,,1,112273.00,CHF,0.731922502,,CHF,1,,CHF,,0.00,,70
Equities and similar positions , Holcim N,CH0012214059,1750,Switzerland,CHF,58.98,CHF,05.02.26,,1,103215.00,CHF,0.672872205,,CHF,1,,CHF,,0.00,,70
Equities and similar positions , Idorsia Rg,CH0363463438,40000,Switzerland,CHF,1.995,CHF,05.02.26,,1,79800.00,CHF,0.520226730,,CHF,1,,CHF,,0.00,,70
Equities and similar positions , Lindt & Spruengli N,CH0010570759,20,Switzerland,CHF,10448.32,CHF,05.02.26,,1,208966.40,CHF,1.362279536,,CHF,1,,CHF,,0.00,,70
Equities and similar positions , Nestle N,CH0038863350,2500,Switzerland,CHF,78.28,CHF,05.02.26,,1,195700.00,CHF,1.275794124,,CHF,1,,CHF,,0.00,,70
Equities and similar positions , Novartis N,CH0012005267,700,Switzerland,CHF,91.15,CHF,05.02.26,,1,63805.00,CHF,0.415953214,,CHF,1,,CHF,,0.00,,70
Equities and similar positions , Roche Hldg DR,CH0012032048,700,Switzerland,CHF,258.40,CHF,05.02.26,,1,180880.00,CHF,1.179180588,,CHF,1,,CHF,,0.00,,70
Equities and similar positions , Sandoz Group Rg,CH1243598427,5000,Switzerland,CHF,42.25,CHF,05.02.26,,1,211250.00,CHF,1.377166626,,CHF,1,,CHF,,0.00,,70
Equities and similar positions , SGS Rg,CH1256740924,2000,Switzerland,CHF,96.48,CHF,05.02.26,,1,192960.00,CHF,1.257931702,,CHF,1,,CHF,,0.00,,70
Equities and similar positions , Sonova Hldg N,CH0012549785,300,Switzerland,CHF,257.67,CHF,05.02.26,,1,77301.00,CHF,0.503935419,,CHF,1,,CHF,,0.00,,70
Equities and similar positions , Straumann Hldg Rg,CH1175448666,1750,Switzerland,CHF,111.35,CHF,05.02.26,,1,194862.50,CHF,1.270334351,,CHF,1,,CHF,,0.00,,70
Equities and similar positions , Swisscom Rg,CH0008742519,400,Switzerland,CHF,569.00,CHF,05.02.26,,1,227600.00,CHF,1.483754433,,CHF,1,,CHF,,0.00,,70
Equities and similar positions , UBS Group N,CH0244767585,6000,Switzerland,CHF,31.89,CHF,05.02.26,,1,191340.00,CHF,1.247370709,,CHF,1,,CHF,,0.00,,70
Equities and similar positions , Zurich Insur Gr N,CH0011075394,400,Switzerland,CHF,557.05,CHF,05.02.26,,1,222820.00,CHF,1.452592983,,CHF,1,,CHF,,0.00,,70
Equities and similar positions , AB InBev,BE0974293251,700,Eurozone,EUR,59.26,EUR,05.02.26,,0.9323,38673.67,CHF,0.252118758,42.84,CHF,1.0945,32823.9,CHF,32.65,-14.82,17.82,70
Equities and similar positions , adidas N,DE000A1EWWW0,1000,Eurozone,EUR,162.35,EUR,05.02.26,,0.9323,151358.91,CHF,0.986728707,63.08,CHF,1.0945,69044.98,CHF,134.04,-14.82,119.22,70
Equities and similar positions , Allianz Rg,DE0008404005,250,Eurozone,EUR,280.45,EUR,05.02.26,,0.9323,65365.88,CHF,0.426128822,,CHF,,,CHF,,,,70
Equities and similar positions , AXA,FR0000120628,1750,Eurozone,EUR,36.84,EUR,05.02.26,,0.9323,60105.38,CHF,0.391834910,,CHF,,,CHF,,,,70
Equities and similar positions , BASF N,DE000BASF111,4000,Eurozone,EUR,47.11,EUR,05.02.26,,0.9323,175682.61,CHF,1.145298130,,CHF,,,CHF,,,,70
Equities and similar positions , Bayer N,DE000BAY0017,7000,Eurozone,EUR,25.33,EUR,05.02.26,,0.9323,165306.11,CHF,1.077652364,,CHF,,,CHF,,,,70
Equities and similar positions , Daimler Tr Hldg Rg,DE000DTR0CK8,6000,Eurozone,EUR,42.07,EUR,05.02.26,,0.9323,235331.17,CHF,1.534154925,,CHF,,,CHF,,,,70
Equities and similar positions , Danone,FR0000120644,1000,Eurozone,EUR,60.32,EUR,05.02.26,,0.9323,56236.34,CHF,0.366612095,,CHF,,,CHF,,,,70
Equities and similar positions , E.ON N,DE000ENAG999,15000,Eurozone,EUR,13.81,EUR,05.02.26,,0.9323,193125.95,CHF,1.259013520,,CHF,,,CHF,,,,70
Equities and similar positions , Fid Germany AD,LU0048580004,6000,Eurozone,EUR,30.72,EUR,05.02.26,,0.9323,171841.54,CHF,1.120257649,,CHF,,,CHF,,,,70
Equities and similar positions , MEDICAL BioH IC,LU0294851513,6000,Eurozone,EUR,13.84,EUR,05.02.26,,0.9323,77418.19,CHF,0.504699409,,CHF,,,CHF,,,,70
Equities and similar positions , Mercedes-BenzGr N,DE0007100000,3500,Eurozone,EUR,56.75,EUR,05.02.26,,0.9323,185178.09,CHF,1.207200388,,CHF,,,CHF,,,,70
Equities and similar positions , RWE I,DE0007037129,7000,Eurozone,EUR,35.01,EUR,05.02.26,,0.9323,228478.76,CHF,1.489483192,,CHF,,,CHF,,,,70
Equities and similar positions , Shell Rg,GB00BP6MXD84,7000,United Kingdom,EUR,32.25,EUR,05.02.26,,0.9323,210466.73,CHF,1.372060353,,CHF,,,CHF,,,,70
Equities and similar positions , Siemens Energy Rg,DE000ENER6Y0,1500,Eurozone,EUR,90.96,EUR,05.02.26,,0.9323,127203.01,CHF,0.829253221,,CHF,,,CHF,,,,70
Equities and similar positions , Siemens N,DE0007236101,800,Eurozone,EUR,206.61,EUR,05.02.26,,0.9323,154098.00,CHF,1.004585212,,CHF,,,CHF,,,,70
Equities and similar positions , HSBC Hldg Rg,GB0005405286,15000,United Kingdom,GBP,10.38,GBP,05.02.26,,1.0895,169635.15,CHF,1.105873927,,CHF,,,CHF,,,,70
Equities and similar positions , Vodafone Group Rg,GB00BH4HKS39,230000,United Kingdom,GBP,0.776,GBP,05.02.26,,1.0895,194453.96,CHF,1.267671025,,CHF,,,CHF,,,,70
Equities and similar positions , Altria Group Rg,US02209S1033,3000,North America,USD,55.77,USD,05.02.26,,0.8036,134450.32,CHF,0.876499352,,CHF,,,CHF,,,,70
Equities and similar positions , Amgen Rg,US0311621009,600,North America,USD,312.44,USD,05.02.26,,0.8036,150646.07,CHF,0.982081612,,CHF,,,CHF,,,,70
Equities and similar positions , Boeing Co Rg,US0970231058,800,North America,USD,198.70,USD,05.02.26,,0.8036,127740.26,CHF,0.832755585,,CHF,,,CHF,,,,70
Equities and similar positions , Carrier Global Rg,US14448C1045,1750,North America,USD,70.96,USD,05.02.26,,0.8036,99791.05,CHF,0.650551010,,CHF,,,CHF,,,,70
Equities and similar positions , Citigroup Rg,US1729674242,1750,North America,USD,93.16,USD,05.02.26,,0.8036,131010.91,CHF,0.854077397,,CHF,,,CHF,,,,70
Equities and similar positions , GE Aerospace Rg,US3696043013,400,North America,USD,243.89,USD,05.02.26,,0.8036,78396.00,CHF,0.511073879,,CHF,,,CHF,,,,70
Equities and similar positions , Gen Digital Rg,US6687711084,7000,North America,USD,26.56,USD,05.02.26,,0.8036,149405.31,CHF,0.973992944,,CHF,,,CHF,,,,70
Equities and similar positions , GE Vernova Rg,US36828A1016,500,North America,USD,484.58,USD,05.02.26,,0.8036,194704.24,CHF,1.269302659,,CHF,,,CHF,,,,70
Equities and similar positions , iShs MSCI Au USD-Ac,IE00B5377D42,3000,Asia Pacific exJP,USD,40.94,USD,05.02.26,,0.8036,98698.15,CHF,0.643426277,,CHF,,,CHF,,,,70
Equities and similar positions , iShs NASDAQ USD-Ac,IE00B53SZB19,125,North America,USD,1033.48,USD,05.02.26,,0.8036,103813.07,CHF,0.676771076,,CHF,,,CHF,,,,70
Equities and similar positions , Johnson&Johnson Rg,US4781601046,1750,North America,USD,169.07,USD,05.02.26,,0.8036,237763.14,CHF,1.550009291,,CHF,,,CHF,,,,70
Equities and similar positions , Otis Worldwide Rg,US68902V1070,2500,North America,USD,99.53,USD,05.02.26,,0.8036,199955.77,CHF,1.303538050,,CHF,,,CHF,,,,70
Equities and similar positions , Pepsico Rg,US7134481081,1750,North America,USD,142.62,USD,05.02.26,,0.8036,200566.51,CHF,1.307519519,,CHF,,,CHF,,,,70
Equities and similar positions , Pfizer Rg,US7170811035,6000,North America,USD,26.11,USD,05.02.26,,0.8036,125891.98,CHF,0.820706404,,CHF,,,CHF,,,,70
Equities and similar positions , Philip Mrrs Int Rg,US7181721090,1250,North America,USD,158.53,USD,05.02.26,,0.8036,159243.39,CHF,1.038128640,,CHF,,,CHF,,,,70
Equities and similar positions , Procter&Gamble Rg,US7427181091,1750,North America,USD,152.13,USD,05.02.26,,0.8036,213940.42,CHF,1.394705822,,CHF,,,CHF,,,,70
Equities and similar positions , RTX Rg,US75513E1010,1250,North America,USD,155.86,USD,05.02.26,,0.8036,156561.37,CHF,1.020644230,,CHF,,,CHF,,,,70
Equities and similar positions , Verizon Comm Rg,US92343V1044,2500,North America,USD,43.94,USD,05.02.26,,0.8036,88275.46,CHF,0.575479372,,CHF,,,CHF,,,,70
Equities and similar positions , Xtr MSCI Jap 1CC,LU0274209740,1450,Japan,USD,106.7701,USD,05.02.26,,0.8036,124405.71,CHF,0.81101724,68.09,CHF,0.9137,90210.64,CHF,49.96,-12.05,37.91,70
Alternative investments , SwCHGETF -EA (USD)-,CH0047533549,56,Switzerland,USD,4295.92,USD,05.02.26,,0.8036,193315.58,CHF,1.260249775,1805.68,CHF,0.9001,91011.61,CHF,123.13,-10.72,112.41,70

so create the csv file and as a unit test result create or update a zukunft.com json that can be imported

create a similar csv for the existing portfolio split test data that creates a new json with a different name that can be compare with the existing json

#### check

Differences remaining

- Button colours: the generated page uses the site's grey filled buttons, while the sample shows blue outlines. Those button rules are shared across the whole site and the sample doesn't load the site's stylesheet, so changing them here would affect every page.
- Font: it differs for the same reason.
- Button icons: these belong to step 7.
- Import position: Import is below Save here and at the top in the sample. I didn't change this because it's page structure, not styling.
- Walk table: it still scrolls sideways, so the last column is cut off until you scroll. The sample does the same.
- Diagram scale font: the sample uses a monospace font for it, and I kept the normal one.

#### step 2: add a "Add child" and remove

Add an add two children and remove one child test case similar to the high and low test cases 
so that all main test cases are covered

#### step 3: the input fields (1), (2), (3), (4)

- the formulas of the warnings name the first and the second child: 
  a new child needs a sum over the instances, so update them with the add child task

The buttons "Add child" and remove are a separate task after step 9, 
because a new child has no allocation before the walk is calculated: 
a new instance needs its own phrase, its "is a" triple and its target share through the logged save, 
and the page needs the remove button per field. The button stays without a function until then.

#### step 4: the result table (9), (17)

- the sample shows a positive deviation with a plus sign and a small negative one as -0.00, the generated page 0.00 and the sign only in the positive style; `calculator signed` is also used by other use cases, so check their samples first

#### step 5: the detail tables (5), (6), (7), (8), (14), (15), (16)

- the number fields of the holdings (6): `calculator editable` has no renderer code yet, and the fields wait for step 9, because the page must show the walk of the saved quantity or price
- the colours of the walk cells and the hint below the holdings

#### step 7: the buttons (13)

- the buttons "Download", "Report", "Import", "Import portfolio", "Import FX rates", "Copy as zukunft.com JSON" and "Copy as CSV" have no function: each function is a task of its own, and an import is a write that needs the usual permission and log rules
- the icons of the sample

#### step 8: decide what the generated page keeps

- the section "Inputs from" exists only in the sample and holds the buttons "Import portfolio" and "Import FX rates", 
- which the generated page shows at the assumptions and the holdings

#### step 9: calculate the split again in the browser (18)

The rows of the walk are stored results of the formulas, and the browser calculates them again after an input change with the values of the rows from the phrase data. The summary rows stay modelled and the rounding of a tie differs, see part 3 and 5, so the numbers of the case reports can still differ from the sample. An operand with `prior` next to another phrase reads the row before in the `is follower of` chain (`prior_operand`) in `formula_calc`, `calculator.js`, `calculator_json.js`, `formula_editor.js` and the calc-validation. The round to a multiple needs nothing new, because it is `round( "x" / "lot size" ) * "lot size"`. The plan, each part with a positive and a negative test and a run before the next part:

1. the previous row in the backend: `element_group::figures` reads it, but no test covers it with a database yet; add a write test with a follower chain, a first row and a row in the middle, and a negative test with two chains in the context. A figure selects its number by the exact phrase group of the context and the operand, so check with the formulas of part 3 that the context of a walk formula selects the stored numbers
2. the walk order selector: the holdings have one follower chain, in the order of the largest lot value, and `is ranked by` also accepts a result; the option "Bank report order" needs its own order for the rows and for `prior`, because one phrase has one previous phrase per chain, so decide this with the formulas of part 3
3. the formulas in `portfolio_split_calculator.json`: the unit value, the lot value, the target, the counts of the children and the parent and the cumulative deviation exist with a calc-validation of all 42 rows. Still open: the equal part of a position when the rounded counts exceed it, the second child without mirroring: `first child` and `second child` are named in the formulas, so a new child needs a formula by the instances, and a rounding rule for a tie: the sample rounds LISP 3.5 up but SCHP 420.5, STMN 874.5, ABBN 1925.5 and the cent of `CHF cash` down, only because its carried deviation has floating point noise. The stored numbers of these rows are just below the tie (e.g. 420.499999) to keep the counts of the sample, so a calculation from exact numbers rounds them up and the walk differs from there on
4. the results of the rows in the phrase data: the from walk loads them for a table with `calculator page independent` and a cell of such a table reads the one result if no one value qualifies, so `calculator page independent` keeps its `PROPOSED` text only for the items and the check table. Still open: a unit test of a cell with a result
5. add the number fields of the holdings and the add child task. The modelled realised value, the totals per kind of holding and the realised share stay, because a formula cannot sum over the rows yet

if done remove this prompt from pending.md

**next prompt**

**full test run with re-pin: first /test/reset_db.php, then at least twice /test/test.php**

**Prompt:** id check, use cases of the calculator editor, the sample compare and pure HTML views (size L; re-pin,
because the ahv vat use case gets new data). One phase, four agents in parallel:
- agent id check: "ID check", the api fixtures like `phrase_data_pv_install.json` in the re-pin check
- agent ahv vat: "ahv vat", the other targets, their weights and the combined index rating in the use case json
- agent samples: "config yaml", "more sample" and "compare" of the calculator section below: the pinned views, one
  check script that every `*_sample.html` has a generated page and is part of the compare and the generation build
- agent pure html: "pure HTML and JavaScript views", the url parameter with its `*_HUMAN` twin and the JavaScript
  version of the formula editor

## ID check

include files like phrase_data_pv_install.json in the re-pin id check and if  AUTO_RE_PIN_OBJECT_ID is true update the id

## Calculator editor

### pdf creation parameter

which section should start a new page in the pdf report and which section should be printed in 
portrait or landscape format should be a parameter that the user can select in the editor

### use cases

#### ahv vat

Add other targets e.g. distribution of money from the rich to the poor and from the old to the young and add the
possibility to set the weight of the targets and combine them with the personal gain to one index rating

#### config yaml

add these to the config.yaml at 'pinned views'

#### selection calculator and editor

create a selection calculator with editor for the Swiss National Science Fund (snsf) to select projects to fund

#### more sample

write a script that for all *_sample.html files within src/test/resources/web/html/use_cases
a generated file exists and is generated

## pure HTML and JavaScript views

add an url parameter for to use the pure HTML version of the view. If the parameter is not given and a JavaScript version exists use the javascrip version

create a JavaScript version of the formula editor view

if done remove this prompt from pending.md

**next prompt**

**full test run with re-pin: first /test/reset_db.php, then at least twice /test/test.php**

**Prompt:** calculator: evaluators, functions, tests, phraseData load and the json page (size L, two phases; re-pin,
because `math.json` gets the law type and the IRF formula changes). "compare" is part of the prompt above. The lead
asks the developer the rename of point 3 of "base on json" first.
- phase 1, five agents in parallel:
  - agent evaluators: "differences between the evaluators" and the function `C` of step 3 of "functions used in the
    data but not calculated" in all five evaluators
  - agent law: steps 1 and 2 of "functions used in the data but not calculated": the law formula type, the skip in
    `math_function_report` and the IRF formula in the data
  - agent tests: "list functions", "assert_contains names the wrong entry" and "saved result of a failed calculation"
  - agent load once: "load the phrase data once per step", the fix of `phrase_list::get_by_ids` with its test first
  - agent conversions: point 1 of "base on json", the unit test of `number_format::unit_conversions`
- phase 2, three agents in parallel, after the agent evaluators:
  - agent formula editor: "formula editor", the blocked save and the test of the "... more" buttons
  - agent json script: points 5 and 6 of "base on json" and "PV calculator page compared with its sample": the shared
    script code, the plain match instead of the ranking, the json rule fixtures for PHP and the script, the sub texts
  - agent json page: points 4 and 7 of "base on json", the page without the related objects and without the unused
    embedded data; point 2 follows from point 5 and is checked by the lead

## calculator

### differences between the evaluators

fix the edge cases that `docs/math.md` marks as a diff:
- the backend loses precision, because it turns intermediate results into text with 14 digits: `( 1 / 3 ) * 3` gives 0.99999999999999 and the page 1
- the page evaluators reject a number in scientific notation and a number without digits on both sides of the point (`5E3`, `1.5E-5 * 2`, `.5`, `2.`), so check if a page writes a small value in this form, which then gives no number
- the backend rejects a double sign (`--3`), the page evaluators give 3
- only the backend calculates the short if form `if( 1 ) 5`, all page evaluators and the formula editor give no number

### list functions

- the unit_read test of `formula::fill_list_element_group` has no stored value with a variance or a confidence, so the exclusion of `words::LIST_FUNCTION_EXCLUDED` is only tested on the page (`use_case_ui_tests`)
- the import calc validation (`data_object::expression_with_values`) replaces an operand by one number, so a list formula in a calc validation needs the same list selection

### functions used in the data but not calculated

`docs/math.md` lists `C`, `IRF`, `d`, `integral` and `lim`. Except `IRF` they are in laws of `math.json`, where the letters stand for any number, so most of them are no calculation at all.

Recommended, in this order:
1. **Mark a law as not calculated** for `d`, `integral` and `lim`: add a formula type e.g. "law" (`formula_type`), which is shown with its latex and never calculated, so no user gets an error for a formula that only documents a rule. `math_function_report::used_functions` skips this type, so that the report lists only real gaps. This needs a data change of the math.json formulas and possibly a raise of the minor version, if the json format changes.
2. **`IRF`**: rewrite the formula in the data with what is already calculated, because `exp` exists and the coefficients are values: `"IRF" = "IRF coefficient a0" + "IRF coefficient a1" * exp( 0 - "time" / "IRF time scale tau1" ) + ...`. Then it is a real result and the name IRF is a phrase instead of a function.
3. **`C`**: add it as a function of two numbers to all five evaluators, because a binomial coefficient is a plain calculation of numbers: reject a negative or a non-integer argument with a message and take care of the overflow of a big `n`. The law of the binomial theorem in math.json still gets the type of step 1, because its `sum` runs over an index, which the list functions do not have.

Alternatives, only if a real calculation needs them:
- `integral` and `d` as numbers: a numeric calculation over a table of values, e.g. the trapezoid rule over the values of a time row, under a new name such as `area( "y" ; "x" )`; a symbolic calculation would need a computer algebra system, which is not worth the dependency
- `lim`: no numeric form that is reliable, so it stays a law

Keep the error message for a formula without the law type, because a function that does not exist must never give a wrong number.

### formula editor

- a function inserted by the "... more" buttons gives no number until its arguments are filled, and like a single open bracket such an expression can be saved: block the save of an expression that is no valid calculation
- add a test of the "... more" buttons, which so far only the math report covers for the calculation

### PV calculator page compared with its sample

the remaining differences of `pv_lifetime_balance_diff.md`:
- the numbers in a row sub text are not updated by `calculator.js` of the server page when an input changes, like the number in a row label; `calculator_json.js` builds them again

### assert_contains names the wrong entry

`test_base::assert_contains` compares by position, so one missing entry is reported as the last entry of the list e.g. "+zukunft.com" for a missing "modelled"; report the entries that are really missing

### saved result of a failed calculation

a failed formula calculation overwrites the stored result with nothing (`docs/json_structure.md`); add a test that `formula::calc_result` and `result::save_if_updated` really write the empty number, and fix the save path if they do not

### compare

add the other *_sample.html files in src/test/resources/web/html/use_cases in the compare and add a check script that
all *_sample.html files are part of the compare and generation build

### base on json

the calculator page e.g. http://localhost/http/view.php?m=16&id=-2757 is built in the browser by
`calculator_json.js` from one /api/phraseData answer (the seed) and the page settings
(`system_page::calculator_json`, docs/llm/frontend.md "Javascript only for the calculator"); with
`&sc=0` the page of the server is shown; the snapshots are `use_cases/pv_lifetime_balance_js.html`
and `use_cases/pv_lifetime_balance_no_js.html`; every "which value, which format" decision lives in the
data, the code only applies plain rules (phrase match, the arithmetic, the number format)

open points of the review:
1. `number_format::unit_conversions` has no unit test of its own: a conversion formula e.g.
   "kg" = "tonne" * 1000 gives both directions, and a formula that is no conversion e.g.
   "t" = ln( 2 ) / "η" is left out; the page settings (`system_page::calculator_page_settings`,
   `calculator_type_names`) are only checked indirectly
2. the open differences of the two snapshots: the server numbers the operand sources by the results
   in the preferred order of numbers (`sandbox_value::sorted`, first by the `impact`), which the
   phraseData answer does not carry, so the script numbers e.g. the monthly mixes of treeze 2025
   before the Swissolar fact sheet 2024; for the same reason a field whose value no result uses
   and that two values fit equally shows the first value of the preferred order on the server,
   while the script leaves the field empty with a warning (`onlyOne`), until the ranking of
   point 5 is replaced; the use case fixes such a tie by naming the field value
3. `system_page::CALC_EDITOR_SEED_ID` and `CALC_EDITOR_TYPES_ID` are also the ids of the calculator
   page, so a name without "editor" fits better (a rename is the developer's decision)
4. the page object: the calculator view loads its phrase with the related objects
   (`frontend::page_api_par`, `views::VIEWS_WITHOUT_RELATED`), which the page does not use; load it
   without them or take it from the selection of the phraseData answer, and count the api calls of
   the page in a database read test, so that the page needs only the view, one phraseData answer
   and the typeLists and config of the session
5. `calculator_json.js` repeats code: `evaluate` the arithmetic of `calculator.js`, `readJson` the
   big id handling of `calculator_editor.js`, the reader of the calculator triples and the layout
   the editor preview (`calculator_editor.js::previewPage`), and `fieldEntry` the ranking of
   `ui_list::calculator_value`; share the script code in one file, and replace the ranking after
   the first rank, the value that a result of the page uses, which the formulas of the data decide,
   by a plain match of the data: where two values fit a field equally, the use case gets the missing
   qualifier phrase or a calculator triple that names the used value, and a tie is reported by the
   calc validation of the use case
6. `calculator_json.js` has no test, because the repository has no javascript runtime; one json
   test fixture per rule (seed and settings in, field values, cell texts, operand fields, notes and
   sources out), read by the PHP unit test and by a check of the script, so that the PHP and the
   javascript implementation of the plain rules cannot drift
7. the page of the server (`sc=0`) embeds the seed and the verbs and units of the type cache
   (`system_page::calculation_result` → `calculator_page_data`, about 100 KB), which its script
   `calculator.js` never reads; the `fields` plan and the `digits` of the `calc-data`
   (`ui_list::calculator_script`) are not read either; remove all three and move the test
   "... the phrase data of the page as the seed" of `use_case_ui_tests` to the json page tests

### load the phrase data once per step

`cfg/export/phrase_data.php` collects the data by the phrase ids and loads it once per step, but two
places load per object:
- `value_phrase_ids` calls `load_phrases()` for each value; take the phrase ids from the group key
  (`$val->grp()->id_lst()`) like `result_phrase_ids`, because `complete_phrases` loads them once with
  all other phrases
- `with_ids` calls `export_json($msg)` with the default `$do_load = true`, which reloads each value and
  result by its id and loads its phrases; set the phrase lists of the value and result groups (incl. the
  source group of a result, whose names `result::export_json` exports as context) from the completed
  phrase list and export with `$do_load = false`

`phrase_list::get_by_ids` reads each id from the new empty list instead of `$this`, so it always returns
an empty list; fix it and add a unit test before it is used to set the group phrase lists

if done remove this prompt from pending.md

**next prompt**

**full test run with re-pin: first /test/reset_db.php, then at least twice /test/test.php**

**Prompt:** new words in the calculator editor, the math operators, external links, the names a user has given and the
phrase selector of the settings (size L; re-pin, because the settings selector gets a system triple with a code id).
The power sign `^` is already calculated (`chars::POW`, `calculator.js`), so "power sign" is only the other operators
and the formula editor buttons. One phase, five agents in parallel:
- agent new words: "add new words", the offer to add the missing words and triples and the wikipedia references
- agent operators: "power sign", the missing usual math operators in all evaluators and the formula editor
- agent dependencies: "external links", the script that writes `docs/dependencies.md`
- agent user names: the four tasks of "names that a user has given" one after the other, the tests first; the sql
  fixtures are regenerated by the developer
- agent selector: "settings" and "check past changes again", the pattern based phrase selector by the triple
  `phrase selector (system)` and the same for the view selector and the other db row fields

## Calculator editor

### add new words

if with the load not matching word or triple is found, offer the user to add a word with the standard parameters.
if the search pattern contains spaces suggest to add the single words separated by spaces and triples for the combined words.
search in wikipedia for all words and triples and suggest to include these as references. 

### power sign

in the formula editor and all other places add the power sign '^' as an operator.

add other usual math operators that are missing

## external links

create a script that collects or potential external links and dependencies and list the in '/docs/dependencies.md'

## names that a user has given

a user who renames a word, triple, formula, source, view or component sees the own name, so every load by
name must find the object by that name: `(u.<x>_name = $2 OR (s.<x>_name = $2 AND u.<x>_name IS NULL))`
(`sql_par_type::TEXT_USR`, see `sandbox_named::load_sql_by_name` and `phrase::load_sql_by_name`);
the `*_norm_by_name` loads select the standard row by design and stay as they are

- see /docs/llm/coding.md and let `term::load_sql_by_name` find a term by the name that the user has given it
  like `phrase::load_sql_by_name` (`sql_par_type::TEXT_USR`); add a positive and a negative test in
  `term_tests` like the phrase test; `term_by_name.sql` and its mysql twin are regenerated by the developer
- see /docs/llm/coding.md and let the lists by names (`phrase_list_by_names`, `word_list_by_names`,
  `formula_list_by_names`, `WHERE s.<x>_name = ANY ($2)`) find the names that the user has given: add a
  user-specific list parameter type next to `sql_par_type::TEXT_USR` that creates
  `(u.<x>_name = ANY ($2) OR (s.<x>_name = ANY ($2) AND u.<x>_name IS NULL))` and use it in the lists; e.g. a
  value of an update put message that names a phrase renamed by the user is not found yet; tests first
- see /docs/llm/coding.md and let the name searches (`*_names_like` and `*_list_by_name_like` of word, triple,
  phrase, term, formula, source, view and component, `WHERE s.<x>_name ilike $2`) find the names that the user
  has given, so that the selector of the calculator editor suggests a renamed phrase by its new name; the order
  by name uses the shown name; tests first
- see /docs/llm/coding.md and let `group::load_sql_by_name` find a group by the name that the user has given
  it in `user_groups`, like the other named objects; tests first

## settings

for phrase names used in the settings page e.g. http://localhost/http/view.php?m=126&u=9 e.g. for  'user
    default
        backend
            impact
                phrase
                    happy time points'

use a pattern base phrase search for the 'happy time points' phrase and all other phrases. 
Select it by triples linked to a triple 'phrase selector (system)' with the  code_id 'phrase_select'

like for the phrase selector do the same for view selector and other fields where a db row should be selected

### check past changes again

dark / light mode switch, phrase selector, view selector, colour picker, font selector

if done remove this prompt from pending.md

**next prompt**

**full test run with re-pin: first /test/reset_db.php, then at least twice /test/test.php**

**Prompt:** calculation editor messages, code rules and api security (size M). The save of the calculator editor sends
its message to the existing `api/updatePut` (`update_put_write_tests`), so "update json message" and "test case" mean
`updatePut` where they name `/api/updateDrop`, and `unitNames` is read from the seed (`typeUnits()`) already. One
phase, four agents in parallel:
- agent rules: "general" and "javascript" as rules in `docs/llm/`, and the documented options of "unitNames"
- agent verbs: "verb names", the verb names of `script/calculator_editor.js` written by a php script
- agent save cases: the open points of "test case": the deleted rows of a cleared limit, a removed and a moved input,
  the new triples in `triple_names::TEST_TRIPLES`, the sub-item scenarios and the write test with an undo per message
- agent security: "api security" and "relogin": the test that an api request without a session is rejected and the
  login link in the message of the calculator editor

## calculation editor

### update json message

if in the 'Calculator editor' save is pressed a json should be created and send to the api endpoint /api/updateDrop, which needs to be created
The json should be similar to the import json that is described in docs/llm/json_structure.md, but the 'api_json' differs in some ways
1. it uses the api header including the execution time report if needed
2. it contains the database id which is used as the prime key if given
3. also for types the database id is preferred used  

create some tests that adds / updates / compares message files in src/test/resources/api/calculator_editor

### test case

create test cased for all possible change types in the calculation editor.
For each change type do the change in the frontend and write / compare the message that would be send to /api/updateDrop

`src/test/js/calculator_editor_scenarios.js` acts like a user in a copy of the calculator editor snapshot in a
headless LibreWolf, Firefox or chromium, the first one found, and `use_case_ui_tests` compares the sent message with `src/test/resources/web/calculator_editor/`
for a formula expression, a description, a renamed, a new and a removed triple, a new word, a new, a changed and a
removed value, an undo and an input, a row and a row group moved up or down. The write test saves and undoes the
description, the formula, the renamed triple, the changed number, the new limit, the new input field and the moved
row and row group. Open:
- the triples of a new input field are not in `triple_names::TEST_TRIPLES`, so the global cleanup does not remove
  them if a run stops before the undo
- scenarios for the sub-items: a row filter, a detail column, a side result, a value phrase and the options of a phrase selector

for the backend test create workflow write tests that take each message that could be dropped to /api/updateDrop and execute it, check the result and execute a corresponding undo message.


### general

add to /docs/llm/* that no concrete word, triple, formula or any other name should be never part of the code.
The ONLY link between production code and concrete database rows is the code_id. This is way it is named code id.
This is a general rules that does not allow ANY exception. 

For testing the name, id and other fields can be used. But ONLY for testing!

### javascript

add to /docs/llm/* that js files should not have any const that is not takes from a php const to fulfill the "on point of change" rule
this implies that all js files must be written by a php script

#### unitNames

src/main/resources/script/calculator_editor.js contains unitNames (e.g. 'const unitNames = new Set(["kWh", "kWp", "kg", "gram", "t", "percent", "a", "kWh/a"]);')
there should be no specific const in the js file. the unitNames should be taken either from the seed json 
or if this takes too long by execution time a units json should be included
or the script should be updated automatically by a php script every time a unit is updated 
which can be detected by a parent phrase with a code_id
for the moment the preferred option is to use the exists seed json, which might need to be extended
but all other options with the pros and cons should be documented in /docs/*

#### verb names

src/main/resources/script/calculator_editor.js contains verb names (e.g. '        if (t.verb === "is symbol for") {')
to cover the case that a verb name changes the js script should be created by a php script that updated the js script
every time a verb is renamed. for the moment it is fine if the script is updated with the full test run because 
adding or changing a verb is at the moment a code change which requires a full test run

## api security

if it does not yet exist create a test that checks if an api request is rejected if it is not part of a previous created session

session can only be created either via a proper session start via gui
or using a predefined api session login that uses e.g. a public / private key pair and other extra security 

### relogin

if the calculation editor gets a message from the backend that might indicate that e.g. the session id is missing or invalide
suggest to the user to login again with a link in the message shown to the user

if done remove this prompt from pending.md

**next prompt**

**full test run: /test/test.php**

**Prompt:** system triple ids in production and the view and component editor (size L, three phases like the table
editor; re-pin, because the editor adds system views). The table editor is the prompt at the top, and the process
editor is part of the process catch prompt of "PV installation calculator" below.
- phase 1, lead: the solution concept of "other editors" for system triples whose id differs in production, as a
  section of `docs/llm/architecture.md`, and a hand made `use_cases/view_editor_sample.html` with its use case json,
  built like `calculator_editor_sample.html`
- phase 2, three agents in parallel: agent script (`script/view_editor.js`, its css and messages, the save with the
  `updatePut` body), agent server (the system view, its page and the generated `use_cases/view_editor.html` with the
  diff to the sample) and agent docs (`docs/llm/frontend.md`)
- phase 3, two agents in parallel: agent js tests (the scenarios per action) and agent save tests (the `updatePut`
  fixtures and their write tests)

## other editors

be aware (or better create a solution concept) for the case that in production the id of system triples used for testing will differ from the id in the development databases

### view editor

uss the calculation editor as a base and create a view and component editor

### process editor

uss the calculation editor as a base and process editor, so multi step input views

if done remove this prompt from pending.md

**next prompt**

**full test run with re-pin: first /test/reset_db.php, then at least twice /test/test.php**

**Prompt:** phraseData cache, the open API script, the server busy view and the id usage check (size M). One phase,
four agents in parallel:
- agent cache: "speed up user data creations", the phraseData answer from the api cache with the user overwrites
- agent openapi: "open API update", the generator script, the diff test and the generated yaml
- agent busy: "server busy", the waiting view and the parallel cache check
- agent id usage: "id usage", the code test that a pinned `*_ID` const is used by tests only

## phrase data api

### speed up user data creations

include the phraseData calls in the api cache handling 

if the phraseData for a user is requested and the cache contains the standard data already 
get the standard data from the cache, read the user overwrites from the database 
and overwrite the standard data with the user overwrites

### open API update

write a script that recreates the open API yaml based on the code and create a test that reports the differences between
the created openAPI and src/main/resources/openapi/zukunft_com_api.yaml and writes the created to src/main/resources/openapi/zukunft_com_api_generated.yaml

## cleanup

### server busy

similar to the 'collecting data' waiting view if the response takes longer
add to all pages a 'server busy' view if the server has not finished the page view

is it possible that at least the check if a view is already in the cache is done in parallel 
even if the server is collection data for another view?

### id usage

create a code test script that checks that the pinned word, triple, ... id (*_ID) are never used in the real code and only used for testing

if done remove this prompt from pending.md

**next prompt**

**full test run: /test/test.php**

**Prompt:** test coverage (size L, three phases). The rules of "steps" stay: one class per agent, so each agent owns
the test files of its classes and every diff stays reviewable per class.
- phase 1, up to six agents in parallel, each with a disjoint set of test files: step 1, the shared functions of its
  classes; stop for a run: `/test/test.php`
- phase 2, up to six agents in parallel, each with a disjoint set of classes: steps 2 and 3; stop for a run:
  `/test/test.php`
- phase 3, three agents in parallel: agent db with step 4, agent unreachable with step 5 (the exception list is a
  decision of the developer) and agent guard with step 6

### test coverage

#### steps

goal: every public function of `docs/code_test_coverage.md` has at least two unit test calls
(a positive and a negative test, see `docs/llm/coding.md`); the report is generated by
`bin/code_test_coverage.php` and is never edited by hand, so each step below changes the tests
(or the counter) and the developer regenerates the report to see the gap shrink

starting point: 1126 of 6200 public functions are covered; of the 4869 gaps 3275 are in the
main backend, 1414 in the frontend and 180 in shared, 381 functions have one call (359 of
them share their name and need two calls per class, see step 1), and the 205 functions
that no code calls are listed in the
section "never called" at the end of the report, for the developer to decide per function
whether it is removed or kept

rules for every step:
- one class (or one shared function name) per prompt, so that each diff stays reviewable
- the tests are added to the existing test file of the class (e.g. `unit/word_tests.php`,
  `unit_ui/value_list_ui_tests.php`), the objects come from the test factories
  (`test_words`, `test_values`, …) and the users from `$t->usr1`, `$t->usr2`
- every feature gets a positive and a negative test, and the negative test asserts the
  reported outcome (`msg_id`, empty or `false`)
- a call counts only between a `$test_name = …` line and the last `$t->assert…` of the same
  block, so the tested call stays inside that block and never inside a `$t->assert_…` helper;
  the counter itself stays as simple as it is (`docs/llm/testing.md`, "The test coverage
  counter stays simple"): a function that only a harness helper calls or that shares its
  name with other classes gets its own explicit test instead of a smarter counter
- no source function is changed to make it testable in the same prompt; a function that
  cannot be tested without a change is listed in `docs/llm/pending_prio_2.md` instead
- the LLM never runs the tests; the developer runs them and regenerates the report

1. cover the functions that many classes share, in the order of the number of classes:
   `default_id` (46 classes), `load_sql` (45),
   `db_fields_changed` (44), `db_fields_all` (43), `import_mapper` (38), `load_dummy` and
   `api_array` (37), `name_field` and `fill` (36), `delta` (32), `diff_msg` (28), `url_mapper`
   and `save` (27); one prompt per function name, which adds one positive and one negative
   block per class to the test file of that class; `reset` (67 classes) is not listed as a
   gap, because the counter also counts every `$msg->reset()` of the test blocks, so the reset
   of each class still gets its own positive and negative test
2. cover the classes without database and frontend first, because their tests are fast and
   need no fixture: `html_base` (114 gaps), `sql_creator` (80),
   `sql_type_list` (50), then the frontend forms and lists (`system_form` 87, `db_object` 66,
   `phrase_list` 53, `triple` 52, `phrase` 48)
3. cover the backend model classes with the unit test factories (no database): `sandbox`
   (134), `sandbox_multi` (123), `triple` (97), `user` (95), `phrase_list` (92), `phrase`
   (78), `group` (72), `word` (69), `sandbox_value` (69), `sandbox_link` (69), `ref` (60),
   `data_object` (58), `formula_map` (57), `component` (55), `value_base` (55)
4. cover the functions that need the database in `unit_read` / `unit_write`: `sql_db` (122),
   the `load_*` functions that are left after step 1 and the functions with one call that
   need the database for the second: `value_base::save_as_default`, `view::load_by_term`, web
   `db_object::add_via_api`, `component::log_link`, `config_numbers::load_cfg`,
   `db_cache_page::del_all`, `type_lists::load_cached`, `system_object::system_user` and
   `user_log`, and the `add` that writes the object to the database: `sandbox`,
   `sandbox_named`, `sandbox_link`, `sandbox_multi`, `value_base`, `value_time_series`,
   `triple`, `verb`, `group`, `ref`, `formula_map`, `ip_range`, `change_log` and `change_link`
5. decide on the functions that a unit test cannot reach, e.g. the bootstrap of
   `application` (`start`, `start_api`, `open_db`, `end`) and the functions with one call
   that have no failure branch for a negative test (`frontend::activation_url`,
   `html_base::p`, `import_convert_xbrl::instance_file_name`,
   `change_log_list::load_sql_by_user_value`, `phrase::value_selection`): either a test of
   the request lifecycle covers them, or the counter gets a named exception list with the
   reason per function, which `docs/llm/testing.md` documents and which must stay short
6. keep the gain: a test in `coding_rule_tests.php` compares the covered count of the report
   with a const in `src/test/php/const/` and fails if it drops, so a new function without
   tests is reported at once; the const is raised after each step

if done remove this prompt from pending.md

**next prompt**

**full test run: /test/test.php**

**Prompt:** no `TODO Prio 0` left and the test clean up (size L, three phases). The rules of "TODO" stay; a file that
two steps name belongs to the agent of the lower step, which takes the TODO of the other step as well.
- phase 1, three agents in parallel: agent triage with step 1, agent report with step 2 and agent backend with step 3;
  stop for a run: `/test/test.php`
- phase 2, up to five agents in parallel: step 4 with one agent per group of test files, agent stubs with step 5 and
  agent tests with step 6; stop for a run: `/test/test.php`
- phase 3, three agents in parallel: agent deprecate with the deprecate and merge parts of step 7, agent rename with
  the rest of step 7 and agent clean up with the items 1 to 6 of "clean up"; item 7 is decided by the lead

### TODO

goal: no `TODO Prio 0` is left in the code; each one is either solved (the comment is removed
together with the fix) or, if the work shows that it is less urgent, given a Prio 1, 2 or 3
with a concrete text

starting point: 158 `TODO Prio 0` in src, test, http and api (the paths below are relative to
`src/main/php/` resp. `src/test/php/`)

rules for every step:
- one step or one class per prompt, so that each diff stays reviewable
- the test comes first: a fix gets a positive and a negative unit test (`docs/llm/coding.md`,
  "Testing rules"); a fix that cannot be tested without the database goes to `unit_read` /
  `unit_write`
- a solved TODO is removed, never rewritten to "done"; a TODO that is only partly solved keeps
  the open part with a new priority
- an activated test that fails is not switched off again: the cause is fixed, or the test gets
  a `TODO Prio 1` with the reported diff
- the LLM never runs the tests; the developer runs them and reports the result

1. triage the bare "review" TODOs, because they do not say what is wrong: read the code, then
   replace each by a concrete TODO with a Prio 1, 2 or 3, or solve it if it is a one-line fix:
   `cfg/db/sql_db.php:1282`, `cfg/phrase/phrase.php:931`, `cfg/phrase/term.php:1418`,
   `cfg/result/result.php:2021` and `:2049`, `cfg/user/user.php:2213`,
   `web/component/execute/system_form.php:2826`, `web/frontend.php:701`,
   `unit_write/group_write_tests.php:130`, `unit_write/triple_write_tests.php:387`,
   `unit_write/word_write_tests.php:403`, and `cfg/value/value_list.php:1309` ("review error")
2. report every failure instead of failing silently (top rule 3):
   - read an optional array key with `key_exists`: `cfg/result/result.php:1676`,
     `cfg/sandbox/sandbox_multi.php:1667`, `cfg/system/ip_range.php:323`,
     `cfg/verb/verb.php:706`, `cfg/view/view.php:565`
   - turn the warnings back into errors: `cfg/phrase/phrase_list.php:2309`,
     `web/phrase/phrase_list.php:2174`
   - return or report the failure to the caller: `cfg/db/sql_db.php:3408` (return false),
     `cfg/db/sql_db.php:4748` and `cfg/import/import_file.php:85` (take the `user_message` of
     the caller), `cfg/sandbox/sandbox_typed.php:140` (a not mapped row on `$msg`),
     `cfg/view/term_view.php:563` (use a `msg_id`), `cfg/formula/formula_map.php:698` and
     `shared/calc/expression.php:214` (the ref text check with the user message)
   - log a missing mapper overwrite: `web/sandbox/ListBase.php:121`, `utils/test_lib.php:311`
   - one message per request: `http/about.php:45` (all frontend calls),
     `cfg/component/component_link_list.php:479` (the reset of all lists),
     `utils/test_cleanup.php:290` (a local message for each del call), and the shared message
     status const of `cfg/db/sql_message.php:232` and `cfg/user/user_message.php:238`
3. close the gaps in security and data correctness of the backend:
   - `cfg/db/db_check.php:126`: in prod and test the setup runs only if the setup flag is on
   - `cfg/db/sql_par.php:168`: the unique check includes the keys
   - `cfg/user/user.php:237`: every user field is saved and in the api message, and
     `:732` / `:2280` write each DateTime as `DateTimeInterface::ATOM` with `?` for null
   - `cfg/word/triple.php:572`: the mapper does not save, the save moves to `import_obj`
   - `cfg/sandbox/sandbox.php:1180`, `:1181`, `:1433`: the removed `load_owner` and the
     component `reload_phrases` are called where the load needs them
   - `cfg/helper/db_object_seq_id_user.php:143`, `cfg/helper/type_list.php:316` (save a new
     type), `cfg/sandbox/sandbox_list.php:370` (fill), `cfg/phrase/phrase_list.php:1533` (the
     time type check), `cfg/log/change_link.php:386` and `:412`,
     `cfg/view/view_relation.php:571` (the export if requested), `cfg/sandbox/sandbox.php:3282`
     (use it for the import)
   - the usage field is maintained by the backend, so that the workaround of
     `unit_workflow/triple_url_tests.php:494` and `unit_workflow/word_url_tests.php:758` is
     removed
   - the type cache: `cfg/formula/formula_link_type.php:199` and `:253`,
     `cfg/helper/type_lists.php:288`, `web/sandbox/sandbox.php:123` (the dto cache in the api
     mapper)
4. activate the disabled tests, one test file per prompt, and fix what they report:
   - the unit tests that only need a db write: `unit/group_tests.php:378`,
     `unit/result_tests.php:179`, `:188`, `:199`, `unit/triple_tests.php:121`,
     `unit/value_tests.php:580`
   - `unit_write/triple_write_tests.php` (`:163`, `:208`, `:240`, `:262` "fix it" and `:364`),
     `all_unit_write_tests.php:152`, `api_write_tests.php:88`, `element_group_write_tests.php:197`,
     `element_write_tests.php:125`, `expression_write_tests.php:157`,
     `formula_trigger_tests.php:137`, `formula_write_tests.php:110`, `import_write_tests.php:339`,
     `term_write_tests.php:95`, `view_relation_write_tests.php:57` and `:76`,
     `xbrl_write_tests.php:88` and `:95`
   - `unit_read/formula_list_read_tests.php:83`, `unit_read/value_list_read_tests.php:79` (load
     both values), `unit_api/api_tests.php:215`, `unit_ui/localhost_ui_tests.php:114`
   - the disabled source parts: `cfg/const/files.php:317`, `web/component/component.php:994`,
     `cfg/sandbox/sandbox.php:4523` (activate or remove)
5. replace the frontend stubs by real code, so that each system page shows its content:
   `web/component/execute/system_page.php` (the 14 "fill with real code" and `:561` the title
   of a search result), `ui_base.php:117`, `ui_list.php:4000`, `ui_rank.php:61` and `:71`,
   `system_form.php:3005` (wire up the request action) and `:672` (system to web language),
   `web/formula/formula.php:911` (the formula links in the mask), `web/value/value.php:1372`
   (the source selector) and `:1015` (the database part moves to the backend),
   `web/view/view_exe.php:770` (tooltip and url), `web/system/sys_log.php:71` (the missing
   backend fields), `web/helper/data_object.php:285` (the missing objects e.g. view_link_list),
   `web/word/word.php:288` (all fields in the url); each page gets its html snapshot test
6. add the missing unit tests (together with "test coverage"):
   `cfg/phrase/phrase_list.php:2247` and `:2265`, `cfg/sandbox/sandbox.php:2539`,
   `cfg/word/triple.php:2229` and `:2250`, `shared/helper/ListOfIdNamedCodeObjects.php:204` and
   `:276`, `shared/helper/ListOfIdNamedObjects.php:206`, `shared/json_fields.php:63`,
   `unit/base_object_tests.php:389` (add and unset), `unit/element_list_tests.php:95` (fail
   cases), `unit/type_tests.php:402` and `:403` (language, position and system time types),
   `utils/test_base.php:3259` (the test view relation)
7. deprecate, merge and rename, each with the callers in the same prompt:
   - deprecate: `web/frontend.php:839` (`$usr_backend`), `web/user/user.php:148`,
     `cfg/formula/formula_map.php:224`, `cfg/formula/formula_list.php:847` (remove),
     `web/html/html_base.php:939` (use `url_new`), `:2527`
   - merge: `cfg/application.php:337` with `frontend.php`, `cfg/user/user.php:1586`
   - one way for all: `web/html/html_base.php:1911` (all input fields),
     `web/html/html_selector.php:118`, `web/types/type_list.php:529` (the var order of all
     selectors), `cfg/helper/type_list.php:391` (the load_sql overwrites),
     `cfg/phrase/phrase_type.php:143` (one field conversion), `cfg/user/user.php:627` (an object
     mapper library), `cfg/component/component_type_list.php:53` (load_dummy)
   - consts and config: `shared/const/views.php:1334` (a key value map),
     `shared/helper/Config.php:77` and `:81` (only the fallback of config.yaml),
     `shared/types/component_types.php:362`
   - rename: `web/helper/data_object.php:213` (`$typ_lst`), `unit_write/expression_write_tests.php:114`
     (`user_text`)
   - the test factories: `create/test_formulas.php:335`, `create/test_triples.php:285` and
     `:307` (a to_url function), `create/test_verbs.php:112` (a real filled verb),
     `create/test_views.php:556`, `unit_read/all_unit_read_tests.php:97`,
     `unit/coding_rule_tests.php:1073`
   - the rule of `cfg/word/triple.php:375` ("explicit is better than implicit") is added to
     `docs/llm/coding.md` with this case as the sample

#### clean up

1. The new 'assert should use most specific parameters' rule has no coded check. coding.md says every machine-checkable coding rule gets a check in unit/coding_rule_tests.php. "No assert_true / assert_false / assert(…, false) around array_key_exists" is machine-checkable, but there is no such test, so a new wrapped assert would go unnoticed.
2. The assert-line rule is only partly applied. The new testing.md section says to read keys without ?? and to read nested values on the line before. About 80 asserts in the tests committed in 0dc5b8100 still break it, for example sys_log_tests.php:214 ($vars[json_fields::DESCRIPTION] ?? '') and component_tests.php (($vars[…] ?? null)?->value).
3. Duplicated logic in the two new helpers. assert_key_exists and assert_key_not_exists in test_base.php share their first three steps (lookup, key list, diff). A small private function returning the key list would remove the copy. This is minor.
4. The new helpers have no $exe_max_time parameter, unlike assert_text_contains and the other helpers. So they always use the default timeout, which is fine for array lookups but differs from their siblings. Low priority.
5. Two consts hold the same text: test_const::TERM_VIEW_COM and VIEW_RELATION_COM are both 'add usage and log of a word'. Keeping them separate is right as long as the two fixtures are meant to be independent; just be aware of it.
6. A second message is created mid-test in user_ui_tests.php ("the api url of the system user…"): $sys_msg = new user_message($sys_ui). This only names what was already inline before. The test needs a message for the system user, but the rule says to create $msg once.
7. docs/llm/pending.md has a new "view edit" entry (move the "main + variance" entries to the navbar edit menu). It isn't part of this work; I assume it's yours. Commit it separately or name it in the message.

if done remove this prompt from pending.md

**next prompt**

**full test run: /test/test.php**

**Prompt:** the calculator page in less than 3 seconds (size L, two phases). The rules of "calculator" stay; the
developer measures the debug=9 url before the prompt and in each stop, and the lead writes the times into the report.
- phase 1, four agents in parallel: agent values with step 1 and 1c (step 1b only if the developer's `EXPLAIN` shows
  the regex scans as the bigger part, because it needs a database upgrade), agent names with step 2, agent bootstrap
  with step 6 and agent page cache with step 8; stop for a run: `/test/test.php` and the debug=9 url
- phase 2, three agents in parallel: agent cells with step 3 and then step 4 (both extend the same api calls), agent
  php with step 5 and agent tree with step 7

### calculator

goal: `view.php?m=16&id=-2573` (pv lifetime balance) is created in less than 3 seconds; the
debug=9 log shows 59 seconds (77 before the request cache of the values, commit 5f63abe8b),
of which about 52 are spent waiting for about 120 api calls

where the time goes (debug=9 log):
- 34 s: the 40 `api/valueList/?phrase_ids=…` calls of the cells (13.7 s → 47.5 s), each set
  once now; the 14 sets that contain phrase 289 take 1.2 to 1.4 s each (18 s), the other 26
  take 0.43 to 0.76 s each (16 s), while a `phraseList` call takes 0.13 s, so the value
  query is slow on its own, not only with phrase 289
- 11 s: the 13 input names are looked up three times with
  `api/phraseList/?k=<input name>&pd=up&px=2` (6.9 s → 12.8 s with the 13 single `valueList`
  calls of their values, 48.2 s → 50.6 s and 55.4 s → 57.7 s), because
  `ui_list::add_field_links` loads the related phrases of a name without a cache check; the
  values of the names are loaded once already
- 6.5 s: frontend php between the calls: 3.6 s between "calculator input" rendered (51.7 s) and
  the first call of "calculation result" (55.4 s), 1.1 s and 1.2 s after the last call of each
  of the two components, 0.5 s before the second input name pass
- 4.1 s: the frontend bootstrap: the components of the 195 system views (1.2 s),
  `api/config/?1=frontend` (1.4 s) and `api/triple/?ir=1&id=2573` (1.0 s)
- 2.5 s: the 6 `api/phraseList/?il=…&pd=up|down` calls of the phrase tree of the page, one per
  level (4.2 s → 6.7 s)
- 0.6 s: `api/resultList/?phrase_ids=-2573`
- the page is written to the cache as `db_cache_page_id 0`

rules for every step:
- one step per prompt; measure before and after with the same debug=9 url and write the two
  times into the commit message
- every cache entry is keyed by the complete key (all sorted phrase ids, the full name), never
  a prefix or a hash, and by the user; a known empty answer is cached too, so it is not asked
  again (`docs/llm/coding.md`, cache rule)
- the double calculation check stays: the cached numbers are the numbers of this request only,
  a save still recalculates in the backend
- a new api parameter gets its `url_var` with the `*_HUMAN` twin, an api fixture and a unit
  test with a positive and a negative case
- the LLM never runs the tests; the developer runs them and the debug=9 url

done: request cache for the values of a phrase id set (`data_object::load_values_of`, key: the
sorted ids), so each of the 40 sets is loaded once: 77 s → 59 s

analysed: the value query (`api/valueList/index.php` → `value_list::load_by_phr_lst` with
`$or = true` → `sandbox_value_list::load_sql_by_phr_lst_multi`, fixture
`db/value/value_list_by_phr_lst_all_p5.sql`) returns every value of **any** of the given
phrases, but `formula_calc::value_of` keeps only a value whose phrases are all in the context,
so a set with `annual` (289, about 350 seeded values) loads, names, serialises and caches about
400 rows for the handful the page uses; the statement is a union of 5 tables and the 3 group id
tables (`values_standard`, `values`, `values_big`) are filtered with a regex (`group_id ~`), which
no btree index serves, so they are scanned on every call; the api logs no step at debug level 9,
so the split between query, phrase names, json and cache insert is not visible yet

1. load only the values that the context can use: a new statement
   `load_sql_by_phr_lst_subset` returns the values whose phrases are **all** in the given list:
   prime tables `phrase_id_1 IN (ids) AND phrase_id_2 IN (ids, 0) AND …` (uses the existing
   per-column indexes), group id tables one regex `^(key1|key2|…)+$` per row instead of one per
   phrase; the api gets a url var for the subset mode (with its `*_HUMAN` twin, api fixture and
   unit test), and only `ui_list::add_context_values` uses it, because the field values and the
   table loads need any value of a phrase; the developer confirms the cause first with
   `EXPLAIN (ANALYZE, BUFFERS)` of the prepared statement of the fixture with the ids
   `289,-2573,807,-2529,-2542` and the row count of the result; expected: 34 s → about 10 s
   1b. if the regex scans stay the bigger part of the plan: a trigram index
   (`CREATE EXTENSION pg_trgm; CREATE INDEX … USING gin (group_id gin_trgm_ops)`) on the three
   group id tables and their `user_values*` twins, which postgres uses for `~`; needs a database
   upgrade script and the minor version raise (`docs/llm/versions.md`)
   1c. api timing: `log_debug(…, url_var::DEBUG_LEVEL_MAIN_STEP)` after the load, the phrase
   names, the json and the cache insert of `api/valueList/index.php`, so that
   `api/valueList/?phrase_ids=…&debug=9` shows the split like `view.php` does
2. request cache for the related phrases of a name: `data_object` keeps the phrase list of each
   `phrase_list::load_related_by_name` call (key: the name, the direction and the levels, like
   `load_values_of` for the values), and `ui_list::add_field_links` asks the cache first, so
   the 13 input names are looked up once instead of three times; expected: 11 s → 4 s
3. load all cells in one call: the calculation plan collects every phrase id set of the page
   first and `api/valueList` gets a new parameter for a list of phrase id sets, so the 40
   remaining calls become one; expected: the call overhead of 40 × 0.13 s is gone and the
   query time of step 1 is paid once
4. load the input names in one call: `api/phraseList` takes a list of names (`k`) instead of
   one name per call, and the values of the found phrases come with the call of step 3;
   expected: 4 s → 0.5 s
5. frontend php: find out what the 3.6 s between "calculator input" rendered and the first
   call of "calculation result" do (probably `calculator_numbers` and `calculation_table`
   repeating the page calculation for the cells, `formula_calc::result` per cell) and the
   1.1 s and 1.2 s after the last call of each component; a number that is calculated more
   than once per page is calculated once; expected: 6.5 s → 1 s
6. frontend bootstrap: load the components of the system view of the request only, not of all
   195 system views, and find out why `api/config/?1=frontend` and `api/triple/?ir=1` take more
   than 1 s each (the other calls take 0.13 s); expected: 4.1 s → 1 s
7. load the phrase tree of the page in one call: `api/phraseList/?il=…&pd=up` takes the
   levels (`px`) like the name lookup, so the 6 calls of the tree become one or two;
   expected: 2.5 s → 0.5 s
8. page cache: find out why the page is inserted as `db_cache_page_id 0` and whether the next
   request of the same url gets the page from the cache; if not, fix the insert, so that a
   second request takes less than 0.2 s

if done remove this prompt from pending.md

**next prompt**

**full test run: /test/test.php**

**Prompt:** PV lifetime balance page matches its sample (size L, two phases; re-pin, because `pv_lifetime_balance.json`
changes). The steps of "general" are partly done (the calculator editor exists): the lead checks them first, and asks
the developer the decision of step 2 of "general" (retire or keep the component types).
- phase 1, four agents in parallel:
  - agent save: step 1 of "remaining" and step 4 of "general"
  - agent data: steps 2, 3 and 6 of "remaining" and the monthly table, the replacement mix and the context load of
    "open for the calculator page"
  - agent texts: steps 4 and 5 of "remaining" (the formula sample and the notes)
  - agent checks: the value ranking and the repeated loads of "open for the calculator page"
  - stop for a run with re-pin
- phase 2, one agent: step 7 of "remaining" and step 5 of "general", the comparison of the page with the sample

## PV lifetime balance page

### remaining

the differences of `pv_lifetime_balance_no_js.html` to `pv_lifetime_balance_sample.html`, as steps in
the order of the page; each step is fixed in the data of `pv_lifetime_balance.json` or in the
renderer, never by adapting the sample

1. save - the parts after the save of the changed numbers (docs/llm/frontend.md "The save of a
   calculator page"):
    - write test: a unit_write test that saves one changed calculator number of the pv page as
      the overwrite of `$t->usr1`, checks the shown number, the "changes saved" info and the
      change log, and removes the overwrite again by the logged `del()`
    - api: the save writes through `db_object::update`, which converts the frontend value to the
      backend object; the PUT api call is the open TODO of `db_object::update` for every edit
    - user without login: the save is refused by the backend and the page shows the message; the
      save button could instead say "login to save" before the user enters numbers
    - the selected option of a phrase selector (e.g. the replacement mix) is not saved, because a
      selector names a phrase and not a value; saving it needs a value or a triple of the user
    - the confirmation names only the number of saved values, not which numbers have changed
      from what, as the settings page does (`frontend::setting_saved_text`)
2. monthly table: the twelve month rows Jan … Dec in their order with the short month names,
   the numbers without decimals (701 instead of 701.23, 106 instead of 105.83) and the monthly
   Swiss mix in the column "CH mix g/kWh" (151, 147, …), which has no page phrase today
3. assumptions - the parts that the json cannot solve:
    - the tags "assumed 0.5" of the battery production emissions and "assumed 0.6" of the
      battery lifetime: the confidence value has the phrases of the value without its unit (a
      value has one unit only), so the lookup of the confidence must ignore the unit phrase
    - the source numbers in a hint: "Swiss 3 kWp mono-Si pitched roof, 2023 update [1];
      converted to a fixed amount per kWp" with the number inside the hint, and the two sources
      "[5][6]" of the battery production emissions
    - replacement mix: the option labels with the number and the source ("European residual mix
      — 523 g/kWh [4]", "ENTSO-E production mix 2022 — ~395 g/kWh (midpoint of 350–440) [3]")
      and the residual mix as the first option: the triple "production mix order" (production
      mix is follower of residual mix) is in the json, but the selector does not use it
    - the three derived assumptions (production mix 395, residual mix 523, east-west yield
      factor 0.85) are not shown, because they are options of the selectors already
    - the input of the load share during PV hours has no `step="0.05" min="0" max="1"`, although
      the json has the field step and both field limits of "day share"; the field steps of the
      other inputs are shown, so the property lookup fails for the value group of the day share
4. formula sample - add parameter 'show formula sample' to the field 
   and create a sample based on the formula and the database values -   
   table, sub texts: "10.0 kWp × 976 kWh/kWp × factor 0.85" for the PV production, "1'048
   kg CO₂-eq/kWp → 42.1 g/kWh at this site" for the PV system, "2 × 10 kWh × 100 kg/kWh" for
   the battery and "payback 12.1 a" / "14.7 a" / "3.0 a" for the three saving rows; the labels
   of the saving rows name the mix: "(128 g/kWh)", "(106 g/kWh PV-weighted)", "(523 g/kWh)"
5. notes: only the three notes of the page with their own titles and sources ("How the CO₂
   saving is counted.", "Seasonal Swiss mix. … [3]", "Self-consumption model.") in `strong`,
   instead of the description of every formula (19 notes with the formula name in `b`)
6. sources - just add sources - more sources is an excepted difference:   
   the seven sources of the sample in the order of their first use ([1] IEA PVPS,
   [2] BAFU, [3] treeze, [4] Swissolar, [5] J. Industrial Ecology 2025, [6] HTW Berlin 2019,
   [7] gruenes.haus) with their full citation as the link text and `target="_blank"
   rel="noopener"`; today five sources with the short source name, gruenes.haus first and
   the two battery sources missing
7. the comparison: let the use case test assert the generated page against the sample with
   a normalisation of the differences that are design and not content: the html ids and
   names of the fields (`n-2526` instead of `kwp`), the hidden group ids, the `form`
   attributes and the save form, the cell ids, and the calculator data with `calculator.js`
   instead of the inline script of the sample

### general

the generated page `src/test/resources/web/html/use_cases/pv_lifetime_balance_no_js.html` should
match the hand made sample `pv_lifetime_balance_sample.html` without hardcoding, generated
from the database of the use case json; the calculation follows the double calculation check
of `docs/llm/frontend.md`. the sample is the target and is never adapted to the renderer.

the page is the system view `Calculator` with the components `title calculator`,
`calculator input` and `calculation result` for `example PV installation`, configured by the
calculator triples of `pv_lifetime_balance.json` (guide: `docs/llm/new_view_sample_pv_balance.md`).

run the steps one at a time, each as its own commit with the tests written first, and ask for
the test run after each step (an llm never runs test/*, docs/llm/coding.md).

1. the values and results in `src/test/resources/web/html/use_cases/pv_lifetime_balance_no_js.html`:
   when the page is created, all values and results of the page are already in the database
   (imported from `pv_lifetime_balance.json` and calculated), so the page shows the numbers of
   the input fields, the main result table, the detail tables and "PV life-cycle emissions"
   from the database; each number in the html carries the database id or group id of its
   value or result, so that the save action (step 4) knows which value to update and which
   result to compare; the numbers, the hidden group id of each input and the cell ids are on
   the page, still empty are the rows "Self-consumption rate", "Autarky" and "vs. replacement
   mix" (the residual mix net avoided emission 113'805) and the assumption "PV life-cycle
   emissions"; the lifetime column shows kg, the sample t
2. delete `docs/llm/samples/new_view_sample_pv_balance.json` and point the links of the guide
   and of `docs/llm/json_views.md` to `calculator_keywords.json` and
   `pv_lifetime_balance.json`; decide whether the component types that only the old view used
   (`view_title`, `name_sub_title`, `details_start`, `details_end`, `assumption_fields`,
   `formula_notes`, `sources_used`, `table_with_own_columns`) are retired or kept for other
   use case views, because retiring them shifts the component type ids
3. the `Calculator editor` view with the components of section 6 of the guide and a snapshot
   test of the editor for `example PV installation`
4. the save: write the inputs as values by the ids of step 1, use the entered numbers as the
   inputs of the page calculation, start the backend calculation job, compare the job results
   with the frontend results and show every difference as a message
5. when the generated page matches the sample up to the numbers, let the use case test assert
   the generated page against the sample

open for the calculator page:

- judge the page only after a database recreation: `test_part.php` imports into the existing
  database and never removes an object that a newer json left out
- inputs: the maximum of the three number fields
- monthly table, column "CH mix g/kWh", month rows: the monthly Swiss consumption mix values
  (e.g. 150.8 for January) have no page phrase, so a month cell (page phrase, month, `consumption
  mix`, `gram per kWh`) selects none of them; the column needs a way to select a general value,
  e.g. a calculator keyword for a column without the page phrase
- replacement mix selector: the options come from the `is a` triples instead of the values of
  the selector phrase, and the gross formula uses the selected mix
- context load: the derived assumptions and the sources are loaded by any phrase of a result,
  so values and sources of other files show up (PVGIS 5.3, BEA US GDP 2023) and the field
  definition values need a filter; e.g. the assumption "Swiss consumer mix, annual" shows the
  79.2 of `pv_switzerland_co2.json` instead of the 128 (BAFU 2018) of `pv_lifetime_balance.json`,
  and "PV life-cycle emissions" stays empty although the file has 35.8 (IEA PVPS 2023); a result whose values the page has not loaded keeps its
  stored number; an api that answers with the values within a phrase set would be leaner
- monthly table: the months in their order (January … December, not by number) and the short
  names "Jan" … "Dec" (open point of the guide)
- notes: only the notes meant for the page, with their own titles (open point of the guide:
  a `calculator note` list or notes as descriptions of the row groups)
- sources: numbered in the order of the first use and cited as [n] in the hints and notes
- value ranking: `ui_list::context_count` has no unit test, only the `run_pv_lifetime_balance`
  snapshot covers it; add a case in `use_case_ui_tests`: a general value against a value with a
  place phrase
- repeated loads: `view_exe::add_page_cache` runs twice per request (in `frontend::render_url`
  and in `view_exe::show`); the `is_calculator()` guard stops the second run only if the first
  has found calculator triples, so for a phrase without them that is opened with the
  `Calculator` view all api loads run twice

if done remove this prompt from pending.md

**next prompt**

**full test run with re-pin: first /test/reset_db.php, then at least twice /test/test.php**

**Prompt:** navbar menus and the order of the start page problems (size M; re-pin, because the problem files change).
The column entries are in the edit icon menu already and are named "+ variance" (see the table page snapshots), so
the lead checks "view edit" for the "... more" limit only. One phase, three agents in parallel:
- agent menu: the rest of "view edit" and "import / export", the import and export entries of the user menu
- agent problem order: "start page", the numbers of the problem json files in the listed order
- agent menu test: the snapshot and the negative test of the "... more" limit from `config.yaml`

## view edit

move the entries like 'main + variance' from the '...' menu in the table component to the 'edit icon' menu in the navbar
if there are more than 5 (config.yaml value) entries in one group show a '... more' entry in the menu

## import / export

add to the user menu in the navbar an import and an export entry that links to the import and export view (id 145 and 150)

## start page

adjust the numbers in the json data files so that the problem / solution are in this order

1. Establish and enforce rules against disinformation
2. Enable effective climate protection through a public carbon border tax
3. Use a neutral knowledge base for decision-making
4. Cap wealth on ethical grounds and use it to fund a basic income
5. Don’t use AI for complex problems or for interpersonal communication
6. Prevent monopolies with a market-share tax
7. Accept that health matters and finance it in a socially fair way
8. Achieve digital sovereignty through free software
9. Use smartvote for initiatives and build a liquid democracy
10. Measure the economy’s actual benefit instead of relying on GDP

if done remove this prompt from pending.md

**next prompt**

**full test run with re-pin: first /test/reset_db.php, then at least twice /test/test.php**

**Prompt:** process catch views and the process editor (size L, three phases; re-pin, because system phrases, views
and the PV calculator use case are added). "LLM KI prompt suggestion" is the plan, "PV installation calculator" the
background; the "process editor" of "other editors" is phase 3.
- phase 1, lead: step 1, the system phrases of a field definition; stop for a run with re-pin
- phase 2, two agents in parallel: agent model with step 2 and agent components with step 3; stop for a run:
  `/test/test.php`
- phase 3, three agents in parallel: agent views with step 4, agent use case with step 5 and agent editor with the
  process editor built on the calculator editor; the lead writes step 6, the docs, after the three

### PV installation calculator

Catch process forms

create a 'process catch start page' view that has the components 
'title process catch start', 'text explain process catch start', 
'process catch initial input fields' and 'button next process catch start'. 
The 'process catch start page' has one easy to answer question 
with a next button that leads to the 'process catch main page'.

create a 'process catch main page' view that has the components 
'title process catch main', 'text explain process catch main', 
'process catch main input fields' and 'button next process catch main'. 
The 'process catch main page' has around three input fields that
are most relevant for the result calculation and 
for complex answers offer a limited number of pre-selection options.  
The next button leads to the 'process catch detail page'.

create a 'process catch detail page' view that has the components 
'title process catch detail', 'text explain process catch detail', 
'process catch detail tab one', 'process catch detail tab two', 
'process catch detail tab three', 'process catch detail tab four' and 'process catch detail tab five'
and 'process catch detail tab one fields', (and '... two, three, ... five)
with a next button that leads to the 'process catch result page'.

create a 'process catch result page' view that has the components 
'title process catch result', 'text explain process catch result',
'process catch result tab one', 'process catch result tab two',
'process catch result tab three', 'process catch result tab four' and 'process catch result tab five'
and 'process catch result tab one fields', (and '... two, three, ... five)
with a back button that leads to the 'process catch detail page'.

for each field these triples should be created:

- '... field label phrase' e.g. 'place of the PV installation' for the place / sun intensity
- '... field value phrases' e.g. 'PV installation location' to store the field input value in the database
- '... field type' e.g. location selector
- '... field unit' e.g. kWh
- '... field limit up' 
- '... field limit down'
- '... field limit steps'
- '... field limit suggestion'
- '... field phrase selector label' to describe what the selected phrase will be used for e.g. if the parent is 'main column (system)' the label could be 'Main table columns:' 
- '... field phrase selector parent' to preselect the phrases that can be selected e.g. if the parent is 'column (system)' all columns related to the phrase are shown so that one can be selected to define the default sorting 
- '... field phrase selector value based' to preselect the phrases that can be selected e.g. if the phrase for the table is 'global warming solution' the phrases of all values related to it are shown 
- '... selection label' to describe what a sorted list of phrases mean e.g. if the parent is 'main column (system)' the label could be 'Main table columns:'
- '... selection parent' to show the table columns of a phrase e.g. if the parent is ''
- '... button next tooltip' with the text for the tooltip (the text in the button is a msg_id defined by the component not a triple)
- '... button next view' with the view id for the step 
- '... button refresh tooltip' with the view id for the
- '... button refresh label' with the view id for the
- '... button refresh view' with the view id for the step
- '... button back tooltip' with the view id for the
- '... button back view' with the view id for the step

For each phrase e.g. "PV Calculator" each field tripel a phrase or a list of phrases can be assigned via triples


Unsorted notes:

based on the start phrase the title text, the label and type of the input field and the phrases for the input field 
are assigned to the start phrase via triples

e.g. if the catch phrase is called with the phrase "PV Calculator" the Place / PO Box selector is shown

the next page contains the up to 3 fields with a preselection that are most relevant for the calculation and a next button

e.g. for the phrase "PV Calculator" it is the type or size of the panels and the electricity consumption per year

depending on the selection e.g. 10 qm sub-fields are shown e.g. the vertikal and horizontal orientation

the third page shows some calculated values with tabs to fine tune the assumptions but not yet the final report and a button 'calculate'

the fourth page shows the earnings in EUR 

This means that these system phrases with a code_id are needed:

'process catch field label phrase (system)' e.g. place of the PV installation 
catch values phrases (system) e.g. PV installation location  
catch fields type (system) e.g. locator
main values phrases (system) e.g. PV installation size


create a component type 'geolocator by place name / post code' with the parameters: 'label' (which is a translatable 'place name / post code') and the value phrases (which are 'place of user')

Horizontal angle

Vertical angle

Eigenverbrauch

### LLM KI prompt suggestion

the goal is a guided calculation for a question phrase like "PV calculator": four process catch
views (start, main, detail, result) whose fields are not coded but defined as data, so that any
question phrase gets its own calculator by a json import. the views and the component types are
system data (system_views.json, code_id, docs/llm/json_views.md), the fields of a question are use
case data without a code_id (docs/llm/json_structure.md "Use case files never carry a code_id")
and the numbers are calculated by the formulas of pv_yield.json. the entered numbers are saved as
values of the user with the existing value save, so the result page is the calculator view of the
question phrase and every page is url state without javascript (docs/llm/frontend.md). run the
steps one at a time, each as its own commit with the tests written first, and ask for the test run
after each step (an llm never runs test/*, docs/llm/coding.md).

1. the system phrases of a field definition: add to the system word and triple data the phrase
   'process catch field (system)' and the properties of a field as triples with a code_id in
   shared/const/triples.php (like triples::SYSTEM_COLUMN_MAIN): 'field label', 'field value
   phrases', 'field type', 'field unit', 'field limit up', 'field limit down', 'field step' and
   'field suggestion', plus the field types 'location selector', 'number with limits' and
   'pre-selection' linked with "can be" to 'field type' like the column tiers. use only the
   verbs of docs/llm/json_structure.md "Allowed verbs"; a property that fits no verb gets one by
   "Adding a new verb", never a free text. add the triple_names const and the test factory of
   each phrase, a unit test that the import creates them with the code id and re-baseline the
   pinned ids from list.csv (docs/llm/testing.md). document the pattern in json_structure.md.

2. the field model of the frontend: a class web/component/catch_field.php that is built from
   the request cache only (docs/llm/frontend.md "The frontend never accesses the database"): for
   a question phrase and a step (start, main, detail tab n, result tab n) it returns the fields
   assigned to that step by the triples of step 1, each with its label, value phrases, type,
   unit, limits, step and suggestion. the fields of a step keep the order of their phrase ids.
   unit tests with a test_phrases factory list for every property, a missing property and a
   field assigned to two steps (shown on both).

3. the component types: add to shared/types/component_types.php and the system component data
   'process catch fields' (the fields of the step named by the component code id as form
   inputs: a text input with the postal codes of the cache as datalist for the location
   selector, a number input with min, max and step for the number with limits and a select of
   the option phrases for the pre-selection, the suggestion as the preset value), 'process catch
   tabs' (the tab names of the detail and the result step as links that carry the selected tab
   as a new url var with its human twin and the url_mapper branches, docs/llm/state-and-
   messages.md) and 'process catch results' (the results of the question phrase as the table
   with related columns of the calculator view). every renderer gets its object_pages snapshot
   (docs/llm/testing.md "Every HTML-returning function ... contributes to an object_pages
   snapshot"); a label is the phrase name, so it is translated like every phrase.

4. the four views in system_views.json with the code ids process_catch_start, process_catch_main,
   process_catch_detail and process_catch_result (views.php consts and views::TEST_VIEW_IDS, so
   the views_by_id snapshots cover them): title, explain text, the fields component of the step,
   the tabs where the step has tabs and the next button, the result page with a back button. the
   next button submits the form: the frontend saves each entered number as a value with the
   value phrases of the field plus the question phrase for the logged in user (the existing
   value save api, docs/llm/frontend.md "Form field name is the url var"), reports a rejected
   number on the $msg of the request and redirects to the next view with the question phrase
   as id. an anonymous user gets the ip user rules like every other change.

5. the PV calculator use case: a new use_cases/pv_calculator.json appended to
   files::USE_CASE_FILES with the question word 'PV calculator' and its fields: start 'place of
   the PV installation' (location selector, value phrase postal code, which selects the
   irradiation of pv_yield_by_country.json); main 'PV installation size' (number with limits in
   m2, suggestion 10), 'panel type' (pre-selection) and 'electricity consumption per year'
   (number in kWh, suggestion 4500); detail tabs 'orientation' (horizontal and vertical angle of
   the panels), 'self-consumption' (self-consumption share), 'prices' (household electricity
   price and feed-in remuneration preset from the country data); result 'PV return' in EUR per
   year by the formula 'PV return by self-consumption share' of pv_yield.json. the file imports
   for a normal user and names no code_id and no id. tests: assert_view_by_name for every step
   with the use case data and a workflow test under src/test/php/unit_workflow (next free
   workflow id in test/php/const/workflows.php) that walks start, main, detail and result and
   snapshots every page (docs/llm/testing.md "Unit workflow tests snapshot every step"), plus
   the write workflow that checks the saved values of the user.

6. docs: json_views.md gets the component types and the four views, json_structure.md the field
   triples with the pv_calculator.json example and frontend.md the catch process rule: one easy
   question on the start page, at most three fields on the main page, the fine tuning in tabs
   on the detail page and the money on the result page; pending.md loses this section.

if done remove this prompt from pending.md

**next prompt**

**full test run with re-pin: first /test/reset_db.php, then at least twice /test/test.php**

**Prompt:** new use cases, the code id rule, the calculator data, config value 0, the import time, the job list and
the phrase default view (size L; re-pin, because new use case files are imported). "to recheck" are review notes of
an old change: the lead checks each, fixes point 3 and 4 if still open and removes the rest. One phase, six agents in
parallel:
- agent use cases: "use cases", the CBAM and the company sheet use case and the pinned views in `config.yaml`
  (portfolio split is pinned already)
- agent code id: the first part of "clean up", the coding rule test that production code selects a db row by its
  code id only
- agent calc data: "calculator data only with the numbers the page uses"
- agent config: "config value 0 is treated as missing", backend and frontend
- agent import time: "unlink import chain time measurement", the counters reset per import file
- agent pages: "job list" and "phrase default view" (the jobs entry of the user menu and the pie chart)

## use cases

add CBAM calculation, portfolio split and company sheet use cases and pin them on the start page

## clean up

db row identification should not be based on the database id instead the code_id should be used. *_ID pinned db rows e.g. words should only be used for tests.

### calculator data only with the numbers the page uses

`ui_list::calculator_number_plan` writes every number of the request cache into the `calc-data` json of a calculator page, also the values of other use cases (e.g. solution priorities, city values). The PV lifetime balance page carries 467 numbers, but shows about 30 cells and has 75 inputs, which adds about 15 KB to each page.
Suggestion: write only the numbers that a cell, an input or a result token of the page references (`cells`, `inputs`, `results`), and the results that a cell shows or another result uses. Test positive (a referenced number is in the data) and negative (a value of another phrase is not), then regenerate `pv_lifetime_balance_no_js.html`.

### config value 0 is treated as missing

`config_numbers::get_by` (backend) returns the fallback if the config value is 0, so an explicit 0 or `false` in `config.yaml` is ignored whenever the fallback is not null (e.g. a switch with a true fallback). The workaround is `get_by_names` (see `config_numbers::pod_switch`).
Suggestion: only a missing value (null) is missing, so `get_by` uses the fallback only if the value is null and returns a configured 0 as 0. The guard against an unwanted 0 (e.g. a zero list limit) moves to the getters that need it. Align the frontend `web/helper/config::get_by` to the same rule. It already keeps 0 only by chance, because `$val->value($msg)` returns the formatted text "0" and PHP 8 does not treat "0" as equal to null. Then remove the workaround note in `docs/llm/architecture.md` (*Using a config.yaml setting in a script*, step 3).

## unlink import chain time measurement

If more than one json is imported make it optional either to count the percent load of all import files or per json file.
As the log below shown it seems that at the moment the elapse time is nor resetted for each import json. In a first step reset all count parameters for each json import file so that the percent ist always the person of load per json file.

455.2125 import time_definition.json (5 kBytes)
455.7386 import time_definition.json 100% 18.77s / 18.77s  check 4739 formulas (42975.6 formulas per sec vs. 5)
455.7386 import time_definition.json done: 4739 byte
455.7397 import base_phrases.json (6 kBytes)
456.1197 import base_phrases.json done: 6144 byte
456.1209 import base_views.json (10 kBytes)
456.4491 import base_views.json done: 9884 byte
456.4501 import solution_prio.json (105 kBytes)
456.9500 import solution_prio.json 54.6% 19.981s / 36.629s  count 9 results (665.8 results per sec vs. 480) e.g. poverty,reward ratio,trillion,EUR per person year,high: 0.025 (group_id ) for user 1 (zukunft.com system)
457.7437 import solution_prio.json 60.3% 20.775s / 34.426s  Add 155 words (324.2 words per sec vs. 340)
458.3303 import solution_prio.json 64.2% 21.361s / 33.279s  Add 68 triples (240.9 triples per sec vs. 300)
458.8901 import solution_prio.json 67.7% 21.921s / 32.383s  Add 20 triples (314.7 triples per sec vs. 300)
459.4951 import solution_prio.json 70% 22.526s / 32.192s  check 176 triples (38451.8 triples per sec)
460.0975 import solution_prio.json 77.6% 23.129s / 29.795s  save 1 values (100.3 values per sec vs. 300) e.g. global,human,population,billion: 8.5 (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4 = 273,198,159,14) for user 1 (zukunft.com system)
460.6002 import solution_prio.json 79.5% 23.631s / 29.709s  Add 4 words (429.6 words per sec vs. 340)
461.1050 import solution_prio.json 79.6% 24.136s / 30.306s  save 33 results (214.1 results per sec vs. 210) e.g. citizen participation,reward ratio,trillion,EUR per person year,high: 0.225 (group_id 10-0M-06-2H+32+) for user 1 (zukunft.com system)
461.1681 import solution_prio.json done: 104746 byte
461.1695 import country.json (9 kBytes)
461.8019 import country.json 100% 24.833s / 24.833s  load 9 formulas (2744 formulas per sec vs. 4000)
462.3377 import country.json 100% 25.369s / 25.369s  load 1 components (1149.4 components per sec vs. 4000)
462.3548 import country.json done: 8542 byte


## to recheck

1. Two steps in one commit. Steps 3 (calculator reader) and 4 (default view of a phrase) are uncommitted together, but pending.md asks for one commit per step. Splitting is possible because the step 4 changes are in separate files and hunks: cfg/word/triple.php, web/word/triple.php, web/phrase/phrase.php, frontend.php, horizontal_tests.php, triple_tests.php, phrase_ui_tests.php, system_view_ui_tests.php, the guide, the api fixtures and the snapshots.
2. Unrelated pending.md section. The new "## speed up" section doesn't belong to either step; better as its own commit.
3. Extra backend call. In normal mode, frontend::phrase_view_id loads the phrase from the backend for every url with id and without m. It isn't served from the request cache.
4. Hard-to-read condition in dbo_for_url (frontend.php:2710). It reads the url id with ?? 0 and then also checks array_key_exists. It is correct, but a local $url_id would make it easier to read.

## job list

add to the user submenu of the navbar an entry 'jobs' that links to a view that shows an overview of the jobs of an user

## phrase default view

add e.g. a pie chart to the default phrase page with the top impact on the phrase e.g. for the 'global warming' default page show the 'co2-eq emissions by sector'

if done remove this prompt from pending.md

**next prompt**

**full test run with re-pin: first /test/reset_db.php, then at least twice /test/test.php**

**Prompt:** admin dashboard (size L, two phases; re-pin, because new job types and statuses are seeded). The error
log view and the admin jobs of "deprecate http_old" (its steps 4 and 6) and the `sys_log_detail` view of "fill the
language, log, import, ..." are done here. All agents add their own functions to
`web/component/execute/system_page.php`; the lead merges the dashboard layout.
- phase 1, five agents in parallel:
  - agent health: "system health", the read only button, the ip and email lists, the health section and the sanity api
  - agent errors: "errors", the backend selection, the sys log detail page and the summary line
  - agent logins: "failed logins", the ip address from the entry point, the db test and the retry delay
  - agent usage: "usage and settings"
  - agent security: "security", the whitelist state and the blacklist with the change log
  - stop for a run with re-pin
- phase 2, one agent: "jobs", the job views, the recalculation job, the reload of the base configuration, the config
  change requests and the calc status, each start refused for a non admin

## admin dashboard

### system health

add after each user of the admin dashboard column "Server time per user" (system_page::admin_user_load)
a button that sets the user status to user_statuum::READ_ONLY: enforce the read only status in
cfg user::is_blocked, add an admin only status change to api/user (refuse system users) and a frontend
action like action_job; check before that the server-to-server put sends the csrf header
(rest_call::set_session_token is only called in the tests)

add to the admin dashboard a list of top 10 (config.yaml value) ip-address ranges that have done most requests
and one list with ip-address ranges that have done most db updates (the threshold for a critical number is also a config.yaml value)
after each user should be a button that moves the ip_address to the blacklist

add to the admin dashboard a list of top 10 (config.yaml value) email servers that have done an account opening
(the threshold for a critical number is also a config.yaml value)
after each user should be a button that moves the email server to the blacklist

add to the admin dashboard a system health section with the release number
(read from the git versioned file of pending_prio_2.md "move the release number in a file"),
the db version, the result of cfg/db/db_check, the age of the config cache
and whether config.yaml is newer than the cache, and the number of cached pages;
use the same data for an admin only sanity api that monitor tools like checkMK can call
(docs/todo.md "create a sanity API for monitor tools")

add to the admin dashboard the number of user sandbox rows that are equal to the standard value and can be removed (the del_usr_cfg() cleanup of the dsp_sandbox_* family that belongs to the system consistency checks, see pending_prio_2.md) and the number of possible duplicates (input of find_duplicates), each with a link to the cleanup job resp. the list

### errors

show in the admin dashboard the last and most relevant system errors e.g. if there is an fatal error not yet solved
show it on the top of the list and below not yet solved errors followed by warnings

move the selection of the unassigned errors and the pending error fixes from the frontend to the backend: add to cfg/system/sys_log_list::load_sql a filter by status and by solver (none = unassigned), let api/sysLogList use the requested type instead of ignoring it, add the sql fixture for each new query and replace in web/component/execute/system_page.php::admin_errors_unassigned() and admin_errors_delayed_fix() the client side filtered() on the newest sys_log_list::ADMIN_READ_SIZE entries by the backend selection, so that an older open error of a pod with many open errors is also shown

fill the sys_log_detail view (the link target of error_log, error_update and the admin dashboard) and web/component/execute/system_page.php::error_log() with the page of one sys log entry: error_log() of the error log view (m=65, views::ERROR_LOG_ID, linked from the critical error message of cfg/log_text/text_log_functions.php as /http/view.php?m=65&id=<sys_log id>) returns only 'error_log placeholder' (snapshot src/test/resources/web/html/views_by_id/sys_log/65_sys_log_2.html), while web/system/sys_log.php::page_view already creates the page of one error entry and has no caller any more since http_old/error_log.php is retired; load the sys log entry by the id from the url, show it and add a page test; link each row of the dashboard error tables to it and allow the admin to assign the error to themselves, to set the status and to add a solution text; a normal user may only see their own errors

add to the admin dashboard a small summary line above the error tables with the number of open errors per sys log level (fatal, error, warning), the number of new errors in the last 24 hours and the number of error emails sent and suppressed today (see the system@zukunft.com email account in the section 'email accounts', max 5 per day by config.yaml)

### failed logins

user::login writes each failed login as a forced warning to sys_log (text sys_log_functions::LOGIN_FAILED_TEXT, the typed name as description) and the admin dashboard groups them by the typed name; still missing: the ip address (sys_log has no field for it and user::login must not read $_SERVER, so the entry point has to pass it), a db test that a failed login writes the sys_log row, the increasing login retry delay of docs/todo.md ("add an increasing login retry delay to avoid brute force attacts") based on these rows, and a selection of the failed logins in the backend instead of the newest sys_log_list::ADMIN_READ_SIZE entries

### jobs

link each job of the delayed jobs column to the job_detail view

fill the job control view (m=83, views::JOB_CONTROL_ID, process_list placeholder) and the job async view (m=82, views::JOB_ASYNC_ID, process_progress placeholder) in web/component/execute/system_page.php with the job list, the status of each job and the progress in percent of the running job (this replaces the ob_flush progress stream of the retired http_old/calculate.php), link them from the admin dashboard and add a page test

add a job type that recalculates all formula results (shared/types/job_types.php has only the per object triggers like value_update and formula_update; the queue building of the retired http_old/calculate.php with result_list::frm_upd_lst and formula::calc can be taken over) and let the admin start it from the job control view (m=83); it must run as an async job and not block the http request; the old controller was a denial of service target, because any user, even an ip user without a login, could start it by a GET request, so the new job must be startable only by an admin and needs a test for the negative case

add the 'reload the base configuration' action for an admin (import_file::import_system_data, the action of the retired http_old/async_process.php and the 'Force reloading the base configuration e.g. to check that the units definition are still OK' of http_old/user.php; see also the TODO in cfg/import/import_file.php) to http/server_admin.php or as a job type, with a confirm step, because the import overwrites the system objects; run it as an async job whose progress is shown in the job async view (m=82), not synchronously in the http request as the old page did, and add a test that a normal user cannot start it

add to the admin dashboard the open 'config change request' jobs of the users (see the section 'fill the configuration and quarantine views') with the config phrase, the requested value, the current value and an accept and a reject button; an accepted request changes the default of all users like the admin setting change of the settings page

add the calc_status view (outdated results and calculation progress) and show on the admin dashboard the number of outdated results with a link to it

### usage and settings

add to the admin dashboard a section with the x (config.yaml) most changed user settings: the config phrases with the most user overwrites, the number of users who changed it, the default and the most used user value, so that the admin can decide to change the default (docs/todo.md "the admin dashboard the x most changed user settings")

add to the admin dashboard a usage section with the number of active users today, in the last 7 and 30 days, the new signups and the number of changes per day by object type, read from the change log by one backend load with its sql fixture

add a section with the number of connected pods, their usage and the most likely and the latest SPAM posts (docs/todo.md "create an admin page with the numbner of connected pods, the usage an the most likely and latest SPAM posts"); show "not yet recorded" as long as the pod connection is not implemented

### security

add to the admin dashboard a security section that shows the state of the ip and user whitelist mode (server_admin/state.json), the newest entries of the database user and ip blacklist with the reason and the time and a remove button for each entry, and an input to add an ip address or range to the blacklist (docs/todo.md "allow admin users to change IP blacklist"); every change is written with the change log

if done remove this prompt from pending.md

**next prompt**

**full test run with re-pin: first /test/reset_db.php, then at least twice /test/test.php**

**Prompt:** group id follow ups, email accounts, the value and word edit views and two import and triple details (size
L; re-pin, because the value line component is split into new system components). "todo from group id change" are
review notes: points 6 to 9 are checked and removed by the lead, points 1 to 5 are fixed. One phase, six agents in
parallel:
- agent group id: points 1 to 5 of "todo from group id change"
- agent email: "email accounts", the two accounts in `.env.example` and the error mail limit of `config.yaml`
- agent value line: "add value", the split of `system form phrase value line`
- agent edit mode: "word default view", the edit mode url vars with their numeric prefix, the inline edit and add of
  values, the formula unlink and the triple list with its add icon
- agent names: "json import: word and triple names", the import check with its exceptions
- agent triple view: "triple view", the add value and add formula icons

## todo from group id change

Things to fix or check
1. result_id.php docblocks are stale. get_id() and alpha_num_result() still say "512-bit key as 112 chars or list of more than 16 keys with 6 chars" and "the 512 bit db key …", next to the new compact-key line (your on-disk edit kept both). The old lines are now wrong.
2. group_id.php header typo: the table list numbers values_big as "1." instead of "3.". It's in the comment I edited, but the typo was already there.
3. The unit majority counts every number. split_by_unit counts low/high range numbers as well, so a unit stored with ranges counts up to three per row against one per row for another unit. That's fine for the start page today; counting distinct rows would be exact.
4. The MySQL search needs MySQL 8. REGEXP_LIKE(…, 'c') doesn't exist in MariaDB, which docs/todo.md names as a possible target.
5. pg_unpadded does a little per-row work. It checks the field types of every Postgres row. PHP caches the type names, so it's cheap, but the char columns could be found once per result instead.
6. The group_id_url.php header says "see docs/llm/group_id.md", which is only correct once that new file is committed. It's already staged (AM), so this just means committing it together.
7. Three pieces are new or unverified. pg_unpadded, the main-delete fix and the unit-majority rule are only lint-checked, and MySQL is untested throughout. A full test.php run after the DB reset and syncing /var/www/html is still needed.
8. The start-page snapshot will still differ from the committed one. Expect trillion EUR in the header with the calculated results (2.2, 4, …), not the old values.
9. The memory-recorded no-plain-delete rule is followed. result::del_row_of_other_table deletes without a change log, but results are derived data (MAIN_CLASSES_NO_CHANGE_LOG), which matches the documented exception.


## email accounts

add to .env (.env.example) a system@zukunft.com email account that is used to email admin@zukunft.com any time a system error has accused but max 5 (config.yaml parameter) emails per day.

add to .env (.env.example) a noreply@zukunft.com email account that is used to send non signup messages to the user e.g. if they have reached a top ranking in the user trust list 

## add value

split the 'system form phrase value line' component into separate components if possible e.g. one component for the 'selected phrases', the 'add phrase selector' the 'find and select phrase button' and the 'value type selector' 

## word default view

in the values column of the word and triple default view show after the column title 'values' additional an edit icon that is grey if no user is logged in. If a user is logged in the section of the values will change to "edit mode" which means that after each value an edit icon is shown, that can be used to inline change the value. This probably implies that an editional set of edit mode parameters needs to be added to the url_var. The suggestion is to use a numeric prefix for the edit mode parameters

if in the word and triple default view the edit mode add a plus icon at the end of each value group that can be used to inline add new values. if the puls icon is pressed the group header plus all common phrases of the group are shown in one additional line in the table. Another additional line shows a phrase selector to add additionalal phrases for the new value. the next additional lines show a field for the values and the value type and a cancel and save button. 

edit icon for formulas that allow to unlink formulas

add a list with the triples and a plus sign to add a new triple

## json import: word and triple names

add to the JSON import check that word and triple names in English start always with a small letter with a few exceptions based on triples on the same json import file: if the name is a symbol or a country 

## triple view

add values icon and add formula icon

if done remove this prompt from pending.md

**next prompt**

**full test run with re-pin: first /test/reset_db.php, then at least twice /test/test.php**

**Prompt:** every number of the start page is a result (size L, two phases; re-pin, because the problem files change).
The global warming row is the pattern; each problem file is owned by one agent, and a shared building block is
declared verbatim as the section says.
- phase 1, six agents in parallel, each with two of the twelve other problem files: the chain of the row as results
  by the four rules, the calc validations target first, and the open points of its files as notes for the lead
- phase 2, three agents in parallel: agent percent with the '%' format of a percent column with an empty cell, agent
  reasons with the 'reasons' column extension and agent meanings with the qualifier triples of `power` and
  `displacement`, if the developer accepts the changed phrase groups

## start page

each number of the start page should be a result of a calculation not a value

if in the component 'table with related columns' all values of the column are in 'percent' the values of this column are formatted with '%', but if one cell is empty, that default format is used. Change it, so that all 'percent' values use the '%' format independent from the other values of the table.

add column 'reasons' with sub column to start page as a second column extensions option so click ... once add the 'reason' column and the next time it will add the minor columns

the global warming row of `start_page/problem_global_warming.json` is done and is the pattern for
the other twelve problem files: seventeen of the eighteen numbers of the row are results, five target
values and their twelve bounds, and the file states only the inputs behind them; the loss reduction
of the public CBAM calculation is an assumed input. a calc validation may use a
result that the same file has reproduced before it, so the entries are ordered target first, range
afterwards, and a result that did not reproduce is never an operand.

the chain of the row, each step an own formula:

| result | from |
| --- | --- |
| potential loss in trillion EUR | world GDP * annual damage share |
| loss reduction in percent | an assumed input: 72.73 percent of the public CBAM calculation, so the gain is 1.6 trillion EUR |
| potential loss in GDP percent | minus the annual damage share * the long run growth factor |
| potential loss in htp percent | the GDP share * the htp to GDP damage factor |
| initial effort in person year | the annual damage in money * the effort per trillion EUR |
| potential gain in billion htp | minus the htp share * the world htp * the loss reduction |
| each low and high bound | the target * an own probability range factor |

four rules that the pattern depends on:

- the range formula selects the target by the word `assumed` and the factor by
  `probability range factor`, so a factor value never carries `assumed` and every target does
- a factor value must carry no phrase that names a column of the ranking (`potential`, `loss`,
  `gain`, `initial effort`, `loss reduction`), else the table sorts it into that column and shows it
  as the bound of the cell. it is scoped by the units and the subject instead, e.g.
  `global warming, trillion, EUR, probability range factor, low`
- the factor of each measure must be the only factor whose phrases fit the context of that measure
- the two bounds are calculated separately, so a range may be asymmetric; a loss stated as a
  negative share simply gets the two factors the other way round

what is still open:

- four inputs are tuned so that the results match the numbers that the start page has used until
  now, and they are placeholders for a real model: the long run growth factor of 16, the htp to GDP
  damage factor of 1.15625, the world htp of 1189.19 billion and the effort of 1.3636 person years
  per trillion EUR. the growth factor should become a damage function of the expected warming and
  the world htp the population times the horizon of the ranking
- the world htp of 1189.19 billion does not fit the other htp numbers of the pod: the global welfare
  baseline of theses_complex_simple.json is 8 billion people * 0.6 waking welfare fraction = 4.8
  billion HTP per year, and `htp = htp percent * global human population` of solution_prio.json
  gives 8.5 billion, so the tuned number is about 250 times bigger. it is the horizon that is
  missing, and until it is stated the gain column of every problem file carries the same open factor
- the probability range factor is stated per measure. it is a step function of the confidence in the
  view-validation table of solution_prio.json, and `factor = 0.5 / confidence` reproduces every row
  of that table, so it could be calculated from the confidence value instead
- one pair of formulas per file, because a formula names its operands by phrase and the centre has
  no phrase of its own. marking the centre with the word `centre` (already declared in
  solution_prio.json and used by nothing) would let all problem files share one pair, but it changes
  the phrase group of every start page number

the 46 climate solutions of the former `start_page/co2_eq_reduction_ranking.json` live in
`problem_global_warming.json` since 2026-09-25, because the ranking of the solutions and the
problem row are one topic: each solution has its reduction potential, fulfillment probability and
initial effort in billion USD, the expected return and effort return score of the co2 ranking, and
as parts of the solution "reduce climate gas emissions" the expected return centre, loss reduction,
gain and initial effort in the measures of the start page ranking, with bounds, 10 results per
solution. the file is self-consistent again, so no import order and no database is needed to check
it. what is still open:

- the file is imported by every database setup (`files::SAMPLE_VIEW_DATA_FILES`), so the ids of
  everything imported after it shift once and the pinned ids need a re-baseline from the
  regenerated `list.csv`
- the emissions 52.4 and 65.6 (`minimum`/`maximum` of `global greenhouse gas emissions 2019`) would
  make every context with `minimum` or `maximum` ambiguous, so the chain computes the expected
  return centre without the emissions first and divides by the emissions in a context without the
  two words; the bounds use one probability range factor per solution for the loss reduction and
  the gain
- four expected return centres (nuclear energy, soil carbon buildup, green hydrogen, supply chain
  laws) are exact halves at the third decimal, e.g. 0.225: `calc_internal` passes the result of
  a bracket through a string, which rounds 0.44999999999999996 to 0.45, so the parser yields the
  exact 0.225 and the plain number reproduces; a check that calculates with floats only (e.g. a
  python replay) gets 0.22499999999999998 and rounds the other way, so a replay must use the php
  parser; a calc validation that compares with a tolerance instead of rounding would end the issue
- the co2 entries keep their style (numbers as json numbers, `"assigned"` lists) next to the
  problem entries (numbers as strings, `"assigned_word"`); the repo formatter can unify them
- the `expected return` bounds of all 46 climate solutions are calc-validations of the
  `expected return formula` (minimum times minimum, maximum times maximum) since 2026-09-25; the
  stated values stay next to the results, as the coal phase-out entries did before

the 44 populism solutions of `problem_populism.json` (since 2026-09-25) follow the climate solutions:
a triple `populism solution` (solution of populism), `populism solution is part of avoid wrong
decisions`, one word plus `<name> is a populism solution` per measure, and per measure the assumed
reduction potential (share of the GDP decline it avoids when fully in place), fulfillment probability
and cost in billion USD, from which the loss reduction (potential times probability), the gain (the
gain formula of the problem, so a measure's gain is its share of the 34.1) and the effort (cost times
the 0.054545 person years per billion USD of the carbon border tariff) are calculated with bounds by
one range factor pair 0.4 / 2.5 per measure, 9 results per solution. what is still open:

- no source has such numbers for populism: every potential, probability and cost is an own
  estimate, marked `assumed` and reasoned in the value description, and the potentials are not
  additive (they sum to about 3.3, because the measures overlap)
- values and results use the qualified name `fact-checking (populism solution)` for every measure,
  because fact-checking, media literacy, prebunking etc. are measures of the disinformation problem
  too and the same user must not state two numbers for one phrase group; the climate solutions
  qualify only the names that clashed at the time (`research (climate solution)`)
- a measure with a space is a composition triple of single word atoms (`civic education` =
  education kind of civic, `campaign finance reform` = reform of `campaign finance`), the head noun
  as from, an adjective joined by `kind of`, a noun by `of`, a preposition of the name by its verb
  (`access to electricity`, `step-up in basis`); a measure whose name has no preposition that a
  verb covers is rephrased (`taxing capital gains as income` = `income tax` on `capital gains`,
  `privacy by design` = design with privacy, `safety evaluations before deployment` = safety
  evaluations of deployment, because `before` is a word and no verb); the same table serves all
  problem files, so a shared building block is declared verbatim in every file that uses it
- `open data` is merged into `transparent government data` and `local self-government` into
  `decentralization`; the merged names are only mentioned in the descriptions
- the costs are in billion USD next to the EUR of the problem row, so that the climate calibration
  of the effort is reused verbatim
- the gain factor 0.102411 is shared by the solution and its parts, so its phrase group no longer
  names `avoid wrong decisions`

the 35 biased information solutions of `problem_biased_information.json` (since 2026-09-25) follow
the same pattern as parts of the Delphi method: `biased information solution`, `<name> (biased
information solution)`, assumed reduction potential, fulfillment probability and cost in billion USD,
9 results per solution, the gain factor 1.00909 shared with the solution. what is still open:

- own estimates without a source, as for populism; the potentials sum to about 2, so they are not
  additive either
- `wisdom of crowds` is merged into `independent judgment aggregation` and `inoculation` into
  `prebunking`; the Delphi method itself is the whole and has no part of its own
- `prebunking` and `fact-checking` are re-declared name-only and `media literacy` as the verbatim
  triple, because `problem_populism.json` imports first and owns them; each problem qualifies its
  own copy
- the five problem files share one general formula pair `loss reduction of a solution part` /
  `initial effort of a solution part`: the home is `problem_populism.json` (the first import), the
  other four repeat the two formulas verbatim, so the import merges them by name

the 31 wealth concentration solutions of `problem_wealth_concentration.json` (since 2026-09-25)
follow the same pattern as parts of the basic income, the gain factor 0.29899 shared with the
solution. what is still open:

- own estimates without a source; the potentials sum to about 2.4
- `universal basic income` is the solution `basic income` itself and has no part of its own;
  `estate tax` is merged into `inheritance tax` and `baby bonds` into `universal basic capital`
- `wealth cap at the value of a statistical life` is built on the `wealth surcharge` of the file
  (`wealth surcharge` on `value of a statistical life`), so the measure of the list and the concept
  paper phrase are linked
- `affordable housing`, `anti-corruption measures` and `minimum wage` are re-declared as the
  verbatim triples without description, because `problem_populism.json` resp. `problem_poverty.json`
  import first and own them

the 34 poverty solutions of `problem_poverty.json` (since 2026-09-25) follow the same pattern as
parts of the poverty research, the gain factor 17.92188 shared with the solution. what is still open:

- the gain factor is tuned so that the 1 percent loss reduction of the research gives 34.1 billion
  htp, so the parts get gains far above every row of the ranking: economic growth 614, unconditional
  cash transfers 273, vaccination 164 billion htp (global warming 320). this is the consequence of the
  34.1 of the row, which is probably a copy of the populism gain (the htp share gives 13.6, see
  above); once the row is corrected the factor and all 34 gains follow from the regenerated chain
- own estimates without a source; the potentials sum to about 2.6
- `negative income tax` is merged into `universal basic income` and `mobile money` into `financial
  inclusion`; `anti-corruption` of the list is the existing word `anti-corruption measures`
- `anti-corruption measures`, `social safety nets` and `affordable housing` are re-declared as the
  verbatim triples of problem_populism.json without description; `economic growth` is the triple
  of economics.json (growth kind of economic), which imports later, so the poverty file is its home
  and carries the description

the 35 black-box AI solutions of `problem_black_box_ai.json` (since 2026-09-25) follow the same
pattern as parts of the public AI, the gain factor 0.294318 shared with the solution. what is still
open:

- own estimates without a source; the potentials sum to about 2.1
- `EU AI Act` is merged into `risk-based regulation` and `model cards` into `open model
  documentation`; `red teaming` is re-declared as the verbatim triple, because
  `problem_biased_information.json` owns it
- `open-source models` and `public AI infrastructure` are parts of the definition of the solution
  `public AI` (open source availability, public good), so they overlap with the whole more than the
  other parts do

two words carry two concepts in the main data and lost the later description on 2026-09-25, because
the import merges by name and the home file wins: `power` (the exponent of
math_propagation_of_uncertainty.json, the energy per time of economics.json) and `displacement`
(the pushed out generation of pv_switzerland_co2.json, the vector of physics.json); a `must be one
of` qualifier triple per meaning (`power (math)`, `power (physics)`) would give each its description
back, but changes the phrase groups of the values that use the bare word

if done remove this prompt from pending.md

**next prompt**

**full test run with re-pin: first /test/reset_db.php, then at least twice /test/test.php**

**Prompt:** open review notes of earlier changes (size M). "temp", "add formula" and "start page temp" are review
notes: the lead asks the developer the decisions they name ("Worth a decision" 1 to 4, the judgement call of
"temp") and removes what is done or declined. One phase, three agents in parallel:
- agent triple fixtures: the not found triple 998 of `del_triple_wf6` and `change_triple_wf5` in "add formula", the
  cause first, with a test
- agent messages: "Nice to have" of "add formula": the object neutral formula message, the error workflows that
  assert the reason and the word name of `SYSTEM_TEST_INVALID_EXP`
- agent result page: "start page temp" 1, 2 and 4: the tests of the three loaders, the result page snapshot with data
  and the include order

## temp

One judgement call to confirm in the fix itself. REFRESH looks like a render var but is deliberately not in CONTROL_VARS, because formula::url_mapper() reads it, so it does influence the mapping. PATTERN is also left out, as it is form state of the search and add views. If you disagree with either, that is the place to say so.

## add formula

Nine triple fixtures gained a warning that needs your judgement. workflow_write/del_triple_wf6/* (8 files) and the last step of change_triple_wf5 now render: the triple with id 998 cannot be found; it may have been deleted That is a direct consequence of the api fix: before, end_api() appended an html error page behind the json, json_decode() failed, api_curl_call() returned an empty array and load_by_id() did nothing — the backend's answer was silently dropped. Now the body is clean json, so the frontend reads it and reports. The message itself is the pre-existing OBJECT_NOT_FOUND path, so the question is whether that load should fail in those steps. My suspicion is the triple id changes during the write run (a rename by a non-owner writes a new row), and del_triple_wf6 then opens the old id. Worth one look before the fixtures are accepted — it is the kind of signal the snapshot baseline exists for.

Worth a decision

1. Per-user caching has no invalidation. db_cache_page::html_by_url() returns the stored row regardless of age, nothing deletes it on a write, and the refresh job is still a TODO. That gap existed for the shared page, but it now applies to a user's own page: after their own change the redirect is live, yet the next plain view of that url can serve their pre-change page. The coding rules ask for is_outdated() on a cache — this one has none. I would add a max age or an invalidation on write before this reaches a real deployment.

2. end_api() no longer writes a NOK message to sys_log. That is the fix for the corrupted json and the false "end_api error" rows, but a backend rejection that is not an error now leaves no admin trace at all. If you want to keep the trace, a log_warning on the non-error branch would do it without touching the response.

3. The ip guard trusts the name. An anonymous row is recognised by name == ip, so an account that registers under a name that looks like an ip could still be identified by ip. Signup does not forbid such a name today. The alternative — the profile — is not available that early, as the 500 showed.

4. test_mappers::formula_url() still emits the generic url_var::TYPE while the form posts fy. It only builds render urls, and the word does the same, so it is consistent within that file — but it no longer matches the mapper.

Nice to have

- frontend::session_user_id() reads $_SESSION inside a function. It follows the documented boundary pattern of session_token() right above it, but

1. A formula message in a generic class. msg_id::FORMULA_VALIDATION_FAILED is added inside web/sandbox/db_object.php, so a word or value load carrying a refresh would tell the user "the entered formula cannot be used". Either the text becomes object-neutral or the branch moves to the formula class.
2. The error workflows cannot fail. They only snapshot, so the silent no-message state above passes as "correct". Once the message works, each error workflow should assert the reason (assert_text_contains), which is also what makes them a regression test rather than a recording.
3. url_var::REFRESH_TERMS now has no producer in the frontend (only refresh_from_url still accepts it). It is a url contract, so I left it — your call whether to retire it.
4. formula_names::SYSTEM_TEST_INVALID_EXP names a word ("System Test Word Never Added") from formula_names; a reserved-but-never-added name would sit better in word_names.
5. docs/llm/pending.md still carries your temp notes and the item on line 21 that this change implements.
6. Process slip on my side: I removed the dead form_button_refresh line from both yaml files with a python one-liner instead of the Edit tool. The result is one clean line per file, but it broke your "Edit/Write only" rule.

## start page temp

The new snapshot does not actually show a longer list. 1_start_page_more.html is line-for-line identical to 1_start_page.html apart from the 9dls=20 back targets and the dls=20 carried into the full-table link — same six table rows. So the test pins the url and mapping contract, which is the regression, but not the "more rows" behaviour, because the test cache's value list is shorter than the short limit. The _more name may mislead a later reader; making it a real witness needs a start-page fixture with more rows than the short limit.

1. Should fix — the three backend loaders are untested. The regenerated coverage doc lists load_values_used, load_results_used and load_formulas_used each with 0 unit test calls (code_test_coverage.md:1512-1523). The three renderers and name_link are covered (2 → 4 calls), but the new selection rule — source-group phrases matched with and, fallback to the result phrases matched with or — is the core behaviour of this change and nothing exercises it. The project's pattern for a db loader is an sql-creation test (result_list_tests::assert_sql_by_val), which would fit here.

2. Should fix — no snapshot shows the page with data. system_views_read_tests renders only views::RESULT_OVERWRITE by factory, and views_by_id/result/9_… is built from an url, so its three columns are empty. The committed baseline therefore never shows a filled Values/Formulas/Results column, and a regression in the api emit or the mapping would not change any snapshot. An assert_view_by_factory(views::RESULT, …) case would close that.

3. Nice to have — the result changes query has no table filter. The new fixture change_value_by_res_prime_last.sql selects change_values_prime by group_id alone (inherited from the value path), so a value change with the same id would show up in a result's changes tab. Result ids carry the formula id, so the id spaces do not collide today — worth a comment or an explicit table filter if that ever changes.

4. Nice to have — web/result/result.php:47: the two new includes make the local alphabetical run PHRASE → RESULT → VALUE → USER; VALUE belongs after USER.

5. Nice to have — docs/code_object_name_exceptions.md gained $res_tab (the tab-box test variable, mirroring $val_tab on the value side).

6. For the record: a result built from an url renders the three columns as nothing — no message, no error (the choice documented in the renderers, following ui_list::formula_list() for a word). An empty column therefore means either "nothing used" or "not loaded"; only the loaded page distinguishes them via the info messages. And system show result value is now linked by no view, left defined so the component ids do not shift again.

if done remove this prompt from pending.md

**next prompt**

**full test run: /test/test.php**

**Prompt:** fill the configuration, job and skeleton views (size L, two phases; re-pin, because the view ids are pinned
after the reset). `sys_log_detail` is part of the admin dashboard prompt; quarantine stays postponed. Each agent adds
its own functions to `web/component/execute/system_page.php` and a page test per view.
- phase 1, five agents in parallel: agent config with the three configuration views and the config change request
  job, agent language, agent log with the change log views, agent import export and agent find lists with the find
  and object list views of "fill the language, log, ..."
- phase 2, two agents in parallel: agent jobs with `job_detail`, `calc_status` and the job placeholders, and agent
  placeholders with `word_find`, `search_full`, `undo`, `error_update`, `system_log` and `verbs` (fill or remove); the
  lead pins the view ids and the `views::ADMIN_MASK_IDS` after the re-pin run

## fill the configuration and quarantine views

the views user_config, system_config, admin_config, user_quarantine and all_quarantine still show only the "system body
not yet available" hint; the user_jobs and all_jobs views are filled

pin the view ids of user_config, system_config, admin_config, user_quarantine, all_quarantine, user_jobs and all_jobs
after the next reset and add admin_config, all_quarantine and all_jobs to views::ADMIN_MASK_IDS

user configuration - show the standard user configuration and the overwrites of the user (config_numbers::load_usr_cfg via api/config)

system configuration - show the system configuration read only with the possibility to request changes; a change request
is saved as a job of a new job type 'config change request' with the config phrase and the requested value as parameter,
so that the admin sees and handles it in the all jobs view

admin system configuration - show the full system configuration to an admin with the possibility to adjust it (api/config
has no write path yet)

user quarantine and all quarantine - postponed until it is defined what quarantine is and how data is moved there

the existing job views job_async, job_control and job_check and the admin delayed jobs component still show placeholders
or no data

## fill the language, log, import, export, process, find and list skeleton views

these system views show only the "system body not yet available" hint:
- language: phrase_translations, language_forms (api/languageForm), translations_missing, list_languages
- system log: sys_log_detail (link target of error_log, error_update and the admin dashboard)
- change log: change_log_user (with undo per row), change_log_recent, object_history (incl. api/changeLogLinkList),
  change_detail (old and new value with revert)
- import: import_preview (like confirm_add), import_result (linked from user_jobs), import_url, import_wikidata
  (import_wikidata.php)
- export: export_selections (the saved selections of the export forms), export_object, export_ready (download of a job)
- process: job_detail (link target of user_jobs and all_jobs), calc_status (outdated results and calculation progress)
- find: value_find (api/valueList), ref_find (by external id), find_duplicates (admin, input for quarantine)
- object lists: list_words, list_triples, list_sources, list_refs, list_formulas, list_views, list_components

job_detail and calc_status use a user as the main object in the view mapping, because the unit test url mapper has no
show url for a job yet

adding the 28 views and their title components at the end of system_views.json shifted the ids of all base views by 28
(views.php) and of the components after them by 28 (components::SOLUTION_PRIO_TITLE_ID); confirm the view ids from
unit/view/list.csv and the component ids from unit/component/list.csv after the next reset

also fill or remove the existing placeholder views word_find, search_full, undo, error_log, error_update, system_log
(no components) and verbs (no components)

if done remove this prompt from pending.md

**next prompt**

**full test run with re-pin: first /test/reset_db.php, then at least twice /test/test.php**

**Prompt:** the missing workflows and small page fixes (size L; re-pin, because the confirm pages add system views).
"maybe to fix" is decided by the lead (a load before the sort is either fixed for all three loaders or moved to
prio 2). One phase, five agents in parallel, each with its workflow snapshots:
- agent links: the link and unlink workflows of "general" with the fa-plus icon and the confirm page of a formula
- agent quick: the quick workflows of "general"
- agent groups: the group add, change and del workflows
- agent views check: "views", which `views_by_id` snapshots are useful, as a list for the developer
- agent pages: "admin" (the system errors entry of the admin menu) and "main pages" (the logout page)

## workflows

### general

add the missing workflows link workflows such as

- link a formula to a phrase
- unlink a formula from a phrase
- link a component to a view
- unlink a component from a view
- link a view to a term
- unlink a view from a term
- add a child view to a view
- remove a child view from a view

add a fa-plus icon at the end of the phrases linked to a formula in the formula default view and add and edit view that,
based on url_to_action, call a confirm page that shows something like 'link phrase xxx to formula xxx' with a cancel and
confirm button

add the missing workflows quick workflows such as

- add a word or select a phrase
- change a value
- change a result
- select a term and add it to a formula

add group add, change and del workflows

## views

check if the views_by_id are 'useful'

## admin

add to the admin menu a page that shows the system errors

## main pages

in the logout page add an OK button that calls the back page from the url without token and make the "you have been
logged out" bigger

## maybe to fix:

Limit before sort — load_values_similar () reads with value_list::read_limit (), but the impact sort happens in the
   renderer (list_most_relevant). With more matches than the limit, the highest-impact similar values can be cut before
   sorting. Same pattern as word/source::load_values_related, so consistent — just noting it.

if done remove this prompt from pending.md

**next prompt**

**full test run with re-pin: first /test/reset_db.php, then at least twice /test/test.php**

**Prompt:** cleanup scripts, value_base abstract and the open points of the object pages (size L). "value page tabs" and
"component page tabs" are notes: the lead keeps only their open points. One phase, six agents in parallel:
- agent scripts: the check scripts of "cleanup" (default page of each main class, the shown fields, the queries
  without a limit) and the open API check in `/test/test.php`
- agent docs speed: why the diff of `docs/code_functions_all.md` takes long
- agent views menu: the edit link of each alternative view in the edit icon menu and the view selector link of the
  word default page
- agent value: `value_base` abstract and the undo and apply icons of a text, time and geo value
- agent counts: the count query of "verb default view" and "user page", and whether the triple name fields are needed
- agent change log: the empty field name of `change_log_list::table_field_to_query_name` for all branches, with the
  renamed prepared statements and their fixtures

## cleanup
check why diff for docs/code_functions_all.md still takes very long

write a php script that checks that a default page for all main classes exists and that the default pages show all
fields that are not explicitly defined as not_show

create a script that checks that all fields of the main classes are shown on the related default view, add and edit view
except the fields that are explicitly excluded

create a script loops over the resources that lists all queries '*.sql' that does not have a limit and that does not
have a unique db id in the where condition.

add the check of the open_api specification to /test/test.php

in the edit icon menu add after the name of each alternative view a link to edit the view and make the view name a
link to the view default page

fix the view selector link in the word_default page

make value_base abstract: value_base is only the shared parent of value, value_time, value_text, value_geo and
value_time_series, but it can still be created, and because the table name is derived from the class a value_base
object queries the not existing table value_bases_main (e.g. test_values::cleanup used new value_base() until the test
value cleanup was activated). declare the class abstract, turn the functions that each child must overwrite into
abstract functions (e.g. value(), which sandbox_value today only reports at runtime as 'expected to be overwritten'),
check if sandbox_value can be abstract as well, and replace any remaining creation of value_base or class based
creation from def::VALUE_CLASSES (e.g. in the test mappers) with the matching concrete value class

### verb default view

a cut list shows that it continues, but not by how much: the verb page ends the triples with '...' without a number,
because the loaded list is itself cut by the read limit, so the number of the remaining triples is not known (unlike
value_list::more_tail, which knows the count within the loaded list). the exact number needs its own count query, the
same open point as the per type count of the user page

triple_fields::FLD_NAME_GIVEN and FLD_NAME_AUTO are null for all 971 triples of the test database, although the import
sets a name for each of them: the generated name lands in triple_name only, so a query that needs to know whether the
user has given an own name cannot tell. check if the import should fill name_generated and if the two fields are needed
at all beside triple_name

### value page tabs

the value default view now has the 'value tab box' component, so the value page shows the views that can show a value
(all views of the value view type, loaded by the new view_list::load_by_type), the change log and the user overwrites

the 'my' tab of the value page has an undo icon for the numeric value, the source and the sandbox fields (see value::
db_fld_to_url), but not for a text, time or geo value, because url_var has only NUMERIC_VALUE; the same applies to the
apply icon of the 'others' tab

the shared changes and overwrites api arrays moved from cfg/sandbox/sandbox.php to the new
cfg/sandbox/sandbox_related.php, because sandbox (one db id per row) and sandbox_multi (a group id per row) have no
common parent and the value needs the same code; a third hierarchy would use the same helper

adding the 'value tab box' to base_views.json shifts the database id of every component imported after it: the two
components of company.json and companies.json move from 347/348 to 348/349 in
src/test/resources/unit/component/list.csv, and the component links of every view after value_default shift by one too

## user page

a group of the 'all_user_overwrites' column shows only the changes of the shown page and not how many overwrites of that
type the user has: change_log_actions::GROUP_BY_TYPE splits the rows into one table per object type, but the list is cut
to the configured row limit before the split, so a group holds the newest changes of its type and not all of them. a
count per type needs its own query that counts the user sandbox rows per table; user_list::load_sql_count_all_rows
already builds that union per user and would only have to keep the per-table counts instead of summing them

## component page tabs

the component default view now has the 'component tab box' component with the changes and the my tab; it has no views
tab, because the views that use the component are listed by the separate 'component views' component of the same page

api/component/index.php did not read the url flags at all, so it always sent api_json ([]) and the owner of a component
was never sent either although component::api_json_array emits it under incl_related; the endpoint now uses
api_type_list::from_url_array like the triple and formula endpoint

web/component/component.php has a load_by_id_with_related and a db_fld_to_url, both only on the component: view_base and
source extend the same web sandbox_code_id, so adding either to the parent would also change the view and the source
page, which are separate pending items

change_log_list::table_field_to_query_name returns '_of_cmp' for a component changes tab (leading underscore) and logs
'field name not expected' although an empty field name is the normal case of load_obj_last; the same wart exists for the
formula ('_of_frm'), view, source, verb, group and value branches, whereas the ref and the type branch handle the empty
field name properly - fixing all of them renames the prepared statements and churns the committed fixtures, so it needs
its own change

adding the 'component tab box' to base_views.json shifts the database id of every component imported after it, like the
value tab box did before

if done remove this prompt from pending.md

**next prompt**

**full test run: /test/test.php**

**Prompt:** triage of "more" (size L, two phases). The section mixes open tasks and review notes of old changes.
- phase 1, lead: check each item against the code, remove what is done or no longer true and sort the rest into the
  groups of phase 2; ask the developer the items of "Worth deciding"
- phase 2, up to five agents in parallel, each with one group: agent reset (the silent not-ok of the forced reset,
  `Message::add_err`, the type of a no update import and the dropped fill results), agent buttons (the button review,
  `dsp_edit`, `$back`, the tooltips with the object name, the dead code and the test constants in `web/`), agent
  lists (the verb list add button, `type_list::get`, the docblocks, `text_h4`, the sort test), agent fixtures (the
  volatile change time, the half regenerated fixtures, the untracked files) and agent views (the last component
  without `div_row`, the owner field, the view relation position, the empty changes tab, the missing translations)

### more

find and fix the silent not-ok on the fresh-database reset path: during reset_db_forced the request message reached the
config check of db_check with status NOK but without any error text (only the DONE and 'finished successful' infos), so
a sub step of the fresh-db startup (user creation, type fill or base import) returns a failed message without recording
the reason - a 'never fail silently' violation; the broken-sql side effect is fixed (db_object_seq_id::sql_write now
uses a build-scoped message), so the next forced reset should surface which step it is

review Message::add_err: it passes ok=true to add (), so an error added via add_err never sets the message to not ok
(is_ok () stays true), which inverts the intent of the plain add () that flips to not ok even for infos; decide the
intended contract and align add, add_err, add_id and add_info_id

decide how a no update import should treat the object type: sandbox_typed::diff_msg skips the type when $ex_def is set,
so a re-declaration with another type is never reported and the later import file silently wins; company.json declares
the components "Cash Flow Statement" and "company with ratios" as calc_sheet and companies.json declares the same names
as values_related resp. word_value_list, so today companies.json overwrites the type of company.json without a message;
either report the type like the other fields or give the two files their own component names resp. one code_id to merge
(see docs/llm/json_views.md)

thread the two fill () results of the no update import in sandbox_list_named::update: both $sbc->fill ($dbo, ...)
and $dbc->fill ($sbx, ...) drop the returned user_message, so a permission problem of a fill setter (e.g. component::
set_ui_msg_code_id for a user without can_set_ui_msg_id) is lost instead of being reported

review buttons

web/value/value_list.php:1352 and :1360 were not migrated — $phr_row->btn_add ($back) / $val_main->btn_add ($back) still
pass the old string $back into the new array $url_arr parameter. dsp_table ($phr_row, $back, ...) is untyped so nothing
catches it statically; at runtime this is a TypeError.

deprecate dsp_edit

deprecate http_old

review $back

review $msg

repeat the check of the fields in the default page, the my tab and the fill of 'all_user_overwrites' for refs,
term_views and any missing main or link class

Worth fixing
formula button tooltips lost their object name
button is now btn_edit () with no $url_arr at view.php:171 and word.php:751.
Production web/ code now includes and reads test constants. web/view/view.php:67 and web/word/triple_list.php:51 add a real include_once test_paths::CONST . 'word_names.php' and hardcode word_names::ZH_ID as the back target. list_sort.php extends the same pattern to triple_names::GLOBAL_PROBLEM_ID — and there the include_once is commented out while the use is active, so it only works via the autoloader. triple_list.php:147 has a TODO Prio 0 admitting the $
url_arr is a placeholder; view.php:145 has none. Either way "Zurich" as the back link on a generic view navbar is a
placeholder, not behaviour.
Dead code left behind in view.php:617,619 — $call_edit / $call_del are still built via url_back (...) but no longer
used, since btn_edit ()/btn_del () build their own url. The buttons also lost the word= and back context those urls
carried.
Unused additions in tests: graph_tests.php:60-62 adds $base_url, $
lan, $url_arr — none used in the file; languages is imported unused in both graph_tests.php and horizontal_ui_tests.php.
Tooltip regression, visible in base_ui_tests.php:512: the delete title went from delete this formula of scale minute to sec to delete this formula — the object name dropped out of the button title. Intentional?
Nit: shared/const/words.php:335 inserts SOLUTION after STATEMENT, breaking the alphabetical order of that list.
docs/llm/pending.md drops the self:: vs $this:: item (correctly — db_object::btn_add
uses $this::VIEW_ADD_ID) but keeps the AUTO_UPDATE_TEST_FILES item, which is still open per point 1.              
horizontal_ui_tests:106-111 calls these for many classes but only asserts the icon is present, not the url — so nothing catches it.
dsp_list () drops the add button on an empty list. $vrb is assigned inside the foreach, so it stays null when there are
no verbs and the if
($vrb != null) block is skipped — a user looking at an empty verb list has no way to add the first one. The old free btn_add () was unconditional. The button also now belongs to whichever verb happened to be last in the list, which reads oddly for an "add new verb" action.
$add_script in dsp_list () is now dead (verb_list.php:131) — nothing consumes it since btn_add () builds its own url.
Two url styles on one page, visible in the new snapshot: list () emits m=verb_edit / m=verb_add (code ids, via
html_base::list ()'s url_back) while the migrated dsp_list () button emits m=6 (view id). Same page, same action, two
conventions — the html_base::list () side has not been through the #247 migration yet.
My architecture.md section pins the wrong parameter order. It writes add_obj ($obj, $
allow_duplicates, $msg), which matches ListOfIdObjects and sandbox_list_named but not the new type_list::add_obj ($
obj, $msg, $allow_duplicates). The new order is the one the rules actually require (a user_message parameter must be
required, so it cannot follow an optional one) — the two older siblings still have
Message $msg = new Message (). The doc should either not name an order or say the new one is the target.
type_list::get () now has a false alarm. It logs log_err ('probably ... are duplicate code_id') whenever count ($this->
hash) != count
($this->lst) — which is exactly the state a legitimately duplicated list is in, since hash is keyed by code_id. It only fires if someone calls get () on such a list, but the check contradicts the feature just added.
url_back () lost its docblock (html_base.php:730-760). base_url_clean () was inserted between the existing /** … @return string the created url */ block and function url_back (, so that block now documents the private helper (which has its own docblock right below it) and url_back () is undocumented. Moving base_url_clean () above the docblock fixes it.
sort_by_name ()'s docblock is now false (type_list.php:403). It justifies returning a copy with "because get () maps the position in the hash to the position in the list" — but get () no longer does that, it was changed in this same diff. Returning a copy is still right (a preloaded type list is shared for the whole request and must not be reordered under other holders), but the stated reason has to be the new one.
text_h4 () breaks the pattern of its siblings. text_h1/2/3 pass the matching level as the non-bootstrap tag (H2,H1 / H4,H2 / H5,H3); text_h4 passes self::H6, self::H3, so with UI_USE_BOOTSTRAP = false an h4 renders as <h3>. Invisible today because bootstrap is on — the snapshot correctly shows <h6>.
The snapshot does not actually exercise sort_by_impact (). Every verb from load_dummy () has impact 0.0, so the short and more versions fall through to the name tie-break and render alphabetically — identical to what sort_by_name () would produce. The new sort is unproven by the committed test; a fixture with distinct impacts (or an assert on sort_by_impact () directly) would close that.
$url_arr is unused in both verb_list_ui_tests.php:64 and word_list_ui_tests.php:57. Nothing on either page takes one.
word_list_ui_tests.php / word_list.html are unrelated to this change. The only effect is that the three stylesheet hrefs
became absolute (http://localhost/…) because $base_url, $lan were added to its html_page_test call. Fine in itself, but
it is a separate concern riding along — worth a glance that you want it in this commit.
src/test/resources/import/carbon_leakage_effect.json is staged and unrelated — 244 new lines, referenced by no test and
no PHP file, and nothing in this change set produces it. It looks like it wandered in from other work. Either it belongs
to a different commit or its consumer is still missing.
The component insertion point causes ~1,600 lines of avoidable churn. I placed system title user directly after system
title user settings, which lands it at component id 98 and shifts every later component id by one:
unit/component/list.csv 450 changed lines, unit/component_link/list.csv 1,866. Appending both new components at the end
of their components blocks would have given the same result with a two-line fixture diff. I checked the pinned consts in
shared/const/components.php — the highest is FORM_PLURAL_ID = 92, below the insertion point, so nothing is silently
mis-pinned. But if you would rather not carry that renumbering, moving the two definitions to the end of their blocks
and re-running is cheap now and awkward later.
Seven user snapshots were renamed, not edited — 74_user.html → 74_user_9.html, and likewise 49, 50, 62, 85, 87, 89. The
filename carries the dbo id, which is now 9 instead of 0. Correct consequence of user_filled_loaded, but it makes the
diff read as delete+add; worth confirming the deletions are the paired old files and that the runner's "remove test
files not used any more" pass did not drop anything else.
component_types.csv gains a seeded row, so the database needs the new type. I read that as data rather than structure
and left version.txt alone — your call if it should carry the minor bump plus a db_check step.
The new component is not wrapped in the row col-md-12 column div — visible in 74_user_9.html: the first four components
each sit in <div class="row col-md-12">, the fifth does not. Since the request was specifically "in a fixed column",
this defeats the point of the placement.
The cause is pre-existing, not introduced here: view_exe::show () wraps a row only when it reaches the next component
($result .= $html->div_row ($row, $row_style) in the BELOW branch), and the final flush at line 271-273 is a
bare $result .= $row; with no div_row (). So the last component of any view has always been unwrapped — in the committed
snapshot it was user system errors that lacked the div, and now it is the new one. Fixing the flush to use div_row ()
with the same style resolution would wrap both, and would change the tail of many view snapshots.
The two change-log api fixtures now carry a per-run change_time. change_log_list_word_1_word_name.json gained
"change_time": "2026-08-18T09:10:19+00:00", which the committed baseline did not have. assert_api_compare () strips it
before comparing (json_remove_volatile → change_log::FLD_TIME), so tests still pass — but the stored file now holds a
timestamp that differs on every reset, so these two files will churn on each run.
Cause: the update_files_with_not_yet_fixed_db_id () helpers I added last commit write the raw api response
($lib->json_for_dev ($created)) without passing it through json_remove_volatile () first. The type_list_check () pattern
I copied has the same property (its header timestamp also moves each run), so this is consistent with the existing
code — but for the change log it bakes a volatile value per row where there was none. Stripping volatile fields before
writing would make these fixtures stable.
The files also switched from 2-space to 4-space indentation, confirming a different writer than whatever produced the
committed versions.
rest_call::api_call ()
gained $extra_headers for a test-only need. The parameter exists solely so assert_api_get_not_permitted () can send X-Forwarded-For and reach the rejection arm — no production caller passes it. Defensible (the local test runner is an own-pod call, so there is no other way to exercise that branch), but it is production API surface added for a test.
The two skip-guards in assert_api_get / assert_api_get_by_text got looser. They now also skip when the response lacks an email field. That correctly absorbs the new core-json response when the admin login fails — but it also means a genuine regression that drops the email from the admin response would be silently skipped rather than failing. Narrower would be to check for the msg key or an explicit core-shape match rather than "email missing".
load_by_id_with_related () ignores the load_by_user result. The $msg is threaded and merged, so problems do reach the
user; but a failed change-log call leaves chg_log as an empty list, which the renderer cannot distinguish from "this
user has no changes" — the page then says "This user has not changed anything." even when the load failed.
The changeLogList branch has no permission check, justified in the comment as "the change log is public like on the
object pages". That is consistent with the existing by-object branch, which is equally unguarded — worth being
deliberate about, since this is the first endpoint keyed on a user id rather than an object id.
The budget can overshoot by design. A group renders whole once started, so the last group may push the total past the
limit — with your 20 and a 15-member group starting at budget 1, the page shows 21. The comment says so, and it follows
from your "cap each group / keep groups whole" choice, but the limit is a target rather than a hard ceiling. If you want
a hard cap, the last group would have to be truncated mid-way.
impact_group no longer defaults to the configured limit. Its $budget is now required and it uses max (0, $budget). Every
caller passes a real value today, but a future caller that passes 0 gets a section with no rows and a tail counting
everything — silently correct-looking, easy to get wrong. A
?int $budget = null defaulting to configured_limit () would be safer.
The per-group cap re-reads the config per section. time_groups () and relevant_phrase_groups () each call configured_limit () for $
per_group, and group_block () calls it again per group. Correct, just repeated work on a hot path; passing it down with
the budget would do.
The word pages moved too — word.html, word_api.html, word_default_word_1/197, word_del, word_edit, value_list.html all
changed. Expected (the same renderer), but they are the pages to eyeball for over-truncation, since the fixtures there
are small and now render against a limit of 6 in tests.
The owner is still not rendered — the task is incomplete. No snapshot anywhere contains ow=. url_mapper::url_par ()
drops null values,
so $obj->get_user ()->name () is null when the test url is built, and the system show field owner component renders nothing on both 116_formula_link_1.html and 115_component_1.html. The transport chain exists end to end; the test fixture just never supplies the name.
view_relation start position is now an empty field. Removing the 'position missing missing' placeholder was right, but test_mappers::view_relation_url doesn't carry url_var::POSITION (and view_relation has no to_url_array override), so 43_view_relation_update_1.html renders the field with no value. Component link works (value="1"), formula link is fully wired. That is exactly the "unexplained asymmetry" the fix-the-pattern rule warns about — one of the three is wired, one reads from an existing url var, one can't.
Empty "Changes" tab on the formula link page. change_log_table_pure returns a table with when / who / what headers even with zero rows, so tab_box doesn't drop it. 116_formula_link_1.html shows a Changes tab containing only headers.
form_field_formula_link_prio has no German translation. I changed the en text to "Priority"; de.yaml has no entry for it at all (nor for form_field_view_term_link_prio / form_field_component_link). Pre-existing, but the rule is en and de.
Seed component ids shifted. The three new components in base_views.json pushed Cash Flow Statement 344→347 and company with ratios 345→348. list.csv is regenerated and no *_ID const pins either, so nothing broke — reporting it rather than absorbing it silently, as the json rules require.
Two include_once lines commented out (yours): sandbox_named.php no longer includes its own parent sandbox.php, and type_list.php drops change_table_field.php. Same shape in web/sandbox/sandbox.php, where change_log_list.php is commented out while api_mapper does new change_log_list (). All load today, but each now depends on some other file loading the class first — the same class of fragility as the sandbox_link fatal.
Minor: $url_arr is declared but unused in source_ui_tests.php and user_ui_tests.php (used 4× in component_ui_tests.php).
And code_object_name_exceptions.md grew for formula_link — my test vars $lnk_fld_url, $
lnk_plain, $lnk_tab — against the rule that the list stays short.
Not a defect: the coverage doc lists show_link_type, show_order_nbr and link_type as "0 unit test calls". They are tested, but in unit_ui/, which that generator doesn't count.     
api/ui_config/ui_config.json — timestamp-only diff. Only the header timestamp changed. Consider reverting; it's pure churn.
load_sql_by_user_value () is public but has no external caller. load_by_user () is the only user. Its sibling load_sql_by_user () is public too, and the test calls it directly, so this matches the local convention — just noting it.
sort_by_time_and_cut () duplicates the web twin. The frontend web/log/change_log_list already has sort_by_time_and_what () + head (). The two apps are deliberately independent (docs/llm/frontend.md), so this is the accepted pattern rather than a DRY violation — but the backend now sorts and the frontend re-sorts the same list.
The changes table comment is the load contract. load_by_user () reading 13 tables rests entirely on that comment ("all tables except value and link changes"). If a value change ever lands in changes, it would be listed twice. Not currently possible, but nothing asserts it.
version.txt correctly untouched — no json format or db structure change.
Generated docs: code_user_message_exceptions.md only shifts a line number, doesn't grow. code_test_coverage.md adds the three new functions.
The fold is duplicated verbatim in the backend and the frontend std_table* function. That mirrors the existing prefix-strip duplication rather than adding a new pattern, so it is consistent, but both copies now have to move together.
Verification: start page and user page render without a fatal; a script replaying both std_table* functions over every row of change_tables.csv shows all fifteen user_values* folding to values while user_value_links, value_ts_data, the change_values_* log tables and the *_standard* tables stay untouched; and a text group id round-trips through the api mapper into …?m=19&id=PmS%2BXk9wQzR0dGVzdA&n=&8n=123456&z=1. No sql fixtures need regenerating — the l2.table_id  filter travels as a bound parameter (= ANY ($
2) / IN (?)), so the longer list does not change the prepared-statement text. No secrets in the diff.
A caller I missed, already fixed by you: test_words::zh_full_ui () calls the web word's set_type, so it needed
user_message_ui not user_message. My caller scan mis-classified it because zh_ui () returns a word_ui. I re-checked —
that was the only one; every other set_type call is on a backend word, a component, element or sql_db.
The fixtures are now coherent: component 352 = the new field, link 1013 = verb view position 7, sub-title 8, triples 9 —
exactly as computed; the api type-list fixtures carry only the new system_show_field_name_in_formulas, none of the old
colliding name. The verb snapshot's triples are in ascending id order, confirming the tie-break landed.
change_log_list_word_1.json shifted by +3 (ids 6885→6888 …) — the re-import wrote three more change rows. That fixture
pins absolute change-log ids, so it will drift on every re-import; not caused by this change, but worth knowing.
docs/code_user_message_exceptions.md now reports db_object_seq_id.php:350 - user_message $msg = new user_message (),
instead of the full signature — the generator matches a single line and your reformat of api_json split the signature
across lines. Cosmetic, but the entry is now less readable than its siblings.
Checked and clean: no &&/|| in added code, no secrets, includes and use blocks alphabetically placed, log_err only on
genuine programming-error paths.

Worth deciding
Button tooltips lost their object detail. formula::btn_edit ()/btn_del () were renamed to *_back, so the formula page
now uses the generic db_object::btn_edit (), which passes
no $explain. Visible in formula.html: title="change formula for scale minute to sec" → "change formula", and "delete this formula of scale minute to sec" → "delete this formula". If that's intended, fine; if not, the new generic variants need an $
explain path too.
api/.htaccess — <FilesMatch
"^[A-Za-z0-9_]+$"> re-allows every extensionless name under /api, which is what the routes need. Worth noting it also re-allows any other dot-free file that lands there; a tighter alternative is listing the route names, at the cost of maintenance. Your call — the current form is the one I proposed.
src/test/resources/unit/user/list.csv and api/ui_config/ui_config.json changed too — presumably from a reset_db run; worth a glance that they belong in this commit.
code_object_name_exceptions.md grows by two: $lst_dbl
and $lst_empty are added to the verb_list line. Three instances in one scope is a legitimate deviation, but the file is supposed to shrink — $
vrb_lst, $vrb_lst_dbl, $vrb_lst_empty in the test would keep it flat.
test_verbs::list_short ()
mixes $this->verb_is () with self::verb_part () (the factory declares that one static) — harmless, just uneven.
In the base type_list::set_from_json_array (), the verb arm calls $vrb->api_mapper ($value, $msg) and ignores the
returned bool, while verb_list's override guards on it. Pre-existing, but the two now sit side by side.
ames_one_line () / the sort closures type the entries as verb|type_object while get () uses verb|ref_type|type_object.
ref_type extends type_object, so it works, but the two unions should read the same.
name_tip () entries without a description render as a bare <span>name</span> — a wrapper with neither class nor title.
Only "is a" carries a tooltip in the snapshot, since load_dummy () sets no descriptions.
Still open from earlier: get () opens with count ($this->hash) != count ($this->lst) and logs probably … are duplicate
code_id, which is a false alarm on a duplicates-allowed list and fires on every call. It is the last consumer of the
parallel-arrays assumption the rest of this diff removed.

Fixtures are half-regenerated — rerun before committing. Two regeneration runs are mixed in the tree (28-08 22:25 and
29-08 10:05):

- api/type_lists/type_lists.json is from the older run: its 13 system sub title usage number entries lack
  ui_msg_value_exception: 0, while ui_config.json has all 13.
- views_by_id/component/34_component_update_1.html (older run) still renders Used 0 times, while its sibling
  views_by_object/component/component_edit_component_1.html renders Not used.
- views_by_object/formula/formula_default_formula_21.html / _26.html still carry the live 29-08-2026 10:31; they pick up
  the fixed test time only on the next run.
- Two snapshots from July still show Used 0 times and were never regenerated:
  workflow/change_triple_by_name_wf8/wf8_show_edit.html and
  workflow_write/change_word_wf2/wf2_show_edit_back_edit_save_cancel_edit_save_confirmed_edit.html (the second is one of
  the orphaned wf snapshots already listed in pending_prio_2.md).

api/result/ is untracked — needs git add, and the served copy in /var/www/html needs the new endpoint plus the backend
changes before the http api tests run.

api/formula/formula_body.json lost "usage": 7 and "ref_text" while gaining need_all_val. Nothing in this batch removes
either; both are db-state dependent (usage is written by a batch job, ref_text on save), so this may flip back on the
next regeneration — worth confirming it is intended.

if done remove this prompt from pending.md

**next prompt**

**full test run: /test/test.php**

**Prompt:** alias direction check and the test data set (size M). "start page" below lists the basic steps of the start
page, which are done (the start page shows the global issues by impact with the column tiers): the lead checks and
removes it. One phase, two agents in parallel:
- agent alias: `triple_list::validate_alias_direction` with a positive and a negative test
- agent test data: the filled `$dto` of `solution_prio.json` from the `/test/create/` factories

## prepare

create in triple list a 'validate_alias_direction' function that checks if the alias verb always leads to the same main
phrase

based on the /test/create/ class function and the solution_prio.json create a set of test data (a filled $dto object
where the database id is taken from the /test/create/ class functions and the rest e.g. the val)

## start page

the basic steps to show the start page are

- table with 'global issues'
- sort global issues by impact which is htp
- get mayor, main and minor columns linked to 'global issues' via triple
- the order of the column may differ and is relative e.g. 'per cent is after number'
- the number of rows to show is taken from the config but can be overwritten

if done remove this prompt from pending.md

**next prompt**

**full test run: /test/test.php**

# move to Prio 2

**Prompt:** the condition formula of a word and a triple, the user picture and the language fields (size L, two phases;
re-pin, because the database structure changes with a minor version raise and a `db_check` upgrade).
- phase 1, one agent: "word db table" and "triple db table", the field `condition_formula_id` of both tables with the
  rename, the sql fixtures and the upgrade; stop for a run with re-pin
- phase 2, four agents in parallel: agent triple view ("triple add and edit view" and "triple" of "language"), agent
  word view ("word add and edit view" and "word" of "language"), agent picture ("log in") and agent default view (the
  language line of the word default view)

## triple add and edit view

reduce the size of the triple description field to 8/12 and add after the description a formula selector for the
conditional formula

## word db table

similar to the triple table field 'triple_condition_id' add a field 'condition_formula_id' to the word table

## word add and edit view

reduce the size of the word description field to 8/12 and add after the description a formula selector for the
conditional formula

## triple db table

rename the triples table field 'triple_condition_id' to 'condition_formula_id'

## log in

if no picture of the user is uploaded create a random picture and show it in the top write corner if the user is logged
in

## language

### word

add the missing db fields to the word add and edit views: a language selection (language_id, preselected with the user
language) and, for users whose profile passes can_set_code_id, the code_id field; show the non-changeable fields impact
and inactive as display-only info in the edit view next to the existing usage component

show the missing db fields in the word default view the language e.g. as a small info line next to the description

### triple

add the missing db fields to the triple add and edit views: a language selection (language_id, preselected with the user
language), the given name (name_given) as its own field beside the generated name (name_auto, display-only) so the user
sees the difference and can empty the given name to fall back to the generated one, the condition formula selection
(triple_condition_id) and, for users whose profile passes can_set_code_id, the code_id field; show the non-changeable
fields impact and inactive as display-only info in the edit view next to the existing usage component

if done remove this prompt from pending.md

**next prompt**

**full test run with re-pin: first /test/reset_db.php, then at least twice /test/test.php**

**Prompt:** deprecate http_old (size L, three phases; re-pin, because the verb gets a db field). Steps 4 and 6 of
"Other" are part of the admin dashboard prompt; step 1 is done.
- phase 1, three agents in parallel: agent bugs with step 2 (the code bugs 1 to 4 with their tests and the view
  consts of bug 5), agent fields with step 3 and agent guard with step 10; stop for a run: `/test/test.php`
- phase 2, the lead asks the developer the decision of step 5 first; two agents in parallel: agent defaults with
  step 5 and agent export import with step 7; stop for a run with re-pin
- phase 3, up to four agents in parallel: step 8 with one agent per section of `pending_prio_2.md` in the order of
  their prio; then the lead does step 9 (delete the scripts in batches) and step 11

## deprecate http_old

**Target:** `http_old/` is empty and removed, every feature of its scripts is part of a generic view of
`system_views.json` called via `/http/view.php`, or is recorded as an open gap, and the `TODO clean up http_old` line
of `docs/todo.md` is gone. No script of `http_old/` has a caller in `src/` any more (checked: none has one today).

**Rule for every script:** a script is deleted only when its row in the table below says that it can be removed, or
when every functionality of its row is done or accepted as a gap by the developer. A deleted script never takes a
feature with it that is not written down.

### http_old scripts and the functionality to move

The detailed description of the rows marked with "prio 2" is the section `### <view> (missing parts of the retired
http_old/<script>.php)` in `docs/llm/pending_prio_2.md`.

| script | generic replacement | functionality to move |
|---|---|---|
| `async_process.php` | none | an admin imports the system data (`import_file::import_system_data`) with the progress shown; the job type `job_types::BASE_IMPORT` exists, but nothing in `web/` can create the job: add an admin button that creates it and show the progress on the job control view (`views::JOB_CONTROL_ID`) |
| `calculate.php` | none | an admin recalculates the results of all formulas in blocks with the progress shown every response time (`RESPONSE_TIME` config); add a job type for the full recalculation, an admin button that creates the job and the progress on the job control view, never a long running request |
| `error_log.php` | `views::ERROR_LOG_ID` | show the sys_log entry of the url id (`sys_log_ui::page_view`); today `system_page::error_log` returns only `'error_log placeholder'` |
| `formula_result.php` | `views::RESULT_EXPLAIN_ID` | prio 2: explain a result by `web/result/result.php::explain` with the leading phrase and the time phrase of the url; today `system_page::result_explain` is a placeholder |
| `formula_test.php` | `views::FORMULA_TEST_ID` | prio 2: the step by step calculation with the 'more details' level and the response time limit, the 'refresh results' button and maybe the admin only 'user' parameter; today `system_page::formula_test` is a placeholder |
| `get_csv.php` | csv export of the value tables, `views::EXPORT_CSV_ID` | a csv of the results of the formulas given by name (`url_var::WORDS`) with the `ref_text` of each formula as the column head; check if the new csv export of the value tables covers it, else add it to the export views |
| `get_json.php` | `api/json`, `views::EXPORT_JSON_ID` | prio 2 (`### json export`): the json export of a phrase list with all related phrases (`phrase_list::are` + `json_io::export`); `api/json` is hard coded to the word id 1 and the export views do not export |
| `get_json_test.php` | none needed | nothing; a static json answer for the autocomplete experiments of `src/test/php/dev/test_js.php`: **can be removed** (prio 2 also asks to remove or rewrite these experiments) |
| `get_xml.php` | `views::EXPORT_XML_ID` | the xml export of a phrase list with all related phrases (`phrase_list::are` + `xml::export_by_phrase_list`); the export views do not export yet (see `### json export` in prio 2) |
| `import.php` | `views::IMPORT_ID` | prio 2: process the uploaded json file (checks for type, size and `is_uploaded_file`), show the counters per object type and allow it only for `user::can_import`; today the upload reaches only `log_ignored_write_step` |
| `json_save.php` | `api/json`, `views::EXPORT_JSON_ID` | the same json export as `get_json.php`; nothing in addition, so solved with the `get_json.php` row |
| `link_del.php` | `views::TRIPLE_DEL_ID` | nothing; the triple del view shows the triple, its usage and the confirm: **can be removed** |
| `phrase_list.php` | `views::WORD_ADD_ID` | nothing new; its body was a copy of the retired `word_add.php`, whose gaps are in prio 2 (`### word add view`): **can be removed** |
| `source_add.php` | `views::SOURCE_ADD_ID` | remember the new source as the last used source of the user (`user::src`, `user_db::FLD_SOURCE`) and preselect it in `web/value/value.php::source_selector`; the empty name, the duplicate name (now a merge if url and doi agree, else `NAME_ALREADY_EXISTS`), the fields and the back navigation are covered |
| `source_edit.php` | `views::SOURCE_EDIT_ID` | remember the changed source as the last used source of the user (as `source_add.php`); verify that the edit view without an id or with an unknown id tells the user that the source is missing, as the old page did |
| `source_del.php` | `views::SOURCE_DEL_ID` | nothing; the del view shows the name, the description, the usage and the confirm: **can be removed** |
| `user.php` | `views::USER_ID` | prio 2: the settings form instead of `'user_setting placeholder'`, the latest changes of the user, the import link for `user::can_import` and all open issues for an admin |
| `value` | none needed | nothing; an html meta refresh to the no longer existing `http://www.zukunft.com/value.php`: **can be removed** |
| `value.php` | `views::VALUE_DEFAULT_ID` | prio 2: show the number instead of `'main_value placeholder'` and maybe open a value by the names of its phrases (`t=zurich,inhabitants`) |
| `value_add.php` | `views::VALUE_ADD_ID` | prio 2: the phrases of the new value from the calling page, the conversion of the user entry (`value_base::convert`) and the selected source as the default source of the user |
| `value_edit.php` | `views::VALUE_EDIT_ID` | prio 2: show and change the phrases of the value and refuse an empty number (`web/value/value.php::input_valid`) |
| `value_del.php` | `views::VALUE_DEL_ID` | prio 2: show the phrases of the value in the delete question |
| `values_paste.php` | none | nothing; the whole body is commented out and never worked, the idea to paste a table of values is not a feature that is lost: **can be removed** |
| `verb_add.php` | `views::VERB_ADD_ID` | the field "name in formulas" (`url_var::NAME_IN_FORMULA`; the component `system form field name in formulas` exists but is not part of the view); refuse a verb name that is already used by a word, triple or formula as the old page did with `term::load_by_name` (see the defect in step 2) |
| `verb_edit.php` | `views::VERB_EDIT_ID` | the field "name in formulas" as for `verb_add.php`; remember the verb as the last used verb of the user (`user::set_verb` is a stub without db field) |
| `verb_del.php` | `views::VERB_DEL_ID` | nothing; the del view shows the name, the usage in triples and formulas and the confirm: **can be removed** |
| `verbs.php` | `views::VERBS_ID` | prio 2: the list of all verbs with an edit, a delete and an add button for an admin; today the view has no component |
| `view_add.php` | `views::VIEW_ADD_ID` | prio 2: open the view edit view after the add, the sample word and the view type preset via the url |
| `view_edit.php` | `views::VIEW_EDIT_ID` | prio 2: the view designer (link, create, unlink and move the components) and the sample word |
| `view_del.php` | `views::VIEW_DEL_ID` | nothing of the old page; the missing in-use check is a gap of both pages and stays in prio 2: **can be removed** |
| `view_pur.php` | `/http/view.php` | nothing; an unfinished experiment of a view-only render that only called `frontend::show_view`: **can be removed** |
| `view_select.php` | alternative views of the edit icon menu | prio 2: the 'design the view' and the 'delete the view' button per view and the saving 'switch' button |

### bugs found while checking http_old

All found by reading the code, none by a test run; confirm each with a failing test before the fix.

#### Code

1. `web/verb/verb.php::url_mapper` (line 154) sets `frm_name` from `url_var::IMPACT` instead of
   `url_var::NAME_IN_FORMULA`, so the name in formulas of a verb is taken from the impact field of the url.
2. `web/component/execute/system_form.php::form_field_name_in_formulas` shows the label
   `msg_id::FORM_FIELD_PLURAL_REVERSE` instead of `msg_id::FORM_FIELD_NAME_IN_FORMULAS`.
3. `cfg/verb/verb.php::get_similar` reports a name that is already used by a word, triple or formula
   (`term::id_used_msg`) only to the local `$sim_msg` buffer of `verb::save`, which is dropped if no similar verb is
   found, so a verb with the name of a word is added; the old `verb_add.php` refused it. A dropped message is also a
   never-fail-silently violation.
4. `cfg/user/user.php::set_verb` carries the comment "remember the last source that the user has used", but it is
   about the verb, and it writes nothing (the update is commented out, the db field is missing).

#### Documentation

5. Every `/http/view.php?m=<number>` in the `http_old` sections of `docs/llm/pending_prio_2.md` names an outdated
   view id; the consts behind the numbers are right, the numbers are not, e.g. (as in the doc → current const):
   `WORD_ADD_ID` m=2 → 21, `WORD_EDIT_ID` m=3 → 48, `TRIPLE_ID` m=92 → 4, `TRIPLE_ADD_ID` m=9 → 22,
   `TRIPLE_EDIT_ID` m=10 → 51, `TRIPLE_DEL_ID` m=11 → 52, `VALUE_ADD_ID` m=18 → 20, `VALUE_EDIT_ID` m=19 → 54,
   `VALUE_DEL_ID` m=20 → 55, `VALUE_DEFAULT_ID` m=96 → 5, `FORMULA_ADD_ID` m=24 → 56, `FORMULA_EDIT_ID` m=25 → 57,
   `FORMULA_DEL_ID` m=26 → 58, `VIEW_ADD_ID` m=30 → 84, `VIEW_EDIT_ID` m=31 → 85, `VIEW_DEL_ID` m=32 → 86,
   `COMPONENT_DEL_ID` m=35 → 90, `RESULT_EXPLAIN_ID` m=70 → 63, `FORMULA_TEST_ID` m=71 → 59, `UNDO_ID` 73 → 104,
   `USER_ID` m=74 → 123, `IMPORT_ID` m=76 → 145, `EXPORT_ID` … `EXPORT_ODS_ID` m=77 … m=81 → 150 … 154,
   `VERBS_ID` m=86 → 170, `VERB_ADD_ID` / `VERB_EDIT_ID` / `VERB_DEL_ID` m=6 / 7 / 8 → 70 / 71 / 72 (m=6 is now the
   source view), `PHRASE_ID` m=110 → 112. Name the view by its const only, because a number shifts with every new
   seed view; m=20 is now even the value add view instead of the value del view.

#### Other

1. **List the functionality to move per script.** Done: see the table above.
2. **Fix the code bugs 1 to 4 above,** each with a positive and a negative unit test, and fix the documentation bug 5
   by replacing the numbers with the consts.
3. **Add the missing form fields** that need no new concept: the "name in formulas" field to `verb_add` and
   `verb_edit` (appended component position, no new component), with the workflow snapshots.
4. **Show the error log entry** in `views::ERROR_LOG_ID` by the url id, reusing `sys_log_ui::page_view`, with a page
   test in `unit_ui/`.
5. **Remember the last used source and verb.** Decide with the developer when the user default is set (after the add
   or edit of a source, after a value saved with a source, or both), add the missing verb db field with the minor
   version raise and the `db_check` upgrade, set the default via the api and preselect it in the value and triple forms.
6. **Admin jobs:** a button for an admin that creates a `BASE_IMPORT` job, and a new job type to recalculate all
   formulas (replacing `calculate.php`), both shown on the job control view.
7. **Export and import:** finish the `### json export` and `### import view` sections of `pending_prio_2.md`
   (`api/json`, the export views, the upload of the import view).
8. **Work off the other sections** of `pending_prio_2.md` that name a retired `http_old` script (e.g. the view
   designer of `view_edit.php`, the phrase links of `word_add.php`, the formula assignments of `formula_edit.php`), in
   the order of their prio, one section per commit.
9. **Delete the scripts** in batches: first those marked **can be removed** in the table, then each script when its
   row is done or accepted. Each batch also removes the legacy code that only these scripts used (e.g. `dsp_edit`,
   `linked_components`, `btn_yesno`, `dsp_go_back`, after a search for every caller), and keeps every `include_once`
   that might still define a class.
10. **Guard it:** add a check to `unit/coding_rule_tests.php` that no file in `src/` links to a `/http/<name>.php`
    other than the scripts in `http/`, so a retired script cannot get a new caller.
11. **Finish:** remove `http_old/` and the `TODO clean up http_old` line of `docs/todo.md`.

if done remove this prompt from pending.md

**next prompt**

**full test run with re-pin: first /test/reset_db.php, then at least twice /test/test.php**

**Prompt:** the word splitter (size S). One agent: "later", the word splitter in `convert_wikipedia_table.php` with a
positive and a negative test.

## later

add the word splitter to convert_wikipedia_table.php

if done remove this prompt from pending.md

**next prompt**

**full test run: /test/test.php**

**Prompt:** the error limit for independent import entries (size M; re-pin, because the new `config.yaml` key
becomes a seed word and shifts the ids after it). One agent: "error limit", see `docs/llm/dependent-errors.md` and
the entry of the same name in `docs/llm/pending_prio_2.md`.

## error limit

1. **Config key:** add the number of errors after which the tries of fully independent import entries stop to
   `config.yaml` under `import`, with a default and a tooltip-comment that says each user can overwrite it.
2. **Read it:** read the value via the config of the requesting user (`$msg->usr`), so that a user overwrite wins
   over the default.
3. **Stop at the limit:** in the import loops over independent entries, stop when the per-row errors reach the
   limit and add a notice with `ok = true` that the rest was not checked (`docs/llm/dependent-errors.md`, rule 3).
4. **Tests:** a positive and a negative test on both sides of the limit, and a test that a user overwrite of the
   limit is used instead of the default.
5. **Finish:** remove the entry "error limit for independent import entries" from `docs/llm/pending_prio_2.md`.

if done remove this prompt from pending.md

**next prompt**

**full test run with re-pin: first /test/reset_db.php, then at least twice /test/test.php**

## the delete rules of docs/llm/delete.md that the code does not follow yet

1. **`del_links` only for system and admin users:** `sandbox::del_exe` calls `del_links()` only if the requesting
   user is a system or admin user; for any other user an object with dependent rows (section 5 of
   `docs/llm/delete.md`) is refused with a new message that names the dependent rows, so that the user removes
   them first. Add the message to `messages.php` and the translations.
2. **An excluded word excludes its results:** if `del()` excludes a word for the user (`exclude_exe`), all results
   with the word in their group are excluded for this user too.
3. **Tests:** a write test that a normal user cannot delete a word with own values (refused, word and values kept)
   and that an admin user can (word and values deleted); and a write test that the results of a word excluded by
   usr2 are hidden for usr2 and still shown for usr1.

if done remove this prompt from pending.md

**next prompt**

**full test run: /test/test.php**

## expected errors of the next test.php run

The write section of `test.php` is not expected to run without errors yet. Open after the last fixes:

1. **Formula write flow not run yet:** the `assert_write_named` call of `formula_write_tests.php` is switched on
   again after a long time with the included `formula_filled_add`. The flow deletes the formula as `usr2`, then as
   `usr1`, re-adds it and deletes it again; a formula delete also removes the formula word, its links and
   elements, so follow-up errors are expected there, e.g. a formula word that survives or a name in use from the
   word of the previous add.
2. **View write flow after the include fix:** the exclude check of `view_write_tests.php` should pass with the
   included `view_filled_add`, but the later steps of `assert_write_named` for views (ownership transfer, all
   delete, re-add) have never been reached before.
3. **Cleanups unconfirmed:** `cleanup_without_created` and the remembered rows of `create_test_db_entries` in
   `check_cleanup` are not confirmed by a full run; the remembered ids also skip a real leftover that existed
   before the create, so such a leftover is not reported.
4. **Timeout of 182 seconds:** only made measurable, the cause is unknown.
5. **Final cleanup order:** words hidden by a cleanup may block the delete of the rows that use them.
6. **Delete rules not implemented:** see the delete rules of `docs/llm/delete.md` at the end of this file.
7. **`exclude_exe` guard:** returns ok when the object is already excluded for the user; reasonable, but it
   also hid the excluded standard row of the view until the log check caught it.

The next step is a `test_part.php` run with the active word, triple, value, formula, job and view write tests
and to fix what it reports, starting with the formula write flow.

