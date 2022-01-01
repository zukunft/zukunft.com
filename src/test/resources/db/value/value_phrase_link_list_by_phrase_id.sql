PREPARE value_phrase_link_list_by_phrase_id (int) AS
    SELECT s.value_phrase_link_id,
           s.user_id,
           s.value_id,
           s.phrase_id,
           s.weight,
           s.link_type_id,
           s.condition_formula_id,
           l.phrase_id
      FROM value_phrase_links s
 LEFT JOIN phrases l ON s.phrase_id = l.phrase_id
     WHERE s.phrase_id = $1;