# JSON import file format

Rules for the zukunft.com JSON import/export format. These apply whenever you
author or edit a `.json` file under `src/main/resources/messages/**` or
`src/test/resources/import/**`, or any file consumed by
`import_file::json_file`. Domain noun definitions (what a *word*, *triple*,
*value* actually means) live in `docs/llm/architecture.md` — open that first if
the terminology is unfamiliar.

The always-on one-line rules are indexed in `docs/llm/coding.md` under "Domain &
import rules" and link back here for detail.

## Envelope

Every import JSON has this top-level shape:

```json
{
  "version":   "0.0.3",
  "time":      "2026-06-04 12:00:00",
  "user":      "username",
  "selection": [ "..." ],
  "description": "...",
  "words":     [ ... ],
  "triples":   [ ... ],
  "formulas":  [ ... ],
  "sources":   [ ... ],
  "values":    [ ... ]
}
```

### Self-consistency

Every assigned phrase, every triple `from`/`to`, every formula input, and every
source referenced by a value MUST be defined in the same file. The import
resolver works from the per-file `data_object` (`$dto`) and never falls back to
the database — a missing reference produces an error, not a DB lookup (see
*`import_mapper` reads only from `$dto`* below).

When a file *references* a base word it doesn't own (e.g. `chemistry`
`is part of` `science`), re-declare that base word **name-only**:
`{"name": "science"}` / `{"name": "law"}`. On import the name-only entry merges
with the canonical definition in its home file — no data duplicated.

A formula's `expression` may still reference phrases from earlier base files
(every physics formula uses units like `kg`, `metre`); those resolve at
calculation time, not via the per-file import cache.

### Version check

`version` is matched against `def::PRG_VERSION`. If the file's version is newer,
the import emits `msg_id::IMPORT_VERSION_NEWER`:
*`Import file has been created with version "X", which is newer than this,
which is "Y"`*. The check is non-fatal — subsequent objects are still
processed — but the message surfaces in the returned `user_message`.

## Words

A word is the atomic phrase:

```json
{
  "name": "Fermi",
  "description": "Italian-American physicist, after whom Fermi estimation is named",
  "type": "measure",
  "refs": [
    { "name": "Enrico_Fermi", "type": "wikipedia" },
    { "name": "Q8753",        "type": "wikidata" }
  ]
}
```

- `name` is the unique key. Descriptions and `refs` are optional.
- `type` is set only when the word is a measure (SI unit, `percent`, etc.).
- `refs` lists external citations (Wikipedia article slug, Wikidata Q-id).

### Words are the most atomic text — no spaces if it can be avoided

A word is the smallest reusable unit of meaning in the graph. Pick the
**shortest atomic token** that still names a concept on its own, and **never put
a space in a word** when a composition of single words plus a triple expresses
the same thing.

- `economics textbook` is **not** a word — define `economics` and `textbook`
  separately and combine them as the triple
  `{from: "textbook", verb: "of", to: "economics", name: "economics textbook"}`.
- `MNI coordinate`, `cluster size`, `t statistic`, `right intraparietal sulcus`
  — none of these are words. Each splits into single-word atoms (`MNI`,
  `coordinate`, `cluster`, `size`, `t`, `statistic`, `right`, `intraparietal`,
  `sulcus`) joined by composition triples (see *Composition pattern* under
  *Triples*).

The single-word atom is then reusable across many compositions — `count` is
shared by `participant count`, `actor count`, `scan count`, `laptop count`,
etc., each expressed as `{from: "count", verb: "of", to: "<thing>"}`. A
multi-word word would lock that reuse away.

Three narrow exceptions where a space inside a word name is unavoidable:

1. **Parenthetical disambiguation labels** (`second (time)`, `year (unit)`,
   `degree (angle)`) are **triple names**, not word names — they are the
   `must be one of` qualifier triples described below. The bare atomic word
   (`second`, `year`, `degree`) is still defined without a space.
2. **External proper nouns** that are written with a space in the real world
   (`Bosnia and Herzegovina`, `Burkina Faso`) stay as a single word — they have
   no atomic decomposition that is also a useful zukunft.com phrase.
3. **External identifiers** carried verbatim (a Wikipedia slug used in a `refs`
   entry, e.g. `Mental_chronometry`) are not word names and are not subject to
   this rule.

If you find yourself adding a multi-word `name` to the `words` array, stop and
decompose first — add the missing single-word atoms (often half are already in
the file) and a building-block triple per compound.

### No leading or trailing whitespace in any phrase name

A phrase `name` (word *or* triple), and every `from` / `to` / `assigned` /
`assigned_word` / value-`words[]` reference to one, is matched **byte-for-byte**.
A stray trailing space turns `"Civil liberties "` and `"Civil liberties"` into
two different phrases — the import either treats them as separate or fails to
resolve the reference, and the duplicate quietly proliferates as values copy
the typo.

- **Wrong** — name with trailing space:

```json
{ "name": "Civil liberties ", "from": "liberties", "verb": "kind of", "to": "Civil" }
```

- **Right** — trimmed name; references match exactly:

```json
{ "name": "Civil liberties",  "from": "liberties", "verb": "kind of", "to": "Civil" }
```

The same trim applies to every place a name appears: word/triple/source `name`,
triple `from`/`to`, formula `assigned`/`assigned_word`, value `words[]` entries,
and `value.source`. When you notice a trailing-space name in an existing file,
fix it everywhere in that file — partial trims create the same split-identity
problem.

### Lower-case the first letter unless the name needs a capital

A phrase `name` (word *or* triple) starts with a **lower-case letter** unless the
first token is a real proper noun (a person, place, organisation, ticker, ISO
code, etc.) or an established acronym/initialism. Sentence-case
capitalisation copied from a source document (a financial-statement caption, a
table header, a column title) is **wrong** — the import treats `"Gross profit"`
and `"gross profit"` as two distinct phrases (names are matched byte-for-byte;
see the trim rule above), so a mid-file mix silently splits the same concept in
two.

- **Wrong** — sentence-case copied from the source caption:

```json
{ "name": "Gross profit", "from": "profit", "verb": "kind of", "to": "gross" },
{ "name": "Total revenues", "from": "revenues", "verb": "kind of", "to": "total" }
```

- **Right** — lower-case first letter; proper nouns / tickers keep their case:

```json
{ "name": "gross profit", "from": "profit", "verb": "kind of", "to": "gross" },
{ "name": "total revenues", "from": "revenues", "verb": "kind of", "to": "total" },
{ "name": "net income attributable to ABB",
  "from": "net income", "verb": "of", "to": "ABB" }
```

Apply the same lower-case-first to every place the name appears: word/triple/
source `name`, triple `from`/`to`, formula `assigned`/`assigned_word`, value
`words[]` entries, and `value.source`. When you notice a stray-capital name in
an existing file, fix it everywhere in that file — a partial rename creates the
same split-identity problem as a partial trim.

### Intentional symbol / abbreviation aliasing

A short symbol may alias several phrases on purpose. `m` is the symbol for the
SI unit `metre` (in `units.json`) and the abbreviation for `million` (in
`scaling.json`). This is **by design**: the context (the other phrases in the
value's group) disambiguates. Do not force the symbol unique or rename one
side; only flag a genuine unintended collision (e.g. a formula name equal to a
triple name).

### Disambiguate an ambiguous *word* with qualifier triples

When a single word can mean more than one thing, the word stays **defined
once**, and each distinct meaning is made unique by a **qualifier triple** —
never by duplicating or renaming the word.

Take `second` (time unit or ranking number):

1. Define the word `second` **once**, with a description stating it has several
   usages (e.g. *"used both as the SI time unit and as the ranking number;
   reference the qualifier triple, not this word"*).
2. Create one triple per meaning, using the verb **`must be one of`** (a verb
   for exactly this purpose) — each meaning `must be one of` the readings of
   `second`: `second (time unit)` and `second (ranking number)`.
3. In all data (import JSON, values, formulas, links) reference **only the
   triples**, never the bare word.

**Display rule** — render only the original word (`second`), with the qualifier
in the tooltip. Never print the full triple name inline.

- **Right**: `second (time unit)` displays as `second`, tooltip `time unit`.
- **Wrong**: printing `second (time unit)` inline.

### Pick real company names that reflect well

When an example in these docs, or any sample data under
`src/main/resources/messages/**` or `src/test/resources/import/**`, names a real
company, choose a firm known for a **positive contribution** — e.g. the
wind-turbine maker `Vestas`. **Never** use a company associated with harmful
conduct (environmental damage, privacy or data abuse (US Cloud Act), labour or human-rights
violations, manipulation, ...), and avoid the large consumer-platform
incumbents whose reputation is contested. The data ships as part of the product;
the names we pick should not endorse bad actors. The same applies to people,
products and sources.

## Triples

A triple combines two phrases with a verb:

```json
{
  "name": "economics textbook",
  "from": "textbook",
  "verb": "of",
  "to":   "economics",
  "description": "..."
}
```

- `from` and `to` are phrase names (a word or another triple, defined in the
  same file).
- `verb` must be one of the predicates in `src/main/resources/verbs.json`.
- `name` is the unique display name of the triple.

### `from`/`verb`/`to` is unique within an import

Two triples must never share the same `from` + `verb` + `to` with different
names or meanings — the duplicate key gives an ambiguous id on import.

When two distinct concepts would share the same key, introduce an intermediate
**building-block triple** and point one concept at it. E.g. `Newton` `name of`
`law` was used for both *Newton's second law* and *Newton's law of gravitation*;
the fix is to first create `second law` (= `second` `kind of` `law`) and build
*Newton's second law* as `Newton` `name of` `second law`, so its key differs
from the gravitation triple's. When such a building block already exists
implicitly, rebuild the other users on top of the new block too.

This is distinct from the intentional symbol ambiguity above: the same **name**
may alias several phrases on purpose, but the same **triple key** must never
map to two different triple names.

### A triple whose `from`/`to` is a *named* triple must have its own explicit `name`

When a triple references another **named** triple as its `from` (or `to`) —
often an `is part of` membership triple built on a named law/concept — it MUST
carry an explicit, unique `name`. If omitted, the import cannot build a
distinct name (the referenced triple's name is not resolved yet) and reuses the
referenced triple's name — two triples must not share a name, so import fails.

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

### Omit `name` when it is just the auto-generated `<from> <verb> <to>`

The importer auto-generates a triple's name from `<from> <verb> <to>` when no
`name` is given. **Never repeat that exact string in an explicit `name`** — it is
pure noise and clutters the file. Only add an explicit `name` when the desired
display name **differs** from the auto-generated form (e.g. it omits the verb,
reorders parts, or carries a domain-of-art label), or when there would otherwise
be a name clash described above.

- **Wrong** — `name` repeats the auto-generated `<from> <verb> <to>`:

```json
{ "name": "ecosystem is part of ecology",
  "from": "ecosystem", "verb": "is part of", "to": "ecology" }
```

- **Right** — omit the `name`; the importer derives `ecosystem is part of ecology`:

```json
{ "from": "ecosystem", "verb": "is part of", "to": "ecology" }
```

- **Right** — keep an explicit `name` when it differs from the auto-form
  (here the display name `cell biology` is shorter than `biology kind of cell`):

```json
{ "name": "cell biology",
  "from": "biology", "verb": "kind of", "to": "cell" }
```

This applies even when `from` and/or `to` are named triples: the importer can
still build `<from> <verb> <to>` deterministically from the referenced phrase
names, so the explicit `name` is only required when a different display name is
wanted or when the auto-name would actually collide with another triple's name
in the file. Re-importing files where every `is part of` triple repeats its own
auto-name is a common LLM mistake — strip them.

### Composition pattern

For a compound noun `X Y` where `Y` is the head noun and `X` modifies it, the
conventional composition is:

```json
{ "from": "<Y head>", "verb": "of" or "kind of", "to": "<X modifier>",
  "name": "<X> <Y>" }
```

- Use `of` for noun-noun qualifications (`textbook of economics` → `economics
  textbook`).
- Use `kind of` for adjective-noun categorisations (`value kind of expected` →
  `expected value`).
- Use `and` to combine peers (`group and computer` → contrast name `group vs
  computers`).
- Use `per` when the resulting concept is a quotient (`expected value per cost`
  → `expected value to cost ratio`).

The triple's `name` is free-form and need not be grammatically derivable from
its `from`/`verb`/`to` — it just has to be unique.

## Formulas

```json
{
  "name": "definition of pH",
  "expression": "\"pH\" = - log(\"hydrogen ion concentration\")",
  "latex": "pH = -\\log[\\mathrm{H}^+]",
  "assigned_word": "hydrogen ion concentration"
}
```

### Assign the formula to its *input* phrase(s), never its result

A formula is linked to phrases via `assigned_word` (single) or the `assigned`
array (several). Assign it to the **phrase(s) it uses as input**, **never** the
result it computes — the assignment makes the formula *applicable*: a formula
is offered wherever an assigned phrase has a value. `"pH" = - log("hydrogen
ion concentration")` is assigned to `hydrogen ion concentration` (input), so
pH can be computed wherever a hydrogen-ion concentration is known. Assigning
to `pH` (result) would be wrong — you'd already need the pH to find the
formula.

- **Right (single)**: `{"assigned_word": "hydrogen ion concentration"}`
- **Right (several)**: `{"assigned": ["pH", "pOH"]}` for `"pH" + "pOH" = 14`
- **Wrong**: assigning either formula to its result

If a formula's inputs all live elsewhere — e.g. `"molar mass" = "mass" /
"mole"` whose inputs belong to the units/physics data and must **not** be
redefined — either define the formula in the file owning its inputs, or leave
it **unassigned**.

### Assign the formula to the most *parent* phrase

Assign a formula at the highest level of the phrase hierarchy where it
applies, never to every child: `bid-ask spread absolut` works for every
currency, so it is assigned once to the phrase `currency` (currencies.json) —
not to `CHF`, `EUR`, ... . The assignment is inherited by all children, e.g.
by every phrase that `is a` currency.

The same formula may apply to further hierarchies that are owned by other
import files — `bid-ask spread absolut` also applies to `securities`, which
is added by the instruments import file. Such assignments are **cumulative**:
an import *adds* its parent phrase to the formula's existing assignments and
never replaces assignments made by other files, unless the import states
otherwise.

### The `percent` measure auto-scales

A formula whose result is assigned to `percent` and that computes a ratio
(e.g. `( "this" - "prior" ) / "prior"`) is shown as a percentage **without** an
explicit `* 100`. Do not "fix" such formulas by adding `* 100` — the missing
factor is intentional; scaling happens via the `percent` measure, not the
expression.

### Formula name uniqueness across types

A formula's `name` must not equal an existing triple's `name`. The system
allows a symbol to alias multiple *phrases* (word/triple) by design (see
*Intentional symbol aliasing*), but a formula-vs-triple collision is a genuine
unintended collision worth flagging. Convention: suffix the formula name with
` formula` when it would otherwise equal a triple it produces (e.g. triple
`disinformation dam expected value` + formula
`disinformation dam expected value formula`).

## Sources

```json
{
  "name": "Berns paper",
  "description": "Berns GS, ... (2005). Biological Psychiatry 58:245-253",
  "url":  "https://doi.org/..."
}
```

Sources are referenced by name in `value.source` and must be defined in the
same file as the values that use them. Sources live in their own namespace —
a source whose name equals a triple's name is fine.

## Values

```json
{
  "words":  [ "phrase", "qualifier 1", "qualifier 2" ],
  "number": "20",
  "source": "..."
}
```

### Qualify a value as specifically as the data allows — prefer triples from single words

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

### Word vs triple in a value — does the order carry meaning?

A value's `words` array is an **unordered set**: the import cannot tell `["A", "B"]`
from `["B", "A"]`. So when two phrases qualify a value, ask whether their order
could change the meaning:

- **If the order could be relevant, use a triple** instead of two flat words.
  The triple fixes the direction in its `from`/`verb`/`to`, and the value
  references the single triple name — so the meaning survives.
- **If the order is never relevant, use two (or more) flat words** and do *not*
  invent a triple. A triple costs a name and a database row; spend it only where
  direction earns it. Over-triplifying buries the reusable single-word atoms, so
  when in doubt that the order matters, leave it as separate words.

**Order could matter → triple:**

- A ratio — `revenue / cost` ≠ `cost / revenue`: tag the value with
  `{"from": "revenue", "verb": "per", "to": "cost", "name": "revenue per cost"}`,
  not the two bare words.
- A directed flow — exports *from* Switzerland *to* Germany differ from the
  reverse: `{"from": "Switzerland", "verb": "to", "to": "Germany", "name": "Switzerland to Germany"}`.
- A signed change over a period — a value measured *from* 2023 *to* 2024 flips
  sign if the years swap: `{"from": "2023", "verb": "to", "to": "2024", "name": "2023 to 2024"}`.

**Order is irrelevant → flat words:**

- `{"words": ["Vestas", "revenue", "2024"], "number": "..."}` — "Vestas's revenue
  in 2024" reads the same whatever the qualifier order; no triple needed.
- `{"words": ["City of Zurich", "inhabitant", "2025"], "number": "443037"}` — the
  entity, measure and period have no direction among themselves.
- `{"words": ["Switzerland", "population", "2023"], "number": "..."}` — a plain
  fact tagged by entity, measure and period.

### `"share": "public"` is the default and must be omitted

Only add `share` when it differs from `public`.

## Components

```json
{
  "name":           "system sub title values",
  "type":           "system_sub_title",
  "code_id":        "system_sub_title_values",
  "ui_msg_code_id": "system_sub_title_values"
}
```

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

## `import_mapper` reads only from the per-file `$dto`

`import_mapper` (and helpers like `import_map_names`) must **only** map the
object from the JSON and resolve references from the passed-in `data_object`
(the per-file import cache, `$dto`). It must never read from the database. If a
referenced phrase/word/triple/source is not in the `$dto`, **add a translatable
error to `$msg`** (e.g. `msg_id::IMPORT_FORMULA_ASSIGN_PHRASE_MISSING`) — do
**not** load it from the DB and do **not** create a placeholder.

This keeps import deterministic and each file self-consistent: everything a
file references must be present in that file's import (re-declared name-only
if a base word), and the mapper fails loudly otherwise. Any DB access —
loading rows, cross-file lookups, merging — belongs to the **save** step, not
`import_mapper`.

## Related

- `docs/llm/architecture.md` — domain noun definitions (*word*, *triple*,
  *phrase*, *term*, *value*, …).
- `docs/llm/constants.md` — `config.yaml` keys are at most two
  space-separated words (parallel rule for the YAML config loader).
- `docs/llm/testing.md` — tests that depend on JSON import fixtures must
  recreate the artifact from a shared constant.