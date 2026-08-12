# pending - list of planned llm prompts with prio 1

## start page

the basic steps to show the start page are

- table with 'global issues'
- sort global issues by impact which is htp
- get mayor, main and minor columns linked to 'global issues' via triple
- the order of the column may differ and is relative e.g. 'per cent is after number'
- the number of rows to show is taken from the config but can be overwritten

## main pages

in the logout page add an OK button that calls the back page from the url without token and make the "you have been logged out" bigger

### triple

show the missing db fields in the triple default view: the weight, the condition formula

### verb

extend the verb default view, which today only shows the verb name and the related triples, with the missing db fields: the description, the plural (name_plural), the reverse (name_reverse), the plural reverse (name_plural_reverse), the name used in formulas (formula_name) and the usage

### values

add change log to value default view

show the missing db fields in the value default view: the source of the value, the timestamp of the last update and the share and protection status

### source

add the missing db field to the source add and edit views: the code_id field, shown only for users whose profile passes can_set_code_id (for sources the code_id is a user changeable field)

extend the source default view, which today only shows the source name and the related values, with the missing db fields: the source type, the url as a link, the description and the usage

### ref

show the non-changeable ref db fields last_update (the timestamp of the last successful update of the reference) and impact as display-only info in the ref edit view

extend the ref default view, which today only shows the reference name and the related values, with the missing db fields: the linked phrase, the reference type, the external key, the url as a link to the external page, the source, the description and the last_update timestamp

### formula

show the missing db fields in the formula default view: the latex format (rendered, next to the existing expression components), the formula type, the 'all values needed' flag and the timestamp of the last update

### result

extend the result default view, which today only shows the related results, with the missing db fields of the requested result itself: the result value with its phrase group, a link to the formula that calculated it and the timestamp of the last calculation (last_update)

### view

add the missing db fields to the view add and edit views: a language selection (language_id, preselected with the user language) and, for users whose profile passes can_set_code_id, the code_id field; show the usage as display-only info in the edit view, because unlike the other edit views the view edit view has no usage section yet

create the missing view default view (view_default): a view that shows a view itself with its name, description, type, style and the list of linked components, plus the usage e.g. the terms that use it as their default view

### component

add the missing db fields to the component add and edit views: a formula selection (formula_id, used for the calculated component types), a linked component selection (linked_component_id) together with its link types (component_link_type_id and link_type_id), and, for users whose profile passes can_set_code_id, the code_id and ui_msg_code_id fields (with ui_msg_code_id_vars, ui_msg_code_id_exception and ui_msg_value_exception); show the usage as display-only info in the edit view

create the missing component default view (component_default): a view that shows a component itself with its name, description, type, style, the row and column phrases and the views that use it

### view link

add the missing db field to the view link add and edit views: the description field (a user changeable term_view db field); additionally the forms show fields without a term_view db column: a view style select in both forms and a priority field in the add form resp. the component link order number field in the edit form, so either add the style and priority columns to the term_view table (see the priority TODO in term_view.php and form_field_view_link_priority) or remove these form fields, and use the same priority component in the add and the edit form

### formula link

the formula link add and edit views show a description field, but the formula_link table has no description column; either add the description column to the formula_link table or remove the description field from both forms

## workflows

add the missing workflows for the main objects e.g. source, ref, view, component. Compared to the word workflows the workflows only need one back test.

## admin

add to the admin menu a page that shows the system errors

# move to Prio 2

## log in 

if no picture of the user is uploaded create a random picture and show it in the top write corner if the user is logged in

## language 

### word

add the missing db fields to the word add and edit views: a language selection (language_id, preselected with the user language) and, for users whose profile passes can_set_code_id, the code_id field; show the non-changeable fields impact and inactive as display-only info in the edit view next to the existing usage component

show the missing db fields in the word default view the language e.g. as a small info line next to the description

### triple

add the missing db fields to the triple add and edit views: a language selection (language_id, preselected with the user language), the given name (name_given) as its own field beside the generated name (name_auto, display-only) so the user sees the difference and can empty the given name to fall back to the generated one, the condition formula selection (triple_condition_id) and, for users whose profile passes can_set_code_id, the code_id field; show the non-changeable fields impact and inactive as display-only info in the edit view next to the existing usage component
