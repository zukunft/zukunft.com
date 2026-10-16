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

### The order of the component types: most used first, grouped by similarity

`src/test/resources/unit/component/list_types_sorted.csv` is the target order of
the component types (`db_code_links/component_types.csv`), so that a type
selector shows the types an editor needs most often on top and similar types
side by side. Only the rows and the ids differ from the code links: the
`code_id` and the `type_name` stay unchanged, because the code selects a type by
its `code_id` only.

The groups follow how often the types are used by the components of the import
json (e.g. `system_title` 66 times, `system_form_title` 52, `system_sub_title`
16, `change_log` 13), from the top:

1. **page titles** — the system titles and the `title_of_…_with_edit_link` types
2. **form frame** — the hidden back stack and confirm status, the buttons, form end; a view
   with a `form_end` opens its form with a `system_form_title` (never a plain `system_title`),
   and a view whose component renders its own form (the file selector of the import) has no
   `form_end`, because html forms cannot nest (`coding_rule_tests::html_form_balance_tests`)
3. **layout rows** — row start, center, right, end
4. **tabs and history** — view tab box, change log, usage, user overwrites
5. **object names** — word, triple, verb, source, … names, aliases, symbols
6. **related phrases** — children, parents, the lists up and down
7. **values** — related values, table, columns, chart, values by triple or source
8. **results and formulas** — including the expression displays
9. **read only fields** — `system_show_field_*`
10. **form fields** — `system_form_field_*`
11. **object selectors** — each single selector followed by its multi selector
12. **type selectors** — `form_select_*_type`, share, protection
13. **link settings** — the priority and position of a link
14. **views and components** — the view relations, preview and diff
15. **confirm popup** — title, object, changes, impact
16. **simple add forms** — add a word, triple or value without the confirm view
17. **import and export**
18. **selection and ranking**
19. **system page bodies** — `system_body_*`
20. **admin** — the admin form fields and the admin monitoring bodies

Within a group the more general type comes first (e.g. the name before the
description, the plural before the reverse). A new type is added to the group it
is most similar to, and the usage counts decide only the order of the groups,
never the id of an existing type in the code links (the id shift rules above
apply to the types as well).

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
`style` is an optional Bootstrap column class (e.g. `col-md-4`) or one of the
coded styles of `view_styles.csv`: `text-left`, `fields-in-line` and `all-rows`,
which shows every row of a list component instead of the short list with the
more link, e.g. the result chain of a use case table (`docs/llm/frontend.md`
"A page never fills the screen").

### The process catch views: four pages for one question phrase

`process_catch_views.json` holds the start, main, detail and result page of a
process catch (code ids `views::PROCESS_CATCH_START` … `_RESULT`), which guide
the user of a question phrase e.g. the PV calculator from one easy question to
the result. The id of each page is the phrase id of the question, so the same
four views serve every question; the fields of a page come from the field
triples of the question phrase (`process_catch_fields.json`, see
`docs/llm/json_structure.md` "A system keyword with properties and types").

| component type | what it shows |
|---|---|
| `system_title_for_phrase` | the translated page title with the name of the question phrase |
| `system_text_explain` | the translated `ui_msg_code_id` of the component as a paragraph |
| `process_catch_fields` | the input fields of the page or, with a tab code id, of one tab, shown only if that tab is selected |
| `process_catch_tab` | one tab: the selected tab as text, every other tab as a link to the same page with the tab url var (`url_var::TAB`) |
| `process_catch_button_next` | a link to the next page with the same question phrase (`views::PROCESS_CATCH_NEXT`) |
| `process_catch_button_back` | a link to the page before with the same question phrase (`views::PROCESS_CATCH_BACK`) |
| `process_catch_button_refresh` | a form with the page, the question phrase and the selected tab (only if the url selects one) as hidden fields and a submit with the `ui_msg_code_id` text, so the page is shown again with the entered values; the editors use it as the form of their phrase selector, whose selected name replaces the phrase of the page (`views::PHRASE_SELECT_VIEWS`) |
| `geolocator` | a text input for a place name or postal code with the `ui_msg_code_id` as label and `url_var::PLACE` as name; it joins the refresh form by its `form` attribute, and the `row` phrase of the component is the value phrase of the place (`place of user`) |

A tab and the fields of the tab are linked by the **`code_id`** of the
component (`components::PROCESS_CATCH_TAB_NBR`), never by a shared
`ui_msg_code_id`, because the ui message of a component is unique (see above);
the tab label is the message of the tab number in the code
(`system_page::PROCESS_CATCH_TABS`). Every link carries the whole page state,
so the pages work without javascript.

### The components of a use case page

A use case page like the PV lifetime balance (`pv_lifetime_balance.json`) is
built from the related columns table and these component types, which take
their text from the component itself, because a use case may carry no
`ui_msg_code_id` (see "Use case files" in `docs/llm/json_structure.md`):

| component type | what it shows |
|---|---|
| `view_title` | the name of the view as the page title and the description of the view as the lead text; the view of the page is put into the request cache by `view_exe::show` |
| `name_sub_title` | the **name** of the component as a section heading, so the component is named like the heading e.g. `Inputs` |
| `description_text` | the **description** of the component as a paragraph e.g. a note of the page |
| `details_start` | the start of a collapsed block with the name of the component as the summary line; the block spans the components up to the `details_end`, like a `row_start` spans a row |
| `details_end` | the end of the collapsed block |
| `value_input_fields` | the values of the page phrase as number input fields, each labelled by the other phrases of the value and sorted by the label; a first version until the fields come from the field triples of the phrase like the process catch fields |
| `sources_used` | the sources of the values of the page phrase as a list with a link to the source url, each source once, sorted by name |

The save button of the inputs reuses `system_button_save` until the save
starts the backend calculation (`docs/llm/frontend.md` "The frontend
calculates, a save job verifies").

## View relations

A view relation links a parent view to a child view; its `type` says what the
child does for the parent:

```json
"view_relations": [
  { "parent": "Table", "child": "Table editor", "type": "editor" }
]
```

| type | effect |
|---|---|
| `add_components` | the components of the child are added to the parent at the start `position` |
| `del_components` | the components of the child are removed from the parent |
| `editor` | the edit menu of the navbar offers the child instead of the alternative views, opened with the same object |

`parent` and `child` name views **of the same file**, because the import maps a
relation to the views it has just read; a view of another file is re-declared by
its name only (`{ "name": "Table" }`), like a borrowed word.

