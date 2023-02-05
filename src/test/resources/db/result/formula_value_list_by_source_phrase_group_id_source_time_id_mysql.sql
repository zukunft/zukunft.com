PREPARE formula_value_list_by_source_phrase_group_id_source_time_id FROM
   'SELECT formula_value_id,
           formula_id,
           user_id,
           source_phrase_group_id,
           source_time_id,
           phrase_group_id,
           formula_value,
           last_update,
           dirty
      FROM formula_values
     WHERE source_phrase_group_id = ?
       AND source_time_id = ?';