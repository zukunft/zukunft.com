PREPARE result_std_by_id FROM
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
     WHERE result_id = ?';