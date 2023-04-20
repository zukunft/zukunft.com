PREPARE result_list_by_source_phrase_group_id_source_time_id (int, int) AS
    SELECT result_id,
           formula_id,
           user_id,
           source_phrase_group_id,
           source_time_id,
           phrase_group_id,
           result,
           last_update,
           dirty
      FROM results
     WHERE source_phrase_group_id = $1
       AND source_time_id = $2;