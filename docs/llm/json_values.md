# JSON values and calc-validation

Rules for the `values` and the `calc-validation` entries of a zukunft.com import file: how a
value names the number it is about, how its quality, status and confidence are stated, and how
an expected formula result is checked at import time. The envelope, the words, triples,
formulas and sources of an import file are in `docs/llm/json_structure.md`; a section named in
*italics* that is not in this file is there.

The always-on one-line rules are indexed in `docs/llm/coding.md` under "Domain &
import rules" and link back here for detail.

## Values

```json
{
  "words":  [ "phrase", "qualifier 1", "qualifier 2" ],
  "number": "20",
  "source": "..."
}
```

An optional `"name"` is the name that a user has given to the phrase group of the value. It is
exported only if a user has set one and never repeats the name generated from the `words`.

### Different numbers for the same value in different files are a feature, not a problem

Two files may state a different number for the same phrase group, e.g. one estimate of the
world htp in `problem_global_warming.json` and another in a later file, **if the files belong
to different users**: the target of zukunft.com is to show transparently which options and
results are possible, so each user's number is kept as that user's value and the reader sees
where the estimates differ and why. Do not "correct" a number in one file to match another
file, and do not remove a file's number because another file has one; state the reasoning in
the `description` instead. Only within one user is a phrase group one value, so two files of
the **same** user that state the same group are the duplication that the *home file* rule
avoids: the later import overwrites the earlier number silently.

### A study that contradicts an assumed value is a second value with a reason phrase

If a study finds a number that contradicts an `assumed` value of the same file, keep the
assumed value unchanged and add the study number as a second value: the phrases of the
assumed value without `assumed`, plus **one phrase that says why the numbers differ**,
usually the scope, the method or the scenario of the study, e.g. `Germany` for a measurement
of German companies or `safely managed` for a cost of a higher service level. The second
value names the study as its `source`, and its `description` states what the study measured
and ends with what it contradicts, e.g. `…; contradicts the assumed share, which expects
part of the profit to move to wages (Jäger, Schoefer & Heining 2021)`.

```json
{ "words": ["codetermination (wealth concentration solution)", "reduction potential", "percent", "assumed"], "number": "0.06" },
{ "words": ["codetermination (wealth concentration solution)", "reduction potential", "percent", "Germany"], "number": "0",
  "source": "Jäger, Schoefer & Heining 2021" }
```

- The reason phrase keeps the two values apart: without it, both would be one phrase group,
  and the import would overwrite the assumption with the study number.
- A formula picks only a value whose phrases are all in its context. So the results and the
  calc-validation of the assumed value stay unchanged, and a result for the context with the
  reason phrase shows what the study number would mean.
- Add the value only if the study number has the **same meaning and unit** as the assumed
  value, e.g. "no effect" is 0 for a share and a yearly cost times the years is a total cost.
  If the mapping needs your own judgement, e.g. a change of the Gini or a probability of a
  vote, or the study only shows the opposite direction, add no value: write in the reply
  which study contradicts which number, and leave the decision to the user.
- Never change or remove the assumed value because of the study; the reader compares both.

### Qualify a value as specifically as the data allows — build from single words

A value's `words` array is the phrase group the number belongs to. **Always
describe a value as specifically as the data allows**: add as many qualifying
phrases as possible. A bare `{"words": ["price"], "number": "20"}` claims *the*
price is 20 — meaningless. Add the context (dataset, entity, period, source).

Express every qualifier through **single words**, and combine them into a triple
only where the combination itself carries meaning — the entity, the order or the
verb (see *Word vs triple in a value* below):

- Define the individual words (`economics`, `textbook`, `example`).
- Combine them with **existing verbs** into triples, building up. Because each
  triple's `to`/`from` is itself a named triple, give it an explicit `name`:
  - `{"from": "textbook", "verb": "of", "to": "economics", "name": "economics textbook"}`
  - `{"from": "example", "verb": "of", "to": "economics textbook", "name": "economics textbook example"}`
- Reference the resulting triple by name in the value's `words` array — here the
  `of` chain is what says the example is *from* the textbook and the textbook is
  *about* economics, which the three loose words would not.

- **Vague**: `{"words": ["price"], "number": "20", "share": "public", "source": "economics textbook example"}`
- **Specific**: `{"words": ["price", "economics textbook example"], "number": "20", "source": "economics textbook example"}`

### Name the entity globally, not just locally

"As specific as the data allows" is judged from the **global** point of view, not
only within the file: the `words` group must pin the number down to **one** real
quantity in the whole graph. The qualifier that usually decides this is the
**entity**, and a generic word is not specific enough.

- **Wrong** — generic entity word, group is not globally unique:

```json
{ "words": ["canton", "GDP", "2022", "CHF"], "number": "159800000000" }
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
{ "words": ["Zurich (canton)", "GDP", "2022", "CHF"], "number": "159800000000" }
```

Now the group identifies one number globally. The shared atoms (`canton`, `GDP`,
`2022`, `CHF`) stay reusable across every entity; only the entity phrase carries
the global identity. Apply it to every value —
`Zurich (city)` not bare `city`, `Vestas` not bare `company`. The entity triple
doubles as the disambiguation of the ambiguous name (`Zurich` the canton vs the
city — see *Disambiguate an ambiguous word with qualifier triples*).

### Word vs triple in a value — single words unless the order or the verb carries meaning

**Default to single words in a value's `words` array; reach for a triple only
when the combination itself carries information that the flat words would lose.**
That is the case for exactly two things: the **order** of the combined phrases
and the **verb** that combines them.

The reason is what the user can do with the result. Every phrase of the group is
its own link, so

```json
{ "words": ["primary", "school", "Zurich", "canton", "2024"], "number": "252" }
```

gives the reader of "number of primary schools in Zurich (canton)" five entry
points instead of one: from `school` they reach every other school figure, from
`Zurich` the city and the canton alike, from `primary` every other primary
measure. And nothing is lost, because the triples built from those words
(`primary school`, `Zurich (canton)`) hang on the words themselves — so the
single word is the *shorter* path to the triple, while a triple in the value is a
dead end that leads back to its two parts and no further.

A triple also costs a name and a database row. Spend it only where the order or
the verb earns it; when in doubt, leave the phrases separate.

**The order carries meaning → triple.** A value's `words` array is an **unordered
set**: the import cannot tell `["A", "B"]` from `["B", "A"]`. So whenever
swapping two phrases would name a different quantity, the direction has to live
in a triple's `from`/`to`. `forum_outreach_estimate.json` holds both directions
of the same pair, and they are reciprocals:

```json
{ "name": "qualified collaborator per hour", "from": "qualified collaborator", "verb": "per", "to": "hour" },
{ "name": "hour per qualified collaborator", "from": "hour", "verb": "per", "to": "qualified collaborator" }
```

As flat words both would be `["qualified collaborator", "hour"]` — one group for
two different numbers. The same holds for any ratio (`revenue per cost`), any
directed flow (exports *from* Switzerland *to* Germany) and any signed change
over a period (*from* 2023 *to* 2024 flips sign if the years swap).

**A scale or unit of the divisor → triple.** A flat scaling word (`million`,
`billion`, `trillion`) and a flat unit (`EUR`, `USD`) describe the **number
itself**, and the calculation scales the whole value by the scaling word. So a
ratio whose divisor carries the scale or the unit must build the divisor as its
own triple and the quotient with `per` on top of it. The effort factor of
`start_page/problem_black_box_ai.json` is *"the person years needed to set up a
solution per trillion EUR of annual damage"*:

- **Wrong** — reads as a number of person years **in trillion EUR**:

```json
{ "words": ["black-box AI", "person year", "trillion", "EUR", "factor", "assumed"], "number": "90.909091" }
```

- **Right** — the scale and the currency stay with the divisor:

```json
{ "name": "trillion EUR", "from": "trillion", "verb": "of", "to": "EUR" },
{ "from": "person year", "verb": "per", "to": "trillion EUR" }

{ "words": ["black-box AI", "person year per trillion EUR", "factor", "assumed"], "number": "90.909091" }
```

A formula names the operand the same way (`"person year per trillion EUR","factor"`)
and a calc-validation `context` lists the triple instead of `person year`. A
scale of the **whole** ratio stays a flat word: `"trillion","EUR per person year"`
is the reward ratio in trillion EUR per person year.

**A qualifier of one phrase of the group → triple.** The flat words of a value
qualify the whole number and none of them says which other word it belongs to.
So `["potential", "loss", "trillion", "EUR"]` does not tell that the loss is the
potential one: `potential` could as well qualify `EUR` or the whole group. The
triple `potential loss` (`loss` `kind of` `potential`, defined in
`solution_prio.json`) links the qualifier to the phrase it qualifies, so use it:

- **Wrong** — `potential` floats in the group:

```json
{ "words": ["citizen participation", "potential", "loss", "trillion", "EUR", "assumed"] }
```

- **Right** — the loss is the potential one:

```json
{ "words": ["citizen participation", "potential loss", "trillion", "EUR", "assumed"] }
```

The formula names the operand the same way (`"potential loss","trillion","EUR"`),
and so does a calc-validation `context`. A file that uses the triple repeats its
`from`/`verb`/`to` from the home file (see *Self-consistency*). The same holds for
`potential gain`.

**The verb carries meaning → triple.** Two phrases can be linked by more than one
relation, and then the verb alone decides which number is meant. `zh_city.json`
uses the same `from` and the same `to` twice and only changes the verb:

```json
{ "name": "inbound commuters to Zurich (city)",     "from": "commuter", "verb": "in", "to": "Zurich (city)" },
{ "name": "outbound commuters from Zurich (city)",  "from": "commuter", "verb": "of", "to": "Zurich (city)" }
```

The 2023 values are 235'000 and 58'000. Flattened to
`["commuter", "Zurich (city)", "2023"]` both would land in one group, and the
question the number answers — who commutes *into* the city versus *out of* it —
would be gone.

**Neither → flat words:**

- `{"words": ["Vestas", "revenue", "2024"], "number": "..."}` — "Vestas's revenue
  in 2024" reads the same whatever the qualifier order, and no verb links the
  three; no triple needed.
- `{"words": ["Zurich", "city", "inhabitant", "2025"], "number": "443037"}` — the
  entity, measure and period have no direction among themselves.
- `{"words": ["Switzerland", "population", "2023"], "number": "..."}` — a plain
  fact tagged by entity, measure and period.

The one qualifier that stays a triple either way is the **entity**, because it
must be globally unique (see *Name the entity globally, not just locally*):
`Zurich (canton)` is a phrase in its own right, not the accident of two words
meeting in a group.

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

### A value or result has only one phrase of the type measure unit, percent or factor

A phrase of the type `measure_unit` (`EUR`, `km2`, `gram per kWh`), `percent`
(`percent`) or `factor` (`factor`) says **how the number is stated**: the
frontend shows it as the unit or as the symbol behind the number (`%`, `x`) and
heads a table column with it. A number is stated in one way only, so the `words`
of a value, of a `results` and of a `calc-validation` entry carry **at most one**
phrase of these three types. A scaling word (`million`, `billion`) is no such
type, so `["trillion", "EUR"]` stays as it is.

If more than one of these types is needed to describe the number, the
combination is **one triple** that carries the one type, like the unit
`gram per kWh` of `use_cases/pv_switzerland_co2.json`:

```json
{ "from": "gram", "verb": "per", "to": "kWh", "type": "measure_unit" }
```

**A percent is already a factor.** `start_page/problem_disinformation.json`
states *"how many times bigger the long run loss is than the loss per year"*:

- **Wrong** — `percent` and `factor`, so the page shows the number with two
  symbols (`%` and `x`):

```json
{ "words": ["disinformation", "percent", "GDP", "factor", "assumed"], "number": "15.7233" }
```

- **Right** — `percent` alone says that the number is a ratio (15.7233 is shown
  as 1'572.33 %, see *A `percent` value is stored as the decimal ratio*); what
  the ratio is about is named by phrases without one of the three types:

```json
{ "words": ["disinformation", "loss", "long run growth", "percent", "assumed"], "number": "15.7233" }
```

**A ratio of two measures is one unit.** Two flat measures also hide the
direction (see *The order carries meaning → triple*):

- **Wrong** — `tonne` and `USD` are both a measure:

```json
{ "words": ["carbon price", "tonne", "USD"], "number": "19" }
```

- **Right** — the unit is the triple:

```json
{ "from": "USD", "verb": "per", "to": "tonne", "type": "measure_unit" }

{ "words": ["carbon price", "USD per tonne"], "number": "19" }
```

**A number with a unit is no factor.** The conversion ratio of
`start_page/fermi_internet_pro_contra.json` is an amount of money per DALY:

- **Wrong** — two measures and `factor`:

```json
{ "words": ["conversion ratio", "factor", "CHF", "DALY", "assumed"], "number": "50000" }
```

- **Right**:

```json
{ "from": "CHF", "verb": "per", "to": "DALY", "type": "measure_unit" }

{ "words": ["conversion ratio", "CHF per DALY", "assumed"], "number": "50000" }
```

**What is measured is no measure.** A share of the GDP is stated as a
percentage; `GDP` only says what the share refers to:

- **Wrong** — the word `GDP` typed `measure_unit` in `base_data/GDP.json`, so every
  value with `percent` and `GDP` is a value with two number types:

```json
{ "name": "GDP", "type": "measure_unit" }

{ "words": ["global warming", "potential loss", "percent", "GDP", "assumed"], "number": "-0.32" }
```

- **Right** — `GDP` is a quantity, so it is no number type (see *Words*); the
  value stays as it is, `percent` is its one number type and a GDP amount carries
  its unit as a phrase of its own (`["world", "GDP", "trillion", "EUR", "2024"]`).
  Because `GDP` describes the number and not the row of a value table, it is
  typed `measure_non_unit`, which heads no row or column but is named in the
  column header behind the unit (`loss in percent GDP`):

```json
{ "name": "GDP", "type": "measure_non_unit" }

{ "words": ["global warming", "potential loss", "percent", "GDP", "assumed"], "number": "-0.32" }
```

A formula names the operand and the result the same way, and the `context` of a
`calc-validation` entry lists a unit triple instead of its parts.

### `"share": "public"` is the default and must be omitted

Only add `share` when it differs from `public`.

### `measured value` is the default and must never be added

A value is assumed to be measured: it comes from an observation or from the
source named in its `source` field. So a `measured value` qualifier says nothing
that is not already implied, while it lengthens every phrase group, creates a
word or triple that must be re-declared in every file that borrows it, and makes
two otherwise identical groups look different.

Only the **deviation** from the default is worth recording. Keep the word
`assumed` for a number that is *not* measured — a Fermi input, a policy
parameter, an order-of-magnitude guess — so a reader can tell at a glance which
numbers carry evidence and which do not. The qualifier is the single word, not
an `assumed value` triple: the phrases of a value are the words of that value,
so the `... value` part only repeats what the group already is.

- **Right**: `{"words": ["carbon leakage rate", "global warming", "percent"], "number": "14", "source": "Branger & Quirion (2014b)"}`
- **Right**: `{"words": ["disinformation", "public discourse", "assumed", "percent"], "number": "20"}`
- **Wrong** — the qualifier only repeats the default:
  `{"words": ["carbon leakage rate", "global warming", "measured value", "percent"], "number": "14", "source": "..."}`

The same applies to the `context` of a `calc-validation` entry and to the word
or triple itself: a file must not define a `measured value` word, nor a
`value kind of measured` / `value must be one of measured` triple.

A measured number without a `source` is a smell — either name the source or,
if it really is an assumption, mark it `assumed`. A `modelled` or
`calculated` value and a `field limit down`, `field limit up` or `field step`
of an input are no measurement either and need no source; a chosen point of a
sensitivity grid is an own choice and is marked `assumed`.

### A calculated value inherits the lowest quality of its inputs

The words of the phrase type `value_quality` (home file `solution_prio.json`)
are grouped by an `is a` triple into four groups, from the weakest to the
strongest evidence:

| group        | e.g.                                                         |
|--------------|--------------------------------------------------------------|
| `conjecture` | `assumed`, `expert estimate`, `extrapolated`, `modelled`, `calculated` |
| `report`     | `self-reported`, `single measurement`, `official statistics`, `audited` |
| `science`    | `preprint`, `randomized controlled trial`, `meta-analysis`   |
| `convention` | `defined`, `legal`, `proven`                                 |

A formula cannot add evidence, so its result has the **lowest** quality of all
its inputs:

- `defined` × `audited` → the result is a `report` (`audited`)
- `audited` × `assumed` → the result is a `conjecture` (`assumed`)
- `defined` × `legal` → the result stays a `convention`

`calculated` (and `modelled`) only says *how* the number came about; it is never
ranked on its own, because that would turn every result into a conjecture even
when all inputs are conventions. Never lift a result above its weakest input,
e.g. by tagging a formula result `audited` because the main input was audited.

### A value status is not a rung of the quality ladder

The quality says how a number has been *found*; the status, a word of the
phrase type `value_status`, says what *happened* to it afterwards:

- `disputed` — challenged by a published objection that is not yet resolved
- `retracted` — withdrawn by the authors or the publisher
- `superseded` — replaced by a corrected or a newer number of the same source
- `outdated` — right when it was found, but the measured thing has changed

A peer-reviewed number can still be retracted, so a status never replaces or
lowers the quality word: a value keeps both, e.g.
`{"words": ["...", "peer reviewed by quality journal", "retracted", "percent"], ...}`.
A value without a status word is valid. A calculated value carries every status
of its inputs, because one retracted input makes the result unusable whatever
the quality of the other inputs is.

### The confidence of a value quality is a default value, never a fixed number

Each value quality has a default confidence, the probability from 0 to 1 that
the number is right, stored as a normal value with a description of the reason
(`solution_prio.json`):

- `{"words": ["assumed", "confidence", "percent"], "number": "0.3", "description": "..."}`
- `{"words": ["double-blind randomized controlled trial", "confidence", "percent", "assumed"], "number": "0.9", "description": "..."}`

The defaults rise along the ladder (e.g. `assumed` 0.3, `official statistics`
0.8, `double-blind randomized controlled trial` 0.9, `defined` 1). The number
itself is a judgement, so it is marked `assumed` like every other number without
a source; the `assumed` row needs no second `assumed`.

A value and not a const in the code, because the default must stay overridable:

- a **user** overwrites the default like any other value, e.g. to trust
  `self-reported` company figures less;
- a **single value** carries its own confidence, e.g.
  `["<claim words>", "confidence", "percent"]`, which wins over the default of
  its quality, because a small double-blind trial can deserve less confidence
  than a large observational cohort.

`calculated` has no default: a result takes the confidence of its weakest input,
like its quality (see above). A value status never lowers the confidence; a
`retracted` number is unusable whatever its confidence is.

## Calc-validation

Optional. A list of *expected* formula results: each entry is **recomputed** from
the file's own values and formulas and compared, so a broken formula or a
mistyped input is caught at import time. Same shape as a stored `result`, and
additionally routed through `validate_results` — a mismatch is reported as a
failed validation and stops the import, never saved as a value.

An operand is looked up in the `values` of the file first and, if the file states
no value for it, in the entries of `calc-validation` that have **already been
reproduced**. Entries are checked in file order, so a file can calculate a target
value first and its probability range from that target afterwards. A result that
did not reproduce is never an operand, else a wrong number would confirm the next
one instead of being reported.

The database is never asked for an operand, so a chain that needs the numbers of
another file belongs into that file: the climate solutions are calculated in
`problem_global_warming.json`, because their gain uses the welfare loss share of
the global warming problem (see *Self-consistency*).

A checked entry is a result like any other, so it is **also stored** in the
results table with its phrases, its number and the formula that calculated it.
Its `context` is always stored as the source group: a context of more than four
phrases does not fit the bigint `source_group_id` column of `results_prime` and
`results_main`, so such a result is saved in `results` (up to 16 context phrases)
or `results_big` with the text key of its phrases (see `result::table_type`).

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
  values carry (measure, period, currency, …), not just the phrase the
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
