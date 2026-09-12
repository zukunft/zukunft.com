# JSON import: views, components and view-validation

The view and component definition rules of a `.json` import file. The
envelope, the data rules (words, triples, formulas, sources, values) and the
`import_mapper` / `$dto` discipline are in `docs/llm/json_structure.md`.

## View-validation

Optional. The counterpart of `calc-validation` for *pages* instead of numbers:
each entry pins the most relevant output a page should show **after** the import,
so a layout or wiring regression is caught at import time. An entry is a
human-readable page URL plus the expected rendering as **Markdown** (the compact,
diff-friendly form of the page — not the full HTML):

```json
{
  "url": "http://localhost/http/view.php?words=Pi",
  "result": "# Pi\n\nis a *mathematical constant*\n\n## Values\n\n- 3.14159265359\n"
}
```

- `url` — the page to render, written in the **human-readable** url form
  (`?words=Pi`, `?mask_id=word&id=…`, `…&show`), never with raw internal ids.
- `result` — the expected most-relevant output of that page as Markdown; the
  import renders the page and compares, reporting a mismatch as a failed
  validation (it is not saved).

Keep `result` to the *relevant* output — the title, the key related phrases and
the top values/formulas — not every pixel, so the check stays stable across
cosmetic layout changes.

## Components

```json
{
  "name":           "system sub title values",
  "type":           "system_sub_title",
  "code_id":        "system_sub_title_values",
  "ui_msg_code_id": "system_sub_title_values"
}
```

### A seed component gets its database id from its import position — append, then pin

The import json cannot choose a database id: a seed component's id is simply its
creation order across the seed files (`cfg/const/files.php`:
`SYSTEM_DATA_FILES` → `BASE_DATA_FILES`, and within a file top to bottom). Two
consequences for every **new** seed component (and equally for seed views):

1. **Append, never insert.** Add the definition at the **end** of the
   `components` block of its file, and prefer the **latest-imported** file that
   can hold it. An insertion in the middle shifts the id of every later
   component and churns the generated baselines
   (`src/test/resources/unit/component/list.csv`,
   `.../component_link/list.csv`) by hundreds of rows. Real case: `system title
   verb` defined mid-`system_views.json` landed at id 99 and renumbered ~180
   rows; moved to `base_views.json` it became id 283 and the shift reverted.
2. **Pin the id where a test needs the number.** Code references a component
   only by `code_id` (never by the numeric id). But when a test needs the id,
   record it as a `*_ID` const in `shared/const/components.php`, re-baselined
   from the regenerated `list.csv` after a reset — never guessed — so a later
   shift fails the test loudly instead of drifting silently (same rule as the
   phrase id consts in `docs/llm/testing.md`). Test-only components that never
   come from the seed use the out-of-band 900 range (`COL_FIRST_ID = 901`) so
   they cannot collide with seed ids.

### `ui_msg_code_id` is globally unique — never reuse on a new component

The `components` table has a unique key `components_ui_msg_code_id_uk` on
`ui_msg_code_id` (not covering `ui_msg_code_id_vars`/`_exception`; NULL allowed
many times). A `ui_msg_code_id` effectively identifies one component. Two
component definitions in the import JSON must never carry the same
`ui_msg_code_id` under **different** `code_id`s — the import then tries to
`INSERT` a second row with a duplicate `ui_msg_code_id` and the save fails with
`duplicate key value violates unique constraint "components_ui_msg_code_id_uk"`.

Because the import `$dto` is **per file** (a view-component link resolves only
via `$dto->get_component_by_name()`), a view in one file (`base_views.json`)
cannot link a component defined only in another (`system_views.json`). The fix
is the component counterpart of name-only base-word re-declaration: **re-declare
the existing system component with its exact canonical `name` + `code_id`** (and
the same `type`/`ui_msg_code_id*` fields), then link by that name. On save the
component is matched by `code_id` and **merged** (updated in place) instead of
inserted, so the unique `ui_msg_code_id` is not duplicated.

- **Wrong** — new component, fresh `code_id`, borrowed `ui_msg_code_id`:

```json
{ "name": "word values subtitle", "type": "system_sub_title",
  "code_id": "word_default_values_subtitle", "ui_msg_code_id": "system_sub_title_values" }
```

- **Right** — re-declare the canonical component so the save merges by `code_id`:

```json
{ "name": "system sub title values", "type": "system_sub_title",
  "code_id": "system_sub_title_values", "ui_msg_code_id": "system_sub_title_values" }
```

This mirrors the "define once, link many" pattern already used inside
`system_views.json`.

## Views

A view is a named page layout that links an ordered list of components to a main
object type:

```json
{
  "name": "Word (default)",
  "description": "the default view for words",
  "code_id": "word_default",
  "type": "word",
  "components": [
    { "position": "1", "name": "Word title" },
    { "position": "2", "name": "system show field description" },
    { "position": "3", "name": "phrase aliases", "position_type": "combine" }
  ]
}
```

- `name` is the unique display name; `code_id` is the stable internal key
  (`word_default`, `triple_default`, …).
- `type` is the main object the view renders (`word`, `triple`, `verb`, `source`,
  `formula`, …).
- `components` is the ordered list of component links. Each entry references a
  component **by `name`** — defined in the `components` block above or re-declared
  canonically (see *Components*) — plus the link-only fields `position`,
  `position_type`, `style`.

### Component positions are contiguous, starting at 1

`position` is `1, 2, 3, …` with **no gaps**: the importer rejects a hole
(`the component position 4 is missing in the view "…"`, and every later component
reported as "position N instead of N-1"). When you remove a component, renumber
the rest so the sequence stays gapless.

### `position_type` places the component in the row/column flow

Optional, default `below`. The values that have coded layout behaviour:

| value | effect |
|---|---|
| `below` | start a new full-width row (the default) |
| `combine` | stack below the previous component **within the same column** |
| `side_or_first_below` | start the first column of a side-by-side group |
| `side_or_below` | start a following column of that group |
| `side_or_last_below` | start the last column of that group |

A side-or-below group shows its columns next to each other on wide screens and
wraps them onto fewer rows (down to one) as the screen narrows; build a multi-row
column by giving its first component the `side_or_*` type and the rest `combine`.
`style` is an optional Bootstrap column class (e.g. `col-md-4`).

