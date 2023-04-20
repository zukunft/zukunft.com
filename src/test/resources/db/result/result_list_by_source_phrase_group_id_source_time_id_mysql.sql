PREPARE result_list_by_source_phrase_group_id_source_time_id FROM
   'SELECT result_id,
           formula_id,
           user_id,
           source_phrase_group_id,
           source_time_id,
           phrase_group_id,
           result,
           last_update,
           dirty
      FROM results
     WHERE source_phrase_group_id = ?
       AND source_time_id = ?';