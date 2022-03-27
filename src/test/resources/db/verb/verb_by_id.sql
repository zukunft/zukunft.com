PREPARE verb_by_id (int) AS
    SELECT verb_id,
           verb_name,
           code_id,
           description,
           name_plural,
           name_reverse,
           name_plural_reverse,
           formula_name,
           words
      FROM verbs
     WHERE verb_id = $1;