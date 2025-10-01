Zukunft exchange json
---------------------

The zukunft.com pod use the same json format for transferring data between the pods and the im- and export. 

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
- triples - a list of triples that are used to configure the component e.g. Year is used as a column
