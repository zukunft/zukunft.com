PREPARE value_phrase_link_list_by_group_id FROM
   'SELECT s.value_phrase_link_id,
           s.user_id,
           s.group_id,
           s.phrase_id,
           s.weight,
           s.link_type_id,
           s.condition_formula_id,
           l.group_id
      FROM value_phrase_links s
 LEFT JOIN `values` l ON s.group_id = l.group_id
    WHERE s.group_id = ?';