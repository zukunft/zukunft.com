Zukunft exchange json
---------------------

The zukunft.com pod use the same json format for transferring data between the pods and the im- and export. 

complete sample
---------------

A json file always starts with the header fields and is followed by the data sections. Each section is a json array of objects, and the objects refer to each other **by name**: a triple names its `from`/`verb`/`to`, a value lists the `words` it belongs to and a word can point to its default `view`. The objects that are referenced are expected to be part of the same json (in most cases the words and triples sections).

The following sample contains the header and one entry for each main section:

```json
{
  "version": "0.0.3",
  "time": "2025-06-07 10:00:00",
  "user": "timon",
  "selection": [
    "sample of the zukunft.com exchange format"
  ],
  "words": [
    { "name": "City", "description": "a large human settlement" },
    {
      "name": "Zurich",
      "description": "the largest city of Switzerland",
      "view": "Word",
      "refs": [
        { "name": "Q72", "type": "wikidata" }
      ]
    },
    { "name": "Geneva", "description": "a city in western Switzerland" },
    { "name": "inhabitant", "plural": "inhabitants", "description": "a person who lives in a place" },
    {
      "name": "million",
      "description": "the scaling factor of one thousand thousand",
      "share": "public",
      "protection": "admin_protection"
    },
    { "name": "2020", "type": "time" }
  ],
  "verbs": [
    { "name": "is a", "description": "link a word to its parent type", "reverse": "are" }
  ],
  "triples": [
    { "name": "", "from": "Zurich", "verb": "is a", "to": "City" }
  ],
  "sources": [
    { "name": "Statistical Office Switzerland", "url": "https://www.bfs.admin.ch" }
  ],
  "references": [
    { "phrase": "Zurich", "name": "Q72", "type": "wikidata" }
  ],
  "values": [
    {
      "words": [ "Zurich", "inhabitant", "2020" ],
      "number": "434008",
      "source": "Statistical Office Switzerland"
    }
  ],
  "value-list": [
    {
      "context": [ "inhabitant", "2020" ],
      "source": "Statistical Office Switzerland",
      "values": [
        { "Zurich": 434008 },
        { "Geneva": 203856 }
      ]
    }
  ],
  "value-time-series": [
    {
      "context": [ "Zurich", "inhabitant" ],
      "time-values": [
        { "date": "2019-01-01T00:00:00+00:00", "value": 428737 },
        { "date": "2020-01-01T00:00:00+00:00", "value": 434008 }
      ]
    }
  ],
  "formulas": [
    {
      "name": "inhabitants in million",
      "description": "scale the number of inhabitants to millions",
      "expression": "\"million\" = \"inhabitant\" / 1000000",
      "assigned_word": "inhabitant"
    }
  ],
  "views": [
    {
      "name": "Word",
      "description": "the default view to show a word with its values",
      "type": "detail_view",
      "components": [
        { "position": "1", "name": "Word name" }
      ]
    }
  ],
  "components": [
    {
      "name": "Word name",
      "description": "simply show the word or triple name",
      "type": "phrase_name"
    }
  ]
}
```

`value-list` and `value-time-series` are compact alternatives to many single `values`: they define a shared `context` once and add only the differentiating word, triple or timestamp per entry. A `results` section can be added with the same shape as `values` plus a `source words` list; it is only used to verify that an im- and export recreates the same numbers (see the results section below). All fields are explained per section in the rest of this document.

header
------

The json is expected to have some header information like

- the version that has created the json
- the time of the creation
- the user who has created the json 
- the name of the selection

sections
--------

After the header the json contains arrays with the data sections which are

- words
- verbs
- triples
- sources
- references
- values
- formulas
- results
- components
- views

general fields
--------------

All im- and exportable objects have these json fields:

- share - for the access control 
- protection - for the write control
- owner - to define the user who created the standard
- excluded - to remove the object for one user and keeping it for the others

word fields
-----------

For the word entries these additional json fields can be used:

- name - the unique text key for the word
- description - the tooltip explanation who the word is expected to be used
- type - to assign predefined functionality to this word e.g. if a measure word is divided by a measure word that result is expected to be in percent
- view - the default view that should be used for this word
- language forms - list of language specific forms of this word e.g. the plural just to make the output look nicer

verb fields
-----------

verbs with a code_id con only be imported by admin users. Like words verbs have the name, description, type, view and language forms field and additional:  

-  reverse - for user-friendly text if the triple is used the other way round e.g. "Zurich is a city" and "cities are Zurich, ..." 

triple fields
-------------

Like words triples have the name, description, type, view and language forms field and additional:

- from - the word or triple that is used as subject
- verb - the verb that is used as the predicate
- to - the word or triple that is used as the object

source fields
-------------

Like words triples have the name, description, type and view field and additional:

- url - the internet link 
- parent (planned) - the parent source mainly to concat the name and group the sources

reference fields
----------------

For the reference entries the json fields are (beside name and description to explain the reference to the user):

- phrase - for the word or triple that should be linked
- external-key - the unique external key 
- type - the base url and the connection type for the data synchronisation

value fields
------------

Values are always linked to a sorted list of words and triples called group. The json fields are

words - list of words and triples that can also contain a group name and description that overwrites the generated name base on the list.
number - the numeric value itself, which can also be a longer text that is not included in the search index

value list fields
-----------------

For more efficient transfer of many similar values a value list section can be used. The main difference to the single values is that a context can be defined, which is a list of words and triples that are added to each value entry. 
this way each value can be differentiated by just one word, triple or time stamp.

formula fields
--------------

Like words formula have the name, description, type and view field and additional:

- expression - the formula expression that is converted into internal references so that the words used in the formula are automatically renamed if the used word is renamed
- assigned_word - the single word or triple (one name) the formula is assigned to
- assigned - a json array with the names of the words or triples the formula is assigned to

A formula can be assigned to one or more phrases (a phrase is a word or a triple). Use `assigned_word` to assign exactly one phrase and `assigned` to assign several. The names always refer to phrases that are also part of the same json (in the words or triples sections); the phrases themselves are not repeated inside the assignment.

Trying to put more than one phrase into `assigned_word` (a comma-separated string or a json array with several entries) is rejected on import with the hint to use `assigned` instead. The export follows the same rule: it writes `assigned_word` when the formula is assigned to exactly one phrase and `assigned` when it is assigned to several, so an exported formula round-trips on re-import.

Also note that a formula name must never be the same as a word, verb or triple name, because all of these are terms and a shared name would lead to an ambiguous reference.

Sample with a single assigned phrase:

```json
{
  "name": "scale thousand to one",
  "expression": "\"one\" = \"thousand\" * 1000",
  "assigned_word": "thousand"
}
```

Sample with several assigned phrases:

```json
{
  "name": "currency conversion calculation",
  "expression": "\"target amount\" = \"source amount\" * \"exchange rate\"",
  "assigned": ["conversion", "exchange rate"]
}
```

results fields
--------------

Like values the results have the list of words and triples and additional:

- source words - a list a words and triples that have been used to create the result

The results are included in the im- and export json only to verify the im- end export by recreating the results and reporting any differences.

view fields
-----------

Views with a code_id con only be imported by admin users. Like words views have the name, description and type field and additional:

- parent (planned) - to inherit components and behavior

component fields
----------------

Like words components have the name, description and type field and additional:

- user interface message id - a unique id to translate a text to the language that the user is using
- triples - a list of triples that are used to configure the component e.g. year is used as a column
