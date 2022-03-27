PREPARE verb_by_name (text, text) AS
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
     WHERE ( verb_name = $1 OR formula_name = $2);