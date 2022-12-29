PREPARE verb_list_all FROM
   'SELECT
         verb_id,
         verb_name,
         code_id,
         description,
         name_plural,
         name_reverse,
         name_plural_reverse,
         formula_name,
         words
    FROM verbs
ORDER BY verb_id
   LIMIT ?';