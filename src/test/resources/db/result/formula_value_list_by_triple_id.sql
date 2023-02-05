PREPARE formula_value_list_by_triple_id (int) AS
    SELECT s.formula_value_id,
           s.formula_id,
           s.user_id,
           s.source_phrase_group_id,
           s.source_time_id,
           s.phrase_group_id,
           s.formula_value,
           s.last_update,
           s.dirty,
           l.phrase_group_id
      FROM formula_values s
 LEFT JOIN phrase_group_triple_links l ON s.phrase_group_id = l.phrase_group_id
     WHERE l.triple_id = $1;