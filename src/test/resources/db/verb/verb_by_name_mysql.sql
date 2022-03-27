PREPARE verb_by_name FROM
   'SELECT verb_id,
           verb_name,
           code_id,
           description,
           name_plural,
           name_reverse,
           name_plural_reverse,
           formula_name,
           words
      FROM verbs
     WHERE ( verb_name = ? OR formula_name = ?)';