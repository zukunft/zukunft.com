# The calculator editor json

The calculator editor reads, exports and shows one json file per calculator page.
A complete example that uses every part at least once is
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
