PREPARE result_list_by_src_grp FROM
   'SELECT group_id,
           formula_id,
           user_id,
           source_group_id,
           numeric_value,
           last_update
      FROM results
     WHERE source_group_id = ?';