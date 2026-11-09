# delete — when an object is deleted, when it is only excluded, and what goes with it

How to delete (always the logged `del()`) is top rule 4 of `docs/llm/coding.md` and the
section "Delete always with change log" of `docs/llm/architecture.md`. This file lists
*when* `del()` deletes, when it only excludes the object for the requesting user, and which
dependent rows it removes.

## 1. Entry and permission

- `del()` of the object is the single entry point for every delete (frontend, api, import,
  test cleanup).
- First `can_be_deleted_by($msg)`: only a user with a login, or an ip user if the pod permits
  it, may delete; otherwise `NO_UPDATE_PRIVILEGES` and nothing is changed.
- The object is reloaded from the database; a link object also reloads its linked objects.

## 2. Delete or exclude (user sandbox objects)

Valid for word, triple, formula, view, component, source, ref and the link objects
(`sandbox::del`), and in the same way for values (`sandbox_multi::del`).

1. **Not used by someone else → delete** (`del_exe`).
2. **Used by someone else** (`used_by_someone_else`) means:
   - the requesting user is not the owner, or
   - another user has a changed user row of the object (`changer`), or
   - for a word or triple only: the object is used by data (`used_by_data`, see 3).
3. **The owner deletes a used object** → the median user becomes the owner (`set_owner`;
   a link object writes its standard row with the reloaded linked objects), the user row
   of the new owner is removed (`del_usr_cfg_exe`), and the check of 2 is repeated: if
   nothing uses the object any more, it is deleted.
4. **Otherwise → exclude only for the requesting user** (`exclude_exe`): the user row gets
   `excluded = 1` with its change log entry, and the user gets the notice
   `DELETE_EXCLUDED_IN_USE` with `ok = true`. If the user already sees the object as
   excluded, nothing is written. The standard row and all other users keep the object.
5. **An excluded word takes its results with it:** if a word is excluded for the user, all
   results with the word in their group are excluded for this user too, so that the user
   sees no result of a word that the user has removed.

## 3. Used by data (word and triple)

A word or a triple is used by data if at least one of these exists:

| user | found via |
|---|---|
| a value with the phrase in its group | `value_list::load_by_phr` (limit 1) |
| a result with the phrase in its group | `result_list::load_by_phrase` |
| a formula linked to the phrase | `formula_list::load_by_phr` |
| a formula whose expression names it | `formula_list::load_by_word_ref` / `load_by_triple_ref` (element rows) |
| a triple with it as from or to | `triple_list::load_by_phr` |

A word or triple without database id is never used by data.

## 4. The delete itself (`del_exe`)

1. `del_links()` removes the dependent rows of section 5, with a message of its own; only for a
   system or admin user (see 5). For any other user, an object with dependent rows is not
   deleted: the delete is refused with a message that names the dependent rows, and the user
   removes them first.
2. Only if that message is ok, the object row is removed with the logged delete
   (`sql_type::LOG`); otherwise `FAILED_TO_DELETE_UNUSED` and the object stays.

## 5. Dependent rows (`del_links`)

`del_links()` may only be called by a system or admin user, because it also removes rows
that other users have created or still use.

| deleted object | removes before itself | how |
|---|---|---|
| word | its values, its triples | `del()` of each; a value with `NO_USER_SANDBOX` (see 6) |
| triple | its values | `del()` with `NO_USER_SANDBOX` |
| view | its component links and view relations | `del()` of each (delete or exclude) |
| component | its component links | `del()` of each (delete or exclude) |
| formula | formula links, formula elements, results, the formula word | elements and results without log (derived rows), the rest with `del()` |
| source | its refs | `del()` of each |
| link objects (component link, formula link, view relation, term view) | nothing | — |
| value | nothing | — |
| group | nothing (its values must be deleted before) | — |
| phrase | forwards to the word or triple | — |

A delete only goes from an object to the rows that depend on it, never back: the delete of a
value, triple, formula or link never deletes a word, triple or formula it refers to.

## 6. Values

- Owner, changer and median user as in section 2; a value has no data usage check.
- With `NO_USER_SANDBOX` (the value is deleted because a phrase of its group is deleted) a
  value still used by another user is refused with an error instead of excluded, because
  without the phrase no user could select it any more.
- `del_exe` removes the excluded user rows of the value and then the value with log.
- The group row of the value is not removed by the value delete.

## 7. Rows deleted without log

Only derived rows that the next read recreates, guarded as described in
`docs/llm/architecture.md`: cache rows, formula elements, results.

## 8. Test cleanup

- Test rows are deleted with `del()`; their change log entries are removed before
  (`cleanup_change_log*`, see `docs/llm/testing.md`).
- `write_named_cleanup` deletes a named test row as `usr1`, `usr2` and `usr_normal`, also under
  the renamed name, and then `write_named_cleanup_force`: all user rows are removed and the
  owner deletes. By section 2 a test row that is still used by data stays as an exclusion
  of the owner, so the rows that use it are removed first.
- The full cleanup `test_cleanup::cleanup` removes in dependency order: the values of
  `test_val_ids`, the test groups, term views, views and components, …, triples, words.
- The rows of `create_test_db_entries` belong to the whole write section: only the full
  cleanup at its end removes them. A cleanup inside one write test removes only the rows of
  that test.
- `check_cleanup` looks for rows named `System Test%`; if it finds one, it runs the full
  cleanup, which also removes the rows of `create_test_db_entries`.
- `test/test.php` recreates the database before the write tests, so a test row that exists
  before its test adds it comes from a failed delete of the same run, never from an earlier
  run (see `docs/llm/testing.md`).
