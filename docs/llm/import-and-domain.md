# Import & domain rules

Detail for the "Domain & import rules" in `CLAUDE.md`. These fire only when
touching domain objects or import JSON. Domain noun definitions:
`docs/llm/architecture.md`.

## The `percent` measure auto-scales

The `percent` measure word is auto-scaled by the formula engine: a formula whose
result is assigned to `percent` and that computes a ratio (e.g.
`( "this" - "prior" ) / "prior"`) is shown as a percentage **without** an
explicit `* 100`. Do not "fix" such formulas by adding `* 100` — the missing
factor is intentional; scaling happens via the `percent` measure, not the
expression.

## Some symbols and abbreviations are intentionally ambiguous

A short symbol can be the alias of more than one phrase on purpose — `m` is the
symbol for the SI unit `metre` (in `units.json`) and the abbreviation for
`million` (in `scaling.json`). This is **by design**: the context (the other
phrases in the value's group) disambiguates. Do not force the symbol unique or
rename one side; only flag a genuine unintended collision (e.g. a formula name
equal to a triple name).

## Disambiguate an ambiguous word with qualifier triples

When a single word can mean more than one thing, the word stays **defined
once**, and each distinct meaning is made unique by a **qualifier triple** —
never by duplicating or renaming the word. The bare word is the shared label;
the data always references the unambiguous triple.

Take `second` (time unit or ranking number):

1. Define the word `second` **once**, with a description stating it has several
   usages (e.g. *"used both as the SI time unit and as the ranking number;
   reference the qualifier triple, not this word"*).
2. Create one triple per meaning: `second (time unit)` and
   `second (ranking number)`, using the verb **`must be one of`** (a verb for
   exactly this purpose) — each meaning `must be one of` the readings of
   `second`.
3. In all data (import JSON, values, formulas, links) reference **only the
   triples**, never the bare word.

This is the word-level counterpart of the intentional symbol ambiguity above: a
symbol may *alias* several phrases on purpose, but an ambiguous *word* is
resolved by pinning each meaning to its own qualifier triple.

### Display rule: show the original word, qualifier in the tooltip

Although the data references the qualifier triple, the user still sees the short
familiar word. When a qualifier triple is shown, render **only the original
word** and move the qualifier into the tooltip.

- **Right**: `second (time unit)` displays as `second`, with `time unit` only in
  the mouse-over tooltip
- **Wrong**: printing the full triple name `second (time unit)` inline

## A triple's `from`/`verb`/`to` combination is unique within an import

Within the same import JSON a triple is identified by its `from` + `verb` + `to`
combination, so that combination must not be reused for two triples with
**different names or meanings** — the duplicate key gives an ambiguous id
assignment on import.

When two distinct concepts would share the same `from`/`verb`/`to`, introduce an
intermediate **building-block triple** and point one concept at it. E.g. `Newton`
`name of` `law` was used for both *Newton's second law* and *Newton's law of
gravitation*; the fix is to first create `second law` (= `second` `kind of`
`law`) and build *Newton's second law* as `Newton` `name of` `second law`, so its
key differs from the gravitation triple's. When such a building block already
exists implicitly (e.g. `first`/`second` `kind of` `law` was also used by the
thermodynamics laws), rebuild those users on top of the new block too.

This is distinct from the intentional symbol ambiguity: the same **name** may
alias several phrases on purpose, but the same **triple key** must never map to
two different triple names.

### A triple whose `from`/`to` is a named triple must have its own explicit `name`

When a triple references another **named** triple as its `from` (or `to`) — often
an `is part of` membership triple built on a named law/concept triple — it
**must carry an explicit, unique `name`**. If omitted, the import cannot build a
distinct name (the referenced triple's name is not resolved yet) and reuses the
referenced triple's name — and two triples must not share a name, so import
fails.

- **Wrong** — second triple has no `name`, collides with the first:
```json
{ "name": "Faraday's law of electrolysis", "from": "Faraday", "verb": "name of", "to": "law" },
{ "from": "Faraday's law of electrolysis", "verb": "is part of", "to": "electrochemistry" }
```
- **Right** — give the membership triple its own name:
```json
{ "name": "Faraday's law", "from": "Faraday", "verb": "name of", "to": "law" },
{ "name": "Faraday's law of electrolysis",
  "from": "Faraday's law", "verb": "is part of", "to": "electrochemistry" }
```

A membership triple whose `from` is a plain **word** (e.g. `force` `is part of`
`mechanics`) can stay unnamed — the word's name is available. The clash arises
only when the `from`/`to` is itself a named triple.

## Assign an import formula to a same-file input phrase, not its result

A formula is linked to phrases via `assigned_word` (single) or the `assigned`
array (several). Assign it to the **phrase(s) it uses as input**, **never** the
result it computes — the assignment makes the formula *applicable*: a formula is
offered wherever an assigned phrase has a value. `"pH" = - log("hydrogen ion
concentration")` is assigned to `hydrogen ion concentration` (input), so pH can
be computed wherever a hydrogen-ion concentration is known. Assigning to `pH`
(result) would be wrong — you'd already need the pH to find the formula.

- **Right (single)**: `{"name": "definition of pH", "expression": "\"pH\" = - log(\"hydrogen ion concentration\")", "assigned_word": "hydrogen ion concentration"}`
- **Right (several)**: `{"name": "self-ionization of water", "expression": "\"pH\" + \"pOH\" = 14", "assigned": ["pH", "pOH"]}`
- **Wrong**: assigning either formula to its result

**Import files must be strictly self-consistent**: every phrase a formula is
assigned to must be defined in the same import file (each file imports with its
own per-file cache, so a phrase from another file can't be resolved). If a
formula's inputs all live elsewhere — e.g. `"molar mass" = "mass" / "mole"` whose
inputs belong to the units/physics data and must **not** be redefined — either
define the formula in the file owning its inputs, or leave it **unassigned**.

The same self-consistency applies to **triples**: every `from` and `to` phrase
must be defined in the same file. When a file only *references* a base word it
doesn't own (e.g. `chemistry` `is part of` `science`), re-declare that base word
name-only: `{"name": "science"}` / `{"name": "law"}` (exactly how `physics.json`
re-declares `kg`, `metre`, `second`). On import the name-only entry merges with
the canonical definition in its home file — no data duplicated.

This self-consistency is about import resolution (assignments and triples). A
formula's `expression` may still reference phrases from earlier base files (every
physics formula uses units like `kg`, `metre`); those resolve when the formula is
calculated, not via the per-file import cache.

## Qualify a value as specifically as possible — prefer triples from single words

A value's `words` array is the phrase group the number belongs to. **Always
describe a value as specifically as the data allows**: add as many qualifying
phrases as possible. A bare `{"words": ["price"], "number": "20"}` claims *the*
price is 20 — meaningless. Add the context (dataset, entity, period, source).

Express each qualifier as a **phrase built from single words**, and **prefer a
triple over a flat extra word**:

- Define the individual words (`economics`, `textbook`, `example`).
- Combine them with **existing verbs** into triples, building up. Because each
  triple's `to`/`from` is itself a named triple, give it an explicit `name`:
  - `{"from": "textbook", "verb": "of", "to": "economics", "name": "economics textbook"}`
  - `{"from": "example", "verb": "of", "to": "economics textbook", "name": "economics textbook example"}`
- Reference the resulting triple by name in the value's `words` array.

- **Vague**: `{"words": ["price"], "number": "20", "share": "public", "source": "economics textbook example"}`
- **Specific**: `{"words": ["price", "economics textbook example"], "number": "20", "source": "economics textbook example"}`

**`"share": "public"` is the default and must be omitted** — only add `share`
when it differs from `public`.

## `import_mapper` maps from the dto only — never reads the database

`import_mapper` (and helpers like `import_map_names`) must **only map the object
from the json and resolve references from the passed-in `data_object` (the
per-file import cache, `$dto`). It must never read from the database.** If a
referenced phrase/word/triple/source is not in the dto, **add a translatable
error to `$msg`** (e.g. `msg_id::IMPORT_FORMULA_ASSIGN_PHRASE_MISSING`) — do
**not** load it from the DB and do **not** create a placeholder.

This keeps import deterministic and each file self-consistent: everything a file
references must be present in that file's import (re-declared name-only if a base
word), and the mapper fails loudly otherwise. Any DB access — loading rows,
cross-file lookups, merging — belongs to the **save** step, not `import_mapper`.

## A component's `ui_msg_code_id` is globally unique — never reuse on a new component

The `components` table has a unique key `components_ui_msg_code_id_uk` on
`ui_msg_code_id` (not covering `ui_msg_code_id_vars`/`_exception`; NULL allowed
many times). So a `ui_msg_code_id` effectively identifies one component. Two
component definitions in the import JSON must never carry the same
`ui_msg_code_id` under **different** `code_id`s — the import then tries to
`INSERT` a second row with a duplicate `ui_msg_code_id` and the save fails with
`duplicate key value violates unique constraint "components_ui_msg_code_id_uk"`.

Because the import `$dto` is **per file** (a view-component link resolves only
via `$dto->get_component_by_name()`), a view in one file (`base_views.json`)
cannot link a component defined only in another (`system_views.json`). The fix is
the component counterpart of name-only base-word re-declaration: **re-declare the
existing system component with its exact canonical `name` + `code_id` (and the
same `type`/`ui_msg_code_id*` fields)**, then link by that name. On save the
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
