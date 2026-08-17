# pending - list of planned llm prompts with prio 1

## start page

review buttons

web/value/value_list.php:1352 and :1360 were not migrated — $phr_row->btn_add($back) / $val_main->btn_add($back) still pass the old string $back into the new array $url_arr parameter. dsp_table($phr_row, $back, ...) is untyped so nothing catches it statically; at runtime this is a TypeError.

deprecate dsp_edit

decprecate http_old

review $back

review $msg

create in triple list a 'validate_alias_direction' function that checks if the alias verb always leads to the same main phrase

based on the /test/create/ class function and the solution_prio.json create a set of test data (a filled $dto object where the database id is taken from the /test/create/ class functions and the rest e.g. the val)

the basic steps to show the start page are

- table with 'global issues'
- sort global issues by impact which is htp
- get mayor, main and minor columns linked to 'global issues' via triple
- the order of the column may differ and is relative e.g. 'per cent is after number'
- the number of rows to show is taken from the config but can be overwritten

Worth fixing
formula button tooltips lost their object name
button is now btn_edit() with no $url_arr. Same shape at view.php:171 and word.php:751.
Production web/ code now includes and reads test constants. web/view/view.php:67 and web/word/triple_list.php:51 add a real include_once test_paths::CONST . 'word_names.php' and hardcode word_names::ZH_ID as the back target. list_sort.php extends the same pattern to triple_names::GLOBAL_PROBLEM_ID — and there the include_once is commented out while the use is active, so it only works via the autoloader. triple_list.php:147 has a TODO Prio 0 admitting the $url_arr is a placeholder; view.php:145 has none. Either way "Zurich" as the back link on a generic view navbar is a placeholder, not behaviour.
Dead code left behind in view.php:617,619 — $call_edit / $call_del are still built via url_back(...) but no longer used, since btn_edit()/btn_del() build their own url. The buttons also lost the word= and back context those urls carried.
Unused additions in tests: graph_tests.php:60-62 adds $base_url, $lan, $url_arr — none used in the file; languages is imported unused in both graph_tests.php and horizontal_ui_tests.php.
Tooltip regression, visible in base_ui_tests.php:512: the delete title went from delete this formula of scale minute to sec to delete this formula — the object name dropped out of the button title. Intentional?
Nit: shared/const/words.php:335 inserts SOLUTION after STATEMENT, breaking the alphabetical order of that list.
docs/llm/pending.md drops the self:: vs $this:: item (correctly — db_object::btn_add uses $this::VIEW_ADD_ID) but keeps the AUTO_UPDATE_TEST_FILES item, which is still open per point 1.              
horizontal_ui_tests:106-111 calls these for many classes but only asserts the icon is present, not the url — so nothing catches it.

Worth deciding
Button tooltips lost their object detail. formula::btn_edit()/btn_del() were renamed to *_back, so the formula page now uses the generic db_object::btn_edit(), which passes no $explain. Visible in formula.html: title="change formula for scale minute to sec" → "change formula", and "delete this formula of scale minute to sec" → "delete this formula". If that's intended, fine; if not, the new generic variants need an $explain path too.
api/.htaccess — <FilesMatch "^[A-Za-z0-9_]+$"> re-allows every extensionless name under /api, which is what the routes need. Worth noting it also re-allows any other dot-free file that lands there; a tighter alternative is listing the route names, at the cost of maintenance. Your call — the current form is the one I proposed.
src/test/resources/unit/user/list.csv and api/ui_config/ui_config.json changed too — presumably from a reset_db run; worth a glance that they belong in this commit.


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

## later

add the word splitter to convert_wikipedia_table.php

