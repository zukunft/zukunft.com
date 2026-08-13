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
  "values":    [ ... ],
  "calc-validation": [ ... ],
  "components": [ ... ],
  "views":      [ ... ],
  "view-validation": [ ... ]
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

### The home file is the one imported **first**, not the one that sounds canonical

Which file owns a phrase is decided by the **import order**, because the first
import creates the row and every later one merges into it (system imports run
`no_upd`, so a later file cannot overwrite a filled field anyway). Read the order
out of `cfg/const/files.php`:

1. `SYSTEM_DATA_FILES` — `system_views`, `sources`, `units`, `scaling`,
   `time_definition`, `base_phrases`, `base_views`, **`solution_prio`**,
   `country`, `currency`, `company`, …
2. `BASE_DATA_FILES` — `math`, `physics`, `chemistry`, `economics`, … which the
   const block itself labels *"loaded at the end not to change the id of objects
   used by the system tests"*
3. `BASE_DATA_PATH_FILES` — the per-topic demo files

So `solution_prio.json` is the home of `probability`, `score`, `deviation`,
`return`, `market`, `tax`, … even though `math.json` and `economics.json` look
like the natural place for them: those import **later** and therefore re-declare
name-only. Putting the definition in the late file instead is the mistake — the
early file then ships a bare name and the tooltip stays empty until the late file
happens to be imported.

The reason is id stability, not taxonomy. The phrases the unit tests pin by id
live in the early files, so their ids must not move; a domain file that is
expected to grow (`math`, `physics`, `economics`) is imported last precisely so
that adding to it shifts nothing the tests depend on (see
*Phrase id consts are re-baselined …* in `docs/llm/testing.md`). Keep it that
way: **add a new phrase to the latest file that can own it**, and only move a
definition earlier when a test needs to pin it.

A formula's `expression` may still reference phrases from earlier base files
(every physics formula uses units like `kg`, `metre`); those resolve at
calculation time, not via the per-file import cache.

A borrowed **triple** cannot be re-declared name-only — it needs `from`/`verb`/
`to` — so repeat the home definition **verbatim**. Triples merge by name on
import, so a divergent re-declaration means the last file imported silently wins
and the relation depends on the import order. `climate gas` is the warning
example: it is `gas can cause warming` in one file, `climate can cause warming`
in a second and `gas of climate` in a third.

### Split a large domain into a concept file and a list file

A word's full definition (description, refs, type) lives in exactly one **home
file**; everywhere else it is re-declared name-only. When a domain has many
instances, split that home in two:

- **concept file** (`currency.json`) — the genus word `currency`, its
  qualifiers, and its *behaviour*: the formulas that apply to every instance
  (`exchange rate`, `bid-ask spread`, `conversion`, `cross rate`).
- **list file** (`currencies.json`) — one entry per instance (`US dollar`,
  `Euro`, …), each with its own description, ISO code and symbol.

The concept file imports before the list file and before any consumer, so every
borrowed word resolves to its home. A word already owned by a general base file
(`million` → `scaling.json`, `year` → `time_definition.json`, `price` →
`economics.json`) is re-declared name-only in both — never re-described.

System-data imports run with `no_upd` (existing non-empty fields are kept; only
empty fields are filled), so a file that repeats an owned word with a
*divergent* description fails the import — a signal the definition has leaked
out of its home file.

### Don't overwrite the initial-setup default; change it under another user

The words, triples and formulas loaded during the initial system setup (the
`files::SYSTEM_DATA_FILES` sequence — `base_phrases`, `units`, `scaling`,
`time_definition`, `country`, `currency`, `currencies`, …) are the shared
**default** every new user starts from. Do not overwrite an existing non-empty
field (`description`, formula `expression`, `protection`, …) of one of these
without a good reason. `no_upd` enforces this for the system imports (only empty
fields are filled), but treat it as the rule everywhere: when a later file names
an already-defined phrase, either **repeat its existing value verbatim** or
re-declare it **name-only** — never supply a divergent one. E.g. `Euro` keeps
the description it received at initial setup; a file that references `Euro` again
repeats that same description (or is name-only), it does not give a different one.

If a value genuinely must change, do not edit the shared default in place.
Import the change **under a different user** so it becomes that user's personal
(sandbox) version, while the original stays the default description that new
users still see.

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

### Prefer a Wikipedia link over a free-text description

Reach for a Wikipedia `ref` before writing a `description`: a
`{ "name": "<article slug>", "type": "wikipedia" }` entry ties the word to a
shared, maintained definition instead of prose that drifts. Add the matching
Wikidata Q-id (`"type": "wikidata"`) too when you know it.

When the Wikipedia article does **not** really match the meaning you need, do not
force a near-miss description. Break the concept down into the single,
well-defined items it is composed of — each its own word with its own Wikipedia
ref — and combine them with triples, so the precise meaning emerges from
referenced parts. E.g. rather than an unreferenced word `jet fuel for short-haul`,
define `jet fuel` (wiki) and `short-haul flight` (wiki) and link them
`jet fuel` `used for` `short-haul flight`.

When something needed for a full match is missing from Wikipedia entirely, leave
a **todo** by starting a job from the JSON rather than inventing an unreferenced
description — the job records the gap so a maintained reference can be added (or
created) later, and the word keeps its best partial reference until then.

### Still carry the Wikipedia lead sentence as the `description`

Preferring the `ref` does **not** mean leaving `description` empty. Even when a
word or triple already has a Wikipedia (or Wikidata) `ref`, also fill its
`description` with the **first sentence — or two — of the Wikipedia lead**, so the
UI has tooltip text to show without a live fetch. The two work together: the
`ref` does not *replace* the description, it *keeps it fresh* — a refresh job
follows the Wikipedia link and re-pulls the lead, so the copied text is a cached
snapshot the link refreshes, not hand-written prose that drifts (which is what the
rule above warns against).

Copy the lead as **clean prose**, stripped down to the defining sentence(s):

- drop the inline wiki-links (keep the words, remove the markup),
- drop the bracketed **alternative names / native spellings**,
- drop the **pronunciation / IPA** parentheses,
- drop the bold restatement of the name itself (the `name` already carries it).

- **Wrong** — raw lead pasted in, with pronunciation, native name, link markup
  and the repeated name:

```json
{ "name": "Zurich",
  "description": "Zürich (/ˈzjʊərɪk/ ZURE-ik; Swiss Standard German: [ˈtsyːrɪç]) is the largest [[city]] in [[Switzerland]], in the north-central part of the country.",
  "refs": [ { "name": "Zurich", "type": "wikipedia" } ] }
```

- **Right** — cleaned defining sentence; the `ref` still drives the refresh:

```json
{ "name": "Zurich",
  "description": "the largest city in Switzerland, in the north-central part of the country.",
  "refs": [ { "name": "Zurich", "type": "wikipedia" } ] }
```

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

**Refs are the exception — they carry an external key, not an internal name.**
For a `ref` the identifying part is the **external key** (the Wikipedia article
slug or the Wikidata Q-id, with its `type` / `ref_type`), and that key follows
the **external source's** spelling and casing — Wikipedia and Wikidata
capitalise (`Zurich (City)`, `Canton_of_Zürich`, `Q72`). Never lower-case a
ref to match an internal phrase, and never assume a ref tracks the phrase name:
the lower-case-first and byte-for-byte rules above govern *phrase names only*;
in a `ref` the external key is what counts and the internal name is irrelevant,
so leave refs exactly as the external system spells them even when the phrase
they belong to is lower-cased.

### Intentional symbol / abbreviation aliasing

A short symbol may alias several phrases on purpose. `m` is the symbol for the
SI unit `metre` (in `units.json`) and the abbreviation for `million` (in
`scaling.json`). This is **by design**: the context (the other phrases in the
value's group) disambiguates. Do not force the symbol unique or rename one
side; only flag a genuine unintended collision (e.g. a formula name equal to a
triple name).

### `is symbol for` (formula replacer) vs `is alias of` (one merged phrase)

Both verbs link a short string to a phrase, but they mean different things — pick
the right one, because only `is symbol for` drives the formula display:

- **`is symbol for`** — the linked string is a *typographic symbol* that **stands
  in for** the phrase when a formula is rendered. The latex generator
  (`formula::update_latex`) replaces each expression phrase with its symbol, so
  `( "kg" * "metre" * "metre" ) / ( "second (time)" * "second (time)" )` is shown
  as `\frac{\text{kg} \cdot \text{m}^2}{\text{s}^2}`. The symbol stays a **separate
  phrase** with its own meaning; it is only substituted in formula output.
- **`is alias of`** — the two names are **the same phrase under another spelling**;
  the alias adds no new meaning and is *not* used as a formula replacer. Use it for
  alternative spellings / long forms, e.g. `U.S. dollar` / `US-Dollar` is alias of
  `US dollar`, `Nestle` is alias of `Nestlé`.

Rule of thumb: a one- or two-character typographic symbol (`s`, `m`, `kg`, `J`,
`π`, `$`, `€`) is `is symbol for`; a longer alternative spelling or full name is
`is alias of`. Multi-character unit notations that are not pure symbols (`mol`,
`m/s`) stay `is alias of`.

**Direction** — `is symbol for` always points **from the symbol to the phrase**
(`from` = the symbol, `to` = the full phrase), so the replacer can find the symbol
by the verb. Flip a triple that has the symbol on the `to` side.

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

### Allowed verbs

`verb` must be one of the predicates below (use the **name**; the `code_id` is the
stable internal key). This is the set defined in `src/main/resources/verbs.json`
at the moment:

| name | code_id | use |
|---|---|---|
| `is a` | `is` | child → parent category (Zurich is a canton) |
| `is part of` | `contains` | membership; the parts sum to the same total |
| `can be part of` | `can_be_part_of` | optional membership in both directions |
| `kind of` | `kind_of` | a sub-kind of a parent category |
| `must be one of` | `must_be_one_of` | disambiguate a word's several meanings |
| `of` | `of` | narrow a selection (population of humans) |
| `with` | `with` | same-by-same comparison |
| `has a` | `has` | assign a potential property |
| `uses` | `uses` | real use (a turbine uses wind) |
| `is used by` | `used_by` | passive form of `uses` |
| `used for` | `used_for` | intended purpose (fuel used for a jet) |
| `issue` | `issue` | issuer relation (a company issues a report) |
| `influences` | `influence` | a directed influence |
| `is an acronym for` | `acronym` | acronym expansion |
| `is alias of` | `alias` | alternative spelling of the same phrase, merged to one (no formula replacement) |
| `is symbol for` | `symbol` | the symbol shown in place of the phrase in formulas (`from`=symbol, `to`=phrase, e.g. USD for US dollar) |
| `name of` | `name_of` | a proper name of a category |
| `can` | `can` | assign a behavior (GDP can decline) |
| `can be` | `can_be` | a possible state |
| `can get` | `can_get` | a possible acquisition |
| `can have` | `can_have` | a possible possession |
| `can cause` | `can_cause` | a causal relation with a factor |
| `can use` | `can_use` | a possible use that creates a new result |
| `can be made of` | `can_be_made_of` | a possible material / composition |
| `can be packed in` | `can_be_packed_in` | a possible packaging option |
| `can be used as a differentiator for` | `can_contain` | table differentiator (row hidden when no value) |
| `per` | `per` | quotient unit (metre per second) |
| `times` | `times` | product unit (J⋅s for the Planck constant) |
| `and` | `and` | combine two phrases into one |
| `scaled by` | `scaled` | the usual scaling (kWh) |
| `in` | `in` | the measure unit |
| `on` | `on` | a subgroup (taxes on income) |
| `to` | `to` | a time range or assignment type |
| `between` | `between` | a range (lower–upper bound) |
| `by parts` | `by_parts` | a method on the parts (integration by parts) |
| `is selector for` | `selector` | group a selection list to shorten it |
| `is ranked by` | `rank` | the sort key for related objects |
| `is time jump for` | `time_jump` | the default time period |
| `is term jump for` | `term_jump` | the default term jump |
| `is measure type for` | `measure_type` | the default measure type |
| `is follower of` | `follow` | **time** ordering: sequence / successor (2025 is follower of 2024) |
| `is before` | `before` | **position** ordering: this phrase is placed before the other (a column shown before another column); reverse of `is after` |
| `is after` | `after` | **position** ordering: this phrase is placed after the other (a column shown after another column); reverse of `is before` |
| `term type needed` | `term_needed` | the formula needs the linked term type |
| `supports` | `supports` | argumentation: a finding backs a claim |
| `explains` | `explains` | argumentation: gives the reason for another phrase |
| `refines` | `refines` | argumentation: makes another phrase more precise |
| `is evidence for` | `evidence` | argumentation: an observation is empirical evidence for a claim |
| `is analogous to` | `analogous` | same structure without a causal link (symmetric) |
| `limits` | `limits` | bounds how far another phrase can go; reverse `is limited by` |
| `enables` | `enables` | makes another phrase possible without causing it |
| `reduces` | `reduces` | lowers the size of another phrase |
| `adds to` | `adds_to` | one amount is added to another to form the total |
| `competes with` | `competes` | two options exclude or weaken each other (symmetric) |
| `aims to trigger` | `aims_to_trigger` | the intended effect of an action |
| `lowers barrier for` | `lowers_barrier` | makes another phrase easier to reach |
| `is single attempt in` | `single_attempt` | one try of the attempts an ensemble rate is computed from |
| `expected from` | `expected_from` | the expected value of an outcome |
| `weighted by` | `weighted` | a quantity multiplied by a weight |
| `not set` | `not_set` | none — no verb selected |

### Adding a new verb

A verb not in the list above can be proposed in the **same object shape** as the
entries in `src/main/resources/verbs.json` — `name` + `code_id` + `description`,
plus the display forms `name_plural`, `name_reverse`, `name_plural_reverse` (leave
a form empty when the reverse reading is not used, as `per` and `to` do), and the
optional `formula_name` / `protection`:

```json
{
  "name": "is supplier of",
  "code_id": "supplier",
  "description": "...",
  "name_plural": "are suppliers of",
  "name_reverse": "is supplied by",
  "name_plural_reverse": "are supplied by"
}
```

**Approval process.** A proposed verb is private to the requesting user until an
**admin confirms** the request; only after that confirmation can other users use
the new verb. The confirmation also raises a **request to the developer** to add
fixed (coded) functionality for the verb — or at least to link it to an existing
verb's code functionality — so the new predicate behaves consistently across the
app rather than being a name-only relation.

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

### An unnamed triple is reachable only by its auto-generated name

The corollary of the rule above: a triple without a `name` **is** its
`<from> <verb> <to>` string, and nothing can reach it by a shorter phrase. So
when a value, a formula operand or another triple must reference the compound by
a display name, that name has to be on the triple.

- **Wrong** — the value can never resolve `m/s`, because the triple's phrase name
  is the whole sentence `m/s is symbol for metre per second`:

```json
{ "from": "m/s", "verb": "is symbol for", "to": "metre per second" }
{ "words": ["German North Sea Coast", "median", "Wind", "speed", "m/s"], "number": "6.9" }
```

- **Right** — the explicit `name` differs from the auto-form, so it is required:

```json
{ "name": "m/s", "from": "m/s", "verb": "is symbol for", "to": "metre per second" }
```

This failure is silent at authoring time and only surfaces on import as
*the phrase(s) … are not defined in the import file*, so check every value
phrase against the triple **names**, not against the triples.

The auto-form is not always `<from> <verb> <to>`: `triple::generate_name()` gives
the `is` verb its own shape, `<from> (<to>)`, so

```json
{ "from": "Swiss franc", "verb": "is a", "to": "currency" }
```

is stored as **`Swiss franc (currency)`**, not as `Swiss franc is a currency`.
That is the name anything referencing this triple has to use — including a test
const, which is why such a const is read out of `list.csv` rather than composed
from the parts.

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

### Name the formula for the most *general* concept, not the instance

A formula's `name` describes the **general operation** it performs, never the
specific phrase it happens to compute in one file. Name it `foreign count`,
`natural balance`, `welfare` — not `canton foreign count` or `city natural
balance`. A general name lets the *one* formula be reused for every phrase it is
assigned to (see *Assign the formula to the most parent phrase* below), so the
general name and the parent-phrase assignment go together: a single
`foreign count (formula)` assigned to `["city", "canton"]` serves both, where a
`city foreign count formula` plus a `canton foreign count formula` would
duplicate the same logic.

The result variable **inside** the `expression` may be general (resolved per
entity by the context, as below) or a specific instance (`"canton GDP per
capita" = …`) — what must stay general is the formula's own `name`:

- **Right** — general name, parent assignment:

```json
{ "name": "foreign count (formula)",
  "expression": "\"foreign nationals\" = \"population\" * \"foreign share\" / 100",
  "assigned": [ "city", "canton" ] }
```

- **Wrong** — instance-specific name duplicated per child phrase:

```json
{ "name": "city foreign count formula",   "expression": "...", "assigned": [ "city" ] },
{ "name": "canton foreign count formula", "expression": "...", "assigned": [ "canton" ] }
```

A single general formula can only address every entity when each operand has a
phrase the per-entity context resolves uniquely (here the genus word `population`
plus the `foreign share` triple — see *Naming a formula operand: triple vs flat atoms*). If
an operand has no such shared phrase (e.g. the area values are only keyed
`city area` / `canton area`), the validator cannot pick it per entity, and you
fall back to one specific formula per entity (`city density formula`,
`canton density formula`).

Append the ` formula` / ` (formula)` suffix only to break a clash with a word or
triple of the same general name (see *Formula name uniqueness across types*) —
never to dress an instance-specific name up as generic.

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

### A `percent` value is stored as the decimal ratio, never as the ×100 number

The same scaling applies to a stored value: a value qualified with `percent`
holds the **ratio**, and the `percent` measure turns it into a percentage for
the user. So a share of 18 % is `"number": "0.18"`, not `"18"`.

- **Right**: `{"words": ["share of population 65+", "Zurich (canton)", "2025", "percent"], "number": "0.18"}`
- **Wrong** — off by a factor of 100 as soon as the number is calculated with:
  `{"words": ["share of population 65+", "Zurich (canton)", "2025", "percent"], "number": "18"}`

This is not cosmetic. A ×100 value silently breaks every formula that consumes
it: `"profit" = "holders" * "switching willingness" * "market share" * "margin"`
gives the right answer with `0.02` and `0.05`, and a 10 000× too large one with
`2` and `5`. It also forces a compensating `/ 100` into the consumer and a
`* 100` into the producer, both of which the `percent` measure already does.

The decimal form covers the whole range: 0.5 % is `"0.005"`, 230 % is `"2.3"`,
and a `calc-validation` entry whose `words` contain `percent` states its
expected result the same way.

**Before storing, sanity-check the magnitude** — the two forms are easy to
confuse when the number is small. A housing vacancy rate of `0.06` is the ×100
form of 0.06 % (`"0.0006"`), while an investor share of `0.85` is already the
ratio for 85 %. Decide from the real-world quantity, not from the size of the
number.

### Period-over-period change: reuse the system `increase` formula

Do **not** write a bespoke "growth rate" / "year-over-year change" formula. The
base data ships the canonical one in `time_definition.json`:

```json
{ "name": "increase", "expression": "\"percent\" = ( \"this\" - \"prior\" ) / \"prior\"", "assigned_word": "year" }
```

`this` and `prior` are hardcoded **time-jump** formulas (`type: time_this` /
`time_prior`) that fetch the current and previous period's value through the time
dimension. To apply it to your measure, mirror `country.json`: re-declare the
time block (`this`, `next`, `prior`, the `Now` word, and `increase` with its
`assigned_word` set to your measure, e.g. `population`), mark each year
`{"type": "time"}`, link each `… is a year`, and chain them
`{"from": "2025", "verb": "is follower of", "to": "2024"}` so `prior` can step
back. Tag the year on the values (`["canton", "population", "2025", …]`); the
increase is then derived for every entity that has both a year and its
predecessor. The result goes to `percent`, so there is **no `* 100`** (above).

`increase` resolves `this`/`prior` only at calc time through the time dimension,
so it **cannot be reproduced by `calc-validation`** (which does literal value
lookup — see below). That is why no base file calc-validates `increase`: leave
the period-over-period result out of your `calc-validation` block.

### Formula name uniqueness across types

A formula's `name` must not equal an existing triple's `name`. The system
allows a symbol to alias multiple *phrases* (word/triple) by design (see
*Intentional symbol aliasing*), but a formula-vs-triple collision is a genuine
unintended collision worth flagging. Convention: suffix the formula name with
` formula` when it would otherwise equal a triple it produces (e.g. triple
`disinformation dam expected value` + formula
`disinformation dam expected value formula`).

### A word and a triple must never share a name

Word and triple live in **one** phrase namespace. A file that declares the word
`km` *and* a triple named `km` creates two phrases with the same name, so every
value naming `km` becomes ambiguous. This is the most common defect found when
splitting a multi-word word into a triple: the compound becomes a triple while
the old word declaration is left behind.

The idiom for a symbol that must keep both is to qualify the triple name, as
`units.json` does — the word stays `l`, `km`, `t`, and the relation triple is
named `l (unit symbol)`, `km (unit symbol)`, `t (unit symbol)`:

```json
{ "name": "km" }
{ "name": "km (unit symbol)", "from": "km", "verb": "is symbol for", "to": "kilometre" }
```

The same applies across files: a phrase that is a triple in its home file must
not be re-declared as a word in a consumer file.

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

### A source name never appears in a value's `words`

Because the namespaces are separate, a source name put into `words` is not a
phrase and cannot resolve. Provenance goes in `source`; the phrase group names
the **claim the number is about**.

- **Wrong**: `{"words": ["confidence", "Berns paper", "assumed value"], "number": "0.7"}`
- **Right**: `{"words": ["confidence", "model harm weight", "assumed value"], "number": "0.7", "source": "Berns paper"}`

If two values would then share one phrase group, that is the signal that the
group is under-qualified — name the distinct claim, do not fall back to the
source name.

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

### Name the entity globally, not just locally

"As specific as the data allows" is judged from the **global** point of view, not
only within the file: the `words` group must pin the number down to **one** real
quantity in the whole graph. The qualifier that usually decides this is the
**entity**, and a generic word is not specific enough.

- **Wrong** — generic entity word, group is not globally unique:

```json
{ "words": ["canton", "GDP", "2022", "CHF", "measured value"], "number": "159800000000" }
```

There are 26 cantons, so this never says *which* canton's GDP it is — another
canton's 2022 GDP would map to an indistinguishable group, and the row reads as
no unique fact.

- **Right** — name the actual entity as its own (disambiguated) triple, then
  reference it:

```json
{ "name": "Zurich (canton)", "from": "Zurich", "verb": "is a", "to": "canton" }
```

```json
{ "words": ["Zurich (canton)", "GDP", "2022", "CHF", "measured value"], "number": "159800000000" }
```

Now the group identifies one number globally. The shared atoms (`canton`, `GDP`,
`2022`, `CHF`, `measured value`) stay reusable across every entity; only the
entity phrase carries the global identity. Apply it to every value —
`Zurich (city)` not bare `city`, `Vestas` not bare `company`. The entity triple
doubles as the disambiguation of the ambiguous name (`Zurich` the canton vs the
city — see *Disambiguate an ambiguous word with qualifier triples*).

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
- `{"words": ["city of Zurich", "inhabitant", "2025"], "number": "443037"}` — the
  entity, measure and period have no direction among themselves.
- `{"words": ["Switzerland", "population", "2023"], "number": "..."}` — a plain
  fact tagged by entity, measure and period.

### Naming a formula operand: triple vs flat atoms

The order test above decides triple-vs-flat for a value that is only ever **read
back as data**. A value a **formula** consumes adds a second consideration: an
`expression` (and `assigned`) references each input by a *single phrase name* that
the one calc-validation `context` must resolve to exactly one value. Two shapes
satisfy that, and the order is irrelevant to both:

- **Triple operand** (`city population`): self-documenting —
  `"city population" / "canton population"` plainly divides two populations — and
  it resolves unambiguously whatever other values exist. Cost: a named triple and
  a row, with the single-word atoms locked inside it.
- **Flat atoms + context**: tag the value with single words
  (`["city", "population", "2025", …]`) and let the expression name a
  distinguishing atom — the entity (`"city" / "canton"`) or the genus
  (`"population" * "foreign share"`) — with the per-entity context picking the
  right value. Keeps the atoms reusable (the project's default leaning — see *Word
  vs triple in a value*), at the cost of an expression that only reads correctly
  with its context in hand and stays correct only while the context excludes every
  other `city` / … value.

It is a genuine trade-off, not a rule: weigh **self-documentation** against
**atomic reuse** per case. `zurich.json` (a base-data file, where reusable atoms
matter most) takes the flat route — `share of canton` is `"city" / "canton"`, and
one general `foreign count = "population" * "foreign share" / 100` serves both
entities because the shared atom `population` and the `foreign share` triple
resolve per entity by context. A file that prizes legible formulas over atom reuse
would instead keep `city population` / `canton population` triples and divide
those. Either way the single calc-validation `context` must uniquely resolve every
operand (see *Calc-validation*), and two same-measure operands that share *one*
context can only be told apart by distinct names (entity atoms or triples), never
by a bare genus word alone.

### `"share": "public"` is the default and must be omitted

Only add `share` when it differs from `public`.

### `measured value` is the default and must never be added

A value is assumed to be measured: it comes from an observation or from the
source named in its `source` field. So a `measured value` qualifier says nothing
that is not already implied, while it lengthens every phrase group, creates a
word or triple that must be re-declared in every file that borrows it, and makes
two otherwise identical groups look different.

Only the **deviation** from the default is worth recording. Keep `assumed value`
for a number that is *not* measured — a Fermi input, a policy parameter, an
order-of-magnitude guess — so a reader can tell at a glance which numbers carry
evidence and which do not.

- **Right**: `{"words": ["carbon leakage rate", "global warming", "percent"], "number": "14", "source": "Branger & Quirion (2014b)"}`
- **Right**: `{"words": ["disinformation", "public discourse", "assumed value", "percent"], "number": "20"}`
- **Wrong** — the qualifier only repeats the default:
  `{"words": ["carbon leakage rate", "global warming", "measured value", "percent"], "number": "14", "source": "..."}`

The same applies to the `context` of a `calc-validation` entry and to the word
or triple itself: a file must not define a `measured value` word, nor a
`value kind of measured` / `value must be one of measured` triple.

A measured number without a `source` is a smell — either name the source or,
if it really is an assumption, mark it `assumed value`.

## Calc-validation

Optional. A list of *expected* formula results: instead of being stored, each
entry is **recomputed** from the file's own values and formulas and compared, so
a broken formula or a mistyped input is caught at import time. Same shape as a
stored `result`, but routed through `validate_results` — a mismatch is reported
as a failed validation, never saved as a value.

```json
{
  "context": [ "status quo harm weight", "exposure duration", "DALY", "year", "adult" ],
  "formula": "status quo harm per person formula",
  "words":   [ "status quo harm per person", "DALY", "adult" ],
  "number":  "0.08",
  "note":    "optional human comment, ignored by the importer"
}
```

- `context` — the phrases that select the **input** values. The match is
  **literal, with no parent/child inheritance**: the validator takes each value
  whose phrase group is *wholly contained* in `context`, so `context` must be a
  **superset of every input value's full group** — include every qualifier the
  values carry (measure, period, `measured value`, …), not just the phrase the
  expression names. A value tagged `city population` is therefore *not* found by a
  bare `population` unless the value also carries the word `population`; and to
  tell two same-measure inputs apart, put the distinguishing word (`city` /
  `canton`) in the context so the wrong one is excluded.
- `formula` — the name of the formula (defined in this file's `formulas`) that
  computes the result.
- `words` — the phrases that identify the **result itself** (its group): what the
  `number` is about.
- `number` — the expected calculated value, as a string. The import recomputes it
  and compares **rounded to `data_object::CALC_VALIDATION_DECIMALS` decimals**
  (currently 2), so floating-point roundoff between the stored number and the
  recalculation is tolerated — you need not store full machine precision. A
  difference larger than that rounding is reported as a failed validation.
- `note` — optional, ignored by the importer.

### Keep every entry reproducible and order-independent

Each entry must be reproducible from the file's values and formulas alone — no
hidden state — and independent of the order the values are imported, so the check
stays stable. Encode an alternative scenario (e.g. an upper-bound sign-flip) as
its own value + `calc-validation` chain, never as prose in a `note`.

## View-validation, Components and Views

moved to [json_views.md](json_views.md) — the rules for defining views and
components in an import file and for pinning a page rendering after the import

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
