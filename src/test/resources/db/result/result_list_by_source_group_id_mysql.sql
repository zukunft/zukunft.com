PREPARE result_list_by_source_group_id FROM
   'SELECT group_id,
           formula_id,
           user_id,
           source_group_id,
           result,
           last_update
      FROM results
     WHERE source_group_id = ?';