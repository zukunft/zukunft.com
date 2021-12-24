PREPARE value_phrase_link_list_by_value_id FROM
   'SELECT s.value_phrase_link_id,
           s.user_id,
           s.value_id,
           s.phrase_id,
           s.weight,
           s.link_type_id,
           s.condition_formula_id,
           l.value_id
      FROM value_phrase_links s
 LEFT JOIN values l ON s.value_id = l.value_id
    WHERE l.value_id = ?';