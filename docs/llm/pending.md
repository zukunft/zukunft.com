# pending - list of planned llm prompts with prio 1

## start page

the basic steps to show the start page are

- table with 'global issues'
- sort global issues by impact which is htp
- get mayor, main and minor columns linked to 'global issues' via triple
- the order of the column may differ and is relative e.g. 'per cent is after number'
- the number of rows to show is taken from the config but can be overwritten

## main pages

#### change log

in the change log flip the 'to' and 'from'

### word and triple

add a 'my' tab additional to the views and changes tabs that is shown if the user logged in and has created some user overwrites. the tab should list the entries of the related "user_" tables e.g. for words the user specific changes from "user_words" 

"more" should always be a link that shows more values

### values

add change log to value default view

## workflows

add the missing workflows for the main objects e.g. source, ref, view, component. Compared to the word workflows the workflows only need one back test.

## admin

add to the admin menu a page that shows the system errors
