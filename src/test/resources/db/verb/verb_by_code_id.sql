PREPARE verb_by_code_id (text) AS
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
     WHERE code_id = $1 AND code_id IS NOT NULL;