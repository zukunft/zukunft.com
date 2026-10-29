# The calculator editor json

The calculator editor reads, exports and shows one json file per calculator page.
A complete example that uses every part, including the proposed ones, at least once is
[calculator_editor_sample.json](../src/test/resources/web/calculator_editor/calculator_editor_sample.json):
a fictional page that compares the heating costs of a house.

The file is the answer of the pod to the `phraseData` request. The export button of
the editor writes the same format, including the unsaved changes. The import button
sends such a file, or any other zukunft.com import json, to the pod and then shows the
calculator of its page phrase.

## The idea

A calculator page is not programmed. It is described by **words**, by **triples**
that link two words with a verb (e.g. `heat pump` · `is a` · `heating system`) and
by **numbers**. The editor turns these triples into the page structure and back.

Two phrases matter most:

- the **page phrase** in `selection`, e.g. `sample house`: its values and results are
  the numbers that the page shows
- the **base phrase**, e.g. `house heating`: it holds the lists and the texts of the page,
  so that several page phrases can share one page layout

## The parts of the file

| part        | what it holds                                                                 |
|-------------|-------------------------------------------------------------------------------|
| header      | `pod`, `type`, `user`, `version`, `time` and a `description` of the file      |
| `selection` | the page phrase as the first entry, with its `name` and `description`         |
| `words`     | every word with its `name` and an optional `description`                      |
| `triples`   | the links between two phrases: `from`, `verb`, `to`, an optional `name`, `description` and `type` |
| `formulas`  | the calculations with `name`, `description` and `expression`                  |
| `sources`   | where a number comes from, with `name`, `description` and `url`               |
| `values`    | the numbers entered by a user: `words`, `number`, optional `source` and `description` |
| `results`   | the numbers calculated by the pod: `words`, `number`, `formula` and `context`  |

Objects that are already stored on a pod also have an `id`. A new object, e.g. one
added in the editor and exported before it is saved, has no id yet.

A number is found by its words: the value with the words `sample house`,
`heat pump efficiency` and `assumed` is the assumed efficiency of the sample house.

## How the triples build the page

### Lists

The page has up to five lists. Each list is a triple from the base phrase to a keyword:

| list           | triple                                                      |
|----------------|-------------------------------------------------------------|
| inputs         | `house heating` · `has a` · `calculator input`             |
| assumptions    | `house heating` · `has a` · `calculator assumption`        |
| result columns | `house heating` · `has a` · `calculator result column`     |
| row groups     | `house heating` · `has a` · `calculator row group`         |
| detail tables  | `house heating` · `has a` · `calculator detail table`      |

An **item** of a list is a named triple from the list to the phrase of the item, e.g.
`house heating input heated area` = `house heating inputs` · `has a` · `heated area`.
Some items have items of their own:

- a row group has rows (`has a`), and a row has filters (`with`)
- a detail table has columns (`has a`) and extra rows (`with`), and a column has filters (`with`)

The order of the items is a chain: an item `is follower of` the item before it.

### Texts and marks

A text or a mark is a triple from an item, a list or a phrase to a keyword. A text
is the `description` of that triple.

| keyword                | effect                                          |
|------------------------|-------------------------------------------------|
| `calculator label`     | the shown name, e.g. the page title or a row label |
| `calculator hint`      | the help text, e.g. the lead text or the text below a field |
| `calculator highlight` | shows a row in bold as a key figure             |
| `calculator signed`    | colours the positive and negative numbers of a row group |

A text of an item is used on this page only. A text of a phrase is used on every
page that shows the phrase, and the text of the item wins. Without any text, the
editor shows the name of the phrase.

### Input fields

- **number with limits**: `heated area` · `can be` · `number with limits`, with the
  values `field limit down`, `field limit up` and `field step` of the phrase
- **phrase selector**: `heating system` · `can be` · `phrase selector`. Its options are:
  - triples named `heating system option …`, each with an optional label and order
  - phrases that `is a` heating system, with an optional order
  - with `can be` · `field phrase selector value based`: the phrases of the values
    of the field, if no option is listed
- **the selected option**: the page phrase `uses` it, e.g. `sample house` · `uses` · `heat pump`
- **value phrases**: a field shows only the value that also has these phrases, e.g.
  `heated area` · `has a` · `living space`, and that triple `can be` · `field value phrases`

### Numbers

- **assumed**: a value with the word `assumed`; a second value with the extra word
  `confidence` gives its confidence
- **measured**: a value with a `source`; the page shows the number of the source
- **field limits**: a value with the phrase of the field and a field keyword
- **units**: a triple with the type `measure unit`, e.g. `kWh per year`, or a
  symbol, e.g. `m²` · `is symbol for` · `square metre`; a share with the unit
  `percent` is shown in %
- **side results**: a result shown below a row, e.g.
  `annual heat demand side result` = `annual heat demand` · `has a` · `heat demand per area`
- **a row phrase built from the group phrase**: if the row phrase is a triple from the
  group phrase, e.g. `saving heat pump` = `saving` · `with` · `heat pump`, the row shows
  the label of `heat pump`

## Proposed elements

These keywords are defined in
[calculator_keywords.json](../src/main/resources/messages/calculator_keywords.json),
each with a description that starts with `PROPOSED NEW ELEMENT`. The editor does not
read them yet. The sample uses each of them once, so that the page structure that
they need is clear before the code is written.

### Input fields

| element | triple in the sample | effect |
|---|---|---|
| checkbox | `solar thermal` · `can be` · `calculator checkbox` | a checkbox; checked is the value 1, unchecked 0, so a formula can use it as a factor |
| read only | `house heating input heat demand per area` · `has a` · `calculator read only` | the input list shows the result of the item phrase instead of a field |
| repeat | `house heating input occupant` · `has a` · `calculator repeat` | one field per instance with an add and a remove button |
| warning | `heat pump capacity gap` · `has a` · `calculator warning` | the description is shown below the input fields while the result of the page phrase is above zero |

A repeated input:

- the values `field limit down` and `field limit up` of the repeat triple are the
  minimum and the maximum count
- the instances are the phrases that `is a` the item phrase, e.g. `first occupant` · `is a` · `occupant`;
  a new instance is named by the next ordinal word
- the value of an instance has the instance phrase, e.g. `sample house`, `first occupant`, `litre per day`
- a result column or a detail table column of the repeated phrase is shown once per instance

### Tables

| element | triple in the sample | effect |
|---|---|---|
| section | `house heating detail fuel` · `has a` · `calculator section` | the detail table gets a collapsed section of its own with its label as the summary |
| row number | `house heating detail fuel` · `has a` · `calculator row number` | the first column is the position of the row |
| order | `house heating detail fuel` · `is ranked by` · `price` | the rows are ordered by the number of the phrase, the highest first |
| editable column | `house heating detail column price` · `has a` · `calculator editable` | the cells are number fields that join the save form |
| phrase column | `house heating detail column billing unit` · `has a` · `calculator phrase column` | a cell shows the phrase that the numbers of the row have and that is a column phrase, e.g. `CHF per kWh` with `CHF per kWh` · `is a` · `billing unit` |
| page independent | `house heating detail fuel` · `has a` · `calculator page independent` | the cells select their numbers without the page phrase, e.g. the energy prices that are the same for every house; also for an item or the check table |
| diagram | `house heating detail column price` · `has a` · `calculator diagram` | the column is also drawn as a line along the rows, with a legend of the last numbers |
| decimals | the value `price`, `calculator decimals` = 3 | every cell of the phrase shows this number of decimals |
| colour | `house heating result columns` · `has a` · `calculator colour` | the columns get the colours of the page palette in their order |

Two proposed rules for the detail tables need no keyword:

- the rows are the phrases below the table phrase over several levels of `is a` and
  `is part of`, without the phrases that have rows below them, e.g. the stocks, the bonds
  and the cash of a portfolio below `holding`
- a cell skips the numbers that have the phrase of another column of the same table, e.g.
  the count column of a child skips the cumulative deviation of that child

### Checks and notes

- **check table**: `house heating` · `has a` · `calculator check` shows one row per value
  of the page with the word `expected`. The calculated number is the number with the
  same phrases without `expected`, and the source of the expected value is the hint of
  the row. The table shows both numbers and whether they match.
- **notes**: `house heating notes` = `house heating` · `has a` · `calculator note` is a list.
  The description of an item, e.g. `house heating note sizing`, is a note of the page with
  the label of the item in bold in front. The notes come before the descriptions of the
  formulas.

### Buttons

| element | triple in the sample | effect |
|---|---|---|
| file import | `house heating detail fuel` · `has a` · `calculator file import` | an import button with the label of the triple; the file fills the values of its phrase |
| download | `house heating inputs` · `has a` · `calculator download` | exports the tables of the page as a spreadsheet |
| printable report | `house heating inputs` · `has a` · `calculator printable report` | shows the page as a printable pdf |
| copy | `house heating detail fuel` · `has a` · `calculator copy` | copies the rows of the table as zukunft.com json or as csv |

### Calculations

The formula engine calculates with `+ - * /` only. A calculator like the portfolio split
also needs rounding to a multiple, min and max, a sum over a list, a condition, a sort by
a result and a walk that carries a number from one row into the next. Until the engine
has them, such results are stored as values and an input change does not calculate
them again.
