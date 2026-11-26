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
| `calculator signed`    | colours the positive and negative numbers of a row group; a flagged row or column also shows the plus sign and no colour for a number that rounds to zero |
| `calculator number note` | a short note behind the unit of the number in a row label, e.g. PV-weighted |

A text of an item is used on this page only. A text of a phrase is used on every
page that shows the phrase, and the text of the item wins. Without any text, the
editor shows the name of the phrase.

### Input fields

- **number with limits**: `heated area` · `can be` · `number with limits`, with the
  values `field limit down`, `field limit up` and `field step` of the phrase
- **phrase selector**: `heating system` · `can be` · `phrase selector`. Its options are:
  - triples named `heating system option …`, each with an optional label and order
  - phrases that `is a` heating system, with an optional order
  - an option without a value only selects its phrase and has no number, so the
    results keep their numbers while it is selected
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
- **the previous row**: an operand with `prior` next to another phrase, e.g. `"cumulative deviation","prior"`,
  reads the number of the phrase before the phrase of the result context in its `is follower of` chain; the
  first phrase of the chain has the number 0, and a context with phrases of two chains selects no number
- **units**: a triple with the type `measure unit`, e.g. `kWh per year`, or a
  symbol, e.g. `m²` · `is symbol for` · `square metre`; a share with the unit
  `percent` is shown in %
- **the unit of the result table**: if all numbers of the table except the shares have one unit, the first header
  names it and the cells show only the number, e.g. `CHF`; else each cell shows its unit
- **side results**: a result shown below a row, e.g.
  `annual heat demand side result` = `annual heat demand` · `has a` · `heat demand per area`
- **a row phrase built from the group phrase**: if the row phrase is a triple from the
  group phrase, e.g. `saving heat pump` = `saving` · `with` · `heat pump`, the row shows
  the label of `heat pump`

## Proposed elements

These keywords are defined in
[calculator_keywords.json](../src/main/resources/messages/calculator_keywords.json).
A description that starts with `PROPOSED NEW ELEMENT` marks a keyword that the code does
not fully read yet. The editor does not read any of them. The sample uses each of them
once, so that the page structure that they need is clear before the code is written.
The calculator page already shows the checkbox, the read only field and the fields of a
repeated input.

### Input fields

| element | triple in the sample | effect |
|---|---|---|
| checkbox | `solar thermal` · `can be` · `calculator checkbox` | a checkbox; checked is the value 1, unchecked 0, so a formula can use it as a factor |
| read only | `house heating input heat demand per area` · `has a` · `calculator read only` | the input list shows the number of the item phrase instead of a field |
| repeat | `house heating input occupant` · `has a` · `calculator repeat` | one field per instance with an add and a remove button; the description of the triple is the text of the add button, and a calculator label of an instance is the name behind the label of the field; what the repeat triple `has a` are the instances that the add button adds in the browser, the next one that is no instance yet, with the number of the first instance (a share at most what the others leave of 100 %) and the results of the first instance calculated again; the button is disabled if none is left; the remove button takes an instance with its numbers and results from the page, while there are more than one, and an instance keeps the name of its phrase |
| warning | `heat pump capacity gap` · `has a` · `calculator warning` | the description is shown below the input fields while the result of the page phrase is above zero; the page calculates the result again when an input changes |
| slider | `house heating input heated area` · `has a` · `calculator slider` | a range slider coupled to the number field, with the field limits and the field step of the phrase; a `field step` value of the triple itself is the step of the slider; without javascript only the number field is shown |
| preset | `house heating` · `has a` · `calculator preset` | a list with one button per item, with the label and the hint of the item; the button sets each input to the value that has the item phrase and the phrases of the input, e.g. `budget case`, `heat pump efficiency`; without javascript the buttons are not shown |
| tag | `house heating input heated area` · `has a` · `calculator tag` | the description replaces the generated measured or assumed tag, e.g. `measured 50-500` |

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
| row number | `house heating detail fuel` · `has a` · `calculator row number` | the first column is the position of the row, and the row shows the `calculator hint` of its phrase as sub text |
| column unit | `house heating detail column fuel` · `has a` · `calculator column unit` | each cell of the column shows the unit of its number as sub text e.g. shares |
| signed column | `house heating detail column saving` · `has a` · `calculator signed` | a number of the column shows its sign in the style of the sign, also the plus sign |
| order | `house heating detail fuel` · `is ranked by` · `price` | the rows are ordered by the number of the phrase, the highest first |
| editable column | `house heating detail column price` · `has a` · `calculator editable` | the cells are number fields that join the save form |
| phrase column | `house heating detail column billing unit` · `has a` · `calculator phrase column` | a cell shows the phrase that the numbers of the row have and that is a column phrase, e.g. `CHF per kWh` with `CHF per kWh` · `is a` · `billing unit` |
| page independent | `house heating detail fuel` · `has a` · `calculator page independent` | the cells select their numbers without the page phrase, e.g. the energy prices that are the same for every house; also for an item or the check table |
| alternative order | `walk order option bank statement` · `has a` · `calculator alternative order` | while the user has chosen this selector option, the rows of a detail table and the operand `prior` follow the `is alternative follower of` triple of a phrase instead of its `is follower of` triple, and the table is not ranked by its `is ranked by` phrase; only the javascript page walks it so far |
| diagram | `house heating detail column price` · `has a` · `calculator diagram` | the column is also drawn as a line along the rows, with a tooltip per point and a legend of the last numbers; if the filter of the column is an instance of a repeated input, one line per instance in the colours of the instances |
| decimals | the value `price`, `calculator decimals` = 3 | the cells of the row of the phrase in the result table show this number of decimals |
| result width | the value `house heating`, `calculator result width` = 896 | the result panel of the page has this width in pixels instead of the standard width |
| table hint | `house heating detail fuel` · `has a` · `calculator hint` | the description is shown below the detail table or the check table |
| colour | `house heating column heat pump` · `has a` · `calculator colour` | a flagged single column gets the first colour of the page palette and the instances of a repeated column the others cycling, in the header and in the cells of a detail table |
| difference column | `house heating column difference` · `has a` · `calculator difference` | the result column shows the first column minus the second column of its row; the cell stays empty if one of them has no number |
| cell | `house heating row annual heat demand` · `has a` · `calculator cell` | a list: an item with a `with` triple to a column phrase shows the number of the page phrase and the item phrase in this column of the row, e.g. the cost of the alternative in the column without the plant; the first column shows the number of the row phrase also without the column phrase if no number has both |
| sensitivity | `house heating` · `has a` · `calculator sensitivity` | a collapsed table that the browser calculates per cell while it is open: its first `has a` item names the input of the rows, the second the input of the columns; their `has a` items are the levels with a label, and the value of the table phrase and the level replaces the input; the items of its `calculator cell` list are the numbers of a cell, and the verdict colours the cell; without javascript the cells are empty |

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

### Arguments and verdict

- **more**: `house heating` · `has a` · `calculator more` is a lead text in a collapsed section below the hint.
- **arguments**: `house heating arguments` = `house heating` · `has a` · `calculator argument` is a list. The label of
  the list is the heading of the two sides separated by `|`. An item names a triple: a `supports` triple is an argument
  of the first side and a `limits` triple one of the second side, with its label in bold and its description as the
  text. The first argument of a side is shown and the others are collapsed.
- **verdict**: `house heating verdict` = `house heating` · `has a` · `calculator verdict` compares the number of its first
  `with` phrase with the number of its second `with` phrase, the threshold. Its three `has a` items in their order
  are the states for a number below minus the threshold, above the threshold and in between, with the label as the
  headline and the hint as the text. A `calculator note` of the verdict is shown below it.
- **scale**: `house heating verdict scale` = `house heating verdict` · `has a` · `calculator scale` draws the meter. The
  values `field limit down` and `field limit up` of the triple are its ends, and the description is the text
  below it with the parts separated by `|`.

### Placeholders in a text

The description of a text triple or an argument can contain:

| placeholder | shows | example |
|---|---|---|
| `{num:text}` | the text in the style of a number | `{num:820 CHF}` |
| `{source:name}` | the `[n]` link to the source in the source list | `{source:sample fuel price statistic}` |
| `{result:phrase:decimals}` | the live number of the phrase for the page phrase, formatted with these decimals, or without decimals if the placeholder names none; also a sub text of a row and a verdict text use it | `{result:annual saving:0}` |

### Buttons

| element | triple in the sample | effect |
|---|---|---|
| file import | `house heating detail fuel` · `has a` · `calculator file import` | an import button with the label of the triple; the file fills the values of its phrase; on the page or its base phrase the triple is a list: a collapsed section with its label and an import button per item |
| download | `house heating inputs` · `has a` · `calculator download` | exports the tables of the page as a spreadsheet |
| printable report | `house heating inputs` · `has a` · `calculator printable report` | a link to the pdf report of the page: the logo of the user setting `report logo` or of zukunft.com, a table per column filtered by `split count` with the positions of this portfolio, the other tables, notes and sources of the page and the formulas used |
| copy | `house heating detail fuel` · `has a` · `calculator copy` | copies the rows of the table as zukunft.com json or as csv |
| case | `portfolio split inputs` · `has a` · `calculator case` | the field for the name of a case above the save button of a page that is built from another phrase; a page without this triple has no such field |

### Calculations

The formula engine calculates with `+ - * /`, brackets, `min( a ; b ; … )`, the smallest of
its arguments, and `max( a ; b ; … )`, the largest; the arguments are separated by `;`, because
a `,` joins the phrase names of one operand (`"delivered electricity","lifetime"`). It also
calculates `if( condition ; then ; else )` with `=`, `&` and `|` in the condition and only
the chosen part, the power `^` and the functions of one number `abs`, `cos`, `exp`, `floor` (round down), `ln`, `log`
(base 10, or `log( x ; base )`), `round`, `sec`, `sin` and `sqrt`, with an angle in radian. `sum`, `count`, `mean`,
`largest` and `smallest` take a list: a phrase as their argument stands for every stored value that names the phrase and
all phrases of the calculation, without calculated results and without a value that also names a
variance or a confidence or all phrases of another value of the list and more (e.g. the expected count of an
agreement next to the count of the walk), and a calculation inside the argument like `sum( "a" * "b" )` gives an
error. All functions are listed in `docs/math.md`. A calculator
like the portfolio split also needs rounding to a multiple, a sort by
a result and a walk that carries a number from one row into the next. Until the engine
has them, such results are stored as values and an input change does not calculate
them again. A formula may name a function that the engine does not have, e.g. to document a
law, but calculating it shows the user an error that names the function and gives no number.
If a calculation fails, the stored result is overwritten with nothing and never kept, so that
the problem is obvious on every page instead of hidden behind an outdated number.

## csv format

The format of a csv file, e.g. the bank statement of the positions that fills the holdings of the portfolio split
page, is a phrase with triples to the keywords of `csv_keywords.json` and with value templates, so that a new format
is a definition json and no code. Example: `src/test/resources/import/portfolio_csv/bank_position_csv.json`. A cell
text of the csv, e.g. a header or an asset class of the bank, is the description of a triple and is compared with
the complete cell, so the phrase names stay lower case.

| keyword | triple in the example | effect |
|---|---|---|
| header start | `bank position csv` · `has a` · `csv header start` | the description is the first cell of the header row; the lines below it with as many cells are the rows |
| column | `bank position csv columns` · `has a` · `quantity` | a list: each item of the group triple to `csv column` is a column, its description the header text; the first column of the header row with this text |
| required, number, positive | `bank position csv quantity` · `has a` · `csv number` | a row whose cell is empty, no number or not above zero is left out and reported |
| fallback | `bank position csv price currency` · `has a` · `csv fallback` | the description names the column whose cell is taken while this cell is empty |
| row name, row description | `bank position csv instrument` · `has a` · `csv row name` | the cell is the name or the description of the word of the row |
| kind | `bank position csv asset class kind` · `has a` · `stock` | a list from the kind column: each item maps the cell of its description to its kind; the word of a row is a its kind, and a row whose cell has no item is left out and reported |
| context | `bank position csv instrument` · `is part of` · `csv context` | stands for the first phrase of the context of the conversion, e.g. the portfolio |
| optional | a word of a value template | no value for an empty or zero number cell |

The templates:

- a **value** of the definition is a template: per row each column item is the phrase of its cell, the cell of a
  number column times the number of the template is the number (`0.01` for a price in percent), a template without
  a number column writes its own number (a default lot size), a kind phrase limits the template to the rows of that
  kind or of a kind that is a it, and a template without the row name column is written once per phrases, e.g. the
  exchange rate of a currency pair; a written value gets the source of the csv, a template with `assumed` none
- a **triple** of the definition from or to a column item that is no triple to a csv keyword is a template too, e.g.
  the hint of a holding or the currency pair: each column item is the phrase of its cell, and `{<header text>}` in
  its description is the cell of that column (`{Instrument Name}`); a template whose name starts with the name of its
  from column gets the row cell instead (`bank position csv instrument hint` gives `2120.001.01 hint`), the others
  no name; a template whose from and to are the same cell writes nothing, neither does a value template that uses
  it; a written triple whose from, verb and to the definition declares is written as that declaration, e.g. a
  currency pair of `portfolio_split_calculator.json`
- the words and triples that the templates use are declared like in any import json and copied into the result
